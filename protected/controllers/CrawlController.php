<?php

/**
 * 通用爬虫相关的接口
 * @author benzhan
 */
class CrawlController extends Controller {

    private function _crawlPage($rule_id, $url) {
        $objHttp = new dwHttp();
        // 先decode，再encode，可以把字符给encode，又不会引起多次encode
        $url = urldecode($url);
        $url = urlencode($url);
        $url = URL_SPIDER . "previewRule?rule_id={$rule_id}&url={$url}";
        return $objHttp->get($url, 35, '', 86400 * 14);
    }

    function actionClearViewCache($args) {
        $rules = [
            'rule_id' => 'string',
            'url' => ['string', 'nullable' => true]
        ];
        Param::checkParam2($rules, $args);

        $url = urlencode($args['url']);
        $url = URL_SPIDER . "previewRule?rule_id={$args['rule_id']}&url={$url}";

        $objRedis = dwRedis::init('logic');
        $key = 'globals:url_map:' . $url;

        $objRedis->del($key);

        return $key;
    }

    function actionUrl($args) {
        $rules = [
            'rule_id' => 'string',
            'url' => ['string', 'nullable' => true],
        ];
        Param::checkParam2($rules, $args);

        $rule_id = $args['rule_id'];
        $objRule = new TableHelper('rule', 'crawl');
        $rule = $objRule->getRow(compact('rule_id'));

        $url = arrayPop($args, 'url');
        $iframeHtml = $this->_crawlPage($rule_id, $url);

        $urlInfo = parse_url($url);
        // 修改js和css的路径，改为绝对路径
        $iframeHtml = preg_replace_callback('/("|\')([^"\']+\.(css|js)(\?[^"\']*)?)["\']/', function($matches) use($urlInfo) {
            $q = trim($matches[1]);
            $src = trim($matches[2]);
            if (strpos($src, '//') === 0) {
                $src = "{$q}{$urlInfo['scheme']}:{$src}{$q}";
            } else if (strpos($src, '/') === 0) {
                $src =  "{$q}{$urlInfo['scheme']}://{$urlInfo['host']}{$src}{$q}";
            } else if (strpos($src, 'http') !== 0) {
                // 相对链接
                $pos = strrpos($urlInfo['path'], '/');
                $dir = substr($urlInfo['path'], 0, $pos);
                $src =  "{$q}{$urlInfo['scheme']}://{$urlInfo['host']}{$dir}/{$src}{$q}";
            } else {
                $src = "{$q}{$src}{$q}";
            }
            return $src;
        }, $iframeHtml);


        if ($rule['data_type'] == 'html') {
            $iframeHtml = str_replace('location.href', 'location.href2', $iframeHtml);
            Response::exitMsg($iframeHtml);
        } else {
            $this->tpl->assign('json', $iframeHtml);
            $this->tpl->display('crawl/page_crawl_json');
        }
    }

    function actionPageCrawlView($args) {
        $rules = [
            'rule_id' => 'string',
            'url' => ['string', 'nullable' => true],
        ];
        Param::checkParam2($rules, $args);

        $rule_id = $args['rule_id'];
        $url = arrayPop($args, 'url');

        $objRule = new TableHelper('rule', 'crawl');
        $rule = $objRule->getRow($args);
        $url = $url ?: $rule['demo_url'];

        $objItem = new TableHelper('item', 'crawl');
        $items = $objItem->getAll($args);

        if ($rule['parent_rule_id']) {
            $where = ['rule_id' => $rule['parent_rule_id']];
            $parent_rule = $objRule->getRow($where);
            $parent_items = $objItem->getAll($where);
            $url = $url ?: $parent_rule['demo_url'];
        }

        $objCmdb3Table = new TableHelper('Cmdb3Table', 'Report');
        $where = ['tableId' => '4424e428-3ac7-e981-ec28-96592dbd5739'];
        $extraJsCss = $objCmdb3Table->getOne($where, ['_field' => 'extraJsCss']);

        $task = [
            'url' => $url,
            'rule_id' => $rule_id,
            'task_id' => -1,
            'task_key' => -1,
        ];

        $iframeUrl = "/crawl/url?rule_id={$rule['rule_id']}&url=" . urlencode($url);
        $this->tpl->assign(compact('iframeUrl', 'extraJsCss', 'url', 'task'));
        $this->tpl->assign(compact( 'rule', 'items', 'parent_rule', 'parent_items'));
        $this->tpl->display('crawl/page_crawl_view');
    }

    function relative_to_absolute($content, $feed_url) {
        preg_match('/(http|https|ftp):///', $feed_url, $protocol);
        $server_url = preg_replace("/(http|https|ftp|news):///", "", $feed_url);
        $server_url = preg_replace("//.*/", "", $server_url);
        if ($server_url == '') {
            return $content;
        }
        if (isset($protocol[0])) {
            $new_content = preg_replace('/href="//', 'href="'.$protocol[0].$server_url.'/', $content);
            $new_content = preg_replace('/src="//', 'src="'.$protocol[0].$server_url.'/', $new_content);
        } else {
            $new_content = $content;
        }
        return $new_content;
    }

    function format_url($srcurl, $baseurl) {
        $srcinfo = parse_url($srcurl);
        if(isset($srcinfo['scheme'])) {
            return $srcurl;
        }
        $baseinfo = parse_url($baseurl);
        $url = $baseinfo['scheme'].'://'.$baseinfo['host'];
        if(substr($srcinfo['path'], 0, 1) == '/') {
            $path = $srcinfo['path'];
        }else{
            $path = dirname($baseinfo['path']).'/'.$srcinfo['path'];
        }
        $rst = array();
        $path_array = explode('/', $path);
        if(!$path_array[0]) {
            $rst[] = '';
        }
        foreach ($path_array AS $key => $dir) {
            if ($dir == '..') {
                if (end($rst) == '..') {
                    $rst[] = '..';
                }elseif(!array_pop($rst)) {
                    $rst[] = '..';
                }
            }elseif($dir && $dir != '.') {
                $rst[] = $dir;
            }
        }
        if(!end($path_array)) {
            $rst[] = '';
        }
        $url .= implode('/', $rst);
        return str_replace('\\', '/', $url);
    }

    function actionRuleItems($args) {
        $rules = [
            'rule_id' => 'string'
        ];
        Param::checkParam2($rules, $args);

        $objItem = new TableHelper('item', 'crawl');
        $items = $objItem->getAll($args);

        return $items;
    }

    static function _getTableList($rule_id) {
        $sql = "SELECT db_name, `table_name`, pri_key, notice_url, rule_id, is_default, update_mode 
                   FROM data_db JOIN rule_db_conf ON data_db.db_id = rule_db_conf.db_id 
                  WHERE rule_id = '{$rule_id}'";
        $objDbTable = new TableHelper('data_db', 'crawl');
        return $objDbTable->getDb()->getAll($sql);
    }

    static function getTabels($rule_id) {
        if (!$rule_id) {
            return [];
        }

        $tables = self::_getTableList($rule_id);
        $objRule = new TableHelper('rule', 'crawl');
        $rule = $objRule->getRow(compact('rule_id'));

        if ($rule['parent_rule_id']) {
            $tables = arrayFormatKey($tables, 'table_name');
            $parentTables = self::_getTableList($rule['parent_rule_id']);
            foreach ($parentTables as $parentTable) {
                if (!$tables[$parentTable['table_name']]) {
                    $tables[$parentTable['table_name']] = $parentTable;
                }
            }
        }

        $map = [];
        foreach ($tables as $data) {
            $objTable = new TableHelper($data['table_name'], $data['db_name']);
            $sql = "SHOW FULL FIELDS FROM `{$data['table_name']}`";
            $fields = $objTable->getDb()->getAll($sql);

            foreach ($fields as $field) {
                if (!$data['is_default']) {
                    $fieldName = "{$data['table_name']}.{$field['Field']}";
                } else {
                    $fieldName = $field['Field'];
                }
                $map[$fieldName] = $fieldName;
            }
        }

        return $map;
    }

    function actionRuleJs($args) {
        $rules = [
            'rule_id' => 'string',
        ];
        Param::checkParam2($rules, $args);

        $objItem = new TableHelper('item', 'crawl');
        $items = $objItem->getAll($args);

        $objRule = new TableHelper('rule', 'crawl');
        $rule = $objRule->getRow($args);

        if ($rule['parent_rule_id']) {
            $where = ['rule_id' => $rule['parent_rule_id']];
            $parent_rule = $objRule->getRow($where);
            $parent_items = $objItem->getAll($where);
        } else {
            $parent_rule = [];
            $parent_items = [];
        }

        $arr = [$parent_items, $items];

        $this->tpl->assign(compact('arr', 'rule', 'parent_rule'));
        $this->tpl->display('crawl/page_crawl_js');
    }

    /**
     * 获取规则的执行日志
     * @return array
     */
    function actionRuleData($args) {
        $rules = [
            'rule_id' => 'string',
            'startTime' => 'string',
        ];
        Param::checkParam2($rules, $args);

        $objCrawlLog = new TableHelper('crawl_log', 'crawl');

        $_where = "create_time > '" . $objCrawlLog->escape($args['startTime']) . "'";
        $_field = 'log_id, task_id, task_key, rule_id, url, state, create_time, content';
        $_limit = 5000;
        $datas = $objCrawlLog->getAll(['rule_id' => $args['rule_id']], compact('_where', '_limit', '_field'));

        return $datas;
    }


    /**
     * 创建模版的视图
     */
    function actionZqVideoView($args) {
        $this->tpl->display('crawl/zq_video_view');
    }


    /**
     * 生成专区模板视频
     */
    function actionGenZqVideo($args) {
        $rules = [
            'type' => ['string', 'enum' => ['qq', 'youku']],
            'game_id' => 'int',
        ];
        Param::checkParam2($rules, $args);

        $objGameInfo = new TableHelper('game_info', 'dw_ka');
        $where = ['game_id' => $args['game_id']];
        $keyWord = ['_field' => 'game_name'];
        $gameName = $objGameInfo->getOne($where, $keyWord);

        // 添加视频列表
        $objRule = new TableHelper('rule', 'crawl');
        $rule_id = "ka:{$args['game_id']}:video_list:{$args['type']}";
        $parent_rule_id = "ka:0:video_list:{$args['type']}_tpl";
        $rule_name = "{$gameName}:视频列表:{$args['type']}";
        $create_time = $update_time = NOW;
        $creator = User::getUserName();
        $request_mode = $update_mode = $data_type = '';
        $rule_type = 'ka';
        $enable = 0;
        $min_length = $max_exception_count = null;

        $data = compact('rule_id', 'parent_rule_id', 'rule_name', 'create_time', 'update_time');
        $data += compact('creator', 'request_mode', 'update_mode', 'data_type', 'rule_type', 'enable', 'min_length', 'max_exception_count');
        $objRule->addObject($data);

        // 添加视频详情
        $data['rule_id'] = "ka:{$args['game_id']}:video_detail:{$args['type']}";
        $data['parent_rule_id'] = "ka:0:video_detail:{$args['type']}_tpl";
        $data['rule_name'] = "{$gameName}:视频详情:{$args['type']}";
        $objRule->addObject($data);
    }

}
