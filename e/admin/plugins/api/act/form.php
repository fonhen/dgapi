<?php
$m = api_param_get('m');
if($m === ''){
	$title = '添加';
	$data = array(
		'open' => 1,
		'name' => '',
		'info' => ''
	);
}else if(isset($api_conf['list'][$m])){
	$title = '编辑';
	$data = $api_conf['list'][$m];
}else{
	printerror2('数据不存在');
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>API管理</title>
<link href="../../adminstyle/<?=$loginadminstyleid?>/adminstyle.css" rel="stylesheet" type="text/css">
</head>
<body style="min-width:900px;">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr>
    <td>位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">API管理</a> &gt; <?=$title?>API</td>
  </tr>
</table>
<form name="form1" method="post" action="index.php<?=$ecms_hashur['whehref']?>&act=edit&m=<?=$m?>">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
  <tr class="header">
    <td height="25" colspan="2"><?=$title?>API</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td width="150" height="25">模块:(*)</td>
    <td height="25"><input name="m" type="text" value="<?=$m?>" size="42"> (由小写字母组成)</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">名称:(*)</td>
    <td height="25"><input name="name" type="text" value="<?=$data['name']?>" size="42"></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">说明</td>
    <td height="25"><textarea name="info" cols="60" rows="6"><?=$data['info']?></textarea></td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">状态</td>
    <td height="25"><input type="checkbox" value="1" name="open" <?=($data['open'] ? 'checked="checked"' : '')?>>开启</td>
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