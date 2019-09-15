<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Exception:Config (异常类:配置文件)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Exception;

class ConfigException extends Exception{
    public function __construct($code, $class, $filename = '', $type = '')
    {
		$this->code = $code;
		
		$this->path = $class->path;
		$this->prefix = $class->prefix;
		$this->subfix = $class->subfix;
		
		switch($this->code){
			case 0:
				$this->message = l('配置文件目录不存在！');
			break;
			
			case 1:
				$this->message = l('配置文件不存在！');
				$this->filename = $filename;
			break;
			
			case 2:
				$this->message = l('配置文件未能通过类型验证！');
				$this->filename = $filename;
				$this->type = $type;
			break;
		}
    }
}