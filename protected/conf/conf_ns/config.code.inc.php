<?php

//常量
defined('CODE_301_REDIRECT') || define('CODE_301_REDIRECT', -16);
defined('CODE_DB_ERROR') || define('CODE_DB_ERROR', -10);
defined('CODE_INTER_ERROR') || define('CODE_INTER_ERROR', -4);
defined('CODE_IP_LIMITED') || define('CODE_IP_LIMITED', -15);
defined('CODE_KA_GOLD_ERROR') || define('CODE_KA_GOLD_ERROR', -8004);
defined('CODE_KA_LINQU_ERROR') || define('CODE_KA_LINQU_ERROR', -8002);
defined('CODE_KA_LOTTERY_ERROR') || define('CODE_KA_LOTTERY_ERROR', -8005);
defined('CODE_NEED_CAPTCHA') || define('CODE_NEED_CAPTCHA', -21);
defined('CODE_NEED_CHECK_ANOTHER_PWD') || define('CODE_NEED_CHECK_ANOTHER_PWD', -1001);
defined('CODE_NEED_SET_ANOTHER_PWD') || define('CODE_NEED_SET_ANOTHER_PWD', -1000);
defined('CODE_NEED_VERIFY') || define('CODE_NEED_VERIFY', -1002);
defined('CODE_NORMAL_ERROR') || define('CODE_NORMAL_ERROR', -9);
defined('CODE_NOT_ENGOUGH_PRODUCT') || define('CODE_NOT_ENGOUGH_PRODUCT', -4000);
defined('CODE_NOT_EXIST_CART') || define('CODE_NOT_EXIST_CART', -4004);
defined('CODE_NOT_EXIST_INTERFACE') || define('CODE_NOT_EXIST_INTERFACE', -2);
defined('CODE_NOT_EXIST_NODE') || define('CODE_NOT_EXIST_NODE', -1002);
defined('CODE_NO_PERMITION') || define('CODE_NO_PERMITION', -8);
defined('CODE_ORDER_ERROR_FEE') || define('CODE_ORDER_ERROR_FEE', -5001);
defined('CODE_PARAM_ERROR') || define('CODE_PARAM_ERROR', -3);
defined('CODE_PRODUCT_EMPTY_ERROR') || define('CODE_PRODUCT_EMPTY_ERROR', -4002);
defined('CODE_PRODUCT_ERROR_FEE') || define('CODE_PRODUCT_ERROR_FEE', -4001);
defined('CODE_QUEUE_REQUEST') || define('CODE_QUEUE_REQUEST', -18);
defined('CODE_REDIS_ERROR') || define('CODE_REDIS_ERROR', -13);
defined('CODE_REPEAT_REQUEST') || define('CODE_REPEAT_REQUEST', -17);
defined('CODE_REQUEST_ERROR') || define('CODE_REQUEST_ERROR', -12);
defined('CODE_REQUEST_TIMEOUT') || define('CODE_REQUEST_TIMEOUT', -11);
defined('CODE_SIGN_ERROR') || define('CODE_SIGN_ERROR', -6);
defined('CODE_SUCCESS') || define('CODE_SUCCESS', 0);
defined('CODE_UNAUTH_ERROR') || define('CODE_UNAUTH_ERROR', -14);
defined('CODE_UNKNOW_ERROT') || define('CODE_UNKNOW_ERROT', -1);
defined('CODE_UNLOGIN_ERROR') || define('CODE_UNLOGIN_ERROR', -7);
defined('CODE_USER_LIMITED') || define('CODE_USER_LIMITED', -19);
defined('CODE_USER_LOGIN_FAIL') || define('CODE_USER_LOGIN_FAIL', -5);

//数组
if (!isset($GLOBALS['code_map'])) { $GLOBALS['code_map'] = []; }
$GLOBALS['code_map'] += array (
  -16 => '正在跳转中...',
  -10 => '系统繁忙，请稍后再试',
  -4 => '系统繁忙，请稍后再试',
  -15 => 'IP受限',
  -8004 => '钻石不够',
  -8002 => '礼包领取失败',
  -8005 => '抽奖失败，请重试！',
  -21 => '请输入验证码',
  -1001 => '需要验证二次密码',
  -1000 => '需要设置二次密码',
  -1002 => '节点不存在',
  -9 => '常规错误',
  -4000 => '商品库存不足',
  -4004 => '该商品已下架',
  -2 => '接口不存在',
  -8 => '没有权限',
  -5001 => '总价不正确',
  -3 => '参数错误',
  -4002 => '商品为空',
  -4001 => '商品价格变动',
  -18 => '排队中',
  -13 => '系统繁忙，缓存错误，请稍后再试',
  -17 => '重复请求',
  -12 => '访问外部接口出错，请稍后重试',
  -11 => '网络请求超时，请重试',
  -6 => '签名错误',
  0 => '成功',
  -14 => '未授权',
  -1 => '未知错误',
  -7 => '没有登录',
  -19 => '用户受限',
  -5 => '登录态失效，请重新登录',
);
