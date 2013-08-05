<?php (defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');


class lText {
	/**
	 * 过滤文字内容并替换
	 *
	 * @param string $Text 待过滤文字
	 * @param string or array $FilterList 过滤列表
	 * @param string or array $FilterReplace 替换内容
	 * @return string
	 */
	static public function Filter($Text, $FilterList, $FilterReplace="***")
	{
		return str_replace(
		$FilterList,
		$FilterReplace,
		$Text
		);
	}
	static public function RemoveBBCode($Text, $Replace='')
	{
		$Text =str_replace(array(
		"<",			//防止链接被破坏
		">",
		"\n",			//换行 nl2br
		),array(
		"&lt;",
		"&gt;",
		"<br />",
		),$Text);

		return preg_replace("/\[(.+?)\]/is", $Replace, $Text);
	}
	/**
	 * 转换表情符号到表情图片
	 *
	 * @param string $Text
	 * @return string
	 */
	static public function Emoticon($Text)
	{
		return preg_replace(array(
		'/:)/',
		"/:|/",
		"/8O/",
		"/:?:/",
		"/:?/",
		"/8)/",
		"/:D/",
		"/:oops:/",
		"/:P/",
		"/:roll:/",
		"/;)/",
		"/:cry:/",
		"/:o/",
		"/:lol:/",
		"/:x/",
		"/:(/",

		"/XD/",
		),array(
		"<img src=\"/emoticons/foo/icon_smile.gif\" /><span style='display:none;'>:)</span>",
		"<img src=\"/emoticons/foo/icon_neutral.gif\" /><span style='display:none;'>:|</span>",
		"<img src=\"/emoticons/foo/icon_eek.gif\" /><span style='display:none;'>8O</span>",
		"<img src=\"/emoticons/foo/icon_question.gif\" /><span style='display:none;'>:?:</span>",
		"<img src=\"/emoticons/foo/icon_confused.gif\" /><span style='display:none;'>:?</span>",
		"<img src=\"/emoticons/foo/icon_cool.gif\" /><span style='display:none;'>8)</span>",
		"<img src=\"/emoticons/foo/icon_biggrin.gif\" /><span style='display:none;'>:D</span>",
		"<img src=\"/emoticons/foo/icon_redface.gif\" /><span style='display:none;'>:oops:</span>",
		"<img src=\"/emoticons/foo/icon_razz.gif\" /><span style='display:none;'>:P</span>",
		"<img src=\"/emoticons/foo/icon_rolleyes.gif\" /><span style='display:none;'>:roll:</span>",
		"<img src=\"/emoticons/foo/icon_wink.gif\" /><span style='display:none;'>;)</span>",
		"<img src=\"/emoticons/foo/icon_cry.gif\" /><span style='display:none;'>:cry:</span>",
		"<img src=\"/emoticons/foo/icon_surprised.gif\" /><span style='display:none;'>:o</span>",
		"<img src=\"/emoticons/foo/icon_lol.gif\" /><span style='display:none;'>:lol</span>",
		"<img src=\"/emoticons/foo/icon_mad.gif\" /><span style='display:none;'>:x</span>",
		"<img src=\"/emoticons/foo/icon_sad.gif\" /><span style='display:none;'>:(</span>",

		"<img src=\"/emoticons/foo/icon_lol.gif\" /><span style='display:none;'>XD</span>",
		),$Text);
	}
	static public function WindCode($Text, $Set=array())
	{
		$Def =array('pic'=>true);
		$Set =array_merge($Def, $Set);
		$CodeLayout = '<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="quotecodeheader"> Code:</td>
                                </tr>
                                <tr>
                                    <td class="codebody">$1</td>
                                </tr>
                           </table>';
		$QuoteLayout = '<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="quotecodeheader"> Quote:</td>
                                </tr>
                                <tr>
                                    <td class="quotebody">$1</td>
                                </tr>
                           </table>';

		$r =$Text;
		if(strpos($r,"[code]") !== false && strpos($r,"[/code]") !== false){
			$r=preg_replace("/\[code\](.+?)\[\/code\]/is","$CodeLayout",$r);
		}
		$r = preg_replace('/\[list=([aA1]?)\](.+?)\[\/list\]/is', "<ol type=\"\\1\" style=\"margin:0 0 0 25px\">\\2</ol>", $r);

		$searcharray = array('[u]','[/u]','[b]','[/b]','[i]','[/i]','[list]','[li]','[/li]','[/list]','[sub]', '[/sub]','[sup]','[/sup]','[strike]','[/strike]','[blockquote]','[/blockquote]','[hr]','[/backcolor]', '[/color]','[/font]','[/size]','[/align]'
		);
		$replacearray = array('<u>','</u>','<b>','</b>','<i>','</i>','<ul style="margin:0 0 0 15px">','<li>', '</li>','</ul>','<sub>','</sub>','<sup>','</sup>','<strike>','</strike>','<blockquote>','</blockquote>', '<hr />','</span>','</span>','</span>','</font>','</div>'
		);
		$r = str_replace($searcharray,$replacearray,$r);

		$searcharray = array(
		"/\[font=([^\[\(&]+?)\]/is",
		"/\[color=([#0-9a-z]{1,10})\]/is",
		"/\[backcolor=([#0-9a-z]{1,10})\]/is",
		"/\[email=([^\[]*)\]([^\[]*)\[\/email\]/is",
		"/\[email\]([^\[]*)\[\/email\]/is",
		"/\[size=(\d+)\]/is",
		"/\[align=(left|center|right|justify)\]/is",
		"/\[glow=(\d+)\,([0-9a-zA-Z]+?)\,(\d+)\](.+?)\[\/glow\]/is"
		);
		$replacearray = array(
		"<span style=\"font-family:\\1 \">",
		"<span style=\"color:\\1 \">",
		"<span style=\"background-color:\\1 \">",
		"<a href=\"mailto:\\1 \">\\2</a>",
		"<a href=\"mailto:\\1 \">\\1</a>",
		"<font size=\"\\1\">",
		"<div align=\"\\1\">",
		"<div style=\"width:\\1px;filter:glow(color=\\2,strength=\\3);\">\\4</div>"
		);
		$r = preg_replace($searcharray,$replacearray,$r);
		
		//if($Set['pic']){
		$r = preg_replace("/\[img\](.+?)\[\/img\]/is", '<img src="$1">',$r);
		//}
		
		if(strpos($r,'[/URL]')!==false || strpos($r,'[/url]')!==false){
			$searcharray = array(
			"/\[url=(https?|ftp|gopher|news|telnet|mms|rtsp|thunder)([^\[\s]+?)\](.+?)\[\/url\]/is",
			"/\[url\]www\.([^\[]+?)\[\/url\]/is",
			"/\[url\](https?|ftp|gopher|news|telnet|mms|rtsp|thunder)([^\[]+?)\[\/url\]/is"
			);
			$replacearray = array(
			"<a href=\"\\1\\2\" target=\"_blank\">\\3</a>",
			"<a href=\"http://www.\\1\" target=\"_blank\">www.\\1</a>",
			"<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",
			);
			$r = preg_replace($searcharray,$replacearray,$r);
		}

		$searcharray = array(
		"/\[fly\]([^\[]*)\[\/fly\]/is",
		"/\[move\]([^\[]*)\[\/move\]/is"
		);
		$replacearray = array(
		"<marquee width=90% behavior=alternate scrollamount=3>\\1</marquee>",
		"<marquee scrollamount=3>\\1</marquee>"
		);
		$r = preg_replace($searcharray,$replacearray,$r);
		
		/*
		if($Set['hide'] && strpos($r,"[post]")!==false && strpos($r,"[/post]")!==false){
			$r=preg_replace("/\[post\](.+?)\[\/post\]/is","post('\\1')",$r);
		}
		if($Set['encode'] && strpos($r,"[hide=")!==false && strpos($r,"[/hide]")!==false){
			$r=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is","hiden('\\1','\\2')",$r);
		}
		if($Set['sell'] && strpos($r,"[sell")!==false && strpos($r,"[/sell]")!==false){
			$r=preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/is","sell('\\1','\\2')",$r);
		}
		*/
		
		if(strpos($r,"[quote]") !== false && strpos($r,"[/quote]") !== false){
			$r = preg_replace("/\[quote\](.+?)\[\/quote\]/is","$QuoteLayout",$r);
		}
		
		/*
		if($Set['flash']){
			$r = preg_replace("/\[flash=(\d+?)\,(\d+?)(\,(0|1))?\](.+?)\[\/flash\]/is", "wplayer('\\5','\\1','\\2','\\4','flash')",$r,$db_cvtimes);
		} else{
			$r = preg_replace("/\[flash=(\d+?)\,(\d+?)(\,(0|1))?\](.+?)\[\/flash\]/is","<img src='$imgpath/$stylepath/file/music.gif' align='absbottom'> <a target='_blank' href='\\5 '>flash: \\5</a>",$r,$db_cvtimes);
		}
		if($type=="post"){
			$t = 0;
			while(strpos($r,"[table") !== false && strpos($r,"[/table]") !== false){
				$r = preg_replace('/\[table(=(\d{1,3}(%|px)?))?\](.*?)\[\/table\]/is', "tablefun('\\2','\\3','\\4')",$r);
				if(++$t>4) break;
			}
			if($Set['mpeg']){
				$r = preg_replace(
				array(
				"/\[wmv=(0|1)\](.+?)\[\/wmv\]/is",
				"/\[wmv(=([0-9]{1,3})\,([0-9]{1,3})\,(0|1))?\](.+?)\[\/wmv\]/is",
				"/\[rm(=([0-9]{1,3})\,([0-9]{1,3})\,(0|1))?\](.+?)\[\/rm\]/is"
				),
				array(
				"wplayer('\\2','314','53','\\1','wmv')",
				"wplayer('\\5','\\2','\\3','\\4','wmv')",
				"wplayer('\\5','\\2','\\3','\\4','rm')"
				),$r,$db_cvtimes
				);
			} else{
				$r = preg_replace(
				array(
				"/\[wmv=[01]{1}\](.+?)\[\/wmv\]/is",
				"/\[wmv(?:=[0-9]{1,3}\,[0-9]{1,3}\,[01]{1})?\](.+?)\[\/wmv\]/is",
				"/\[rm(?:=[0-9]{1,3}\,[0-9]{1,3}\,[01]{1})\](.+?)\[\/rm\]/is"
				),
				"<img src=\"$imgpath/$stylepath/file/music.gif\" align=\"absbottom\"> <a target=\"_blank\" href=\"\\1 \">\\1</a>",$r,$db_cvtimes
				);
			}
			if($Set['iframe']){
				$r = preg_replace("/\[iframe\](.+?)\[\/iframe\]/is","<IFRAME SRC=\\1 FRAMEBORDER=0 ALLOWTRANSPARENCY=true SCROLLING=YES WIDTH=97% HEIGHT=340></IFRAME>",$r,$db_cvtimes);
			} else{
				$r = preg_replace("/\[iframe\](.+?)\[\/iframe\]/is","Iframe Close: <a target=_blank href='\\1 '>\\1</a>",$r,$db_cvtimes);
			}
			$tpc_tag && $r = relatetag($r,$tpc_tag);
			strpos($r,'[s:')!==false && $r = showface($r);
		}
		*/
		return $r;
	}
	/**
	 * 转换包含bbcode的内容
	 *
	 * @param string $Text 包含bbcode的内容
	 * @return string
	 */
	static public function BBCode($Text)
	{
		$r =str_replace(array(
		"<",			//防止链接被破坏
		">",
		"\n",			//换行 nl2br
		),array(
		"&lt;",
		"&gt;",
		"<br />",
		),$Text);


		$URLSearchString = " a-zA-Z0-9\:\/\-\?\&\.\=\_\~\#\'";
		$MAILSearchString = $URLSearchString . " a-zA-Z0-9\.@";
		$CodeLayout = '<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="quotecodeheader"> Code:</td>
                                </tr>
                                <tr>
                                    <td class="codebody">$1</td>
                                </tr>
                           </table>';
		$phpLayout = '<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="quotecodeheader"> Code:</td>
                                </tr>
                                <tr>
                                    <td class="codebody">$1</td>
                                </tr>
                           </table>';
		$QuoteLayout = '<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="quotecodeheader"> Quote:</td>
                                </tr>
                                <tr>
                                    <td class="quotebody">$1</td>
                                </tr>
                           </table>';

		$r =preg_replace(array(
		"/\[url\]([$URLSearchString]*)\[\/url\]/",
		"(\[url\=([$URLSearchString]*)\](.+?)\[/url\])",
		//"(\[url\=([$URLSearchString]*)\]([$URLSearchString]*)\[/url\])",
		"(\[mail\]([$MAILSearchString]*)\[/mail\])",
		"/\[mail\=([$MAILSearchString]*)\](.+?)\[\/mail\]/",

		"(\[b\](.+?)\[\/b])is",
		"(\[i\](.+?)\[\/i\])is",
		"(\[u\](.+?)\[\/u\])is",
		"(\[s\](.+?)\[\/s\])is",
		"(\[o\](.+?)\[\/o\])is",
		"(\[color=(.+?)\](.+?)\[\/color\])is",
		"(\[size=(.+?)\](.+?)\[\/size\])is",
		"(\[font=(.+?)\](.+?)\[\/font\])",

		"/\[list\](.+?)\[\/list\]/is",
		"/\[list=1\](.+?)\[\/list\]/is",
		"/\[list=i\](.+?)\[\/list\]/s",
		"/\[list=I\](.+?)\[\/list\]/s",
		"/\[list=a\](.+?)\[\/list\]/s",
		"/\[list=A\](.+?)\[\/list\]/s",

		"/\[img\](.+?)\[\/img\]/",
		"/\[img\=([0-9]*)x([0-9]*)\](.+?)\[\/img\]/",

		"/\[code\](.+?)\[\/code\]/is",
		"/\[php\](.+?)\[\/php\]/is",
		"/\[quote\](.+?)\[\/quote\]/is",
		),array(
		'<a href="$1" target="_blank">$1</a>',
		'<a href="$1" target="_blank">$2</a>',
		//'<a href="$1" target="_blank">$2</a>',
		'<a href="mailto:$1">$1</a>',
		'<a href="mailto:$1">$2</a>',

		'<span class="bold">$1</span>',
		'<span class="italics">$1</span>',
		'<span class="underline">$1</span>',
		'<span class="strikethrough">$1</span>',
		'<span class="overline">$1</span>',
		"<span style=\"color: $1\">$2</span>",
		"<span style=\"font-size: $1px\">$2</span>",
		"<span style=\"font-family: $1;\">$2</span>",

		'<ul class="listbullet">$1</ul>',
		'<ul class="listdecimal">$1</ul>',
		'<ul class="listlowerroman">$1</ul>',
		'<ul class="listupperroman">$1</ul>',
		'<ul class="listloweralpha">$1</ul>',
		'<ul class="listupperalpha">$1</ul>',

		'<img src="$1">',
		'<img src="$3" height="$2" width="$1">',

		"$CodeLayout",
		$phpLayout,
		"$QuoteLayout",
		),$r);
		$r = str_replace("[*]", "<li>", $r);
		return $r;
	}
}
?>