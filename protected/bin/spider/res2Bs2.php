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
ini_set("memory_limit","1G");
error_reporting(E_ALL & ~E_NOTICE);

require_once realpath(dirname(__FILE__)) . '/../../common.php';
require_once ROOT_PATH . '/bin/common_script.php';

$procIndex = $argv[1] ?: 0;
//需要开启子进程个数，默认开启两个子进程
$procNum = $argv[2] ?: 2;
// 这个脚本只能单进程进行
$pidFile = BASE_DIR . "/protected/bin/run/res2Bs2Worker2_{$procIndex}.pid";
$flag = singleProcess(getCurrentCommand(), $pidFile);
if (!$flag) {
    exit("Sorry, this script file has already been running ...,pid:{$pidFile}\n");
}

const TASK_SIZE = 20;
const TASK_MAX_SIZE = 100;

_log("start script procIndex:{$procIndex}, procNum:{$procNum}");
if ($procIndex == 0) {
    // Master进程
    runMaster($procNum);
} else {
    // Worker进程
    runWorker($procIndex);
}

/**
 * @TODO 每次判断子进程，优先给的任务数（尝试次数小于10的任务）较少的子进程填充数据
 * 如果子进程的任务数，达到了最大值则停止填充
 *
 * 如果有数据填充，每次填充完休息5秒
 * 如果没数据填充，每次检测完，休息60秒
 *
 * @param $procNum
 */
function runMaster($procNum) {
    $objSaveData = new TableHelper('saveas_data', 'crawl');
    while (true) {
        // Master进程
        //找出需要转存个数最少并且按照尝试次数（大于10）最少的来排序，这里的 num 指的是，总的任务数再减去其中已经尝试超过 10 次的次数，为了防止子任务内都是超过 10 次转存次数，导致 master 无法分配给子进程任务
        $sql = "SELECT update_time, COUNT(saveas_data_id)- SUM(try_num > 10) AS num, SUM(try_num > 10) as proc_try10_count  FROM saveas_data
                WHERE update_time <= {$procNum} AND update_time >= 0 GROUP BY update_time ORDER BY num, proc_try10_count ASC";
        $numMap = $objSaveData->getDb()->getAll($sql);
        $numMap = arrayFormatKey($numMap, 'update_time', 'num');

        for ($i = 0; $i <= $procNum; $i++) {
            $numMap[$i] = $numMap[$i] ?: 0;
        }
        
        if ($numMap[0] <= 0) {
            sleep(60);
        } else {
            $needAdd = [];
            // 没有超过最大值的进程才增加任务
            foreach ($numMap as $procNO => $num) {
                if ($procNO > 0 && $num < TASK_MAX_SIZE) {
                    $needAdd[] = $procNO;
                }
            }

            //$needAdd 已经排序，越少任务还有尝试次数越少的排在越前面
            _log("find new data num:{$numMap[0]}");

            //只处理 update_time=0 的记录
            $where = ['update_time' => 0];
            $keyWord = ['_limit' => TASK_SIZE * count($needAdd), '_sortKey' => '`create_time` DESC, try_num ASC'];
            $all_save_data = $objSaveData->getAll($where, $keyWord);

            // 先分成多个数组，然后每个数组批量插入
            $all_save_data = array_chunk($all_save_data, TASK_SIZE);
            for ($i = 0; $i < count($all_save_data); $i++) {
                $datas = $all_save_data[$i];
                $saveas_data_ids = array_column($datas, 'saveas_data_id');

                $procNO = $needAdd[$i];
                //修改 update_time 标识属于哪个子进程
                $objSaveData->updateObject(['update_time' => $procNO], ['saveas_data_id' => $saveas_data_ids]);
                _log("fill saveas_data. procNO:{$procNO}, count:" . count($saveas_data_ids));
            }

            sleep(5);
        }
    }
}

/**
 * @TODO  每次取20个数据出来处理，数据按尝试次数降序
 * 尝试次数 大于 5000次的数据，不做处理
 *
 * 如果有数据，每次处理完休息3秒
 * 如果没数据每次检测完，休息10秒
 * @param $procIndex
 */
function runWorker($procIndex) {
    if (!$procIndex) {
        return;
    }

    $objSaveData = new TableHelper('saveas_data', 'crawl');
    //只处理 update_time=$procIndex 的记录，并且按最新需要转存的数据和尝试次数排序，而且尝试次数少于5000
    $_where = ['update_time' => $procIndex];
    $_keyWord = ['_where' => 'try_num < 5000', '_sortKey' => '`create_time` DESC, try_num ASC'];

    //获取所有数据库配置
    $objDataDb = new TableHelper('data_db', 'crawl');
    $all_data_db = $objDataDb->getAll();
    $all_data_db = arrayFormatKey($all_data_db, 'db_id');

    $all_save_data = $objSaveData->getAll($_where, $_keyWord);
    foreach ($all_save_data as $key => $save_data) {
        $save_db_id = $save_data['db_id'];
        if (!$all_data_db[$save_db_id]) {
            _log("找不到db_id:{$save_db_id}");
            //不再转存
            _updateTime($save_data['saveas_data_id'], -1);
            continue;
        }

        $tableName = $all_data_db[$save_db_id]['table_name'];
        $dbKey = $all_data_db[$save_db_id]['db_name'];
        //获取此转存字段数据
        $whereStr = $save_data['key_value'];
        $where = json_decode($save_data['key_value'], true);
        if (empty($where)) {
            _log("找不到where:{$whereStr}");
            //不再转存
            _updateTime($save_data['saveas_data_id'], -1);
            continue;
        }

        $objTable = new TableHelper($tableName, $dbKey);
        $valueName = $save_data['field_name'];
        $data = $objTable->getRow($where);
        if (!$data) {
            _log("找不到数据，dbKey:{$dbKey}, tableName:{$tableName}, where:{$save_data['key_value']}");
            //不再转存
            _updateTime($save_data['saveas_data_id'], -1);
            continue;
        }

        $src = trim($data[$valueName]);
        if ($save_data['save_as'] == 1) {
            _log("单文件：{$dbKey}.{$tableName}:{$valueName} whereStr:{$whereStr}");
            if (strrpos($src, 'dwstatic.com') !== false) {
                _log("跳过资源：{$src}");
                //不再转存
                _updateTime($save_data['saveas_data_id'], -1);
                continue;
            }

            //资源为单文件
            $newSrc = downloadOne($src, $whereStr, $save_data['save_as_referer']);
            if ($newSrc !== false) {
                $newData = [$valueName => $newSrc];
                $objTable->updateObject($newData, $where);
                //不再转存
                _updateTime($save_data['saveas_data_id'], -1);
                _log("单文件转存资源：{$dbKey}.{$tableName}, whereStr:{$whereStr}, 资源更新完成");
            } else {
                //失败后，增加尝试次数
                _incTryNum($save_data['saveas_data_id']);
            }
        } else if ($save_data['save_as'] == 2) {
            _log("数组：{$dbKey}.{$tableName}:{$valueName}, whereStr:{$whereStr}");
            //资源为数组
            $img_list = json_decode($src, true);
            $flag = true;
            $try_num = $save_data['try_num'];
            $img_list2 = downloadMulti($img_list, $whereStr, $save_data['save_as_referer'], $flag, $try_num);
            if ($img_list2 && count($img_list2)) {
                $newSrc = json_encode($img_list2);
            } else {
                $newSrc = '';
            }

            if ($newSrc != $src) {
                $newData = [$valueName => $newSrc];
                $objTable->updateObject($newData, $where);
            } else {
                _incTryNum($save_data['saveas_data_id']);
                _log("数组没改变，不需要转存资源");
            }

            if ($flag) {
                //不再转存，如果转存中没有出错，或者已经超过5000次出错
                _updateTime($save_data['saveas_data_id'], -1);
                _log("数组转存资源成功");
            }  else {
                //失败后，增加尝试次数
                _incTryNum($save_data['saveas_data_id']);
            }
        } else if ($save_data['save_as'] == 3) {
            _log("富文本：{$dbKey}.{$tableName}:{$valueName}, whereStr:{$whereStr}");
            //资源为富文本
            $img_match = downTextImg($src);
            $img_list = $img_match[1];
            if (empty($img_list)) {
                //不再转存
                _updateTime($save_data['saveas_data_id'], -1);
                _log("富文本没有图片，不需要转存，continue");
                continue;
            }

            $replace_img = [];
            $flag = true;
            foreach ($img_list as $img) {
                if (strrpos($img, 'dwstatic.com') == false) {
                    $bs2_img = downloadOne($img, $whereStr, $save_data['save_as_referer']);
                    if ($bs2_img === false) {
                        $flag = false;
                    } else if ($bs2_img === '') {
                        _log("continue:" . $img);
                        // 要删除的文件
                        continue;
                    } else {
                        $replace_img[$img] = $bs2_img;
                    }
                }
            }

            if (!empty($replace_img)) {
                foreach ($replace_img as $key => $value) {
                    $src = str_replace($key, $value, $src);
                }

                $newData = [$valueName => $src];
                $objTable->updateObject($newData, $where);
            }

            //更新成功并且是否所有转存成功
            if ($flag) {
                _updateTime($save_data['saveas_data_id'], -1);
                _log("富文本转存资源成功");
            } else {
                //失败后，增加尝试次数
                _incTryNum($save_data['saveas_data_id']);
                _log("富文本转存资源失败，flag:{$flag}");
            }
        }
    }
}

function _updateTime($saveas_data_id, $update_time = 0) {
    $objSaveData = new TableHelper('saveas_data', 'crawl');
    if ($update_time != 0) {
        $update_time = strtotime(date('Y-m-d H:i:s'));
    }

    $objSaveData->updateObject(['update_time' => $update_time], ['saveas_data_id' => $saveas_data_id]);
}

function _incTryNum($saveas_data_id) {
    $objSaveData = new TableHelper('saveas_data', 'crawl');
    $save_data = $objSaveData->getRow(['saveas_data_id' => $saveas_data_id]);
    if ($save_data) {
        $objSaveData->updateObject(['try_num' => $save_data['try_num'] + 1], ['saveas_data_id' => $saveas_data_id]);
    }
}

function downloadMulti($img_list, $whereStr, $referer, &$flag = true, $try_num = 0) {
    $img_list2 = [];
    $is_assoc = is_assoc($img_list);
    foreach ($img_list as $k => $img) {
        if (is_array($img)) {
            $val = downloadMulti($img, $whereStr, $referer, $flag, $try_num);
        } else {
            $val = downloadOne($img, $whereStr, $referer);
            if ($val === false) {
                //如果 try_num 超过5000，则删除这个链接，不算转存失败
                if ($try_num < 5000) {
                    $flag = false;
                    $val = $img;
                }
            } else if ($val === '') {
                _log("continue:" . $img);
                // 要删除的文件
                continue;
            }
        }

        if (!empty($val)) {
            if ($is_assoc) {
                $img_list2[$k] = $val;
            } else {
                $img_list2[] = $val;
            }
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
            _log("skig src:$src");
            return $src;
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