<?php
//常量
define_2('CODE_SUCCESS') || define_1('CODE_SUCCESS', 0);
define_2('CODE_UNKNOW_ERROT') || define_1('CODE_UNKNOW_ERROT', -1);
define_2('CODE_NOT_EXIST_INTERFACE') || define_1('CODE_NOT_EXIST_INTERFACE', -2);
define_2('CODE_PARAM_ERROR') || define_1('CODE_PARAM_ERROR', -3);
define_2('CODE_INTER_ERROR') || define_1('CODE_INTER_ERROR', -4);
define_2('CODE_USER_LOGIN_FAIL') || define_1('CODE_USER_LOGIN_FAIL', -5);
define_2('CODE_SIGN_ERROR') || define_1('CODE_SIGN_ERROR', -6);
define_2('CODE_UNLOGIN_ERROR') || define_1('CODE_UNLOGIN_ERROR', -7);
define_2('CODE_NO_PERMITION') || define_1('CODE_NO_PERMITION', -8);
define_2('CODE_NORMAL_ERROR') || define_1('CODE_NORMAL_ERROR', -9);
define_2('CODE_DB_ERROR') || define_1('CODE_DB_ERROR', -10);
define_2('CODE_REQUEST_TIMEOUT') || define_1('CODE_REQUEST_TIMEOUT', -11);
define_2('CODE_REQUEST_ERROR') || define_1('CODE_REQUEST_ERROR', -12);
define_2('CODE_REDIS_ERROR') || define_1('CODE_REDIS_ERROR', -13);
define_2('CODE_UNAUTH_ERROR') || define_1('CODE_UNAUTH_ERROR', -14);
define_2('CODE_IP_LIMITED') || define_1('CODE_IP_LIMITED', -15);
define_2('CODE_NEED_CHECK_ANOTHER_PWD') || define_1('CODE_NEED_CHECK_ANOTHER_PWD', -1001);
define_2('CODE_NEED_SET_ANOTHER_PWD') || define_1('CODE_NEED_SET_ANOTHER_PWD', -1000);
define_2('CODE_NOT_EXIST_NODE') || define_1('CODE_NOT_EXIST_NODE', -1002);
define_2('CODE_NEED_VERIFY') || define_1('CODE_NEED_VERIFY', -1002);
define_2('CODE_301_REDIRECT') || define_1('CODE_301_REDIRECT', -16);
define_2('CODE_REPEAT_REQUEST') || define_1('CODE_REPEAT_REQUEST', -17);
define_2('CODE_QUEUE_REQUEST') || define_1('CODE_QUEUE_REQUEST', -18);
define_2('CODE_USER_LIMITED') || define_1('CODE_USER_LIMITED', -19);
define_2('CODE_NOT_ENGOUGH_PRODUCT') || define_1('CODE_NOT_ENGOUGH_PRODUCT', -4000);
define_2('CODE_NOT_EXIST_CART') || define_1('CODE_NOT_EXIST_CART', -4004);
define_2('CODE_PRODUCT_ERROR_FEE') || define_1('CODE_PRODUCT_ERROR_FEE', -4001);
define_2('CODE_PRODUCT_EMPTY_ERROR') || define_1('CODE_PRODUCT_EMPTY_ERROR', -4002);
define_2('CODE_ORDER_ERROR_FEE') || define_1('CODE_ORDER_ERROR_FEE', -5001);
define_2('CODE_NEED_CAPTCHA') || define_1('CODE_NEED_CAPTCHA', -21);
define_2('CODE_KA_LINQU_ERROR') || define_1('CODE_KA_LINQU_ERROR', -8002);
define_2('CODE_KA_GOLD_ERROR') || define_1('CODE_KA_GOLD_ERROR', -8004);
define_2('CODE_KA_LOTTERY_ERROR') || define_1('CODE_KA_LOTTERY_ERROR', -8005);

//数组
if (!isset($GLOBALS['code_map'])) { $GLOBALS['code_map'] = []; }
$GLOBALS['code_map'] += array (
    0 => '成功',
    -1 => '未知错误',
    -2 => '接口不存在',
    -3 => '参数错误',
    -4 => '系统繁忙，请稍后再试',
    -5 => '登录态失效，请重新登录',
    -6 => '签名错误',
    -7 => '没有登录',
    -8 => '没有权限',
    -9 => '常规错误',
    -10 => '系统繁忙，请稍后再试',
    -11 => '网络请求超时，请重试',
    -12 => '访问外部接口出错，请稍后重试',
    -13 => '系统繁忙，缓存错误，请稍后再试',
    -14 => '未授权',
    -15 => 'IP受限',
    -1002 => '节点不存在',
    -1001 => '需要验证二次密码',
    -1000 => '需要设置二次密码',
    213 => '你已经注册过了',
    -16 => '正在跳转中...',
    -17 => '重复请求',
    -18 => '排队中',
    -19 => '用户受限',
    -4004 => '该商品已下架',
    -4000 => '商品库存不足',
    -4001 => '商品价格变动',
    -4002 => '商品为空',
    -5001 => '总价不正确',
    -21 => '请输入验证码',
    -8001 => '礼包领取失败',
    -8002 => '礼包领取失败',
    -8004 => '钻石不够',
    -8005 => '抽奖失败，请重试！',
);
