<?php

class model{
	/**
	 *
	 * @var app
	 */
	public $app = null;
	public $options = [];
	private static $instance = [];
	public $_sql =[];
	public function __construct($set = []){
		$this->options = $set;
		$this->app = next::$app;
		if(method_exists($this, '_init')) $this->_init();
	}
	/**
	 * @param array $set
	 * @return self
	 */
	public static function i($set = []){
		$c = get_called_class();
		if(empty(self::$instance[$c])) self::$instance[$c] = new $c($set);
		return self::$instance[$c];
	}
	/**
	 * @param array $set
	 * @return self
	 */
	public static function instance($set = []){
		return self::i($set);
	}
	/**
	 * @param $table
	 * @param $pid
	 * @return hSQL
	 */
	public function sql($table, $pid){
		if(!isset($this->_sql[$table])) $this->_sql[$table] =hSQL::factory($table, $pid);
		return $this->_sql[$table];
	}
	public function _map($array, $key = 0, $value = 1){
		if(!is_array($array)) return $array;
		$r = [];
		if(is_null($key)){
			foreach($array as $_key => $_value){
				$r[] = is_callable($value) ?$value($_value, $_key) :$_value[$value];
			}
		}else{
			foreach($array as $_key => $_value){
				$r[$_value[$key]] = is_callable($value) ?$value($_value, $_key) :$_value[$value];
			}
		}
		return $r;
	}
}
