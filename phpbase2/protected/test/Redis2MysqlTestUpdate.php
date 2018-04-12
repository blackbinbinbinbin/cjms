<?php

require_once '../common.php';

function testQuery() {
    $oChapterHistory = new ChapterHistory();
    $objR2m = $oChapterHistory->objR2m;
    $objR2m->setDebug(true);

//    $where = [];
//    $where['device_token'] = '98311fed-e50d-1e4e-2c73-ceb0e493c43b';
//    // $where['uid'] = '0';
//    $where['bookId'] = '2';
//    $where['volumeId'] = '4';
//    $where['chapterId'] = '132';
//    $row = $objR2m->getRow($where);
//    var_dump($row);

    $where = [];
    $where['chapter_history_id'] = '2';
    $row = $objR2m->getRow($where);
    var_dump($row);

    $where = [];
    $where['chapter_history_id'] = '2';
    $args = ['url' => $row['url'] . '1'];
    $result = $objR2m->updateObject($args, $where);
    var_dump($result);

    $where = [];
    $where['chapter_history_id'] = '1';
    $args = [];
    $args['device_token'] = '98311fed-e50d-1e4e-2c73-ceb0e493c43b';
    $args['uid'] = '0';
    $args['bookId'] = '2';
    $args['url'] = $row['url'] . '2';
    $row = $objR2m->updateObject($args, $where);
    var_dump($row);

    $where = [];
    $where['device_token'] = '98311fed-e50d-1e4e-2c73-ceb0e493c43b';

    $args = [];
    $args['url'] = '2333';
    $row = $objR2m->updateObjects($args, $where);
    var_dump($row);

    $where = [];
    $where['chapter_history_id'] = '1';
    $row = $objR2m->getRow($where);
    var_dump($row);

    $where = [];
    $where['chapter_history_id'] = '1';
    $args = [];
    $args['url'] = $row['url'] . '3';
    $result = $objR2m->updateObject($args, $where);
    var_dump($result);

    $msg = $objR2m->getDebugMsg();
    var_dump($msg);

    exit;


}


$act = $_GET['act'];
$act || $act = 'testQuery';
$act();
