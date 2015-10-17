<?php
class hHtml_table{
	private $data =array();
	private $column =array();
	private $suffix =array();
	private $filter =array();
	private $set =array();
	public function __construct($Data, $Sets =array()){
		$this->data =$Data;
		if(is_string($Sets)) $caption =$Sets;
		else{
			$this->set['namep'] =isset($Sets['name']) ?$Sets['name'] .'.' :'';
			if(!empty($Sets['caption'])) $caption =next::i18n($Sets['caption']);
			elseif(isset($Sets['name'])){
				$_cp =$this->set['namep'] .'caption';
				$caption =next::i18n($_cp);
				if($_cp ==$caption) $caption ='';
			}
		}
		$this->set['title'] =$caption;
		$this->set['class'] ='table' .(isset($Sets['class']) ?' ' .$Sets['class'] :'');
	}
	public function setColumn($Column =array()){
		$this->column =$Column;
		return $this;
	}
	public function setSuffix($Suffix){
		$this->suffix =$Suffix;
		return $this;
	}
	public function setFilter($Filter){
		$this->filter =$Filter;
		return $this;
	}
	public function flush($return =false){
		if($return) return (string)$this;
		echo $this;
	}
	public function __toString(){
		if(!is_array($this->data) ||empty($this->data)){
			$tr ="\t<tr><td>无数据</td></tr>\n";
			$th ='';
		}else{
			/**
			 * 1 从column设定继承顺序
			 * 2 合并row中字段和suffix中字段
			 * 3 另外新建容器 保存column设定中字段 并把不包含在column设定中的字段附加在后
			 * 4 根据新容器中顺序 进行显示
			 */
			// 从数据中第一row中得到key 并设定顺序
			if(!empty($this->column)){
				$_namep =isset($this->set['namep']) ?$this->set['namep'] :'';
				$_col_max =0;
				$_row =array();
				foreach($this->data as $row_num=>$row){
					if(!is_array($row)) $row =array($row);
					$_row =array_merge($_row, $row);
				}
				$_colfor =array();
				$_suffix =$this->suffix;
				foreach($this->column as $col=>$set){
					if(empty($set)) unset($_row[$col], $_suffix[$col]);
					if(isset($_row[$col]) ||isset($_suffix[$col])) $_colfor[$col] =hHtml_form::Language(isset($this->column[$_namep .$col]) ?$this->column[$_namep .$col] :$col);
				}
				foreach($_row as $col=>$set)
					$_colfor[$col] =next::i18n(isset($this->column[$_namep .$col]) ?$this->column[$_namep .$col] :$col);
				foreach($_suffix as $col=>$set)
					$_colfor[$col] =next::i18n(isset($this->column[$_namep .$col]) ?$this->column[$_namep .$col] :$col);
				$_col_max =count($_colfor);
				$th ="\t<tr>\n\t\t<th>" .implode("</th>\n\t\t<th>", $_colfor) ."</th>\n\t</tr>";
			}else{
				$_col_max =0;
				foreach($this->data as $row){
					if($_col_max <count($row)){
						$_col_max =count($row);
						$_row =$row;
					}
				}
				$_colfor =(is_array($_row)) ?array_keys($_row) :array(0);
				$th ='';
			}
			$tr =array();
			foreach($this->data as $idx=>$row){
				if(is_array($row)){
					$_count =count($row);
					reset($row);
				}else
					$_count =1;
				if($_count ==1 &&$_col_max !=1 &&is_array($row)){
					$_str =is_array($row) ?current($row) :$row;
					$tr[] ="\t<tr>\n\t\t<td colspan='{$_col_max}'>" .$_str ."</td>\n\t</tr>";
				}elseif($_count ==0){}else{
					if(!is_array($row)) $row =array($row);
					$row['.'] =$idx;
					$td =array();
					foreach($_colfor as $col=>$_null){
						$r =(isset($this->suffix[$col])) ?$this->suffix[$col] :$row[$col];
						if(isset($this->filter[$col]) &&is_callable($this->filter[$col])) $r =call_user_func($this->filter[$col], $r, $col);
						$r =preg_replace('/\'(\[([._a-zA-Z0-9]*)\])\'/', '\$row["\\2"]', var_export($r, true));
						$r =preg_replace('/(\[([._a-zA-Z0-9]*)\])/', '{\$row["\\2"]}', $r);
						@eval("\$r =\"$r\";");
						eval("\$r =$r;");
						$td[$col] =($r !=='') ?$r :'&nbsp;';
					}
					$tr[] ="\t<tr>\n\t\t<td>" .implode("</td>\n\t\t<td>", $td) ."</td>\n\t</tr>";
				}
			}
			$tr =implode("\n", $tr);
		}
		$t =(!empty($this->set['title']) >0) ?"	<caption>{$this->set['title']}</caption>\n" :'';
		$h ="\n<table class='{$this->set['class']}' border='0' cellpadding='0' cellspacing='0'>\n{$t}\n<thead>\n{$th}\n</thead>\n{$tr}\n</table>\n";
		return $h;
	}
}
class hHtml_form{
	private $Hidden;
	private $Children;
	private $hasSubmit =false;
	public $Action;
	public $Sets =array('method' =>'post','class' =>'form');
	private $NameP ='';
	public static function Language($KeyWord){
		if(is_array($KeyWord)) return $KeyWord;
		if(strpos($KeyWord, '.') ==false) return $KeyWord;
		return next::i18n($KeyWord);
	}
	/**
	 * 创建一个表单类
	 *
	 * @param string $Action
	 *        	提交链接
	 * @param array $Sets
	 *        	= [caption][id][method][name] 表单设定内容
	 */
	public function __construct($Action ='', $Sets =array()){
		$this->Action =$Action;
		$this->Sets['action'] =$Action;
		if(is_array($Sets) &&count($Sets) >0){
			foreach($Sets as $k=>$v)
				$this->Sets[$k] =$v;
		}elseif(is_string($Sets)) $this->Sets['caption'] =next::i18n($Sets);
		if(!isset($this->Sets['name']) &&isset($this->Sets['id'])) $this->Sets['name'] =$this->Sets['id'];
		$this->NameP =(isset($this->Sets['name'])) ?$this->Sets['name'] .'_' :'';
		$this->CaptionP =(isset($this->Sets['name'])) ?$this->Sets['name'] .'.' :'form.';
		if(!isset($this->Sets['caption']) &&isset($this->Sets['name'])) $this->Sets['caption'] =$this->CaptionP .'caption';
		$this->hasSubmit =false;
	}
	private function Label($Caption, $Namefor =''){
		if($Caption ==''){
			$Caption =$this->CaptionP .$Namefor;
		}
		$_c =next::i18n($Caption);
		$Namefor =$this->NameP .$Namefor;
		return "<label for='{$Namefor}'>{$_c}</label>";
	}
	private function Input($Type, $Name, $Value, $Attributes =false){
		return hHtml::Input($Type, $this->NameP .$Name, $Value, $Attributes);
	}
	private function Children($Caption, $Input ='', $Rem =''){
		if(empty($Input)) $this->Children[] =array($Caption);
		else{
			if(!empty($Rem)) $Rem ='<span>' .next::i18n($Rem) .'</span>';
			$this->Children[] =array($Caption,$Input,$Rem);
		}
	}
	public function Hidden($Name, $Value){
		$this->Hidden[] =$this->Input('hidden', $Name, $Value);
		return $this;
	}
	public function Info($Info){
		$this->Children('<span class="info">' .next::i18n($Info) .'</span>');
		return $this;
	}
	public function File($Name, $Value ='', $Caption ='', $Rem =''){
		$this->Sets['enctype'] ='multipart/form-data';
		$this->Children($this->Label($Caption, $Name), $this->Input('file', $Name, $Value), $Rem);
		return $this;
	}
	public function Text($Name, $Value ='', $Caption ='', $Rem =''){
		$this->Children($this->Label($Caption, $Name), $this->Input('text', $Name, $Value), $Rem);
		return $this;
	}
	public function Password($Name, $Value ='', $Caption ='', $Rem =''){
		$this->Children($this->Label($Caption, $Name), $this->Input('password', $Name, $Value), $Rem);
		return $this;
	}
	public function Radio($Name, $Option, $Captionid =0, $Valueid =1, $Sel =null, $Caption ='', $Rem =''){
		$h =array();
		foreach($Option as $k=>$o){
			if(is_array($o)){
				$sValue =$o[$Valueid];
				$sCaption =$o[$Captionid];
			}else{
				$sValue =$k;
				$sCaption =$o;
			}
			// $bysel = (!is_null($Sel) && $sValue == $Sel)? " checked='checked'":'';
			$sCaption =next::i18n($sCaption);
			$h[] ="\t";
			$h[] =$this->Input('radio', $Name, $sValue, (!is_null($Sel) &&$sValue ==$Sel) ?array('checked' =>'checked') :array());
			$h[] ="&nbsp;{$sCaption}\n";
			// $h[] ="\t<input type='radio' name='{$Name}' value='{$sValue}'{$bysel} />&nbsp;{$sCaption}\n";
		}
		$this->Children($this->Label($Caption, $Name), implode("\n", $h), $Rem);
		return $this;
	}
	public function Checkbox($Name, $Option, $Captionid =0, $Valueid =1, $Sel =array(), $Caption ='', $Rem =''){
		$h =array();
		foreach($Option as $k=>$o){
			if(is_array($o)){
				$sValue =$o[$Valueid];
				$sCaption =$o[$Captionid];
			}else{
				$sValue =$k;
				$sCaption =$o;
			}
			$bysel =(!is_null($Sel) &&$sValue ==$Sel) ?" checked='checked'" :'';
			$sCaption =next::i18n($sCaption);
			$h[] ="\t";
			$h[] =$this->Input('checkbox', $Name .'[]', $sValue, (!is_null($Sel) &&$sValue ==$Sel) ?array('checked' =>'checked') :array());
			$h[] ="&nbsp;{$sCaption}\n";
			// $h[] ="\t<input type='checkbox' name='{$Name}[]' value='{$sValue}'{$bysel} />&nbsp;{$sCaption}\n";
		}
		$this->Children($this->Label($Caption, $Name), implode("\n", $h), $Rem);
		return $this;
	}
	public function Select($Name, $Option, $Captionid =0, $Valueid =1, $Sel =null, $Caption ='', $Rem ='', $Group =null, $Groupid =null){
		$hasGroup =(!is_null($Group) &&!is_null($Groupid));
		$og ='';
		$eg =false;
		$h =array();
		$h[] ="\n\t<select id='{$this->NameP}{$Name}' name='{$this->NameP}{$Name}'>";
		foreach($Option as $k=>$o){
			if(is_array($o)){
				$sValue =$o[$Valueid];
				$sCaption =$o[$Captionid];
			}else{
				$sValue =$k;
				$sCaption =$o;
			}
			$bysel =(!is_null($Sel) &&$sValue ==$Sel) ?" selected='selected'" :'';
			$sCaption =next::i18n($sCaption);
			if($hasGroup &&$og !=='' &&$og !==$o[$Groupid]){
				$h[] ="\t</optgroup>";
				$og ="";
			}
			if($hasGroup &&isset($Group[$o[$Groupid]]) &&$og =='' &&$og !==$o[$Groupid]){
				$h[] ="\t<optgroup label='{$Group[$o[$Groupid]]}'>";
				$og =$o[$Groupid];
				$eg =false;
			}
			$h[] ="\t\t<option value='{$sValue}'{$bysel}>{$sCaption}</option>";
		}
		if(!empty($og)){
			$h[] ="\t</optgroup>";
		}
		$h[] ="\t</select>";
		$this->Children($this->Label($Caption, $Name), implode("\n", $h), $Rem);
		return $this;
	}
	public function Textarea($Name, $Value ='', $Caption ='', $Rem ='', $Cols =30, $Rows =5){
		$this->Children($this->Label($Caption, $Name), "<textarea id='{$this->NameP}{$Name}' name='{$this->NameP}{$Name}' size='400' cols='{$Cols}' rows='{$Rows}'>$Value</textarea>", $Rem);
		return $this;
	}
	public function Submit($Name ='submit', $Value ='', $Caption ='', $Rem =''){
		if($Value ==''){
			$Value =$Name;
			$Name ='submit';
		}
		$this->hasSubmit =true;
		$_c =next::i18n($Caption);
		$this->Children($_c, $this->Input('submit', $Name, next::i18n($this->CaptionP .$Value)), $Rem);
		return $this;
	}
	public function Flush($Return =null){
		if($Return) return $this->__toString();
		else echo $this->__toString();
		return true;
	}
	public function __toString(){
		if(!$this->hasSubmit) $this->Submit('submit');
		$class =$this->Sets['class'];
		$caption =isset($this->Sets['caption']) ? next::i18n($this->Sets['caption']) :'';
		unset($this->Sets['class'], $this->Sets['caption']);
		$h =array();
		$h[] ="<form";
		foreach($this->Sets as $n=>$v){
			$h[] =" {$n}='{$v}'";
		}
		$h[] =" class='{$class}'";
		$h[] =">";
		if(count($this->Hidden)){
			$h[] ="\n" .implode("\n", $this->Hidden) ."\n";
		}
		$h[] ="<fieldset>";
		$h[] ="<legend>{$caption}</legend>";
		foreach($this->Children as $child){
			$h[] ="<p>" .implode('', $child) ."</p>";
		}
		$h[] ="</fieldset>";
		$h[] ="</form>";
		return implode("\n", $h);
	}
	public function Table($Return =null){
		if(!$this->hasSubmit) $this->Submit('提交');
		$class =$this->Sets['class'];
		$caption =next::i18n($this->Sets['caption']);
		unset($this->Sets['class'], $this->Sets['caption']);
		$h ="<form"; // method='{$this->Sets['method']}' action='{$this->Action}' name='{$this->Sets['name']}' id='{$this->Sets['id']}' enctype='multipart/form-data'>";
		foreach($this->Sets as $n=>$v){
			$h .=" {$n}='{$v}'";
		}
		$h .=" class='{$class}'";
		$h .=">";
		if(count($this->Hidden)){
			$h .="\n" .implode("\n", $this->Hidden) ."\n";
		}
		$h .=hHtml::Table($this->Children, $caption);
		/*
		 * $h .=hHtml::Table($this->Children, array( 'col' =>false, 'title' =>$caption, $class), true);
		 */
		$h .="</form>\n";
		if($Return) return $h;
		else echo $h;
		return true;
	}
}

class Html_tag{
	private $name=null;
	private $attr=array();
	private $html=null;
	private $sub=array();
	public $lv=0;
	function __construct($name){
		$this->name =$name;
	}
	function append($sub){
		$sub->lv =$this->lv+1;
		$this->sub[] =$sub;
		return $this;
	}
	function appendTo($parent){
		if (is_a($parent, __CLASS__)){
			$parent->append($this);
		}
		return $this;
	}
	function data($name, $value=null){
		return $this->attr($name, $value, 'data-');
	}
	function attr($name, $value=null, $pref=''){
		if (is_null($value)){
			if(is_array($name)){
				foreach ($name as $key => $value) {
					$this->attr[$pref.$key] =$value;
				}
			} else if(is_string($pref.$name)) return $this->attr[$pref.$name];
		} else $this->attr[$pref.$name] =$value;
		return $this;
	}
	function html($str){
		$this->sub=array();
		$this->sub[] =$str;
		return $this;
	}
	function _attr($attr){
		if(count($attr) ==0) return null;
		$_attr =array();
		foreach ($attr as $key => $value) {
			$_attr[] =$key.'="'.$value.'"';
		}
		$_attr =implode(' ', $_attr);
		strlen($_attr) && $_attr=' '.$_attr;
		return $_attr;
	}
	function _sub($sub){
		if(count($sub) ==0) return null;
		$_sub =implode("\n", $sub);
		is_a($sub[0], __CLASS__) && $_sub="\n".$_sub;
		return $_sub;
	}
	function __toString(){
		$_attr=$this->_attr($this->attr);
		$_html=$this->_sub($this->sub);
		is_null($_attr) &&$_attr='';
		$_pref=str_repeat("\t", $this->lv);
		$_pref2=is_a($this->sub[0], __CLASS__) ?"\n".$_pref :'';
		return $_pref.'<'.$this->name.$_attr.(is_null($_html) ?'/>' :'>'.$_html.$_pref2.'</'.$this->name.'>');
	}
}

class hHtml{
	static public function tag($name, $attr=array()){
		return new Html_tag($name);
	}
	static public function Drop($class="drop"){
		return "<div class='{$class}'></div>";
	}
	/**
	 * 生成链接字符串
	 *
	 * @param string $Uri
	 *        	字符串
	 * @param string $Title
	 *        	链接文字
	 * @param array $Attributes
	 *        	链接属性
	 * @return string
	 */
	static public function Anchor($Uri, $Title =false, $Attributes =false){
		return "<a href='{$Uri}'" .((empty($Attributes)) ?'' :self::Attributes($Attributes)) .'>' .((empty($Title)) ?$Uri :next::i18n($Title)) .'</a>';
	}
	static public function Attributes($Args){
		if(empty($Args)) return '';
		if(is_string($Args)) return ' ' .$Args;
		$r ='';
		foreach($Args as $key=>$val)
			$r .=" {$key}='$val'";
			// foreach($Args as $key => $val) $r .= ' '.$key.'=\''.$val.'\'';
		return $r;
	}
	static public function Input($Type, $Name, $Value, $Attributes =false){
		return "<input type='{$Type}' name='{$Name}' id='{$Name}' value='{$Value}'" .((empty($Attributes)) ?'' :self::Attributes($Attributes)) ." />";
	}
	/**
	 *
	 * @param string $Action
	 * @param unknown $Sets
	 * @return hHtml_form
	 */
	static public function Form($Action ='', $Sets =array()){
		return new hHtml_form($Action, $Sets);
	}
	/**
	 *
	 * @param unknown $Data
	 * @param string $Title
	 * @return hHtml_table
	 */
	static public function Table($Data, $Title =''){
		return new hHtml_table($Data, $Title);
	}
	/**
	 * 生成跳转页面
	 *
	 * @param string $uri
	 *        	跳转路径
	 * @param string $info
	 *        	显示信息
	 * @param string $method
	 *        	链接的编号
	 * @param int $step
	 *        	等待时间
	 * @param
	 *        	string or array $ASTO 同时打开的 at same time open 字符串时刷新框架，array(框架目标, 打开链接(空为刷新))
	 */
	public static function Redirect($uri ='', $info ='', $method ='302', $step =2, $ASTO =array()){
		if($method =='refresh'){
			header('Refresh: ' .$step .'; url=' .$uri);
		}else{
			$codes =array(300 =>'Multiple Choices',301 =>'Moved Permanently',302 =>'Found',303 =>'See Other',304 =>'Not Modified',305 =>'Use Proxy',307 =>'Temporary Redirect');
			$method =isset($codes[(int)$method]) ?$method :302;
			header('HTTP/1.1 ' .$method .' ' .$codes[$method]);
			header('Location: ' .$uri);
		}
		if($info ==''){
			$info =$uri;
		}
		//$v =Next::View('redirect.tpl', array('uri' =>$uri,'info' =>$info), true);
		if(!empty($ASTO)){
			if(is_array($ASTO)){
				$_h =array();
				$_h[] ="<script type='text/javascript'>";
				foreach($ASTO as $_target=>$_uri){
					$_e =($_target !='parent') ?"parent.window.{$_target}" :"window";
					$_uri =(!empty($_uri)) ?".location.href='{$_uri}'" :".location.reload()";
					$_h[] ="{$_e}{$_o};";
				}
				$_h[] ="</script>";
				$_h =implode("\n", $_h);
			}else{
				$_h ="<script type='text/javascript'>parent.window.{$ASTO}.location.reload();</script>";
			}
			if(strpos($v, '</body>')){
				$v =str_replace('</body>', "$_h</body>", $v);
			}elseif(strpos($v, '</html>')){
				$v =str_replace('</html>', "$_h</html>", $v);
			}else{
				$v .="$_h";
			}
		}
		die($v);
		// Last resort, exit and display the URL
		// die('<a href="'.$uri.'">'.$info.'</a>');
	}
}