<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

class Input {
    // 全局过滤规则
    static $filter  =   null;

    /**
     * 获取系统变量 支持过滤和默认值
     * @access public
     * @param string $method 输入数据类型
     * @param array $args 参数 array(key,filter,default)
     * @return mixed
     */
    static public function __callStatic($method,$args=[]) {
        static $_PUT    =   null;    
        $name           =   $args[0];        
        $default        =   isset($args[2]) ? $args[2] : null;
        if(strpos($name,'/')){ // 指定修饰符
            list($name,$type)   =   explode('/',$name,2);
        }else{ // 默认强制转换为字符串
            $type   =   's';
        }
        switch(strtolower($method)) {
            case 'get'     :   
                $input =& $_GET;
                break;
            case 'post'    :   
                $input =& $_POST;
                break;
            case 'put'     :   
                if(is_null($_PUT)){
                    parse_str(file_get_contents('php://input'), $_PUT);
                }
                $input  =   $_PUT;
                break;
            case 'param'   :
                switch($_SERVER['REQUEST_METHOD']) {
                    case 'POST':
                        $input  =  $_POST;
                        break;
                    case 'PUT':
                        if(is_null($_PUT)){
                            parse_str(file_get_contents('php://input'), $_PUT);
                        }
                        $input  =   $_PUT;
                        break;
                    default:
                        $input  =  $_GET;
                }
                break;
            case 'path'    :   
                $input  =   [];
                if(!empty($_SERVER['PATH_INFO'])){
                    $depr   =   Config::get('url_pathinfo_depr');
                    $input  =   explode($depr,trim($_SERVER['PATH_INFO'],$depr));            
                }
                break;
            case 'request' :   
                $input =& $_REQUEST;   
                break;
            case 'session' :   
                $input =& $_SESSION;   
                break;
            case 'cookie'  :   
                $input =& $_COOKIE;    
                break;
            case 'server'  :   
                $input =& $_SERVER;    
                break;
            case 'globals' :   
                $input =& $GLOBALS;    
                break;
            default:
                return null;
        }

        if(''==$name) { // 获取全部变量
            $data       =   $input;
            if(isset(self::$filter)) {
                $filter =   self::$filter;
                if(is_string($filters)){
                    $filters    =   explode(',',$filters);
                }
                foreach($filters as $filter){
                    $data   =   self::filter($filter,$data); // 参数过滤
                }
            }
        }elseif(isset($input[$name])) { // 取值操作
            $data       =   $input[$name];
            if(!empty($args[1])) {
                $filters    =   explode(',',$args[1]);
                if(is_string($filters)){
                    if(0 === strpos($filters,'/')){
                        if(1 !== preg_match($filters,(string)$data)){
                            // 支持正则验证
                            return   $default;
                        }
                    }else{
                        $filters    =   explode(',',$filters);                    
                    }
                }elseif(is_int($filters)){
                    $filters    =   array($filters);
                }
                
                if(is_array($filters)){
                    foreach($filters as $filter){
                        if(function_exists($filter)) {
                            $data   =   is_array($data) ? self::filter($filter,$data) : $filter($data); // 参数过滤
                        }else{
                            $data   =   filter_var($data,is_int($filter) ? $filter : filter_id($filter));
                            if(false === $data) {
                                return   $default;
                            }
                        }
                    }
                }
            }        
            if(!empty($type)){
                switch(strtolower($type)){
                    case 'a':   // 数组
                        $data   =   (array)$data;
                        break;
                    case 'd':   // 数字
                        $data   =   (int)$data;
                        break;
                    case 'f':   // 浮点
                        $data   =   (float)$data;
                        break;
                    case 'b':   // 布尔
                        $data   =   (boolean)$data;
                        break;
                    case 's':   // 字符串
                    default:
                        $data   =   (string)$data;
                }
            }
        }else{ // 变量默认值
            $data       =    $default;
        }
        is_array($data) && array_walk_recursive($data,'self::filterExp');
        return $data;
    }

    // 过滤表单中的表达式
    static public function filterExp(&$value){
        // TODO 其他安全过滤

        // 过滤查询特殊字符
        if(preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i',$value)){
            $value .= ' ';
        }
    }

    static public function filter($filter, $data) {
        $result = array();
        foreach ($data as $key => $val) {
            $result[$key] = is_array($val)
             ? self::filter($filter, $val)
             : call_user_func($filter, $val);
        }
        return $result;
    }    
}
