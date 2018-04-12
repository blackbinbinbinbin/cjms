<?php

require_once '../common.php';

function testQuery() {
    $objTplGroup = new Redis2Mysql('tpl_group', 'shop_base', 'shop_base');
    $groupDatas = $objTplGroup->getAll();
    var_dump($groupDatas);

    $objTplGroup->addObject(['tpl_goods_id' => 2, 'tpl_list_id' => 2]);

    $groupDatas = $objTplGroup->getAll();
    var_dump($groupDatas);exit;

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
