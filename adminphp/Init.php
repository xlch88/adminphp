<?php
/* ----------------------------------------------- *
 | AdminPHP   Version : 2.0 beta
 | 简单粗暴的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

define('adminphp_version',		2000);
define('adminphp_version_name', '2.0');

if(version_compare(PHP_VERSION,'5.5.0', '<')){
	include(adminphp . 'template/oldVersion.php');
	die();
}

session_start();
date_default_timezone_set('PRC');

/* 性能统计 START */
include(adminphp . 'PerformanceStatistics.php');
\AdminPHP\PerformanceStatistics::begin();
\AdminPHP\PerformanceStatistics::log('START');

/* (<ゝω·)☆ キラッ~! Kira~! */
include(adminphp . 'AdminPHP.php');

/* 性能统计 END */
\AdminPHP\PerformanceStatistics::log('END');
\AdminPHP\PerformanceStatistics::show();