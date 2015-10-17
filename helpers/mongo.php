<?php

class hMongo extends MongoClient{
	private static $___link =array();
	private $__ns ='';
	/**
	 * 
	 * @param string $Link
	 * @return hRedis
	 */
	static public function factory($Link='mongo'){
		if (!isset(self::$___link[$Link])){
			$s =next::config('db.'.$Link);
			if (is_array($s)) {
				try {
					self::$___link[$Link] =new self($s[0]);
					//self::$___link[$Link]->connect($s[0]);
					self::$___link[$Link]->$s[1];
				} catch (MongoConnectionException $e) {
					die($e->getMessage());
				}
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
