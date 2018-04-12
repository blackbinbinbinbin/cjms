<?php

// ini_set("memory_limit", "3072M");
require_once './protected/common.php';

header("Cache-control: private");
header("Content-type: text/html; charset=" . DEFAULT_CHARSET);

$url = $_SERVER["REQUEST_URI"];
$helper = new RouterHelper($url);

$className = $helper->getClassName();
$funcName = $helper->getFunName();

define('CONTROLLER_NAME', $className);
define('ACTION_NAME', $funcName);

$is_ajax = $_GET['_from'] == 'ajax';
$actionName = "action{$funcName}";
// 如果带有doc参数则会转化为文档模式
$helper->genDoc($className, $actionName);
if (!class_exists($className)) {
    $msg = "class {$className} is not exist.";
    if ($is_ajax) {
        $helper->error($actionName, $msg);
    } else {
        $objBaseController = new BaseController(true);
        $objBaseController->go404($msg, CODE_NOT_EXIST_INTERFACE);
    }
}

if (method_exists($className, $actionName)) {
    $args = $_REQUEST;
    $oClass = new $className();
    $data = $oClass->$actionName($args);
    Response::success($data);
} else {
    $msg = "className:{$className}, method:{$actionName} is not exist.";
    if ($is_ajax) {
    	$helper->error($actionName, $msg);
    } else {
        $objBaseController = new BaseController(true);
        $objBaseController->go404($msg, CODE_NOT_EXIST_INTERFACE);
    }    
}






