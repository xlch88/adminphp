<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : index.php (页面入口)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

/*
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Max-Age: 3600');
if(($_SERVER['REQUEST_METHOD'] ?? '') == 'OPTIONS') { //跨域支持
	header("HTTP/1.1 200 OK");
	die();
}
*/

// 定义常量,这些常量缺一不可
define('root',		realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);

// 引入框架文件
include(root . 'adminphp' . DIRECTORY_SEPARATOR . 'Init.php');
