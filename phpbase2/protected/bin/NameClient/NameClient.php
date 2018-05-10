<?php
/**
 *  名字服务客户端
 *  @author solu
 */
set_time_limit(0);
ini_set("default_socket_timeout", 100000000000);

$st = microtime(true);

ini_set("display_errors", "On");
error_reporting(E_ALL & ~E_NOTICE);

require_once realpath(dirname(__FILE__)) . '/../../common.php';

$nodes = [];
foreach ($GLOBALS['nameServ_php'] as $name) {
    $nodes[$name] = [
        'type' => 'php',
        'path' => CONF_PATH . "config.{$name}.inc.php",
        'watch' => $name
    ];
}
$GLOBALS['nameServ'] = $nodes;

//init
foreach ($nodes as $k => $v) {
    dispath($v['watch']);
}

$objRedis = dwRedis::init('name_serv');
$objRedis->subscribe(['config_update_event'], function($instance, $channelName, $message) {
    $arrMsg = json_decode($message, true);
    if ($arrMsg['env'] === ENV) {
        dispath($arrMsg['key']);
    }
});

function dispath($key) {
    foreach ($GLOBALS['nameServ'] as $k => $v) {
        if ($v['watch'] === $key) {
            (new NameServiceHelper($v))->makeConfig($key);
        }
    }
}
