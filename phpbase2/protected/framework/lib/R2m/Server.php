<?php

require_once ROOT_PATH . 'framework/lib/R2m/Header.php';


/**
 * Created by PhpStorm.
 * @author benzhan
 * Date: 15/12/24
 * Time: 下午5:57
 */
class R2m_Server {
    private $serv;

    private $_authList = [];
    private $_objRedis2Mysqls;

    private static $_map = [
        CMD_ADD_OBJECT => 'addObject',
        CMD_ADD_OBJECT_NX => 'addObjectNx',
        CMD_GET_ROW => 'getRow',
        CMD_GET_ALL => 'getAll',
        CMD_REPLACE_OBJECT => 'replaceObject',
        CMD_UPDATE_OBJECT => 'updateObject',
        CMD_UPDATE_OBJECTS => 'updateObjects',
        CMD_DEL_OBJECT => 'delObject',
        CMD_DEL_OBJECTS => 'delObjects',
        CMD_DEL_ROW_CACHE => 'delRowCache',
        CMD_DEL_LIST_CACHE => 'delListCache',
        CMD_SET_DEBUG => 'setDebug',
        CMD_DEBUG_MSG => 'getDebugMsg',
        CMD_GET_INSERT_ID => 'getInsertId',
        CMD_ADD_OBJECTS => 'addObjects',
        CMD_ADD_OBJECTS2 => 'addObjects2',
        CMD_GET_ALL_SQL => 'getAllSql',
    ];

    public function __construct() {
        $ip = CallLog::getWanIp();
        $confKey = $GLOBALS['r2mConfKeys'][$ip];
        $conf = $GLOBALS[$confKey];
        Tool::log("start ip:{$ip}, confKey:{$confKey}, conf:" . json_encode($conf));
        if (!$conf) {
            YYms::reportServerError('redis2mysql config error. server ip:' . $_SERVER['SERVER_ADDR']);
            $conf = $GLOBALS['r2mConf'];
        }

        $this->serv = new swoole_server($conf['host'], $conf['port']);

        $this->serv->set(
          array(
            'daemonize'     => false,
            'worker_num' => WORKER_NUM,    //worker process num
            'debug_mode'    => 1,

            'dispatch_mode' => 2,
            'open_length_check' => true,
            'package_length_type' => 'N',
            'package_length_offset' => 14,
            'package_body_offset' => 18,
            'package_max_length' => 800000,

            'max_request' => 0,
            'heartbeat_idle_time' => 300,
            'heartbeat_check_interval' => 60,
          )
        );

        $this->serv->on('start', array($this, 'onStart'));
        $this->serv->on('connect', array($this, 'onConnect'));
        $this->serv->on('receive', array($this, 'onReceive'));
        $this->serv->on('close', array($this, 'onClose'));
    }

    public function onStart($serv) {
    }

    public function onConnect($serv, $fd, $from_id) {
        $conf = $GLOBALS['r2mConf'];
        if ($conf['pwd']) {
            $this->_authList[$fd] = false;
        } else {
            $this->_authList[$fd] = true;
        }
    }

    private function getRedis2Mysql($conf) {
        $key = join(',', $conf);
        $objRedis2Mysql = $this->_objRedis2Mysqls[$key];
        if (!$objRedis2Mysql) {
            $objRedis2Mysql = new Redis2Mysql($conf[0], $conf[1], $conf[2]);
            $this->_objRedis2Mysqls[$key] = $objRedis2Mysql;
        }

        return $objRedis2Mysql;
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, &$data) {
        $startTime2 = microtime(true);
        $objServer = new R2m_SwooleServer($serv, $fd);
        $objHelper = new R2m_Helper($objServer);
        $result = $objHelper->parse($data);

        $udp_client = $serv->connection_info($fd, $from_id);

        CallLog::setCallId($udp_client['last_time']);
        CallLog::setClientIp($udp_client['remote_ip'] . ':' . $udp_client['remote_port']);

        $msgList = [];
        $content = arrayPop($result, 'content');
        $msgList[] = "params:" . json_encode($result);
        if (is_array($content)) {
            $msgList[] = "conf:" . json_encode($content['conf']);
        } else {
            $msgList[] = "conf:" . $content;
        }

        $code = CODE_SUCCESS;
        if ($result['cmd'] == CMD_AUTH) {
            if ($GLOBALS['r2mConf']['pwd'] == $content) {
                $this->_authList[$fd] = true;
                $msg = "id:{$result['id']}, set auth success.";
                CallLog::setParam($msg);
                CallLog::setUrl('auth');
                $msgList[] = $msg;
            }
        } else if ($this->_authList[$fd]) {
            try {
                $funcName = self::$_map[$result['cmd']];
                $conf = $content['conf'];
                $dataStr = $content['data'];
                if ($result['contentType'] == CONTENT_TYPE_JSON) {
                    $data = json_decode($dataStr, true);
                } else {
                    $data = $dataStr;
                }

                $objRedis2Mysql = $this->getRedis2Mysql($conf);
                $objRedis2Mysql->setDebug(true);
                $msgList[] = "call: $funcName , data:" . $dataStr;

                $resResult = call_user_func_array([$objRedis2Mysql, $funcName], $data);

                $objDb = $objRedis2Mysql->objTable->getDb();
                $queryNum = $objDb->getQueryNum();
                $updateNum = $objDb->getUpdateNum();
                if ($queryNum || $updateNum) {
                    $code = CODE_NORMAL_ERROR;
                    CallLog::setParam("id:{$result['id']}, queryNum:$queryNum, updateNum:$updateNum");
                    $objDb->setQueryNum(0);
                    $objDb->setUpdateNum(0);
                } else {
                    CallLog::setParam("id:{$result['id']}, {$funcName}");
                }
                CallLog::setUrl("{$conf[1]}:{$conf[0]}:{$funcName}");

                $objHelper->response(RESULT_SUCCESS, $resResult, $result['id']);
                if ($objRedis2Mysql->getDebug() || DEBUG) {
                    $debugMsg = $objRedis2Mysql->getDebugMsg();
                    $msgList[] = $debugMsg;
                }

            } catch (Exception $ex) {
                if ($ex instanceof DB_Exception) {
                    $code = CODE_DB_ERROR;
                } else if ($ex instanceof  RedisException || $ex instanceof R2m_Exception) {
                    $code = CODE_REDIS_ERROR;
                } else {
                    $code = CODE_UNKNOW_ERROT;
                }

                $msg = '[配置信息:' . json_encode($conf) . "]\n";
                $msg .= $ex->getMessage() . "\n";
                $msg .= $ex->getTraceAsString() . "\n";
                $msgList[] = $msg;
                $objHelper->response(RESULT_ERROR, $msg, $result['id']);
            }
        } else {
            $objHelper->response(RESULT_NO_AUTHOR, 'no auth', $result['id']);
            $msgList[] = 'no auth';
            $code = CODE_NO_PERMITION;
        }

        CallLog::logSelfCall($code, join("\n", $msgList), $startTime2);
    }

    public function onClose($serv, $fd, $from_id) {
        unset($this->_authList[$fd]);
    }

    public function start() {
        $this->serv->start();
    }

    // 业务逻辑

}
