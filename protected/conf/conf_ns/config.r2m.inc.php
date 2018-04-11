<?php

//常量
defined('REDIS_PRE_KEY_RANDOM_ID') || define('REDIS_PRE_KEY_RANDOM_ID', 'genRandomId:');

//数组
if (!isset($GLOBALS['r2mConf'])) { $GLOBALS['r2mConf'] = []; }
$GLOBALS['r2mConf'] += array (
  'host' => '61.160.36.225',
  'port' => '9501',
  'pwd' => 'test',
);
if (!isset($GLOBALS['r2mConf_wuxiduoxian01_shop'])) { $GLOBALS['r2mConf_wuxiduoxian01_shop'] = []; }
$GLOBALS['r2mConf_wuxiduoxian01_shop'] += array (
  'host' => '61.160.36.225',
  'port' => '9501',
  'pwd' => 'test',
);
if (!isset($GLOBALS['r2mConf']['default'])) { $GLOBALS['r2mConf']['default'] = []; }
$GLOBALS['r2mConf']['default'] += array (
  'host' => '61.160.36.225',
  'port' => '9501',
  'pwd' => 'test',
);
if (!isset($GLOBALS['r2mConf']['wuxiduoxian01_shop'])) { $GLOBALS['r2mConf']['wuxiduoxian01_shop'] = []; }
$GLOBALS['r2mConf']['wuxiduoxian01_shop'] += array (
  'host' => '61.160.36.225',
  'port' => '9501',
  'pwd' => 'test',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['act'])) { $GLOBALS['r2mInfo']['dw_ka']['act'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['act'] += array (
  'key' => 'act_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['blacklist_ip'])) { $GLOBALS['r2mInfo']['dw_ka']['blacklist_ip'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['blacklist_ip'] += array (
  'key' => 'app_name,ip',
  'ttl' => '300',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['blacklist_ka_user'])) { $GLOBALS['r2mInfo']['dw_ka']['blacklist_ka_user'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['blacklist_ka_user'] += array (
  'key' => 'app_name,user_id',
  'ttl' => '600',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['blacklist_yyuser'])) { $GLOBALS['r2mInfo']['dw_ka']['blacklist_yyuser'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['blacklist_yyuser'] += array (
  'key' => 'app_name,yyuid',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['blacklist_yy_user'])) { $GLOBALS['r2mInfo']['dw_ka']['blacklist_yy_user'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['blacklist_yy_user'] += array (
  'key' => 'app_name,yyuid',
  'ttl' => '600',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['game_info'])) { $GLOBALS['r2mInfo']['dw_ka']['game_info'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['game_info'] += array (
  'key' => 'game_id',
  'ttl' => '600',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['game_type'])) { $GLOBALS['r2mInfo']['dw_ka']['game_type'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['game_type'] += array (
  'key' => 'game_type_id',
  'ttl' => '0',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['gift_info'])) { $GLOBALS['r2mInfo']['dw_ka']['gift_info'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['gift_info'] += array (
  'key' => 'gift_id',
  'ttl' => '600',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['gift_online'])) { $GLOBALS['r2mInfo']['dw_ka']['gift_online'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['gift_online'] += array (
  'key' => 'gift_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['gift_recommend'])) { $GLOBALS['r2mInfo']['dw_ka']['gift_recommend'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['gift_recommend'] += array (
  'key' => 'gift_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['gift_res'])) { $GLOBALS['r2mInfo']['dw_ka']['gift_res'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['gift_res'] += array (
  'key' => 'res_id',
  'ttl' => '600',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['lottery_reward'])) { $GLOBALS['r2mInfo']['dw_ka']['lottery_reward'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['lottery_reward'] += array (
  'key' => 'reward_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['qa'])) { $GLOBALS['r2mInfo']['dw_ka']['qa'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['qa'] += array (
  'key' => 'qa_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['qa_items'])) { $GLOBALS['r2mInfo']['dw_ka']['qa_items'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['qa_items'] += array (
  'all_key' => 'qa_id,item_id',
  'key' => 'item_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['user'])) { $GLOBALS['r2mInfo']['dw_ka']['user'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['user'] += array (
  'key' => 'user_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['wx_lucky_money'])) { $GLOBALS['r2mInfo']['dw_ka']['wx_lucky_money'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['wx_lucky_money'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['wx_message'])) { $GLOBALS['r2mInfo']['dw_ka']['wx_message'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['wx_message'] += array (
  'key' => 'msg_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['zt_call'])) { $GLOBALS['r2mInfo']['dw_ka']['zt_call'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['zt_call'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['zt_lottery'])) { $GLOBALS['r2mInfo']['dw_ka']['zt_lottery'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['zt_lottery'] += array (
  'key' => 'lottery_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_ka']['zt_r_lottery_gift'])) { $GLOBALS['r2mInfo']['dw_ka']['zt_r_lottery_gift'] = []; }
$GLOBALS['r2mInfo']['dw_ka']['zt_r_lottery_gift'] += array (
  'key' => 'gift_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['back_order'])) { $GLOBALS['r2mInfo']['dw_shop']['back_order'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['back_order'] += array (
  'key' => 'back_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['cart'])) { $GLOBALS['r2mInfo']['dw_shop']['cart'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['cart'] += array (
  'all_key' => 'user_id',
  'key' => 'cart_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['collect_goods'])) { $GLOBALS['r2mInfo']['dw_shop']['collect_goods'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['collect_goods'] += array (
  'all_key' => 'user_id',
  'key' => 'collect_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['coupons'])) { $GLOBALS['r2mInfo']['dw_shop']['coupons'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['coupons'] += array (
  'key' => 'coupon_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['delivery_order'])) { $GLOBALS['r2mInfo']['dw_shop']['delivery_order'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['delivery_order'] += array (
  'key' => 'delivery_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['order_action'])) { $GLOBALS['r2mInfo']['dw_shop']['order_action'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['order_action'] += array (
  'key' => 'action_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['order_finance'])) { $GLOBALS['r2mInfo']['dw_shop']['order_finance'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['order_finance'] += array (
  'all_key' => 'finance_order_sn',
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['order_info'])) { $GLOBALS['r2mInfo']['dw_shop']['order_info'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['order_info'] += array (
  'all_key' => 'user_id',
  'key' => 'order_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['recommend_zhibo'])) { $GLOBALS['r2mInfo']['dw_shop']['recommend_zhibo'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['recommend_zhibo'] += array (
  'key' => 'platform,room_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['user_coupons'])) { $GLOBALS['r2mInfo']['dw_shop']['user_coupons'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['user_coupons'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['user_exchange_log'])) { $GLOBALS['r2mInfo']['dw_shop']['user_exchange_log'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['user_exchange_log'] += array (
  'key' => 'log_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_shop']['user_message'])) { $GLOBALS['r2mInfo']['dw_shop']['user_message'] = []; }
$GLOBALS['r2mInfo']['dw_shop']['user_message'] += array (
  'all_key' => 'user_id',
  'key' => 'msg_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_company'])) { $GLOBALS['r2mInfo']['dw_sy']['game_company'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_company'] += array (
  'key' => 'company_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_forbid_word'])) { $GLOBALS['r2mInfo']['dw_sy']['game_forbid_word'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_forbid_word'] += array (
  'key' => 'forbid_word',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_info'])) { $GLOBALS['r2mInfo']['dw_sy']['game_info'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_info'] += array (
  'key' => 'game_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_package_android'])) { $GLOBALS['r2mInfo']['dw_sy']['game_package_android'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_package_android'] += array (
  'key' => 'game_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_package_ios'])) { $GLOBALS['r2mInfo']['dw_sy']['game_package_ios'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_package_ios'] += array (
  'key' => 'game_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_package_operations'])) { $GLOBALS['r2mInfo']['dw_sy']['game_package_operations'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_package_operations'] += array (
  'key' => 'game_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_rank'])) { $GLOBALS['r2mInfo']['dw_sy']['game_rank'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_rank'] += array (
  'key' => 'rank_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_recommend'])) { $GLOBALS['r2mInfo']['dw_sy']['game_recommend'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_recommend'] += array (
  'key' => 'game_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_resource'])) { $GLOBALS['r2mInfo']['dw_sy']['game_resource'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_resource'] += array (
  'all_key' => 'game_id,source_id',
  'key' => 'source_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_spider_conf'])) { $GLOBALS['r2mInfo']['dw_sy']['game_spider_conf'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_spider_conf'] += array (
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_spider_log'])) { $GLOBALS['r2mInfo']['dw_sy']['game_spider_log'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_spider_log'] += array (
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['game_type'])) { $GLOBALS['r2mInfo']['dw_sy']['game_type'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['game_type'] += array (
  'key' => 'game_type_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['hot_word'])) { $GLOBALS['r2mInfo']['dw_sy']['hot_word'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['hot_word'] += array (
  'key' => 'hot_word',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['operations_rank'])) { $GLOBALS['r2mInfo']['dw_sy']['operations_rank'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['operations_rank'] += array (
  'key' => 'game_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy']['search_static'])) { $GLOBALS['r2mInfo']['dw_sy']['search_static'] = []; }
$GLOBALS['r2mInfo']['dw_sy']['search_static'] += array (
  'key' => 'keyword',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_sy_data']['search_static'])) { $GLOBALS['r2mInfo']['dw_sy_data']['search_static'] = []; }
$GLOBALS['r2mInfo']['dw_sy_data']['search_static'] += array (
  'key' => 'keyword',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['ad_position'])) { $GLOBALS['r2mInfo']['dw_task']['ad_position'] = []; }
$GLOBALS['r2mInfo']['dw_task']['ad_position'] += array (
  'key' => 'id',
  'ttl' => '0',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['game_download'])) { $GLOBALS['r2mInfo']['dw_task']['game_download'] = []; }
$GLOBALS['r2mInfo']['dw_task']['game_download'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['gold_record'])) { $GLOBALS['r2mInfo']['dw_task']['gold_record'] = []; }
$GLOBALS['r2mInfo']['dw_task']['gold_record'] += array (
  'key' => 'id',
  'ttl' => '300',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['login_record'])) { $GLOBALS['r2mInfo']['dw_task']['login_record'] = []; }
$GLOBALS['r2mInfo']['dw_task']['login_record'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['login_reward_config'])) { $GLOBALS['r2mInfo']['dw_task']['login_reward_config'] = []; }
$GLOBALS['r2mInfo']['dw_task']['login_reward_config'] += array (
  'key' => 'shop_id,login_days',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['share_visitor_record'])) { $GLOBALS['r2mInfo']['dw_task']['share_visitor_record'] = []; }
$GLOBALS['r2mInfo']['dw_task']['share_visitor_record'] += array (
  'key' => 'record_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['short_url'])) { $GLOBALS['r2mInfo']['dw_task']['short_url'] = []; }
$GLOBALS['r2mInfo']['dw_task']['short_url'] += array (
  'key' => 'url_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['sign_record'])) { $GLOBALS['r2mInfo']['dw_task']['sign_record'] = []; }
$GLOBALS['r2mInfo']['dw_task']['sign_record'] += array (
  'key' => 'id',
  'ttl' => '172800',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['task'])) { $GLOBALS['r2mInfo']['dw_task']['task'] = []; }
$GLOBALS['r2mInfo']['dw_task']['task'] += array (
  'all_key' => 'shop_id,task_id',
  'key' => 'task_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['task_execute_record'])) { $GLOBALS['r2mInfo']['dw_task']['task_execute_record'] = []; }
$GLOBALS['r2mInfo']['dw_task']['task_execute_record'] += array (
  'key' => 'record_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['task_record'])) { $GLOBALS['r2mInfo']['dw_task']['task_record'] = []; }
$GLOBALS['r2mInfo']['dw_task']['task_record'] += array (
  'key' => 'id',
  'ttl' => '300',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['user'])) { $GLOBALS['r2mInfo']['dw_task']['user'] = []; }
$GLOBALS['r2mInfo']['dw_task']['user'] += array (
  'key' => 'shop_id, user_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['dw_task']['user_task'])) { $GLOBALS['r2mInfo']['dw_task']['user_task'] = []; }
$GLOBALS['r2mInfo']['dw_task']['user_task'] += array (
  'key' => 'yyuid,task_id,create_date',
  'ttl' => '300',
);
if (!isset($GLOBALS['r2mInfo']['fhvideo_home']['appChannel'])) { $GLOBALS['r2mInfo']['fhvideo_home']['appChannel'] = []; }
$GLOBALS['r2mInfo']['fhvideo_home']['appChannel'] += array (
  'key' => 'id',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['app_map'])) { $GLOBALS['r2mInfo']['fit_db']['app_map'] = []; }
$GLOBALS['r2mInfo']['fit_db']['app_map'] += array (
  'key' => 'app_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['course'])) { $GLOBALS['r2mInfo']['fit_db']['course'] = []; }
$GLOBALS['r2mInfo']['fit_db']['course'] += array (
  'all_key' => 'gym_id',
  'key' => 'course_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['course_vote_log'])) { $GLOBALS['r2mInfo']['fit_db']['course_vote_log'] = []; }
$GLOBALS['r2mInfo']['fit_db']['course_vote_log'] += array (
  'key' => 'log_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['group_course'])) { $GLOBALS['r2mInfo']['fit_db']['group_course'] = []; }
$GLOBALS['r2mInfo']['fit_db']['group_course'] += array (
  'all_key' => 'gym_id',
  'key' => 'group_course_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['gym'])) { $GLOBALS['r2mInfo']['fit_db']['gym'] = []; }
$GLOBALS['r2mInfo']['fit_db']['gym'] += array (
  'key' => 'gym_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['gym_ad'])) { $GLOBALS['r2mInfo']['fit_db']['gym_ad'] = []; }
$GLOBALS['r2mInfo']['fit_db']['gym_ad'] += array (
  'key' => 'ad_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['gym_card'])) { $GLOBALS['r2mInfo']['fit_db']['gym_card'] = []; }
$GLOBALS['r2mInfo']['fit_db']['gym_card'] += array (
  'key' => 'card_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['gym_subbranch'])) { $GLOBALS['r2mInfo']['fit_db']['gym_subbranch'] = []; }
$GLOBALS['r2mInfo']['fit_db']['gym_subbranch'] += array (
  'key' => 'subbranch_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['gym_user'])) { $GLOBALS['r2mInfo']['fit_db']['gym_user'] = []; }
$GLOBALS['r2mInfo']['fit_db']['gym_user'] += array (
  'all_key' => 'gym_id',
  'key' => 'user_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['gym_user_info'])) { $GLOBALS['r2mInfo']['fit_db']['gym_user_info'] = []; }
$GLOBALS['r2mInfo']['fit_db']['gym_user_info'] += array (
  'key' => 'user_id,gym_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['music'])) { $GLOBALS['r2mInfo']['fit_db']['music'] = []; }
$GLOBALS['r2mInfo']['fit_db']['music'] += array (
  'key' => 'qq_songmid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['music_share'])) { $GLOBALS['r2mInfo']['fit_db']['music_share'] = []; }
$GLOBALS['r2mInfo']['fit_db']['music_share'] += array (
  'key' => 'music_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['order_finance'])) { $GLOBALS['r2mInfo']['fit_db']['order_finance'] = []; }
$GLOBALS['r2mInfo']['fit_db']['order_finance'] += array (
  'all_key' => 'finance_order_sn',
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['place'])) { $GLOBALS['r2mInfo']['fit_db']['place'] = []; }
$GLOBALS['r2mInfo']['fit_db']['place'] += array (
  'all_key' => 'gym_id',
  'key' => 'place_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['post'])) { $GLOBALS['r2mInfo']['fit_db']['post'] = []; }
$GLOBALS['r2mInfo']['fit_db']['post'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['post_comment'])) { $GLOBALS['r2mInfo']['fit_db']['post_comment'] = []; }
$GLOBALS['r2mInfo']['fit_db']['post_comment'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['post_media'])) { $GLOBALS['r2mInfo']['fit_db']['post_media'] = []; }
$GLOBALS['r2mInfo']['fit_db']['post_media'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['post_support'])) { $GLOBALS['r2mInfo']['fit_db']['post_support'] = []; }
$GLOBALS['r2mInfo']['fit_db']['post_support'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['post_tag'])) { $GLOBALS['r2mInfo']['fit_db']['post_tag'] = []; }
$GLOBALS['r2mInfo']['fit_db']['post_tag'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['region'])) { $GLOBALS['r2mInfo']['fit_db']['region'] = []; }
$GLOBALS['r2mInfo']['fit_db']['region'] += array (
  'all_key' => 'parent_id',
  'key' => 'region_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['trade_log'])) { $GLOBALS['r2mInfo']['fit_db']['trade_log'] = []; }
$GLOBALS['r2mInfo']['fit_db']['trade_log'] += array (
  'key' => 'log_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['fit_db']['weixinOpenId'])) { $GLOBALS['r2mInfo']['fit_db']['weixinOpenId'] = []; }
$GLOBALS['r2mInfo']['fit_db']['weixinOpenId'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['app_tools'])) { $GLOBALS['r2mInfo']['hiyd_cms']['app_tools'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['app_tools'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['articles'])) { $GLOBALS['r2mInfo']['hiyd_cms']['articles'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['articles'] += array (
  'key' => 'aid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['articles_keyword'])) { $GLOBALS['r2mInfo']['hiyd_cms']['articles_keyword'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['articles_keyword'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['articles_tags'])) { $GLOBALS['r2mInfo']['hiyd_cms']['articles_tags'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['articles_tags'] += array (
  'key' => 'aid,tid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['audio_comment'])) { $GLOBALS['r2mInfo']['hiyd_cms']['audio_comment'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['audio_comment'] += array (
  'key' => 'course_id,day,index,gender',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['bb_course'])) { $GLOBALS['r2mInfo']['hiyd_cms']['bb_course'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['bb_course'] += array (
  'key' => 'bb_cid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['bb_course_day_info'])) { $GLOBALS['r2mInfo']['hiyd_cms']['bb_course_day_info'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['bb_course_day_info'] += array (
  'key' => 'bb_cid,day',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['bb_course_exercise'])) { $GLOBALS['r2mInfo']['hiyd_cms']['bb_course_exercise'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['bb_course_exercise'] += array (
  'key' => 'bb_cid,day,workout_id,group_sequence,sort',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['bb_course_group'])) { $GLOBALS['r2mInfo']['hiyd_cms']['bb_course_group'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['bb_course_group'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['bb_exercise_desc'])) { $GLOBALS['r2mInfo']['hiyd_cms']['bb_exercise_desc'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['bb_exercise_desc'] += array (
  'key' => 'desc_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['bb_exercise_workout'])) { $GLOBALS['r2mInfo']['hiyd_cms']['bb_exercise_workout'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['bb_exercise_workout'] += array (
  'key' => 'workout_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['bb_relational_video'])) { $GLOBALS['r2mInfo']['hiyd_cms']['bb_relational_video'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['bb_relational_video'] += array (
  'key' => 'bb_cid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['bb_super_group'])) { $GLOBALS['r2mInfo']['hiyd_cms']['bb_super_group'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['bb_super_group'] += array (
  'key' => 'group_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['bb_training_point'])) { $GLOBALS['r2mInfo']['hiyd_cms']['bb_training_point'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['bb_training_point'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['category'])) { $GLOBALS['r2mInfo']['hiyd_cms']['category'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['category'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['com_comments'])) { $GLOBALS['r2mInfo']['hiyd_cms']['com_comments'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['com_comments'] += array (
  'key' => 'app_id,comment_target,comment_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['com_replies'])) { $GLOBALS['r2mInfo']['hiyd_cms']['com_replies'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['com_replies'] += array (
  'key' => 'comment_id,reply_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['com_support'])) { $GLOBALS['r2mInfo']['hiyd_cms']['com_support'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['com_support'] += array (
  'key' => 'comment_id,uid,support_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['course'])) { $GLOBALS['r2mInfo']['hiyd_cms']['course'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['course'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['day_info'])) { $GLOBALS['r2mInfo']['hiyd_cms']['day_info'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['day_info'] += array (
  'key' => 'course_id,day,gender',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['equipment'])) { $GLOBALS['r2mInfo']['hiyd_cms']['equipment'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['equipment'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['exercise'])) { $GLOBALS['r2mInfo']['hiyd_cms']['exercise'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['exercise'] += array (
  'key' => 'id',
  'table' => 'exercise,v_course_exercises,v_workout_exercises',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['file'])) { $GLOBALS['r2mInfo']['hiyd_cms']['file'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['file'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['file_dw_video'])) { $GLOBALS['r2mInfo']['hiyd_cms']['file_dw_video'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['file_dw_video'] += array (
  'key' => 'vid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['muscle'])) { $GLOBALS['r2mInfo']['hiyd_cms']['muscle'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['muscle'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['plan'])) { $GLOBALS['r2mInfo']['hiyd_cms']['plan'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['plan'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['plan_base'])) { $GLOBALS['r2mInfo']['hiyd_cms']['plan_base'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['plan_base'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['plan_body'])) { $GLOBALS['r2mInfo']['hiyd_cms']['plan_body'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['plan_body'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['plan_info'])) { $GLOBALS['r2mInfo']['hiyd_cms']['plan_info'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['plan_info'] += array (
  'key' => 'plan_id,plan_day',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['plan_target'])) { $GLOBALS['r2mInfo']['hiyd_cms']['plan_target'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['plan_target'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['relational_course'])) { $GLOBALS['r2mInfo']['hiyd_cms']['relational_course'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['relational_course'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['r_exercise_group'])) { $GLOBALS['r2mInfo']['hiyd_cms']['r_exercise_group'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['r_exercise_group'] += array (
  'key' => 'exercise_id,group_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['r_plan'])) { $GLOBALS['r2mInfo']['hiyd_cms']['r_plan'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['r_plan'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['sns_post_user'])) { $GLOBALS['r2mInfo']['hiyd_cms']['sns_post_user'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['sns_post_user'] += array (
  'key' => 'uid,sub_uid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['sns_region'])) { $GLOBALS['r2mInfo']['hiyd_cms']['sns_region'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['sns_region'] += array (
  'key' => 'region_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['tags'])) { $GLOBALS['r2mInfo']['hiyd_cms']['tags'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['tags'] += array (
  'key' => 'tid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['template'])) { $GLOBALS['r2mInfo']['hiyd_cms']['template'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['template'] += array (
  'key' => 'tpl_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['topic_url'])) { $GLOBALS['r2mInfo']['hiyd_cms']['topic_url'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['topic_url'] += array (
  'key' => 'tpl_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['training_point'])) { $GLOBALS['r2mInfo']['hiyd_cms']['training_point'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['training_point'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['v_bb_course'])) { $GLOBALS['r2mInfo']['hiyd_cms']['v_bb_course'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['v_bb_course'] += array (
  'key' => 'bb_cid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['v_course'])) { $GLOBALS['r2mInfo']['hiyd_cms']['v_course'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['v_course'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['v_course_exercises'])) { $GLOBALS['r2mInfo']['hiyd_cms']['v_course_exercises'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['v_course_exercises'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['v_workout_exercises'])) { $GLOBALS['r2mInfo']['hiyd_cms']['v_workout_exercises'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['v_workout_exercises'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_cms']['workout'])) { $GLOBALS['r2mInfo']['hiyd_cms']['workout'] = []; }
$GLOBALS['r2mInfo']['hiyd_cms']['workout'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['ad'])) { $GLOBALS['r2mInfo']['hiyd_home']['ad'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['ad'] += array (
  'key' => 'ad_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['badge'])) { $GLOBALS['r2mInfo']['hiyd_home']['badge'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['badge'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['badgeRecord'])) { $GLOBALS['r2mInfo']['hiyd_home']['badgeRecord'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['badgeRecord'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['black_domain_ip'])) { $GLOBALS['r2mInfo']['hiyd_home']['black_domain_ip'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['black_domain_ip'] += array (
  'key' => 'ad_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['food_group'])) { $GLOBALS['r2mInfo']['hiyd_home']['food_group'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['food_group'] += array (
  'key' => 'group_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['food_info'])) { $GLOBALS['r2mInfo']['hiyd_home']['food_info'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['food_info'] += array (
  'key' => 'info_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['food_restaurant'])) { $GLOBALS['r2mInfo']['hiyd_home']['food_restaurant'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['food_restaurant'] += array (
  'key' => 'restaurant_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['health_report'])) { $GLOBALS['r2mInfo']['hiyd_home']['health_report'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['health_report'] += array (
  'key' => 'report_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['health_report_app'])) { $GLOBALS['r2mInfo']['hiyd_home']['health_report_app'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['health_report_app'] += array (
  'key' => 'report_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['mission'])) { $GLOBALS['r2mInfo']['hiyd_home']['mission'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['mission'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['post'])) { $GLOBALS['r2mInfo']['hiyd_home']['post'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['post'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['postComment'])) { $GLOBALS['r2mInfo']['hiyd_home']['postComment'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['postComment'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['postMedia'])) { $GLOBALS['r2mInfo']['hiyd_home']['postMedia'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['postMedia'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['share_visitor_record'])) { $GLOBALS['r2mInfo']['hiyd_home']['share_visitor_record'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['share_visitor_record'] += array (
  'key' => 'share_uid,device_token',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['user'])) { $GLOBALS['r2mInfo']['hiyd_home']['user'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['user'] += array (
  'key' => 'user_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['userAccount'])) { $GLOBALS['r2mInfo']['hiyd_home']['userAccount'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['userAccount'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['userCustomExerciseRecord'])) { $GLOBALS['r2mInfo']['hiyd_home']['userCustomExerciseRecord'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['userCustomExerciseRecord'] += array (
  'key' => 'uid,bid,courseDay,type',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['userFinishedPlanCourse'])) { $GLOBALS['r2mInfo']['hiyd_home']['userFinishedPlanCourse'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['userFinishedPlanCourse'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['userFinishedStep'])) { $GLOBALS['r2mInfo']['hiyd_home']['userFinishedStep'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['userFinishedStep'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['userFinishedWorkout'])) { $GLOBALS['r2mInfo']['hiyd_home']['userFinishedWorkout'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['userFinishedWorkout'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['userFoodRecommend'])) { $GLOBALS['r2mInfo']['hiyd_home']['userFoodRecommend'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['userFoodRecommend'] += array (
  'key' => 'uid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['userJoinBBCourse'])) { $GLOBALS['r2mInfo']['hiyd_home']['userJoinBBCourse'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['userJoinBBCourse'] += array (
  'key' => 'uid,bb_cid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['userJoinCourse'])) { $GLOBALS['r2mInfo']['hiyd_home']['userJoinCourse'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['userJoinCourse'] += array (
  'key' => 'uid,courseId',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['userJoinWorkout'])) { $GLOBALS['r2mInfo']['hiyd_home']['userJoinWorkout'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['userJoinWorkout'] += array (
  'key' => 'uid,workoutId',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['userSharePlan'])) { $GLOBALS['r2mInfo']['hiyd_home']['userSharePlan'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['userSharePlan'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['user_trained_statistics_daily'])) { $GLOBALS['r2mInfo']['hiyd_home']['user_trained_statistics_daily'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['user_trained_statistics_daily'] += array (
  'key' => 'uid,trained_date',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['user_trained_statistics_monthly'])) { $GLOBALS['r2mInfo']['hiyd_home']['user_trained_statistics_monthly'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['user_trained_statistics_monthly'] += array (
  'key' => 'uid,trained_year,month_index',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['user_trained_statistics_weekly'])) { $GLOBALS['r2mInfo']['hiyd_home']['user_trained_statistics_weekly'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['user_trained_statistics_weekly'] += array (
  'key' => 'uid,trained_year,week_index',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_home']['v_user'])) { $GLOBALS['r2mInfo']['hiyd_home']['v_user'] = []; }
$GLOBALS['r2mInfo']['hiyd_home']['v_user'] += array (
  'key' => 'user_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['ad'])) { $GLOBALS['r2mInfo']['hiyd_meal']['ad'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['ad'] += array (
  'key' => 'ad_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['admin_log'])) { $GLOBALS['r2mInfo']['hiyd_meal']['admin_log'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['admin_log'] += array (
  'key' => 'log_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['attribute'])) { $GLOBALS['r2mInfo']['hiyd_meal']['attribute'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['attribute'] += array (
  'key' => 'attr_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['back_order'])) { $GLOBALS['r2mInfo']['hiyd_meal']['back_order'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['back_order'] += array (
  'key' => 'back_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['cart'])) { $GLOBALS['r2mInfo']['hiyd_meal']['cart'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['cart'] += array (
  'all_key' => 'user_id',
  'key' => 'cart_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['collect_goods'])) { $GLOBALS['r2mInfo']['hiyd_meal']['collect_goods'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['collect_goods'] += array (
  'all_key' => 'user_id',
  'key' => 'collect_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['coupons'])) { $GLOBALS['r2mInfo']['hiyd_meal']['coupons'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['coupons'] += array (
  'key' => 'coupon_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['delivery_order'])) { $GLOBALS['r2mInfo']['hiyd_meal']['delivery_order'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['delivery_order'] += array (
  'key' => 'delivery_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['food_recommend'])) { $GLOBALS['r2mInfo']['hiyd_meal']['food_recommend'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['food_recommend'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['gift'])) { $GLOBALS['r2mInfo']['hiyd_meal']['gift'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['gift'] += array (
  'key' => 'gift_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['goods'])) { $GLOBALS['r2mInfo']['hiyd_meal']['goods'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['goods'] += array (
  'key' => 'goods_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['goods_gallery'])) { $GLOBALS['r2mInfo']['hiyd_meal']['goods_gallery'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['goods_gallery'] += array (
  'key' => 'img_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['meals_daily'])) { $GLOBALS['r2mInfo']['hiyd_meal']['meals_daily'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['meals_daily'] += array (
  'key' => 'meals_day,goods_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['meals_delivery_log'])) { $GLOBALS['r2mInfo']['hiyd_meal']['meals_delivery_log'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['meals_delivery_log'] += array (
  'key' => 'log_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['meals_item'])) { $GLOBALS['r2mInfo']['hiyd_meal']['meals_item'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['meals_item'] += array (
  'key' => 'item_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['money_record'])) { $GLOBALS['r2mInfo']['hiyd_meal']['money_record'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['money_record'] += array (
  'key' => 'rec_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['order_action'])) { $GLOBALS['r2mInfo']['hiyd_meal']['order_action'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['order_action'] += array (
  'key' => 'action_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['order_finance'])) { $GLOBALS['r2mInfo']['hiyd_meal']['order_finance'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['order_finance'] += array (
  'all_key' => 'finance_order_sn',
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['order_info'])) { $GLOBALS['r2mInfo']['hiyd_meal']['order_info'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['order_info'] += array (
  'all_key' => 'user_id',
  'key' => 'order_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['products'])) { $GLOBALS['r2mInfo']['hiyd_meal']['products'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['products'] += array (
  'key' => 'product_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['recharge_order_finance'])) { $GLOBALS['r2mInfo']['hiyd_meal']['recharge_order_finance'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['recharge_order_finance'] += array (
  'all_key' => 'finance_order_sn',
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['region'])) { $GLOBALS['r2mInfo']['hiyd_meal']['region'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['region'] += array (
  'all_key' => 'parent_id',
  'key' => 'region_id',
  'ttl' => '0',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['r_shop_company'])) { $GLOBALS['r2mInfo']['hiyd_meal']['r_shop_company'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['r_shop_company'] += array (
  'key' => 'shop_id,company_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['shipping'])) { $GLOBALS['r2mInfo']['hiyd_meal']['shipping'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['shipping'] += array (
  'key' => 'shipping_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['shipping_area'])) { $GLOBALS['r2mInfo']['hiyd_meal']['shipping_area'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['shipping_area'] += array (
  'key' => 'shipping_area_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['shipping_company'])) { $GLOBALS['r2mInfo']['hiyd_meal']['shipping_company'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['shipping_company'] += array (
  'key' => 'company_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['shop'])) { $GLOBALS['r2mInfo']['hiyd_meal']['shop'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['shop'] += array (
  'key' => 'shop_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['shop_bulletin'])) { $GLOBALS['r2mInfo']['hiyd_meal']['shop_bulletin'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['shop_bulletin'] += array (
  'key' => 'bulletin_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['shop_minus'])) { $GLOBALS['r2mInfo']['hiyd_meal']['shop_minus'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['shop_minus'] += array (
  'key' => 'minus_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['star_card'])) { $GLOBALS['r2mInfo']['hiyd_meal']['star_card'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['star_card'] += array (
  'key' => 'card_id',
  'ttl' => '0',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['suppliers'])) { $GLOBALS['r2mInfo']['hiyd_meal']['suppliers'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['suppliers'] += array (
  'key' => 'suppliers_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['user'])) { $GLOBALS['r2mInfo']['hiyd_meal']['user'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['user'] += array (
  'key' => 'user_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['user_coupons'])) { $GLOBALS['r2mInfo']['hiyd_meal']['user_coupons'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['user_coupons'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['user_info'])) { $GLOBALS['r2mInfo']['hiyd_meal']['user_info'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['user_info'] += array (
  'key' => 'user_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['user_message'])) { $GLOBALS['r2mInfo']['hiyd_meal']['user_message'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['user_message'] += array (
  'all_key' => 'user_id',
  'key' => 'msg_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['web_recommend_group'])) { $GLOBALS['r2mInfo']['hiyd_meal']['web_recommend_group'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['web_recommend_group'] += array (
  'key' => 'group_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['web_recommend_item'])) { $GLOBALS['r2mInfo']['hiyd_meal']['web_recommend_item'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['web_recommend_item'] += array (
  'key' => 'item_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_meal']['web_shop_ad'])) { $GLOBALS['r2mInfo']['hiyd_meal']['web_shop_ad'] = []; }
$GLOBALS['r2mInfo']['hiyd_meal']['web_shop_ad'] += array (
  'key' => 'ad_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['ad'])) { $GLOBALS['r2mInfo']['hiyd_shop']['ad'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['ad'] += array (
  'key' => 'ad_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['admin_log'])) { $GLOBALS['r2mInfo']['hiyd_shop']['admin_log'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['admin_log'] += array (
  'key' => 'log_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['attribute'])) { $GLOBALS['r2mInfo']['hiyd_shop']['attribute'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['attribute'] += array (
  'key' => 'attr_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['back_order'])) { $GLOBALS['r2mInfo']['hiyd_shop']['back_order'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['back_order'] += array (
  'key' => 'back_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['cart'])) { $GLOBALS['r2mInfo']['hiyd_shop']['cart'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['cart'] += array (
  'all_key' => 'user_id',
  'key' => 'cart_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['collect_goods'])) { $GLOBALS['r2mInfo']['hiyd_shop']['collect_goods'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['collect_goods'] += array (
  'all_key' => 'user_id',
  'key' => 'collect_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['coupons'])) { $GLOBALS['r2mInfo']['hiyd_shop']['coupons'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['coupons'] += array (
  'key' => 'coupon_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['delivery_order'])) { $GLOBALS['r2mInfo']['hiyd_shop']['delivery_order'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['delivery_order'] += array (
  'key' => 'delivery_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['food_recommend'])) { $GLOBALS['r2mInfo']['hiyd_shop']['food_recommend'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['food_recommend'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['gift'])) { $GLOBALS['r2mInfo']['hiyd_shop']['gift'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['gift'] += array (
  'key' => 'gift_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['goods'])) { $GLOBALS['r2mInfo']['hiyd_shop']['goods'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['goods'] += array (
  'key' => 'goods_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['goods_gallery'])) { $GLOBALS['r2mInfo']['hiyd_shop']['goods_gallery'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['goods_gallery'] += array (
  'key' => 'img_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['meals_delivery_log'])) { $GLOBALS['r2mInfo']['hiyd_shop']['meals_delivery_log'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['meals_delivery_log'] += array (
  'key' => 'log_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['meals_item'])) { $GLOBALS['r2mInfo']['hiyd_shop']['meals_item'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['meals_item'] += array (
  'key' => 'item_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['money_record'])) { $GLOBALS['r2mInfo']['hiyd_shop']['money_record'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['money_record'] += array (
  'key' => 'rec_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['order_action'])) { $GLOBALS['r2mInfo']['hiyd_shop']['order_action'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['order_action'] += array (
  'key' => 'action_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['order_finance'])) { $GLOBALS['r2mInfo']['hiyd_shop']['order_finance'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['order_finance'] += array (
  'all_key' => 'finance_order_sn',
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['order_info'])) { $GLOBALS['r2mInfo']['hiyd_shop']['order_info'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['order_info'] += array (
  'all_key' => 'user_id',
  'key' => 'order_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['products'])) { $GLOBALS['r2mInfo']['hiyd_shop']['products'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['products'] += array (
  'key' => 'product_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['region'])) { $GLOBALS['r2mInfo']['hiyd_shop']['region'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['region'] += array (
  'all_key' => 'parent_id',
  'key' => 'region_id',
  'ttl' => '0',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['r_shop_company'])) { $GLOBALS['r2mInfo']['hiyd_shop']['r_shop_company'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['r_shop_company'] += array (
  'key' => 'shop_id,company_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['shipping'])) { $GLOBALS['r2mInfo']['hiyd_shop']['shipping'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['shipping'] += array (
  'key' => 'shipping_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['shipping_area'])) { $GLOBALS['r2mInfo']['hiyd_shop']['shipping_area'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['shipping_area'] += array (
  'key' => 'shipping_area_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['shipping_company'])) { $GLOBALS['r2mInfo']['hiyd_shop']['shipping_company'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['shipping_company'] += array (
  'key' => 'company_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['shop'])) { $GLOBALS['r2mInfo']['hiyd_shop']['shop'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['shop'] += array (
  'key' => 'shop_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['shop_bulletin'])) { $GLOBALS['r2mInfo']['hiyd_shop']['shop_bulletin'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['shop_bulletin'] += array (
  'key' => 'bulletin_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['shop_minus'])) { $GLOBALS['r2mInfo']['hiyd_shop']['shop_minus'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['shop_minus'] += array (
  'key' => 'minus_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['star_card'])) { $GLOBALS['r2mInfo']['hiyd_shop']['star_card'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['star_card'] += array (
  'key' => 'card_id',
  'ttl' => '0',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['suppliers'])) { $GLOBALS['r2mInfo']['hiyd_shop']['suppliers'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['suppliers'] += array (
  'key' => 'suppliers_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['user'])) { $GLOBALS['r2mInfo']['hiyd_shop']['user'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['user'] += array (
  'key' => 'user_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['user_coupons'])) { $GLOBALS['r2mInfo']['hiyd_shop']['user_coupons'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['user_coupons'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['user_exchange_log'])) { $GLOBALS['r2mInfo']['hiyd_shop']['user_exchange_log'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['user_exchange_log'] += array (
  'key' => 'log_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['user_info'])) { $GLOBALS['r2mInfo']['hiyd_shop']['user_info'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['user_info'] += array (
  'key' => 'user_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['user_message'])) { $GLOBALS['r2mInfo']['hiyd_shop']['user_message'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['user_message'] += array (
  'all_key' => 'user_id',
  'key' => 'msg_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['web_recommend_group'])) { $GLOBALS['r2mInfo']['hiyd_shop']['web_recommend_group'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['web_recommend_group'] += array (
  'key' => 'group_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['web_recommend_item'])) { $GLOBALS['r2mInfo']['hiyd_shop']['web_recommend_item'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['web_recommend_item'] += array (
  'key' => 'item_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['hiyd_shop']['web_shop_ad'])) { $GLOBALS['r2mInfo']['hiyd_shop']['web_shop_ad'] = []; }
$GLOBALS['r2mInfo']['hiyd_shop']['web_shop_ad'] += array (
  'key' => 'ad_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['o2o_store']['bodybuilding'])) { $GLOBALS['r2mInfo']['o2o_store']['bodybuilding'] = []; }
$GLOBALS['r2mInfo']['o2o_store']['bodybuilding'] += array (
  'key' => 'auto_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['oujhome']['chapterHistory'])) { $GLOBALS['r2mInfo']['oujhome']['chapterHistory'] = []; }
$GLOBALS['r2mInfo']['oujhome']['chapterHistory'] += array (
  'all_key' => 'uid,device_token,bookId',
  'key' => 'chapter_history_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['oujhome']['news'])) { $GLOBALS['r2mInfo']['oujhome']['news'] = []; }
$GLOBALS['r2mInfo']['oujhome']['news'] += array (
  'key' => 'news_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['oujhome']['recommendBook'])) { $GLOBALS['r2mInfo']['oujhome']['recommendBook'] = []; }
$GLOBALS['r2mInfo']['oujhome']['recommendBook'] += array (
  'key' => 'rec_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['ad'])) { $GLOBALS['r2mInfo']['shop_base']['ad'] = []; }
$GLOBALS['r2mInfo']['shop_base']['ad'] += array (
  'key' => 'ad_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['attribute'])) { $GLOBALS['r2mInfo']['shop_base']['attribute'] = []; }
$GLOBALS['r2mInfo']['shop_base']['attribute'] += array (
  'key' => 'attr_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['category'])) { $GLOBALS['r2mInfo']['shop_base']['category'] = []; }
$GLOBALS['r2mInfo']['shop_base']['category'] += array (
  'key' => 'cat_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['gift'])) { $GLOBALS['r2mInfo']['shop_base']['gift'] = []; }
$GLOBALS['r2mInfo']['shop_base']['gift'] += array (
  'key' => 'gift_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['goods'])) { $GLOBALS['r2mInfo']['shop_base']['goods'] = []; }
$GLOBALS['r2mInfo']['shop_base']['goods'] += array (
  'key' => 'goods_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['goods_gallery'])) { $GLOBALS['r2mInfo']['shop_base']['goods_gallery'] = []; }
$GLOBALS['r2mInfo']['shop_base']['goods_gallery'] += array (
  'key' => 'img_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['products'])) { $GLOBALS['r2mInfo']['shop_base']['products'] = []; }
$GLOBALS['r2mInfo']['shop_base']['products'] += array (
  'key' => 'product_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['region'])) { $GLOBALS['r2mInfo']['shop_base']['region'] = []; }
$GLOBALS['r2mInfo']['shop_base']['region'] += array (
  'all_key' => 'parent_id',
  'key' => 'region_id',
  'ttl' => '0',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['r_shop_company'])) { $GLOBALS['r2mInfo']['shop_base']['r_shop_company'] = []; }
$GLOBALS['r2mInfo']['shop_base']['r_shop_company'] += array (
  'key' => 'shop_id,company_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['shipping'])) { $GLOBALS['r2mInfo']['shop_base']['shipping'] = []; }
$GLOBALS['r2mInfo']['shop_base']['shipping'] += array (
  'key' => 'shipping_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['shipping_area'])) { $GLOBALS['r2mInfo']['shop_base']['shipping_area'] = []; }
$GLOBALS['r2mInfo']['shop_base']['shipping_area'] += array (
  'key' => 'shipping_area_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['shipping_company'])) { $GLOBALS['r2mInfo']['shop_base']['shipping_company'] = []; }
$GLOBALS['r2mInfo']['shop_base']['shipping_company'] += array (
  'key' => 'company_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['shop'])) { $GLOBALS['r2mInfo']['shop_base']['shop'] = []; }
$GLOBALS['r2mInfo']['shop_base']['shop'] += array (
  'key' => 'shop_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['shop_bulletin'])) { $GLOBALS['r2mInfo']['shop_base']['shop_bulletin'] = []; }
$GLOBALS['r2mInfo']['shop_base']['shop_bulletin'] += array (
  'key' => 'bulletin_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['shop_minus'])) { $GLOBALS['r2mInfo']['shop_base']['shop_minus'] = []; }
$GLOBALS['r2mInfo']['shop_base']['shop_minus'] += array (
  'key' => 'minus_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['suppliers'])) { $GLOBALS['r2mInfo']['shop_base']['suppliers'] = []; }
$GLOBALS['r2mInfo']['shop_base']['suppliers'] += array (
  'key' => 'suppliers_id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['shop_base']['tpl_group'])) { $GLOBALS['r2mInfo']['shop_base']['tpl_group'] = []; }
$GLOBALS['r2mInfo']['shop_base']['tpl_group'] += array (
  'key' => 'tpl_goods_id',
  'ttl' => '3600',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['admin'])) { $GLOBALS['r2mInfo']['svideo_home']['admin'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['admin'] += array (
  'key' => 'uid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['adminMediaAccount'])) { $GLOBALS['r2mInfo']['svideo_home']['adminMediaAccount'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['adminMediaAccount'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['article'])) { $GLOBALS['r2mInfo']['svideo_home']['article'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['article'] += array (
  'key' => 'uid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['articleAuditRecord'])) { $GLOBALS['r2mInfo']['svideo_home']['articleAuditRecord'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['articleAuditRecord'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['articleTag'])) { $GLOBALS['r2mInfo']['svideo_home']['articleTag'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['articleTag'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['articleTagWait'])) { $GLOBALS['r2mInfo']['svideo_home']['articleTagWait'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['articleTagWait'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['articleWait'])) { $GLOBALS['r2mInfo']['svideo_home']['articleWait'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['articleWait'] += array (
  'key' => 'uid',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['mediaAccount'])) { $GLOBALS['r2mInfo']['svideo_home']['mediaAccount'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['mediaAccount'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['notice'])) { $GLOBALS['r2mInfo']['svideo_home']['notice'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['notice'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['tag'])) { $GLOBALS['r2mInfo']['svideo_home']['tag'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['tag'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
if (!isset($GLOBALS['r2mInfo']['svideo_home']['tagUserMap'])) { $GLOBALS['r2mInfo']['svideo_home']['tagUserMap'] = []; }
$GLOBALS['r2mInfo']['svideo_home']['tagUserMap'] += array (
  'key' => 'id',
  'ttl' => '86400',
);
