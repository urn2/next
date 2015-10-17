<?php

class router{
	public $route = [];
	public $set = ['cai' => '/',
		'separator' => '/'];
	public $def = ['controller' => 'index',
		'action' => 'index'];
	public $method = 'get';
	public $buffer = [];
	public function __construct(){
		$this->parse();
	}
	public function getRoute(){
		return $this->route;
	}
	public function parse(){
		$this->route = [$this->def['controller'],
			$this->def['action'],
			'prefix' => '',
			'method' => '',
			'args' => [],];
	}
}

class router_web extends router{
	public function parse(){
		$_route = [$this->def['controller'],
			$this->def['action'],
			'prefix' => (!empty($_SERVER['PATH_INFO'])) ?$_SERVER['PATH_INFO'] :'',
			'method' => $this->method(),
			'args' => $_GET,
			'_get' => $_GET,
			'input' =>file_get_contents('php://input'),
			'header' =>$this->headers(),
		];

		switch($_route['method']){
			case 'post':
				$_route['_post'] =$_POST;
				foreach($_POST as $key => $value) $_route['args'][$key] = $value;
				break;
			case 'delete':
			case 'put':
			case 'options':
			case 'head':
				parse_str($_route['input'], $request_vars);
				$_route['args'] = array_merge($_route['args'], $request_vars);
				$_route['_'.$_route['method']] =$request_vars;
				break;
		}
		$_method =$_route['method'];
		$_route[$_method] =json_decode($_route['input'], true);
		if(is_array($_route[$_method])) $_route['args'] = array_merge($_route['args'], $_route[$_method]);

		if(isset($_GET[$this->set['cai']])){
			if(strpos($_GET[$this->set['cai']], $this->set['separator']) !== false){
				list($_route[0], $_route[1]) = explode($this->set['separator'], $_GET[$this->set['cai']]);
				$_route[0] = !empty($_route[0]) ?strtolower($_route[0]) :$this->def['controller'];
				$_route[1] = !empty($_route[1]) ?strtolower($_route[1]) :$this->def['action'];
			}else
				$_route[0] = strtolower($_GET[$this->set['cai']]);
			unset($_route['args'][$this->set['cai']]);
		}
		$this->route = $_route;
	}
	public function method(){
		if(isset($this->buffer['method'])) return $this->buffer['method'];
		$this->buffer['method'] = strtolower($_SERVER['REQUEST_METHOD']);
		return $this->buffer['method'];
	}
	public function uri(){
		if(isset($this->buffer['uri'])) return $this->buffer['uri'];

		if(isset($_SERVER['HTTP_X_REWRITE_URL'])) $this->buffer['uri'] = $_SERVER['HTTP_X_REWRITE_URL'];elseif(isset($_SERVER['REQUEST_URI'])) $this->buffer['uri'] = $_SERVER['REQUEST_URI'];
		elseif(isset($_SERVER['ORIG_PATH_INFO'])){
			$this->buffer['uri'] = $_SERVER['ORIG_PATH_INFO'];
			if(!empty($_SERVER['QUERY_STRING'])) $this->buffer['uri'] .= '?'.$_SERVER['QUERY_STRING'];
		}
		return $this->buffer['uri'];
	}
	public function headers(){
		if (!function_exists('getallheaders')){
			function getallheaders(){
				$headers = [];
				foreach ($_SERVER as $name => $value){
					if (substr($name, 0, 5) == 'HTTP_') $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
				return $headers;
			}
		}
		$r =getallheaders();
		$name =['Host', 'User-Agent', 'Authorization', 'Accept', 'Accept-Language', 'Accept-Encoding', 'Cookie', 'Connection', 'Content-Type', 'Content-Length', 'Cache-Control', 'Referer', 'X-FireLogger', 'X-FireLoggerAppstats', 'x-insight'];
		foreach($name as $_n) unset($r[$_n]);
		//unset($r['Host'], $r['User-Agent'], $r['Authorization'], $r['Accept'], $r['Accept-Language'], $r['Accept-Encoding'], $r['Cookie'], $r['Connection'], $r['Content-Type'], $r['Content-Length']);
		return $r;
	}
}

class router_cli extends router{
	public function parse(){
		$_route = [$this->def['controller'],
			$this->def['action'],
			'prefix' => '',
			'method' => 'cli',
			'args' => [],];
		$_args = [];
		$args = $_SERVER['argv'];
		array_shift($args); //remove index 0 script file name

		reset($args);
		do{
			$arg = current($args);
			if(substr($arg, 0, 1) == '-'){
				$arg1 = next($args);
				if(substr($arg1, 0, 1) != '-'){
					$_args[substr($arg, 1)] = $arg1;
				}else prev($args);
			}else if(($p = strpos($arg, '=')) !== false){
				$_args[substr($arg, 0, $p)] = substr($arg, $p+1);
			}else $_args[] = $arg;
			$arg = next($args);
		}while(!empty($arg));

		if(isset($_args[$this->set['cai']])){
			if(strpos($_args[$this->set['cai']], $this->set['separator']) !== false){
				list($_route[0], $_route[1]) = explode($this->set['separator'], $_args[$this->set['cai']]);
				$_route[0] = !empty($_route[0]) ?strtolower($_route[0]) :$this->def['controller'];
				$_route[1] = !empty($_route[1]) ?strtolower($_route[1]) :$this->def['action'];
			}else
				$_route[0] = strtolower($_args[$this->set['cai']]);
			unset($_args[$this->set['cai']]);
		}

		$_route['args'] = $_args;

		$this->route = $_route;
	}
}