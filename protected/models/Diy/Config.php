<?php

class Diy_Config {
    private $_dbKey = "Report";

	public function getHosts() {
		$oBaseTable = new TableHelper('Cmdb3Table', $this->_dbKey);
		return $oBaseTable->getCol(array('_field' => 'sourceHost', '_groupby' => 'sourceHost', '_sortKey' => 'sourceHost'));
	}

    public function getRedisHosts() {
        $oBaseTable = new TableHelper('Cmdb3Table', $this->_dbKey);
        return $oBaseTable->getCol(array('_field' => 'redisHost', '_groupby' => 'redisHost', '_sortKey' => 'redisHost'));
    }

    public function getStaticModes() {
        $map = [
            0 => '自动',
            1 => 'sql统计',
            2 => 'php统计',
        ];

        return $map;
    }

	public function getDbs($args) {
	    $db = $this->_getDb($args);
	    
	    $sql = "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA";
	    return $db->getCol($sql);
	}
	
	public function getTables($args) {
	    $db = $this->_getDb($args);
	    $args = $db->escape($args);

		if ($args['nameDb']) {
			$args['sourceDb'] = $GLOBALS['dbInfo'][$args['nameDb']]['dbName'];
		} else if ($args['dbId']) {
            $objDiyDb = new Diy_Db();
            $row = $objDiyDb->objTable->getRow(['dbId' => $args['dbId']]);
            $args = array_merge($args, $row);
        }

	    $sql = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$args['sourceDb']}'";
	    return $db->getCol($sql);
	}
	
	public function getDbFields($args) {
	    $db = $this->_getDb($args);
	    $args = $db->escape($args);
		$sql = "SHOW FULL FIELDS FROM `{$args['sourceTable']}`";
		$datas = $db->getAll($sql);
		return $datas;
	}

    public function getPriKeys($args) {
        $db = $this->_getDb($args);
        $args = $db->escape($args);
        try {
            $sql = "SHOW FIELDS FROM `{$args['sourceTable']}`";
            $datas = $db->getAll($sql);

            $col = array();
            foreach ($datas as $data) {
                if ($data['Key'] == 'PRI') {
                    $col[] = $data['Field'];
                }
            }

            return $col;
        } catch (Exception $ex) {
            return array();
        }
    }
	
	private function _getDb($args) {
	    $oData = new Diy_Data();
	    return $oData->getDb($args);
	}
}
