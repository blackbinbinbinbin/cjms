<?php

define('DEBUG', true);

$GLOBALS['redisInfo']['name_serv'] = array(
    'host' => '10.20.164.64',
    'port' => 6396,
    'pwd' => 'Fh7fVj8fSsKf',
    'db' => 1,
    'connect_timeout' => 0,
);

$GLOBALS['dbInfo']['Web'] = array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '10.25.68.139',
    'dbPort' => '6308',
    'dbName' => 'Web',
    'dbUser' => 'ojuser',
    'dbPass' => 'qs45MWKf',
);

$GLOBALS['dbInfo']['Report'] = array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '10.25.68.139',
    'dbPort' => '6308',
    'dbName' => 'Report',
    'dbUser' => 'ojuser',
    'dbPass' => 'qs45MWKf',
);

defined('CJMS_HOST_DW') || define('CJMS_HOST_DW', 'new.admin.duowan.com');
defined('URL_ADMIN_DUOWAN') || define('URL_ADMIN_DUOWAN', 'http://' . CJMS_HOST_DW . '/');
defined('CJMS_IP') || define('CJMS_IP', '61.160.36.226');
defined('URL_SPIDER') || define('URL_SPIDER', 'http://14.17.108.216:10000/');

define("TYPE_MODULE_CALL", "new_cjms_modulecall");
define("TYPE_SELF_CALL", "new_cjms_selfcall");
define("TYPE_CUSTOM_LOG", "new_cjms_customlog");

//end of script

