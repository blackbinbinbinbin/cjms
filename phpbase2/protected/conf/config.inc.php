<?php

$GLOBALS['nameServ_php'] = [
//  'globals',
//  'code',
//  'r2m',
];

$GLOBALS['r2mMode'] = 'lib';

$GLOBALS['rewrite'] = array(
    '404.html' => 'default/notFound',
    'robots.txt' => 'default/robots',
);

// 只给url()函数使用
$GLOBALS['rewrite2'] = array(

);

//$GLOBALS['redisInfo']['_local'] = array(
//    'host' => '127.0.0.1',
//    'port' => 6379,
//    'connet_timeout' => 1,
//);

$GLOBALS['defaultKey'] = 'dw_shop';
define('PHPBASE2_VERSION', "1.0.2");

//end of script
