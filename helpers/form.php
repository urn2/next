<?php (defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

class hForm {
	private $Hidden;
	private $Children;
	private $hasSubmit =false;

	public $Action;

	public $Sets =array('caption'=>'', 'method'=>'post', 'class'=>'form');
	
	private static function Language($KeyWord) {
		if (is_array($KeyWord)) return $KeyWord;
		if (strpos($KeyWord, '.') ==false) return $KeyWord;
		return Next::Language($KeyWord);
	}
	
	/**
	 * 创建一个表单类
	 * @param string $Action 提交链接
	 * @param array $Sets = [caption][id][method][name] 表单设定内容
	 */
	public function __construct($Action='', $Sets=array()){
		$this->Action =$Action;
		$this->Sets['action'] =$Action;
		if (is_array($Sets) && count($Sets)>0) {
			foreach ($Sets as $k => $v) {
				$this->Sets[$k] =$v;
			}
		} elseif (is_string($Sets)) {
			$this->Sets['caption'] =self::Language($Sets);
		}
		if (!isset($this->Sets['name']) && isset($this->Sets['id'])) {
			$this->Sets['name'] =$this->Sets['id'];
		}
		$this->hasSubmit =false;
	}
	private function Children($Caption, $Input='', $Rem='')
	{
		if (empty($Input)){
			$this->Children[] =array($Caption);
		}else {
			if (!empty($Rem)) {
				$Rem ='<span>'.self::Language($Rem).'</span>';
			}
			$this->Children[] =array($Caption, $Input, $Rem);
		}
	}
	public function Hidden($Name, $Value)
	{
		$this->Hidden[] ="<input type='hidden' name='{$Name}' value='$Value'/>";
		return $this;
	}
	public function Info($Info)
	{
		$this->Children('<span class="info">'.self::Language($Info).'</span>');
		return $this;
	}
	public function File($Name, $Value, $Caption='', $Rem='')
	{
		$this->Sets['enctype'] ='multipart/form-data';
		$_c =self::Language($Caption);
		$this->Children("<label for='{$Name}'>{$_c}</label>", "<input type='file' name='{$Name}' value='$Value'/>", $Rem);
		return $this;
	}
	public function Text($Name, $Value, $Caption='', $Rem='')
	{
		$_c =self::Language($Caption);
		$this->Children("<label for='{$Name}'>{$_c}</label>", "<input type='text' name='{$Name}' value='$Value'/>", $Rem);
		return $this;
	}
	public function Password($Name, $Value, $Caption='', $Rem='')
	{
		$_c =self::Language($Caption);
		$this->Children("<label for='{$Name}'>{$_c}</label>", "<input type='password' name='{$Name}' value='$Value'/>", $Rem);
		return $this;
	}
	public function Radio($Name, $Option, $Captionid=0, $Valueid=1, $Sel =null, $Caption='', $Rem='')
	{
		$h =array();
		foreach ($Option as $k =>$o) {
			if (is_array($o)) {
				$sValue =$o[$Valueid];
				$sCaption =$o[$Captionid];
			} else {
				$sValue =$k;
				$sCaption =$o;
			}
			if (!is_null($Sel) && $sValue ==$Sel) {
				$bysel =" checked='checked'";
			} else $bysel ='';
			$sCaption =Next::Language($sCaption);
			$h[] ="\t<input type='radio' name='{$Name}' value='{$sValue}'{$bysel} />&nbsp;{$sCaption}\n";
		}
		$_c =self::Language($Caption);
		$this->Children("<label for='{$Name}'>{$_c}</label>", implode("\n", $h) , $Rem);
		return $this;
	}
	public function Checkbox($Name, $Option, $Captionid=0, $Valueid=1, $Sel =array(), $Caption='', $Rem='')
	{
		$h =array();
		foreach ($Option as $k=>$o) {
			if (is_array($o)) {
				$sValue =$o[$Valueid];
				$sCaption =$o[$Captionid];
			} else {
				$sValue =$k;
				$sCaption =$o;
			}
			if (!is_null($Sel) && in_array($sValue, $Sel)) {
				$bysel =" checked='checked'";
			} else $bysel ='';
			$sCaption =Next::Language($sCaption);
			$h[] ="\t<input type='checkbox' name='{$Name}[]' value='{$sValue}'{$bysel} />&nbsp;{$sCaption}\n";
		}
		$_c =self::Language($Caption);
		$this->Children("<label for='{$Name}'>{$_c}</label>", implode("\n", $h), $Rem);
		return $this;
	}
	public function Select($Name, $Option, $Captionid=0, $Valueid=1, $Sel =null, $Caption='', $Rem='', $Group =null, $Groupid =null)
	{
		$hasGroup =(!is_null($Group) && !is_null($Groupid));
		$og ='';
		$eg =false;
		$h =array();
		$h[] ="\t<select name='{$Name}'>\n";
		foreach ($Option as $k=>$o) {
			if (is_array($o)){
				$sValue =$o[$Valueid];
				$sCaption =$o[$Captionid];
			} else {
				$sValue =$k;
				$sCaption =$o;
			}
			$bysel =(!is_null($Sel) && $sValue ==$Sel) ?" selected='selected'" :'';
			$sCaption =Next::Language($sCaption);
			if ($hasGroup && $og !=='' && $og !==$o[$Groupid]){
				$h[] ="\t</optgroup>\n";
				$og ="";
			}
			if ($hasGroup && isset($Group[$o[$Groupid]]) && $og =='' && $og !==$o[$Groupid]){
				$h[] ="\t<optgroup label='{$Group[$o[$Groupid]]}'>\n";
				$og =$o[$Groupid];
				$eg =false;
			}
			$h[] ="\t\t<option value='{$sValue}'{$bysel}>{$sCaption}</option>\n";
		}
		if (!empty($og)){
			$h[] ="\t</optgroup>\n";
		}
		$h[] ="\t</select>\n";
		$_c =self::Language($Caption);
		$this->Children("<label for='{$Name}'>{$_c}</label>", implode("\n", $h), $Rem);
		return $this;
	}
	public function Textarea($Name, $Value, $Caption='', $Rem='', $Cols=60, $Rows=5)
	{
		$_c =self::Language($Caption);
		$this->Children("<label for='{$Name}'>{$_c}</label>", "<textarea name='{$Name}' size='400' cols='{$Cols}' rows='{$Rows}'>$Value</textarea>", $Rem);
		return $this;
	}
	public function Submit($Name='submit', $Value='submit', $Caption='', $Rem='')
	{
		$this->hasSubmit =true;
		$_c =self::Language($Caption);
		$_v =self::Language($Value);
		$this->Children($_c, "<input type='submit' name='{$Name}' value='$_v'/>", $Rem);
		return $this;
	}
	public function Flush($Return =null)
	{
		if (!$this->hasSubmit)$this->Submit('提交');
		
		$class =$this->Sets['class'];
		$caption =self::Language($this->Sets['caption']);
		unset($this->Sets['class'], $this->Sets['caption']);
		$h =array();
		$h[] ="<form";// method='{$this->Sets['method']}' action='{$this->Action}' name='{$this->Sets['name']}' id='{$this->Sets['id']}' enctype='multipart/form-data'>";
		foreach ($this->Sets as $n => $v) {
			$h[] =" {$n}='{$v}'";
		}
		$h[] =" class='{$class}'";
		$h[] =">";
		if (count($this->Hidden)){
			$h[] ="\n".implode("\n", $this->Hidden)."\n";
		}
		$h[] ="<fieldset>";
		$h[] ="<legend>{$caption}</legend>";
		//$h[] ="<ol>";
		foreach ($this->Children as $child) {
			$h[] ="<p>".implode('', $child)."</p>";
		}
		//$h[] ="</ol>";
		$h[] ="</fieldset>";
		$h[] ="</form>";

		if ($Return) return implode("\n", $h); else echo implode("\n", $h);
		return true;
	}
	public function Table($Return =null)
	{
		if (!$this->hasSubmit)$this->Submit('提交');
		
		$class =$this->Sets['class'];
		$caption =self::Language($this->Sets['caption']);
		unset($this->Sets['class'], $this->Sets['caption']);
		$h ="<form";// method='{$this->Sets['method']}' action='{$this->Action}' name='{$this->Sets['name']}' id='{$this->Sets['id']}' enctype='multipart/form-data'>";
		foreach ($this->Sets as $n => $v) {
			$h .=" {$n}='{$v}'";
		}
		$h .=" class='{$class}'";
		$h .=">";
		if (count($this->Hidden)){
			$h .="\n".implode("\n", $this->Hidden)."\n";
		}
		$h .=hHtml::Table($this->Children, array('col'=>false, 'title'=>$caption, $class), true);
		$h .="</form>\n";

		if ($Return) return $h; else echo $h;
		return true;
	}
}

?>
