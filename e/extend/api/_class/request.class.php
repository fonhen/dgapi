<?php
class api_request
{
    public function is_get()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='GET';
    }
    
    public function is_post()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='POST';
    }
    
    public function is_delete()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='DELETE';
    }
    
    public function is_head()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='HEAD';
    }
    
    public function is_put()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='PUT';
    }
    
    public function is_trace()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='TRACE';
    }
    
    public function is_option()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])==='OPTION';
    }
    
    function is_ajax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])=='XMLHTTPREQUEST';
    }
    
    public function method()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';
    }
    
    public function get($name , $default = '' , $fn = 'trim')
    {
        $value = isset($_GET[$name]) ? $_GET[$name] : $default;
        return !empty($fn) && function_exists($fn) ? $fn($value) : $value;
    }

    public function post($name , $default = '' , $fn = 'trim')
    {
        $value = isset($_POST[$name]) ? $_POST[$name] : $default;
        return !empty($fn) && function_exists($fn) ? $fn($value) : $value;
    }

    public function param($name , $default = '' , $fn = 'trim')
    {
        $value = isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $default);
        return !empty($fn) && function_exists($fn) ? $fn($value) : $value;
    }
    
    public function put($name = '' , $default = '' , $fn = 'trim')
    {
        return $this->input($name , $default , $fn);
    }
    
    public function delete($name = '' , $default = '' , $fn = 'trim')
    {
        return $this->input($name , $default , $fn);
    }

    public function input($name = '' , $default = '' , $fn = 'trim')
    {
        $input = json_decode(file_get_contents('php://input') , true);
        $input = !empty($input) ? $input : array();
        if(empty($name)){
            return $input;
        }else if(!empty($input)){
            $value = isset($input[$name]) ? $input[$name] : '';
            return !empty($fn) && function_exists($fn) ? $fn($value) : $value;
        }else{
            return null;
        }
    }
    
    public function ip()
    {
        static $ip = null;
        if ($ip !== null) return $ip[0];
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $long = sprintf("%u",ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[0];
    }
}