<?php
/**
 * @since 2014.4.30
 * @author Vea
 *
 */

class hWeixin{
	private $uri ='https://api.weixin.qq.com/cgi-bin/';
	private $file='http://file.api.weixin.qq.com/cgi-bin/';
	private $qr ='https://mp.weixin.qq.com/cgi-bin/';

	private $_token =false;
	private $_on=array();
	public $AppId;
	public $set =array('appid'=>'', 'secret'=>'', 'token'=>'');
	/**
	 * 初始化配置，需要设定微信中的参数 并进行回调
	 * @param array $Set		appid, secret, token
	 * @param array $on			回调函数
	*/
	function __construct($Set=array(), $on =array()){
		$this->set =$Set;
		$this->AppId =$this->set['appid'];
		foreach (array('msg', 'log', 'token') as $_method){
			$this->_on[$_method] =(isset($on[$_method]) && is_callable($on[$_method])) ?$on[$_method] :array($this, '__on'.$_method);
		}
	}
	/**
	 * 生成临时二维码
	 * @param number $SceneId		1-100000
	 * @param number $Expire		秒数
	 * @return {"ticket":"gQG28DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0FuWC1DNmZuVEhvMVp4NDNMRnNRAAIEesLvUQMECAcAAA==","expire_seconds":1800}
	 */
	function ticketTemp($SceneId, $Expire=1800){
		$args =array('access_token'=>$this->token());
		$posts =array('expire_seconds'=>$Expire, 'action_name'=>'QR_SCENE', 'action_info'=>array('scene'=>array('scene_id'=>$SceneId)));
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('qrcode/create', $args, 'post', $posts));
	}
	/**
	 * 生成永久二维码
	 * @param number $SceneId
	 * @return {"ticket":"gQG28DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0FuWC1DNmZuVEhvMVp4NDNMRnNRAAIEesLvUQMECAcAAA=="}
	 */
	function ticketUnlimit($SceneId){
		$args =array('access_token'=>$this->token());
		$posts =array('action_name'=>'QR_LIMIT_SCENE', 'action_info'=>array('scene'=>array('scene_id'=>$SceneId)));
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('qrcode/create', $args, 'post', $posts));
	}
	/**
	 * 通过ticket换取二维码图片
	 * @param string $ticket
	 * @return bin
	 */
	function getQrFromTicket($ticket){
		$args =array('ticket'=>urlencode($ticket));
		return $this->curl('showqrcode', $args, 'get', '', $this->qr);
	}
	/**
	 * 创建分组
	 * 		起始id 从100开始
	 * @param string $Name
	 * @return {"group": {"id": 107,"name": "test"}}
	 */
	function groupCreate($Name){
		$args =array('access_token'=>$this->token());
		$posts =array('group'=>array('name'=>$Name));
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('groups/create', $args, 'post', $posts));
	}
	/**
	 * 获取所有用户分组
	 *
	 * @return {"groups": [
	 * 		{"id": 0,"name": "未分组","count": 72596},
	 * 		{"id": 1,"name": "黑名单","count": 36},
	 * 		{"id": 2,"name": "星标组","count": 8},
	 * 		{"id": 104,"name": "华东媒","count": 4},
	 * 		{"id": 106,"name": "★不测试组★","count": 1}]}
	 */
	function groupGet(){
		$args =array('access_token'=>$this->token());
		return $this->cbJson($this->curl('groups/get', $args));
	}
	/**
	 * 查询用户所在分组
	 * @param string $OpenId
	 * @return {"groupid": 102}
	 */
	function groupGetId($OpenId){
		$args =array('access_token'=>$this->token());
		$posts =array('openid'=>$OpenId);
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('groups/getid', $args, 'post', $posts));
	}
	/**
	 * 修改分组名
	 * @param integer $GroupId
	 * @param string $Name
	 * @return {"errcode": 0, "errmsg": "ok"}
	 */
	function groupUpdate($GroupId, $Name){
		$args =array('access_token'=>$this->token());
		$posts =array('group'=>array('id'=>$GroupId, 'name'=>$Name));
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('groups/update', $args, 'post', $posts));
	}
	/**
	 * 移动用户分组
	 * @param string $OpenId
	 * @param integer $GroupId
	 * @return {"errcode": 0, "errmsg": "ok"}
	 */
	function groupMembersUpdate($OpenId, $GroupId){
		$args =array('access_token'=>$this->token());
		$posts =array('openid'=>$OpenId, 'to_groupid'=>$GroupId);
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('groups/members/update', $args, 'post', $posts));
	}
	/**
	 * 获取用户关注列表
	 * 		或者指定Next获取后续，最大10000
	 * @param string $NextOpenId
	 * @return {"total":2,"count":2,"data":{"openid":["","OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"}
	 */
	function userGet($NextOpenId=''){
		$args =array('access_token'=>$this->token());
		if (!empty($NextOpenId)) $args['next_openid'] =$NextOpenId;
		return $this->cbJson($this->curl('user/get', $args));
	}
	/**
	 * 获取用户信息 需在48小时内获取
	 * @param string $OpenID	用户标识id
	 * @return ["errcode"=>0,"errmsg"=>"ok"]
	 */
	function userInfo($OpenID){
		$args =array('access_token'=>$this->token(), 'openid'=>$OpenID);
		return $this->cbJson($this->curl('user/info', $args));
	}


	/**
	 * 写回调文本信息
	 * @param string $content
	 * @param string $ToUserName
	 * @param string $FromUserName
	 * @return string
	 */
	function writeText($content, $ToUserName, $FromUserName=null){
		if ($FromUserName ==null){
			$FromUserName =$ToUserName->ToUserName;
			$ToUserName =$ToUserName->FromUserName;
		}
		$tpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[%s]]></MsgType>
	<Content><![CDATA[%s]]></Content>
</xml>";
		return sprintf($tpl, $ToUserName, $FromUserName, time(), "text", $content);
	}
	/**
	 * 写回调图片信息
	 * @param string $MediaId		需通过上传接口获取到mediaid
	 * @param string $ToUserName
	 * @param string $FromUserName
	 * @return string
	 */
	function writeImage($MediaId, $ToUserName, $FromUserName=null){
		if ($FromUserName ==null){
			$FromUserName =$ToUserName->ToUserName;
			$ToUserName =$ToUserName->FromUserName;
		}
		$tpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[%s]]></MsgType>
	<Image>
		<MediaId><![CDATA[%s]]></MediaId>
	</Image>
</xml>";
		return sprintf($tpl, $ToUserName, $FromUserName, time(), "image", $MediaId);
	}
	/**
	 * 写回调语音信息
	 * @param string $MediaId
	 * @param string $ToUserName
	 * @param string $FromUserName
	 * @return string
	 */
	function writeVoice($MediaId, $ToUserName, $FromUserName=null){
		if ($FromUserName ==null){
			$FromUserName =$ToUserName->ToUserName;
			$ToUserName =$ToUserName->FromUserName;
		}
		$tpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[%s]]></MsgType>
	<Voice>
		<MediaId><![CDATA[%s]]></MediaId>
	</Voice>
</xml>";
		return sprintf($tpl, $ToUserName, $FromUserName, time(), "voice", $MediaId);
	}
	/**
	 * 写回调视频信息
	 * @param unknown $MediaId
	 * @param unknown $ToUserName
	 * @param string $FromUserName
	 * @param unknown $Title
	 * @param unknown $Description
	 * @return string
	 */
	function writeVideo($MediaId, $ToUserName, $FromUserName=null, $Title='', $Description=''){
		if ($FromUserName ==null){
			$FromUserName =$ToUserName->ToUserName;
			$ToUserName =$ToUserName->FromUserName;
		}

		$Title =($Title =='') ?'' :"		<Title><![CDATA[{$Title}]]></Title>\n";
		$Description =($Description =='') ?'' :"		<Description><![CDATA[{$Description}]]></Description>\n";

		$tpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[%s]]></MsgType>
	<Video>
		<MediaId><![CDATA[%s]]></MediaId>
%s%s
	</Video>
</xml>";
		return sprintf($tpl, $ToUserName, $FromUserName, time(), "video", $MediaId, $Title, $Description);
	}
	/**
	 * 写回调音乐消息
	 * @param unknown $ThumbMediaId
	 * @param unknown $ToUserName
	 * @param string $FromUserName
	 * @param string $Title
	 * @param string $Description
	 * @param string $MusicURL
	 * @param string $HQMusicUrl
	 * @return string
	 */
	function writeMusic($ThumbMediaId, $ToUserName, $FromUserName=null, $Title='', $Description='', $MusicURL='', $HQMusicUrl=''){
		if ($FromUserName ==null){
			$FromUserName =$ToUserName->ToUserName;
			$ToUserName =$ToUserName->FromUserName;
		}
		$Title =($Title =='') ?'' :"		<Title><![CDATA[{$Title}]]></Title>\n";
		$Description =($Description =='') ?'' :"		<Description><![CDATA[{$Description}]]></Description>\n";
		$MusicURL =($MusicURL =='') ?'' :"		<MusicURL><![CDATA[{$MusicURL}]]></MusicURL>\n";
		$HQMusicUrl =($HQMusicUrl =='') ?'' :"		<HQMusicUrl><![CDATA[{$HQMusicUrl}]]></HQMusicUrl>\n";
		$tpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[%s]]></MsgType>
	<Video>
		<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
%s%s%s%s
	</Video>
</xml>";
		return sprintf($tpl, $ToUserName, $FromUserName, time(), "music", $ThumbMediaId, $Title, $Description, $MusicURL, $HQMusicUrl);
	}


	/**
	 * 写图文消息
	 * @param array $Articles		多条支持 自动格式
	 * @param string $ToUserName
	 * @param string $FromUserName
	 * @return string
	 */
	function writeNews($Articles, $ToUserName, $FromUserName=null){
		if (is_string($Articles)){
			$Articles =json_decode($Articles, true);
		}

		if ($FromUserName ==null){
			$FromUserName =$ToUserName->ToUserName;
			$ToUserName =$ToUserName->FromUserName;
		}
		$tpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[%s]]></MsgType>
	<ArticleCount>%s</ArticleCount>
	<Articles>
%s
	</Articles>
</xml>";
		$item ="	<item>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<PicUrl><![CDATA[%s]]></PicUrl>
		<Url><![CDATA[%s]]></Url>
	</item>
";

		$len =count($Articles);
		$items ="";
		foreach ($Articles as $_article) {
			$items .=sprintf($item, $_article['Title'], $_article['Description'], $_article['PicUrl'], $_article['Url']);
		}
		return sprintf($tpl, $ToUserName, $FromUserName, time(), "news", $len, $items);
	}

	/**
	 * 通过客服接口发送消息
	 * @param string $OpenID	用户标识id
	 * @param string $Content	文本内容
	 * @return ["errcode"=>0,"errmsg"=>"ok"]
	 */
	function sendText($OpenID, $Content){
		$args =array('access_token'=>$this->token());
		$posts =array('touser'=>$OpenID, 'msgtype'=>'text', 'text'=>array('content'=>$Content));
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('message/custom/send', $args, 'post', $posts));
	}
	/**
	 * 通过客服接口发送图片
	 * @param string $OpenID
	 * @param string $MediaId
	 * @return ["errcode"=>0,"errmsg"=>"ok"]
	 */
	function sendImage($OpenID, $MediaId){
		$args =array('access_token'=>$this->token());
		$posts =array('touser'=>$OpenID, 'msgtype'=>'image', 'image'=>array('media_id'=>$MediaId));
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('message/custom/send', $args, 'post', $posts));
	}
	/**
	 * 通过客服接口发送语音
	 * @param string $OpenID
	 * @param string $MediaId
	 * @return ["errcode"=>0,"errmsg"=>"ok"]
	 */
	function sendVoice($OpenID, $MediaId){
		$args =array('access_token'=>$this->token());
		$posts =array('touser'=>$OpenID, 'msgtype'=>'voice', 'voice'=>array('media_id'=>$MediaId));
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('message/custom/send', $args, 'post', $posts));
	}
	/**
	 * 通过客服接口发送视频
	 * @param string $OpenID
	 * @param string $MediaId
	 * @param string $Title
	 * @param string $Description
	 * @return ["errcode"=>0,"errmsg"=>"ok"]
	 */
	function sendVideo($OpenID, $MediaId, $Title='', $Description=''){
		$args =array('access_token'=>$this->token());
		$video=array('media_id'=>$MediaId);
		if ($Title!='') $video['title'] =$Title;
		if ($Description!='') $video['description'] =$Description;
		$posts =array('touser'=>$OpenID, 'msgtype'=>'video', 'video'=>$video);
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('message/custom/send', $args, 'post', $posts));
	}
	/**
	 * 通过客服接口发送音乐
	 * @param string $OpenID
	 * @param string $ThumbMediaId
	 * @param string $Title
	 * @param string $Description
	 * @param string $MusicUrl
	 * @param string $HQMusicUrl
	 * @return ["errcode"=>0,"errmsg"=>"ok"]
	 */
	function sendMusic($OpenID, $ThumbMediaId, $Title='', $Description='', $MusicUrl='', $HQMusicUrl=''){
		$args =array('access_token'=>$this->token());
		$music=array('thumb_media_id'=>$ThumbMediaId);
		if ($Title!='') $music['title'] =$Title;
		if ($Description!='') $music['description'] =$Description;
		if ($MusicUrl!='') $music['musicurl'] =$MusicUrl;
		if ($HQMusicUrl!='') $music['hqmusicurl'] =$HQMusicUrl;
		$posts =array('touser'=>$OpenID, 'msgtype'=>'music', 'music'=>$music);
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('message/custom/send', $args, 'post', $posts));
	}
	/**
	 * 通过客服接口发送图文消息
	 * @param unknown $OpenID
	 * @param unknown $Articles
	 * @return ["errcode"=>0,"errmsg"=>"ok"]
	 */
	function sendNews($OpenID, $Articles){
		if (is_string($Articles)){
			$Articles =json_decode($Articles, true);
		}
		$args =array('access_token'=>$this->token());
		$posts =array('touser'=>$OpenID, 'msgtype'=>'news', 'news'=>array('articles'=>$Articles));
		$posts =json_encode($posts, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('message/custom/send', $args, 'post', $posts));
	}
	/**
	 * 上传多媒体文件
	 *
	 * 上传的多媒体文件有格式和大小限制，如下：
	 *
	 * 图片（image）: 128K，支持JPG格式
	 * 语音（voice）：256K，播放长度不超过60s，支持AMR\MP3格式
	 * 视频（video）：1MB，支持MP4格式
	 * 缩略图（thumb）：64KB，支持JPG格式
	 *
	 * 媒体文件在后台保存时间为3天，即3天后media_id失效。
	 *
	 * @param string $File		服务器上真实存在的文件地址
	 * @param string $Type		文件类型 image video vioce thumb
	 * @return {"type":"image","media_id":"vLAsWG-qirJtM9V8wqiy2d9nm9Lo1LhBjw4Li9kiwhBqTw4WYZ1RrT_VtvuBPIVg","created_at":1399357158}
	 */
	function mediaUpload($File, $Type='image'){
		$args =array('access_token'=>$this->token(), 'type'=>$Type);
		$_file =array('media'=>'@'.$File);
		return $this->cbJson($this->curl('media/upload', $args, 'post', $_file, $this->file));
	}


	/**
	 * 下载多媒体文件
	 * @param string $media_id 媒体编号
	 * @param string $path		保存路径
	 * @return boolean
	 */
	function mediaGet($media_id, $path='./'){
		$args =array('access_token'=>$this->token(), 'media_id'=>$media_id);
		$f =$this->curl('media/get', $args, 'get', array(), $this->file);
		return file_put_contents($path.$media_id, $f);
	}
	/**
	 * 创建菜单 已有菜单需要先删除
	 * @param array $menu 特定格式数组
	 * @return ["errcode"=>0,"errmsg"=>"ok"]
	 */
	function menuCreate($menu){
		$args =array('access_token'=>$this->token());
		$menu =json_encode($menu, JSON_UNESCAPED_UNICODE);
		return $this->cbJson($this->curl('menu/create', $args, 'post', $menu));
	}
	/**
	 * 获取当前菜单内容，数组
	 * @return array || ["errcode"=>0,"errmsg"=>"ok"]
	 */
	function menuGet(){
		$args =array('access_token'=>$this->token());
		return $this->cbJson($this->curl('menu/get', $args));
	}
	/**
	 * 删除已经存在菜单
	 * @return ["errcode"=>0,"errmsg"=>"ok"]
	 */
	function menuDelete(){
		$args =array('access_token'=>$this->token());
		return $this->cbJson($this->curl('menu/delete', $args));
	}

	/**
	 * 基础支持 获取access_token唯一票据 调用后7200秒失效 再次请求前次失效
	 *
	 * 调用回调函数 onToken 初始化设定
	 *
	 * @return string
	 */
	function token(){
		//$_token =$this->_on['token']($this);
		$_token =call_user_func($this->_on['token'], $this);
		if ($_token) return $_token;
		$r =$this->cbJson($this->curl('token', array('grant_type'=>'client_credential', 'appid'=>$this->set['appid'], 'secret'=>$this->set['secret'])));
		//return ($_token) ?$_token :$this->_on['token']($this, $r['access_token']);
		return ($_token) ?$_token :call_user_func($this->_on['token'], $this, $r['access_token']);
	}
	/**
	 * 开始接收被动消息并进行回调
	 * @return SimpleXMLElement|NULL
	 */
	function responseMsg(){
		if ($this->checkSignature() && isset($GLOBALS["HTTP_RAW_POST_DATA"]) && !empty($GLOBALS["HTTP_RAW_POST_DATA"])){
			$xml =simplexml_load_string($GLOBALS["HTTP_RAW_POST_DATA"], 'SimpleXMLElement', LIBXML_NOCDATA);
			//$r =$this->_on['msg']($this, $xml, $GLOBALS["HTTP_RAW_POST_DATA"]);
			call_user_func($this->_on['msg'], $this, $xml, $GLOBALS["HTTP_RAW_POST_DATA"]);
			return $xml;
		} else return null;//无消息内容
	}
	/**
	 * 验证消息来源是否正确
	 * @return boolean
	 */
	function checkSignature()
	{
		$tmpArr = array($this->set['token'], @$_GET["timestamp"], @$_GET["nonce"]);
		sort($tmpArr, SORT_STRING);
		return (sha1(implode($tmpArr)) == @$_GET["signature"]);
	}
	/**
	 * 内部使用 汇总参数
	 * @param array $vars
	 * @return string
	 */
	private function _args($vars){
		$args =array();
		foreach ($vars as $key => $value) {
			$args[] =$key.'='.$value;
		}
		return implode('&', $args);
	}
	/**
	 * 内部使用 合成最终地址
	 * @param string $interface
	 * @param array $vars
	 * @param string $base
	 * @return string
	 */
	private function _makeurl($interface, $vars, $base=''){
		return $base.$interface.'?'.$this->_args($vars);
	}
	/**
	 * 内部使用 curl封装
	 * @param string $url
	 * @param string $method
	 * @param string $postdata
	 * @return string
	 */
	private function _curl($url,  $method, $postdata=''){
		$curl =curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		if (strtolower($method) == 'post'){
			curl_setopt($curl, CURLOPT_POST, true);
			@curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		$head =curl_exec($curl);
		curl_close($curl);
		return $head;
	}
	/**
	 * 远程请求服务器并获取内容
	 * @param string $interface
	 * @param array $vars
	 * @param string $method
	 * @param array $postdata
	 * @param string $base
	 * @return mixed
	 */
	private function curl($interface, $vars, $method='get', $postdata='', $base=''){
		$url =$this->_makeurl($interface, $vars, (empty($base) ?$this->uri :$base));
		$html =$this->_curl($url, $method, $postdata);
		//$this->_on['log']($this, $interface, $html, $url, $method, $__post);
		if (!is_string($postdata)) $postdata =json_encode($postdata, JSON_UNESCAPED_UNICODE);
		call_user_func($this->_on['log'], $this, $interface, $html, $url, $method, $postdata);
		return $html;
	}
	/**
	 * 把获取内容解析成php数组
	 * @param string $html
	 * @return array
	 */
	private function cbJson($html){
		$json =json_decode($html, true);
		return $json;
	}
	/**
	 * 默认token读取与存储接口
	 * @param hWeixin $wx
	 * @param string $token
	 * @return boolean|string
	 */
	function __onToken($wx, $token =null){
		if (is_null($token)) return $this->_token;
		return $this->_token =$token;
	}
	/**
	 * 默认回调消息接口
	 * @param hWeixin $wx
	 * @param object $xml
	 * @param xml $RAW
	 * @return boolean
	 */
	function __onMsg($wx, $xml, $RAW){
		return true;
	}
	/**
	 * 默认回调日志接口
	 * @param hWeixin $wx
	 * @param any $var
	 * @param string $name
	 * @param string $url
	 * @return boolean
	 */
	function __onLog($wx, $var, $name='', $url=''){
		$f =fopen('./log/'.date('Ymd').'.log', 'a+');
		$r =fwrite($f, ">>>".date('Y-m-d H:i:s').' '.$name." ".$url." ". var_export($var, true)."\n");
		return ($r===false);
	}
}
