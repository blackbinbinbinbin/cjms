<?php

define('DEBUG', true);
define('ENV', ENV_NEW);

$GLOBALS['redisInfo']['name_serv'] = array(
    'host' => '',
    'port' => '',
    'pwd' => '',
    'db' => 1,
    'connet_timeout' => 0,
);

defined('CJMS_HOST_DW') || define('CJMS_HOST_DW', 'new.admin.duowan.com');
defined('CJMS_IP') || define('CJMS_IP', '61.160.36.226');

define("TYPE_MODULE_CALL", "new_phpbase2_modulecall");
define("TYPE_SELF_CALL", "new_phpbase2_selfcall");
define("TYPE_CUSTOM_LOG", "new_phpbase2_customlog");

define("CALL_LOG_KEY", "logstash:foshan");

//end of script
