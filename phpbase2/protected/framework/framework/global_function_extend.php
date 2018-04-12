<?php

/**
 * 获取用户ip
 * @return bool|string
 */
function getip() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] == $_SERVER['HTTP_X_REAL_IP']) {  //to check ip is pass from proxy
        // web专区代理，同时设置：HTTP_X_FORWARDED_FOR 和 HTTP_X_REAL_IP
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    if (!$ip) {
        $ip = '127.0.0.1';
    }

    return substr($ip, 0, 15);
}


/**
 * 删除并返回某个key的值
 * @author benzhan
 */
function arrayPop(&$array, $key) {
    $value = $array[$key];
    unset($array[$key]);
    return $value;
}

/**
 * 删除并返回某个key的值
 * @author benzhan
 */
function array2Col($array, $key) {
    $col = array();
    foreach ($array as $value) {
        $col[] = $value[$key];
    }
    
    return $col;
}

/**
 * 生成16位的UUID
 */
function uuid16() {
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars,0,16);
    return $uuid;
}

/**
 * 生成UUID
 */
function uuid() {
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars,0,8) . '-';
    $uuid .= substr($chars,8,4) . '-';
    $uuid .= substr($chars,12,4) . '-';
    $uuid .= substr($chars,16,4) . '-';
    $uuid .= substr($chars,20,12);
    return $uuid;
}

/**
 * 从数组中过滤出指定key的子数组<br>
 * 支持arrayFilte(array $arr, array $keys)<br>
 * 支持arrayFilte(array $arr, $key1, $key2, $key3)<br>
 * @author benzhan
 */
function arrayFilter($array, $keys) {
    if (!is_array($array) || empty($array)) {
        return [];
    }

    if (!is_array($keys)) {
        $args = func_get_args();
        //最后一个是数组
        $array = arrayPop($args, 0);
        $keys = $args;
    }
    
    $tData = array();
    foreach ($keys as $key) {
        // if (isset($array[$key])) {
            $tData[$key] = $array[$key];
        // }
    } 
    return $tData;
}

/**
 * 格式化数组为 array(key => value)
 * @author benzhan
 * @param array $array
 * @param string $key
 * @return array:unknown
 */
function arrayFormatKey($array, $key, $valueKey = null) {
    if (!is_array($array) || empty($array)) {
        return [];
    }

    $tData = array();
    foreach ($array as $value) {
        if ($valueKey) {
            $tData[$value[$key]] = $value[$valueKey];
        } else {
            $tData[$value[$key]] = $value;
        }
    }
    
    return $tData;
}

/**
 * 格式化数组为 array(key => [value])
 * @author solu
 * @param array $array
 * @param string $key
 * @return array
 */
function arrayFormatKey2($array, $key, $valueKey = null) {
    if (!is_array($array) || empty($array)) {
        return [];
    }

    $tData = array();
    foreach ($array as $value) {
        if ($valueKey) {
            $tData[$value[$key]][] = $value[$valueKey];
        } else {
            $tData[$value[$key]][] = $value;
        }
    }
    
    return $tData;
}

/**
 * 从数组中过滤元素的空格
 * @author benzhan
 */
function arrayTrim($array) {
    foreach ($array as $key => $value) {
        $array[$key] = trim($value);
    }

    return $array;
}


// 自动包含类
function myAutoload($className) {
    if (class_exists($className, false) || interface_exists($className, false)) {return false;}

    $dirs = array(
        BASE_DIR . 'protected/models',
        BASE_DIR . 'protected/extensions',
        BASE_DIR . 'protected/controllers',
        FRAMEWORK_PATH . 'protected/framework',
        FRAMEWORK_PATH . 'protected/framework/lib',
    );
    
//    $file_exists = false;
    $className = str_replace("_", DIRECTORY_SEPARATOR, $className);
//    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    $pathName = DIRECTORY_SEPARATOR . $className . '.php';
    foreach ($dirs as $dir) {
        $file = $dir . $pathName;
        if (file_exists($file)) {
            require_once $file;
//            $file_exists = true;
            break;
        } 
        // var_dump($file);
    }
}
spl_autoload_register('myAutoload');

/**
 * 全局错误处理
 */
function error_handler ($error_level, $error_message, $file, $line) {
    $EXIT = FALSE;
    switch ($error_level) {
        case E_NOTICE:
        case E_USER_NOTICE:
            $error_type = 'Notice';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $error_type = 'Warning';
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $error_type = 'Fatal Error';
            $EXIT = TRUE;
            break;
        default:
            $error_type = 'Unknown';
            $EXIT = TRUE;
            break;
    }
    
    if ($EXIT) {
        Tool::writeLog($error_message, $error_type, 1, $file, $line);
    }
}
set_error_handler ('error_handler');

/**
 * 全局异常处理
 * @param Throwable $ex
 */
function exception_handler($ex) {
    $debugMsg = $ex->getMessage() . ' in ' . $ex->getFile() . ' on line ' . $ex->getLine();
    $debugMsg .= "\r\n" . $ex->getTraceAsString();
    if ($ex instanceof DB_Exception) {
        Response::error(CODE_DB_ERROR, null, $debugMsg);
    } else if ($ex instanceof  RedisException || $ex instanceof R2m_Exception) {
        Response::error(CODE_REDIS_ERROR, null, $debugMsg);
    } else {
        Response::error(CODE_UNKNOW_ERROT, null, $debugMsg);
    }
}
set_exception_handler('exception_handler');


if( !function_exists('apc_store') ){
    function apc_store(){}
    function apc_add(){}
    function apc_fetch(){}
}

function var_log($msg, $label='notice'){
    Tool::writeLog($msg, $label, 2);
}


if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}


/**
 * 生成url
 * @param string $route controller/action
 * @param array $param 参数
 * @param string $protocol 协议，可以是：''，'http:' 或 'https:'
 * @return string 别名后的地址
 */
function url($route = '', $param = array(), $protocol = '') {
    $site_url = $protocol . '//' . $_SERVER['HTTP_HOST'] . '/';
    return url_relative($route, $param, $site_url);
}


/**
 * 生成相对的url
 * @param string $route controller/action
 * @param array $param 参数
 * @param string $site_url 相对的host
 * @return string 别名后的地址
 */
function url_relative($route = '', $param = array(), $site_url = '/') {
    $rewrite = $GLOBALS['rewrite'];
    if ($GLOBALS['rewrite2']) {
        $rewrite = $GLOBALS['rewrite2'] + $rewrite;
    }
    foreach ($rewrite as $rule => $mapper) {
        if ($route != $mapper) {
            continue;
        }

        $matches = [];
        preg_match_all('/<([^:<>]+)(:[^<>]+)?>/', $rule, $matches);
        if (count($matches[0]) > 0) {
            // 有自定义参数
            foreach ($matches[1] as $i => $keyName) {
                if (!isset($param[$keyName])) {
                    continue(2);
                }
            }

            // 符合有参数的规则，开始替换
            foreach ($matches[0] as $i => $item) {
                $rule = str_replace($item, $param[$matches[1][$i]], $rule);
            }

        }

        return $site_url . $rule;
    }

    if ($param) {
        $route .= "?".http_build_query($param);
    }
    return $site_url . $route;
}

/** 判断是否内网IP */
function fromInternal() {
    $arrInternal = array(
        '183.63.160.184/183.63.160.190',  // 电信
        '58.248.137.145/58.248.137.150',  // 联通
        '183.237.185.75/183.237.185.79',  // 移动
    );

    $remoteIp = getip();
    $remoteIp = ip2long($remoteIp);
    if ($remoteIp != -1) {
        foreach ($arrInternal as $v) {
            list($start, $end) = explode('/', $v);
            if ($remoteIp >= ip2long($start) && $remoteIp <= ip2long($end)) {
                return true;
            }
        }
    }

    return false;
}


if (!function_exists('array_column')) {
    function array_column(array $arr, $key) {
        $data = [];
        foreach ($arr as $v) {
            isset($v[$key]) && $data[] = $v[$key];
        }

        return $data;
    }
}

/**
 * @param array      $array
 * @param int|string $position
 * @param mixed      $insert
 */
if (!function_exists('array_insert')) {
    function array_insert(&$array, $position, $insert) {
        if (is_int($position)) {
            array_splice($array, $position, 0, $insert);
        } else {
            $pos   = array_search($position, array_keys($array));
            $array = array_merge(
              array_slice($array, 0, $pos),
              $insert,
              array_slice($array, $pos)
            );
        }
    }
}


/**
 * Get Headers function
 * @param string $url
 * @param string $proxy ip代理
 * @return array
 */
function getUrlHeaders($url, $proxy = '') {
    $ch = curl_init($url);
    curl_setopt( $ch, CURLOPT_NOBODY, true );
    curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11');
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
    curl_setopt( $ch, CURLOPT_HEADER, false );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_MAXREDIRS, 3 );
    if ($proxy) {
        $proxyInfo = explode(':', $proxy);
        curl_setopt($ch, CURLOPT_PROXY, $proxyInfo[0]);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo[1]); //代理服务器端口
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    }

    curl_exec( $ch );
    $headers = curl_getinfo( $ch );
    curl_close( $ch );

    return $headers;
}


/**
 * 开发人员，看文档用
 */
if (DEBUG && !$GLOBALS['developers']) {
    $GLOBALS['developers'] = [
        'dw_zhanchaojiang',
        'dw_luwenbing',
        'dw_linxianda',
        'dw_xubin1',
        'dw_chenjunqiang',
        'dw_chenyan3',
        'dw_kangzhihong',
        'dw_wukunlin',
	    'dw_zhongxiaofa',
	    'dw_fangkunbiao',
	    'dw_luxiaoming',
	    'dw_chenyaoqiang',
	    'dw_yinsong',
	    'dw_liyanjie',
        'dw_lishaoqi1',
        'dw_lishen1',
	    'dw_dengmingyu',
    ];
}
