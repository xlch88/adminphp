<?php
namespace AdminPHP\Module\DB;

use AdminPHP\Module\DB;
use AdminPHP\Exception\DBHelperException;

class DBHelper{
	var $dbid	= null;
	var $table	= null;
	
	private $sqlType	= '';
	private $from		= [];
	private $cols		= '';
	private $where		= [];
	private $having		= [];
	private $orderBy	= [];
	private $groupBy	= [];
	private $limit		= '';
	private $offset		= 0;
	private $bindValues = [];
	private $join		= [];
	private $flags		= [];
	private $for_update	= [];
	private $union		= [];
	private $set		= [];
	private $returning	= [];
	
	public function __construct($dbid){
		$this->dbid		= $dbid;
	}
	
	public function select(){
		$args = func_get_args();
		
		if(count($args) == 1 && is_array($args[0])){
			$args = $args[0];
		}
		
		foreach($args as $index => $col){
			$col = trim($col);
			if(strpos($col, '(') === FALSE && strpos($col, ')') === FALSE && strpos($col, ',') === FALSE){
				$col = '[T]' . $col;
			}
			$args[$index] = $col;
		}
		
		$this->cols = implode(', ', $args);
		$this->sqlType = 'select';
		
		return $this;
	}

	public function insert($table){
		$this->table = DB::quoteName('[T]' . $table);
		$this->sqlType = 'insert';

		return $this;
	}

	public function delete($table){
		$this->from[] = DB::quoteName('[T]' . $table);
		$this->sqlType = 'delete';

		return $this;
	}

	public function update($table){
		$this->table = DB::quoteName('[T]' . $table);
		$this->sqlType = 'update';

		return $this;
	}

	public function set(){
		$args = func_get_args();

		$this->set = $this->buildCols($args, 'insert');

		return $this;
	}
	
	public function from(){
		$args = func_get_args();
		
		foreach($args as $table){
			$this->from[] =  DB::quoteName('[T]' . $table);
		}
		
		return $this;
	}
	
	public function innerJoin(string $table, string $on){
		return $this->join($table, $on, 'inner');
	}
	
	public function leftJoin(string $table, string $on){
		return $this->join($table, $on, 'left');
	}
	
	public function rightJoin(string $table, string $on){
		return $this->join($table, $on, 'right');
	}
	
	public function join(string $table, string $on, $type = ''){
		$this->join[] = trim(strtoupper($type) . ' JOIN ' . DB::quoteName('[T]' . $table) . ' ' . DB::fixJoinCondition($on));
		
		return $this;
	}
	
	public function where(){
		$this->where[] = $this->buildCols(func_get_args(), 'where');

		return $this;
	}

	public function whereOr(){
		$this->where[] = $this->buildCols(func_get_args(), 'whereOr');
		
		return $this;
	}
	
	public function having(){
		$this->having[] = $this->buildCols(func_get_args(), 'where');

		return $this;
	}

	public function havingOr(){
		$this->having[] = $this->buildCols(func_get_args(), 'whereOr');
		
		return $this;
	}
	
	public function orderBy(){
		$this->buildBy(func_get_args(), 'orderBy');
		return $this;
	}

	public function groupBy(){
		$this->buildBy(func_get_args(), 'groupBy');
		return $this;
	}
	
	public function limit($var1, $var2 = null){
		if(is_null($var2)){
			$this->limit = (int)$var1;
		}else{
			$this->limit = (int)$var1 . ', ' . (int)$var2;
		}
		
		return $this;
	}
	
	public function offset($val){
		$this->offset = (int)$val;
		
		return $this;
	}
	
	public function buildCols($args, $mode){
		$return = [];

		if(count($args) == 1 && is_string($args[0])){
			//->where('a = "b"')
			$return[] = $args[0];
		}elseif(count($args) == 3 && is_string($args[0]) && is_string($args[1]) && DB::isValue($args[2])){
			//->where('a', '=', 'b')
			$sql = DB::quoteName(trim(DB::safe($args[0])));
			$sql.= ' ' . trim($args[1]) . ' ';
			$sql.= '"' . DB::safe(DB::toValue($args[2])) . '"';
			$return[] = $sql;
		}elseif(count($args) == 2 && is_string($args[1]) && DB::isValue($args[1])){
			//->where('a', 'b')
			$sql = DB::quoteName(trim(DB::safe($args[0])));
			$sql.= ' = ';
			$sql.= '"' . DB::safe(DB::toValue($args[1])) . '"';
			$return[] = $sql;
		}elseif(count($args) == 1 && is_array($args[0])){
			//->where(['a' => 'b', ['a', '>', 'b']])
			$sql = DB::arr2sql($args[0], $mode);
			$return[] = $sql;
		}elseif(count($args) == 2 && is_string($args[0]) && is_array($args[1])){
			//->where('a = :b', [':b' => "awa"])
			$return[] = $args[0];
			$this->bindValues = array_merge($this->bindValues, $args[1]);
		}elseif($args == array_filter($args, function($v, $k){ return is_array($v); }, ARRAY_FILTER_USE_BOTH)){
			//->where(['a', 'b'], ['a', '<', '1'])
			var_dump($args);
			$sql = DB::arr2sql($args, $mode);
			$return[] = $sql;
		}else{
			throw new DBHelperException(0, $args);
		}
		
		if($mode == 'insert'){
			return implode(', ', $return);
		}else{
			return '(' . implode($mode = 'where' ? ' AND ' : ' OR ', $return) . ')';
		}
	}

	public function buildBy($args, $type){
		if(count($args) == 1 && is_array($args[0])){
			$args = $args[0];
		}
		
		$result = [];
		foreach($args as $index => $value){
			if(!is_numeric($index)){
				$result[] = DB::quoteNamesIn($index . ' ' . strtoupper($value));
			}elseif(is_string($value)){
				$result[] = DB::quoteNamesIn($value);
			}else{
				throw new DBHelperException(1, $args, $index, $value);
			}
		}
		
		$this->{$type} = array_unique(array_merge($this->{$type}, $result));
	}
	public function build($type){
		switch($type){
			case 'cols':
				return $this->cols;
			break;
			
			case 'flags':
				if (!$this->flags) {
					return '';
				}
				return implode(' ', array_keys($this->flags));
			break;
			
			case 'from':
				return 'FROM ' . implode(', ', $this->from);
			break;
			
			case 'where':
				return $this->where ? 'WHERE ' . implode(' AND ', $this->where) : '';
			break;

			case 'having':
				return $this->having ? 'HAVING ' . implode(' AND ', $this->having) : '';
			break;
			
			case 'orderBy':
				return $this->orderBy ? 'ORDER BY ' . implode(', ', $this->orderBy) : '';
			break;

			case 'groupBy':
				return $this->groupBy ? 'GROUP BY ' . implode(', ', $this->groupBy) : '';
			break;
			
			case 'limit':
				return $this->limit ? 'LIMIT ' . $this->limit : '';
			break;
			
			case 'offset':
				return $this->offset ? 'OFFSET ' . $this->offset : '';
			break;
			
			case 'join':
				return $this->join ? implode(' ', $this->join) : '';
			break;
			
			case 'for_update':
				if (!$this->for_update) {
					return '';
				}
				return 'FOR UPDATE';
			break;
			
			case 'into':
				return 'INTO ' . $this->table;
			break;

			case 'table':
				return $this->table;
			break;

			case 'set':
				return 'SET ' . (is_array($this->set) ? DB::arr2sql($this->set, 'insert') : $this->set);
			break;
		}
	}
	
	public function buildSQL(){
		$return = [];
		
		$return[] = strtoupper($this->sqlType);
		switch($this->sqlType){
			case 'select':
				$return[] = $this->build('flags');
				$return[] = $this->build('cols');
				$return[] = $this->build('from');
				$return[] = $this->build('join');
				$return[] = $this->build('where');
				$return[] = $this->build('groupBy');
				$return[] = $this->build('having');
				$return[] = $this->build('orderBy');
				$return[] = $this->build('limit');
				$return[] = $this->build('offset');
				$return[] = $this->build('for_update');
			break;

			case 'insert':
				$return[] = $this->build('into');
				$return[] = $this->build('set');
			break;

			case 'delete':
				$return[] = $this->build('flags');
				$return[] = $this->build('from');
				$return[] = $this->build('where');
				$return[] = $this->build('orderBy');
				$return[] = $this->build('limit');
				$return[] = $this->build('offset');
			break;

			case 'update':
				$return[] = $this->build('flags');
				$return[] = $this->build('table');
				$return[] = $this->build('set');
				$return[] = $this->build('where');
				$return[] = $this->build('orderBy');
				$return[] = $this->build('limit');
				$return[] = $this->build('offset');
			break;
		}
		
		return implode(' ', array_filter($return));
	}
	
	public function row(){
		if($this->sqlType != 'select'){
			throw new DBHelperException(2);
		}
		
		$this->limit(1);
		
		if($result = $this->query()){
			return $result[0];
		}else{
			return false;
		}
	}
	
	public function rows(){
		if($this->sqlType != 'select'){
			throw new DBHelperException(2);
		}
		
		return $this->query();
	}
	
	public function query(){
		$sql = $this->buildSQL();
		$db = db($this->dbid);
		
		switch($this->sqlType){
			case 'select':
				$db->execute($sql, $this->bindValues);
				return $db->sQuery->fetchAll(\PDO::FETCH_ASSOC);
			break;

			case 'insert':
				$db->execute($sql);

				if ($db->sQuery->rowCount() > 0){
					return $db->link->lastInsertId();
				}
				
				return null;
			break;

			case 'delete':
				if(!$db->execute($sql)){
					return 0;
				}

				return $db->sQuery->rowCount();
			break;

			case 'update':
				if(!$db->execute($sql)){
					return 0;
				}

				return $db->sQuery->rowCount();
			break;
		}
	}
	
	public function safe($text){
		return addslashes($text);
	}

	public function calcFoundRows($enable = true)	{ $this->setFlag('SQL_CALC_FOUND_ROWS', $enable);	return $this; }
	public function cache($enable = true)			{ $this->setFlag('SQL_CACHE', $enable);				return $this; }
	public function noCache($enable = true)			{ $this->setFlag('SQL_NO_CACHE', $enable);			return $this; }
	public function straightJoin($enable = true)	{ $this->setFlag('STRAIGHT_JOIN', $enable);			return $this; }
	public function highPriority($enable = true)	{ $this->setFlag('HIGH_PRIORITY', $enable);			return $this; }
	public function smallResult($enable = true)		{ $this->setFlag('SQL_SMALL_RESULT', $enable);		return $this; }
	public function bigResult($enable = true)	 	{ $this->setFlag('SQL_BIG_RESULT', $enable);		return $this; }
	public function bufferResult($enable = true)	{ $this->setFlag('SQL_BUFFER_RESULT', $enable);		return $this; }
	public function forUpdate($enable = true)		{ $this->for_update = $enable;						return $this; }
	public function distinct($enable = true)		{ $this->setFlag('DISTINCT', $enable);				return $this; }
	public function lowPriority($enable = true)		{ $this->setFlag('LOW_PRIORITY', $enable);			return $this; }
    public function ignore($enable = true)			{ $this->setFlag('IGNORE', $enable);				return $this; }
    public function quick($enable = true)			{ $this->setFlag('QUICK', $enable);					return $this; }
    public function delayed($enable = true)			{ $this->setFlag('DELAYED', $enable);				return $this; }

	public function setFlag($flag, $enable = true){
        if ($enable) {
            $this->flags[$flag] = true;
        } else {
            unset($this->flags[$flag]);
        }
    }
}