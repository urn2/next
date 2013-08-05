<?php (defined('AGREE_LICENSE') &&AGREE_LICENSE ===true) ||die('No access allowed.');
/*
class lShmop {
	static public function _write($Addr, $Data)
	{
		$c =serialize($Data);
		$l =strlen($c);
		$shm_id = @shmop_open($Addr, "w", 0, 0);
		if (!$shm_id) {
			$shm_id =shmop_open($Addr, "c", 0644, $l);
		} else{
			shmop_delete($shm_id);
			shmop_close($shm_id);
			$shm_id =shmop_open($Addr, "c", 0644, $l);
		}

		if ($shm_id){
			$shm_bytes_written = shmop_write($shm_id, $c, 0);
			shmop_close($shm_id);
			return $shm_bytes_written;
		} else{
			return false;
		}
	}
	static public function _read($Addr, $Length=0)
	{
		$shm_id = @shmop_open($Addr, "a", 0, 0);
		if (!$shm_id) {
			$shm_id =shmop_open($Addr, "n", 0644, $Length);
		}
		if($shm_id){
			$shm_size = shmop_size($shm_id);
			$date = shmop_read($shm_id, 0, $shm_size);
			shmop_close($shm_id);
			return unserialize($date);
		} else {
			return false;
		}
	}
	static public function GetAddr($Str)
	{
		return fmod(hexdec(md5($Str)), 2147483647);
	}
	static public function Delete($Str)
	{
		self::Write($Str, null);
		
		$Addr =self::GetAddr($Str);
		$shm_id = @shmop_open($Addr, "a", 0, 0);
		if ($shm_id){
			
			shmop_delete($shm_id);
			shmop_close($shm_id);
			shmop_delete($shm_id);
			return true;
		} else return false;
	}
	static public function Write($Str, $Var)
	{
		return self::_write(self::GetAddr($Str), $Var);
	}
	static public function Read($Str)
	{
		return self::_read(self::GetAddr($Str));
	}
}
*/


?>