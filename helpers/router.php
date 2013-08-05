<?php
class hRouter{
	const DEF_CONTROLLER ='index';
	const DEF_ACTION ='index';
	protected static $_rote =array('cai' =>'/', 'separator' =>'/');
	public static $Info =array();
	protected static $RW =array();
	public function __construct($Parse =false){
		if ($Parse){
			self::$_rote =Next::Config('app.cai', self::$_rote);
			self::$Info =self::Parse();
		}
	}
	public function Link($CAI ='', $Args =array(), $Prefix =''){
		$l =array();
		if (!empty($CAI)){
			if (strpos($CAI, '/') !== false){
				$_cai =explode('/', $CAI);
				$_c =array_shift($_cai);
				$_a =implode('/', $_cai);
			} else{
				$_c =$CAI;
				$_a ='';
			}
			$l[] =strtolower(self::$_rote['cai'] . '=' . $_c . self::$_rote['separator'] . $_a);
		}
		if (!empty($Args)){
			if (is_array($Args)) foreach ($Args as $k =>$v)
				$l[] =strtolower($k) . '=' . $v;
			else $l[] =$Args;
		}
		$p =(!empty($Prefix)) ? $Prefix : (isset(self::$Info['prefix']) ? self::$Info['prefix'] : '');
		return $p . ((empty($l)) ? '' : '?' . implode($l, '&')); // .'#query';
	}
	public function Execute(){
		// if ((func_num_args() >0) &&is_array($_ri =func_get_arg(0))
		// &&!empty($_ri)){
		// $_ri =func_get_arg(0);
		// } else{
		if (empty(self::$Info)) $this->__construct(true);
		$_ri =self::$Info;
		if (isset($_ri['match']) && !$_ri['match']){
			Next::callEvent('system.404');
			// Event::Run('system.404');
			die();
		}
		// }
		$c ='c' . $_ri[0];
		if (class_exists($c, true)){
			$c =new $c($_ri);
			Next::$hasAction =true;
		} else
			Next::callEvent('system.404'); // throw new
			                                      // Exception(Next::L10n('core.no_controller',
			                                      // $_ri[0]));
				                                      // die($c->$_ri[1]());
		//$c->$_ri[1]();
		// return
	}
	public static function Parse(){
		$_info =array(self::DEF_CONTROLLER, self::DEF_ACTION,
			// 'cai' =>self::DEF_CONTROLLER .'/' .self::DEF_ACTION,
			'args' =>$_GET,
			'prefix' =>(!empty($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : '');
		if (isset($_GET[self::$_rote['cai']])){
			if (strpos($_GET[self::$_rote['cai']], self::$_rote['separator']) !== false){
				list($_info[0], $_info[1]) =explode(self::$_rote['separator'], $_GET[self::$_rote['cai']]);
				$_info[0] =!empty($_info[0]) ? $_info[0] : self::DEF_CONTROLLER;
				$_info[1] =!empty($_info[1]) ? $_info[1] : self::DEF_ACTION;
				// $_info['cai'] =$_info[0] .'/' .$_info[1];
			}
			unset($_info['args'][self::$_rote['cai']]);
		}
		return $_info;
	}
	public static function Method(){
		if (isset(self::$RW['method'])) return self::$RW['method'];
		self::$RW['method'] =strtolower($_SERVER['REQUEST_METHOD']);
		return self::$RW['method'];
	}
	public static function Uri(){
		if (isset(self::$RW['uri'])) return self::$RW['uri'];
		$uri ='';
		if (isset($_SERVER['HTTP_X_REWRITE_URL'])) $uri =$_SERVER['HTTP_X_REWRITE_URL'];
		elseif (isset($_SERVER['REQUEST_URI'])) $uri =$_SERVER['REQUEST_URI'];
		elseif (isset($_SERVER['ORIG_PATH_INFO'])){
			$uri =$_SERVER['ORIG_PATH_INFO'];
			if (!empty($_SERVER['QUERY_STRING'])) $uri .='?' . $_SERVER['QUERY_STRING'];
		}
		self::$RW['uri'] =$uri;
		return $uri;
	}
}