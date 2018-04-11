<?php
/**
 * 定时往代理池添加代理
 * User: XianDa
 * Date: 2017/11/13
 * Time: 18:38
 */

set_time_limit(0);
require_once realpath(dirname(__FILE__)) . '/../../common.php';
require_once realpath(dirname(__FILE__)) . '/../common_script.php';

$startTime = microtime(true);
ini_set("display_errors", "On");
error_reporting(E_ALL & ~E_NOTICE);

// 这个脚本只能单进程进行
$pidFile = BASE_DIR . '/protected/data/spider_calculate.pid';
$flag = singleProcess(getCurrentCommand(), $pidFile);
if (!$flag) {
    exit("Sorry, this script file has already been running ...,pid:{$pidFile}\n");
}
echo "开始时间：{$startTime} \r\n";

$objRedis = dwRedis::init('logic');
ipProxyList();
function ipProxyList(){
    global $objRedis;

    $proxyKey = _proxyKey();
    $date = date('Y-m-d H:i:s', time());
    $score = 10;

    $proxyList = [];

    $proxyIpsFree = _getXProxyList(true);
    if($proxyIpsFree){
        foreach ($proxyIpsFree as $item) {
            $proxy = "{$item['ip']}:{$item['port']}";
            $proxyList[] = $proxy;
            var_dump("{$date} : 免费-proxy: {$proxy}");
            $objRedis->zAdd($proxyKey, $score, $proxy);
        }
    }

    $proxyBigEle= _getBigEleProxy();
    if($proxyBigEle){
        foreach ($proxyBigEle as $key => $item) {
            $proxy = "{$item['host']}:{$item['port']}";
            $proxyList[] = $proxy;
            var_dump("{$date} : 大象-proxy: {$proxy}");
            $objRedis->zAdd($proxyKey, $score, $proxy);
        }
    }

    // 设置皇室战争代理池
    _setProxyPool($proxyList);

    $proxyList = _getFreeProxy();
    if($proxyList){
        var_dump("{$date} : 61.160.36.225:8000端口数据");
        foreach ($proxyList as $proxy){
            $objRedis->zAdd($proxyKey, $score, $proxy);
        }
    }
}

function _proxyCountKey() {
    $key = 'globals:proxy_pool:count';
    return $key;
}

function _proxyKey() {
    $key = 'globals:ip_proxy_pool:total';
    return $key;
}

// 设置ip代理数据 -- java端使用
function _setProxyPool($proxyList) {

    global $objRedis;
    $url = "www.xdaili.cn";
    $key = 'globals:url_map:' . $url;

    $data = [];
    $timeOut = 20 * 60;
    $objRedis->set($key, json_encode($proxyList), $timeOut);
    $data = [
        'proxyList' => $proxyList,
        'ttl' => $timeOut,
    ];
    return $data;
}

function _getFreeProxy(){
    $url = 'http://61.160.36.225:8000/?count=100';
    $dwHttp = new dwHttp();
    $result = $dwHttp->get2($url);
    $proxyList = [];
    if($result){
        $result = json_decode($result, true);
        foreach ($result as $item){
            if($item[2] < 20){
                continue;
            }
            $proxy = "{$item[0]}:{$item[1]}";
            $proxyList[] = $proxy;
        }
    }
    return $proxyList;
}

function _getXProxyList($free = false, $count = 3, $return_type = 2) {
    return false;

    $spiderId = "f387f8f00198476ba5d00e45d657feaa";
    $secretId = "1b0a79c292c94c408bdeecabc6e201bc";
    $orderno = "YZ201710253613I6TZz0";
    $url = "http://api.xdaili.cn/xdaili-api/greatRecharge/getGreatIp";
    if (!$free) {
        $params = [
            'spiderId' => $spiderId,
            'orderno' => $orderno,
            'returnType' => $return_type,
            'count' => $count,
        ];
        $params = http_build_query($params);
        $url .= '?' . $params;
    } else {
        $url = "http://www.xdaili.cn/ipagent/freeip/getFreeIps?page=1&rows=10";
    }
    $dwHttp = new dwHttp();
    $result = $dwHttp->get2($url);

    $result = json_decode($result, true);
    $proxyList = $result['RESULT'];
    if ($free) {
        $proxyList = $result['RESULT']['rows'];
    }

    return $proxyList;
}

function _getBigEleProxy(){
    $url = "http://pvt.daxiangdaili.com/ip/?tid=558868675841028&num=200&format=json";
    $dwHttp = new dwHttp();
    $json = $dwHttp->get2($url);
    if($json){
        $json = json_decode($json, true);
        if($json['error']){
            return false;
        }
        return $json;
    }
    return false;
}

