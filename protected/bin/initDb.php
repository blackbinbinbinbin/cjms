<?php
/**
 * Created by PhpStorm.
 * @author benzhan
 * Date: 15/11/11
 * Time: 下午4:30
 */
require_once realpath(dirname(__FILE__)) . '/../common.php';
require_once realpath(dirname(__FILE__)) . '/common_script.php';

$pids = [];
$startTime = microtime(true);

if (singleProcess(getCurrentCommand(), BASE_DIR . '/protected/data/initDb.pid')) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL & ~E_NOTICE);

    run();
} else {
    exit("Sorry, this script file has already been running ...\n");
}

function run() {
    $objField = new TableHelper('Cmdb3Field', 'Report');
    $objEdit = new TableHelper('Cmdb3Edit', 'Report');

    $fieldDatas = $objField->getAll();
    $datas = $objEdit->getAll();

    $tDatas = [];
    foreach ($fieldDatas as $fieldData) {
        $tDatas[$fieldData['tableId']][$fieldData['fieldName']] = $fieldData;
    }
    $fieldDatas = $tDatas;

    $num = 0;
    foreach ($datas as $data) {
        $fieldData = $fieldDatas[$data['tableId']][$data['fieldName']];
        if ($fieldData['isPrimaryKey'] == 1 && $fieldData['inputType']) {
            continue;
        }

        $where = arrayFilter($data, 'tableId', 'fieldName');
        $newData = arrayFilter($data, 'inputType', 'isPrimaryKey');

        $num += $objField->updateObject($newData, $where);
    }

    var_dump($num);
}

