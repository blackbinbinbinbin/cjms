<?php

/**
* 用户信息类
* @author benzhan
* @package common
*/
class Login {
    /**
     * 检查管理员权限
     */
    private static function checkPermissions($userId) {
        $objUser = new TableHelper("cUser");
        $where = array('enable' => 1);

        // if (preg_match('/^(\d){11}$/', $userId)) {
        //     // 手机号码
        //     $where["userId"] = $userId;
        // } else {
        //     // 多玩通行证
        //     $where["userName"] = $userId;
        // }
        $where["userId"] = $userId;
    	$row = $objUser->getRow($where);

    	return $row;
    }

    public static function openLogin() {
        $self = 'http://' . $_SERVER['HTTP_HOST'];
        $selfUrl = urlencode($self);
        Response::exitMsg("<script type='text/javascript'>top.location = '" . SITE_URL . "user/login?refer={$selfUrl}';</script>");
    }

    public static function noPermission() {
        $self = 'http://' . $_SERVER['HTTP_HOST'];
        $selfUrl = urlencode($self);
        Response::exitMsg("<meta charset='utf-8'>对不起，您没有权限！请联系Ben开通！<a href='" . SITE_URL ."user/login?refer={$selfUrl}'>换个帐号登录</a>");
    }
        
    public static function checkLogin($isApi = false) {
        if (IS_CJMS) {
            return self::_checkCjmsLogin($isApi);
        } else {
            return self::noPermission();
        }
    }

    // -------------------------------------- CJMS ----------------------------------
    private static function _checkCjmsLogin() {
        session_start();
        $token = $_SESSION['token'];
        $userId = $_SESSION['userId'];
        $objUser = new TableHelper('cUser');
        $user = $objUser->getRow(['userId' => $userId]);
        if ($token == md5($user['userId'] . $user['password'] . APP_SECRET)) {
            return true;
        }
        $isApi || self::openLogin();
        return 0;
    }

    public static function logout() {
        if (IS_OUJ) {
            setcookie("ouid", null, time()-3600, COOKIE_PATH, COOKIE_DOMAIN2);
            setcookie("otoken", null, time()-3600, COOKIE_PATH, COOKIE_DOMAIN2);
            setcookie("username", null, time()-3600, COOKIE_PATH, COOKIE_DOMAIN2);
        } else if (IS_DUOWAN) {
            setcookie("yyuid", null, time()-3600, COOKIE_PATH, COOKIE_DOMAIN2);
            setcookie("osinfo", null, time()-3600, COOKIE_PATH, COOKIE_DOMAIN2);
            setcookie("username", null, time()-3600, COOKIE_PATH, COOKIE_DOMAIN2);
            setcookie("password", null, time()-3600, COOKIE_PATH, COOKIE_DOMAIN2);
            setcookie("udb_oar", null, time()-3600, COOKIE_PATH, COOKIE_DOMAIN2);
        }

        setcookie("PHPSESSID", null, time()-3600, COOKIE_PATH, COOKIE_DOMAIN);
        session_destroy();
        header("location: " . SITE_URL);
    }
}

//end of script
