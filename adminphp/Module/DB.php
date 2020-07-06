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

namespace AdminPHP\Module;

use AdminPHP\Module\PerformanceStatistics;
use AdminPHP\Exception\DBException;
use AdminPHP\Module\DB\DBHelper;

class DB {
	public $link = null;
	public $log = [];
	public static $prefixReplace = '6F04CC78DA08B37A';
	public $isConnect = false;
	public static $dbList = [];
	public $dbid = null;
	public $sQuery = null;
	
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
		'connect'	=> false,			//是否主动连接(为否则在执行查询时再进行连接)
		'isThrow'	=> true,			//连接失败是否抛出异常
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
	 * @param array  $dbConfig 数据库连接配置
	 * @param string $id       数据库id，用于db()函数的返回
	 * @return boolean
	 */
	public function __construct($config, $id = 'default'){
		$this->config = array_merge($this->config, $config);
		$this->isThrow = $this->config['isThrow'];
		
		self::$dbList[$id] = &$this;
		$this->dbid = $id;
		
		if($this->config['connect']){
			return $this->connect();
		}
		
		return true;
	}
	
	/**
	 * 连接
	 */
	public function connect(){
		try{
			if($this->config['dsn']){
				$this->link = new \PDO($this->config['dsn'], $this->config['username'], $this->config['password'], $this->config['options']);
			}else{
				switch($this->config['type']){
					case 'mysql':
						$dsn = 'mysql:dbname=' . $this->config['db'] . ';charset=' . $this->config['charset'];
						
						if(!$this->config['unixSocket']){
							$dsn .= ';host=' . $this->config['ip'] . ';port=' . $this->config['port'];
						}else{
							$dsn .= ';unix_socket=' . $this->config['unixSocket'];
						}
						
						$this->link = new \PDO($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
					break;
					
					case 'sqlite':
						$dsn = 'sqlite:' . $this->config['file'];
						$this->link = new \PDO($dsn, '', '', $this->config['options']);
					break;
				}
			}
			
			$this->link->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			
			$this->config['dsn'] = $dsn;
		}catch(\PDOException $e){
			if($this->isThrow){
				throw new DBException(0, $this, [
					'PDOException'	=> $e,
					'dsn'			=> isset($dsn) ? $dsn : $this->config['dsn']
				]);
			}
			
			return false;
		}
		
		$this->isConnect = true;
		
		$this->execute('set sql_mode = "' . (!isset($this->config['sqlmode']) ? '' : $this->config['sqlmode']) . '"');
		$this->execute('set character set "' . $this->config['charset'] . '"');
		$this->execute('set names "' . $this->config['charset'] . '"');
	}
	
	/**
	 * 获取查询结果(全部)
	 * 
	 * @param string $sql SQL语句
	 * @return array
	 */
	public function get_rows($sql, $args = [], $fetch_style = \PDO::FETCH_ASSOC){
		$this->execute($sql, $args);
		
		return $this->sQuery->fetchAll($fetch_style);
	}
	
	/**
	 * 获取查询结果(第一行)
	 * 
	 * @param string $sql SQL语句
	 * @return array
	 */
	public function get_row($sql, $args = [], $fetch_style = \PDO::FETCH_ASSOC){
		$this->execute($sql, $args);
		
		return $this->sQuery->fetch($fetch_style);
	}
	
	/**
	 * 使用DBHelper生成查询语句
	 * 
	 * @param string $cols 需要取出的列
	 * @return \AdminPHP\Module\DB\DBHelper
	 */
	public function select(){
		$dbHelper = new DBHelper($this->dbid);
		return call_user_func_array(array($dbHelper, 'select'), array(func_get_args()));
	}

	/**
	 * 修改符合条件的行
	 * 
	 * @param string $table 表名
	 * @param string $data 提交的数据
	 * @param mixed  $where 条件
	 * @return result
	 */
	public function update(string $table = null, array $data = null, $where = null){
		if(is_null($where) && is_null($data)){
			$dbHelper = new DBHelper($this->dbid);
			return call_user_func_array(array($dbHelper, 'update'), array($table));
		}

		if(is_null($where)){
			throw new \ArgumentCountError('where不能为空！');
		}

		$sql = 'UPDATE `[T]' . $table . '` SET ' . self::arr2sql($data, 'insert') . ' WHERE ' . (is_array($where) ? self::arr2sql($where) : $where);
		
		if(!$this->execute($sql)){
			return 0;
		}

		return $this->sQuery->rowCount();
	}
	
	/**
	 * 删除符合条件的行
	 * 
	 * @param string $table 表名
	 * @param mixed  $where 条件
	 * @return result
	 */
	public function delete(string $table, $where = null){
		if(is_null($where)){
			$dbHelper = new DBHelper($this->dbid);
			return call_user_func_array(array($dbHelper, 'delete'), array($table));
		}

		$sql = 'DELETE FROM `[T]' . $table . '` WHERE ' . (is_array($where) ? self::arr2sql($where, 'where') : $where);

		if(!$this->execute($sql)){
			return 0;
		}

		return $this->sQuery->rowCount();
	}
	
	/**
	 * 插入行并返回插入的主键ID
	 * 
	 * @param string $table 表名
	 * @param mixed $data 数组
	 * @return int
	 */
	public function insert(string $table = null, $data = null){
		if(is_null($data)){
			$dbHelper = new DBHelper($this->dbid);
			return call_user_func_array(array($dbHelper, 'insert'), array($table));
		}

		$sql = 'INSERT INTO `[T]' . $table . '` SET ' . (is_array($data) ? self::arr2sql($data, 'insert') : $data);
		$this->execute($sql);

		if ($this->sQuery->rowCount() > 0){
			return $this->link->lastInsertId();
		}
		
		return null;
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
		$this->execute($sql, $args);

		return $this->sQuery->fetchColumn();
	}
	
	/**
	 * 执行SQL语句
	 * 
	 * @param string  $sql	 SQL语句
	 * @param array   $args	参数
	 * @param boolean $isThrow 出现错误是否抛出异常
	 * @return result
	 */
	public function execute($sql, $args = [], $isThrow = null){
		if(!$this->isConnect) $this->connect();
		if($isThrow === null) $isThrow = $this->isThrow;
		
		$sql = $this->handleSQL($sql);
		$startTime = PerformanceStatistics::getMicrotime();
		
		try{
			$this->sQuery = $this->link->prepare($sql);
			$isSuccess = $this->sQuery->execute($args);
		}catch(\PDOException $ex){
			if($isThrow){
				throw new DBException(1, $this, [
					'sql'		=> $sql,
					'args'		=> $args,
					'errorCode'	=> $this->sQuery->errorCode(),
					'errorInfo'	=> $this->sQuery->errorInfo()
				]);
			}
			
			return false;
		}
		
		if($this->config['isLogSQL']) $this->log[] = [
			'sql'	=> $sql,
			'time'	=> round((PerformanceStatistics::getMicrotime() - $startTime) * 1000, 2)
		];
		
		return $isSuccess;
	}
	
	/**
	 * 数组转换为sql语句
	 * 
	 * @param array  $arr 数组
	 * @param string $mode 模式(where或insert)
	 * @return string
	 */
	static public function arr2sql($arr = [], $mode = 'where'){
		$return = [];
		
		foreach ($arr as $key => $value) {
			if($key === '#'){						// KEY = '#'
				if(!is_string($value)){
					throw new DBException(2, null, ['arr' => $arr, 'key' => $key, 'value' => $value]);
				}
				
				$sql = $value;
			}elseif(substr($key, 0, 1) === '#'){		// KEY = '#abc'
				if(!self::isValue($value)){
					throw new DBException(2, null, ['arr' => $arr, 'key' => $key, 'value' => $value]);
				}
				
				$sql = '`' . self::safe(substr($key, 1)) . '` = ' . $value;
			}elseif(substr($key, 0, 1) === '@'){		// KEY = '@array'
				if(!is_array($value)){
					throw new DBException(3, null, ['arr' => $arr, 'key' => $key, 'value' => $value]);
				}
				
				if($mode == 'insert'){
					$sql = '`' . self::safe(substr($key, 1)) . '` = "' . self::safe(json_encode($value)) . '"';
				}else{
					$sql = '`' . self::safe(substr($key, 1)) . '` IN ("' . implode('", "', safe2($value, 'sql')) . '")';
				}
			}elseif(is_numeric($key)){				// KEY IS NUMBER
				if(is_array($value)){
					if(count($value) == 2){
						$sql = '`' . self::safe($value[0]) . '` = "' . self::safe($value[1]) . '"';
					}elseif(count($value) == 3){
						$sql = '`' . self::safe($value[0]) . '` ' . $value[1] . ' "' . self::safe($value[2]) . '"';
					}else{
						throw new DBException(4, null, ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
				}elseif(is_string($value)){
					$sql = $value;
				}else{
					throw new DBException(5, null, ['arr' => $arr, 'key' => $key, 'value' => $value]);
				}
			}else{									// DEFAULT
				if(!self::isValue($value)){
					throw new DBException(2, null, ['arr' => $arr, 'key' => $key, 'value' => $value]);
				}
				
				if($value === null){
					$sql = '`' . self::safe($key) . '` = NULL';
				}else{
					$sql = '`' . self::safe($key) . '` = "' . self::safe(self::toValue($value)) . '"';
				}
			}
			
			$return[] = $sql;
		}
		
		return implode([
			'insert'	=> ', ',
			'where'		=> ' AND ',
			'whereOr'	=> ' OR '
		][$mode], $return);
	}
	
	/**
	 * 获取错误信息
	 * 
	 * @return string
	 */
	public function error(){
		return '[' . $this->sQuery->errorCode() . '] ' . $this->sQuery->errorInfo();
	}

	
	/**
	 * 转义表前缀
	 * 
	 * @param mixed $val 值
	 * @return boolean
	 */
	public function handleSQL($sql){
		$sql = str_replace(['[T]', self::$prefixReplace], [$this->config['prefix'], '[T]'], $sql);
		PerformanceStatistics::$SQLCount++;
		
		return $sql;
	}

	/**
	 * 是否符合mysql插入数据值
	 * 
	 * @param mixed $val 值
	 * @return boolean
	 */
	static public function isValue($val){
		return is_string($val) || is_numeric($val) || is_null($val) || is_bool($val);
	}
	
	/**
	 * 转为mysql插入数据值
	 * 
	 * @param mixed $val 值
	 * @return string
	 */
	static public function toValue($val){
		if(is_bool($val)){
			return $val ? '1' : '0';
		}
		
		return (string)$val;
	}

	/**
	 * 转义特殊字符
	 * 
	 * @param mixed $text
	 * @return string
	 */
	public static function safe($text){
		$text = addslashes($text);
		return str_replace('[T]', self::$prefixReplace, $text);
	}

	static public function quoteName($spec){
        $spec = trim($spec);
        $seps = array(' AS ', ' ', '.');
        foreach ($seps as $sep) {
            $pos = strripos($spec, $sep);
            if ($pos) {
                return self::quoteNameWithSeparator($spec, $sep, $pos);
            }
        }
        return self::replaceName($spec);
    }
    
    static protected function quoteNameWithSeparator($spec, $sep, $pos){
        $len   = strlen($sep);
        $part1 = self::quoteName(substr($spec, 0, $pos));
        $part2 = self::replaceName(substr($spec, $pos + $len));
        return "[T]{$part1}{$sep}{$part2}";
	}
	
    static protected function replaceName($name){
        $name = trim($name);
        if ($name == '*') {
            return $name;
        }
        return '`' . $name . '`';
	}
	
    static public function fixJoinCondition($cond){
        if (!$cond) {
            return '';
        }

        $cond = self::quoteNamesIn($cond);

        if (strtoupper(substr(ltrim($cond), 0, 3)) == 'ON ') {
            return $cond;
        }

        if (strtoupper(substr(ltrim($cond), 0, 6)) == 'USING ') {
            return $cond;
        }

        return 'ON ' . $cond;
	}
	
    static public function quoteNamesIn($text){
        $list = self::getListForQuoteNamesIn($text);
        $last = count($list) - 1;
        $text = null;
        foreach ($list as $key => $val) {
            if (($key + 1) % 3) {
                $text .= self::quoteNamesInLoop($val, $key == $last);
            }
        }
        return $text;
	}
	
    static protected function getListForQuoteNamesIn($text){
        $apos = "'";
        $quot = '"';
        return preg_split(
            "/(($apos+|$quot+|\\$apos+|\\$quot+).*?\\2)/",
            $text,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
	}
	
    static protected function quoteNamesInLoop($val, $is_last){
        if ($is_last) {
            return self::replaceNamesAndAliasIn($val);
        }
        return self::replaceNamesIn($val);
	}
	
    static protected function replaceNamesAndAliasIn($val){
        $quoted = self::replaceNamesIn($val);
        $pos    = strripos($quoted, ' AS ');
        if ($pos !== false) {
            $bracket = strripos($quoted, ')');
            if ($bracket === false) {
                $alias = self::replaceName(substr($quoted, $pos + 4));
                $quoted = substr($quoted, 0, $pos) . " AS $alias";
            }
        }
        return $quoted;
	}
	
	static protected function replaceNamesIn($text){
        if (strpos($text, "'") !== false || strpos($text, '"') !== false) {
            return $text;
        }

        $word = '[a-z_][a-z0-9_]*';
        $find = "/(\\b)($word)\\.($word)(\\b)/i";
        $repl = '$1`[T]$2`.`$3`$4';

        $text = preg_replace($find, $repl, $text);

        return $text;
    }
}
