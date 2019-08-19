<?php
namespace AdminPHP\Exception;

use AdminPHP\Exception\Exception;

class DBException extends Exception{
    public function __construct($code, $db, $sql = '')
    {
		$this->code = $code;
		
		switch($this->code){
			case 0: //连接失败
				$this->message = '连接数据库失败!';
				$this->connect_errno = mysqli_connect_errno();
				$this->connect_error = mysqli_connect_error();
			break;
			
			case 1: //查询错误
				$this->message		= '查询错误:' . $db->error();
				$this->sql			= $sql;
				$this->query_errno	= mysqli_errno($db->link);
				$this->query_error	= mysqli_error($db->link);
				$this->log_sql		= $db->log;
			break;
		}
		
		$this->dbConfig = $db->dbConfig;
		
		$this->removeTraceCount = 1;
    }
}