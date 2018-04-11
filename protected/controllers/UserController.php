<?php

/**
 * 用户
 * @author benzhan
 */
class UserController extends BaseController {
    
    public function __construct() {

        parent::__construct();

        // isset($_SESSION) || session_start();        
        @session_start();        
    }

    protected $noLoginActions = array('login', 'logout', 'saveAnotherPwd', 'checkAnotherPwd', 'verifyAnotherPwd');

    function actionLogout() {
        Login::logout();
    }

    /**
     * OUJ登录
     * @author hawklim
     */
    function actionLogin($args) {
        $rules = array(
            'refer' => array('string', 'nullable'=>true),
        );
        Param::checkParam($rules, $args);
        $refer = $args['refer'] ? $args['refer'] : '';
        $this->tpl->assign('refer', $refer);
        if (IS_OUJ) {
            $page = 'ouj_login';
        } else if (IS_DUOWAN) {
            $page = 'duowan_login';
        } else if (IS_OUJ) {
            $page = 'hiyd_login';
        }
        $this->tpl->display('user/' . $page);
    }

    /**
     * 设置二次密码
     * @author hawklim
     */
    function actionSetAnotherPwd() {
        $this->tpl->display('user/setAnotherPwd');
    }

    /**
     * 保存二次密码
     * @author hawklim
     */
    function actionSaveAnotherPwd($args) {
        $rules = array(
            'anotherPwd' => 'string',
        );
        Param::checkParam($rules, $args);
        $anotherPwd = md5($args['anotherPwd']);

        $objUser = new TableHelper('cUser');
        $where = array("userId" => $_SESSION['userId'], 'enable' => 1);        
        $flag = $objUser->updateObject(compact('anotherPwd'), $where);
        if ($flag) {
            Response::success($flag, '设置成功');
        } else {
            Response::error(CODE_NORMAL_ERROR, '保存失败');
        }
    }

    /**
     * 验证二次密码
     * @author hawklim
     */
    function actionCheckAnotherPwd() {
        $this->tpl->display('user/checkAnotherPwd');
    }

    /**
     * 校验二次密码
     * @author hawklim
     */
    function actionVerifyAnotherPwd($args) {
        $rules = array(
            'anotherPwd' => 'string',
        );
        Param::checkParam($rules, $args);
        $anotherPwd = md5($args['anotherPwd']);

        $objUser = new TableHelper('cUser');
        $where = array("userId" => $_SESSION['userId'], 'anotherPwd' => $anotherPwd, 'enable' => 1);        
        $flag = $objUser->getRow($where);
        if ($flag) {
            $_SESSION['verifyAnotherPwd'] = 1;
            Response::success($flag, '校验成功');
        } else {
            Response::error(CODE_NORMAL_ERROR, '校验失败');
        }
    }
}
