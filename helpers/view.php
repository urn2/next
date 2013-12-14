<?php
class hView{
	public $Data =array();
	private $Cache ='';
	private $CacheCycle =0;
	private $CacheFile ='';
	public $File ='';
	/**
	 *
	 * 工厂模式
	 * @param unknown_type $Template
	 * @param unknown_type $Data
	 * @param unknown_type $Cache
	 * @param unknown_type $CacheCycle
	 * @return hView
	 */
	public static function factory($Template ='', $Data =array(), $Cache ='', $CacheCycle =0){
		return new hView($Template, $Data, $Cache, $CacheCycle);
	}
	public function __construct($Template ='', $Data =array(), $Cache ='', $CacheCycle =0){
		$this->File =$Template;
		$this->Data =$Data;
		$this->Cache =$Cache;
		$this->CacheCycle =$CacheCycle;
		$this->CacheFile =next::$app->buffer['/'] . next::$app->_file($this->Cache, 'cache/views');
	}
	public function __destruct(){}
	public function __set($Nm, $Val){
		$this->Data[$Nm] =$Val;
	}
	public function __get($Nm){
		return $this->Data[$Nm];
	}
	public function __isset($Nm){
		return isset($this->Data[$Nm]);
	}
	public function __unset($Nm){
		unset($this->Data[$Nm]);
	}
	public function __toString(){
		return $this->Flush(true);
	}
	public function SetFile($File =''){
		$this->File =$File;
	}
	public function SetData($Data =array()){
		$this->Data =$Data;
	}
	public function MergeData($Data =array()){
		if (is_array($Data)) $this->Data =array_merge($Data, $this->Data);
	}
	public function HasCache(){
		return empty($this->Cache) ? false : (is_file($this->CacheFile) && ($this->CacheCycle === true || (filemtime($this->CacheFile) > time() - $this->CacheCycle)));
	}
	/**
	 *
	 * @param boolean $Return
	 * @return string
	 */
	public function FlushCache($Return =false){
		$_f =file_get_contents($this->CacheFile) . next::i18n('core.template-cache-time', date('Y-m-d H:i:s', filemtime($this->CacheFile)));
		if (!$Return){
			echo $_f;
			return;
		}
		return $_f;
	}
	/**
	 *
	 * @param boolean $Return
	 * @return string
	 */
	public function Flush($Return =false){
		try{
			if ($this->HasCache()) return $this->FlushCache($Return);
			if (!$f =next::$app->_path($this->File, 'views')){
				if (!$Return){
					echo next::i18n('core.template-no-found', $this->File);
					return;
				} else return next::i18n('core.template-no-found', $this->File);
			}
			extract($this->Data, EXTR_REFS);
			ob_start();
			include ($f);
			$r =ob_get_contents();
			ob_end_clean();

			if (!empty($this->Cache)) file_put_contents($this->CacheFile, $r);
			if (!$Return){
				echo $r;
				return;
			} else
				return $r;
		} catch (Exception $e){
			vFL::out($e);
			if (!$Return){
				echo next::i18n('template-has-error', $f);
				return;
			} else
				return next::i18n('template-has-error', $f);
		}
	}
}