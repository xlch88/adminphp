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
    public function __construct($code, $route = '', $text = '')
    {
		$this->code = $code;
		$this->route = $route;
		
		switch($this->code){
			case 0:
				$this->message = l('路由规则内有未闭合括号！');
			break;
			
			case 1:
				$this->message = l('路由解析失败。');
				$this->errText = $text;
			break;
			
			case 2:
				$this->message = l('未找到正则表达式。');
				$this->regexName = $text;
			break;
		}
		
		$this->routeList = Router::$routeList;
		$this->regexList = Router::$regexList;
    }
}