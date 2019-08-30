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
    public function __construct($code, $db, $sql = '', $arr2sql = [])
    {
		$this->code = $code;
		
		switch($this->code){
			case 0: //连接失败
				$this->message			= l('连接数据库失败！');
				$this->connect_errno	= mysqli_connect_errno();
				$this->connect_error	= mysqli_connect_error();
			break;
			
			case 1: //查询错误
				$this->message		= l('执行SQL查询语句时出现错误。');
				$this->sql			= $sql;
				$this->query_errno	= mysqli_errno($db->link);
				$this->query_error	= mysqli_error($db->link);
				$this->log_sql		= $db->log;
			break;
			
			case 2: //arr2sql错误
				$this->message		= l('arr2sql转换错误: 值应该是文本型/整数型/布尔型，却传入了数组或其他类型。');
				$this->arr			= $arr2sql['arr'];
				$this->key			= $arr2sql['key'];
				$this->value		= $arr2sql['value'];
			break;
			
			case 3: //arr2sql错误
				$this->message		= l('arr2sql转换错误: 值应该是数组型，却传入了文本型/整数型/布尔型或其他类型。');
				$this->arr			= $arr2sql['arr'];
				$this->key			= $arr2sql['key'];
				$this->value		= $arr2sql['value'];
			break;
		}
		
		$this->dbConfig = $db->dbConfig;
    }
}