<?php

class dwRedis extends Redis {
    public $isConnected = false;
    public $redisInfo = null;

    public function __construct($cannotCall, $key = 'default', $tryPconnect = 1) {
        if ($cannotCall != '$cannotCall_123456') {
            throw new RedisException('please call dwRedis::init', CODE_REDIS_ERROR);
        }

        $redisInfo = $GLOBALS['redisInfo'][$key];

        if ($redisInfo) {
            $this->redisInfo = $redisInfo;
            $timeout_str = isset($redisInfo['connect_timeout']) ? $redisInfo['connect_timeout'] : $redisInfo['connet_timeout'];
            $connect_timeout = isset($timeout_str) ? (int) $timeout_str : 3;
            // 最多重试三次连接redis
            $times = 2;
            for ($i = $times; $i >= 1; $i--) {
//                $timeout = $connect_timeout / $i;
                $timeout = $connect_timeout;
                if ($tryPconnect == 1 && !$redisInfo['db'] && $i == $times) {
                    // 没有db，就用pconnect，因为pconnect处理不同库有问题
                    $flag = $this->pconnect($redisInfo['host'], $redisInfo['port'], $timeout);
//                    $flag = $this->connect($redisInfo['host'], $redisInfo['port'], $timeout);
                } else {
                    // pconnect可能有bug，所以除了第一次用pconnect，后续尝试都用connect
                    $flag = $this->connect($redisInfo['host'], $redisInfo['port'], $timeout);
                }

                if ($flag && isset($redisInfo['pwd'])) {
                    if ($i == $times) {
                        // 第一次重试要try catch
                        try {
                            $flag = $this->auth($redisInfo['pwd']);
                        } catch (RedisException $ex) {
                            $msg = $ex->getMessage() . "\n" . $ex->getTraceAsString();
                            Tool::err($msg);
                            $flag = false;
                        }
                    } else {
                        // 非第一次，不需要try catch
                        $flag = $this->auth($redisInfo['pwd']);
                    }
                }

                if ($flag && isset($redisInfo['db'])) {
                    $flag = $this->select($redisInfo['db']);
                }

                if ($flag) {
                    $this->isConnected = true;
                    break;
                } else {
                    $this->isConnected = false;
                }

                // 如果不限连接时间，则不重连
                if ($timeout <= 0) {
                    break;
                }
            }

        }
    }
    
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

        if (!isset(self::$_objs[$key . '_' . $tryPconnect])) {
            self::$_objs[$key] = new dwRedis('$cannotCall_123456', $key, $tryPconnect);
        }
        
        return self::$_objs[$key];
    }

    public static function cleanInstance() {
        self::$_objs = [];
    }
}
