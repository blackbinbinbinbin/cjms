<?php
class Bs2UploadHelper {
    private static $_savePath = '/tmp/tmpfile/' . TODAY . '/';

    /**
     * 上传本地小文件到Bs2
     * @param $data ['filename', 'filepath', 'bucket', 'content-type']
     * @param bool $delSource
     * @return bool|string
     */
    public static function bs2Upload($data, $delSource = false){
        $params = [];
        $params['accesskey'] = BS2_ACCESS_KEY;
        $params['access_key_secret'] = BS2_ACCESS_SECRET;
        $params['filename'] = $data['filename'];
        $params['localfile'] = $data['filepath'];
        $params['bucket'] = $data['bucket'] ? $data['bucket'] : BS2_SNS_BUCKET;
        $params['bs2host'] = BS2_HOST;
        $params['bs2dlhost'] = BS2_DL_HOST;
        $params['large'] = false;
        $data['content-type'] && $params['content-type'] = $data['content-type'];

        $bs2upload = new Bs2upload($params);
        if ($bs2upload->uploadFile()) {
            if ($delSource) {
                unlink($data['filepath']);
            }

            if ($params['bucket'] == BS2_FILE_BUCKET || $params['bucket'] == BS2_SNS_BUCKET) {
                return $bs2upload->getImgUrl();
            } else {
                return $bs2upload->getDownloadUrl(false);
            }
        } else {
            return false;
        }
    }

    /**
     * 上传本地大文件到Bs2
     * @param $data ['filename', 'filepath', 'bucket', 'content-type']
     * @return bool|string
     */
    public static function uploadLargeFile($data) {
        $params = [];
        $params['accesskey'] = BS2_ACCESS_KEY;
        $params['access_key_secret'] = BS2_ACCESS_SECRET;
        $params['filename'] = $data['filename'];
        $params['localfile'] = $data['filepath'];
        $params['bucket'] = $data['bucket'] ?: BS2_LARGE_FILE_BUCKET;
        $params['bs2host'] = BS2_HOST;
        $params['bs2dlhost'] = BS2_DL_HOST;
        $params['large'] = true;
        $data['content-type'] && $params['content-type'] = $data['content-type'];

        $bs2upload = new Bs2upload($params);
        if ($bs2upload->uploadsFile()) {
            return $bs2upload->getDownloadUrl();
        } else {
            return false;
        }
    }

    /**
     * 下载大文件到本地，并上传到Bs2
     * @param $url
     * @param bool $isLarge 是否是大文件
     * @param bool $delSource
     * @return array|bool
     */
    public static function uploadFileFromUrl($url, $isLarge = false, $bucket = '', $delSource = false) {
        list($filepath, $filename, $info, $diskCache) = self::saveFileFromUrl($url);
        if (!$filepath) {
            return false;
        }

        $extension = $info['extension'] ? $info['extension'] : 'jpg';
        $newFileName = md5($url) . "-{$info['fileSize']}.{$extension}";

        $objLogic = dwRedis::init('logic');
        $redisKey = 'global:uploadFileFromUrl_map';
        $bs2Cache = false;
        $url = $objLogic->hGet($redisKey, $newFileName);
        if ($url) {
            $bs2Cache = true;
            if ($delSource) {
                unlink($filepath);
            }
            return [$url, $info, $diskCache, $bs2Cache];
        }

        if (!$bucket) {
            $bucket = $isLarge ? BS2_LARGE_FILE_BUCKET : BS2_FILE_BUCKET;
        }

        $data = [
            'bucket' => $bucket,
            'filename' => $newFileName,
            'filepath' => $filepath,
        ];
        $info['mime'] && $data['content-type'] = $info['mime'];

        if ($isLarge) {
            $url = self::uploadLargeFile($data);
        } else {
            $url = self::bs2Upload($data, $delSource);
        }

        if ($url) {
            $objLogic->hSet($redisKey, $newFileName, $url);
            $objLogic->expire($redisKey, 86400 * 90);
        }

        $info['filepath'] = $filepath;
        return [$url, $info, $diskCache, $bs2Cache];
    }


    /**
     * 下载图片，并上传图片信息到Bs2
     * @param $url
     * @param bool $delSource
     * @return array|bool
     */
    public static function uploadFromUrl($url, $delSource = false, $referer = null) {
        list($filepath, $filename, $info, $diskCache) = self::saveFromUrl($url, $referer);
        if (!$filepath) {
            return false;
        }

        $extension = $info['extension'] ? $info['extension'] : 'jpg';
        $newFileName = md5($url) . "-{$info[0]}x{$info[1]}.{$extension}";

        $objLogic = dwRedis::init('logic');
        $redisKey = 'global:uploadFromUrl_map';
        $bs2Cache = false;
        $url = $objLogic->hGet($redisKey, $newFileName);
        if ($url) {
            $bs2Cache = true;
            if ($delSource) {
                unlink($filepath);
            }
            return [$url, $info, $diskCache, $bs2Cache];
        }

        $data = [
            'bucket' => BS2_SNS_BUCKET,
            'filename' => $newFileName,
            'filepath' => $filepath,
        ];
        $info['mime'] && $data['content-type'] = $info['mime'];

        $url = self::bs2Upload($data, $delSource);
        if ($url) {
            $objLogic->hSet($redisKey, $newFileName, $url);
            $objLogic->expire($redisKey, 86400 * 90);
        }

        return [$url, $info, $diskCache, $bs2Cache];
    }

    /**
     * 获取头部信息
     * @param string $url
     * @return array
     */
    private static function _getUrlHeaders($url) {
        $ch = curl_init($url);
        curl_setopt( $ch, CURLOPT_NOBODY, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
        curl_setopt( $ch, CURLOPT_HEADER, false );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 3 );
        curl_exec( $ch );
        $headers = curl_getinfo( $ch );
        curl_close( $ch );

        return $headers;
    }

    /**
     * 下载图片文件到本地
     * @param $url
     * @return bool | array
     */
    public static function saveFromUrl($url, $referer = null) {
        $result = self::saveFileFromUrl($url, 10, $referer);
        if (!$result) {
            return $result;
        }

        $path = $result[0];
        $filename = $result[1];
        $info = $result[2];
        $diskCache = $result[3];

        if ($info['extension'] != 'mp4') {
            // 视频不需要获取宽高
            $handle = fopen($path, 'rb');
            $data = fread($handle, 4);
            fclose($handle);

            if ($data == 'RIFF') {
                $filename .= "{$filename}.jpg";
                $_p = self::$_savePath;
                $newPath = "{$_p}{$filename}";

                exec("dwebp {$path} -o {$newPath}");
                if (file_exists($newPath)) {
                    $path = $newPath;
                }

                $im = imagecreatefrompng($path);
                imagejpeg($im, $path, 75);
                imagedestroy($im);
            }

            $info2 = getimagesize($path);
            $info = array_merge($info2, $info);
        }

        return [$path, $filename, $info, $diskCache];
    }

    public static function getUrlExtension($url) {
        $parts = explode('?', $url);
        $parts = explode('#', $parts[0]);
        $parts = explode('/', $parts[0]);
        // 有些地址是：http://img.cdn.funqgame.com/shumen/20180329/2plekaam.jpg@!p_w800
        $parts = explode('@', end($parts));
        $parts = explode('.', $parts[0]);
        if (count($parts) > 1) {
            return trim(end($parts));
        } else {
            return '';
        }
    }

    public static function getOnlineFileType($url, $referer = null, $timeout = 5) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        if ($referer) {
            curl_setopt ($ch, CURLOPT_REFERER, $referer);
        }

        $results = explode("\n", trim(curl_exec($ch)));
        foreach($results as $line) {
            if (strtok($line, ':') == 'Content-Type') {
                $parts = explode(":", $line);

                $map = [
                    'image/gif'	=> 'gif',
                    'image/jpeg' => 'jpg',
                    'image/pjpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/x-png' => 'png',

                    'video/mp4' => 'mp4',
                    'video/avi' => 'avi',
                    'video/msvideo' => 'avi',
                    'video/x-msvideo' => 'avi',
                    'video/x-troff-msvideo' => 'avi',
                ];

                $mime = strtolower(trim($parts[1]));
                return $map[$mime];
            }
        }

        return null;
    }

    private static function getFileType($contentType) {
        $map = [
            'image/gif' => 'gif',
            'image/jpeg' => 'jpg',
            'application/x-jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',

            'video/mpeg4' => 'mp4',
            'video/mpg' => 'mpeg',
        ];

        return $map[$contentType];
    }

    /**
     * 从网上下载文件
     * @param string $url 源文件地址
     * @param int $timeout 超时时间（秒）
     * @return bool | array
     */
    public static function saveFileFromUrl($url, $timeout = 10, $referer = null) {
        $extension = self::getUrlExtension($url);
        $filename = md5($url);
        if ($extension) {
            $filename .= ".{$extension}";
        }
//        Tool::log($extension);

        $_p = self::$_savePath;
        if (!file_exists($_p)) {
            $flag = mkdir($_p, 0777, true);
            if (!$flag) {
                Tool::err("can not mkdir:{$_p}");
                return false;
            }
        }
        $path = "{$_p}{$filename}";
        $headerPath = "{$path}_detail";

        $fileSize = filesize($path);
        $diskCache = file_exists($path) && $fileSize > 0;
        if (!$diskCache) {
            $wgetshell = "wget -O {$path} -o $headerPath '{$url}' --no-check-certificate --timeout={$timeout}";
            if ($referer) {
                $wgetshell .= " --referer='{$referer}'";
            }
//            Tool::log($wgetshell);
            $ret = shell_exec($wgetshell);
//            Tool::log($ret);
        }

        $headerStr = file_get_contents($headerPath);
        $contentType = '';
        if ($headerStr) {
            $flag = preg_match("/Length:[^\[]+\[([^\}]+)\]/", $headerStr, $matches);
            if ($flag) {
                $contentType = $matches[1];
                $extension2 = self::getFileType($contentType);
                $extension = $extension2 ? $extension2 : $extension;
            }
        }

        $info2 = [];
        $info2['filename'] = $filename;
        $info2['extension'] = $extension ? $extension : 'jpg';
        $info2['contentType'] = $contentType;
        $info2['fileSize'] = $fileSize;
        return [$path, $filename, $info2, $diskCache];
    }

    /**
     * 获取视频文件长度
     * @author solu
     * @param  string $filepath 文件路径
     * @return int              视频长度（毫秒）
     */
    public static function getVideoLength($filepath) {
        $length = 0;

        $echo = exec("ffmpeg -i {$filepath} 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");
        $arrTime = explode(':', $echo);
        if ($arrTime) {
            $length = ($arrTime[0] * 3600 + $arrTime[1] * 60 + $arrTime[2]) * 1000;            
        }

        return $length;
    }

    public static function fileTypeMap($mimeType) {
        $arrMime = explode('/', $mimeType);

        return in_array($arrMime[0], ['video', 'audio', 'image']) ? $arrMime[0] : 'other';
    }

    /**
     * 更新视频url生成第一帧截图
     * @author solu
     * @param  string $videoUrl  视频url
     * @param  string $size      图片尺寸 [弃用，取视频原分辨率]
     * @return string            截图url
     */
    public static function genCoverFromVideoUrl($videoUrl, $size = '800x450') {
        list($filepath, $filename) = self::saveFromUrl($videoUrl);
        if (!$filepath) {
            return false;
        }

        $info = pathinfo($filename);
        $imageName = $info['filename'] . '.jpg';
        $imagePath = self::$_savePath . $imageName;
        exec("ffmpeg -ss 00:00:00 -i '{$filepath}' -y -f image2 -vframes 1 {$imagePath} 1>/dev/null 2>/dev/null");

        unlink($filepath);
        if(!filesize($imagePath)) {
            return false;
        }

        $data = [
            'bucket' => BS2_FILE_BUCKET,
            'filename' => $imageName,
            'filepath' => $imagePath,
        ];

        return self::bs2Upload($data, true);
    }


}