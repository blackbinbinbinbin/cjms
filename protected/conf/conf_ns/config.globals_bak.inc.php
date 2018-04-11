<?php
//常量
define_2('URL_USER_API') || define_1('URL_USER_API', 'http://test.user.api.oxzj.net/');
define_2('SNS_TEST_UID') || define_1('SNS_TEST_UID', 1);
define_2('SNS_TEST_TOKEN') || define_1('SNS_TEST_TOKEN', 'lucifer_test_token');
define_2('URL_M_OUJ') || define_1('URL_M_OUJ', 'http://test.m.ouj.com/');
define_2('URL_PAY_API') || define_1('URL_PAY_API', 'http://test.pay.api.oxzj.net/');
define_2('URL_SHOP_API') || define_1('URL_SHOP_API', 'http://test.m.meals.hiyd.com/');
define_2('SNS_HOST') || define_1('SNS_HOST', 'test.sns.admin.ouj.com');
define_2('SNS_IP') || define_1('SNS_IP', '61.160.36.225');
define_2('URL_SNS_API') || define_1('URL_SNS_API', 'http://test.api.oxzj.net/');
define_2('REDIS_PRE_KEY_SESSION') || define_1('REDIS_PRE_KEY_SESSION', 'User_Session:');
define_2('SHOP_APP_ID') || define_1('SHOP_APP_ID', 2);
define_2('SHOP_APP_SECRET') || define_1('SHOP_APP_SECRET', 'Kvg!F:#X80r5.p9F');
define_2('SNS_APP_ID') || define_1('SNS_APP_ID', 1);
define_2('SNS_APP_SECRET') || define_1('SNS_APP_SECRET', '$DWEeMLJMj9Xl2^4');
define_2('WX_APP_ID') || define_1('WX_APP_ID', 'wx892910db2cdd2c12');
define_2('WX_APP_SECRE') || define_1('WX_APP_SECRE', 'eed2932f5a29b4f12d2607aa77548949');
define_2('COOKIE_DOMAIN_OUJ') || define_1('COOKIE_DOMAIN_OUJ', 'ouj.com');
define_2('BAIDU_AK') || define_1('BAIDU_AK', 'E3ZEgAwYfHrQvGjBFFonfG7m');
define_2('BS2_ACCESS_KEY') || define_1('BS2_ACCESS_KEY', 'ak_uiz');
define_2('BS2_ACCESS_SECRET') || define_1('BS2_ACCESS_SECRET', 'fd855479b53b6632204c8d0f0b8ed47428265bda');
define_2('BS2_FILE_BUCKET') || define_1('BS2_FILE_BUCKET', 'ojiastoreimage');
define_2('BS2_AUDIO_BUCKET') || define_1('BS2_AUDIO_BUCKET', 'ojiastoreaudio');
define_2('BS2_VIDEO_BUCKET') || define_1('BS2_VIDEO_BUCKET', 'ojiastorevideos');
define_2('BS2_SNS_BUCKET') || define_1('BS2_SNS_BUCKET', 'ojiasnsimage');
define_2('BS2_HOST') || define_1('BS2_HOST', 'bs2ul.yy.com');
define_2('BS2_DL_HOST') || define_1('BS2_DL_HOST', 'bs2dl.huanjuyun.com');
define_2('COOKIE_DOMAIN_HIYD') || define_1('COOKIE_DOMAIN_HIYD', 'hiyd.com');
define_2('REDIS_KEY_DEVICETOKEN_ALL') || define_1('REDIS_KEY_DEVICETOKEN_ALL', 'device_token:all:');
define_2('REDIS_KEY_DEVICETOKEN_STORE') || define_1('REDIS_KEY_DEVICETOKEN_STORE', 'device_token:store:');
define_2('REDIS_ACCESSCODE_PREFIX') || define_1('REDIS_ACCESSCODE_PREFIX', 'ACCESS_CODE:');
define_2('MAX_DEVICETOKEN_GEN') || define_1('MAX_DEVICETOKEN_GEN', 1);
define_2('BS2_DEL_HOST') || define_1('BS2_DEL_HOST', 'bs2.yy.com');
define_2('URL_ADMIN_OUJ') || define_1('URL_ADMIN_OUJ', 'http://test.admin.ouj.com/');
define_2('IP_ADMIN_OUJ') || define_1('IP_ADMIN_OUJ', '61.160.36.225');
define_2('NODE_ID_HIYD') || define_1('NODE_ID_HIYD', 118);
define_2('DOMAIN_ADMIN_OUJ') || define_1('DOMAIN_ADMIN_OUJ', 'test.admin.ouj.com');
define_2('REDIS_KEY_STATIC_ARTICLE_VISIT') || define_1('REDIS_KEY_STATIC_ARTICLE_VISIT', 'globals:static_article_visit_count:');
define_2('REDIS_KEY_ARTICLE_CLICK_RANK') || define_1('REDIS_KEY_ARTICLE_CLICK_RANK', 'globals:article_click_rank:');
define_2('URL_HIYD') || define_1('URL_HIYD', 'http://test.www.hiyd.com/');
define_2('URL_KU_HIYD') || define_1('URL_KU_HIYD', 'http://test.ku.hiyd.com/');
define_2('REDIS_KEY_HEALTH_REPORT_DATA') || define_1('REDIS_KEY_HEALTH_REPORT_DATA', 'globals:health_report_data');
define_2('WX_APP_ID_OUJ') || define_1('WX_APP_ID_OUJ', 'wx892910db2cdd2c12');
define_2('WX_APP_SECRE_OUJ') || define_1('WX_APP_SECRE_OUJ', 'eed2932f5a29b4f12d2607aa77548949');
define_2('REDIS_RRE_KEY_LOGIN_URL') || define_1('REDIS_RRE_KEY_LOGIN_URL', 'globals:login_url:');
define_2('HIYD_QQ_APP_ID') || define_1('HIYD_QQ_APP_ID', 101218804);
define_2('HIYD_QQ_APP_SECRE') || define_1('HIYD_QQ_APP_SECRE', 'c298fb6dd6857c6541dff61ed7370265');
define_2('URL_M_HIYD') || define_1('URL_M_HIYD', 'http://test.m.hiyd.com/');
define_2('REDIS_KEY_HEALTH_REPORT_IMG') || define_1('REDIS_KEY_HEALTH_REPORT_IMG', 'globals:health_report_img');
define_2('REDIS_KEY_ORDERID_POOL') || define_1('REDIS_KEY_ORDERID_POOL', 'global:orderid_pool');
define_2('REDIS_PRE_KEY_ORDER_RANK') || define_1('REDIS_PRE_KEY_ORDER_RANK', 'ORDERRANKED:');
define_2('REDIS_PRE_KEY_WXJSSING2') || define_1('REDIS_PRE_KEY_WXJSSING2', 'wx_jssign2_oauth:');
define_2('REDIS_PRE_KEY_GOODS_ACCESS') || define_1('REDIS_PRE_KEY_GOODS_ACCESS', 'goods_access:');
define_2('REDIS_KEY_USER_ACCESS_MAP') || define_1('REDIS_KEY_USER_ACCESS_MAP', 'global:user_access_map');
define_2('REDIS_PRE_KEY_DEVICE_SESSION') || define_1('REDIS_PRE_KEY_DEVICE_SESSION', 'Device_Session:');
define_2('URL_MOBILE_SHOP') || define_1('URL_MOBILE_SHOP', 'http://test.m.meals.hiyd.com/');
define_2('URL_WEB_SHOP') || define_1('URL_WEB_SHOP', 'http://test.meals.hiyd.com/');
define_2('KUAIDI_CALLBACK') || define_1('KUAIDI_CALLBACK', 'http://test.m.shop.hiyd.com/kuaidi/callback');
define_2('CJMS_IP') || define_1('CJMS_IP', '61.160.36.225');
define_2('CJMS_HOST') || define_1('CJMS_HOST', 'test.admin.ouj.com');
define_2('URL_USER_ORDER') || define_1('URL_USER_ORDER', 'http://shop.ouj.com/main/#!page=order-review&env=test&order_id=');
define_2('WX_APP_ID_MEAL') || define_1('WX_APP_ID_MEAL', 'wx489f3e891508cf09');
define_2('WX_APP_SECRE_MEAL') || define_1('WX_APP_SECRE_MEAL', 'fea0ce52a21fb194422277010cb42580');
define_2('URL_SHOP_API2') || define_1('URL_SHOP_API2', 'http://test.m.shop.hiyd.com/');
define_2('COOKIE_DOMAIN_KU_HIYD') || define_1('COOKIE_DOMAIN_KU_HIYD', 'ku.hiyd.com');
define_2('WX_APP_ID_FIT') || define_1('WX_APP_ID_FIT', 'wxa46e6794bc38e236');
define_2('WX_APP_SECRE_FIT') || define_1('WX_APP_SECRE_FIT', 'cf876b3bbae4b8b2bdb03570c391964f');
define_2('URL_SHOP_API3') || define_1('URL_SHOP_API3', 'http://test.m.fit.hiyd.com/');
define_2('URL_FOOD_HIYD') || define_1('URL_FOOD_HIYD', 'http://test.food.hiyd.com/');
define_2('URL_M_FOOD_HIYD') || define_1('URL_M_FOOD_HIYD', 'http://test.m.food.hiyd.com/');
define_2('URL_SHOP_API_DW') || define_1('URL_SHOP_API_DW', 'http://test.plus.duowan.com/');
define_2('CJMS_HOST_DW') || define_1('CJMS_HOST_DW', 'test.admin.duowan.com');
define_2('KUAIDI_CALLBACK_DW') || define_1('KUAIDI_CALLBACK_DW', 'http://test.hezi.plus.duowan.com/kuaidi/callback');
define_2('URL_SHOP_API_HZ') || define_1('URL_SHOP_API_HZ', 'http://test.hezi.plus.duowan.com/');
define_2('REDIS_KEY_STATIC_USERGOLD') || define_1('REDIS_KEY_STATIC_USERGOLD', 'globals:static_usergold:');
define_2('REDIS_KEY_STATIC_IPTASKS') || define_1('REDIS_KEY_STATIC_IPTASKS', 'globals:static_iptasks');
define_2('REDIS_KEY_TASK_IPLIMITS') || define_1('REDIS_KEY_TASK_IPLIMITS', 200);
define_2('REDIS_KEY_TASK_IPUSERLIMITS') || define_1('REDIS_KEY_TASK_IPUSERLIMITS', 2);
define_2('REDIS_KEY_STATIC_IPUSERS') || define_1('REDIS_KEY_STATIC_IPUSERS', 'globals:static_ipusers');
define_2('REDIS_KEY_HEZI_BLACK_IPS') || define_1('REDIS_KEY_HEZI_BLACK_IPS', 'globals:hezi_black_ips');
define_2('REDIS_KEY_HEZI_BLACK_USERS') || define_1('REDIS_KEY_HEZI_BLACK_USERS', 'globals:hezi_black_users');
define_2('WX_APP_ID_MEAL_v1') || define_1('WX_APP_ID_MEAL_v1', 'wxa46e6794bc38e236');
define_2('WX_APP_SECRE_MEAL_v1') || define_1('WX_APP_SECRE_MEAL_v1', 'cf876b3bbae4b8b2bdb03570c391964f');
define_2('REDIS_KEY_STATIC_TASK_REPORTS') || define_1('REDIS_KEY_STATIC_TASK_REPORTS', 'globals:static_task_reports:');
define_2('EXCHANGE_GOLD_NUM_LIMIT') || define_1('EXCHANGE_GOLD_NUM_LIMIT', 50);
define_2('REDIS_KEY_RECHARGE_SHIPPING') || define_1('REDIS_KEY_RECHARGE_SHIPPING', 'globals:recharge_shipping');
define_2('URL_DW_RECHARGE_CALLBACK') || define_1('URL_DW_RECHARGE_CALLBACK', 'http://test.plus.duowan.com/recharge/callback');
define_2('REDIS_KEY_DW_BLACK_IPS') || define_1('REDIS_KEY_DW_BLACK_IPS', 'globals:dw_black_ips');
define_2('URL_SHOP_API_KA') || define_1('URL_SHOP_API_KA', 'http://test.kaplus.duowan.com/');
define_2('REDIS_KEY_KA_RECEIVE_QUEUE') || define_1('REDIS_KEY_KA_RECEIVE_QUEUE', 'globals:ka_receive_queue:');
define_2('URL_KA') || define_1('URL_KA', 'http://test.ka.duowan.com/');
define_2('REDIS_RRE_KEY_LOTTERY_REWARD_STOCK') || define_1('REDIS_RRE_KEY_LOTTERY_REWARD_STOCK', 'globals:lottery_reward_stock:');
define_2('REDIS_KEY_HEZI_IPPVS') || define_1('REDIS_KEY_HEZI_IPPVS', 'globals:hezi_ippvs');
define_2('URL_KA_HOST') || define_1('URL_KA_HOST', 'test.ka.duowan.com');
define_2('REDIS_KEY_STATIC_TASK_BOOK_GAME') || define_1('REDIS_KEY_STATIC_TASK_BOOK_GAME', 'globals:static_task_book_game');
define_2('URL_ADMIN_DUOWAN') || define_1('URL_ADMIN_DUOWAN', 'http://test.admin.duowan.com/');
define_2('URL_KA_M') || define_1('URL_KA_M', 'http://test.mka.duowan.com/');
define_2('URL_KA_HOST_M') || define_1('URL_KA_HOST_M', 'test.mka.duowan.com');
define_2('WX_APP_ID_DW') || define_1('WX_APP_ID_DW', 'wxb25813d7b9e2147b');
define_2('WX_APP_SECRE_DW') || define_1('WX_APP_SECRE_DW', '51710497377c2bf56e00fe38b161b14d');
define_2('REDIS_PRE_KEY_WX_UNIONID') || define_1('REDIS_PRE_KEY_WX_UNIONID', 'globals:wx_unionid:');
define_2('REDIS_KEY_STATIC_TASK_GIFT_ACTIVE') || define_1('REDIS_KEY_STATIC_TASK_GIFT_ACTIVE', 'globals:static_task_gift_active:');
define_2('URL_H5GAME') || define_1('URL_H5GAME', 'http://test.h5game.5253.com/');
define_2('COOKIE_DOMAIN_DUOWAN') || define_1('COOKIE_DOMAIN_DUOWAN', 'duowan.com');
define_2('BS2_LARGE_FILE_BUCKET') || define_1('BS2_LARGE_FILE_BUCKET', 'ojiaauthorvideos');
define_2('URL_SY') || define_1('URL_SY', 'http://test.sy.duowan.com/');
define_2('URL_SY_M') || define_1('URL_SY_M', 'http://test.sy.duowan.cn/');

//数组
if (!isset($GLOBALS['dbInfo']['default'])) { $GLOBALS['dbInfo']['default'] = []; }
$GLOBALS['dbInfo']['default'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'Web',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['oujhome'])) { $GLOBALS['dbInfo']['oujhome'] = []; }
$GLOBALS['dbInfo']['oujhome'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'ouj_home',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['fit_db'])) { $GLOBALS['dbInfo']['fit_db'] = []; }
$GLOBALS['dbInfo']['fit_db'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'fit_db',
    'dbPass' => 'ojia305',
    'dbPort' => '3306',
    'dbType' => 'mysqli',
    'dbUser' => 'ojiatest',
    'enable' => 'true',
);
if (!isset($GLOBALS['dbInfo']['fit_sns'])) { $GLOBALS['dbInfo']['fit_sns'] = []; }
$GLOBALS['dbInfo']['fit_sns'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'fit_sns',
    'dbPass' => 'ojia305',
    'dbPort' => '3306',
    'dbType' => 'mysqli',
    'dbUser' => 'ojiatest',
    'enable' => 'true',
);
if (!isset($GLOBALS['dbInfo']['Web'])) { $GLOBALS['dbInfo']['Web'] = []; }
$GLOBALS['dbInfo']['Web'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'Web',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['Report'])) { $GLOBALS['dbInfo']['Report'] = []; }
$GLOBALS['dbInfo']['Report'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'Report',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['book_db'])) { $GLOBALS['dbInfo']['book_db'] = []; }
$GLOBALS['dbInfo']['book_db'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'book_db',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['hiyd_home'])) { $GLOBALS['dbInfo']['hiyd_home'] = []; }
$GLOBALS['dbInfo']['hiyd_home'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'hiyd_home',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['hiyd_cms'])) { $GLOBALS['dbInfo']['hiyd_cms'] = []; }
$GLOBALS['dbInfo']['hiyd_cms'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'hiyd_cms',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['fit_web'])) { $GLOBALS['dbInfo']['fit_web'] = []; }
$GLOBALS['dbInfo']['fit_web'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'fit_web',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['fit_report'])) { $GLOBALS['dbInfo']['fit_report'] = []; }
$GLOBALS['dbInfo']['fit_report'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'fit_report',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['hiyd_data'])) { $GLOBALS['dbInfo']['hiyd_data'] = []; }
$GLOBALS['dbInfo']['hiyd_data'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'hiyd_data',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['o2o_store'])) { $GLOBALS['dbInfo']['o2o_store'] = []; }
$GLOBALS['dbInfo']['o2o_store'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'o2o_store',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['hiyd_home_slave'])) { $GLOBALS['dbInfo']['hiyd_home_slave'] = []; }
$GLOBALS['dbInfo']['hiyd_home_slave'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'hiyd_home',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['base_db'])) { $GLOBALS['dbInfo']['base_db'] = []; }
$GLOBALS['dbInfo']['base_db'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'base_db',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['hiyd_cms_test'])) { $GLOBALS['dbInfo']['hiyd_cms_test'] = []; }
$GLOBALS['dbInfo']['hiyd_cms_test'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'hiyd_cms',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['hiyd_meal'])) { $GLOBALS['dbInfo']['hiyd_meal'] = []; }
$GLOBALS['dbInfo']['hiyd_meal'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'hiyd_meal',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['hiyd_shop'])) { $GLOBALS['dbInfo']['hiyd_shop'] = []; }
$GLOBALS['dbInfo']['hiyd_shop'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'hiyd_shop',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['dw_task'])) { $GLOBALS['dbInfo']['dw_task'] = []; }
$GLOBALS['dbInfo']['dw_task'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'dw_task',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['dw_shop'])) { $GLOBALS['dbInfo']['dw_shop'] = []; }
$GLOBALS['dbInfo']['dw_shop'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'dw_shop',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['shop_base'])) { $GLOBALS['dbInfo']['shop_base'] = []; }
$GLOBALS['dbInfo']['shop_base'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'shop_base',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['svideo_home'])) { $GLOBALS['dbInfo']['svideo_home'] = []; }
$GLOBALS['dbInfo']['svideo_home'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'svideo_home',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['svideo_data'])) { $GLOBALS['dbInfo']['svideo_data'] = []; }
$GLOBALS['dbInfo']['svideo_data'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'svideo_data',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['adsystem'])) { $GLOBALS['dbInfo']['adsystem'] = []; }
$GLOBALS['dbInfo']['adsystem'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'adsystem',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['bbs_db'])) { $GLOBALS['dbInfo']['bbs_db'] = []; }
$GLOBALS['dbInfo']['bbs_db'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'bbs_db',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['gamecenter_opt'])) { $GLOBALS['dbInfo']['gamecenter_opt'] = []; }
$GLOBALS['dbInfo']['gamecenter_opt'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '172.27.203.6',
    'dbPort' => '3306',
    'dbName' => 'gamecenter_opt',
    'dbUser' => 'yxtq',
    'dbPass' => 'yxtq@duowan@$',
);
if (!isset($GLOBALS['dbInfo']['5253_tq'])) { $GLOBALS['dbInfo']['5253_tq'] = []; }
$GLOBALS['dbInfo']['5253_tq'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '172.27.203.6',
    'dbPort' => '3306',
    'dbName' => 'db_groundhog_new',
    'dbUser' => 'yxtq',
    'dbPass' => 'yxtq@duowan@$',
);
if (!isset($GLOBALS['dbInfo']['dw_ka'])) { $GLOBALS['dbInfo']['dw_ka'] = []; }
$GLOBALS['dbInfo']['dw_ka'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_ka',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['dw_video'])) { $GLOBALS['dbInfo']['dw_video'] = []; }
$GLOBALS['dbInfo']['dw_video'] += array (
    'dbHost' => '115.238.171.70',
    'dbName' => 'video_dw',
    'dbPass' => '38v1axmW5x',
    'dbUser' => 'net_fanhe_r',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '6304',
);
if (!isset($GLOBALS['dbInfo']['resource'])) { $GLOBALS['dbInfo']['resource'] = []; }
$GLOBALS['dbInfo']['resource'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'resource',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo'][' gamecenter_opt'])) { $GLOBALS['dbInfo'][' gamecenter_opt'] = []; }
$GLOBALS['dbInfo'][' gamecenter_opt'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '172.27.203.6',
    'dbPort' => '3306',
    'dbName' => 'gamecenter_opt',
    'dbUser' => 'yxtq',
    'dbPass' => 'yxtq@duowan@$',
);
if (!isset($GLOBALS['dbInfo']['issuecode'])) { $GLOBALS['dbInfo']['issuecode'] = []; }
$GLOBALS['dbInfo']['issuecode'] += array (
    'dbHost' => '172.27.203.6',
    'dbName' => 'issuecode',
    'dbPass' => 'yxtq@duowan@$',
    'dbUser' => 'yxtq',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['dw_ka_sn'])) { $GLOBALS['dbInfo']['dw_ka_sn'] = []; }
$GLOBALS['dbInfo']['dw_ka_sn'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_ka_sn',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['dw_game_sync'])) { $GLOBALS['dbInfo']['dw_game_sync'] = []; }
$GLOBALS['dbInfo']['dw_game_sync'] += array (
    'dbHost' => '172.28.19.250',
    'dbName' => 'test',
    'dbPass' => 'boke45731',
    'dbUser' => 'boketest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '6306',
);
if (!isset($GLOBALS['dbInfo']['dw_ka_book'])) { $GLOBALS['dbInfo']['dw_ka_book'] = []; }
$GLOBALS['dbInfo']['dw_ka_book'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_ka_book',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['dw_ms'])) { $GLOBALS['dbInfo']['dw_ms'] = []; }
$GLOBALS['dbInfo']['dw_ms'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_ms',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['dw_ka_master2'])) { $GLOBALS['dbInfo']['dw_ka_master2'] = []; }
$GLOBALS['dbInfo']['dw_ka_master2'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_ka',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['dw_ka_sn_master2'])) { $GLOBALS['dbInfo']['dw_ka_sn_master2'] = []; }
$GLOBALS['dbInfo']['dw_ka_sn_master2'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_ka_sn',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['nation_hiyd_cms'])) { $GLOBALS['dbInfo']['nation_hiyd_cms'] = []; }
$GLOBALS['dbInfo']['nation_hiyd_cms'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'nation_hiyd_cms',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['dw_game'])) { $GLOBALS['dbInfo']['dw_game'] = []; }
$GLOBALS['dbInfo']['dw_game'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_game',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['nation_hiyd_home'])) { $GLOBALS['dbInfo']['nation_hiyd_home'] = []; }
$GLOBALS['dbInfo']['nation_hiyd_home'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'nation_hiyd_home',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['dw_ka_data'])) { $GLOBALS['dbInfo']['dw_ka_data'] = []; }
$GLOBALS['dbInfo']['dw_ka_data'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_ka_data',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['dw_ka_user'])) { $GLOBALS['dbInfo']['dw_ka_user'] = []; }
$GLOBALS['dbInfo']['dw_ka_user'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_ka_user',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['dw_ls'])) { $GLOBALS['dbInfo']['dw_ls'] = []; }
$GLOBALS['dbInfo']['dw_ls'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_ls',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['dw_sy'])) { $GLOBALS['dbInfo']['dw_sy'] = []; }
$GLOBALS['dbInfo']['dw_sy'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_sy',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['dw_sy_old'])) { $GLOBALS['dbInfo']['dw_sy_old'] = []; }
$GLOBALS['dbInfo']['dw_sy_old'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_sy_old',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['dw_sy_data'])) { $GLOBALS['dbInfo']['dw_sy_data'] = []; }
$GLOBALS['dbInfo']['dw_sy_data'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_sy_data',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['dbInfo']['fhvideo_data'])) { $GLOBALS['dbInfo']['fhvideo_data'] = []; }
$GLOBALS['dbInfo']['fhvideo_data'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'fhvideo_data',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['fhvideo_home'])) { $GLOBALS['dbInfo']['fhvideo_home'] = []; }
$GLOBALS['dbInfo']['fhvideo_home'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'fhvideo_home',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['redisGroup']['dw_ka'])) { $GLOBALS['redisGroup']['dw_ka'] = []; }
$GLOBALS['redisGroup']['dw_ka'] += array (
    'master' => 'dw_ka',
    'slave1' => 'dw_ka-slave1',
    'defalut_ttl' => '3600',
);
if (!isset($GLOBALS['redisInfo']['default'])) { $GLOBALS['redisInfo']['default'] = []; }
$GLOBALS['redisInfo']['default'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
);
if (!isset($GLOBALS['redisInfo']['logstash_redis'])) { $GLOBALS['redisInfo']['logstash_redis'] = []; }
$GLOBALS['redisInfo']['logstash_redis'] += array (
    'host' => '61.160.36.225',
    'port' => '6403',
    'pwd' => 'ojia123',
    'connet_timeout' => '1',
);
if (!isset($GLOBALS['redisInfo']['oujhome_history'])) { $GLOBALS['redisInfo']['oujhome_history'] = []; }
$GLOBALS['redisInfo']['oujhome_history'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
);
if (!isset($GLOBALS['redisInfo']['oujhome_logic'])) { $GLOBALS['redisInfo']['oujhome_logic'] = []; }
$GLOBALS['redisInfo']['oujhome_logic'] += array (
    'host' => '61.160.36.225',
    'port' => '6404',
    'pwd' => 'ojia123',
);
if (!isset($GLOBALS['redisInfo']['sns'])) { $GLOBALS['redisInfo']['sns'] = []; }
$GLOBALS['redisInfo']['sns'] += array (
    'host' => '61.160.36.225',
    'port' => '6382',
    'pwd' => 'oujsns',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['fit_db'])) { $GLOBALS['redisInfo']['fit_db'] = []; }
$GLOBALS['redisInfo']['fit_db'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '1',
);
if (!isset($GLOBALS['redisInfo']['logic'])) { $GLOBALS['redisInfo']['logic'] = []; }
$GLOBALS['redisInfo']['logic'] += array (
    'host' => '61.160.36.225',
    'port' => '6404',
    'pwd' => 'ojia123',
);
if (!isset($GLOBALS['redisInfo']['fit_rank'])) { $GLOBALS['redisInfo']['fit_rank'] = []; }
$GLOBALS['redisInfo']['fit_rank'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '4',
);
if (!isset($GLOBALS['redisInfo']['hiyd_home'])) { $GLOBALS['redisInfo']['hiyd_home'] = []; }
$GLOBALS['redisInfo']['hiyd_home'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '4',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['hiyd_cms'])) { $GLOBALS['redisInfo']['hiyd_cms'] = []; }
$GLOBALS['redisInfo']['hiyd_cms'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '3',
    'connet_timeout' => '0',
    'connect_timeout' => '0',
    'support_pro' => '1',
);
if (!isset($GLOBALS['redisInfo']['oujhome'])) { $GLOBALS['redisInfo']['oujhome'] = []; }
$GLOBALS['redisInfo']['oujhome'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
);
if (!isset($GLOBALS['redisInfo']['data_report'])) { $GLOBALS['redisInfo']['data_report'] = []; }
$GLOBALS['redisInfo']['data_report'] += array (
    'host' => '61.160.36.225',
    'port' => '6403',
    'pwd' => 'ojia123',
    'connet_timeout' => '1',
);
if (!isset($GLOBALS['redisInfo']['hiyd_sns'])) { $GLOBALS['redisInfo']['hiyd_sns'] = []; }
$GLOBALS['redisInfo']['hiyd_sns'] += array (
    'host' => '61.160.36.225',
    'port' => '6382',
    'pwd' => 'oujsns',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['hiyd_meal'])) { $GLOBALS['redisInfo']['hiyd_meal'] = []; }
$GLOBALS['redisInfo']['hiyd_meal'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '5',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['hiyd_shop'])) { $GLOBALS['redisInfo']['hiyd_shop'] = []; }
$GLOBALS['redisInfo']['hiyd_shop'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '6',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['shop_base'])) { $GLOBALS['redisInfo']['shop_base'] = []; }
$GLOBALS['redisInfo']['shop_base'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '7',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['dw_task'])) { $GLOBALS['redisInfo']['dw_task'] = []; }
$GLOBALS['redisInfo']['dw_task'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '8',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['dw_shop'])) { $GLOBALS['redisInfo']['dw_shop'] = []; }
$GLOBALS['redisInfo']['dw_shop'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '9',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['svideo_home'])) { $GLOBALS['redisInfo']['svideo_home'] = []; }
$GLOBALS['redisInfo']['svideo_home'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '10',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['svideo_data'])) { $GLOBALS['redisInfo']['svideo_data'] = []; }
$GLOBALS['redisInfo']['svideo_data'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '11',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['user_token'])) { $GLOBALS['redisInfo']['user_token'] = []; }
$GLOBALS['redisInfo']['user_token'] += array (
    'host' => '61.160.36.225',
    'port' => '6400',
    'pwd' => 'user_token_3',
    'db' => '2',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['adsystem'])) { $GLOBALS['redisInfo']['adsystem'] = []; }
$GLOBALS['redisInfo']['adsystem'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '11',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['adsystem_api'])) { $GLOBALS['redisInfo']['adsystem_api'] = []; }
$GLOBALS['redisInfo']['adsystem_api'] += array (
    'host' => '61.160.36.225',
    'port' => '6408',
    'pwd' => 'ojia123',
    'db' => '0',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['fanhe_home'])) { $GLOBALS['redisInfo']['fanhe_home'] = []; }
$GLOBALS['redisInfo']['fanhe_home'] += array (
    'host' => '61.160.36.225',
    'port' => '6398',
    'pwd' => 'ojia_pay',
    'db' => '2',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['dw_ka'])) { $GLOBALS['redisInfo']['dw_ka'] = []; }
$GLOBALS['redisInfo']['dw_ka'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '11',
    'connet_timeout' => '0',
    'support_pro' => '1',
);
if (!isset($GLOBALS['redisInfo']['dw_ms'])) { $GLOBALS['redisInfo']['dw_ms'] = []; }
$GLOBALS['redisInfo']['dw_ms'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '11',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['dw_ka_r2m'])) { $GLOBALS['redisInfo']['dw_ka_r2m'] = []; }
$GLOBALS['redisInfo']['dw_ka_r2m'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '11',
    'connet_timeout' => '0',
    'support_pro' => '1',
);
if (!isset($GLOBALS['redisInfo']['dw_ka_user'])) { $GLOBALS['redisInfo']['dw_ka_user'] = []; }
$GLOBALS['redisInfo']['dw_ka_user'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '11',
    'connet_timeout' => '0',
    'support_pro' => '1',
);
if (!isset($GLOBALS['redisInfo']['dw_game'])) { $GLOBALS['redisInfo']['dw_game'] = []; }
$GLOBALS['redisInfo']['dw_game'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '12',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['store_redis'])) { $GLOBALS['redisInfo']['store_redis'] = []; }
$GLOBALS['redisInfo']['store_redis'] += array (
    'host' => '61.160.36.225',
    'port' => '6398',
    'pwd' => 'ojia_pay',
    'db' => '3',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['dw_ka-slave1'])) { $GLOBALS['redisInfo']['dw_ka-slave1'] = []; }
$GLOBALS['redisInfo']['dw_ka-slave1'] += array (
    'host' => '61.160.36.225',
    'port' => '6411',
    'pwd' => 'ojia123',
    'db' => '11',
    'connet_timeout' => '0',
    'support_pro' => '1',
);
if (!isset($GLOBALS['redisInfo']['dw_sy'])) { $GLOBALS['redisInfo']['dw_sy'] = []; }
$GLOBALS['redisInfo']['dw_sy'] += array (
    'host' => '61.160.36.225',
    'port' => '6379',
    'pwd' => 'ojia123',
    'db' => '0',
    'connet_timeout' => '3000',
);
if (!isset($GLOBALS['redisInfo']['fhvideo_app'])) { $GLOBALS['redisInfo']['fhvideo_app'] = []; }
$GLOBALS['redisInfo']['fhvideo_app'] += array (
    'host' => '61.160.36.225',
    'port' => '6398',
    'pwd' => 'ojia_pay',
    'connet_timeout' => '0',
    'db' => '3',
);
if (!isset($GLOBALS['redisInfo']['dw_sy_store'])) { $GLOBALS['redisInfo']['dw_sy_store'] = []; }
$GLOBALS['redisInfo']['dw_sy_store'] += array (
    'host' => '61.160.36.225',
    'port' => '6379',
    'pwd' => 'ojia123',
    'db' => '0',
    'connet_timeout' => '3000',
);
if (!isset($GLOBALS['redisInfo']['name_serv'])) { $GLOBALS['redisInfo']['name_serv'] = []; }
$GLOBALS['redisInfo']['name_serv'] += array (
    'host' => '61.160.36.225',
    'port' => '6405',
    'pwd' => 'ojia123',
    'db' => '1',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['thriftInfo']['cacheServer'])) { $GLOBALS['thriftInfo']['cacheServer'] = []; }
$GLOBALS['thriftInfo']['cacheServer'] += array (
    'host' => '61.160.36.225',
    'port' => '9004',
);
if (!isset($GLOBALS['thriftInfo']['payServer'])) { $GLOBALS['thriftInfo']['payServer'] = []; }
$GLOBALS['thriftInfo']['payServer'] += array (
    'host' => '61.160.36.225',
    'port' => '9003',
);
if (!isset($GLOBALS['thriftInfo']['userServer'])) { $GLOBALS['thriftInfo']['userServer'] = []; }
$GLOBALS['thriftInfo']['userServer'] += array (
    'host' => '61.160.36.225',
    'port' => '9001',
);
if (!isset($GLOBALS['thriftInfo']['authServer'])) { $GLOBALS['thriftInfo']['authServer'] = []; }
$GLOBALS['thriftInfo']['authServer'] += array (
    'host' => '61.160.36.225',
    'port' => '9002',
);
if (!isset($GLOBALS['thriftInfo']['userAccountServer'])) { $GLOBALS['thriftInfo']['userAccountServer'] = []; }
$GLOBALS['thriftInfo']['userAccountServer'] += array (
    'host' => '61.160.36.225',
    'port' => '9005',
);
if (!isset($GLOBALS['thriftInfo']['centerServer'])) { $GLOBALS['thriftInfo']['centerServer'] = []; }
$GLOBALS['thriftInfo']['centerServer'] += array (
    'host' => '61.160.36.225',
    'port' => '9006',
);
if (!isset($GLOBALS['thriftInfo']['centerServerHezi'])) { $GLOBALS['thriftInfo']['centerServerHezi'] = []; }
$GLOBALS['thriftInfo']['centerServerHezi'] += array (
    'host' => '61.160.36.225',
    'port' => '9008',
);
if (!isset($GLOBALS['wxInfo']['2'])) { $GLOBALS['wxInfo']['2'] = []; }
$GLOBALS['wxInfo']['2'] += array (
    'appId' => 'wx892910db2cdd2c12',
    'appSecret' => 'eed2932f5a29b4f12d2607aa77548949',
    'remark' => '偶家科技公众账号',
);
if (!isset($GLOBALS['wxInfo']['1'])) { $GLOBALS['wxInfo']['1'] = []; }
$GLOBALS['wxInfo']['1'] += array (
    'appId' => 'wx892910db2cdd2c12',
    'appSecret' => 'eed2932f5a29b4f12d2607aa77548949',
    'remark' => '偶家app',
);
if (!isset($GLOBALS['wxInfo']['6'])) { $GLOBALS['wxInfo']['6'] = []; }
$GLOBALS['wxInfo']['6'] += array (
    'appId' => 'wx489f3e891508cf09',
    'appSecret' => 'fea0ce52a21fb194422277010cb42580',
    'remark' => 'HiMeals善食(老版)',
);
if (!isset($GLOBALS['wxInfo']['7'])) { $GLOBALS['wxInfo']['7'] = []; }
$GLOBALS['wxInfo']['7'] += array (
    'appId' => 'wxa46e6794bc38e236',
    'appSecret' => 'cf876b3bbae4b8b2bdb03570c391964f',
    'remark' => '善食健康餐(新版)',
);
if (!isset($GLOBALS['wxInfo']['10'])) { $GLOBALS['wxInfo']['10'] = []; }
$GLOBALS['wxInfo']['10'] += array (
    'appId' => 'wxb25813d7b9e2147b',
    'appSecret' => '51710497377c2bf56e00fe38b161b14d',
    'remark' => '多玩网(订阅号)',
);
if (!isset($GLOBALS['wxInfo']['11'])) { $GLOBALS['wxInfo']['11'] = []; }
$GLOBALS['wxInfo']['11'] += array (
    'appId' => 'wx15447448f1c813d3',
    'appSecret' => 'a615b4d0a4491f2f52080821a1f57211',
    'remark' => '5253手游礼包(服务号)',
);
if (!isset($GLOBALS['wxInfo']['12'])) { $GLOBALS['wxInfo']['12'] = []; }
$GLOBALS['wxInfo']['12'] += array (
    'appId' => 'wxec4655885769a485',
    'appSecret' => 'd63005d85623cff2857edeaf1139b3c4',
    'remark' => '多玩特权(服务号)',
);
if (!isset($GLOBALS['wxInfo']['13'])) { $GLOBALS['wxInfo']['13'] = []; }
$GLOBALS['wxInfo']['13'] += array (
    'appId' => 'wxccdf3948732e9e41',
    'appSecret' => 'bad95413a0c4c3c3c58f19fa1b6111db',
    'remark' => '多玩特权(开发者平台网站应用)',
);
if (!isset($GLOBALS['wxInfo']['14'])) { $GLOBALS['wxInfo']['14'] = []; }
$GLOBALS['wxInfo']['14'] += array (
    'appId' => 'wxd500ae8e6e63b6c9',
    'appSecret' => '56e7424d4f7f77c7c0dccf08890fc332',
    'remark' => '多玩商城(登陆,支付,红包)',
    'payAppId' => '7',
);
if (!isset($GLOBALS['dbInfo']['glance_home'])) { $GLOBALS['dbInfo']['glance_home'] = []; }
$GLOBALS['dbInfo']['glance_home'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'glance_home',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['glance_data'])) { $GLOBALS['dbInfo']['glance_data'] = []; }
$GLOBALS['dbInfo']['glance_data'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'glance_data',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['glance_home_copy'])) { $GLOBALS['dbInfo']['glance_home_copy'] = []; }
$GLOBALS['dbInfo']['glance_home_copy'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'glance_home_copy',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['glance_data_copy'])) { $GLOBALS['dbInfo']['glance_data_copy'] = []; }
$GLOBALS['dbInfo']['glance_data_copy'] += array (
    'dbHost' => '61.160.36.225',
    'dbName' => 'glance_data_copy',
    'dbPass' => 'ojia305',
    'dbUser' => 'ojiatest',
    'dbType' => 'mysqli',
    'enable' => 'true',
    'dbPort' => '3306',
);
if (!isset($GLOBALS['dbInfo']['dw_ka_book_master2'])) { $GLOBALS['dbInfo']['dw_ka_book_master2'] = []; }
$GLOBALS['dbInfo']['dw_ka_book_master2'] += array (
    'enable' => 'true',
    'dbType' => 'mysqli',
    'dbHost' => '61.160.36.225',
    'dbPort' => '3306',
    'dbName' => 'dw_ka_book',
    'dbUser' => 'ojiatest',
    'dbPass' => 'ojia305',
);
if (!isset($GLOBALS['redisInfo']['glance_home'])) { $GLOBALS['redisInfo']['glance_home'] = []; }
$GLOBALS['redisInfo']['glance_home'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '11',
    'connet_timeout' => '0',
);
if (!isset($GLOBALS['redisInfo']['glance_data'])) { $GLOBALS['redisInfo']['glance_data'] = []; }
$GLOBALS['redisInfo']['glance_data'] += array (
    'host' => '61.160.36.225',
    'port' => '6407',
    'pwd' => 'ojia123',
    'db' => '11',
    'connet_timeout' => '0',
);
