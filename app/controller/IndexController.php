<?php
namespace App\Controller;

use AdminPHP\AdminPHP;

class IndexController{
	public function index(){
		view('index/index', [
			'text'	=> 'AdminPHP<br/>V2 Beta'
		]);
	}
	
	public function router(){
		$username = i('id', 'args', 'html');
		
		echo '<h1>Welcome ' . $username . '~!</h1>';
	}
	
	public function exception(){
		AdminPHP::$config['debug'] = false;
		
		throw new \Exception('啊，异常了！', 23333);
	}
	
	public function exception2(){
		AdminPHP::$config['debug'] = true;
		
		throw new \Exception('啊，异常了！', 23333);
	}
	
	public function exception3(){
		AdminPHP::$config['debug'] = false;
		AdminPHP::$config['adminInfo'] = [
			'自定义信息1'	=> '啦啦啦1',
			'自定义信息2'	=> '啦啦啦2',
			'自定义信息3'	=> '啦啦啦3'
		];
		
		throw new \Exception('啊，异常了！', 23333);
	}
	
	public function sysinfo(){
		$type = i('type', 'all', ['error', 'success', 'info']);
		
		sysinfo([
			'code'	=> '233',
			'type'	=> $type,
			'info'	=> '这是一个提示页面',
			'more'	=> [
				'By.Xlch88',
				'<a href="https://github.com/xlch88/adminphp" target="_blank">https://github.com/xlch88/adminphp</a>',
				'https://xlch.me/',
				'第四条提示语'
			]
		]);
	}
	
	public function notice(){
		notice('这里是提示信息', '?qwq');
	}
}