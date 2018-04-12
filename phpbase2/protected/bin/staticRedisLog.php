<?php

require_once realpath(dirname(__FILE__)) . '/common_script.php';

/**
 * 统计Redis的成功率【中间方案，暂不考虑】
 * User: ben
 * Date: 2017/4/17
 * Time: 下午6:02
 */

ini_set("display_errors", "On");
error_reporting(E_ALL & ~E_NOTICE);

if (singleProcess(getCurrentCommand(), BASE_DIR . '/protected/bin/run/staticRedisLog.pid')) {
    main();
} else {
    exit("Sorry, this script file has already been running ...\n");
}

function main() {

    $data = getData();

    $conf_path = '/run/dwRedis.ini';
    $conf = parse_ini_file($conf_path);
    unset($conf['updateTime']);
    unset($conf['totalWeight']);

    $scoreMap = $errorMap = $successMap = [];
    foreach ($scoreMap as $key => $score) {
        $errorTimes = (int) $errorMap[$key];
        $successTimes = (int) $successMap[$key];
        $successTimes = max($successTimes, 1);
        $errorRate =  $errorTimes / $successTimes;

        $currentScore = max($conf[$key], 1);
        $currentScore += ceil($score / $currentScore);
        $currentScore = ceil($currentScore * (1 - $errorRate * 5) );
        $conf[$key] = max($currentScore, 1);
    }

    writeConf($conf);
}

function getData() {
    $allFiles = [];
    foreach (scandir(dwRedis2::$logDir) as $afile) {
        if ($afile == '.' || $afile == '..') {
            continue;
        }

        $pos = strrpos($afile, '-');
        $allFiles[substr($afile, 0, $pos)][] = $afile;
    }

    $scoreMap = [];
    $errorRateMap = [];
    $successMap = [];
    $errorMap = [];
    // 从最新的日志开始统计，累计1000条就忽略后续日志，优先选择最近的日志文件
    foreach ($allFiles as $key => $files) {
        rsort($files);
        $line = 0;

        $scoreMap[$key] = 0;
        $errorMap[$key] = 0;
        $successMap[$key] = 0;

        foreach ($files as $file) {
            $str = file_get_contents(dwRedis2::$logDir . $file, false, null, 0, 200000);
            if (!$str) {
                continue;
            }

            $values = explode('\n', $str);
            foreach ($values as $value) {
                $scoreMap[$key] += $value;
                if ($value < 0) {
                    $errorMap[$key] += 1;
                } else {
                    $successMap[$key] += 1;
                }

                $line++;
            }


            if ($line > 1000) {
                break;
            }
        }

        // 失败率统计
        $errorRateMap[$key] =  $errorMap[$key] / ($successMap[$key] + $errorMap[$key]);
    }

//    foreach ($scoreMap as $key => $score) {
//        $errorTimes = (int) $errorMap[$key];
//        $successTimes = (int) $successMap[$key];
//        $errorRateMap[$key] =  $errorTimes / $successTimes;

//        $currentScore = max($conf[$key], 1);
//        $currentScore += ceil($score / $currentScore);
//        $currentScore = ceil($currentScore * (1 - $errorRate * 5) );
//        $conf[$key] = max($currentScore, 1);
//    }

    return compact('scoreMap', 'errorRateMap');
}

function getConfs() {

}

function writeConf($conf) {

    // 格式化比例
    $total = 0;
    foreach ($conf as $key => $value) {
        $total += $value;
    }

    foreach ($conf as $key => $value) {
        // 最小也有百分之一的概率
        $conf[$key] = max(ceil($value / $total * 10000), 100);
    }

    $newTotal = 0;
    foreach ($conf as $key => $value) {
        $newTotal += $value;
    }

    $newConf = [
        'updateTime' => time(),
        'totalWeight' => $newTotal,
    ];
    $newConf = array_merge($newConf, $conf);

    $str = '';
    foreach ($newConf as $key => $value) {
        $str .= "{$key} = {$value}";
    }
    file_put_contents(dwRedis2::$confPath, $str);
}


