<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/9
 * Time: 16:46
 */
class SpiderProxy {

    private $spiderId = "f387f8f00198476ba5d00e45d657feaa";
    private $secretId = "1b0a79c292c94c408bdeecabc6e201bc";
    private $orderno = "YZ201710253613I6TZz0";
    private static $_objRedis = null;

    private function _getRedis() {
        if (!self::$_objRedis) {
            //连接 redis
            $redisInfo = $GLOBALS['redisInfo']['logic'];
//            self::$_objRedis = new dwRedis($redisInfo['host'], $redisInfo['port'], $redisInfo['pwd']);
            self::$_objRedis = dwRedis::init('logic');
        }
        return self::$_objRedis;
    }

    public function _setWhiteList($ip){
        $dwHttp = new dwHttp();
        $url = "http://www.xdaili.cn/ipagent/whilteList/addIp?spiderId={$this->spiderId}&ip={$ip}";
        $result = $dwHttp->get($url);
        return $result;
    }

    private function _proxyKey() {
        $key = "globals:ip_proxy_pool:total";
        return $key;
    }

    public function _getProxyPool() {
        $url = "www.xdaili.cn";
        $key = 'globals:url_map:' . $url;

        $data = [];
        //连接 redis
        $objRedis = $this->_getRedis();
        $json = $objRedis->get($key);
        if ($json) {
            $timeOut = $objRedis->ttl($key);
            $proxyList = json_decode($json, true);
            $data = [
                'proxyList' => $proxyList,
                'ttl' => $timeOut,
            ];
            return $data;
        }
        $proxyIps = $this->_getXProxyId();
        $proxyList = [];
        if($proxyIps){
            foreach ($proxyIps as $item) {
                $proxyList[] = "{$item['ip']}:{$item['port']}";
            }
            $proxyIpsFree = $this->_getXProxyId(true);
            if($proxyIpsFree){
                foreach ($proxyIpsFree as $item) {
                    $responseTime = (int)$item['responsetime'];
                    $proxyList[] = "{$item['ip']}:{$item['port']}";
                }
            }
        }
        $timeOut = 5 * 60;
        $objRedis->set($key, json_encode($proxyList), $timeOut);
        $data = [
            'proxyList' => $proxyList,
            'ttl' => $timeOut,
        ];
        return $data;

    }

    public function getBestProxy($domain, $end_num = -1) {
        $proxyKey = $this->_proxyKey();
        $objRedis = $this->_getRedis();
        $start_num = 0;
        $proxyList = $objRedis->zRevrange($proxyKey, $start_num, $end_num, true);
        $proxyData = [];
        foreach ($proxyList as $proxy => $score) {
            if ($score < 10) {
                continue;
            }
            $proxyData[] = $proxy;
        }

        if (!$proxyData) {
            foreach ($proxyList as $proxy => $score) {
                $proxyData[] = $proxy;
            }
        }

        // 动态计算，筛选最佳代理
        $objProxyStatic = new ProxyStatic();
        $bestProxy = $objProxyStatic->getProxyStatic($domain, $proxyData);

        return $bestProxy;
    }

    private function _getProxyKey($domain, $proxy){
        $key = 'globals:url_map:' . $domain . ':' . $proxy;
        return $key;
    }

    public function setDomainProxyError($domain, $proxy, $type) {
        $this->_setDomainProxyError($domain, $proxy, $type);
    }

    private function _setDomainProxyError($domain, $proxy, $type){
        $key = $this->_getProxyKey($domain, $proxy);
        $objRedis = $this->_getRedis();
        $timeOut = 5 * 60;
        $objRedis->set($key, $type, $timeOut);
    }

    private function _getDomainProxy($domain, $proxy) {
        $key = $this->_getProxyKey($domain, $proxy);
        $objRedis = $this->_getRedis();
        $result = $objRedis->get($key);
        return $result;
    }

    private function _getXProxyId($free = false, $count = 3, $return_type = 2) {
        $url = "http://api.xdaili.cn/xdaili-api/greatRecharge/getGreatIp";
        if (!$free) {
            $params = [
                'spiderId' => $this->spiderId,
                'orderno' => $this->orderno,
                'returnType' => $return_type,
                'count' => $count,
            ];
            $params = http_build_query($params);
            $url .= '?' . $params;
        } else {
            $url = "http://www.xdaili.cn/ipagent/freeip/getFreeIps?page=1&rows=10";
        }

        $dwHttp = new dwHttp();
        $result = $dwHttp->get2($url);

        $result = json_decode($result, true);
        $proxyList = $result['RESULT'];
        if ($free) {
            $proxyList = $result['RESULT']['rows'];
        }

        return $proxyList;
    }
}