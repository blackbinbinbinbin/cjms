<?php
class dwHttp {
	protected $way='';
	protected $addCallId = true;
	private $proxyList = null;

	public function __construct($way=''){	
		if(in_array($way,array('curl','socket','file_get_contents'))){ //如果指定访问方式，则按指定的方式去访问
			$this->way=$way;	
		}elseif(function_exists('curl_init')){ //curl方式
			$this->way='curl';
		}else if(function_exists('fsockopen')){ //socket
			$this->way='socket';
		}else if(function_exists('file_get_contents')){ //php系统函数file_get_contents
			$this->way='file_get_contents';
		}else{
			$this->way='';
		}	
	}

    /**
     * 是否需要添加callId
     * @param $flag
     */
	public function enableCallId($flag = true) {
        $this->addCallId = $flag;
    }

    /**
     * 设置自动代理
     * @param int $poolNum 代理ip池数量
     */
    public function setAutoProxy($poolNum = 50) {
        $proxyUrl = "http://61.160.36.225:8000/?count={$poolNum}";
        $json = $this->get($proxyUrl, 5, "", 300);
        if ($json) {
            $this->proxyList = json_decode($json, true);
        }
    }

    public function getProxy() {
        if ($this->proxyList) {
            $index = array_rand($this->proxyList);
            return $this->proxyList[$index];
        }

        return false;
    }

    private function getCache($key) {
        $objRedis = dwRedisPro::init('logic');
        $key = 'globals:url_map:' . $key;
        $response = $objRedis->get($key);
        return $response;
    }

    private function setCache($key, $ttl, $response) {
        $objRedis = dwRedisPro::init('logic');
        $key = 'globals:url_map:' . $key;
        return $objRedis->setex($key, $ttl, $response);
    }
	
	/**
     * 通过get方式获取数据
     * @author ben
     * @param string $url 请求地址
     * @param int $timeout 超时时间
     * @param string $header 请求头部
     * @param int $ttl 缓存时间(秒)，默认为0，不缓存
     * @param callable $cacheCb 缓存回调事件
     * @return string 响应数据
     */
	public function get($url, $timeout=5, $header="", $ttl = 0, callable $cacheCb = null) {
		if (empty($url)||empty($timeout)) return false;
		if (!preg_match('/^(http|https)/is',$url)) $url="http://".$url;

		if ($ttl > 0) {
		    $key = $url;
            $response = $this->getCache($key);
            if ($response) {
                return $response;
            }
        } else {
		    $key = '';
        }
			
		$startTime = microtime(true);
		if ($this->addCallId && strpos($url, '_call_id=') === false && strpos($url, '&sign=') === false) {
    		if (strpos($url, '?') === false) {
    		    $and = '?';
    		} else {
    		    $and = '&';
    		}
    		
    		$url .= $and . '_call_id=' . CallLog::getCallId();
		}
		
		switch($this->way){
			case 'curl':
			    $response = $this->curlGet($url, $timeout, $header);
			    break;
			case 'socket':
			    $response = $this->socketGet($url, $timeout, $header);
			    break;
			case 'file_get_contents':
			    $response = $this->phpGet($url, $timeout, $header);
			    break;
			default:
			    return false;	
		}

        if ($ttl > 0 && $response) {
		    if ($cacheCb) {
		        if ($cacheCb($response)) {
                    $this->setCache($key, $ttl, $response);
                }
            } else {
                $this->setCache($key, $ttl, $response);
            }
        }
		
		CallLog::logModuleCall("GET", $url, null, $response, $startTime);
		return $response;
	}

    /**
     * 重试n次，通过get方式获取数据
     * @author ben
     * @param string $url 请求地址
     * @param int $times 重试次数，默认为3次
     * @param int $firstTimeout 第一次超时时间，后续超时间为：$i * $firstTimeout
     * @param string $header 头部信息
     * @param int $ttl 缓存时间(秒)，默认为0，不缓存
     * @param callable $cacheCb 缓存回调事件
     * @return string 响应数据
     */
	public function get2($url, $times = 3, $firstTimeout = 3, $header = "", $ttl = 0, callable $cacheCb = null) {
	    for ($i = 1; $i <= $times; $i++) {
            $response = $this->get($url, $firstTimeout * $i, $header, $ttl, $cacheCb);
            if ($response !== false) {
                return $response;
            }
        }

        return false;
    }

    /**
     * 通过POST方式发送数据
     * @author ben
     * @param string $url 请求地址
     * @param string|array $post_data 提交的数据
     * @param int $timeout 超时时间
     * @param string $header 头部信息
     * @param int $ttl 缓存时长(秒)，默认为0，不缓存
     * @return string 响应数据
     */
	public function post($url, $post_data=array(), $timeout = 5, $header="", $ttl = 0) {
		if(empty($url)||empty($timeout)) return false;
		if(!preg_match('/^(http|https)/is',$url)) $url="http://".$url;
		
		$startTime = microtime(true);

        if ($ttl > 0) {
            $key = $url . '?__post__=' . md5(http_build_query($post_data));
            $response = $this->getCache($key);
            if ($response) {
                return $response;
            }
        } else {
            $key = '';
        }

		if ($this->addCallId && is_array($post_data) && !$post_data['_call_id'] && !$post_data['sign']) {
		    $post_data['_call_id'] = CallLog::getCallId();
		}

		switch($this->way){
			case 'curl':
			    $response = $this->curlPost($url, $post_data, $timeout, $header);
			    break;
			case 'socket':
			    $response = $this->socketPost($url, $post_data,$timeout, $header);
			    break;
			case 'file_get_contents':
			    $response = $this->phpPost($url, $post_data, $timeout, $header);
			    break;
			default:
			    return false;	
		}

        if ($ttl > 0 && $response) {
            $this->setCache($key, $ttl, $response);
        }

		CallLog::logModuleCall("POST", $url, $post_data, $response, $startTime);
		return $response;
	}

    /**
     * 重试n次，通过POST方式发送数据
     * @author ben
     * @param string $url 请求地址
     * @param int $times 重试次数，默认为3次
     * @param int $firstTimeout 第一次超时时间，后续超时间为：$i * $firstTimeout
     * @param string $header 头部信息
     * @param int $ttl 缓存时长(秒)，默认为0，不缓存
     * @return string 响应数据
     */
    public function post2($url, $post_data = array(), $times = 3, $firstTimeout = 3, $header = "", $ttl = 0) {
        for ($i = 1; $i <= $times; $i++) {
            $response = $this->post($url, $post_data, $firstTimeout * $i, $header, $ttl);
            if ($response !== false) {
                return $response;
            }
        }

        return false;
    }

	//发送文件
	public function postFile($url, $post_data=array(), $timeout=30, $cookie=''){
		$c = curl_init(); 
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($c, CURLOPT_URL, $url); 
		curl_setopt($c, CURLOPT_POST, true); 
		curl_setopt($c, CURLOPT_TIMEOUT, $timeout); 
		empty($cookie) or curl_setopt($c, CURLOPT_COOKIEFILE, $cookie); 
		empty($cookie) or curl_setopt($c, CURLOPT_COOKIEJAR, $cookie); 
		curl_setopt($c, CURLOPT_POSTFIELDS, $post_data); 
		$data = curl_exec($c); 
		curl_close($c); 
		return $data;	
	}
	
	//通过curl get数据
	protected function curlGet($url, $timeout=5, $header="") {
	    $startTime = microtime(true);
	    
		$header = empty($header) ? $this->defaultHeader() : $header;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, explode("\r\n", $header));//模拟的header头
		//设置curl默认访问为IPv4
		if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
		    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		}

		$proxy = $this->getProxy();
		if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy[0]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy[1]); //代理服务器端口
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }

		$result = curl_exec($ch);
		
		if (microtime(true) - $startTime >= $timeout - 1) {
		    $info = curl_getinfo($ch);
		    // Util::log(print_r($info, true), 'slow_network', null, null, 2);
		}
		
		curl_close($ch);
		return $result;
	}
	
	//通过curl post数据
	protected function curlPost($url, $post_data='', $timeout=5, $header="") {
	    $startTime = microtime(true);
	    
		$header = empty($header) ? $this->defaultHeader() : $header;
		$post_string = is_array($post_data) ? http_build_query($post_data) : $post_data;  
		$ch = curl_init();
		
		$ssl = substr($url, 0, 8) == "https://" ? true : false;
		if ($ssl) {
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
	
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_URL, $url);
		//设置curl默认访问为IPv4
		if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
		    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		}

        $proxy = $this->getProxy();
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy[0]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy[1]); //代理服务器端口
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, explode("\r\n", $header));//模拟的header头
		$result = curl_exec($ch);
		
		if (microtime(true) - $startTime >= $timeout - 1) {
    		$info = curl_getinfo($ch);
    		// Util::log(print_r($info, true), 'slow_network', null, null, 2);
		}

		curl_close($ch);
		
		return $result;
	}
	
	//通过socket get数据
	protected function socketGet($url,$timeout=5,$header="") {
		$header = empty($header) ? $this->defaultHeader() : $header;
		$url2 = parse_url($url);
		$url2["path"] = isset($url2["path"])? $url2["path"]: "/" ;
		$url2["port"] = isset($url2["port"])? $url2["port"] : 80;
		$url2["query"] = isset($url2["query"])? "?".$url2["query"] : "";
		$host_ip = @gethostbyname($url2["host"]);

		if(($fsock = fsockopen($host_ip, $url2['port'], $errno, $errstr, $timeout)) < 0){
			return false;
		}
		
		$request =  $url2["path"] .$url2["query"];
		$in  = "GET " . $request . " HTTP/1.0\r\n";
		if(false===strpos($header, "Host:")){	
			 $in .= "Host: " . $url2["host"] . "\r\n";
		}
		
		$in .= $header;
		$in .= "Connection: Close\r\n\r\n";
		
		if(!@fwrite($fsock, $in, strlen($in))){
			@fclose($fsock);
			return false;
		}
		return $this->GetHttpContent($fsock);
	}
	
	//通过socket post数据
	protected function socketPost($url, $post_data='', $timeout=5, $header="") {
		$header = empty($header) ? $this->defaultHeader() : $header;
		$post_string = is_array($post_data) ? http_build_query($post_data) : $post_data;  
	
		$url2 = parse_url($url);
		$url2["path"] = ($url2["path"] == "" ? "/" : $url2["path"]);
		$url2["port"] = ($url2["port"] == "" ? 80 : $url2["port"]);
		$host_ip = @gethostbyname($url2["host"]);
		$fsock_timeout = $timeout; //超时时间
		if(($fsock = fsockopen($host_ip, $url2['port'], $errno, $errstr, $fsock_timeout)) < 0){
			return false;
		}
		$request =  $url2["path"].($url2["query"] ? "?" . $url2["query"] : "");
		$in  = "POST " . $request . " HTTP/1.0\r\n";
		$in .= "Host: " . $url2["host"] . "\r\n";
		$in .= $header;
		$in .= "Content-type: application/x-www-form-urlencoded\r\n";
		$in .= "Content-Length: " . strlen($post_string) . "\r\n";
		$in .= "Connection: Close\r\n\r\n";
		$in .= $post_string . "\r\n\r\n";
		unset($post_string);
		if(!@fwrite($fsock, $in, strlen($in))){
			@fclose($fsock);
			return false;
		}
		return $this->GetHttpContent($fsock);
	}

	//通过file_get_contents函数get数据
	protected function phpGet($url,$timeout=5, $header="") {
		$header = empty($header) ? $this->defaultHeader() : $header;
		$opts = array( 
				'http'=>array(
							'protocol_version'=>'1.0', //http协议版本(若不指定php5.2系默认为http1.0)
							'method'=>"GET",//获取方式
							'timeout' => $timeout ,//超时时间
							'header'=> $header)
				  ); 
		$context = stream_context_create($opts);    
		return  @file_get_contents($url,false,$context);
	}
	
	//通过file_get_contents 函数post数据
	protected function phpPost($url, $post_data=array(), $timeout=5, $header="") {
		$header = empty($header) ? $this->defaultHeader() : $header;
		$post_string = is_array($post_data) ? http_build_query($post_data) : $post_data;   
		$header.="Content-length: ".strlen($post_string);
		$opts = array( 
				'http'=>array(
							'protocol_version'=>'1.0',//http协议版本(若不指定php5.2系默认为http1.0)
							'method'=>"POST",//获取方式
							'timeout' => $timeout ,//超时时间 
							'header'=> $header,  
							'content'=> $post_string)
				  ); 
		$context = stream_context_create($opts);    
		return  @file_get_contents($url,false,$context);
	}
	
	//默认模拟的header头
	protected function defaultHeader(){
		$header="User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12\r\n";
		$header.="Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$header.="Accept-language: zh-cn,zh;q=0.5\r\n";
		$header.="Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
		return $header;
	}
	
	//获取通过socket方式get和post页面的返回数据
	protected function GetHttpContent($fsock=null){
		$out = '';
		do {
    		$buff = @fgets($fsock, 2048);
    		$out .= $buff;
		} while($buff);
		
		fclose($fsock);
		$pos = strpos($out, "\r\n\r\n");
		$head = substr($out, 0, $pos);    //http head
		$status = substr($head, 0, strpos($head, "\r\n"));    //http status line
		$body = substr($out, $pos + 4, strlen($out) - ($pos + 4));//page body
		$pregStr = "/^HTTP\/d\.\d\s([\d]+)\s.*$/";
		if(preg_match($pregStr, $status, $matches)){
			if(intval($matches[1]) / 100 == 2){
				return $body;  
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}