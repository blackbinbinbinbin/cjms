<?php

class Diy_DataHive {
    private $_rowsNum = -1;
    private $_limit;
    private $_groupby;
    private $_mergeGroupby;
    private $_max;
    private $_min;
    private $_count;
    private $_distinctCount;
    private $_distinct;
    private $_sum;
    private $_avg;
    private $_save;
    public $pageStatic;

    private $_needProcess;

    private function _getCustomGroupby($tableInfo, $fields, &$keyWord) {
        $groupby = $keyWord['_groupby'];
        $this->_groupby = [];
        $this->_mergeGroupby = [];

        if ($groupby) {
            $parts = explode(',', $groupby);
            $oldGroupby = [];
            foreach ($parts as $part) {
                $v = trim(trim($part), '`');
                $field = $fields[$v];

                if ($field['needMerge'] & 1) {
                    // groupby的列是合并列，则不需要当作groupby
                    $this->_mergeGroupby[] = $v;
                } else if ($field['fieldVirtualValue'] && $field['callBack']) {
                    $this->_groupby[] = $v;
                } else {
                    $oldGroupby[] = $v;
                }
            }

            $this->_needProcess = $this->_groupby || $this->_mergeGroupby;
            $this->_groupby = array_merge($this->_groupby, $oldGroupby);
        }

        $this->_limit = $keyWord['_limit'];
        // 构造指标map
        $valueNames = ['_max', '_min', '_sum', '_avg', '_count', '_distinctCount', '_save'];
        foreach ($valueNames as $valueName) {
            $this->$valueName = [];
            $values = $keyWord[$valueName];
            if ($values) {
                $parts = explode(',', $values);
                $tData = [];
                foreach ($parts as $fieldName) {
                    $field = $fields[$fieldName];
//                    if ($field['fieldVirtualValue'] && $field['callBack'] && $valueName != '_save') {
                    if ($field['callBack'] && $valueName != '_save') {
                        $this->_needProcess = true;
                    }

                    $fieldName = trim($fieldName);
                    $tData[] = $fieldName;
                }
                $this->$valueName = $tData;
            }
        }

        if ($tableInfo['staticMode'] == 2) {
            // 判断是不是php统计方式
            $this->_needProcess = true;
        } else if ($tableInfo['staticMode'] == 1) {
            // 判断是不是sql统计方式
            $this->_needProcess = false;
        }

        // 自己统计的情况，需要去掉限制
        if ($this->_needProcess) {
            // 去掉groupby
            unset($keyWord['_groupby']);
            // 去掉以前的分组计算
            //$keyWord['_field'] = preg_replace('/(COUNT|MAX|MIN|SUM|AVG)\([^\)]+\) AS /', ' ', $keyWord['_field']);
            $keyWord['_field'] = preg_replace('/(COUNT|MAX|MIN|SUM|AVG)\([\(]*([^\)]+)[\)]*\) AS /', '($2) AS ', $keyWord['_field']);
            $keyWord['_field'] = preg_replace('/[\(]*(DISTINCT) [\(]*([^\)]+)[\)]* AS /', '($2) AS ', $keyWord['_field']);

            $this->_limit = arrayPop($keyWord, '_limit');
        }

    }

    public function getTableData(TableHelper $oBase, $fields, $keyWord, $args) {
        $objTable = new Diy_Table();
        $tableInfo = $objTable->getTableInfo($args['tableId']);

        $this->_getCustomGroupby($tableInfo, $fields, $keyWord);

        $objField = new Diy_Field();
        $allFields = $objField->getFields($args['tableId']);

//        $tableName = $oBase->getTableName2();
        $tableName = $oBase->getTableName();
        // var_dump($oBase);exit;
        if (strpos($tableName, ' ') === false) {
            if (!$oBase->checkTableExist($tableName)) {
                if ($keyWord['_debug'] == 1) {
                    var_dump("$tableName is not exist.");
                    exit;
                }
                return [];
            }
        }

        // $keyWord['_debug'] = 1;
        $datas = $oBase->getAll($keyWord);
//        var_dump($datas);exit;

        // sql统计方式，统计当前页面
        if ($tableInfo['pageStaticFlag'] && !$this->_needProcess) {
            $this->_staticPageData($datas);
        }

        if (!$args['_rawData']) {
            $datas = $this->_formatDatas($datas, $allFields, $args['fields']);
        }

        if ($this->_needProcess) {
            $datas = $this->_processData($datas, $keyWord);
            // php处理方式需要处理完，再统计
            if ($tableInfo['pageStaticFlag']) {
                $this->_staticPageData($datas);
            }
        } else {
//            $this->_rowsNum = $oBase->getFoundRows();
        }

        return $datas;
    }

    public function getRowNum() {
        return $this->_rowsNum;
    }

    /**
     * php统计方式
     * @param $datas
     * @param $keyWord
     *
     * @return array
     */
    private function _processData($datas, $keyWord) {
        $tDatas = [];

        foreach ($datas as $data) {
            $key = '';
            foreach ($this->_groupby as $fieldName) {
                $key .= '#[' . $data[$fieldName] . ']$';
            }

            $oldData = $tDatas[$key];
            if ($oldData) {
                foreach ($this->_max as $fieldName) {
                    $oldData[$fieldName] = max($oldData[$fieldName], $data[$fieldName]);
                }

                foreach ($this->_min as $fieldName) {
                    $oldData[$fieldName] = min($oldData[$fieldName], $data[$fieldName]);
                }

                foreach ($this->_count as $fieldName) {
                    $oldData[$fieldName]++;
                }

                foreach ($this->_distinctCount as $fieldName) {
                    if (isset($data[$fieldName]) && $data[$fieldName] != '') {
                        $oldData[$fieldName][$data[$fieldName]] = 1;
                    }
                }

                foreach ($this->_sum as $fieldName) {
                    $oldData[$fieldName] += $data[$fieldName];
                }

                foreach ($this->_avg as $fieldName) {
                    $oldData[$fieldName] += $data[$fieldName];
                }

            } else {
                $oldData = $data;
                foreach ($this->_count as $fieldName) {
                    // 从1开始计数
                    $oldData[$fieldName] = 1;
                }

                foreach ($this->_distinctCount as $fieldName) {
                    $oldData[$fieldName] = [];
                    if (isset($data[$fieldName]) && $data[$fieldName] != '') {
                        $oldData[$fieldName][$data[$fieldName]] = 1;
                    }
                }
            }

            $tDatas[$key] = $oldData;
        }

        foreach ($this->_distinctCount as $fieldName) {
            foreach ($tDatas as $i => $tData) {
                $tDatas[$i][$fieldName] = count($tData[$fieldName]);
            }
        }

        if ($keyWord['_sortKey']) {
            $sortDatas = [];
            foreach ($tDatas as $key => $data) {
                $sortDatas[] = $data[$keyWord['_sortKey']];
            }

            if (strtolower($keyWord['_sortDir']) == 'desc') {
                $flag = SORT_DESC;
            } else {
                $flag = SORT_ASC;
            }

            array_multisort($sortDatas, $flag, $tDatas);
        }


        $this->_rowsNum = count($tDatas);
        $datas = array_values($tDatas);

        if ($this->_limit) {
            $parts = explode(',', $this->_limit);
            $offset = trim($parts[0]);
            $length = trim($parts[1]);

            $datas = array_slice($datas, $offset, $length);
        }

        return $datas;
    }

    private function _staticPageData($datas) {
        $oldData = [];
        foreach ($datas as $data) {
            foreach ($this->_max as $fieldName) {
                $oldData[$fieldName] = max($oldData[$fieldName], $data[$fieldName]);
            }

            foreach ($this->_min as $fieldName) {
                $oldData[$fieldName] = min($oldData[$fieldName], $data[$fieldName]);
            }

            $sumFieldNames = array_merge($this->_count, $this->_distinctCount, $this->_sum, $this->_avg);
            foreach ($sumFieldNames as $fieldName) {
                $oldData[$fieldName] += $data[$fieldName];
            }
        }

        foreach ($this->_avg as $fieldName) {
            $oldData[$fieldName] = $oldData[$fieldName] / count($datas);
        }

        $this->pageStatic = $oldData;
    }

    /**
     * 格式化数据，翻译字段、执行回调
     * @param array $datas 二维数据
     * @param array $fields 列信息
     * @author benzhan
     */
    private function _formatDatas($datas, $fields, $selectFields = array()) {
        if (!$datas) {
            return $datas;
        }

        $map = $this->_getMap($datas, $fields);
        $funcs = array ();
        foreach ( $datas as $rowIndex => $row ) {
            $tempRow2 = [];
            foreach ($row as $fieldName => $value ) {
                $field = $fields[$fieldName];
                $_fieldName = '_' . $fieldName;
                // 过滤不显示的列
                if (!$field['fieldDisplay']) { continue; }

                //字典表
                if ($field['fieldMap'] || $field['mapKey']) {
                    $arr = $map[$fieldName][$row[$fieldName]];
                    $v = $arr ?  $arr : $row[$fieldName];
                    isset($row[$_fieldName]) || $row[$_fieldName] = $row[$fieldName];
                    $value = $v;
                } else if ($field['fieldLength']) {
                    $v = $field['fieldLength'][$row[$fieldName]];
                    isset($v) || $v = $row[$fieldName];
                    isset($row[$_fieldName]) || $row[$_fieldName] = $row[$fieldName];
                    $value = $v;
                }

                $fieldLength = $field['fieldLength'] ? $field['fieldLength'] : $field['enumMap'];
                if ($field['needMap2'] && $fieldLength) {
                    isset($row[$_fieldName]) || $row[$_fieldName] = $value;
                    if (is_array($value)) {
                        foreach ($value as $i => $v) {
                            $value[$i] = $fieldLength[$v];
                        }
                    } else {
                        $value = $fieldLength[$row[$fieldName]];
                    }
                }

                if (is_array($value)) {
                    $value = join(',', $value);
                }
                $row[$fieldName] = $value;

                //字段回调
                if ($field['callBack']) {
                    isset($row[$_fieldName]) || $row[$_fieldName] = $row[$fieldName];
                    if (!isset($funcs[$fieldName])) {
                        $funcs[$fieldName] = create_function('&$_row, $_field, $_rowIndex, $rowIndex, $_val', $field['callBack']);
                    }

                    $value = $funcs[$fieldName]($row, $field, $rowIndex, $rowIndex, $value);
//                    if ($row['__' . $fieldName] === false) {
//                        $tempRow2['__' . $fieldName] = $row['__' . $fieldName];
//                    }
                }

                if ($selectFields && !in_array($fieldName, $selectFields)) { continue; }
                isset($row['_' . $fieldName]) && $tempRow2['_' . $fieldName] = $row['_' . $fieldName];
                $tempRow2[$fieldName] = $value;

            }

            $datas[$rowIndex] = $tempRow2;
        }

        return $datas;
    }


    private function _getMap($datas, $fields) {
        if (!$datas) {
            return $datas;
        }

        $map = array ();
        $fieldNames = array_keys(current($datas));
        foreach ( $fieldNames as $fieldName ) {
            $field = $fields[$fieldName];

            $mapKey = $field['mapKey'];
            if ($mapKey) {
                $map[$fieldName] = Diy_MapData::getData($mapKey);
            } else {
                $fieldMap = json_decode($field['fieldMap'], true);
                if (!$fieldMap) { continue; }

                foreach ($datas as $rowNum => $row ) {
                    $val = $row[$fieldName];
                    //字典表
                    $map[$fieldName][$val] = $val;
                }

                $funcName = $fieldMap['name'];
                //$key = $field['fieldVirtualValue'] ? $field['fieldVirtualValue'] : $fieldName;
                $args = $fieldMap+ array('where' => $map[$fieldName]);
                $map[$fieldName] = Diy_Map::$funcName($args);
            }
        }

        return $map;
    }

    public function getKeys() {
        $keyNames = ['_max', '_min', '_count', '_distinctCount', '_save', '_sum', '_avg', '_groupby'];
        $keys = [];
        foreach ($keyNames as $keyName) {
            $keys[$keyName] = $this->$keyName;
        }

        return $keys;
    }

    public function getNeedProcess() {
        return $this->_needProcess;
    }

}



