<?php

class hMemcache extends Memcache{
	private static $___link =array();
	private $__ns ='';
	/**
	 * 
	 * @param string $Link
	 * @return hMemcache:
	 */
	static public function factory($Link='memcache'){
		if (!isset(self::$___link[$Link])){
			$s =next::config('db.'.$Link);
			self::$___link[$Link] =new self;
			if (is_array($s)) {
				self::$___link[$Link]->connect($s[0], $s[1]);
				self::$___link[$Link]->__ns =$s[2];
			}
		}
		return self::$___link[$Link];
	}
	public function set($key, $var, $flag=null, $expire=null){
		return parent::set($this->__ns.'.'.$key, $var, $flag, $expire);
	}
	public function get($key, $flags=null){
		return parent::get($this->__ns.'.'.$key, $flags);
	}

	public function delete ($key, $timeout = null) {
		return parent::delete($this->__ns.'.'.$key, $timeout);
	}


}
