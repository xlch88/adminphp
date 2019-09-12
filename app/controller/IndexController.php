<?php
namespace App\Controller;

use AdminPHP\AdminPHP;
use AdminPHP\Cache;
use DB;

class IndexController{
	public function index(){
		view('index/index', [
			'text'	=> 'AdminPHP<br/>V2 Beta'
		]);
	}
	
	public function dbtest(){
		var_dump([\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
		
		echo '<pre>';
		echo '连接数据库...' . "\r\n";
		$db = new DB([
			/* --   数据库类型   -- */
			'type'		=> 'mysql',			//类型[mysql,sqllite]
			
			/* --     MySQL      -- */
			'ip'		=> '127.0.0.1',		//IP
			'port'		=> 3306,			//端口
			'username'	=> 'root',			//用户名
			'password'	=> 'root',			//密码
			'db'		=> 'test',			//数据库名
			
			/* --      表前缀    -- */
			'prefix'	=> 'test_',				//表前缀
		], true);
		
		print_r($db);
		
		echo '获取多行...' . "\r\n";
		print_r($db->get_rows('select * from [T]user where username like :username', [
			':username'	=> 'xlch%'
		]));
		
		echo '获取单行...' . "\r\n";
		print_r($db->get_row('select * from [T]user where username = :username', [
			':username'	=> 'xlch88'
		]));
		
		echo '修改值...' . "\r\n";
		var_dump($db->update('user', [
			'#token'	=> 'rand()'
		], [
			'username'	=> 'xlch88'
		]));
		
		echo '插入一行...' . "\r\n";
		var_dump($db->insert('insert into [T]user set username = :username, password = "123456", token = "qwq"', [
			':username'	=> 'xlch66'
		]));
		
		echo '插入一行... (数组形式)' . "\r\n";
		var_dump($db->insert_array('user', [
			'username'	=> 'xlch66',
			'password'	=> '123456',
			'#token'	=> 'rand()'
		]));
		
		print_r($db->log);
		
		echo '获取多行...' . "\r\n";
		print_r($db->get_rows('select * from [T]user'));
		
 		echo '删除...' . "\r\n";
		var_dump($db->delete('user', [
			'username'	=> 'xlch66'
		]));
		
		echo '获取数量...' . "\r\n";
		var_dump($db->count('select count(*) from [T]user where username like "xlch%"'));
		
		echo '手动fetch...' . "\r\n";
		$stmt = $db->query('select * from [T]user');
		while($row = $db->fetch($stmt, \PDO::FETCH_BOTH)){
			print_r($row);
		}
		
		echo '</pre>';
	}
	
	public function keyao(){
		view('index/yaoke');
	}
	
	public function keyao2(){
		view('index/yaoke2');
	}
	
	public function router(){
		$username = i('id', 'args', 'html');
		
		echo '<h1>Welcome ' . $username . '~!</h1>';
	}
	
	public function exception(){
		AdminPHP::$config['debug'] = false;
		
		throw new \Exception('啊，异常了！', 23333);
	}
	
	public function exception2(){
		AdminPHP::$config['debug'] = true;
		
		throw new \Exception('啊，异常了！', 23333);
	}
	
	public function exception3(){
		AdminPHP::$config['debug'] = false;
		AdminPHP::$config['adminInfo'] = [
			'自定义信息1'	=> '啦啦啦1',
			'自定义信息2'	=> '啦啦啦2',
			'自定义信息3'	=> '啦啦啦3'
		];
		
		throw new \Exception('啊，异常了！', 23333);
	}
	
	public function setCache(){
		var_dump(Cache::set('qwq', '123456', time()+233));
	}
	
	public function getCache(){
		var_dump(Cache::get('qwq'));
	}
	
	public function sysinfo(){
		$type = i('type', 'all', ['error', 'success', 'info']);
		
		sysinfo([
			'code'	=> '233',
			'type'	=> $type,
			'info'	=> '这是一个提示页面',
			'more'	=> [
				'By.Xlch88',
				'<a href="https://github.com/xlch88/adminphp" target="_blank">https://github.com/xlch88/adminphp</a>',
				'https://xlch.me/',
				'第四条提示语'
			]
		]);
	}
	
	public function notice(){
		notice('这里是提示信息', '?qwq');
	}
}