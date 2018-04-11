<?php
/**
 * 资源下载Worker进程
 * User: XianDa
 * Date: 2017/5/26
 * Time: 14:01
 */

$startTime = time();
echo $startTime."脚本开始\n";
$st = microtime(true);
ini_set("display_errors", "On");
error_reporting(E_ALL & ~E_NOTICE);

require_once realpath(dirname(__FILE__)) . '/../../common.php';
require_once ROOT_PATH . '/bin/common_script.php';

// 这个脚本只能单进程进行
$pidFile = BASE_DIR . "/protected/bin/run/res2Bs2Master.pid";
$flag = singleProcess(getCurrentCommand(), $pidFile);
if (!$flag) {
    exit("Sorry, this script file has already been running ...,pid:{$pidFile}\n");
}

$objGameArticle = new TableHelper('game_article', 'dw_ka');
$_where = 'article_id > 100000';
$all_game_article = $objGameArticle->getALl(['_where' => $_where]);

foreach ($all_game_article as $key => $article) {
	$article_id = $article['article_id'];
	$source_url = $article['source_url'];
	$row = $objGameArticle->getRow(['source_url' => $source_url]);
	unset($article['article_id']);
	if ($row) {
		unset($article['source_url']);
		$objGameArticle->updateObject($article, ['source_url' => $source_url]);
	} else {
		$objGameArticle->addObject($article);
	}
	$objGameArticle->delObject(['article_id' => $article_id]);
}
