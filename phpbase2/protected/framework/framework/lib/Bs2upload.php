<?php
require_once "ydnclient_util.php";

/**
 * 使用方式
 * $params = array();
 * $params['accesskey'] = BS2_ACCESS_KEY;
 * $params['access_key_secret'] = BS2_ACCESS_SECRET;
 * $params['filename'] = '20140227_tuofu_1001.rar';
 * $params['localfile'] = '/root/tmp/20140227_tuofu_1001.rar';
 * $params['bucket'] = BS2_FILE_BUCKET;
 * $params['bs2host'] = BS2_HOST;
 * $params['bs2dlhost'] = BS2_DL_HOST;
 * $params['large'] = true;
 * $blocksize = $blocksize > 0 ? $blocksize : BS2_UPLOADS_DEFAULT_BLOCK_SIZE;
 * $this->load->library('bs2upload', $params);
 * if ($this->bs2upload->uploadsFile($blocksize)) {
 * 		var_dump($this->bs2upload->getDownloadUrl());
 * }
 */


/**
 * BS2云存储文件上传
 * @author 		晏巍健
 * @version		1.0 - 2014-02-25
 * @package		Libraries
 */
class Bs2upload {

	private $accesskey;

	private $access_key_secret;

	private $filename;

	private $localfile;

	private $bucket;

	private $fileinfo;

	private $zone;

	private $bs2host;

	private $bs2dlhost;

	private $bs2delhost;

	private $content_type;

	private $large;//true：表示是大文件 false：表示非大文件，非大文件最大致能上传16M的
	private $proxy; // 通过代理访问 格式ip:port

	const REPEAT_TIMES = 2;//允许失败重复上传次数

	const BS2_UPLOADS_DEFAULT_BLOCK_SIZE = 10240000;//分块上传默认块大小 10M/块
//	const BS2_UPLOADS_DEFAULT_BLOCK_SIZE = 2048000;//分块上传默认块大小 2M/块

	public function __construct($params = array()) {
		if ($params) {
			$this->setParams($params);
		}
		set_time_limit(0);
		//参数校验
	}

	/**
	 * 重新设置上传参数
	 * @param array $params
	 */
	public function setParams($params) {
		$this->setFilename($params['filename']);
		$this->setLocalfile($params['localfile']);
		$this->accesskey = $params['accesskey'];
		$this->access_key_secret = $params['access_key_secret'];
		$this->bucket = $params['bucket'];
		$this->bs2host = $params['bs2host'];
		$this->bs2dlhost = $params['bs2dlhost'];
		$this->bs2delhost = $params['bs2delhost'];
		$this->large = $params['large'] ? true : false;
		$this->proxy = $params['proxy'] ?: '';
		if ($params['content-type']) {
			$this->content_type = $params['content-type'];
		}
	}

	/**
	 * 设置本地文件
	 * @param string $localfile
	 * @return true
	 */
	public function setLocalfile($localfile) {
		$this->localfile = $localfile;
		$this->fileinfo = null;
		$this->zone = null;
		return true;
	}

	/**
	 * 设置文件上传名
	 * @param string $filename
	 * @return true
	 */
	public function setFilename($filename) {
		$this->filename = preg_replace('/\s+/', '_', $filename);
		$this->zone = null;
		return true;
	}

	/**
	 * 分块文件上传初始化
	 * @param string $mime MimeType
	 * @return array
	 */
	public function uploadsInit($mime = '') {
		if (! $this->large) {
			return false;
		}
		$url = "http://{$this->bucket}.{$this->bs2host}/{$this->filename}?uploads";
		if ($mime) {
			$url .= "&mime={$mime}";
		}
		$headers = array(
		'Date: ' . strftime('%a, %d %b %Y %H:%M:S GMT'),
		'Authorization: ' . generate_token($this->accesskey, $this->access_key_secret, 'POST', $this->bucket, $this->filename, time()),
		'AccessId: test___'
		);
		if ($this->content_type) {
			$headers[] = "Content-Type: {$this->content_type}";
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '');
		$ret = curl_exec($ch);
		$info = curl_getinfo($ch);

		curl_close($ch);
		if(false === $info) {
            Tool::err("#Upload stage# FAIL: zn got some problem, {$url}");
			return false;
		}
		if (200 != $info['http_code']) {
            Tool::err("#Upload stage# FAIL: zn got some problem, http code:{$info['http_code']}, {$url}");
			return false;
		}
		$res = json_decode($ret, true);
		if (!is_array($res)) {
            Tool::err("#Upload stage# FAIL: zn got some problem, response:{$ret}, {$url}");
			return false;
		}

		$this->zone = $res['zone'];
		return $res;
	}

	/**
	 * 上传文件分块
	 * @param int $uploadid 上传号
	 * @param int $partnumber 块号
	 * @param mime $data
	 * @return boolean
	 */
	public function uploads($uploadid, $partnumber, $data) {
		if (! $this->large) {
			return false;
		}
		$url = "http://{$this->zone}/{$this->filename}?uploadid={$uploadid}&partnumber={$partnumber}";
		$headers = array(
            'Date: ' . strftime('%a, %d %b %Y %H:%M:S GMT'),
            'Authorization: ' . generate_token($this->accesskey, $this->access_key_secret, 'PUT', $this->bucket, $this->filename, time()),
            'AccessId: test___',
            'X-HTTP-Method-Override: PUT'
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$rt = curl_exec($ch);
		$info = curl_getinfo($ch);

		curl_close($ch);
		if (200 != $info['http_code']) {
            Tool::err("#Upload stage# FAIL: zn got some problem, http code:{$info['http_code']}, {$url}");
			return false;
		}
		return true;
	}

	/**
	 * 分块文件上传完成
	 * @param int $uploadid 上传号
	 * @param int $partcount
	 * @param int $file_length
	 * @return boolean
	 */
	public function uploadsComplete($uploadid, $partcount) {
		if (! $this->large) {
			return false;
		}
		$url = "http://{$this->zone}/{$this->filename}?uploadid={$uploadid}";
		$headers = array(
		'Date: ' . strftime('%a, %d %b %Y %H:%M:S GMT'),
		'Authorization: ' . generate_token($this->accesskey, $this->access_key_secret, 'POST', $this->bucket, $this->filename, time()),
		'AccessId: test___'
		);
		if ($this->content_type) {
			$headers[] = "Content-Type: {$this->content_type}";
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(compact('partcount')));
		$rt = curl_exec($ch);
		$info = curl_getinfo($ch);

		curl_close($ch);
		if (200 != $info['http_code']) {
            Tool::err("#Upload stage# FAIL: zn got some problem, http code:{$info['http_code']}, {$url}");
			return false;
		}
		return true;
	}

	/**
	 * 查询文件上传状态
	 * @return array
	 */
	public function uploadsStatus() {
		if (! $this->large) {
			return false;
		}
		$url = "http://{$this->bs2host}/{$this->filename}?uploadstatus&bucket={$this->bucket}";
		$headers = array(
		'Date: ' . strftime('%a, %d %b %Y %H:%M:S GMT'),
		'Authorization: ' . generate_token($this->accesskey, $this->access_key_secret, 'GET', $this->bucket, $this->filename, time()),
		'AccessId: test___'
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$xml = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		if(false === $xml) {
			error_log("#Upload stage# FAIL: zn got some problem, {$url}");
			return false;
		}
		if (200 != $info['http_code']) {
			error_log("#Upload stage# FAIL: zn got some problem, http code:{$info['http_code']}, {$url}");
			return false;
		}
		$res = @simplexml_load_string($xml);
		if (! is_object($res)) {
			error_log("#Upload stage# FAIL: zn got some problem, response:{$xml}, {$url}");
			return false;
		}
		$res = (array)$res;
		return $res;
	}

	/**
	 * 获得上传文件的信息
	 * @return array
	 */
	public function getFileInfo() {
		if (! is_object($this->fileinfo)) {
			$this->fileinfo = stat($this->localfile);
		}
		return $this->fileinfo;
	}

	/**
	 * 分块上传文件
	 * @param int $block_size
	 * @return boolean
	 */
	public function uploadsFile($block_size = 0) {
		if (! $this->large || ! $this->filename) {
			return false;
		}
		if ($block_size <= 0) {
			$block_size = self::BS2_UPLOADS_DEFAULT_BLOCK_SIZE;
		}
		$initinfo = $this->uploadsInit();

		if (! $initinfo['uploadid']) {
			return false;
		}
		// if (! is_file($this->localfile)) {
		// 	return false;
		// }
		$f = fopen($this->localfile, 'r');
		if (!$f) {
			return false;
		}
		$partnumber = 0;
		while (! feof($f)) {
			$data = fread($f, $block_size);
			for ($i = 0; $i < self::REPEAT_TIMES; $i ++) {
				if ($this->uploads($initinfo['uploadid'], $partnumber, $data)) {
					break;
				}
			}
			if ($i >= self::REPEAT_TIMES) {
				Tool::err("#Upload stage# FAIL: zn got some problem, uploads in {$partnumber}");
				return false;
			}
			$partnumber ++;
		}
		fclose($f);
		$partcount = $partnumber;
		$fileinfo = $this->getFileInfo();
		if (!$this->uploadsComplete($initinfo['uploadid'], $partcount)) {
            Tool::err("#Upload stage# FAIL: zn got some problem, uploads not complete");
			return false;
		}
		return true;
	}

	/**
	 * 上传文件，不分块
	 * @return boolean
	 */
	public function uploadFile() {
		if ($this->large || ! $this->filename) {
			return false;
		}
		$url = "http://{$this->bucket}.{$this->bs2host}/{$this->filename}";
		$headers = array(
		'Date: ' . strftime('%a, %d %b %Y %H:%M:S GMT'),
		'Authorization: ' . generate_token($this->accesskey, $this->access_key_secret, 'PUT', $this->bucket, $this->filename, time()),
		'AccessId: test___',
		'X-HTTP-Method-Override: PUT'
		);
		if (preg_match('/^http(s)?:\/\//ui', $this->localfile) > 0) {//是URL
			$tmp_headers = getUrlHeaders($this->localfile, $this->proxy);
			if ($tmp_headers['http_code'] != 200 || preg_match('/^(image|video)\//ui', $tmp_headers['content_type']) <= 0) {//只允许抓图片或视频
				return false;
			}

			ob_start();
			
			$ch = curl_init($this->localfile);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11');

			if ($this->proxy) {
				$proxyInfo = explode(':', $this->proxy);
				curl_setopt($ch, CURLOPT_PROXY, $proxyInfo[0]);
				curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo[1]); //代理服务器端口
				curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			}
			curl_exec($ch);
			curl_close($ch);


			$data = ob_get_contents();
			ob_end_clean();

			$headers[] = "Content-Type: {$tmp_headers['content_type']}";
		} else {
			$data = @file_get_contents($this->localfile);
			if ($this->content_type) {
				$headers[] = "Content-Type: {$this->content_type}";
			}
		}
		if (! $data) {
			return false;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		if (200 != $info['http_code']) {
			error_log("#Upload stage# FAIL: zn got some problem, http code:{$info['http_code']}, {$url}");
			return false;
		}
		return true;
	}

	/**
	 * 删除文件
	 */
	public function delFile(){
		if (!$this->filename) {
			return false;
		}
		$url = "http://{$this->bucket}.{$this->bs2delhost}/{$this->filename}";
		$headers = array(
		'Date: ' . strftime('%a, %d %b %Y %H:%M:S GMT'),
		'Authorization: ' . generate_token($this->accesskey, $this->access_key_secret, 'DELETE', $this->bucket, $this->filename, time()),
		'AccessId: test___',
		'X-HTTP-Method-Override: DELETE'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if (200 != $info['http_code']) {
			error_log("#Upload stage# FAIL: zn got some problem, http code:{$info['http_code']}, {$url}");
			return false;
		}
		return true;			
	}

	/**
	 * 获得文件下载URL
	 * @return string
	 */
	public function getDownloadUrl() {
//			return "http://{$this->bucket}." . ($cdn ? str_replace('bs2dl', 'bs2cdn', $this->bs2dlhost) : $this->bs2dlhost) . "/{$this->filename}";
			return "http://{$this->bucket}.{$this->bs2dlhost}/{$this->filename}";
	}

	/**
	 * 获取图片url
	 */
	public function getImgUrl(){
		return "http://screenshot.dwstatic.com/{$this->bucket}/{$this->filename}";
	}	
}