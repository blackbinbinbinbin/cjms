<?php

/**
 * 响应类
 * Copyright(c) 2014 by benzhan. All rights reserved
 * To contact the author write to {@link mailto:zhanchaojiang@qq.com}
 * 
 * @author benzhan
 * @version Jul 19, 2014
 */
class Response {
    public static $ver = '1.0';
    private static $code = CODE_SUCCESS;
    public static $debugMsg;

    /**
     * 处理成功的响应
     * @param mixed $data
     */
    public static function success($data = [], $msg = null, $debugMsg = null, $defaultHeader = false, $gzip = false) {
        $code = CODE_SUCCESS;
        self::$debugMsg = $debugMsg;
        
        $response = array(
            'result' => 1,
            'code' => $code,
        );
        
        if (!$msg) {
            $msg = $GLOBALS['code_map'][$code];
        }
        
        if (DEBUG && $debugMsg) {
            $msg = $msg . " 【调试信息:{$debugMsg}】";
        }
        
        $response['msg'] = $msg;
        $response['data'] = $data;

        self::exitData($response, $defaultHeader, null, $gzip);
    }

    /**
     * 处理失败的响应
     * @param int $code
     * @param string $msg
     * @param string $debugMsg
     * @param mixed $extData
     */
    public static function error($code, $msg = null, $debugMsg = null, $extData = null, $defaultHeader = false, $gzip = false) {
        self::$debugMsg = $debugMsg;
        
        if (!$msg) {
            $msg = $GLOBALS['code_map'][$code];
        }
        
        if (DEBUG && $debugMsg) {
            $msg = $msg . " 【调试信息:{$debugMsg}】";
        }
        
        $response = array(
            'result' => 0 ,'code' => $code ,'msg' => $msg
        );
        
        if (!empty($extData)) {
            $response = array_merge($response, $extData);
        }

        self::exitData($response, $defaultHeader, null, $gzip);
    }

    /**
     * 退出脚本，返回数据
     * @param array $response
     */
    public static function exitData(array $response, $defaultHeader = false, $debugMsg = '', $gzip = false) {
        self::$code = (int) $response['code'];
        $json = json_encode($response, JSON_UNESCAPED_UNICODE);
        
        // 记录调用接口，为doc使用
        DocController::logSelfData(self::$code, $json);

        if (!$defaultHeader) {
//            header('Content-Type: text/json;charset='. DEFAULT_CHARSET);
            header('Content-Type: text/json');
        }

        self::exitMsg($json, self::$code, $debugMsg, $gzip);
    }
    
    /**
     * 退出脚本，返回数据
     */
    public static function exitMsg($json, $code = CODE_SUCCESS, $debugMsg = '', $gzip = false) {
        // 这个是没有参数的文档情况
        if ($GLOBALS['__getRules']) {
            $GLOBALS['__getRules'] = false;
            $args = ['__getRules' => true];
            Param::getRule([], $args);
        }

        $orgin = $_SERVER['HTTP_ORIGIN'];
        $orgin || $orgin = '*';
        
        header('Access-Control-Allow-Origin: ' . $orgin);
        header('Access-Control-Allow-Methods: GET, POST');
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Accept-Type");
    
        if (!DEBUG) {
            ob_clean();
        }

        if ($debugMsg) {
            $msg = $json . " 【调试信息:{$debugMsg}】";
        } else {
            $msg = $json;
        }

        CallLog::logSelfCall($code, $msg);

        if ($gzip) {
            $json = Tool::ob_gzip($json);
        }

        //jquery jsonp callback处理
        if ($_REQUEST['callback'] && preg_match('/^jQuery(\d+)_(\d+)$/', $_REQUEST['callback'])) {
            exit("{$_REQUEST['callback']}($json)");
        } else {
            exit($json);
        }
    }

    public static function move301Msg($url, $msg = '正在跳转...') {
        header('HTTP/1.1 301 Moved Permanently');
        self::move302Msg($url, $msg);
    }

    public static function move302Msg($url, $msg = '正在跳转...') {
        header('Location: ' . $url);
        $debugMsg = "Host:{$_SERVER['HTTP_HOST']}, redirect:{$url}";
        Response::exitMsg($msg, CODE_301_REDIRECT, $debugMsg);
    }

}

//end of script
