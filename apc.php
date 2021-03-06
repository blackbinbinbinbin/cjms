<?php
$white_ip_array = array('183.60.177.224/27','58.248.138.0/28', '113.108.232.32/28', '172.16.53.148/8');

function check_ip($ip_array = array(), $remote_ip = ''){
	$remote_ip = empty($remote_ip) ? $_SERVER['REMOTE_ADDR'] : $remote_ip;
	//判断ip是否在白名单
	foreach($ip_array as $ip){
		$ip_info = explode('/', $ip);
		$mask = isset($ip_info[1]) ? $ip_info[1] : 32;
		if(substr(sprintf("%032b", ip2long($ip_info[0])), 0, $mask) === substr(sprintf("%032b", ip2long($remote_ip)), 0, $mask)){
			return true;
		}
	}
	return false;
}

//不在ip白名单内，显示404页面
if( !check_ip($white_ip_array) ){
	header('Location: http://www.duowan.com/s/404/404.html');
	exit;
}

if( function_exists('apc_clear_cache') && apc_clear_cache() ){
	echo 1;
}else{
	echo 0;
}
?>