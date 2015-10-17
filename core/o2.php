<?php

class o2 implements ArrayAccess{
	var $data = [];
	function set($data){ $this->data = $data; }
	function get(){return $this->data;}
	function has($name = null){ return is_null($name) ?!empty($this->data) :isset($this->data[$name]); }
	function merge($data){ $this->data = array_merge($this->data, $data); }
	function clear(){ $this->data = []; }
	function offsetSet($offset, $value){ $this->data[$offset] = $value; }
	function &offsetGet($offset){ return $this->data[$offset]; }
	function offsetExists($offset){ return isset($this->data[$offset]); }
	function offsetUnset($offset){ unset($this->data[$offset]); }
	function __construct($data = []){ $this->data = $data; }
	//function __destruct(){ }
	function __sleep(){ return ['data']; } //serialize
	function &__get($offset){ return $this->offsetGet($offset); } // ->name
	function __set($offset, $value){ $this->offsetSet($offset, $value); } // ->name =xxx
	function __isset($offset){ return $this->offsetExists($offset); } //isset() empty()
	function __unset($offset){ $this->offsetUnset($offset); } //unset()
	function __toString(){ return json_encode($this->data, JSON_UNESCAPED_UNICODE); } //echo
	function __debugInfo(){ return $this->data; } //php5.6 var_dump
	function __call($name, $args){
		switch(count($args)){
			case 0:
				$this->offsetGet($name);
				return $this;
			case 1:
				$this->offsetSet($name, $args[0]);
				return $this;
			default:
				$this->offsetSet($name, $args);
				return $this;
		}
	} // name(), name(value), name(value1, ...)
	function __invoke(){
		switch(func_num_args()){
			case 0:
				return $this->data;
			case 1:
				$this->offsetGet(func_get_arg(0));
				return $this;
			default:
				$this->offsetSet(func_get_arg(0), func_get_arg(1));
				return $this;
		}
	} //(), (name), (name, value)
	static function __set_state($data){
		return new o2($data);
	} //var_export
}
