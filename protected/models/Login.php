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

        if (preg_match('/^(\d){11}$/', $userId)) {
            // 手机号码
            $where["userId"] = $userId;
        } else {
            // 多玩通行证
            $where["userName"] = $userId;
        }
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
        if (IS_OUJ) {
            return self::_checkOujLogin($isApi);
        } else if (IS_DUOWAN) {
            return self::checkDuowanLogin($isApi);
        } else if (IS_HIYD) {
            return 0;
        } else {
            $isApi || self::noPermission();
            return -1;
        }
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

    // -------------------------------------- 偶家 ----------------------------------
    private static function _checkOujLogin($isApi = false) {
        if ($_COOKIE['ouid'] && $_COOKIE['otoken']) {
            $session_id = md5($_COOKIE['ouid'] . $_COOKIE['otoken']);
            session_id($session_id);
            // 保存一天   $lifeTime = 24 * 3600;   session_set_cookie_params($lifeTime);
            $lifeTime = 7 * 24 * 3600;
            session_set_cookie_params($lifeTime, COOKIE_PATH, COOKIE_DOMAIN);
            session_start();

            if ($_SESSION['username']) {
                return 1;
            } else {
                $userInfo = ThirdApi::checkTokenAndGetUser($_COOKIE['ouid'], $_COOKIE['otoken']);
                if ($userInfo) {
                    $admin = self ::checkPermissions($userInfo['mobile']);
                    if (!$admin) {
                        unset($_SESSION['username']);
                        $isApi || self::noPermission();
                        return -1;
                    } else {
                        // $_SESSION['username'] = $userInfo['mobile'];
                        $_SESSION['username'] = $admin['userName'];
                        $_SESSION['userId'] = $admin['userId'];
                        $_SESSION['showname'] = $admin['userName'];
                        setcookie('username', $admin['userName'], time() + $lifeTime, COOKIE_PATH, COOKIE_DOMAIN);
                        return 1;
                    }
                } else {
                    $isApi || self::openLogin();
                    return 0;
                }
            }
        } else {
            $isApi || self::openLogin();
            return 0;
        }

    }

    // -------------------------------------- 多玩 ----------------------------------
    private static function checkUdb() {
        $url = "http://webapi.duowan.com/api_udb2.php";

        if (!$_COOKIE["yyuid"]) {
            return false;
        }

        $data = array();
        foreach ($_COOKIE as $key => $value) {
            $data["COOKIE[{$key}]"] = $value;
        }

        $data["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"] || $_SERVER["HTTP_HOST"];
        $data["HTTP_HOST"] = $_SERVER["HTTP_HOST"];
        $data["format"] = "json";

        $objHttp = new dwHttp();
        $json = $objHttp->post($url, $data);

        return json_decode($json, true);
    }

	/**
	 * 新登录方式校验接口
	 * @author solu
	 */
	private static function checkUdb2() {
		$api = 'http://udbproxy.duowan.com/api/auth';

		$data['COOKIE[lg_uid]'] = @$_COOKIE['lg_uid'];
		$data['COOKIE[lg_openid]'] = @$_COOKIE['lg_openid'];
		$data['COOKIE[lg_type]'] = @$_COOKIE['lg_type'];
		$data['COOKIE[lg_token]'] = @$_COOKIE['lg_token'];

		$data['COOKIE[udb_l]'] = @$_COOKIE['udb_l'];
		$data['COOKIE[udb_n]'] = @$_COOKIE['udb_n'];
		$data['COOKIE[udb_oar]'] = @$_COOKIE['udb_oar'];  //有udb_oar就没有oauthCookie

		$data['COOKIE[yyuid]'] = @ $_COOKIE['yyuid'];
		$data['COOKIE[username]'] = @$_COOKIE['username'];
		$data['COOKIE[password]'] = @$_COOKIE['password'];
		$data['COOKIE[osinfo]'] = @$_COOKIE['osinfo'];
		$data['COOKIE[oauthCookie]'] = @$_COOKIE['oauthCookie'];

		//app的登录态
		$data['COOKIE[otoken]'] = @$_COOKIE['otoken'];
		$data['COOKIE[ouid]'] = @$_COOKIE['ouid'];
		$data['COOKIE[appid]'] = @$_COOKIE['appid'];

		$data['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
		$data['HTTP_HOST'] = $_SERVER['HTTP_HOST'];

		$data["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];
		$data["HTTP_HOST"] = $_SERVER["HTTP_HOST"];
		$data["format"] = "json";

		$objHttp = new dwHttp();
		$json = $objHttp->post2($api, $data);

		return json_decode($json, true);
	}

    public static function checkDuowanLogin($isApi = false) {
        // 保存一天   $lifeTime = 24 * 3600;   session_set_cookie_params($lifeTime);
        $lifeTime = 7 * 24 * 3600;
        session_set_cookie_params($lifeTime, COOKIE_PATH, COOKIE_DOMAIN);
        session_start();

        if ($_SESSION['username']) {
            return 1;
        }

        if ($_SESSION["username"]) {
            if (!self ::checkPermissions($_SESSION["username"])) {
                unset($_SESSION["username"]);
                $isApi || self::noPermission();
                return -1;
            }
        } else {
            $result = self::checkUdb2();
            if ($result && $result['username']) {
                if (!self ::checkPermissions($result['username'])) {
                    unset($_SESSION["username"]);
                    $isApi || self::noPermission();
                    return -1;
                } else {
                    // $_SESSION['username'] = $userInfo['mobile'];
                    $_SESSION['username'] = $result['username'];
                    $_SESSION['userId'] = $result['yyuid'];
                    $_SESSION['showname'] = $result['username'];
                    return 1;
                }
            } else {
                $isApi || self::openLogin();
                return 0;
            }
        }
    }

    // -------------------------------------- Hi运动 ----------------------------------
//    private static function checkHiyd() {
//        $url = "http://www.hiyd.com/user/checkLogin";
//
//        if (!$_COOKIE["username"]) {
//            return false;
//        }
//
//        $data = array();
//        foreach ($_COOKIE as $key => $value) {
//            $data["COOKIE[{$key}]"] = $value;
//        }
//
//        $data["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];
//        $data["HTTP_HOST"] = $_SERVER["HTTP_HOST"];
//        $data["format"] = "json";
//
//        $objHttp = new dwHttp();
//        $json = $objHttp->post($url, $data);
//
//        return json_decode($json, true);
//    }
//
//    public static function checkHiydLogin() {
//        $token = $_COOKIE['otoken'];
//        $uid = $_COOKIE['ouid'];
//
//        if ($uid && $token) {
//            // 基础信息缓存
//            $cacheKey = self::_getSessionKey($token);
//            $objRedis = dwRedis::init('hiyd_home');
//            $json = $objRedis->get($cacheKey);
//
//            if ($json) {
//                $baseInfo = json_decode($json, true);
//
//                // 判断是否检验过这个token
//                if ($baseInfo['id'] == $uid) {
//                    $userInfo = self::getVUserInfo($uid, $baseInfo);
//                    if (!$userInfo) {
//                        $userInfo = self::setUser($baseInfo, $token, 0);
//                    }
//                    self::$userInfo = $userInfo;
//                    return self::$userInfo;
//                }
//            }
//
//            // 没检验过的token需要调用接口验证
//            $appid = $_COOKIE['appid'] ? $_COOKIE['appid'] : APP_ID;
//            $baseInfo = ThirdApi::checkTokenAndGetUser($uid, $token, $appid);
//            if ($baseInfo) {
//                self::$userInfo = self::setUser($baseInfo, $token);
//            }
//        }
//    }
}

//end of script
