<?php

/**
 * 图表相关
 * @author benzhan
 */
class ChartController extends BaseController {

    private function _parseTitle($title) {
        preg_match_all('/\$(\w+)/', $title, $matches);
        return $matches[1];
    }

    private function _translateTitle($group, $title) {
        extract($group);
        $code = '$title = "' . $title .'";';
        eval($code);
        return $title;
    }

    /**
     * 图片列表
     * @param $args
     */
    function actionList($args) {
        $objData = new DiyDataController();
        $objData->_checkParam($args);
        Permission::checkTableRight($args['tableId']);

        $objChart = new Diy_Chart();
        $list = $objChart->getList($args['tableId']);
        $datas = [];
        $temp = [];
        $num = 0;

        $oData = new Diy_Data();
        foreach ($list as $value) {
            $splitField = trim($value['splitField']);
            if ($splitField) {
                $args2 = $args;
                // 增加固定条件
                $this->_fillWhere($value['fixedCondition'], $args2);

                $titleField = $this->_parseTitle($value['title']);
                $allFields = explode(',', $splitField);
                if ($titleField) {
                    $allFields = array_merge($allFields, $titleField);
                    $allFields = array_unique($allFields);
                }

                // 分割字段
                $args2['fields'] = $allFields;
                $args2['keyWord']['_groupby'] = $splitField;
                $args2['keyWord']['_limit'] = 30;
                $groups = $oData->getTableData($args2);

                foreach ($groups as $group) {
                    $value2 = $value;
                    if ($titleField) {
                        $value2['title'] = $this->_translateTitle($group, $value2['title']);
                    }

                    $group = arrayFilter($group, $splitField);
                    $value2['chartId'] .= '?' . http_build_query($group);
                    $temp[] = $value2;
                    $num += $value2['width'];
                    if ($num >= 12) {
                        $datas[] = $temp;
                        $temp = [];
                        $num = 0;
                    }
                }
            } else {
                $temp[] = $value;
                $num += $value['width'];
                if ($num >= 12) {
                    $datas[] = $temp;
                    $temp = [];
                    $num = 0;
                }
            }
        }

        if ($num >= 0) {
            $datas[] = $temp;
        }

        $oTable = new Diy_Table();
        $isAdmin = $oTable->isAdmin($args['tableId']);

        $this->tpl->assign('isAdmin', $isAdmin);
        $this->tpl->assign('datas', $datas);
        $this->tpl->display('chart/list');
    }

    private function _fillWhere($str, &$args) {
        // 增加固定条件
        if ($str) {
            parse_str($str, $param);
            foreach ($param as $key => $value) {
                if (is_array($value)) {
                    $item = [$key, 'in', $value];
                } else {
                    $item = [$key, '=', $value];
                }
                $args['where'][] = $item;
            }
        }
    }

    function array_column2($arr, $key) {
        $values = [];
        foreach ($arr as $value) {
            $values[] = $value[$key];
        }
        return $values;
    }

    /**
     * 图片
     * @param $args
     */
    function actionItem($args) {
        $objData = new DiyDataController();
        $objData->_checkParam($args);

        $tableId = $args['tableId'];
        Permission::checkTableRight($tableId);

//        keyWord:{"_sortKey":"date","_sortDir":"ASC","_showChart":"1"}
        $objChart = new Diy_Chart();
        $chartId = $args['chartId'];
        $parts = explode('?', $chartId);
        $chartId = $parts[0];

        $chart = $objChart->objTable->getRow(['chartId' => $chartId]);
        $xAxis = $chart['xAxis'];
        $args['keyWord']['_sortKey'] = $xAxis;
        $args['keyWord']['_sortDir'] = 'ASC';

        $where = $parts[1];
        $this->_fillWhere($where, $args);
        $this->_fillWhere($chart['fixedCondition'], $args);

        $objField = new Diy_Field();
        $fields = $objField->getFields($tableId);

        $oData = new Diy_Data();
        $datas = $oData->getTableData($args);

        $legendData = [];
        $seriesData = [];

        $yAxis = $chart['yAxis'];
        $compareField = trim($chart['compareField']);
        if ($compareField) {
            $xAxisData = [];
            $parts = explode(',', $compareField);
            $parts = arrayTrim($parts);

            $newDatas = [];
            foreach ($datas as $data) {
                $keys = arrayFilter($data, $parts);
                $keys = arrayTrim($keys);
                $key = join('->', $keys);
                $xAxisValue = $data[$xAxis];
                $newDatas[$key][$xAxisValue] = $data;

                $xAxisData[$xAxisValue] = $xAxisValue;
            }
            $xAxisData = array_values($xAxisData);

            // 补齐数据
            $newDatas2 = [];
            foreach ($newDatas as $key => $newData) {
                foreach ($xAxisData as $xAxisValue) {
                    $newDatas2[$key][] = $newData[$xAxisValue] ?: [];
                }
            }
            $newDatas = $newDatas2;

            foreach ($newDatas as $key => $datas) {
                $yAxisParts = explode(',', $yAxis);
                $yAxisParts = arrayTrim($yAxisParts);
                foreach ($yAxisParts as $yAxisPart) {
                    $legend = count($yAxisParts) > 1 ? $fields[$yAxisPart]['fieldCName'] . '->' : '';
                    $legendData[] = $legend . trim(strip_tags($key));
                    $seriesData[] = $this->array_column2($datas, $yAxisPart);
                }

            }
        } else {
            $xAxisData = $this->array_column2($datas, $xAxis);
            $yAxisParts = explode(',', $yAxis);
            $yAxisParts = arrayTrim($yAxisParts);
            foreach ($yAxisParts as $yAxisPart) {
                $legendData[] = $fields[$yAxisPart]['fieldCName'];
                $seriesData[] = $this->array_column2($datas, $yAxisPart);
            }
        }

        $this->tpl->assign(compact('xAxisData', 'legendData', 'seriesData'));
        $this->tpl->assign(compact('chart', 'fields', 'args'));

        if ($chart['needCustom'] && $chart['customConfig']) {
            $fullPath = ROOT_PATH . "data/chartConfig/{$chartId}.html";
            $dir = dirname($fullPath);
            mkdir($dir, 0777, true);
            file_put_contents($fullPath, $chart['customConfig']);

            ob_start();
            extract($this->tpl->vars);
            require $fullPath;
            $html = ob_get_contents();
            ob_end_clean();

            Response::exitMsg($html, CODE_SUCCESS, '', true);
        } else {
            $this->tpl->display('chart/item');
        }
    }

    /**
     * 图表配置
     * @param $args
     */
    function actionEditConfigDialog($args) {
        $rules = [
            'tableId' => ['string', 'desc' => '报表id'],
            'chartId' => ['string', 'nullable' => true, 'desc' => '图表id'],
        ];
        Param::checkParam2($rules, $args);
        Permission::checkTableRight($args['tableId']);

        $tableId = $args['tableId'];
        $chartId = current(explode('?', $args['chartId']));

        $objField = new Diy_Field();
        $fields = $objField->getFields($tableId);

        $weidu = [];
        $target = [];
        foreach ($fields as $field) {
            $txt = "{$field['fieldName']} ({$field['fieldCName']})";
            if ($field['fieldDisplay'] & 2) {
                $weidu[] = $txt;
            } else if ($field['fieldDisplay'] & 1) {
                $target[] = $txt;
            }
        }

        $objChart = new Diy_Chart();
        if ($chartId) {
            $chart = $objChart->objTable->getRow(compact('chartId'));
        } else {
            $xAxis = '';
            $yAxis = [];
            foreach ($fields as $fieldName => $field) {
                $endStr = strtolower(substr($fieldName, -4, 4));
                if ($field['fieldDisplay'] & 2) {
                    if ($endStr == 'date' || $endStr == 'time') {
                        $xAxis = $xAxis ?: $fieldName;
                    }
                }

                if ($field['fieldDisplay'] & 1) {
                    $yAxis[] = $fieldName;
                }
            }

            $chart = [
                'title' => '',
                'type' => 'line',
                'dataType' => 'tile',
                'width' => '4',
                'height' => '300',
                'xAxis' => $xAxis,
                'yAxis' => join(', ', $yAxis),
            ];
        }

        $types = $objChart->getTypes();
        $dataTypes = $objChart->getDataTypes();
        $this->tpl->assign(compact('tableId', 'fields', 'types', 'dataTypes', 'chart', 'weidu', 'target'));
        $this->tpl->display('chart/config');
    }

    /**
     * 删除配置
     */
    function actionDel($args) {
        $rules = [
            'chartId' => ['string', 'nullable' => true, 'desc' => '图表id'],
        ];
        Param::checkParam2($rules, $args);
        $args['chartId'] = current(explode('?', $args['chartId']));

        $objChart = new Diy_Chart();
        $newData = ['enable' => 0];
        $objChart->objTable->updateObject($newData, $args);
    }

    /**
     * 保存配置
     * @param $args
     */
    function actionSaveConfig($args) {
        $objChart = new Diy_Chart();
        $types = $objChart->getTypes();
        $dataTypes = $objChart->getDataTypes();

        $rules = [
            'chartId' => ['string', 'nullable' => true, 'desc' => '图表id'],
            'tableId' => ['string', 'desc' => '报表id'],
            'title' => ['string', 'nullable' => true, 'desc' => '标题'],
            'width' => ['int', 'rang' => '[1,12]', 'desc' => '宽度(栅格)'],
            'height' => ['int', 'desc' => '高度(像素)'],
            'xAxis' => ['string', 'desc' => 'x轴字段'],
            'yAxis' => ['string', 'desc' => 'Y轴字段'],
            'splitField' => ['string', 'nullable' => true, 'desc' => '分割字段'],
            'compareField' => ['string', 'nullable' => true, 'desc' => '对比字段'],
            'fixedCondition' => ['string', 'nullable' => true, 'desc' => '固定条件字段'],
            'type' => ['string', 'enum' => array_keys($types), 'desc' => '类型'],
            'dataType' => ['string', 'enum' => array_keys($dataTypes), 'desc' => '数据类型'],
            'weight' => ['int', 'nullable' => true, 'desc' => '权重'],
            'needCustom' => ['int', 'nullable' => true, 'desc' => '是否自定义'],
            'customConfig' => ['string', 'nullable' => true, 'desc' => '自定义配置'],
        ];
        Param::checkParam2($rules, $args);

        Permission::checkTableRight($args['tableId']);

        $chartId = arrayPop($args, 'chartId');
        $args['chartId'] = current(explode('?', $chartId));
        $args['updateTime'] = NOW;

        if ($chartId) {
            $objChart->objTable->updateObject($args, compact('chartId'));
            Response::success($chartId, '保存成功');
        } else {
            $args['chartId'] = uuid16();
            $args['createTime'] = NOW;
            $args['creator'] = User::getUserName();
            $objChart->objTable->addObject($args);
            Response::success($args['chartId'], '新增成功');
        }
    }

    function actionSampleConf() {
        $fileName = ROOT_PATH . 'views/chart/item.html';
        $file = file_get_contents($fileName);
        echo "<pre>" . htmlspecialchars($file) . "</pre>";
        exit;
    }

}
