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
    <td>位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">API管理</a> &gt; 基本设置</td>
  </tr>
</table>
<form method="post" action="index.php<?=$ecms_hashur['whehref']?>&act=saveconf">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
  <tr class="header">
    <td height="25" colspan="2">基本设置</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td width="200" height="25">默认模块获取变量</td>
    <td height="25"><input name="module" type="text" value="<?=$api_conf['module']?>" size="42"> 字母,区分大小写</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">默认控制器获取变量</td>
    <td height="25"><input name="controller" type="text" value="<?=$api_conf['controller']?>" size="42"> 字母,区分大小写</td>
  </tr>
	<tr bgcolor="#FFFFFF">
    <td height="25">示例(注意红色部分)</td>
    <td height="25" style="color:#555;">/e/extend/index.php?<b style="color:red;"><?=$api_conf['module']?></b>=[Api模块]&<b style="color:red;"><?=$api_conf['controller']?></b>=[Api控制器]<br/></td>
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