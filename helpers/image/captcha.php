<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2015/02/26 026
 * Time: 09:37
 */
class hImageCAPTCHA extends hImage{
	var $session = ['captcha.value', 'captcha.time'];
	var $size = [0, 0];//图片尺寸
	var $color =[];
	var $font =[];
	var $font_default =[
		1=>[5, 8, 0]
		,2=>[5, 11, 2]
		,3=>[6, 11, 2]
		,4=>[8, 15, 1]
		,5=>[8, 13, 2]
	];

	public static function factory($config=[]){
		return new self($config);
	}
	public function __construct($config=[]){
		if(isset($config['session'])) $this->session = $config['session'];
		if(isset($config['scale'])) $this->scale = $config['scale'];

		//session_start();
		//return $this;
	}
	/**
	 * 调整图片尺寸
	 *
	 * @param int $Width 图片宽
	 * @param int $Height 图片高
	 * @return hImageCAPTCHA 对象本身
	 */
	public function size($Width, $Height, $TrueColor =false){
		if(!is_null($this->Canvas)) imagedestroy($this->Canvas);
		$this->size = [$Width, $Height];
		$this->Canvas =($TrueColor) ?imagecreatetruecolor($Width, $Height) :imagecreate($Width, $Height);
		return $this;
	}

	public function font($FontFile){
		$this->font =$FontFile;
		return $this;
	}

	/**
	 * 返回对比色
	 *
	 * @param int $Color 0-255 单位颜色
	 * @return int
	 */
	private function clearColor($Color){
		return ($Color>127) ?rand(0, 127) :rand(127, 255);
		//return ($Color>127) ?rand(0,127) :rand(127, 255);
	}
	/**
	 * 绘制背景
	 *
	 * @param array $Background 背景色，颜色数组 红 绿 蓝
	 * @param array $Border 边框颜色，颜色数组
	 * @return hImageCAPTCHA 对象本身
	 */
	public function drawBackground($Background =null, $Border = null){
		//$this->color['background'] =$Background;

		$b =$this->allocateColor($Background);
		$this->color['background'] =$b;
		imagefilledrectangle($this->Canvas, 0, 0, $this->size[0] - 1, $this->size[1] - 1, $b[0]);

		//$br =imagecolorallocate($this->Canvas, $Border[0], $Border[1], $Border[2]);
		//imagerectangle($this->Canvas, 0, 0, $this->size[0]-1, $this->size[1]-1, $br);

		return $this;
	}
	/**
	 * 绘制覆盖层内容
	 *
	 * @param int $Dot 杂点数目
	 * @return hImageCAPTCHA 对象本身
	 */
	public function drawMark($Dot = 20/*, $Char=10*/){
		if($Dot>0){
			if($Dot>100) $Dot = 100;
			for($i = 1; $i<=$Dot; $i++){
				//$c = imagecolorallocate($this->Canvas, mt_rand(50, 255), mt_rand(50, 255), mt_rand(50, 255));
				$c =$this->allocateColor(['rgb', mt_rand(50, 255), mt_rand(50, 255), mt_rand(50, 255)]);
				//$x =mt_rand(0,50);
				//$c =imagecolorallocate($this->Canvas,mt_rand(100,230),mt_rand(100,230),mt_rand(100,230));
				imagesetpixel($this->Canvas, mt_rand(2, $this->size[0] - 2), mt_rand(2, $this->size[1] - 2), $c[0]);
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
	private function makeQuestion($Max){
		$a = rand(1, $Max);
		$b = rand(1, $Max);
		$o = rand(1, 3);
		switch($o){
			case 2:
				if($a>$b){
					$r = $a - $b;
					$m = $a.'-'.$b.'=?';
				}
				else{
					$r = $b - $a;
					$m = $b.'-'.$a.'=?';
				}

				break;
			case 3:
				$r = $a*$b;
				$m = $a.'*'.$b.'=?';
				break;
			case 4:
				$r = $a/$b;
				$m = $a.'/'.$b.'=?';
				break;
			case 1:
			default:
				$r = $a + $b;
				$m = $a.'+'.$b.'=?';
				break;
		}
		return ['r' => $r, 'm' => $m];
	}
	/**
	 * 绘制问题到图片
	 *
	 * @param int $MaxNum 算式中最大可能出现的数字
	 * @param array $Config 配置
	 * @return hImageCAPTCHA 对象本身
	 */
	public function drawQuestion($MaxNum = 10, $Config=[]){
		$Info = $this->makeQuestion($MaxNum);
		$Config['session'] =$Info['r'];
		return $this->drawString($Info['m'], $Config);
	}
	/**
	 * 生成随机位数的数字
	 *
	 * @param int $Length 位数
	 * @return hImageCAPTCHA 对象本身
	 */
	private function makeCode($Length){
		$r = '';
		mt_srand((double)microtime()*1000000);
		for($i = 0; $i<$Length; $i++){
			$r .= mt_rand(1, 9);
		}
		return $r;
	}
	/**
	 * 绘制指定个数的数字到图片中
	 *
	 * @param int $CodeNum 图片个数
	 * @param array $Config 配置
	 * @return hImageCAPTCHA 对象本身
	 */
	public function drawNum($CodeNum = 4, $Config=[]){
		$Code = $this->makeCode($CodeNum);
		return $this->drawString($Code, $Config);
	}
	/**
	 * 绘制文本到图片上
	 *
	 * @param       $String 待绘制内容
	 * @param array $Config [session=>存储内容, type=>绘制方式]
	 * @return $this
	 */
	public function drawString($String, $Config=[]){
		$str =trim($String);
		$len =strlen($str);

		$SessionValue =(isset($Config['session'])) ?$Config['session'] :null;
		if($SessionValue !==false){
			$_SESSION[$this->session[0]] = ($SessionValue==null) ?$str :$SessionValue;
			$_SESSION[$this->session[1]] = time();
		}

		$Type =(isset($Config['type'])) ?$Config['type'] :'rand';
		switch($Type){
			case 'rand':
				for($i = 0; $i<$len; $i++){
					$color =$this->allocateColor(['rgb', mt_rand(50, 255), mt_rand(0, 120), mt_rand(50, 255)]);
					$font =mt_rand(3, 5);
					$fb =$this->font_default[$font];
					$w =$this->size[0]/$len;
					$l =($fb[0] <$w) ?mt_rand(0, $w -$fb[0]) :0;
					$x =$i*$w + $l;
					$y =mt_rand(1, $this->size[1] -$fb[1]-$fb[2]-1);
					imagestring($this->Canvas, $font, $x, $y, $str[$i], $color[0]);
				}
				break;
			default:
				$color =$this->allocateColor(['rgb', mt_rand(50, 255), mt_rand(0, 120), mt_rand(50, 255)]);
				imagestring($this->Canvas, 3, 1, 1, $str, $color[0]);
				break;
		}
		return $this;
	}
}