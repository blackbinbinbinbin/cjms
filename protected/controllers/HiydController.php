<?php

/**
 * Hi运动接口
 * @author benzhan
 */
class HiydController extends BaseController {

    function actionGenImgs() {
        $objTable = new TableHelper('health_report_tag', 'hiyd_home');
        $_where = 'img_info IS NULL OR img_info = "" ';
        $datas = $objTable->getAll(compact('_where'));
        foreach ($datas as $data) {
            $this->_process($data, $objTable);
        }
    }

    function actionGenOneImg($args) {
        $rules = [
            'id' => 'int'
        ];
        Param::checkParam2($rules, $args);

        $objTable = new TableHelper('health_report_tag', 'hiyd_home');
        $where = ['id' => $args['id']];
        $data = $objTable->getRow($where);
        if ($data) {
            $this->_process($data, $objTable);
        }
    }

    private function _process($data, TableHelper $objTable) {
        $img = $data['img'];
        if (strpos($img, 'http://') !== false) {
            // 需要转存
            $imgUrl = $img;
        } else {
            $imgUrl = URL_M_HIYD . 'act/report2/text2image.php?text=' . urlencode($data['name']);
        }

        $objHttp = new dwHttp();
        $file_content = $objHttp->get($imgUrl);
        $img = imagecreatefromstring($file_content);
        $width = imagesx($img);
        $height = imagesy($img);

        $file_content = chunk_split(base64_encode($file_content));//base64编码

        $newData = [];
        $newData['img'] = 'data:image/png;base64,' . $file_content;//合成图片的base64编码
        $newData['img_info'] = json_encode(compact('width', 'height'));

        $objTable->updateObject($newData, ['id' => $data['id']]);
    }

}
