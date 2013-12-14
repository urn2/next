<?php
(defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

class hImage{
	protected $Canvas =null;
	protected $Error =array();
	protected $ContentType =array('jpg' =>'jpeg', 'png' =>'png', 'gif' =>'gif');
	protected $ExtType =array(
		'jpeg' =>'jpg', 'jpg' =>'jpg', 'png' =>'png', 'gif' =>'gif');
	protected $hasSendHeader =false;

	public function __construct(){
		;
	}

	public function __destruct(){
		if (!is_null($this->Canvas)) imagedestroy($this->Canvas);
	}

	public static function factory(){
		return new self;

	}

	public function render(){
		if (!empty($this->Error)) return $this->Error;
	}

	protected function sendHeader($Type ='gif', $NoCache =true){
		if ($this->hasSendHeader) return false;
		if ($NoCache){
			header('Pragma:no-cache');
			header('Cache-control:no-cache');
		}
		$Type =isset($this->ContentType[$Type]) ?$this->ContentType[$Type] :'gif';
		header('Content-type: image/' .(isset($this->ContentType[$Type]) ?$this->ContentType[$Type] :'gif'));
	}

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
				$this->Canvas =imagecreatefromjpeg($File);
				break;
			case 'png':
			case "image/png":
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

	public function Cut($Box){
		$im =imagecreatetruecolor($Box['width'], $Box['height']);
		imagecopy($im, $this->Canvas, 0, 0, $Box['left'], $Box['top'], $Box['width'], $Box['height']);
		imagedestroy($this->Canvas);
		$this->Canvas =$im;
		return $this;
	}

	public function Resize($MaxX, $MaxY =null){
		if (!is_numeric($MaxY)){
			if (is_array($MaxX)){
				$MaxY =isset($MaxX['y']) ?$MaxX['y'] :$MaxX[1];
				$MaxX =isset($MaxX['x']) ?$MaxX['x'] :$MaxX[0];
			} else
				$MaxY =$MaxX;
		}

		$ix =imagesx($this->Canvas);
		$iy =imagesy($this->Canvas);

		if ($ix <=$MaxX &&$iy <=$MaxY) return $this;
		if ($ix >=$iy){
			$x =$MaxX;
			$y =$x *$iy /$ix;
		} else{
			$y =$MaxY;
			$x =$ix /$iy *$y;
		}
		$nc =imagecreatetruecolor($x, $y);
		//imagecopyresized($nc, $this->Canvas, 0, 0, 0, 0, floor($x), floor($y), $ix, $iy);
		imagecopyresampled($nc, $this->Canvas, 0, 0, 0, 0, floor($x), floor($y), $ix, $iy);
		imagedestroy($this->Canvas);
		$this->Canvas =$nc;
		return $this;
	}
	public function getWH(){
		return array('width'=>imagesx($this->Canvas), 'height'=>imagesy($this->Canvas));
	}
}
