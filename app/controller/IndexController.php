<?php
namespace App\Controller;

class IndexController{
	public function hello(){
		view('index/index', [
			'text'	=> 'AdminPHP<br/>V2 Beta'
		]);
	}
	
	public function exception(){
		throw new \Exception('啊，异常了！', 23333);
	}
	
	public function sysinfo(){
		sysinfo([
			'code'	=> '233',
			'type'	=> 'error',
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