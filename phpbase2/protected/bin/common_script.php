<?php

require_once realpath(dirname(__FILE__)) . '/../common.php';

/**
 * 保证单进程
 * @param string $processName 进程名
 * @param string $pidFile 进程文件路径
 * @return boolean 是否继续执行当前进程
 */
function singleProcess($processName, $pidFile) {
    $command = getProcessCommand($pidFile);

    if ($command == $processName) {
        return FALSE;
    }

    $cur_pid = posix_getpid();
    if ($fp = @fopen($pidFile, "wb")) {
        var_dump('fopen wb:' . $cur_pid);
        fputs($fp, $cur_pid);
        ftruncate($fp, strlen($cur_pid));
        fclose($fp);
        return TRUE;
    } else {
        var_dump('fopen faild:' . $pidFile);
        return FALSE;
    }
}

/**
 * 获取当前进程对应的Command
 * @return string 命令及其参数
 */
function getCurrentCommand() {
    $pid = posix_getpid();
    $command = exec("/bin/ps -p $pid -o command=");
    return $command;
}

/**
 * 检查进程是否在运行
 * @return  string 命令或者空字符串
 */
function getProcessCommand($pidFile) {
    if (file_exists($pidFile) && $fp = @fopen($pidFile, "rb")) {
        flock($fp, LOCK_SH);
        $last_pid = fread($fp, filesize($pidFile));
        fclose($fp);
        if (!empty($last_pid)) {
            $command = exec("/bin/ps -p $last_pid -o command=");
            return $command;
        } else {
            return '';
        }
    } else {
        return '';
    }

}