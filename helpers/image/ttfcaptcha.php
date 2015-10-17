<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2015/02/26 026
 * Time: 09:37
 */
class hImageTTFCAPTCHA extends hImageCAPTCHA{
	var $fonts =[];
	var $scale =2;

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
	 * @param bool $TrueColor
	 * @return hImageCAPTCHA 对象本身
	 */
	public function size($Width, $Height, $TrueColor =false){
		if(!is_null($this->Canvas)) imagedestroy($this->Canvas);
		$this->size = [$Width, $Height];
		$this->Canvas =($TrueColor) ?imagecreatetruecolor($Width*$this->scale, $Height*$this->scale) :imagecreate($Width*$this->scale, $Height*$this->scale);
		return $this;
	}

	public function font($FontFile){
		$this->fonts[] =$FontFile;
		return $this;
	}

	public function drawLine($lineWidth =2, $Color=null){
		if(is_null($Color)){
			//$Color =$this->allocateColor(['rgb', mt_rand(50, 255), mt_rand(0, 120), mt_rand(50, 255)]);
			$_color =['rgb', mt_rand(50, 255), mt_rand(0, 120), mt_rand(50, 255)];
			if(isset($this->color['background']) && is_array($this->color['background'])){
				$_c =$this->color['background'][1];
				$_hsl =self::rgb2hsl($_c);
				array_unshift($_hsl, 'hsl');
				$_color =self::colorLightness($_hsl, 40);

				$s =(isset($_color[5])) ?$_color[5] :$_color[2];
				if($s <=0) $s=100;
				$_color[2] =$_color[5] =$s;

				$_color[1] =$_color[4] =rand(0, 360);
			}
			$Color =$this->allocateColor($_color);

		} else $Color =$this->allocateColor($Color);

		$x1 = $this->size[0]*$this->scale*rand(0,3)/10;
		$x2 = $this->size[0]*$this->scale*rand(7, 10)/10;
		$y1 = rand($this->size[1]*$this->scale*.40, $this->size[1]*$this->scale*.65);
		$y2 = rand($this->size[1]*$this->scale*.40, $this->size[1]*$this->scale*.65);
		$width = $lineWidth/2*$this->scale;
		for($i = $width*-1; $i<=$width; $i++){
			imageline($this->Canvas, $x1, $y1 + $i, $x2, $y2 + $i, $Color[0]);
		}
		return $this;
	}

	/**
	 * 返回校正后的字符位置
	 * @param        $font
	 * @param int    $size
	 * @param int    $angle
	 * @param int    $x 字符左上角坐标
	 * @param int    $y
	 * @param string $str 字符
	 * @return array [x, y, width, height]
	 */
	private function fixTTFPos($font, $size=14, $angle=0, $x=0, $y=0, $str='W'){
		$box =imagettfbbox($size, $angle, $font, $str);
		return [$x-$box[0], $y-$box[7], $box[2]-$box[0], $box[1]-$box[7]];
	}
	/**
	 * 颜色变亮或变暗
	 * @param     $color 颜色数组 hsl 或 hsv
	 * @param int $value (-100~100)
	 * @return array 颜色数组 hsl 或 hsv
	 */
	static public function colorLightness($color, $value=50){
		$n =2;
		if(is_string($color[0])) $n +=1;
		$v =(isset($color[$n+3])) ?$color[$n+3] :$color[$n];
		$v =($v >50) ?$v -$value:$v +$value;
		if($v >100) $v -=100;
		if($v <0) $v +=100;
		$color[$n] =$v;
		if(isset($color[$n+3])) $color[$n+3] =$v;
		return $color;
	}

	/**
	 * 绘制文本到图片上
	 * @param string $String 待绘制内容
	 * @param array $Config [session=>存储内容, type=>绘制方式]
	 * @return $this
	 */
	public function drawString($String, $Config=[]){
		if(count($this->fonts) ==0){
			$Config['type']=false;
			return parent::drawString('no font!', $Config);
		}
		$str =trim($String);
		$len =strlen($str);

		$SessionValue =(isset($Config['session'])) ?$Config['session'] :null;
		if($SessionValue !==false){
			$_SESSION[$this->session[0]] = ($SessionValue==null) ?$str :$SessionValue;
			$_SESSION[$this->session[1]] = time();
		}

		$font =$this->fonts[array_rand($this->fonts)];
		$left =0;
		$_color =['rgb', mt_rand(50, 255), mt_rand(0, 120), mt_rand(50, 255)];

		if(isset($this->color['background']) && is_array($this->color['background'])){
			$_c =$this->color['background'][1];
			$_hsl =self::rgb2hsl($_c);
			array_unshift($_hsl, 'hsl');
			$_color =self::colorLightness($_hsl, 50);

			$s =(isset($_color[5])) ?$_color[5] :$_color[2];
			if($s <=0) $s=100;
			$_color[2] =$_color[5] =$s;

			$h =(isset($_color[4])) ?$_color[4] :$_color[1];
			$h =$h-($h%60);
			$h =rand($h+120, $h+240);
			if($h >360) $h-=360;
			$_color[1] =$_color[4] =$h;
		}
		$color =$this->allocateColor($_color);


		$Type =(isset($Config['type'])) ?$Config['type'] :'center';
		switch($Type){
			case 'rand':
				for($i = 0; $i<$len; $i++){
					$size =is_array($font[1]) ?mt_rand($font[1][0], $font[1][1]) :$font[1];
					$size *=$this->scale;

					$angle =mt_rand(0, 5);

					$coods =$this->fixTTFPos($font[0], $size, $angle, 0, 0, $str[$i]);

					$w =$this->size[0]*$this->scale/$len;
					$l =($coods[2] <$w) ?mt_rand(0, $w -$coods[2]) :0;
					$x =$i*$w + $l+$coods[0];
					$y =($coods[3] <$this->size[1]*$this->scale-2) ?mt_rand(1, $this->size[1]*$this->scale -$coods[3]-1)+$coods[1] :$coods[1];

					imagettftext($this->Canvas, $size, $angle, $x, $y, $color[0], $font[0], $str[$i]);
					$left +=$coods[2];
				}
				break;
			case 'center':
			default:
				$size =is_array($font[1]) ?mt_rand($font[1][0], $font[1][1]) :$font[1];
				$size *=$this->scale;
				$angle =0;
				$coods =$this->fixTTFPos($font[0], $size, $angle, 0, 0, $str);

				$w =$this->size[0]*$this->scale;
				$l =($coods[2] <$w) ?($w -$coods[2])/2 :0;
				$x =0*$w + $l+$coods[0];
				$y =($coods[3] <$this->size[1]*$this->scale-2) ?($this->size[1]*$this->scale -$coods[3])/2+$coods[1] :$coods[1];
				imagettftext($this->Canvas, $size, $angle, $x, $y, $color[0], $font[0], $str);

				//if($Wave) $this->wave();
				break;
		}
		//if($this->scale >1) $this->reduceScale();
		return $this;
	}
	public function render(){
		if($this->scale >1) $this->reduceScale();
	}

	private function reduceScale(){
		$imResampled = imagecreatetruecolor($this->size[0], $this->size[1]);
		imagecopyresampled($imResampled, $this->Canvas, 0, 0, 0, 0, $this->size[0], $this->size[1], $this->size[0]*$this->scale, $this->size[1]*$this->scale);
		imagedestroy($this->Canvas);
		$this->Canvas = $imResampled;
	}

}