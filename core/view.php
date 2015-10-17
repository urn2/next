<?php


class view extends o2{
	static $_cls = __CLASS__;
	static $_err = null;
	public function __construct($file = '', $data = []){
		if(is_array($file)) $this->data = $file;elseif(!empty($file) && is_string($file)){
			$this->data = $data;
			$this->data['_file_'] = $file;
		}
	}
	public function __toString(){ return $this->render(); }
	public function setFile($file = ''){ $this->data['_file_'] = $file; }
	public function render(){
		if(!isset($this->data['_file_'])) return json_encode($this->data, JSON_UNESCAPED_UNICODE);
		$_f = $this->data['_file_'];
		try{
			$f = next::$app->_path($_f, 'views', false);
			if(!$f) return next::i18n('core.template-no-found', $_f);
			//-------------begin
			$views = [];
			$data = [];
			foreach($this->data as $_key => $_value){
				if($_value instanceof view){
					$views[] = $_key;
				}else $data[$_key] = $_value;
			}
			foreach($views as $_key) $this->data[$_key]->_inherit_ = $data;
			$at = false;
			if(!defined('___DEBUG') && is_null(self::$_err)){
				self::$_err = error_reporting();
				error_reporting(self::$_err || E_NOTICE);
				$at = true;
			}
			if(isset($this->data['_inherit_'])){
				extract($this->data['_inherit_'], EXTR_REFS);
				unset($this->data['_inherit_']);
			}
			//-------------end
			extract($this->data, EXTR_REFS);
			ob_start();
			include($f);
			if(!defined('___DEBUG') && $at) error_reporting(self::$_err);
			$r = ob_get_contents();
			ob_end_clean();
			return $r;
		}catch(Exception $e){
			return next::i18n('template-has-error', $_f);
		}
	}
}