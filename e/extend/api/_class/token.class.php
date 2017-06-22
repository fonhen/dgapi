<?php
class api_token {
	
	private $config = array(
		"token"	=> 'token',
		"time" => 't',
		"timeout" => 300,
		"key" => 'dgapi-token'
	);
	
	public function __construct($config = array()){
		$this->config = array_merge($this->config, $config);
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
	
	public function build($param = array()){
		global $config;
		$param = !is_array($param) ? array() : $param;
		$arr = array($config['module'] , $config['controller'] , $this->config['token']);
		foreach($arr as $k){
			if(isset($param[$k])){
				unset($param[$k]);
			}
		}
		ksort($param);
		return md5($this->query($param , false) . '&token=' . $this->key);
	}
	
	public function query($param , $t = true){
		$str = '';
		foreach($param as $k=>$v){
			$str .= $str ? '&'.$k.'='.$v : $k.'='.$v;
		}
		if(true === $t){
			$str .= '&'.$this->config['token'].'='.$this->build($param);
		}
		return $str;
	}
	
	public function check(){
		$token = isset($_GET[$this->config['token']]) ? $_GET[$this->config['token']] : '';
		$time = isset($_GET[$this->config['time']]) ? (int)$_GET[$this->config['time']] : 0;
		if($time > 0 && !empty($token) && $this->build($_GET) === $token){
			return time() - $time <= $this->config['timeout'] ? 1 : -1;
		}else{
			return 0;
		}
	}
}