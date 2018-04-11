<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2017/11/28
 * Time: 下午2:49
 */

class ProxyStatic {
    public static $proxy = null;
    private static $_objRedis = null;
    const PRE_KEY = 'globals:ip_proxy_pool:';

    private function _getRedis() {
        if (!self::$_objRedis) {
            self::$_objRedis = dwRedis::init('logic');
        }

        return self::$_objRedis;
    }


    private function _getProxyDayKey($host) {
        $preKey = self::PRE_KEY . $host . '_' . date('Y-m-d');
        return $preKey;
    }

    private function _getProxyKey($host, $prev = 0) {
        $period = 300;
        $time = (int) (time() / $period);
        $time = ($time - $prev) * $period;

        $preKey = self::PRE_KEY . $host . ':';
        $key = $preKey . date('H_i', $time);

        return $key;
    }

    public function getProxyStatic($host, $proxyList, $num = 3) {
        $key1 = $this->_getProxyKey($host);
        $key2 = $this->_getProxyKey($host, 1);
        $key3 = $this->_getProxyDayKey($host);

        $objRedis = $this->_getRedis();
        $static1 = $objRedis->hGetAll($key1);
        $static2 = $objRedis->hGetAll($key2);
        $static3 = $objRedis->hGetAll($key3);

        $totalWeight = 0;
        $weightList = [];
        foreach ($proxyList as $i => $proxy) {
            $diff = max($static3[$proxy . '_f'] - $static3[$proxy . '_s'], 0);
            $s = $static1[$proxy . '_s'] * 2 + $static2[$proxy . '_s'];
            $f = $static1[$proxy . '_f'] * 2 + $static2[$proxy . '_f'];

            $weight = 0;
            if ($s < 5 && $f + $diff < 5) {
                $weight = 60;
            } else if ($s == 0 && $f < 5) {
                $total = $static3[$proxy . '_s'] + $static3[$proxy . '_f'];
                // 历史数据就超过了阀值
                $rate = $static3[$proxy . '_s'] / $static3[$proxy . '_f'];
                // 成功数大于失败数的一半，才进行尝试
                if ($total < 5 || $rate > 0.5) {
                    $weight = 60;
                }
            } else {
                $weight = $s / ($s + 2 * $f) * 100;
            }

            $totalWeight += $weight;
            $weightList[] = $weight;
        }

        if ($totalWeight <= 0) {
            return [];
        }

        $arr = [];
//        $weightList = array_unique($weightList);
        $times = min(count($weightList), $num);
        for ($t = 0; $t < $times; $t++) {
            $rand = rand(0, $totalWeight);
            foreach ($weightList as $k => $weight) {
                if ($rand <= $weight) {
                    $arr[] = $proxyList[$k];
                    break;
                } else {
                    $rand -= $weight;
                }
            }
        }

        return $arr;
    }

    public function staticProxy($host, $result, $startTime) {
        $span = microtime(true) - $startTime;

        $s = $f = 0;
        if ($result) {
            if ($span < 1) {
                $s = 2;
            } else if ($span < 3) {
                $s = 1;
            } else if ($span > 5) {
                $f = 1;
            }
        } else {
            $f = 2;
        }

        if (self::$proxy && ($s || $f)) {
            $key1 = $this->_getProxyKey($host);
            $key3 = $this->_getProxyDayKey($host);

            //连接 redis
            $objRedis = $this->_getRedis();
            $leftTtl1 = $objRedis->ttl($key1);
            $leftTtl3 = $objRedis->ttl($key3);

            if ($s) {
                $hashKey = self::$proxy . '_s';
                $objRedis->hIncrBy($key1, $hashKey, $s);
                $objRedis->hIncrBy($key3, $hashKey, $s);
            } else {
                $hashKey = self::$proxy . '_f';
                $objRedis->hIncrBy($key1, $hashKey, $f);
                $objRedis->hIncrBy($key3, $hashKey, $f);
            }

            if ($leftTtl1 < 0) {
                $objRedis->expire($key1, 20 * 60);
            }

            if ($leftTtl3 < 0) {
                $objRedis->expire($key3, 2 * 86400);
            }
        }
    }

    public function changeScore($host, $proxy, $score = 1) {
        // 算一次失败
        $key1 = $this->_getProxyKey($host);
        $key3 = $this->_getProxyDayKey($host);

        //连接 redis
        $objRedis = $this->_getRedis();
        if ($score > 0) {
            $hashKey = $proxy . '_s';
        } else {
            $hashKey = $proxy . '_f';
        }

        $score = abs($score);
        $newScore = $objRedis->hIncrBy($key1, $hashKey, $score);
        $objRedis->hIncrBy($key3, $hashKey, $score);

        $leftTtl1 = $objRedis->ttl($key1);
        if ($leftTtl1 < 0) {
            $objRedis->expire($key1, 20 * 60);
        }

        $leftTtl3 = $objRedis->ttl($key3);
        if ($leftTtl3 < 0) {
            $objRedis->expire($key3, 2 * 86400);
        }

        return $newScore;
    }
}