<?php

/**
 * Diy编辑
 * @author benzhan
 */
class DiyEditController extends BaseController {

    private $_fields;
    private $_searchFields;
    private $_relaValues = [];

    public function actionAddView($args) {
        $rules = array(
            'tableId' => 'string',
            'inPage' => array('int', 'nullable' => true),
        );
        Param::checkParam($rules, $args);
        
        // 检查权限
        Permission::checkTableRight($args['tableId']);
        
        $tableId = $args['tableId'];
        $objTable = new Diy_Table();
        $tableInfo = $objTable->getTableInfo($tableId);
        if (!$tableInfo['editFlag']) {
            Response::error(CODE_NO_PERMITION, '这张报表不能编辑');
        }

        $objField = new Diy_Field();
        $searchFields = $objField->getFields2($tableId);

        $objEdit = new Diy_Edit();
        $fields = $objEdit->getFields($tableId);
        $fields = $objEdit->processFieldView($fields, $searchFields, 'showInAdd');


        // 设置默认值
        foreach ($args as $fieldName => $defaultValue) {
            if ($fields[$fieldName]) {
                $fields[$fieldName]['defaultValue'] = $defaultValue;
            }
        }
        $fields = $objEdit->groupFieldView($fields);

        $template = Template::init();
        $template->assign('tableInfo', $tableInfo);
        $template->assign('fields', $fields);
        $template->assign('tableId', $tableId);

        // 不能替换增加webp
        $_GET['__orgin'] = true;
        if ($args['inPage']) {
            $template->display('diy/table_add_page');
        } else {
            $template->display('diy/table_add_dialog');
        }
    }
    
    public function actionEditView($args) {
        $rules = array(
            'tableId' => 'string',
            'inPage' => array('int', 'nullable' => true),
        );
        Param::checkParam($rules, $args);
        
        // 检查权限
        Permission::checkTableRight($args['tableId']);
    
        $tableId = arrayPop($args, 'tableId');
        $objTable = new Diy_Table();
        $tableInfo = $objTable->getTableInfo($tableId);
        if (!$tableInfo['editFlag']) {
            Response::error(CODE_NO_PERMITION, '这张报表不能编辑');
        }
        
        $where = $this->_checkPriKey($args, $tableId);
        $row = $this->_getEditData($tableId, $where);

        foreach ($row as $fieldName => $value) {
            $field = $this->_fields[$fieldName];
            $searchField = $this->_searchFields[$fieldName];

            if ($searchField['needMap2']) {
                // 二次翻译的字段需要进行一次翻译
                $row['_' . $fieldName] = $row[$fieldName];
                $row[$fieldName] = $field['map'][$row[$fieldName]];
            }

            // 判断是否是date或datetime的int或bigint
            if (strpos($field['orginType'], 'int') !== false) {
                if ($field['inputType'] == 'date') {
                    $row[$fieldName] = date('Y-m-d', $value);
                } else if ($field['inputType'] == 'datetime') {
                    $row[$fieldName] = date('Y-m-d H:i:s', $value);
                }
            } else if ($field['inputType'] == 'image' || $field['inputType'] == 'audio' || $field['inputType'] == 'video') {
                if ($value) {
                    $row[$fieldName] = [];
                    $file = [];
                    $file['url'] = $value;
                    if ($field['inputType'] == 'image') {
//                    $file['thumbnailUrl'] = $value . '?imageview/5/8/w/80/h/80/blur/0.01';
                        $file['thumbnailUrl'] = $value . '?imageview/0/w/120/blur/1';
                        $file['bigUrl'] = $value;
                    }
                    $file['name'] = pathinfo($value, PATHINFO_BASENAME);
                    $row[$fieldName]['files'][] = $file;
                }
            }
        }

        $objEdit = new Diy_Edit();
        $fields = $objEdit->groupFieldView($this->_fields);

        $template = Template::init();
        $template->assign('tableInfo', $tableInfo);
        $template->assign('fields', $fields);
        $template->assign('row', $row);
        $template->assign('priKeys', $where);

        // 不能替换增加webp
        $_GET['__orgin'] = true;
        if ($args['inPage']) {
            $template->display('diy/table_edit_page');
        } else {
            $template->display('diy/table_edit_dialog');
        }
    }

    private function _getEditData($tableId, $where) {
        $objField = new Diy_Field();
        $this->_searchFields = $objField->getFields2($tableId);

        $objEdit = new Diy_Edit();
        $this->_fields = $objEdit->getFields($tableId);
        $this->_fields = $objEdit->processFieldView($this->_fields, $this->_searchFields, 'showInEdit');

        $objData = new Diy_Data();
        $objHelper = $objData->getHelper($tableId);

        $keyWord = [];
        $sql = '';
        if ($this->_fields) {
            foreach ( $this->_fields as $fieldName => $field) {
                $fieldVirtualValue = $this->_searchFields[$fieldName]['fieldVirtualValue'];
                if ($fieldVirtualValue) {
                    $sql .= "{$fieldVirtualValue} AS `{$fieldName}`,";
                } else {
                    $sql .= "`{$fieldName}`,";
                }
            }
        }
        $keyWord['_field'] = substr($sql, 0, -1);
        $row = $objHelper->getRow($where, $keyWord);

        return $row;
    }

    public function _processSaveData(&$args, $tableId, $oldData = [], $type = '') {
        $objEdit = new Diy_Edit();
        $this->_fields = $objEdit->getFields($tableId);

        $objField = new Diy_Field();
        $this->_searchFields = $objField->getFields2($tableId);

        $this->_relaValues = [];

        foreach ($this->_fields as $fieldName => $field) {
            $value = $args[$fieldName];
            $searchField = $this->_searchFields[$fieldName];
            if ($value || $value == '0') {
                // 判断是否是date或datetime的int或bigint
                if (strpos($field['inputType'], 'date') !== false && strpos($field['orginType'], 'int') !== false) {
                    $args[$fieldName] = strtotime($value);
                }

                if ($field['inputType'] == 'checkbox' && $searchField['mapKey'] && $searchField['needMap2']) {
                    $this->_relaValues[$fieldName] = arrayPop($args, $fieldName);
                }
            } else {
                if ($type == 'add') {
                    // 添加模式，并且没有录入值，需要拿查询的默认值
                    $args[$fieldName] = Diy_Field::formatFieldValue($searchField['defaultValue']);
                }
            }

            // 判断是否有编辑默认值
            if ($field['editDefaultValue']) {
                $args[$fieldName] = $objEdit->getEditDefaultValue($field['editDefaultValue'], $args, $fieldName, $oldData);
            }
        }

        return $args;
    }

    private function _processRela($where, $id = 0, $needDel = true, $needAdd = true) {
        if (!$this->_relaValues) { return; }

        $objMap = new Diy_Map();
        foreach ($this->_relaValues as $fieldName => $value) {
            $searchField = $this->_searchFields[$fieldName];

            $mapInfo = $objMap->objTable->getRow(['mapKey' => $searchField['mapKey']]);
            if ($mapInfo['mapType'] == 2 && $mapInfo['nameDb'] && $mapInfo['sourceTable']) {
                $objTableHelper = new TableHelper($mapInfo['sourceTable'], $mapInfo['nameDb']);
                $keyField = $mapInfo['keyName'];
                $valField = $mapInfo['valueName'];

                $datas = [];
                if (trim($value)) {
                    $vs = explode(',', $value);
                    $keyValue = $where[$keyField] ? $where[$keyField] : $id;
                    foreach ($vs as $v) {
                        $datas[] = [$keyField => $keyValue, $valField => trim($v)];
                    }

                    if ($needDel && $keyValue) {
                        $objTableHelper->delObject([$keyField => $keyValue]);
                    }

                    if ($needAdd && $datas) {
                        $objTableHelper->addObjects2($datas);
                    }
                }
            }
        }
    }

    private function _pubEvent($tableInfo, $args) {
        if ($tableInfo['supportPub'] && $tableInfo['pubRedis']) {
            $objRedis = dwRedis::init($tableInfo['pubRedis']);
            $pubMsgCallback = trim($tableInfo['pubMsgCallback']);
            if ($pubMsgCallback) {
                $func = create_function('$args', $pubMsgCallback);
                $json = $func($args);
                if (is_array($json)) {
                    $json = json_encode($json);
                }
            } else {
                $json = json_encode($args);
            }

            $objRedis->publish($tableInfo['pubKey'], $json);
        }

    }

    private function _logAdd($tableId, $tableInfo, $data, $id) {
        $objEdit = new Diy_Edit();
        $fields = $objEdit->getFields($tableId);

        $userId = $_COOKIE['ouid'];
        $userName = $_COOKIE['username'];

        $operDesc = '';
        foreach ($data as $fieldName => $value) {
            $operDesc .= "{$fields[$fieldName]['fieldCName']}: {$value}\n";
        }

        $operType = '添加';
        $operSql = "INSERT INTO `{$tableInfo['sourceTable']}` SET ";
        foreach ($data as $fieldName => $value) {
            $operSql .= "`{$fieldName}` = '{$value}', ";
        }
        $operSql = substr($operSql, 0, -2);

        $priKeys = $objEdit->getPriKeys($tableId);
        $rollbackSql = "DELETE FROM `{$tableInfo['sourceTable']}` WHERE ";

        $where = [];
        foreach ($priKeys as $fieldName) {
            $value = $data[$fieldName] ? $data[$fieldName] : $id;
            $rollbackSql .= "`{$fieldName}` = '{$value}', ";
            $where[$fieldName] = $value;
        }
        $rollbackSql = substr($rollbackSql, 0, -2);

        $type = 'add';
        $this->_pubEvent($tableInfo, compact('type', 'where', 'data'));

        $objOperLog = new TableHelper('Cmdb3OperationLog', 'Report');
        $objOperLog->addObject(compact('tableId', 'userId', 'userName', 'operType', 'operDesc', 'operSql', 'rollbackSql'));
    }

    private function _logUpdate($tableId, $tableInfo, $oldData, $newData, $where) {
        $objEdit = new Diy_Edit();
        $fields = $objEdit->getFields($tableId);

        $objOperLog = new TableHelper('Cmdb3OperationLog', 'Report');
        $userId = $_COOKIE['ouid'];
        $userName = $_COOKIE['username'];

        $operDesc = '';
        foreach ($newData as $fieldName => $value) {
            if ($oldData[$fieldName] != $value) {
                $operDesc .= "{$fields[$fieldName]['fieldCName']}: 由 '{$oldData[$fieldName]}' 改为 '{$value}' \n";
            }
        }

        // 修改
        $operType = '修改';
        $operSql = "UPDATE `{$tableInfo['sourceTable']}` SET ";
        foreach ($newData as $fieldName => $value) {
            $operSql .= "`{$fieldName}` = '{$value}', ";
        }
        $operSql = substr($operSql, 0, -2) . ' ';

        $operSql .= "WHERE ";
        foreach ($where as $fieldName => $value) {
            $operSql .= "`{$fieldName}` = '{$value}' AND ";
        }
        $operSql = substr($operSql, 0, -4);

        $rollbackSql = "UPDATE `{$tableInfo['sourceTable']}` SET ";
        foreach ($oldData as $fieldName => $value) {
            $rollbackSql .= "`{$fieldName}` = '{$value}', ";
        }
        $rollbackSql = substr($rollbackSql, 0, -2) . ' ';

        $rollbackSql .= "WHERE ";
        foreach ($where as $fieldName => $value) {
            $rollbackSql .= "`{$fieldName}` = '{$value}' AND ";
        }
        $rollbackSql = substr($rollbackSql, 0, -4);

        $type = 'update';
        $data = $newData;
        $this->_pubEvent($tableInfo, compact('type', 'where', 'data', 'oldData'));

        $objOperLog->addObject(compact('tableId', 'userId', 'userName', 'operDesc', 'operType', 'operSql', 'rollbackSql'));
    }

    private function _logDelete($tableId, $tableInfo, $oldData, $where) {
        $objEdit = new Diy_Edit();
        $fields = $objEdit->getFields($tableId);

        $objOperLog = new TableHelper('Cmdb3OperationLog', 'Report');
        $userId = $_COOKIE['ouid'];
        $userName = $_COOKIE['username'];

        $operDesc = '';
        foreach ($oldData as $fieldName => $value) {
            $operDesc .= "{$fields[$fieldName]['fieldCName']}: {$value}\n";
        }

        $operType = '删除';
        $operSql = "DELETE FROM `{$tableInfo['sourceTable']}` WHERE ";
        foreach ($where as $fieldName => $value) {
            $operSql .= "`{$fieldName}` = '{$value}' AND ";
        }
        $operSql = substr($operSql, 0, -4);

        $oldData = $where + $oldData;
        $rollbackSql = "INSERT INTO {$tableInfo['sourceTable']} SET ";
        foreach ($oldData as $fieldName => $value) {
            $rollbackSql .= "`{$fieldName}` = '{$value}', ";
        }
        $rollbackSql = substr($rollbackSql, 0, -2) . ' ';

        $type = 'delete';
        $data = $oldData;
        $this->_pubEvent($tableInfo, compact('type', 'where', 'data'));

        $objOperLog->addObject(compact('tableId', 'userId', 'userName', 'operDesc', 'operType', 'operSql', 'rollbackSql'));
    }

    private function _logRollback($tableId, $row) {
        $userId = $_COOKIE['ouid'];
        $userName = $_COOKIE['username'];

        $operDesc = "撤销{$row['operType']}, 操作日志ID:{$row['operLogId']}";
        $operType = '撤销';
        $operSql = $row['rollbackSql'];

        $objOperLog = new TableHelper('Cmdb3OperationLog', 'Report');
        $objOperLog->addObject(compact('tableId', 'userId', 'userName', 'operDesc', 'operType', 'operSql'));
    }

    private function _logImport($tableId, $operDesc) {
        $userId = $_COOKIE['ouid'];
        $userName = $_COOKIE['username'];

        $operType = '导入';
        $objOperLog = new TableHelper('Cmdb3OperationLog', 'Report');
        $objOperLog->addObject(compact('tableId', 'userId', 'userName', 'operDesc', 'operType'));
    }

    private function _saveCallBack($tableInfo, $type, $newData, $oldData, $where) {
        // 判断是否存在保存回调
        if ($tableInfo['saveCallBack']) {
            $func = create_function('$_type, $_newData, $_oldData, $_where', $tableInfo['saveCallBack']);
            $ret = $func($type, $newData, $oldData, $where);
            if (!$ret) {
                Response::error(CODE_NO_PERMITION);
            }
        }
    }

    public function actionAdd($args) {
        $rules = array(
            'tableId' => 'string',
        );
        Param::checkParam($rules, $args);
        
        // 检查权限
        Permission::checkTableRight($args['tableId']);
        
        $tableId = arrayPop($args, 'tableId');
        $type = 'add';
        $args = $this->_processSaveData($args, $tableId, [], $type);

        $objTable = new Diy_Table();
        $tableInfo = $objTable->getTableInfo($tableId);

        // 保存回调
        $newData = $args;
        $oldData = [];
        $where = [];
        $this->_saveCallBack($tableInfo, $type, $newData, $oldData, $where);

        $eventData = compact('type', 'newData', 'oldData', 'where');
        Diy_Table::triggerBeforEvent(Diy_Table::EVENT_SAVE_BEFORE, $eventData);

        $args = $eventData['newData'];
        $objData = new Diy_Data();

        // 添加的时候，如果没有值就不插入
        foreach ($args as $key => $value) {
            if (!$value && $value != '0') {
                unset($args[$key]);
            }
        }

        $objEdit = new Diy_Edit();
        $priKeys = $objEdit->getPriKeys($tableId);
        $where = arrayFilter($args, $priKeys);
        if (count($where) == count($priKeys)) {
            $objData = new Diy_Data();
            $objHelper = $objData->getHelper($tableId);
            $count = $objHelper->getCount($where);
            if ($count > 0) {
                Response::error(CODE_NORMAL_ERROR, '重复添加，已经存在：' . join(',', $where));
            }
        }

//        var_dump($args);exit;
        // 更新操作
        if ($tableInfo['supportR2M']) {
            $objHelper = $objData->getR2M($tableId);
        } else {
            $objHelper = $objData->getHelper($tableId);
        }

        $flag = $objHelper->addObject($args);
        $id = $objHelper->getInsertId();

        $this->_logAdd($tableId, $tableInfo, $args, $id);
        $this->_processRela($args, $id, false, true);

        Diy_Table::trigger(Diy_Table::EVENT_SAVE_AFTER, $eventData);

        Response::success($flag, '新增成功！');
    }
    
    private function _checkPriKey($args, $tableId) {
        $objEdit = new Diy_Edit();
        $priKeys = $objEdit->getPriKeys($tableId);
        if (!$priKeys) {
            Response::error(CODE_PARAM_ERROR, '这个报表没有主键，不能编辑！');
        }
        
        $where = arrayFilter($args, $priKeys);
        $objData = new Diy_Data();
        $objHelper = $objData->getHelper($tableId);
        
        $count = $objHelper->getCount($where);
        if ($count > 1) {
            Response::error(CODE_PARAM_ERROR, '影响行数超过1条，不能保存！');
        } else if ($count === 0) {
            Response::error(CODE_PARAM_ERROR, '影响行数为0条，不能保存！');
        }
        
        return $where;
    }
    
    public function actionDel($args) {
        $rules = array(
            'tableId' => 'string',
        );
        Param::checkParam($rules, $args);
        
        // 检查权限
        Permission::checkTableRight($args['tableId']);
    
        $tableId = arrayPop($args, 'tableId');
        $where = $this->_checkPriKey($args, $tableId);

        $objTable = new Diy_Table();
        $tableInfo = $objTable->getTableInfo($tableId);

        $row = $this->_getEditData($tableId, $where);
        $type = 'del';
        $this->_processSaveData($row, $tableId, $row, $type);


        // 保存回调
        $newData = $oldData = $row;
        $this->_saveCallBack($tableInfo, $type, $newData, $oldData, $where);

        $eventData = compact('type', 'newData', 'oldData', 'where');
        Diy_Table::triggerBeforEvent(Diy_Table::EVENT_SAVE_BEFORE, $eventData);

        $objData = new Diy_Data();
        $objHelper = $objData->getHelper($tableId);
        if ($tableInfo['supportR2M']) {
            $objR2m = $objData->getR2M($tableId);
            $flag = $objR2m->delObject($where);
        } else {
            $flag = $objHelper->delObject($where);
        }

        $this->_logDelete($tableId, $tableInfo, $row, $where);
        $this->_processRela($where + $args, 0, true, false);

        Diy_Table::trigger(Diy_Table::EVENT_SAVE_AFTER, $eventData);

        Response::success($flag, '删除成功！');
    }

    public function actionRollback($args) {
        $rules = array(
          'operLogId' => 'string',
        );
        Param::checkParam2($rules, $args);

        $objHelper = new TableHelper('Cmdb3OperationLog', 'Report');
        $row = $objHelper->getRow($args);

        $tableId = $row['tableId'];
        // 检查权限
        Permission::checkTableRight($tableId);

        $objTable = new Diy_Table();
        $tableInfo = $objTable->getTableInfo($tableId);
        if ($tableInfo['supportR2M']) {
            Response::error(CODE_PARAM_ERROR, 'R2m报表,暂不支持撤销');
        }

        if (!$row['rollbackSql']) {
            Response::error(CODE_PARAM_ERROR, '这个记录不能回滚');
        }

        $objData = new Diy_Data();
        $objHelper = $objData->getHelper($tableId);
        $objHelper->getDb()->update($row['rollbackSql']);

        $this->_logRollback($tableId, $row);

        Response::success('撤销成功！');
    }

    public function actionDelMulti($args) {
        $rules = array(
          'tableId' => 'string',
          'ids' => 'string'
        );
        Param::checkParam($rules, $args);

        // 检查权限
        Permission::checkTableRight($args['tableId']);

        $objData = new Diy_Data();
        $objTable = new Diy_Table();
        $tableId = arrayPop($args, 'tableId');
        $tableInfo = $objTable->getTableInfo($tableId);

        $flag = false;
        $ids = json_decode($args['ids'], 'true');
        $objR2m = null;
        $objHelper = $objData->getHelper($tableId);

        $type = 'del';
        foreach ($ids as $data) {
            $where = $this->_checkPriKey($data, $tableId);
            if (!$this->_fields) {
                $row = $this->_getEditData($tableId, $where);
                $this->_processSaveData($row, $tableId, $row, $type);

                // 保存回调
                $newData = $args;
                $oldData = $row;
                $this->_saveCallBack($tableInfo, $type, $newData, $oldData, $where);

                $eventData = compact('type', 'newData', 'oldData', 'where');
                Diy_Table::triggerBeforEvent(Diy_Table::EVENT_SAVE_BEFORE, $eventData);
            } else {
                $row = $data;
            }

            if ($tableInfo['supportR2M']) {
                if (!$objR2m) {
                    $objR2m = $objData->getR2M($tableId);
                }
                $flag = $objR2m->delObject($where);
            } else {
                $flag = $objHelper->delObject($where);
            }

            $this->_logDelete($tableId, $tableInfo, $row, $where);

            $this->_processRela($where, 0, true, false);

            Diy_Table::trigger(Diy_Table::EVENT_SAVE_AFTER, $eventData);
        }

        Response::success($flag, '删除成功！');
    }

    public function actionSave($args) {
        $rules = array(
            'tableId' => 'string',
            '_where' => 'string'
        );
        Param::checkParam($rules, $args);
        
        // 检查权限
        Permission::checkTableRight($args['tableId']);
        
        $tableId = arrayPop($args, 'tableId');
        $where = arrayPop($args, '_where'); 
        $where = json_decode($where, true);
        
        $where = $this->_checkPriKey($where, $tableId);

        $objTable = new Diy_Table();
        $tableInfo = $objTable->getTableInfo($tableId);


        $objData = new Diy_Data();
        if ($tableInfo['supportR2M']) {
            $objHelper = $objData->getR2M($tableId);
        } else {
            $objHelper = $objData->getHelper($tableId);
        }

        $oldData = $objHelper->getRow($where);
        $type = 'edit';
        $args = $this->_processSaveData($args, $tableId, $oldData, $type);

        $newData = [];
        foreach ($args as $key => $value) {
            //  这里是怕设置了空数据，造成：0000-00-00 00:00:00
            if ($oldData[$key] != $value) {
                $newData[$key] = $value;
            }
        }

        // 保存回调
        $this->_saveCallBack($tableInfo, $type, $newData, $oldData, $where);

        $eventData = compact('type', 'newData', 'oldData', 'where');
        Diy_Table::triggerBeforEvent(Diy_Table::EVENT_SAVE_BEFORE, $eventData);
        $newData = $eventData['newData'];

        if ($newData) {
            $flag = $objHelper->updateObject($newData, $where);
            $this->_logUpdate($tableId, $tableInfo, $oldData, $newData, $where);
            $this->_processRela($where + $args, 0, true, true);
        } else {
            $flag = false;
        }

        Diy_Table::trigger(Diy_Table::EVENT_SAVE_AFTER, $eventData);

        Response::success($flag, '保存成功！');
    }


    public function actionImportView($args) {
        $rules = array(
          'tableId' => 'string',
        );
        Param::checkParam($rules, $args);

        // 检查权限
        Permission::checkTableRight($args['tableId']);

        $this->tpl->display('diy/table_edit_import');
    }

    private function _getCsvData($file) {
        $str = file_get_contents($file);
        //判断是否不是UTF-8编码，如果不是UTF-8编码，则转换为UTF-8编码
        $encode = mb_detect_encoding($str,"UTF-8, ISO-8859-1, GBK");
        if ($encode != "UTF-8" ) {
            $str = iconv("gbk","utf-8", $str);
        }

        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        $rows = explode("\n", $str);

        return $rows;
    }

    private function _getExcelData($file) {
        $objXLSXReader = new XLSXReader($file);
        $sheetNames = $objXLSXReader->getSheetNames();
        $datas = $objXLSXReader->getSheetData(current($sheetNames));

        $errorRowNum = 0;
        foreach ($datas as $i => $data) {
            if ($i === 0) {
                foreach ($data as $fieldName) {
                    if ($fieldName === null) {
                        $errorRowNum++;
                    }
                }
            }

            if ($errorRowNum > 0) {
                $datas[$i] = array_slice($data, 0, -$errorRowNum);
            } else {
                break;
            }
        }

        return $datas;
    }

    public function actionImport($args) {
        $rules = array(
          'tableId' => 'string',
        );
        Param::checkParam($rules, $args);

        // 检查权限
        Permission::checkTableRight($args['tableId']);

        $files = $_FILES['data_file']['tmp_name'];
        $files2 = $_FILES['data_file']['name'];
        if (!$files) {
            Response::error(CODE_PARAM_ERROR, '没发现文件');
        }

        $tableId = arrayPop($args, 'tableId');
        $objTable = new Diy_Table();
        $tableInfo = $objTable->getTableInfo($tableId);

        $objEdit = new Diy_Edit();
        $priKeys = $objEdit->getPriKeys($tableId);
        if (!$priKeys) {
            Response::error(CODE_PARAM_ERROR, '这个报表没有主键，不能编辑！');
        }

        $templateFileds = explode(',', $tableInfo['templateField']);
        $fieldNames = [];
        foreach ($templateFileds as $i => $templateFiled) {
            $v = trim($templateFiled);
            $v && $fieldNames[] = $v;
        }

        $objData = new Diy_Data();
        if ($tableInfo['supportR2M']) {
            $objHelper = $objData->getR2M($tableId);
        } else {
            $objTableHelper = $objData->getHelper($tableId);
            $objHelper = $objTableHelper;
        }

        $addCount = 0;
        $updateCount = 0;
        $noChangeCount = 0;
        foreach ($files as $fileIndex => $file) {
            $fileNameInfo = pathinfo($files2[$fileIndex]);
            if ($fileNameInfo['extension'] !== 'xlsx') {
                $msg = '只支持xlsx文件上传';
                Response::error(CODE_PARAM_ERROR, $msg);
            } elseif ($file) {
                $rows = $this->_getExcelData($file);

                $type = 'import';
                $this->_saveCallBack($tableInfo, $type, $rows, [], []);

                Diy_Table::trigger(Diy_Table::EVENT_IMPORT_BEFORE, $rows);
                $newRows =[];
                $insertDatas = [];
                foreach ($rows as $i => $row) {
//                    $row = trim($row);
                    if ($i == 0 || !$row) {
                        continue;
                    }

//                    $row = str_getcsv($row);
                    if (count($row) != count($fieldNames)) {
                        Response::error(CODE_PARAM_ERROR, '模板格式不对');
                    }

                    $newRow = [];
                    foreach ($row as $j => $v) {
                        $fieldName = $fieldNames[$j];
                        $newRow[$fieldName] = $v;
                    }

                    $where = arrayFilter($newRow, $priKeys);
                    if (count($where) == count($priKeys)) {
                        $row = $objHelper->getRow($where);
                        if ($row) {
                            $diff = array_diff($row, $newRow);
                            if ($diff) {
                                $updateCount++;
                                $objHelper->updateObject($newRow, $where);
                            } else {
                                $noChangeCount++;
                            }
//                            $this->_logUpdate($tableId, $tableInfo, $row, $newRow, $where);
                        } else {
                            $addCount++;
                            $insertDatas[] = $newRow;
//                            $objHelper->addObject($newRow);
//                            $id = $objHelper->getInsertId();
//                            $this->_logAdd($tableId, $tableInfo, $newRow, $id);
                        }
                    }

                    $newRows[] = $newRow;
                }

                if ($insertDatas) {
                    $objHelper->addObjects2($insertDatas);
                }

                Diy_Table::trigger(Diy_Table::EVENT_IMPORT_AFTER, $newRows);
            }
        }

        $c = $addCount + $updateCount;
        $msg = "受影响{$c}行, 增加了{$addCount}行, 修改了{$updateCount}行，{$noChangeCount}行不变.";
        $this->_logImport($tableId, $msg);
        Response::success([], $msg);
    }
}
