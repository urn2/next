<?php

class hDB_MySQLx{
	private $Link =null;
	public $Num =array('Query' =>0, 'Update' =>0);

	public function __construct($Host, $User, $Password, $DB){
		if (!$this->Link =@mysql_connect($Host, $User, $Password)) throw new Exception(Next::Language('core.db_no_conn'));
		mysql_query("SET NAMES 'utf8'");
		if (!mysql_select_db($DB)) throw new Exception(Next::Language('core.db_no_db'));
	}

	public function __destruct(){//if (!is_null($this->Link)) mysql_close($this->Link);
}

	public function _Query($SQL){
		$this->Num['Query']++;
		return mysql_query($SQL, $this->Link);
	}

	public function _UnbufferedQuery($SQL){
		$this->Num['Update']++;
		return mysql_unbuffered_query($SQL, $this->Link);
	}

	public function Query($SQL, $Type =MYSQL_ASSOC){
		$handle =$this->_Query($SQL);
		if (!($handle)) return false;
		$num =mysql_num_rows($handle);
		$r =array();
		if ($num >0){
			while ($a =mysql_fetch_array($handle, $Type)){
				$r[] =$a;
			}
			//$r =mysql_fetch_array($handle, $Type);
		//if ($num>100) mysql_free_result($handle);
		}
		return $r;
	}

	public function One($SQL, $Type =MYSQL_ASSOC){
		if (!($handle =$this->_Query($SQL))) return false;
		return mysql_fetch_array($handle, $Type);
	}

	public function Update($SQL){
		if (!($handle =$this->_UnbufferedQuery($SQL))) return false;
		return mysql_affected_rows();
	}

	public function Insert($SQL){
		return $this->_UnbufferedQuery($SQL);
	}

	public function Insert4Id($SQL){
		return ($this->Insert($SQL)) ?mysql_insert_id() :false;
	}

	public function Count($SQL){
		$r =-1;
		if (!($handle =$this->_Query($SQL))) return false;
		$a =mysql_fetch_array($handle, MYSQL_ASSOC);
		if (isset($a['COUNT'])) $r =(int)$a['COUNT'];
		return $r;
	}

	public function InsertArray($DbName, $Array =array(), $IsReplace =false){
		if (count($Array) ==0) return false;
		$fields =array();
		$values =array();
		foreach ($Array as $field =>$value){
			$fields[] ='`' .$field .'`';
			$values[] ="'" .$value ."'";
		}
		$fields =implode(', ', $fields);
		$values =implode(', ', $values);
		$sql =$IsReplace ?"REPLACE" :"INSERT";
		$sql .=" INTO `{$DbName}` ({$fields}) VALUES ({$values})";
		return $sql;
		//return $this->Insert4Id($sql);
	}
}
?>