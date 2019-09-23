<?php
class api_user {
    public $api;
    public function __construct(){
        $this->api = new api();
    }
    //获取会员信息
    public function find($username = '' , $password = '' , $all = false){
        $where = is_numeric($username) ? 'userid='.$username : 'username="'.$username.'"';
        $dbtbpre = $this->api->dbtbpre;
        $user = $this->api->one("select * from {$dbtbpre}enewsmember where {$where} limit 1");
        if($password && $user){
            if($this->mark_password($password , $user['salt']) !== $user['password']){
                return false;
            }
        }
        if($user && $all){
            $data = $this->api->one("select * from {$dbtbpre}enewsmemberadd where userid=".$user['userid']." limit 1");
            $user = array_merge($user , $data);
        }
        return $user;
    }

    //是否已登陆
    public function islogin($all = true){
        $userid = (int)getcvar('mluserid');
        $username = RepPostVar(getcvar('mlusername'));
        $rnd = RepPostVar(getcvar('mlrnd'));
        if(!$userid || !$username || !$rnd){
            return false;
        }
        $user = $this->find($userid , false , $all);
        //检测用户是否已过期
        if($user['userdate']){
            if($user['userdate'] - time() <= 0){
                $this->set_group($user['userid'] , $user['zgroupid']);
                if($user['zgroupid']){
                    $user['groupid'] = $user['zgroupid'];
                    $user['zgroupid'] = 0;
                }
            }
        }
        return $user;
    }

    //设置用户会员组
    public function set_group($userid , $groupid = 0){
        $groupid = (int)$groupid;
        $userid = (int)$userid;
        return $userid ? $this->api->update("enewsmember" , array('groupid' => $groupid , 'userdate' => 0) , "userid=".$userid) : false;
    }

    //会员登陆
    public function login($user , $time = 0){
        if(empty($user) || !is_array($user)){
            return false;
        }
        $rnd = make_password(20);
        $lasttime = time();
        $user['groupid'] = (int)$user['groupid'];
        $lastip = egetip();
        $lastipport = egetipport();
        $time = $time ? time()+ $time : 0;
        //update
        $this->api->update("enewsmember" , "rnd='{$rnd}'" , "userid=".(int)$user['userid']);
        $this->api->update("enewsmemberadd" , "lasttime='{$lasttime}',lastip='{$lastip}',loginnum=loginnum+1,lastipport='{$lastipport}'" , "userid=".(int)$user['userid']);
        //cookie
        esetcookie("mlusername" , $user['username'] , $time);
        esetcookie("mluserid" , $user['userid'] , $time);
        esetcookie("mlgroupid" , $user['groupid'] , $time);
        esetcookie("mlrnd" , $rnd , $time);
        esetcookie('mlauth', $this->get_auth_code($user['userid'], $user['username'], $rnd, $user['groupid']) , $time);
        return true;
    }

    //会员注册
    public function register($data){
        if(empty($data) || !is_array($data) || !isset($data['username']) || !isset($data['password'])){
            return false;
        }
        //检测用户名是否已存在
        if($this->has_username($data['username'])){
            return false;
        }
        
        //注册时删除userid
        if(isset($data['userid'])){
            unset($data['userid']);
        }
        
        //注册时间
        if(!isset($data['registertime'])){
            $data['registertime'] = time();
        }
        
        //会员组
        if(!isset($data['groupid'])){
            $data['groupid'] = (int)$this->api->public_r['defaultgroupid'];
        }
        
        //userkey
        $data['userkey'] = make_password(12);
        
        //rnd
        $data['rnd'] = make_password(20);
        
        //salt
        $data['salt'] = make_password($this->api->ecms_config['member']['saltnum']);
        
        //密码处理
        $data['password'] = $this->mark_password($data['password'] , $data['salt']);
        
        //checked
        if(!isset($data['checked'])){
            $data['checked'] = @$this->api->level_r[$data['groupid']]['regchecked'] == 1 ? 1 : 0;
            if($data['checked'] && $this->public_r['regacttype']==1){
                $data['checked'] = 0;
            }
        }
        //积分
        $data['userfen'] = isset($data['userfen']) ? (int)$data['userfen'] : (int)$this->api->public_r['reggetfen'];
        
        $userid = $this->api->insert('enewsmember' , $data);
        if(!$userid){
            return false;
        }else{
            $data['userid'] = $userid;
            //副表信息
            $add = array(
                'userid' => $userid,
                'regip' => egetip(),
                'regipport' => egetipport()
            );
            $this->api->insert('enewsmemberadd' , $add);
            return $data;
        }
    }

    //检查会员名是否已存在
    public function has_username($username){
        $username = RepPostVar($username);
        return $this->api->total('enewsmember' , "username = '".$username."'");
    }

    //检查邮箱是否已存在
    public function has_email($email){
        $email = RepPostVar($email);
        return $this->api->total('enewsmember' , "email = '".$email."'");
    }

    //验证码
    public function verify_code($name , $code = false){
        $name = $name === 'login' ? 'checkloginkey' : 'checkregkey';
        if($code !== false){
            //验证
            return api_check_code($name , $code , 0);
        }else{
            //设置
            esetcookie($name , '' , 0 , 0);
        }
    }


    //注销登陆
    public function logout(){
        esetcookie("mlusername","",0);
        esetcookie("mluserid","",0);
        esetcookie("mlgroupid","",0);
        esetcookie("mlrnd","",0);
        esetcookie("mlauth","",0);
    }

    //生成会员密码
    public function mark_password($pw , $salt = ''){
        $type = $this->api->ecms_config['member']['pwtype'];
        if($type == 0){
            return md5($pw);
        }else if($type == 1){
            return $pw;
        }else if($type == 3){
            return substr(md5($pw),8,16);
        }else{
            return md5(md5($pw).$salt);
        }
    }

    //获取登陆验证符
    public function get_auth_code($userid, $username, $rnd, $groupid){
        if( (float)EmpireCMS_VERSION < 7.5 ){
            $code = md5(md5($rnd.'-'.$userid.'-'.$username.'-'.$groupid).'-#empire.cms!-'.$this->api->ecms_config['cks']['ckrndtwo']);
        }else{
            $code = md5(md5($rnd.'--d-i!'.$userid.'-(g*od-'.$username.$this->api->ecms_config['cks']['ckrndtwo'].'-'.$groupid).'-#empire.cms!--p)h-o!me-'.$this->api->ecms_config['cks']['ckrndtwo']);
        }
        return $code;
    }
}