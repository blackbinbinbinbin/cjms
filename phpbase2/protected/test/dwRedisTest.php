<?php

require_once '../common.php';
require_once ROOT_PATH . "framework/lib/vendor/autoload.php";

use Flexihash\Flexihash;

function testQuery() {
    $objRedis = dwRedisPro::init('dw_ka');
    $value0 = $objRedis->get('testQuery');
    $flag = $objRedis->set('testQuery', 'testQuery' . rand(1, 100));
    $value = $objRedis->get('testQuery');

    var_dump($value0, $flag, $value);

    $value0 = $objRedis->hGetAll('testHash');
    $flag = $objRedis->hSet('testHash', '1dd', rand(1, 100));
    $value = $objRedis->hGetAll('testHash');

    $flag2 = $objRedis->del('testQuery','testHash');
    var_dump($value0, $flag, $value, $flag2);

//
//    $objRedis2 = dwRedisPro::init('dw_ka_r2m');
//    $flag = $objRedis2->set('testQuery2', 'testQuery2');
//    $value = $objRedis2->get('testQuery2');
//    $flag2 = $objRedis2->del('testQuery2');
//
//    var_dump($flag, $value, $flag2);

//    $hash = new Flexihash();
//
//// bulk add
//    $hash->addTargets(array('cache-1', 'cache-2', 'cache-3'));
//
//// simple lookup
//    $hash->lookup('object-a'); // "cache-1"
//    $hash->lookup('object-b'); // "cache-2"
//
//// add and remove
//    $hash
//        ->addTarget('cache-4')
//        ->removeTarget('cache-1');
//
//// lookup with next-best fallback (for redundant writes)
//    $hash->lookupList('object', 2); // ["cache-2", "cache-4"]
//
//// remove cache-2, expect object to hash to cache-4
//    $hash->removeTarget('cache-2');
//    $hash->lookup('object'); // "cache-4"

}


$act = $_GET['act'];
$act || $act = 'testQuery';
$act();
