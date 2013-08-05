<?php (defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

class lStructure {
	static function Module($Set =array(), $Return =null)
	{
		$id =(isset($Set['id'])) ? " id='{$Set['id']}'" :'';
		$class =" class='module".(isset($Set['class'])) ? " {$Set['class']}'" :"'";
		$title =(isset($Set['caption'])) ? "<h2><span>".isset($Set['caption'])."</span></h2>\n" :'';
		$data =(isset($Set['data'])) ?isset($Set['data']) :array(array());
		$h ="";
		foreach ($data as $head => $content) {
			$h .=(is_string($head)) ?"\t<div class='list_head'>\n\t<h3><span>{$head}</span></h3>\n</div>\n" :'';
			$h .="\t<div class='list_content'>\n\t\t<ul class='ordered'>\n";
			$no =1;
			foreach ($content as $caption => $link) {
				$h .="\t\t\t<li><a";
				if (is_string($link)) {
					$h .=" href='{$link}'";
				} elseif (is_array($link)) {
					foreach ($link as $k => $v) {
						$h .=" {$k}='{$v}'";
					}
				}
				$h .="><em>{$no}.</em><span>{$caption}</span></a></li>\n";
			}
			$h .="\t\t</ul>\n\t</div>\n";
		}
		$h ="<div{$id}{$class}>\n\t{$title}\n\t<div class='modulecontent'>{$h}\t</div>\n\t<div class='module_btm'/>\n</div>";
		if ($Return) return $h; else echo $h;
		return true;
	}

	/**
	 * 输出网页头
	 * @param array $Set = [doctype][charset][css][title][body]
	 * @param null or true $Return
	 * @return string or echo
	 */
	static function Header($Set =array(), $Return =null)
	{
		if (!isset($Set['doctype'])) $Set['doctype'] ='xhtml';
		$doctype =($Set['doctype'] !='html') ? '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">':'<DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
		$charset=(isset($Set['charset'])) ?$Set['charset'] :'utf-8';
		//$css =isset($_COOKIE[Config::Get('style','cookie_theme_admin')])
		//?$_COOKIE[Config::Get('style','cookie_theme_admin')]
		//:Config::Get('style', 'theme');
		//$css ='foo2';
		$css =Next::Config('style', 'theme');
		$css=(isset($Set['css'])) ?$Set['css'] : "http://html.zhiyin.cn/themes/{$css}/admin.css";
		$title=(isset($Set['title'])) ?$Set['title'] :'';
		$body =(isset($Set['body'])) ?"<body id='{$Set['body']}'>" :'';

		if (isset($Set['js'])) {
			$js=(is_array($Set['js'])) ?$js="\n\t<script language='javascript' src='http://html.zhiyin.cn".implode("' type='text/javascript'></script>\n\t<script language='javascript' src='http://html.zhiyin.cn", $Set['js'])."' type='text/javascript'></script>"
			:"<script language='javascript' src='http://html.zhiyin.cn".$Set["js"]."'></script>";
		} else $js="";
		$h ="{$doctype}\n<html>\n<head>\n\t<meta http-equiv='content-type' content='text/html;charset={$charset}'>\n\t<title>{$title}</title>\n\t<link rel='icon' href='favicon.ico' type='image/ico' />\n\t<link rel='stylesheet' href='{$css}' type='text/css'>\n{$js}\n</head>\n{$body}";
		if ($Return) return $h; else echo $h;
		return true;
	}
	//roots, trunk(s), branches, twigs and leaves
	static function Tree($Set =array(), $Key='', $Return =null)
	{
		static $Num =0;
		static $Level =0;
		//$first =($Num ==0);
		$parent =$Num;
		$html =array();
		//if ($first) {
		//$html[] ="<div class='tree'>";
		//}
		$html[] =($Level >0) ?"<dl id='branch_{$Num}' class='collapsed'>" :"<dl id='branch_{$Num}'>";
		$Level ++;
		foreach ($Set as $caption =>$aset) {
			$Num ++;
			if (is_array($aset)) {
				$link ="<a href='#' onclick='tree.branch(\"{$Num}\");return false;'>{$caption}</a>";
				$html[] ="\t<dt id='twig_{$Num}'>{$link}</dt>";
				$html[] =self::Tree($aset, '', true);
			} else {
				list($c, $a, $t) =explode('|', $aset);
				$link =lRouter::Link($c, $a);
				$target =($t ==='') ?'_parent' :'main';
				$link =lHtml::Anchor($link, $caption, array('target'=>$target, 'onclick'=>"tree.leaf({$Num}, {$parent});"));

				$class =($Num ==1) ?" class='click'" :"";
				
				$html[] ="\t<dd id='leaf_{$Num}'{$class}>{$link}</dd>";
			}
		}
		$html[] ="</dl>";
		$Level --;
		//if ($first) {
		//$html[] ='<script language="javascript" type="text/javascript" src="/scripts/all.js"></script>';
		//$html[] ='<script language="javascript" type="text/javascript" src="/scripts/tree.js"></script>';
		//$html[] ="</div>";
		//}
		if ($Return) return implode("\n", $html); else echo implode("\n", $html);
		return true;
	}
	static function PageNav($Sets =array(), $Return =null)
	{
		$def =array(
		'page' =>1,
		'max' =>1,
		'class' =>'autoTable',
		'link' =>'',
		'pagename'=>'p',
		);
		$Sets =array_merge($def, $Sets);

		$info =array(
		'first'=>"<a href='".$Sets['link'].'&'.$Sets['pagename']."=1'>|<</a>",
		'last'=>"<a href='".$Sets['link'].'&'.$Sets['pagename'].'='.$Sets['max']."'>>|</a>",
		);
		for ($i =1; $i<=$Sets['max'];$i++){
			$info[$i] ="<a href='".$Sets['link'].'&'.$Sets['pagename'].'='.$i."'>{$i}</a>";
		}
		if ($Return) {
			return lHtml::Table(array($info), array('class'=>$Sets['class']), true);
		} else lHtml::Table(array($info), array('class'=>$Sets['class']));
	}
	static public function Array_Dump($Var, $Parent =null)
	{
		if (!(is_array($Var) || is_object($Var))) {
			return array();
		}
		$r =array();
		foreach ($Var as $Key => $Value) {
			$rr =array();
			$rr['k'] =($Parent ==null) ?'$'.$Key :$Parent.'['.$Key.']';
			$rr['t'] =$VarType=gettype($Value);
			switch ($VarType){
				case 'array':
				case 'object':
					$r[] =array($rr['k']);
					$rr =self::Array_Dump($Value, $rr['k']);
					$r =array_merge($r, $rr);
					break;
				case 'string':
					if (($p =strpos($Value, 'Template/'))==0 && $p !==false) {
						$rr['t'] ='template';
						$rr['v'] =$Value;
					} else $rr['v']=var_export($Value, true);
					$r[] =$rr;
					break;
				default:
					$rr['v']=var_export($Value, true);
					$r[] =$rr;
					break;
			}
		}
		return $r;
	}
	static public function Array_Out($Var, $Title='')
	{
		$r =self::Array_Dump($Var);
		//self::Header(array('css'=>'themes/array_dump.css','body'=>'array_dump',));
		//echo "<link rel='stylesheet' href='/themes/array_dump.css' type='text/css'>";
		lHtml::Table($r, array(
		'col'=>array('k'=>'变量名', 't'=>'类型', 'v'=>'内容'),
		'class' =>'Array_Dump',
		'title' =>$Title,
		));
	}
	/**
	 * 把数组路径转换成嵌套数组 如 array(a, b) => array(a=>array('sub'=>array(b=>array()))
	 *
	 * @param array $Array 初始数组，可共用，如 views/debug/status.php中
	 * @param array $PathArray 数组路径
	 * @param anything $Data 任意值，赋给最后一层的 'data'
	 * @return array 新的 $Array
	 */
	static public function PathArray($Array, $PathArray=array(), $Data=null)
	{
		if (count($PathArray)>0) {
			$f =array_shift($PathArray);
			if (!isset($Array[$f])) $Array[$f] =array();
			if (!is_null($Data) && !isset($Array[$f]['data'])) $Array[$f]['data'] =$Data;
			if (count($PathArray)>0) {
				$Array[$f]['sub'] =self::PathArray($Array[$f]['sub'], $PathArray, $Data);
			}
		}
		return $Array;
	}

	/**
	 * 生成一个可以伸缩的TreeView
	 *
	 * @param array $Column array('first'=>array(second, third, ...))
	 * @param array $Data array(1=>array('data'=>array('1', '2', ...), 'sub=>array(2=>...
	 * @param string $Tab 排版用途
	 * @return string
	 */
	static public function TreeView($Column, $Data, $Tab="\t", $Return=null)
	{
		//global $MaxLevel;
		static $MaxLevel=0;
		static $Level =0;
		$Level +=1;
		if ($MaxLevel<$Level) {
			$MaxLevel =$Level;
		}
		static $Parent=0;
		$h ="\n";
		$id ="dl_{$Parent}";
		$h .=$Tab."<dl id={$id} class='l{$Level}'>";
		foreach ($Data as $caption => $a) {
			$Parent +=1;
			$id =$Parent;
			//$id ="dl_{$Parent}";
			if (isset($a['sub'])) {
				$s=self::TreeView($Column, $a['sub'],$Tab."\t", true);
			} else $s ='';
			if ($s =='') {
				$h .="\n".$Tab."\t<dt>{$caption}</dt>";
			} else $h .="\n".$Tab."\t<dt><a id='sdl_{$id}' href=# onclick='TreeView_Show($id);return false'>{$caption}</a></dt>";
			foreach ($a['data'] as $k =>$d) {
				$h .="<dd class='{$k}'>{$d}</dd>";
			}
			$h .=$s;
		}
		$h .="\n".$Tab."</dl>";
		$Level -=1;
		if ($Level ==0) {
			$l="\n<dl class='xlink'>\n";
			for ($i =1;$i<=$MaxLevel;$i++){
				$j =$i +1;
				$l .="\t<dd class='xlink'><a href=# onclick='TreeView_level({$j});return false'>{$i}</a></dd>\n";
			}
			$l .="</dl>\n";

			$h .=<<<HTML
<script>
function TreeView_Show(id){
	var e=document.getElementById('dl_'+id);
	if( e!=null){
		e.style.display =((e.style.display =='') || e.style.display =='none') ?'block' :'none';
	}
}
function TreeView_level(id){
	var p=document.getElementById('debug_benchmark');
	var o =p.getElementsByTagName('dl');
	for(var i=0;i<o.length;i++){
		var e =o.item(i);
		var cc =e.className.substr(0,1);
		if (cc !='l') continue;
		var c =e.className.substr(1);
		e.style.display =(c <id) ?'block' :'none';
	}
}
</script>
HTML;
			$c ='';
			foreach ($Column as $f => $oth) {
				$c .="<dl class='header'>";
				$c .="\t<dt class='header'>{$f}</dt>";
				foreach ($oth as $dd) {
					$c .="<dd class='header'>{$dd}</dd>";
				}
				$c .="</dl>";
			}
			if ($Return) return $c.$h.$l;
			else echo $c.$h.$l;
}
return $h;
	}
	static public function Calendar($Sets =array(), $Return =null)
	{
		//    年  月  日 日年 周 周数 月天数 闰年
		//$now =date("Y,n,j,z,w,W,t,L", $Time);
		//list($Y, $n, $j, $z, $w, $W, $t, $L) =explode(',', $now);
		//$now =array('Y' =>(int)$Y,'n' =>(int)$n,'j' =>(int)$j,'z' =>(int)$z,'w' =>(int)$w,'W' =>(int)$W,'t' =>(int)$t,'L' =>(int)$L);
		$now =date("Y,n,j", time());
		list($Y, $n, $j) =explode(',', $now);

		$def =array(
		'data' =>null,
		'now' =>array($Y, $n, $j),
		'date'=>array($Y, $n, $j),
		'week1st'=>'sun',
		'week'=>array('week'=>'周', 'sun'=>'周日', 'mon'=>'周一', 'tue'=>'周二', 'wed'=>'周三', 'thu'=>'周四', 'fri'=>'周五', 'sat'=>'周六'),
		'show'=>array('week'=>1, 'weeks'=>0, 'month'=>0, 'date'=>1),
		'style'=>'',
		);
		$Sets =array_merge($def, $Sets);
		$week1st_sun =$Sets['week1st'] =='sun';

		//周标题列表
		if ($week1st_sun) $weeklist =array(0=>'sun', 1=>'mon', 2=>'tue', 3=>'wed', 4=>'thu', 5=>'fri', 6=>'sat');
		else  $weeklist =array(1=>'mon', 2=>'tue', 3=>'wed', 4=>'thu', 5=>'fri', 6=>'sat', 7=>'sun');

		$html =array();
		$html[] ="\n\n\t<div class='calendar";
		$html[] =(!empty($Sets['style'])) ?' '.$Sets['style'] :'';
		$html[] ="'>";

		//月份的题头
		if ($Sets['show']['month']) {
			if ($Sets['data'] !=null) {
				$ly =$ny=$lm=$nm=$Sets['date'];

				$lm[1] -=1;if ($lm[1] <1) {$lm[1] =12;$lm[0] --;}
				$nm[1] +=1;if ($nm[1] >12) {$nm[1] =1;$nm[0] ++;}
				$ly[0] -=1;if ($ly[0] <1901) {$lm[0] =2099;}
				$ny[0] +=1;if ($ny[0] >2099) {$lm[0] =1901;}

				$date =mktime(0, 0, 0, $lm[1], $lm[2], $lm[0]);
				$lastmonth =($Sets['data'] ==null) ?'' :call_user_func($Sets['data'], $date, 'lastmonth');
				$date =mktime(0, 0, 0, $nm[1], $nm[2], $nm[0]);
				$nextmonth =($Sets['data'] ==null) ?'' :call_user_func($Sets['data'], $date, 'nextmonth');
				$date =mktime(0, 0, 0, $ly[1], $ly[2], $ly[0]);
				$lastyear =($Sets['data'] ==null) ?'' :call_user_func($Sets['data'], $date, 'lastyear');
				$date =mktime(0, 0, 0, $ny[1], $ny[2], $ny[0]);
				$nextyear =($Sets['data'] ==null) ?'' :call_user_func($Sets['data'], $date, 'nextyear');
			}
			$html[] ="\n\t\t<dl class='month'>";
			if ($Sets['show']['weeks']){
				$today =($Sets['data'] ==null) ?'' :call_user_func($Sets['data'], time(), 'today');
				$html[] ="\n\t\t\t<dt>{$today}</dt>";
			}
			$html[] =(empty($lastyear)) ?'' :"\n\t\t\t<dd>{$lastyear}</dd>";
			$html[] ="\n\t\t\t<dd>".$Sets['date'][0]."</dd>";
			$html[] =(empty($nextyear)) ?'' :"\n\t\t\t<dd>{$nextyear}</dd>";
			$html[] =(empty($lastmonth)) ?'' :"\n\t\t\t<dd>{$lastmonth}</dd>";
			$html[] ="\n\t\t\t<dd>".$Sets['date'][1]."</dd>";
			$html[] =(empty($nextmonth)) ?'' :"\n\t\t\t<dd>{$nextmonth}</dd>";
			if ($Sets['show']['date']) $html[] ="\n\t\t\t<dd>".$Sets['date'][2]."</dd>";
			$html[] ="\n\t\t</dl>";
		}
		//周几的题头
		if ($Sets['show']['week']) {
			$html[] ="\n\t\t<dl class='week'>";
			$html[] =($Sets['show']['weeks']) ?"\n\t\t\t<dt>".$Sets['week']['week']."</dt>" :"";
			foreach ($weeklist as $n => $wc) {
				$html[] ="\n\t\t\t<dd class='";
				$html[] =$wc;
				$html[] ="'>";
				$html[] =$Sets['week'][$wc];
				$html[] ="</dd>";
			}
			$html[] ="\n\t\t</dl>";
		}

		//日历
		if ($Sets['show']['date']) {
			$date =mktime(0, 0, 0, $Sets['date'][1], 1, $Sets['date'][0]);
			$monthdays =(int)date('t', $date);			//该月天数
			$first =strtolower(date('D', $date));		//第一天周几

			$html[] ="\n\t\t<dl class='date";
			$html[] =($week1st_sun) ?'' :' week1st_mon';
			$html[] ="'>";

			/*if ($Sets['show']['weeks']) {
			$html[] ="\n\t\t\t<dt>";
			$html[] =($week1st_sun && $first =='sun') ?(int)date('W', $date)+1 :date('W', $date);
			$html[] ="</dt>";
			}*/
			for ($i=1;$i<=$monthdays;$i++)
			{
				$date =mktime(0, 0, 0, $Sets['date'][1], $i, $Sets['date'][0]);
				$data =($Sets['data'] ==null) ?(string)$i :call_user_func($Sets['data'], $date);
				$week =strtolower(date('D', $date));
				if ($Sets['show']['weeks']) {
					$_week =(int)date('W', $date);
					if ($week1st_sun && $week =='sun'){
						$_week +=1;
						if ($_week >53) $_week =1;
						$html[] ="\n\t\t\t<dt>{$_week}</dt>";
					} elseif (!$week1st_sun && $week =='mon'){
						$html[] ="\n\t\t\t<dt>{$_week}</dt>";
					} elseif ($i ==1) {
						if($week1st_sun && $first =='sun'){
							$_week +=1;
							if ($_week >53) $_week =1;
						}
						$html[] ="\n\t\t\t<dt>{$_week}</dt>";
					}
				}
				$html[] ="\n\t\t\t<dd class='";
				$html[] =$week;
				$html[] =(($i ==$Sets['now'][2] && $Sets['date'][1] ==$Sets['now'][1] && $Sets['date'][0] ==$Sets['now'][0]) ?" today":"");
				$html[] =(($i ==$Sets['date'][2]) ?" selected":"");
				$html[] =(($i==1) ? " day1st_{$first}":"");
				$html[] =(($data !==(string)$i) ?" data":"");
				$html[] ="'>";
				$html[] =$data;
				//$html[] ='-'.date('W', $date);
				$html[] ="</dd>";
			}
			$html[] ="\n\n\t\t</dl>";
		}
		$html[] ="\n\t</div>";
		if ($Return) return implode("", $html); else echo implode("", $html);
	}
}





?>