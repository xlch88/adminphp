<?php
$userInfo = false;
if(isset($_COOKIE['adminphp_token'])){
	$userInfo = db()->get_row('select * from mj_user where token = "'.daddslashes($_COOKIE['adminphp_token']).'"');
}
if(!$userInfo && $c != 'Api'&& !($c == 'Index' && $m == 'debug')){
	notice('您的登录已经过期！请返回平台重新登录！', '#');
}