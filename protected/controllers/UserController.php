<?php

/**
 * 用户
 * @author blackbinbin
 */
class UserController extends BaseController {
    
    public function __construct() {

        parent::__construct();

        // isset($_SESSION) || session_start();        
        @session_start();        
    }

    protected $noLoginActions = array('login', 'logout', 'verifypwd', 'saveAnotherPwd', 'checkAnotherPwd', 'verifyAnotherPwd');

    function actionLogout() {
        Login::logout();
    }

    /**
     * CJMS登录
     * @author blackbinbin
     */
    function actionLogin($args) {
        $rules = array(
            'refer' => array('string', 'nullable'=>true),
        );
        Param::checkParam($rules, $args);

        $refer = $args['refer'] ? $args['refer'] : '';
        $this->tpl->assign('refer', $refer);
        if (IS_CJMS) {
            $page = 'cjms_login';
        }

        $this->tpl->display('user/' . $page);
    }

    /**
     * 登录校验
     * @author blackbinbin
     */
    function actionVerifyPwd($args) {
        $rules = array(
            'user' => ['string',],
            'password' => ['string',],
        );
        Param::checkParam($rules, $args);

        if ($args['user'] && $args['password']) {
            $objUser = new TableHelper('cUser');
            $user = $objUser->getRow(['userId' => $args['user']]);
            if (!$user) {
                Response::error(CODE_NORMAL_ERROR, '没有此用户');
            }
            
            if ($user['password'] == md5($args['password'])) {
                $lifeTime = 7 * 24 * 3600;
                session_set_cookie_params($lifeTime, COOKIE_PATH, COOKIE_DOMAIN);
                session_start();

                $_SESSION['username'] = $user['userId'];
                $_SESSION['userId'] = $user['userId'];
                $_SESSION['showname'] = $user['userName'];
                $_SESSION['token'] = md5($user['userId'] . $user['password'] . APP_SECRET);
                setcookie('username', $admin['userName'], time() + $lifeTime, COOKIE_PATH, COOKIE_DOMAIN);
                Response::success();
            } else {
                Response::error(CODE_NORMAL_ERROR, '没有此用户');
            }
        }
    }

    /**
     * 设置二次密码
     * @author blackbinbin
     */
    function actionSetAnotherPwd() {
        $this->tpl->display('user/setAnotherPwd');
    }

    /**
     * 保存二次密码
     * @author blackbinbin
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
     * @author blackbinbin
     */
    function actionCheckAnotherPwd() {
        $this->tpl->display('user/checkAnotherPwd');
    }

    /**
     * 校验二次密码
     * @author blackbinbin
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
