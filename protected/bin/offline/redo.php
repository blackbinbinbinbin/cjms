<?php
/**
 * Created by PhpStorm.
 * @author benzhan
 * Date: 15/11/4
 * Time: 下午6:10
 */

require_once realpath(dirname(__FILE__)) . '/common.php';

$pids = [];

main();

function main() {
    // 清楚上次的数据
    clearTimeLog();

    $time = date('H:i');
    $tasks = getRedoTasks();
    $taskLogs = getRedoTaskLogs();

    foreach ($tasks as $task) {
        $task['redoConfig'] = json_decode($task['redoConfig'], true);
        $task['redoConfig'] = $task['redoConfig'][$time];
        if (!$task['redoConfig']) {
            continue;
        }

        $staticType = $task['redoConfig']['staticType'];
        $staticType && $task['staticType'] = $staticType;

        $insertType = $task['redoConfig']['insertType'];
        $insertType && $task['insertType'] = $insertType;

        $taskId = $task['taskId'];
        $log = $taskLogs[$taskId];
        if ($log) {
            // 递增方式，需要计算数据结束时间，防止重复计算
            $endTime = strtotime($log['execEndTime']);
            $span = time() - $endTime;
            switch($log['execStatus']) {
                case 1:
                case 2:
                    if ($span > 3600) {
                        // 时间到了，开始执行
                        addTask($task, $log);
                    } else {
                        var_dump("waiting, this task has runned just now. 任务：{$task['taskName']}, 任务id:{$task['taskId']}");
                    }
                    break;
                case 0:
                    $timeSpan = time() - strtotime($log['execBeginTime']);
                    // 防止无休止的告警
                    if ($timeSpan >= 3600 && $timeSpan < 7200) {
                        // 第二次的时间到了，但老任务还没执行完，需要告警
                        $msg = "重算逻辑执行超时，任务：{$task['taskName']}, 任务id:{$task['taskId']}, logId:{$log['taskLogId']}";
                        YYms::reportServerWarning($msg);
                    } else {
                        var_dump("waiting, this task is running. 任务：{$task['taskName']}, 任务id:{$task['taskId']}");
                    }
                    break;
            }
        } else {
            // 默认需要执行脚本
            addTask($task, []);
        }
    }

    waitTask();

    var_dump(date('Y-m-d H:i:s') . " parent exit. ");
}

function getRedoTasks() {
    $objTask = new TableHelper('Cmdb3Task', 'Report');
    $sql = "SELECT * FROM Cmdb3Task WHERE redoConfig IS NOT NULL AND redoConfig != '' AND enable = 1";
    $tasks = $objTask->getDb()->getAll($sql);
    return $tasks;
}

function getRedoTaskLogs() {
    $date = date('Y-m-d H:i:s', time() - 3 * 86400);
    $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');
    $sql = "SELECT * FROM (
                SELECT * FROM Cmdb3TaskLog
                WHERE execStatus != -1 AND isRedo = 1 AND execBeginTime > '$date'
                ORDER BY dataEndTime DESC
            ) AS t GROUP BY taskId";

    $taskLogs = $objTaskLog->getDb()->getAll($sql);
    $taskLogs = arrayFormatKey($taskLogs, 'taskId');
    return $taskLogs;
}

function addTask($task, $log) {
    $objTask = new Diy_Task($task, 0, 1);
    runTask2($objTask);
}

function runTask2(Diy_Task $objTask) {
    global $pids;

    $pid = pcntl_fork();
    if ($pid == -1) {
        die('fork failed');
    } else if ($pid == 0) {
        DB::$db = [];
        // 子进程
        $objTask->run();
        $pid = getmypid();
        var_dump(date('Y-m-d H:i:s') . " child pid:{$pid}, exit");
        // 一定要exit
        exit;
    } else {
        var_dump(date('Y-m-d H:i:s') . " pcntl_fork pid:{$pid}");
        $pids[] = $pid;
    }
}

function waitTask() {
    global $pids;
    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
        var_dump(date('Y-m-d H:i:s') . " parent pid:{$pid}, status:{$status}");
    }
}

function clearTimeLog() {
    $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');
    // 删除5分钟还没执行完成的记录
    $execBeginTime = time() - 300;
    $execBeginTime = date('Y-m-d H:i:s', $execBeginTime);

    $execStatus = 0;
    $_where = "execBeginTime < '{$execBeginTime}'";
    $where = compact('_where', 'execStatus');
    $taskLogs = $objTaskLog->getAll($where);
    if (count($taskLogs) > 0) {
        $msg = '';
        foreach ($taskLogs as $log) {
            $msg .= "执行超时，任务id:{$log['taskId']}, logId:{$log['taskLogId']}\r\n";
        }
        YYms::reportServerWarning($msg);

        // 插入到慢日志表
        $objTaskSlowLog = new TableHelper('Cmdb3TaskSlowLog', 'Report');
        $objTaskSlowLog->addObjects2($taskLogs);

        // 删除数据
        $sql = "DELETE FROM `Cmdb3TaskLog` WHERE execStatus = 0 AND {$_where}";
        $objTaskLog->getDb()->query($sql);

        var_dump($objTaskLog->sqls);
        var_dump($objTaskSlowLog->sqls);
        var_dump($sql);
    }
}

