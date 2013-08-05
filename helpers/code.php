<?php

class hCode{

	public static function RandomStr($Len =6, $Char ="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"){
		$r ='';
		srand((double)microtime() *1000000);
		for ($i =0; $i <$Len; $i++){
			$r .=$Char[rand() %strlen($Char)];
		}
		return $r;
	}

	public static function RandomReadableStr($Len =6){
		$conso =array(
			'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 
			's', 't', 'v', 'w', 'x', 'y', 'z');
		$vocal =array('a', 'e', 'i', 'o', 'u');
		$r ='';
		srand((double)microtime() *1000000);
		$max =$Len /2;
		for ($i =1; $i <=$max; $i++)
			$r .=$conso[rand(0, 19)] .$vocal[rand(0, 4)];
		return $r;
	}

}