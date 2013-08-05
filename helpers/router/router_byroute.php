<?php
(defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

class hRouter_ByRoute extends hRouter_Rewrite{

	public function __construct($Parse =true){
		if ($Parse) self::$Info =self::Parse();
	}

	public function Execute(){
		if (empty(self::$Info)) self::__construct(true);
		if (isset(self::$Info['jump'])){
			header('Location: http://127.0.0.1/forum/images/'.self::$Info['jump']);
			die();
		} else parent::Execute();
	}

	public static function Parse(){
		$_routess =Next::Config('routes.route');
		
		$_type =self::Method();
		$_uri =self::Uri();
		//$_uri =strtolower($_SERVER['REQUEST_URI']);
		//$_uri_parts =strtolower($_SERVER['PATH_INFO']);
		//$_uri_parts = strtolower(lRequest::Uri());
		

		$_args =$_GET;
		$_opt =array();
		if (isset($_routess[$_type][$_uri])){
			$_opt =$_routess[$_type][$_uri];
		} elseif (isset($_routess['*'][$_uri])){
			$_opt =$_routess['*'][$_uri];
		}
		if (!empty($_opt)){
			$_match =self::_match($_uri, $_uri, $_opt);
			if (!empty($_match)) return $_match;
		}
		$_routess2 =array();
		if (isset($_routess[$_type])) $_routess2 =$_routess[$_type];
		if (isset($_routess['*'])) $_routess2 =array_merge($_routess2, $_routess['*']);
		unset($_type);
		foreach ($_routess2 as $_route =>$_opt){
			$_match =self::_match($_uri, $_route, $_opt);
			//Next::Dump($_match, $_uri, $_route, $_opt);
			if (!empty($_match)){
				return $_match;
			}
		}
		$_match =parent::Parse();
		$_match['match'] =false;
		return $_match;
	}

	protected static function _match($Uri, $Route, $Options =array()){
		//static $_uri_parts ='';
		//if (!is_array($_uri_parts)) 
		$_uri_parts =explode('/', substr($Uri, 1));
		$_opt =$Options;
		if ($Uri ===$Route)
			$_routes =$_uri_parts;
		else $_routes =explode('/', substr($Route, 1));
		
		$_size =count($_routes);
		if ($_size !==count($_uri_parts)) return false;
		//if ($_routes[0] !==$_uri_parts[0]) return false;
		if (isset($_opt['extension'])){
			$_last =$_uri_parts[$_size -1];
			$_ext =$_opt['extension'];
			if (is_string($_ext)){
				$_last =explode($_ext, $_last);
			} elseif (is_array($_ext)){
				foreach ($_opt['extension'] as $_ext)
					if (empty($_ext) ||(strpos($_last, $_ext) !==false)) break;
				$_last =explode($_ext, $_last);
			}
			$_len =count($_last);
			if ($_len <2) return false;
			if ($_last[$_len -1] !=='') return false;
			if ($_len ===2)
				$_uri_parts[$_size -1] =$_last[0];
			else $_uri_parts[$_size -1] =$_last[0] .str_repeat($_uri_parts['extension'], $_len -2);
			//unset($_opt['extension']);
			$Uri ='/'.implode('/', $_uri_parts);
		}
		if ($Uri ===$Route) return $_opt;
		if (strpos($Route, '/:') ===false) return false;
		$_uri_static =explode('/:', $Route, 2);
		if (strpos($Uri, $_uri_static[0]) !==0) return false;
		$_opt['args'] =array();
		foreach ($_routes as $_idx =>$_parts){
			if (!empty($_parts) &&$_parts[0] ===':'){
				$_opt['args'][substr($_parts, 1)] =$_uri_parts[$_idx];
			}
		}
		if (isset($_opt['match'])){
			foreach ($_opt['args'] as $_arg =>$_value)
				if (isset($_opt['match'][$_arg]) &&preg_match($_opt['match'][$_arg], $_value) ==0) return false;
			//unset($_opt['match']);
		}
		return $_opt;
	}
}




