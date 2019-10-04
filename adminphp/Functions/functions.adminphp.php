<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : 函数集:框架函数
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

/**
 * 输出模板
 * 实际位置:\AdminPHP\View::view()
 * 
 * @param string  $_templateFile 模板文件
 * @param array   $args          参数
 * @param boolean $_isRoot       是否从根目录
 * @return void
 */
function view($templateFile_, $args = [], $isRoot = false){
	return \AdminPHP\View::view($templateFile_, $args, $isRoot);
}

/**
 * 生成地址
 * 实际位置:\AdminPHP\Router::url()
 * 
 * @param string $route 指向位置，“应用名/控制器名/方法名”
 * @param string $args  参数
 * @return string
 */
function url($router = '', $args = ''){
	return \AdminPHP\Router::url($router, $args);
}

/**
 * 执行钩子
 * 函数别名: do_hook()
 * 实际位置:\AdminPHP\Hook::do()
 * 
 * @param string $id   钩子ID
 * @param array  $args 参数们
 * @return boolean
 */
function do_hook($id, $args = []){
	return \AdminPHP\Hook::do($id, $args);
}

/**
 * 将函数添加到钩子队列
 * 越早添加的越早被执行
 * 若函数返回FALSE(不包含空) 则停止执行下一个钩子
 * 若函数返回TRUE 则停止执行并返回TRUE
 * 实际位置:\AdminPHP\Hook::add()
 * 
 * @param string   $id       钩子ID
 * @param function $function 回调函数
 * @return void
 */
function add_hook($id, $function){
	return \AdminPHP\Hook::add($id, $function);
}

/**
 * 多语言处理
 * 若未从语言字典中找到$text,则使用$default,若没有传入$default则直接处理$text.
 * 实际位置:\AdminPHP\Language::languagePrintf()
 *
 * @param string $value   语言原文或者数组路径
 * @param array  $args    参数
 * @param mixed  $default 默认值
 * @return mixed
 */
function l($value, $args = [], $default = null){
	return \AdminPHP\Language::languagePrintf($value, $args, $default);
}

/**
 * 获取参数
 * 
 * @param string $i      参数键值
 * @param string $method 类型[1/get, 2/post, 0/all, args] 其中args为路由值
 * @param mixed  $filter 过滤，支持functions.safe内的函数，支持多个使用","分割。若传入数组则仅限数组内的值，若都不匹配则返回第一个值
 * @return mixed
 */
function i($i, $method = 'all', $filter = ''){
	$return = null;
	
	$method = strtolower("$method");
	switch($method){
		case '1':
		case 'get':
			$return = isset($_GET[$i]) ? $_GET[$i] : i($i, 'args');
		break;
		
		case 'args':
			$return = isset(\AdminPHP\Router::$args[$i]) ? \AdminPHP\Router::$args[$i] : '';
		break;
		
		case '2':
		case 'post':
			$return = isset($_POST[$i]) ? $_POST[$i] : '';
		break;
		
		case '0':
		case 'all':
		default:
			$return = isset($_REQUEST[$i]) ? $_REQUEST[$i] : i($i, 'args');
		break;
	}
	
	if(is_array($filter)){
		$return = in_array($return, $filter) ? $return : $filter[0];
	}else{
		$filter_ = explode(',', $filter);
		foreach($filter_ as $filter){
			switch(strtolower($filter)){
				case 'html':
				case 'sql':
				case 'attr':
				case 'url':
				case 'path':
					$return = safe2($return, $filter);
				break;
				
				case 'int':
					$return = (int)$return;
				break;
				
				case 'float':
				case 'number':
					$return = (float)$return;
				break;
				
				case 'bool':
				case 'boolean':
					$return = in_array(strtolower($return), ['1', 'true', 't', 'yes', 'y', '√', 'on']);
				break;
			}
		}
	}
	
	return $return;
}

/**
 * 输出系统提示
 *
 * @param string $notice 提示内容
 * @param string $go     跳转到的页面
 * @param int    $time   跳转倒计时(秒)
 * @return void
 */
function notice($notice, $go = '', $time = 0){
	$notice = [
		'notice'	=>	$notice,
		'time'		=> $time
	];
	
	if($go){
		$notice['go'] = $go;
	}
	
	extract($notice);
	include(adminphp . 'Template/notice.php');
	
	/* 性能统计 END */
	\AdminPHP\PerformanceStatistics::log('END');
	\AdminPHP\PerformanceStatistics::show();
	die();
}

/**
 * 输出系统页面
 * 比较丰富美观的系统页面
 *
 * @param array $args 参数列表
 * @return void
 */
function sysinfo($args = []){
	if(!isset($args['title'])){
		switch($args['type']){
			case 'success':
				$args['title'] = '耶~！成功了呢！';
			break;
			
			case 'info':
				$args['title'] = '提示信息';
			break;
			
			case 'error':
				$args['title'] = '啊哈... 出了一点点小问题_(:з」∠)_';
			break;
		}
	}
	if(!isset($args['more'])){
		switch($args['type']){
			case 'success':
				$args['moreTitle'] = '温馨提示：';
				$args['more'] = [
					'您进行的操作已经完成了！'
				];
			break;
			
			case 'error':
				$args['moreTitle'] = '啊哈... 出了一点点小问题_(:з」∠)_';
				$args['more'] = [
					'你进入了一个未知的领域...',
					'手滑输错了地址',
					'网线被机房管理员踢了',
					'没有张贴『永不宕机』灵☯符'
				];
			break;
		}
	}
	if(!isset($args['code'])){
		switch($args['type']){
			case 'success':
				$args['code'] = '200';
			break;
			
			case 'info':
				$args['code'] = '233';
			break;
			
			case 'error':
				$args['code'] = '500';
			break;
		}
	}
	
	$defaultValue = [
		'code'=>'404',
		'type'=>'error', //[info, error, success]
		'title'=>'啊哈... 出了一点点小问题_(:з」∠)_',
		'info'=>'页面找不到啦...',
		'moreTitle'=>'可能的原因：',
		'more'=>[
			'你进入了一个未知的领域...',
			'手滑输错了地址',
			'网线被机房管理员踢了',
			'没有张贴『永不宕机』灵☯符'
		],
		'statusCode'=>'233',
		'buttons'=>false,
		'autoJump'=>false
		/*
		'autoJump'=>[
			'url'=>'/',
			'sec'=>'60'
		]
		*/
	];
	
	$defaultValue = array_merge($defaultValue, $args);
	
	if($defaultValue['buttons'] == false || !is_array($defaultValue['buttons'])) $defaultValue['buttons'] = [
		[
			'type'=>'success',
			'title'=>'返回首页',
			'href'=>'/',
			'target'=>'_self'
		],
		[
			'type'=>'danger',
			'title'=>'返回上一层',
			'href'=>'javascript:history.go(-1)',
			'target'=>'_self'
		]
	];
	
	$defaultValue['colorList'] = [
		'success'	=> '#59a734',
		'error'		=> '#ffb1b1',
		'info'		=> '#2488ff'
	];
	
	$defaultValue['color'] = isset($defaultValue['color']) ? $defaultValue['color'] : 
		isset($defaultValue['colorList'][$defaultValue['type']]) ? $defaultValue['colorList'][$defaultValue['type']] : $defaultValue['colorList']['success'];

	extract($defaultValue);
	
	@header('HTTP/1.1 ' . $code);
	include(adminphp . 'Template/sysinfo.php');
	
	/* 性能统计 END */
	\AdminPHP\PerformanceStatistics::log('END');
	\AdminPHP\PerformanceStatistics::show();
	die();
}