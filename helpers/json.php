<?php

class hJson {
	private  $Value =array();
	public function __set($Nm, $Val)
	{
		$this->Value[$Nm] =$Val;
	}
	public function __get($Nm)
	{
		return $this->Value[$Nm];
	}
	public function __isset($Nm)
	{
		return isset($this->Value[$Nm]);
	}
	public function __unset($Nm)
	{
		unset($this->Value[$Nm]);
	}
	public function __toString()
	{
		return json_encode($this->Value);
	}
	public function Flush($Return =false)
	{
		if ($Return) {
			return json_encode($this->Value);
		} else {
			echo json_encode($this->Value);
		}
	}
}




?>