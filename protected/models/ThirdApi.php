<?php

/**
 * 第三方Api
 * @author benzhan
 */
class ThirdApi {
    
    private static $DES_KEY = "login_oj";
    private static $DES_IV = "12345678";
    
    /**
     * 登陆第三方系统
     * @author benzhan
     * @param array $userInfo
     * @param number $thirdType
     */
    public static function loginByThird(array $userInfo, $thirdType = 1) {
        $url = URL_USER_API . 'auth/loginByThird.do';
        $data = array();
        $data['_call_id'] = CallLog::getCallId();
        $data['thirdType'] = $thirdType;
        $data['appId'] = APP_ID;
        $data['userJson'] = json_encode($userInfo);
        $data = self::getSignData($data);
    
        $dwHttp = new dwHttp();
        $ret = $dwHttp->post($url, $data);
        if ($ret) {
            $result = json_decode($ret, true);
            if ($result['result']) {
                return $result['data'];
            }
        }
    }
    
    /**
     * 登陆第三方系统
     * @author benzhan
     * @param string $uid
     * @param string $token
     */
    public static function checkTokenAndGetUser($uid, $token) {
        $url = URL_USER_API . 'auth/checkTokenAndGetUser.do';
        $data = array();
        $data['_call_id'] = CallLog::getCallId();
        $data['uid'] = $uid;
        $crypt = new DES(self::$DES_KEY, self::$DES_IV);
        $data['token'] = $crypt->decrypt($token);

//        var_dump($url);
//        var_dump(http_build_query($data));

        if ($data['token']) {
            // 需要用应用的appid
            $data['appId'] = APP_ID;
            $dwHttp = new dwHttp();
            $ret = $dwHttp->post($url, $data);
//            var_dump($ret);
//            exit;
            if ($ret) {
                $result = json_decode($ret, true);
                if ($result['result']) {
                    return $result['data'];
                }
            }
        }
    }
    

    /**
     * 加签名来进行get请求
     * @author benzhan
     * @param string $url 请求的url
     * @param array $data 请求的数据
     * @param bool $isServer 是否服务端签名
     * @return string  返回的数据
     */
    public static function getSignUrl($url, $data, $isServer = true) {
        $data = self::getSignData($data, $isServer);
        $param = http_build_query($data);
        $url = "{$url}?{$param}";
    
        return $url;
    }
    
    /**
     * 签名数据
     * @author benzhan
     * @param array $data
     * @param string $isServer
     */
    public static function getSignData(array $data, $isServer = true) {
        $data['_call_id'] || $data['_call_id'] = CallLog::getCallId();
        $data['sign'] = self::getSign($data, $isServer);
    
        return $data;
    }
    
    public static function getSign(array $data, $isServer = true) {
        ksort($data);
        $param = http_build_query($data);
        $param = str_replace('%2A', '*', $param);
        if ($isServer) {
            $appKey = APP_SECRET;
            $str = "key={$appKey}{$param}";
        } else {
            $str = "{$data['app_id']}{$param}";
        }
        
        return md5($str);
    }
    
}


