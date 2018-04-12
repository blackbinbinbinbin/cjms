<?php
/**
 * 模板引擎
 *
 * Copyright(c) 2005-2008 by 陈毅鑫(深空). All rights reserved
 *
 * To contact the author write to {@link mailto:shenkong@php.net}
 *
 * @author 陈毅鑫(深空)
 * @version $Id: Template.class.php 1687 2008-07-07 01:16:07Z skchen $
 * @package common
 */

class Template {
    protected static $obj;

    public $vars;
    public $includeFiles;
    public $includeFile;
    public $templates;
    public $template;
    public $contents;
    protected $_content;
    protected $_contents;
    protected $_path;
    public $dir = 'views';

    protected function __construct() {
        $this->vars = array();
    }

    /**
     * 初始化模板引擎
     *
     * @return Template 模板引擎对象
     */
    public static function &init() {
        if (is_null(self::$obj)) {
            self::$obj = new Template();
        }
        return self::$obj;
    }

    /**
     * 注册模板变量
     *
     * 注册模板变量后在模板里就可以直接使用该变量,注册与被注册变量名不一定要一样
     * 如:$template->assign('var', $value);
     * 意思是将当前变量$value注册成模板变量var,在模板里就可以直接调用$val
     *
     * @param string|array $var 注册到模板里的变量名的字符串形式,不包含$
     * @param mixed $value 需要注册的变量
     */
    public function assign($var, $value = null) {
        if (is_array($var)) {
            foreach ($var as $key => $val) {
                $this->vars[$key] = $val;
            }
        } else {
            $this->vars[$var] = $value;
        }
    }
    
    
    private function getDefaultTemplate() {
        $traceList = debug_backtrace();
        foreach ($traceList as $trace) {
            if ($trace['file'] !=  ROOT_PATH .'lib' . DIRECTORY_SEPARATOR . 'Template.class.php') {
                //将.php换成.html
                $temp = substr($trace['file'], 0, -4);
                $lastPost = strrpos($temp, DIRECTORY_SEPARATOR);
                $fileName = substr($temp, $lastPost + 1) . '.html';
                $path = substr($temp, 0, $lastPost) . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $fileName;
                break;
            }
        }
        
        return $path;
    }

    public static function replaceArrImg(&$arr, $key) {
        foreach ($arr as $i => $data) {
            $arr[$i][$key] = self::replaceImg($data[$key]);
        }
    }

    public static function replaceImg($content) {
        $scheme = 'http';
        if (ENV == ENV_FORMAL || ENV == ENV_NEW) {
            $domain = 'w2.dwstatic.com';
            $ua = $_SERVER["HTTP_USER_AGENT"];
            if (strpos($ua,'MSIE ') === false) {
                $scheme = 'https';
            }
        } else {
//            $domain = 'new-w2.dwstatic.com';
            $domain = 'w2.dwstatic.com';
        }

        $content = str_replace('http://image.yy.com/', "{$scheme}://{$domain}/yy/", $content);
        $content = str_replace('http://screenshot.dwstatic.com/', "{$scheme}://{$domain}/yy/", $content);

        $content = str_replace('http://img.dwstatic.com/', "{$scheme}://{$domain}/img_dwstatic/", $content);
        $content = str_replace('http://vimg.dwstatic.com/', "{$scheme}://{$domain}/vimg_dwstatic/", $content);

//        $pattern = "/http:\/\/(\w+).dwstatic.com\/([^'\"\s)]+)/";
//        $whitelist = ['w2', 'w5', 'new-w2', 'new-w5', 'pub', 'mw2'];
//        $content = preg_replace_callback($pattern, function($matches) use ($whitelist, $scheme, $domain) {
//            if (in_array($matches[1], $whitelist)) {
//                return $matches[0];
//            } else {
//                return "{$scheme}://{$domain}/{$matches[1]}_dwstatic/{$matches[2]}";
//            }
//        }, $content);

        $supportWebp = strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') || $_COOKIE['_webp'];
        if ($supportWebp) {
            if (!$_COOKIE['_webp']) {
                setcookie('_webp', 1);
            }

            $reg = "/(\/\/{$domain}\/[^\?\'\"]+\?imageview\/[^\'\"\s\)]+)([\'\"\s\)])/";
            $content = preg_replace($reg,'$1/format/webp$2', $content);

            $reg = "/(\/\/{$domain}\/[^\?\s\'\"\)]+)([\'\"\s\)])/";
            $content = preg_replace($reg,'$1?imageview/format/webp$2', $content);
        }

//        var_dump($content);exit;

        return $content;
    }

    /**
     * 解析模板文件
     *
     * 解析模板,并将变量植入模板,解析完后返回字符串结果
     *
     * @param unknown_type $templates
     * @return unknown
     */
    public function fetch($templates) {
        if (is_array($templates)) {
            $this->templates = $templates;
        } else {
            $this->templates = [$templates];
        }

        extract($this->vars);

        $this->_contents = '';
        if ($this->templates) {
            foreach ($this->templates as $this->template) {
                $this->_path = $this->getPath($this->template);

                ob_end_clean();
                ob_start();
                require $this->_path;
                $this->_content = ob_get_contents();
                ob_end_clean();
                ob_start();
                $this->_contents .= $this->_content;
                $this->contents[$this->template] = $this->_content;
            }
        } else {
            $this->_path = $this->getDefaultTemplate();

            ob_end_clean();
            ob_start();
            require $this->_path;
            $this->_content = ob_get_contents();
            ob_end_clean();
            ob_start();
            $this->_contents .= $this->_content;
            $this->contents[$this->template] = $this->_content;
        }

        if ($_GET['doc'] != 'class' && $_GET['doc'] != 'module') {
            // 记录调用接口，为doc使用
            DocController::logSelfData(CODE_SUCCESS, $this->vars);
        }

        if (!$_GET['__orgin']) {
            $this->_contents = self::replaceImg($this->_contents);
        }

        return $this->_contents;
    }

    public function getPath($path) {
        return ROOT_PATH . $this->dir . DIRECTORY_SEPARATOR . $path . ".html";
    }

    public function display($templates = array(), $code = CODE_SUCCESS) {
//        if (!is_array($templates)) {
//            $templates = func_get_args();
//        }

        $html = $this->fetch($templates);
        // 支持jQuery的jsonp
        if ($_GET['_'] && preg_match('/^jQuery(\d+)_(\d+)$/', $_GET['callback'])) {
            $html = json_encode($html);
            Response::exitMsg($html, $code);
        } else {
            Response::exitMsg($html, $code);
        }
    }
}

/**
 * 包含模板
 *
 * 当你需要在主模板文件里(有些模板引擎称之为layout布局模板,其实不是所有模板都是布局)
 * 再包含其他公共模板的时候,使用该函数进行包含,则所有已注册的变量均可在被包含文件里使
 * 用,貌似支持多层嵌套,没有测试过,参数可以使用数组,也可以使用多个参数,如:
 * <?=includeFile('user.header', 'user.main', 'user.footer')?> 或者
 * <?=includeFile(array('user.header', 'user.main', 'user.footer'))?>
 *
 * @param string|array $filename 模板名(module.templateName形式)
 */
function includeFile($templates) {
    $template = Template::init();
    if (is_array($templates)) {
        $template->includeFiles = $templates;
    } else {
        $template->includeFiles = func_get_args();
    }
    extract($template->vars);
    foreach ($template->includeFiles as $template->includeFile) {
        require $template->getPath($template->includeFile);
    }
}

//end of script
