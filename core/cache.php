<?php

class Cache{
	protected $prefix ='next.';
	protected $cache =array();
	public function __construct($ID_Prefix ='next'){
		$this->prefix =$ID_Prefix . '.';
	}
	public function __destruct(){
		if (strpos(___DEBUG, 'cache')) $this->details();
	}
	public function has($ID){
		return isset($this->cache[$this->prefix . $ID]);
	}
	public function set($ID, $Data =null){
		$this->cache[$this->prefix . $ID] =$Data;
		return true;
	}
	public function get($ID, $Data =null){
		return (isset($this->cache[$this->prefix . $ID])) ?$this->cache[$this->prefix . $ID] :$Data;
	}
	public function delete($ID){
		unset($this->cache[$this->prefix . $ID]);
		return true;
	}
	public function details(){
		Next::Dump(self::_name(), $this->cache);
	}
	public static function _name(){
		return __CLASS__;
	}
}