<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Init (框架引导、初始化)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

define('adminphp', dirname(__FILE__) . DIRECTORY_SEPARATOR);

const adminphp_version = 2003;
const adminphp_version_name = '2.2(Beta)';
const appPath = root . 'app' . DIRECTORY_SEPARATOR;

header("Content-type: text/html; charset=utf-8");

if(version_compare(PHP_VERSION,'5.6.0', '<')){
	include(adminphp . 'Template/oldVersion.php');
	die();
}

if(!realpath(appPath) || !is_dir(appPath)){
	die('boot failed. app path not found.');
}

if(!file_exists($configFile = appPath . 'init.php')){
	die('init file not found. (appPath/init.php)');
}

/* 性能统计 START */
include(adminphp . 'Module' . DIRECTORY_SEPARATOR . 'PerformanceStatistics.php');
\AdminPHP\Module\PerformanceStatistics::begin();
\AdminPHP\Module\PerformanceStatistics::log('START');

/* (<ゝω·)☆ キラッ~! Kira~! */
include(adminphp . 'AdminPHP.php');

// 启动 (<ゝω·)☆
\AdminPHP\AdminPHP::init(include($configFile));
