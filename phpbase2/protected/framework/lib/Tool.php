<?php

/**
 * 工具类
 * @author benzhan
 * @version 1.0 update time: 2011-10-13
 * @package lib
 */
class Tool {

    /**
     * 调试日志(只有DEBUG为true时, 才记录)
     * @author benzhan
     * @param string/array $content 调试内容
     */
    public static function debug($content, $callLevel = 1) {
        if (DEBUG) {
            self::writeLog($content, 'debug', $callLevel);
        }
    }
    
    /**
     * 记录流水日志
     * @author benzhan
     * @param string/array $content 流水日志内容
     */
    public static function log($content, $callLevel = 1) {
        self::writeLog($content, 'log', $callLevel);
    }

    /**
     * 记录警告日志
     * @author benzhan
     * @param string/array $content 流水日志内容
     */
    public static function warning($content, $callLevel = 1) {
        self::writeLog($content, 'warning', $callLevel);
    }


    /**
     * 记录错误日志
     * @author benzhan
     * @param string/array $content 错误日志内容
     */
    public static function err($content, $callLevel = 1) {
        self::writeLog($content, 'error', $callLevel);
    }

    public static function writeLog($content, $label = 'log', $callLevel = 0, $file = null, $line = null) {
        if (is_array($content)) {
            $content = json_encode($content);
        }
        
        if (!$file) {
            $backtrace = debug_backtrace();
            $obj = $backtrace[$callLevel];
            $file = $obj['file'];
            $line = $obj['line'];
        }
    
        // 框架结构
        $index = strpos($file, "/protected/");
        if ($index !== false) {
            $file = substr($file, $index + 1);
        } else {
            $parts = explode("/", $file);
            $file = end($parts);
        }
    
        self::logstash($label, $file, $line, $content);
    }

    /**
     * 生成订单id
     * @author benzhan
     * @param string $prefix 订单前缀
     */
    public static function genOrderId($prefix = "") {
        $objRedis = dwRedis::init('logic');
         
        $times = 0;
        $key = '';
        do {
            // 元宝商城的订单id
            $orderId = $prefix . date('mdHis') . (microtime(true) % 1000) . rand(100, 999);
            $key = REDIS_KEY_ORDERID_POOL . ':' . $orderId;
            $flag = $objRedis->setNx($key, 1);
        } while(!$flag && $times++ < 100);
         
        // 这个id缓存1分钟
        $objRedis->expire($key, 60);
    
        return $orderId;
    }
    
    public static function postWithClientIp($url, $post_data, $timeout = 5, $header = '') {
        $client = getip();
        $header .= "\r\nX-FORWARDED-FOR:{$client}\r\nCLIENT-IP:{$client}\r\n";
        $http = new dwHttp();
        $out = $http->post($url, $post_data, $timeout, $header);
    
        return $out;
    }
    
    public static function getWithClientIp($url, $timeout = 5, $header = '') {
        $client = getip();
        $header .= "\r\nX-FORWARDED-FOR:{$client}\r\nCLIENT-IP:{$client}\r\n";
        $http = new dwHttp();
        $out = $http->get($url, $timeout, $header);
         
        return $out;
    }

    /**
     * gzip压缩
     * @param string $content 需要压缩的内容
     * @return string 压缩后的内容
     * @author benzhan
     */
    public static function ob_gzip($content) {
        if (!headers_sent() && extension_loaded("zlib") && strstr($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip")) {
            $content = gzencode($content, 9);
            header("Content-Encoding: gzip");
            header("Vary: Accept-Encoding");
            header("Content-Length: " . strlen($content));
        }

        return $content;
    }
    
    /**
     * 标准的缓存，只有result为1时，才会进行缓存
     * @author benzhan
     * @param unknown $url
     * @param unknown $post_data
     * @param number $timeout
     * @param string $header
     * @return unknown|Ambigous <boolean, unknown>
     */
    public static function postWithCache($url, $post_data, $timeout = 5, $header = '', $expire = 86400) {
        $md5 = md5(http_build_query($post_data));
        $key = "{$url}:{$md5}";
        $objRedis = new dwRedis();
        $value = $objRedis->get($key);
         
        if ($value) {
            return $value;
        } else {
            $http = new dwHttp();
            $json = $http->post($url, $post_data, $timeout, $header);
            $objResult = json_decode($json, true);
            if ($objResult['result']) {
                $objRedis->set($key, $json);
                $objRedis->expire($key, $expire);
            }
             
            return $json;
        }
    }
    
    private static function logstash($label, $file, $line, $msg) {
        global $startTime;
    
        $data = array();
        $data['call_id'] = CallLog::getCallId();
        $url = $_SERVER['REQUEST_URI'];
        $parts = explode('?', $url);
        $data['url'] = $parts[0];
        $data['log_level'] = $label;
        $data['delay'] = microtime(true) - $startTime;
        $data['server_ip'] = CallLog::getWanIp();
        $data['client_ip'] = getip();
        $data['file'] = $file;
        $data['line'] = $line;
        $data['msg'] = $msg;
    
        $data['yyuid'] = $GLOBALS['yyuid'];
        $data['d_uuid'] = $_REQUEST['d_uuid'];
        $data['platform'] = $_REQUEST['platform'];
    
        $pushData = array(
            'message' => $data,
            'type' => TYPE_CUSTOM_LOG,
            'time' => date('Y-m-d H:i:s', $startTime)
        );
    
        CallLog::writeRedis($pushData);
    }
    
    /**
     * 获取分页Html
     * @param array $args array('rowNum', 'page', 'pageSize')
     * @author benzhan
     */
    public static function getPageHtml($args) {
        isset($args['pageSize']) || $args['pageSize'] = 20;
        isset($args['page']) || $args['page'] =  (int) $_REQUEST['_page'];
        if (isset($args['pageTpl'])) {
            $args['jsClick'] = false;
        } else {
            $args['pageTpl'] = 'javascript:void(%d);';
            $args['jsClick'] = true;
        }

        // 不需要分页
        if ($args['rowNum'] <= $args['pageSize']) {
            return '';
        }

        $args['options'] = array(10, 20, 50, 100);
        $args['total'] = ceil($args['rowNum'] / $args['pageSize']);

        $tpl = Template::init();
        $tpl->assign($args);
        return $tpl->fetch('bootstrap_pager');
    }

	/**
	 * 生成退款id
	 * @author benzhan
	 * @param string $prefix 订单前缀
	 * @return string
	 */
	public static function genRefundId($prefix = "") {
		$objRedis = dwRedis::init('logic');

		$times = 0;
		$key = '';
		do {
			// 元宝商城的订单id
			$orderId = $prefix . date('Ymd') . (microtime(true) % 1000) . rand(100, 999);
			$key = REDIS_KEY_REFUNDID_POOL . ':' . $orderId;
			$flag = $objRedis->setNx($key, 1);
		} while(!$flag && $times++ < 100);

		// 这个id缓存1分钟
		$objRedis->expire($key, 60);

		return $orderId;
	}

	/**
	 * 导出CSV文件
	 * @author solu
	 * @param  string $fileName 文件名
	 * @param  array  $data     导出数据
	 * @param  array  $header   表头
	 * @return NULL
	 */
	public static function exportCSV(string $fileName, array $data, array $header = array()) {
		if (empty($data)) {
			return false;
		}
		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("content-type: application/csv;charset=UTF-8");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$fileName}.csv");
		header("Content-Transfer-Encoding: binary");


		$df = fopen("php://output", 'w');
		fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
		if (!empty($header)) {
			fputcsv($df, $header);
		}
		foreach ($data as $row) {
			fputcsv($df, $row);
		}
		fclose($df);
		exit;
	}
}

//end of script
