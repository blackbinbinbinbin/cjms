<?php

class UserNode extends Model {
    protected $tableName = 'rUserNode';
    
    function getUserIds($nodeId) {
        $where = array('nodeId' => $nodeId);
        $keyWord = array('_field' => 'userId');
        $userIds = $this->objTable->getCol($where, $keyWord);
        
        return $userIds;
    }
    
    function getAllUserIds($nodeId) {
        // 计算父节点
        $objRMenu = new RMenuNode();
        $parentIds = $objRMenu->getAllParentIds($nodeId);
        
        // 也需要把当前节点也参与查询
        if (is_array($nodeId)) {
            $parentIds = array_merge($parentIds, $nodeId);
        } else {
            $parentIds[] = $nodeId;
        }
        
        // 计算子节点
        $childIds = $objRMenu->getAllChildIds($nodeId);
        $parentIds = array_merge($parentIds, $childIds);
        
        $where = array('nodeId' => $parentIds);
        $keyWord = array('_field' => 'DISTINCT userId');
        $userIds = $this->objTable->getCol($where, $keyWord);
    
        return $userIds;
    }
    
}
