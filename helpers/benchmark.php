<?php

class hBenchmark{

	protected $data =array();

	//public function __construct($Time=___N, $Memory=___M){
	public function __construct($Time=0, $Memory=0){
		$_benchmark =&$this->data;
		
		$_benchmark['level'][] ='_benchmark_total';
		$_benchmark['marks']['_benchmark_total'] =array('time' =>0);
		$_benchmark['time'] =$Time;// ___N; // microtime(true);
		$_benchmark['memory'] =$Memory;//___M; //memory_get_usage(true);
	}

	public function begin($Name){
		$_benchmark =&$this->data;
		
		$_benchmark['level'][] =$Name;
		$Name =implode('|', $_benchmark['level']);
		if (!isset($_benchmark['marks'][$Name])){
			$_benchmark['marks'][$Name] =array(
				'time' =>microtime(true) - $_benchmark['time'], 
				'memory' =>memory_get_usage(true) - $_benchmark['memory']);
			return true;
		}
		return false;
	}

	public function end(){
		$_benchmark =&$this->data;
		
		$_ln =implode('|', $_benchmark['level']);
		if (isset($_benchmark['marks'][$_ln]) && !isset($_benchmark['marks'][$_ln]['end'])){
			$_benchmark['marks'][$_ln]['time_end'] =microtime(true) - $_benchmark['time'];
			$_benchmark['marks'][$_ln]['memory_end'] =memory_get_usage(true) - $_benchmark['memory'];
		}
		$_ln =array_pop($_benchmark['level']);
		return $_ln;
	
	}

	public function getResults(){
		$_benchmark =&$this->data;
		
		$_marks =&$_benchmark['marks'];
		
		$r =array();
		$_ks =array_keys($_marks);
		krsort($_ks);
		foreach ($_ks as $_k =>$n){
			if (!isset($_marks[$n]['end'])){
				$_marks[$n]['time_end'] =microtime(true) - $_benchmark['time'];
				$_marks[$n]['memory_end'] =memory_get_usage(true) - $_benchmark['memory'];
			
			}
			$r[$_k] =$_marks[$n];
		}
		ksort($r);
		$_r =array();
		foreach (array_keys($_marks) as $_k =>$n)
			$_r[$n] =$r[$_k];
		return $_r;
	}
}