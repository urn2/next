<?php

class hDB{
	protected static $instance =array();
	/**
	 * 工厂模式
	 *
	 * @param string $Config
	 * @return mysqli
	 */
	public static function factory($Config ='default'){
		if(is_array($Config)){
			$_config =$Config;
			$Config ="default";
		}
		if (!isset(self::$instance[$Config]) ||!is_resource(self::$instance[$Config])){
			$_config =empty($_config) ?next::config('db.' .$Config) :$_config;
			if (empty($_config)) $_config =next::config('db.default');
			$_class ='hdb_' .$_config['driver'];
			$db =new $_class();
			$db->connect($_config);
			$db->select_db($_config['db']);
			self::$instance[$Config] =$db;
		}
		return self::$instance[$Config];
	}
}
