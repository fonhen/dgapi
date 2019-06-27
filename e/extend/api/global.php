<?php



/*
 * =============================================================
 * 验证类函数
 * 函数名格式 : api_check_功能名称
 * =============================================================
 */
 
//验证码验证 1:成功 , -1:超时 0:失败
function api_check_code($name , $val = '' , $ecms = 0){
	global $public_r;
    if((float)EmpireCMS_VERSION < 7.5){
        list($cktime , $pass , $code) =  explode(',',getcvar($name , $ecms));
    
        $time=time();
        if($cktime > $time || $time-$cktime > $public_r['keytime']*60){
            return -1;
        }
        if( empty($val) || md5($val) !== $code ){
            return 0;
        }
        
        $checkpass = md5(md5(md5($val).'EmpireCMS'.$cktime).$public_r['keyrnd']);
        
        if( $checkpass !== $pass ){
            return 0;
        }
        return 1;
    }else{
        list($cktime , $pass , $code) =  explode(',',getcvar($name , $ecms));
        $time=time();
        if($cktime > $time || $time-$cktime > $public_r['keytime']*60){
            return -1;
        }
        $checkpass=md5('d!i#g?o-d-'.md5(md5($name.'E.C#M!S^e-'.$val).'-E?m!P.i#R-e'.$cktime).$public_r['keyrnd'].'P#H!o,m^e-e');
        if( empty($val) || $checkpass !== $pass ){
            return 0;
        }else{
            return 1;
        }
    }
}
 
//时间验证
function api_check_timeclosedo($ecms){
	global $public_r;
	if(stristr($public_r['timeclosedo'],','.$ecms.',') && strstr($public_r['timeclose'],','.date('G').',')){
		return false;
	}
	return true;
}

//IP验证
function api_check_ip($doing){
	global $public_r,$empire,$dbtbpre;
	$pr=$empire->fetch1("select opendoip,closedoip,doiptype from {$dbtbpre}enewspublic limit 1");
	if(!strstr($pr['doiptype'],','.$doing.',')){
		return true;
	}
	$userip=egetip();
	//允许IP
	if($pr['opendoip']){
		$close=1;
		foreach(explode("\n",$pr['opendoip']) as $ctrlip){
			if(preg_match("/^(".preg_quote(($ctrlip=trim($ctrlip)),'/').")/",$userip)){
				$close=0;
				break;
			}
		}
		if($close==1){
			return false;
		}
	}
	//禁止IP
	if($pr['closedoip']){
		foreach(explode("\n",$pr['closedoip']) as $ctrlip){
			if(preg_match("/^(".preg_quote(($ctrlip=trim($ctrlip)),'/').")/",$userip)){
				return false;
			}
		}
	}
	return true;
}

//来源验证
function api_check_posturl(){
	global $public_r;
	if($public_r['canposturl']){
		$r=explode("\r\n",$public_r['canposturl']);
		$count=count($r);
		$b=0;
		for($i=0;$i<$count;$i++){
			if(strstr($_SERVER['HTTP_REFERER'],$r[$i])){
				$b=1;
				break;
			}
		}
		if($b==0){
			return false;
		}
	}
	return true;
}