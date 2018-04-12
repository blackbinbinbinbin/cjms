<?php

/**
 * 首页
 * @author benzhan
 */
class DefaultController extends BaseController {

    public function __construct() {
        parent::__construct(true);
    }
    
    /**
     * 首页
     * @author benzhan
     */
    function actionIndex() {
        $objUser = new User();
        $keyWord = ['_limit' => 5];
        $datas = $objUser->objR2m->getAll([], $keyWord);
        $this->tpl->assign(compact('datas'));
        $this->tpl->display('index');
    }


    /**
     * 测试
     * @author benzhan
     * @param int user_id 用户id
     */
    function actionTest($args) {
        $rules = [
            'yyuid' => ['int', 'nullable' => true, 'default' => '50013623']
        ];
        Param::checkParam2($rules, $args);

        $objUser = new User();
        $user = $objUser->objR2m->getRow($args);
        if ($user) {
            $flag = $objUser->objR2m->updateObject(['yyuid' => $args['yyuid']], $args);
        } else {
            $flag = 0;
        }

        Response::success($flag);
    }
    
    /**
     * 爬虫规则
     * @author solu
     */
    public function actionRobots($args) {
        if (ENV !== ENV_FORMAL) {
            $str = <<<EOT
User-agent: *
Disallow: /
EOT;
        } else {
            $str = <<<EOT
User-agent: *
Allow:/
EOT;
        }
        header('Content-Type: text/plain');
        Response::exitMsg($str);
    }

}
