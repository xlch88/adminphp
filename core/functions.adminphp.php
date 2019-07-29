<?php
function view($templateFile_, $args = [], $isSystem = 0){
	\AdminPHP\View::view($templateFile_, $args, $isSystem);
}
function do_hook($id, $args = []){
	\AdminPHP\Hook::doHook($id, $args);
}
function add_hook($id, $function){
	\AdminPHP\Hook::addHook($id, $function);
}

function notice($notice, $go = ''){
	$notice = [
		'notice'	=>	$notice
	];
	
	if($go){
		$notice['go'] = $go;
	}
	
	view(adminphp . 'template/notice', $notice, 1);
	die();
}

function i($i, $method = 'all', $filter = ''){
	$return = null;
	
	switch($method){
		case 1:
		case 'get':
			$return = isset($_GET[$i]) ? $_GET[$i] : '';
		break;
		
		case 2:
		case 'post':
			$return = isset($_POST[$i]) ? $_POST[$i] : '';
		break;
		
		case 0:
		case 'all':
		default:
			$return = isset($_REQUEST[$i]) ? $_REQUEST[$i] : '';
		break;
	}
	
	switch($filter){
		case 'html':
		case 'sql':
		case 'attr':
		case 'url':
		case 'path':
			$func = 'safe_' . $filter;
			$return = $func($return);
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
			$return = in_array(strtolower($return), ['1', 'true', 't', 'yes', 'y', '√']) ? true : false;
		break;
	}
	
	return $return;
}

function sysInfo($args = []){
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
	
	$defaults = [
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
	
	foreach($args as $key=>$value){
		$defaults[$key] = $value;
	}
	if($defaults['buttons'] == false || !is_array($defaults['buttons'])) $defaults['buttons'] = [
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
	
	view(adminphp . 'template/sysinfo', $defaults, 1);
	
	exit();
}