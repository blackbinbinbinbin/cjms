#!/usr/local/php/bin/php
<?php

set_time_limit(0);
require_once realpath(dirname(__FILE__)) . '/../../common.php';

// 定义错误码
$warningCode = array(
  CODE_DB_ERROR => 1,
  CODE_REDIS_ERROR => 1,
  CODE_UNKNOW_ERROT => 1,
  CODE_PARAM_ERROR => 100,
  -99999 => 1,
  -99997 => 1,
);

checkCode('http://61.160.36.225:9200/', 'logstash', 'app_hiyd', $warningCode);
checkCode2('http://14.152.33.214:8018/', 'logstash-ad_new_log', 'app_svideo', $warningCode);
checkCode2('http://14.152.33.214:8018/', 'logstash-ad_new_log', 'api_glance', $warningCode);

// 定义错误码
$warningCode2 = array(
    CODE_DB_ERROR => 1,
    CODE_REDIS_ERROR => 1,
    CODE_UNKNOW_ERROT => 2,
    CODE_PARAM_ERROR => 500,
    -8002 => 1,
    -8005 => 1,
);

$noSms = [
    -8002 => true,
    -8005 => true,
    CODE_PARAM_ERROR => true,
];

checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'ka_selfcall', $warningCode2, 'ka', $noSms);
checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'mka_selfcall', $warningCode2, 'ka', $noSms);
checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'kaplus_selfcall', $warningCode2, 'ka', $noSms);


checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'mkaplus_selfcall', $warningCode2, 'ka', $noSms);
checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'hezi_selfcall', $warningCode2, 'ka', $noSms);
checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'dw_selfcall', $warningCode2, 'ka', $noSms);


checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'sy_selfcall', $warningCode2, '*', $noSms);
checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'msy_selfcall', $warningCode2, '*', $noSms);

checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'mbnx_selfcall', $warningCode2, '*', $noSms);

checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'steam_selfcall', $warningCode2, '*', $noSms);
checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'up_admin_selfcall', $warningCode2, '*', $noSms);

checkCode2('http://14.152.33.214:8018/', 'logstash-yxtq_log', 'ips_selfcall', $warningCode2, '*', $noSms);



// 定义错误码
$warningCode = array(
  -99998 => 1,
);

checkCode('http://61.160.36.225:9200/', 'logstash', 'pay_selfcall', $warningCode);


// 定义错误码
$warningCode = array(
    CODE_DB_ERROR => 1,
    CODE_REDIS_ERROR => 1,
    CODE_UNKNOW_ERROT => 1,
    CODE_PARAM_ERROR => 500,
    // CODE_NOT_EXIST_INTERFACE => 300,
);

//checkCode('http://61.160.36.225:9200/', 'logstash', 'westore_mobile_selfcall', $warningCode);
//checkCode('http://61.160.36.225:9200/', 'logstash', 'westore_mobile_modulecall', $warningCode);

//checkCode('http://61.160.36.225:9200/', 'logstash', 'oujhome_selfcall', $warningCode);
//checkCode('http://61.160.36.225:9200/', 'logstash', 'm_oujhome_selfcall', $warningCode);


// 定义错误码
$warningCode2 = array(
  CODE_DB_ERROR => 1,
  CODE_REDIS_ERROR => 1,
  CODE_UNKNOW_ERROT => 1,
  CODE_PARAM_ERROR => 500,
  // CODE_NOT_EXIST_INTERFACE => 300,
);

checkCode('http://61.160.36.225:9200/', 'logstash', 'hiyd_selfcall', $warningCode, 'web');
checkCode('http://61.160.36.225:9200/', 'logstash', 'm_hiyd_selfcall', $warningCode, 'web');

/**
 * 检查错误码
 * @author benzhan
 */
function checkCode($url, $prefix, $type, $warningCode, $key = '*') {
    // 设置20秒的延时，怕最新的数据还没入库
    $to = (int) (microtime(true) * 1000) - 20 * 1000;
    $from = $to - 310 * 1000;
    $date = getLogstashDate($from, $to, $prefix);
    $url = "{$url}{$date}/_search";

    $postContent = '{"facets":{"terms":{"terms":{"field":"message.code","size":100,"order":"count","exclude":[]},"facet_filter":{"fquery":{"query":{"filtered":{"query":{"bool":{"should":[{"query_string":{"query":"*"}}]}},'
      . '"filter":{"bool":{"must":[{"range":{"@timestamp":{"from":' . $from . ',"to":' . $to . '}}},'
      . '{"fquery":{"query":{"query_string":{"query":"\"' . $type . '\""}},"_cache":true}}]}}}}}}}},"size":0}';

    $objHttp = new dwHttp();
    $ret = $objHttp->post($url, $postContent, 5);
    $result = json_decode($ret, true);
//    var_dump($url . '?' . $postContent);
//    var_dump($result);

    $terms = $result['facets']['terms']['terms'];
    $errMsg = '';
    foreach ($terms as $term) {
        $code = $term['term'];
        $count = $term['count'];
        $maxTimes = $warningCode[$code];
        if ($maxTimes && $count >= $maxTimes) {
            $errMsg .= " 【错误码{$code} 出现{$count}次】,";
        }
    }
    
    if ($errMsg) {
        $errMsg = substr($errMsg, 0, -1);
        $errMsg = "【错误码】{$type} 告警:{$errMsg}";
        YYms::reportServerError($errMsg, $key);
        echo $errMsg;
    } else {
        $msg = "【正常】{$type}\n";
        echo $msg;
    }
}


/**
 * ES v2.x 检查错误码
 * @author benzhan
 */
function checkCode2($url, $prefix, $type, $warningCode, $key = '*', $noSms = []) {
    // 设置20秒的延时，怕最新的数据还没入库
    $to = (int) (microtime(true) * 1000) - 20 * 1000;
    $from = $to - 310 * 1000;
    $date = getLogstashDate($from, $to, $prefix);
    $url = "{$url}{$date}/_search";

    $postContent = <<<CONTENT
{
  "query": {
    "filtered": {
      "query": {
        "query_string": {
          "query": "*",
          "analyze_wildcard": true
        }
      },
      "filter": {
        "bool": {
          "must": [
            {
              "query": {
                "match": {
                  "type": {
                    "query": "{$type}",
                    "type": "phrase"
                  }
                }
              }
            },
            {
              "query": {
                "query_string": {
                  "analyze_wildcard": true,
                  "query": "*"
                }
              }
            },
            {
              "range": {
                "@timestamp": {
                  "gte": {$from},
                  "lte": {$to},
                  "format": "epoch_millis"
                }
              }
            }
          ],
          "must_not": []
        }
      }
    }
  },
  "size": 0,
  "aggs": {
    "2": {
      "terms": {
        "field": "message.code",
        "size": 10,
        "order": {
          "_count": "desc"
        }
      }
    }
  }
}
CONTENT;

    $objHttp = new dwHttp();
    $ret = $objHttp->post($url, $postContent, 5);
    $result = json_decode($ret, true);

    $terms = $result['aggregations'];
    $errMsg = '';
    $warningMsg = '';
    foreach ($terms as $term) {
        foreach ($term['buckets'] as $bucket) {
            $code = $bucket['key'];
            $count = $bucket['doc_count'];
            $maxTimes = $warningCode[$code];
            if ($maxTimes && $count >= $maxTimes) {
                $msg = " 【错误码{$code} 出现{$count}次】,";
                if ($noSms[$code]) {
                    $warningMsg .= $msg;
                } else {
                    $errMsg .= $msg;
                }
            }
        }
    }

    if ($warningMsg) {
        $warningMsg = substr($warningMsg, 0, -1);
        $warningMsg = "【Warning】{$type} 提醒:{$warningMsg}";
        YYms::reportServerWarning($warningMsg, $key);
        echo $warningMsg;
    }

    if ($errMsg) {
        $errMsg = substr($errMsg, 0, -1);
        $errMsg = "【错误码】{$type} 告警:{$errMsg}";
        YYms::reportServerError($errMsg, $key);
        echo $errMsg;
    } else {
        $msg = "【正常】{$type}\n";
        echo $msg;
    }
}

function getLogstashDate($from, $to, $prefix = 'ybsc') {
    $from = $from - 8 * 3600 * 1000;
    $to = $to - 8 * 3600 * 1000;
    $date1 = date('Y.m.d', $from / 1000);
    $date2 = date('Y.m.d', $to / 1000);

    if ($date1 != $date2) {
        // 说明是隔天的情况
        return "{$prefix}-{$date1},{$prefix}-{$date2}";
    } else {
        return "{$prefix}-{$date1}";
    }
}


