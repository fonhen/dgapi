<?php
$m = api_param_get('m');
if($m === '' || !isset($api_conf['list'][$m])){
	printerror2('参数错误');
}
$data = $api_conf['list'][$m];
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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>API管理</title>
<link href="../../adminstyle/<?=$loginadminstyleid?>/adminstyle.css" rel="stylesheet" type="text/css">
<script>
function delc(name){
	if(confirm('确认要删除此控制器吗，删除后将无法恢复!')){
		self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=delc&m=<?=$m?>&c=' + name;
	}
}
</script>
</head>
<body>
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr>
    <td>位置：<a href="index.php<?=$ecms_hashur['whehref']?>&act=index">API管理</a> &gt; <a href="index.php<?=$ecms_hashur['whehref']?>&act=list&m=<?=$m?>"><?=$data['name']?></a> </td>
		 <td><div align="right" class="emenubutton">
        <input type="button" value="增加控制器" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=editc&m=<?=$m?>';">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="自定义函数库" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=function&m=<?=$m?>';">
      </div></td>
  </tr>
</table>

		
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="tableborder">
	<tr class="header">
		<td width="100" align="center">预览</td>
		<td width="150" align="center">控制器</td>
		<td width="200" align="center">名称</td>
		<td>说明</td>
		<td width="100" align="center">状态</td>
		<td width="240" height="25"  align="center">操作</td>
	</tr>
	<?php
	if(empty($c_conf)){
	?>
	<tr bgcolor="#FFFFFF" align="center" height="30">
		<td colspan="6" style="color:#555;">没有相关数据</td>
	</tr>
	<?php
	}else{
		foreach($c_conf as $k=>$r){
	?>
	<tr bgcolor="#FFFFFF" onmouseout="this.style.backgroundColor='#ffffff'" onmouseover="this.style.backgroundColor='#f9f9f9'">
		<td align="center"><a href="<?=api_url($m , $k)?>" target="_blank">[预览]</a></td>
		<td align="center"><a href="index.php<?=$ecms_hashur['whehref']?>&act=editc&m=<?=$m?>&c=<?=$k?>"><?=$k?></a></td>
		<td align="center"><a href="index.php<?=$ecms_hashur['whehref']?>&act=editc&m=<?=$m?>&c=<?=$k?>"><?=$r['name']?></a></td>
		<td style="color:#666;"><?=$r['info']?></td>
		<td align="center"><?=($r['open'] ? '正常' : '已关闭')?></td>
		<td align="center"><button type="button" onclick="self.location.href='index.php<?=$ecms_hashur['whehref']?>&act=editc&m=<?=$m?>&c=<?=$k?>';">修改</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="delc('<?=$k?>')">删除</button></td>
	</tr>
	<?php
		}
	}
	?>
</table>

<br/>

<div>注：预览功能,仅仅只是简单的仿问到API对应的模块与控制器上,其它参数请自行拼写</div>
		
	
</body>
</html>
