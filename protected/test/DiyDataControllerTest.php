<?php

require_once 'BaseTest.php';

class DiyDataControllerTest extends BaseTest {
    protected $obj;
    
    public function __construct() {
        $this->obj = new DiyDataController();
    }
    
    public function actionTable() {
        $param = 'tableId=ee524ea0-10e2-edf9-9ad5-7222c4b567cc&where=%5B%5D&keyWord=%7B%22_page%22%3Anull%2C%22_pageSize%22%3Anull%2C%22_sortKey%22%3Anull%2C%22_sortDir%22%3Anull%7D';
        parse_str($param, $args);
        
        $this->obj->actionTable($args);
    }

}

$obj = new DiyDataControllerTest();
$obj->actionTable();
