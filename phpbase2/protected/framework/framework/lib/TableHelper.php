<?php

/**
 * 基本类，提供增删改查
 * @author benzhan
 */
class TableHelper {
    private $_tableName;
    /**
     * @type DB_Mysqli
     */
    private $_db;
    private $_onlyRecordSql = false;
    public $found_rows = 0;
    
    /**
     * 基本类，提供增删改查
     * @author benzhan
     * @param string $tableName 表名
     * @param string/object $dbKey $dbKey或db对象
     */
    function __construct($tableName, $dbKey = 'default') {
        $dbs = DB::init($GLOBALS['dbInfo']);
        $this->_tableName = $tableName;
        false && $this->_db = $dbs;
        if (is_string($dbKey)) {
            $this->_db = $dbs[$dbKey];
        } else {
            $this->_db = $dbKey;
        }
    }

    function setOnlyRecordSql($flag) {
        $this->_onlyRecordSql = $flag;
    }
    
    function setTableName($tableName) {
        $this->_tableName = $tableName;
    }
    
    /**
     * 生成SQL
     * @author benzhan
     * @param array $args 参数列表，特殊参数前缀_
     * @param array $keyWord 查询关键字, array('_field', '_where', '_limit', '_sortKey', '_sortDir', '_lockRow', '_tableName')
     */
    function buildSql(array $where = array(), $keyWord = array(), $forCountNum = false) {
        $where = array_merge($where, $keyWord);
        
        // 是否输出计算总行数的SQL
        if(!$forCountNum) {
            $field = $where['_field'];
            $field || $field = "*";
        }else {
            $field = "count(*)";
        }
    
        $tableName = $this->getTableName($where);
        $_where = $where['_where'] ? $where['_where'] : '1';
        $sql = "SELECT $field FROM {$tableName} WHERE {$_where} ";
        //构造条件部分
        $where = $this->_db->escape($where);
        foreach ($where as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }
    
            if (is_array($value)) {
                $sql .= "AND `{$key}` IN ('" . implode("','", $value) . "') ";
            } else {
                isset($value) && $sql .= "AND `{$key}` = '{$value}' ";
            }
        }
    
        $where['_groupby'] && $sql .= "GROUP BY {$where['_groupby']} ";
        $sortKey = $where['_sortKey'] ? $where['_sortKey'] : $where['_sortExpress'];
        $sortDir = $where['_sortDir'] ? $where['_sortDir'] : $where['_sortDirection'];
        $sort = $where['_sort'] ? $where['_sort'] . ', ' : '';
    
        //排序
        if ($sortKey && !$forCountNum) {
            $sql .= "ORDER BY {$sort} {$sortKey}  {$sortDir} ";
        }
        
        //标识是否锁行，注意的是也有可能锁表
        if(!$forCountNum) {
            $where['_lockRow'] && $sql .= "FOR UPDATE ";
        }
        
        if($where['_groupby'] && $forCountNum) {
            $sql = "SELECT COUNT(*) FROM(".$sql.") tbl";
        }

        return $sql;
    }    
    
    
    function buildSql2($tableName, $field, $where, $onlyData) {
        $_where = $where['_where'] ? $where['_where'] : '1';
        if ($onlyData) {
            $field = "*";
        }
        
        $sql = "SELECT $field FROM {$tableName} WHERE {$_where} ";
        //构造条件部分
        $where = $this->_db->escape($where);
        foreach ($where as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }
    
            if (is_array($value)) {
                $sql .= "AND `{$key}` IN ('" . implode("','", $value) . "') ";
            } else {
                isset($value) && $sql .= "AND `{$key}` = '{$value}' ";
            }
        }
    
        if (!$onlyData) {
            $where['_groupby'] && $sql .= "GROUP BY {$where['_groupby']} ";
            $sortKey = $where['_sortKey'] ? $where['_sortKey'] : $where['_sortExpress'];
            $sortDir = $where['_sortDir'] ? $where['_sortDir'] : $where['_sortDirection'];
            $sort = $where['_sort'] ? $where['_sort'] . ', ' : '';
            
            //排序
            if ($sortKey) {
                $sql .= "ORDER BY {$sort} {$sortKey}  {$sortDir} ";
            }
        }

        //标识是否锁行，注意的是也有可能锁表
        $where['_lockRow'] && $sql .= "FOR UPDATE ";
    
        return $sql;
    }
        
    
    /**
     * 检查表是否存在
     * @param string $tableName 表名
     * @param string $dbName 【可选】数据库名
     * @return bool true/false
     */
    function checkTableExist($tableName, $dbName = null) {
        if (strpos($tableName, ".")) {
            $parts = explode(".", $tableName);
            $dbName = $parts[0];
            $tableName = $parts[1];
        }
        
        if ($dbName) {
            //------------检查数据库是否存在
            $sql = "SELECT 1 FROM information_schema.SCHEMATA WHERE schema_name = '$dbName'";
            $result = $this->_db->getOne($sql);
            if (!$result) {
                return false;
            }
    
            //------------检查数据表是否存在
            $sql = "SHOW TABLES FROM $dbName LIKE '{$tableName}'";
            $result = $this->_db->getOne($sql);
        } else {
            //------------检查数据表是否存在
            $sql = "SHOW TABLES LIKE '{$tableName}'";
            $result = $this->_db->getOne($sql);
        }

        return !!$result;
    }
    
    /**
     * 【兼容函数】读取数据
     * @author benzhan
     * @param array $args 参数列表，特殊参数前缀_
     * @param array $keyWord 查询关键字, array('_field', '_where', '_limit', '_sortKey', '_sortDir', '_lockRow', '_tableName', '_groupby', '_foundRows')
     */
    private function getObject(array $where = array(), $keyWord = array()) {
        $where = array_merge($where, $keyWord);
        
        $fetch = $where['_fetch'];
        $fetch || $fetch = 'getAll';
        
        $field = $where['_field'];
        $field || $field = "*";
        
        if ($where['_foundRows']) {
            $field = "SQL_CALC_FOUND_ROWS {$field}";
        }

        $tableNames = (array) $this->getTableName($where);
        
        $allSql = '';
        foreach ($tableNames as $i => $tableName) { 
            //检查表名是不是表达式
            if (strpos($tableName, ' ') !== false) { 
                continue;
            }
            //检查表是否存在
//            if (!$this->checkTableExist($tableName)) {
//                unset($tableNames[$i]);
//            }
        }
        
       if (!$tableNames) {
           return array();
       }
        
        foreach ($tableNames as $i => $tableName) {
            if (count($tableNames) > 1) {
                //多表查询的情况
                $sql = $this->buildSql2($tableName, $field, $where, true);
                if ($allSql) {
                    $allSql .= "UNION ($sql)";
                } else {
                    $allSql = "($sql)";
                }
            } else  {
                //单表查询的情况
                $allSql = $this->buildSql2($tableName, $field, $where, false);
            }
        }
        
        if (count($tableNames) > 1) {
            $allSql = str_replace('SQL_CALC_FOUND_ROWS', '', $allSql);
            $allSql = $this->buildSql2("({$allSql}) AS t", $field, $where, false);
        }
        
        // Tool::debug($allSql);
        if ($where['_debug']) {
            var_dump($allSql);
            exit;
        }

        if (!$this->_onlyRecordSql) {
            if ($where['_foundRows']) {
                $rows = $this->_db->$fetch($allSql, $where['_limit']);
                $foundRows = $this->_db->getOne('SELECT FOUND_ROWS()');
                $data = compact('rows', 'foundRows');
            } else {
                $data = $this->_db->$fetch($allSql, $where['_limit']);
            }
            return $data;
        } else {
            $this->_db->recordSql('[only_record] ' . $allSql);
            return [];
        }
    }

    
    /**
     * 读取数据
     * @author benzhan
     * @param array $args 参数列表，特殊参数前缀_
     * @param array $keyWord 查询关键字, array('_field', '_where', '_limit', '_sortKey', '_sortDir', '_lockRow', '_tableName', '_groupby')
     * @return array 返回二维数组
     */
    function getAll(array $where = array(), $keyWord = array()) {      
        return $this->getObject($where, $keyWord);
    }
    
    /** 
     * 获取数据的行数
     * @author benzhan
     * @param array $args 参数列表，特殊参数前缀_
     * @param array $keyWord 查询关键字, array('_field', '_where', '_lockRow', '_tableName')
     * @return int 行数
     */
    function getCount(array $where = array(), $keyWord = array()) {
        $keyWord['_field'] = 'COUNT(*)';
        return (int) $this->getOne($where, $keyWord);
    }
    
    /**
     * 获取上次SQL_CALC_FOUND_ROWS查询的行数
     * @author benzhan
     */
    function getFoundRows() {
        $sql = "SELECT FOUND_ROWS()";
        // Tool::debug($sql);
        if (!$this->_onlyRecordSql) {
            $this->found_rows = $this->_db->getOne($sql);
        } else {
            $this->_db->recordSql('[only_record] ' . $sql);
            $this->found_rows = 0;
        }
        return $this->found_rows;
    }
    
    /**
     * 统计总记录数
     * @author benzhan
     */
    function getFoundRowsByCount(array $where = array(), $keyWord = array()) {
        $sql = $this->buildSql($where, $keyWord, true);
        if (!$this->_onlyRecordSql) {
            $this->found_rows = $this->_db->getOne($sql);
            return $this->found_rows;
        } else {
            $this->_db->recordSql('[only_record] ' . $sql);
            return 0;
        }

    }    

    /** 
     * 获取一行一列
     * @author benzhan
     * @param array $args 参数列表，特殊参数前缀_
     * @param array $keyWord 查询关键字, array('_field', '_where', '_lockRow', '_tableName')
     */
    function getOne(array $where = array(), $keyWord = array()) {
        $keyWord['_fetch'] = 'getOne';
        $keyWord['_limit'] = 1;
        return $this->getObject($where, $keyWord);
    }
    
    /** 
     * 获取一列
     * @author benzhan
     * @param array $args 参数列表，特殊参数前缀_
     * @param array $keyWord 查询关键字, array('_field', '_where', '_lockRow', '_tableName')
     */
    function getCol(array $where = array(), $keyWord = array()) {
        $keyWord['_fetch'] = 'getCol';
        return $this->getObject($where, $keyWord);
    }

    /**
     * 读取一行数据
     * @author benzhan
     * @param array $args 参数列表，特殊参数前缀_
     * @param array $keyWord 查询关键字, array('_field', '_where', '_lockRow', '_tableName')
     */
    function getRow(array $where = array(), $keyWord = array()) {      
        $args['_limit'] = 2;
        $datas = $this->getObject($where, $keyWord);
        if (count($datas) > 1) {
            //throw new Exception('查询的结果大于1条。');
        } else if (count($datas) <= 0) {
            return null;
        } 
        
        return (array) current($datas);
    }
    
    /**
     * 如果不存在，则插入一行数据(过期接口,请使用addObjectNx)
     * @author benzhan
     * @param array $args 参数列表
     */
    function addObjectIfNoExist(array $args, array $where) {
        return $this->addObjectNx($args, $where);
    }

    /**
     * 如果不存在，则插入一行数据
     * @author benzhan
     * @param array $args 参数列表
     */
    function addObjectNx(array $args, array $where) {
        if ($this->getCount($where)) { return true; }
        return $this->addObject($args);
    }
    
    /**
     * INSERT一行数据
     * @author benzhan
     * @param array $args 参数列表
     */
    function addObject(array $args) {
        return $this->_addObject($args, 'add');
    }
   
    /**
     * REPLACE一行数据
     * @author benzhan
     * @param array $args 参数列表
     */
    function replaceObject(array $args) {
        return $this->_addObject($args, 'replace');
    }
    
    private function _addObject(array $args, $type = 'add') {
        $sql = ($type == 'add' ? 'INSERT INTO ' : 'REPLACE INTO ');
        $tableName = $this->getTableName($args);
        $args = $this->_db->escape($args);
        $sql .= "{$tableName} SET " . $this->genBackSql($args, ', ');
        // Tool::debug($sql);
        if (!$this->_onlyRecordSql) {
            $this->_db->update($sql);
            return $this->_db->affectedRows();
        } else {
            $this->_db->recordUdateSql('[only_record] ' . $sql);
            $this->_db->recordSql('[only_record] ' . $sql);
            return false;
        }
    }
    
    /**
     * INSERT多行数据
     * @author benzhan
     * @param array $cols 列名 
     * @param array $args 参数列表
     */
    function addObjects(array $cols, array $args) {
        return $this->_addObjects($cols, $args, 'add');
    }
    
    private function getArrayCol($args) {
        if (!$args) { return false; }
        $value = current($args);
        return array_keys($value);
    }
    
    /**
     * INSERT多行数据
     * @author benzhan
     * @param array $args array(array(key => $value, ...))
     */
    function addObjects2(array $args) {
        $cols = $this->getArrayCol($args);
        if (!$cols) { return false; }
        
        return $this->addObjects($cols, $args);
    }

    function addOrUpdateObjects(array $args) {
        $cols = $this->getArrayCol($args);
        if (!$cols) { return false; }

        return $this->_addObjects($cols, $args, 'addOrUpdate');
    }
    
    /**
     * INSERT多行数据(避免重复插入记录)
     * @author hawklim
     * @param array $args array(array(key => $value, ...))
     */
    function addObjectsIfNoExist(array $args) {
        $cols = $this->getArrayCol($args);
        if (!$cols) { return false; }

        return $this->_addObjects($cols, $args, 'addIfNotExist');
    }

    /**
     * REPLACE多行数据
     * @author benzhan
     * @param array $cols 列名 
     * @param array $args 参数列表
     */
    function replaceObjects(array $cols, array $args) {
        return $this->_addObjects($cols, $args, 'replace');
    }
    
    /**
     * REPLACE多行数据
     * @author benzhan
     * @param array $args array(array(key => $value, ...))
     */
    function replaceObjects2(array $args) {
        $cols = $this->getArrayCol($args);
        if (!$cols) { return false; }
    
        return $this->replaceObjects($cols, $args);
    }

    private function _addObjects2(array $cols, array $args, $type = 'add') {
        // $sql = ($type == 'add' ? 'INSERT ' : 'REPLACE ');
        if ($type == 'add' || $type == 'addOrUpdate') {
            $sql = 'INSERT ';
        } elseif($type == 'addIfNotExist') {
            $sql = 'INSERT IGNORE ';
        } else {
            $sql = 'REPLACE ';
        }

        $tableName = $this->getTableName($args);
        $args = $this->_db->escape($args);

        $sql .= "{$tableName} (`" . join("`,`", $cols) . "`) VALUES ";
        foreach ($args as $value) {
            $sql .= "('" . join("', '", $value) . "'),";
        }
        $sql = substr($sql, 0, -1);

        if ($type == 'addOrUpdate') {
            $sql .= ' ON DUPLICATE KEY UPDATE ';
            $parts = [];
            foreach ($cols as $col) {
                $parts[] = "`{$col}`=VALUES(`{$col}`)";
            }
            $sql .= join(', ', $parts);
        }

        // Tool::debug($sql);
        if (!$this->_onlyRecordSql) {
            $this->_db->update($sql);
            return $this->_db->affectedRows();
        } else {
            $this->_db->recordUdateSql('[only_record] ' . $sql);
            $this->_db->recordSql('[only_record] ' . $sql);
            return false;
        }
    }
    
    private function _addObjects(array $cols, array $args, $type = 'add') {
        $args = array_chunk($args, 3000);
        $affectedNum = 0;
        foreach ($args as $datas) {
            $num = $this->_addObjects2($cols, $datas, $type);
            if ($num) {
                $affectedNum += $num;
            }
        }

        return $affectedNum;
    }
    
    /**
     * 获取最后自增的id
     * @author benzhan
     */
    function getInsertId() {
        return $this->_db->lastID();
    }
    
    /**
     * 获取影响行数
     * @author benzhan
     */
    function getAffectedRows() {
        return $this->_db->affectedRows();
    }
    
    /**
     * 修改一条数据
     * @author benzhan
     * @param array $args 更新的内容
     * @param array $where 更新的条件
     */
    function updateObject(array $args, array $where) {
        $args = $this->_db->escape($args);
        $where = $this->_db->escape($where);
        $tableName = $this->getTableName($args);
        
        if (!$where) {
            throw new DB_Exception('更新数据不能没有限制条件');
        }
        
        $sql = "UPDATE `{$tableName}` SET " . $this->genBackSql($args, ', ') . ' WHERE 1 '. $this->genFrontSql($where, 'AND ');
        // var_dump($sql);exit;
        if (!$this->_onlyRecordSql) {
            $this->_db->update($sql);
            return $this->_db->affectedRows();
        } else {
            $this->_db->recordUdateSql('[only_record] ' . $sql);
            $this->_db->recordSql('[only_record] ' . $sql);
            return false;
        }
    }
    
    /**
     * 删除数据
     * @author benzhan
     * @param array $where 更新的条件
     */
    function delObject(array $where) {
        $where = $this->_db->escape($where);
        $tableName = $this->getTableName($where);
        
        if (!$where) {
            throw new DB_Exception('删除数据不能没有限制条件');
        }
        
        $sql = "DELETE FROM `{$tableName}` WHERE 1 " . $this->genFrontSql($where, 'AND ');
        // Tool::debug($sql);
        if (!$this->_onlyRecordSql) {
            $this->_db->update($sql);
            return $this->_db->affectedRows();
        } else {
            $this->_db->recordUdateSql('[only_record] ' . $sql);
            $this->_db->recordSql('[only_record] ' . $sql);
            return false;
        }
    }
    
    /**
     * 把key => value的数组转化为后置连接字符串 
     * @author benzhan
     * @param array $args
     * @param string $connect
     */
    function genBackSql(array $args, $connect = ', ') {
        $str = '';
        foreach ($args as $key => $value) {
            if (is_array($value)) {
                $str .= "`$key` IN ('" . join("','", $value) . "') " . $connect; 
            } else if (isset($value)) {
                $str .= "`$key` = '$value'" . $connect; 
            } else {
                $str .= "`$key` IS NULL" . $connect; 
            }
        }
        return substr($str, 0, -strlen($connect));
    }
    
    /**
     * 把key => value的数组转化为前置连接字符串 
     * @author benzhan
     * @param array $args
     * @param string $connect
     */
    function genFrontSql(array $args, $connect = 'AND ') {
        $str = $args['_where'] ? "{$connect} {$args['_where']} " : '';
//        $str = '';
        foreach ($args as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }

            if (is_array($value)) {
                $str .= "$connect `$key` IN ('" . join("','", $value) . "') "; 
            } else if (isset($value)) {
                $str .= "$connect `$key` = '$value' "; 
            } else {
                $str .= "$connect `$key` IS NULL "; 
            }
        }
        return $str;
    }
    
    function autoCommit($flag) {
        $this->_db->autoCommit($flag);
    }
    
    function tryCommit() {
        $this->_db->tryCommit();
    }
    
    function commit() {
        $this->_db->commit();
    }
    
    function rollback() {
        $this->_db->rollback();
    }
    
    function escape($value) {
        return $this->_db->escape($value);
    }
        
    function getTableName(&$args = []) {
        if (isset($args['_tableName'])) {
            $tableName = arrayPop($args, '_tableName');
        } else {
            $tableName = $this->_tableName;
        }
        
        return $tableName;
    }

    /**
     *
     * @return DB_MySQLi
     * @author benzhan
     */
    public function getDb() {
        return $this->_db;
    }
    
    public function isTableExist($db, $table) {
        $sql = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME='{$table}' AND TABLE_SCHEMA='{$db}'";
        if (!$this->_onlyRecordSql) {
            return $this->_db->getRow($sql);
        } else {
            $this->_db->recordSql('[only_record] ' . $sql);
            return false;
        }
    }
    
        
}


//end of script
