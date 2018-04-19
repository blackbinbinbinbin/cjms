<?php

define('DEBUG', true);
// 本地开发的时候不需要redis通知名字服务器发布配置，直接生成文件了，其他环境的需要配置
// $GLOBALS['redisInfo']['name_serv'] = array(
//   'host' => '127.0.0.1',
//   'port' => 6405,
//   'pwd' => 'ojia123',
//   'db' => 1,
//   'connect_timeout' => 0,
// );

//默认的两张表
$GLOBALS['dbInfo']['Web'] = array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '127.0.0.1',
    'dbPort' => '3306',
    'dbName' => 'Web',
    'dbUser' => 'root',
    'dbPass' => 'root',
);

$GLOBALS['dbInfo']['Report'] = array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '127.0.0.1',
    'dbPort' => '3306',
    'dbName' => 'Report',
    'dbUser' => 'root',
    'dbPass' => 'root',
);

defined('CJMS_HOST_DW') || define('CJMS_HOST_DW', 'test.admin.duowan.com');
defined('URL_ADMIN_DUOWAN') || define('URL_ADMIN_DUOWAN', 'http://' . CJMS_HOST_DW . '/');
defined('CJMS_IP') || define('CJMS_IP', '127.0.0.1');
defined('URL_SPIDER') || define('URL_SPIDER', 'http://14.17.108.216:9998/');

define("TYPE_SELF_CALL", "test_cjms_selfcall");
define("TYPE_MODULE_CALL", "test_cjms_modulecall");
define("TYPE_CUSTOM_LOG", "test_cjms_customlog");

//end of script
