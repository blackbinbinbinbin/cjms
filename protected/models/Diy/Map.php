<?php

// require_once ROOT_PATH . 'conf/diyConfig.inc.php';

/**
 * 扩展的字典表
 * @author benzhan
 */
class Diy_Map extends Model {
    protected $tableName = 'Cmdb3Map';
    protected $dbKey = "Report";
//
//    private static $map;
//
//    /**
//     * 通用的获取一对一【指 key 与 value的映射关系是一对一】的字典表
//     * @param array $options array('keyField', 'valField', 'where', 'valKey')
//     * @param string $dbKey
//     * @return array
//     */
//    private static function commonGetMap($options, $tableName, $dbKey = 'rms') {
//        /**
//         * @var DB_MySQLi
//         */
//        $db = DB::init($GLOBALS['dbInfo']);
//
//        $keyField = $options['keyField'];
//        $valField = $options['valField'];
//
//        $where  = $options['where'];
//        $cacheKey = "{$dbKey}_{$tableName}_{$keyField}_{$valField}_{$options['valKey']}_" . json_encode($where);
//        if (!isset(self::$map[$cacheKey])) {
//
//            $sql = "SELECT $keyField,$valField FROM $tableName WHERE 1 ";
//
//            $_where = arrayPop($options, '_where');
//            if ($_where) {
//                $sql .= "AND {$_where} ";
//            }
//
//            if ($where) {
//                $db[$dbKey]->escape($where);
//
//                $key = $options['valKey'] ? $valField : $keyField;
//                if (is_array($where)) {
//                    $sql .= "AND {$key} IN ('" . join("','", $where) . "') ";
//                } else {
//                    $sql .= "AND {$key} = '{$where}' ";
//                }
//            }
//
//            $where2  = (array) $options['_where'];
//            foreach ($where2 as $key => $val) {
//                if (is_array($val)) {
//                    $sql .= "AND {$key} IN ('" . join("','", $val) . "') ";
//                } else {
//                    $sql .= "AND {$key} = '{$val}' ";
//                }
//            }
//
//            // Tool::debug($sql);
//
//            $sql .= ' GROUP BY ' . $keyField;
//            $datas = $db[$dbKey]->getAll($sql);
//
//            if ($options['valKey']) {
//                $retValue = array();
//                foreach ($datas as $data) {
//                    $retValue[$data[$valField]] = $data[$keyField];
//                }
//
//                // 没找到翻译的也要补充回去
//                if ($where) {
//                    foreach ($where as $value) {
//                        $retValue[$value] || $retValue[$value] = $value;
//                    }
//                }
//
//                return $retValue;
//            } else {
//                $map = array();
//                foreach ($datas as $data) {
//                    $map[$data[$keyField]][] = $data[$valField];
//                }
//                self::$map[$cacheKey] = $map;
//            }
//        }
//
//        return self::$map[$cacheKey];
//    }
//
//
//    /**
//     * 获取作家名称
//     * @param $options
//     * @return array
//     * @author ben
//     */
//    public static function getAuthorName($options) {
//        return self::commonGetMap($options, 'author', 'book_db');
//    }
//
//    /**
//     *
//     * @param $options
//     * @return array
//     * @author benzhan
//     */
//    public static function getBookName($options) {
//        return self::commonGetMap($options, 'book', 'book_db');
//    }
//
//    public static function getShopName($options) {
//        return self::commonGetMap($options, 'shop', 'ojia_shop');
//    }
//
//    public static function getBarName($options) {
//        $extraDatas = [];
//        $extraDatas['0'][] = '全部';
//
//        $datas = self::commonGetMap($options, 'bar', 'bbs_db');
//        return $extraDatas + $datas;
//    }
//
//    public static function getVoteItemName($options) {
//        return self::commonGetMap($options, 'vote_activity_item', 'oujhome');
//    }
//
//    public static function getDbName($options) {
//        return self::commonGetMap($options, 'Cmdb3Db', 'Report');
//    }

}



