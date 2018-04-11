<?php

/**
 * 基础Controller
 * @author benzhan
 */
class BaseController extends Controller {
    
    protected $noLoginActions = array(); // 不需要登录action

    public function __construct() {
        parent::__construct();

        // 如果是需要登录态的action
        if (defined('ACTION_NAME') && !in_array(ACTION_NAME, $this->noLoginActions)) {
            //Login::checkLogin();
        }
    }



    
}
