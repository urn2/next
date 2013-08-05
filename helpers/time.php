<?php (defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');


class lTime {
	const Minute =60;
	const Hour =3600;
	const Day =86400;
	/**
	 * 根据时间，返回相对与当前的近似时间字符
	 *
	 * @param int $Time 指定时间
	 * @param int $Now 相对时间，默认为当前时间
	 * @return string
	 */
	static public function Near($Time, $Now=0)
	{
		if ($Now ==0) {
			$Now =time();
		}
		$Gap =$Now -$Time;

		if ($Gap <self::Minute) {
			$Return =$Gap.'秒前';
		}elseif ($Gap <self::Minute*60) {
			$Return =floor($Gap/self::Minute).'分钟前';
		}elseif ($Gap <self::Day) {
			$Return =floor($Gap/self::Hour).'小时前';
		}else{
			$Date =getdate($Time);
			$DTime =ceil($Time/self::Day)*self::Day;
			$Gap =$Now -$DTime;
			$Days =floor($Gap/self::Day);
			if ($Days ==0) {
				$Return ='昨天'.self::PeriodOfHour($Date['hours']);
			} elseif ($Days ==1){
				$Return ='前天'.self::PeriodOfHour($Date['hours']);
			} elseif ($Days <10){
				$Return =$Days.'天前';
			} elseif ($Days<15){
				$Return ='半月前';
			} elseif ($Days<30){
				$Return =$Days.'天前';
			} else {
				$NDate =getdate($Now);
				$Mouth =$NDate['mon'] -$Date['mon']+($NDate['year']-$Date['year'])*12;
				if ($Mouth<12) {
					$Return =$Mouth.'个月前';
				} else {
					$Return =($NDate['year']-$Date['year']).'年前';
				}
			}
		}

		return $Return;//.'|'.$Days.'|'.$Hour.'|'.$Minute.'|'.date('m-d H:i:s', $Time);


	}
	/**
	 * 模糊时间输出
	 *
	 * @param int $Hour 24小时制小时
	 * @return string
	 */
	static public function PeriodOfHour($Hour)
	{
		$Prefix ='';
		if ($Hour>=0 && $Hour<5) {
			$Prefix ='凌晨';
		}elseif ($Hour>=5 && $Hour<9) {
			$Prefix ='早上';
		}elseif ($Hour>=9 && $Hour<12) {
			$Prefix ='上午';
		}elseif ($Hour>=12 && $Hour<13) {
			$Prefix ='中午';
		}elseif ($Hour>=13 && $Hour<17) {
			$Prefix ='下午';
		}elseif ($Hour>=17 && $Hour<20) {
			$Prefix ='傍晚';
		}elseif ($Hour>=20 && $Hour<24) {
			$Prefix ='晚上';
		}
		return $Prefix.$Hour.'点';
	}

}

?>