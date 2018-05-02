<?php

//常量
defined('BS2_ACCESS_KEY') || define('BS2_ACCESS_KEY', 'ak_uiz');
defined('BS2_ACCESS_SECRET') || define('BS2_ACCESS_SECRET', 'fd855479b53b6632204c8d0f0b8ed47428265bda');
defined('BS2_AUDIO_BUCKET') || define('BS2_AUDIO_BUCKET', 'ojiastoreaudio');
defined('BS2_DEL_HOST') || define('BS2_DEL_HOST', 'bs2.yy.com');
defined('BS2_DL_HOST') || define('BS2_DL_HOST', 'bs2dl.huanjuyun.com');
defined('BS2_FILE_BUCKET') || define('BS2_FILE_BUCKET', 'ojiastoreimage');
defined('BS2_HOST') || define('BS2_HOST', 'bs2ul.yy.com');
defined('BS2_LARGE_FILE_BUCKET') || define('BS2_LARGE_FILE_BUCKET', 'ojiaauthorvideos');
defined('BS2_SNS_BUCKET') || define('BS2_SNS_BUCKET', 'ojiasnsimage');
defined('BS2_VIDEO_BUCKET') || define('BS2_VIDEO_BUCKET', 'ojiastorevideos');

//数组
if (!isset($GLOBALS['dbInfo']['crawl'])) { $GLOBALS['dbInfo']['crawl'] = []; }
$GLOBALS['dbInfo']['crawl'] += array (
  'dbHost' => '127.0.0.1',
  'dbName' => 'crawl',
  'dbPass' => 'root',
  'dbPort' => '3306',
  'dbType' => 'mysqli',
  'dbUser' => 'root',
  'enable' => 'true',
);
if (!isset($GLOBALS['dbInfo']['ms'])) { $GLOBALS['dbInfo']['ms'] = []; }
$GLOBALS['dbInfo']['ms'] += array (
  'dbHost' => '127.0.0.1',
  'dbName' => 'ms',
  'dbPass' => 'root',
  'dbPort' => '3306',
  'dbType' => 'mysqli',
  'dbUser' => 'root',
  'enable' => '1',
);
if (!isset($GLOBALS['dbInfo']['Report'])) { $GLOBALS['dbInfo']['Report'] = []; }
$GLOBALS['dbInfo']['Report'] += array (
  'dbHost' => '127.0.0.1',
  'dbName' => 'Report',
  'dbPass' => 'root',
  'dbPort' => '3306',
  'dbType' => 'mysqli',
  'dbUser' => 'root',
  'enable' => 'true',
);
if (!isset($GLOBALS['dbInfo']['Web'])) { $GLOBALS['dbInfo']['Web'] = []; }
$GLOBALS['dbInfo']['Web'] += array (
  'dbHost' => '127.0.0.1',
  'dbName' => 'Web',
  'dbPass' => 'root',
  'dbPort' => '3306',
  'dbType' => 'mysqli',
  'dbUser' => 'root',
  'enable' => 'true',
);
