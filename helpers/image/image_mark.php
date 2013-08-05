<?php (defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

class hImage_Mark{
	private $Origin =array();
	private $Bitmap =array();
	private $Background =array();
	private $Mark =array();
	public $HasError =false;
	public $Error =array();
	public $Stretch =false;

	public function __construct($Origin, $Stretch =false){
		$this->Origin =$this->LoadImage($Origin, $Mine);
		if (!$this->Origin['bmp']){
			$this->HasError =true;
			$this->Error[] ='load file failed.';
		}
		$this->Stretch =$Stretch;
	}

	public function __destruct(){
		imagedestroy($this->Origin['bmp']);
		imagedestroy($this->Background['bmp']);
		imagedestroy($this->Bitmap['bmp']);
	}

	private function LoadImage($File){
		$size =getimagesize($File);
		if ($size){
			$r =array();
			$r['size']['width'] =$size[0];
			$r['size']['height'] =$size[1];
			$r['mine'] =$size['mime'];
			$r['file'] =$File;
			switch ($size['mime']){
				case 'image/jpeg':
					$r['bmp'] =imagecreatefromjpeg($File);
					break;
				case 'image/gif':
					$r['bmp'] =imagecreatefromgif($File);
					break;
				case 'image/png':
					$r['bmp'] =imagecreatefrompng($File);
					break;
				default:
					$r['bmp'] =imagecreatefromjpeg($File);
					break;
			}
			if (!$r['bmp']){
				$r =false;
			}
			return $r;
		} else
			return false;
	}

	public function Size($Width =200, $Height =300){
		$this->Bitmap['size'] =array();
		$this->Bitmap['size']['width'] =$Width;
		$this->Bitmap['size']['height'] =$Height;
		if (!$this->Stretch){
			$_1 =$Width /$Height;
			$_2 =$this->Origin['size']['width'] /$this->Origin['size']['height'];
			if ($_1 >$_2){
				$this->Bitmap['size']['width'] =(int)round($Height *$this->Origin['size']['width'] /$this->Origin['size']['height']);
			} elseif ($_1 <$_2){
				$this->Bitmap['size']['height'] =(int)round($Width *$this->Origin['size']['height'] /$this->Origin['size']['width']);
			}
		}
		return $this;
	}

	public function Mark($Mark){
		$this->Mark =$this->LoadImage($Mark);
		if (!$this->Mark['bmp']){
			$this->HasError =true;
			$this->Error[] ='load mark failed.';
		}
		return $this;
	}

	public function Flush($Type ='jpg', $Filename =null){
		if ($this->HasError){
			var_export($this->Error);
		}
		$this->Bitmap['bmp'] =imagecreatetruecolor($this->Bitmap['size']['width'], $this->Bitmap['size']['height']);
		$ok =imagecopyresampled($this->Bitmap['bmp'], $this->Origin['bmp'], 0, 0, 0, 0, $this->Bitmap['size']['width'], $this->Bitmap['size']['height'], $this->Origin['size']['width'], $this->Origin['size']['height']);
		$x =$this->Bitmap['size']['width'] -$this->Mark['size']['width'];
		$y =$this->Bitmap['size']['height'] -$this->Mark['size']['height'];
		imagecopy($this->Bitmap['bmp'], $this->Mark['bmp'], $x, $y, 0, 0, $this->Mark['size']['width'], $this->Mark['size']['height']);
		if (!$Filename){
			header("Pragma:no-cache");
			header("Cache-control:no-cache");
		}
		switch ($Type){
			case 'jpg':
				if (!$Filename) header("Content-type: image/jpeg");
				$r =imagejpeg($this->Bitmap['bmp'], $Filename);
				break;
			case 'png':
				if (!$Filename) header("Content-type: image/png");
				$r =imagepng($this->Bitmap['bmp'], $Filename);
				break;
			case 'gif':
			default:
				if (!$Filename) header("Content-type: image/gif");
				$r =imagegif($this->Bitmap['bmp'], $Filename);
				break;
		}
		if ($Filename){
			return $r;
		}
	}
}
