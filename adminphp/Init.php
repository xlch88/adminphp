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

define('adminphp_version',		2002);
define('adminphp_version_name', '2.0');

if(version_compare(PHP_VERSION,'5.5.0', '<')){
	include(adminphp . 'Template/oldVersion.php');
	die();
}

/* 性能统计 START */
include(adminphp . 'PerformanceStatistics.php');
\AdminPHP\PerformanceStatistics::begin();
\AdminPHP\PerformanceStatistics::log('START');

/* (<ゝω·)☆ キラッ~! Kira~! */
include(adminphp . 'AdminPHP.php');