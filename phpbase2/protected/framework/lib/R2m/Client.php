<?php

require_once realpath(dirname(__FILE__)) . '/Header.php';

/**
 * Created by PhpStorm.
 * @author benzhan
 * Date: 15/12/24
 * Time: 下午5:57
 */
class R2m_Client {
    private static $_objHelper;
    private $_conf;
    private $_pwd;
    private $_objTableHelper;

    public function __construct($tableName, $dbKey = 'default', $cacheKey = 'default') {
        $r2mConfKeys = $GLOBALS['r2mConfKeys'];
        if ($r2mConfKeys) {
            $index = array_rand($r2mConfKeys);
            $r2mConf = $GLOBALS['r2mConf'][$r2mConfKeys[$index]] ?: $GLOBALS[$r2mConfKeys[$index]];
        } else {
            $r2mConf = $GLOBALS['r2mConf']['default'];
        }

        $this->_pwd = $r2mConf['pwd'];
        $this->_conf = [$tableName, $dbKey, $cacheKey];
        $this->_objTableHelper = new TableHelper($tableName, $dbKey);
        $this->_objTableHelper->setOnlyRecordSql(true);

        if (self::$_objHelper == null) {
            if (!$r2mConf) {
                Response::error(CODE_REDIS_ERROR, "can not find r2mConf:【{$r2mConfKeys[$index]}】");
            }
            // 设置长连接
            $client = new R2m_Socket($r2mConf['host'], $r2mConf['port'], true);
            self::$_objHelper = new R2m_Helper($client);
        }
    }

    /**
     * 增加一行数据
     * @param array $args
     * @param boolean $updateList
     * @return int
     */
    public function addObject(array $args, $updateList = true) {
        $this->_objTableHelper->addObject($args);
        return $this->_request(CMD_ADD_OBJECT, func_get_args());
    }

    /**
     * 如果不存在，则插入一行数据
     * @param array $args
     * @param array $where
     * @param boolean $updateList
     * @return int
     */
    public function addObjectNx(array $args, array $where, $updateList = true) {
        $this->_objTableHelper->addObjectNx($args, $where);
        return $this->_request(CMD_ADD_OBJECT_NX, func_get_args());
    }

    /**
     * INSERT多行数据
     * @author benzhan
     * @param array $cols 列名
     * @param array $args 参数列表
     */
    public function addObjects(array $cols, array $args, $updateList = true) {
        $this->_objTableHelper->addObjects($cols, $args);
        return $this->_request(CMD_ADD_OBJECTS, func_get_args());
    }

    /**
     * INSERT多行数据
     * @author benzhan
     * @param array $args array(array(key => $value, ...))
     */
    public function addObjects2(array $args, $updateList = true) {
        $this->_objTableHelper->addObjects2($args);
        return $this->_request(CMD_ADD_OBJECTS2, func_get_args());
    }

    /**
     * 获取插入数据的自增id
     * @return mixed
     * @author benzhan
     */
    public function getInsertId() {
        return $this->_request(CMD_GET_INSERT_ID, func_get_args());
    }

    /**
     * 获取一个key的数据
     * @param array $where
     * @return array <NULL, array>
     */
    public function getRow(array $where) {
        $this->_objTableHelper->getRow($where);
        return $this->_request(CMD_GET_ROW, func_get_args());
    }

    /**
     * 读取多行数据
     * @param array $where
     * @param array $keyWord 查询关键字, array('_field', '_where', '_limit', '_sortKey', '_sortDir', '_lockRow', '_tableName')
     * @param  bool $updateList 是否强制更新缓存
     * @return array:
     */
    public function getAll(array $where = array(), array $keyWord = array(), $updateList = false) {
        $this->_objTableHelper->getAll($where, $keyWord);
        return $this->_request(CMD_GET_ALL, func_get_args());
    }

    /**
     * 查询数据, 归类到all:others
     * @param $sql
     * @return mixed
     * @throws R2m_Exception
     * @author benzhan
     */
    public function getAllSql($sql) {
        $this->_objTableHelper->getDb()->recordSql($sql);
        return $this->_request(CMD_GET_ALL_SQL, func_get_args());
    }

    /**
     * 删除行的缓存
     * @param array $where
     * @throws R2m_Exception
     */
    public function delRowCache(array $where) {
        return $this->_request(CMD_DEL_ROW_CACHE, func_get_args());
    }

    /**
     * 删除列表的缓存
     * @param array $where
     */
    public function delListCache(array $where = []) {
        return $this->_request(CMD_DEL_LIST_CACHE, func_get_args());
    }

    /**
     * 替换一个key的数据
     * @param array $args
     * @param boolean $updateList
     * @return int 影响行数
     */
    public function replaceObject(array $args, $updateList = true) {
        $this->_objTableHelper->replaceObject($args);
        return $this->_request(CMD_REPLACE_OBJECT, func_get_args());
    }

    /**
     * 修改一个key的数据
     * @param array $args 更新的内容
     * @param array $where 更新的条件
     * @param boolean $updateList
     * @return int 影响行数
     */
    public function updateObject(array $args, array $where, $updateList = true) {
        $this->_objTableHelper->updateObject($args, $where);
        return $this->_request(CMD_UPDATE_OBJECT, func_get_args());
    }

    /**
     * 修改多个key的数据
     * @param array $args 更新的内容
     * @param array $where 更新的条件
     * @param boolean $updateList
     * @return int 影响行数
     */
    public function updateObjects(array $args, array $where, $updateList = true) {
        $this->_objTableHelper->updateObject($args, $where);
        return $this->_request(CMD_UPDATE_OBJECTS, func_get_args());
    }

    /**
     * 删除一个key的数据
     * @param array $where
     * @param boolean $updateList
     * @return int
     */
    public function delObject(array $where, $updateList = true) {
        $this->_objTableHelper->delObject($where);
        return $this->_request(CMD_DEL_OBJECT, func_get_args());
    }

    /**
     * 删除多个key的数据
     * @param array $where 删除的条件
     * @param boolean $updateList
     * @return int 影响行数
     */
    public function delObjects(array $where, $updateList = true) {
        $this->_objTableHelper->delObject($where);
        return $this->_request(CMD_DEL_OBJECTS, func_get_args());
    }

    /**
     * 设置是否开启调试
     * @param $debug
     * @return mixed
     * @throws R2m_Exception
     * @author benzhan
     */
    public function setDebug($debug) {
        return $this->_request(CMD_SET_DEBUG, func_get_args());
    }

    /**
     * 获取调试信息
     * @return string
     * @author benzhan
     */
    public function getDebugMsg() {
        return $this->_request(CMD_DEBUG_MSG, func_get_args());
    }

    private function _request($cmd, $data) {
        self::$_objHelper->open($this->_pwd);

        $id = CallLog::getCallId();
        $ret = self::$_objHelper->request($this->_conf, $cmd, $data, $id);
        if ($ret) {
            $result = self::$_objHelper->receive();
            if ($result['cmd'] == RESULT_SUCCESS) {
                return $result['content'];
            } else if ($result['cmd'] == RESULT_ERROR) {
                $error = json_encode($result);
                throw new R2m_Exception($error);
            } else {
                $error = json_encode($result);
                throw new R2m_Exception($error);
            }
        } else {
            throw new R2m_Exception('write data error');
        }
    }


}
