<?php

require_once '../common.php';

class BaseTest {
    protected $obj;
    
    public function __construct() {
        header("Cache-control: private");
        header("Content-type: text/html; charset=" . DEFAULT_CHARSET);
    }

    public function __destruct() {

    }

}

