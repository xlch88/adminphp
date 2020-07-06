<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : 函数集:辅助函数
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

if(!function_exists('get_curl')){
	/**
	 * 使用curl进行http访问
	 *
	 * @param mixed          $url     地址
	 * @param boolean|string $post    post数据,不为false则以post访问并提交数据
	 * @param boolean|string $referer referer来源
	 * @param boolean|string $cookie  cookie
	 * @param boolean        $header  是否在返回的数据中包含头部信息
	 * @param boolean|string $ua      客户端标识
	 * @param boolean        $justheader  是否隐藏除header外的数据(只保留头部信息)，配合$header使用
	 * @param int            $timeout 超时时间，单位秒
	 * @return string
	 */
	function get_curl($url, $post = false, $referer = false, $cookie = false, $header = false, $ua = false, $justheader = false, $timeout = 5, $customHeader = false, $cookieFile = 0, $cookieFileType = 0) {
		if(is_array($url)){
			$args = array_merge([
				'url'			=> '',
				'post'			=> 0,
				'referer'		=> 0,
				'cookie'		=> 0,
				'header'		=> 0,
				'ua'			=> 0,
				'justheader'	=> 0,
				'timeout'		=> 5,
				'customHeader'	=> 0,
				'cookieFile'	=> 0,
				'cookieFileType'=> 0
			], $url);
			
			extract($args);
		}
		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		if($customHeader){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $customHeader);
		}else{
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Accept: application/json',
				'Accept-Encoding: gzip,deflate,sdch',
				'Accept-Language: zh-CN,zh;q=0.8',
				'Connection: close'
			]);
		}
		
		if ($post) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if ($header) {
			curl_setopt($ch, CURLOPT_HEADER, true);
		}
		if ($cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		if ($referer) {
			if ($referer === 1) {
				curl_setopt($ch, CURLOPT_REFERER, 'https://www.adminphp.net/');
			} else {
				curl_setopt($ch, CURLOPT_REFERER, $referer);
			}
		}
		if ($ua) {
			curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		} else {
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36');
		}
		if ($justheader) {
			curl_setopt($ch, CURLOPT_NOBODY, 1);
		}
		
		if($cookieFile){
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
		}
		
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
}

if(!function_exists('real_ip')){
	/**
	 * 获取客户端真实IP，一般情况下，使用CDN会导致获取到的ip是CDN的，使用该函数可以解决这个问题。
	 * 存在伪造ip的安全隐患，建议您重新定义该函数。
	 *
	 * @return string
	 */
	function real_ip() {
		$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		return $ip;
	}
}

if(!function_exists('randString')){
	/**
	 * 获取指定长度的0-9a-zA-Z随机字符
	 *
	 * @param int  $length     长度 
	 * @param bool $verifyCode 验证码模式，删除了部分不好区分的字符，如1iIl o0O
	 * @return string
	 */
	function randString($length, $verifyCode = false) {
		if($verifyCode){
			$strPol = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		}else{
			$strPol = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
		}
		
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str.= $strPol[rand(0, strlen($strPol) - 1) ];
		}
		return $str;
	}
}

if(!function_exists('returnJson')){
	/**
	 * 输出json并结束程序
	 *
	 * @param mixed $arr 数组或其他可被json_encode编码的值
	 * @return null
	 */
	function returnJson($arr) {
		header('Content-Type: application/json; charset=UTF-8');
		
		die(json($arr));
	}
}

if(!function_exists('json')){
	/**
	 * json_encode简化
	 *
	 * @param mixed $arr 数组或其他可被json_encode编码的值
	 * @return null
	 */
	function json($arr) {
		return json_encode($arr, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
	}
}

if(!function_exists('returnCallback')){
	/**
	 * 输出jsonp并结束程序
	 * 适用于jquery的jsonp
	 *
	 * @param mixed  $arr 数组或其他可被json_encode编码的值
	 * @param string $arr 回调函数名，留空则从i(callback)获取
	 * @return null
	 */
	function returnCallback($arr, $key = '') {
		header('Content-Type: application/javascript; charset=UTF-8');
		
		$key = safe_html($key ? : i('callback'));
		die($key . '(' . json_encode($arr, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE) . ')');
	}
}

if(!function_exists('returnXML')){
	/**
	 * 输出xml并结束程序
	 *
	 * @param array            $arr  数组
	 * @param string           $root 根
	 * @return null
	 */
	function returnXML($arr, $root = null) {
		header('Content-Type: application/xml; charset=UTF-8');
		
		die(xml($arr, $root));
	}
}

if(!function_exists('array_merge2')){
	/**
	 * 适用于二维数组的array_merge
	 * 主要用来合并默认配置项
	 *
	 * @param array $arr1 缺省值组
	 * @param array $arr2 原数组
	 */
	function array_merge2($arr1, $arr2){
		foreach($arr2 as $index => $value){
			if(is_array($value) && array_keys($value) !== range(0, count($value) - 1)){
				$arr1[$index] = isset($arr1[$index]) ? array_merge2($arr1[$index], $value) : $value;
			}else{
				$arr1[$index] = $value;
			}
		}
		
		return $arr1;
	}
}

if(!function_exists('format_bytes')){
	function format_bytes($size, $delimiter = '') {
		$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
		return round($size, 2) . $delimiter . $units[$i];
	}
}

if(!function_exists('xml')){
	/**
	 * 数组转换为xml
	 * 支持二维数组
	 * @param array            $arr  数组
	 * @param string           $root 根
	 * @param SimpleXMLElement $xml  SimpleXMLElement
	 * @return string
	 */
	function xml($arr, $root = null, $xml = null) {
		if ($xml === null) {
			$xml = new \SimpleXMLElement($root !== null ? $root : '<root/>');
		}
		foreach ($arr as $key => $value) {
			if(is_array($value)){
				xml($value, $key, $xml->addChild($key));
			}else{
				$xml->addChild($key, $value);
			}
		}
		return $xml->asXML();
	}
}


if(!function_exists('vendor')){
	function vendor($path){
		$file = appPath . 'Common/Vendor/' . $path;
		
		if(is_file($file . '.php')){
			include($file . '.php');
			return true;
		}elseif(is_file($file . '.class.php')){
			include($file . '.class.php');
			return true;
		}
		
		return false;
	}
}

function getCenterText($text, $left, $right, $lr = false){
	$text = explode($left, $text);
	$text = explode($right, $text[1]);
	$text = $text[0];
	
	if($lr){
		$text = $left . $text . $right;
	}
	
	return $text;
}