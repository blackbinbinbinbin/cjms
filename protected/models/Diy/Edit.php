<?php
class Diy_Edit extends Model {
    protected $tableName = 'Cmdb3Edit';
    protected $dbKey = "Report";

    /**
     * 获取列信息
     * 
     * @param string $tableId            
     * @author benzhan
     */
    public function getFields($tableId) {
        $where = compact('tableId');
        
        $other = array(
            '_sortKey' => 'fieldPosition',
            '_sortDir' => 'ASC'
        );
        
        $fields = $this->objTable->getAll($where, $other);
        
        return arrayFormatKey($fields, 'fieldName');
    }
    
    /**
     * 获取主键
     * @param string $tableId
     * @return array
     */
    public function getPriKeys($tableId) {
        $isPrimaryKey = 1;
        $where = compact('tableId', 'isPrimaryKey');
        $other = array('_field' => 'fieldName');
        $cols = $this->objTable->getCol($where, $other);

        if (!$cols) {
            $objCmdb3Field = new TableHelper('Cmdb3Field', 'Report');
            $cols = $objCmdb3Field->getCol($where, $other);
        }

        return $cols;
    }

    public function processEditFields($newFields, $fields) {
        $newFields = arrayFormatKey($newFields, 'Field');
        
        foreach ($newFields as $field) {
            $fieldName = $field['Field'];
            if (!$fields[$fieldName]) {
                $newField = array();
                
                $typeInfo = Diy_Field::getFieldType($field, $fieldName, $newField);
                $newField['orginType'] = $typeInfo['fieldType'];
                $newField['inputType'] = $typeInfo['inputType'];
                $newField['fieldName'] = $fieldName;

                if ($field['Comment']) {
                    $parts = explode('：', $field['Comment']);
                    $newField['fieldCName'] = array_shift($parts);
                    if (count($parts) > 0) {
                        $newField['inputType'] = 'radio';
                    }
                } else {
                    $newField['fieldCName'] = $fieldName;
                }

                $newField['placeholder'] = $newField['fieldCName'];
                $newField['fieldPostion'] = count($fields);

                if ($fieldName == 'update_time' || $fieldName == 'updateTime') {
                    $newField['editDefaultValue'] = "::return NOW;";
                }

                $fileNameArr = [
                    'update_time',
                    'updateTime',
                    'create_time',
                    'createTime',
                    'creator',
                ];

                if ($field['Extra'] != 'auto_increment' && !in_array($fieldName, $fileNameArr)) {
                    $newField['showInAdd'] = 1;
                    $newField['showInEdit'] = 1;
                } else {
                    $newField['showInAdd'] = 0;
                    $newField['showInEdit'] = 0;
                }

                if ($field['Null'] == 'YES') {
                    $newField['required'] = 0;
                } else {
                    $newField['required'] = 1;
                }
                
                $newField['inputTip'] = '';
                
                $fields[$fieldName] = $newField;
            }
        }
        
        return $fields;
    }
    
    public function processEditFields2($fields) {
        foreach ($fields as $key => $field) {
            // 设置默认显示高级选项
            if ($field['inputTip'] || $field['postfixTip'] || $field['newlineTip'] || $field['fieldLength'] || $field['editDefaultValue']) {
                $field['showAdv'] = 1;
            }

            $field['labelColSpan'] || $field['labelColSpan'] = 3;
            $field['inputColSpan'] || $field['inputColSpan'] = 8;
            $field['postfixColSpan'] || $field['postfixColSpan'] = 0;
            $field['isPrimaryKey'] = (int) $field['isPrimaryKey'];
    
            $fields[$key] = $field;
        }
    
        return $fields;
    }

    public function processFieldView($fields, $searchFields, $filter = 'showInAdd') {
        $searchFields = arrayFormatKey($searchFields, 'fieldName');
        foreach ($fields as $i => $field) {
            if (!$field[$filter]) {
                unset($fields[$i]);
            } else {
                $field['attr'] = "name='{$field['fieldName']}' title='{$field['fieldCName']}' placeholder='{$field['placeholder']}' ";
                if ($field['required']) {
                    $field['attr'] .= 'required ';
                }
        
                if ($field['inputType'] == 'date' || $field['inputType'] == 'datetime' || $field['inputType'] == 'time') {
                    $field['attr'] .= "fieldType='{$field['inputType']}' ";
                }

//                $arr = array('select', 'checkbox', 'radio', 'auto_complete');
                $searchField = $searchFields[$field['fieldName']];
//                if (in_array($field['inputType'], $arr)) {
                    if ($searchField['enumMapKey']) {
                        $field['fieldLength'] = $valueMap = Diy_MapData::getData($searchField['enumMapKey']);
                        foreach ($field['fieldLength'] as $k => $v) {
                            $field['fieldLength'][$k] = current($v);
                        }
                    } else if ($searchField['fieldLength']) {
                        if (is_string($searchField['fieldLength'])) {
                            $field['fieldLength'] = Diy_Field::getFieldLength($searchField['fieldLength']);
                        } else {
                            $field['fieldLength'] = $searchField['fieldLength'];
                        }
                    } else if ($searchField['mapKey']) {
                        $field['fieldLength'] = $valueMap = Diy_MapData::getData($searchField['mapKey'], [], true);
                    } else if ($searchField['fieldMap']) {
                        $fieldMap = json_decode($searchField['fieldMap'], true);

                        $funcName = $fieldMap['name'];
                        $field['fieldLength'] = $valueMap = Diy_Map::$funcName($fieldMap);
                        foreach ($field['fieldLength'] as $k => $v) {
                            $field['fieldLength'][$k] = current($v);
                        }
//                        var_dump($field['fieldLength'] );exit;
                    }

                    if ($searchField['needMap2'] && $searchField['mapKey']) {
                        $field['map'] = $valueMap = Diy_MapData::getData($searchField['mapKey']);
                    }
//                }

                $field['defaultValue'] = Diy_Field::formatFieldValue($searchField['defaultValue']);
                $fields[$i] = $field;
            }
        }
        
        return $fields;
    }

    public function groupFieldView($fields) {
        $totalFields = [];
        $groupFields = [];
        $lastSpan = 12;
        foreach ($fields as $fieldName => $field) {
            $currentSpan = $field['labelColSpan'] + $field['inputColSpan'] + $field['postfixColSpan'];
            if ($lastSpan + $currentSpan > 12) {
                if (count($groupFields) > 0) {
                    $totalFields[$fieldName] = $groupFields;
                }

                $groupFields = [];
                $lastSpan = $currentSpan;
            } else {
                $lastSpan += $currentSpan;
            }

            $groupFields[$fieldName] = $field;
        }

        if (count($groupFields) > 0) {
            $totalFields[] = $groupFields;
        }

        return $totalFields;
    }

    public function getEditDefaultValue($editDefaultValue, &$args, $fieldName, $oldData = []) {
        if (!$editDefaultValue) { return $editDefaultValue; }

        try {
            if (strpos($editDefaultValue, '::') === 0) {
                $editDefaultValue = ltrim($editDefaultValue, ':');
                $func = create_function('&$_row, $_val, $_oldRow', $editDefaultValue);
                return $func($args, $args[$fieldName], $oldData);
            } else {
                return $editDefaultValue;
            }
        } catch (Exception $ex) {
            Tool::err($ex);
            return array();
        }
    }

}
