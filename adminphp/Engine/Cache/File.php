<?php
/* ----------------------------------------------- *
 | [ AdminPHP ] Version : 2.0 beta
 | 简单粗暴又不失高雅的迫真 OOP MVC 框架，，，
 |
 | URL     : https://www.adminphp.net/
 * ----------------------------------------------- *
 | Name    : CacheEngine/File (缓存引擎 - 文件)
 |
 | Author  : Xlch88 (i@xlch.me)
 | LICENSE : WTFPL http://www.wtfpl.net/about
 * ----------------------------------------------- */

namespace AdminPHP\Engine\Cache;

use InvalidArgumentException;

class File {
	private $path = '';
	private $subfix = '';

	public function __construct($config){
		$config = array_merge([
			'path'			=> root . 'runtime' . DIRECTORY_SEPARATOR . 'cache',
			'file_subfix'	=> '.cache.php'
		], $config);

		if(!realpath($config['path']) || !file_exists($config['path'])){
			if(!mkdir($config['path'], 0777, true)){
				throw new InvalidArgumentException(l('路径是无效的，且无法被创建！'));
			}

			file_put_contents(realpath($config['path']) . '/.gitignore', '*');
		}

		$this->path = realpath($config['path']) . DIRECTORY_SEPARATOR;
		$this->subfix = $config['file_subfix'];
	}

	public function get($key, $default = null){
		$file = $this->path . $key . $this->subfix;

		if(!is_file($file)){
			return $default;
		}

		$data = file_get_contents($file);
		$data = substr($data, 15, strlen($data) - 15);

		$data = json_decode($data, true);

		if(!$data || !isset($data['expiry']) || !isset($data['data']) || ($data['expiry'] != false && $data['expiry'] < time())){
			return $default;
		}

		return unserialize($data['data']);
	}

	public function set($key, $value, $expiry = false){
		$data = '<?php die(); ?>' . json_encode([
			'data'		=> serialize($value),
			'expiry'	=> $expiry ? time() + $expiry : false
		]);

		$file = $this->path . $key . $this->subfix;
		return !(file_put_contents($file, $data) === FALSE);
	}

	public function delete($key){
		$file = $this->path . $key . $this->subfix;
		return unlink($file);
	}
}
