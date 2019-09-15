<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Exception:Init (异常类:初始化)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Exception;

use AdminPHP\Exception\Exception;

class InitException extends Exception{
    public function __construct($code, $path = '')
    {
		$this->code = $code;
		$this->path = $path;
		
		switch($this->code){
			case 0:
				$this->message = l('应用目录不存在！');
			break;
			
			case 1:
				$this->message = l('模板目录不存在！');
			break;
		}
    }
}