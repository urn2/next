<?php

class hMail {
	public $To =array();
	public $Cc =array();
	public $Bcc =array();
	public $From ='';
	public $Subject ='';
	public $Message ='';
	private $Header =array();
	
	public $hasError ='';
	/**
	 * 直接发送邮件
	 *
	 * @param string $From 从
	 * @param string $To 到
	 * @param string $Subject 标题
	 * @param string $Message 内容
	 * @return boolean
	 */
	static public function SendNow($From ,$To, $Subject, $Message='')
	{
		$heads="From: {$From}\nReturn-Path: {$From}\nMIME-Version: 1.0\nContent-type: text/html; charset=utf-8\n";
		return mail($To, self::Encode($Subject), $Message, $heads);
	}
	/**
	 * 检测邮件地址是否正确
	 *
	 * @param string $Mail
	 * @return boolean
	 */
	static public function Check($Mail)
	{
		return (ereg( "^[^@ ]+@([a-zA-Z0-9-]+.)+([a-zA-Z0-9-]{2}|net|com|gov|mil|org|edu|int)$",$Mail));
	}
	/**
	 * 进行base64编码
	 *
	 * @param string $String 字符串
	 * @return string 编码后字符串
	 */
	static public function Encode($String)
	{
		return '=?UTF-8?B?'.base64_encode($String).'?=';
	}
	/**
	 * 设定邮箱
	 *
	 * @param array $Mails 邮件列表
	 * @param string $Error 错误信息
	 * @return array
	 */
	private function Mails($Mails =array(), $Error ='')
	{
		if (is_string($Mails) && self::Check($Mails)) {
			return array($Mails);
			//$this->To[] =$Mails;
		} elseif (is_array($Mails) && !empty($Mails)){
			foreach ($Mails as $amail) {
				if (!self::Check($amail)) {
					$this->hasError =$Error;
					break;
				}
			}
			if (empty($this->hasError)) {
				return $Mails;
				//$this->To =$Mails;
			}
		} else $this->hasError =$Error;
		return array();
	}
	/**
	 * 接收者邮箱
	 *
	 * @param array $Mails 邮件列表
	 */
	public function To($Mails =array())
	{
		$this->To =$this->Mails($Mails, 'To');
	}
	/**
	 * 抄送
	 *
	 * @param array $Mails 邮件列表
	 */
	public function Cc($Mails =array())
	{
		$this->Cc =$this->Mails($Mails, 'Cc');
	}
	/**
	 * 背景抄送
	 *
	 * @param array $Mails 邮件列表
	 */
	public function Bcc($Mails =array())
	{
		$this->Bcc =$this->Mails($Mails, 'Bcc');
	}
	/**
	 * 设定发送者
	 *
	 * @param string $Mail 发送者，即回复地址
	 * @param string $Nickname 昵称
	 */
	public function From($Mail, $Nickname ='')
	{
		if (is_string($Mail) && self::Check($Mail)) {
			if (!empty($Nickname)) {
				$this->From =self::Encode($Nickname)."\t<".$Mail.">";
			} else $this->From =$Mail;
		} else $this->hasError ='From';
	}
	/**
	 * 设定邮件标题
	 *
	 * @param string $Subject 邮件标题
	 */
	public function Subject($Subject)
	{
		$this->Subject =str_replace("\n", '', $Subject);
		$this->Subject =self::Encode($this->Subject);
	}
	/**
	 * 设定邮件内容
	 *
	 * @param string $Message 邮件内容
	 */
	public function Message($Message)
	{
		$this->Message =str_replace("\n.", "\n..", $Message);
	}
	/*public function Html($Message)
	{
		$this->Message =str_replace("\n.", "\n..", $Message);
		//$this->Header[] ='MIME-Version: 1.0';
		//$this->Header[] ='Content-type: text/html; charset=utf-8';
	}*/
	/**
	 * 发送邮件
	 *
	 * @return boolean 是否发送成功
	 */
	public function Send()
	{
		if (!empty($this->hasError)) return false;
		$this->Header[] ='MIME-Version: 1.0';
		$this->Header[] ='Content-type: text/html; charset=utf-8';
		$this->Header[] ="From: ".$this->From;
		$this->Header[] ="Return-Path: ".$this->From;
		if (!empty($this->Cc)) $this->Header[] ="Cc: ".implode(", ", $this->Cc);
		if (!empty($this->Bcc)) $this->Header[] ="Bcc: ".implode(", ", $this->Bcc);
		//$header =(!empty($this->Header)) ?implode("\n", $this->Header) :null;
		$header =implode("\n", $this->Header);
		return mail(implode(", ", $this->To),$this->Subject,$this->Message, $header);
	}
}