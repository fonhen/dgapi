<?php
require('../../class/EmpireCMS_version.php');
require('../../class/connect.php');
require('../../class/db_sql.php');
require("../../data/dbcache/class.php");
require("../../data/dbcache/MemberLevel.php");
require('../../class/userfun.php');
require('./global.php');
require("./api.class.php");
require("./function.php");
$link = db_connect();
$empire = new mysqlquery();
$api = new api();
$editor=1;
$config = array();
if(!is_file("./conf.php")){
	$api->error('插件配置文件不存在');
}
$config = require("conf.php");

if(!isset($_GET[$config['module']]) || !isset($_GET[$config['controller']])){
	$api->error('参数错误');
}
$m = $_GET[$config['module']];
$c = $_GET[$config['controller']];
if($m === '' || !isset($config['list'][$m])){
	$api->error('模块不存在');
}

$m_list = $config['list'][$m];
if(!$m_list['open']){
	$api->error('模块已被禁用');
}


$m_conf_file = './'.$m.'/_conf.php';
if(!is_file($m_conf_file)){
	$api->error('模块配置文件加载失败');
}
$m_conf = require($m_conf_file);
if( $c==='' || !isset($m_conf[$c])){
	$api->error('控制器不存在');
}
$c_conf = $m_conf[$c];
if(!$c_conf['open']){
	$api->error('控制器已被禁用');
}

$api_file = './'.$m.'/'.$c.'.php';
if(!is_file($api_file)){
	$api->error('控制器加载失败');
}

define('api_m' , $m);
define('api_c' , $c);

$m_fun_file = './'.$m.'/_function.php';
if(is_file($m_fun_file)){
	require($m_fun_file);
}
require($api_file);

db_close();
$empire = null;
$api = null;







