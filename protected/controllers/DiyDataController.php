<?php

/**
 * Diy数据
 * @author benzhan
 */
class DiyDataController extends BaseController {
    
    function actionReport($args) {
        $this->_checkParam($args);
        
        $objTable = new Diy_Table();
        if (!$objTable->isAdmin($args['tableId'])) {
            Permission::checkUrlRight($_SERVER['REQUEST_URI']);
        }

        $tableInfo = $objTable->getTableInfo($args['tableId']);
        $this->tpl->assign(compact('tableInfo'));
        $this->tpl->display('diy/report');
    }

    function _mergeEditField($tableId, &$other) {
        $objEdit = new Diy_Edit();
        $editFields = $objEdit->getFields($tableId);
        $editFields = $objEdit->processFieldView($editFields, $other['fields'], 'showInEdit');

        foreach ($other['fields'] as $fieldName => $field) {
            if (!$editFields[$fieldName]) {
                continue;
            }

            $field['td_attr'] = '';
            $field = $editFields[$fieldName] + $field;
            if ($field['isHidden']) {
                $field['td_attr'] .= ' style="display:none;" ';
            } else if ($field['easyEdit']) {
                $field['td_attr'] .= ' easyEdit="1" style="position:relative;" ';
            }

            $other['fields'][$fieldName] = $field;
        }
    }
    
    /**
     * 表格
     * @author benzhan
     */
    function actionTable($args) {
        @ini_set('memory_limit', '1024M');
        $tableId = $args['tableId'];

        $this->_checkParam($args);
        // 操作日志的报表，不需要检查权限
        Permission::checkTableRight($tableId);

        // 检查权限
        $oConfig = new Diy_Table();
        $tableInfo = $oConfig->getTableInfo($tableId);
        
        $objField = new Diy_Field();
        $fields = $objField->getFields($tableId);

        $page = $args['keyWord']['_page'];
        $pageSize = $args['keyWord']['_pageSize'] ?: $tableInfo['pagination'];
        $result = $this->_getPageData($args, $page, $pageSize);
        if ($_GET['_data']) {
            return $result;
        }

        $datas = $result['datas'];
        $other = $result['other'];
        $other['tableInfo'] = $tableInfo;
        $other['fields'] = $this->_getFormatFields($fields, $args['keyWord']);
        $other['showGroupBy'] = $args['keyWord']['_showGroupBy'];
        $other['hideNoGroupBy'] = $args['keyWord']['_hideNoGroupBy'];

        if ($tableInfo['editFlag']) {
            $this->_mergeEditField($tableId, $other);
            $priKeys = [];
            foreach ($fields as $field) {
                if ($field['isPrimaryKey']) {
                    $priKeys[] = $field['fieldName'];
                }
            }

            $tData = array();
            foreach ($datas as $i => $data) {
                foreach ($priKeys as $priKey) {
                    $tData[$i][$priKey] = $data['_' . $priKey] ?: $data[$priKey];
                }
            }
            $other['priKeys'] = $tData;
        }

        $this->assignTableArgs($datas, $other);
        $showDel = $other['tableInfo']['editFlag'] && !$other['tableInfo']['hideEditFlag'] && !$other['tableInfo']['safeEditFlag'];

        $template = Template::init();
        $template->assign('showDel', $showDel);
        $template->assign('tableInfo', $tableInfo);
        $template->display('diy/table');
    }

    function actionStatic($args) {
        @ini_set('memory_limit', '1024M');
        $tableId = $args['tableId'];

        $this->_checkParam($args);
        // 操作日志的报表，不需要检查权限
        Permission::checkTableRight($tableId);

        $objField = new Diy_Field();
        $fields = $objField->getFields($tableId);

        unset($args['keyWord']['_groupby']);
        unset($args['keyWord']['_save']);

        $oData = new Diy_Data();
        $datas = $oData->getTableData($args);

        $other = [];
        $other['fields'] = $this->_getFormatFields($fields, $args['keyWord']);
        $fieldNames = $this->_getSortKey($datas, $other);
        $data = $datas[0];

        if ($other['fields']['fieldDisplay'] & 4) {
            $other['fields']['isHidden'] = true;
        }

        $this->tpl->assign(compact('data', 'other', 'fieldNames'));
        $this->tpl->display('diy/static');
    }

    /**
     * 表格
     * @author benzhan
     */
    function actionPager($args) {
        @ini_set('memory_limit', '1024M');

        $this->_checkParam($args);

        if (!isset($args['rowNum'])) {
            $oData = new Diy_Data();
            $args['rowNum'] = $oData->getTableDataNum($args);
        }

        $oConfig = new Diy_Table();
        $tableInfo = $oConfig->getTableInfo($args['tableId']);

        $args['pageSize'] = $args['keyWord']['_pageSize'] ?: $tableInfo['pagination'];
        $args['page'] = $args['keyWord']['_page'] ?:1;
        $totalPage = ceil($args['rowNum'] / $args['pageSize']);
        if ($args['page'] > $totalPage) {
            $args['page'] = 1;
        }

        $pagerHtml = Tool::getPageHtml($args);
        Response::exitMsg($pagerHtml);
    }
    
    public function actionExportCSV($args) {
        $this->_checkParam($args);

        @ini_set('memory_limit', '1524M');

        $tableId = $args['tableId'];
        $oConfig = new Diy_Table();
        $tableInfo = $oConfig->getTableInfo($tableId);
        
        $objField = new Diy_Field();
        $fields = $objField->getFields($tableId);

        $oData = new Diy_Data();
        $datas = $oData->getTableData($args);

        $newFileds = [];
        foreach ($fields as $fieldName => $field) {
            // 只有 纬度和指标 并且是 非隐藏字段 才需要导出
            if (($field['fieldDisplay'] & 1) || ($field['fieldDisplay'] & 2) && !($field['fieldDisplay'] & 4)) {
                $newFileds[] = $fieldName;

                if ($field['fieldLength'] || $field['fieldVirtualValue'] || $field['mapKey'] || $field['enumMapKey']) {
                    $fields[$fieldName]['fieldType'] = 'string';
                }
            }
        }

        foreach ($datas as $i => $data) {
            $datas[$i] = arrayFilter($data, $newFileds);
        }

        $this->_makeExcel($tableInfo['tableCName'], $newFileds, $fields, $datas);
    }

    private function _makeCsv($name, $newFileds, $fields, $datas) {
        $headers = array();
        foreach ($newFileds as $fieldName) {
            $field = $fields[$fieldName];
            $headers[$field['fieldName']] = str_replace('"', '""', strip_tags($field['fieldCName']));
        }

        $body = array();
        $body[] = '"' . join('","', $headers) . '"';
        if ($datas) {
            foreach ($datas as $data) {
                $tData = array();
                foreach ($headers as $fieldName => $fieldCName) {
//                    $tData[] = str_replace('"', '""', strip_tags($data[$fieldName]));
                    $tData[] = str_replace('"', '""', $data[$fieldName]);
                }
                $body[] = '"' . join('","', $tData) . '"';
            }
        }

        $ret = join("\r\n", $body);
        header("Content-type:text/csv;charset=utf8");
        $filename = strip_tags($name) . ".csv"; // 文件名
        header("Content-Disposition:attachment;filename=" . $filename);
        header("Content-Encoding: utf-8");

        echo pack('H*','EFBBBF');
        exit($ret);
    }

    private function _makeExcel($name, $newFileds, $fields, $datas) {
        $header = array();
        foreach ($newFileds as $fieldName) {
            $field = $fields[$fieldName];
            $fieldCName = str_replace('"', '""', strip_tags($field['fieldCName']));
            $fieldType = $field['fieldType'];
            $type = 'string';
            switch ($fieldType) {
                case 'int':
                case 'float':
                    $type = 'integer';
                    break;
                    // XLSXReader不识别以下格式，囧
//                case 'date':
//                    $type = 'date';
//                    break;
//                case 'datetime':
//                    $type = 'datetime';
//                    break;
//                case 'time':
//                    $type = 'time';
//                    break;
            }
            $header[$fieldCName] = $type;
        }

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Sheet1', $header);
        foreach($datas as $data) {
            $writer->writeSheetRow('Sheet1', $data);
        }

        $filename = strip_tags($name) . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
// If you're serving to IE 9, then the following may be needed
// If you're serving to IE over SSL, then the following may be needed
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified

        $writer->writeToStdOut();
        exit;
    }

    public function actionExportTemplate($args) {
        $this->_checkParam($args);

        @ini_set('memory_limit', '1524M');

        $tableId = $args['tableId'];
        $oConfig = new Diy_Table();
        $tableInfo = $oConfig->getTableInfo($tableId);

        $tableInfo['templateField'] && $args['_field'] = $tableInfo['templateField'];
        $args['_rawData'] = true;
        $oData = new Diy_Data();
        $args['keyWord']['_limit'] = '0, 100000';
        $datas = $oData->getTableData($args);

        $objField = new Diy_Field();
        $fields = $objField->getFields($tableId);

        $templateFileds = explode(',', $tableInfo['templateField']);
        $newFileds = [];
        foreach ($templateFileds as $i => $templateFiled) {
            $v = trim($templateFiled);
            $v && $newFileds[] = $v;
        }

//        $this->_makeCsv($tableInfo['tableCName'], $newFileds, $fields, $datas);
        $this->_makeExcel($tableInfo['tableCName'], $newFileds, $fields, $datas);
    }
    
    public function _checkParam(&$args) {
        isset($args['where']) && $args['where'] = json_decode($args['where'], true);
        isset($args['keyWord']) && $args['keyWord'] = json_decode($args['keyWord'], true);
        
        $rules = array(
            'tableId' => 'string',
            'where' => array('array', 'nullable' => true),
            'keyWord' => array('object',
                'nullable' => true,
                'elem' => array(
                    '_page' => array('int', 'nullable' => true),
                    '_pageSize' => array('int', 'nullable' => true),
                    '_sortKey' =>  array('string', 'nullable' => true),
                    '_sortDir' =>  array('string', 'enum' => array('ASC', 'DESC'), 'nullable' => true),
                ),
            ),
        );
        Param::checkParam($rules, $args);
    }
    
    private function _getPageData($args, $page, $pageSize) {
        //特殊处理page为-1的参数
        if ($page == -1) {
            $page = 1;
            $pageSize = 1000000;
        } else if (!$page) {
            $page = 1;
        }
        $args['keyWord']['_limit'] = ($page - 1) * $pageSize . "," . $pageSize;
         
        $oData = new Diy_Data();
        $datas = $oData->getTableData($args);
        if (!$datas) {
            $args['keyWord']['_limit'] = $pageSize;
            $page = 1;
            $datas = $oData->getTableData($args);
        }
//        $rowNum = $oData->getTableDataNum($args);

        //如果没那么多页，则需要重新查询, 查最后一页数据返回
//        if ($rowNum < (int) $args['keyWord']['_limit'] ) {
//            $page = 1;
//            $args['keyWord']['_limit'] = $page ? ($page - 1) * $pageSize . "," . $pageSize : $pageSize;
//            $datas = $oData->getTableData($args);
//        }

        $pageStatic = $oData->getDataHive()->pageStatic;

        $keyWord = $args['keyWord'];
        $needProcess = $oData->getDataHive()->getNeedProcess();
        $other = compact('rowNum', 'page', 'pageSize', 'keyWord', 'needProcess', 'pageStatic');
        return compact('datas', 'other');
    }

    /**
     * 初始化列的groupby和cal属性
     * @param array $fields
     * @param array $args
     * @author benzhan
     */
    private function _getFormatFields($fields, $args) {
        $keyWords = array('_min', '_max', '_avg', '_sum', '_count', '_distinct', '_distinctCount', '_groupby', '_save');
    
        foreach ($keyWords as $keyWord) {
            if (!$args[$keyWord]) { continue; }
            $fieldNames = explode(',', $args[$keyWord]);
            foreach ($fieldNames as $fieldName) {
                if ($keyWord == '_groupby') {
                    $fields[$fieldName]['groupby'] = '_groupby';
                } else {
                    $fields[$fieldName]['cal'] = $keyWord;
                }
            }
        }

        foreach ($fields as $i => $field) {
            if ($field['fieldDisplay'] & 2) {
                $field['th_attr'] = ' data-role="groupby" ';
            } else if ($field['fieldDisplay'] & 1) {
                $field['th_attr'] = ' data-role="value" ';
            } else {
                $field['th_attr'] = '';
            }

            $fields[$i] = $field;
        }

        return $fields;
    }

    protected function assignTableArgs($datas, $other) {
        global $startTime;
        $fieldNames = $this->_getSortKey($datas, $other);
    
        $mergeFieldData = $this->_mergeCol($datas, $other['fields']);
        $other['fields'] = $this->_getRowIcon($other['fields'], $other['showGroupBy']);

//        $pagerHtml = Tool::getPageHtml($other);
        $other['timeSpan'] = microtime(true) - $startTime;
        $timeSpan = round($other['timeSpan'], 3);
        $memory = round(memory_get_peak_usage() / 1024 /1024, 3);
        $msg = "耗时：{$timeSpan}秒, 最高内存占用：{$memory}MB";
        $msg .= $other['needProcess'] ? "（php统计）" : "（Sql统计）";
        $pagerHtml = "<script>$('#msgDiv').text('{$msg}');</script>";
        // var_dump(compact('datas', 'fieldNames', 'other', 'mergeFieldData', 'pagerHtml'));exit;
        
        $template = Template::init();
        $template->assign(compact('datas', 'fieldNames', 'other', 'mergeFieldData', 'pagerHtml'));
    }
    
    private function _getSortKey($datas, $other) {
        if (!$datas) { return array(); }
    
        $data = current($datas);
        $fieldNames = array_keys($data);
    
        $map = array();
        foreach ($fieldNames as $fieldName) {
            $field = $other['fields'][$fieldName];
            $map[$fieldName] = $field['fieldSortName'];
        }
    
        return $map;
    }
    
    /**
     * 适当排序，然后再合并相同的行
     * @param array $datas 二维数组
     * @param array $fields
     * @author benzhan
     */
    private function  _mergeCol(&$datas, $fields) {
        $objData = new Diy_Data();
        $mergeFieldNames = $objData->getMergeField($fields);
        if (!$mergeFieldNames) { return; }
    
        //$this->_sortData($datas, $mergeFieldNames);
        $mergeFieldData = $count = array();
        $lastFieldName = '';
    
        foreach ($datas as $i => $data) {
            foreach ($data as $fieldName => $value) {
                if (!$mergeFieldNames[$fieldName]) { continue; }
                if ($lastFieldName) {
                    //判断前一列是否合并了
                    $lastFieldIsMerge = $mergeFieldData[$lastFieldName][$i] == 0;
                } else {
                    $lastFieldIsMerge = false;
                }
    
                //对需要合并的列则判断是否跟上一列相同，并且前一列是合并的
                if ($value == $datas[$i - 1][$fieldName] && $lastFieldIsMerge) {
                    $count[$fieldName]++;
                    $mergeFieldData[$fieldName][$i] = 0;
                } else {
                    if ($count[$fieldName] > 1) {
                        $mergeFieldData[$fieldName][$i - $count[$fieldName]] = $count[$fieldName];
                    }
                    $mergeFieldData[$fieldName][$i] = $count[$fieldName] = 1;
                }
    
                $lastFieldName = $fieldName;
            }
        }
    
        $i++;
        foreach ($mergeFieldNames as $fieldName) {
            if ($count[$fieldName] > 1) {
                $mergeFieldData[$fieldName][$i - $count[$fieldName]] = $count[$fieldName];
            } else {
                $count[$fieldName] = 1;
            }
        }
    
        return $mergeFieldData;
    }
    

    /**
     * 获取分组或计算的图标
     * @param unknown_type $fields
     * @author benzhan
     */
    private function _getRowIcon($fields, $showGroupBy) {
        $style = ($showGroupBy ? '' : "style='display:none;'");
        foreach ($fields as $k => $field) {
            if ($field['fieldDisplay'] & 1) {
                // 这个是指标
                $field['icon'] = "<a class='cal icon {$field['cal']}' {$style} title=\"
                <select>
                <option value=''>原始</option>
                <option value='_save'>保留</option>
                <option value='_max'>最大</option>
                <option value='_min'>最小</option>
                <option value='_avg'>平均</option>
                <option value='_sum'>总和</option>
                <option value='_count'>计数</option>
                <option value='_distinctCount'>去重计数</option>
                <option value='_distinct'>去重</option>
                </select>
                \"></a>";
            } else if ($field['fieldDisplay'] & 2) {
                // 这个是纬度
                $className = $field['groupby'] ? '_groupby' : '_noGroupby';
                $field['icon'] = "<a {$style} class='{$className} icon'></a>";
            }

            if ($field['fieldDisplay'] & 4) {
                $field['isHidden'] = true;
            }
            
            $fields[$k] = $field;
        }
        
        return $fields;
    }

}
