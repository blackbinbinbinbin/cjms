#!/usr/local/php/bin/php
<?php

set_time_limit(0);
require_once realpath(dirname(__FILE__)) . '/../../common.php';

$objRedis = CallLog::getLogRedis();
$keys = [
    "logstash:foshan",
    "logstash:redis",
    "logstash:dw#1",
];

const LIST_MAX_LEN = 8888;
foreach ($keys as $key) {
    $len = $objRedis->lLen($key);
    if ($len > LIST_MAX_LEN) {
        $objRedis->del($key);
        $warningMsg = "【Warning】Redis 日志队列满了:{$key}";
//        YYms::reportServerWarning($warningMsg);
    }
}




