<?php
/*******************************************
 *                                         *
 *          Copyright (C) 2020             *
 *                                         *
 *******************************************
 *                                         *
 *	Author  :   Xlch88                     *
 *  Date    :   2020-04-01                 *
 *	Email   :   i@xlch.me                  *
 *                                         *
 *******************************************/

return [
	'routes'	=> [
		'*'			=> [
			'/'		=> 'index/index/index'
		],
	],
	'regex'		=> [ ],
	'options'	=> [
		/* 如果直接输入域名进来/访问首页，我应该路由到哪里？ */
		'index'		=> 'index/index/index',
		
		/* 路由缺省值 */
		'default'	=> [
			'a'	=> 'index',
			'c'	=> 'index',
			'm'	=> 'index'
		],
		
		/* 路由方案类型
		   0 = 关闭			=> ?a=[app]&c=[controller]&m=[method]
		   1 = PATH_INFO	=> index.php/aaa/bbb/ccc
		   2 = $_GET['s']	=> index.php?s=aaa/bbb/ccc
		   3 = index.php?	=> index.php?aaa/bbb/ccc
		*/
		'router'	=> 0,
		
		/* Rewrite (中文:伪静态/URL重写)
		   开启后可以隐藏index.php, 让地址栏更美观.
		   当然，需要修改网站服务器软件的配置。
		*/
		'rewrite'	=> false,
		
		/* 自动路由 */
		'autoRoute'	=> [
			'enable'	=> true,
			'prefix'	=> '',
			'explode'	=> '/',
			'subfix'	=> '',
		],
		
		/*
			禁止以?a=[app]&c=[controller]&m=[method]的形式访问
			开启后可增强安全性，防止他人绕过路由直接访问到方法。
			开启此项的同时，自动路由也会被关闭，无论上面↑的enable设置与否。
			开启此项后，将只能通过路由规则访问到方法，且使用url()函数生成无法被解析的路由时，会抛出异常。
		*/
		'disableArgumentAccess'	=> false
	]
];
