

<style type="text/css">
#ns_benchmark {width: 100%; margin: 0 auto;}
#ns_benchmark * { /*background-color:rgba(0,255,0,0.10);*/color: #000; font-family: '微软雅黑', '新宋体', 'Courier New', tahoma, Arial; font-size: 12px; margin: 0; padding: 0; border-collapse: collapse;}
#ns_benchmark dl a {text-decoration: none; color: #6989BC;}
#ns_benchmark dl {clear: both;}
#ns_benchmark dl dt {float: left; padding: 1px 0 0 5px;position:absolute;}
#ns_benchmark dl dd {width: 75px; float: right; text-align: right; padding-right: 5px;}
#ns_benchmark .tvll {float: right;}
#ns_benchmark div {clear: both; display: block;}
#ns_benchmark div div {display: none; margin-left: 2%; margin-bottom: 1px;}
#ns_benchmark dl dd.progress {overflow: hidden; width: 500px; font-size: 0;}
#ns_benchmark dl dd.progress * {font-size: 1px;line-height: 1px;height: 13px; margin: 0; padding: 0; float: left; width: 0; display: block; border: 0;}
#ns_benchmark div:after,#ns_benchmark dl:after,#ns_benchmark dl.header:after {content: "."; display: block; height: 0; clear: both; visibility: hidden;}
#ns_benchmark dl.tvll {padding: 0; width: 100%;background: transparent url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAwMy8yMC8xMEzun9wAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzQGstOgAAAAoklEQVQ4jeWRQRUCMQxEP2AACUhYCZXAkSNSVkIlIAEJlVAJi4M6KAdmH7N9gTuPXPKamcw0Cfx8HJRn5WXAZ6DY+wxcgCRu2wnowAOYgGYNHVg5VVgBjhLITsxesDpyrN9G6aaaAoFTgH10WiTmdceKBEMB2I7SRyJwjX7jRB8lEoDXNQrAPgCbXG5Wm9S0RmI4eeSU2S6x6t2BO+89/X08ASRwLdHNdsfOAAAAAElFTkSuQmCC) no-repeat scroll 3px bottom;}
#ns_benchmark dl.tvll dd {width: auto;}
#ns_benchmark dl.tvll a {padding: 3px 8px; display: block; text-align: center;}
#ns_benchmark dl.tvll:hover a {background-color: #EBEFF9;}
#ns_benchmark dl.tvll a:hover {background-color: #9CC2EF; color: #000;}
#ns_benchmark dl.header {border-top: 1px solid #9CC2EF; background-color: #EBEFF9; font-weight: bold; padding: 3px 0;}
#ns_benchmark dl.header dt {padding-left: 20px; background: transparent url(data:image/gif;base64,R0lGODlhEAAQANUAAP////3+//v9/vz9/vr8/vj7/vn7/vb5/fX5/fT4/fL3/e30/O70/Ozz/Ovz/Orz++ry/Ony++bw++fw++Xv++bv++Pu+uDs+t3q+dvp+dno+dro+dfn+dfm+dHj983g98bc9cbc9sXc9cLa9cHZ9b/Y9L7X9L3W9LvV9LnU9LrU9LjT9LfS9LXS87TR87XR86fJ8ajJ8abI8KXI8KfI8aTH8aLG8KDF8KHF8J3D75zC753C7wAAAAAAAAAAAAAAACH5BAEHAAYALAAAAAAQABAAAAatQIMwIZLtjjIQQsg0dG63EaayMcFwnWYnh4IodDgEQpLSZQ0InKmRMOhcAkGC0QobQLRIgkB4AwAECRM4IgYyJAoFAQE6JH8ABQoqMwY5GAhxAjofjwAIGzqVEwcDpW8GjwcWoTkZmHE6OjYSfwgcoTIliYsXJDE6IQoKK5R4enyPDh4nFIRoOCgPbZlydHZOOy8LwtwULGZaUCcaVCc1WE1DRTnsMiJL6fHyBkEAOw==) no-repeat scroll 3px 1px;}
#ns_benchmark div {border: 1px solid #CDCDCD; border-width: 0 0 1px 0;}
#ns_benchmark div div {border-width: 1px 0 1px 0;}
#ns_benchmark dl:hover {background-color: #F4F6FC;}
#ns_benchmark dl dd.progress {padding: 1px 0; border-left: 1px solid #CDCDCD; border-right: 1px solid #fff;}
#ns_benchmark dl dd.progress div.progress {background: #9CC2EF;}
#ns_benchmark dl dd.progress div.progress_before {border: 0px soild #9CC2EF; border-width: 0px 0; background: #CDCDCD; background: #F4F6FC; clear: none;}
#ns_benchmark dl dd.progress div.progress_width {background: #9CC2EF; clear: none;}
</style>
<div id="ns_benchmark">
<?php
function _PathArray(&$Array, $PathArray =array(), $Data =null){
	if (count($PathArray) >0){
		$f =array_shift($PathArray);
		if (!isset($Array[$f])) $Array[$f] =array();
		if (!is_null($Data) &&!isset($Array[$f]['data'])) $Array[$f]['data'] =$Data;
		if (count($PathArray) >0){
			$Array[$f]['sub'] =_PathArray($Array[$f]['sub'], $PathArray, $Data);
		}
	}
	return $Array;
}

function _TreeView($Column, $Data, $Tab ="\t", $Return =null){
	//global $MaxLevel;
	static $MaxLevel =0;
	static $Level =0;
	$Level +=1;
	if ($MaxLevel <$Level){
		$MaxLevel =$Level;
	}
	static $Parent =0;

	$h =array();
	$id ="dl_{$Parent}";
	$h[] =$Tab ."<div id={$id} class='dl l{$Level}'>";
	foreach ($Data as $caption =>$a){
		if (strpos($caption, ',') !==false){
			$_cp =explode(',', $caption);
			$caption =array_shift($_cp);
		} else
			$_cp =null;
		$caption =Next::Language('core.' .$caption, $_cp);

		$Parent +=1;
		$id =$Parent;
		//$id ="dl_{$Parent}";
		$h[] =$Tab ."\t<dl>";
		if (isset($a['sub'])){
			$s =_TreeView($Column, $a['sub'], $Tab ."\t", true);
		} else
			$s ='';
		if ($s ==''){
			$h[] =$Tab ."\t<dt>{$caption}</dt>";
		} else
			$h[] =$Tab ."\t<dt><a id='sdl_{$id}' href=# onclick='TreeView_Show($id);return false'>{$caption}</a></dt>";
		foreach ($a['data'] as $k =>$d){
			$h[] =$Tab ."\t<dd class='{$k}'>{$d}</dd>";
		}
		$h[] =$Tab ."\t</dl>";
		if ($s !=='') $h[] =$s;
	}
	$h[] =$Tab ."</div>";
	$Level -=1;
	if ($Level ==0){
		$l =array();
		$l[] =$Tab ."<dl class='tvll'>";
		for ($i =1; $i <=$MaxLevel; $i++){
			$j =$i +1;
			$l[] =$Tab ."\t<dd><a href=# onclick='TreeView_level({$j});return false'>{$i}</a></dd>";
		}
		$l[] =$Tab ."</dl>";

		$h[] =<<<HTML
<script>
function TreeView_Show(id){
	var e=document.getElementById('dl_'+id);
	if( e!=null){
		e.style.display =((e.style.display =='') | e.style.display =='none') ?'block' :'none';
	}
}
function TreeView_level(id){
	var p=document.getElementById('ns_benchmark');
	var o =p.getElementsByTagName('div');
	for(var i=0;i<o.length;i++){
		var e =o.item(i);
		var cc =e.className.substr(0,4);
		if (cc !='dl l') continue;
		var c =e.className.substr(4);
		e.style.display =(c <id) ?'block' :'none';
	}
}
TreeView_level(10);
</script>
HTML;
		$c =array();
		foreach ($Column as $f =>$oth){
			$c[] =$Tab ."<dl class='header'>";
			$c[] =$Tab ."\t<dt>{$f}</dt>";
			foreach ($oth as $dd){
				$c[] =$Tab ."\t<dd>{$dd}</dd>";
			}
			//$c[]=$Tab."\t<hr style='clear:both;display:block;' />";
			$c[] =$Tab ."</dl>";
			$c[] ="";
		}
		if ($Return)
			return implode("\n", $l) .implode("\n", $c) .implode("\n", $h);
		else echo implode("\n", $l) .implode("\n", $c) .implode("\n", $h);
	}
	return implode("\n", $h);
}

$first =current($Data);
$all =$first['time_end'];// -$first['time'];

$Tmp =array();
foreach ($Data as $caption =>$one){
	if (strpos($caption, '|') !==false){
		$c =explode('|', $caption);
	} else
		$c =array($caption);

	//$da =array('memory' =>$one['memory_stop'] -$one['memory'], 'time' =>number_format(($one['time_stop'] -$one['time']) *1000, 2) .'ms', 'progress' =>'<div class="progress_before" style="width:' .(($one['time']) /$all *99) .'%;">&nbsp;</div><div class="progress_width" style="width:' .(($one['time_stop'] -$one['time']) /$all *99) .'%;">&nbsp;</div>');
	$da =array(
		'time' =>number_format(($one['time_end'] -$one['time']) *1000, 2) .'ms', 
		'progress' =>'
<div class="progress_before" style="width:' .(($one['time']) /$all *99) .'%;">&nbsp;</div>
<div class="progress_width" style="width:' .(($one['time_end'] -$one['time']) /$all *99) .'%;">&nbsp;</div>');
	//'progress'=>'<div class="progress" style="margin-left:'.(($one['time'])/$all*90).'%;width:'.(($one['time_stop']-$one['time'])/$all*90).'%;">&nbsp;</div>'


	$Tmp =_PathArray($Tmp, $c, $da);
}
_TreeView(array(Next::Language('core.benchmark_list') =>array(Next::Language('core.benchmark_time'), Next::Language('core.benchmark_progress'))), $Tmp)
?>
</div>