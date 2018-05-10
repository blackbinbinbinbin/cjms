<?php

/**
 * 后台的公共服务
 * @author hawklim
 */
class ServiceController extends BaseController {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 图片上传(jqueryFileUpload)
     * @author hawklim
     */
    function actionUploadImg() {
    	$params = array(
    		'server_script' => '/service/uploadImg',
    		'bucket' => BS2_FILE_BUCKET,
            // 'max_width' =>  640,
            // 'max_heigth' => 480,
    	);
        $upload = new UploadHandler($params); 
    }

    /**
     * 上传音视频(jqueryFileUpload)
     * @author hawklim
     */
    function actionUploadAV() {
    	$params = array(
    		'inline_file_types' => '/\.(mp3|mp4|wmv|flv|swf)$/i',
    		'server_script' => '/service/uploadAv',
    		'bucket' => BS2_VIDEO_BUCKET,
    	);
    	$upload = new UploadHandler($params); 
    }

    /**
     * 上传Apk的方法
     * @author benzhan
     */
    function actionUploadApk($args) {
        $params = array(
            'inline_file_types' => '/\.(apk)$/i',
            'server_script' => '/service/UploadApk',
            'bucket' => BS2_LARGE_FILE_BUCKET,
        );
        $upload = new UploadHandler($params);
    }

   
}
