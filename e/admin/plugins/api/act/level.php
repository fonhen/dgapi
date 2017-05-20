<?php
defined('EmpireCMSAdmin') or die;
$sql = $empire->query("select groupid,groupname from {$dbtbpre}enewsgroup order by groupid limit 100");
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
    <td>位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">API管理</a> &gt; 权限管理</td>
  </tr>
</table>
<form name="form1" method="post" action="index.php<?=$ecms_hashur['whehref']?>&act=savelevel">
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
  <tr class="header">
    <td height="25" colspan="2">权限管理 (选中代表具有权限)</td>
  </tr>
	<?php
		while($r=$empire->fetch($sql)){
			$groupid = (int)$r['groupid'];
			$checked = isset($conf[$groupid]) && $conf[$groupid];
	?>
	<tr bgcolor="#FFFFFF">
    <td width="300" height="25"><?=$r['groupname']?><input type="hidden" value="<?=$groupid?>" name="gid[]" /></td>
    <td height="25"><input type="checkbox" name="level[]" value="<?=$groupid?>" <?=($checked ? 'checked' : '')?>></td>
  </tr>
	<?php
		}
	?>
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