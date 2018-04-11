<?php
/**
 * 讯代理爬虫调用接口
 * @author XianDa
 */

class SpiderController extends Controller {

    public function actionSetWhiteList(){
        $rule = [
            'ip' => ['string', 'nullable' => true, 'desc' => 'ip']
        ];
        Param::checkParam2($rule, $args);
        $ip = $args['ip'];
        $objSpiderProxy = new SpiderProxy();
        $ret = $objSpiderProxy->_setWhiteList($ip);
        return $ret;
    }

    public function actionIpList($args) {
        $rule = [
            'free' => ['int', 'nullable' => true, 'desc' => '是否免费'],
            'count' => ['int', 'nullable' => true, 'default' => 3, 'desc' => '请求数量'],
            'flush' => ['int', 'nullable' => true, 'desc' => '是否主动刷新proxy'],
        ];
        Param::checkParam2($rule, $args);

        $objSpiderProxy = new SpiderProxy();
        $proxyList = $objSpiderProxy->_getProxyPool();
        return $proxyList;
    }

    public function actionBestProxy($args) {
        $rule = [
            'domain' => ['string', 'desc' => '域名'],
            'num' => ['int', 'nullable' => true, 'default' => -1, 'desc' => '提取量'],
        ];
        Param::checkParam2($rule, $args);


        $num = (int) $args['num'];
        $objSpiderProxy = new SpiderProxy();
        $proxy = $objSpiderProxy->getBestProxy($args['domain'], $num);
        $data['proxyList'] = $proxy;

        return $data;
    }

    public function actionReportProxy($args) {
        $rule = [
            'domain' => ['string', 'desc' => '域名'],
            'proxy' => ['string', 'desc' => '代理IP地址,eg: 192.168.1.1:30'],
            'score' => ['int', 'desc' => '分数，可以为负数'],
        ];
        Param::checkParam2($rule, $args);

        $objProxyStatic = new ProxyStatic();
        $newScore = $objProxyStatic->changeScore($args['domain'], $args['proxy'], $args['score']);

        return $newScore;
    }

    /**
     * 视频信息
     */
    public function actionVideoInfo($args) {
        $rule = [
            'url_md5' => ['string', 'desc' => '视频地址md5'],
        ];
        Param::checkParam2($rule, $args);

        $objTable = new TableHelper('video_save_log', 'crawl');
        $row = $objTable->getRow($args);

        if ($row) {
            $data = [];
            $data['title'] = $row['title'];
            $data['cover'] = '';
            $data['text'] = '';
            $data['segments'][] = $row['video_url'];

            Response::exitData($data);
        } else {
            Response::exitMsg('{}');
        }
    }

    /**
     * 视频事件通知（视频平台的通知）
     */
    public function actionVideoEvent($args) {
        $rule = [
            'eventCode' => ['string', 'desc' => '事件名'],
            'jsonData' => ['string', 'desc' => '回调的数据，json格式, 若是视频则包含vid和channel'],
            'sign' => ['string', 'desc' => '签名,格式为md5(eventCode+jsonData+密钥)'],
        ];
        Param::checkParam2($rule, $args);

        $appKey = 'duowan~!@#$%^&*';
        $sign = md5("{$args['eventCode']}{$args['jsonData']}{$appKey}");
        if ($sign != $args['sign']) {
            Response::error(CODE_SIGN_ERROR, null, "sign:{$args['sign']}, sign2:{{$sign}}");
        }

        $ret = ['code' => 1];
        $data = json_decode($args['jsonData'], true);
        if (!$data) {
            $ret['errMsg'] = 'jsonData错误';
            Response::exitMsg(json_encode($ret), CODE_PARAM_ERROR);
        }

        // 其他频道的事件不处理
        if ($data['channel'] != 'spidervideo') {
            Response::exitMsg(json_encode($ret), CODE_NO_PERMITION);
        }

        $objTable = new TableHelper('video_save_log', 'crawl');
        $where = ['vid' => $data['vid']];
        $row = $objTable->getRow($where);
        if (!$row) {
            $ret['errMsg'] = '不存在该vid:' . $data['vid'];
            Response::exitMsg(json_encode($ret), CODE_PARAM_ERROR);
        }

        $newData = ['state' => $args['eventCode']];
        $objTable->updateObject($newData, $where);

        if ($args['eventCode'] == 'video_delete') {
            $errMsg = $this->_tryRecover($row);
        } else {
            $errMsg = null;
        }

        if ($errMsg) {
            $ret['errMsg'] = $errMsg;
            Response::exitMsg(json_encode($ret), CODE_PARAM_ERROR);
        } else {
            Response::exitMsg(json_encode($ret));
        }
    }

    private function _tryRecover($row) {
        $flag = preg_match('/([^.]+)\.([^:]+):([^:]+):([\w\W]+)/', $row['title'], $matches);
        if (!$flag) {
            return "title异常：{$row['title']}";
        }

        $dbKey = $matches[1];
        $tableName = $matches[2];
        $fieldName = $matches[3];
        $whereStr = $matches[4];
        $where = json_decode($whereStr, true);
        if (!$where) {
            return "where异常：{$whereStr}";
        }

        $objTable = new TableHelper($tableName, $dbKey);
        $count = $objTable->getCount($where);
        if ($count > 1) {
            return "where数量异常，{$whereStr}的查询结果数量：{$count}";
        }

        $curStr = $objTable->getOne($where, ['_field' => $fieldName]);
        if ($curStr == $row['vid']) {
            $newData = [
                $fieldName => $row['video_url']
            ];
        } else {
            $needle = '"' . $row['vid'] . '"';
            $pos = strpos($curStr . '', $needle);
            if ($pos === false) {
                return "找不到needle:{$needle}, curStr:{$curStr}, where:{$whereStr}";
            } else {
                $newData = [
                    $fieldName => str_replace($needle, '"' . $row['video_url'] . '"', $curStr)
                ];
            }
        }

        $objTable->updateObject($newData, $where);
//        return 'newData:' . json_encode($newData) . ', where:' . json_encode($where);
        return null;
    }

    /**
     * 重爬任务
     */
    public function actionRedoTask($args) {
        $rule = [
            'rule_id' => ['string', 'desc' => '规则id'],
        ];
        Param::checkParam2($rule, $args);

        $objCrawl = new TableHelper('task', 'crawl');
        $rule_id = $objCrawl->escape($args['rule_id']);
        $sql = "UPDATE `task` SET next_crawl_time = 0 WHERE rule_id = '{$rule_id}'";
        $flag = $objCrawl->getDb()->update($sql);


        return $flag;
    }
}