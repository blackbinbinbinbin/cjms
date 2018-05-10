<?php

//require_once __DIR__ ."/../../init.php";
require_once "./dwWebDB.php";
$yyuid = (string) 50014850;
$ret = dwWebDB::getYYnoByUid($yyuid);
//$ret = dwWebDB::getYYnoByUid($yyuid);
//var_dump('yyno', $ret);
var_dump($ret);
$a = dwWebDB::logo($argv[1]);
var_dump($a);



