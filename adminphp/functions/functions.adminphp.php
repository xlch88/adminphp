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

function view($templateFile_, $args = [], $isSystem = 0){
	return \AdminPHP\View::view($templateFile_, $args, $isSystem);
}

function url($router = '', $args = ''){
	return \AdminPHP\Router::url($router, $args);
}

function do_hook($id, $args = []){
	return \AdminPHP\Hook::do($id, $args);
}

function add_hook($id, $function){
	return \AdminPHP\Hook::add($id, $function);
}

function l($value, $args = [], $default = null){
	return \AdminPHP\Language::languagePrintf($value, $args, $default);
}

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
					$return = in_array(strtolower($return), ['1', 'true', 't', 'yes', 'y', '√']);
				break;
			}
		}
	}
	
	return $return;
}

function notice($notice, $go = '', $time = 0){
	$notice = [
		'notice'	=>	$notice,
		'time'		=> $time
	];
	
	if($go){
		$notice['go'] = $go;
	}
	
	extract($notice);
	include(adminphp . 'template/notice.php');
	
	/* 性能统计 END */
	\AdminPHP\PerformanceStatistics::log('END');
	\AdminPHP\PerformanceStatistics::show();
	die();
}

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
	include(adminphp . 'template/sysinfo.php');
	
	/* 性能统计 END */
	\AdminPHP\PerformanceStatistics::log('END');
	\AdminPHP\PerformanceStatistics::show();
	die();
}