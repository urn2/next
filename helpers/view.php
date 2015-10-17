<?php
/**
 * 提供魔术方法，可以直接写为 view['title']='xxx' 设置模板中的title变量值为xxx
 * @author Vea
 * 
 * @todo 在最外层模板输出notice屏蔽
 *
 */
class hView implements ArrayAccess{
	public $Data =array();
	private $Cache ='';
	private $CacheCycle =0;
	private $CacheFile ='';
	public $File ='';
	
	static $_err =null;
	
	/**
	 *
	 * 工厂模式
	 * @param string $Template			模板路径与名字，基于 应用/views/目录
	 * @param unknown_type $Data		模板赋值，数组，key为模板中变量名，value为值
	 * @param unknown_type $Cache		构建缓存，缓存名，基于 应用/cache/views/目录
	 * @param unknown_type $CacheCycle	缓存有效时间，秒
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
		$this->CacheFile =next::$app->options['/'][0] . next::$app->_file($this->Cache, 'cache/views');
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
		return $this->flush(true);
	}
	public function setFile($File =''){
		$this->File =$File;
	}
	/**
	 * @param offset
	 */
	public function offsetExists ($offset) {
		return isset($this->Data[$offset]);
	}

	/**
	 * @param offset
	 */
	public function offsetGet ($offset) {
		return $this->Data[$offset];
	}

	/**
	 * @param offset
	 * @param value
	 */
	public function offsetSet ($offset, $value) {
		$this->Data[$offset] =$value;
	}

	/**
	 * @param offset
	 */
	public function offsetUnset ($offset) {
		unset($this->Data[$offset]);
	}
	/**
	 * 覆盖设置模板赋值
	 * @param array $Data
	 */
	public function data($Data =null, $Replace=false){
		if (is_array($Data)){
			if ($Replace) {
				$this->Data =$Data;
			} else $this->Data =array_merge($this->Data, $Data);
			return $this;
		} else return $this->Data;
	}
	/**
	 * 缓存是否存在
	 * @return boolean
	 */
	public function hasCache(){
		return empty($this->Cache) ? false : (is_file($this->CacheFile) && ($this->CacheCycle === true || (filemtime($this->CacheFile) > time() - $this->CacheCycle)));
	}
	/**
	 * 返回或直接输出缓存内容
	 * @param boolean $Return	是否返回
	 * @return string
	 */
	public function flushCache($Return =false){
		$_f =file_get_contents($this->CacheFile) . next::i18n('core.template-cache-time', date('Y-m-d H:i:s', filemtime($this->CacheFile)));
		if (!$Return){
			echo $_f;
			return;
		}
		return $_f;
	}
	/**
	 * 返回或直接输出模板内容
	 * @param boolean $Return	是否返回
	 * @return string
	 */
	public function flush($Return =false){
		try{
			if ($this->hasCache()) return $this->flushCache($Return);
			if (!$f =next::$app->_path($this->File, 'views')){
				if (!$Return){
					echo next::i18n('core.template-no-found', $this->File);
					return;
				} else return next::i18n('core.template-no-found', $this->File);
			}
			//-------------begin
			$views =array();
			$datas =array();
			foreach ($this->Data as $_key =>$_value){
				if ($_value instanceof hView) {
					$views[] =$_key;
				} else $datas[$_key] =$_value;
			}
			foreach ($views as $_key) $this->Data[$_key]->_inherit_ =$datas;
			
			if (is_null(self::$_err)) {
				self::$_err =error_reporting();
				error_reporting(self::$_err || E_NOTICE);
				$at=true;
			}
			if (isset($this->Data['_inherit_'])){
				extract($this->Data['_inherit_'], EXTR_REFS);
				unset($this->Data['_inherit_']);
			}
			//-------------end
			
			extract($this->Data, EXTR_REFS);
			ob_start();
			include ($f);
			
			if ($at) error_reporting(self::$_err);
			$r =ob_get_contents();
			ob_end_clean();

			if (!empty($this->Cache)) @file_put_contents($this->CacheFile, $r);
			if (!$Return){
				echo $r;
				return;
			} else
				return $r;
		} catch (Exception $e){
			if (!$Return){
				echo next::i18n('template-has-error', $f);
				return;
			} else
				return next::i18n('template-has-error', $f);
		}
	}
}