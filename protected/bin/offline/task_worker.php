<?php

require_once realpath(dirname(__FILE__)) . '/common.php';

//$argv = ['', 66];
//$argc = count($argv);

if ($argc < 2) {
    exit("Sorry, without task_id ...\n");
}
$task_id = $argv[1];

if (singleProcess(getCurrentCommand(), BASE_DIR . "/protected/data/task_worker_{$task_id}.pid")) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL & ~E_NOTICE);
    main($task_id);
} else {
    exit("Sorry, this script file has already been running ...\n");
}

function main($task_id) {
    var_dump("main {$task_id}");
    $task = getTask($task_id);

    // 是否有互斥脚本在运行
    $exclusiveTasks = getExclusiveTasks($task_id);
    if ($exclusiveTasks && isTaskRunning($exclusiveTasks)) {
        var_dump("waiting, there is exclusive task running...任务:{$task['taskName']}, 任务id:{$task['taskId']}");
        return false;
    }

    $taskLog = getTaskLog($task_id);
    if ($taskLog) {
        // 递增方式，需要计算数据结束时间，防止重复计算
        if ($task['timeField']) {
            $endTime = strtotime($taskLog['dataEndTime']);
        } else {
            $endTime = strtotime($taskLog['execEndTime']);
        }

        $span = time() - $task['execDelay'] - $endTime;
        switch($taskLog['execStatus']) {
            // 成功和部分成功都可以继续
            case 1:
            case 2:
                if ($span > $task['execInterval']) {
                    // 时间到了，开始执行
                    runTask($task, $taskLog);
                } else {
                    var_dump("waiting, this task has runned just now. span:{$span}, execInterval:{$task['execInterval']}. 任务：{$task['taskName']}, 任务id:{$task['taskId']}");
                }
                break;
            case 0:
                // 执行失败
                var_dump("this task is doing");
                break;
            default:
                var_dump("unknown execStatus:{$taskLog['execStatus']}");
                break;
        }
    } else {
        var_dump("} else {");
        // 默认需要执行脚本
        runTask($task, []);
    }
}

function runTask($task, $log) {
    $maxBeginTime = time() - $task['execDelay'] - $task['timeInterval'];
    if ($task['staticType'] == 1) {
        // 递增方式，需要计算数据结束时间，防止重复计算
        $dataBeginTime = $log['dataEndTime'] ? strtotime($log['dataEndTime']) + 1 : $maxBeginTime;
    } else if ($task['staticType'] == 2) {
        // 覆盖方式
        // 判断结束时间是否超过了最大值
        if ($log['dataBeginTime']) {
            $dataBeginTime = strtotime($log['dataEndTime']) + 1;
            // 结束时间大于最大时间,则从最大的时间开始
            $dataBeginTime = min($dataBeginTime, $maxBeginTime);
        } else {
            $dataBeginTime = $maxBeginTime;
        }
    } else {
        var_dump($task);
        var_dump($GLOBALS['dbInfo']['Report']);
        var_dump("unknown staticType:{$task['staticType']}");
        return;
    }

    $objTask = new Diy_Task($task, $dataBeginTime);
    $objTask->run();
    $msg = $objTask->getErrorMsg();
    $msg && var_dump($msg);
}

function isTaskRunning($taskIds) {
    if (!is_array($taskIds)) {
        $taskIds = explode(',', $taskIds);
    }
    foreach ($taskIds as $id) {
        if (getProcessCommand(BASE_DIR . "/protected/data/task_worker_{$id}.pid")) {
            return true;
        } 
    }
    return false;
}

function getExclusiveTasks($taskId) {
    $objDetail = new TableHelper('Cmdb3TaskExclusiveDetail', 'Report');
    $exclusives = $objDetail->getAll(compact('taskId'));
    if ($exclusives) {
        $groupId = array_map(function($v) {
            return $v['groupId'];
        }, $exclusives);

        $details = $objDetail->getAll(compact('groupId'));

        $exclusiveTasks = [];
        foreach ($details as $k => $v) {
            if ($v['taskId'] != $taskId) {
                $exclusiveTasks[] = $v['taskId'];
            }
        }
        return $exclusiveTasks;
    } else {
        return false;
    }
}