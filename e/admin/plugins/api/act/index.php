<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>API管理</title>
<link href="../../adminstyle/<?=$loginadminstyleid?>/adminstyle.css" rel="stylesheet" type="text/css">
<script>
function del(name){
	if(confirm('确认要删除此接口吗，删除后将无法恢复!')){
		self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=del&m='+name;
	}
}
</script>
</head>
<body>
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr>
    <td>位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">API管理</a></td>
		 <td><div align="right" class="emenubutton">
        <input type="button" value="增加接口" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=form';">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="权限设置" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=level';">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="基本设置" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=conf';">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="自定义函数库" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=function';">
      </div></td>
  </tr>
</table>

<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
  <tr class="header">
    <td width="150" align="center">模块</td>
    <td width="200"  align="center" height="25">名称</td>
		<td  height="25" >说明</td>
		<td width="100" align="center">开启状态</td>
    <td width="240" height="25"  align="center">操作</td>
  </tr>
	<?php
	if(empty($api_conf['list'])){
	?>
	<tr bgcolor="#FFFFFF" align="center" height="30">
		<td colspan="5" style="color:#555;">没有相关数据</td>
	</tr>
	<?php
	}else{
		foreach($api_conf['list'] as $k=>$r){
	?>
	<tr bgcolor="#FFFFFF" onmouseout="this.style.backgroundColor='#ffffff'" onmouseover="this.style.backgroundColor='#f9f9f9'">
		<td align="center"><a href="index.php<?=$ecms_hashur['whehref']?>&act=list&m=<?=$k?>"><?=$k?></a></td>
		<td align="center"><a href="index.php<?=$ecms_hashur['whehref']?>&act=list&m=<?=$k?>"><?=$r['name']?></a></td>
		<td style="color:#777;"><?=$r['info']?></td>
		<td align="center"><?=($r['open'] ? '是' : '否')?></td>
		<td align="center"><button type="button" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=list&m=<?=$k?>';">管理</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=form&m=<?=$k?>';">修改</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del('<?=$k?>')">删除</button></td>
	</tr>
	<?php
		}
	}
	?>
</table>
</body>
</html>