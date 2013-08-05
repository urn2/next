<?php


class dPay_99bill {
	private $_Send =array("inputCharset", "bgUrl", "version", "language", "signType", "signMsg", "merchantAcctId", "payerName", "payerContactType", "payerContact", "orderId", "orderAmount", "orderTime", "productName", "productNum", "productId", "productDesc", "ext1", "ext2", "payType", "redoFlag", "pid", "key");
	private $_Receive =array("merchantAcctId", "version", "language", "signType", "payType", "bankId", "orderId", "orderTime", "orderAmount", "dealId", "bankDealId", "dealTime", "payAmount", "fee", "ext1", "ext2", "payResult", "errCode", "key");
	private $_HasReceive =null;

	public $Info =array(
	//字符集.固定选择值。可为空。
	///只能选择1、2、3.
	///1代表UTF-8; 2代表GBK; 3代表gb2312
	///默认值为1
	"inputCharset"=>"1",
	//服务器接受支付结果的后台地址.与[pageUrl]不能同时为空。必须是绝对地址。
	///快钱通过服务器连接的方式将交易结果发送到[bgUrl]对应的页面地址，在商户处理完成后输出的<result>如果为1，页面会转向到<redirecturl>对应的地址。
	///如果快钱未接收到<redirecturl>对应的地址，快钱将把支付结果GET到[pageUrl]对应的页面。
	"bgUrl"=>"",
	//网关版本.固定值
	///快钱会根据版本号来调用对应的接口处理程序。
	///本代码版本号固定为v2.0
	"version"=>"v2.0",
	//语言种类.固定选择值。
	///只能选择1、2、3
	///1代表中文；2代表英文
	///默认值为1
	"language"=>"1",
	//签名类型.固定值
	///1代表MD5签名
	///当前版本固定为1
	"signType"=>"1",
	//人民币网关账户号
	///请登录快钱系统获取用户编号，用户编号后加01即为人民币网关账户号。
	"merchantAcctId"=>"1001153656201",
	//支付人姓名
	///可为中文或英文字符
	"payerName"=>"",
	//支付人联系方式类型.固定选择值
	///只能选择1
	///1代表Email
	"payerContactType"=>"",
	//支付人联系方式
	///只能选择Email或手机号
	"payerContact"=>"",
	//商户订单号
	///由字母、数字、或[-][_]组成
	//$orderId=date('YmdHis');
	"orderId"=>"20090824126000",
	//订单金额
	///以分为单位，必须是整型数字
	///比方2，代表0.02元
	"orderAmount"=>"1",
	//订单提交时间
	///14位数字。年[4位]月[2位]日[2位]时[2位]分[2位]秒[2位]
	///如；20080101010101
	//$orderTime=date('YmdHis');
	"orderTime"=>"20090824126000",
	//商品名称
	///可为中文或英文字符
	"productName"=>"",
	//商品数量
	///可为空，非空时必须为数字
	"productNum"=>"",
	//商品代码
	///可为字符或者数字
	"productId"=>"",
	//商品描述
	"productDesc"=>"",
	//扩展字段1
	///在支付结束后原样返回给商户
	"ext1"=>"",
	//扩展字段2
	///在支付结束后原样返回给商户
	"ext2"=>"",
	//支付方式.固定选择值
	///只能选择00、10、11、12、13、14
	///00：组合支付（网关支付页面显示快钱支持的各种支付方式，推荐使用）10：银行卡支付（网关支付页面只显示银行卡支付）.11：电话银行支付（网关支付页面只显示电话支付）.12：快钱账户支付（网关支付页面只显示快钱账户支付）.13：线下支付（网关支付页面只显示线下支付方式）
	"payType"=>"00",
	//同一订单禁止重复提交标志
	///固定选择值： 1、0
	///1代表同一订单号只允许提交1次；0表示同一订单号在没有支付成功的前提下可重复提交多次。默认为0建议实物购物车结算类商户采用0；虚拟产品类商户采用1
	"redoFlag"=>"",
	//快钱的合作伙伴的账户号
	///如未和快钱签订代理合作协议，不需要填写本参数
	"pid"=>"",///合作伙伴在快钱的用户编号
	//人民币网关密钥
	///区分大小写.请与快钱联系索取
	"key"=>"ZUZNJB8MF63GA83J",
	);
	public function __construct()
	{
	}
	public function __set($Nm, $Val)
	{
		$this->Info[$Nm] =$Val;
		return $this;
	}
	public function __get($Nm)
	{
		return $this->Info[$Nm];
	}
	public function __isset($Nm)
	{
		return isset($this->Info[$Nm]);
	}
	public function __unset($Nm)
	{
		unset($this->Info[$Nm]);
		return $this;
	}
	public function _construct()
	{
		$Merchant =func_get_arg(0);
		$Key =func_get_arg(1);
		if (!empty($Key)) $this->Info['key'] =$Key;
		if (!empty($Merchant)) $this->Info['merchantAcctId'] =$Merchant;
	}
	public function _Sign()
	{
		$SignArray =func_get_arg(0);
		if (empty($SignArray)){
			$SignArray =$this->_Send;
		}// else return false;
		$opt =array();
		foreach ($SignArray as $_opt){
			$_val =trim($this->Info[$_opt]);
			if ($_val!=""){
				$opt[] ="{$_opt}={$_val}";
			}
		}
		return strtoupper(md5(implode('&', $opt)));
		//$_str =implode('&', $opt);
		//$_str2 =strtoupper(md5($_str));
		//var_dump($_str, $_str2);
		//return $_str2;
	}
	public function Set($Nm, $Val=null)
	{
		if (is_array($Nm)){
			foreach ($Nm as $_k => $_v) {
				//if (isset($this->Info[$_k])){
				$this->Info[$_k] =$_v;
				//}
			}
		} else $this->__set($Nm, $Val);
		return $this;
	}
	public function Product($Name="", $Desc="", $Num=0, $Id="")
	{
		$this->Info['productName'] =trim($Name);
		$this->Info['productNum'] =$Num >0 ?strval($Num) :"";
		$this->Info['productId'] =trim($Id);
		$this->Info['productDesc'] =trim($Desc);
		return $this;
	}
	public function Order($Id='', $Amount=0, $CanRedo =true)
	{
		if (func_num_args() >0) {
			$this->Info['redoFlag'] =$CanRedo ?'0' :'1';
			$this->Info['orderId'] =trim($Id);
			$this->Info['orderAmount'] =(int)$Amount;
			$this->Info['orderTime'] =date('YmdHis');
			return $this;
		} else {
			return array(
			'orderId' =>$this->Info['orderId'],
			'orderAmount' =>$this->Info['orderAmount'],
			'orderTime' =>$this->Info['orderTime'],
			);
		}
	}
	public function Payer($Name='', $Contact="", $ContactType=1)
	{
		$this->Info['payerName'] =trim($Name);
		$this->Info['payerContact'] =trim($Contact);
		$this->Info['payerContactType'] =(int)$ContactType;
		return $this;
	}
	public function Ex()
	{
		if (func_num_args() >0) {
			$this->Info['ext1'] =func_get_arg(0);
			$this->Info['ext2'] =func_get_arg(1);
			return $this;
		} else {
			return array(
			'ext1' =>$this->Info['ext1'],
			'ext2' =>$this->Info['ext2'],
			);
		}
	}
	public function Form($Url="", $Caption="提交到快钱", $Return =null)
	{
		$_send =array_flip($this->_Send);
		$__send =$_send;
		unset($__send['signMsg']);
		$__send =array_flip($__send);
		$this->Info['signMsg'] =$this->_Sign($__send);

		$__send =$_send;
		unset($__send['key']);
		$__send =array_flip($__send);

		$_form =array();
		$_form[] ="<form name='kqPay' method='post' action='{$Url}'>";// target='_blank'
		foreach ($__send as $_key){
			$_val =trim($this->Info[$_key]);
			if ($_val!=""){
				$_form[] ="	<input type='hidden' name='{$_key}' value='{$_val}' />";
			}
		}
		$_form[] ="	<input type='submit' name='submit' value='{$Caption}' />";
		$_form[] ="</form>";
		if (empty($Return)) {
			echo implode("\n", $_form);
		} else return implode("\n", $_form);
	}
	public function Receive()
	{
		if (!is_null($this->_HasReceive)){
			return $this->_HasReceive;
		}
		$_infokey =$this->_Receive;
		unset($_infokey['merchantAcctId'], $_infokey['key']);
		foreach ($_infokey as $_key){
			if (isset($_REQUEST[$_key])) {
				$this->Info[$_key] =trim($_REQUEST[$_key]);
			}
		}
		$this->Info['signMsg'] =$this->_Sign($this->_Receive);
		$_msg =trim($_REQUEST['signMsg']);
		$this->Info['payResult'] =trim($_REQUEST['payResult']);
		$this->_HasReceive =($this->Info['signMsg'] === strtoupper($_msg));
		return $this->_HasReceive;
	}
	public function Result()
	{
		$ok =($this->Info['payResult'] ==='10');
		return $ok ?true :(int)$this->Info['errCode'];
	}
	public function Pay()
	{
		if ($this->Result()){
			return array(
			'dealId'=>$this->Info['dealId'],
			'dealTime'=>$this->Info['dealTime'],
			'bankId'=>$this->Info['bankId'],
			'bankDealId'=>$this->Info['bankDealId'],
			'payAmount'=>$this->Info['payAmount'],
			'fee'=>$this->Info['fee'],
			);
		} else return false;
	}
	public function Callback($OK, $Return=null)
	{
		$rr =$OK ?'1' :'0';
		$r ="<result>{$rr}</result><redirecturl>{$this->Info['jump']}</redirecturl>";
		if ($Return) {
			return $r;
		} else die($r);
	}
	public function SQL($Option)
	{
		$now =time();
		switch ($Option) {
			case 'order':
				$sql ="INSERT `pay_99bill` SET 
`order_id` ='{$this->Info['orderId']}',
`orderAmount` ='{$this->Info['orderAmount']}',
`orderTime` ='{$this->Info['orderTime']}',
`redoFlag` ='{$this->Info['redoFlag']}',
`productName` ='{$this->Info['productName']}',
`productNum` ='{$this->Info['productNum']}',
`productId` ='{$this->Info['productId']}',
`productDesc` ='{$this->Info['productDesc']}',
`payerName` ='{$this->Info['payerName']}',
`payerContact` ='{$this->Info['payerContact']}',
`payerContactType` ='{$this->Info['payerContactType']}',
`ext1` ='{$this->Info['ext1']}',
`ext2` ='{$this->Info['ext2']}',
`payType` ='{$this->Info['payType']}',
`create` ='{$now}'
";
				break;
			case 'pay':
				$sql ="UPDATE `pay_99bill` SET 
`pay` ='{$this->Info['payAmount']}',
`fee` ='{$this->Info['fee']}',
`dealTime` ='{$this->Info['dealTime']}',
`dealId` ='{$this->Info['dealId']}',
`bankDealId` ='{$this->Info['bankDealId']}',
`bankId` ='{$this->Info['bankId']}',
`payResult` ='{$this->Info['payResult']}',
`errCode` ='{$this->Info['errCode']}',
`update` ='{$now}'
WHERE `order_id` ='{$this->Info['orderId']}'
";
				break;
			case '':	
			default:
				$sql ="";
				break;
		}
		return $sql;
	}
}




/*
CREATE TABLE IF NOT EXISTS `pay_99bill` (
  `order_id` char(50) NOT NULL default '',
  `orderAmount` int(10) unsigned NOT NULL default '0',
  `orderTime` char(14) NOT NULL default '',
  `redoFlag` char(1) NOT NULL default '',
  `productName` varchar(256) NOT NULL default '',
  `productNum` char(8) NOT NULL default '',
  `productId` char(20) NOT NULL default '',
  `productDesc` varchar(400) NOT NULL default '',
  `payerName` char(32) NOT NULL default '',
  `payerContactType` char(2) NOT NULL default '1',
  `payerContact` char(50) NOT NULL,
  `ext1` char(128) NOT NULL default '',
  `ext2` char(128) NOT NULL default '',
  `payType` char(2) NOT NULL default '',
  `pay` int(10) unsigned NOT NULL default '0',
  `fee` int(10) unsigned NOT NULL default '0',
  `dealTime` char(14) NOT NULL default '',
  `dealId` char(30) NOT NULL default '',
  `bankDealId` char(30) NOT NULL default '',
  `bankId` char(8) NOT NULL default '',
  `payResult` int(2) unsigned NOT NULL default '11',
  `errCode` char(10) NOT NULL default '0',
  `create` int(10) unsigned NOT NULL default '0',
  `update` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='支付网关 快钱记录';
*/

?>