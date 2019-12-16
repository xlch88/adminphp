<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : 类:数据库
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

use AdminPHP\PerformanceStatistics;
use AdminPHP\Exception\DBException;

class DB {
	public $link = null;
	public $log = [];
	public static $prefixReplace = '6F04CC78DA08B37A';
	
	public $config = [
		/* --   数据库类型   -- */
		'type'		=> 'mysql',			//类型[mysql,sqllite]
		
		/* --     MySQL      -- */
		'ip'		=> '',				//IP
		'port'		=> 3306,			//端口
		'username'	=> '',				//用户名
		'password'	=> '',				//密码
		'db'		=> '',				//数据库名
		'unixSocket'=> '',				//使用unix socket连接，若此项不为空，则忽略上面字段直接使用此字段进行连接
		
		/* --     SQLite     -- */
		'file'		=> '',				//SQLite文件
		
		/* --     PDO DSN    -- */
		'dsn'		=> '',				//若此项不为空，则忽略上面字段直接使用此字段进行连接
		
		/* --      选项      -- */
		'isLogSQL'	=> true,			//是否记录已查询SQL
		'charset'	=> 'utf8',			//编码
		'options'	=> [				//driver_options
			\PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION
		],
		
		/* --      表前缀    -- */
		'prefix'	=> '',				//表前缀
	];
	
	public $isThrow = true;	//出现错误时是否抛出异常
	
	/**
	 * 初始化
	 * 
	 * @param array   $dbConfig 数据库连接配置
	 * @param boolean $isThrow  连接失败是否抛出异常
	 * @return array
	 */
	public function __construct($config, $isThrow = true){
		$this->config = array_merge($this->config, $config);
		$this->isThrow = $isThrow;
		
		try{
			if($this->config['dsn']){
				$this->link = new \PDO($this->config['dsn'], $this->config['username'], $this->config['password'], $this->config['options']);
			}else{
				switch($this->config['type']){
					case 'mysql':
						$dsn = 'mysql:dbname=' . $this->config['db'] . ';host=' . $this->config['ip'] . ';charset=' . $this->config['charset'] . ';port=' . $this->config['port'];
						$this->link = new PDO($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
					break;
					
					case 'sqlite':
						$dsn = 'sqlite:' . $this->config['file'];
						$this->link = new PDO($dsn, '', '', $this->config['options']);
					break;
				}
			}
			$this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}catch(PDOException $e){
			if($this->isThrow){
				throw new DBException(0, $this, [
					'PDOException'	=> $e,
					'dsn'			=> isset($dsn) ? $dsn : $this->config['dsn']
				]);
			}
			
			return false;
		}
		
		$this->query('set sql_mode = ""');
		$this->query('set character set "' . $this->config['charset'] . '"');
		$this->query('set names "' . $this->config['charset'] . '"');
		
		return true;
	}
	
	/**
	 * 获取查询结果(全部)
	 * 
	 * @param string $sql SQL语句
	 * @return array
	 */
	public function get_rows($sql, $args = [], $fetch_style = PDO::FETCH_ASSOC){
		$stmt = $this->query($sql, $args);
		
		return $stmt->fetchAll($fetch_style);
	}
	
	/**
	 * 获取查询结果(第一行)
	 * 
	 * @param string $sql SQL语句
	 * @return array
	 */
	public function get_row($sql, $args = [], $fetch_style = PDO::FETCH_ASSOC){
		$result = $this->get_rows($sql, $args, $fetch_style);
		
		return $result ? $result[0] : false;
	}
	
	/**
	 * 修改符合条件的行
	 * 
	 * @param string $table 表名
	 * @param string $data 提交的数据
	 * @param mixed  $where 条件
	 * @return result
	 */
	public function update(string $table, array $data, $where){
		$sql = 'UPDATE `[T]' . $table . '` SET ' . $this->arr2sql($data, 'insert') . ' WHERE ' . (is_array($where) ? $this->arr2sql($where) : $where);
		
		return $this->exec($sql);
	}
	
	/**
	 * 删除符合条件的行
	 * 
	 * @param string $table 表名
	 * @param mixed  $where 条件
	 * @return result
	 */
	public function delete(string $table, $where){
		$sql = 'DELETE FROM `[T]' . $table . '` WHERE ' . (is_array($where) ? $this->arr2sql($where, 'where') : $where);
		
		return $this->exec($sql);
	}
	
	/**
	 * 插入行并返回插入的主键ID
	 * 
	 * @param string $sql SQL语句
	 * @return int
	 */
	public function insert($sql, $args = []){
		$stmt = $this->query($sql, $args);
		
		return $this->link->lastInsertId();
	}
	
	/**
	 * 以数组形式插入行并返回插入的主键ID
	 * 
	 * @param string $table 表名
	 * @param mixed $data 数组
	 * @return int
	 */
	public function insert_array(string $table, $data){
		$sql = 'INSERT INTO `[T]' . $table . '` SET ' . (is_array($data) ? $this->arr2sql($data, 'insert') : $data);
		
		return $this->insert($sql);
	}
	
	/**
	 * 获取COUNT查询数量
	 * 
	 * @param string  $sql  SQL语句
	 * @param array   $args 参数
	 * @param boolean $int  是否转换为整数
	 * @return int
	 */
	public function count($sql, $args = [], $int = true){
		if(!($result = $this->get_row($sql, $args, PDO::FETCH_BOTH))){
			return 0;
		}
		
		if(!isset($result[0][0])){
			return 0;
		}
		
		return $int ? (int)$result[0] : $result[0];
	}
	
	public function handleSQL($sql){
		$sql = str_replace(['[T]', self::$prefixReplace], [$this->config['prefix'], '[T]'], $sql);
		
		PerformanceStatistics::$SQLCount++;
		if($this->config['isLogSQL']) $this->log[] = $sql;
		
		return $sql;
	}
	
	/**
	 * 查询SQL语句
	 * 
	 * @param string  $sql	 SQL语句
	 * @param array   $args	参数
	 * @param boolean $isThrow 出现错误是否抛出异常
	 * @return result
	 */
	public function query($sql, $args = [], $isThrow = null){
		if($isThrow === null) $isThrow = $this->isThrow;
		
		$sql = $this->handleSQL($sql);
		
		try{
			$stmt = $this->link->prepare($sql);
			$stmt->execute($args);
		}catch(PDOException $ex){
			if($isThrow){
				throw new DBException(1, $this, [
					'sql'		=> $sql,
					'args'		=> $args,
					'errorCode'	=> $stmt->errorCode(),
					'errorInfo'	=> $stmt->errorInfo()
				]);
			}
			
			return false;
		}
		
		return $stmt;
	}
	
	/**
	 * 执行SQL语句并返回影响行数
	 * PDO::exec()
	 * 
	 * @param string $sql SQL语句
	 * @param boolean $isThrow 出现错误是否抛出异常
	 * @return result
	 */
	public function exec($sql, $isThrow = null){
		if($isThrow === null) $isThrow = $this->isThrow;
		
		try{
			$sql = $this->handleSQL($sql);
			$return = $this->link->exec($sql);
		}catch(PDOException $ex){
			if($isThrow){
				throw new DBException(1, $this, [
					'sql'		=> $sql,
					'args'		=> $sql,
					'errorCode'	=> $ex->getMessage(),
					'errorInfo'	=> $ex->getCode()
				]);
			}
			
			return false;
		}
		
		return $return;
	}
	
	/**
	 * 获取一条数据
	 * 
	 * @param resource $stmt 查询返回结果
	 * @return array
	 */
	public function fetch($stmt, $style = PDO::FETCH_ASSOC){
		return $stmt->fetch($style);
	}
	
	/**
	 * 数组转换为sql语句
	 * 
	 * @param array  $arr 数组
	 * @param string $mode 模式(where或insert)
	 * @return string
	 */
	public function arr2sql($arr = [], $mode = 'where'){
		$return = [];
		foreach ($arr as $key => $value) {
			if($mode == 'insert'){
				if($key == '#'){
					if(!is_string($value)){
						throw new DBException(2, $this, ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = $value;
				}elseif(substr($key, 0, 1) == '#'){
					if(!is_string($value) && !is_numeric($value) && !is_bool($value)){
						throw new DBException(2, $this, ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes(substr($key, 1)) . '` = ' . $value;
				}elseif(substr($key, 0, 1) == '@'){
					if(!is_array($value)){
						throw new DBException(3, $this, ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes(substr($key, 1)) . '` = "' . addslashes(json_encode($value)) . '"';
				}else{
					if(!is_string($value) && !is_numeric($value) && !is_bool($value)){
						throw new DBException(2, $this, ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes($key) . '` = "' . addslashes($value) . '"';
				}
			}else{
				if($key == '#'){
					if(!is_string($value)){
						throw new DBException(2, $this, ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = $value;
				}elseif(substr($key, 0, 1) == '#'){
					if(!is_string($value) && !is_numeric($value) && !is_bool($value)){
						throw new DBException(2, $this, ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes(substr($key, 1)) . '` = ' . $value;
				}elseif(substr($key, 0, 1) == '@'){
					if(!is_array($value)){
						throw new DBException(3, $this, ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes(substr($key, 1)) . '` IN ("' . implode('", "', safe2($value, 'sql')) . '")';
				}else{
					if(!is_string($value) && !is_numeric($value) && !is_bool($value)){
						throw new DBException(2, $this, ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes($key) . '` = "' . addslashes($value) . '"';
				}
			}
			
			$return[] = $sql;
		}
		return implode(($mode == 'insert' ? ', ' : ' AND '), $return);
	}
	
	/**
	 * 转义特殊字符
	 * 
	 * @return string
	 */
	public static function safe($text){
		$text = addslashes($text);
		return str_replace('[T]', self::$prefixReplace, $text);
	}
	
	/**
	 * 获取错误信息
	 * 
	 * @param PDOStatement $stmt 查询返回结果
	 * @return string
	 */
	public function error($stmt){
		return '[' . $stmt->errorCode() . '] ' . $stmt->errorInfo();
	}
}