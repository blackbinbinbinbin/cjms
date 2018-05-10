<?php

class Controller{

    /**
     * @var Template
     */
    protected $tpl;
    
    public function __construct($initTpl = true) {
        if ($initTpl) {
            $this->tpl = Template::init();
        }
    }
    
}