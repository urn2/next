<?php

class hRedis extends Redis{
	private static $___link =array();
	private $__ns ='';
	/**
	 * 
	 * @param string $Link
	 * @return hRedis
	 */
	static public function factory($Link='redis'){
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
	/*
	public function set($key, $var){
		return parent::set($this->__ns.'.'.$key, $var);
	}
	public function get($key){
		return parent::get($this->__ns.'.'.$key);
	}
	*/



}
