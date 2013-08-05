<?php

class hPay{
	private $pay =null;
	//public function __construct($Gateway="99bill", $Merchant="", $Key="")
	public function __construct()
	{
		$fgas =func_get_args();
		$gw =array_shift($fgas);
		if (class_exists($class ="dPay_{$gw}", true)){
			$this->pay =new $class();
			call_user_func_array(array($this->pay, '_construct'), $fgas);
		}
		else throw new Exception('Unknow Pay ['.$gw.'].');
	}
	public function __set($Nm, $Val)
	{
		$this->pay->$Nm =$Val;
		return $this;
	}
	public function __get($Nm)
	{
		return $this->pay->$Nm;
	}
	public function __isset($Nm)
	{
		return isset($this->pay->$Nm);
	}
	public function __unset($Nm)
	{
		unset($this->pay->$Nm);
		return $this;
	}
	/**
	 * 生成签名
	 *
	 * @return string 签名字符
	 */
	public function _Sign()
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, '_Sign'), $fgas);
	}
	/**
	 * 设置支付网关信息
	 *
	 * @param string or array $Nm 信息名字或信息数组
	 * @param string $Val 信息值
	 * @return 对象本身
	 */
	public function Set($Nm, $Val=null)
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'Set'), $fgas);
	}
	/**
	 * 设置产品信息
	 *
	 * @param string $Name 产品名称
	 * @param string $Desc 产品简介
	 * @param integer $Num 产品数量
	 * @param string $Id 产品编号
	 * @return 对象本身
	 */
	public function Product($Name="", $Desc="", $Num=0, $Id="")
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'Product'), $fgas);
	}
	/**
	 * 设置订单信息 或 返回订单信息(空参数时)
	 *
	 * @param string $Id		订单号
	 * @param integer $Amount	订单金额 分为单位整数
	 * @return 对象本身
	 */
	public function Order($Id='', $Amount=0, $CanRedo =true)
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'Order'), $fgas);
	}
	/**
	 * 设置支付者信息
	 *
	 * @param string $Name			支付人名字
	 * @param integer $Contact		支付人联络方式
	 * @param string $ContactType	联络方式 1=email
	 * @return 对象本身
	 */
	public function Payer($Name='', $Contact="", $ContactType=1)
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'Payer'), $fgas);
	}
	/**
	 * 设置附加信息 或 返回附加信息
	 *
	 * @return 对象本身
	 */
	public function Ex()
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'Ex'), $fgas);
	}
	/**
	 * 生成支付表单
	 *
	 * @param string $Url		网关地址
	 * @param string $Caption	提交按钮标题
	 * @param boolean $Return	是否返回 或 直接输出
	 * @return string or NULL	返回表单内容
	 */
	public function Form($Url="", $Caption="提交", $Return =null)
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'Form'), $fgas);
	}
	/**
	 * 获取网关返回信息，并验证是否正确
	 *
	 * @return boolean 收到网关返回信息
	 */
	public function Receive()
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'Receive'), $fgas);
	}
	/**
	 * 判断是否支付成功，非成功即返回错误编号和错误信息
	 *
	 * @return true or array(code:integer,msg:string)
	 */
	public function Result()
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'Result'), $fgas);
	}
	/**
	 * 得到支付相关信息
	 *
	 * @return string
	 */
	public function Pay()
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'Pay'), $fgas);
	}
	/**
	 * 得到回执内容
	 *
	 * @return string
	 */
	public function Callback($OK, $Return=null)
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'Callback'), $fgas);
	}
	/**
	 * 根据参数返回SQL操作链接
	 *
	 * @param string $Option  order|pay
	 * @return string
	 */
	public function SQL($Option)
	{
		$fgas =func_get_args();
		return call_user_func_array(array($this->pay, 'SQL'), $fgas);
	}
}






?>