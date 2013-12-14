<?php
class router{
	public $route =array();
	public $set =array(
		'cai' =>'/',
		'separator' =>'/');
	public $def =array(
		'controller' =>'index',
		'action' =>'index');
	public $method ='get';
	public $buffer=array();
	public function __construct(){
		$this->parse();
	}
	public function getRoute(){
		return $this->route;
	}
	public function parse(){
		$_route =array(
			$this->def['controller'],
			$this->def['action'],
			'prefix' =>(!empty($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : '',
			'method'=>$this->method(),
			'args' =>$_GET);
		//if ($_route['method'] == 'post') $_route['post'] =$_POST;
		switch ($_route['method']){
			case 'post':
				$_route['post'] =$_POST;
				break;
			case 'delete':
			case 'put':
				parse_str(file_get_contents('php://input'), $request_vars);
				$_route['args'] =array_merge($_GET, $request_vars);
				
				break;
		}
		if (isset($_GET[$this->set['cai']])){
			if (strpos($_GET[$this->set['cai']], $this->set['separator']) !== false){
				list($_route[0], $_route[1]) =explode($this->set['separator'], $_GET[$this->set['cai']]);
				$_route[0] =!empty($_route[0]) ? strtolower($_route[0]) : $this->def['controller'];
				$_route[1] =!empty($_route[1]) ? strtolower($_route[1]) : $this->def['action'];
			} else
				$_route[0] =strtolower($_GET[$this->set['cai']]);
			unset($_route['args'][$this->set['cai']]);
		}
		$this->route =$_route;
	}
	public function method(){
		if (isset($this->buffer['method'])) return $this->buffer['method'];
		$this->buffer['method'] =strtolower($_SERVER['REQUEST_METHOD']);
		return $this->buffer['method'];
	}
	public function uri(){
		if (isset($this->buffer['uri'])) return $this->buffer['uri'];

		if (isset($_SERVER['HTTP_X_REWRITE_URL'])) $this->buffer['uri'] =$_SERVER['HTTP_X_REWRITE_URL'];
		elseif (isset($_SERVER['REQUEST_URI'])) $this->buffer['uri'] =$_SERVER['REQUEST_URI'];
		elseif (isset($_SERVER['ORIG_PATH_INFO'])){
			$this->buffer['uri'] =$_SERVER['ORIG_PATH_INFO'];
			if (!empty($_SERVER['QUERY_STRING'])) $this->buffer['uri'] .='?' . $_SERVER['QUERY_STRING'];
		}
		return $this->buffer['uri'];
	}
}