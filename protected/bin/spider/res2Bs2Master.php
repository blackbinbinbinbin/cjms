<?php
/**
 * 资源下载Worker进程
 * User: XianDa
 * Date: 2017/5/26
 * Time: 14:01
 */

$startTime = time();
echo $startTime."脚本开始\n";
$st = microtime(true);
ini_set("display_errors", "On");
error_reporting(E_ALL & ~E_NOTICE);

require_once realpath(dirname(__FILE__)) . '/../../common.php';
require_once ROOT_PATH . '/bin/common_script.php';

// 这个脚本只能单进程进行
$pidFile = BASE_DIR . "/protected/bin/run/res2Bs2Master.pid";
$flag = singleProcess(getCurrentCommand(), $pidFile);
if (!$flag) {
    exit("Sorry, this script file has already been running ...,pid:{$pidFile}\n");
}

$objRedis = dwRedis::init('logic');
$listKey = 'globals:res2Bs2_list';
$count = $objRedis->sCard($listKey);
if ($count > 5) {
    exit("{$listKey} num:{$count}, not need fill.");
}

$objRule = new TableHelper('rule', 'crawl');
$rule_ids = $objRule->getCol(['enable' => '0'], ['_field' => 'rule_id']);

$objItem = new TableHelper('item', 'crawl');
// $objDbTable = new TableHelper('db_table', 'crawl');
$objDbTable = new TableHelper('data_db', 'crawl');

$_field = 'rule_id, field_name, save_as, last_saveas_time, save_as_referer';
if ($rule_ids) {
    $_where = "rule_id NOT IN ('" . join("', '", $rule_ids) . "')";
} else {
    $_where = '';
}
$items = $objItem->getAll(['save_as' => [1,2,3], 'enable' => 1], compact('_where', '_field'));

$pageSize = 3000;
//$pageSize = 1;
foreach ($items as $item) {
    $last_saveas_time = $item['last_saveas_time'];
    $span = time() - strtotime($last_saveas_time);
    if ($span < 300) {
        continue;
    }

    // 更新最后同步时间
    $newData = ['last_saveas_time' => date('Y-m-d H:i:s')];
    $where = arrayFilter($item, ['rule_id', 'field_name']);
    $objItem->updateObject($newData, $where);

    $parts = explode('.', $item['field_name']);
    if (count($parts) > 1) {
        $tableName = trim($parts[0]);
        $fieldName = trim($parts[1]);
    } else {
        $tableName = null;
        $fieldName = trim($parts[0]);
    }

//    $tables = $objDbTable->getAll(['rule_id' => $item['rule_id']]);
    $sql = "SELECT db_name, table_name, pri_key, notice_url, rule_id, is_default, update_mode 
                   FROM data_db JOIN rule_db_conf ON data_db.db_id = rule_db_conf.db_id 
                  WHERE rule_id = '{$item['rule_id']}'";
    $tables = $objDbTable->getDb()->getAll($sql);

    $dbName = false;
    $keyName = false;
    $update_time_field = false;
//    $rules = arrayFormatKey2($rules, 'table_name');
    foreach ($tables as $table) {
        $flag1 = $tableName && $tableName == $table['table_name'];
        $flag2 = !$tableName && $table['is_default'];
        if ($flag1 || $flag2) {
            $tableName = $table['table_name'];
            $dbName = $table['db_name'];
            $keyName = $table['pri_key'];
            $update_time_field = $table['update_time_field'];
            break;
        }
    }

    if (!$dbName || !$tableName || !$fieldName || !$keyName) {
        _log("参数错误：\$dbName:{$dbName}, \$tableName:{$tableName}, \$fieldName:{$fieldName}, \$keyName:{$keyName}");
        continue;
    }

    $offset = 0;
//    $_where = "`{$keyName}` IS NOT NULL ";
    $_where = '';
    if ($update_time_field) {
        $_where = "`{$update_time_field}` > '{$last_saveas_time}' ";
    }

    $objTable = new TableHelper($tableName, $dbName);
    $count = $objTable->getCount(compact('_where'));

    $limits = [];
    $totalPage = $count / $pageSize;
    for ($i = 0; $i < $totalPage; $i++) {
        $limits[] = $i * $pageSize . ',' . $pageSize;
    }

    // 需要加入到任务队列中
    $data = compact('dbName', 'tableName', 'keyName', 'fieldName', '_where');
    $data['save_as_referer'] = $item['save_as_referer'];
    $data['is_array'] = $item['save_as'] == 2; // 判断是否是json数组
    $data['is_text'] = $item['save_as'] == 3; // 判断是否是json数组
    $data['update_time_field'] = $update_time_field;

    foreach ($limits as $limit) {
        $data['_limit'] = $limit;
        $json = json_encode($data);

        $prekey = "globals:res2Bs2:";
        if (!$objRedis->exists($prekey . join('|', $data))) {
            _log('sAdd: ' . $json);
            $objRedis->sAdd('globals:res2Bs2_list', $json);
        }

    }
//        break;
}

// 记录日志
function _log($log) {
    $time = date('Y-m-d H:i:s');
    var_dump("【{$time}】{$log}");
}
