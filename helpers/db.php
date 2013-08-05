<?php

interface iDB{
	public function connect($Config);
	public function exec($SQL, $Unbuffered =null, $FreeNow =null);
	public function fetch($Callback =null, $Type =MYSQL_ASSOC);
	public function fetchOne($Callback =null, $Type =MYSQL_ASSOC);
	public function affectedRows();
	public function getRows();
	public function lastId();
}

class hDB{
	protected static $instance =array();
	/**
	 * 工厂模式
	 * 
	 * @param string $Config
	 * @return hDB_MySQL
	 */
	public static function factory($Config ='default'){
		if (!isset(self::$instance[$Config]) ||!is_resource(self::$instance[$Config])){
			$_config =Next::Config('db.' .$Config);
			if (empty($_config)) $_config =Next::Config('db.default');
			$_class ='hdb_' .$_config['driver'];
			
			self::$instance[$Config] =new $_class($_config);
		}
		return self::$instance[$Config];
	}
}
