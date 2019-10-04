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

function get_curl($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $justheader = 0, $timeout = 3) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $httpheader[] = "Accept:application/json";
    $httpheader[] = "Accept-Encoding:gzip,deflate,sdch";
    $httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
    $httpheader[] = "Connection:close";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
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
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36');
    }
    if ($justheader) {
        curl_setopt($ch, CURLOPT_NOBODY, 1);
    }
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_NOSIGNAL, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $ret = curl_exec($ch);
    curl_close($ch);
    return $ret;
}

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

function randString($length) {
    $str = null;
    $strPol = "0123456789abcdefghijklmnopqrstuvwxyz"; //大小写字母以及数字
    $max = strlen($strPol) - 1;
    for ($i = 0; $i < $length; $i++) {
        $str.= $strPol[rand(0, $max) ];
    }
    return $str;
}

function returnJson($arr) {
    die(json_encode($arr, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
}

function returnCallback($arr, $key = '') {
    $key = safe_html($key ? : i('callback'));
    die($key . '(' . json_encode($arr, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE) . ')');
}
/**
 * 适用于二维数组的array_merge
 * 主要用来合并默认配置项
 *
 * @param array $arr1 缺省值组
 * @param array $arr2 原数组
 */
function array_merge2($arr1, $arr2){
	foreach($arr2 as $index => $value){
		if(is_array($value)){
			$arr1[$index] = isset($arr1[$index]) ? array_merge2($arr1[$index], $value) : $value;
		}else{
			$arr1[$index] = $value;
		}
	}
	
	return $arr1;
}