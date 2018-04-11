<?php
/**
 * Created by PhpStorm.
 * User: XianDa
 * Date: 2017/9/26
 * Time: 14:46
 */

require_once realpath(dirname(__FILE__)) . '/../../common.php';
require_once realpath(dirname(__FILE__)) . '/../common_script.php';

$startTime = microtime(true);

// 这个脚本只能单进程进行
$pidFile = BASE_DIR . '/protected/data/initNsConfigData.pid';
$flag = singleProcess(getCurrentCommand(), $pidFile);
if (!$flag) {
    exit("Sorry, this script file has already been running ...,pid:{$pidFile}\n");
}

// 包含名字服务的配置
foreach ($GLOBALS['nameServ_php'] as $name) {
    include_once "/data/webapps/conf_new/config.{$name}.inc.php";
}

$_keys = [
    'code',
    'globals',
    'r2m',
    'shop',
];

$node_cols = ['node_name', 'env', 'node_type', 'object_type', 'dir_1', 'dir_2', 'dir_3', 'node_value', 'node_tips', 'value_type', 'enable', 'creator', 'create_time', 'update_time', 'publish_time'];
$hashTable_cols = ['node_name', 'env', 'key_name', 'key_value', 'value_type', 'enable', 'creator', 'create_time', 'update_time'];

$objNsNode = new TableHelper('ns_node', 'Web');
$objNsHashTable = new TableHelper('ns_hash_table', 'Web');

$objRedis = dwRedis::init('name_serv');

$defineds = get_defined_constants(true); // 获取定义的常量值

foreach ($_keys as $key) {

    var_dump("开始获取 {$key} 定义的数据");

    $visitKeys = $objRedis->keys($key . '*');

    $nodeDatas = [];
    $hasTableDatas = [];
    $hasTables = [];
    $hasMaps = [];
    if($visitKeys){
        foreach ($visitKeys as $visitKey) {
            $tmp = [];
            var_dump("解析 key：{$visitKey} ");
            $exp_visitKey = explode(':', $visitKey);

            if (array_key_exists($exp_visitKey[1], $defineds['user']) && !$exp_visitKey[2]) { // 判断是否常量

                var_dump("常量 {$exp_visitKey[1]}");

                $keyValue = $defineds['user'][$exp_visitKey[1]];
                $node_type = 'string';
                $node_tips = 'null';
                if (is_numeric($keyValue)) {
                    if($GLOBALS['code_map'][$keyValue]){
                        $node_type = 'code';
                        $node_tips = $GLOBALS['code_map'][$keyValue];
                    }
                }

                $nodeDatas[] = _makeNodesData($visitKey, $keyValue, $node_type, $node_tips); // 常量数据
            } else {
                if (in_array($exp_visitKey[1], $GLOBALS)) {

                    var_dump("全局变量 {$exp_visitKey[1]}");

                    if($exp_visitKey[1] == 'code_map'){
                        continue;
                    }

                    $hasMaps = _formatSourceData($visitKey, $exp_visitKey); // 判断是否全局数组
                    $nodeDatas[] = $hasMaps['nodeData'];
                    $hasTables[] = $hasMaps['hasTableData'];
                }
            }
        }

        if ($nodeDatas) {

            var_dump("格式化 nodeData 开始写入 节点数据");

            $nodeDatas = array_filter($nodeDatas);
            try {
                $objNsNode->replaceObjects($node_cols, $nodeDatas);
            } catch (Exception $ex) {
                print_r($ex->getMessage());
            }
        }

        if ($hasTables) {

            // 格式化 hashTable 数据
            foreach ($hasTables as $key => $values) {
                foreach ($values as $value){
                    $hasTableDatas[] = $value;
                }
            }

            var_dump("格式化 hasTableData 开始写入 节点哈希数据");

            $hasTableDatas = array_filter($hasTableDatas);
            try {
                $objNsHashTable->replaceObjects($hashTable_cols, $hasTableDatas);
            } catch (Exception $ex) {
                print_r($ex->getMessage());
            }
        }
    }
}

// 生成_node_节点数据
function _makeNodesData($visitKey, $keyValue = null, $node_type = 'string', $node_tips = ' ') {

    if (ENV == ENV_DEV) {
        $env = 1;
    } else if (ENV == ENV_NEW) {
        $env = 2;
    } else {
        $env = 4;
    }

    $enable = 1;
    $creator = 'admin';
    $booleans = ['true', 'false'];

    $exp_visitKey = explode(':', $visitKey);
    $dir_1 = $exp_visitKey[0];
    $dir_2 = $exp_visitKey[1] && $exp_visitKey[2] ? $exp_visitKey[1] : '';
    $dir_3 = $exp_visitKey[2] && $exp_visitKey[3] ? $exp_visitKey[2] : '';

    if($dir_2){
        switch ($dir_2){
            case 'dbInfo':
                $object_type = 'db';
                break;
            case 'r2mInfo':
                $object_type = 'r2m';
                break;
            case 'redisInfo':
                $object_type = 'redis';
                print_r($object_type);
                $p = true;
                break;
            default:
                $object_type = 'other';
                break;
        }
    }
    if($dir_1 == 'r2m'){
        $object_type = 'r2m';
    }

    $data['node_name'] = $visitKey;
    $data['env'] = $env;
    $data['node_type'] = $node_type;
    $data['object_type'] = $object_type;
    $data['dir_1'] = $dir_1;
    $data['dir_2'] = $dir_2;
    $data['dir_3'] = $dir_3;
    $data['node_value'] = $keyValue;
    $data['node_tips'] = $node_tips;
    if (is_numeric($keyValue)) {
        $value_type = 'number';
    } elseif (in_array($keyValue, $booleans)) {
        $value_type = $keyValue;
    } else {
        $value_type = null;
        if ($keyValue) {
            $value_type = 'string';
        }
    }
    $data['value_type'] = $value_type;
    $data['enable'] = $enable;
    $data['creator'] = $creator;
    $data['create_time'] = NOW;
    $data['update_time'] = NOW;
    $data['publish_time'] = NOW;
    return $data;
}

function _makeHasTableData($node_name, $keyValues) {

    if (ENV == ENV_DEV) {
        $env = 1;
    } else if (ENV == ENV_NEW) {
        $env = 2;
    } else {
        $env = 4;
    }
    $enable = 1;
    $creator = 'admin';
    $booleans = ['true', 'false'];

    $hasTables = [];
    foreach ($keyValues as $key => $value) {
        $tmp = [];
        if (is_array($value)) {
            continue;
        }
        $tmp['node_name'] = $node_name;
        $tmp['env'] = $env;
        $tmp['key_name'] = $key;
        $tmp['key_value'] = $value;
        if (is_numeric($value)) {
            $value_type = 'number';
        } elseif (in_array($value, $booleans)) {
            $value_type = $value;
        } else {
            $value_type = 'string';
        }
        $tmp['value_type'] = $value_type;
        $tmp['enable'] = $enable;
        $tmp['creator'] = $creator;
        $tmp['create_time'] = NOW;
        $tmp['update_time'] = NOW;
        $hasTables[] = $tmp;
    }

    return $hasTables;
}

function _formatCodeMap(){
    $code_map = $GLOBALS['code_map'];
    foreach ($code_map as $key => $val){

    }
}

function _formatSourceData($visitKey, $exp_visitKey) {
    $keyValues = [];
    $length = count($exp_visitKey);
    switch ($length){ // 判断数据长度找到对应的数据
        case 0:
        case 1:
            $keyValues = $GLOBALS[$exp_visitKey[0]];
            break;
        case 2:
            $keyValues = $GLOBALS[$exp_visitKey[1]];
            break;
        case 3:
            $keyValues = $GLOBALS[$exp_visitKey[1]];
            $keyValues = $keyValues[$exp_visitKey[2]];
            break;
        case 4:
            $keyValues = $GLOBALS[$exp_visitKey[1]];
            $keyValues = $keyValues[$exp_visitKey[2]];
            $keyValues = $keyValues[$exp_visitKey[3]];
            break;
    }
    $formData['hasTableData'] = _makeHasTableData($visitKey, $keyValues);
    $formData['nodeData'] = _makeNodesData($visitKey, null, 'hash_table');

    return $formData;
}
