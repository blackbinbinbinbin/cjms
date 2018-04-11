<?php

/**
 * 开放的接口
 * @author benzhan
 */
class OpenController extends Controller {

    private function _getUserInfo() {
        if ($_SESSION['username']) {
            $objUser = new TableHelper('cUser');
            $where = ['userName' => $_SESSION['username']];
            $keyWord = [
                '_field' => 'userId,userName,extraInfo',
            ];

            return $objUser->getRow($where, $keyWord);
        }
    }

    /**
     * 检验用户
     * @author benzhan
     * @param string userId 用户的id
     */
    function actionCheckUser($args) {
        $rule = [
            'userId' => 'string',
        ];

        Param::checkParam2($rule, $args);

        $data = $this->_getUserInfo();
        if (!$data) {
            Response::error(CODE_NO_PERMITION);
        }

        return $data;
    }

    /**
     * 检验权限1
     * @author benzhan
     * @param string url 配置在管理平台的url
     * @param string userId 用户的id
     */
    function actionCheckRight($args) {
        $rule = [
            'url' => 'string',
            'userId' => 'string',
        ];
        Param::checkParam($rule, $args);
        $flag = Permission::checkUrlRight($args['url'], $args['userId']);
        if (!$flag) {
            Response::error(CODE_NO_PERMITION);
        }

        $data = $this->_getUserInfo();

        return $data;
    }

    /**
     * 检验权限2
     * @author benzhan
     * @param string nodeId 节点id
     * @param string userId 用户的id
     */
    function actionCheckRight2($args) {
        $rule = [
            'nodeId' => 'string',
            'userId' => 'string',
        ];
        Param::checkParam($rule, $args);
        $flag = Permission::checkRight($args['nodeId'], $args['userId']);
        if (!$flag) {
            Response::error(CODE_NO_PERMITION);
        }

        $data = $this->_getUserInfo();

        return $data;
    }

    /**
     * 检验偶家账号权限
     *
     * @param  string url 配置在管理平台的url
     * @param  string adminAppId 后台项目的appid
     * @param  string ouid ouid
     * @param  string otoken otoken
     * @author  hawklim
     */
    function actionCheckOujRight($args) {
        $rule = [
            'url' => 'string',
            'adminAppId' => 'string',
            'ouid' => 'string',
            'otoken' => 'string',
        ];
        Param::checkParam($rule, $args);

        $_COOKIE = $args;

        $flag = Login::checkLogin();
        if ($flag == 1) {
            $flag = Permission::checkOujUrlRight($args['url'], $args['adminAppId']);
            if (!$flag) {
                Response::error(CODE_NO_PERMITION);
            }

            $data = $this->_getUserInfo();
            Response::success($data);
        } else if ($flag == -1) {
            Response::error(CODE_NO_PERMITION);
        } else {
            Response::error(CODE_USER_LOGIN_FAIL);
        }

    }

    /**
     * 检验多玩用户权限
     * @author benzhan
     */
    function actionCheckDwRight($args) {
        $rule = [
            'url' => 'string',
            'adminAppId' => 'string',
            'osinfo' => 'string',
            'password' => 'string',
            'yyuid' => 'string',
            'udb_oar' => 'string',
        ];
        Param::checkParam($rule, $args);

        $_COOKIE = $args;

        $flag = Login::checkLogin(true);
        if ($flag == 1) {
            $flag = Permission::checkOujUrlRight($args['url'], $args['adminAppId']);
            if (!$flag) {
                Response::error(CODE_NO_PERMITION);
            }

            $data = $this->_getUserInfo();
            Response::success($data);
        } else if ($flag == -1) {
            Response::error(CODE_NO_PERMITION);
        } else {
            Response::error(CODE_USER_LOGIN_FAIL);
        }
    }

    /**
     * 检验偶家用户
     * @author benzhan
     */
    function actionCheckOujUser($args) {
        $rule = [
            'ouid' => 'string',
            'otoken' => 'string',
        ];

        Param::checkParam($rule, $args);

        $_COOKIE = $args;
        $flag = Login::checkLogin();
        if ($flag == 1) {
            $data = $this->_getUserInfo();
            $data['username'] = $_SESSION['username'];

            Response::success($data);
        } else if ($flag == -1) {
            Response::error(CODE_NO_PERMITION);
        } else {
            Response::error(CODE_USER_LOGIN_FAIL);
        }
    }


    /**
     * 检验二次密码
     *
     * @param  string url 配置在管理平台的url
     * @param  string adminAppId 后台项目的appid
     * @param  string ouid ouid
     * @param  string otoken otoken
     * @author  hawklim
     */
    // function actionCheckAnotherPwd($args) {
    //     $rule = array(
    //         'url' => 'string',
    //         'adminAppId' => 'string',
    //         'ouid' => 'string',
    //         'otoken' => 'string',
    //     );
    //     Param::checkParam($rule, $args);

    //     $_COOKIE = $args;

    //     $appid = $args['adminAppId'];
    //     $url = $args['url'];
    //     $path = trim(parse_url(urldecode($url), PHP_URL_PATH), '/');
    //     $pathArr = explode('/', $path);
    //     $moduleName = $pathArr[0];
    //     if (!$moduleName) {
    //         Response::error(CODE_PARAM_ERROR, null, 'moduleName is not valid');
    //     }

    //     $objCMenuNode = new CMenuNode();
    //     $node = $objCMenuNode->objTable->getRow(compact('appid', 'moduleName'));

    //     Permission::checkAnotherPwd($node['nodeId']);
    //     Response::success();
    // }

    /**
     * 增加操作记录，需要签名
     * @author benzhan
     */
    function actionAddOperationLog($args) {
        $rule = [
            'tableId' => ['string', 'desc' => 'DIY报表的id'],
            'userId' => ['string', 'desc' => '用户名，可用yyuid'],
            'userName' => ['string', 'desc' => '用户名，可用多玩通行证'],
            'operType' => ['string', 'desc' => '操作类型（中文），例如：添加、修改、删除等'],
            'operDesc' => ['string', 'desc' => '操作描述'],
            'operSql' => ['string', 'nullable' => true, 'desc' => '操作的sql'],
            'rollbackSql' => ['string', 'nullable' => true, 'desc' => '回滚的sql'],
            'app_id' => ['string', 'desc' => '应用id'],
            'sign' => ['string', 'desc' => '签名'],
        ];

        Param::checkParam2($rule, $args);

//        $sign2 = arrayPop($args, 'sign');
//        $sign = ThirdApi::getSign($args, false);
//        if ($sign2 != $sign) {
//            Response::error(CODE_SIGN_ERROR, null, "args sign:{$sign2}, real sign:{$sign}");
//        }

        unset($args['app_id']);
        unset($args['sign']);

        $objOperLog = new TableHelper('Cmdb3OperationLog', 'Report');
        $objOperLog->addObject($args);

        Response::success();
    }

}
