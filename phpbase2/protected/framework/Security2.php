<?php

/**
 * 安全模块
 * @author benzhan
 */
class Security2 {

    public static $prefixKaKey = "globals:security_move_ka:";
    public static $prefixYyKey = "globals:security_move_yy:";

    private static $_redisKey = 'dw_ka_r2m';
    private $_apiName;

    /**
     * @var int 验证码次数限制
     */
    private $_captchaLimit;

    /**
     * @var int ip最大领取数限制
     */
    private $_ipLimit;

    /**
     * 安全模块
     * @param string $apiName 接口名字
     * @param int $captchaLimit 访问多少次出图片验证码
     * @param int $ipLimit 访问多少次禁止访问
     */
    public function __construct($apiName, $captchaLimit = 15, $ipLimit = 30, $redisKey = 'dw_ka_r2m') {
        $this->_captchaLimit = $captchaLimit;
        $this->_ipLimit = $ipLimit;
        $this->_apiName = $apiName;
        self::$_redisKey = $redisKey;

        // 需要登陆态
        $userid = User::getUserId();
        if (!$userid) {
            Response::error(CODE_USER_LOGIN_FAIL);
        }
    }

    public static function getYYuid() {
        $userId = User::getUserId();
        if ($userId) {
            return $_COOKIE['yyuid'];
        } else {
            return 0;
        }
    }

    private static function _getRpKey($userId, $yyuid) {
        if ($yyuid) {
            return self::$prefixYyKey . "{$yyuid}";
        } else {
            return self::$prefixKaKey . "{$userId}";
        }
    }

    public static function clearInvalidRp() {
        $rp = $_COOKIE['__rp'];
        if ($rp) {
            $userId = User::getUserId();
            $yyuid = self::getYYuid();
            if ($userId) {
                $objRedis = dwRedis::init(self::$_redisKey);
                $cacheKey = self::_getRpKey($userId, $yyuid);
                $time = $objRedis->get($cacheKey);
                if ($rp == $time) {
                    // 设置环境信息
                    $env = self::getEnv();
                    $envKey = self::getEnvKey($cacheKey);
                    $env2 = $objRedis->get($envKey);
                    if ($env2 && $env == $env2) {
                        return true;
                    }
                } else {
                    $env2 = $env = '';
                }

                // __rp值是非法的，要清除掉
                $domain = 'duowan.com';
                setcookie('__rp', null, time() - 1, '/', $domain);

                $debugMsg = "clear {$domain} cookie __rp:{$rp}. 【rp:{$rp}, redis rp: {$time}, env:{$env}, redis env:{$env2}】";
                Tool::log($debugMsg);
            }
        }


        return false;
    }

    private static function getEnvKey($cacheKey) {
        return $cacheKey . '_env';
    }

    private static function getEnv() {
        $isInWeixin = preg_match('/MicroMessenger/i', $_SERVER['HTTP_USER_AGENT']);
//        if ($isInWeixin) {
//            // 微信里面有代理ip
//            $arr = [strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]), $_SERVER['HTTP_USER_AGENT']];
//        } else {
//            $arr = [getip(), strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]), $_SERVER['HTTP_USER_AGENT']];
//        }
        $arr = [strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]), $_SERVER['HTTP_USER_AGENT']];
        return join(' + ', $arr);
    }

    /**
     * 设置rp值
     * @return array
     */
    public static function genRp() {
        $userId = User::getUserId();
        $yyuid = self::getYYuid();
        if ($userId) {
            $objRedis = dwRedis::init(self::$_redisKey);
            $cacheKey = self::_getRpKey($userId, $yyuid);

            $lastValue = $objRedis->get($cacheKey);
            $value = $lastValue ? $lastValue : time();

            // 比Cookie多10分钟，以防临界点：cookie传过来，但服务端过期
            $ttl = 3600 * 3;
            $objRedis->setex($cacheKey, $ttl + 600, $value);

            // 设置环境信息
            $envKey = self::getEnvKey($cacheKey);
            $envValue = self::getEnv();
            $objRedis->setex($envKey, $ttl + 600, $envValue);

            return compact('value', 'ttl');
        } else {
            return [];
        }
    }

    /**
     * 设置rp值
     * @return string
     */
    public static function setRp() {
        $rp = self::genRp();

        if ($rp) {
            $expire = time() + $rp['ttl'];
            $value = $rp['value'];
            $domain = 'duowan.com';
            setcookie('__rp', $value, $expire, COOKIE_PATH, $domain);
            $debugMsg = "set {$domain} cookie __rp:{$value}";
        } else {
            $debugMsg = "not login, no set cookie.";
        }

        return $debugMsg;
    }

    /**
     * 检查是否是刷子
     * @author benzhan
     */
    public static function checkRp() {
        $userId = User::getUserId();
        $yyuid = self::getYYuid();
        if ($userId) {
            if ($yyuid) {
                // 加入到刷子表
                $objShuaziLog = new TableHelper('shuazi_yy_log', 'dw_ka');
                $where = ['yyuid' => $yyuid];
            } else {
                // 加入到刷子表
                $objShuaziLog = new TableHelper('shuazi_ka_log', 'dw_ka');
                $where = ['user_id' => $userId];
            }

            $rp = $_COOKIE['__rp'];
            if ($rp) {
                $objRedis = dwRedis::init(self::$_redisKey);
                $cacheKey = self::_getRpKey($userId, $yyuid);
                $time = $objRedis->get($cacheKey);

                $env = self::getEnv();
                $envKey = self::getEnvKey($cacheKey);
                $env2 = $objRedis->get($envKey);

                if ($rp == $time && $env == $env2) {
                    $row = $objShuaziLog->getRow($where);
                    if ($row) {
                        // 验证成功，需要从刷子表删除
                        if ($yyuid) {
                            Tool::log('checkRp, delete shuazi_yy_log yyuid:' . $yyuid);
                        } else {
                            Tool::log('checkRp, delete shuazi_ka_log user_id:' . $userId);
                        }

                        $objShuaziLog->delObject($where);
                    }

                    return true;
                } else {
                    Tool::log("checkRp false, del rp. rp:{$rp}, redis rp: {$time}, env:{$env}, redis env:{$env2}");

                    // __rp值是非法的，要清除掉
                    $domain = 'duowan.com';
                    setcookie('__rp', null, time() - 1, '/', $domain);
                }
            }

            // 添加到刷子流水日志
//            self::_addShuaziLog($where);
        }

        return false;
    }

    private static function _addShuaziLog($where) {
        // 添加到刷子流水日志
        $log = [];
        $log += $where;
        $log['url'] = SITE_URL . ltrim($_SERVER['REQUEST_URI'], '/');
        $log['remark'] = $_SERVER['HTTP_REFERER'];
        $log['client_ip'] = getip();
        $log['ua'] = $_SERVER['HTTP_USER_AGENT'];
        $log['create_time'] = NOW;

        if ($where['yyuid']) {
            $objShuaziLog = new TableHelper('shuazi_yy_log', 'dw_ka');
            $objShuaziLog->addObject($log);
        } else if ($where['user_id']) {
            $objShuaziLog = new TableHelper('shuazi_ka_log', 'dw_ka');
            $objShuaziLog->addObject($log);
        }
    }

    private function getIpKey() {
        $ip = getip();
        return "globals:ip_static_{$this->_apiName}:{$ip}";
    }

    public static function getIpDebugMsg() {
        $ipMsg = "
                   HTTP_X_FORWARDED_FOR: {$_SERVER['HTTP_X_FORWARDED_FOR']} ,
                   HTTP_CLIENT_IP: {$_SERVER['HTTP_CLIENT_IP']} ,
                   HTTP_CDN_SRC_IP: {$_SERVER['HTTP_CDN_SRC_IP']} ,
                   HTTP_X_REAL_IP: {$_SERVER['HTTP_X_REAL_IP']} ,
                   HTTP_REMOTE_ADDR: {$_SERVER['HTTP_REMOTE_ADDR']} ,
                   REMOTE_ADDR: {$_SERVER['REMOTE_ADDR']}";
        return $ipMsg;
    }

    public static function getErrorMsg() {
        $errMsg = <<<Msg
您所在IP到达领号上限，本礼包暂时无法领取。</br>
请换个网络或使用手机流量，或者明天再来领取！</br>
仍有问题，请联系 邮箱：yunan@ouj.com 
Msg;
        return $errMsg;
    }

    public static function getErrorMsg2() {
        $errMsg = <<<Msg
游戏特权卡码开放合作，但请勿刷卡。</br>
如有拿卡合作需求，请邮件yunan@ouj.com沟通。
Msg;
        return $errMsg;
    }

    private function getCaptchaKey() {
        $userId = User::getUserId();
        $yyuid = self::getYYuid();
        if ($yyuid) {
            return "globals:captcha_yy_{$this->_apiName}:{$yyuid}";
        } else {
            return "globals:captcha_ka_{$this->_apiName}:{$userId}";
        }
    }

    private function _build($length = 5, $charset = 'abcdefghijklmnpqrstuvwxyz123456789') {
        $phrase = '';
        $chars = str_split($charset);

        for ($i = 0; $i < $length; $i++) {
            $phrase .= $chars[array_rand($chars)];
        }

        return $phrase;
    }

    /**
     * 检查ip是否超过限制，是否要显示验证码，或者直接禁止
     * @param null $errMsg
     * @return bool
     * @author benzhan
     */
    public function check($errMsg = null) {
        $objRedis = dwRedis::init(self::$_redisKey);
        $key = $this->getIpKey();
        $times = (int) $objRedis->get($key);

        $flag = self::checkRp();

        $reffer = $_SERVER['HTTP_REFERER'];
        // 判断是否是 刷子
        $isHttpRequest = strpos($reffer, 'WinHttpRequest') !== false;
        // 如果是刷子，默认当作加了5次访问
        if ($flag && !$isHttpRequest) {
            $increment = 1;
        } else {
            // 刷子
            $increment = 5;
//            Tool::log('shuazi:' . Security2::getIpDebugMsg());
        }
        $times += $increment;

        if ($times > $this->_ipLimit) {
            if ($errMsg == null) {
                $errMsg = self::getErrorMsg();
            }

            $ipMsg = self::getIpDebugMsg();
            Response::error(CODE_IP_LIMITED, $errMsg, "checkRp:{$flag}, isHttpRequest:{$isHttpRequest}, {$ipMsg}");
        } else if ($times >= $this->_captchaLimit) {
            $captchaKey = $this->getCaptchaKey();
            if ($_REQUEST['_phrase']) {
                $phrase = $objRedis->get($captchaKey);
                // 验证码检查成功
                $phrase2 = strtolower(trim($_REQUEST['_phrase']));
                if ($phrase == $phrase2) {
                    return true;
                }
            }

            // 设置cookie，显示图片验证码
            $phrase = $this->_build();
            $objRedis->setex($captchaKey, 86400, $phrase);
            $time = time();
            $imageUrl = SITE_URL . "static/captcha?apiName={$this->_apiName}&ts={$time}";
            Response::error(CODE_NEED_CAPTCHA, null, "phrase:{$phrase}, checkRp:{$flag}", ['data' => $imageUrl]);
        }

        return false;
    }

    /**
     * 操作结束，ip的计数加1
     * @author benzhan
     */
    public function done() {
        $objRedis = dwRedis::init(self::$_redisKey);
        $key = $this->getIpKey();
        if ($objRedis->exists($key)) {
            $objRedis->incr($key);
        } else {
            $objRedis->setex($key, 86400, 1);
        }
    }

    public function getCaptchaImg($fresh) {
        $objRedis = dwRedis::init(self::$_redisKey);
        $captchaKey = $this->getCaptchaKey();
        if ($fresh) {
            $phrase = $this->_build();
            $objRedis->setex($captchaKey, 86400, $phrase);
        } else {
            $phrase = $objRedis->get($captchaKey);
        }

        if ($phrase) {
            header('Content-Type:image/png');
            $objVerify = new dwVerify(182, 62, strlen($phrase), 30);
            $objVerify->show($phrase);
        } else {
            Response::error(CODE_NORMAL_ERROR, '没产生验证码');
        }

        return $phrase;
    }

    public static function checkIp() {
//        return true;

        $ip = getip();
//        $ipFixedBlackList = [
//            '61.160.36.132' => 1
//        ];
//
//        // 判断是否是代码级别黑名单
//        if ($ipFixedBlackList[$ip]) {
//            $errMsg = self::getErrorMsg();
//            Response::exitMsg($errMsg, CODE_UNAUTH_ERROR, "代码级别黑名单, ip:{$ip} is in fixedBlackList.");
//        }

        $objBlackListIp = new TableHelper('blacklist_ip', 'dw_ka');
        $row = $objBlackListIp->getRow(['app_name' => 'ka', 'ip' => $ip, '_field' => 'ip, expire_time']);

        if ($row && $row['expire_time'] >= TODAY) {
            $ipMsg = self::getIpDebugMsg();
            $errMsg = self::getErrorMsg2();
            Response::exitMsg($errMsg, CODE_UNAUTH_ERROR, "ip:{$ip} is in blanklist. {$ipMsg}");
        }
    }

    public static function checkYYUser() {
        $userId = $_COOKIE['user_id'];
        $yyuid = $_COOKIE['yyuid'] ?: $_COOKIE['lg_uid'];
        if ($userId || $yyuid) {
            if ($yyuid) {
                $objBlackListUser = new TableHelper('blacklist_yy_user', 'dw_ka');
                $userField = 'yyuid';
                $row = $objBlackListUser->getRow(['app_name' => 'ka', $userField => $yyuid], ['_field' => "{$userField},expire_time"]);
            } else {
                $objBlackListUser = new TableHelper('blacklist_ka_user', 'dw_ka');
                $userField = 'user_id';
                $row = $objBlackListUser->getRow([$userField => $userId], ['_field' => "{$userField},expire_time"]);
            }

            if ($row['expire_time'] >= TODAY) {
                $ipMsg = self::getIpDebugMsg();
                $errMsg = self::getErrorMsg();
                Response::exitMsg($errMsg, CODE_UNAUTH_ERROR, "userId:{$userId}, yyuid:{$yyuid} is in blanklist. {$ipMsg}");
            }
        }
    }

    public function getIPTimes() {
	    $objRedis = dwRedis::init(self::$_redisKey);
	    $key = $this->getIpKey();
	    $times = (int) $objRedis->get($key);

	    return $times ?: 0;
    }

    private function getLockKey($key) {
        return "globals:lock_key:{$this->_apiName}:{$key}";
    }

    public function lock($key, $ttl = 5) {
        $objRedis = dwRedis::init(self::$_redisKey);
        $lockKey = $this->getLockKey($key);
        $num = $objRedis->incr($lockKey);
        if ($num > 1) {
            Response::error(CODE_NORMAL_ERROR, '操作太快，请稍后再试');
        }

        $objRedis->expire($lockKey, $ttl);
    }

    public function unlock($key) {
        $objRedis = dwRedis::init(self::$_redisKey);
        $lockKey = $this->getLockKey($key);
        $objRedis->del($lockKey);
    }
}
