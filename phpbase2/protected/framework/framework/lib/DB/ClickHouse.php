<?php

require_once realpath(dirname(__FILE__)) . '/../vendor/autoload.php';

class DB_ClickHouse extends DB {
    /**
     * ClickHouse构造函数
     *
     * @param array $dbInfo 数据库配置信息
     * @param string $dbKey db的key
     */
    public function __construct($dbInfo, $dbKey) {
        $this->dbKey = $dbKey;
        $this->dsn = $dbInfo;
        $this->sqls = [];
        $this->uSqls = [];
        $this->qSqls = [];
    }

    /**
     * 连接数据库
     *
     * 连接数据库之前可能需要改变DSN，一般不建议使用此方法
     *
     * @param string $type 选择连接主服务器或者从服务器
     * @return boolean
     * @throws DB_Exception
     */
    public function connect($type = "slave") {
        $dbHost = $this->dsn["dbHost"];
        $dbName = $this->dsn["dbName"];
        $dbUser = $this->dsn["dbUser"];
        $dbPass = $this->dsn["dbPass"];
        $dbPort = $this->dsn["dbPort"];

        $config = [
            'host' => $dbHost,
            'port' => $dbPort,
            'username' => $dbUser,
            'password' => $dbPass
        ];

        $db = new ClickHouseDB\Client($config);
        $db->setConnectTimeOut(3);
        $db->setTimeout(10);
        $db->database($dbName);
        $this->uConn = $db;

        if (!$this->uConn) {
            throw new DB_Exception('更新数据库连接失败');
        }

        // 主库的qConn和uConn是一样的
        if (!isset($this->dsn["slave"])) {
            $this->qConn =& $this->uConn;
        }

        return true;
    }

    /**
     * 关闭数据库连接
     *
     * 一般不需要调用此方法
     */
    public function close() {
//        if ($this->uConn === $this->qConn) {
//            if (is_object($this->uConn)) {
//                mysqli_close($this->uConn);
//            }
//        } else {
//            if (is_object($this->uConn)) {
//                mysqli_close($this->uConn);
//            }
//            if (is_object($this->qConn)) {
//                mysqli_close($this->qConn);
//            }
//        }
    }

    /**
     * 执行一个SQL查询
     *
     * 本函数仅限于执行SELECT类型的SQL语句
     *
     * @param string $sql SQL查询语句
     * @param mixed $limit 整型或者字符串类型，如10|10,10
     * @param boolean $quick 是否快速查询
     * @return \ClickHouseDB\Statement 返回查询结果资源句柄
     * @throws DB_Exception
     */
    public function query($sql, $limit = null, $quick = false) {
        if ($limit != null) {
            if (!preg_match('/^\s*SHOW/i', $sql) && !preg_match('/FOR UPDATE\s*$/i', $sql) && !preg_match('/LOCK IN SHARE MODE\s*$/i', $sql)) {
                $sql = $sql . " LIMIT " . $limit;
            }
        }

        if (!$this->qConn || !$this->ping($this->qConn)) {
            $this->connect("slave");
        }

        $this->sql = $sql;
        $startTime = microtime(true);
//        $this->qConn->
        /**
         * @var ClickHouseDB\Client $conn
         */
        $conn = $this->qConn;
        $this->qrs = $conn->select($sql);
//        $this->qrs = mysqli_query($this->qConn, $sql, $quick ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT);
        $this->recordSql($sql, $startTime);

        if (!$this->qrs) {
            throw new DB_Exception('查询失败:' . mysqli_error($this->qConn) . "[sql:{$sql}]");
        } else {
            $this->queryNum++;
            return $this->qrs;
        }
    }

    /**
     * 获取结果集
     *
     * @param \ClickHouseDB\Statement $rs 查询结果资源句柄
     * @param int $fetchMode 返回的数据格式
     * @return array 返回数据集每一行，并将$rs指针下移
     */
    private function fetch($rs, $fetchMode = self::DB_FETCH_DEFAULT) {
        switch ($fetchMode) {
            case 1:
//                $fetchMode = self::DB_FETCH_ASSOC;
                return $rs->rows();
                break;
            case 2:
//                $fetchMode = self::DB_FETCH_ROW;
                return $rs->fetchOne();
                break;
            case 3:
//                $fetchMode = self::DB_FETCH_ARRAY;
                return $rs->fetchOne();
                break;
            default:
//                $fetchMode = self::DB_FETCH_DEFAULT;
                return $rs->rows();
                break;
        }
//        return mysqli_fetch_array($rs, $fetchMode);
    }

    /**
     * 执行一个SQL更新
     *
     * 本方法仅限数据库UPDATE操作
     *
     * @param string $sql 数据库更新SQL语句
     * @param boolean $recordSql 是否记录sql
     * @return boolean
     * @throws DB_Exception
     */
    public function update($sql, $recordSql = true) {
        if (!$this->uConn || !$this->ping($this->uConn)) {
            $this->connect("master");
        }

        $this->sql = $sql;
        $startTime = microtime(true);
        /**
         * @var ClickHouseDB\Client $conn
         */
        $conn = $this->uConn;
        $ret = $conn->write($sql);
        $this->urs = !$ret->error();
//        $this->urs = mysqli_query($this->uConn, $sql);

        if ($recordSql) {
            $this->recordSql(null, $startTime);
            $this->recordUdateSql(null, $startTime);
        }

        if (!$this->urs) {
            throw new DB_Exception('更新失败:' . mysqli_error($this->uConn));
        } else {
            $this->updateNum++;
            return $this->urs;
        }
    }

    /**
     * 返回SQL语句执行结果集中的第一行第一列数据
     *
     * @param string $sql 需要执行的SQL语句
     * @return mixed 查询结果
     */
    public function getOne($sql) {
        if (!$rs = $this->query($sql, 1, true)) {
            return false;
        }

        $row = $this->fetch($rs, self::DB_FETCH_ROW);
        return current($row);
    }

    /**
     * 返回SQL语句执行结果集中的第一列数据
     *
     * @param string $sql 需要执行的SQL语句
     * @param mixed $limit 整型或者字符串类型，如10|10,10
     * @return array 结果集数组
     */
    public function getCol($sql, $limit = null) {
        $result = array();
        $rows = $this->getAll($sql, $limit);
        if ($rows) {
            foreach ($rows as $row) {
                $result[] = current($row);
            }
        }

        return $result;
    }

    /**
     * 返回SQL语句执行结果中的第一行数据
     *
     * @param string $sql 需要执行的SQL语句
     * @param int $fetchMode 返回的数据格式
     * @return array 结果集数组
     */
    public function getRow($sql, $fetchMode = self::DB_FETCH_ROW) {
        if (!$rs = $this->query($sql, 1, true)) {
            return false;
        }
        $row = $this->fetch($rs, $fetchMode);
        return $row;
    }

    /**
     * 返回SQL语句执行结果中的所有行数据
     *
     * @param string $sql 需要执行的SQL语句
     * @param mixed $limit 整型或者字符串类型，如10|10,10
     * @param int $fetchMode 返回的数据格式
     * @return array 结果集二维数组
     */
    public function getAll($sql, $limit = null, $fetchMode = self::DB_FETCH_DEFAULT) {
        if (!$rs = $this->query($sql, $limit, true)) {
            return false;
        }

        return $this->fetch($rs, $fetchMode);
    }

    /**
     * 返回最近一次查询返回的结果集条数
     * @param \ClickHouseDB\Statement $rs
     * @return int
     */
    public function rows($rs) {
        return $rs->count();
    }

    /**
     * 返回最近一次更新的结果条数
     *
     * @return int
     */
    public function affectedRows() {
        return $this->urs->count();
    }

    /**
     * 返回最近一次插入语句的自增长字段的值
     *
     * @return int
     */
    public function lastID() {
//        return mysqli_insert_id($this->uConn);
        return 0;
    }

    /**
     * @param ClickHouseDB\Client $conn
     * @return bool
     */
    public function ping($conn) {
//        return mysqli_ping($conn);
        return $conn->ping();
    }


    /**
     * 析构函数，暂时不需要做什么处理
     *
     */
    public function __destruct() {
    }

}

//end of script
