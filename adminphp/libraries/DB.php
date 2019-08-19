<?php
use AdminPHP\PerformanceStatistics;
use AdminPHP\Exception\DBException;

class DB {
    var $link = null;
	var $isLogSQL = true;
	var $log = [];
	var $dbConfig = [];
	
    public function __construct($dbConfig, $isThrow = true){
		$this->dbConfig = $dbConfig;
		
        $this->link = mysqli_connect($this->dbConfig['ip'], $this->dbConfig['user'], $this->dbConfig['pass'], $this->dbConfig['db'], $this->dbConfig['port']);
        if (!$this->link) {
			if(!$isThrow){
				return false;
			}
			
			throw new DBException(0, $this);
        }
		$this->dbConfig['isLogSQL'] = isset($this->dbConfig['isLogSQL']) && $this->dbConfig['isLogSQL'];
		$this->dbConfig['charset']	= isset($this->dbConfig['charset']) ? $this->dbConfig['charset'] : 'utf8';
		
		$this->isLogSQL = $this->dbConfig['isLogSQL'];
		
        $this->query('set sql_mode = ""');
        $this->query('set character set "' . $this->dbConfig['charset'] . '"');
        $this->query('set names "' . $this->dbConfig['charset'] . '"');
		
        return true;
    }
	
    public function fetch($q){
        return mysqli_fetch_assoc($q);
    }
	
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
	
    public function arr2sql($arr = [], $mode = 'where'){
        $return = [];
        foreach ($arr as $key => $value) {
			$sql = '`' . addslashes($key) . '`';
			
			if($mode == 'insert'){
				if(!is_array($value) && substr($value, 0, 1) == '@'){
					$sql.= ' = ' . substr($value, 1);
				}elseif($key == '@'){
					$sql = $value;
				}else{
					$sql.= ' = "' . addslashes(is_array($value) ? json_encode($value) : $value) . '"';
				}
			}else{
				if(is_array($value)){
					$sql.= 'IN ("' . implode('", "', $value) . '")';
				}elseif(substr($value, 0, 1) == '@'){
					$sql.= ' = ' . substr($value, 1);
				}elseif($key == '@'){
					$sql = $value;
				}else{
					$sql.= ' = "' . $value . '"';
				}
			}
			
            $return[] = $sql;
        }
        return implode(($mode == 'insert' ? ', ' : ' AND '), $return);
    }
	
    public function update(string $table, array $data, $where){
        $sql = 'UPDATE `' . addslashes($table) . '` SET ' . $this->arr2sql($data, 'insert') . ' WHERE ' . (is_array($where) ? $this->arr2sql($where) : $where);
		
        return $this->query($sql);
    }
	
    public function get_row($q){
        $result = $this->query($q);
        return $result ? mysqli_fetch_assoc($result) : false;
    }
	
    public function count($q){
        $result = $this->query($q);
        $count = mysqli_fetch_array($result);
        return $count[0];
    }
	
    public function query($q, $isThrow = true){
		PerformanceStatistics::$SQLCount++;
		if($this->isLogSQL) $this->log[] = $q;
		
		$data = mysqli_query($this->link, $q);
		
		if($data === FALSE){
			if(!$isThrow) return false;
			
			throw new DBException(1, $this, $q);
		}
		
        return $data;
    }
	
    public function escape($str){
        return mysqli_real_escape_string($this->link, $str);
    }
	
	public function delete(string $table, $where){
		$where_ = [];
		
		$sql = 'DELETE FROM `' . $table . '` WHERE ' . (is_array($where) ? $this->arr2sql($where, 'where') : $where);
		
        return $this->query($sql);
	}
	
    public function insert($q){
        if ($this->query($q)) {
            return mysqli_insert_id($this->link);
        }
        return false;
    }
	
    public function affected(){
        return mysqli_affected_rows($this->link);
    }
	
    public function insert_array($table, $array){
        $q = "INSERT INTO `{$table}`";
        $q .= " (`" . implode("`,`", daddslashes(array_keys($array))) . "`) ";
        $q .= " VALUES ('" . implode("','", daddslashes(array_values($array))) . "') ";
        if ($this->query($q)) {
            return mysqli_insert_id($this->link);
        }
        return false;
    }
	
    public function error(){
        $error = mysqli_error($this->link);
        $errno = mysqli_errno($this->link);
        return '[' . $errno . '] ' . $error;
    }
	
    public function close(){
        return mysqli_close($this->link);
    }
}