<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : app/init.php (应用初始化配置)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

return [
	/* 定义APP以及模板目录 */
	'path'		=> [
		'template'	=> root . 'template',
		'app'		=> root . 'app'
	],
	
	/* URL路由 */
	'router'	=> [
		'web'	=> 'webRouter',
		'cmd'	=> 'cmdRouter',
	],
	
	/* 调试模式
	   非黑即白,设置为false将屏蔽任何可能泄露的信息,反之将尽可能显示所有的信息. 
	   上线后一定设置为【false】！
	*/
	'debug'			=> false,
	'debugCookie'	=> false,
	'debugInLan'	=> true,
	'debugInLocal'	=> true,
	
	/* 性能统计 */
	'performanceStatistics'	=> [
		'enable'	=> true,	//启用 
		'show'		=> true		//显示在页面底部的注释
	],
	
	/* 配置文件 */
	'config'	=> [
		'path'			=> appPath . 'Config',		//配置文件路径
		'prefix'		=> '',						//文件名前缀
		'subfix'		=> '.php',					//文件名后缀
		/* 'webConfigFile'	=> [
			'name'	=> 'webConfig',
			'type'	=> 'json'
		] */
	],
	
	/* 防止CSRF攻击 */
	'AntiCSRF'	=> [
		'enable'		=> false,				//启用
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
		'enable'	=> true,
		'engine'	=> 'file'
	],
	
	/* 时区设置 date_default_timezone_set() */
	'timezone'	=> 'PRC'
];