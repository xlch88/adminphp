<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Controller (控制器)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP;

use AdminPHP\AutoLoad;

class InitController{
	/**
	 * 初始化控制器
	 * 
	 * @return void
	 */
	static public function init(){
		global $a, $c, $m;
		
		if($a != ''){
			$controller = '\\App\\' . ucfirst($a) . '\\Controller\\' . ucfirst($c) . 'Controller';
		}else{
			$controller = '\\App\\Controller\\' . ucfirst($c) . 'Controller';
		}
		
		if(($file = AutoLoad::load($controller, false, true)) === FALSE){
			self::error(404); //控制器文件未找到
			return;
		}
		
		include($file);
		
		if(!class_exists($controller, false)){
			self::error(500); //控制器文件找到但是未找到控制器Class
			return;
		}
		
		if(!method_exists($controller, $m) || $m == 'init'){
			self::error(404); //控制器已加载但找不到方法
			return;
		}
		
		if(!((new \ReflectionMethod($controller, $m))->isPublic())){
			self::error(403); //访问私有方法
			return;
		}
		
		$controller = new $controller();
		
		//执行初始化方法
		if(method_exists($controller, 'init') && (new \ReflectionMethod($controller, 'init'))->isPublic())
			$controller->init();
		
		//执行控制器方法
		$return = $controller->$m();
		
		self::returnHandle($return);
	}
	
	static private function returnHandle($return){
		if(is_null($return)){
			return;
		}
		
		if(is_object($return) && get_class($return) === 'AdminPHP\\Model\\ControllerReturn'){
			switch($return->type){
				case 'status_success':
					call_user_func_array('returnSuccess', $return->data);
				break;
				case 'status_error':
					call_user_func_array('returnError', $return->data);
				break;
				case 'status_info':
					call_user_func_array('returnInfo', $return->data);
				break;
				
				case 'data_json':
					call_user_func_array('returnJson', $return->data);
				break;
				case 'data_jsonp':
					call_user_func_array('returnCallback', $return->data);
				break;
				case 'data_xml':
					call_user_func_array('returnXML', $return->data);
				break;
				
				default:
					throw new \InvalidArgumentException(l('ControllerReturn类型无法被处理!'));
				break;
			}
		}elseif(is_array($return)){
			call_user_func_array('returnData', [ $return, 'info' ]);
		}elseif(is_bool($return) === true){
			die($return ? 'true' : 'false');
		}else{
			die((string)$return);
		}
	}
	
	/**
	 * 发生错误的处理
	 * 
	 * @param int $type 错误代码
	 * @return void
	 */
	static private function error($type = 404){
		if(Hook::do('controller_error', [ 'type' => $type ])){ //用户自定义处理
			return;
		}
		switch($type){
			case 404:
				sysinfo(l('@adminphp.sysinfo.statusCode.404', $type, [
					'code'		=> '%s',
					'type'		=> 'error', //[info, error, success]
					'title'		=> '啊哈... 出了一点点小问题_(:з」∠)_',
					'info'		=> '页面找不到啦...',
					'moreTitle'	=> '可能的原因：',
					'more'		=> [
						'你进入了一个未知的领域...',
						'手滑输错了地址',
						'你在用脸滚键盘',
						'你的猫在键盘漫步'
					]
				]));
			break;
			
			case 500:
				sysinfo(l('@adminphp.sysinfo.statusCode.500', $type, [
					'code'		=> '%s',
					'type'		=> 'error', //[info, error, success]
					'title'		=> '啊哈... 出了一点点小问题_(:з」∠)_',
					'info'		=> '页面找不到啦...',
					'moreTitle'	=> '可能的原因：',
					'more'		=> [
						'程序员手滑写错了什么东西...',
						'手滑输错了地址',
						'你在用脸滚键盘',
						'你的猫在键盘漫步'
					]
				]));
			break;
			
			case 403:
				sysinfo(l('@adminphp.sysinfo.statusCode.403', $type, [
					'code'		=> '%s',
					'type'		=> 'error', //[info, error, success]
					'title'		=> '拒绝访问 (╯‵□′)╯︵┻━┻',
					'info'		=> '该页面拒绝访问',
					'moreTitle'	=> '可能的原因：',
					'more'		=> [
						'程序员手滑写错了什么东西...',
						'你在尝试干点不好的事情',
						'你在用脸滚键盘',
						'你的猫在键盘漫步'
					]
				]));
			break;
		}
	}
}