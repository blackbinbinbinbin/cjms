<?php

/**
 * 首页
 * @author benzhan
 */
class DefaultController extends BaseController {

    /**
     * 首页
     * @author benzhan
     */
    function actionIndex() {
        $objVMenu = new VMenuNode();
        $datas = $objVMenu->getLevel3Data();

        $this->tpl->assign('menus', $datas);
        $this->tpl->display('index');
    }
    
    /**
     * 获取默认的tree结构
     * @author benzhan
     * @param array $args
     */
    function actionMenuTree(array $args) {
        $rules = array(
          'nodeId' => 'int'
        );
        Param::checkParam($rules, $args);
        $nodeId = $args['nodeId'];
        $pid = $args['nodeId'];

        $objMenu = new VMenuNode();
        do {
            $node = $objMenu->objTable->getRow(['nodeId' => $pid]);
            $pid = $node['parentNodeId'];
        } while($pid);

        $nodes = $objMenu->getLevel2Data([$node]);
        $treeData = [];
        foreach ($nodes as $node) {
            $l2Items = arrayPop($node, 'items');
            $l2Items = array_values($l2Items);
            $treeData['text'] = $node['nodeName'];
            $treeData['value'] = $node['nodeId'];
            $treeData['data'] = $node;
            if ($nodeId == $node['nodeId']) {
                $treeData['isChecked'] = true;
            }
            if (!$l2Items) {
                continue;
            }

            foreach ($l2Items as $i => $item) {
                $l3Items = arrayPop($item, 'items');
                $l3Items = array_values($l3Items);
                $l2Node = [];
                $l2Node['text'] = $item['nodeName'];
                $l2Node['value'] = $item['nodeId'];
                $l2Node['data'] = $item;
                if ($nodeId == $item['nodeId']) {
                    $l2Node['isChecked'] = true;
                }
                $treeData['items'][$i] = $l2Node;

                if (!$l3Items) {
                    continue;
                }

                foreach ($l3Items as $j => $elem) {
                    $l4Items = arrayPop($elem, 'items');
                    $l3Node = [];
                    $l3Node['text'] = $elem['nodeName'];
                    $l3Node['value'] = $elem['nodeId'];
                    $l3Node['data'] = $elem;
                    if ($l4Items) {
                        $l3Node['items'] = [];
                    }
                    $treeData['items'][$i]['items'][$j] = $l3Node;
                }
            }
        }

        $this->tpl->assign('tree', array('items' => array('items' => $treeData)));
        $this->tpl->display('menu/tree');
    }

    function actionMenuList($args) {
        $rules = array(
          'nodeId' => 'int',
        );
        Param::checkParam($rules, $args);

        $objMenu = new VMenuNode();
        $nodes = $objMenu->getDirectSubNode($args['nodeId']);
        foreach ($nodes as $i => $node) {
            $url = $node['data']['rightUrl'];
            if ($url) {
                $connect = strpos($url, '?') === false ? '?' : '&';
                $url .= $connect . '_nodeId=' . $node['value'];
                $node['data']['rightUrl'] = str_replace('"', '%22', $url);
                $nodes[$i] = $node;
            }
        }

        $this->tpl->assign(array('items' => $nodes));
        $this->tpl->display('menu/list');
    }

    /**
     * 获取默认的tree结构
     * @author benzhan
     * @param array $args
     */
    function actionGetSiteMap(array $args) {
        $rules = array(
            'nodeId' => 'int'
        );
        Param::checkParam($rules, $args);
        
        $nodeId = $args['nodeId'];
        $objMenu = new VMenuNode();
        $siteMap = array();
        do {
            $node = $objMenu->objTable->getRow(compact('nodeId'));
            array_unshift($siteMap, $node);
            $nodeId = $node['parentNodeId'];
        } while($nodeId);



        
        Response::success($siteMap);
    }
    
    /**
     * 验证二次密码
     * @author hawklim
     */
     function actionCheckAnotherPwd($args) {
        $rule = array(
            'nodeId' => 'string',
        );
        Param::checkParam($rule, $args);
        
        Permission::checkAnotherPwd($args['nodeId']);
        Response::success([]);
    }   


//    function actionFixMap() {
//        $objMap = new TableHelper('Cmdb3Map', 'Report');
//        $mapDatas = $objMap->getAll();
//        $mapIds = [];
//        foreach ($mapDatas as $mapData) {
//            $mapKey = "{$mapData['nameDb']}:{$mapData['sourceTable']}:{$mapData['keyName']}:{$mapData['valueName']}";
//            if (!$mapData['mapKey']) {
//                $mapId = $mapData['mapId'];
//                $objMap->updateObject(compact('mapKey'), compact('mapId'));
//                $mapIds[] = $mapId;
//            }
//        }
//
//        $mapDatas = $objMap->getAll();
//        $mapDatas = arrayFormatKey($mapDatas, 'mapId');
//
//        // 修改map字段
//        $objField = new TableHelper('Cmdb3Field', 'Report');
//        $fieldDatas = $objField->getAll(['_where' => 'mapId > 0 OR enumMapId > 0']);
//        foreach ($fieldDatas as $field) {
//            $mapId = $field['mapId'];
//            $mapKey = $mapDatas[$mapId]['mapKey'];
//            $enumMapId = $field['enumMapId'];
//            $enumMapKey = $mapDatas[$enumMapId]['mapKey'];
//            $fieldId = $field['fieldId'];
//
//            var_dump(compact('mapId', 'mapKey', 'enumMapId', 'enumMapKey', 'fieldId'));
//            $objField->updateObject(compact('mapKey', 'enumMapKey'), compact('fieldId'));
//        }
//        exit;
//
//
//        return $mapIds;
//    }
}
