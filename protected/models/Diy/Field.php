<?php

class Diy_Field extends Model {
    protected $tableName = 'Cmdb3Field';
    protected $dbKey = "Report";

    private static $fieldsCache;

	/**
	 * 获取格式化字段长度后的列信息，key => value格式，其中fieldName为key
	 * @param string $tableId 表Id
	 * @param bool $onlyDisplay 只过滤展示列
	 * @author benzhan
	 */
	public function getFields($tableId, $onlyDisplay = false) {
        if (self::$fieldsCache[$tableId . $onlyDisplay]) {
            return self::$fieldsCache[$tableId . $onlyDisplay];
        }

	    //这里一定要加memcache
//	    $where = compact('tableId');
//	    $objTable = new Diy_Table();
//	    $tableInfo = $objTable->getTableInfo($tableId);

	    $fields = $this->getFields2($tableId, $onlyDisplay);
	    
	    foreach ($fields as $fieldName => $field) {
	        $fields[$fieldName]['fieldLength'] = self::getFieldLength($field['fieldLength']);
			$fields[$fieldName]['enumMap'] = self::getEnumMap($field['enumMapKey']);
			$fields[$fieldName]['fieldVirtualValue'] = self::getFieldVirtualValue($field['fieldVirtualValue']);
			$fields[$fieldName]['isHidden'] = ($field['fieldDisplay'] & 4) > 0;
	    }

        self::$fieldsCache[$tableId . $onlyDisplay] = $fields;
	    return $fields;
	}
	
	/**
	 * 获取为未格式化的列信息
	 * @param string $tableId
	 * @author benzhan
	 */
	public function getFields2($tableId, $onlyDisplay = false) {
	    //这里一定要加memcache
	    $where = compact('tableId');
	    
	    $other = array('_sortKey' => 'fieldPosition', '_sortDir' => 'ASC');
	    $onlyDisplay && $other['_where'] = "fieldDisplay > 0";
	    
	    $oBaseField = new TableHelper('Cmdb3Field', $this->dbKey);
	    $fields = $oBaseField->getAll($where, $other);

	    return arrayFormatKey($fields, 'fieldName');
	}
	
	/**
	 * 设置默认的列
	 * @param array $args array('tableId', 'fields'...)
	 * @author benzhan
	 */
	public function setDefaultFields($args) {
	    $rules = array(
            'tableId' => 'string',
            'fields' => 'strArr'
	    );
	    Param::checkParam($rules, $args);
	    
	    $where = array();
	    $where['tableId'] = $args['tableId'];
	    
	    $oBaseField = new TableHelper('Cmdb3Field', $this->dbKey);
	    
	    $oBaseField->autoCommit(false);
	    $oBaseField->updateObject(array('defaultDisplay' => 0), $where);
	    $where['fieldName'] = $args['fields'];
	    $oBaseField->updateObject(array('defaultDisplay' => 1), $where);
	    $oBaseField->tryCommit();
	    
	    return true;
	}

	public static function getEnumMap($enumMapKey) {
		if (!$enumMapKey) { return null; }

		return Diy_MapData::getData($enumMapKey, [], true);
	}

	public static function getFieldLength($fieldLength) {
		if (!$fieldLength) { return $fieldLength; }

		try {
			$selectRange = json_decode($fieldLength, true);
			if ($selectRange) { return $selectRange; }

            $fieldLength = ltrim($fieldLength, ':');
			$func = create_function('', $fieldLength);
			return $func();
		} catch (Exception $ex) {
			Tool::err($ex);
			return array();
		}
	}

	public static function getFieldVirtualValue($fieldVirtualValue) {
		if (strpos($fieldVirtualValue, '$') !== false && strpos($fieldVirtualValue, 'return ') !== false) {
			$func = create_function('', $fieldVirtualValue);
			return $func();
		} else {
			return $fieldVirtualValue;
		}
	}

	public static function getFieldType($field, $fieldName, &$newField) {
		$fieldType = $field['Type'];
		preg_match('/(\w+)\((.+)\)/', $fieldType, $matches);
		if ($matches) {
			$fieldType = $matches[1];
			$length = (int) $matches[2];
		} else {
			$length = 0;
		}

		// 处理类型
		$fieldType = $GLOBALS['diy']['fieldTypeMap'][$fieldType];
		$inputType = 'text';
		if ($fieldType == 'string') {
			if ($length > 200) {
				$fieldType = 'text';
				$inputType = 'textarea';
			}

			$lowerFieldName = strtolower($fieldName);
			if (strpos($lowerFieldName, 'bg_') === 0
					|| strpos($lowerFieldName, '_bg') !== false
					|| strpos($lowerFieldName, '_img') !== false
					|| strpos($lowerFieldName, 'img_') !== false
					|| strpos($lowerFieldName, '_icon') !== false
					|| strpos($lowerFieldName, 'icon_') !== false
					|| strpos($lowerFieldName, '_image') !== false
					|| strpos($lowerFieldName, 'image_') !== false) {
			    $html = <<<HTML
if (\$_val) {
    return "<a target='_blank' href='{\$_val}'><image src='{\$_val}' style='width:120px;' /></a>";
} else {
    return '-';
}
HTML;

				$newField['callBack'] = $html;
				$inputType = 'image';
			}

		} else if ($fieldType == 'enum') {
			// 处理枚举类型
			$values = explode(',', $matches[2]);
			$fieldLength = array();
			foreach ($values as $value) {
				if ($value[0] == "'") {
					$value = substr($value, 1, -1);
				}

				$fieldLength[$value] = $value;
			}
			$newField['fieldLength'] = json_encode($fieldLength);
			$inputType = 'select';
		} else if ($fieldType == 'int') {
			$lowerFieldName = strtolower($fieldName);

			if (strpos($lowerFieldName, 'time') !== false) {
				$fieldType = 'datetime';
				$inputType = 'datetime';
				$newField['fieldVirtualValue'] = "FROM_UNIXTIME($fieldName,'%Y-%m-%d %H:%i:%s')";
			} else if (strpos($lowerFieldName, 'date') !== false || strpos($lowerFieldName, 'day') !== false) {
				$fieldType = 'date';
				$inputType = 'date';
				$newField['fieldVirtualValue'] = "FROM_UNIXTIME($fieldName,'%Y-%m-%d')";
			} else if (strpos($lowerFieldName, 'enable') === 0 || strpos($lowerFieldName, 'is_') === 0) {
				$newField['fieldLength'] = '{"1":"有效","0":"无效"}';
				$inputType = 'radio';
			} else {
				$inputType = 'number';
			}
		} else if ($fieldType == 'date' || $fieldType == 'datetime' || $fieldType == 'time') {
			$inputType = $fieldType;
		}

		if ($field['Key'] == 'PRI') {
			$newField['isPrimaryKey'] = 1;
		} else {
			$newField['isPrimaryKey'] = 0;
		}

		return compact('fieldType', 'inputType');
	}

	private function _processComment(&$newField, $Comment) {
	    $parts = explode('：', $Comment);
        $newField['fieldCName'] = array_shift($parts);
        if (count($parts) > 0) {
            $str = join(':', $parts);
            $parts = explode(',', $str);
            $data = array();
            foreach ($parts as $part) {
                $kv = explode(':', $part);
                $data[$kv[0]] = $kv[1];
            }
            $newField['fieldLength'] = json_encode($data, JSON_UNESCAPED_UNICODE);
            $newField['inputType'] = 'radio';
        }
    }
	
	public function processFields($newFields, $fields, $tableType = 1) {
	    $newFields = arrayFormatKey($newFields, 'Field');
	
	    foreach ($newFields as $field) {
	        $fieldName = $field['Field'];
	        if (!$fields[$fieldName]) {
	            $newField = array();

				$info = self::getFieldType($field, $fieldName, $newField);

				$fieldType = $newField['fieldType'] = $info['fieldType'];
	            $newField['inputType'] = $info['inputType'];

	            $newField['fieldName'] = $newField['fieldSortName'] = $fieldName;
				if ($tableType == 2) {
					$newField['fieldCName'] = $fieldName;
				} else {
//	            	$newField['fieldCName'] = $field['Comment'] ? $field['Comment'] : $fieldName;
                    $this->_processComment($newField, $field['Comment']);
				}
	            $newField['fieldPostion'] = count($fields);

	            // 设置默认的展现方式
	            if (preg_match('/\w+Id/', $fieldName) || ($fieldType != 'int' && $fieldType != 'float')) {
	                $newField['fieldDisplay'] = 2;
	            } else {
	                $newField['fieldDisplay'] = 1;
	            }

                $newField['showInCondition'] = 1;
	            $newField['defaultValue'] = $field['Default'];
	            if ($fieldName == 'create_time' || $fieldName == 'createTime') {
                    $newField['defaultValue'] = "::return NOW;";
                }

                if ($fieldName == 'creator') {
                    $newField['defaultValue'] = "::return User::getUserName();";
                }
	
	            $fields[$fieldName] = $newField;
	        }
	    }
	
	    return $fields;
	}
	
	public function processFields2($fields) {
	    foreach ($fields as $key => $field) {
	        // 设置默认显示高级选项
	        if ($field['fieldVirtualValue'] || $field['defaultValue'] || $field['fieldMap'] || $field['mapKey'] || $field['callBack'] || $field['fieldLength']) {
	            $field['showAdv'] = 1;
	        }

			$field['isPrimaryKey'] = (int) $field['isPrimaryKey'];
	        $fields[$key] = $field;
	    }
	
	    return $fields;
	}

    public static function formatFieldValue($val) {
	    try {
            if (preg_match('/^::/', $val)) {
                $callback = ltrim($val, ':');
                $callback = create_function('', $callback);
                if ($callback) {
                    return $callback();
                }
            }
        } catch (Exception $ex) {
	        Tool::err($ex->getMessage());
        }

        if (is_array($val)) {
            return current($val);
        }

        return $val;
    }
	
}
