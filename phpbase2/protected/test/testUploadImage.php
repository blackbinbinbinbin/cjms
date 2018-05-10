<?php

require_once '../common.php';

$oldSrc = 'http://img.mp.itc.cn/upload/20170210/23ff60fd9c284163bec4138111d2889c_th.jpg';
list($newSrc, $info, $diskCache, $bs2Cache) = Bs2UploadHelper::uploadFromUrl($oldSrc);

var_dump("diskCache:{$diskCache}, bs2Cache:{$bs2Cache} —— old:{$oldSrc}, new:{$newSrc}", $info);
exit;
