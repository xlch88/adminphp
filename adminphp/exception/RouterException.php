<?php
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
				$this->message = '路由规则内有未闭合括号!';
			break;
			
			case 1:
				$this->message = '路由解析失败!';
				$this->errText = $text;
			break;
			
			case 2:
				$this->message = '未找到正则表达式!';
				$this->regexName = $text;
			break;
		}
		
		$this->routeList = Router::$routeList;
		$this->regexList = Router::$regexList;
    }
}