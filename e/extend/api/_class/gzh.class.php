<?php
class gzh {
	private $config = array(
		"token"	=> '',
		"aeskey" => ''
	);
	
	private $api = null;
	
	public function __construct($config = array()){
		$this->config = array_merge($this->config, $config);
		$this->api = new api();
	}
	
	public function __get($name) {
		if(isset($this->config[$name])){
			return $this->config[$name];
		}else{
			return false;
		}
	}

	public function __set($name,$value){
		if(isset($this->config[$name])){
			$this->config[$name] = $value;
		}
	}
	
	/* 微信公众号认证 */
	public function check(){
		if(isset($_GET['echostr'])){
			$timestamp = $this->api->get('timestamp');
			$nonce = $this->api->get('nonce');
			
			$arr = array($this->token, $timestamp, $nonce);
			sort($arr, SORT_STRING);
			$code = sha1(implode('',$arr));
			
			header('Content-Type: text');
			echo $this->api->get('signature') === $code ? $this->api->get('echostr') : '';
			exit;
		}
	}
	
	/* 返回微信数据 */
	public function getPost(){
		$post = file_get_contents("php://input");
		if(empty($post)){
			return false;
		}else{
			return $this->xml_to_arr($post);
		}	
	}
	
	/* 返回图文消息 */
	public function textpic($datas = array() , $post){
		if(!$post){
			$post = $this->getPost();
		}
		if(!empty($post) && !empty($datas)){
			$xml = '<xml><ToUserName>< ![CDATA['.$post["FromUserName"].'] ]></ToUserName><FromUserName>< ![CDATA['.$post['ToUserName'].'] ]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType>< ![CDATA[news] ]></MsgType><ArticleCount>'.count($datas).'</ArticleCount><Articles>';
			foreach($datas as $v){
				$xml .= '<item><Title>< ![CDATA['.$v["title"].'] ]></Title> <Description>< ![CDATA['.$v["description"].'] ]></Description><PicUrl>< ![CDATA['.$v["picurl"].'] ]></PicUrl><Url>< ![CDATA['.$v["picurl"].'] ]></Url></item>';
			}
			$xml .= '</Articles></xml>';
			$this->xml($xml);
		}
	}
	
	/* 返回文字消息 */
	public function text($content = '' , $post){
		if(!$post){
			$post = $this->getPost();
		}
		if(!empty($post) && !empty($content)){
			$xml = '<xml><ToUserName><![CDATA['.$post['FromUserName'].']]></ToUserName><FromUserName><![CDATA['.$post['ToUserName'].']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$content.']]></Content></xml>';
			$this->xml($xml);
		}
	}
	
	/* 输出xml */
	protected function xml($xml){
		header('Content-Type: text/xml; charset=utf-8');
		echo $xml;
		exit;
	}
	
	
	protected function xml_to_arr($xml){
		$arr1 = array('ToUserName' , 'FromUserName' , 'MsgType' , 'Content');
		$arr2 = array('MsgId' , 'CreateTime');
		$arr = array();
		foreach($arr1 as $v){
			$arr[$v] = $this->str_cut($xml , '<'.$v.'><![CDATA[' , ']]></'.$v.'>');
		}
		foreach($arr2 as $v){
			$arr[$v] = $this->str_cut($xml , '<'.$v.'>' , '</'.$v.'>');
		}
		return $arr;
	}
	
	protected function str_cut($str , $startCode = '' , $endCode = ''){
		if($startCode == ''){
			return $str;
		}
		$arr = explode($startCode , $str);
		if(!isset($arr[1])){
			return '';
		}
		if($endCode == ''){
			return $arr[1];
		}else{
			$arr = explode($endCode , $arr[1]);
			return $arr[0];
		}
	}
	
	
}