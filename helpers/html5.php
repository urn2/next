<?php

class htmlTag{
	public $Name, $Content ='', $Attributes =array(), $Subs =array();
	private $hasContent =FALSE, $hasSubs =FALSE;
	public $Parent =NULL;
	const prefix ='<', suffix ='>';
	public static function factory($Name ='!-- --'){
		$cls =new self();
		$cls->Name =$Name;
		return $cls;
	}
	public function Content($Text){
		if ($this->Name == 'input'){
			$this->Attributes['value'] =$Text;
		} else{
			$this->Content =$Text;
			$this->hasContent =strlen($Text) > 0;
		}
		return $this;
	}
	public function Attributes($Attr, $Value =NULL){
		if (is_array($Attr)){
			$this->Attributes =array_merge($this->Attributes, $Attr);
		} elseif (is_string($Attr)){
			if (is_null($Value)) return $this->Attributes[$Attr];
			$this->Attributes[$Attr] =$Value;
		}
		return $this;
	}
	public function Subs($Sub){
		$this->hasSubs =TRUE;
		if (is_string($Sub)){
			$_s =self::factory($Sub);
			$_s->Parent =$this;
			$this->Subs[] =$_s;
			return $_s;
		}
		if (is_object($Sub) && is_a($Sub, __CLASS__)){
			$this->Subs[] =$Sub;
		}
		return $this;
	}
	public function __toString(){
		$tag =array(self::prefix, $this->Name);
		//$tag =self::prefix . $this->Name;
		if (!empty($this->Attributes)){
			foreach ($this->Attributes as $a =>$v){
				$tag[] =' ' . $a . '="' . $v . '"';
			}
		}
		if ($this->hasContent || $this->hasSubs){
			$tag[] =self::suffix;
			if ($this->hasSubs){
				foreach ($this->Subs as $_s){
					$tag[] =(string)$_s;
				}
			} else
				$tag[] =$this->Content;
			$tag[] =self::prefix;
			$tag[] ='/';
			$tag[] =$this->Name;
				//$tag .=self::suffix . $this->Content . self::prefix . '/' . $this->Name;
		} else{
			$tag[] ='/';
				//$tag .=' /';
		}
		//$tag .=self::suffix;
		$tag[] =self::suffix;
		$tag[] ="\n";
		return implode('', $tag);
	}
}

