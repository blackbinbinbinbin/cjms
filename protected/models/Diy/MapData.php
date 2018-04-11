<?php
/**
 * Created by PhpStorm.
 * @author benzhan
 * Date: 15/9/17
 * Time: 下午6:05
 */

class Diy_MapData {

    private static $map = [];

    /**
     * 获取db对象
     * @param array $tableInfo 表的信息
     * @author benzhan
     */
    public static function getDbKey($tableInfo) {
        if ($tableInfo['nameDb']) {
            return $tableInfo['nameDb'];
        } else if ($tableInfo['dbId']) {
            // dbId覆盖原来数据
            $objDiyDb = new Diy_Db();
            $row = $objDiyDb->objTable->getRow(['dbId' => $tableInfo['dbId']]);
            $tableInfo = array_merge($tableInfo, $row);
            $id = $tableInfo['dbId'];
            $tempDbKey = "tempDb{$id}";
        } else {
            $id = $tableInfo['tableId'];
            $tempDbKey = "tempTable{$id}";
        }

        if (!$GLOBALS['dbInfo'][$tempDbKey]) {
            $tableInfo['sourceType'] || $tableInfo['sourceType'] = "mysqli";
            $tableInfo['sourceDb'] || $tableInfo['sourceDb'] = 'information_schema';
            $dbInfo = array (
              'enable' => true,
              'dbHost' => $tableInfo['sourceHost'],
              'dbPort' => $tableInfo['sourcePort'],
              'dbName' => $tableInfo['sourceDb'],
              'dbUser' => $tableInfo['sourceUser'],
              'dbPass' => $tableInfo['sourcePass'],
              'dbType' => $tableInfo['sourceType']
            );

            $GLOBALS['dbInfo'][$tempDbKey] = $dbInfo;
        }

        return $tempDbKey;
    }

    /**
     * 获取db对象
     * @param array $tableInfo 表的信息
     * @author benzhan
     * @return DB_MySQLi
     */
    public static function getDb($tableInfo) {
        global $db;
        $tempDbKey = self::getDbKey($tableInfo);

        $db = DB::init($GLOBALS['dbInfo']);

        if ($_GET['_debug'] && $_GET['step'] == 10) {
            var_dump($tempDbKey);
            var_dump($GLOBALS['dbInfo']);
            var_dump($db[$tempDbKey]);
            exit;
        }

        return $db[$tempDbKey];
    }

    /**
     * 获取表名和where
     * @param array $mapInfo
     * @param array $where
     * @return array('tableName', 'where')
     * @author benzhan
     */
    private static function _getTableNameInfo($mapInfo, $where) {
        $sourceCallBack = trim($mapInfo['sourceCallBack']);
        if ($sourceCallBack) {
            $func = create_function('&$_where', $sourceCallBack);
            $tableName = $func($where);
        } else {
            $tableName = $mapInfo['sourceTable'];
        }

        return compact('tableName', 'where');
    }

    public static function getData($mapKey, $where = [], $oneArr = false) {
        $cacheKey = "{$mapKey}_{$oneArr}_" . json_encode($where);
        if (!isset(self::$map[$cacheKey])) {

            $objMap = new Diy_Map();
            $objDb = new Diy_Db();

            $mapInfo = $objMap->objTable->getRow(compact('mapKey'));
            if ($mapInfo['nameDb'] && $mapInfo['dbId']) {
                $dbInfo = $objDb->objTable->getRow(['dbId' => $mapInfo['dbId']]);
                $dbInfo && $mapInfo += $dbInfo;
            }

            /**
             * @var DB_MySQLi
             */
            $db = self::getDb($mapInfo);

            $keyField = trim($mapInfo['keyName']);
            $valField = trim($mapInfo['valueName']);

            if ($mapInfo['sourceCallBack']) {
                $arr = self::_getTableNameInfo($mapInfo, $where);
                $tableName = $arr['tableName'];
                $where = $arr['where'];
            } else {
                $tableName = "`{$mapInfo['sourceTable']}`";
            }

            if (strpos($keyField, '_') == false && strpos($keyField, ' ') == false) {
                $keyField = "`{$keyField}`";
            }

            if (strpos($valField, '_') == false && strpos($valField, ' ') == false) {
                $valField = "`{$valField}`";
            }

            $sql = "SELECT {$keyField}, {$valField} FROM {$tableName} WHERE 1 ";

            if ($where) {
                foreach ($where as $key => $value) {
                    if (is_array($value)) {
                        $sql .= "AND {$key} IN ('" . join("','", $value) . "') ";
                    } else {
                        $sql .= "AND {$key} = '{$value}' ";
                    }
                }
            }

            $filter = self::getMapFilter($mapInfo);
            if ($filter) {
                $sql .= "AND {$filter} ";
            }
            // $sql .= ' GROUP BY ' . $keyField;
            $datas = $db->getAll($sql, null, DB::DB_FETCH_ROW);
            $dataCallBack = $mapInfo['dataCallBack'];
            if ($dataCallBack) {
                $func = create_function('$datas', $dataCallBack);
                $datas = $func($datas);
            }

            $map = array();
            foreach ($datas as $data) {
                if ($oneArr) {
                    $map[$data[0]] = $data[1];
                } else {
                    $map[$data[0]][] = $data[1];
                }
            }
            self::$map[$cacheKey] = $map;
        }

        return self::$map[$cacheKey];
    }

    public static function getMapFilter($mapInfo) {
        $filter = trim($mapInfo['mapFilter']);
        if (!$filter) { return ''; }

        try {
            if (strpos($filter, '::') === 0) {
                $filter = ltrim($filter, ':');
                $func = create_function('$_mapInfo', $filter);
                return $func($mapInfo);
            } else {
                return $filter;
            }
        } catch (Exception $ex) {
            Tool::err($ex);
            return '';
        }

    }


}