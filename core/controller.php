<?php

class controller{
	const doExt = 'on';
	const doBefore = 'before';
	const doAfter = 'after';
	//public $doExt = 'on';
	/**
	 *
	 * @var webapp
	 */
	public $app;
	/**
	 *
	 * @var router
	 */
	public $route = [];
	/**
	 * @var view
	 */
	public $data = null;
	public $_status = [1 => 'error',];
	public function __construct($route, $app){
		$this->data = $this->view();
		$this->app = $app;
		$this->route = $route;
		$r = class_uses($this);
		foreach($r as $n) $this->exec('_init'.$n, false);

		$this->exec(self::doBefore, false);
		$this->init($this->route);
	}
	public function __destruct(){
		$this->exec(self::doAfter, false);
		$this->exec('render', false);
	}
	public function render(){
		if(@$this->data->has()){
			if(!$this->data->has('_file_') && $this->arg('callback', false)){
				echo $this->arg('callback')."(".$this->data.");";
			}else echo $this->data;
		}
	}
	public function init($route){
		$this->exec($route[1]);
	}
	public function exec($name, $isAction = true){
		if($isAction){
			$_fnc = self::doExt.$name;
			$_fncp = $this->route['method'].$name;
			$_fncb = self::doBefore.$name;
			$_fnca = self::doAfter.$name;

			$me = method_exists($this, $_fnc);
			$mep = method_exists($this, $_fncp);
			$meb = method_exists($this, $_fncb);
			$mea = method_exists($this, $_fnca);

			$result = null;

			if($me || $mep || $meb || $mea){
				if($meb) $result = $this->$_fncb();
				if($result !== false && $mep) $result = $this->$_fncp();
				if($result !== false && $me) $result = $this->$_fnc();
				if($result !== false && $mea) $this->$_fnca();
			}else $this->nofound($name);
		}else if(method_exists($this, $name)) $this->$name();
	}
	public function nofound(){
		$v = $this->app->_path($this->route[0].'/'.$this->route[1], 'views', false);//自动加载模板不允许继承
		if($v) $this->data['_file_'] = $v;else $this->app->status(404, $this->route[0].'/'.$this->route[1]);
		return false;
	}
	private function _format_arg($value, $def=null, $filter=null, $pattern=''){
		switch($filter){
			case 'i':
			case 'int':
			case 'integer':
				return (int)$value;
				break;
			case 'n':
			case 'num':
				$_value =trim($value);
				return preg_match('/^(\d+)$/', $_value) >0 ?$_value :$def;
				break;
			case 'a':
			case 'arr':
			case 'array':
				if(is_string($value)){
					if(strpos($value, ',') !==false) $value =explode(',', $value);
					else $value =[$value];
				}
				if(!is_array($value)) return $def;
				$r =[];
				foreach($value as $_k =>$_v){
					$_r =$this->_format_arg($_v, null, $pattern);
					if(!is_null($_r)) $r[$_k] =$this->_format_arg($_v, null, $pattern);
				}
				return $r;
			case 'pcre':
			case 'preg':
				$_value =trim($value);
				return preg_match($pattern, $_value) >0 ?$_value :$def;
				break;
			case 'b':
			case 'bool':
			case 'boolean':
				return (boolean)$value;
			case 'word':
				if(strpos($value, ';') !==false || strpos($value, ')') !==false || strpos($value, '(') !==false) return $def;
			case 's':
			case 'str':
			case 'string':
				$value =htmlspecialchars($value);
			case null:
				return $value;
				break;
			default:
				if(is_array($filter)){
					if(isset($filter[0])) $value =str_replace($filter, '', $value);
					else foreach($filter as $search =>$replace){
						$value =str_replace($search, $replace, $value);
					}
				}
				return $value;
				break;
		}

	}

	/**
	 * @param null $name
	 * @param null $def
	 * @return null || all args
	 */
	public function arg($name = null, $def = null, $filter = null, $pattern=''){
		return is_null($name)
			?$this->route['args']
			:((isset($this->route['args'][$name]))
				?$this->_format_arg($this->route['args'][$name], $def, $filter, $pattern)
				:$def);
	}
	/**
	 * @param null $method is method
	 * @return bool || method
	 */
	public function method($method = null){
		return is_null($method) ?$this->route['method'] :$method == $this->route['method'];
	}
	/**
	 * @param string $file
	 * @param array  $data
	 * @return view
	 */
	function view($file = '', $data = []){
		return new view($file, $data);
	}
	/**
	 * fn(code, msg), fn(code, data), fn(data), fn(code)
	 * @param int  $code [data=>,err=>]
	 * @param null $data || msg
	 * @return bool
	 */
	function status($code = 0, $data = null){
		$_data =[];
		if(!is_numeric($code)){
			if(isset($code['data']) || isset($code['err'])) $_data =$code;
			else $_data =['err'=>0, 'data'=>$code];
		}else{
			$_data['err'] = $code;
			if(isset($this->_status[$code])) $_data['msg'] = $this->app->i18n($this->_status[$code]);
			if($code ==0){
				if(func_num_args() >1) $_data['data'] =$data;
				elseif(is_null($data)) $_data['data'] =$this->data->get();
			}
			elseif(is_string($data)) $_data['msg'] = $data;
		}
		$this->data->set($_data);
		return false;


		unset($this->data['_file_']);
		if(!is_numeric($code)){
			if(isset($code['data']) || isset($code['err'])) $this->data->merge($code);else{
				$this->data['err'] = 0;
				$this->data['data'] = $code;
			}
		}else{
			$this->data['err'] = $code;
			if(isset($this->_status[$code])) $this->data['msg'] = $this->app->i18n($this->_status[$code]);
			if($code ==0 && func_num_args() > 1) $this->data['data'] = $data;
			elseif(is_string($data)) $this->data['msg'] = $data;
		}
		return false;
	}
}

class cli_controller extends controller{
}

class rest_controller extends controller{
	public $_data = null;
	public $_status = [0 => 'ok',
		1 => 'error',];
	function after(){
		if(!is_null($this->_data)) echo json_encode($this->_data, JSON_UNESCAPED_UNICODE);
	}
	public function status($code = 0, $data = null){
		$info = isset($this->_status[$code]) ?$this->_status[$code] :$this->_status[1];
		$this->_data['err'] = $code;
		$this->_data['msg'] = $this->app->i18n($info);
		$this->_data['data'] = $data;
		return false;
	}
	public function result($Data = [], $Info = '', $Result = 1){
		$this->_data['data'] = $Data;
		$this->_data['result'] = $Result;
		$this->_data['info'] = $this->app->i18n($Info);
		return false;
	}
	public function data($Data = []){
		$this->_data = $Data;
		return false;
	}
	public function error($info, $data = null){
		$this->_data['data'] = $data;
		$this->_data['result'] = 0;
		$this->_data['info'] = $this->app->i18n($info);
		return false;
	}
}