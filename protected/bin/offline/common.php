<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2017/10/21
 * Time: 上午7:49
 */

set_time_limit(0);

require_once realpath(dirname(__FILE__)) . '/../../common.php';
require_once ROOT_PATH . 'bin/common_script.php';

ini_set('memory_limit', '2048M');

ini_set("display_errors", "On");
error_reporting(E_ALL & ~E_NOTICE);



function getTasks() {
    $objTask = new TableHelper('Cmdb3Task', 'Report');
    $where = ['enable' => 1];
    //    $where = ['taskId' => 12];
    $tasks = $objTask->getAll($where);
    return $tasks;
}

function getTaskLogs($tasks) {
    $date = date('Y-m-d H:i:s', time() - 3 * 86400);
    $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');

    $taskIds = array_column($tasks, 'taskId');
    $taskLogs = [];
    foreach ($taskIds as $taskId) {
        $sql = "SELECT * FROM Cmdb3TaskLog
                WHERE execStatus != -1 AND isRedo = 0 AND execBeginTime > '{$date}' 
                AND taskId = '{$taskId}' ORDER BY dataEndTime DESC";

        $log = $objTaskLog->getDb()->getRow($sql);
        if ($log) {
            $taskLogs[$taskId] = $log;
        }
    }

    return $taskLogs;
}


function getTask($task_id) {
    $objTask = new TableHelper('Cmdb3Task', 'Report');
    $where = [];
//    $where = ['enable' => 1];
    $where['taskId'] = $task_id;
    $task = $objTask->getRow($where);
    return $task;
}

function getTaskLog($taskId) {
    $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');
    $sql = "SELECT * FROM Cmdb3TaskLog
            WHERE execStatus != -1 AND isRedo = 0 AND taskId = {$taskId}
            ORDER BY dataEndTime DESC";

    $row = $objTaskLog->getDb()->getRow($sql);
    return $row;
}
