<?php


define("APP_ID", 4);
define("APP_SECRET", 'X80r5.p9F');

$prefix = ENV != ENV_FORMAL ? ucfirst(ENV) . '_' : '';
// $isOuj = $isDuowan = $isHiyd = false;
// if (strpos(COOKIE_DOMAIN, 'ouj.com') !== false) {
//     define('COOKIE_DOMAIN2', 'ouj.com');
//     define('COOKIE_DOMAIN_ADMIN', 'admin.ouj.com');
//     $isOuj = true;
//     define('SITE_NAME', $prefix . '偶家后台');
// } else if (strpos(COOKIE_DOMAIN, 'duowan.com') !== false) {
//     define('COOKIE_DOMAIN2', 'duowan.com');
//     define('COOKIE_DOMAIN_ADMIN', 'admin.duowan.com');
//     $isDuowan = true;
//     define('SITE_NAME', $prefix . '多玩后台');
// } else if (strpos(COOKIE_DOMAIN, 'hiyd.com') !== false) {
//     define('COOKIE_DOMAIN2', 'hiyd.com');
//     define('COOKIE_DOMAIN_ADMIN', 'admin.hiyd.com');
//     $isHiyd = true;
//     define('SITE_NAME', $prefix . '偶家');
// }

// define('IS_OUJ', $isOuj);
// define('IS_DUOWAN', $isDuowan);
// define('IS_HIYD', $isHiyd);
//域名 cookie 路径, 可以根据不同的域名进行兼容修改
$isCjms = true;
define('SITE_NAME', $prefix . '后台管理');
define('COOKIE_DOMAIN2', 'funkstyle.com');
define('COOKIE_DOMAIN_ADMIN', 'funkstyle.com');
define('IS_CJMS', $isCjms);

$GLOBALS['nameServ_php'] = [
  'globals',
  'code',
  'r2m',
];

$GLOBALS['r2mMode'] = 'lib';

$GLOBALS['rewrite'] = array(
    '404.html' => 'default/notFound',
    'robots.txt' => 'default/robots',
);

$GLOBALS['defaultKey'] = 'Web';
define("CALL_LOG_KEY", "logstash:dw#1");

//名字服务中需要发布到不同环境的服务器配置
//在本地开发环境中默认是生成文件到 /conf/conf_ns 下
//预发布环境
// define("CJMS_IP_NEW", "new ip");
// define("CJMS_HOST_NEW", "new domain");
//正式环境
// define("CJMS_IP_FORM", "form ip");
// define("CJMS_HOST_FORM", "form domain");
//测试环境
// define("CJMS_IP_DEV", "dev ip");
// define("CJMS_HOST_DEV", "dev domain");

// 爬虫配置
// 代理爬虫，http://fs3.daili666.com 帐号密码
define("SPIDER_USER", "dwpcgame@163.com");
define("SPIDER_PASSWORD", "Duowanpc2017");
//end of script
