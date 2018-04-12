<?php

require_once realpath(dirname(__FILE__)) . '/Header.php';

/**
 * Created by PhpStorm.
 * @author benzhan
 * Date: 15/12/24
 * Time: 下午5:57
 */
class R2m_Helper {
    private $_ver;
    private $_trans;
    public $errMsg;

    public function __construct(R2m_Transport $transport) {
        $this->_trans = $transport;
        $this->_ver = R2M_VERSION;
    }

    public function receive() {
        $str = $this->_trans->readAll(14);
        $lenStr = $this->_trans->read(4);
        $str .= $lenStr;
        $len = BitHelper::readI32($lenStr);
        $str .= $this->_trans->readAll($len);

        $result = $this->parse($str);
        return $result;
    }

    public function parse($str) {
        $ver = BitHelper::readI16($str);
        if ($ver != $this->_ver) {
            $this->errMsg = "no match redis2mysql service version. client:{$ver}, server:{$this->_ver}";
        }

        $cmd = BitHelper::readI16($str);
        $cmdType = BitHelper::readI16($str);
        $codeType = BitHelper::readI16($str);
        $contentType = BitHelper::readI16($str);
        $id = BitHelper::readI32($str);

        $content = BitHelper::readString($str);

        if ($contentType == CONTENT_TYPE_JSON) {
            $content = json_decode($content, true);
        }

        return compact('ver', 'cmd', 'cmdType', 'codeType', 'contentType', 'id', 'content');
    }

    public function request($conf, $cmd, $data, $id = 0) {
        return $this->cmd($conf, $cmd, $data, $id, CMD_TYPE_REQUEST);
    }

    public function response($cmd, $data, $id = 0) {
        return $this->cmd(null, $cmd, $data, $id, CMD_TYPE_RESPONSE);
    }

    /**
     * 授权
     * @param $pwd
     * @author benzhan
     */
    private function auth($pwd) {
        $id = CallLog::getCallId();
        $this->request(null, CMD_AUTH, $pwd, $id);
    }

    public function open($pwd) {
        $flag = true;
        if (!$this->_trans->isOpen()) {
            $flag = $this->_trans->open();
            if (!$flag) {
                throw new R2m_Exception('can not open.');
            }

            // 第一次打开成功需要授权
            if ($flag && $pwd) {
                $this->auth($pwd);
            }
        }

        return $flag;
    }

    public function close() {
        return $this->_trans->close();
    }

    public function cmd($conf, $cmd, $data, $id = 0, $cmdType = CMD_TYPE_REQUEST, $codeType = CODE_TYPE_BINARY) {
        BitHelper::writeI16($this->_ver);
        BitHelper::writeI16($cmd);
        BitHelper::writeI16($cmdType);
        BitHelper::writeI16($codeType);
        if (is_array($data)) {
            $contentType = CONTENT_TYPE_JSON;
            $data = json_encode($data);
        } else {
            $contentType = CONTENT_TYPE_TEXT;
        }
        BitHelper::writeI16($contentType);
        BitHelper::writeI32($id);

        if ($conf) {
            $args = compact('conf', 'data');
            $args = json_encode($args);
        } else {
            $args = $data;
        }

        BitHelper::writeString($args);
        return $this->_trans->write(BitHelper::getWriteBuffer());
    }

}
