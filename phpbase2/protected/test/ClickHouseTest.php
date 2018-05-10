<?php

require_once '../common.php';

function testQuery() {
    $db = DB::init($GLOBALS['dbInfo'], 'dw_ups');
//    $datas = $db->getRow('SHOW DATABASES');
    $datas2 = $db->getCol("SHOW TABLES");
    var_dump($datas2);

    $datas = $db->getAll('SHOW DATABASES');
    var_dump($datas);



//    $where = [];
//    $where['chapter_history_id'] = '2';
//    $args = [];
//    $args['url'] = '666';
//    $rows = $objR2m->updateObject($args, $where);
//
//    $args = [];
//    $args['chapter_history_id'] = '7';
//    $args = $objR2m->getRow($args);
//
//    $args['url'] = '66';
//    $rows = $objR2m->replaceObject($args);


//    $where = [];
//    $where['chapter_history_id'] = '8';
//    $objR2m->delObject($where);


}


$act = $_GET['act'];
$act || $act = 'testQuery';
$act();
