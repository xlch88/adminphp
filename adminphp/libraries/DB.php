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
	
	var $dbConfig = [
		/* -- 数据库连接信息 -- */
		'ip'		=> '127.0.0.1',		//IP
		'port'		=> 3306,			//端口
		'username'	=> 'root',			//用户名
		'password'	=> 'root',			//密码
		'db'		=> 'test',			//数据库名
		
		/* --      选项      -- */
		'isLogSQL'	=> true,			//是否记录已查询SQL
		'charset'	=> 'utf8',			//编码
		
		/* --      表前缀    -- */
		'prefix'	=> '',				//表前缀
	];
	
	/**
	 * 初始化
	 * 
	 * @param array $dbConfig 数据库连接配置
	 * @param boolean $isThrow 连接失败是否抛出异常
	 * @return array
	 */
    public function __construct($dbConfig, $isThrow = true){
		$this->dbConfig = array_merge($this->dbConfig, $dbConfig);
		
        $this->link = @mysqli_connect($this->dbConfig['ip'], $this->dbConfig['user'], $this->dbConfig['pass'], $this->dbConfig['db'], $this->dbConfig['port']);
        if (!$this->link) {
			if(!$isThrow){
				return false;
			}
			
			throw new DBException(0, $this);
        }
		
		$this->isLogSQL = $this->dbConfig['isLogSQL'];
		
        $this->query('set sql_mode = ""');
        $this->query('set character set "' . $this->dbConfig['charset'] . '"');
        $this->query('set names "' . $this->dbConfig['charset'] . '"');
		
        return true;
    }
	
	/**
	 * 获取查询结果(全部)
	 * 
	 * @param string $sql SQL语句
	 * @return array
	 */
    public function get_rows($sql){
        $rows = [];
        $result = $this->query($sql);
        if ($result) {
            while ($row = $this->fetch($result)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
	
	/**
	 * 获取查询结果(一行)
	 * 
	 * @param string $sql SQL语句
	 * @return array
	 */
    public function get_row($sql){
        $result = $this->query($sql);
		
        return $result ? $this->fetch($result) : false;
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
		
        return $this->query($sql);
    }
	
	/**
	 * 删除符合条件的行
	 * 
	 * @param string $table 表名
	 * @param mixed  $where 条件
	 * @return result
	 */
	public function delete(string $table, $where){
		$where_ = [];
		
		$sql = 'DELETE FROM `[T]' . $table . '` WHERE ' . (is_array($where) ? $this->arr2sql($where, 'where') : $where);
		
        return $this->query($sql);
	}
	
	/**
	 * 插入行并返回插入的主键ID
	 * 
	 * @param string $sql SQL语句
	 * @return int
	 */
    public function insert($q){
        if ($this->query($q)) {
            return mysqli_insert_id($this->link);
        }
        return false;
    }
	
	/**
	 * 以数组形式插入行并返回插入的主键ID
	 * 
	 * @param string $table 表名
	 * @param array $array 数组
	 * @return int
	 */
    public function insert_array($table, $array){
		$table = '[T]' . $table;
		
        $q = "INSERT INTO `{$table}`";
        $q .= " (`" . implode("`,`", safe2(array_keys($array), 'sql')) . "`) ";
        $q .= " VALUES ('" . implode("','", safe2(array_values($array), 'sql')) . "') ";
        if ($this->query($q)) {
            return mysqli_insert_id($this->link);
        }
        return false;
    }
	
	/**
	 * 获取COUNT查询数量
	 * 
	 * @param string $sql SQL语句
	 * @return int
	 */
    public function count($sql){
        $result = $this->query($sql);
        $count = mysqli_fetch_array($result);
        return $count[0];
    }
	
	/**
	 * 查询SQL语句
	 * 
	 * @param string $sql SQL语句
	 * @param boolean $isThrow 出现错误是否抛出异常
	 * @return result
	 */
    public function query($sql, $isThrow = true){
		$sql = str_replace(['[T]', self::$prefixReplace], [$this->dbConfig['prefix'], '[T]'], $sql);
		
		PerformanceStatistics::$SQLCount++;
		if($this->dbConfig['isLogSQL']) $this->log[] = $sql;
		
		$data = mysqli_query($this->link, $sql);
		
		if($data === FALSE){
			if(!$isThrow) return false;
			
			throw new DBException(1, $this, $sql);
		}
		
        return $data;
    }
	
	/**
	 * 获取一条数据
	 * 
	 * @param resource $result 查询返回结果
	 * @return array
	 */
    public function fetch($q){
        return mysqli_fetch_assoc($q);
    }
	
	/**
	 * 返回上一句SQL影响的行数
	 * 
	 * @return int
	 */
    public function affected(){
        return mysqli_affected_rows($this->link);
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
						throw new DBException(2, $this, '', ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = $value;
				}elseif(substr($key, 0, 1) == '#'){
					if(!is_string($value) && !is_numeric($value) && !is_bool($value)){
						throw new DBException(2, $this, '', ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes(substr($key, 1)) . '` = ' . $value;
				}elseif(substr($key, 0, 1) == '@'){
					if(!is_array($value)){
						throw new DBException(3, $this, '', ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes(substr($key, 1)) . '` = "' . addslashes(json_encode($value)) . '"';
				}else{
					if(!is_string($value) && !is_numeric($value) && !is_bool($value)){
						throw new DBException(2, $this, '', ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes($key) . '` = "' . addslashes($value) . '"';
				}
			}else{
				if($key == '#'){
					if(!is_string($value)){
						throw new DBException(2, $this, '', ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = $value;
				}elseif(substr($key, 0, 1) == '#'){
					if(!is_string($value) && !is_numeric($value) && !is_bool($value)){
						throw new DBException(2, $this, '', ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes(substr($key, 1)) . '` = ' . $value;
				}elseif(substr($key, 0, 1) == '@'){
					if(!is_array($value)){
						throw new DBException(3, $this, '', ['arr' => $arr, 'key' => $key, 'value' => $value]);
					}
					$sql = '`' . addslashes(substr($key, 1)) . '` IN ("' . implode('", "', safe2($value, 'sql')) . '")';
				}else{
					if(!is_string($value) && !is_numeric($value) && !is_bool($value)){
						throw new DBException(2, $this, '', ['arr' => $arr, 'key' => $key, 'value' => $value]);
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
	 * @return string
	 */
    public function error(){
        $error = mysqli_error($this->link);
        $errno = mysqli_errno($this->link);
        return '[' . $errno . '] ' . $error;
    }
	
	/**
	 * 关闭数据库连接
	 * 
	 * @return boolean
	 */
    public function close(){
        return mysqli_close($this->link);
    }
}