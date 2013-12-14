<?php
class model{
	public $app =null;
	public $options =array();
	public function __construct($set =array()){
		$this->options =$set;
		if (method_exists($this, '_init')) $this->_init();
	}
	/**
	 *
	 * @return self
	 */
	public static function i(){
		$c =get_called_class();
		return new $c();
	}
}
