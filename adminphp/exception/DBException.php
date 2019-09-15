<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : Exception:DBException (异常类:数据库)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Exception;

use AdminPHP\Exception\Exception;

class DBException extends Exception{
    public function __construct($code, $db, $arg2 = '')
    {
		$this->code = $code;
		
		switch($this->code){
			case 0: //连接失败
				$this->message			= l('连接数据库失败！');
				$this->PDOException		= '[' . $arg2['PDOException']->getCode() . '] ' . $arg2['PDOException']->getMessage();
				$this->dsn				= $arg2['dsn'];
			break;
			
			case 1: //查询错误
				$this->message		= l('执行SQL查询语句时出现错误。');
				$this->sql			= $arg2['sql'];
				$this->args			= $arg2['args'];
				$this->PDOErrorCode	= $arg2['errorCode'];
				$this->PDOErrorInfo	= $arg2['errorInfo'];
				$this->log_sql		= $db->log;
			break;
			
			case 2: //arr2sql错误
				$this->message		= l('arr2sql转换错误: 值应该是文本型/整数型/布尔型，却传入了数组或其他类型。');
				$this->arr			= $arg2['arr'];
				$this->key			= $arg2['key'];
				$this->value		= $arg2['value'];
			break;
			
			case 3: //arr2sql错误
				$this->message		= l('arr2sql转换错误: 值应该是数组型，却传入了文本型/整数型/布尔型或其他类型。');
				$this->arr			= $arg2['arr'];
				$this->key			= $arg2['key'];
				$this->value		= $arg2['value'];
			break;
		}
		
		$this->dbConfig = $db->config;
    }
}