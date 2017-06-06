<?php
define('EmpireCMSAdmin','1');
require("../../../class/connect.php");
require("../../../class/db_sql.php");
require("../../../class/functions.php");
$extend_dir = '../../../extend/api/';
$api_conf_dir = $extend_dir . "conf.php";
require("./function.php");
$link=db_connect();
$empire=new mysqlquery();
$editor=2;
//验证用户
$lur=is_login();
$logininid=$lur['userid'];
$loginin=$lur['username'];
$loginrnd=$lur['rnd'];
$loginlevel=$lur['groupid'];
$loginadminstyleid=$lur['adminstyleid'];
//ehash
$ecms_hashur=hReturnEcmsHashStrAll();
$ecms_hashur['whehref'] = !isset($ecms_hashur['whehref']) || trim($ecms_hashur['whehref']) === '' ? '?_hash=' : $ecms_hashur['whehref'];

$api_conf= require($extend_dir . "conf.php");

$act = api_param_get('act');

//检查是否已安装
if(!is_file('./install.lock')){
	if(api_build_conf('./conf.php' , array($loginlevel => true))){
		file_put_contents('./install.lock','');	
	}else{
		exit('安装失败,请检查目录权限!');
	}
}
$conf = require("./conf.php");

//验证权限
api_check_level($loginlevel);

if($act === 'form' || $act === 'list' || $act === "level" || $act === "conf" || $act === "editc" || $act === 'function'){
	require('./act/'.$act.'.php');
}else if($act === 'del' || $act === 'edit' || $act === 'savelevel' || $act === 'saveconf' || $act === 'delc' || $act === 'savec' || $act === 'savef'){
	api_post($act);
}else{
	require('./act/index.php');
}





