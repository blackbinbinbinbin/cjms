<?php

require_once 'BaseTest.php';

class CrawlControllerTest extends BaseTest {
    protected $obj;
    
    public function __construct() {
        $this->obj = new CrawlController();
    }
    
    public function actionPageCrawlView() {
        //$param = 'json=%5B%7B%22parentNodeId%22%3A0%2C%22nodeId%22%3A1%2C%22sortPos%22%3A1%7D%2C%7B%22parentNodeId%22%3A1%2C%22nodeId%22%3A5%2C%22sortPos%22%3A1%7D%2C%7B%22parentNodeId%22%3A1%2C%22nodeId%22%3A9%2C%22sortPos%22%3A2%7D%2C%7B%22parentNodeId%22%3A1%2C%22nodeId%22%3A13%2C%22sortPos%22%3A3%7D%2C%7B%22parentNodeId%22%3A1%2C%22nodeId%22%3A14%2C%22sortPos%22%3A4%7D%2C%7B%22parentNodeId%22%3A14%2C%22nodeId%22%3A15%2C%22sortPos%22%3A1%7D%2C%7B%22parentNodeId%22%3A14%2C%22nodeId%22%3A17%2C%22sortPos%22%3A2%7D%2C%7B%22parentNodeId%22%3A17%2C%22nodeId%22%3A16%2C%22sortPos%22%3A1%7D%2C%7B%22parentNodeId%22%3A14%2C%22nodeId%22%3A12%2C%22sortPos%22%3A3%7D%2C%7B%22parentNodeId%22%3A14%2C%22nodeId%22%3A18%2C%22sortPos%22%3A4%7D%2C%7B%22parentNodeId%22%3A0%2C%22nodeId%22%3A3%2C%22sortPos%22%3A2%7D%2C%7B%22parentNodeId%22%3A3%2C%22nodeId%22%3A2%2C%22sortPos%22%3A1%7D%2C%7B%22parentNodeId%22%3A3%2C%22nodeId%22%3A4%2C%22sortPos%22%3A2%7D%5D';
        $param = 'rule_id=steam:game_detail';
        parse_str($param, $args);
        
        $this->obj->actionPageCrawlView($args);
    }

}

$objMenuControllerTest = new CrawlControllerTest();
$objMenuControllerTest->actionPageCrawlView();
