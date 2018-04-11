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

if (singleProcess(getCurrentCommand(), BASE_DIR . '/protected/data/offlineTask.pid')) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL & ~E_NOTICE);

    while(true) {
        main();
    }
} else {
    exit("Sorry, this script file has already been running ...\n");
}

function main() {
    $tasks = getTasks();
    $taskLogs = getTaskLogs($tasks);

    $pids = [];
    foreach ($tasks as $task) {
        $taskId = $task['taskId'];
        $log = $taskLogs[$taskId];
        $pid = 0;
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
                        $pid = addTask($task, $log);
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
            $pid = addTask($task, []);
        }

        if ($pid) {
            $pids[] = $pid;
        }
    }

    if ($pids) {
        waitTask($pids);
    } else {
        // var_dump('no pids, sleep 3 seconds.');
        sleep(3);
    }
}
//
//function getTasks() {
//    $objTask = new TableHelper('Cmdb3Task', 'Report');
//    $where = ['enable' => 1];
//    $tasks = $objTask->getAll($where);
//    return $tasks;
//}
//
//function getTaskLogs() {
//    $date = date('Y-m-d H:i:s', time() - 3 * 86400);
//    $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');
//    $sql = "SELECT * FROM (
//                SELECT * FROM Cmdb3TaskLog
//                WHERE execStatus != -1 AND isRedo = 0 AND execBeginTime > '$date'
//                ORDER BY dataEndTime DESC
//            ) AS t GROUP BY taskId";
//
//    $taskLogs = $objTaskLog->getDb()->getAll($sql);
//    $taskLogs = arrayFormatKey($taskLogs, 'taskId');
//    return $taskLogs;
//}

function addTask($task, $log) {
    if ($task['staticType'] == 1) {
        // 递增方式，需要计算数据结束时间，防止重复计算
        $dataBeginTime = $log['dataEndTime'] ? strtotime($log['dataEndTime']) + 1 : 0;
    } else if ($task['staticType'] == 2) {
        // 覆盖方式，不需要算数据结束时间
        $dataBeginTime = $log['dataBeginTime'] ? strtotime($log['dataBeginTime']) + $task['execInterval'] : 0;
    } else {
        return;
    }

    $objTask = new Diy_Task($task, $dataBeginTime);
    return runTask2($objTask);
}

function runTask2(Diy_Task $objTask) {
    $pid = pcntl_fork();
    if ($pid == -1) {
        die('fork failed');
    } else if ($pid == 0) {
        DB::$db = [];
        // 子进程
        $objTask->run();
        $pid = getmypid();
        $msg = $objTask->getErrorMsg();
        $msg && var_dump($msg);
        // var_dump(date('Y-m-d H:i:s') . " child pid:{$pid}, exit");
        // 一定要exit
        exit;
    } else {
        // var_dump(date('Y-m-d H:i:s') . " pcntl_fork pid:{$pid}");
        return $pid;
    }
}

function waitTask(array $pids) {
    $startTime = microtime(true);

    // var_dump("waitTask, pids:" . join(',', $pids));
    sleep(3);
    $runningPids = [];
    foreach ($pids as $pid) {
        $command = exec("/bin/ps -p $pid -o command=");
        $currentCmd = getCurrentCommand();
        if ($command == $currentCmd) {
            $runningPids[] = $pid;
        }
    }

    // var_dump("waitTask, runningPids:" . join(',', $runningPids));
    if ($runningPids) {
        $endTime = microtime(true);
        // 判断时间是否到了
        if ($endTime - $startTime > 180) {
            // 杀死所有子进程
            foreach ($runningPids as $pid) {
                posix_kill($pid, SIGKILL);
                var_dump("timeout, posix_kill($pid, SIGKILL)");
            }

            exit;
        } else {
            // 递归查询
            waitTask($runningPids);
        }
    } else {
        // var_dump('all child is finish, parent exit');
    }
}

