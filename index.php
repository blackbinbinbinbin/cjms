<?php

// ini_set("memory_limit", "3072M");
require_once './protected/common.php';

header("Cache-control: private");
header("Content-type: text/html; charset=" . DEFAULT_CHARSET);

$url = $_SERVER["REQUEST_URI"];
// $url = '/diyData/table?tableId=3a29ca87-7e6a-b01a-f2f8-809d0a162be5&where=%5B%5B"app_id"%2C"%3D"%2C"4"%5D%2C%5B"page"%2C"%3D"%2C"书首页"%5D%5D&keyWord=%7B"_sortKey"%3A"content"%2C"_sortDir"%3A"DESC"%2C"_groupby"%3A"bookId"%2C"_count"%3A"page_auto_id"%7D';
// parse_str(explode('?', $url)[1], $_GET);
$helper = new RouterHelper($url);
$className = $helper->getClassName();
$funcName = $helper->getFunName();

define('CONTROLLER_NAME', $className);
define('ACTION_NAME', $funcName);

$actionName = "action{$funcName}";
// 如果带有doc参数则会转化为文档模式
$helper->genDoc($className, $actionName);

if (class_exists($className)) {
    $oClass = new $className();
} else {
    $msg = "class {$className} is not exist.";
    $helper->error($actionName, $msg);
}

if (method_exists($oClass, $actionName)) {
    $args = $_REQUEST;
    
    $data = $oClass->$actionName($args);
    Response::success($data);
} else {
    $msg = "method {$actionName} is not exist.";
    $helper->error($actionName, $msg);
}
