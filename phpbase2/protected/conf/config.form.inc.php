<?php

define('DEBUG', false);
define('ENV', ENV_FORMAL);

$GLOBALS['redisInfo']['name_serv'] = array(
    'host' => '10.20.164.64',
    'port' => 6396,
    'pwd' => 'Fh7fVj8fSsKf',
    'db' => 2,
    'connet_timeout' => 0,
);

defined('CJMS_HOST_DW') || define('CJMS_HOST_DW', 'admin.duowan.com');
defined('CJMS_IP') || define('CJMS_IP', '61.160.36.226');

define("TYPE_MODULE_CALL", "phpbase2_modulecall");
define("TYPE_SELF_CALL", "phpbase2_selfcall");
define("TYPE_CUSTOM_LOG", "phpbase2_customlog");

define("CALL_LOG_KEY", "logstash:foshan");

//end of script
