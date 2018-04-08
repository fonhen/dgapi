<?php
function api_post($act='edit'){
	global $api_conf , $api_conf_dir , $extend_dir , $ecms_hashur;
	$list = $api_conf['list'];
	$url = 'index.php'.$ecms_hashur['whehref'];
	if($act === 'del'){
		//删除模块
		$m = api_param_get('m');
		if(empty($m) || !isset($list[$m])){
			printerror2('要删除的接口不存在');
		}else{
			unset($list[$m]);
			$api_conf['list'] = $list;
			if(api_build_conf($api_conf_dir , $api_conf)){
				api_del_dir($extend_dir . $m);
				printerror2('删除成功' , $url.'&act=index&t='.time());
			}else{
				printerror2('请检查文件操作权限');
			}
		}
	}else if($act === 'edit'){
		//添加与更新模块
		$m = api_param_get('m');
		if($m !== '' && !isset($list[$m])){
			printerror2('数据不存在');
		}
		$data = array();
		$data['m'] = strtolower(api_param_post('m'));
		if($data['m'] === ''){
			printerror2('模块不能为空');
		}elseif(!preg_match("/^[a-z]+$/" , $data['m'])){
			printerror2('模块名只能由英文字母组成');
		}elseif($m !== $data['m'] && isset($list[$data['m']])){
			printerror2('模块已存在');
		}
		
		$data['name'] = api_param_post('name');
		$data['info'] = api_param_post('info');
		$data['info'] = api_param_post('info');
		$data['open'] = (int)api_param_post('open') ? 1 : 0;
		
		$list[$data['m']] = $data;
		if($m !== '' && $m !== $data['m']){
			unset($list[$m]);
			if(is_dir($extend_dir . $m)){
				$res = @rename($extend_dir . $m , $extend_dir . $data['m']);
				if(false === $res){
					printerror2('请检查文件夹操作权限');
				}
			}
		}
		$api_conf['list'] = $list;
		if(api_build_conf($api_conf_dir , $api_conf)){
			printerror2('操作成功' , $url.'&act=form&m='.($m ? $data['m'] : '').'&t='.time());
		}else{
			printerror2('请检查文件操作权限');
		}
	}else if($act === 'savelevel'){
		//权限设置
		$level = api_param_post('level' , array() , false);
		if(is_array($level)){
			$arr = array();
			foreach($level as $r){
				$arr[(int)$r] = !!$r;
			}
			if(api_build_conf('./conf.php' , $arr)){
				printerror2('操作成功');
			}else{
				printerror2('操作失败');
			}
		}else{
			printerror2('非法操作');
		}
	}else if($act === 'saveconf'){
		//基本设置
		$module = api_param_post('module');
		$controller = api_param_post('controller');
		if(!preg_match("/^[a-zA-Z_]+$/" , $module)){
			printerror2('模块变量名不合法');
		}
		if(!preg_match("/^[a-zA-Z_]+$/" , $controller)){
			printerror2('控制器变量名不合法');
		}
		if($controller === $module){
			printerror2('模块变量名与控制器变量名不能相同');
		}
		$api_conf['module'] = $module;
		$api_conf['controller'] = $controller;
		if(api_build_conf($api_conf_dir , $api_conf)){
			printerror2('操作成功');
		}else{
			printerror2('请检查文件操作权限');
		}
	}else if($act === 'savec'){
		//添加与更新控制器
		$m = api_param_get('m');
		if($m === '' || !isset($list[$m])){
			printerror2('模块不存在');
		}
		$c = api_param_get('c');
		$c_dir = $extend_dir . $m . '/';
		$c_conf_dir = $c_dir . '_conf.php';
		$c_conf = @require($c_conf_dir);
		if(!is_array($c_conf)){
			printerror2('控制器配置获取失败');
		}

		$data = array(
			'c' => strtolower(api_param_post('c')),
			'name' => api_param_post('name'),
			'info' => api_param_post('info'),
			'open' => api_param_post('open' , 0 , 'intval') ? 1 : 0
		);
		$code = api_param_post('code');
		
		if($data['c'] === ''){
			printerror2('控制器不为能空');
		}else if(!preg_match("/^[a-zA-Z]+$/" , $data['c'])){
			printerror2('控制器只能由字母组成');
		}else if($c !== $data['c'] && isset($c_conf[$data['c']])){
			printerror2('控制器已存在');
		}else{
			$c_conf[$data['c']] = $data;
		}
		
		if($data['name'] === ''){
			printerror2('名称不为能空');
		}
		
		$c_file_dir = $c_dir . $data['c'] . '.php';
		
		if( $c!== '' && $c !== $data['c']){
			unset($c_conf[$c]); //删除之前的
			if(is_file($c_dir . $c . '.php') && false === @rename($c_dir . $c . '.php' , $c_file_dir)){
				printerror2('控制器文件没有操作权限');
			}
		}
		
		if(!api_build_conf($c_conf_dir , $c_conf)){
			printerror2('控制器配置保存失败');
		}
		
		if(false !== file_put_contents($c_file_dir , $code)){
			printerror2('操作成功' , $url.'&act=editc&m='.$m.'&c='.($c ? $data['c'] : '').'&t='.time());
		}else{
			printerror2('操作失败');
		}
		
	}else if($act === 'delc'){
		//删除控制器
		$m = api_param_get('m');
		if($m === '' || !isset($list[$m])){
			printerror2('模块不存在');
		}
		$c = api_param_get('c');
		$c_dir = $extend_dir . $m . '/';
		$c_conf_dir = $c_dir . '_conf.php';
		$c_conf = @require($c_conf_dir);
		if(!is_array($c_conf)){
			printerror2('控制器配置获取失败');
		}
		
		if($c === '' || !isset($c_conf[$c])){
			printerror2('要删除控制器不存在');
		}
		
		$c_file_dir = $c_dir . $c . '.php';
		
		unset($c_conf[$c]);
		
		if(!api_build_conf($c_conf_dir , $c_conf)){
			printerror2('控制器配置保存失败');
		}
		
		if(is_file($c_file_dir) && false === @unlink($c_file_dir)){
			printerror2('删除控制器失败');
		}else{
			printerror2('删除控制器成功' , $url . '&act=list&m='.$m.'&t='.time());
		}
		
	}else if($act === 'savef'){
		//更新自定义函数库
		$code = api_param_post('code');
		$m = api_param_get('m');
		if($m !== '' && !isset($list[$m])){
			printerror2($m.'模块不存在');
		}
		if(strpos($code , '<?php') !== 0){
			printerror2('代码必须以已 &lt;?php 开头');
		}
		$code_file_dir = $extend_dir . ($m ? $m . '/_' : '') . 'function.php';
		if(false !== @file_put_contents($code_file_dir , $code)){
			printerror2('操作成功');
		}else{
			printerror2('保存文件失败');
		}
	}
}

function api_check_level($gid){
	global $conf;
	if(empty($conf) || !isset($conf[$gid]) || !$conf[$gid]){
		printerror2('权限不足!');
	}
}

function api_del_dir($dir = ''){
	$res = true;
	if( is_dir($dir) ){
		$dh  = @opendir($dir);
		if(false !== $dh ){
			while(false !== ($filename = readdir($dh))){
				if($filename !== '.' && $filename !== '..'){
					$filedir = $dir .'/'. $filename;
					if(is_dir($filedir)){
						api_del_dir($filedir);
					}else{
						@chmod($filedir , 0777);
						@unlink($filedir);
					}
				}
			}
			if(!readdir($dh)){
				@rmdir($dir);	
			}
			@closedir($dh);	
		}else{
			$res = false;
		}
	}else{
		$res = false;
	}
	return $res;
}

function api_del_file($path = ''){
	if(is_file($filepath)){
		return @unlink($path);
	}else{
		return true;
	}
}

function api_build_conf($path , $conf = array()){
	$content = "<?php"."\r\n"."return ".var_export($conf,true).";";
	return file_put_contents($path , $content);
}

function api_param_post($name = '' , $default = '' , $fn = 'trim'){
	$value = isset($_POST[$name]) ? (get_magic_quotes_gpc() ? stripslashes($_POST[$name]) : $_POST[$name]) : $default;
	if(!empty($fn) && function_exists($fn)){
		return $fn($value);
	}else{
		return $value;
	}
}

function api_param_get($name = '' , $default = '' , $fn = 'trim'){
	$value = isset($_GET[$name]) ? $_GET[$name] : $default;
	if(!empty($fn) && function_exists($fn)){
		return $fn($value);
	}else{
		return $value;
	}
}

function api_url($m , $c){
	global $extend_dir , $api_conf;
	return $extend_dir . 'index.php?'.$api_conf['module'].'='.$m.'&'.$api_conf['controller'].'='.$c;
	
}