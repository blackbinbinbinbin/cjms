<?php

require_once '../common.php';

function testQuery() {
    $oChapterHistory = new ChapterHistory();
    $objR2m = $oChapterHistory->objR2m;

    $where = [];
    $where['device_token'] = '98311fed-e50d-1e4e-2c73-ceb0e493c43b';
    // $where['uid'] = '0';
    $where['bookId'] = '2';
    $where['volumeId'] = '4';
    $where['chapterId'] = '132';
    $row = $objR2m->getRow($where);

    $where = [];
    $where['chapter_history_id'] = '1';
    $row = $objR2m->getRow($where);

    $where = [];
    $where['chapter_history_id'] = '2';
    $row = $objR2m->getRow($where);

    $objR2m = new R2m_Client('readerConfig', 'oujhome');

    $where = [];
    $where['uid'] = '100028';
    $row = $objR2m->getRow($where);

    $where = [];
    $where['id'] = '1';
    $row = $objR2m->getRow($where);

    $where = [];
    $where['id'] = '2';
    $row = $objR2m->getRow($where);
}


$act = $_GET['act'];
$act || $act = 'testQuery';
$act();
