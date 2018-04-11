<?php

/**
 * Diy报表配置
 * @author benzhan
 */
class DiyConfigController extends BaseController {
    private $_dbKey = "Report";

    protected $noLoginActions = array('reviceSync');

    /**
     * 配置报表
     * @param int tableType 报表类型(1:普通报表, 2:源表)
     * @author benzhan
     */
    function actionEdit($args) {
        require_once ROOT_PATH . 'conf/diyConfig.inc.php';
        
        $rules = array(
            'tableId' => array('string', 'nullable' => true),
            'tableType' => ['int', 'nullable' => true]
        );
        Param::checkParam($rules, $args);
        
        $tableId = $args['tableId'];
        $tableType = $args['tableType'];
        
        if ($tableId) {
            $oConfigTable = new Diy_Table();
            $tableInfo = $oConfigTable->getTableInfo($tableId);
            $isAdmin = $oConfigTable->isAdmin2($tableInfo);
            if (!$isAdmin) {
                Response::exitMsg("<meta charset='utf-8'><p>对不起，您没有权限. </p>");
            }
            $link = SITE_URL . 'DiyData/report?tableId=' . $tableId;
        } else {
            $link = "新建的页面,保存后自动生成...";
        }
        
        $oConfig= new Diy_Config();
        $sourceHosts = $oConfig->getHosts();
        $redisHosts = $oConfig->getRedisHosts();
        $staticModes = $oConfig->getStaticModes();

        $oDb = new Diy_Db();
        $dbs = $oDb->objTable->getAll();

        $nameDbs = array_keys($GLOBALS['dbInfo']);
        $nameRedises = array_keys($GLOBALS['redisInfo']);

        $nameRedisKeys = [];
        foreach ($GLOBALS['r2mInfo'] as $dbName => $tables) {
            foreach ($tables as $tableName => $info) {
                $nameRedisKeys[] = "{$dbName}:{$tableName}";
            }
        }

        sort($nameDbs);
        sort($nameRedises);
        sort($nameRedisKeys);

//        $map = $GLOBALS['diy']['map'];
        $pageSizes = $GLOBALS['diy']['pageSizes'];

        $args = compact('tableInfo', 'sourceHosts', 'dbs', 'redisHosts', 'link', 'map', 'pageSizes', 'isAdmin', 'tableType');
        $args += compact('nameDbs', 'nameRedises', 'nameRedisKeys', 'staticModes');

        $this->tpl->assign($args);
        $this->tpl->display('diy/config');
    }


    function actionGetDbIds() {
        try {
            $oDiyDb = new Diy_Db();
            $datas = $oDiyDb->objTable->getAll();
            $tData = [];
            foreach ($datas as $data) {
                $tData[$data['dbId']] = $data['sourceHost'] . ':' . $data['sourcePort'] . ' -> ' . $data['sourceDb'];
            }

            return $tData;
        } catch(Exception $ex) {
            Response::error(CODE_DB_ERROR, $ex->getMessage());
        }
    }

    /**
     * 读取数据库系信息
     * @author benzhan
     * @param unknown $args
     */
    function actionGetDbs($args) {
        $rules = array(
            'sourceHost' => 'ip',
            'sourcePort' => 'int',
            'sourceUser' => array('string', 'emptyable' => true),
            'sourcePass' => array('string', 'emptyable' => true),
        );
        Param::checkParam2($rules, $args);

        try {
            $oConfig= new Diy_Config();
            $datas = $oConfig->getDbs($args);
            $tData = [];
            foreach ($datas as $data) {
                $tData[$data] = $data;
            }
            return $tData;
        } catch(Exception $ex) {
            Response::error(CODE_DB_ERROR, $ex->getMessage());
        }
    }
    
    /**
     * 获取表格信息
     * @author benzhan
     * @param unknown $args
     */
    function actionGetTables($args) {
        $rules = array(
            'sourceHost' => ['string', 'nullable' => true],
            'sourcePort' => ['int','nullable' => true],
            'sourceDb' => array('string', 'emptyable' => true),
            'sourceUser' => array('string', 'emptyable' => true),
            'sourcePass' => array('string', 'emptyable' => true),
            'dbId' => array('string', 'nullable' => true),
            'nameDb' => array('string', 'nullable' => true),
        );
        Param::checkParam($rules, $args);
    
        $oConfig= new Diy_Config();
        $datas = $oConfig->getTables($args);
        $tData = [];
        foreach ($datas as $data) {
            $tData[$data] = $data;
        }
        return $tData;
    }

    function actionGetPriKeys($args) {
        $rules = array(
          'nameDb' => array('string', 'nullable' => true),
          'dbId' => ['int', 'nullable' => true],
          'sourceHost' => ['ip', 'nullable' => true],
          'sourcePort' => ['int','nullable' => true],
          'sourceDb' => ['string','nullable' => true],
          'sourceUser' => array('string', 'emptyable' => true),
          'sourcePass' => array('string', 'emptyable' => true),
          'sourceTable' => 'string'
        );
        Param::checkParam($rules, $args);

        $oConfig= new Diy_Config();
        return $oConfig->getPriKeys($args);
    }

    function actionTestConnectRedis($args) {
        $rules = array(
          'redisHost' => 'ip',
          'redisPort' => 'int',
          'redisPass' => array('string', 'emptyable' => true),
          'redisDb' => array('int', 'emptyable' => true),
        );
        Param::checkParam($rules, $args);

        $oData = new Diy_Data();
        $objRedis = $oData->getRedis($args);
        if (!$objRedis->isConnected) {
            Response::error(CODE_NORMAL_ERROR, "Redis 连接失败");
        }
    }

    public function actionLoadFieldTable($args) {
        require_once ROOT_PATH . 'conf/diyConfig.inc.php';
        
        $rules = array(
            'tableId' => array('string', 'nullable' => true),
            'loadType' => array('int', 'enum' => array(1, 2, 3)),
            'tableType' => array('int', 'nullable' => true),
        );
        Param::checkParam($rules, $args);
        
        $tableId = $args['tableId'];
        $tableType = $args['tableType'];
        $tableType || $tableType = 1;

        // 加载原字段
        $objField = new Diy_Field();
        if ($tableId && $args['loadType'] & 1) {
            $oldFields = $objField->getFields2($tableId);
            $fields = $oldFields;
        } else {
            $oldFields = [];
        }
        
        // 加载数据库字段
        if ($args['loadType'] & 2) {
            $objConfig = new Diy_Config();
            $newFields = $objConfig->getDbFields($args);

            $fields = $objField->processFields($newFields, array(), $tableType);
        } else {
            $newFields = [];
        }
    
        // 原字段覆盖数据库字段
        if ($args['loadType'] == 3) {
            $fields = $objField->processFields($newFields, $oldFields, $tableType);
        }

        // 获取数据字典
        $objMap = new Diy_Map();
        $where = ['enable' => 1];
        $keyWord = ['_sortKey' => 'nameDb ASC, title ASC'];
        $maps = $objMap->objTable->getAll($where, $keyWord);
        foreach ($maps as $i => $value) {
            $maps[$i]['title'] = "{$value['nameDb']}-{$value['title']}";
        }

        $fields = $objField->processFields2($fields);

        $fieldTypes = $GLOBALS['diy']['fieldTypes'];
        $inputTypes = $GLOBALS['diy']['inputTypes'];

        $map = $GLOBALS['diy']['map'];
        $data = compact('fields', 'fieldTypes', 'inputTypes', 'map', 'maps');
        
        $template = Template::init();
        $template->assign(compact('data'));
        $template->display('diy/config_table');
    }
    
    public function actionSaveTableAndFields($args) {
        $args['fields'] = json_decode($args['fields'], true);
        $rules = array(
            'tableId' => array('string', 'nullable' => true),
            'tableType' => array('int', 'nullable' => true),
            'fields' => 'array',
        );
        Param::checkParam($rules, $args);
        
        $oBaseTable = new TableHelper('Cmdb3Table', $this->_dbKey);
        $tableId = $args['tableId'];
        $tableType = $args['tableType'];
        $tableType || $tableType = 1;
        $fields = arrayPop($args, 'fields');
    
        try {
            $oBaseTable->autoCommit(false);
            $args['lastModifyTime'] = date('Y-m-d H:i:s');
            if ($tableId) {
                $one = $oBaseTable->getOne(compact('tableId'));
                if (!$one) {
                    Response::error(CODE_PARAM_ERROR, null, 'tableId is not valid.');
                }
                
                // 修改时，才需要检查权限
                $this->_checkRight($tableId);
                //修改表信息
                $where = compact('tableId');
                $oBaseTable->updateObject($args, $where);
            } else {
                //添加表信息
                $args['tableId'] = $tableId = uuid();
                $args['tableType'] = $tableType;
                $args['createTime'] = date('Y-m-d H:i:s');
                // $args['authorId'] = $user['userId'] ? $user['userId'] : 0;
                $args['authorName'] = $_SESSION['username'] ? $_SESSION['username'] : 'guest';
                $args['tableName'] || $args['tableName'] = $args['tableCName'];
                $oBaseTable->addObject($args);
                $where = compact('tableId');
            }
    
            $oBaseFields = new TableHelper('Cmdb3Field', $this->_dbKey);
            $oldFieldIds = $oBaseFields->getAll($where, array('_field' =>'fieldId'));
            $oldFieldIds = arrayFormatKey($oldFieldIds, 'fieldId');

            $oEditFields = new TableHelper('Cmdb3Edit', $this->_dbKey);
            foreach ($fields as $field) {
                $field['tableId'] = $tableId;

                $fieldId = $field['fieldId'];
                if ($fieldId) {
                    unset($oldFieldIds[$fieldId]);
                    $oBaseFields->updateObject($field, compact('fieldId'));

                    // 同时修改Edit表的主键
                    // $newData = arrayFilter($field, ['isPrimaryKey', 'inputType']);
                    $newData = arrayFilter($field, ['isPrimaryKey']);
                    $where = arrayFilter($field, ['tableId', 'fieldName']);
                    $oEditFields->updateObject($newData, $where);
                } else {
                    $field['fieldId'] = uuid();
                    $oBaseFields->addObject($field);
                }
            }
    
            if ($oldFieldIds) {
                $oldFieldIds = array_keys($oldFieldIds);
                $oBaseFields->delObject(array('fieldId' => $oldFieldIds));
            }
    
            $oBaseTable->tryCommit();
        } catch (Exception $ex) {
            Response::error(CODE_DB_ERROR, null, $ex->getMessage());
        }
    
        Response::success($tableId, "保存成功,复制链接地址可查看数据");
    }
    
    public function actionCopyTable($args) {
        $tableId = $args['tableId'];
        $rules = array(
            'tableId' => array('string', 'nullable' => true),
        );
        Param::checkParam($rules, $args);
        
        $newTableId = uuid();
    
        try {
            //复制table
            $oBase = new TableHelper('Cmdb3Table', $this->_dbKey);
            $oBase->autoCommit(false);
    
            Tool::log('copy Cmdb3Table.');
            $where = compact('tableId');
            $where = $oBase->escape($where);
            $table = $oBase->getRow($where);
            $table['tableId'] = $newTableId;
            $table['createTime'] = date('Y-m-d H:i:s');
            $table['tableName'] .= '【复制】' . $table['createTime'];
            $table['tableCName'] .= '【复制】' . $table['createTime'];
            $table['lastModifyTime'] = date('Y-m-d H:i:s');
            
//            $table['authorId'] = $_SESSION['yyuid'] ? $_SESSION['yyuid'] : 0;
            $table['authorId'] = 0;
            $table['authorName'] = $_SESSION['username'] ? $_SESSION['username'] : 'guest';
            $table['admins'] = $table['authorName'];
            
            $oTable = new Diy_Table();
            $isAdmin = $oTable->isAdmin2($table);
            if (!$isAdmin) {
                $table['sourceUser'] = '';
                $table['sourcePass'] = '';
            }
    
            $oBase->addObject($table);
                
                // 复制tableMeta
                /*
             * $oBase = new TableHelper('Cmdb3TableMeta', $this->_dbKey);
             * $tableMetas = $oBase->getAll($where);
             * foreach ($tableMetas as $i => $tableMeta) {
             * $tableMetas[$i]['tableId'] = $newTableId;
             * }
             * $oBase->addObjects2($tableMetas);
             */
                
            // 复制fields
            $oBase = new TableHelper('Cmdb3Field', $this->_dbKey);
            $fields = $oBase->getAll($where);
            foreach ($fields as $i => $field) {
                $fields[$i]['tableId'] = $newTableId;
                $fields[$i]['fieldId'] = uuid();
            }
            $oBase->addObjects2($fields);
            
            // 复制edit
            $objEdit = new Diy_Edit();
            $fields = $objEdit->objTable->getAll($where);
            foreach ($fields as $i => $field) {
                $fields[$i]['tableId'] = $newTableId;
                $fields[$i]['fieldId'] = uuid();
            }
            $objEdit->objTable->addObjects2($fields);
    
            $oBase->tryCommit();
            return true;
        } catch (Exception $ex) {
            Response::error(CODE_DB_ERROR, null, $ex->getMessage());
        }
    }

    public function actionDeleteTable($args) {
        $tableId = $args['tableId'];
        $where = compact('tableId');
        $rules = array(
            'tableId' => array('string', 'nullable' => true),
        );
        Param::checkParam($rules, $args);
        
        // 检查权限
        $this->_checkRight($tableId);
        
        try {
            $oBase = new TableHelper('Cmdb3Table', $this->_dbKey);
            $where = $oBase->escape($where);
            $oBase->autoCommit(false);
            
            $oBase->delObject($where + array(
                '_tableName' => 'Cmdb3TableMeta'
            ));
            
            $oBase->delObject($where + array(
                '_tableName' => 'Cmdb3Field'
            ));
            
            $oBase->delObject($where + array(
                '_tableName' => 'Cmdb3Edit'
            ));
            
            $oBase->delObject($where + array(
                '_tableName' => 'Cmdb3Table'
            ));
            
            $oBase->tryCommit();
            return true;
        } catch (Exception $ex) {
            Tool::err($ex->getMessage());
            Tool::err($ex->getTrace());
            Response::error(CODE_DB_ERROR, null, $ex->getMessage());
        }
        
        return true;
    }
    
    private function _setDefault($args, $metaKey) {
        $rules = array(
            'tableId' => 'string',
            'metaValue' => 'string'
        );
        Param::checkParam2($rules, $args);
        
        $args['metaKey'] = $metaKey;
        // 检查权限
        $this->_checkRight($args['tableId']);
        
        $objTable = new Diy_Table();
        $flag = $objTable->setTableMeta($args);
        Response::success($flag, '保存成功！');
    }

    public function actionSetDefaultCondition($args) {
        $this->_setDefault($args, 'tableDefaultCondition');
    }
    
    public function actionSetDefaultView($args) {
        $this->_setDefault($args, 'tableDefaultView');
    }
    
    public function actionGetDefaultCondition($args) {
        $rules = array(
            'tableId' => 'string',
        );
        Param::checkParam2($rules, $args);
    
        // 检查权限
        $this->_checkRight($args['tableId']);
        
        $args['metaKey'] = 'tableDefaultCondition';
    
        $objTable = new Diy_Table();
        $data = $objTable->getTableMeta($args);
        Response::success($data);
    }
    
    /**
     * 检查用户权限
     * @author benzhan
     * @param unknown $tableId
     */
    private function _checkRight($tableId) {
        $objTable = new Diy_Table();
        if (!$objTable->isAdmin($tableId)) {
            Response::error(CODE_NO_PERMITION, null, '');
        }
    }
    
    /**
     * 加载编辑功能的列表
     * @author benzhan
     * @param string tableId 表id
     * @param int loadEditType 加载类型，1，加载原字段；2，加载数据库；3，原字段覆盖数据库字段
     */
    public function actionLoadEditFields($args) {
        require_once ROOT_PATH . 'conf/diyConfig.inc.php';
        
        $rules = array(
            'tableId' => array('string', 'nullable' => true),
            'loadEditType' => array('int', 'enum' => array(1, 2, 3)),
        );
        Param::checkParam($rules, $args);
        
        $tableId = $args['tableId'];
        // 加载原字段
        $objEdit = new Diy_Edit();
        if ($tableId && $args['loadEditType'] & 1) {
            $oldFields = $objEdit->getFields($tableId);
            $fields = $oldFields;
        }
        
        // 加载数据库字段
        if ($args['loadEditType'] & 2) {
            $objConfig = new Diy_Config();
            $newFields = $objConfig->getDbFields($args);
            $fields = $objEdit->processEditFields($newFields, array());
        }

        // 原字段覆盖数据库字段
        if ($args['loadEditType'] == 3) {
            $fields = $objEdit->processEditFields($newFields, $oldFields);
        }
        
        $fields = $objEdit->processEditFields2($fields);
        
        $inputTypes = $GLOBALS['diy']['inputTypes'];
        $map = $GLOBALS['diy']['map'];
        $data = compact('fields', 'inputTypes', 'map');
        
        $template = Template::init();
        $template->assign(compact('data'));
        $template->display('diy/config_edit');
  
    }

    public function actionSaveEditTable($args) {
        $args['fields'] = json_decode($args['fields'], true);
        $rules = array(
            'tableId' => array('string'),
            'fields' => 'array',
        );
        Param::checkParam($rules, $args);
    
        $oBaseTable = new TableHelper('Cmdb3Table', $this->_dbKey);
        $tableId = $args['tableId'];
        $fields = arrayPop($args, 'fields');
    
        try {
            $oBaseTable->autoCommit(false);
            $args['lastModifyTime'] = date('Y-m-d H:i:s');

            $one = $oBaseTable->getOne(compact('tableId'));
            if (!$one) {
                Response::error(CODE_PARAM_ERROR, null, 'tableId is not valid.');
            }

            // 修改时，才需要检查权限
            $this->_checkRight($tableId);
            //修改表信息
            $where = compact('tableId');
            $oBaseTable->updateObject($args, $where);

            $objEdit = new Diy_Edit();
            $oldFieldIds = $objEdit->objTable->getAll($where, array('_field' =>'fieldId'));
            $oldFieldIds = arrayFormatKey($oldFieldIds, 'fieldId');
    
            foreach ($fields as $field) {
                $field['tableId'] = $tableId;
                $fieldId = $field['fieldId'];
                if ($fieldId) {
                    unset($oldFieldIds[$fieldId]);
                    $objEdit->objTable->updateObject($field, compact('fieldId'));
                } else {
                    $field['fieldId'] = uuid();
                    $objEdit->objTable->addObject($field);
                }
            }
    
            if ($oldFieldIds) {
                $oldFieldIds = array_keys($oldFieldIds);
                $objEdit->objTable->delObject(array('fieldId' => $oldFieldIds));
            }
    
            $oBaseTable->tryCommit();
        } catch (Exception $ex) {
            Response::error(CODE_DB_ERROR, null, $ex->getMessage());
        }
    
        Response::success($tableId, "保存成功,复制链接地址可查看数据");
    }

    public function actionImportView() {
        $this->tpl->display('diy/config_import');
    }

    private function _exportDiyReport($tableId) {
        $args = compact('tableId');
        $objCmdb3Table = new TableHelper('Cmdb3Table', $this->_dbKey);
        $table = $objCmdb3Table->getRow($args);
        if (!$table) {
            Response::error(CODE_PARAM_ERROR);
        }

        // 隐私信息不能导出
        unset($table['sourceUser']);
        unset($table['sourcePass']);
        unset($table['redisPass']);

        $objCmdb3Field = new TableHelper('Cmdb3Field', $this->_dbKey);
        $fields = $objCmdb3Field->getAll($args);

        $objCmdb3Edit = new TableHelper('Cmdb3Edit', $this->_dbKey);
        $editFields = $objCmdb3Edit->getAll($args);

        $objCmdb3TableMeta = new TableHelper('Cmdb3TableMeta', $this->_dbKey);
        $tableMetas = $objCmdb3TableMeta->getAll($args);

        $mapKeys = [];
        foreach ($fields as $field) {
            $mapKeys[] = $field['mapKey'];
        }

        if (count($mapKeys) > 0) {
            $objCmdb3Map = new TableHelper('Cmdb3Map', $this->_dbKey);
            $maps = $objCmdb3Map->getAll(['mapKey' => $mapKeys]);
        } else {
            $maps = [];
        }

        $data = compact('table', 'fields', 'editFields', 'tableMetas', 'maps');

        return $data;
    }

    private function _importDiyReport($data) {
        $objCmdb3Table = new TableHelper('Cmdb3Table', $this->_dbKey);
        $table = $data['table'];
        $where = array('tableId' => $table['tableId']);
        $oldTable = $objCmdb3Table->getRow($where);
        if ($oldTable) {
            Response::error(CODE_PARAM_ERROR, '已经存在相同的tableId');
        }

        $maps = $data['maps'];
        foreach ($maps as $map) {
            $objCmdb3Map = new TableHelper('Cmdb3Map', $this->_dbKey);
            $where = array('mapKey' => $map['mapKey']);
            unset($map['mapId']);
            $objCmdb3Map->addObjectIfNoExist($map, $where);
        }


        unset($table['createTime']);
        $table['lastModifyTime'] = date('Y-m-d H:i:s');
//        $table['tableName'] = "{$table['tableName']}【导入】";
//        $table['tableCName'] = "{$table['tableCName']}【导入】";
        $objCmdb3Table->addObject($table);

        $objCmdb3Field = new TableHelper('Cmdb3Field', $this->_dbKey);
        $objCmdb3Field->addObjects2($data['fields']);

        $objCmdb3Edit = new TableHelper('Cmdb3Edit', $this->_dbKey);
        $objCmdb3Edit->addObjects2($data['editFields']);

        $objCmdb3TableMeta = new TableHelper('Cmdb3TableMeta', $this->_dbKey);
        $objCmdb3TableMeta->addObjects2($data['tableMetas']);
    }

    public function actionExport($args) {
        $rules = array(
          'tableId' => array('string'),
        );
        Param::checkParam2($rules, $args);

        // 检查权限
        $this->_checkRight($args['tableId']);

        $data = $this->_exportDiyReport($args['tableId']);

        $content = json_encode($data);
        $file_name = "Report_{$args['tableId']}.json";

        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:" . strlen($content));
        Header("Content-Disposition: attachment; filename=" . $file_name);

        Response::exitMsg($content);
    }

    public function actionImport() {
        $file = $_FILES['report_conf'];
        $json = file_get_contents($file['tmp_name']);
        $data = json_decode($json, true);
        if (!$data) {
            Response::error(CODE_PARAM_ERROR, '上传的文件有问题,' . $file['tmp_name']);
        }

        $this->_importDiyReport($data);

        Response::exitMsg('导入成功，请刷新列表');
    }

    public function actionSync($args) {
        $rules = array(
          'tableId' => array('string'),
        );
        Param::checkParam2($rules, $args);

        // 检查权限
        $this->_checkRight($args['tableId']);
        if (ENV === ENV_DEV) {
            $host = 'admin.ouj.com';
            $ip = '61.160.36.226';
        } else {
            $host = 'test.admin.ouj.com';
            $ip = '61.160.36.225';
        }

        $data = $this->_exportDiyReport($args['tableId']);
        $data['sign'] = ThirdApi::getSign($data);
        $data = json_encode($data);

        $objHttp = new dwHttp();
        $url = "http://{$ip}/diyConfig/reviceSync";
        $msg = $objHttp->post($url, $data, 15, "Host:{$host}");

        Response::exitMsg($msg);
    }

    public function actionReviceSync() {
        $data = file_get_contents("php://input");
        $data = json_decode($data, true);

        $sign = arrayPop($data, 'sign');
        $sign2 = ThirdApi::getSign($data);
        if (!$sign || $sign != $sign2) {
            Response::error(CODE_SIGN_ERROR, null);
        }

        $this->_importDiyReport($data);

        Response::success();
    }
}

