<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : 函数集:安全函数
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

/**  
* 过滤HTML标签,防止XSS。
* 如“<h1>”替换为“&lt;h1&gt;”
* 
* @param string $text 待过滤文本
* @return string
*/
function safe_html($text){
	return htmlspecialchars($text);
}

/**  
* 过滤SQL参数,防止注入.
* 可传入array或string.
* 
* @param string $text 待过滤文本/数组
* @return string
*/
function safe_sql($text){
	return \DB::safe($text);
}

/**  
* 过滤标签参数,防止XSS.
* 如：未过滤参数为“http://xlch.me/" <h1>”
* 显示为：“<img src="http://xlch.me/" <h1>">”
* 过滤后：“<img src="http://xlch.me/%22+<h1>">”
* 
* @param mixed $text 待过滤文本
* @return string
*/
function safe_attr($text){
	return htmlspecialchars($text);
}

/**  
* 不影响原意的情况下编码url,防止XSS和注入. (仍需safe_sql)
* 编码前：“http://xlch.me/赵耀biss/滑稽/qwq/<h1>XSS!</h1>/Сука блять.php?input=<script>alert(1);</script>”
* 编码后：“http://xlch.me/%E8%B5%B5%E8%80%80biss/%E6%BB%91%E7%A8%BD/qwq/%3Ch1%3EXSS!%3C/h1%3E/%D0%A1%D1%83%D0%BA%D0%B0+%D0%B1%D0%BB%D1%8F%D1%82%D1%8C.php?input=%3Cscript%3Ealert(1);%3C/script%3E”
* 
* @param string $text 待过滤文本
* @return string
*/
function safe_url($text){
    return str_replace([
		'%21',
		'%2A',
		'%27',
		'%28',
		'%29',
		'%3B',
		'%3A',
		'%40',
		'%26',
		'%3D',
		'%2B',
		'%24',
		'%2C',
		'%2F',
		'%3F',
		'%25',
		'%23',
		'%5B',
		'%5D'
	], [
		'!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]"
	], urlencode($text));
}

/**  
* 防止目录包含漏洞
* 编码前：GET['dir'] = “../../../.\../qwq.jpg\0.php”
* 编码后：safe_path(GET['dir']) = “qwq.jpg.php”
* 
* @param string $var 待过滤文本
* @return string
*/
function safe_path($text){
	return str_replace([
		chr(0),
		'..\\',
		'../',
		'\\..',
		'/..',
		
		'.\\',
		'./',
		'\\.',
		'/.',
		
		//文件名、目录名不应该包含以下符号
		':',
		'*',
		'?',
		'"',
		'<',
		'>',
		'|'
		
	], '', $text);
}

/**  
* 传入数组形式
* 
* @param mixed $var 待过滤文本数组
* @param string $method 过滤方法,以safe_开头的函数
* @return string
*/
function safe2($var, string $method){
	$method_ = 'safe_' . $method;
	
	if(is_array($var)) {
		foreach($var as $key => $val) {
			$var[$key] = safe2($val, $method);
		}
	} else {
		$var = $method_($var);
	}
	
	return $var;
}