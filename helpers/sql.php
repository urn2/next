<?php

class hSQL{
	const sortASC ='ASC', sortDESC ='DESC';
	private $db =null;
	private $table =null;
	private $primary =null;
	private $list =array();
	public $SQL ='';
	private $_sql =array();
	
	private $join=array();
	
	public static function factory($Table, $Primary =null, $Config ='default'){
		return new hSQL($Table, $Primary, $Config);
	}
	public function __construct($Table, $Primary =null, $Config ='default'){
		$_config =Next::Config('db.' . $Config);
		$_class ='hdb_' . $_config['driver'];
		$this->db =new $_class($_config);
		$this->table =$Table;
		$this->primary =$Primary;
	}
	public function create($Fields, $IsReplace =null){
		if (!is_array($Fields)) return false;
		if (empty($Fields)) return false;
		if (is_array(current($Fields)) && empty($IsReplace)){
			$_cols =array_keys(current($Fields));
			$_vals =array();
			foreach ($Fields as $_n =>$_fields){
				$_vals2 =array_values($_fields);
				$_vals[] =implode("', '", $_vals2);
			}
			$_vals =implode("'), ('", $_vals);
		} else{
			$_cols =array_keys($Fields);
			$_vals =array_values($Fields);
			$_vals =implode("', '", $_vals);
		}
		$_cols =implode('`, `', $_cols);
		$this->SQL =($IsReplace ?"REPLACE" :"INSERT") . " INTO `{$this->table}` (`{$_cols}`) VALUES ('{$_vals}')";
		return $this->db->exec($this->SQL)->status();
	}
	public function read(){
		$_get =empty($this->_sql['select']) ?'*' :$this->_sql['select'];
		$_sort =empty($this->_sql['sort']) ?'' :$this->_sql['sort'];
		$_where =empty($this->_sql['filter']) ?'' :' WHERE ' . $this->_sql['filter'];
		$_limit =empty($this->_sql['limit']) ?'' :$this->_sql['limit'];
		$_join =empty($this->_sql['join']) ?'' : implode('', $this->_sql['join']);
		$this->SQL ="SELECT {$_get} FROM `{$this->table}`{$_join}{$_where}{$_sort}{$_limit}";
		$this->_sql =array();
		return $this->db->exec($this->SQL)->fetch();
	}
	public function update(){
		if (empty($this->_sql['set'])) return false;
		$_set =$this->_sql['set'];
		$_where =empty($this->_sql['filter']) ?'' :' WHERE ' . $this->_sql['filter'];
		$_limit =empty($this->_sql['limit']) ?'' :$this->_sql['limit'];
		$this->SQL ="UPDATE `{$this->table}` SET {$_set}{$_where}{$_limit}";
		$this->_sql =array();
		return $this->db->exec($this->SQL)->affectedRows();
	}
	public function delete() {
		$_where =empty($this->_sql['filter']) ?'' :' WHERE ' . $this->_sql['filter'];
		$_limit =empty($this->_sql['limit']) ?'' :$this->_sql['limit'];
		$this->SQL ="DELETE FROM `{$this->table}`{$_where}{$_limit}";
		$this->_sql =array();
		return $this->db->exec($this->SQL)->affectedRows();
	}
	
	public function join($Table, $Conditions =null, $IsLeft =true) {
		$s =($IsLeft) ?' LEFT JOIN ' :' RIGHT JOIN';
		$s .=" `{$Table}`";
		if (is_array($Conditions)){
			$s .=" ON (";
			foreach ($Conditions as $Row => $As) {
				$s .="`{$Table}`.`{$Row}` = `{$this->table}`.`{$As}`";
			}
			$s .=")";
		} elseif (is_string($Conditions)){
			$s .=$Conditions;
		}
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
					$_where[] =(is_numeric($_col)) ?$_val :"`{$_col}` = '" . mysql_real_escape_string($_val) . "'";
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
			$_sets[] ="`{$this->table}`.`{$key}` = '{$value}'";
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
				if ($Nums == 0) $_fields[] ='*';
				foreach ($_fields as $key =>$value)
					$_s[] =is_numeric($key) ?"`{$_table}`.`{$value}`" :"`{$_table}`.`{$key}` `{$value}`";
				
			}
		}
		return implode(', ', $_s);
	}
}
