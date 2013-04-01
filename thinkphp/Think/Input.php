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
// $Id$
namespace Think;
class Input {
    // 全局过滤规则
    static $filter  =   NULL;

    /**
     * 获取系统变量 支持过滤和默认值
     * @access public
     * @param string $type 输入数据类型
     * @param array $args 参数 array(key,filter,default)
     * @return mixed
     */
    static public function __callStatic($type,$args=[]) {
        switch(strtolower($type)) {
            case 'get':      $input      =& $_GET;break;
            case 'post':     $input      =& $_POST;break;
            case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
            case 'param'   :  
                switch($_SERVER['REQUEST_METHOD']) {
                    case 'POST':
                        $input  =  $_POST;
                        break;
                    case 'PUT':
                        parse_str(file_get_contents('php://input'), $input);
                        break;
                    default:
                        $input  =  $_GET;
                }
                break;
            case 'request': $input      =& $_REQUEST;break;
            case 'server':   $input      =& $_SERVER;break;
            case 'cookie':   $input      =& $_COOKIE;break;
            case 'session':  $input      =& $_SESSION;break;
            case 'globals':   $input      =& $GLOBALS;break;
            default:return NULL;
        }
        // 变量全局过滤
        array_walk_recursive($input,'self::filter_exp');
        if(self::$filter) {
            $_filters    =   explode(',',self::$filter);
            foreach($_filters as $_filter){
                // 全局参数过滤
                array_walk_recursive($input,$_filter);
            }
        }
        if(''== $args[0]) {
            // 返回全部数据
            return $input;
        }elseif(array_key_exists($args[0],$input)) {
            $filters    =   isset($args[1])?$args[1]:'';
            $filters    =   explode(',',$filters);
            $data       =   $input[$args[0]];
            foreach($filters as $filter){
                if(is_callable($filter)) {
                    $data   =   is_array($data)?array_map($filter,$data):$filter($data); // 参数过滤
                }else{
                    $data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
                    if(false === $data) {
                        return	 isset($args[2])?$args[2]:NULL;
                    }
                }
            }
        }else{
            // 不存在指定输入
            $data	 =	 isset($args[2])?$args[2]:NULL;
        }
        return $data;
    }

    // 过滤表单中的表达式
    static private filter_exp(&$value){
        if (in_array(strtolower($value),array('exp','or'))){
            $value .= ' ';
        }
    }
}