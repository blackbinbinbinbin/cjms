<?php

/**
 * 公共服务
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
        $data = $upload->get_response();

        Response::exitData($data);
    }

    /**
     * 图片上传(jqueryFileUpload)
     * @author hawklim
     */
    function actionUploadImg2($args) {
        $params = array(
            'server_script' => '/service/uploadImg',
            'bucket' => BS2_FILE_BUCKET,
        );
        $params = array_merge($params, $args);
        $upload = new UploadHandler($params);
        $data = $upload->get_response();

        Response::exitData($data);
    }

    /**
     * 后台回帖用
     */
    public function actionSnsUploadImg() {
        $params = array(
            'server_script' => '/service/snsUploadImg',
            'bucket' => BS2_SNS_BUCKET,
            'accept_file_types' => '/\.(jpg|png|gif)$/i',
            'max_ratio' => 12, //宽高最大值比最小值比例 12:1
        );

        $upload = new UploadHandler($params);
        $data = $upload->get_response();

        Response::exitData($data);
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
        $data = $upload->get_response();

        Response::exitData($data);
    }

    /**
     * 普通上传
     * @author hawklim
     */
    function actionUpload() {
        $upload = new OjiaUpload();
        $file = $upload->uploadFile($_FILES['imgFile']);
        if ($file[0]['error'] == 0) {
            $tempArr = pathinfo($file[0]['name']);
            $ext = strtolower($tempArr["extension"]);

            $md5 = md5_file($file[0]['tmp_name']);
            $filename = "cjms_{$md5}.{$ext}";
            $url = $upload->bs2Upload(array('filename' => $filename, 'filepath'=>$file[0]['tmp_name']));
            if (!$url) {
//                alert('bs2上传失败');
                Response::exitData(array('error'=>1, 'msg'=>'bs2上传失败'));
            } else {
//                echo json_encode();
                Response::exitData(array('error'=>0, 'url'=>$url));
            }
        } else {
            //PHP上传失败
            if (!empty($file[0]['error'])) {
//                alert($file[0]['errmsg']);
                Response::exitData(array('error'=>1, 'msg'=>$file[0]['errmsg']));
            }
        }
        
    }

    function actionRunTask($args) {
        $rules = [
            'taskId' => 'int'
        ];
        Param::checkParam2($rules, $args);

        $objCmdb3Task = new TableHelper('Cmdb3Task', 'Report');
        $task = $objCmdb3Task->getRow($args);

        $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');
        $keyWord = ['_sortKey' => 'taskLogId', '_sortDir' => 'DESC'];
        $args['execStatus'] = [0, 1, 2];
        $log = $objTaskLog->getRow($args, $keyWord);

        if (!$log || $log['execStatus'] != 0) {
            if ($task['staticType'] == 1) {
                // 递增方式，需要计算数据结束时间，防止重复计算
                $dataBeginTime = $log['dataEndTime'] ? strtotime($log['dataEndTime']) + 1 : 0;
            } else if ($task['staticType'] == 2) {
                // 覆盖方式，不需要算数据结束时间
                $dataBeginTime = $log['dataBeginTime'] ? strtotime($log['dataBeginTime']) + $task['execInterval'] : 0;
            } else {
                return;
            }

            $span = time() - $task['execDelay'] - $dataBeginTime;
            if ($span < $task['execInterval']) {
                Response::error(CODE_NORMAL_ERROR, "waiting, this task has runned just now. 任务：{$task['taskName']}, 任务id:{$task['taskId']}");
            } else {
                $objTask = new Diy_Task($task, $dataBeginTime, 0, 1);
                $datas = $objTask->run();
                Response::success($datas);
            }
        } else {
            Response::error(CODE_NORMAL_ERROR, '异常，其他进程正在执行任务！');
        }
    }

    function actionRedoTask($args) {
        set_time_limit(300);

        $rules = [
          'taskId' => 'int',
          'beginTime' => 'string',
          'endTime' => 'string',
          'isRedo' => ['int', 'nullable' => true],
        ];
        Param::checkParam($rules, $args);

        $beginTime = strtotime($args['beginTime']);
        $span = strtotime($args['endTime']) - $beginTime;
        $objCmdb3Task = new TableHelper('Cmdb3Task', 'Report');
        $task = $objCmdb3Task->getRow(['taskId' => $args['taskId']]);

        if ($span / $task['timeInterval'] > 500) {
            $msg = '时间间隔太大了，执行次数超过了500';
            Response::error(CODE_NORMAL_ERROR, $msg);
        }

        $objTaskLog = new TableHelper('Cmdb3TaskLog', 'Report');
        $where = ['taskId' => $args['taskId']];
        $keyWord = ['_sortKey' => 'taskLogId', '_sortDir' => 'DESC'];

        for ($i = 0; $i <= $span; $i += $task['timeInterval']) {
            // 获取最新的记录，怕有其他进程在运行
            $log = $objTaskLog->getRow($where, $keyWord);
            if (!$log || $log['execStatus'] != 0) {
                // 覆盖方式，不需要算数据结束时间
                $dataBeginTime = $beginTime + $i;
                $isRedo = $args['isRedo'];
                $isRedo || $isRedo = 3;
                if ($isRedo == 1) {
                    $task['redoConfig'] = json_decode($task['redoConfig'], true);
                    $task['redoConfig'] = current($task['redoConfig']);

                    $staticType = $task['redoConfig']['staticType'];
                    $staticType && $task['staticType'] = $staticType;

                    $insertType = $task['redoConfig']['insertType'];
                    $insertType && $task['insertType'] = $insertType;
                }

                if ($task['staticType'] == 1) {
                    $msg = '不支持递增方式的重算';
                    Response::error(CODE_NORMAL_ERROR, $msg);
                }
                
                $objTask = new Diy_Task($task, $dataBeginTime, $isRedo, 0);
                $objTask->run();
            } else {
                Response::error(CODE_NORMAL_ERROR, '异常，其他进程正在执行任务！');
            }

        }

        Response::success("span:{$span}, i:$i, beginTime:$dataBeginTime, staticType:$staticType, insertType:$insertType");
    }
   
}
