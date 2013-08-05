<?php
(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('No access allowed.');

class hRequest{
	private static $RW =array();
	public function __isset($Param){
		return isset($_GET[$Param]) || isset($_POST[$Param]) || isset(self::$RW['params'][$Param]);
	}
	public function __get($Param){
		return self::Param($Param);
	}
	public function __set($Param, $Value){
		self::$RW['params'][$Param] =$Default;
	}
	public function __unset($Param){
		unset(self::$RW['params'][$Param]);
	}
	public static function Param($Param, $default =NULL){
		if (isset($_GET[$Param]))
			return $_GET[$Param];
		elseif (isset($_POST[$Param]))
			return $_POST[$Param];
		elseif (isset(self::$RW['params'][$Param]))
			return self::$RW['params'][$Param];
		else
			return $default;
	}
	public static function setParam($Param, $Default){
		self::$RW['params'][$Param] =$Default;
	}
	public static function isGet(){
		return self::Method() == 'GET';
	}
	public static function isPost(){
		return self::Method() == 'POST';
	}
	public static function isPut(){
		return self::Method() == 'PUT';
	}
	public static function isDelete(){
		return self::Method() == 'DELETE';
	}
	public static function isHead(){
		return self::Method() == 'HEAD';
	}
	public static function isOptions(){
		return self::Method() == 'OPTIONS';
	}
	public static function isXmlHttpRequest(){
		return strtolower(self::Header('X_REQUESTED_WITH')) == 'xmlhttprequest';
	}
	public static function isFlash(){
		return strtolower(self::Header('USER_AGENT')) == 'shockwave flash';
	}
	public static function hasFirePHP(){
		if (!@preg_match_all('/\sFirePHP\/([\.|\d]*)\s?/si', self::Header('USER_AGENT'), $m) || !version_compare($m[1][0], '0.0.6', '>=')){
			return false;
		}
		return true;
	}
	public static function Header($Name){
		$_name ='HTTP_' . strtoupper(str_replace('-', '_', $Name));
		if (!empty($_SERVER[$_name])) return $_SERVER[$_name];
		if (function_exists('apache_request_headers')){
			$_headers =apache_request_headers();
			if (!empty($_headers[$Name])) return $_headers[$Name];
		}
		return false;
	}
	public static function RawBody(){
		$_body =file_get_contents('php://input');
		return (strlen(trim($_body)) > 0) ?$_body :false;
	}
	public static function File($Key){
		$f =&$_FILES[$Key];
		return (isset($f['name']) && isset($f['type']) && isset($f['size']) && isset($f['tmp_name']) && isset($f['error']) && ($f['error'] == UPLOAD_ERR_OK) && is_file($f['tmp_name']) && is_uploaded_file($f['tmp_name']) && is_readable($f['tmp_name'])) ?$f :false;
	}
	public static function Get($Param =NULL, $Default =NULL){
		if (is_null($Param)) return $_GET;
		return isset($_GET[$Param]) ?$_GET[$Param] :$Default;
	}
	public static function Post($Param =NULL, $Default =NULL){
		if (is_null($Param)) return $_POST;
		return isset($_POST[$Param]) ?$_POST[$Param] :$Default;
	}
	public static function PostFilter($Param, $Prefix =''){
		if (is_string($Param)){
			return self::_fiter($_POST[$Param], $Prefix);
		} elseif (is_array($Param)){
			$result =array();
			if ($Prefix != '') $Prefix .='_';
			foreach ($Param as $name =>$type){
				if (is_numeric($name)){
					$name =$type;
					$type =type::str;
				}
				$result[$name] =self::_fiter($_POST[$Prefix . $name], $type);
			}
			return $result;
		}
	}
	private static function _fiter($Value, $Type){
		switch ($Type){
		case type::Integer:
			return (int)$Value;
			break;
		default:
			return $Value;
			break;
		}
	}
	
	public static function PostInt($Param =NULL, $Default =NULL){
		return (int)self::Post($Param, $Default);
	}
	public static function PostStr($Param =NULL, $Default =NULL){
		$r =self::Post($Param, $Default);
		return ($r != '') ?$r :$Default;
	}
	public static function Cookie($Param =NULL, $Default =NULL){
		if (is_null($Param)) return $_COOKIE;
		return isset($_COOKIE[$Param]) ?$_COOKIE[$Param] :$Default;
	}
	public static function Server($Param =NULL, $Default =NULL){
		if (is_null($Param)) return $_SERVER;
		return isset($_SERVER[$Param]) ?$_SERVER[$Param] :$Default;
	}
	public static function Env($Param =NULL, $Default =NULL){
		if (is_null($Param)) return $_ENV;
		return isset($_ENV[$Param]) ?$_ENV[$Param] :$Default;
	}
	public static function Protocol(){
		if (isset(self::$RW['protocol'])) return self::$RW['protocol'];
		list($_protocol) =explode('/', $_SERVER['SERVER_PROTOCOL']);
		return strtolower($_protocol);
	}
	public static function Port(){
		if (isset(self::$RW['port'])) return self::$RW['port'];
		self::$RW['port'] =(isset($_SERVER['SERVER_PORT'])) ?intval($_SERVER['SERVER_PORT']) :80;
		if (isset($_SERVER['HTTP_HOST'])){
			$_host =explode(':', $_SERVER['HTTP_HOST']);
			if (count($_host) > 1){
				$_port =intval(end($_host));
				if ($_port !== self::$RW['port']) self::$RW['port'] =$_port;
			}
		}
		return self::$RW['port'];
	}
	public static function Method(){
		return $_SERVER['REQUEST_METHOD'];
	}
	public static function Uri(){
		if (isset(self::$RW['uri'])) return self::$RW['uri'];
		$uri ='';
		if (isset($_SERVER['HTTP_X_REWRITE_URL']))
			$uri =$_SERVER['HTTP_X_REWRITE_URL'];
		elseif (isset($_SERVER['REQUEST_URI']))
			$uri =$_SERVER['REQUEST_URI'];
		elseif (isset($_SERVER['ORIG_PATH_INFO'])){
			$uri =$_SERVER['ORIG_PATH_INFO'];
			if (!empty($_SERVER['QUERY_STRING'])) $uri .='?' . $_SERVER['QUERY_STRING'];
		}
		self::$RW['uri'] =$uri;
		return $uri;
	}
	public static function Pathinfo(){
		if (!empty($_SERVER['PATH_INFO'])) return $_SERVER['PATH_INFO'];
		if (($uri =self::Uri()) === null) return '';
		if ($pos =strpos($uri, '?')) $uri =substr($uri, 0, $pos);
		return $uri;
	}
	public static function Referer(){
		return self::Header('REFERER');
	}
	public static function ip(){
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $_SERVER['REMOTE_ADDR'];
	}
}