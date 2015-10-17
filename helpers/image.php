<?php
(defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

class hImage{
	protected $Canvas =null;
	protected $Error =[];
	protected $ContentType =['jpg' =>'jpeg', 'png' =>'png', 'gif' =>'gif'];
	protected $ExtType =[
		'jpeg' =>'jpg', 'jpg' =>'jpg', 'png' =>'png', 'gif' =>'gif'];
	protected $hasSendHeader =false;
	public $defOut ='png';

	public function __construct($config=[]){
		;
	}

	public function __destruct(){
		if (!is_null($this->Canvas)) imagedestroy($this->Canvas);
	}

	public static function factory($config=[]){
		return new self($config);
	}
	/**
	 * controller 直接输出
	 * @return bool
	 */
	public function has(){
		return !is_null($this->Canvas);
	}
	public function outType($type){
		$this->defOut =$type;
		return $this;
	}
	/**
	 * echo
	 * @return string
	 */
	public function __toString(){
		ob_start();
		$this->flush($this->defOut);
		$r =ob_get_contents();
		ob_end_clean();
		return $r;
	}
	/**
	 * 渲染
	 * @return array
	 */
	public function render(){
		if (!empty($this->Error)) return $this->Error;
	}
	/**
	 * 输出头部信息（直接输出图片）
	 * @param string $Type
	 * @param bool   $NoCache
	 */
	protected function sendHeader($Type ='gif', $NoCache =true){
		if ($this->hasSendHeader) return false;
		if ($NoCache){
			header('Pragma:no-cache');
			header('Cache-control:no-cache');
		}
		$Type =isset($this->ContentType[$Type]) ?$this->ContentType[$Type] :'gif';
		header('Content-type: image/' .$Type);
	}
	/**
	 * 输出图片或文件
	 * @param string $Type
	 * @param null   $Filename
	 * @return bool
	 */
	public function flush($Type ='jpg', $Filename =null){
		if (!empty($this->Error)) return $this->Error;
		$this->render();
		if (!empty($this->Error)) return $this->Error;
		if (!$Filename) $this->sendHeader($Type);
		switch ($Type){
			case 'jpg':
				$r =imagejpeg($this->Canvas, $Filename);
				break;
			case 'png':
				$r =imagepng($this->Canvas, $Filename);
				break;
			case 'gif':
			default:
				$r =imagegif($this->Canvas, $Filename);
				break;
		}
		if ($Filename) return $r;
	}

	/**
	 * load from file.
	 * @param unknown_type $File
	 * @return hImage
	 */
	public function loadFrom($File, $type=''){
		if(empty($type)){
			$_ext =explode('.', $File);
			$_ext =$_ext[count($_ext) -1];
		} else $_ext =$type;
		switch ($_ext){
			case 'jpg':
			case 'jpeg':
			case "image/jpeg":
			case "image/pjpeg":
				$this->Canvas =imagecreatefromjpeg($File);
				break;
			case 'png':
			case "image/png":
			case "image/x-png":
				$this->Canvas =imagecreatefrompng($File);
				break;
			case 'gif':
			case "image/gif":
				$this->Canvas =imagecreatefromgif($File);
				break;
			default:
				$this->Canvas =imagecreatefromstring($File);
				break;
		}
		return $this;
	}
	/**
	 * need center
	 * @param array $Box [left,top,width,height]
	 * @return hImage
	 */
	public function cut($Box){
		$im =imagecreatetruecolor($Box['width'], $Box['height']);
		imagecopy($im, $this->Canvas, 0, 0, $Box['left'], $Box['top'], $Box['width'], $Box['height']);
		imagedestroy($this->Canvas);
		$this->Canvas =$im;
		return $this;
	}

	public function resize($Width, $Height=null){
		if (!is_numeric($Height)){
			if (is_array($Width)){
				$Width =isset($Width['y']) ?$Width['y'] :$Width[1];
				$Height =isset($Width['x']) ?$Width['x'] :$Width[0];
			} else
				$Height =$Width;
		}
		$ix =imagesx($this->Canvas);
		$iy =imagesy($this->Canvas);
		/*
		if ($ix <=$Width &&$iy <=$Height) return $this;
		if ($ix >=$iy){
			$x =$Width;
			$y =$x *$iy /$ix;
		} else{
			$y =$Height;
			$x =$ix /$iy *$y;
		}*/
		$nc =imagecreatetruecolor($Width, $Height);
		//imagecopyresized($nc, $this->Canvas, 0, 0, 0, 0, floor($x), floor($y), $ix, $iy);
		imagecopyresampled($nc, $this->Canvas, 0, 0, 0, 0, floor($Width), floor($Height), $ix, $iy);
		imagedestroy($this->Canvas);
		$this->Canvas =$nc;
		return $this;

	}
	/**
	 * 最小尺寸 如 1024*768 的图片，缩放成200像素，按比例即 267*200
	 * @param number $Width
	 * @param number $Height
	 * @return hImage
	 */
	public function resizeMin($Width, $Height=null){
		if (!is_numeric($Height)){
			if (is_array($Width)){
				$Width =isset($Width['y']) ?$Width['y'] :$Width[1];
				$Height =isset($Width['x']) ?$Width['x'] :$Width[0];
			} else
				$Height =$Width;
		}
		$ix =imagesx($this->Canvas);
		$iy =imagesy($this->Canvas);

		if ($ix >=$iy){
			$y =$Height;
			$x =$ix /$iy *$y;
		} else{
			$x =$Width;
			$y =$x *$iy /$ix;
		}
		$this->resize($x, $y);
		return $this;
	}

	/**
	 * 最小尺寸 如 1024*768 的图片，缩放成200像素，按比例即 200*150
	 * @param number $Width
	 * @param number $Height
	 * @return hImage
	 */
	public function resizeMax($Width, $Height =null){
		if (!is_numeric($Height)){
			if (is_array($Width)){
				$Width =isset($Width['y']) ?$Width['y'] :$Width[1];
				$Height =isset($Width['x']) ?$Width['x'] :$Width[0];
			} else
				$Height =$Width;
		}
		$ix =imagesx($this->Canvas);
		$iy =imagesy($this->Canvas);

		if ($ix <=$Width &&$iy <=$Height) return $this;
		if ($ix >=$iy){
			$x =$Width;
			$y =$x *$iy /$ix;
		} else{
			$y =$Height;
			$x =$ix /$iy *$y;
		}
		$this->resize($x, $y);
		return $this;
	}
	public function getWH(){
		return ['width'=>imagesx($this->Canvas), 'height'=>imagesy($this->Canvas)];
	}


	/*                              变形                            */

	public function waveX($Amplitude=14, $Period=12){
		$xp = $this->scale*$Period*rand(1, 3);
		$k = rand(0, 100);
		for($i = 0; $i<($this->size[0]*$this->scale); $i++){
			imagecopy($this->Canvas, $this->Canvas, $i - 1, sin($k + $i/$xp)*($this->scale*$Amplitude), $i, 0, 1, $this->size[1]*$this->scale);
		}
		return $this;
	}
	public function waveY($Amplitude=5, $Period=11){
		$k = rand(0, 100);
		$yp = $this->scale*$Period*rand(1, 2);
		for($i = 0; $i<($this->size[1]*$this->scale); $i++){
			imagecopy($this->Canvas, $this->Canvas, sin($k + $i/$yp)*($this->scale*$Amplitude), $i - 1, 0, $i, $this->size[0]*$this->scale, 1);
		}
		return $this;
	}
	public function wave($X=[8, 12], $Y=[5, 11]){
		$this->waveX($X[0], $X[1]);
		$this->waveY($Y[0], $Y[1]);
		return $this;
	}

	/*                              滤镜                            */

	private function filter($Filter, $arg1=null, $arg2=null, $arg3=null){
		if(function_exists('imagefilter')){
			switch(func_num_args()){
				case 1:
					imagefilter($this->Canvas, $Filter);
					break;
				case 2:
					imagefilter($this->Canvas, $Filter, $arg1);
					break;
				case 3:
					imagefilter($this->Canvas, $Filter, $arg1, $arg2);
					break;
				case 4:
					imagefilter($this->Canvas, $Filter, $arg1, $arg2, $arg3);
					break;
			}
		}
		return $this;
	}
	/**
	 * 反转颜色
	 * @return $this
	 */
	public function filterNegate(){
		return $this->filter(IMG_FILTER_NEGATE);
	}
	/**
	 * 灰度
	 * @return $this
	 */
	public function filterGrays(){
		return $this->filter(IMG_FILTER_GRAYSCALE);
	}
	/**
	 * 亮度
	 * @param int $level
	 * @return hImage
	 */
	public function filterBrightness($level=0){
		return $this->filter(IMG_FILTER_BRIGHTNESS, $level);
	}
	/**
	 * 对比度
	 * @param int $level
	 * @return hImage
	 */
	public function filterContrast($level=0){
		return $this->filter(IMG_FILTER_CONTRAST, $level);
	}
	/**
	 * 颜色化
	 * @param $Red  [0-255]
	 * @param $Blue [0-255]
	 * @param $Green [0-255]
	 * @return hImage
	 */
	public function filterColorize($Red, $Blue, $Green){
		return $this->filter(IMG_FILTER_COLORIZE, $Red, $Blue, $Green);
	}
	/**
	 * 用边缘检测来突出图像的边缘
	 * @return $this
	 */
	public function filterEdgedetect(){
		return $this->filter(IMG_FILTER_EDGEDETECT);
	}
	/**
	 * 浮雕
	 * @return $this
	 */
	public function filterEmboss(){
		return $this->filter(IMG_FILTER_EMBOSS);
	}
	/**
	 * 高斯模糊
	 * @return $this
	 */
	public function filterBlur(){
		return $this->filter(IMG_FILTER_GAUSSIAN_BLUR);
	}
	/**
	 * 移除轮廓
	 * @return $this
	 */
	public function filterMeanRemoval(){
		return $this->filter(IMG_FILTER_MEAN_REMOVAL);
	}
	/**
	 * 平滑
	 * @param int $level
	 * @return hImage
	 */
	public function filterSmooth($level=0){
		return $this->filter(IMG_FILTER_SMOOTH, $level);
	}
	/**
	 * 像素化[突出显示]
	 * @param int  $size    像素格子大小
	 * @param bool $advanced
	 * @return hImage
	 */
	public function filterPixelate($size=1, $advanced =true){
		return $this->filter(IMG_FILTER_PIXELATE, $size, $advanced);
	}


	/*                              颜色                            */

	/**
	 * 设置并返回颜色
	 * @param array $Color [type, value1, value2, value3...] 颜色设定
	 * @param bool $setItTransparent 是否透明
	 * @return array [颜色, [r,g,b]]
	 */
	public function allocateColor($Color, $setItTransparent =false){
		$alpha =false;
		switch($Color[0]){
			case 'hex':
				$_color =self::hex2rgb($Color[1]);
				break;
			case 'hsv':
			case 'hsb':
				$_color =self::hsv2rgb(array_slice($Color, 1));
				break;
			case 'hsl':
				$_color =self::hsl2rgb(array_slice($Color, 1));
				break;
			case 'rgb':
				$_color =array_slice($Color, 1);
				break;
			case 'rgba':
				$_color =array_slice($Color, 1);
				$alpha =round((1-$_color[3])*127);
				break;
			default:
				$_color =[0,0,0];
				$setItTransparent =true;
		}
		$_allocate =($alpha ==false)
			?imagecolorallocate($this->Canvas, $_color[0], $_color[1], $_color[2])
			:imagecolorallocatealpha($this->Canvas, $_color[0], $_color[1], $_color[2], $alpha);
		if($setItTransparent) imagecolortransparent($this->Canvas, $_allocate);
		return [$_allocate, $_color];
	}
	/**
	 * http://zh.wikipedia.org/zh-hans/HSL%E5%92%8CHSV%E8%89%B2%E5%BD%A9%E7%A9%BA%E9%97%B4
	 *
	 * @param $rgb [r(0~255), g(0~255), b(0~255)]
	 * @return array [h(0~360), s(0~100), b(0~100)]
	 */
	static public function rgb2hsv($rgb){
		list($r, $g, $b) =$rgb;
		if(count($rgb) ==6) list($r0, $g0, $b0, $r, $g, $b) =$rgb;
		$h =$s =$v =0;
		$min =min($r, $g, $b);
		$max =max($r, $g, $b);

		$delta =$max -$min;

		$v =$max/255;
		$s =($max !==0) ?$delta /$max :0;
		if($delta !==0){
			if($max ==$r) $h =($g -$b) /$delta;
			elseif($max ==$g) $h =2+($b -$r) /$delta;
			else $h =4+($r -$g) /$delta;
		} else $h =0;
		$h *=60;
		return [(int)round($h), (int)round($s*100), (int)round($v*100), $h, $s*100, $v*100];
	}
	static public function rgb2hsb($rgb){
		return self::rgb2hsv($rgb);
	}
	/**
	 * @param $rgb [r(0~255), g(0~255), b(0~255)]
	 * @return array [h(0~360), s(0~100), l(0~100)]
	 */
	static public function rgb2hsl($rgb){
		list($r, $g, $b) =$rgb;
		if(count($rgb) ==6) list($r0, $g0, $b0, $r, $g, $b) =$rgb;
		$h =$s =$l =0;
		$min =min($r, $g, $b);
		$max =max($r, $g, $b);

		$delta =$max -$min;
		$sigma =$max +$min;
		$l =$sigma /2;
		if($delta !==0){
			if($max ==$r) $h =($g -$b) /$delta;
			elseif($max ==$g) $h =2+($b -$r) /$delta;
			else $h =4+($r -$g) /$delta;
			$s =($l >127.5) ?$delta /(510-$sigma) :$delta /$sigma;
		} else {
			$h =0;
			$s =0;
		}
		$h *=60;
		return [(int)round($h), (int)round($s*100), (int)round($l/255*100), $h, $s*100, $l/255*100];
	}
	/**
	 * @param array $hsl [h(0~360), s(0~100), l(0~100)]
	 * @return array [r(0~255), g(0~255), b(0~255)]
	 */
	static public function hsl2rgb($hsl){
		list($h, $s, $l) =$hsl;
		if(count($hsl) ==6) list($h0, $s0, $l0, $h, $s, $l) =$hsl;
		$l /=100;
		if($s ==0) return [(int)round($l*255), (int)round($l*255), (int)round($l*255), $l*255, $l*255, $l*255];
		$s /=100;
		$h =$h /360;
		$q =($l <0.5) ?$l*(1+$s) :$l+$s-($l*$s);
		$p =2*$l -$q;
		$tc =[$h +1/3, $h, $h -1/3];
		$ec =[0, 0, 0];
		foreach($tc as $k=> $c){
			if($c <0) $c+=1;
			if($c >1) $c-=1;
			if($c <1/6) $cc =$p +(($q -$p) *6*$c);
			elseif($c <1/2) $cc =$q;
			elseif($c <2/3) $cc =$p +(($q -$p) *6*(2/3 -$c));
			else $cc =$p;
			$ec[$k] =(int)round($cc*255);
			$ec[$k+3] =$cc*255;
		}
		return $ec;
	}
	/**
	 * @param array $hsv [h(0~360), s(0~100), v(0~100)]
	 * @return array [r(0~255), g(0~255), b(0~255)]
	 */
	static public function hsv2rgb($hsv){
		list($h, $s, $v) =$hsv;
		if(count($hsv) ==6) list($h0, $s0, $v0, $h, $s, $v) =$hsv;
		$s /=100;
		$v /=100;
		$hi =($h/60) % 6;
		$f =$h /60 -$hi;
		$p =$v *(1 -$s);
		$q =$v *(1 -$f *$s);
		$t =$v *(1-(1-$f)*$s);
		switch($hi){
			case 0:$r =$v;$g =$t;$b =$p;break;
			case 1:$r =$q;$g =$v;$b =$p;break;
			case 2:$r =$p;$g =$v;$b =$t;break;
			case 3:$r =$p;$g =$q;$b =$v;break;
			case 4:$r =$t;$g =$p;$b =$v;break;
			case 5:$r =$v;$g =$p;$b =$q;break;
		}
		return [(int)round($r*255), (int)round($g*255), (int)round($b*255), $r*255, $g*255, $b*255];
	}
	/**
	 * @param $rgb [r(0~255), g(0~255), b(0~255)]
	 * @param bool $upper
	 * @return string #ffffff
	 */
	static public function rgb2hex($rgb, $upper =false){
		list($r, $g, $b) =$rgb;
		$format =$upper ?'#%02X%02X%02X' :'#%02x%02x%02x';
		return sprintf($format, $r, $g, $b);
	}
	/**
	 * @param string $hex #fff #FFFFFF
	 * @return array [r(0~255), g(0~255), b(0~255)]
	 */
	static public function hex2rgb($hex){
		if($hex[0] =='#') $hex =substr($hex, 1);
		if(strlen($hex) ==6) list($r, $g, $b) =[$hex[0].$hex[1], $hex[2].$hex[3], $hex[4].$hex[5]];
		elseif(strlen($hex) ==3) list($r, $g, $b) =[$hex[0].$hex[0], $hex[1].$hex[1], $hex[2].$hex[2]];
		else return [0,0,0];
		return [hexdec($r), hexdec($g), hexdec($b)];
	}

	/**
	 * http://zh.wikipedia.org/wiki/YUV
	 * @param $rgb
	 */
	static public function rgb2yuv($rgb){
		//todo
	}

}
