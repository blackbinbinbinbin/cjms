<?php

/**
 * 调用类日志
 *
 * @author benzhan
 * @package lib
 */
class CallLog {
    private static $call_id = null;
    private static $objRedis = null;
    /**
     * @var float 采样率
     */
    public static $rate = 1.0;
    /**
     * @var bool 是否要上报
     */
    private static $flag = null;

    public static function getLogRedis() {
        if (self::$objRedis == null) {
            try {
                self::$objRedis = dwRedis::init('logstash_redis');
            } catch (Exception $ex) {
                DEBUG && var_dump($ex->getMessage());
            }
        }

        return self::$objRedis;
    }
    
    public static function writeRedis($pushData) {
        if (self::$flag === null) {
            $rand = rand(0, 1000) / 1000;
            self::$flag = $rand <= self::$rate;
        }


        if (!self::$flag) {
            return false;
        }

        try {
            $config = $GLOBALS['redisInfo']['logstash_redis'];
            
            if ($config && class_exists("Redis")) {
                $objRedis = self::getLogRedis();
                $json = json_encode($pushData, JSON_UNESCAPED_UNICODE);
                if ($json && $objRedis) {
                   $len = $objRedis->rPush(CALL_LOG_KEY, $json);
                   // 数量大于10w，则要删除队列
                   if ($len && $len > 100000) {
                       $objRedis->lPop(CALL_LOG_KEY);
                   }
                } else {
//                    Tool::warning("writeRedis json_encode error. data:\r\n" . var_export($pushData, true));
                }
            }
        } catch (Exception $ex) {
        }
    }

    public static function getCallId() {
        if (!self::$call_id) {
            self::$call_id = (microtime(true) % 1000000) . rand(100, 999);
        }

        return self::$call_id;
    }

    public static function logModuleCall($method, $toUrl, $postData, $response, $startTime) {
        $data = array();
        $data['from_call_id'] = self::getCallId();
        $data['from_url'] = self::getUrl();
        $data['method'] = $method;
        
        $parts = explode('?', $toUrl);
        $data['to_url'] = $parts[0];
        $getParam = $parts[1];
        
        if (is_array($postData)) {
            $postParam = http_build_query($postData);
        } else {
            $postParam = $postData;
        }

        if ($getParam && $postParam) {
            $param = $getParam . '&' . $postParam;
        } else if ($getParam) {
            $param = $getParam;
        } else {
            $param = $postParam;
        }

        $param = mb_substr($param, 0, 3000, "utf-8");
        $data['param'] = $param;
        if ($response !== false) {
            $objResult = json_decode($response, true);
            $data['code'] = $objResult['code'] ?: $objResult['errcode'];
            $data['response'] = mb_substr($response, 0, 3000, "utf-8");
        } else {
            $data['code'] = CODE_REQUEST_TIMEOUT;
        }
        
        $data['delay'] = round(microtime(true) - $startTime, 6);
        $data['server_ip'] = self::getWanIp();
        
        $pushData = array(
            'message' => $data,
            'type' => TYPE_MODULE_CALL,
            'time' => date('Y-m-d H:i:s', $startTime)
        );
        
        self::writeRedis($pushData);
    }

    private static $_url = null;
    public static function setUrl($url) {
        self::$_url = $url;
    }

    public static function getUrl() {
        if (self::$_url) {
            return self::$_url;
        } else {
            $controller = str_replace('Controller', '', CONTROLLER_NAME);
            return '/' . lcfirst($controller) . '/' . ACTION_NAME;
        }
    }

    public static function logSelfCall($code, $response) {
        global $startTime;
        
        $data = array();
        $data['call_id'] = self::getCallId();
        $data['url'] = self::getUrl();
        $data['method'] = $_SERVER['REQUEST_METHOD'];
        $param = http_build_query($_REQUEST);
        
        $data['param'] = mb_substr($param, 0, 3000, "utf-8");
        $data['cookie'] = http_build_query($_COOKIE);
        if (Response::$debugMsg) {
            $response = '【debugMsg:' . Response::$debugMsg . "】{$response}";
        }
        
        $data['response'] = mb_substr($response, 0, 3000, "utf-8");
        $data['code'] = $code;
        $data['delay'] = round(microtime(true) - $startTime, 6);
        if ($GLOBALS['xhprof']['delay_time_value'] > 0 && ($data['delay'] >= $GLOBALS['xhprof']['delay_time_value'])) {
            $GLOBALS['xhprof']['save'] = 1;
        }

        /**
         * @var DB_MYSQLi $db
         */
        $db = null;
        $sqls = [];
        foreach (DB::$db as $key => $db) {
            $sqls = $sqls + $db->getSql();
        }

        ksort($sqls);
        $data['sql'] = join("\n", $sqls);
        $data['server_ip'] = self::getWanIp();
        $data['client_ip'] = getip();
        $data['useragent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['referer'] = $_SERVER['HTTP_REFERER'];
        
        $pushData = array(
            'message' => $data,
            'type' => TYPE_SELF_CALL,
            'time' => date('Y-m-d H:i:s', $startTime)
        );
        
        self::writeRedis($pushData);
        self::log2Xprof();
        self::logSql();
    }

    public static function logSql() {
        global $startTime;

        /**
         * @var DB_MYSQLi $db
         */
        $sql = '';
        $db = null;
        foreach (DB::$db as $key => $db) {
            $sqls = $db->getUpdateSql();
            if (count($sqls) > 0) {
                $sql .= "[{$key}] " . join("\n[{$key}] ", $sqls) . "\n";
            }
        }

        if ($sql) {
            $data = [];
            $data['call_id'] = self::getCallId();
            $data['sql'] = $sql;

            $pushData = array(
              'message' => $data,
              'type' => TYPE_SELF_CALL . '_sql',
              'time' => date('Y-m-d H:i:s', $startTime)
            );
            self::writeRedis($pushData);
        }

    }

    public static function getWanIp() {
        $apcKey = 'log_wan_ip';
        $wanIp = apc_fetch($apcKey);
        
        if ($wanIp) {
            $matches = array();
            preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $wanIp, $matches);
            if ($matches) {
                return $wanIp;
            }
        }
        
        $output = array();
        $return_var = array();
        $ret = exec("ifconfig", $output, $return_var);
        
        $datas = array();
        $key = null;
        foreach ($output as $row) {
            $matches = array();
            preg_match('/^[^\s]+/', $row, $matches);
            if ($matches) {
                $key = $matches[0];
            } else {
                preg_match('/inet addr:(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $row, $matches);
                if ($matches && $key) {
                    $datas[$key] = $matches[1];
                    $key = '';
                }
            }
        }
        
        foreach ($datas as $key => $ip) {
            $matches = array();
            preg_match('/^eth\d+$/', $key, $matches);
            if ($matches && !self::isInternalIp($ip)) {
                break;
            }
        }
        
        $ip || $ip = $_SERVER['SERVER_ADDR'];
        apc_add($apcKey, $ip, 3600 * 24);
        
        return $ip;
    }

    private static function isInternalIp($ip) {
        $internalIps = [
            '10.', // A类网的预留ip
            '172.16.', // B类网的预留ip
            '192.168.', // C类网的预留ip
            '127.', // 本地网络
        ];

        foreach ($internalIps as $internalIp) {
            $len = strlen($internalIp);
            $subIp = substr($ip, 0, $len);
            if ($subIp == $internalIp) {
                return true;
            }
        }

        return false;
    }

    private static function log2Xprof() {

        // 开启xhprof并达到保存条件
        if( defined('_XHPROF_OPEN') && _XHPROF_OPEN && $GLOBALS['xhprof']['save'] == 1 ){
            $xhprof_data = xhprof_disable();  
            $xhprof_data = serialize($xhprof_data);
            $source = empty($GLOBALS['xhprof']['appname'])?$_SERVER['HTTP_HOST']:$GLOBALS['xhprof']['appname'];

            // $xhprof_dir = ini_get("xhprof.output_dir") . '/' . $source . '.' . str_replace('.', '_', $_SERVER['SERVER_ADDR']);
            $xhprof_dir = ini_get("xhprof.output_dir") . '/' . $source;
            if( !file_exists($xhprof_dir) ){
                mkdir($xhprof_dir, 0777, true);
            }
            $file_name = $xhprof_dir.'/'.CONTROLLER_NAME.'_'.ACTION_NAME.'_'.uniqid() .'.'.$source.'.xhprof';
            file_put_contents($file_name, $xhprof_data);
        }
    }
}

//end of script
