<?php
(defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');

class hFile{
	public static function ScanPath($Path, $Skeletonize=''){
		$_path =realpath($Path);
		if (!$_path) return null;
		
		if ($Skeletonize =='') $Skeletonize =$_path;
		$_s =strlen($Skeletonize) +1;
		
		$_files =array();
		$_handle =opendir($_path);
		while (false !== ($_file =readdir($_handle))){
			if ($_file == '.' || $_file == '..') continue;
			$__path =$_path . DIRECTORY_SEPARATOR . $_file;
			if (is_dir($__path)){
				$__files =self::ScanPath($__path, $Skeletonize);
				if (is_array($__files)) $_files =array_merge($_files, $__files);
				continue;
			}
			$_files[] =substr($__path, $_s);
		}
		closedir($_handle);
		return $_files;
	}
	public static function Zip($Files =array(), $Zip2 ='', $NoOverwrite =false){
		if (!$NoOverwrite && file_exists($Zip2)) return false;
		$_files =array();
		if (is_array($Files)){
			foreach ($Files as $_file){
				if (file_exists($_file)) $_files[] =$_file;
			}
		}
		if (count($_files)){
			$zip =new ZipArchive();
			if ($zip->open($Zip2, $NoOverwrite ?ZipArchive::OVERWRITE :ZipArchive::CREATE) != true){
				return false;
			}
			foreach ($_files as $_file){
				$zip->addFile($_file, $_file);
			}
			$zip->close();
			return file_exists($Zip2);
		} else
			return false;
	}
	public static function ZipExtract($Zip2, $Path){
		$zip =new ZipArchive();
		if ($zip->open($Zip2) !== true) return false;
		$zip->extractTo($Path);
		$zip->close();
		return true;
	}
	public static function Download($FileName, $Dname =null){
		if (is_file($FileName) && file_exists($FileName)){
			header('Content-length: ' . filesize($FileName));
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . (empty($Dname) ? basename($FileName) :$Dname) . '');
			readfile($FileName);
		} else{
			echo "Download Nothing!";
		}
	}
	/**
	 * 保存内容到文件
	 *
	 * @param string $FileName 文件名
	 * @param string $Content 文件内容
	 * @param boolean $UnlinkFile 文件存在时是否删除存在文件
	 * @param string $Mode 文件mod
	 * @return boolean 是否保存成功
	 */
	static public function SaveContent($FileName, $Content ='', $UnlinkFile =false, $Mode ='wb'){
		if (is_file($FileName) && $UnlinkFile) unlink($FileName);
		$r_file =fopen($FileName, $Mode);
		if ($r_file){
			flock($r_file, LOCK_EX);
			fwrite($r_file, $Content);
			flock($r_file, LOCK_UN);
			fclose($r_file);
			return true;
		}
		return false;
	}
	/**
	 * 保存内容为php文件
	 *
	 * @param string $FileName 文件名
	 * @param string $Var 变量名
	 * @param string $Content 变量内容
	 * @param boolean $UnlinkFile 文件存在时是否删除存在文件
	 * @param string $Mode 文件mod
	 * @return boolean 是否保存成功
	 */
	static public function Save2PHP($FileName, $Var, $Content, $UnlinkFile =false, $Mode ='wb'){
		$r =var_export($Content, True);
		$find =array("=> 
", "
    ", "
  ),", " ");
		$replace =array("=>", "", "),", "");
		$r =str_replace($find, $replace, $r);
		return self::SaveContent($FileName, "<?PHP\n\${$Var} = " . $r . ";\n?>", $UnlinkFile, $Mode);
	}
}