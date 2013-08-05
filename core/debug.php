<?php
(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('No access allowed.');

class Debug{
	private static $hasBenchmark =false;
	public static function Dump(){
		if (vFire::Has()){
			if (func_num_args() == 2 && is_string(func_get_arg(0))){
				vFire::log(func_get_arg(1), func_get_arg(0));
			} else
				foreach (func_get_args() as $_arg){
					vFire::log($_arg);
				}
			
		} else{
			$_debug =debug_backtrace();
			$_debug =isset($_debug[3]) ?$_debug[3] :$_debug[2];
			$_debug['file'] =basename($_debug['file'], '.php');
			ob_start();
			echo "<span style='font-family:Courier New;'>{$_debug['class']}{$_debug['type']}{$_debug['function']}($) in {$_debug['file']}:{$_debug['line']}</span>";
			echo '<pre style="font-family:Courier New;font-size:12px;margin:0;">';
			if (is_array(func_get_args())){
				foreach (func_get_args() as $_arg){
					var_dump($_arg);
					echo "\n";
				}
			} else
				var_dump(func_get_args());
			echo '</pre>';
			$out =ob_get_contents();
			ob_end_clean();
			$out =preg_replace(array(
				'/=>\n(\040)+/si', 
				'/array\((\d+)\) {/si', 
				'/string\((\d+)\) "(.+)"/i', 
				'/int\((\d+)\)/si', 
				'/}/si', 
				'/\["(.+)"\]/i', 
				'/\[(\d+)\]/si'), array(
				'<span style="color:lightblue">=></span>', 
				'<span style="color:gray">{<sup>\1</sup></span>', 
				'<span style="color:green"><sub>\1</sub>"\2"</span>', 
				'<span style="color:red">\1</span>', 
				'<span style="color:gray">}</span>', 
				'<span style="color:silver">[</span><span style="color:darkblue">\'\1\'</span><span style="color:silver">]</span>', 
				'<span style="color:silver">[</span><span style="color:red">\1</span><span style="color:silver">]</span>'), $out);
			echo $out;
		}
	}
	public static function Error(){
		if (!vFire::Has()) return;
		foreach (Next::$Error as $_error){
			$_str =str_pad($_error['message'], 50, '.') . str_pad($_error['file'], 60, '.', STR_PAD_LEFT) . str_pad($_error['line'], 4, '.', STR_PAD_LEFT);
			switch ($_error['code']){
			case E_USER_NOTICE:
			case E_NOTICE:
				vFire::info($_str);
				break;
			case E_USER_WARNING:
			case E_WARNING:
				vFire::warn($_str);
				break;
			case E_USER_ERROR:
			case E_ERROR:
				vFire::error($_str);
				break;
			default:
				vFire::error($_str);
				break;
			}
		}
		//self::Benchmark();
		if (!empty(Next::$Error)) vFire::trace(Next::Language('core.error_trace_title'));
	}
	public static function Benchmark($Data =array()){
		if (self::$hasBenchmark) return;
		self::$hasBenchmark =true;
		if (empty($Data)) $Data =Next::Benchmarks();
		if (vFire::Has()){
			$_first =current($Data);
			$_all =$_first['time_end'] - $_first['time'];
			vFire::group(Next::Language('core.benchmark', number_format(($_all) * 1000, 2) . 'ms'), array(
				'Collapsed' =>true));
			/*$_table = array();
			$_table[] =array(
				'起始时间',
				'进度',
				'经过时间',
				'时间占比',
				//'内存占用',
				'统计项目'
			);*/
			foreach ($Data as $_name =>$_data){
				$_level =explode('|', $_name);
				$_count =count($_level);
				$_caption =array_pop($_level);
				if (strpos($_caption, ',') !== false){
					$_cp =explode(',', $_caption);
					$_caption =array_shift($_cp);
				} else
					$_cp =null;
				$_caption =Next::Language('core.' . $_caption, $_cp);
				$_max =100;
				$_begin =(int)round($_data['time'] / $_all * $_max);
				$_progress =(int)round(($_data['time_end'] - $_data['time']) / $_all * $_max);
				$_end =(int)$_max - $_begin - $_progress;
				if ($_end < 0) $_end =0;
				/*
				$_table[] =array(
					number_format(($_data['time']) * 1000, 2) . 'ms',
					str_repeat('-', $_begin) .'|'. str_repeat('=', $_progress) . str_repeat('-', $_end),
					number_format(($_data['time_end'] - $_data['time']) * 1000, 2) . 'ms',
					number_format(($_data['time_end'] - $_data['time']) / $_all * 100, 2) . '%',
					//number_format(($_data['memory_end'] -$_data['memory'])),
					str_repeat("..", $_count - 1) . $_caption
				);*/
				$_str =str_pad(number_format(($_data['time']) * 1000, 2) . 'ms', 7, '.', STR_PAD_LEFT);
				$_str .='' . str_repeat('-', $_begin) . '|' . str_repeat('=', $_progress) . str_repeat('-', $_end);
				$_str .='|' . str_pad(number_format(($_data['time_end'] - $_data['time']) * 1000, 2) . 'ms', 7, '.', STR_PAD_LEFT);
				$_str .='|' . str_pad(number_format(($_data['time_end'] - $_data['time']) / $_all * 100, 2) . '%', 7, '.', STR_PAD_LEFT);
				//$_str .='|' .str_pad(number_format(($_data['memory_end'] -$_data['memory'])), 7, '.', STR_PAD_LEFT);
				$_str .='|' . str_repeat("..", $_count - 1) . $_caption;
				vFire::log($_str);
			}
			vFire::groupEnd();
				//vFire::table(Next::Language('core.benchmark', number_format(($_all) * 1000, 2) . 'ms'), $_table);
		} else{
			//$_sys =dirname(dirname(__FILE__)) .DIRECTORY_SEPARATOR;
			Next::View(___NEXT . 'views/benchmark.php', $Data);
		}
	}
}