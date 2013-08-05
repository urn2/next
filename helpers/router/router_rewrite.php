<?php

class hRouter_Rewrite extends hRouter{

	public function __construct($Parse =false){
		if ($Parse) self::$Info =self::Parse();
	}

	public function Execute(){
		if (empty(self::$Info)) self::__construct(true);
		parent::Execute();
	}

	public static function Parse(){
		$_uri =self::Uri();
		if (strpos($_uri, '?')!==false)	list($_uri, $_query) =explode('?', $_uri);
		$_uri =preg_replace('!//+!', '/', trim($_uri, '/'));
		$_parts =explode('/', $_uri);
		$_info =array();
		if (count($_parts) >0 &&preg_match('/^[_a-z]+$/', $_parts[0])){
			$_info[] =$_parts[0];
			array_shift($_parts);
		} else
			$_info[] =self::DEF_CONTROLLER;
		if (count($_parts) >0 &&preg_match('/^[_a-z]+[_a-z0-9]*$/', $_parts[0])){
			$_info[] =$_parts[0];
			array_shift($_parts);
		} else
			$_info[] =self::DEF_ACTION;
		//$_info['cai'] =$_info[0].'/'.$_info[1];
		$_info['args'] =(count($_parts) >0) ?array_merge($_parts, $_GET) :$_GET;
		return $_info;
	}
}