<?php
/**
 * Created by PhpStorm.
 * @author benzhan
 * Date: 15/11/4
 * Time: 下午6:10
 */

require_once realpath(dirname(__FILE__)) . '/common.php';

pcntl_signal(SIGCLD, SIG_IGN);
pcntl_signal(SIGCHLD, SIG_IGN);


if (singleProcess(getCurrentCommand(), BASE_DIR . '/protected/data/task_scheduler.pid')) {
    $lastDate = date('Y-m-d');
    while(true) {
        $lastDate = main($lastDate);
    }
} else {
    exit("Sorry, this script file has already been running ...\n");
}

function main($lastDate) {
    $tasks = getTasks();
    $taskLogs = getTaskLogs($tasks);

    foreach ($tasks as $task) {
        $taskId = $task['taskId'];
        $log = $taskLogs[$taskId];

        // 是否有互斥脚本在运行
        $exclusiveTasks = getExclusiveTasks($taskId);
        if ($exclusiveTasks && isTaskRunning($exclusiveTasks)) {
            var_dump("waiting, there is exclusive task running...任务:{$task['taskName']}, 任务id:{$task['taskId']}");
            continue;
        }

        if ($log) {
            // 递增方式，需要计算数据结束时间，防止重复计算
            if ($task['timeField']) {
                $endTime = strtotime($log['dataEndTime']);
            } else {
                $endTime = strtotime($log['execEndTime']);
            }
            $span = time() - $task['execDelay'] - $endTime;

            switch($log['execStatus']) {
                // 成功和部分成功都可以继续
                case 1:
                case 2:
                    if ($span > $task['execInterval']) {
                        // 时间到了，开始执行
                        runTask($taskId);
                    } else {
                        // var_dump("waiting, this task has runned just now. 任务：{$task['taskName']}, 任务id:{$task['taskId']}");
                    }
                    break;
                case 0:
//                    $timeSpan = time() - strtotime($log['execBeginTime']);
//                    // 防止无休止的告警
//                    if ($timeSpan > $task['execInterval'] && $timeSpan < 3 * $task['execInterval']) {
//                        // 第二次的时间到了，但老任务还没执行完，需要告警
//                        $msg = "执行超时，任务：{$task['taskName']}, 任务id:{$task['taskId']}, logId:{$log['taskLogId']}";
//                        YYms::reportServerWarning($msg);
//                    } else {
//                        var_dump("waiting, this task is running. 任务：{$task['taskName']}, 任务id:{$task['taskId']}");
//                    }
                    break;
            }
        } else {
            // 默认需要执行脚本
            runTask($taskId);
        }

    }

    // 删除历史记录
    $today = date('Y-m-d');
    if ($lastDate != $today) {
        deleteTaskOldLogs();
    }

    // 休息3秒
    sleep(3);

    return $today;
}


function deleteTaskOldLogs() {
    $date = date('Y-m-d H:i:s', time() - 30 * 86400);
    $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');
    $sql = "DELETE FROM Cmdb3TaskLog WHERE execBeginTime < '$date'";
    $objTaskLog->getDb()->update($sql);
}


function runTask($taskId) {
    // 时间到了，开始执行
    $cmd = '/usr/local/php/bin/php ' . ROOT_PATH . "bin/offline/task_worker.php {$taskId} >> /tmp/task_worker_{$taskId}.log & ";
    var_dump($cmd);
    exec($cmd);
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
                $exclusiveTasks[] = $taskId;
            }
        }
        return $exclusiveTasks;
    } else {
        return false;
    }
}