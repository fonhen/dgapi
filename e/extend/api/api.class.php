<?php
class api {
	public $empire , $publib_r , $ecms_config;
	
	/* param */
	public function get($name , $default = '' , $fn = 'trim'){
		$value = isset($_GET[$name]) ? $_GET[$name] : $default;
		return !empty($fn) && function_exists($fn) ? $fn($value) : $value;
	}
	
	public function post($name , $default = '' , $fn = 'trim'){
		$value = isset($_POST[$name]) ? $_POST[$name] : $default;
		return !empty($fn) && function_exists($fn) ? $fn($value) : $value;
	}
	
	public function param($name , $default = '' , $fn = 'trim'){
		$value = isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $default);
		return !empty($fn) && function_exists($fn) ? $fn($value) : $value;
	}
	
	/* 输出 */
	
	public function show($str , $type = 'text/html' , $charset='utf-8'){
		header('Content-Type: '.$type.'; charset='.$charset);
		exit($str);
	}
	
	public function error($str , $code = 404 , $type = 'text/html' , $charset='utf-8'){
		$this->send_http_status($code);
		$this->show($str , $type , $charset);
	}

	public function json($arr , $options = 0){
		$json = is_array($arr) ? json_encode($arr , $options) : trim($arr);
		$this->show($json , 'application/json');
	}
	
	public function jsonp($arr , $cb = 'callback' , $options = 0){
		$json = is_array($arr) ? json_encode($arr , $options) : trim($arr);
		$cb = $cb ? $cb : 'callback';
		$json = $cb.'('.$json.');';
		$this->show($json , 'application/json');
	}
	
	/* 数据库 */
	
	public function execute($sql = '' , $exit = true){
		return $exit ? $this->empire->query($sql) : $this->empire->query1($sql);
	}
	
	public function query($sql = ''){
		$data = $this->empire->query($sql);
		$res = array();
		while($r = $this->empire->fetch($data)){
			$arr = array();
			foreach($r as $k=>$v){
				if(is_string($k)){
					$arr[$k] = $v; 
				}
			}
			$res[] = $arr;
		}
		return $res;
	}
	
	public function one($sql = ''){
		$res = $this->empire->fetch1($sql);
		if(!empty($res)){
			foreach($res as $k=>$r){
				if(!is_string($k)){
					unset($res[$k]);
				}
			}
		}else{
			$res = false;
		}
		return $res;
	}
	
	/*
	 * 会员登陆
	 * @param data 登陆时提交的表单数据
	 * @retuen
	 * true / 成功
	 * 100 / 参数错误
	 * 200 / 帐号密码为空
	 * 201 / 帐号密码不正确
	 * 202 / 帐号不存在
	 * 203 / 密码不正确
	 * 204 / 帐号被锁定
	 * 300 / 验证码为空
	 * 301 / 验证码超时
	 * 302 / 验证码不正确
	 * 
	*/
	public function user_login($data){
		if(empty($data) || !is_array($data)){
			return 100;
		}
		$username = trim($data['username']);
		$password = trim($data['password']);
		if($username === '' || $password === ''){
			return 200;
		}
		
		if($this->public_r['loginkey_ok']){
			$key = trim($data['key']);
			if($key === ''){
				return 300;
			}else{
				$key = $this->ecms_check_showkey('checkloginkey' , $key , 0);
				if($key === 'timeout'){
					return 301;
				}else if($key === 'fail'){
					return 302;
				}
			}
		}
		$username = RepPostVar($username);
		$password = RepPostVar($password);
		
		$user = $this->empire->fetch1("select * from " . $this->ecms_config['member']['tablename'] . " where username = '" . $username . "' limit 1");
		if(empty($user) || !is_array($user)){
			return 202;
		}
		if((int)$user['checked'] === 0){
			return 204;
		}
		if(!eDoCkMemberPw($password , $user['password'] , $user['salt'])){
			return 203;
		}
		
		$rnd = make_password(20);
		$lasttime = time();
		$user['groupid'] = (int)$user['groupid'];
		$lastip = egetip();
		$lastipport = egetipport();
		
		$dbtbpre = $this->ecms_config['db']['dbtbpre'];
		
		$this->empire->query("update " . $this->ecms_config['member']['tablename'] . " set rnd = '$rnd' where userid = '$user[userid]'");
		$this->empire->query("update {$dbtbpre}enewsmemberadd set lasttime='$lasttime',lastip='$lastip',loginnum=loginnum+1,lastipport='$lastipport' where userid='$user[userid]'");
		
		$lifetime=(int)$data['lifetime'];
		$logincookie=0;
		if($lifetime){
			$logincookie=time()+$lifetime;
		}
		esetcookie("mlusername" , $username , $logincookie);
		esetcookie("mluserid" , $user['userid'] , $logincookie);
		esetcookie("mlgroupid" , $user['groupid'] , $logincookie);
		esetcookie("mlrnd" , $rnd , $logincookie);
		
		qGetLoginAuthstr($user['userid'] , $username , $rnd , $user['groupid'] , $logincookie);
		
		ecmsEmptyShowKey('checkloginkey');
		esetcookie("returnurl","");
		
		return $user;
		
	}
	
	/* 会员登出 */
	public function user_logout(){
		EmptyEcmsCookie();
	}
	
	/* ecms 验证码验证 */
	public function ecms_check_showkey($varname , $postval , $ecms=0){
		list($cktime , $pass , $val) =  explode(',',getcvar($varname,$ecms));
		$time=time();
		if($cktime > $time || $time-$cktime > $this->public_r['keytime']*60){
			return 'timeout';
		}
		if( empty($postval) || md5($postval)<>$val ){
			return 'fail';
		}
		$checkpass = md5(md5(md5($postval).'EmpireCMS'.$cktime).$this->public_r['keyrnd']);
		if( $checkpass <> $pass ){
			return 'fail';
		}
		return true;
	}
	
	
	function send_http_status($code) {
		static $_status = array(
			// Informational 1xx
			100 => 'Continue',
			101 => 'Switching Protocols',
			// Success 2xx
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			// Redirection 3xx
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Moved Temporarily ',  // 1.1
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			// 306 is deprecated but reserved
			307 => 'Temporary Redirect',
			// Client Error 4xx
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			// Server Error 5xx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			509 => 'Bandwidth Limit Exceeded'
		);
		if(isset($_status[$code])) {
			header('HTTP/1.1 '.$code.' '.$_status[$code]);
			// 确保FastCGI模式下正常
			header('Status:'.$code.' '.$_status[$code]);
		}
	}
}