<?php

/**
 * 创建一个表单
 * @param string $Action 提交链接
 * @param array $Sets = [id][method] 表单设定内容
 *
 */
class hCssForm{
	private $Hidden;
	private $Children;
	private $hasSubmit =false;

	public $Action;
	
	public $Sets =array('caption'=>'', 'id'=>'cssform', 'method'=>'post'/*, 'class'=>'cssform', /*'enctype'=>'multipart/form-data'*/);
	
	static private function Attributes($Args)
	{
		if (empty($Args)) return '';
		if (is_string($Args)) return ' '.$Args;
		$r ='';
		foreach($Args as $key => $val) $r .= " {$key}='$val'";
		//		foreach($Args as $key => $val) $r .= ' '.$key.'=\''.$val.'\'';
		return $r;
	}
	/**
	 * 创建一个表单类
	 * @param string $Action 提交链接
	 * @param array $Sets = [caption][id][method][name] 表单设定内容
	 */
	public function __construct($Action, $Sets=array()){
		$this->Action =$Action;
		$this->Sets['action'] =$Action;
		if (count($Sets)>0) {
			foreach ($Sets as $k => $v) {
				$this->Sets[$k] =$v;
			}
		}
		if (!isset($this->Sets['name']) && isset($this->Sets['id'])) {
			$this->Sets['name'] =$this->Sets['id'];
		}
		$this->hasSubmit =false;
	}
	static private function Input($Set=array())
	{
		if (!isset($Set['name']) && isset($Set['id'])) {
			$Set['name'] =$Set['id'];
		}
		return "<input".self::Attributes($Set).' />';
	}
	static private function Label($Set=array())
	{
		if (is_array($Set) && count($Set) ==0){
			return '';
		} elseif (is_string($Set)){
			return "<label>{$Set}</label>";
		}
		$for =isset($Set['for']) ? " for='{$Set['for']}'" :'';
		$caption =isset($Set['caption']) ? $Set['caption'] :'&nbsp;';
		$em =isset($Set['em']) &&$Set['em'] ==true ? " <em>*</em>":'';
		return "<label{$for}>$caption$em</label>";
	}
	static private function Note($Set=array())
	{
		if (is_array($Set) && count($Set) ==0){
			return '';
		} elseif (is_string($Set)){
			return "<p>{$Set}</p>";
		}
		return "<p>".implode('</p><p>', $Set)."</p>";
	}
	static private function Field($Sub, $Label=array())
	{
		if (is_array($Label) && count($Label) ==0){
			return "<fieldset>\n{$Sub}</fieldset>\n";;
		} elseif (is_string($Label)){
			return "<fieldset>\n<legend>{$Label}</legend>{$Sub}</fieldset>\n";
		}
		$em =isset($Label['em']) &&$Label['em'] ==true ? " <em>*</em>":'';
		$legend =isset($Label['caption']) ? "\t<legend>{$Label['caption']}{$em}</legend>\n" :'';
		return "<fieldset>\n{$legend}{$Sub}</fieldset>\n";
	}
	
	public function Hidden($Set=array())
	{
		$Set['type']='hidden';
		$this->Hidden[] =self::Input($Set);
	}
	public function Info($Info)
	{
		$this->Children[] =array('info', $Info);
	}
	public function File($Set =array(), $Label =array(), $Note =array())
	{
		if (is_array($Label) && isset($Set['id']) && count($Label)>0) $Label['for'] =$Set['id'];
		$Set['type'] ='file';
		$this->Children[] =array('text', self::Label($Label),self::Input($Set),self::Note($Note));
	}
	public function Text($Set =array(), $Label =array(), $Note =array())
	{
		if (is_array($Label) && isset($Set['id']) && count($Label)>0) $Label['for'] =$Set['id'];
		$Set['type'] ='text';
		$this->Children[] =array('text', self::Label($Label),self::Input($Set),self::Note($Note));
	}
	public function Password($Set =array(), $Label =array(), $Note =array())
	{
		if (is_array($Label) && isset($Set['id']) && count($Label)>0) $Label['for'] =$Set['id'];
		$Set['type'] ='password';
		$this->Children[] =array('password', self::Label($Label),self::Input($Set),self::Note($Note));
	}
	public function Radio($Option, $Set =array(), $Label =array(), $Note =array())
	{
		$idvalue =isset($Set['idvalue']) ?$Set['idvalue'] :1;
		$idcaption =isset($Set['idcaption']) ?$Set['idcaption'] :0;
		$sel =isset($Set['sel']) ?$Set['sel']: null;
		unset($Set['idvalue'], $Set['idcaption'], $Set['sel']);
		$h =array();
		if (!is_array($Option)) return ;
		foreach ($Option as $k => $o) {
			if (is_array($o)) {
				$aset =$Set;
				$aset['type'] ='radio';
				$aset['value'] =$o[$idvalue];
				if (!is_null($sel) && $o[$idvalue] ==$sel) $aset['checked'] ='checked';
				$h[] =self::Input($aset)." {$o[$idcaption]}";
			}elseif (is_string($o) || is_numeric($o)){
				$aset =$Set;
				$aset['type'] ='radio';
				$aset['value'] =$k;
				if (!is_null($sel) && ($k ==$sel)) $aset['checked'] ='checked';
				$h[] =self::Input($aset)." {$o}";
			}
		}
		//$this->Children[] =array('radio', self::Field("\t\t<label>".implode("</label>\n\t\t<label>", $h)."</label>\n", $Label));
		$this->Children[] =array('radio', self::Label($Label),  "<label>".implode("</label><label>", $h)."</label>");
	}
	public function Checkbox($Option, $Set =array(), $Label =array(), $Note =array())
	{
		$idvalue =isset($Set['idvalue']) ?$Set['idvalue'] :0;
		$idcaption =isset($Set['idcaption']) ?$Set['idcaption'] :1;
		$sel =isset($Set['sel']) ?$Set['sel']: null;
		unset($Set['idvalue'], $Set['idcaption'], $Set['sel']);
		$h =array();
		if (!is_array($Option)) return ;
		foreach ($Option as $o) {
			$aset =$Set;
			if (!isset($aset['name']) && isset($aset['id'])) $aset['name'] =$aset['id'];
			if (isset($aset['name'])) $aset['name'] .='[]';
			$aset['value'] =$o[$idvalue];
			$aset['type'] ='checkbox';
			if (!is_null($sel) && in_array($o[$idvalue], $sel)) $aset['checked'] ='checked';
			$h[] =self::Input($aset)." {$o[$idcaption]}";
		}
		$this->Children[] =array('checkbox', self::Field("<label>".implode("</label><label>", $h)."</label>", $Label));
	}
	//public function Select($Name, $Option, $Captionid=0, $Valueid=1, $Sel =null, $Caption='', $Rem='')
	public function Select($Option, $Set =array(), $Label =array(), $Note =array())
	{
		if (!is_array($Option)) return ;
		if (is_array($Label) && isset($Set['id']) && count($Label)>0) $Label['for'] =$Set['id'];

		if (!isset($Set['name']) && isset($Set['id'])) $Set['name'] =$Set['id'];

		$idvalue =isset($Set['idvalue']) ?$Set['idvalue'] :1;
		$idcaption =isset($Set['idcaption']) ?$Set['idcaption'] :0;
		$sel =isset($Set['sel']) ?$Set['sel']: null;
		unset($Set['idvalue'], $Set['idcaption'], $Set['sel']);

		$h =array('');
		foreach ($Option as $o) {
			if (is_array($o)) {
				$aset =array();
				$aset['value'] =$o[$idvalue];
				if (!is_null($sel) && $o[$idvalue] ==$sel) $aset['selected'] ='selected';
				$h[] ="<option".self::Attributes($aset).">{$o[$idcaption]}</option>";
			}elseif (is_string($o) || is_numeric($o)){
				$aset =array();
				$aset['value'] =$o;
				if (!is_null($sel) && $o ==$sel) $aset['selected'] ='selected';
				$h[] ="<option".self::Attributes($aset).">{$o}</option>";
			}
		}
		$this->Children[] =array('select', 
			self::Label($Label),
			"<select".self::Attributes($Set).">".implode("",$h)."</select>",
			self::Note($Note)
		);
	}
	public function Textarea($Set =array(), $Label =array(), $Note =array())
	{
		if (is_array($Label) && isset($Set['id']) && count($Label)>0) $Label['for'] =$Set['id'];

		if (!isset($Set['name']) && isset($Set['id'])) $Set['name'] =$Set['id'];
		
		if (!isset($Set['cols'])) $Set['cols'] =60;
		if (!isset($Set['rows'])) $Set['rows'] =5;
		
		$value =isset($Set['value']) ?$Set['value'] :'';
		$this->Children[] =array('textarea', 
			self::Label($Label),
			"<textarea".self::Attributes($Set).">{$value}</textarea>",
			self::Note($Note)
		);
	}
	public function Submit($Set =array(), $Label =array(), $Note =array())
	{
		$this->hasSubmit =true;
		//$this->Children[] =array($Caption, "<input type='submit' id='{$Name}' name='{$Name}' value='$Value'/>", $Rem);

		//if (isset($Set['id']) && count($Label)>0) $Label['for'] =$Set['id'];
		//if (empty($Label)) {$Label =array('caption'=>'&nbsp;');}
		$Set['name'] ='submit';
		$Set['type'] ='submit';
		$this->Children[] =array('submit', self::Label($Label),self::Input($Set),self::Note($Note));
	}
	public function Flush($Return =null)
	{
		if (!$this->hasSubmit)$this->Submit(array('value'=>'提交'));
		$c =$this->Sets['caption'];
		unset($this->Sets['caption']);
		
		$this->Sets['class'] =isset($this->Sets['class']) ?'cssform '.$this->Sets['class'] :'cssform';
		$h =array();
		$h[] ="<form".self::Attributes($this->Sets).">";
		if (count($this->Hidden)) $h =array_merge($h, $this->Hidden);
		$h[] ="<fieldset>";
		if (!empty($c)) {
			$h[] ="\t<legend><span>{$c}</span></legend>";
		}
		$h[] ="\t<ol>";
		foreach ($this->Children as $child) {
			$type =array_shift($child);
			$h[] ="\t\t<li class='{$type}'>".implode('', $child)."</li>";
		}
		$h[] ="\t</ol>";
		$h[] ="</fieldset>";
		$h[] ="</form>";

		if ($Return) return implode("\n", $h); else echo implode("\n", $h);
		return true;
	}
}

?>