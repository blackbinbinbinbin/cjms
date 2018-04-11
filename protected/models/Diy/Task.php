<?php

class Diy_Task {
    private $_task;
    private $_startTime;
    private $_taskLogId;
    private $_dataBeginTime;
    private $_debug;
    private $_isRedo;
    private $_errorTimes = 0;
    private $_errorMsg = '';
    private $_updateNum = 0;
    private $_insertNum = 0;
    private $_replaceNum = 0;

    public function __construct($task, $dataBeginTime = null, $isRedo = 0, $debug = 0) {
        $this->_task = $task;
        $this->_dataBeginTime = $dataBeginTime;
        $this->_isRedo = $isRedo;
        $this->_debug = $debug;

    }

    private function _getBeginTime() {
        // 递增方式，需要计算数据结束时间，防止重复计算
        if ($this->_isRedo == 1) {
            $redoConfig = $this->_task['redoConfig'];
            $delay = (int) $redoConfig['delay'];
            if (!$delay) {
                $this->_errorMsg .= $this->_task['taskName'] . '[' . $this->_task['taskId'] . ". no redoConfig.\r\n";
                exit;
            }
            $maxBeginTime = time() - $delay;
        } else {
            $maxBeginTime = time() - $this->_task['execDelay'] - $this->_task['timeInterval'];
        }

        $dataBeginTime = $this->_dataBeginTime;
        if ($dataBeginTime) {
            if ($dataBeginTime > $maxBeginTime) {
                $msg = $this->_task['taskName'] . '[' . $this->_task['taskId'] . "], error beginTime:{$dataBeginTime} > {$maxBeginTime}\r\n";
                $this->_errorMsg .= $msg;
                return false;
            }
        } else {
            $dataBeginTime = $maxBeginTime;
        }

        if ($this->_isRedo == 1) {
            // 重算的逻辑都用小时来格式化
            $dataBeginTime = (int) ($dataBeginTime / 3600) * 3600;
        } else {
            // 小于或等于1个小时才进行格式化
            if ($this->_task['timeInterval'] && $this->_task['timeInterval'] <= 3600) {
                $dataBeginTime = (int) ($dataBeginTime / $this->_task['timeInterval']) * $this->_task['timeInterval'];
            }
        }


        return $dataBeginTime;
    }

    private function _getEndTime($dataBeginTime) {
        if ($this->_isRedo == 1) {
            $redoConfig = $this->_task['redoConfig'];
            $interval = (int) $redoConfig['interval'];
        } else {
            $interval = $this->_task['timeInterval'];
        }

        $dataEndTime = $dataBeginTime + $interval - 1;
        return $dataEndTime;
    }

    public function run() {
        $dataBeginTime = $this->_getBeginTime();
        if ($dataBeginTime === FALSE) {
            return false; 
        }

        $dataEndTime = $this->_getEndTime($dataBeginTime);
        $dataBeginTime = date('Y-m-d H:i:s', $dataBeginTime);
        if (strpos($dataBeginTime, '00:00:00')) {
            $dataBeginTime = substr($dataBeginTime, 0, 10);
        }

        $dataEndTime = date('Y-m-d H:i:s', $dataEndTime);

        $this->_logStart($dataBeginTime, $dataEndTime);

        $affectRows = 0;
        $oData = new Diy_Data();
        $objTable = null;

        try {
            parse_str($this->_task['sourceArgs'], $args);
            $args['where'] && $args['where'] = json_decode($args['where'], true);
            $args['where'] || $args['where'] = [];

            $newWhere = [];
            foreach ($args['where'] as $i => $value) {
                // 需要把时间条件去掉
                if ($value[0] != $this->_task['timeField']) {
                    $newWhere[] = $value;
                } else {
                    if ($value['timeField'][1] != ':' && $_GET['_debug']) {
                        var_dump('有点隐患，时间字段不是用区间，可能会导致看到的数据跟统计不一样');
                    }
                }
            }

            if ($this->_task['timeField']) {
                $newWhere[] = [$this->_task['timeField'], ':', $dataBeginTime, $dataEndTime];
            }

            $args['where'] = $newWhere;
            $args['keyWord'] && $args['keyWord'] = json_decode($args['keyWord'], true);
            $args['keyWord'] || $args['keyWord'] = [];
            foreach ($args as $key => $value) {
                if (strpos($key, '_') === 0) {
                    $args['keyWord'][$key] = $value;
                }
            }

            $datas = $oData->getTableData($args);

            // 插入到数据库
            if ($this->_task['toNameDb']) {
                $dbs = DB::init($GLOBALS['dbInfo']);
                $objDb = $dbs[$this->_task['toNameDb']];
            } else if ($this->_task['toDbId']) {
                $objDb = $oData->getDb(['dbId' => $this->_task['toDbId']]);
            } else {
                return false;
            }

            $tableName = $this->_getToTableName($this->_task, $args);
            $objTable = new TableHelper($tableName, $objDb);
            $objTable->getDb()->debug(true);

            // 确认表已经创建
//            if (!$objTable->checkTableExist($tableName)) {
//                $sql = $this->_getTableSql($this->_task['toTableSql'], $tableName);
//                $objTable->getDb()->query($sql);
//            }

            $keys = $oData->getDataHive()->getKeys();
            if ($this->_task['staticType'] == 1) {
                // 递增方式需要计算老数据
                $oldDatas = $this->_getOldData($objTable, $datas, $keys);
                if ($_GET['_debug2']) {
                    var_dump($oldDatas);
                    exit;
                }
            } else {
                // 覆盖方式不需要计算老数据
                $oldDatas = [];
            }

            $newDatas = $this->_processData($datas, $oldDatas, $keys);
            $newDatas = $this->_formatData($objTable, $newDatas);

            $affectRows = count($newDatas);
            if ($this->_task['insertType'] == 'replace') {
                $insertType = 'replace';
                if ($this->_checkReplace($objTable, $newDatas)) {
                    $this->_replaceData($objTable, $newDatas);
                } else {
                    $affectRows = 0;
                    throw new Exception('这个数据，没包含全部列，不能用replace');
                }
            } else {
                $insertType = 'update';
                $this->_updateData2($objTable, $newDatas);
            }

            $sqls = $this->_getSql($objTable, $oData);
            $this->_logEnd($insertType, $affectRows, $sqls);
        } catch(Exception $ex) {
            if ($objTable) {
                $sqls = $this->_getSql($objTable, $oData);
            } else {
                $sqls = [];
            }

            $this->_logError($ex, $affectRows, $sqls);
        }

        return compact('sqls', 'affectRows');
    }

    private function _getSql(TableHelper $objTable, Diy_Data $oData) {
        // 取前100的sql
        $sqls = $objTable->getDb()->getSql();
        if (count($sqls) > 30) {
            $oldCount = count($sqls);
            $sqls = array_slice($sqls, 0, 30);
            $sqls[] = '...(skip num:' . ($oldCount - 30) . ')';
        }

        $sqls = array_merge($oData->oBase->getDb()->getSql(), $sqls);
        $objTable->getDb()->clearSql();

        $maxLen = 1000;
        foreach ($sqls as $i => $sql) {
            $len = strlen($sql);
            if ($len > $maxLen + 50) {
                $skipNum = $len - $maxLen;
                $sqls[$i] = substr($sql, 0, $maxLen) . "...(skip length:{$skipNum})";
            }
        }

        return $sqls;
    }

    private function _formatData(TableHelper $objTable, $newDatas) {
        $dbFields = $this->_getDbFields($objTable);
        if ($this->_task['fieldMap']) {
            $map = json_decode($this->_task['fieldMap'], TRUE);
        } else {
            $map = [];
        }

        foreach ($newDatas as $i => $newData) {
            $data = [];
            foreach ($newData as $key => $value) {
                $key = isset($map[$key]) ? $map[$key] : $key;
                if ($dbFields[$key]) {
                    $data[$key] = $value;
                }
            }
            $newDatas[$i] = $data;
        }
        

        return $newDatas;
    }

    private function _getOlDdata(TableHelper $objTable, $datas, $keys) {
        $where = [];
        if ($this->_task['fieldMap']) {
            $map = json_decode($this->_task['fieldMap'], true);
            $rMap = array_flip($map);
        } else {
            $map = $rMap = [];
        }

        foreach ($keys['_groupby'] as $fieldName) {
            $values = [];
            foreach ($datas as $data) {
                $values[$data[$fieldName]] = $data[$fieldName];
            }

            if (count($values) <= 3) {
                $fieldName2 = $rMap[$fieldName] ? $rMap[$fieldName] : $fieldName;
                $where[$fieldName2] = array_values($values);
            }
        }

        $oldDatas = $objTable->getAll($where);
        $newDatas = [];
        if ($rMap) {
            foreach ($oldDatas as $i => $oldData) {
                $newData = [];
                foreach ($map as $key1 => $key2) {
                    $newData[$key1] = $oldData[$key2];
                }

                foreach ($oldData as $fieldName => $value) {
                    if (!$rMap[$fieldName]) {
                        $newData[$fieldName] = $value;
                    }
                }

                $newDatas[] = $newData;
            }
        } else {
            $newDatas = $oldDatas;
        }

        return $newDatas;
    }

    /**
     * 只有目标表有主键，并且没多余字段才能用replace方式，不然会有数据丢失的风险
     * @param TableHelper $objTable
     * @param $newDatas
     * @author benzhan
     * @return boolean 是否可以用replace
     */
    private function _checkReplace(TableHelper $objTable, $newDatas) {
        if (!$newDatas) {
            return false;
        }

        $row = current($newDatas);
        $cols = array_keys($row);

        $dbFields = $this->_getDbFields($objTable);
        foreach ($cols as $col) {
            unset($dbFields[$col]);
        }

        if (count($dbFields) > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function _updateData(TableHelper $objTable, $newDatas) {
        $priKeys = $this->_getPriKeys($objTable);
        foreach ($newDatas as $newData) {
            $data = [];
            $where = [];
            foreach ($newData as $key => $value) {
                if ($priKeys[$key]) {
                    $where[$key] = $value;
                } else {
                    $data[$key] = $value;
                }
            }

            if (!empty($where)) {
                if ($this->_task['insertType'] == 'only_update_multi') {
                    // 只更新多条数据
                    $count = 1;
                } else {
                    $datas = $objTable->getAll($where);
                    $count = count($datas);
                }

                if ($count > 1) {
                    $this->_errorTimes++;
                    $this->_errorMsg .= 'update存在多条数据【' .json_encode($where) . "】\r\n";
                } else if ($count == 1) {
                    // 只插入不更新时, 不修改数据
                    if ($this->_task['insertType'] != 'only_insert_nx') {
                        $objTable->updateObject($data, $where);
                        $this->_updateNum++;
                    }
                } else if ($count == 0 && $this->_task['insertType'] != 'only_update') {
                    try {
                        $objTable->addObject(array_merge($data, $where));
                        $this->_insertNum++;
                    } catch (Exception $ex) {
                        $this->_errorTimes++;
                        $this->_errorMsg .= $ex->getMessage() . "\r\n" . $ex->getTraceAsString() . "\r\n";
                    }
                }
            } else {
                $msg = $this->_task['taskName'] . '[' . $this->_task['taskId'] . ']:empty where. data:' . json_encode($data);
                $msg .= ', $priKeys:' . json_encode($priKeys) . ', newData:' . json_encode($newData);
                $this->_errorTimes++;
                $this->_errorMsg .= $msg . "\r\n";
                if (!$this->_debug) {
                    YYms::reportServerWarning($msg);
                }
            }
        }
    }

    private function _updateData2(TableHelper $objTable, $newDatas) {
        if (!$newDatas) { return false; }

        if ($this->_task['insertType'] == 'only_insert_nx') {
            // 只插入模式
            $this->_insertNum += $objTable->addObjectsIfNoExist($newDatas);
        } else if ($this->_task['insertType'] == 'only_update') {
            // 只更新模式
            $this->_updateData($objTable, $newDatas);
        } else if ($this->_task['insertType'] == 'only_update_multi' || $this->_task['insertType'] == 'update') {
            // 插入或更新模式
            $this->_insertNum += $objTable->addOrUpdateObjects($newDatas);
        } else {
            throw new Exception("遇到不存在的插入方式：{$this->_task['insertType']}");
        }
    }

    private function _replaceData(TableHelper $objTable, $newDatas) {
        if ($newDatas) {
            $row = current($newDatas);
            $cols = array_keys($row);

            $this->_replaceNum += count($newDatas);
            // 插入到数据库
            $objTable->replaceObjects($cols, $newDatas);
        }
    }

    private function _logStart($dataBeginTime, $dataEndTime) {
        $this->_startTime = microtime(true);

        $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');

        $where = [];
        $where['taskId'] = $this->_task['taskId'];
        $where['execStatus'] = 0;
        $where['isRedo'] = $this->_isRedo;
        $row = $objTaskLog->getRow($where);
        if ($row) {
            $msg = "{$this->_task['taskName']}[{$this->_task['taskId']}], 已经有任务正在执行...\r\n";
            $this->_errorMsg .= $msg;
            exit;
        }

        $log = [];
        $log['taskId'] = $this->_task['taskId'];
        $log['execBeginTime'] = date('Y-m-d H:i:s');
        $log['dataBeginTime'] = $dataBeginTime;
        $log['dataEndTime'] = $dataEndTime;
        $log['execStatus'] = 0;
        $log['isRedo'] = $this->_isRedo;
        $objTaskLog->addObject($log);
        $this->_taskLogId = $objTaskLog->getInsertId();
    }

    private function _logEnd($insertType, $affectRows, $sqls) {
        $endTime = microtime(true);

        $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');
        $log = [];
        $log['execTime'] = $endTime - $this->_startTime;
        $log['execEndTime'] = date('Y-m-d H:i:s');

        $msg = "insertNum:{$this->_insertNum}, updateNum:{$this->_updateNum}, replaceNum:{$this->_replaceNum}\r\n";
        if ($this->_errorTimes > 0) {
            $log['execStatus'] = 2;
            $log['remark'] = $msg . $this->_errorMsg;
        } else {
            $log['execStatus'] = 1;
            $log['remark'] = $msg;
        }

        $log['insertType'] = $insertType;
        $log['affectRows'] = $affectRows;
        $log['usedMemory'] = memory_get_peak_usage();
        $log['querySql'] = join("\n", $sqls);
        $objTaskLog->updateObject($log, ['taskLogId' => $this->_taskLogId]);
    }

    private function _logError(Exception $ex, $affectRows, $sqls) {
        $endTime = microtime(true);

        $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');
        $log = [];
        $log['execTime'] = $endTime - $this->_startTime;
        $log['execEndTime'] = date('Y-m-d H:i:s');
        $log['execStatus'] = -1;
        $log['insertType'] = '';
        $log['affectRows'] = $affectRows;
        $log['usedMemory'] = memory_get_peak_usage();
        $log['remark'] = $ex->getMessage() . "\r\n" . $ex->getTraceAsString();
        $log['querySql'] = join("\n", $sqls);
        $objTaskLog->updateObject($log, ['taskLogId' => $this->_taskLogId]);

        $msg = $this->_task['taskName'] . '[' . $this->_task['taskId'] . ", 执行失败:" . $ex->getMessage();
        $this->_debug || YYms::reportServerWarning($msg);
    }

    private function _processData($datas, $oldDatas, $keys) {
        $newDatas = [];
        $weidu = array_merge($keys['_groupby'], $keys['_save']);
        if ($weidu) {
            $map = [];
            foreach ($datas as $data) {
                $key = '';
                foreach ($weidu as $fieldName) {
                    $data[$fieldName] && $key .= '#[' . $data[$fieldName] . ']$';
                }
                // 过滤掉_groupby为空的字段
                $key && $map[$key] = $data;
            }

            foreach ($oldDatas as $oldData) {
                $key = '';
                $newData = [];

                if ($weidu) {
                    foreach ($weidu as $fieldName) {
                        $oldData[$fieldName] && $key .= '#[' . $oldData[$fieldName] . ']$';
                        $newData[$fieldName] = $oldData[$fieldName];
                    }
                } else {
                    $this->_errorMsg .= "error, no group by.\r\n";
                    exit;
                }

                // 如果不存在新数据，则跳过
                $data = $map[$key];
                if (!$data) {
                    continue;
                } else {
                    unset($map[$key]);
                }

                // 存在新数据，则需要计算
                foreach ($keys['_max'] as $fieldName) {
                    $newData[$fieldName] = max($oldData[$fieldName], $data[$fieldName]);
                }

                foreach ($keys['_min'] as $fieldName) {
                    $newData[$fieldName] = min($oldData[$fieldName], $data[$fieldName]);
                }

                foreach ($keys['_count'] as $fieldName) {
                    $newData[$fieldName] = $oldData[$fieldName] + $data[$fieldName];
                }

                foreach ($keys['_sum'] as $fieldName) {
                    $newData[$fieldName] = $oldData[$fieldName] + $data[$fieldName];
                }

                foreach ($keys['_distinctCount'] as $fieldName) {
                    $newData[$fieldName] = $oldData[$fieldName] + $data[$fieldName];
                }

                // 保留字段
                foreach ($keys['_save'] as $fieldName) {
                    $newData[$fieldName] = $data[$fieldName];
                }

                $newDatas[] = $newData;
            }
        } else {
            $map = $datas;
        }

        foreach ($map as $data) {
            // 这个是新数据
            $newData = [];

            foreach ($keys['_groupby'] as $fieldName) {
                $newData[$fieldName] = $data[$fieldName];
            }

            foreach ($keys['_max'] as $fieldName) {
                $newData[$fieldName] = $data[$fieldName];
            }

            foreach ($keys['_min'] as $fieldName) {
                $newData[$fieldName] = $data[$fieldName];
            }

            foreach ($keys['_count'] as $fieldName) {
                $newData[$fieldName] = $data[$fieldName];
            }

            foreach ($keys['_sum'] as $fieldName) {
                $newData[$fieldName] = $data[$fieldName];
            }

            foreach ($keys['_distinctCount'] as $fieldName) {
                $newData[$fieldName] = $data[$fieldName];
            }

            // 保留字段
            foreach ($keys['_save'] as $fieldName) {
                $newData[$fieldName] = $data[$fieldName];
            }

            $newDatas[] = $newData;
        }

        return $newDatas;
    }

    private function _getTableSql($sql, $tableName) {
        $pattern = '/CREATE TABLE .+ \(/i';
        $sql = preg_replace($pattern, "CREATE TABLE `{$tableName}` (", $sql);
        return $sql;
    }

    private function _getToTableName($tableInfo, $args) {
        $sourceCallBack = trim($tableInfo['toTableCallback']);
        if ($sourceCallBack) {
            $func = create_function('&$args', $sourceCallBack);
            $tableName = $func($args);
        } else {
            $tableName = $tableInfo['toTable'];
        }

        return $tableName;
    }

    private static $dbFieldsMap = [];
    private function _getDbFields(TableHelper $objHelper) {
        $dsn = $objHelper->getDb()->dsn;
        $tableName = $objHelper->getTableName();
        $key = "{$dsn['dbHost']}:{$dsn['dbPort']}:{$dsn['dbName']}:{$tableName}";
        if (!isset(self::$dbFieldsMap[$key])) {
            try {
                $sql = "SHOW FULL FIELDS FROM `{$tableName}`";
                $datas = $objHelper->getDb()->getAll($sql);

                $col = array();
                foreach ($datas as $data) {
                    $col[$data['Field']] = $data['Field'];
                }
                self::$dbFieldsMap[$key] = $col;
            } catch (Exception $ex) {
                self::$dbFieldsMap[$key] = [];
            }
        }

        return self::$dbFieldsMap[$key];
    }

    private function _getPriKeys(TableHelper $objHelper) {
        try {
//            $tableName = $objHelper->getTableName2();
            $tableName = $objHelper->getTableName();
            $sql = "SHOW FIELDS FROM `{$tableName}`";
            $datas = $objHelper->getDb()->getAll($sql);

            $col = array();
            foreach ($datas as $data) {
                if ($data['Key'] == 'PRI') {
                    $col[$data['Field']] = $data['Field'];
                }
            }

            return $col;
        } catch (Exception $ex) {
            return array();
        }
    }

    public function getErrorMsg() {
        return $this->_errorMsg;
    }
}
