<?php
(defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

class lConvert {
	/**
	 * Unix时间转换成农历日期
	 *
	 * @param int $Date
	 * @return array('tiangan'=>'天干','dizhi'=>'地支','shengxiao'=>'生肖','yue'=>'月','ri'=>'日','run'=>'闰年','month'=>'月数字','day'=>'日数字','jieqi'=>'节气','jie'=>'节','string'=>'全部')
	 */
	static public function Unix2Lunar($Date)
	{
		//if ($Date>1612972800) return false;//mktime(0,0,0,2,11,2021)
		$__tiangan=array("null","甲","乙","丙","丁","戊","己","庚","辛","壬","癸");
		$__dizhi=array("null","子","丑","寅","卯","辰","巳","午","未","申","酉","戌","亥");
		$__shengxiao=array("null","鼠","牛","虎","兔","龙","蛇","马","羊","猴","鸡","狗","猪");
		$__yue=array("闰","正","二","三","四","五","六","七","八","九","十","十一","十二","月");
		$__ri=array("null","初一","初二","初三","初四","初五","初六","初七","初八","初九","初十","十一","十二","十三","十四","十五","十六","十七","十八","十九","二十","廿一","廿二","廿三","廿四","廿五","廿六","廿七","廿八","廿九","三十");
		$__term=array(0,21208,42467,63836,85337,107014,128867,150921,173149,195551,218072,240693,263343,285989,308563,331033,353350,375494,397447,419210,440795,462224,483532,504758);
		$__jieqi=array("小寒","大寒","立春","雨水","惊蛰","春分","清明","谷雨","立夏","小满","芒种","夏至","小暑","大暑","立秋","处暑","白露","秋分","寒露","霜降","立冬","小雪","大雪","冬至");
		/*$Lunar2Unix =11;
		for($y=1900;$y<1970;$y++) {
			$Lunar2Unix+=365;
			if ($y%4==0) $Lunar2Unix++;
		}*/
		$Lunar2Unix =25213;//1900.12.21 0:0:0 - 1970.1.1 0:0:0
		//$Lunar2Unix =25579;//1900.1.1 0:0:0 - 1970.1.1 0:0:0
		$today =$Lunar2Unix +(int)floor(($Date+(int)date('Z'))/(24*60*60));
		
		//查表得到农历月份信息
		$lunar =array();
		include(___NEXT.'data/lunar.php');
		$todayl =0;
		$break =false;
		foreach ($lunar as $y => $_year){
			for ($i =1;$i<14;$i++){
				$todayl +=$_year[$i];
				if ($todayl >=$today) {
					$break =true;
					break;
				}
			}
			if ($break) break;
		}
		$_leap =($_year[0]>0 && $_year[0]==$i-1);
		$_month =($_year[0]>0 && $_year[0]<$i) ?$i-1 :$i;//当前农历月份 出现闰月-1
		$_day =$_year[$i] -($todayl -$today);
		if ($_day >$_year[$i]) $_day -=$_year[$i];//倒退方式确定日期？
		
		$r =array(
			'tiangan'=>$__tiangan[$_year[14]],
			'dizhi'=>$__dizhi[$_year[15]],
			'shengxiao'=>$__shengxiao[$_year[15]],
			'yue'=>($_year[0]>0 && $_year[0]+1 ==$i) ?$__yue[0].$__yue[$_month] :$__yue[$_month].$__yue[13],
			'ri'=>$__ri[$_day],
			'run'=>$_leap,
			'month'=>$_month,
			'day'=>$_day,
		);

		$d =getdate($Date);
		//alert(Date.UTC(1900,0,5,18,30));
		$t1 =(31556925.9747*($d['year']-1900) + $__term[($d['mon']-1)*2]*60  ) -2208576600; // -2208549300; //+mktime(2,5,0,1,6,1900);
		$t3 =idate('d', $t1);
		if ($t3 ==$d['mday']){
			$r['jieqi'] =$__jieqi[($d['mon']-1)*2];
		}
		$t2 =(31556925.9747*($d['year']-1900) + $__term[($d['mon']-1)*2+1]*60  ) -2208576600; // -2208549300; //+mktime(2,5,0,1,6,1900);
		$t4 =idate('d', $t2);
		if ($t4 ==$d['mday']) {
			$r['jieqi'] =$__jieqi[($d['mon']-1)*2+1];
		}
		
		if ($_month ==1 && $_day ==1 && !$_leap) $r['jieqi'] ="春节";
		elseif ($_month ==1 && $_day ==15 && !$_leap) $r['jieqi'] ="元宵";
		elseif ($_month ==5 && $_day ==5 && !$_leap) $r['jieqi'] ="端午";
		elseif ($_month ==8 && $_day ==15 && !$_leap) $r['jieqi'] ="中秋";
		if ($d['mon'] ==1 && $d['mday'] ==1) $r['jie'] ="元旦";
		elseif ($d['mon'] ==3 && $d['mday'] ==8) $r['jie'] ="妇女节";
		elseif ($d['mon'] ==5 && $d['mday'] ==1) $r['jie'] ="劳动节";
		elseif ($d['mon'] ==8 && $d['mday'] ==1) $r['jie'] ="建军节";
		elseif ($d['mon'] ==9 && $d['mday'] ==10) $r['jie'] ="教师节";
		elseif ($d['mon'] ==10 && $d['mday'] ==1) $r['jie'] ="国庆节";
   
		$r['string'] =$r['tiangan'].$r['dizhi'].'('.$r['shengxiao'].')'.$r['yue'].$r['ri'];
		return $r;
	}
	/**
	 * 汉字转拼音
	 *
	 * @param int $Zi
	 * @param string $Suffix
	 * @return string
	 */
	static public function Zi2Pinyin($Zi, $Suffix ='')
	{
		$pinyin =array();
		include(___NEXT.'data/pinyin.php');
		$py =$Zi;
		foreach ($pinyin as $y => $z) {
			$py =str_replace($z, $y.$Suffix, $py);
		}
		return $py;
	}
	/**
	 * IPv4转地址
	 *
	 * @param string $IP
	 * @return string
	 */
	static public function Ip($IP)
	{
		$return = '';
		if(preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $IP)) {
			$IParray = explode('.', $IP);
			if($IParray[0] == 10 || $IParray[0] == 127 || ($IParray[0] == 192 && $IParray[1] == 168) || ($IParray[0] == 172 && ($IParray[1] >= 16 && $IParray[1] <= 31))) {
				$return = '- LAN';
			} elseif($IParray[0] > 255 || $IParray[1] > 255 || $IParray[2] > 255 || $IParray[3] > 255) {
				$return = '- Invalid IP Address';
			} else {
				//$dz_tiny =___NEXT.'data/tinyipdata.dat';
				//$wry =___NEXT.'data/wry.dat';
				$dz_tiny =Next::Path('data/tinyipdata.dat');
				$wry =Next::Path('data/wry.dat');

				if(@file_exists($wry)) {
					$return = self::IpByWry($IP, $wry);
				} elseif(@file_exists($dz_tiny)) {
					$return = self::IpByDz($IP, $dz_tiny);
				} else $return ='No Data File.';
			}
		}
		return $return;
	}
	/**
	 * Dz论坛的IP转换函数复写
	 *
	 * @param string $IP
	 * @param string $File IP数据路径
	 * @return string
	 */
	static private function IpByDz($IP, $File)
	{

		static $fp = NULL, $offset = array(), $index = NULL;

		$IPdot = explode('.', $IP);
		$IP    = pack('N', ip2long($IP));

		$IPdot[0] = (int)$IPdot[0];
		$IPdot[1] = (int)$IPdot[1];

		if($fp === NULL && $fp = @fopen($File, 'rb')) {
			$offset = unpack('Nlen', fread($fp, 4));
			$index  = fread($fp, $offset['len'] - 4);
		} elseif($fp == FALSE) {
			return  '- Invalid IP data file';
		}

		$length = $offset['len'] - 1028;
		$start  = unpack('Vlen', $index[$IPdot[0] * 4] . $index[$IPdot[0] * 4 + 1] . $index[$IPdot[0] * 4 + 2] . $index[$IPdot[0] * 4 + 3]);

		$index_offset =0;
		$index_length =array('len'=>0);
		for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {

			if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $IP) {
				$index_offset = unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
				$index_length = unpack('Clen', $index{$start + 7});
				break;
			}
		}

		fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
		if($index_length['len']) {
			return '- '.fread($fp, $index_length['len']);
		} else {
			return '- Unknown';
		}


	}
	/**
	 * 的IP转换函数复写
	 *
	 * @param string $IP
	 * @param string $File IP数据路径
	 * @return string
	 */
	static private function IpByWry($IP, $File)
	{
		if(!$fd = @fopen($File, 'rb')) {
			return '- Invalid IP data file';
		}

		$IP = explode('.', $IP);
		$IPNum = $IP[0] * 16777216 + $IP[1] * 65536 + $IP[2] * 256 + $IP[3];

		if(!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4)) ) return;
		@$IPbegin = implode('', unpack('L', $DataBegin));
		if($IPbegin < 0) $IPbegin += pow(2, 32);
		@$IPend = implode('', unpack('L', $DataEnd));
		if($IPend < 0) $IPend += pow(2, 32);
		$IPAllNum = ($IPend - $IPbegin) / 7 + 1;

		$BeginNum = $IP2num = $IP1num = 0;
		$IPAddr1 = $IPAddr2 = '';
		$EndNum = $IPAllNum;

		while($IP1num > $IPNum || $IP2num < $IPNum) {
			$Middle= intval(($EndNum + $BeginNum) / 2);

			fseek($fd, $IPbegin + 7 * $Middle);
			$IPData1 = fread($fd, 4);
			if(strlen($IPData1) < 4) {
				fclose($fd);
				return '- System Error';
			}
			$IP1num = implode('', unpack('L', $IPData1));
			if($IP1num < 0) $IP1num += pow(2, 32);

			if($IP1num > $IPNum) {
				$EndNum = $Middle;
				continue;
			}

			$DataSeek = fread($fd, 3);
			if(strlen($DataSeek) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
			fseek($fd, $DataSeek);
			$IPData2 = fread($fd, 4);
			if(strlen($IPData2) < 4) {
				fclose($fd);
				return '- System Error';
			}
			$IP2num = implode('', unpack('L', $IPData2));
			if($IP2num < 0) $IP2num += pow(2, 32);

			if($IP2num < $IPNum) {
				if($Middle == $BeginNum) {
					fclose($fd);
					return '- Unknown';
				}
				$BeginNum = $Middle;
			}
		}

		$IPFlag = fread($fd, 1);
		if($IPFlag == chr(1)) {
			$IPSeek = fread($fd, 3);
			if(strlen($IPSeek) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$IPSeek = implode('', unpack('L', $IPSeek.chr(0)));
			fseek($fd, $IPSeek);
			$IPFlag = fread($fd, 1);
		}

		if($IPFlag == chr(2)) {
			$AddrSeek = fread($fd, 3);
			if(strlen($AddrSeek) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$IPFlag = fread($fd, 1);
			if($IPFlag == chr(2)) {
				$AddrSeek2 = fread($fd, 3);
				if(strlen($AddrSeek2) < 3) {
					fclose($fd);
					return '- System Error';
				}
				$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
				fseek($fd, $AddrSeek2);
			} else {
				fseek($fd, -1, SEEK_CUR);
			}

			while(($char = fread($fd, 1)) != chr(0))
			$IPAddr2 .= $char;

			$AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
			fseek($fd, $AddrSeek);

			while(($char = fread($fd, 1)) != chr(0))
			$IPAddr1 .= $char;
		} else {
			fseek($fd, -1, SEEK_CUR);
			while(($char = fread($fd, 1)) != chr(0))
			$IPAddr1 .= $char;

			$IPFlag = fread($fd, 1);
			if($IPFlag == chr(2)) {
				$AddrSeek2 = fread($fd, 3);
				if(strlen($AddrSeek2) < 3) {
					fclose($fd);
					return '- System Error';
				}
				$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
				fseek($fd, $AddrSeek2);
			} else {
				fseek($fd, -1, SEEK_CUR);
			}
			while(($char = fread($fd, 1)) != chr(0))
			$IPAddr2 .= $char;
		}
		fclose($fd);

		if(preg_match('/http/i', $IPAddr2)) {
			$IPAddr2 = '';
		}
		$IPaddr = "$IPAddr1 $IPAddr2";
		$IPaddr = preg_replace('/CZ88\.NET/is', '', $IPaddr);
		$IPaddr = preg_replace('/^\s*/is', '', $IPaddr);
		$IPaddr = preg_replace('/\s*$/is', '', $IPaddr);
		if(preg_match('/http/i', $IPaddr) || $IPaddr == '') {
			$IPaddr = '- Unknown';
		}

		return '- '.iconv('GB2312', 'UTF-8', $IPaddr);

	}
	static public function FileSize($Size, $Set=array())
	{
		$_def =array('l'=>1,'s'=>' ','b'=>'B','kb'=>'KB','mb'=>'MB','gb'=>'GB','tb'=>'TB',);
		$_max =array('b'=>0,'kb'=>1024,'mb'=>1048576,'gb'=>1073741824,'tb'=>1099511627776,);
		$_set =array_merge($_def, $Set);
		
		$_t =($Size >$_max['tb'])?'tb':($Size >$_max['gb'])?'gb':($Size >$_max['mb'])?'mb':($Size >$_max['kb'])?'kb' :'b';
		$_r =($Size *100 /$_max[$_t]) /100;
		$_l =($_r <10)?2:($_r <100)?1:0;
		$_r =round($_r, $_l);
		return $_r.$_set['s'].$_set[$_t];
	}
}

?>