<?php
namespace AdminPHP\Module\DB;

use AdminPHP\Module\DB;
use AdminPHP\Exception\DBHelperException;

/**
 *
 */
class DBHelper{
	/**
	 * @var null
	 */
	var $dbid	= null;
	/**
	 * @var null
	 */
	var $table	= null;
	
	/**
	 * @var string
	 */
	private $sqlType	= '';
	/**
	 * @var array
	 */
	private $from		= [];
	/**
	 * @var string
	 */
	private $cols		= '';
	/**
	 * @var array
	 */
	private $where		= [];
	/**
	 * @var array
	 */
	private $having		= [];
	/**
	 * @var array
	 */
	private $orderBy	= [];
	/**
	 * @var array
	 */
	private $groupBy	= [];
	/**
	 * @var string
	 */
	private $limit		= '';
	/**
	 * @var int
	 */
	private $offset		= 0;
	/**
	 * @var array
	 */
	private $bindValues = [];
	/**
	 * @var array
	 */
	private $join		= [];
	/**
	 * @var array
	 */
	private $flags		= [];
	/**
	 * @var array
	 */
	private $for_update	= [];
	/**
	 * @var array
	 */
	private $union		= [];
	/**
	 * @var array
	 */
	private $set		= [];
	/**
	 * @var array
	 */
	private $returning	= [];
	
	/**
	 * @param $dbid
	 */
	public function __construct($dbid){
		$this->dbid		= $dbid;
	}
	
	/**
	 * @return $this
	 */
	public function select(){
		$args = func_get_args();
		
		if(count($args) == 1 && is_array($args[0])){
			$args = $args[0];
		}
		
		foreach(array_filter($args) as $index => $col){
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
	
	/**
	 * @return $this
	 */
	public function insert($table){
		$this->table = DB::quoteName('[T]' . $table);
		$this->sqlType = 'insert';

		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function delete($table){
		$this->from[] = DB::quoteName('[T]' . $table);
		$this->sqlType = 'delete';

		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function update($table){
		$this->table = DB::quoteName('[T]' . $table);
		$this->sqlType = 'update';

		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function set(){
		$args = func_get_args();

		$this->set = $this->buildCols($args, 'insert');

		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function from(){
		$args = func_get_args();
		
		foreach($args as $table){
			$this->from[] =  DB::quoteName('[T]' . $table);
		}
		
		return $this;
	}
	
	/**
	 * @param string $table
	 * @param string $on
	 * @return $this
	 */
	public function innerJoin(string $table, string $on){
		return $this->join($table, $on, 'inner');
	}
	
	/**
	 * @param string $table
	 * @param string $on
	 * @return $this
	 */
	public function leftJoin(string $table, string $on){
		return $this->join($table, $on, 'left');
	}
	
	/**
	 * @param string $table
	 * @param string $on
	 * @return $this
	 */
	public function rightJoin(string $table, string $on){
		return $this->join($table, $on, 'right');
	}
	
	/**
	 * @param string $table
	 * @param string $on
	 * @param string $type
	 * @return $this
	 */
	public function join(string $table, string $on, $type = ''){
		$this->join[] = trim(strtoupper($type) . ' JOIN ' . DB::quoteName('[T]' . $table) . ' ' . DB::fixJoinCondition($on));
		
		return $this;
	}
	
	/**
	 * @return $this
	 * @throws DBHelperException
	 */
	public function where(){
		if(!count(func_get_args()) || !func_get_args()[0]) return $this;
		
		$this->where[] = $this->buildCols(func_get_args(), 'where');

		return $this;
	}
	
	/**
	 * @return $this
	 * @throws DBHelperException
	 */
	public function whereOr(){
		if(!count(func_get_args()) || !func_get_args()[0]) return $this;
		
		$this->where[] = $this->buildCols(func_get_args(), 'whereOr');
		
		return $this;
	}
	
	/**
	 * @return $this
	 * @throws \AdminPHP\Exception\DBException
	 */
	public function having(){
		if(!count(func_get_args()) || !func_get_args()[0]) return $this;
		
		$this->having[] = $this->buildCols(func_get_args(), 'where');

		return $this;
	}
	
	/**
	 * @return $this
	 * @throws DBHelperException
	 */
	public function havingOr(){
		if(!count(func_get_args()) || !func_get_args()[0]) return $this;
		
		$this->having[] = $this->buildCols(func_get_args(), 'whereOr');
		
		return $this;
	}
	
	/**
	 * @return $this
	 * @throws DBHelperException
	 */
	public function orderBy(){
		$this->buildBy(func_get_args(), 'orderBy');
		return $this;
	}
	
	/**
	 * @return $this
	 * @throws DBHelperException
	 */
	public function groupBy(){
		$this->buildBy(func_get_args(), 'groupBy');
		return $this;
	}
	
	/**
	 * @param $var1
	 * @param null $var2
	 * @return $this
	 */
	public function limit($var1, $var2 = null){
		if(is_null($var2)){
			$this->limit = (int)$var1;
		}else{
			$this->limit = (int)$var1 . ', ' . (int)$var2;
		}
		
		return $this;
	}
	
	/**
	 * @param $val
	 * @return $this
	 */
	public function offset($val){
		$this->offset = (int)$val;
		
		return $this;
	}
	
	/**
	 * @param $args
	 * @param $mode
	 * @return string
	 * @throws DBHelperException
	 * @throws \AdminPHP\Exception\DBException
	 */
	public function buildCols($args, $mode){
		$return = [];
		
		if(count($args) == 1 && is_string($args[0])){
			//->where('a = "b"')
			$return[] = $args[0];
		}elseif(count($args) == 3 && is_string($args[0]) && is_string($args[1]) && DB::isValue($args[2])){
			//->where('a', '=', 'b')
			$return[] = DB::arr2sql([ [$args[0], $args[1], $args[2]] ], $mode);
		}elseif(count($args) == 2 && is_string($args[1]) && DB::isValue($args[1])){
			//->where('a', 'b')
			$return[] = DB::arr2sql([$args[0] => $args[1]], $mode);
		}elseif(count($args) == 1 && is_array($args[0])){
			//->where(['a' => 'b', ['a', '>', 'b']])
			$return[] = DB::arr2sql($args[0], $mode);
		}elseif(count($args) == 2 && is_string($args[0]) && is_array($args[1])){
			//->where('a = :b', [':b' => "awa"])
			$return[] = $args[0];
			$this->bindValues = array_merge($this->bindValues, $args[1]);
		}elseif($args == array_filter($args, function($v, $k){ return is_array($v); }, ARRAY_FILTER_USE_BOTH)){
			//->where(['a', 'b'], ['a', '<', '1'])
			$return[] = DB::arr2sql($args, $mode);
		}else{
			throw new DBHelperException(0, $args);
		}
		
		if($mode == 'insert'){
			return implode(', ', $return);
		}else{
			return '(' . implode($mode = 'where' ? ' AND ' : ' OR ', $return) . ')';
		}
	}
	
	/**
	 * @param $args
	 * @param $type
	 * @throws DBHelperException
	 */
	public function buildBy($args, $type){
		if(count($args) == 1 && is_array($args[0])){
			$args = $args[0];
		}
		
		$result = [];
		foreach($args as $index => $value){
			if(!$args) continue;
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
	
	/**
	 * @param $type
	 * @return string|void|null
	 * @throws \AdminPHP\Exception\DBException
	 */
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
	
	/**
	 * @return string
	 * @throws \AdminPHP\Exception\DBException
	 */
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
	
	/**
	 * @return false|mixed
	 * @throws DBHelperException
	 */
	public function row(){
		if($this->sqlType != 'select'){
			throw new DBHelperException(2);
		}
		
		$this->limit(1);
		
		if($result = $this->query()){
			if(count($result[0]) === 1){
				return $result[0][array_keys($result[0])[0]];
			}else{
				return $result[0];
			}
		}else{
			return false;
		}
	}
	
	/**
	 * @return int|void|null
	 * @throws DBHelperException
	 */
	public function rows(){
		if($this->sqlType != 'select'){
			throw new DBHelperException(2);
		}
		
		return $this->query();
	}
	
	/**
	 * @param null $col
	 * @return array
	 * @throws DBHelperException
	 */
	public function cols($col = null){
		if($this->sqlType != 'select'){
			throw new DBHelperException(2);
		}
		
		if($result = $this->query()){
			if($col === null){
				$col = array_keys($result[0])[0];
			}
			
			return array_column($result, $col);
		}else{
			return [];
		}
	}
	
	/**
	 * @return int|void|null
	 * @throws \AdminPHP\Exception\DBException
	 */
	public function query(){
		$sql = $this->buildSQL();
		$db = db($this->dbid);
		
		switch($this->sqlType){
			case 'select':
				$db->execute($sql, $this->bindValues);
				return $db->sQuery->fetchAll(\PDO::FETCH_ASSOC);

			case 'insert':
				$db->execute($sql);

				if ($db->sQuery->rowCount() > 0){
					return $db->link->lastInsertId();
				}
				
				return null;

			case 'update':
			case 'delete':
				if(!$db->execute($sql)){
					return 0;
				}

				return $db->sQuery->rowCount();
		}
	}
	
	/**
	 * @param $text
	 * @return string
	 */
	public function safe($text){
		return addslashes($text);
	}
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function calcFoundRows($enable = true)	{ $this->setFlag('SQL_CALC_FOUND_ROWS', $enable);	return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function cache($enable = true)			{ $this->setFlag('SQL_CACHE', $enable);				return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function noCache($enable = true)			{ $this->setFlag('SQL_NO_CACHE', $enable);			return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function straightJoin($enable = true)	{ $this->setFlag('STRAIGHT_JOIN', $enable);			return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function highPriority($enable = true)	{ $this->setFlag('HIGH_PRIORITY', $enable);			return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function smallResult($enable = true)		{ $this->setFlag('SQL_SMALL_RESULT', $enable);		return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function bigResult($enable = true)	 	{ $this->setFlag('SQL_BIG_RESULT', $enable);		return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function bufferResult($enable = true)	{ $this->setFlag('SQL_BUFFER_RESULT', $enable);		return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function forUpdate($enable = true)		{ $this->for_update = $enable;						return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function distinct($enable = true)		{ $this->setFlag('DISTINCT', $enable);				return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function lowPriority($enable = true)		{ $this->setFlag('LOW_PRIORITY', $enable);			return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function ignore($enable = true)			{ $this->setFlag('IGNORE', $enable);				return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function quick($enable = true)			{ $this->setFlag('QUICK', $enable);					return $this; }
	
	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function delayed($enable = true)			{ $this->setFlag('DELAYED', $enable);				return $this; }
	
	/**
	 * @param $flag
	 * @param bool $enable
	 */
	public function setFlag($flag, $enable = true){
        if ($enable) {
            $this->flags[$flag] = true;
        } else {
            unset($this->flags[$flag]);
        }
    }
}
