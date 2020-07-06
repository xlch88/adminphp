<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Exception:Router (异常类:路由)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Exception;

use AdminPHP\Exception\Exception;
use AdminPHP\Router;

class RouterException extends Exception{
    public function __construct($code, $route = '', $text = '', $text2 = '')
    {
		$this->code = $code;
		
		switch($this->code){
			case 0:
				$this->route = $route;
				$this->message = l('路由规则内有未闭合括号！');
			break;
			
			case 1:
				$this->route = $route;
				$this->message = l('路由解析失败。');
				$this->errText = $text;
			break;
			
			case 2:
				$this->route = $route;
				$this->message = l('未找到正则表达式。');
				$this->regexName = $text;
			break;
			
			case 3:
				$this->message = l('读取路由配置失败。');
				$this->filename = $text;
				$this->type = $text2;
			break;
			
			case 4:
				$this->message = l('最终路由地址参数错误，必须为3个参数。');
				$this->routeTo = $route;
			break;
		}
		
		//$this->routeList = Router::$routeList;
		//$this->regexList = Router::$regexList;
    }
}