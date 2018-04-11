<?php
/**
 * Created by PhpStorm.
 * User: XianDa
 * Date: 2017/9/25
 * Time: 15:07
 */

//$flag = ini_set('default_socket_timeout', PHP_INT_MAX);  //不超时
ini_set('default_socket_timeout', 100000000); //不超时
set_time_limit(0);
require_once realpath(dirname(__FILE__)) . '/../../common.php';
require_once realpath(dirname(__FILE__)) . '/../common_script.php';

$startTime = microtime(true);
ini_set("display_errors", "On");
error_reporting(E_ALL & ~E_NOTICE);

// 这个脚本只能单进程进行
$pidFile = BASE_DIR . '/protected/data/initNsConfigFile.pid';
$flag = singleProcess(getCurrentCommand(), $pidFile);
if (!$flag) {
    exit("Sorry, this script file has already been running ...,pid:{$pidFile}\n");
}

$env = 1;
if (ENV == ENV_NEW) {
    $env = 2;
} elseif (ENV == ENV_FORMAL) {
    $env = 4;
} else {
    $env = 1;
}

$GLOBALS['targetEnv'] = $env;

$serverNodes = getServerConf($env);
if (!$serverNodes) {
    $ip = getip();
    _log("{$ip}, 未被监听");
    exit;
}

$ip = $serverNodes['server_ip'];
$nodes = explode(',', $serverNodes['keys']);
foreach ($nodes as $node) {
    $node = trim($node);
    $subscribeDatas[] = "pub_event::{$env}:$node";
    $subscribeDatas[] = "pub_version_back::{$env}:{$node}";
    _writeConfigFile($env, trim($node), 1, false);
}

$restartChannel = "pub_restart:{$env}:{$ip}";
$echoChannel = "pub_echo:{$env}:{$ip}";

$subscribeDatas[] = $restartChannel;
$subscribeDatas[] = $echoChannel;

if ($subscribeDatas) {
    $objRedis = dwRedis::init('name_serv');
    $objRedis->subscribe($subscribeDatas, function ($instance, $channel, $message) use ($restartChannel, $echoChannel) {
        if ($channel == $restartChannel) {
            _log('nameService restart');
            exit;
        } else if ($channel == $echoChannel) {
            notifyPubResult(1, $message);
        } else {
            $channels = explode(':', $channel);
            $rollBack = false;
            $notify = true;
            if ($channels[0] == 'pub_version_back') {
                _log("回滚： $channel");
                $rollBack = true;
            }
            $channelName = $channels[3];
            _writeConfigFile($GLOBALS['targetEnv'], $channelName, $message, $notify, $rollBack);
        }
    });
}

function _writeConfigFile($env, $name, $version, $notify = true, $rollback = false) {
    _fileMakeDir(CONF_PATH);

    _log("准备写入：{$name}");
    $objRedis = dwRedis::init('name_serv');

    if (!$rollback) {
        $nsNodes = $objRedis->get("pub_data:{$env}:{$name}");
    } else {
        $nsNodes = $objRedis->get("pub_data:{$env}:{$version}");
    }
    if (!$nsNodes) {
        _log("未发现数据: {pub_data:{$env}:$name}");

        return false;
    }

    $nsNodes = json_decode($nsNodes, true);

    // 特殊的一级目录
    //    if ($name == 'code') {
    $items = [];
    foreach ($nsNodes as $info) {
        if ($info['node_type'] == 'code') {
            $items[] = [
                'key_name' => $info['node_value'],
                'key_value' => $info['node_tips'],
                'value_type' => "number",
            ];
        }
    }

    if (count($items) > 0) {
        $nsNodes[] = [
            'node_name' => "{$name}:code_map",
            'node_type' => 'hash_table',
            'items' => $items,
        ];
    }
    //    }

    $configs = [];
    foreach ($nsNodes as $info) {
        $info_name = $info['node_name'];
        $info_name = explode(':', $info_name);
        if ($info['node_type'] == 'hash_table') {
            $keyName = getArrayKey($info['node_name']);
            $hashTables = $info['items'];
            $value = [];
            foreach ($hashTables as $table) {
                $value[$table['key_name']] = $table['key_value'];
            }
            $configs['hash'][$keyName] = $value;
        } else {
            $configs['string'][$info_name[1]] = $info['node_value'];
        }
    }

    $template = Template::init();
    $template->assign(compact('configs'));
    //    $tmpl = 'php';
    $tmpls = ['js', 'php'];


    $result = 1;
    foreach ($tmpls as $tmpl) {
        $strConfig = $template->fetch("name_server/{$tmpl}");
        $path = CONF_PATH . "config.{$name}.inc.{$tmpl}";
        if (file_put_contents($path, $strConfig)) {
            _log("path:{$path} 生成配置成功");
        } else {
            $result = -1;
            _log("path:{$path} 生成配置失败");
        }
        ob_flush();
    }

    if ($notify) {
        notifyPubResult($result, $version);
    }
}

function notifyPubResult($result, $version = '')
{
    $url = "http://" . CJMS_IP . "/nameService/notifyPubResult?result={$result}&version_id={$version}&phpbase2_ver=" . PHPBASE2_VERSION;
    $header = "HOST:" . CJMS_HOST_DW;

    $objHttp = new dwHttp();
    $json = $objHttp->get2($url, 3, 2, $header);
    $result = json_decode($json, true);
    if ($result['result']) {
        $msg = "version {$version} 推送成功";
    } else {
        $msg = $result['msg'];
    }

    var_dump($msg);
}

function getServerConf($env)
{
    $url = "http://" . CJMS_IP . "/nameService/ServerConf?env={$env}";
    $header = "HOST:" . CJMS_HOST_DW;

    $objHttp = new dwHttp();
    $json = $objHttp->get2($url, 3, 2, $header);
    $result = json_decode($json, true);
    if ($result['result']) {
        return $result['data'];
    } else {
        var_dump($json);
        exit;
    }
}

function getArrayKey($keyName)
{
    $keyName = explode(':', $keyName);
    array_shift($keyName);
    $str = "['" . join("']['", $keyName) . "']";

    return $str;
}

function _fileMakeDir($path)
{
    $extend = "/";
    $dirs = explode($extend, $path);
    $dirs = array_filter($dirs);
    $path = $extend;
    foreach ($dirs as $key => $value) {
        $path .= $value;
        if (!is_dir($path)) {
            var_dump("不存在{$path}");
            mkdir($path, 0777, true);
        }
        $path .= $extend;
    }
}

function _log($msg)
{
    $date = date('Y-m-d H:i:s', time());
    var_dump("[{$date}], {$msg}");
}

exit;
