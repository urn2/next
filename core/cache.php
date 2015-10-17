<?php

class cache implements ArrayAccess{
	public $ns = '';
	public $cache = [];
	static public function factory($ns, $set = []){
		return new self($ns, $set);
	}
	function __construct($ns, $set = []){
		$this->ns = $ns.'.';
		$this->init($set);
	}
	function init($set = []){ }
	public function set($key, $var, $expire = null, $flag = null){
		$this->cache[$key] = $var;
		return true;
	}
	public function get($key, $flags = null){
		if(!isset($this->cache[$key])) return false;
		return $this->cache[$key];
	}
	public function delete($key, $timeout = 0){
		unset($this->cache[$key]);
		return true;
	}
	public function flush(){
		$this->cache = [];
		return true;
	}
	public function offsetExists($offset){
		return $this->get($offset) !== false;
	}
	public function offsetGet($offset){
		return $this->get($offset);
	}
	public function offsetSet($offset, $value){
		$this->set($offset, $value);
	}
	public function offsetUnset($offset){
		$this->delete($offset);
	}
}

class cache_file extends cache{
	private $file = './';
	function init($set = []){
		$this->file = next::$work.$set[0].$this->ns.'cache'.$set[1];
		$cache = [];
		//if(is_file($this->file))
		@include $this->file;
		$this->cache = $cache;
	}
	public function __destruct(){
		$r = var_export($this->cache, true);
		file_put_contents($this->file, "<?PHP\n \$cache = {$r};");
	}
}

class cache_files extends cache{
	private $path = './';
	private $ext = '.json';
	function init($set = []){
		$this->path = next::$work.$set[0];
		$this->ext = $set[1];
	}
	public function set($key, $var, $expire = null, $flag = null){
		$value = json_encode($var, JSON_UNESCAPED_UNICODE);
		return file_put_contents($this->path.$this->ns.$key.$this->ext, $value);
	}
	public function get($key, $flags = null){
		$_file = $this->path.$this->ns.$key.$this->ext;
		if(!is_file($_file)) return null;
		$value = file_get_contents($_file);
		return ($flags) ?$value :json_decode($value, true);
	}
	public function delete($key, $timeout = 0){
		return unlink($this->path.$this->ns.$key.$this->ext);
	}
	public function flush(){
		$_handle = opendir($this->path);
		while(false !== ($_file = readdir($_handle))){
			if($_file == '.' || $_file == '..') continue;
			if(strpos($this->ns, $_file) === 0) unlink($_file);
		}
		closedir($_handle);
		return true;
	}
}

class cache_memcache extends cache{
	/**
	 * @var Memcache
	 */
	private $mem = null;
	function init($set = []){
		if(is_null($this->mem)){
			$this->mem = new Memcache();
			$this->mem->connect($set[0], $set[1]);
		}
		return $this->mem;
	}
	public function set($key, $var, $expire = null, $flag = null){
		$value = json_encode($var, JSON_UNESCAPED_UNICODE);
		return $this->mem->set($this->ns.$key, $value, $flag, $expire);
	}
	public function get($key, $flags = null){
		$def =null;
		$value = $this->mem->get($this->ns.$key, $def);
		return ($flags) ?$value :json_decode($value, true);
	}
	public function delete($key, $timeout = 0){
		return $this->mem->delete($key, $timeout);
	}
	public function flush(){
		return $this->mem->flush();
	}
}

class cache_redis extends cache{
	/**
	 * @var Redis
	 */
	private $rds = null;
	function init($set = []){
		if(is_null($this->rds)){
			$this->rds = new Redis();
			$this->rds->connect($set[0], $set[1]);
		}
		return $this->rds;
	}
	public function set($key, $var, $expire = null, $flag = null){
		$value = is_string($var) ?$var :json_encode($var, JSON_UNESCAPED_UNICODE);
		return ($expire > 0) ?$this->rds->setex($this->ns.$key, $expire, $value) :$this->rds->set($this->ns.$key, $value);
	}
	public function get($key, $flags = null){
		if(is_array($key)){
			$func = 'mGet';
			$flags = true;
		}else $func = 'get';
		$value = $this->rds->{$func}($this->ns.$key);
		return ($flags) ?$value :json_decode($value, true);
	}
	public function delete($key, $timeout = 0){
		return $this->rds->delete($this->ns.$key);
	}
	public function flush(){
		return $this->rds->flushDB();
	}
	public function offsetExists($offset){
		return $this->rds->exists($this->ns.$offset) !== false;
	}
}

