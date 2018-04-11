<?php
/**
 * 资源下载Worker进程
 * User: Ben
 * Date: 2018/1/6
 * Time: 14:01
 */
$startTime = time();
echo $startTime."脚本开始\n";
$st = microtime(true);
ini_set("display_errors", "On");
error_reporting(E_ALL & ~E_NOTICE);

require_once realpath(dirname(__FILE__)) . '/../../common.php';
require_once ROOT_PATH . '/bin/common_script.php';

$procIndex = $argv[1] ?: 0;
// 这个脚本只能单进程进行
$pidFile = BASE_DIR . "/protected/bin/run/res2Bs2Worker_{$procIndex}.pid";
$flag = singleProcess(getCurrentCommand(), $pidFile);
if (!$flag) {
    exit("Sorry, this script file has already been running ...,pid:{$pidFile}\n");
}

while(true) {
    $objRedis = dwRedis::init('logic');
    $json = $objRedis->sPop('globals:res2Bs2_list');
    if ($json) {
        _log($json);
        $data = json_decode($json, true);
        $prekey = "globals:res2Bs2:";
        $key = $prekey . join('|', $data);
        $objRedis->setex($key, 3600, date('Y-m-d H:i:s'));

        updateTableSrc($data, $objRedis, $key);

        $objRedis->del($key);
    } else {
        sleep(10);
    }
}

$currentName = "";
function updateTableSrc($data, dwRedis $objRedis, $key) {
    global $currentName;
    $dbKey = $data['dbName'];
    $tableName = $data['tableName'];
    $valueName = $data['fieldName'];
    $update_time_field = $data['update_time_field'];
    $currentName = "{$dbKey}.{$tableName}:{$valueName}";

    $objTable = new TableHelper($tableName, $dbKey);
    $keyName = explode(',', $data['keyName']);
    foreach ($keyName as $i => $part) {
        $keyName[$i] = trim($part);
    }

    $keyWord = arrayFilter($data, ['_where', '_limit']);
    $keyWord['_field'] = '`' . join("`, `", $keyName) . "`, `{$valueName}`";
    $datas = $objTable->getAll([], $keyWord);

    _log("_limit {$data['_limit']} has:" . count($datas));
    if (!$datas) {
        return false;
    }

    foreach ($datas as $k => $value) {
        $newSrc = false;
        $diskCache = false;
        $bs2Cache = false;
        $src = trim($value[$valueName]);
        if (!$src) {
            continue;
        }

        $where = arrayFilter($value, $keyName);
        $whereStr = json_encode($where);
//        _log("{$whereStr}开始，is_array:" . $data['is_array']);
        if ($data['is_array']) { //资源列表数据
            $img_list = json_decode($src, true);
            $img_list2 = downloadMulti($img_list, $whereStr, $data['save_as_referer']);
            if (count($img_list2)) {
                $newSrc = json_encode($img_list2);
            } else {
                $newSrc = '';
            }

            if ($newSrc != $src) {
                $newData = [$valueName => $newSrc];
                if ($update_time_field) {
                    $newData[$update_time_field] = date('Y-m-d H:i:s');
                }

                $objTable->updateObject($newData, $where);
                $oldCount = count($img_list);
                $newCount = count($img_list2);
                _log("{$whereStr}, 游戏资源更新完成, oldCount:{$oldCount}, newCount:{$newCount}");
            }
        } else if ($data['is_text']) {
            //富文本内 img 标签替换
            $img_match = downTextImg($src);
            $img_list = $img_match[1];
            $need_update = false;
            $newCount = 0;
            $replace_img = [];

            foreach ($img_list as $img) {
                if (strrpos($img, 'dwstatic.com') == false) {
                    $need_update = true;
                    $bs2_img = downloadOne($img, $whereStr, $data['save_as_referer']);
                    if ($bs2_img) {
                        $newCount++;
                        $replace_img[$img] = $bs2_img;
                        // $src = str_replace($img, $bs2_img, $src);
                    }
                }
            }
            
            if ($need_update && !empty($replace_img)) {
                foreach ($replace_img as $key => $value) {
                    $src = str_replace($key, $value, $src);
                }

                $newData = [$valueName => $src];
                if ($update_time_field) {
                    $newData[$update_time_field] = date('Y-m-d H:i:s');
                }

                $objTable->updateObject($newData, $where);
                $oldCount = count($img_list);
                _log("{$whereStr}, 游戏资源(富文本)更新完成, oldCount:{$oldCount}, newCount:{$newCount}");
            }
        } else {
            $newSrc = downloadOne($src, $whereStr, $data['save_as_referer']);
            if ($newSrc !== false) {
                $newData = [$valueName => $newSrc];
                if ($update_time_field) {
                    $newData[$update_time_field] = $update_time_field;
                }

                $objTable->updateObject($newData, $where);
            }
        }

        // 延长时间
        $objRedis->expire($key, 3600);
    }

    return count($datas);
}

function downloadMulti($img_list, $whereStr, $referer) {
    $img_list2 = [];
    $is_assoc = is_assoc($img_list);
    foreach ($img_list as $k => $img) {
        if (is_array($img)) {
            $val = downloadMulti($img, $whereStr, $referer);
        } else {
            $val = downloadOne($img, $whereStr, $referer);
            if ($val === false) {
                $val = $img;
            } else if ($val === '') {
                _log("continue:" . $img);
                // 要删除的文件
                continue;
            } else {
                _log("get new image:" . $val);
            }
        }

        if ($is_assoc) {
            $img_list2[$k] = $val;
        } else {
            $img_list2[] = $val;
        }
    }

    return $img_list2;
}

function grabOneVideo($video_url, $whereStr) {
    global $currentName;

    $objTable = new TableHelper('video_save_log', 'crawl');
    $where = compact('video_url');
    $row = $objTable->getRow($where);

    // -------------------  临时兼容代码 ------------------------
    $newUrl = 'http://cdn.edgecast.steamstatic.com/steam/apps/';
    $oldUrl = 'http://cdn.steamstatic.com.8686c.com/steam/apps/';
    $video_url2 = false;
    if (strpos($video_url, $newUrl) !== false) {
        $video_url2 = str_replace($newUrl, $oldUrl, $video_url);
    } else if (strpos($video_url, $oldUrl) !== false) {
        $video_url2 = str_replace($oldUrl, $newUrl, $video_url);
    }

    if ($video_url2) {
        $where = ['video_url' => $video_url2];
        $row2 = $objTable->getRow($where);
        if ($row2['state'] === 'video_update') {
            _log("video_url:{$video_url}, video_url2:{$video_url2}, vid:{$row2['vid']}");
            return $row2['vid'];
        }
    }
    // ----------------------------------------------------------

    $url_md5 = md5($video_url);
    if ($row['state'] === 'video_update') {
        return $row['vid'];
    } else if ($row['state']) {
        return false;
    } else if (!$row) {
        // 先判断正在处理的视频是否超过上限
        $update_time = date('Y-m-d H:i:s', time() - 600);
        $sql = "SELECT COUNT(*) FROM `video_save_log` 
            WHERE `update_time` > '{$update_time}' AND `state` = 'video_check'";
        $count = $objTable->getDb()->getOne($sql);
        if ($count > 10) {
            _log("视频处理达到上限了..., where:{$whereStr}, video_url:{$video_url}");
            return false;
        }

        // 添加新的视频处理任务
        $create_time = $update_time = date('Y-m-d H:i:s');
        $state = '';
        $title = $currentName . ':' . $whereStr;
        $data = compact('video_url', 'url_md5', 'title', 'state', 'create_time', 'update_time');
        $objTable->addObject($data);
    }

    $api  = "http://grab-v.duowan.com/api/grabOneVideo";
    $objHttp = new dwHttp();
    $param = [
        'url' => 'http://' . CJMS_HOST_FORM . '/spider/videoInfo?url_md5=' . $url_md5,
        'channel' => 'spidervideo',
        'udb' => 'dw_zhanchaojiang',
    ];
    $jsonData = json_encode($param);
    $appKey = 'duowan~!@#$%^&*';
    $sign = md5($jsonData . $appKey);
//    $ret = '';
    $ret = $objHttp->post2($api, compact('jsonData', 'sign'));
    $result = json_decode($ret, true);
    if ($result['rs']) {
        $url_task_id = $result['data']['urlTaskId'];
        $vid = $result['data']['vid'];
        $state = 'video_check';
        $newData = compact('url_task_id', 'vid', 'state');
        $objTable->updateObject($newData, $where);
    }

    return false;
}

function downloadOne($src, $whereStr, $referer) {
    // 转化为vid的视频，跳过
    if (is_numeric($src)) {
        return false;
    }

    // 修复爬虫的错误链接问题
    $src = str_replace('http:http://', 'http://', $src);

    $extension = Bs2UploadHelper::getUrlExtension($src);
    if (!$extension) {
        $extension = Bs2UploadHelper::getOnlineFileType($src, $referer);
        if (!$extension && is_404($src)) {
            _log("url is 404, {$whereStr}, remove it:{$src}");
            return '';
        } else {
            _log("can not find extension. default:jpg");
            $extension = 'jpg';
        }
    }

    $extension = strtolower($extension);
    if ($extension == 'mp4') {
        $objRedis = dwRedis::init('logic');

        // 检查回调成功列表
        $datetime = $objRedis->hGet('globals:video_save_doing', $src);
        if ($datetime && (time() - strtotime($datetime) > 86400)) {
            $objRedis->hDel('globals:video_save_doing', $src);
            if (is_404($src)) {
                // 404链接需要删除
                _log("url is 404, {$whereStr}, remove it:{$src}");
                return '';
            } else {
                // 再次添加到转存列表
//                    $objRedis->sAdd('globals:video_save_todo', $src);
                $vid = grabOneVideo($src, $whereStr);
            }
        } else {
            // 添加到转存列表
//                $objRedis->sAdd('globals:video_save_todo', $src);
            $vid = grabOneVideo($src, $whereStr);
        }

        if ($vid) {
            return $vid;
        }

        return false;
    } else if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif') {
        if (strpos($src, 'http://screenshot.dwstatic.com/') !== false) {
            //        _log("skig src:$src");
            return false;
        }

        if (strpos($src, '/%CDN_HOST_MEDIA%/') > 0) {
            _log("error url, {$whereStr}, remove it:{$src}");
            return '';
        }

        list($newSrc, $info, $diskCache, $bs2Cache)  = Bs2UploadHelper::uploadFromUrl($src, true, $referer);
        if ($newSrc) {
            _log("success, {$whereStr}. $src => $newSrc");
            return $newSrc;
        } else {
            _log("load failed, {$whereStr}: $src, info:" . json_decode($info));
            if (is_404($src)) {
                _log("url is 404, {$whereStr}, remove it:{$src}");
                return '';
            }
        }
    } else {
        _log("Unknown extension:{$extension}， src:{$src}");
    }

    return false;
}

function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function is_404($url) {
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);//设置超时时间
    curl_exec($handle);
    //检查是否404（网页找不到）
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    return $httpCode == 404 || $httpCode == 403;
}

// 记录日志
function _log($log) {
    $time = date('Y-m-d H:i:s');
    var_dump("【{$time}】{$log}");
}

//检查富文本img标签
function downTextImg($text) {
    if (preg_match_all ( "/<img[^>]*src[=\"\'\s]+([^\"\']*)[\"\']?[^>]*>/i", $text, $img_match )) {
        if (!empty($img_match) && count($img_match[1]) > 0) {
            return $img_match;
        }
    }
}