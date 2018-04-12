<?php
defined('LIBS_PATH') || define('LIBS_PATH', realpath(dirname(__FILE__)) . "/..");

require_once LIBS_PATH . '/YYThrift/ThriftClient.php';

class YYInfo_dwWebDB {

    /**
     * 查询用户信息
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public static function getUserInfo($uid) {
        try {
            $client = ThriftClient::instance('webdb_gateway_service');

            $user_info = $client->get_user_info($uid, ['nick', 'sign', 'logo_index', 'jifen'], '');

            $key_index = $user_info->key_index;
            $dataset   = $user_info->dataset;

            $info = array_map(function($idx) use ($dataset) {
                return $dataset[0][$idx];
            }, $key_index);

            // 头像
            $avatarurl = 'http://mtq.yy.com/static/yylogo/1.jpg';
            if (!empty($info['logo_index'])) {
                // 系统头像，区间[100,163]
                $logo_index = $info['logo_index'] - 10000;
                if ($logo_index >= 100 && $logo_index <= 163) {
                    $avatarurl = "http://mtq.yy.com/static/yylogo/$logo_index.jpg";
                }
            } else {
                // 自定义头像
                $custom_logo = $client->get_custom_logo($uid);
                $avatarurl  = $custom_logo;
            }

            // YY等级
            $jifen = $info['jifen'];
            $yylevel = self::getYyLevelByJifen($jifen);

            return [
                'nickname'  => $info['nick'],   // 昵称
                'avatarurl' => $avatarurl,      // 头像
                'signature' => $info['sign'],   // 签名
                'jifen'     => $jifen,          // 积分
                'yylevel'   => $yylevel,        // 等级
            ];

        } catch(\Exception $e) {
            Tool::log("get_user_info service load data failure. ".$e->getMessage());
            return false;
        }
    }

    /**
     * [getYyLevelByJifen 根据用户积分计算YY等级]
     * @param  [type] $jifen 用户积分
     * @return [type]        返回结果
     */
    public static function getYyLevelByJifen($jifen) {
        $onlineHours = $jifen / 60;
        if ($onlineHours <= 0) {
            return 1;
        }

        $level = 1;
        $start = 0;
        while (true) {
            $start += $level * 0.5;
            if ($start > $onlineHours) {
                break;
            }
            $level++;
        }
        return $level;
    }

    /**
     * [getUdbSeqByid 根据yyuid 查询udbseq(udbuid)]
     * @param  [type] $yyuid yyuid
     * @return [type]        返回结果
     */
    public static function getUdbSeqByid($yyuid) {
        try {
            $client = ThriftClient::instance('userinfo_service');
	        $params = array('user' => 'user1035', 'password' => '56X2zdiRH9');
            $authorizes = new \Services\userinfo_service\AuthorizeMsg($params);
            $ret = $client->lg_userinfo_transUdbseqByUid($yyuid, $authorizes);
        	return $ret;
        } catch(\Exception $e) {
            Tool::log('get udbseq failed, yyuid:' . $yyuid);
            return false;
        }
    }
    /**
     * [getYYnoByUid 根据yyuid 查询yy号]
     * @param  [type] $yyuid yyuid
     * @return [type]        返回结果
     */
    public static function getYYnoByUid($yyuid) {
        try {
             $client = ThriftClient::instance('imweb_service');
             $ret = $client->imweb_get_imid_by_uid($yyuid);
             return $ret;
        } catch(\Exception $e) {
            Tool::log('get yyno failed , yyuid: ' . $yyuid);
            return false;
        }
    }
}
