#!/usr/local/php/bin/php
<?php

set_time_limit(0);
define('BASE_DIR', dirname(__FILE__) . '/../../');
require_once BASE_DIR . 'protected/bin/common.php';


$specialApis = array(
    // 'iappay' => 4,
    // 'order' => 2,
);
checkApiState('http://61.160.36.225:9200/', 'logstash', 'westore_mobile_modulecall', 'to_url', $specialApis);


/**
 * 获取当前的url
 * @param unknown $date
 * @param unknown $from
 * @param unknown $to
 * @return multitype:unknown
 */
function getUrls($url, $prefix, $type, $filed, $from, $to) {
    $date = getLogstashDate($from, $to, $prefix);
    $url = "{$url}{$date}/_search";
    $postContent = '{"facets":{"query":{"terms":{"field":"message.' . $filed . '","size":100},"facet_filter":{"fquery":{"query":{"filtered":{"query":{"query_string":{"query":"*"}},"filter":{"bool":{"must":[{"range":{"@timestamp":{"from":' . $from;
    $postContent .=  ',"to":' . $to . '}}},{"fquery":{"query":{"query_string":{"query":"\"' . $type . '\""}},"_cache":true}}]}}}}}}}},"size":0}';
    
    $objHttp = new dwHttp();
    $ret = $objHttp->post($url, $postContent);

    $result = json_decode($ret, true);
    $terms = $result['facets']['query']['terms'];
    $urls = array();
    foreach ($terms as $value) {
        $urls[] = $value['term'];
    }
    
    return $urls;
}

function getUrlStatisInfo($url, $prefix, $filed, array $queryUrls, $type, $from, $to) {
    $date = getLogstashDate($from, $to, $prefix);
    $url = "{$url}{$date}/_search";
    
    $postContent = '{"facets":{';
    foreach ($queryUrls as $queryUrl) {
        $postContent .= '"' . $queryUrl . '":{"statistical":{"field":"message.delay"},"facet_filter":{"fquery":{"query":{"filtered":{"query":{"bool":{"should":[{"query_string":{"query":"message.' . $filed . ':\"' . $queryUrl . '\" AND (*)"}}]}},';
        $postContent .= '"filter":{"bool":{"must":[{"range":{"@timestamp":{"from":' . $from . ',"to":' . $to . '}}},{"fquery":{"query":{"query_string":{"query":"\"' . $type . '\""}},"_cache":true}}]}}}}}}},';
    }
    $postContent = substr($postContent, 0, -1);
    $postContent .= '},"size":0}';
    
    $objHttp = new dwHttp();
    $ret = $objHttp->post($url, $postContent);
    $result = json_decode($ret, true);
    
    return $result;
}

/**
 * 检查api的状态
 */
function checkApiState($url, $prefix, $type, $filed, $specialApis) {
    // 设置20秒的延时，怕最新的数据还没入库
    $to = (int) (microtime(true) * 1000) - 20 * 1000;
    $from = $to - 310 * 1000;
    $queryUrls = getUrls($url, $prefix, $type, $filed, $from, $to);
    $curResult = getUrlStatisInfo($url, $prefix, $queryUrls, $type, $from, $to);
    
    $to = $from;
    $from = $to - 310 * 6 * 1000;
    $oldResult = getUrlStatisInfo($prefix, $filed, $queryUrls, $type, $from, $to);
    
    $minCount = 10;
    $errMsg = '';
    foreach ($curResult['facets'] as $queryUrl => $facet) {
        $mean = round($facet['mean'], 4);
        
        $specialDelay = $specialApis[$queryUrl];
        if ($specialDelay) {
            $maxDelay = $specialDelay;
        } else {
            $maxDelay = 0.8;
        }
         
        if ($facet['count'] >= $minCount) {
            if ($mean >= $maxDelay) {
                $errMsg .= "{$queryUrl}平均延时为{$mean},";
            }
            
            $oldFacet = $oldResult['facets'][$queryUrl];
            $oldMean = round($oldFacet['mean'], 4);
            $oldCount = (int) ($oldFacet['count'] / 6);
            if ($oldCount >= $minCount && $mean >= $maxDelay / 3) {
                if ($oldMean <= $mean / 2) {
                    $errMsg .= "{$queryUrl}平均延时由{$oldMean}飙升为{$mean},";
                }
                
                if ($facet['count'] > 3 * $oldCount && $facet['count'] > 100) {
                    $errMsg .= "{$queryUrl}访问次数由{$oldCount}飙升为{$facet['count']},";
                } else if ($facet['count'] < $oldCount / 3) {
                    $errMsg .= "{$queryUrl}访问次数由{$oldCount}陡降为{$facet['count']},";
                }
            }
        }
    }
    
    if ($errMsg) {
        $errMsg = "【接口访问】{$type}告警:{$errMsg}";
        YYms::reportServerWarning($errMsg);
        echo $errMsg;
    } else {
        $msg = "【正常】{$type}\n";
        echo $msg;
    }
     
}

// {"facets":{"stats_activetask":{"statistical":{"field":"message.delay"},"facet_filter":{"fquery":{"query":{"filtered":{"query":{"bool":{"should":[{"query_string":{"query":"message.url:\"activetask\" AND (*)"}}]}},"filter":{"bool":{"must":[{"range":{"@timestamp":{"from":1421737286477,"to":1421737586477}}},{"fquery":{"query":{"query_string":{"query":"\"yb_mobile_selfcall\""}},"_cache":true}}]}}}}}}}},"size":0}

