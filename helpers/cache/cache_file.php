<?php

class hCache_file extends Cache{
	protected $file ='';
	public function __construct($ID_Prefix ='next'){
		parent::__construct($ID_Prefix);
		//$this->prefix =$ID_Prefix . '.';
		$this->file =___NEXT . 'cache/' . $this->prefix . 'cache';
		if (is_file($this->file)) $this->cache =unserialize(file_get_contents($this->file));
	}
	public function __destruct(){
		if (is_writable(___NEXT . 'cache/')) file_put_contents($this->file, serialize($this->cache));
		parent::__destruct();
	}
}