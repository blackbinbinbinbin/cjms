<?php

/**
 * 多玩特权用户接口
 * User: ben
 * Date: 2017/8/25
 * Time: 下午4:00
 */
class dwKaUser {
    const TTL_LOGIN = 14 * 86400;

    /**
     * 获取特权的Session数据
     * @return bool|mixed
     */
    public static function getKaSession() {
        if ($_COOKIE['user_id'] && !$_COOKIE['loginTime']) {
            $expire = time() - 1;
            setcookie('user_id', null, $expire, COOKIE_PATH, COOKIE_DOMAIN_DUOWAN);
            setcookie('loginTime', null, $expire, COOKIE_PATH, COOKIE_DOMAIN_DUOWAN);

            $_COOKIE['user_id'] = null;
            $_COOKIE['loginTime'] = null;
        }

        if ($_COOKIE['user_id']) {
//            $key = 'KA:' . $user_id . '__' . $loginTime;
            $key = REDIS_PRE_KEY_SESSION . 'KA:' . $_COOKIE['user_id'] . '__' . $_COOKIE['loginTime'];
            $objRedis = dwRedis::init('dw_ka_user');
            $json = $objRedis->get($key);
            if ($json) {
                $userInfo = json_decode($json, true);
                return $userInfo;
//                $loginTime = $_COOKIE['loginTime'];
//                if ($userInfo && $loginTime == $userInfo['loginTime']) {
//                    return $userInfo;
//                }

//                // 暂时兼容老版本H5，后续要移除
//                if ($userInfo) {
//                    return $userInfo;
//                }
            }
        }

        return null;
    }

    /**
     * 检查特权用户登录态
     * @return mixed|null
     */
    public static function checkKaLogin($createIfNx = 1) {
        $url = URL_KA . "/user/checkLogin?createIfNx={$createIfNx}";

        if (!$_COOKIE) {
            return null;
        }

        $data = array();
        foreach ($_COOKIE as $key => $value) {
            $data[] = "{$key}={$value}";
        }

        $objHttp = new dwHttp();
        $header = 'COOKIE: ' . join(';', $data);
        $json = $objHttp->get2($url, 3, 2, $header);

        $result = json_decode($json, true);
        if ($result['result']) {
            return $result['data'];
        } else {
            return null;
        }
    }

}