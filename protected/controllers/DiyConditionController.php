<?php

/**
 * Diy条件
 * @author benzhan
 */
class DiyConditionController extends BaseController {

    /**
     * 条件
     * @author benzhan
     */
    function actionIndex($args, $display = true) {
        isset($args['where']) && $args['where'] = json_decode($args['where'], true);
        
        $rules = array(
            'tableId' => 'string',
            'where' => array('array', 'nullable' => true),
        );
        Param::checkParam($rules, $args);
        require_once ROOT_PATH . 'conf/diyConfig.inc.php';

        //将当前的查询条件合并上默认的查询条件
        $oTable = new Diy_Table();
        if (!$args['notDefault']) {
            $defaultCondition = $oTable->getTableMeta(array('tableId' => $args['tableId'], 'metaKey' => 'tableDefaultCondition'));
            $defaultCondition = (array)  json_decode($defaultCondition['metaValue'], true);
            $defaultCondition = $this->_formatDefaultCondition($defaultCondition);
             
            $defaultCondition && $args['where'] = $this->_mergeDefaultCondition($args['where'], $defaultCondition);
        }

        $objField = new Diy_Field();
        $fields = $objField->getFields($args['tableId']);
        $args['fields'] = $this->_adaptFields($fields);

        $tableId = $args['tableId'];
        $tableInfo = $oTable->getTableInfo($tableId);
        if ($tableId) {
            $editLink = SITE_URL . "DiyConfig/edit?tableId={$tableId}&tableType={$tableInfo['tableType']}";
        }

        foreach ($args['where'] as $i => $value) {
            if (count($value) > 3 || $value[1] == ':') {
                $newValue = array_slice($value, 0, 2);
                $newValue[2] = array_slice($value, 2);
                $args['where'][$i] = $newValue;
            }
        }

        $args['isAdmin'] = $oTable->isAdmin($tableId);
        $this->tpl->assign($args);
        $this->tpl->assign('opts', $GLOBALS['diy']['opts']);
        $this->tpl->assign('editLink', $editLink);
        if ($display) {
            $this->tpl->display('diy/condition');
        } else {
            return $this->tpl->fetch('diy/condition');
        }
    }
    
    /**
     * 执行默认条件的回调
     * @param array $defaultCondition
     * @author benzhan
     */
    private function _formatDefaultCondition($defaultCondition) {
        foreach ($defaultCondition as $i => $data) {
            isset($data[2]) && $data[2] = $this->_formatCondition($data[2]);
            isset($data[3]) && $data[3] = $this->_formatCondition($data[3]);
    
            $defaultCondition[$i] = $data;
        }
        
        return $defaultCondition;
    }

    private function _formatCondition($val) {
        return Diy_Field::formatFieldValue($val);
    }
    
    private function _mergeDefaultCondition($where, $defaultCondition) {
        $map = array();
        foreach ($defaultCondition as $i => $value) {
            $map[$value[0]][$value[1]]  = $i;
        }

        if ($where) {
            foreach ($where as $value) {
                $key = $value[0];
                $opt = $value[1];

                $i = $map[$key][$opt];
                if (isset($i)) {
                    $defaultCondition[$i] = $value;
                } else {
                    $defaultCondition[] = $value;
                }
            }
        }

        return $defaultCondition;
    }
    

    /**
     * 适应当前的列配置
     * @param $args array(array(), array())
     * @author benzhan
     */
    private function _adaptFields($fields) {
        $tData = arrayFormatKey($fields, 'fieldName');
        foreach ($fields as $fieldName => $field) {
            if (!$field['showInCondition']) {
                unset($tData[$fieldName]);
                continue;
            }

            $keys = ['fieldType', 'fieldName', 'fieldCName', 'defaultDisplay',
                     'fieldDisplay', 'defaultValue', 'inputType', 'isPrimaryKey'];
            $tData[$fieldName] = arrayFilter($field, $keys);

            if ($field['inputType'] == 'select' || $field['inputType'] == 'radio' || $field['inputType'] == 'checkbox') {
                $enum = [];
                //判断enum类型是否存在字段长度
                if ($field['enumMapKey']) {
                    $enum = Diy_MapData::getData($field['enumMapKey']);
                } else if ($field['fieldLength']) {
                    $enum = $field['fieldLength'];
                } else if ($field['mapKey']) {
                    $enum = Diy_MapData::getData($field['mapKey']);
                }
//                else if ($field['fieldMap']) {
//                    $fieldMap = json_decode($field['fieldMap'], true);
//                    if ($fieldMap) {
//                        $funcName = $fieldMap['name'];
//                        $enum = Diy_Map::$funcName($fieldMap);
//                    }
//                }

                if ($enum) {
                    foreach ($enum as $k => $v) {
                        if (is_array($v)) {
                            $enum[$k] = join(",", $v);
                        } else {
                            $enum[$k] = $v;
                        }
                    }
                    $tData[$fieldName]['enum'] = $enum;
                }
            }

            if ($field['defaultValue']) {
                $tData[$fieldName]['defaultValue'] = $this->_formatCondition($field['defaultValue']);
            }

        }

        return $tData;
    }
}
