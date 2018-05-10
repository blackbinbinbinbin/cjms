<?php

/**
 * 基础Controller
 * @ignore
 * @author benzhan
 */
class BaseController extends Controller {
    
    protected $loginActions = array(); // 需要登陆的吐页面action
    protected $onlyCheckLoginActions = array(); // 只需检查登录态，不需登录的action
    protected $ajaxLoginActions = array(); // 需要登陆的ajax action
    protected $showLogin = false;

    /**
     * 构造函数
     * @param string $initTpl 是否初始化模板
     */
    public function __construct($initTpl = false) {
        parent::__construct($initTpl);

        $loginActions = array_map('strtolower', $this->loginActions);
        $ajaxLoginActions = array_map('strtolower', $this->ajaxLoginActions);
        $onlyCheckLoginActions = array_map('strtolower', $this->onlyCheckLoginActions);
        // 如果是需要登录态的action
        $actionName = strtolower(ACTION_NAME);
        $flag = in_array($actionName, $loginActions);
        $ajaxFlag = in_array($actionName, $ajaxLoginActions);
        $checkFlag = in_array($actionName, $onlyCheckLoginActions);
        $isDoc = $_GET['doc'] === 'func';
        
        if (($flag || $ajaxFlag || $checkFlag) && !$isDoc) {
            if (!User::getUserId()) {
                if ($ajaxFlag) {
                    Response::error(CODE_USER_LOGIN_FAIL);
                } elseif($flag) {
                    $this->showLogin = true;
                }
            } 
        }
    }

    /**
     * 检查Web的参数，失败就跳转404
     * @param $rules
     * @param $args
     * @author benzhan
     */
    public function checkWebParam($rules, &$args) {
        if ($_GET['_from'] == 'json') {
            Param::checkParam($rules, $args);
        } else {
            $result = Param::checkParam($rules, $args, false);
            if (!$result['result']) {
                $this->go404($result['msg']);
            }
        }
    }


    /**
     * 效果同checkWebParam一样，但增加了删除多余字段
     * @author benzhan
     * @param array $rules
     * @param array $args
     * @return Ambigous <multitype:, multitype:boolean , unknown>
     */
    public function checkWebParam2($rules, &$args) {
        if ($_GET['_from'] == 'json') {
            Param::checkParam2($rules, $args);
        } else {
            $result = Param::checkParam2($rules, $args, false);
            if (!$result['result']) {
                $this->go404($result['msg']);
            }
        }
    }

    
    /**
     * 根据参数选择输出
     * @param string $page
     */
    public function displayMain($page, $data = []) {
        if ($_REQUEST['_from'] == 'json') {
            Response::success($data);
        }

        $is_ajax = $_GET['_from'] == 'ajax';
        if ($is_ajax) {
            $html = $this->tpl->fetch($page);
            $title = $this->tpl->vars['seoTdk']['title'];
            if ($title) {
                $html .= "<script>document.title='{$title}';</script>";
            }
        } else {
            // 公用头部数据 
            $headerData = Task_Helper::getHeaderData($_REQUEST['shop_id'], $_COOKIE['yyuid']);
            $this->tpl->assign('header', $headerData);

            $navHtml = Task_Helper::getNavHtml($this->tpl);
            $this->tpl->assign('page', $page);
            $this->tpl->assign('navHtml', $navHtml);
            $html = $this->tpl->fetch('main');
        }

        if ($this->showLogin) {
            $html .= '<script>try{dwUDBProxy.login("")}catch(e){}</script>';
        }

        if (!DEBUG) {
            ob_clean();
        }

        Response::exitMsg($html, CODE_SUCCESS, '', true);
    }

    // 跳转404
    public function go404($msg = '正在跳转中...', $code = CODE_PARAM_ERROR) {
//        header('Location: /404.html');
        Response::exitMsg($msg, CODE_PARAM_ERROR);
    }

}
