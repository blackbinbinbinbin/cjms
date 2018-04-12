<?php

/**
 * Redis类，支持读取主从【中间方案，暂不考虑】
 * @author benzhan
 */
class test_dwRedis_ms {
    private static $_objs;
    public static $confDir = '/run/dwRedis/conf/';
    public static $logDir = '/run/dwRedis/logs/';
    public $curType = null;
    public $curCacheKey = null;

    private static $staticCmd = '/usr/local/php/bin/php ' . FRAMEWORK_PATH . '../staticRedisLog.php &';

    private $redisKeys = null;
    private $key = null;

    private $cacheKeyMap = [];
    private $_readonlyApi = [
        'connect' => 1,
        'sscan' => 1,
        'scan' => 1,
        'zscan' => 1,
        'hscan' => 1,
        'slowlog' => 1,
        'open' => 1,
        'pconnect' => 1,
        'popen' => 1,
        'getoption' => 1,
        'ping' => 1,
        'get' => 1,
        'exists' => 1,
        'getmultiple' => 1,
        'llen' => 1,
        'lsize' => 1,
        'lindex' => 1,
        'lget' => 1,
        'lrange' => 1,
        'lgetRange' => 1,
        'sisMember' => 1,
        'scontains' => 1,
        'scard' => 1,
        'srandMember' => 1,
        'sinter' => 1,
        'sunion' => 1,
        'sdiff' => 1,
        'smembers' => 1,
        'sgetmembers' => 1,
        'randomkey' => 1,
        'select' => 1,
        'keys' => 1,
        'getkeys' => 1,
        'dbsize' => 1,
        'auth' => 1,
        'info' => 1,
        'ttl' => 1,
        'pttl' => 1,
        'mget' => 1,
        'zrange' => 1,
        'zrevrange' => 1,
        'zrangebyscore' => 1,
        'zrevrangebyscore' => 1,
        'zrangebylex' => 1,
        'zrevrangebylex' => 1,
        'zcount' => 1,
        'zcard' => 1,
        'zsize' => 1,
        'zrank' => 1,
        'zrevrank' => 1,
        'zunion' => 1,
        'hget' => 1,
        'hlen' => 1,
        'hvals' => 1,
        'hgetall' => 1,
        'hexists' => 1,
        'hmGet' => 1,
        'time' => 1,
        'pfcount' => 1,
        'getmode' => 1,
    ];
    private $_postfix;
    private $_confPath;


    /**
     * @var Redis $objRedis Redis实例
     */
    public $objRedis = null;

    public function __construct($cannotCall, $key = 'default') {
        if ($cannotCall != '$cannotCall_123456') {
            throw new RedisException('please call dwRedis::init', CODE_REDIS_ERROR);
        }

        $this->key = $key;
        mkdir(self::$logDir, 0777, true);
        $formatTime =  date('YmdHi', intval(time() / 300) * 300);
        // 5分钟记录在一个文件
        $this->_postfix = '-' . $formatTime . '.log';
        $this->_confPath = self::$confDir . $key . '.ini';
    }

    /**
     * 给当前cacheKey评分
     * @param $score
     */
    private function appendScore($score) {
        $path = self::$logDir . $this->curCacheKey . $this->_postfix;
        file_put_contents($path, "$score\n", FILE_APPEND);
    }

    /**
     * 得到Redis对象
     * @return Redis
     */
    private function _getRedisObject() {
        do {
            $this->_getRedisKey($this->key);

            $redisInfo = $GLOBALS['redisInfo'][$this->curCacheKey];
            if ($redisInfo) {
                $objRedis = new Redis();
                !isset($redisInfo['connet_timeout']) && $redisInfo['connet_timeout'] = 3;

                for ($i = 3; $i >= 1; $i--) {
                    // 最多重试三次连接redis
                    $flag = $objRedis->connect($redisInfo['host'], $redisInfo['port'], $redisInfo['connet_timeout'] / $i);
                    if ($flag && isset($redisInfo['pwd'])) {
                        $flag = $objRedis->auth($redisInfo['pwd']);
                    }

                    if ($flag && isset($redisInfo['db'])) {
                        $flag = $objRedis->select($redisInfo['db']);
                    }

                    if ($flag) {
                        return $objRedis;
                    } else {
                        $this->appendScore(-5 * $i);
                        Tool::err("dwRedis2 {$i}th connect error. redis:{$redisInfo['host']}:{$redisInfo['port']}, db:{$redisInfo['db']}.");
                    }
                }
            } else {
                Tool::err("dwRedis2 {$this->curCacheKey} is not in \$GLOBALS['redisInfo'].");
            }
        } while($this->curCacheKey);

        Response::error(CODE_REDIS_ERROR, null, 'dwRedis2, no more cache key.');
        return null;
    }

    public function __call($name, $arguments) {
        do {
            if ($this->objRedis) {
                try {
                    if (!$this->_readonlyApi[$name] && strpos($this->curType, 'slave') !== false) {
                        var_dump('skip '. $this->curCacheKey);
                        // 写操作需要用主库
                        $this->objRedis = $this->_getRedisObject();
                        continue;
                    }

                    $data = call_user_func_array([$this->objRedis, $name], $arguments);
                    // 一个curCacheKey，一个请求中，只计算一次加1操作
                    if (!$this->cacheKeyMap[$this->curCacheKey]) {
                        $this->appendScore(1);
                        $this->cacheKeyMap[$this->curCacheKey] = 1;
                    }

                    return $data;
                } catch (RedisException $ex) {
                    $this->appendScore(-5);
                    Tool::err($ex->getMessage());
                }
            } else {
                $this->objRedis = $this->_getRedisObject();
            }
        } while($this->objRedis);

        return false;
    }

    /**
     * 根据权重来排序redisKey
     * @param $conf
     * @param $redisCacheKeys
     */
    private function _getRedisKeyByWeight($conf, $redisCacheKeys) {
        // 根据权重随机排序
        $totalWeight = arrayPop($conf, 'totalWeight');
        $rand = rand(0, $totalWeight);

        $redisKey = '';
        foreach ($conf as $redisKey => $weight) {
            if ($rand < $weight) {
                break;
            } else {
                $rand -= $weight;
            }
        }

        unset($conf[$redisKey]);
        arsort($conf);
        $keys = array_keys($conf);
        array_unshift($keys, $redisKey);
        $this->redisKeys = [];
        foreach ($keys as $key) {
            $this->redisKeys[$key] = $redisCacheKeys[$key];
        }
    }

    /**
     * 统计Redis链接日志
     * @param $needStatic
     * @param $updateTime
     */
    private function _staticRedisLog($needStatic, $updateTime) {
        if ($needStatic) {
            $time = time();
            // 修改updateTime
            if ($updateTime) {
                $update_str = str_replace($updateTime, $time, file_get_contents($this->_confPath));
            } else {
                // 第一次创建文件
                $update_str = "updateTime = {$time}";
            }
            file_put_contents($this->_confPath, $update_str);

            // 触发统计
            exec(self::$staticCmd);
        }
    }

    /**
     * 获取redisKey
     * @param string $key redisGroup的key或redisInfo的key
     */
    private function _getRedisKey($key) {
        if ($this->redisKeys === null) {
            // 读取配置
            $redisCacheKeys = $GLOBALS['redisGroup'][$key];
            if ($redisCacheKeys) {
                $defalut_ttl = arrayPop($redisCacheKeys, 'defalut_ttl');
                $defalut_ttl || $defalut_ttl = 3600;

                // 排序redisKey
                $conf = parse_ini_file($this->_confPath);
                $needStatic = true;
                $time = time();
                $updateTime = 0;

                if ($conf) {
                    $updateTime = arrayPop($conf, 'updateTime');
                    $span = $time - $updateTime;
                    // 大于300秒，修改updateTime，并触发统计
                    $needStatic = $span > 300;
                    $this->_getRedisKeyByWeight($conf, $redisCacheKeys);
                } else {
                    // 没有统计文件，则打乱顺序
                    $this->redisKeys = $redisCacheKeys;
                    shuffle($this->redisKeys);
                }

                $this->_staticRedisLog($needStatic, $updateTime);
            } else {
                // 只有一个master
                $this->redisKeys = ['master' => $key];
            }
        }

        $this->curType = key($this->redisKeys);
        $this->curCacheKey = array_shift($this->redisKeys);
    }

    /**
     * 得到实例
     * @author benzhan
     * @param string $key
     * @return Redis
     */
    public static function init($key = 'default') {
        if (!isset(self::$_objs[$key])) {
            self::$_objs[$key] = new dwRedis2('$cannotCall_123456', $key);
        }
        
        return self::$_objs[$key];
    }

    public static function cleanInstance() {
        self::$_objs = [];
    }
}
