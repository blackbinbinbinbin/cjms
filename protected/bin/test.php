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

if (singleProcess(getCurrentCommand(), BASE_DIR . '/protected/data/test.pid')) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL & ~E_NOTICE);

    run();
} else {
    exit("Sorry, this script file has already been running ...\n");
}

function run() {
    global $pids;

    $pid = pcntl_fork();
    if ($pid == -1) {
        die(date('Y-m-d H:i:s') . ' fork failed');
    } else if ($pid == 0) {
        // 子进程
        sleep(count($pids) + 1);
        $pid = getmypid();
        var_dump(date('Y-m-d H:i:s') . ' child exit.' . $pid);
        exit;
    } else {
        var_dump(date('Y-m-d H:i:s') . ' pcntl_fork:' . $pid);
        // 父进程休息1秒，才去调度下个进程
        $pids[] = $pid;

        if (count($pids) < 3) {
            run();
        } else {
            var_dump(date('Y-m-d H:i:s') . ' parent end.');
        }

        waitTask();
    }
}


function waitTask() {
    global $pids, $startTime;
    sleep(1);
    var_dump("waitTask, pids:" . join(',', $pids));

    $runningPids = [];
    foreach ($pids as $pid) {
        $command = exec("/bin/ps -p $pid -o command=");
        var_dump('pid:' . $pid . ', command:' . $command);
        if ($command == getCurrentCommand()) {
            $runningPids[] = $pid;
        }
    }

    if ($runningPids) {
        $endTime = microtime(true);
        // 判断时间是否到了
        if ($endTime - $startTime > 240) {
            // 杀死所有子进程
            foreach ($runningPids as $pid) {
                posix_kill($pid, SIGKILL);
                var_dump("posix_kill($pid, SIGKILL)");
            }
            exit;
        } else {
            // 递归查询
            $pids = $runningPids;
            waitTask();
        }
    }

}

run();


