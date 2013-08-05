<?php
(defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

class lColor{
	static public function HSL2RGB($Hue, $Saturation, $Lightness, $MaxH =360, $MaxSL =100, $Max=256) {
		if (is_array($Hue)){
			list($Hue, $Saturation, $Lightness) =$Hue;
		}
		if ($MaxSL >1) {
			$Saturation /=$MaxSL;
			$Lightness /=$MaxSL;
		}
		if ($Saturation ==0) return array($Lightness *$Max, $Lightness *$Max, $Lightness *$Max);
		if ($MaxH >1){
			$Hue /=$MaxH;
		}
		$q =($Lightness <0.5) ?$Lightness *($Saturation +1) :$Lightness +$Saturation -$Lightness*$Saturation;
		$p =$Lightness *2 -$q;
		return array(
		self::Hue2RGB($p, $q, $Hue +1/3) *$Max,
		self::Hue2RGB($p, $q, $Hue) *$Max,
		self::Hue2RGB($p, $q, $Hue -1/3) *$Max,
		);
	}
	static private function Hue2RGB($p, $q, $h)
	{
		$h = ($h < 0) ? $h + 1 : (($h > 1) ? $h - 1 : $h);
		if ($h * 6 < 1) return $p + ($q - $p) * $h * 6;
		if ($h * 2 < 1) return $q;
		if ($h * 3 < 2) return $p + ($q - $p) * (2/3 - $h) * 6;
		return $p;
	}
	static public function RGB2HSL($Red, $Green, $Blue, $Max =256, $MaxH =360, $MaxSL =100)
	{
		if (is_array($Red)) {
			list($Red, $Green, $Blue) =$Red;
		}
		if ($Max >1){$Red /=$Max+1;$Green /=$Max+1;$Blue /=$Max+1;}
		$_Max =max($Red, $Green, $Blue);
		$_Min =min($Red, $Green, $Blue);
		$_Delta =$_Max -$_Min;
		$Lightness =($_Min +$_Max) /2;
		$Saturation =0;
		if ($Lightness >0 && $Lightness <1 && $_Max !=$_Min){
			$Saturation =$_Delta /($Lightness <0.5 ?($Lightness *2) :(2 -$Lightness *2));
		}
		$Hue =0;
		if ($_Delta >0){
			switch ($_Max){
				case $Red:
					$Hue =60*($Green -$Blue) /$_Delta +($Green >=$Blue) ?0 :360;
					break;
				case $Green:
					$Hue =60*($Blue -$Red) /$_Delta +120;
					break;
				case $Blue:
					$Hue =60*($Red -$Green) /$_Delta +240;
					break;
			}
			$Hue =$Hue /360 *$MaxH;
		}
		return array($Hue, $Saturation *$MaxSL, $Lightness *($MaxSL+1));
	}
	static public function RGB2HSV($Red, $Green, $Blue, $Max =256, $MaxH =360, $MaxSV =100)
	{
		if (is_array($Red)) {
			list($Red, $Green, $Blue) =$Red;
		}
		$_Max =max($Red, $Green, $Blue);
		$_Min =min($Red, $Green, $Blue);
		$_Delta =$_Max -$_Min;
		$Saturation =($_Max ==0) ?0 :$_Delta /$_Max;
		if ($Saturation ==0){
			$Hue =-1;
		}else{
			$r =($_Max -$Red) /$_Delta;
			$g =($_Max -$Green) /$_Delta;
			$b =($_Max -$Blue) /$_Delta;
			$Hue =($Red ==$_Max) ?$b -$g:(($Green ==$_Max) ?2 +$r -$b:(($Blue ==$_Max) ?$Hue =4 +$g -$r :0));
			$Hue =60 *($Hue /$MaxH *360);
			$Hue =($Hue <0) ?$Hue +$MaxH :$Hue;
		}
		return array($Hue, $Saturation *$MaxSV, $_Max/$Max *$MaxSV);
	}
	static public function HSV2RGB($Hue, $Saturation, $Value, $MaxH =360, $MaxSV =100, $Max =256)
	{
		if (is_array($Hue)){
			list($Hue, $Saturation, $Value) =$Hue;
		}
		if ($MaxSV >1) {
			$Saturation /=$MaxSV;
			$Value /=$MaxSV;
		}
		if ($Value ==0){
			return array($Value, $Value, $Value);
		}else{
			$Hue =($Hue %=$MaxH) /60;
			$Hi =floor($Hue);
			$Hf =$Hue -$Hi;
			$q[0] =$q[1] =$Value*(1 -$Saturation);
			$q[2] =$Value *(1 -$Saturation *(1 -$Hf));
			$q[3] =$q[4] =$Value;
			$q[5] =$Value*(1 -$Saturation *$Hf);
			//return array($q[($Hi +4) %5]*255, $q[($Hi +2) %5]*255, $q[$Hi %5]*255);
			return array($q[($Hi +4) %6]*$Max, $q[($Hi +2) %6]*$Max, $q[$Hi %6]*$Max);
		}
	}
	static public function RGB2HSB($Red, $Green, $Blue, $Max =256, $MaxH =360, $MaxSV =100)
	{
		return self::RGB2HSV($Red, $Green, $Blue, $Max, $MaxH, $MaxSV);
	}
	static public function HSB2RGB($Hue, $Saturation, $Brightness, $MaxH =360, $MaxSB =100)
	{
		return self::HSV2RGB($Hue, $Saturation, $Brightness, $MaxH, $MaxSB);
	}
	static public function Dec($R, $G=0, $B=0)
	{
		if (is_array($R)) list($R, $G, $B) =$R;
		return array(round($R), round($G), round($B));
	}
	static public function RGB2Hex($R, $G=255, $B=255)
	{
		if (is_array($R)){$G =$R[1];$B =$R[2];$R =$R[0];}
		return str_pad(dechex(round($R)), 2, "0", STR_PAD_LEFT) . str_pad(dechex(round($G)), 2, "0", STR_PAD_LEFT) . str_pad(dechex(round($B)), 2, "0", STR_PAD_LEFT);
	}
	static public function RGB2Web($R, $G=255, $B=255)
	{
		return '#'.self::RGB2Hex($R, $G, $B);
	}
	static public function RGB2HTML($R, $G=255, $B=255)
	{
		return '#'.self::RGB2Hex($R, $G, $B);
	}
	static public function ByName($Name, $Lower =false)
	{
		include(Next::Path('data/x11color.php'));
		return ($Lower) ?$ColorNameLower[$Name] :$ColorName[$Name];
	}
	static public function GetName($R, $G=255, $B=255)
	{
		if (is_array($R)){$G =$R[1];$B =$R[2];$R =$R[0];}
		$Hex =str_pad(dechex(round($R)), 2, "0", STR_PAD_LEFT) . str_pad(dechex(round($G)), 2, "0", STR_PAD_LEFT) . str_pad(dechex(round($B)), 2, "0", STR_PAD_LEFT);
		include(Next::Path('data/x11color.php'));
		return (isset($Color[$Hex])) ?$Color[$Hex] :'#'.$Hex;
	}
}

?>