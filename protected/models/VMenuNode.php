<?php

class VMenuNode extends Model {
    protected $tableName = '(select `c`.`nodeId` AS `nodeId`,`c`.openNewWindow, `c`.`appid` AS `appid`,`c`.`moduleName` AS `moduleName`,`c`.`nodeName` AS `nodeName`,`c`.`leftUrl` AS `leftUrl`,`c`.`needAnotherPwd` AS `needAnotherPwd`,`c`.`display` AS `display`,`c`.`rightUrl` AS `rightUrl`,`c`.`domain` AS `domain`, `r`.`parentNodeId` AS `parentNodeId`,`c2`.`nodeName` AS `parentNodeName`,`r`.`sortPos` AS `sortPos` from ((`Web`.`rMenuNode` `r` left join `Web`.`cMenuNode` `c` on((`c`.`nodeId` = `r`.`nodeId`))) left join `Web`.`cMenuNode` `c2` on((`c2`.`nodeId` = `r`.`parentNodeId`)))) AS vMenuNode';
//    protected $tableName = 'vMenuNode';
    
    private function getChildData($pId, $display = 1) {
        $datas = $this->getChildByPid($pId, $display);
        foreach ($datas as $key => $data) {
            $where = ['parentNodeId' => $data['nodeId']];
            if (is_int($display)) {
                $data['display'] = $display;
            }

            $datas[$key]['childNum'] = $this->objTable->getCount($where);
        }

        return $datas;
    }
    
    function getChildByPid($pId, $display = 1) {
        $where = array('parentNodeId' => $pId);
        if (is_int($display)) {
            $where['display'] = $display;
        }
        return $this->objTable->getAll($where, array('_sortKey' => 'sortPos ASC'));
    }
    
    function getDirectSubNode($pId, $display = 1) {
        $pId = (int) $pId;
        $menuDatas = $this->getChildData($pId, $display);
        if ($menuDatas) {
            $objUserNode = new UserNode();
            foreach ($menuDatas as $key => $data) {
                $allUserIds = $objUserNode->getAllUserIds($data['nodeId']);
                $data['allUserIds'] = join(';', $allUserIds);
                if (Permission::isAdmin3() || in_array($_SESSION['username'], $allUserIds)) {
                    // 如果有权限
                    $userIds = $objUserNode->getUserIds($data['nodeId']);
                    $data['userIds'] = join(';', $userIds);
                    
                    $node = array('text' => $data['nodeName'], 'value' => $data['nodeId'], 'data' => $data);
                    $data['childNum'] > 0 && $node['items'] = array();
                    
                    $menuDatas[$key] = $node;
                } else {
                    // 没有权限
                    unset($menuDatas[$key]);
                }
            }
        }
        
        return $menuDatas;
    }

    function getNodeById($nodeId) {
        $data = $this->objTable->getRow(compact('nodeId'));
        $node = array('text' => $data['nodeName'], 'value' => $data['nodeId'], 'data' => $data);
        
        return $node;
    }

    function getLevel2Data($l1Nodes) {
        $datas = array();
        foreach ($l1Nodes as $l1Node) {
            $nodeId = $l1Node['nodeId'];
            // 过滤域名
            if (!$this->_checkDomain($l1Node)) {
                continue;
            }

            if (Permission::checkRight($nodeId)) {
                $datas[$l1Node['nodeId']] = $l1Node;
            }
        }

        $ids = array_keys($datas);
        $level2Nodes = $this->getChildByPid($ids);
        $level2Nodes = arrayFormatKey($level2Nodes, 'nodeId');

        $nodeIds = [];
        foreach ($level2Nodes as $l2Node) {
            // 过滤域名
            if (!$this->_checkDomain($l2Node)) {
                continue;
            }

            if (Permission::checkRight($l2Node['nodeId'])) {
                $datas[$l2Node['parentNodeId']]['items'][$l2Node['nodeId']] = $l2Node;
                $nodeIds[] = $l2Node['nodeId'];
            }
        }

        if (!$nodeIds) {
            return $datas;
        }

        $level3Nodes = $this->getChildByPid($nodeIds);
        $level3Nodes = arrayFormatKey($level3Nodes, 'nodeId');
        $nodeIds = [];
        foreach ($level3Nodes as $l3Node) {
            // 过滤域名
            if (!$this->_checkDomain($l3Node)) {
                continue;
            }

            if (Permission::checkRight($l3Node['nodeId'])) {
                $l2Node = $level2Nodes[$l3Node['parentNodeId']];
                $datas[$l2Node['parentNodeId']]['items'][$l2Node['nodeId']]['items'][$l3Node['nodeId']] = $l3Node;
                $nodeIds[] = $l3Node['nodeId'];
            }
        }

        if (!$nodeIds) {
            return $datas;
        }

        $level4Nodes = $this->getChildByPid($nodeIds);
        $level4Nodes = arrayFormatKey($level4Nodes, 'nodeId');
        foreach ($level4Nodes as $l4Node) {
            if (Permission::checkRight($l4Node['nodeId'])) {
                $l3Node = $level3Nodes[$l4Node['parentNodeId']];
                $l2Node = $level2Nodes[$l3Node['parentNodeId']];
                $datas[$l2Node['parentNodeId']]['items'][$l2Node['nodeId']]['items'][$l3Node['nodeId']]['items'][] = true;
            }
        }

        return $datas;
    }

    private function _checkDomain($node) {
        // 过滤域名
        if ($node['domain']) {
            if ($node['domain'] == 'duowan.com' && !IS_DUOWAN) {
                return false;
            } else if ($node['domain'] == 'ouj.com' && !IS_OUJ) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * 获取两级数据
     * @author benzhan
     * @return Ambigous <multitype:unknown , unknown>
     */
    function getLevel3Data() {
        $nodes = $this->getChildByPid(0);
        return $this->getLevel2Data($nodes);
    }
    
}
