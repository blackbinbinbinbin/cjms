<?php

require_once '../common.php';

//$url = 'http://imtt.dd.qq.com/16891/C78453EB1CCF6B69FF166E4214CF8712.apk?fsname=com.qq.reader_6.5.1.888_99.apk&csr=1bbd';
$url = 'http://ojiaauthorvideos.bs2dl.yy.com/app-hiyd-release-2.4.3.apk';
$ret = Bs2UploadHelper::uploadFileFromUrl($url, true);
var_dump($ret);

