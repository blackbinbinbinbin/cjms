<?php

require_once 'BaseTest.php';

$objTest = new CrawlController();
$objTest->actionGenZqVideo([
    'type' => 'qq',
    'game_id' => '1',
]);
