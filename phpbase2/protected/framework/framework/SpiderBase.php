<?php

/**
 * 爬虫类
 * @author xubin
 */
class SpiderBase {
	protected $proxy_data = null;
	protected $cache_cookie = [];
	protected $set_cookie_keys = [];
	protected $curl_get_info = null;
	protected $curl_error_info = null;
	protected $header = '';

	public function __construct($defaultHeader = true, $yyProxy = true) {
	    if ($defaultHeader) {
	        $this->setDefHeader();
        }

        if ($yyProxy && (ENV == ENV_FORMAL || ENV == ENV_NEW)) {
            $this->setYYProxy();
        }
    }

    /**
     * 设置指定的代理
     * @param $proxy_data
     */
	public function setProxy($proxy_data) {
		$this->proxy_data = $proxy_data;
	}

    /**
     * 设置YY机房的代理ip
     */
    public function setYYProxy() {
        $proxyList = [
            "10.20.60.190:8118",
            "10.20.60.225:8118",
            "10.20.60.243:8118",
            "10.20.60.241:8118",
            "10.20.60.170:8118",
            "10.20.60.162:8118",
            "10.20.60.155:8118",
            "10.20.60.87:8118",
            "10.20.60.168:8118",
            "10.20.60.137:8118",
            "10.20.60.136:8118",
            "10.20.60.132:8118",
            "10.20.60.129:8118",
            "10.20.60.128:8118",
            "10.20.60.127:8118",
            "10.20.60.126:8118",
            "10.20.61.250:8118",
            "10.20.60.236:8118",
            "10.20.60.233:8118",
            "10.20.60.232:8118",
            "10.20.60.230:8118",
            "10.20.60.228:8118",
            "10.20.60.227:8118",
            "10.20.60.224:8118",
            "10.20.61.118:8118",
            "10.20.61.98:8118",
            "10.20.60.186:8118",
            "10.20.60.172:8118",
            "10.20.60.171:8118",
            "10.20.61.216:8118",
            "10.20.61.215:8118",
            "10.20.49.190:8118",
            "10.20.49.189:8118",
            "10.20.60.23:8118",
            "10.20.96.171:8118",
            "10.20.96.170:8118",
            "10.20.96.169:8118",
            "10.20.60.250:8118",
            "10.20.60.153:8118",
            "10.20.60.108:8118",
            "10.20.61.117:8118",
            "10.20.61.116:8118",
            "10.20.61.84:8118",
            "10.20.61.83:8118",
        ];
        $index = array_rand($proxyList);
        $this->proxy_data = $proxyList[$index];
    }

    /**
     * 设置需要记录的cookie的keys
     * @param $set_cookie_keys
     */
	public function setCookieKeys($set_cookie_keys) {
		$this->set_cookie_keys = $set_cookie_keys;
	}

    /**
     * 自定义爬虫参数
     * @param $url
     * @param int $timeout
     * @param string $header
     * @return bool|string
     */
	public function curlGet($url, $timeout=5, $header="", $path = '') {
		$url_host = parse_url($url, PHP_URL_HOST);
		$startTime = microtime(true);
		    
		$header = empty($header) ? $this->header : $header;

		$ch = curl_init($url);
//		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min($timeout / 2, 10));
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, explode("\r\n", $header));//模拟的header头
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);



		if ($path) {
            $fp = fopen($path, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $header = 0;
        } else {
            $header = 1;
        }
        curl_setopt($ch, CURLOPT_HEADER, $header);

		if ($this->proxy_data) {
			$proxyInfo = explode(':', $this->proxy_data);
			curl_setopt($ch, CURLOPT_PROXY, $proxyInfo[0]);
			curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo[1]); //代理服务器端口
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		}

		$content = curl_exec($ch);
		//解析HTTP数据流
        if ($header) {
            list($header, $body) = explode("\r\n\r\n", $content);
            $pos = strpos($content, "\r\n\r\n");
            $body = substr($content, $pos);

            //解析COOKIE
            $cookie_data = $this->getCookieData($header, $this->set_cookie_keys);
            //cache cookie
            if ($cookie_data && !$this->cache_cookie[$url_host]) {
                $this->cacheCookie($cookie_data, $url_host);
            }
        } else {
            $body = $content;
        }

		if (microtime(true) - $startTime >= $timeout / 2) {
		    $this->curl_get_info = curl_getinfo($ch);
		    $this->curl_error_info = curl_error($ch);
		}
		
		curl_close($ch);
		CallLog::logModuleCall("GET", $url, null, substr($body,0,2000), $startTime);

		if (empty($body)) {
			return false;
		} else {
			return $body;
		}
	}

    /**
     * 设置默认的头部信息 header
     * @param string $host
     * @param string $cookie
     */
	public function setDefHeader($host = null, $cookie = null) {
	    $uaList = [
	        'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.75 Safar',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0)',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safar',
            'Mozilla/5.0 (Linux; U; Android 5.1; zh-cn; OPPO R9m Build/LMY47I) AppleWebKit/537.36',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.31 (KHTML, like Gecko) Chrome/17.0.5',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2',
        ];
	    $index = array_rand($uaList);
	    $ua = $uaList[$index];

	    $header = "User-Agent:{$ua}\r\n";
	    $header.="Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n";
	    $header.="Accept-language: zh-cn,zh;q=0.5\r\n";
	    $header.="Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";

	    if ($cookie) {
	        $header.="Cookie: {$cookie} \r\n";
	    }

	    if ($host) {
	    	$header.="Host: {$host}\r\n";
	    }

	    $this->header = $header;
	}

    /**
     * 可多次重连的get请求
     * @param string $url
     * @param int $times
     * @param int $firstTimeout
     * @return bool|string
     */
	public function get2($url, $times = 3, $firstTimeout = 3) {
	    for ($i = 1; $i <= $times; $i++) {
	        $response = $this->get($url, $firstTimeout * $i);
	        if ($response !== false) {
	            return $response;
	        } else {
	        	$url_host = parse_url($url, PHP_URL_HOST);
	        	$this->header = $this->setDefHeader(null, $this->cache_cookie[$url_host]);
	        }
	    }

	    return false;
	}

    /**
     * 封装的 get 请求
     * @param $url
     * @param int $timeout
     * @return bool|string
     */
	public function get($url, $timeout=5) {	
		if(empty($url)||empty($timeout)) return false;
		if(!preg_match('/^(http|https)/is',$url)) $url="http://".$url;
			
		$startTime = microtime(true);
		$header = $this->header;
		$response = $this->curlGet($url, $timeout, $header);

		if (strpos($url, '_call_id=') === false && strpos($url, '&sign=') === false) {
			if (strpos($url, '?') === false) {
			    $and = '?';
			} else {
			    $and = '&';
			}
			
			$url .= $and . '_call_id=' . CallLog::getCallId();
		}
		CallLog::logModuleCall("GET", $url, null, substr($response,0,2000), $startTime);
		return $response;
	}

    /**
     * 封装的 下载请求
     * @author benzhan
     * @param $url
     * @param int $timeout
     * @return bool|string
     */
    public function download($url, $path, $timeout = 60) {
        if(empty($url)||empty($timeout)) return false;
        if(!preg_match('/^(http|https)/is',$url)) $url="http://".$url;

        $startTime = microtime(true);
        $header = $this->header;
        $response = $this->curlGet($url, $timeout, $header, $path);

        CallLog::logModuleCall("GET", $url, null, substr($response,0,2000), $startTime);
        return $response;
    }


	private function getCookieData($header, $cookie_key_arr) {
	    $cookie = [];
	    // 解析COOKIE
	    $header_list = explode("\r\n", $header);
	    foreach ($header_list as $key => $value) {
	        $ret = preg_match("/set\-cookie:([^\r\n]*;)/i", $value, $matches);
	        if ($ret) {
	            $cookie[] = $matches[1];
	        }
	    } 

	    $cookie_data = [];
	    //打印获得的数据
	    foreach ($cookie as $key => $value) {
	        $set_cookies = explode("=", $value);
	        if (in_array(trim($set_cookies[0]), $cookie_key_arr)) {
	            $cookie_data[] = trim($set_cookies[0]) .'='. substr($set_cookies[1],0,strrpos($set_cookies[1],';'));
	        }
	    }

	    return $cookie_data;
	}

	//缓存cookie
    private function cacheCookie($cookie_data, $host="") {
		$objRedis = dwRedis::init('logic');
		
		$cache_cookie_data = $objRedis->get("sy_spider_cookie_{$host}");
		if (!$cache_cookie_data) {
			$objRedis->set("sy_spider_cookie_{$host}", json_encode($cookie_data));
			$objRedis->expire("sy_spider_cookie_{$host}", 1 * 24 * 3600);
            $cookie = "";
			foreach ($cookie_data as $key => $value) {
				$cookie .= "{$value}; ";
			}
			$this->cache_cookie[$host] = $cookie;
		}
	}

    /**
     * 查看host的cookie，常用于调试
     * @param string $host
     * @return string
     */
	public function getCacheCookie($host = null) {
		$objRedis = dwRedis::init('logic');

		$cache_cookie_data = $objRedis->get("sy_spider_cookie_{$host}");

		if (!$cache_cookie_data) {
			return "";
		} else {
			$cookie_data = json_decode($cache_cookie_data, true);
			$cookie = "";
			foreach ($cookie_data as $key => $value) {
				$cookie .= "{$value}; ";
			}

			return $cookie;
		}
	}
}