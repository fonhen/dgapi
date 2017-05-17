<?php
$m = api_param_get('m');
if($m === '' || !isset($api_conf['list'][$m])){
	printerror2('参数错误');
}
$m_data = $api_conf['list'][$m];
$c_dir = $extend_dir . $m . '/';
if(!is_dir($c_dir)){
	mkdir($c_dir , 0777);
}
if(!is_dir($c_dir)){
	printerror2('文件夹创建失败,请修改相应权限');
}
$c_conf_dir = $c_dir . '_conf.php';
if(!is_file($c_conf_dir)){
	api_build_conf($c_conf_dir);
}
if(!is_file($c_conf_dir)){
	printerror2('配置文件建议失败,请修改目录权限');
}
$c_conf = require($c_conf_dir);

$c = api_param_get('c');
if($c === ''){
	$title = '添加控制器';
	$code = '';
	$data = array(
		'c' => '',
		'name' => '',
		'open' => 1,
		'info' => ''
	);
}else if(isset($c_conf[$c])){
	$data = $c_conf[$c];
	$title = '编辑控制器[' . $c .']';
	$code_dir = $c_dir . $c . '.php';
	if(!is_file($code_dir)){
		file_put_contents($code_dir , '');
	}
	if(is_file($code_dir)){
		$code = @file_get_contents($c_dir . $c . '.php');	
	}else{
		$code = false;
	}
	if(false === $code){
		printerror2('控制器无法创建或读取');
	}
}else{
	printerror2('控制器不存在');
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>API管理</title>
<link href="../../adminstyle/<?=$loginadminstyleid?>/adminstyle.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr>
    <td height="26">位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">API管理</a> &gt; <a href="index.php<?=$ecms_hashur['whehref']?>&act=list&m=<?=$m?>"><?=$m_data['name']?></a>  &gt; <?=($c ? '<a href="'. api_url($m , $c).'" target="_blank">'.$title.'</a>' : $title)?></td>
  </tr>
</table>

<form name="form1" method="post" action="index.php<?=$ecms_hashur['whehref']?>&act=savec&m=<?=$m?>&c=<?=$c?>">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
  <tr class="header">
    <td height="25" colspan="2"><?=$title?></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td width="150" height="25">控制器:(*)</td>
    <td height="25"><input name="c" type="text" value="<?=$c?>" size="42"> (由小写字母组成)</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">状态</td>
    <td height="25"><input type="checkbox" value="1" name="open" <?=($data['open'] ? 'checked="checked"' : '')?>>开启</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">名称:</td>
    <td height="25"><input name="name" type="text" value="<?=$data['name']?>" size="42"></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">说明</td>
    <td height="25"><textarea name="info" cols="60" rows="3"><?=$data['info']?></textarea></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td>程序代码</td>
    <td><textarea name="code" style="width:100%; height:500px;"><?=$code?></textarea></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">&nbsp;</td>
    <td height="25">
			<button type="submit">提交</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="reset">重置</button>
		</td>
  </tr>
</table>

</form>
</body>
</html>