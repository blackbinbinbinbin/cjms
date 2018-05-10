<?php

require_once '../common.php';


$GLOBALS['rewrite'] = [
  'v5/' => 'oldka/home', // 老版本首页
  '<id1>/<id2:\d{1,3}>/<id3:\w+>_test.html' => 'oldka/home', // 新闻页面

  '404.html' => 'default/notFound',
  'robots.txt' => 'default/robots',

  // 新版本规则
  'card_<gift_id:\d+>.html' => 'gift/card',
  'game_<game_id:\d+>.html' => 'game/index',

  // 老版本规则
  '<gift_id:\d>.html' => 'oldka/card',  // 端游发号页面
  'book_<game_id>.html' => 'oldka/game', // 端游专区页面
  'hall/' => 'oldka/cardList', // 礼包列表页面
  'integralMall/' => 'oldka/goodsList', // 礼包列表页面
  'lottery/issueLottery.do' => 'oldka/lottery', // 抽奖页面
  'mgame/card_<gift_id>.html' => 'oldka/mgameCard', // 手游发号页面
  'mgame/game_<game_id>.html' => 'oldka/mgameGame', // 手游专区页面
  'mgame/list.html' => 'oldka/mgameList', // 手游列表页面
  'mgame/list_<p1>.html' => 'oldka/mgameList', // 手游列表页面
  'my/mytqlist.do' => 'oldka/userCard', // 卡码箱
  'search/search.do' => 'oldka/search', // 搜索页面
  'task/listTask' => 'oldka/task', // 任务页面
  'task/my' => 'oldka/my', // 个人中心页面
  'tq_<gift_id>.html' => 'oldka/home', // 特殊礼包页面
  'userCenter/' => 'oldka/my', // 个人中心页面

  'wgame/index.do' => 'oldka/wgame', // 页游首页
  'zone/index.do' => 'oldka/home', // 未知页面

  'home/homePage.do' => 'oldka/home', // 新闻页面
  '<id1>/<id2>.html' => 'oldka/home', // 新闻页面
  'activity/list' => 'oldka/home',    // 活动中心

  'v4/activity/list' => 'oldka/home',    // v4活动中心
  'v4/game/gameinfo.do' => 'oldka/v4Game',    // v4游戏专区
  'v4/hall/kainfo.do' => 'oldka/v4Card',    // v4游戏发号页面
  'v4/hall/tqlist.do' => 'oldka/cardList',    // v4游戏礼包列表
  'v4/home/homePage.do' => 'oldka/home', // 新闻页面
  'v4/integralMall/' => 'oldka/goodsList', // 礼包列表页面
  'v4/lottery/issueLottery.do' => 'oldka/lottery', // 抽奖页面
  'v4/main/index.do' => 'oldka/home',    // v4首页
  'v4/search/search.do' => 'oldka/search', // 搜索页面
  'v4/task/listTask' => 'oldka/task', // 任务页面
  'v4/task/my' => 'oldka/my', // 个人中心页面
  'v4/userCenter/' => 'oldka/my', // 个人中心页面
  'v4/' => 'oldka/home', // v4首页

];



$url = '/rid1/35/xdfdx_test.html';
$_REQUEST = [];
$objRouterHelper = new RouterHelper($url);
$route = $objRouterHelper->urlRewrite($url);
var_dump($route, $url, $_REQUEST);

$orginUrl = url($route, $_REQUEST);
var_dump('orginUrl:' . $orginUrl);

$url = '/v5/';
$_REQUEST = [];
var_dump($objRouterHelper->urlRewrite($url));
var_dump($route, $url, $_REQUEST);

$orginUrl = url($route, $_REQUEST);
var_dump('orginUrl:' . $orginUrl);


$url = '/book_25009.html';
$_REQUEST = [];
var_dump($objRouterHelper->urlRewrite($url));
var_dump($route, $url, $_REQUEST);

$orginUrl = url($route, $_REQUEST);
var_dump('orginUrl:' . $orginUrl);


$url = '/25009.html';
$_REQUEST = [];
var_dump($objRouterHelper->urlRewrite($url));
var_dump($route, $url, $_REQUEST);

$orginUrl = url($route, $_REQUEST);
var_dump('orginUrl:' . $orginUrl);

$url = '/hall/ddxxx';
$_REQUEST = [];
var_dump($objRouterHelper->urlRewrite($url));
var_dump($route, $url, $_REQUEST);

$orginUrl = url($route, $_REQUEST);
var_dump('orginUrl:' . $orginUrl);

$url = '/hall';
$_REQUEST = [];
var_dump($objRouterHelper->urlRewrite($url));
var_dump($route, $url, $_REQUEST);

$orginUrl = url($route, $_REQUEST);
var_dump('orginUrl:' . $orginUrl);


