<?php
function view($t, $args = []){
	global $userInfo;
	global $db;
	global $view;
	
	if(!$view) $view = [];
	
	foreach($args as $key => $value){
		$GLOBALS['view'][$key] = $value;
	}
	
	foreach($GLOBALS['view'] as $key => $value){
		$$key = $value;
	}
	
	$tmp = file_get_contents(template . $t . '.php');
	
	$tmp = str_replace('_assets_m', 'https://assets.adminphp.net/main', $tmp);
	
	eval('?>' . $tmp);
}
function notice($notice, $go = ''){
	$notice = [
		'notice'	=>	$notice
	];
	
	if($go){
		$notice['go'] = $go;
	}
	
	view('common/notice', $notice);
	die();
}
function safestr($string){
	return str_replace(['/', '\\', '.'], '', $string);
}
function i($i){
	return isset($_REQUEST[$i]) ? $_REQUEST[$i] : '';
}
function db(){
	global $db;
	return $db;
}
function returnJson($arr){
	die(json_encode($arr, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
}
function safePath($arg){
	return str_replace(['../', './', '/..', '/.', '..'], '', $arg);
}
function daddslashes($string, $force = 0, $strip = FALSE) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force, $strip);
			}
		} else {
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $string;
}
function real_ip(){
	$ip = $_SERVER['REMOTE_ADDR'];
	if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
		foreach ($matches[0] AS $xip) {
			if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
				$ip = $xip;
				break;
			}
		}
	}
	return $ip;
}

function randString($length){
	$str = null;
	$strPol = "0123456789abcdefghijklmnopqrstuvwxyz";//大小写字母以及数字
	$max = strlen($strPol)-1;

	for($i=0;$i<$length;$i++){
		$str.=$strPol[rand(0,$max)];
	}
	return $str;
}