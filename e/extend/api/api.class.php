<?php
class api {
	public $empire , $public_r , $ecms_config , $dbtbpre , $level_r;
	
	public function __construct(){
		global $public_r, $empire, $dbtbpre, $ecms_config , $level_r;
		$this->empire = $empire;
		$this->public_r = $public_r;
		$this->dbtbpre = $dbtbpre;
		$this->ecms_config = $ecms_config;
		$this->level_r = $level_r;
	}
	
	public function __get($name){
		return false;
	}
	
	public function __set($name , $value){
		return false;
	}
	
	/* load */
	public function load($name = '' , $config = array()){
		$file = './_class/' . $name . '.class.php';
		if(!is_file($file)){
			$this->error($name.'.class.php 不存在');
		}else{
			require_once($file);
		}
		$cname = 'api_'.$name;
		if(!class_exists($cname)){
			$this->error('api_'.$name.' 未定义');
		}
		$config = is_array($config) ? $config : array();
		return @new $cname($config);
	}
	
	public function import($name='' , $model='' , $assign = array()){
		if(is_array($model)){
			$assign = $model;
			$model = '';
		}
		$controller = $this->controller($name , $model);
		if(!empty($assign)){
			foreach($assign as $key=>$val){
				$$key = $val;
			}	
		}
		$api = $this;
		include($controller);
	}
	
	/* get */
	public function controller($name = '' , $model = ''){
		$model = $model !== '' ? $model : api_m;
		return './'.$model.'/'.$name.'.php';
	}
	
	/* cache */
	public function cache($name , $fn , $time = 0 , $format = true){
		$time = (int)$time;
		$filename = md5($name . $this->cachehash);
		$filepath = './_cache/'.$filename;
		if(is_bool($fn) && true === $fn){
			@unlink($filepath);
		}else{
			$mtime = is_file($filepath) ? @filemtime($filepath) : false;
			if($mtime && time() - $mtime <= $time){
				$data = @file_get_contents($filepath);
				return $format ? unserialize($data) : $data;
			}else{
				if(is_object($fn)){
					$data = @$fn();
					@file_put_contents($filepath , $format ? serialize($data) : $data);
					return $data;
				}else{
					return false;
				}
			}
		}
	}
	
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
	
	/* output */
	
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
	
	/* database */
	
	public function execute($sql = '' , $exit = true){
		return $exit ? $this->empire->query($sql) : $this->empire->query1($sql);
	}
	
	public function insert($table = '' , $data = array()){
		if(empty($table) || empty($data) || !is_array($data)){
			return false;
		}
		$table = $this->dbtbpre . $table;
		$field = "";
		$value = "";
		foreach($data as $f=>$v){
			$field .= "," . $f;
			$value .= ",'" . RepPostStr($v) ."'";
		}
		$field = substr($field , 1);
		$value = substr($value , 1);
		
		$sql = "insert into {$table} ({$field}) values ({$value});";
		$res = $this->execute($sql , false);
		if(true === $res){
			return $this->empire->lastid();
		}else{
			return false;
		}
	}
	
	public function update($table = '' , $data = '' , $where = '0'){
		if(empty($table) || empty($data) || (!is_string($data) && !is_array($data))){
			return false;
		}
		$table = $this->dbtbpre . $table;
		if(is_string($data)){
			$setField = $data;
		}else{
			$setField = "";
			foreach($data as $f=>$v){
				$v = !is_array($v) ? "'{$v}'" : $v[0]; 
				$setField .= ",{$f}={$v}";
			}
			$setField = substr($setField , 1);
		}
		$sql = "update {$table} set {$setField} where {$where}";
		return $this->execute($sql , false);
	}
	
	public function select($table = '' , $field = '*' , $where = '1' , $limit = 20 , $page = 1 , $orderby = ''){
		if(empty($table)){
			return false;
		}
		$arr = array(
			'table' => '',
			'field' => '*',
			'where' => '1',
			'limit' => 20,
			'page' => 1,
			'orderby' => ''
		);
		$paramType = 0;
		if(is_array($table)){
			$paramType = 1;
			$arr = array_merge($arr , $table);
		}else if(is_array($field)){
			$paramType = 1;
			$arr = array_merge($arr , $field);
			$arr['table'] = $table;
		}
		if($paramType){
			$table = $arr['table'];
			$field = $arr['field'];
			$where = $arr['where'];
			$limit = $arr['limit'];
			$page = $arr['page'];
			$orderby = $arr['orderby'];
		}
		$page = (int)$page;
		$limit = (int)$limit;
		$page = $page > 0 ? $page : 1;
		$limit = $limit > 0 ? $limit : 10;
		$limit = $limit < 1000 ? $limit : 1000;
		$offset = ($page-1) * $limit;
		
		$table = $this->dbtbpre . $table;
		$orderby = $orderby ? 'order by '.$orderby : '';
		$sql = "select {$field} from {$table} where {$where} {$orderby} limit {$offset},{$limit};";
		return $this->query($sql , false);
	}
	
	public function delete($table = '' , $where = '0'){
		if(empty($table)){
			return false;
		}
		$table = $this->dbtbpre . $table;
		$sql = "delete from {$table} where {$where};";
		return $this->execute($sql , false);
	}
	
	public function query($sql = '' , $exit = false){
		$data = $this->execute($sql , $exit);
		if(false === $data){
			return false;
		}
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
	
	public function total($table = '' , $where = '1'){
		$sql = true !== $where ? "select count(*) as total from ".($this->dbtbpre . $table)." where ".$where : $table;
		return $this->empire->gettotal($sql);
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


