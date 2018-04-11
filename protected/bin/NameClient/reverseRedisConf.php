<?php

/**
 * 根据文件重建redis名字服务
 */

require_once realpath(dirname(__FILE__)) . '/../../common.php';

/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2017/6/23
 * Time: 上午10:48
 */

$fileName = 'code';
$keys = ['code_map'];
$datas = [];
$times = [];

function define_2() {
    return false;
}

function define_1($name, $value) {
    global $datas, $times, $fileName;
    $datas[$fileName . ':' . $name] = $value;
    $times[$fileName . ':' . $name] = NOW;
}

require_once ROOT_PATH . 'conf/conf_ns/config.' . $fileName . '_bak.inc.php';


$objRedis = dwRedis::init('name_serv');
$objRedis->mset($datas);
$objRedis->hMset('_keys:' . $fileName, $times);

//$keys = ['dbInfo', 'redisGroup', 'redisInfo', 'thriftInfo', 'wxInfo'];
//$times = [];
foreach ($keys as $key) {
    foreach ($GLOBALS[$key] as $name => $conf) {
        $elem = current($conf);
        if (is_array($elem)) {
            foreach ($conf as $k => $v) {
                $cacheKey = "{$fileName}:{$key}:{$name}:{$k}";
                if (!$objRedis->exists($cacheKey)) {
                    $objRedis->hMset($cacheKey, $v);
                    $times[$cacheKey] = NOW;
                } else {
                    var_dump("{$cacheKey} has exist.");
                }
            }
        } else if ($elem) {
            $cacheKey = "{$fileName}:{$key}:{$name}";
            if (!$objRedis->exists($cacheKey)) {
                $objRedis->hMset($cacheKey, $conf);
                $times[$cacheKey] = NOW;
            } else {
                var_dump("{$cacheKey} has exist.");
            }
        } else {
            $cacheKey = "{$fileName}:{$key}";
            if (!$objRedis->exists($cacheKey)) {
                $objRedis->hMset($cacheKey, $GLOBALS[$key]);
                $times[$cacheKey] = NOW;
            } else {
                var_dump("{$cacheKey} has exist.");
            }
            break;
        }
    }
}

$objRedis->hMset('_keys:' . $fileName, $times);





