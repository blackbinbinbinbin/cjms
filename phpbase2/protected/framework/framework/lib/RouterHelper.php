<?php

/**
 * 路由类
 *
 * @author benzhan
 * @version 1.0 update time: 2014-7-11
 * @package lib
 */
class RouterHelper {
    private $mParts;

    private $_className = null;
    private $_funcName = null;

    /**
     * RouterHelper constructor.
     * @param string $url 重定向的url，默认为：$_SERVER["REQUEST_URI"]
     * @param bool $rewrite 是否要重写路由，默认为true
     */
    function __construct($url = null, $rewrite = true) {
        if ($url === null) {
            $url = $_SERVER["REQUEST_URI"];
        }

        $this->_splitUrl($url);
        if ($rewrite) {
            // 先检测是不是存在的api，不是的话才进行rewrite
            $className = $this->getClassName();
            $funcName = $this->getFunName();

            $needRewrite = true;
            if (class_exists($className)) {
                $actionName = "action{$funcName}";
                if (method_exists($className, $actionName)) {
                    // 存在api，不需要rewrite
                    $this->_className = $className;
                    $this->_funcName = $funcName;
                    $needRewrite = false;
                }
            }

            if ($needRewrite) {
                $this->_className = null;
                $this->_actionName = null;
                $url = $this->urlRewrite($url);
                $this->_splitUrl($url);
            }
        }

    }

    private function _splitUrl($url) {
        $index = strpos($url, '?');
        if ($index !== false) {
            $url = substr($url, 0, $index);
        }
        $this->mParts = preg_split("/\\//", $url, null, PREG_SPLIT_NO_EMPTY);
    }

    function display($classInfos) {
        $cacheKey3 = TYPE_SELF_CALL . ":GLOBAL_COMMENT";
        $objRedis = CallLog::getLogRedis();
        $g_comments = json_encode($objRedis->hGetAll($cacheKey3));

        $template = Template::init();
        $template->assign('classInfos', $classInfos);
        $template->assign('g_comments', $g_comments);

        $template->display('doc');
    }

    function genDoc($className, $funcName) {
        $doc = $_GET['doc'];
        if ($doc) {
	        $userName = $_COOKIE['username'];
	        if (!$userName) {
		        $userInfo = User::getInfo();
		        $userName = $userInfo['username'] ?: $userInfo['user_name'];
	        }

            if (!DEBUG || !in_array($userName, $GLOBALS['developers'])) {
                header("HTTP/1.1 403 Forbidden");
                Response::exitMsg('对不起，无权访问！具体请咨询Ben.');
            }
            
            $objDoc = new Doc();
            switch ($doc) {
                case "module" :
                    $classInfos = $objDoc->getClassInfos(ROOT_PATH . 'controllers/');
                    $this->display($classInfos);
                    break;
                case "class" :
                    $classInfo = $objDoc->getClassInfo($className);
                    $api = str_replace("_", "/", $className);
                    $api = str_replace("Controller", '', $api);
                    $classInfos = array();
                    $api = lcfirst($api);
                    $classInfos[$api] = $classInfo;
                    $this->display($classInfos);
                    break;
                case "func" :
                    $params = $objDoc->getFuncInfo($className, $funcName);
                    
                    $oClass = new $className();
                    if (method_exists($oClass, $funcName)) {
                        $args = array(
                            "__getRules" => true,
                            '__params' => $params
                        );
                        $GLOBALS['__getRules'] = true;
                        $oClass->$funcName($args);
                    } else {
                        Response::error(CODE_NOT_EXIST_INTERFACE, null, "method {$funcName} is not exist.");
                    }
                    break;
            }
            
            exit();
        }
    }

    function getClassName() {
        if ($this->_className) {
            return $this->_className;
        }

        $parts = $this->mParts;
        $len = count($parts);
        $classParts = array();
        for ($i = 0; $i < $len - 1; $i++) {
            $classParts[] = ucfirst($parts[$i]);
            // $classParts[] = $parts[$i];
        }
        
        if ($len >= 3 && $classParts[$len - 2] == $classParts[$len - 3]) {
            unset($classParts[$len - 2]);
        }
        
        $className = join("_", $classParts);
        $className || $className = 'Default';
        
        return $className . 'Controller';
    }

    function getFunName() {
        if ($this->_funcName) {
            return $this->_funcName;
        }

        $parts = $this->mParts;
        $funcName = end($parts);
        $pos = strpos($funcName, '?');
        if ($pos !== false) {
            $funcName = substr($funcName, 0, $pos);
        }
        $funcName || $funcName = 'Index';
        return $funcName;
    }

    function urlRewrite($url) {
        $url = ltrim($url, '/');
        foreach ($GLOBALS['rewrite'] as $rule => $mapper) {
            $matches = [];
            preg_match_all('/<([^:<>]+)(:[^<>]+)?>/', $rule, $matches);
            if (count($matches[0]) > 0) {
                // 有自定义参数
                foreach ($matches[0] as $i => $item) {
                    $reg = $matches[2][$i] ? ltrim($matches[2][$i], ':') : '\w+';
                    $rule = str_replace($item, "({$reg})", $rule);
                }
            }

            $search = array('/', '.', '?');
            $replace   = array('\/', '\.', '\?');
            $rule = str_replace($search, $replace, $rule);

            $matches2 = [];
            preg_match("/^{$rule}/", $url, $matches2);
            if (count($matches2) > 0) {
                // 匹配到规则
                if (count($matches[0]) > 0) {
                    // 有自定义参数
                    foreach ($matches[1] as $i => $keyName) {
                        $_REQUEST[$keyName] = $matches2[$i + 1];
                    }
                }

                return $mapper;
            }
        }

        return $url;
    }

    function error($funcName, $msg) {
        $parts = explode(".", $funcName);
        if (count($parts) >= 2) {
            header('HTTP/1.1 404 Not Found');
            Response::error(CODE_NOT_EXIST_INTERFACE, null, $msg);
            exit();
        } else {
            Response::error(CODE_NOT_EXIST_INTERFACE, null, $msg);
        }
    }


}

//end of script
