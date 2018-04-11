<?php

class RMenuNode extends Model {
    protected $tableName = 'rMenuNode';
    
    function saveNodeRelation($args, $where) {
        $newData = arrayFilter($args, 'parentNodeId', 'sortPos');
        $where = arrayFilter($where, 'nodeId', 'parentNodeId');
        if (!$where) { return false; }
        
        $this->objTable->updateObject($newData, $where);
        return true;
    }
    
    function deleteNodeRelation($where) {
        $where = arrayFilter($where, 'nodeId', 'parentNodeId');
        if (!$where) { return false; }
        
        return $this->objTable->delObject($where);
    }
    
    function getAllParentIds($nodeId) {
        $parentIds = array();
        $tData = $nodeId;
        
        while ($tData) {
            $where = array('nodeId' => $tData);
            $keyWord = array('_field' => 'parentNodeId');
            $tData = $this->objTable->getCol($where, $keyWord);
            $parentIds = array_merge($parentIds, $tData);
        }
        
        return $parentIds;
    }
    
    function getAllChildIds($nodeId) {
        $nodeIds = array();
        $tData = $nodeId;
    
        while ($tData) {
            $where = array('parentNodeId' => $tData);
            $keyWord = array('_field' => 'nodeId');
            $tData = $this->objTable->getCol($where, $keyWord);
            $nodeIds = array_merge($nodeIds, $tData);
        }
    
        return $nodeIds;
    }

}
