<?php
/**
 * 文件上传类
 *
 * 使用方法：
 * include('OjiaUpload.php');
 * $upload=new OjiaUpload;
 * 上传单个文件
 * $upload->uploadFile($_FILES['filename'],array('gif','doc'));
 * 上传多个文件
 * $upload->uploadFile($_FILES['filename'],array('gif','doc'));
 */

class OjiaUpload {
    /**
     * $allowExt  外部用限定文件类型array('gif','jpg')
     * $maxSize  初始设定允许文件大小为2M,以字节计算
     */
    public function uploadFile($FILES,$allowExt=array(),$maxSize=2097152){
        $length = count($FILES['name']);
        for($i=0;$i<$length;$i++){
            foreach($FILES as $key=>$data){
                if($length == 1)
                    $file[$i][$key] = $data;
                else
                    $file[$i][$key] = $data[$i];
            }
            $tempArr =pathinfo($file[$i]['name']);
            $file[$i]['ext'] = strtolower($tempArr["extension"]);
            if(!empty($allowExt) && (!in_array($file[$i]['ext'],$allowExt))) $file[$i]['error'] = 8;
            if($maxSize && $file[$i]['size'] > $maxSize) $file[$i]['error'] = 2;
            if($file[$i]['ext'] == 'gif'){
                $gif= file_get_contents($file[$i]["tmp_name"]);
                $rs = preg_match('/<\/?(script){1}>/i',$gif);
                if($rs) $file[$i]['error'] = 9;
            }
            if ($file[$i]['error'] != 0) {
                $file[$i]['errmsg']	= $this->_getErrMsg($file[$i]['error']);
            }
        }
        return $file;
    }

    // IPS存储
    public function bs2Upload($data, $bucket = BS2_FILE_BUCKET){
        $params = array();
        $params['accesskey'] = BS2_ACCESS_KEY;
        $params['access_key_secret'] = BS2_ACCESS_SECRET;
        $params['filename'] = $data['filename'];
        $params['localfile'] = $data['filepath'];
        $params['bucket'] = $bucket;
        $params['bs2host'] = BS2_HOST;
        $params['bs2dlhost'] = BS2_DL_HOST;
        $params['large'] = false;

        $parts = explode('.', $data['filename']);
        $postfix = end($parts);
        if ($postfix == 'jpg' || $postfix == 'jpeg') {
            $params['content-type'] = 'image/jpeg';
        } else if($postfix == 'png') {
            $params['content-type'] = 'image/png';
        } else if ($postfix == 'mp3') {
            $params['content-type'] = 'audio/mp3';
        } else if ($postfix == 'mp4') {
            $params['content-type'] = 'video/mpeg4';
        }

        $bs2upload = new Bs2upload($params);
        if ($bs2upload->uploadFile()) {
            if ($params['bucket'] == BS2_FILE_BUCKET) {
                return $bs2upload->getImgUrl();
            } else {
                return $bs2upload->getDownloadUrl(false);
            }
        } else {
            return false;
        }
    }

    private function _getErrMsg($code) {
        switch($code){
            case '1':
                $error = '超过php.ini允许的大小。';
                break;
            case '2':
                $error = '超过表单允许的大小。';
                break;
            case '3':
                $error = '图片只有部分被上传。';
                break;
            case '4':
                $error = '请选择图片。';
                break;
            case '6':
                $error = '找不到临时目录。';
                break;
            case '7':
                $error = '写文件到硬盘出错。';
                break;
            case '8':
                $error = 'File upload stopped by extension。';
                break;
            case '999':
            default:
                $error = '未知错误。';
        }

        return $error;
    }
}
