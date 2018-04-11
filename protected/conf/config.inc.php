<?php


define("APP_ID", 4);
define("APP_SECRET", 'X80r5.p9F');

$prefix = ENV != ENV_FORMAL ? ucfirst(ENV) . '_' : '';
$isOuj = $isDuowan = $isHiyd = false;
if (strpos(COOKIE_DOMAIN, 'ouj.com') !== false) {
    define('COOKIE_DOMAIN2', 'ouj.com');
    define('COOKIE_DOMAIN_ADMIN', 'admin.ouj.com');
    $isOuj = true;
    define('SITE_NAME', $prefix . '偶家后台');
} else if (strpos(COOKIE_DOMAIN, 'duowan.com') !== false) {
    define('COOKIE_DOMAIN2', 'duowan.com');
    define('COOKIE_DOMAIN_ADMIN', 'admin.duowan.com');
    $isDuowan = true;
    define('SITE_NAME', $prefix . '多玩后台');
} else if (strpos(COOKIE_DOMAIN, 'hiyd.com') !== false) {
    define('COOKIE_DOMAIN2', 'hiyd.com');
    define('COOKIE_DOMAIN_ADMIN', 'admin.hiyd.com');
    $isHiyd = true;
    define('SITE_NAME', $prefix . '偶家');
}

define('IS_OUJ', $isOuj);
define('IS_DUOWAN', $isDuowan);
define('IS_HIYD', $isHiyd);

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

define("CJMS_IP_NEW", "61.160.36.226");
define("CJMS_HOST_NEW", "new.admin.duowan.com");
define("CJMS_IP_FORM", "61.160.36.226");
define("CJMS_HOST_FORM", "admin.duowan.com");
define("CJMS_IP_DEV", "61.160.36.225");
define("CJMS_HOST_DEV", "test.admin.duowan.com");

// 代理爬虫，http://fs3.daili666.com 帐号密码
define("SPIDER_USER", "dwpcgame@163.com");
define("SPIDER_PASSWORD", "Duowanpc2017");
//end of script
