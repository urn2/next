<?php
(defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

class hImage_Chart extends hImage{
	//private $Canvas =null;
	private $Width, $Height, $Count, $X, $Y;
	private $Background =array(0, 0, 0), $Border =array(255, 255, 255);
	public $Padding =array(20, 20, 20, 40); //same as css  top right bottom bottom
	public $Limit =array(10 =>10, 100 =>20, 1000 =>30, 10000 =>40);
	public $Shape =array(5, 9); //between width
	public $Font =1;
	private $Shadow =10;
	public $Data =array();
	private $Color =array();
	public $DefaultColor =array(
		1 =>array(0, 69, 134), 2 =>array(255, 66, 14), 3 =>array(255, 211, 32), 
		4 =>array(87, 157, 28), 5 =>array(126, 0, 33), 
		6 =>array(131, 202, 255), 7 =>array(49, 64, 4), 8 =>array(174, 207, 0), 
		9 =>array(75, 31, 111), 10 =>array(255, 149, 14), 
		11 =>array(197, 0, 11), 12 =>array(0, 132, 209));

	public function __construct(){}

	public function __destruct(){
		imagedestroy($this->Canvas);
	}

	public function Set($Data =array(), $Limit =array(10=>10, 100=>20, 1000=>30, 10000=>40)){
		$this->Data =$Data;
		$this->Limit =$Limit;
		return $this;
	}

	public function Shape($Shape =array(9, 9), $Padding =array(10,10,10,10), $isPie =null){
		$this->Padding =$Padding;
		$this->Shape =$Shape;
		if (empty($isPie)){
			$this->Count =count($this->Data);
			$this->X =$this->Count *($this->Shape[0] +$this->Shape[1]) +$this->Shape[0];
			$this->Y =array_sum($this->Limit);
			$this->Padding[2] +=20;
			$this->Padding[3] +=30;
		} else{
			$this->X =$this->Shape[0] *2;
			$this->Y =$this->Shape[1] *2;
			$this->Shadow =round(5 *($this->Shape[0] /$this->Shape[1]));
			if ($this->Shadow >20) $this->Shadow =20;
		}
		//$this->Width =$this->Count*($this->Shape[0]+$this->Shape[1])+$this->Shape[0];
		$this->Width =$this->X +$this->Padding[1] +$this->Padding[3];
		$this->Height =$this->Padding[2] +$this->Padding[0] +$this->Y;
		if ($isPie) $this->Height +=$this->Shadow;
		//$this->Canvas =imagecreate($this->Width, $this->Height);
		$this->Canvas =imagecreatetruecolor($this->Width, $this->Height);
		imageantialias($this->Canvas, true);
		return $this;
	}

	private function Int32Color($r, $g, $b){
		return imagecolorallocate($this->Canvas, $r, $g, $b);
	}

	private function Array2Color($Array){
		return $this->Int32Color($Array[0], $Array[1], $Array[2]);
	}

	private function Color($Color, $Defid =1){
		if (empty($Color))
			$rc =$this->Array2Color($this->DefaultColor[$Defid]);
		elseif (is_numeric($Color) &&0 <$Color &&$Color <13)
			$rc =$this->Array2Color($this->DefaultColor[$Color]);
		else $rc =$this->Array2Color($Color);
		return $rc;
	}

	public function Background($Background =null, $Border =null){
		if (empty($Background)){
			$this->Color['background'] =$this->Int32Color(255, 255, 255);
			imagecolortransparent($this->Canvas, $this->Color['background']);
		} else
			$this->Color['background'] =$this->Color($Background);
		$this->Color['border'] =empty($Border) ?$this->Color['background'] :$this->Color($Border);
		imagefilledrectangle($this->Canvas, 0, 0, $this->Width, $this->Height, $this->Color['background']);
		imagerectangle($this->Canvas, 0, 0, $this->Width -1, $this->Height -1, $this->Color['border']);
		return $this;
	}

	public function Axis($XY =null, $Limit =null){
		$this->Color['xy'] =empty($XY) ?$this->Int32Color(0, 0, 0) :$this->Color($XY);
		$this->Color['limit'] =empty($XY) ?$this->Int32Color(220, 220, 220) :$this->Color($Limit);
		$ha =0;
		foreach ($this->Limit as $l =>$h){
			$ha +=$h;
			imageline($this->Canvas, $this->Padding[3], $this->Padding[0] +($this->Y -$ha), $this->Padding[3] +$this->X, $this->Padding[0] +($this->Y -$ha), $this->Color['limit']);
			imagestring($this->Canvas, $this->Font, $this->Padding[3] -5 *strlen($l) -$this->Shape[0], $this->Padding[0] +($this->Y -$ha) -4, $l, $this->Color['limit']);
		}
		//x y axis
		imageline($this->Canvas, $this->Padding[3], $this->Padding[0], $this->Padding[3], $this->Padding[0] +$this->Y, $this->Color['xy']);
		imageline($this->Canvas, $this->Padding[3], $this->Padding[0] +$this->Y +1, $this->Padding[3] +$this->X, $this->Padding[0] +$this->Y +1, $this->Color['xy']);
		return $this;
	}

	public function Bar($Color =null, $Callback =null){
		$this->Color['bar'] =$this->Color($Color, 1);
		$num =1;
		foreach ($this->Data as $d =>$v){
			if (!empty($Callback) &&is_callable($Callback)){
				$n =call_user_func($Callback, $v);
			} else
				$n =(int)$v;
			$x =$this->Padding[3] +$this->Shape[0] +($this->Shape[0] +$this->Shape[1]) *($num -1);
			$y =0;
			if ($n >0){
				foreach ($this->Limit as $l =>$h){
					if ($n <$l){
						$y +=floor($n /$l *$h);
						break;
					} else
						$y +=$h;
				}
				imagefilledrectangle($this->Canvas, $x, $this->Padding[0] +($this->Y -$y), $x +$this->Shape[1], $this->Padding[0] +$this->Y, $this->Color['bar']);
				imagestring($this->Canvas, $this->Font, $x, $this->Padding[0] +($this->Y -$y) -10, $n, $this->Color['bar']);
			}
			imagestring($this->Canvas, $this->Font, $x, $this->Padding[0] +$this->Y +$this->Shape[0], $d, $this->Color['xy']);
			$num +=1;
		}
		return $this;
	}

	public function Line($Color =null, $Callback =null){
		$this->Color['line'] =$this->Color($Color, 2);
		reset($this->Limit);
		//$filter =key($this->Limit);
		imagesetthickness($this->Canvas, 2);
		$num =1;
		foreach ($this->Data as $d =>$v){
			if (!empty($Callback) &&is_callable($Callback)){
				$n =call_user_func($Callback, $v);
			} else
				$n =(int)$v;
			$x =$this->Padding[3] +$this->Shape[0] +($this->Shape[0] +$this->Shape[1]) *($num -1);
			$y =0;
			if ($n >0){
				foreach ($this->Limit as $l =>$h){
					if ($n <$l){
						$y +=floor($n /$l *$h);
						break;
					} else
						$y +=$h;
				}
				//if ($n>$filter) imagestring($this->Canvas, $this->Font, $x,$this->Padding[0] +($this->Y -$y)-10,$n, $this->Color['line']);
				imagestring($this->Canvas, $this->Font, $x +$this->Shape[1] -4, $this->Padding[0] +($this->Y -$y) -10 *2, $n, $this->Color['line']);
			}
			imageline($this->Canvas, $x, $this->Padding[0] +($this->Y -$y), $x +$this->Shape[1], $this->Padding[0] +($this->Y -$y), $this->Color['line']);
			if (!empty($last)){
				imageline($this->Canvas, $last[0], $last[1], $x, $this->Padding[0] +($this->Y -$y), $this->Color['line']);
			}
			$last =array(
				$x +$this->Shape[1], $this->Padding[0] +($this->Y -$y));
			imagestring($this->Canvas, $this->Font, $x, $this->Padding[0] +$this->Y +$this->Shape[0], $d, $this->Color['xy']);
			$num +=1;
		}
		return $this;
	}

	public function Point($Color =null, $Callback =null, $hasPoint =true){
		$this->Color['point'] =$this->Color($Color, 4);
		reset($this->Limit);
		//$filter =key($this->Limit);
		$num =1;
		foreach ($this->Data as $d =>$v){
			if (!empty($Callback) &&is_callable($Callback)){
				$n =call_user_func($Callback, $v);
			} else
				$n =(int)$v;
			$x =$this->Padding[3] +$this->Shape[0] +($this->Shape[0] +$this->Shape[1]) *($num -1);
			$y =0;
			if ($n >0){
				foreach ($this->Limit as $l =>$h){
					if ($n <$l){
						$y +=floor($n /$l *$h);
						break;
					} else
						$y +=$h;
				}
				//if ($n>$filter) imagestring($this->Canvas, $this->Font, $x,$this->Padding[0] +($this->Y -$y)-10,$n, $this->Color['point']);
				imagestring($this->Canvas, $this->Font, $x +$this->Shape[1] -4, $this->Padding[0] +($this->Y -$y) -10 *2, $n, $this->Color['point']);
			}
			//imageline($this->Canvas,$x, $this->Padding[0] +($this->Y -$y), $x+ $this->Shape[1], $this->Padding[0] +($this->Y -$y), $this->Color['point']);
			$hasPoint &&imagefilledellipse($this->Canvas, $x +$this->Shape[1] /2, $this->Padding[0] +($this->Y -$y), 6, 6, $this->Color['point']);
			if (!empty($last)){
				imagesetthickness($this->Canvas, 2);
				imageline($this->Canvas, $last[0], $last[1], $x +$this->Shape[1] /2, $this->Padding[0] +($this->Y -$y), $this->Color['point']);
				imagesetthickness($this->Canvas, 1);
				$hasPoint &&imagefilledellipse($this->Canvas, $last[0], $last[1], 4, 4, $this->Color['background']);
			}
			$last =array(
				$x +$this->Shape[1] /2, $this->Padding[0] +($this->Y -$y));
			imagestring($this->Canvas, $this->Font, $x, $this->Padding[0] +$this->Y +$this->Shape[0], $d, $this->Color['xy']);
			$num +=1;
		}
		imagefilledellipse($this->Canvas, $last[0], $last[1], 4, 4, $this->Color['background']);
		return $this;
	}

	public function Pie($Color =null, $Callback =null){
		$_center =array(
			$this->Shape[0] +$this->Padding[3], 
			$this->Shape[1] +$this->Padding[0]);
		$_data =array();
		foreach ($this->Data as $d =>$v)
			$_data[] =(!empty($Callback) &&is_callable($Callback)) ?call_user_func($Callback, $v) :(int)$v;
			//$_degrees =rand(0, 270);
		//echo '$_degrees =', var_export($_degrees, true), ';<br />';
		//echo '$_data =', var_export($_data, true), ';<br />';
		$_sum =array_sum($_data);
		$_count =count($_data);
		$_degrees =$_count %360;
		if ($_degrees >270) $_degrees -=180;
		$_begin_degrees =$_degrees;
		$_sum_num =0;
		foreach ($_data as $d =>$v){
			$_tmp =array();
			$_tmp['percent'] =$v /$_sum;
			$_tmp['begin'] =$_degrees;
			$_degrees +=$_tmp['percent'] *360;
			$_sum_num +=1;
			$_tmp['end'] =($_sum_num ==$_count) ?$_begin_degrees +360 :$_degrees;
			$_tmp['mid'] =($_tmp['end'] -$_tmp['begin']) /2 +$_tmp['begin'];
			$_tmp['%'] =sprintf('%.0f%%', $_tmp['percent'] *100); // number_format($_tmp['percent'] *100, 1) .'%';
			$_tmp['ap'] =array(
				cos($_tmp['mid'] *(pi() /180.0)) *($this->Shape[0] *4 /5), 
				sin($_tmp['mid'] *(pi() /180.0)) *($this->Shape[1] *4 /5));
			$_tmp['color'] =$this->Color(lColor::HSL2RGB($_tmp['mid'], 90, 50));
			$_tmp['border'] =$this->Color(lColor::HSL2RGB($_tmp['mid'], 90, 35));
			$_tmp['light'] =$this->Color(lColor::HSL2RGB($_tmp['mid'], 90, 65));
			$_tmp['font'] =$this->Color(lColor::HSL2RGB($_tmp['mid'] +180, 90, 50));
			$_data[$d] =$_tmp;
			for ($k =1; $k <$this->Shadow; $k++)
				imagearc($this->Canvas, $_center[0], $_center[1] +$k, $this->X, $this->Y, $_tmp['begin'], $_tmp['end'], $_tmp['border']);
		}
		//$_style =IMG_ARC_EDGED ||IMG_ARC_NOFILL ||IMG_ARC_CHORD||IMG_ARC_PIE ;
		$_style =IMG_ARC_PIE;
		//$_style =IMG_ARC_NOFILL;
		foreach ($_data as $d =>$v){
			//echo $v['begin'], '------------------', $v['end'], '<br />';
			imagefilledarc($this->Canvas, $_center[0], $_center[1], $this->X, $this->Y, $v['begin'], $v['end'], $v['color'], $_style);
			if ($v['percent'] >0.03) imagestring($this->Canvas, $this->Font, floor($_center[0] +$v['ap'][0] -5), floor($_center[1] +$v['ap'][1] -5), $v['%'], $v['font']);
			imagesetthickness($this->Canvas, 2);
			imagearc($this->Canvas, $_center[0], $_center[1], $this->X, $this->Y, $v['begin'], $v['end'], $v['light']);
			imagearc($this->Canvas, $_center[0], $_center[1] +1, $this->X, $this->Y, $v['begin'], $v['end'], $v['light']);
			imagesetthickness($this->Canvas, 1);
		}
		return $this;
	}

	public function render(){
		;
	}
}
?>