<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : app\Index\Controller\IndexController
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace App\Index\Controller;

class IndexController{
	public function index(){
		sysinfo([
			'title'			=> 'Hello AdminPHP',
			'type'			=> 'success',
			'code'			=> '233',
			'info'			=> 'Powered By AdminPHP',
			'moreTitle'		=> '这是AdminPHP的默认首页',
			'more'			=> [
				'欢迎您使用AdminPHP',
				'Github: http://github/xlch88/adminphp',
				'Copyright &copy; 2021 moesys.',
				'By.Dark495 2021'
			]
		]);
	}
	
	public function cli(){
		// 请在 public 目录下使用 控制台 执行命令 "php index.php colorTest"
		
		foreach([ 1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 'a', 'b', 'c', 'd', 'e', 'f' ] as $color){
			print_c('&' . $color . str_repeat($color, 20));
		}
		
		foreach([
			'n'	=> 'underline',
			'l'	=> 'bold',
			'm'	=> 'strikethrough',
			'o'	=> 'italic'
		] as $code => $format){
			print_c('&' . $code . $format);
		}
	}
}
