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

// 定义常量,这些常量缺一不可
define('root',		dirname(__FILE__) . DIRECTORY_SEPARATOR);

// 引入框架文件
include(root . 'adminphp' . DIRECTORY_SEPARATOR . 'Init.php');

// 启动 (<ゝω·)☆
\AdminPHP\AdminPHP::init(array(
	/* 定义APP以及模板目录 */
	'path'		=> [
		'template'	=> root . 'template',
		'app'		=> root . 'app'
	],
	
	/* URL路由 */
	'router'	=> [
		/* 如果直接输入域名进来/访问首页，我应该路由到哪里？ */
		'index'		=> 'index/index',
		
		/* 路由缺省值 */
		'default'	=> [
			'a'	=> '',
			'c'	=> 'index',
			'm'	=> 'index'
		],
			
		/* 路由方案类型
		   0 = 关闭			=> ?a=[app]&c=[controller]&m=[method]
		   1 = REQUEST_URI	=> index.php/aaa/bbb/ccc
		   2 = $_GET['s']	=> index.php?s=aaa/bbb/ccc
		   3 = index.php?	=> index.php?aaa/bbb/ccc
		*/
		'router'	=> 1,
		
		/* Rewrite (中文:伪静态/URL重写)
		   开启后可以隐藏index.php, 让地址栏更美观.
		   当然，需要修改网站服务器软件的配置。
		*/
		'rewrite'	=> 0,
	],
	
	/* 调试模式
	   非黑即白,设置为false将屏蔽任何可能泄露的信息,反之将尽可能显示所有的信息. 
	   上线后一定设置为【false】！
	*/
	'debug'		=> true,
	
	/* 性能统计 */
	'performanceStatistics'	=> [
		'enable'	=> false,	//启用 
		'show'		=> true		//显示在页面底部的注释
	],
	
	/* 防止CSRF攻击 */
	'AntiCSRF'	=> [
		'enable'		=> true,				//启用
		'cookieName'	=> 'adminphp_formhash',	//COOKIE 键名
		'sessionName'	=> 'adminphp_formhash',	//SESSION 键名
		'argName'		=> 'formhash',			//POST请求 键名
		'varName'		=> 'formHash'			//View 变量名
	],
	
	/* 多语言支持 */
	'language'	=> [
		'use'			=> 'zh-CN',				//程序使用的默认语言
		'cookieName'	=> 'adminphp_language'	//用于记录用户自定义语言的COOKIE键名
	],
	
	/* 缓存功能 */
	'cache'		=> [
		'enable'	=> true,											//是否启用
		'engine'	=> 'file',											//存储引擎(file=文件)
		'path'		=> root . 'runtime' . DIRECTORY_SEPARATOR . 'cache'	//文件保存路径
	],
	
	/* 时区设置 date_default_timezone_set() */
	'timezone'	=> 'PRC'
));