<?php
defined('EmpireCMSAdmin') or die;
$m = api_param_get('m');
if($m !== '' && !isset($api_conf['list'][$m])){
	printerror2($m.'模块不存在');
}
$code_file_dir = $extend_dir . ($m ? $m . '/_' : '') . 'function.php';
if(!is_file($code_file_dir)){
	file_put_contents($code_file_dir , '');
}
$code = @file_get_contents($code_file_dir);
if(false === $code){
	printerror2('读取数据失败');
}

$title = $m ? '&gt; <a href="index.php'.$ecms_hashur['whehref'].'&act=list&m='.$m.'">'.$api_conf['list'][$m]['name'].'</a>' : '';
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
    <td height="26">位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">API管理</a><?=$title?> &gt; 自定义函数库</td>
  </tr>
</table>

<form name="form1" method="post" action="index.php<?=$ecms_hashur['whehref']?>&act=savef&m=<?=$m?>">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
  <tr class="header">
    <td height="25">自定义函数库</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td><textarea name="code" style="width:100%; height:600px;"><?=$code?></textarea></td>
  </tr>
	<tr bgcolor="#f4f4f4">
    <td height="25" align="center">
			<button type="submit">提交</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="reset">重置</button>
		</td>
  </tr>
</table>
<div style="padding:10px 0;">
	此功能需要有一定php基础,如果出错可能会引起相关api失效
</div>

</form>
</body>
</html>