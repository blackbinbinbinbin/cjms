<?php

class Diy_Data {
    private $_dbKey = "Report";
    /**
     * @type Diy_DataHive
     */
    private $_objDataHive = null;
    /**
     * @type TableHelper;
     */
    public $oBase = null;

    public function __construct() {
        $this->_objDataHive = new Diy_DataHive();
    }

    public function getDataHive() {
        return $this->_objDataHive;
    }
    
    /**
     * 读取tableId的数据
     * @param array $args array ('tableId', 'fields', 'where', 'keyWord', 'getCount') 
     * int $tableId 表id
     * array $where 查询条件, array(array('field' => 'id', 'opt' => '=', 'val' => '12'), array(), array())
     * array $fields 选择列 array('fieldName1', 'fieldName2')
     * array $keyWord 查询关键字, array('_limit', '_sortKey', '_sortDir', '_lockRow', '_tableName', '_groupby')
     * string $getCount，是否要返回行数
     * @author benzhan
     */
    public function getTableData($args) {
        $objField = new Diy_Field();
        //修改为只查询展现出来的字段 edit by benzhan 2012-10-23
        $fields = $objField->getFields($args['tableId'], true);

        $fields = $this->_getCalField($fields, $args['keyWord']);
        if ($args['fields']) {
            //如果有传入fields，则按条件过滤
            $sortedFields = arrayFilter((array) $args['fields'], $fields);
            $sortedFields += $fields;
        } else {
            //如果没有传入fields，则过滤出默认的展示项
            $sortedFields = array();
            foreach ($fields as $fieldName => $field) {
                if ($field['defaultDisplay']) { 
                    $sortedFields[$fieldName] = $field; 
                }
            }
        }
        $this->oBase = $this->_getBase($args);
        $keyWord = $this->_getKeyWord($args, $fields);
        //将字典表的值转化为原始值
        $args['where'] = $this->_getTranslateWhere($fields, $args['where']);

        $keyWord['_where'] = $this->getWhereStr($args['where']);
        $sql = '';
//        $sql = 'SQL_CALC_FOUND_ROWS ';
        if ($sortedFields) {
            foreach ( $sortedFields as $fieldName => $field) {
                if ($field['fieldVirtualValue']) {
                    $sql .= "{$field['fieldVirtualValue']} AS `{$fieldName}`,";
                } else {
                    $sql .= "`{$fieldName}`,";
                }
            }
        }

        if ($args['_field']) {
            $keyWord['_field'] = $args['_field'];
        } else {
            $keyWord['_field'] = substr($sql, 0, -1);
        }

        if ($_GET['_debug']) {
            $keyWord['_debug'] = true;
        }

        $datas = $this->_objDataHive->getTableData($this->oBase, $fields, $keyWord, $args);
        return $datas;
    }

    private function _getTranslateGroupby($fields, $groupby) {
//        var_dump($groupby);
        if ($groupby) {
            $parts = explode(',', $groupby);
            foreach ($parts as $k => $part) {
                $v = trim(trim($part), '`');
                $field = $fields[$v];

                if ($field['fieldVirtualValue']) {
                    $parts[$k] = $field['fieldVirtualValue'];
                }
            }

            $groupby = join(',', $parts);
        }
//        var_dump($groupby);exit;
        return $groupby;
    }

    private function _getTableDataNumByCount($args) {
    	$objField = new Diy_Field();
    	$fields = $objField->getFields($args['tableId']);
    	$fields = $this->_getCalField($fields, $args['keyWord']);
//    	$sortedFields = arrayFilter((array) $args['fields'], $fields);
//    	$sortedFields += $fields;
    
    	$this->oBase = $oBase = $this->_getBase($args);
    	$tableName = $oBase->getTableName($args);
        if (strpos($tableName, ' ') === false) {
            if (!$oBase->checkTableExist($tableName)) {
                if ($args['_debug'] == 1) {
                    var_dump("$tableName is not exist.");
                    exit;
                }
                return 0;
            }
        }

    	$keyWord = $this->_getKeyWord($args, $fields);
    	//将字典表的值转化为原始值
    	$args['where'] = $this->_getTranslateWhere($fields, $args['where']);
    	$keyWord['_where'] = $this->getWhereStr($args['where']);

    	// 翻译groupby
        $keyWord['_groupby'] = $this->_getTranslateGroupby($fields, $keyWord['_groupby']);

        $keyWord['_field'] = "count(*)";
    	$sql = $oBase->buildSql($keyWord, array(), true);
    	return $oBase->getDb()->getOne($sql);
    }    
    
    /**
     * 获取行数
     * @param array $args array ('tableId', 'fields', 'where', 'keyWord', 'getCount')  同上
     * @author benzhan
     */
    public function getTableDataNum($args) {
        $rowNum = $this->_objDataHive->getRowNum();

        if ($rowNum == -1) {
            $rowNum = $this->_getTableDataNumByCount($args);
//            $oBase = $this->_getBase($args);
//            $rowNum = $oBase->getFoundRows();
        }

        return $rowNum;
    }
    
    
    /**
     * 获取需要合并的列
     * @author benzhan
     */
    function getMergeField($fields) {
        $mergeFieldNames = array();
        foreach ($fields as $field) {
            if ($field['needMerge']) {
                $mergeFieldNames[$field['fieldName']] = $field['fieldName'];
                $count[$field['fieldName']] = 1;
            }
        }
    
        return $mergeFieldNames;
    }
    
    private function _getKeyWord(&$args, $fields) {
        $keyWord = $args['keyWord'];
        if (!$keyWord['_sortKey']) { 
            //判断是否有默认排序
            foreach ($fields as $field) {
                if ($field['defaultSortOrder'] && $field['defaultDisplay']) {
                    $keyWord['_sortKey'] = $field['fieldName'];
                    $keyWord['_sortDir'] = $field['defaultSortOrder'];
                    break;
                }
            }
        }
        
        //判断是否有合并列
        /*
        $mergeFieldNames = $this->getMergeField($fields);
        foreach ($mergeFieldNames as $fieldName) {
            $dir = $keyWord['_sortKey'] == $fieldName ? $keyWord['_sortDir'] : 'ASC';
            
            if ($keyWord['_sort']) {
                $keyWord['_sort'] .= ", {$fieldName} {$dir}";
            } else {
                $keyWord['_sort'] = "{$fieldName} {$dir}";
            }
        }
        */
        $args['keyWord'] = $keyWord;
        return $keyWord;
    }
    
    /**
     * 将字典表的value翻译为key
     * @param array $fields
     * @param array $where
     * @author benzhan
     */
    private function _getTranslateWhere($fields, $where) {
        foreach ($where as $i => $value) {
            $fieldName = $value[0];
            $field = $fields[$fieldName];

            // 对时间字段特殊处理
            if ($field['fieldType'] == 'date' || $field['fieldType'] == 'datetime') {
                $upper = strtoupper($field['fieldVirtualValue']);
                if ($upper && strpos($upper, 'FROM_UNIXTIME') !== false && strpos($upper, 'UNIX_TIMESTAMP') === false) {
                    // 这个是int类型的时间戳
                    $where[$i][0] = $fieldName;
                    $where[$i][2] && $where[$i][2] = strtotime($where[$i][2]);
                    $where[$i][3] && $where[$i][3] = strtotime($where[$i][3]);
                    continue;
                }
            }

            // 其他字段通用处理
            $key = $field['fieldVirtualValue'] ? $field['fieldVirtualValue'] : $fieldName;
            $where[$i][0] = $key;
            // 这里有可能传入的是value，不需要翻译
            if ($field['needMap2'] && $field['mapKey']) {
                $objMap = new Diy_Map();
                $mapInfo = $objMap->objTable->getRow(['mapKey' => $field['mapKey']]);
                $keyField = $mapInfo['keyName'];
                $valField = $mapInfo['valueName'];

                $objTable = new TableHelper($mapInfo['sourceTable'], $mapInfo['nameDb']);
                $part1 = array_slice($where[$i], 0, 2);
                $part2 = array_slice($where[$i], 2);
                if ($part2) {
                    $where2 = [$valField => $part2];
                    $keyWord = ['_field' => $keyField];
                    $newPart2 = $objTable->getCol($where2, $keyWord);
                    if (!$newPart2) {
                        $newPart2 = [-9299998];
                    }
                    $where[$i] = array_merge($part1, $newPart2);
                }
            }
//            if (!$field['fieldMap']) {  continue; }
//            $fieldMap = json_decode($field['fieldMap'], true);
//            if (!$fieldMap) {  continue; }
//
//            $funcName = $fieldMap['name'];
//            if ($value[1] == 'like') {
//                $args = $fieldMap + array('_where' => "{$fieldMap['valField']} LIKE '%{$value[2]}%'", 'valKey' => true);
//            } else {
//                $args = $fieldMap + array('where' => $value[2], 'valKey' => true);
//            }
//
//
//
//            $valueMap = Diy_Map::$funcName($args);
//
//            //这里比较恶心，有可能当前的值为key无需翻译，目前图片列表就是这样
//            if (!$valueMap) { continue; }
//
//            $where[$i] = array($key, 'in', $valueMap);
        }


    
        return $where;
    }
    
    private function _getBase(&$args) {
        $this->_checkTableDataParam($args);
        $args['where'] = ( array ) $args['where'];
        
        $oConfig = new Diy_Table();
        $tableInfo = $oConfig->getTableInfo($args['tableId']);
        
        $info = $this->_getTableNameInfo($tableInfo, $args['where']);
        $tableName = $info['tableName'];
        $args['where'] = $info['where'];

        $db = $this->getDb($tableInfo);
        if ($_GET['_debug'] && $_GET['step'] == 9) {
            var_dump($tableInfo);
            var_dump($db);
            exit;
        }
        
        $oBase = new TableHelper($tableName, $db);
        return $oBase;
    }
    
    /**
     * 检查是否存在非法的分组计算的情况
     * @author benzhan
     */
    private function _checkInvalidGroupFunction($keys, $virtualValue) {
        $virtualValue = strtoupper($virtualValue);
        $key = join('|', $keys);
       
        return preg_match("/^({$key})(\\s)*\\([\\w`]+\\)$/", $virtualValue);
    }
    
    /**
     * 格式化列的计算规则
     * @param array $fields
     * @param array $keyWord
     */
    private function _getCalField($fields, $keyWord) {
        $keys = array ('MAX', 'MIN', 'SUM', 'COUNT', 'AVG', 'DISTINCT');

        $values = null;
        foreach ( $keys as $key ) {
            $k = '_' . strtolower($key);
            if (!$keyWord[$k]) { continue; }

            if ($key == 'DISTINCT') {
                $left = $right = ' ';
            } else {
                $left = '(';
                $right = ')';
            }
            
            $values = explode(',', $keyWord[$k]);
            foreach ( $values as $value ) {
                if ($fields[$value]['fieldVirtualValue'] && !$this->_checkInvalidGroupFunction($keys, $fields[$value]['fieldVirtualValue'])) {
                    $fields[$value]['fieldVirtualValue'] = "{$key}{$left}{$fields[$value]['fieldVirtualValue']}{$right}";
                } else {
                    $fields[$value]['fieldVirtualValue'] = "{$key}{$left}`{$value}`{$right}";
                }
            }
        }

        if ($keyWord['_distinctCount']) {
            $values = explode(',', $keyWord['_distinctCount']);
            foreach ( $values as $value ) {
                if ($fields[$value]['fieldVirtualValue'] && !$this->_checkInvalidGroupFunction($keys, $fields[$value]['fieldVirtualValue'])) {
                    $fields[$value]['fieldVirtualValue'] = "COUNT(DISTINCT {$fields[$value]['fieldVirtualValue']})";
                } else {
                    $fields[$value]['fieldVirtualValue'] = "COUNT(DISTINCT `{$value}`)";
                }
            }
        }

        //如果存在计算规则，则检查是否传入了groupby
        if ($values && !$keyWord['_groupby']) {
            Tool::err('没有设置groupby字段！');
        }
        
        return $fields;
    }
    

    /**
     * 获取表名和where
     * @param array $tableInfo
     * @param array $where
     * @return array('tableName', 'where')
     * @author benzhan
     */
    private function _getTableNameInfo($tableInfo, $where) {
        $sourceCallBack = trim($tableInfo['sourceCallBack']);
        if ($sourceCallBack) {
            $func = create_function('&$_conditions', $sourceCallBack);
            //格式化后的where
            $_conditions = $this->getFormatWhere($where);

            $tableName = $func($_conditions);
            //$_condition可以被修改，所以要重置where
            $where = array();
            foreach ($_conditions as $key => $value) {
                foreach ($value as $opt => $v) {
                    $tempValue = array($key, $opt);
                    $tempValue = array_merge($tempValue, (array) $v);
                    $where[] = $tempValue;
                }
            }
        } else {
            $tableName = $tableInfo['sourceTable'];
        }
        
        return compact('tableName', 'where');
    }

        
    /**
     * 格式化数据，翻译字段、执行回调
     * @param array $datas 一维数据
     * @param array $fields 列信息
     * @author benzhan
     */
   public function formatRow($row, $fields) {
       $datas = array($row);
       $datas = $this->_objDataHive->formatDatas($datas, $fields);
       return current($datas);
   } 
    
    /**
     * 检查参数
     * @param array $args 
     * @author benzhan
     */
    private function _checkTableDataParam($args) {
//        global $_configs;
//        $opts = array_keys($_configs['opts']);
        $rules = array (
            'tableId' => 'string', 
            'fields' => array ('array', 
                'nullable' => true, 
                'emptyable' => true 
            ), 
            'where' => array ('array', 
                'nullable' => true,
                'elem' => 'array' 
            ), 
            'keyWord' => array ('array', 
                'emptyable' => true 
            ) 
        );
        
        Param::checkParam($rules, $args);
    }
    
    /**
     * 获取where语句
     * @param array $where 条件数据
     * @author benzhan
     */
    function getWhereStr($where) {
        $_where = '1 ';
        if ($where) {
            foreach ( $where as $value ) {
                $field = $value[0];
                if (strpos($field, '|||') === false) {
                    $item = $this->_getWhereItemStr($field, $value);
                    $item && $_where .= "AND {$item} "; 
                } else {
                    //支持|||分割的参数
                    $fields = explode('|||', $field);
                    $_where .= "AND (0 ";
                    foreach ($fields as $field) {
                        $field = trim($field);
                        $item = $this->_getWhereItemStr($field, $value);
                        $item && $_where .= "OR {$item} ";
                    }
                    $_where .= ") ";
                }
            }
        }
        
        return $_where;
    }
    
    private function _getWhereItemStr($field, $value) {
        $opt = $value[1];
        if (count($value) > 3) {
            $val = array_slice($value, 2);
        } else {
            $val = $value[2];
        }

        if (!isset($val) || $val === '' || !$field) {
            return '';
        }

        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $val[$k] = $this->oBase->escape($v);
            }
        } else {
            $val = $this->oBase->escape($val);
        }
        
        $item = $field . ' ';
        switch ($opt) {
            case 'like' :
                $item = "INSTR({$field},'{$val}') > 0 ";
                break;
            case 'like .%' :
                $item .= "LIKE '{$val}%' ";
                break;
            case 'like %.' :
                $item .= "LIKE '%{$val}' ";
                break;
            case ':' :
                if ($val[0] && $val[1]) {
                    $item .= "BETWEEN '{$val[0]}' AND '{$val[1]}' ";
                } else if ($val[0] ) {
                    $item .= ">= '{$val[0]}' ";
                } else if ($val[1]) {
                    $item .= "<= '{$val[1]}' ";
                }
                break;
            default :
                $opt = strtoupper($opt);
                if ($opt == 'IN' || $opt == 'NOT IN') {
                    if (is_string($val)) {
                        $val = explode("\n", $val);
                    }

                    $val = array_map('trim', $val);
                    $val = array_filter($val);
                    $item .= "{$opt} ('" . join("', '", $val) . "') ";
                } else {
                    $item .= "{$opt} '{$val}' ";
                }
            break;
        }
        
        return $item;
    }
    
    /**
     * 格式化where，返回以fieldName和opt为key, val为value
     * @param array $where
     * @author benzhan
     */
    function getFormatWhere($where) {
        $formatWhere = array();
        foreach ($where as $value) {
            if (count($value) == 3) {
                $v = $value[2];
            } else {
                $v = array_slice($value, 2);
            }

            $formatWhere[$value[0]][$value[1]] = $v;
        }
        return $formatWhere;
    }
    
    /**
     * 获取db对象
     * @param array $tableInfo 表的信息
     * @author benzhan
     */
    public function getDb($tableInfo) {
        return Diy_MapData::getDb($tableInfo);
    }
    
    /**
     * 获取db对象
     * @param string $tableId 表的id
     * @author benzhan
     */
    public function getDb2($tableId) {
        $oConfig = new Diy_Table();
        $tableInfo = $oConfig->getTableInfo($tableId);
        return $this->getDb($tableInfo);
    }
    
    /**
     * 获取helper对象
     * @param string $tableId 表的id
     * @author benzhan
     */
    public function getHelper($tableId) {
        $oConfig = new Diy_Table();
        $tableInfo = $oConfig->getTableInfo($tableId);
        $objDb = $this->getDb($tableInfo);
        return new TableHelper($tableInfo['sourceTable'], $objDb);
    }

    /**
     * 获取RedisKey
     * @param $args
     * @return dwRedis
     * @author benzhan
     */
    private function _getRedisKey($args) {
        if ($args['nameRedis']) {
            $key = $args['nameRedis'];
        } else {
            $key = "{$args['redisHost']}:{$args['redisPort']}:{$args['redisDb']}@{$args['redisPass']}";
            $GLOBALS['redisInfo'][$key] = [
              'host' => $args['redisHost'],
              'port' => $args['redisPort'],
              'pwd' => $args['redisPass'],
            ];

            if ($args['redisDb']) {
                $GLOBALS['redisInfo'][$key]['db'] = $args['redisDb'];
            }
        }

        return $key;
    }


    /**
     * 获取Redis对象
     * @param $args
     * @return dwRedis
     * @author benzhan
     */
    public function getRedis($args) {
        $key = $this->_getRedisKey($args);
        return dwRedis::init($key);
    }

    /**
     *
     * @param $tableId
     * @return Redis2Mysql
     * @author benzhan
     */
    public function getR2M($tableId) {
        $oConfig = new Diy_Table();
        $tableInfo = $oConfig->getTableInfo($tableId);

        $dbKey = Diy_MapData::getDbKey($tableInfo);
        $cacheKey = $this->_getRedisKey($tableInfo);

//        if (!$tableInfo['nameRedisKey']) {
//            // 写入dbInfo配置
//            $GLOBALS['r2mInfo'][$dbKey][$tableInfo['sourceTable']] = [
//              'key' => $tableInfo['redisKey'],
//              'ttl' => $tableInfo['redisTtl']
//            ];
//
//            $objR2m = new Redis2Mysql($tableInfo['sourceTable'], $dbKey, $cacheKey);
//        } else {
//            $objR2m = new R2m_Client($tableInfo['sourceTable'], $dbKey, $cacheKey);
//        }

        $objR2m = new Redis2Mysql($tableInfo['sourceTable'], $dbKey, $cacheKey);

        return $objR2m;
    }
}



