<?php

class hSQL{
	const sortASC ='ASC', sortDESC ='DESC';
	private $db =null;
	public $table =null;
	private $primary =null;
	private $list =array();
	public $SQL ='';
	public $_sql =array();

	private $join=array();
	private $_join =array();
	private $history=array();

	public static function factory($Table, $Primary =null, $Config ='default'){
		return new hSQL($Table, $Primary, $Config);
	}
	public function __construct($Table, $Primary =null, $Config ='default'){
		$this->db =hDB::factory($Config);
		$this->table =$Table;
		$this->primary =$Primary;
	}
	public function __destruct(){
		//if (count($this->history) >0) var_dump($this->history);
	}
	public function lastError(){
		return $this->db->error;
	}
	public function lastId(){
		return $this->db->insert_id;
	}
	public function affected(){
		return $this->db->affected_rows;
	}
	public function create($Fields, $IsReplace =null){
		if(is_string($Fields) && $IsReplace ==null){
			$this->SQL =$Fields;
		} else {
			if (!is_array($Fields)) return false;
			if (empty($Fields)) return false;
			if (is_array(current($Fields)) && empty($IsReplace)){
				$_cols =array_keys(current($Fields));
				$_vals =array();
				foreach ($Fields as $_n =>$_fields){
					$__fields =array();
					foreach ($_fields as $k => $v) $__fields[$k] = $this->db->quote($v);
					$_vals2 =array_values($__fields);
					$_vals[] =implode(", ", $_vals2);
				}
				$_vals =implode("'), ('", $_vals);
			} else{
				$__fields =array();
				foreach ($Fields as $k => $v) $__fields[$k] = $this->db->quote($v);
				$_cols =array_keys($Fields);
				$_vals =array_values($__fields);
				$_vals =implode(", ", $_vals);
			}
			$_cols =implode('`, `', $_cols);
			$this->SQL =($IsReplace ?"REPLACE" :"INSERT") . " INTO `{$this->table}` (`{$_cols}`) VALUES ({$_vals})";
		}
		$this->history[] =$this->SQL;
		$o =$this->db->query($this->SQL);
		if($o) return $this->lastId();
		return $o;
	}
	public function count($SQL="", $N='*'){
		$this->_sql['select'] ="COUNT({$N}) COUNT";
		return $this->readOne($SQL, "COUNT");
	}
	public function read($SQL=""){
		if(empty($SQL)){
			$_get =empty($this->_sql['select']) ?'*' :$this->_sql['select'];
			$_sort =empty($this->_sql['sort']) ?'' :$this->_sql['sort'];
			$_where =empty($this->_sql['filter']) ?'' :' WHERE ' . $this->_sql['filter'];
			$_limit =empty($this->_sql['limit']) ?'' :$this->_sql['limit'];
			$_join =empty($this->_sql['join']) ?'' : implode('', $this->_sql['join']);
			$this->SQL ="SELECT {$_get} FROM `{$this->table}`{$_join}{$_where}{$_sort}{$_limit}";
		} else $this->SQL=$SQL;
		$this->_sql =array();
		$this->history[] =$this->SQL;

		$r =$this->db->query($this->SQL);
		if($r) return $r->fetch_all();//SQLITE3_ASSOC, MYSQLI_ASSOC,
		return $r;
	}
	public function readOne($SQL="", $Col=null){
		if(empty($SQL)){
			$_get =empty($this->_sql['select']) ?'*' :$this->_sql['select'];
			$_sort =empty($this->_sql['sort']) ?'' :$this->_sql['sort'];
			$_where =empty($this->_sql['filter']) ?'' :' WHERE ' . $this->_sql['filter'];
			$_limit =empty($this->_sql['limit']) ?'' :$this->_sql['limit'];
			$_join =empty($this->_sql['join']) ?'' : implode('', $this->_sql['join']);
			$this->SQL ="SELECT {$_get} FROM `{$this->table}`{$_join}{$_where}{$_sort}{$_limit}";
		} else $this->SQL=$SQL;
		$this->_sql =array();
		$this->history[] =$this->SQL;

		$r =$this->db->query($this->SQL);
		if(!$r) return false;
		$result =$r->fetch_assoc();

		if(!is_null($Col)) return $result[$Col];
		return $result;
	}
	public function update($SQL=""){
		if(empty($SQL)){
			if (empty($this->_sql['set'])) return false;
			$_set =$this->_sql['set'];
			$_where =empty($this->_sql['filter']) ?'' :' WHERE ' . $this->_sql['filter'];
			$_limit =empty($this->_sql['limit']) ?'' :$this->_sql['limit'];
			$this->SQL ="UPDATE `{$this->table}` SET {$_set}{$_where}{$_limit}";
		} else $this->SQL=$SQL;
		$this->_sql =array();
		$this->history[] =$this->SQL;

		$o =$this->db->query($this->SQL);
		if($o) return $this->affected();
		return $o;
	}
	public function delete($SQL=""){
		if(empty($SQL)){
			$_where =empty($this->_sql['filter']) ?'' :' WHERE ' . $this->_sql['filter'];
			$_limit =empty($this->_sql['limit']) ?'' :$this->_sql['limit'];
			$this->SQL ="DELETE FROM `{$this->table}`{$_where}{$_limit}";
		} else $this->SQL=$SQL;
		$this->_sql =array();
		$this->history[] =$this->SQL;

		$o =$this->db->query($this->SQL);
		if($o) return $this->affected();
		return $o;
	}

	public function join($Table, $Conditions =null, $IsLeft =true) {
		$_table =(is_string($Table)) ?$Table :$Table->table;
		$s =($IsLeft) ?' LEFT JOIN ' :' RIGHT JOIN';
		$s .=" `{$_table}`";
		if (is_array($Conditions)){
			$s .=" ON (";
			foreach ($Conditions as $Row => $As) {
				$s .="`{$_table}`.`{$Row}` = `{$this->table}`.`{$As}`";
			}
			$s .=")";
		} elseif (is_string($Conditions)){
			$s .=$Conditions;
		}

		$this->_sql['filter'] .=" AND ".$Table->_sql['filter'];
		//$this->_sql['select'] .=", ".$Table->_sql['select'];

		//$this->_join[] =$Table;
		$this->_sql['join'][] =$s;
		return $this;
	}

	public function selectFields($Fields ='*') {
		$this->_sql['select']=$this->buildFields(func_get_args(), func_num_args());
		return $this;
	}
	public function setFields($Fields) {
		$this->_sql['set']=$this->buildSetFields($Fields);
		return $this;
	}
	public function orderBy($Sort =array()){
		$Nums =func_num_args();
		$_sort =array();
		if ($Nums == 0 && !empty($this->primary)){
			$_sort[] =$this->primary;
		}
		if (is_string($Sort)){
			if ($Nums == 1){
				$_sort[] =$Sort;
			} else{
				$_arg2 =strtoupper(func_get_arg(1));
				if ($Nums == 2 && ($_arg2 == self::sortASC || $_arg2 == self::sortDESC))
					$_sort[$Sort] =$_arg2;
				else
					$_sort =func_get_args();
			}
		} else{
			$_sort =$Sort;
		}
		$_s =array();
		foreach ($_sort as $key =>$value)
			$_s[] =is_numeric($key) ?"`{$this->table}`.`{$value}` ASC" :"`{$this->table}`.`{$key}` {$value}";
		$this->_sql['sort'] =" ORDER BY " . implode(', ', $_s);
		return $this;
	}
	public function limit($Rows, $Offset =0){
		$this->_sql['limit'] =empty($Rows) ?'' :((func_num_args() == 1) ?" LIMIT {$Rows}" :" LIMIT {$Offset}, {$Rows}");
		return $this;
	}
	public function filter($Conditions){
		$this->_sql['filter'] ='';
		if (!empty($Conditions)){
			$Nums =func_num_args();
			if ($Nums == 1 && !is_array($Conditions)){
				$this->_sql['filter'] ="`{$this->table}`.`{$this->primary}` = '{$Conditions}'";
			} elseif ($Nums == 2){
				$_arg2 =func_get_arg(1);
				$this->_sql['filter'] ="`{$this->table}`.`{$Conditions}` = '{$_arg2}'";
			} else{
				$_where =array();
				foreach ($Conditions as $_col =>$_val)
					$_where[] =(is_numeric($_col)) ?$_val :"`{$this->table}`.`{$_col}` = " . $this->db->quote($_val) . "";
				if (!empty($_where)) $this->_sql['filter'] =implode(' AND ', $_where);
			}
		}
		return $this;
	}
	public function filterStr($Conditions){
		$this->_sql['filter'] =$Conditions;
		return $this;
	}
	private function buildSetFields($Fields =array()){
		if (!is_array($Fields)) return '';
		$_sets =array();
		foreach ($Fields as $key =>$value)
			$_sets[] ="`{$key}` = " . $this->db->quote($value) . "";
		return implode(", ", $_sets);
	}
	private function buildFields($Fields =array(), $Nums =0){
		$fs =current($Fields);
		$Fields =($Nums == 1 && is_array($fs) && (is_array(current($fs)) || current($fs) =='*')) ?$fs :array(
			$this->table =>$Fields);
		$_s =array();
		foreach ($Fields as $_table =>$_fields){
			if (is_string($_fields)){
				$_s[] ="`{$_table}`.{$_fields}";
			} elseif (is_array($_fields)){
				$Nums =count($_fields);

				if ($Nums == 1 && is_array(current($_fields))){
					$_fields =current($_fields);
					$Nums =count($_fields);
				}
				if ($Nums>0){
				foreach ($_fields as $key =>$value)
					$_s[] =is_numeric($key) ?"`{$_table}`.`{$value}`" :"`{$_table}`.`{$key}` `{$value}`";
				} else{
					$_s[] ="`{$_table}`.*";
				}

			}
		}
		return implode(', ', $_s);
	}
}
