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
		$this->CacheFile =Next::AppPath() . Next::File($this->Cache, 'views/cache');
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
		return empty($this->Cache['name']) ?false :(is_file($this->CacheFile) && ($this->CacheCycle === true || (filemtime($this->CacheFile) > time() - $this->CacheCycle)));
	}
	/**
	 * 
	 *
	 * @param boolean $Return
	 * @return string
	 */
	public function FlushCache($Return =false){
		$_benchmark ='_benchmark_cache,' . $this->Cache;
		Next::Benchmark($_benchmark);
		$_f =file_get_contents($this->CacheFile) . Next::Language('core.cache_time', date('Y-m-d H:i:s', filemtime($this->CacheFile)));
		if (!$Return){
			echo $_f;
			Next::$Caches['views']['has'] =true;
			Next::Benchmark($_benchmark, true);
			return;
		}
		Next::Benchmark($_benchmark, true);
		return $_f;
	}
	/**
	 * 
	 *
	 * @param boolean $Return 
	 * @return string
	 */
	public function Flush($Return =false){
		$_benchmark ='_benchmark_view,' . $this->File;
		try{
			Next::$Caches['views']['in'] =true;
			if ($this->HasCache()){
				Next::$Caches['views']['in'] =false;
				return $this->FlushCache($Return);
			}
			Next::Benchmark($_benchmark);
			if (!$f =Next::Path($this->File, 'views')){
				Next::$Caches['views']['in'] =false;
				if (!$Return){
					Next::$Caches['views']['has'] =true;
					echo Next::Language('core.no_template', $this->File);
					return;
				} else
					Next::Language('core.no_template', $this->File);
			}
			extract($this->Data, EXTR_REFS);
			ob_start();
			include ($f);
			$r =ob_get_contents();
			ob_end_clean();
			if (!empty($this->Cache) && $f =fopen($this->CacheFile, 'w')){
				fwrite($f, $r);
				fclose($f);
			}
			Next::$Caches['views']['in'] =false;
			if (!$Return){
				Next::$Caches['views']['has'] =true;
				echo $r;
				Next::Benchmark($_benchmark, true);
				return;
			} else{
				Next::Benchmark($_benchmark, true);
				return $r;
			}
		} catch (Exception $e){
			Next::Dump($e);
			Next::$Caches['views']['in'] =false;
			if (!$Return){
				Next::$Caches['views']['has'] =true;
				echo Next::Language('error_in_template', $f);
				return;
			} else
				return Next::Language('error_in_template', $f);
		}
	}
}