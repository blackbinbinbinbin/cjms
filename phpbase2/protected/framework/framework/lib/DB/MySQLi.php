<?php

class DB_MySQLi extends DB {
    private $_autoCommitTime = 0;
    
    /**
     * MySQLi构造函数
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
//        if ($type == "master" || !isset($this->dsn["slave"])) {
            $dbHost = isset($this->dsn["master"]) ? $this->dsn["master"]["dbHost"] : $this->dsn["dbHost"];
            $dbName = isset($this->dsn["master"]) ? $this->dsn["master"]["dbName"] : $this->dsn["dbName"];
            $dbUser = isset($this->dsn["master"]) ? $this->dsn["master"]["dbUser"] : $this->dsn["dbUser"];
            $dbPass = isset($this->dsn["master"]) ? $this->dsn["master"]["dbPass"] : $this->dsn["dbPass"];
            $dbPort = isset($this->dsn["master"]) ? $this->dsn["master"]["dbPort"] : $this->dsn["dbPort"];
            $this->uConn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
            
            if (!$this->uConn) {
                throw new DB_Exception('更新数据库连接失败');
            }
            if (!isset($this->dsn["slave"])) {
                $this->qConn =& $this->uConn;
            }
            $sql = "SET NAMES utf8mb4";
            $this->update($sql, false);
//        } else {
//            if (empty($this->dsn["slave"])) {
//                $this->connect('master');
//                return $this->qConn =& $this->uConn;
//            }
//            if (empty($_COOKIE[COOKIE_PREFIX . $this->dbKey . 'DbNo'])) {
//                $dbNo = array_rand($this->dsn["slave"]);
//                setcookie(COOKIE_PREFIX . $this->dbKey . 'DbNo', $dbNo, null, COOKIE_PATH, COOKIE_DOMAIN);
//            } else {
//                $dbNo = $_COOKIE[COOKIE_PREFIX . $this->dbKey . 'DbNo'];
//            }
//            $dbInfo = $this->dsn["slave"][$dbNo];
//            $dbHost = $dbInfo["dbHost"];
//            $dbName = $dbInfo["dbName"];
//            $dbUser = $dbInfo["dbUser"];
//            $dbPass = $dbInfo["dbPass"];
//            $dbPort = $dbInfo["dbPort"];
//            $this->qConn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
//
//            mysqli_options($this->qConn,MYSQLI_OPT_INT_AND_FLOAT_NATIVE,true);
//
//            if (!$this->qConn) {
//                if (!$this->uConn) {
//                    $this->connect('slave');
//                }
//                $this->qConn =& $this->uConn;
//                if (!$this->qConn) {
//                    throw new DB_Exception('查询数据库连接失败');
//                }
//            }
//            $sql = "SET NAMES utf8mb4";
//            $this->update($sql, false);
//        }
        return true;
    }

    /**
     * 关闭数据库连接
     *
     * 一般不需要调用此方法
     */
    public function close() {
        if ($this->uConn === $this->qConn) {
            if (is_object($this->uConn)) {
                mysqli_close($this->uConn);
            }
        } else {
            if (is_object($this->uConn)) {
                mysqli_close($this->uConn);
            }
            if (is_object($this->qConn)) {
                mysqli_close($this->qConn);
            }
        }
    }

    /**
     * 执行一个SQL查询
     *
     * 本函数仅限于执行SELECT类型的SQL语句
     *
     * @param string $sql SQL查询语句
     * @param mixed $limit 整型或者字符串类型，如10|10,10
     * @param boolean $quick 是否快速查询
     * @return mysqli_result 返回查询结果资源句柄
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
        $this->qrs = mysqli_query($this->qConn, $sql, $quick ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT);
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
     * @param mysqli_result $rs 查询结果资源句柄
     * @param int $fetchMode 返回的数据格式
     * @return array 返回数据集每一行，并将$rs指针下移
     */
    public function fetch($rs, $fetchMode = self::DB_FETCH_DEFAULT) {
        switch ($fetchMode) {
            case 1:
                $fetchMode = self::DB_FETCH_ASSOC;
                break;
            case 2:
                $fetchMode = self::DB_FETCH_ROW;
                break;
            case 3:
                $fetchMode = self::DB_FETCH_ARRAY;
                break;
            default:
                $fetchMode = self::DB_FETCH_DEFAULT;
                break;
        }
        return mysqli_fetch_array($rs, $fetchMode);
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
        $this->urs = mysqli_query($this->uConn, $sql);

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
        $this->free($rs);
        return $row[0];
    }

    /**
     * 返回SQL语句执行结果集中的第一列数据
     *
     * @param string $sql 需要执行的SQL语句
     * @param mixed $limit 整型或者字符串类型，如10|10,10
     * @return array 结果集数组
     */
    public function getCol($sql, $limit = null) {
        if (!$rs = $this->query($sql, $limit, true)) {
            return false;
        }
        $result = array();
        
        do {
            $rows = $this->fetch($rs, self::DB_FETCH_ROW);
            if ($rows) {
                $result[] = $rows[0];
            }
        } while($rows);
        
        $this->free($rs);
        return $result;
    }

    /**
     * 返回SQL语句执行结果中的第一行数据
     *
     * @param string $sql 需要执行的SQL语句
     * @param int $fetchMode 返回的数据格式
     * @return array 结果集数组
     */
    public function getRow($sql, $fetchMode = self::DB_FETCH_DEFAULT) {
        if (!$rs = $this->query($sql, 1, true)) {
            return false;
        }
        $row = $this->fetch($rs, $fetchMode);
        $this->free($rs);
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
        $allRows = array();
        do {
            $row = $this->fetch($rs, $fetchMode);
            if ($row) {
                $allRows[] = $row;
            }
        } while($row);
        
        $this->free($rs);
        return $allRows;
    }

    /**
     * 设置是否开启事务(是否自动提交)
     *
     * 当设置为false的时候,即开启事务处理模式,表类型应该为INNODB
     *
     * @param boolean $mode
     * @return boolean
     */
    public function autoCommit($mode = false) {
        if (!$this->uConn) {
            $this->connect("master");
        }
        
        if ($mode) {
            //如果为true，则说明要提交
            $this->_autoCommitTime = 0;
        } else {
            //如果为false，则说明要一起commit，并且会积累 
            $this->_autoCommitTime++;
        }
        return mysqli_autocommit($this->uConn, $mode);
    }

    /**
     * 直接提交执行的SQL
     *
     * 当开启事务处理后,要手动提交执行的SQL语句
     * @param boolean $mode
     * @return boolean
     */
    public function commit($mode = true) {
        $this->_autoCommitTime--;
        $result = mysqli_commit($this->uConn);
        mysqli_autocommit($this->uConn, $mode);
        return $result;
    }
    
    /**
     * 尝试提交执行的SQL【当有多个autoCommit时，仅提交最后一次！】
     *
     * 当开启事务处理后,要手动提交执行的SQL语句
     * @param boolean $mode
     * @return boolean
     */
    public function tryCommit($mode = true) {
        $this->_autoCommitTime--;
        //最后一次commit才会提交
        if ($this->_autoCommitTime <= 0) {
            return $this->commit($mode);
        } else {
            return false;
        }
    }

    /**
     * 回滚
     *
     * 当开启事务处理后,有需要的时候进行回滚
     *
     * @return boolean
     */
    public function rollback() {
        return mysqli_rollback($this->uConn);
    }

    /**
     * 返回最近一次查询返回的结果集条数
     * @param mysqli_result $rs
     * @return int
     */
    public function rows($rs) {
        return mysqli_num_rows($rs);
    }
    
    /**
     * 返回最近一次更新的结果条数
     * 
     * @return int
     */
    public function affectedRows() {
        return mysqli_affected_rows($this->uConn);
    }

    /**
     * 返回最近一次插入语句的自增长字段的值
     *
     * @return int
     */
    public function lastID() {
        return mysqli_insert_id($this->uConn);
    }

    /**
     * 释放当前查询结果资源句柄
     * @param mysqli_result $rs
     */
    public function free($rs) {
        if ($rs) {
            mysqli_free_result($rs);
        }
    }
    
    public function ping($conn) {
        return mysqli_ping($conn);
    }

    /**
     * 析构函数，暂时不需要做什么处理
     *
     */
    public function __destruct() {
    }
    
    /**
     * @author benzhan
     * @param string $fileName 文件的路径 
     * @param string $tableName 表名
     * @param array $args 可选参数，key如下：<br/>
     * <b>string type</b>  导入方式，可为REPLACE、IGNORE, 默认为REPLACE<br/>
     * <b>string fieldsTerminated</b>  列的分隔符，默认为\t<br/>
     * <b>string fieldsEnclosed</b>  列的包裹字符，默认无包裹字符<br/>
     * <b>string linesTerminated</b>  行的分隔符，默认为\n<br/>
     * <b>string colName</b>  列的对应顺序，列名用,隔开。如果跟数据库列一致，则可以不用设置，否则一定要设置这个字段，默认为NULL<br/>
     * @return array result 返回结果，如下：<br/>
     * array ('Records' => '0', 'Deleted' => '0', 'Skipped' => '0', 'Warnings' => '0')<br/>
     *
     * <pre>
     * <b>示例:</b>
     * $db = DB::init($_configs['dbInfo']['test'], 'test');
     * $args = array(
     *     "fieldsTerminated" => ",",
     *     "fieldsEnclosed" => '"', 
     *     "linesTerminated" => "\r\n",
     *     "colName" => "logTime, exactTime, appId, rate, page, action, actionNo, uuid, uid, openId, content, model, manufacturer, clientIp, mobile, channel, sdkVersion, gameVersion"
     * );
     * $result = $db['test']->loadFile("/tmp/actionLog20130109.csv", "ActionLog2013.actionLog20130307", $args);
     * </pre>
     */
    public function loadFile($fileName, $tableName, array $args = array()) {
        $default = array(
                'type' => 'REPLACE',
                'fieldsTerminated' => "\t",
                'fieldsEnclosed' => null,
                'linesTerminated' => "\n",
                'colName' => null
        );        
        $args = array_merge($default, $args);
        
        $enclosed = $args['fieldsEnclosed'] ? "OPTIONALLY ENCLOSED BY '{$args['fieldsEnclosed']}'" : "";
        $colName = $args['colName'] ? "({$args['colName']})" : "";
        
        $sql = "LOAD DATA LOCAL INFILE '{$fileName}'  {$args['type']} INTO TABLE {$tableName}
                    character set utf8 FIELDS TERMINATED BY '{$args['fieldsTerminated']}' {$enclosed} 
                    LINES TERMINATED BY '{$args['linesTerminated']}'  {$colName}";
        $this->query($sql);
        
        $result = mysqli_info($this->qConn);
        //Records: 150  Duplicates: 0  Warnings: 0
        $matches = array();
        preg_match_all('/\w+:\s*\d+/', $result, $matches);
        $matches && $matches = $matches[0];
        
        $result = array();
        foreach ($matches as $matche) {
            $parts = explode(": ", $matche);
            $result[$parts[0]] = $parts[1];
        }
        
        return $result;
    }
}

//end of script
