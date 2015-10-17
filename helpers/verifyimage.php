<?php

class hVerifyImage{
	private $Bmp =null;
	private $Width, $Height;
	private $Background=array(0,0,0), $Border=array(255,255,255);
	private $valueName, $timeName;
	public function __construct($SessionValueName='verify.value', $SessionTimeName='verify.time')
	{
		$this->valueName =$SessionValueName;
		$this->timeName =$SessionTimeName;
		//session_start();
		//return $this;
	}
	public static function factory($SessionValueName='verify.value', $SessionTimeName='verify.time'){
		return new hVerifyImage($SessionValueName, $SessionTimeName);
	}
	public function __destruct()
	{
		imagedestroy($this->Bmp);
	}
	public function has(){
		return true;
	}
	public function __toString(){
		return $this->Flush();
	}
	/**
	 * 调整图片尺寸
	 *
	 * @param int $Width 图片宽
	 * @param int $Height 图片高
	 * @return object 对象本身
	 */
	public function Size($Width, $Height)
	{
		if (!is_null($this->Bmp)) imagedestroy($this->Bmp);
		$this->Width =$Width;
		$this->Height =$Height;
		$this->Bmp =imagecreate($Width, $Height);
		return $this;
	}
	/**
	 * 颜色数字到颜色数组
	 *
	 * @param int $r 红 0-255
	 * @param int $g 绿
	 * @param int $b 蓝
	 * @return array
	 */
	private function RGB2Color($r, $g, $b)
	{
		return array($r, $g, $b);
	}
	/**
	 * 绘制透明背景
	 *
	 * @return 颜色本身
	 */
	public function DrawTransparentBackground()
	{
		//$this->Background =$Background;
		$Background =array(0,0,0);
		$bk =imagecolorallocate($this->Bmp, $Background[0], $Background[1], $Background[2]);
		//$tran =
		imagecolortransparent($this->Bmp,$bk);
		imagefilledrectangle($this->Bmp, 0, 0, $this->Width-1, $this->Height-1, $bk);
		return $this;

	}
	/**
	 * 绘制背景
	 *
	 * @param array $Background 背景色，颜色数组 红 绿 蓝
	 * @param array $Border 边框颜色，颜色数组
	 * @return object 对象本身
	 */
	public function DrawBackground($Background=array(0,0,0), $Border=array(0,0,0))
	{
		$this->Background =$Background;
		$this->Border =$Border;

		$bk =imagecolorallocate($this->Bmp, $Background[0], $Background[1], $Background[2]);
		//$tran =
		imagecolortransparent($this->Bmp,$bk);
		imagefilledrectangle($this->Bmp, 0, 0, $this->Width-1, $this->Height-1, $bk);

		//$br =imagecolorallocate($this->Bmp, $Border[0], $Border[1], $Border[2]);
		//imagerectangle($this->Bmp, 0, 0, $this->Width-1, $this->Height-1, $br);

		return $this;
	}
	/**
	 * 绘制覆盖层内容
	 *
	 * @param int $Dot 杂点数目
	 * @return object 对象本身
	 */
	public function DrawMark($Dot=20/*, $Char=10*/)
	{
		if ($Dot >0) {
			if ($Dot >100) $Dot =100;
			for ($i=1;$i<=$Dot;$i++){
				$c =imagecolorallocate($this->Bmp,mt_rand(50,255),mt_rand(50,255),mt_rand(50,255));
				//$x =mt_rand(0,50);
				//$c =imagecolorallocate($this->Bmp,mt_rand(100,230),mt_rand(100,230),mt_rand(100,230));
				imagesetpixel($this->Bmp,mt_rand(2,$this->Width-2), mt_rand(2,$this->Height-2),$c);
			}
		}
		return $this;
	}
	/**
	 * 制作问题和答案，10以内加、减、乘法。
	 *
	 * @param int $Max 最大可能出现的数字
	 * @return array r 结果 m 问题
	 */
	private function MakeQuestion($Max)
	{
		$a =rand(1, $Max);
		$b =rand(1, $Max);
		$o =rand(1,3);
		switch ($o) {
			case 2:
				if ($a>$b) {
					$r =$a-$b;
					$m =$a.'-'.$b.'=?';
				} else {
					$r =$b-$a;
					$m =$b.'-'.$a.'=?';
				}

				break;
			case 3:
				$r =$a*$b;
				$m =$a.'*'.$b.'=?';
				break;
			case 4:
				$r =$a/$b;
				$m =$a.'/'.$b.'=?';
				break;
			case 1:
			default:
				$r =$a+$b;
				$m =$a.'+'.$b.'=?';
				break;
		}
		return array('r'=>$r, 'm'=>$m);
	}
	/**
	 * 返回对比色
	 *
	 * @param int $Color 0-255 单位颜色
	 * @return int
	 */
	static public function ClearColor($Color)
	{
		return ($Color>127) ?rand(0,127) :rand(127, 255);
		//return ($Color>127) ?rand(0,127) :rand(127, 255);
	}
	/**
	 * 绘制问题到图片
	 *
	 * @param int $MaxNum 算式中最大可能出现的数字
	 * @return object 对象本身
	 */
	public function DrawQuestion($MaxNum =10)
	{
		$Info =self::MakeQuestion($MaxNum);
		$_SESSION[$this->valueName] =$Info['r'];
		$_SESSION[$this->timeName] =time();
		$Code =$Info['m'];
		$CodeNum =strlen($Code);
		for ($i=0;$i<strlen($Code);$i++){
			$r =self::ClearColor($this->Background[0]);
			$g =self::ClearColor($this->Background[1]);
			$b =self::ClearColor($this->Background[2]);

			$c =imagecolorallocate($this->Bmp,$r,$g,$b);
			imageString($this->Bmp,mt_rand(3,5),$i*$this->Width/$CodeNum+mt_rand(1,4),mt_rand(1,4),$Code[$i],$c);
    	}
		return $this;
	}
	/**
	 * 生成随机位数的数字
	 *
	 * @param int $Length 位数
	 * @return object 对象本身
	 */
	private function MakeCode($Length)
	{
		$r ='';
		mt_srand((double)microtime() * 1000000);
		for($i=0;$i<$Length;$i++){
			$r .= mt_rand(1,6);
		}
		return $r;
	}
	/**
	 * 绘制指定个数的数字到图片中
	 *
	 * @param int $CodeNum 图片个数
	 * @return object 对象本身
	 */
	public function DrawNum($CodeNum =4)
	{
		/*
		if(isset($_SESSION[$this->timeName]) && isset($_SESSION[$this->valueName]) && ((int)$_SESSION[$this->timeName] >time() -30))
			$Code =$_SESSION[$this->valueName];
		else {
			$Code =self::MakeCode($CodeNum);
			$_SESSION[$this->valueName] =$Code;
			$_SESSION[$this->timeName] =time();
		}*/
		$Code =self::MakeCode($CodeNum);
		$_SESSION[$this->valueName] =$Code;
		$_SESSION[$this->timeName] =time();
		
		for ($i=0;$i<strlen($Code);$i++){
			$c =imageColorAllocate($this->Bmp,mt_rand(50,255),mt_rand(0,120),mt_rand(50,255));
			//$x =mt_rand(100,150);
			//$c =imagecolorallocate($this->Bmp,mt_rand(100,255),mt_rand(100,255),mt_rand(100,255));
			imageString($this->Bmp,mt_rand(3,5),$i*$this->Width/$CodeNum+mt_rand(1,4),mt_rand(1,4),$Code[$i],$c);
    	}
		return $this;
	}
	/**
	 * 输出图片
	 *
	 * @param string $Type 输出图片类型
	 */
	public function Flush($Type ='gif'/*, $PathName =null*/)
	{
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pramga: no-cache");
		switch ($Type) {
			case 'png':
				header("Content-type: image/png");
				imagepng($this->Bmp);
				break;
			case 'gif':
			default:
				header("Content-type: image/gif");
				imagegif($this->Bmp);
				break;
		}
	}
}

?>