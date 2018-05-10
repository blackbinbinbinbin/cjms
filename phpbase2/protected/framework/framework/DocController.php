<?php

class DocController extends Controller {
    
    /**
     * 删除缓存
     * @param unknown $args
     */
    public function actionRemoveRet($args) {
        $rules = array(
            'action' => 'string',
            'code' => 'int'
        );
        Param::checkParam($rules, $args);
        
        $objRedis = CallLog::getLogRedis();
        $args['action'] = str_replace('_', '/', $args['action']);
        $cacheKey = TYPE_SELF_CALL . ":{$args['action']}";
        $value = $objRedis->hDel($cacheKey, $args['code']);
        return $objRedis->lrem($cacheKey, 1, $value);
    }

    public function actionCall($args) {
        $rules = array(
          'action' => 'string',
          'code' => 'int'
        );
        Param::checkParam($rules, $args);

        $objRedis = CallLog::getLogRedis();
        $cacheKey = TYPE_SELF_CALL . ":{$args['action']}";
        $param = $objRedis->hGet($cacheKey, $args['code'] . '_param');

        header("Location:/{$args['action']}?{$param}");
        exit;
    }

    public function actionSaveComment($args) {
        $rules = array(
          'action' => 'string',
          'code' => 'int',
          'path' => 'string',
          'text' => ['string', 'nullable' => true],
        );
        Param::checkParam($rules, $args);

        $matches = [];
        preg_match('/[^:\[\{]+$/', $args['path'], $matches);
        $name = $matches[0];

        $objRedis = CallLog::getLogRedis();
        $args['action'] = str_replace('_', '/', $args['action']);
        $cacheKey = TYPE_SELF_CALL . ":{$args['action']}:comment_{$args['code']}";
        if ($args['text']) {
            $objRedis->hSet($cacheKey, $args['path'], $args['text']);

            $cacheKey2 = TYPE_SELF_CALL . ":GLOBAL_COMMENT";
            $objRedis->hSetNx($cacheKey2, $name, $args['text']);
        } else {
            $objRedis->hDel($cacheKey, $args['path']);
        }

        Response::success();
    }

    public static function getRetData() {
        $ret = [];

        try {
            $config = $GLOBALS['redisInfo']['logstash_redis'];
            if ($config && class_exists("Redis")) {
                $objRedis = CallLog::getLogRedis();
                $controller = str_replace('Controller', '', CONTROLLER_NAME);
                $api = lcfirst($controller) . '/' . lcfirst(ACTION_NAME);
                $cacheKey = TYPE_SELF_CALL . ":{$api}";
                $keys = $objRedis->hKeys($cacheKey);

                foreach ($keys as $key) {
                    $json = $objRedis->hGet($cacheKey, $key);
                    $ret[$key]['ret'] = $json;
                    $cacheKey2 = TYPE_SELF_CALL . ":{$api}:comment_{$key}";
                    $ret[$key]['comments'] = json_encode($objRedis->hGetAll($cacheKey2));
                }
            }
        } catch (Exception $ex) {

        }

        return $ret;
    }

    public static function logSelfData($code, $json) {
        if (DEBUG && $_GET['doc'] != 'func') {
            $json = is_string($json) ? $json : json_encode($json);
            try {
                $config = $GLOBALS['redisInfo']['logstash_redis'];
                if ($config && class_exists("Redis")) {
                    $objRedis = CallLog::getLogRedis();
                    $controller = str_replace('Controller', '', CONTROLLER_NAME);
                    $api = lcfirst($controller) . '/' . lcfirst(ACTION_NAME);
                    $objRedis && $objRedis->hSetNx(TYPE_SELF_CALL . ":{$api}", $code, $json);
                }
            } catch (Exception $ex) {

            }
        }
    }
    
}