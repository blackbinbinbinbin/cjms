<?php

/**
 * 名字服务相关的接口
 * @author benzhan
 */
class NameServiceController extends Controller {

    public function actionNsConfigEnv($args) {
        $rule = [
            'node_name' => ['string', 'desc' => '节点名称'],
            'env' => ['int', 'enum' => [1, 2, 4, 6, 7], 'desc' => '环境：1:测试环境,2:预发布环境,4:正式环境,6:预发布+正式环境,7:所有环境'],
            'targetEnv' => ['string', 'desc' => '目标环境'],
        ];
        Param::checkParam2($rule, $args);

        $targetEnv = $args['targetEnv'];
        if ($targetEnv == 'new') {
            $targetEnv = 2;
            $site_ip = "http://" .CJMS_IP_NEW;
            $site_host = CJMS_HOST_NEW;
        } elseif ($targetEnv == 'formal') {
            $targetEnv = 4;
            $site_ip = "http://" .CJMS_IP_FORM;
            $site_host = CJMS_HOST_FORM;
        } else {
            $targetEnv = 1;
            $site_ip = "http://" . CJMS_IP_DEV;
            $site_host = CJMS_HOST_DEV;
        }

        $objNsNode = new TableHelper('ns_node', 'Web');
        $where = [
            'node_name' => $args['node_name'],
            'env' => $args['env'],
        ];
        $nodeInfo = $objNsNode->getRow($where);
        if (!$nodeInfo) {
            Response::error(CODE_NORMAL_ERROR, "节点：{$args['node_name']}, env：{$args['env']} 没有数据");
        }

        $nodeInfo['env'] = $targetEnv;
        $url = $site_ip . "/nameService/nsNodeConfig";
        $header = "HOST:" . $site_host;

        $dwHttp = new dwHttp();
        $json = $dwHttp->post($url, $nodeInfo, 5, $header);

        $result = [];
        $result['ns_node']['result'] = $json;
        $result['ns_node']['data'] = $nodeInfo;
        if ($json) {
            $json = json_decode($json, true);
            if ($json['code'] == 0 && $nodeInfo['node_type'] == 'hash_table') {
                $objNsTable = new TableHelper('ns_hash_table', 'Web');
                $nsTables = $objNsTable->getAll($where);
                if ($nsTables) {
                    $data = [
                        'node_name' => $args['node_name'],
                        'env' => $targetEnv,
                        'datas' => $nsTables,
                    ];

                    $url = $site_ip . "/nameService/nsHashTable";
                    $json = $dwHttp->post($url, $data, 5, $header);
                    $result['ns_hash_table']['result'] = $json;
                    $result['ns_hash_table']['data'] = $data;
                    if ($json) {
                        $json = json_decode($json, true);
                        if ($json['code'] != 0) {
                            Response::error(CODE_NORMAL_ERROR, $json['msg']);
                        }
                    }
                }
            }else{
                Response::error(CODE_NORMAL_ERROR, $json['msg']);
            }
        }
        return $result;
    }

    /**
     * 名字服务节点数据同步
     * @param $args
     */
    public function actionNsNodeConfig($args) {
        $rule = [
            'node_name' => ['string', 'desc' => '节点名称'],
            'env' => ['int', 'enum' => [1, 2, 4, 6, 7], 'desc' => '环境：1:测试环境,2:预发布环境,4:正式环境,6:预发布+正式环境,7:所有环境'],
            'node_type' => ['string', 'desc' => 'string:字符串,hash_table:哈希表'],
            'object_type' => ['string', 'nullable' => true, 'desc' => '对象类型：db:数据库,redis:Redis配置,r2m:r2m配置,other:其他'],
            'dir_1' => ['string', 'nullable' => true, 'desc' => '一级目录'],
            'dir_2' => ['string', 'nullable' => true, 'desc' => '二级目录'],
            'dir_3' => ['string', 'nullable' => true, 'desc' => '三级目录'],
            'node_value' => ['string', 'nullable' => true,'desc' => '节点值'],
            'value_type' => ['string', 'nullable' => true, '值类型：number:数值,string:字符串,bool:布尔值'],
            'enable' => ['int', '上下架：1:上架,0:下架'],
            'creator' => ['string', 'desc' => '创建人'],
            'create_time' => ['string', 'nullable' => true, 'desc' => '创建时间'],
            'update_time' => ['string', 'nullable' => true, 'desc' => '更新时间'],
            'publish_time' => ['string', 'nullable' => true, 'desc' => '发布时间'],
        ];
        Param::checkParam2($rule, $args);

        $args['create_time'] = $args['update_time'] = NOW;

        $objNsNode = new TableHelper('ns_node', 'Web');

        $nodeInfo = $objNsNode->getRow(['node_name' => $args['node_name'], 'env' => $args['env']]);
        if ($nodeInfo) {
            $objNsNode->updateObject($args, ['node_name' => $args['node_name'], 'env' => $args['env']]);
        } else {
            try {
                $objNsNode->addObject($args);
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                Response::error(CODE_NORMAL_ERROR, $msg);
            }
        }
        Response::success(CODE_SUCCESS);
    }

    /**
     * 名字服务节点hash table 同步
     * @param $args
     */
    public function actionNsHashTable($args){
        $rule = [
            'node_name' => ['string', 'desc' => '节点名称'],
            'env' => ['int', 'enum' => [1, 2, 4, 6, 7], 'desc' => '环境：1:测试环境,2:预发布环境,4:正式环境,6:预发布+正式环境,7:所有环境'],
            'datas' => ['array', 'desc' => 'hash table 数据'],
        ];
        Param::checkParam2($rule, $args);

        $env = (int)$args['env'];
        $where = [
            'node_name' => $args['node_name'],
            'env' => $env,
        ];
        $datas = arrayFormatKey($args['datas'], 'key_name');

        $objNsTable = new TableHelper('ns_hash_table', 'Web');
        $nsTables = $objNsTable->getAll($where);
        if($nsTables){
            foreach ($nsTables as $info){
                $key_name = $info['key_name'];
                if($datas[$key_name]){
                    if($info['key_value'] == $datas[$key_name]['key_value']){
                        unset($datas[$key_name]);
                        continue;
                    }
                    $datas[$key_name]['update_time'] = NOW;
                    $data = $datas[$key_name];
                    $where['key_name'] = $key_name;
                    $objNsTable->updateObject($data, $where);
                    unset($datas[$key_name]);
                }
            }
        }
        if($datas){
            $NsTables = [];
            foreach ($datas as $k => $data) {
                $data['env'] = $env;
                $data['update_time'] = NOW;
                $data['create_time'] = NOW;
                $NsTables[] = $data;
            }
//            $cols = ['node_name', 'env', 'key_name', 'key_value', 'value_type', 'enable', 'creator', 'create_time', 'update_time'];
            try{
                $objNsTable->replaceObjects2($NsTables);
            }catch (Exception  $ex){
                $msg = $ex->getMessage();
                Response::error(CODE_NORMAL_ERROR, $msg);
            }
        }
        Response::success(CODE_SUCCESS, '同步成功');
    }

    /**
     * 获取服务端配置
     * @author ben
     */
    public function actionServerConf($args) {
        $rules = [
            'env' => ['int', 'desc' => '环境']
        ];
        Param::checkParam2($rules, $args);

        $ip = getip();
        $objServer = new TableHelper('ns_server', 'Web');
        $where = [
            'server_ip' => $ip,
            'env' => $args['env'],
        ];

        $row = $objServer->getRow($where);
        if ($row) {
            return $row;
        } else {
            Response::error(CODE_NO_PERMITION, "环境{$args['env']}找不到ip:{$ip}");
        }
    }

    /**
     * 通知发布结果
     * @author ben
     * @param $args
     */
    public function actionNotifyPubResult($args) {
        $rules = [
            'version_id' => ['string', 'desc' => '版本号'],
            'result' => ['string', 'desc' => '执行结果'],
            'phpbase2_ver' => ['string', 'desc' => '框架版本', 'nullable' => true],
        ];
        Param::checkParam2($rules, $args);

        $ip = getip();
        $objServer = new TableHelper('ns_server', 'Web');
        $where1 = ['server_ip' => $ip];
        $row = $objServer->getRow($where1);
        if ($row) {
            $objPubVersion = new TableHelper('ns_pub_version', 'Web');
            $version_id = $args['version_id'];
            if ($version_id == -1) {
                // 心跳包
                $newData = [
                    'last_echo_time' => NOW,
                    'phpbase2_ver' => $args['phpbase2_ver'],
                ];
                $objServer->updateObject($newData, $where1);
            } else {
                $where = ['version_id' => $version_id];
                $version = $objPubVersion->getRow($where);
                if (!$version) {
                    Response::error(CODE_NO_PERMITION, '找不到版本:' . $version);
                }

                $version['pub_result'] .= "{$ip} : {$args['result']}\n";
                // 追加结果
                $objPubVersion->updateObject($version, $where);

                $newData = [
                    'last_version_id' => $version_id,
                    'phpbase2_ver' => $args['phpbase2_ver'],
                ];
                $objServer->updateObject($newData, $where1);
            }
        } else {
            Response::error(CODE_NO_PERMITION, '找不到ip:' . $ip);
        }
    }

    public function actionPublishDialog() {
        $objNode = new TableHelper('ns_node', 'Web');
        $sql = "SELECT DISTINCT dir_1 FROM `ns_node`";
        $keys = $objNode->getDb()->getCol($sql);

        $this->tpl->assign(compact('keys'));
        $this->tpl->display('name_server/publish_dialog');
    }


    /**
     * 检查发布进度
     * @author ben
     * @param $args
     */
    public function actionProcess($args) {
        $rules = [
            'server_ip' => ['string', 'desc' => 'ip列表'],
            'env' => ['int', 'desc' => '环境'],
            'version_id' => ['int', 'desc' => '版本id'],
        ];
        Param::checkParam2($rules, $args);

        $ips = explode(',', $args['server_ip']);
        $objServer = new TableHelper('ns_server', 'Web');
        $datas = $objServer->getAll([
            'server_ip' => $ips,
            'env' => $args['env']
        ]);

        $totalFlag = true;
        $total = count($datas);
        $finish = 0;
        foreach ($datas as $i => $data) {
            $flag = $data['last_version_id'] == $args['version_id'];
            if ($flag) {
                $finish++;
            }

            $datas[$i]['result'] = $flag;
            $totalFlag = $totalFlag && $flag;
        }

        $this->tpl->assign(compact('datas', 'totalFlag', 'total', 'finish'));
        $this->tpl->display('name_server/publish_process');
    }

    /**
     * 通知发布结果
     * @author ben
     * @param $args
     */
    public function actionPublish($args) {
        $rules = [
            'key' => ['string', 'desc' => '一级目录'],
            'env' => ['int', 'desc' => '环境'],
            'version' => ['int', 'nullable' => true, 'base' => '回滚指定版本'],
        ];
        Param::checkParam2($rules, $args);

        $key = $args['key'];
        $env = $args['env'];
        $version = $args['version'] ? (int)$args['version'] : '';

        $objRedis = dwRedis::init('name_serv');
        if (!$version) { // 判断是否 返回指定版本
            $objNode = new TableHelper('ns_node', 'Web');
            $_field = 'node_name, node_type, value_type, node_value, node_tips';
            $where1 = [
                'dir_1' => $key,
                'env' => [$env, 7],
                'enable' => 1
            ];
            $datas = $objNode->getAll($where1, compact('_field'));
            if (!$datas) {
                Response::error(CODE_PARAM_ERROR, '没有数据');
            }

            $hashKeys = [];
            foreach ($datas as $data) {
                if ($data['node_type'] == 'hash_table') {
                    $hashKeys[] = $data['node_name'];
                }
            }

            $objHashTable = new TableHelper('ns_hash_table', 'Web');
            $where2 = [
                'node_name' => $hashKeys,
                'env' => [$env, 7],
                'enable' => 1
            ];
            $_field = 'node_name,key_name,key_value,value_type';
            $hashDatas = $objHashTable->getAll($where2, compact('_field'));
            $hashDatas = arrayFormatKey2($hashDatas, 'node_name');

            foreach ($datas as $i => $data) {
                if ($data['node_type'] == 'hash_table') {
                    $datas[$i]['items'] = $hashDatas[$data['node_name']];
                }
            }

            $objRedis->set("pub_data:{$env}:{$key}", json_encode($datas));

            $objVersion = new TableHelper('ns_pub_version', 'Web');
            $log_time = NOW;
            $creator = User::getUserName();
            $objVersion->addObject(compact('env', 'log_time', 'key', 'creator'));
            $version_id = $objVersion->getInsertId();
            $objRedis->publish("pub_event::{$env}:{$key}", $version_id);

            $time = 86400 * 7;
            $objRedis->set("pub_data:{$env}:$version_id", json_encode($datas), $time);
        }else{
            $version_id = $version;
            $objRedis->publish("pub_version_back::{$env}:{$key}", $version);
        }

        $objServer = new TableHelper('ns_server', 'Web');
        $where = [
            'env' => $args['env'],
        ];
        $keyWord = [
            '_where' => "`keys` LIKE '%{$key}%'",
            '_field' => 'server_ip, `keys`'
        ];

        $servers = $objServer->getAll($where, $keyWord);
        $server_ip = [];
        foreach ($servers as $server) {
            $parts = explode(',', $server['keys']);
            foreach ($parts as $part) {
                if ($key == trim($part)) {
                    $server_ip[] = $server['server_ip'];
                }
            }
        }

        $server_ip = join(',', $server_ip);
        return compact('server_ip', 'env', 'version_id');
    }


    /**
     * 重启通知
     * @author ben
     * @param $args
     */
    public function actionRestart($args) {
        $rules = [
            'ip' => ['string', 'desc' => '服务端ip'],
            'env' => ['int', 'desc' => '环境']
        ];
        Param::checkParam2($rules, $args);

        $ip = $args['ip'];
        $env = $args['env'];

        $objRedis = dwRedis::init('name_serv');
        $channel = "pub_restart:{$env}:{$ip}";
        $objRedis->publish($channel, $ip);

        return $channel;
    }

    /**
     * 心跳通知
     * @author ben
     * @param $args
     */
    public function actionEcho($args) {
        $rules = [
            'ip' => ['string', 'desc' => '服务端ip'],
            'env' => ['int', 'desc' => '环境']
        ];
        Param::checkParam2($rules, $args);

        $ip = $args['ip'];
        $env = $args['env'];

        $objRedis = dwRedis::init('name_serv');
        $objRedis->publish("pub_echo:{$env}:{$ip}", -1);
    }

    public function actionGetVersion($args) {
        $rules = [
            'key' => ['string', 'desc' => '发布的一级目录'],
            'env' => ['int', 'desc' => '环境'],
        ];

        $last_week = date("Y-m-d H:i:s", strtotime("-1 week"));
        $_where = [
            'env' => (int)$args['env'],
            'key' => $args['key'],
        ];
        $keyWords = [
            '_where' => "log_time > '{$last_week}'",
            '_field' => 'version_id, creator, log_time',
//            '_debug' => 1
        ];
        $objPubVersion = new TableHelper('ns_pub_version', 'Web');
        $datas = $objPubVersion->getAll($_where, $keyWords);
        return $datas;
    }

    public function actionCopyNsConfig($args) {
        $rule = [
            'node_name' => ['string', 'desc' => '节点名称'],
            'env' => ['int', 'enum' => [1, 2, 4, 6, 7], 'desc' => '环境：1:测试环境,2:预发布环境,4:正式环境,6:预发布+正式环境,7:所有环境'],
        ];

        $node_name = $args['node_name'];
        $env = (int)$args['env'];
        $tarGetNodeName = $node_name . '_copy';

        $objNsNode = new TableHelper('ns_node', 'Web');
        $where = [
            'node_name' => $node_name,
            'env' => $env,
        ];
        $nodeInfo = $objNsNode->getRow($where);
        if (!$nodeInfo) {
            Response::error(CODE_NORMAL_ERROR, "节点：{$args['node_name']}, env：{$args['env']} 没有数据");
        }
        $nodeInfo['node_name'] = $tarGetNodeName;
        $nodeInfo['update_time'] = NOW;
        $nodeInfo['create_time'] = NOW;
        try {
            $data[] = $nodeInfo;
            $ret = $objNsNode->replaceObjects2($data);
        } catch (Exception $ex) {
            $msg = $ex->getMessage();
            Response::error(CODE_NORMAL_ERROR, $msg);
        }

        if ($nodeInfo['node_type'] == 'hash_table') {
            $objNsTable = new TableHelper('ns_hash_table', 'Web');
            $nsTables = $objNsTable->getAll($where);
            if ($nsTables) {
                foreach ($nsTables as $k => $item) {
                    $nsTables[$k]['node_name'] = $tarGetNodeName;
                    $nsTables[$k]['create_time'] = NOW;
                    $nsTables[$k]['update_time'] = NOW;
                }
//                $cols = ['node_name', 'env', 'key_name', 'key_value', 'value_type', 'enable', 'creator', 'create_time', 'update_time'];
                try {
                    $objNsTable->replaceObjects2($nsTables);
                } catch (Exception  $ex) {
                    $msg = $ex->getMessage();
                    Response::error(CODE_NORMAL_ERROR, $msg);
                }
            }
        }
        Response::success(CODE_SUCCESS, '同步成功');
    }
}
