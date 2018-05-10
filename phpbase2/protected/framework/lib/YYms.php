<?php

/**
 * YY 公司的告警库
 * @author benzhan
 *
 */
class YYms {

    private static function execCmd($reportContent, $featureId, $strategyId) {
        $command = "python /usr/local/i386/public_repos/initial_system/snmp/ext/yymp/yymp_report_script/yymp_report_alarm.py {$featureId} {$strategyId} 0 '$reportContent'";
        exec($command, $output);
        var_log($command);
        var_log('$output:' . json_encode($output));
        return $output;
    }

    private static $map = [
        '*' => [
            12385,
            78809,
            78807,
        ],
        'web' => [
            13491,
            81309,
            81311,
        ],
        'ka' => [
            13491,
            135910,
            135909,
        ],
        'spider' => [
            13491,
            137724,
            137724,
        ],
    ];

    /**
     * 告警服务端自身问题
     * @author benzhan
     * @param string $reportContent 告警内容
     * @param string $key 告警key
     */
    public static function reportServerError($reportContent, $key = '*') {
        $featureId = self::$map[$key][0];
        $strategyId = self::$map[$key][1];

        return self::execCmd($reportContent, $featureId, $strategyId);
    }

    /**
     * 提醒服务端自身问题
     * @author benzhan
     * @param string $reportContent 告警内容
     * @param string $key 告警key
     */
    public static function reportServerWarning($reportContent, $key = '*') {
        $featureId = self::$map[$key][0];
        $strategyId = self::$map[$key][2];

        return self::execCmd($reportContent, $featureId, $strategyId);
    }
}
