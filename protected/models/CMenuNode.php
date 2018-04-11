<?php

class CMenuNode extends Model {
    protected $tableName = 'cMenuNode';
    
    function addNode($args) {
        $parentNodeId = (int) arrayPop($args, "parentNodeId");

        $this->objTable->addObject($args);
        $nodeId = $this->objTable->getInsertId();
        
        $oMenuRelation = new TableHelper('rMenuNode');
        $maxSortPos = $oMenuRelation->getOne(compact('parentNodeId'), array('_field' => 'MAX(sortPos)'));
        $sortPos = $maxSortPos + 1;
        $oMenuRelation->addObject(compact('nodeId', 'sortPos', 'parentNodeId'));
        
        return $nodeId;
    }
    
    function saveNode($args, $oldNodeId) {
        $oldNodeId = (int) $oldNodeId;
        $data = arrayFilter($args, 'nodeId', 'nodeName', 'leftUrl', 'rightUrl', 'appid', 'moduleName', 'needAnotherPwd', 'domain', 'display', 'openNewWindow');
        $data['leftTableId'] = $this->_getTableId($args['leftUrl']);
        $data['rightTableId'] = $this->_getTableId($args['rightUrl']);

        if (isset($oldNodeId)) {
            $where = ['nodeId' => $oldNodeId];
            $this->objTable->updateObject($data, $where);
        } else {
            Response::error(CODE_NORMAL_ERROR, 'without oldNodeId.');
        }
    }
    
    private function _getTableId($url) {
        $pattern = "/[?&]tableId=([^&#]+)/";
        $matches = array();
        $flag = preg_match($pattern, $url, $matches);
        
        if ($flag) {
            return $matches[1];
        } else {
            return '';
        }
    }
    
}
