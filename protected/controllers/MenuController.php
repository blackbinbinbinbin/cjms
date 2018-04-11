<?php

/**
 * 菜单
 * @author benzhan
 */
class MenuController extends BaseController {

    protected $noLoginActions = array('reviceSync');

    /**
     * 首页
     * @author benzhan
     */
    function actionIndex() {
        Permission::checkUrlRight($_SERVER['REQUEST_URI']);
        
        $rootNodeId = 0;
        $objMenu = new VMenuNode();
        $items = $objMenu->getDirectSubNode($rootNodeId, false);
        
        $objUserNode = new UserNode();
        $userIds = $objUserNode->getUserIds($rootNodeId);
        $userIds = join(';', $userIds);

        $data = array(
            'nodeId' => $rootNodeId, 
            'nodeName' => '根目录',
            'leftUrl' => '', 
            'rightUrl' => '', 
            'userIds' => $userIds,
            'allUserIds' => $userIds
        );
        $node = array('text' => $data['nodeName'], 'value' => $rootNodeId, 'data' => $data);
        
        $node['items'] = $items;
        $node = array('items' => $node);
        $this->tpl->assign('tree', array('items' => $node));
        $this->tpl->display('menu/index');
    }
    
    /**
     * 获取子节点
     * @author benzhan
     * @param unknown $args
     */
    function actionGetChildsByPId($args) {
        $rules = array(
            'nodeId' => 'int',
            'showHiddenNode' => array('int', 'default' => 0, 'nullable' => true),
        );
        Param::checkParam($rules, $args);
        
        $objMenu = new VMenuNode();
        $display = $args['showHiddenNode'] ? false : 1;
        $nodes = $objMenu->getDirectSubNode($args['nodeId'], $display);
        $nodes = array('items' => $nodes);
        Response::success($nodes);
    }
    
    /**
     * 添加节点
     * @author benzhan
     * @param unknown $args
     */
    function actionAddNode($args) {
        $rules = array(
            'nodeName' => 'string',
            'leftUrl' => array('string', 'emptyable' => true),
            'rightUrl' => array('string', 'emptyable' => true),
            'parentNodeId' => 'int',
//            'appid' => array('string', 'emptyable' => true),
//            'moduleName' => array('string', 'emptyable' => true),
        );
        Param::checkParam2($rules, $args);
        
//        if (strpos($args['rightUrl'], 'http://') !== false
//                && (!$args['appid'] || !$args['moduleName'])) {
//            Response::error(CODE_PARAM_ERROR, null, 'appid or moduleName can not be empty');
//        }
        
        $objCMenu = new CMenuNode();
        $nodeId = $objCMenu->addNode($args);
        
        Response::success($nodeId, '添加成功！');
    }

    /**
     * 保存子节点
     * @author benzhan
     */
    function actionSaveNode($args) {
        $rules = array(
            'oldNodeId' => 'int',
            'nodeId' => 'int',
            'nodeName' => 'string',
            'leftUrl' => array('string', 'emptyable' => true),
            'rightUrl' => array('string', 'emptyable' => true),
            'userIds' => array('string', 'emptyable' => true),
            'appid' => array('string', 'emptyable' => true),
            'moduleName' => array('string', 'emptyable' => true),
            'needAnotherPwd' => array('int', 'emptyable' => true),
            'domain' => array('string', 'emptyable' => true),
            'display' => array('int', 'emptyable' => true),
            'openNewWindow' => array('int', 'emptyable' => true),
        );
        Param::checkParam2($rules, $args);

        if (strpos($args['rightUrl'], 'http://') !== false 
                && (!$args['appid'] || !$args['moduleName'])) {
            Response::error(CODE_PARAM_ERROR, null, 'appid or moduleName can not be empty'); 
        }        
        
        $userIds = arrayPop($args, 'userIds');
        $oldNodeId = arrayPop($args, 'oldNodeId');
        $nodeId = $args['nodeId'];
        $appid = $args['appid'];
        $moduleName = $args['moduleName'];
        
        $userIds = explode(';', $userIds);
        $data = array();
        foreach ($userIds as $userId) {
            $userId = trim($userId);
            $userId && $data[$userId] = compact('nodeId', 'userId');
        }

        $objCMenu = new CMenuNode();
        if ($oldNodeId != $nodeId) {
            $row = $objCMenu->objTable->getRow(compact('nodeId'));
            if ($row) {
                Response::error(CODE_PARAM_ERROR, '节点id冲突了，请换一个节点id.');
            }
        }

        
        $objUserNode = new TableHelper('rUserNode');
        $objUserNode->autoCommit(false);
        // 删除老数据
        $objUserNode->delObject(['nodeId' => $oldNodeId]);
        // 添加新数据
        $objUserNode->addObjects2($data);

        if ($oldNodeId != $args['nodeId']) {
            // 需要改直属数据
            $objRMenuNode = new TableHelper('rMenuNode');
            $newData = [
              'parentNodeId' => $args['nodeId']
            ];
            $where = [
              'parentNodeId' => $oldNodeId
            ];
            $objRMenuNode->updateObject($newData, $where);

            // 需要改孩子数据
            $newData = [
              'nodeId' => $args['nodeId']
            ];
            $where = [
              'nodeId' => $oldNodeId
            ];
            $objRMenuNode->updateObject($newData, $where);
        }
 

        $objCMenu->saveNode($args, $oldNodeId);
        
        $objUserNode->tryCommit();
        
        Response::success($nodeId, '保存成功！');
    }

    /**
     * 删除节点
     * @author benzhan
     * @param unknown $args
     */
    function actionDeleteNode($args) {
        $rules = array(
            'nodeId' => 'int',
        );
        Param::checkParam($rules, $args);
        
        // 检查是不是有多层子节点
        $objRMenu = new RMenuNode();
        $where = array('parentNodeId' => $args['nodeId']);
        $subNodeIds = $objRMenu->objTable->getCol($where, array('_field' => 'nodeId'));
        
        if ($subNodeIds) {
            $where = array('parentNodeId' => $subNodeIds);
            $subNodeIds = $objRMenu->objTable->getCol($where, array('_field' => 'nodeId'));
            if ($subNodeIds) {
                Response::error(CODE_PARAM_ERROR, '删除失败，请先删除孙子节点');
            }
            
            // 先删除子节点
            $objCMenu = new CMenuNode();
            $where = array('nodeId' => $subNodeIds);
            $objCMenu->objTable->delObject($where);
            
            // 没有孙子节点，所以不用删除后续的
        }
        
        $where = array('parentNodeId' => $args['nodeId']);
        $objRMenu->objTable->delObject($where);
        
        $where = array('nodeId' => $args['nodeId']);
        $objRMenu->objTable->delObject($where);
     
        Response::success(array(), '删除成功！');
    }
    
    /**
     * 修复没有tableId的节点
     */
//    function actionFixNode() {
//        $objMenuNode = new CMenuNode();
//        $datas = $objMenuNode->objTable->getAll();
//
//        $nodeIds = array();
//        foreach ($datas as $data) {
//            if ($data['leftUrl'] && !$data['leftTableId'] || $data['rightUrl'] && !$data['rightTableId']) {
//                $objMenuNode->saveNode($data);
//                $nodeIds[] = $data['nodeId'];
//            }
//        }
//
//        Response::success($nodeIds);
//    }
    
    /**
     * 保存节点的位置
     * @author benzhan
     * @param unknown $args
     */
    function actionSyncPos($args) {
        $rules = array(
            'json' => 'string',
        );
        Param::checkParam($rules, $args);
        
        $json = $args['json'];
        $objJson = json_decode($json, true);
        if (!$objJson) {
            exit(json_encode(array('ret' => true, 'msg' => '保存成功！')));
        }
        
        //要更新的数据
        $updateData = array();
        //新的关系
        $newMap = array();
        //旧的关系
        $oldMap = array();
        
//        try {
            //格式化新的关系
            foreach ($objJson as $obj) {
                $newMap[$obj['parentNodeId']][$obj['nodeId']] = $obj['sortPos'];
            }
            
            //获取旧的关系
            $pIds = array_keys($newMap);
            $objVMenu = new VMenuNode();
            $menuDatas = $objVMenu->getChildByPid($pIds);
            
            foreach ($menuDatas as $data) {
                $oldMap[$data['parentNodeId']][$data['nodeId']] = $data['sortPos'];
            }
            
            $updateData = $addData = $deleteData = array();


            //对比新旧的数据，得到新增数据和变更数据
            foreach ($newMap as $pId => $data) {
                foreach ($data as $id => $sortPos) {
                    $oldSortPos = $oldMap[$pId][$id];
                    if ($oldSortPos == $sortPos) { continue; }
                    
                    $args = array('parentNodeId' => $pId, 'nodeId' => $id, 'sortPos' => $sortPos);
                    if ($_GET['_debug']) {
                        var_dump("\$args :'parentNodeId' => $pId, 'nodeId' => $id, 'sortPos' => $sortPos");
                    }
                    if (isset($oldMap[$pId][$id])) {
                        if ($_GET['_debug']) {
                            var_dump("\$where :'parentNodeId' => $pId, 'nodeId' => $id, 'oldSortPos' => $oldSortPos");
                        }
                        $where = array('parentNodeId' => $pId, 'nodeId' => $id, 'sortPos' => $oldMap[$pId][$id]);
                        $updateData[] = [
                            'args' => $args,
                            'where' => $where
                        ];
                        // 这个在PHP 7.0.0有bug
//                        $updateData[] = compact('args', 'where');
                    } else {
                        $addData[] = $args;
                    }
                }
            }

            if ($_GET['_debug']) {
                var_dump($newMap);
                var_dump($oldMap);
                var_dump($updateData);
                exit;
            }
            
            //对比旧新数据，得到删除数据
            foreach ($oldMap as $pId => $data) {
                foreach ($data as $id => $sortPos) {
                    if ($newMap[$pId][$id] == $sortPos) { continue; }
                    if (!isset($newMap[$pId][$id])) {
                        $where = array('parentNodeId' => $pId, 'nodeId' => $id, 'sortPos' => $sortPos);
                        $deleteData[] = $where;
                    } 
                }
            }
            
            
            $objRMenu = new RMenuNode();
            //插入不同的数据
            foreach ($updateData as $data) {
                $objRMenu->saveNodeRelation($data['args'], $data['where']);
            }
            
            $addData && $objRMenu->objTable->replaceObjects2($addData);
            $deleteData && $objRMenu->deleteNodeRelation($where);
            
//        } catch (Exception $ex) {
//            Response::error(CODE_INTER_ERROR, '保存失败！', $ex->getMessage());
//        }
        
        Response::success(compact('addData', 'deleteData', 'updateData'), '保存成功！');
    }

    function _exportMenuData($rootId) {
        $allNodeIds = [];

        $objRMenuNode = new TableHelper('rMenuNode');
        $parentNodeId = $rootId;
        $allNodeIds[] = $parentNodeId;
        for ($i = 0; $i < 10; $i++) {
            $where = compact('parentNodeId');
            $keyWord = [
              '_field' => 'nodeId'
            ];
            $nodeIds = $objRMenuNode->getCol($where, $keyWord);

            if ($nodeIds) {
                $allNodeIds = array_merge($allNodeIds, $nodeIds);
                $parentNodeId = $nodeIds;
            } else {
                break;
            }
        }

        $objCMenuNode = new TableHelper('cMenuNode');
        $info = $objCMenuNode->getAll(['nodeId' => $allNodeIds]);

        $relation = $objRMenuNode->getAll(['parentNodeId' => $allNodeIds]);
        $rootRelations = $objRMenuNode->getAll(['nodeId' => $rootId]);

        return compact('info', 'relation', 'rootRelations');
    }

    function _importMenuData($data) {
        $objCMenuNode = new TableHelper('cMenuNode');
        $objCMenuNode->autoCommit(false);
        $objCMenuNode->addObjects2($data['info']);

        $objRMenuNode = new TableHelper('rMenuNode');
        $objRMenuNode->addObjects2($data['relation']);
        $objRMenuNode->addObjects2($data['rootRelations']);

        $objCMenuNode->commit();
    }

    /**
     * 同步到正式环境
     * @param $args
     * @author benzhan
     */
    function actionSyncToProduct($args) {
        $rules = array(
          'nodeId' => array('int'),
        );
        Param::checkParam2($rules, $args);

        // 检查权限
        $flag = Permission::checkRight($args['nodeId']);
        if (!$flag) {
            Response::error(CODE_NO_PERMITION);
        }

        if (ENV === ENV_DEV) {
            $host = 'admin.ouj.com';
            $ip = '61.160.36.226';
        } else {
            $host = 'test.admin.ouj.com';
            $ip = '61.160.36.225';
        }

        $data = $this->_exportMenuData($args['nodeId']);
        $data['sign'] = ThirdApi::getSign($data);
        $data = json_encode($data);

        $objHttp = new dwHttp();
        $url = "http://{$ip}/menu/reviceSync";
        $msg = $objHttp->post($url, $data, 15, "Host:{$host}");

        Response::exitMsg($msg);
    }

    function actionReviceSync() {
        $data = file_get_contents("php://input");
        $data = json_decode($data, true);

        $sign = arrayPop($data, 'sign');
        $sign2 = ThirdApi::getSign($data);
        if (!$sign || $sign != $sign2) {
            Response::error(CODE_SIGN_ERROR, null);
        }

        $rootRelations = $data['rootRelations'];
        $nodeIds = array_column($rootRelations, 'parentNodeId');
        $parentNodeId = [];
        foreach ($nodeIds as $nodeId) {
            if ($nodeId != 0) {
                $parentNodeId[] = $nodeId;
            }
        }

        if ($parentNodeId) {
            $objCMenuNode = new TableHelper('cMenuNode');
            $num = $objCMenuNode->getCount(compact('$parentNodeId'));
            if ($num != count($parentNodeId)) {
                Response::error(CODE_PARAM_ERROR, '父节点不存在');
            }
        }


        $this->_importMenuData($data);

        Response::success();
    }
}
