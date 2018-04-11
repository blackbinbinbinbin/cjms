<?php

// require_once ROOT_PATH . 'conf/diyConfig.inc.php';

/**
 * 扩展的字典表
 * @author benzhan
 */
class Diy_Chart extends Model {
    protected $tableName = 'Cmdb3Chart';
    protected $dbKey = "Report";

    public function getTypes() {
        return [
            'line' => '折线图',
            'bar' => '柱状图',
            'pie' => '饼图',
        ];
    }

    public function getDataTypes() {
        return [
            'tile' => '平铺',
            'heap' => '堆积',
        ];
    }

    public function getList($tableId) {
        $where = compact('tableId');
        $where['enable'] = 1;

        $keyWord = ['_sortKey' => 'weight DESC'];
        $list = $this->objTable->getAll($where, $keyWord);

        return $list;
    }
}



