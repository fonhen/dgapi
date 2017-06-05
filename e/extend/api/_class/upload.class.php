<?php
class api_upload {
	
	private $config = array(
		"maxsize"	=> 0,	//文件大小限制
		"mimes" => array(),	//文件MiMe类型
		"exts" => array(), //允许的后缀
		"rootpath" => ""	//保存路径
	);
	
	private $error = false;
	
	public function __construct($config = array()){
		$this->config = array_merge($this->config, $config);
		//格式化配置
		if(!empty($this->config['mimes'])){
				if(is_string($this->mimes)) {
						$this->config['mimes'] = explode(',', $this->mimes);
				}
				$this->config['mimes'] = array_map('strtolower', $this->mimes);
		}
		if(!empty($this->config['exts'])){
				if (is_string($this->exts)){
						$this->config['exts'] = explode(',', $this->exts);
				}
				$this->config['exts'] = array_map('strtolower', $this->exts);
		}
		$this->config['maxsize'] = (int)$this->config['maxsize'];
	}
	
	public function __get($name) {
		return $this->config[$name];
	}

	public function __set($name,$value){
		if(isset($this->config[$name])){
			$this->config[$name] = $value;
		}
	}
	
	public function getError(){
		return $this->error;
	}
	
	public function upload($file , $filename = '' , $savepath = ''){
		if(empty($file) || !is_array($file)){
			$this->error = '未选择上传文件';
			return false;
		}
		if($file['error']) {
			$this->error($file['error']);
			return false;
    }
		if(empty($file['name'])){
			$this->error = '未知上传错误';
			return false;
		}
		if(!is_uploaded_file($file['tmp_name'])) {
			$this->error = '非法上传文件';
			return false;
		}
		$file['size'] = (int)$file['size'];
		if($this->config['maxsize'] > 0 && $this->config['maxsize'] < $file['size']){
			$this->error = '上传文件大小不符';
			return false;
		}
		
    if(function_exists('finfo_open')){
      $finfo = finfo_open( FILEINFO_MIME_TYPE );
			$file['type'] = finfo_file($finfo ,$file['tmp_name']);
    }
		
		if(!$this->checkMime($file['type'])){ 
			$this->error = '上传文件MIME类型不允许';
			return false;
		}

		$file['ext'] = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		
		if(!$this->checkExt($file['ext'])){
			$this->error = '上传文件后缀不允许';
			return false;
		}
		
		//严格检测图片
		if(in_array($file['ext'], array('gif','jpg','jpeg','bmp','png','swf'))) {
			$imginfo = getimagesize($file['tmp_name']);
			if(empty($imginfo) || ($file['ext'] == 'gif' && empty($imginfo['bits']))){
				$this->error = '非法图像文件！';
				return false;
			}
		}
		
		//开始保存文件
		$filename = $filename === '' ? uniqid() : $filename;
		$savepath = $savepath === '' ? ($this->config['rootpath'] !=='' ? $this->config['rootpath'] : '/d/file/').date('Ymd').'/' : $savepath;
		$savepath = str_replace('//' , '/' , '../../../'.$savepath.'/');
		
		$filepath = $savepath . $filename . ($file['ext'] !== '' ? '.' . $file['ext'] : '');
		$filepath = str_replace('//' , '/' , $filepath);
		
		if(!is_dir($savepath) && !@mkdir($savepath , 0777 , true)){
			$this->error = "上传目录创建失败";
			return false;
		}
		
		if(is_dir($savepath) && !is_writable($savepath)){
			$this->error = "上传目录没有写入权限";
			return false;
		}
		
		var_dump($filepath);
		
		if(!@move_uploaded_file($file['tmp_name'], $filepath)){
			$this->error = '文件上传保存错误！';
			return false;
		}
		
		$res = array(
			'filename' => $filename,
			'ext' => $file['ext'],
			'savepath' => $savepath,
			'filepath' => substr($filepath , 8),
			'size' => $file['size']
		);
		
		return $res;
	}

	
	
	public function checkMime($mime){
		return empty($this->config['mimes']) ? true : in_array(strtolower($mime), $this->mimes);
	}
	
	public function checkExt($ext){
		return empty($this->config['exts']) ? true : in_array(strtolower($ext), $this->exts);
	}
	
	
	private function error($no) {
		switch($no){
			case 1:
				$this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
				break;
			case 2:
				$this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
				break;
			case 3:
				$this->error = '文件只有部分被上传';
				break;
			case 4:
				$this->error = '没有文件被上传';
				break;
			case 6:
				$this->error = '找不到临时文件夹';
				break;
			case 7:
				$this->error = '文件写入失败';
				break;
			default:
				$this->error = '未知上传错误';
		}
  }
	
}