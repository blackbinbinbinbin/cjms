<?php

/**
 * 缓存一份到本地Redis，如果没有本地Redis，则不要使用这个类，改为用：dwRedis
 * @author benzhan
 */
class dwRedisPro {
    /**
     * @var dwRedis
     */
    private static $objLocalRedis = null;
    const LOCAL_TTL = 3600;

    private static $_objs;

    /**
     * 得到实例
     * @author benzhan
     * @param string $key
     * @return dwRedis
     */
    public static function init($key = 'default', $tryPconnect = null) {
        if ($tryPconnect === null) {
            // 默认为1
            $tryPconnect = isset($GLOBALS['r2mTryPconnect']) ? (int) $GLOBALS['r2mTryPconnect'] : 1;
        }

        if (isset(self::$_objs[$key])) {
            return self::$_objs[$key];
        }

        // 尝试连接本地Redis
        if ($GLOBALS['redisInfo']['_local']) {
            if (self::$objLocalRedis == null) {
                try {
                    self::$objLocalRedis = dwRedis::init('_local', $tryPconnect);
                } catch (Exception $exception) {
                    // 本地缓存，可忽略
                }
            }
        }

        if ($GLOBALS['redisInfo'][$key]['support_pro'] || $GLOBALS['redisInfo']['_local']) {
            self::$_objs[$key] = new dwRedisPro('$cannotCall_123456', $key, $tryPconnect);
        } else {
            self::$_objs[$key] = new dwRedis('$cannotCall_123456', $key, $tryPconnect);
        }

        return self::$_objs[$key];
    }

    public function __construct($cannotCall, $key = 'default', $tryPconnect = 1) {
        if ($cannotCall != '$cannotCall_123456') {
            throw new RedisException('please call dwRedis::init', CODE_REDIS_ERROR);
        }

        $redisInfo = $GLOBALS['redisInfo'][$key];
        $db = (int) $redisInfo['db'];
        $this->_preKye = "{$redisInfo['host']}:{$redisInfo['port']}_{$db}:";

        $this->objRemoteRedis = dwRedis::init($key, $tryPconnect);
    }


    private $objRemoteRedis = null;
    private $_preKye = '';

    private function _getRemoteTimeKey($key) {
        return "__time_keys:{$key}";
    }

    private function _getLocalTimeKey($key) {
        return "{$this->_preKye}:__time_keys:{$key}";
    }

    private function _getLocalValueKey($key) {
        return "{$this->_preKye}:{$key}";
    }

    private function _setTime($key, $ttl = 0) {
        $remoteTimeKey = $this->_getRemoteTimeKey($key);
        if ($ttl) {
            $this->objRemoteRedis->setex($remoteTimeKey, $ttl, microtime());
        } else {
            $this->objRemoteRedis->set($remoteTimeKey, microtime());
        }

        Tool::debug("_setTime, key:{$key}, ttl:{$ttl}");
    }

    private $_writeApi = [
        'set' => 1,
        'setnx' => 1,
        'hSet' => 1,
        'hSetNx' => 1,
        'hDel' => 1,
        'hIncrBy' => 1,
        'hIncrByFloat' => 1,
        'hMset' => 1,
//        'incr' => 1,
//        'incrByFloat' => 1,
//        'incrBy' => 1,
//        'decr' => 1,
//        'decrBy' => 1,
//        'lPush' => 1,
//        'rPush' => 1,
//        'lPushx' => 1,
//        'lPop' => 1,
    ];

    private $_readApi = [
        'get' => 'set',
        'hGetAll' => 'hMset',
        'hMGet' => 'hMset',
    ];

    private $_ttlApi = [
        'expire' => 1,
        'pExpire' => 1,
        'expireAt' => 1,
        'pExpireAt' => 1,
    ];

    public function __call($name, $arguments) {
        $key = $arguments[0];
        if ($this->_writeApi[$name]) {
            $this->_setTime($key);
            return call_user_func_array([$this->objRemoteRedis, $name], $arguments);
        } else if ($this->_ttlApi[$name]) {
            $flag = call_user_func_array([$this->objRemoteRedis, $name], $arguments);
            $arguments[0] = $this->_getRemoteTimeKey($key);
            call_user_func_array([$this->objRemoteRedis, $name], $arguments);
            Tool::debug("ttl, key:{$arguments[0]}, ttl:{$arguments[1]}");
            return $flag;
        } else if ($this->_readApi[$name]) {
            return $this->_read($name, $key, $arguments);
        } else {
            return call_user_func_array([$this->objRemoteRedis, $name], $arguments);
        }
    }

    private function _read($name, $key, $arguments) {
        if (self::$objLocalRedis) {
            $remoteTimeKey = $this->_getRemoteTimeKey($key);
            $remoteTime = $this->objRemoteRedis->get($remoteTimeKey);

            $localTimeKey = $this->_getLocalTimeKey($key);
            $localTime = self::$objLocalRedis->get($localTimeKey);

            $localVauleKey = $this->_getLocalValueKey($key);
            // 如果远程和本地一致，则返回本地记录
            if ($localTime && $remoteTime && $localTime == $remoteTime) {
//                Tool::debug("read local, name: {$name}, key: {$key}. localTime == remoteTime, remoteTime:{$remoteTime}");
                // 修改为本地的key
                $arguments[0] = $localVauleKey;
//                Tool::debug("localTimeKey: {$localTimeKey}, localVauleKey: {$localVauleKey}. local:" . json_encode(self::$objLocalRedis->redisInfo, true));
                return call_user_func_array([self::$objLocalRedis, $name], $arguments);
            } else {
                $remoteValue = call_user_func_array([$this->objRemoteRedis, $name], $arguments);
                if ($remoteTime && $remoteValue) {
                    // 缓存到本地redis
                    self::$objLocalRedis->setex($localTimeKey, self::LOCAL_TTL, $remoteTime);
                    $setName = $this->_readApi[$name];
                    $arguments[0] = $localVauleKey;
                    $arguments[] = $remoteValue;
                    call_user_func_array([self::$objLocalRedis, $setName], $arguments);
                    self::$objLocalRedis->expire($localVauleKey, self::LOCAL_TTL);

//                    Tool::debug("read remote, set local. name: {$name}, key: {$key}. remoteTime:{$remoteTime}}");
                }

                return $remoteValue;
            }
        } else {
            return call_user_func_array([$this->objRemoteRedis, $name], $arguments);
        }
    }

    public function setex($key, $ttl, $value) {
        $this->_setTime($key, $ttl);
        return $this->objRemoteRedis->setex($key, $ttl, $value);
    }


    public function del($key1, $key2 = null, $key3 = null) {
        if (!is_array($key1)) {
            $keys = func_get_args();
        } else {
            $keys = $key1;
        }

        // 需要同时删除timeKey
        $timeKeys = [];
        foreach ($keys as $key) {
            $timeKeys[] = $this->_getRemoteTimeKey($key);
        }

        $keys = array_merge($keys, $timeKeys);
        Tool::debug("del, keys:" . join(',', $keys));
        return $this->objRemoteRedis->del($keys);
    }

    public function delete($key1, $key2 = null, $key3 = null) {
        return $this->del($key1, $key2, $key3);
    }

}
