<?php

$startTime = microtime(true);

// 根路径 ROOT_PATH 定义, 5.3以后可以直接使用__DIR__
// 所有被包含文件均须使用此常量确定路径
define('ROOT_PATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('BASE_DIR', ROOT_PATH . '/../');

// 设置时区
date_default_timezone_set('PRC');

// 编码定义
define('DEFAULT_CHARSET', 'utf-8');

// 定义当前时间
define('TIME', time());
define('NOW', date('Y-m-d H:i:s', TIME));
define('TODAY', date('Y-m-d', TIME));

// 站点URL，最后不带斜杠
define('BASE_URL', $_SERVER["SCRIPT_NAME"]);
define('COOKIE_PATH', '/');

if (isset($_SERVER['HTTP_HOST'])) {
    define('COOKIE_DOMAIN', $_SERVER['HTTP_HOST']);
    define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
}

define('ENV_LOCAL', 'local');
define('ENV_DEV', 'dev');
define('ENV_FORMAL', 'form');
define('ENV_NEW', 'new');

$fileName = $_SERVER['SCRIPT_FILENAME'];
if (strpos($fileName, '/data/webapps/') === 0 ) {
    if (strpos($fileName, '/test.') !== false || strpos($fileName, '/test-') !== false || strpos($fileName, '_test/') !== false) {
        define('CONF_PATH', '/data/webapps/conf_v2/test/');
        define('FRAMEWORK_PATH', '/data/webapps/framework/phpbase2_test/');
        $env = ENV_DEV;
    } elseif (strpos($fileName, '/new.') !== false || strpos($fileName, '/new-') !== false || strpos($fileName, '_new/') !== false) {
        define('CONF_PATH', '/data/webapps/conf_v2/new/');
        define('FRAMEWORK_PATH', '/data/webapps/framework/phpbase2_new/');
        $env = ENV_NEW;
    } else {
        define('CONF_PATH', '/data/webapps/conf_v2/form/');
//        define('CONF_PATH', '/data/webapps/conf/');
        define('FRAMEWORK_PATH', '/data/webapps/framework/phpbase2/');
        $env = ENV_FORMAL;
    }
} else if (strpos($fileName, '/data_dev/') === 0 ) {
    // 内网开发环境
    define('CONF_PATH', '/data/webapps/conf_v2/test/');
    //    define('FRAMEWORK_PATH', BASE_DIR . '../../framework/phpbase2/');
    define('FRAMEWORK_PATH', '/data/webapps/framework/phpbase2_test/');
    $env = ENV_DEV;
} else {
    // 本地环境
    define('CONF_PATH', realpath(dirname(__FILE__)) . '/conf/conf_ns/');
    define('FRAMEWORK_PATH', BASE_DIR . '/../phpbase2/');
    //define('SPIDER_ROOT_PATH', BASE_DIR . '../crawl/');

    $env = ENV_DEV;
}
define('ENV', $env);
// 包含本地配置
require_once ROOT_PATH . 'conf/config.' . $env . '.inc.php';

// 公共配置文件
require_once ROOT_PATH . 'conf/config.inc.php';

// 公共函数
require_once ROOT_PATH . "extensions/function_extend.php";

// 包含名字服务的配置
foreach ($GLOBALS['nameServ_php'] as $name) {
    include_once CONF_PATH . "config.{$name}.inc.php";
}

// 默认的db和redis
$defaultKey = $GLOBALS['defaultKey'];
if ($defaultKey) {
    // 默认的db和redis
    $GLOBALS['dbInfo']['default'] = $GLOBALS['dbInfo'][$defaultKey];
    $GLOBALS['redisInfo']['default'] = $GLOBALS['redisInfo'][$defaultKey];
}
/**
 * 根据公共配置文件的 DEBUG 常量定义来选择出错提示，
 * 在程序正式发布时候，应将改常量定义为 false
 */
if (true == DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL ^ E_NOTICE);
} else {
    error_reporting(0);
}


