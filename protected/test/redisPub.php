<?php
/**
 * Created by PhpStorm.
 * @author benzhan
 * Date: 16/10/27
 * Time: 下午5:56
 */

require_once 'BaseTest.php';

$objRedis = dwRedis::init('hiyd_meal');
$objRedis->publish('diy:hiyd_meal:shop', 'msg');
