<?php

//$flag = ini_set('default_socket_timeout', PHP_INT_MAX);  //不超时
ini_set('default_socket_timeout', 100000000);  //不超时
set_time_limit(0);
require_once realpath(dirname(__FILE__)) . '/../../common.php';
require_once realpath(dirname(__FILE__)) . '/../common_script.php';

$env = 1;
if (ENV == ENV_NEW) {
    $env = 2;
} elseif (ENV == ENV_FORMAL) {
    $env = 4;
} else {
    $env = 1;
}

$ip = getip();
$objServer = new TableHelper('ns_server', 'Web');
$where = [
    'env' => $env,
    '_field' => 'server_ip'
];
$ips = $objServer->getCol($where);

$objNs = new NameServiceController();
foreach ($ips as $ip) {
    $objNs->actionEcho(compact('ip', 'env'));
}

