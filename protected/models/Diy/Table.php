<?php

class Diy_Table extends Model {
    protected $tableName = 'Cmdb3Table';
    protected $dbKey = "Report";

    const EVENT_SAVE_BEFORE = 'diy_edit_save:before';
    const EVENT_SAVE_AFTER = 'diy_edit_save:after';
    const EVENT_IMPORT_BEFORE = 'diy_edit_import:before';
    const EVENT_IMPORT_AFTER = 'diy_edit_import:after';

    private static $map = [];

    /**
     * 获取表配置信息
     * @param int $tableId 表Id
     * @author benzhan
     */
	public function getTableInfo($tableId) {
	    $where = compact('tableId');
        if (!self::$map[$tableId]) {
            self::$map[$tableId] = $this->objTable->getRow($where);
        }

        return self::$map[$tableId];
    }
	
	public function isAdmin($tableId) {
	    $oConfig = new Diy_Table();
	    $tableInfo = $oConfig->getTableInfo($tableId);
	
	    return $this->isAdmin2($tableInfo);
	}
	
	public function isAdmin2($tableInfo) {
	    $admins = explode(',', $tableInfo['admins']);
	    require_once ROOT_PATH . '/conf/diyConfig.inc.php';
	    $admins = array_merge($admins, $GLOBALS['diy']['whiteList']);
	    return in_array($_SESSION['username'], $admins);
	}
	
	/**
	 * 设置默认条件或选择列
	 * @param array $args array('tableId', 'metaKey', 'metaValue')
	 * @author benzhan
	 */
	public function setTableMeta($args) {
	    $rules = array(
            'tableId' => 'string',
            'metaKey' => 'string',
            'metaValue' => 'string'
	    );
	    Param::checkParam($rules, $args);
	
	    $oBaseTable = new TableHelper('Cmdb3TableMeta', $this->dbKey);
	    return $oBaseTable->replaceObject($args);
	}
	
	/**
	 * 获取默认条件或选择列
	 * @param array $args array('tableId', 'metaKey')
	 * @author benzhan
	 */
	public function getTableMeta($where) {
	    $rules = array(
            'tableId' => 'string',
            'metaKey' => 'string',
	    );
	    Param::checkParam($rules, $where);
	    
	    $oBaseTable = new TableHelper('Cmdb3TableMeta', $this->dbKey);
	    return $oBaseTable->getRow($where);
	}

	private static $eventList = [];

    /**
     * 注册事件
     * @param $eventName
     * @param $callback
     */
	public static function on($eventName, $callback) {
        self::$eventList[$eventName][] = $callback;
    }

    /**
     * 触发前置事件，返回false则推出
     * @param $eventName
     * @param $data
     */
    public static function triggerBeforEvent($eventName, &$data) {
        $flag = self::trigger($eventName, $data);
        if ($flag === false) {
            Response::error(CODE_NO_PERMITION);
        }
    }

    /**
     * 触发事件
     * @param $eventName
     * @param $data
     */
    public static function trigger($eventName, &$data) {
        $flag = null;
	    if (!self::$eventList[$eventName]) {
	        return $flag;
        }

        foreach (self::$eventList[$eventName] as $event) {
            $flag = $event($data);
        }

        return $flag;
    }
	
}
