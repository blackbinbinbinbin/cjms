// 常量
global["CODE_301_REDIRECT"] = -16;
global["CODE_DB_ERROR"] = -10;
global["CODE_INTER_ERROR"] = -4;
global["CODE_IP_LIMITED"] = -15;
global["CODE_KA_GOLD_ERROR"] = -8004;
global["CODE_KA_LINQU_ERROR"] = -8002;
global["CODE_KA_LOTTERY_ERROR"] = -8005;
global["CODE_NEED_CAPTCHA"] = -21;
global["CODE_NEED_CHECK_ANOTHER_PWD"] = -1001;
global["CODE_NEED_SET_ANOTHER_PWD"] = -1000;
global["CODE_NEED_VERIFY"] = -1002;
global["CODE_NORMAL_ERROR"] = -9;
global["CODE_NOT_ENGOUGH_PRODUCT"] = -4000;
global["CODE_NOT_EXIST_CART"] = -4004;
global["CODE_NOT_EXIST_INTERFACE"] = -2;
global["CODE_NOT_EXIST_NODE"] = -1002;
global["CODE_NO_PERMITION"] = -8;
global["CODE_ORDER_ERROR_FEE"] = -5001;
global["CODE_PARAM_ERROR"] = -3;
global["CODE_PRODUCT_EMPTY_ERROR"] = -4002;
global["CODE_PRODUCT_ERROR_FEE"] = -4001;
global["CODE_QUEUE_REQUEST"] = -18;
global["CODE_REDIS_ERROR"] = -13;
global["CODE_REPEAT_REQUEST"] = -17;
global["CODE_REQUEST_ERROR"] = -12;
global["CODE_REQUEST_TIMEOUT"] = -11;
global["CODE_SIGN_ERROR"] = -6;
global["CODE_SUCCESS"] = 0;
global["CODE_UNAUTH_ERROR"] = -14;
global["CODE_UNKNOW_ERROT"] = -1;
global["CODE_UNLOGIN_ERROR"] = -7;
global["CODE_USER_LIMITED"] = -19;
global["CODE_USER_LOGIN_FAIL"] = -5;

// 数组
 
var code_map = {};
code_map = {"-16":"正在跳转中...","-10":"系统繁忙，请稍后再试","-4":"系统繁忙，请稍后再试","-15":"IP受限","-8004":"钻石不够","-8002":"礼包领取失败","-8005":"抽奖失败，请重试！","-21":"请输入验证码","-1001":"需要验证二次密码","-1000":"需要设置二次密码","-1002":"节点不存在","-9":"常规错误","-4000":"商品库存不足","-4004":"该商品已下架","-2":"接口不存在","-8":"没有权限","-5001":"总价不正确","-3":"参数错误","-4002":"商品为空","-4001":"商品价格变动","-18":"排队中","-13":"系统繁忙，缓存错误，请稍后再试","-17":"重复请求","-12":"访问外部接口出错，请稍后重试","-11":"网络请求超时，请重试","-6":"签名错误","0":"成功","-14":"未授权","-1":"未知错误","-7":"没有登录","-19":"用户受限","-5":"登录态失效，请重新登录"};
exports["code_map"] = code_map;
