<?php

class hText2Image{
	const BaseChar ='傅';
	private $Bmp =null;
	private $Width, $Height;
	private $Background=array(0,0,0), $Border=array(255,255,255), $Color=array(0,0,0);
	private $Padding =array(0,0,0,0);//top left bottom right
	private $Cache =false;
	private $Text =array();
	private $Fonts =array(
	0=>array(
		'file'=>'msyh.ttf',
		'size'=>10.5,
		'padding'=>array(0,0,0,0),
		'area'=>array(0 => -2, 1 => 4, 2 => 13, 3 => 4, 4 => 13, 5 => -12, 6 => -2, 7 => -12, ),
		'height'=>16,
		'width'=>15,
	));
	private $Client =array(0,0);
	
	private $Debug =false;
	private $Begin =0;
	public function __construct()
	{
		$this->Begin =microtime(true);
	}
	public function __destruct()
	{
		imagedestroy($this->Bmp);
	}
	public function Debug()
	{
		$this->DebugColor =imagecolorallocate($this->Bmp, 255, 0, 0);
		$this->Debug =true;
		return $this;
	}
	public function Cache($CacheName, $Type='gif')
	{
		$this->Cache =$CacheName;
		if (is_file($this->Cache)){
			switch ($Type){
				case 'png':$this->Bmp =imagecreatefrompng($this->Cache);break;
				case 'gif':
				default:$this->Bmp =imagecreatefromgif($this->Cache);break;
			}
			$this->Width =imagesx($this->Bmp);
			$this->Height =imagesy($this->Bmp);
			return true;
		} else return false;
	}
	/**
	 * 调整图片尺寸
	 *
	 * @param int $Width 图片宽
	 * @param int $Height 图片高
	 * @param array $Padding
	 * @return object 对象本身
	 */
	public function Size($Width, $Height, $Padding =array(0,0,0,0))
	{
		if (!is_null($this->Bmp)) imagedestroy($this->Bmp);
		$this->Width =$Width;
		$this->Height =$Height;
		//$this->Bmp =imagecreate($Width, $Height);
		$this->Bmp =imagecreatetruecolor($Width, $Height);
		$this->Padding =$Padding;
		return $this;
	}
	/**
	 * 指定字体
	 *
	 * @param string $FileName 字体文件名，绝对路径
	 * @param int $Size 字体大小
	 * @param array $Padding 单字四周距离 上 右 下 左
	 */
	public function Font($FileName, $Size=12, $Padding =array(0,0,0,0), $Begin=0)
	{
		$this->Fonts[$Begin] =array(
		'file'=>$FileName,
		'size'=>$Size,
		'padding'=>$Padding,
		);
		$this->Fonts[$Begin]['area'] =imagettfbbox($this->Fonts[$Begin]['size'], 0, $this->Fonts[$Begin]['file'], hText2Image::BaseChar);
		$this->Fonts[$Begin]['height'] =$this->Fonts[$Begin]['area'][1] -$this->Fonts[$Begin]['area'][7];
		$this->Fonts[$Begin]['width'] =$this->Fonts[$Begin]['area'][2] -$this->Fonts[$Begin]['area'][0];
		if ($Begin ==0){
			//$this->Client[0] +=$this->Fonts[$Begin]['width']/2;
			//$this->Client[1] +=$this->Fonts[$Begin]['height']/2;
		}
	}
	private function RGB2Color($r, $g, $b)
	{
		return array($r, $g, $b);
	}
	/**
	 * 绘制背景
	 *
	 * @param array $Background 背景色，颜色数组 红 绿 蓝
	 * @param array $Border 边框颜色，颜色数组
	 * @param boolean $Transparent 背景是否透明，true忽略$Background
	 * @param boolean $NoBorder 是否拥有边框，true忽略$Border
	 * @return object 对象本身
	 */
	public function DrawBackground($Background=array(0,0,0), $Border=array(0,0,0), $Transparent=false, $NoBorder=false)
	{
		$this->Background =imagecolorallocate($this->Bmp, $Background[0], $Background[1], $Background[2]);
		//imagefilledrectangle($this->Bmp, 0, 0, $this->Width-1, $this->Height-1, $this->Background);
		imagefill($this->Bmp, 0, 0, $this->Background);
		if ($Transparent) imagecolortransparent($this->Bmp,$this->Background);

		$this->Border =imagecolorallocate($this->Bmp, $Border[0], $Border[1], $Border[2]);
		if (!$NoBorder){
			$this->Client[0] +=2;
			$this->Client[1] +=2;
			imagerectangle($this->Bmp, 0, 0, $this->Width-1, $this->Height-1, $this->Border);
		}
		return $this;
	}
	/**
	 * 输入文本并计算整体高度和宽度
	 *
	 * @param string $Text 原始内容
	 */
	public function Text($Text)
	{
		$font =$this->Fonts[0];
		
		$this->Text =$this->FormatText($Text);
		$lines =count($this->Text);
		
		$ch =$font['height'] +$font['padding'][0] +$font['padding'][2];
		$h =$ch*$lines +$font['padding'][0] +$font['padding'][2];
		if ($this->Height <$h){
			$this->Height =$h;
			$this->Size($this->Width, $this->Height, $this->Padding);
		}
	}
	/**
	 * 格式化文本
	 *
	 * @param string $Text 原始内容
	 * @return string
	 */
	public function FormatText($Text)
	{
		$font =$this->Fonts[0];
		
		$cw =$font['width'] +$font['padding'][1] +$font['padding'][3];
		$w =$this->Width -$font['padding'][1] -$font['padding'][3];
		$n =(int)ceil($w /$cw);
		
		$r =array(' ');
		//$rr =array(' ', ' ');
		$rr =array();
		$len =mb_strlen($Text, 'UTF-8');
		for ($i=0;$i<$len;$i++){
			$c =mb_substr($Text, $i, 1, 'UTF-8');
			if ($c =="\n"){
				$r[] =$rr;
				$rr =array();
				continue;
			}
			$rr[] =$c;
			if (count($rr)>$n-2){
				$r[] =$rr;
				$rr =array();
				continue;
			}
		}
		$r[] =$rr;
		return $r;
	}
	/**
	 * 绘制文字到图片中
	 *
	 * @param array $Color 颜色数组
	 * @return object 对象本身
	 */
	public function DrawText($Color=array(0,0,0))
	{
		//$GBKArea =array();
		//include(NS::Path('data/gbkarea.php'));
		
		$font =$this->Fonts[0];
		
		$this->Color =imagecolorallocate($this->Bmp, $Color[0], $Color[1], $Color[2]);

		$cw =$font['width'] +$font['padding'][1] +$font['padding'][3];
		$ch =$font['height'] +$font['padding'][0] +$font['padding'][2];
		$w =$this->Width -$this->Padding[1] -$this->Padding[3];
		$n =$w /$cw;
		
		foreach ($this->Text as $line => $ca) {
			$y =$this->Client[1] +$line *$ch;
			foreach ($ca as $num => $c) {
				//$area =isset($GBKArea[$c]) ?$GBKArea[$c] :0;
				//$font =(isset($this->Fonts[$area])) ?$this->Fonts[$area] :$this->Fonts[0];
				$x =$this->Client[0] +$num *$cw;
				//$this->CharArea =
				imagettftext($this->Bmp, $font['size'], 0, $x, $y, $this->Color, $font['file'], $c);
			}
		}
		return $this;
	}
	public function DrawInfo()
	{
		$args =func_get_args();
		$str =implode(' ', $args);
		$Color =array(30,30,30);
		$this->Color =imagecolorallocate($this->Bmp, $Color[0], $Color[1], $Color[2]);
		imagestring($this->Bmp, 0, 0, $this->Height -15 , $str, $this->Color);
	}
	public function DrawMarkLine($Set, $Color =array(230,230,230))
	{
		//$Set =date('siHdmy');
		if ($this->Debug) {
			imagestring($this->Bmp, 1, 0, 10, $Set, $this->DebugColor);
		}
		$str =str_split($Set, 2);
		$lines =array();
		$base =0;
		foreach ($str as $_str) {
			$x =str_split($_str);
			$base +=$x[0];
			$lines[$base] =(int)$x[1];
		}
		$this->MarkLineColor =imagecolorallocate($this->Bmp, $Color[0], $Color[1], $Color[2]);
		$font =$this->Fonts[0];
		$cw =$font['width'] +$font['padding'][1] +$font['padding'][3];
		$ch =$font['height'] +$font['padding'][0] +$font['padding'][2];
		$w =$this->Width -$this->Padding[1] -$this->Padding[3];
		$n =$w /$cw;
		$h =$this->Height -$this->Padding[0] -$this->Padding[2];
		$nh =$h /$ch;
		for ($line =1; $line <=$nh; $line ++){
			$i =$line % $base;
			//if ($i ==0){
			//	$y =$this->Client[1] +($line-1) *$ch+$ch-$ch*1/5;
			//	imageline($this->Bmp, 0, $y, 10000, $y, $this->MarkLineColor);
			//}
			if (isset($lines[$i])){
				$y =$this->Client[1] +($line-1) *$ch+$ch-$ch*1/5;
				//$x =$this->Client[0] +($lines[$i]-1) *$cw+$cw/2;
				$x =$this->Client[0];
				//imageline($this->Bmp, $x, $y, $x +100, $y, $this->DebugColor);
				$nw =$lines[$i];
				while ($nw <$n){
					$nw1 =rand(1, 4);
					//$nw1 =rand(1, $n -$nw);
					$x =($nw-1) *$cw+$cw/2;
					$x1 =($nw-1+$nw1) *$cw+$cw/2;
					imageline($this->Bmp, $x, $y, $x1, $y, $this->MarkLineColor);
					$nw +=$nw1+rand(1, 4);
					//$nw +=$nw1+rand(1, $n -$nw);
				}
			}
		}
		return $this;
	}
	/**
	 * 输出图片
	 *
	 * @param string $Type 输出图片类型
	 */
	public function Flush($Type ='gif', $PathName =null)
	{
		if ($this->Debug && empty($PathName)){
			$s =sprintf('%.4f s', microtime(true) -$this->Begin);
			imagestring($this->Bmp, 1, 0, 0, $s, $this->DebugColor);
		}
		header("Pragma:no-cache");
		header("Cache-control:no-cache");
		switch ($Type) {
			case 'png':
				header("Content-type: image/png");
				if (!!$PathName) imagepng($this->Bmp, $PathName);
					else imagepng($this->Bmp);
				break;
			case 'gif':
			default:
				header("Content-type: image/gif");
				if (!!$PathName) imagegif($this->Bmp, $PathName);
					else imagegif($this->Bmp);
				break;
		}
	}
}

?>