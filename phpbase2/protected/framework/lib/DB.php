<?php

/**
 * DB 抽象类
 * 其中主要是创建了一个静态变量$db，所有集成类的对象实例化到$db中方便调用
 * 该抽象类初始化时候根据配置文件存入$db变量，并调用子类进行DB实例化，使用DB::init()进行调用
 * 本类只实现了一个静态方法，并规定了其子类必须实现的一些方法。
 *
 */
abstract class DB {
    const DB_FETCH_ASSOC    = 1;
    const DB_FETCH_ARRAY    = 3;
    const DB_FETCH_ROW      = 2;
    const DB_FETCH_DEFAULT  = self::DB_FETCH_ASSOC;
    
    const DB_TYPE_MYSQLI = 'mysqli';
    const DB_TYPE_ORACLE = 'oracle';

    public static $db = array();

    protected static $dbType = array(
        'mysqli' => 'MySQLi',
        'oracle' => 'Oracle',
        'clickhouse' => 'ClickHouse',
    );

    public $dsn;
    protected $uConn;
    protected $qConn;
    protected $dbKey;
    protected $sql;
    protected $sqls;
    protected $qrs;
    protected $urs;
    protected $recordUdate = true;
    protected $uSqls;
    protected $qSqls;
    protected $queryNum;
    protected $updateNum;
    protected $debug = DEBUG;

    protected function __construct() {
    }

    /**
     * DB初始化
     *
     * @param array $dsn 配置文件中的DB信息
     * @param string $key dbKey
     * @return DB DB对象
     */
    public static function init($dsn, $key = null) {
        if ($key && self::$db[$key]) {
            return self::$db[$key];
        }

        foreach ($dsn as $dbKey => $dbInfo) {
            if ($dbInfo['enable'] !== false) {
                if (empty(self::$db[$dbKey])) {
                    $dbType = self::$dbType[strtolower($dbInfo['dbType'])];
                    if ($dbType) {
                        $className = 'DB_' . $dbType;
                        self::$db[$dbKey] = new $className($dbInfo, $dbKey);
                    } else {
                        Tool::err("dbKey:{$dbKey} is error, dbType:{$dbInfo['dbType']}");
                    }
                } else {
                    // Tool::debug("dbKey:{$dbKey} is not empty.");
                }
            }
        }

        if ($key) {
            return self::$db[$key];
        } else {
            return self::$db;
        }
    }

    public abstract function connect($type = "slave");
    public abstract function close();
    public abstract function query($sql, $limit = null, $quick = false);
    public abstract function update($sql);
    public abstract function getOne($sql);
    public abstract function getCol($sql, $limit = null);
    public abstract function getRow($sql, $fetchMode = self::DB_FETCH_DEFAULT);
    public abstract function getAll($sql, $limit = null, $fetchMode = self::DB_FETCH_DEFAULT);

    public function getUpdateSql() {
        return $this->uSqls;
    }

    public function getSql() {
        return $this->sqls;
    }

    /**
     * 记录upadte语句
     * @param string $sql sql语句
     * @author benzhan
     */
    public function recordUdateSql($sql = null, $startTime = 0) {
//        if ($this->debug || $this->recordUdate) {
        if ($this->recordUdate) {
            $current = microtime(true);
            if (count($this->uSqls) < 100) {
                $sql = $sql ?: $this->sql;
                if ($startTime > 0) {
                    $span = round($current - $startTime, 4);
                    $sql = "[exec:{$span}] {$sql}";
                }

                $this->uSqls[$current] = $sql;
            } else {
                $this->uSqls[$current] = 'skig:' . (count($this->uSqls) + 1);
            }
        }
    }

    /**
     * 记录当前sql语句
     * @param string $sql sql语句
     * @author benzhan
     */
    public function recordSql($sql = null, $startTime = 0) {
//        if ($this->debug) {
            $current = microtime(true);
            $index = ($current * 10000) % 1000000;
            if (count($this->sqls) < 300) {
                $sql = $sql ?: $this->sql;
                if ($startTime > 0) {
                    $span = round($current - $startTime, 4);
                    $sql = "[{$this->dbKey}] [exec:{$span}] {$sql}";
                }

                $this->sqls[$index] = $sql;
            } else {
                $this->sqls[$index] = 'skig:' . (count($this->sqls) + 1);
            }
//        }
    }

    public function clearSql($sql = null) {
        $this->sqls = [];
    }

    public function setRecordUdateSql($flag = true) {
        $this->recordUdate = $flag;
    }
    
    public function debug($flag = true) {
        $this->debug = $flag;
    }

    public function isDebug() {
        return $this->debug;
    }


    /**
     * 转义需要插入或者更新的字段值
     *
     * 在所有查询和更新的字段变量都需要调用此方法处理数据
     *
     * @param mixed $str 需要处理的变量
     * @return mixed 返回转义后的结果
     */
    public function escape($str) {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = $this->escape($value);
            }
        } else {
            return addslashes($str);
        }
        return $str;
    }

    public function unescape($str) {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = $this->unescape($value);
            }
        } else {
            return stripcslashes($str);
        }
        return $str;
    }
}


//end of script
