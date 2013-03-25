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
class Route {
    // 路由规则
    static private $rules   =   [
            'get'       =>  [],
            'post'      =>  [],
            'put'       =>  [],
            'delete'    =>  [],
            'all'       =>  [],
        ];

    // 添加某个路由规则
    static public function add($rule,$route,$type='get'){
        if(is_array($type)) {
            foreach ($type as $val){
                self::$rules[$val][$rule] =    $route;
            }
        }else{
            self::$rules[$type][$rule] =    $route;
        }
    }

    // 导入路由规则 
    static public function import($rules){
        foreach ($rules as $type=>$rule){
            self::$rules[$type]    =   array_merge(self::$rules[$type],$rule);
        }
    }

    // 添加一条任意请求的路由规则
    static public function any($rule,$route){
        self::add($rule,$route,'all');
    }

    // 添加一条get请求的路由规则
    static public function get($rule,$route){
        self::add($rule,$route,'get');
    }

    // 添加一条post请求的路由规则
    static public function post($rule,$route){
        self::add($rule,$route,'post');
    }

    // 添加一条put请求的路由规则
    static public function put($rule,$route){
        self::add($rule,$route,'put');
    }

    // 添加一条delete请求的路由规则
    static public function delete($rule,$route){
        self::add($rule,$route,'delete');
    }

    // 检测URL路由
    static public function check($regx) {
        // 优先检测是否存在PATH_INFO
        if(empty($regx)) return true;
        // 路由处理
        $rules  =   self::$rules[strtolower($_SERVER['REQUEST_METHOD'])];
        if(!empty(self::$rules['all'])) {
            $rules  =   array_merge(self::$rules['all'],$rules);
        }
        if(!empty($rules)) {
            // 分隔符替换 确保路由定义使用统一的分隔符
            $regx = str_replace(Config::get('pathinfo_depr'),'/',$regx);
            foreach ($rules as $rule=>$route){
                if(0===strpos($rule,'/') && preg_match($rule,$regx,$matches)) { // 正则路由
                    if($route instanceof \Closure) {
                        call_user_func($route);
                        exit;
                    }
                    return self::parseRegex($matches,$route,$regx);
                }else{ // 规则路由
                    $len1   =   substr_count($regx,'/');
                    $len2   =   substr_count($rule,'/');
                    if($len1>=$len2) {
                        if('$' == substr($rule,-1,1)) {// 完整匹配
                            if($len1 != $len2) {
                                continue;
                            }else{
                                $rule =  substr($rule,0,-1);
                            }
                        }
                        if(self::match($regx,$rule)){
                            if($route instanceof \Closure) {
                                call_user_func($route);
                                exit;
                            }
                            return self::parseRule($rule,$route,$regx);
                        }
                    }
                }
            }
        }
        return false;
    }

    // 检测URL和规则路由是否匹配
    static private function match($regx,$rule) {
        $m1 = explode('/',$regx);
        $m2 = explode('/',$rule);
        foreach ($m2 as $key=>$val){
            if(':' == substr($val,0,1)) {// 动态变量
                if(strpos($val,'\\')) {
                    $type = substr($val,-1);
                    if('d'==$type && !is_numeric($m1[$key])) {
                        return false;
                    }
                }elseif(strpos($val,'^')){
                    $array   =  explode('|',substr(strstr($val,'^'),1));
                    if(in_array($m1[$key],$array)) {
                        return false;
                    }
                }
            }elseif(0 !== strcasecmp($val,$m1[$key])){
                return false;
            }
        }
        return true;
    }

    // 解析规范的路由地址
    // 地址格式 [模块/控制器/操作?]参数1=值1&参数2=值2...
    static private function parseUrl($url) {
        $var  =  [];
        if(false !== strpos($url,'?')) { // [模块/控制器/操作?]参数1=值1&参数2=值2...
            $info   =  parse_url($url);
            $path   = explode('/',$info['path']);
            parse_str($info['query'],$var);
        }elseif(strpos($url,'/')){ // [模块/控制器/操作]
            $path = explode('/',$url);
        }else{ // 参数1=值1&参数2=值2...
            parse_str($url,$var);
        }
        if(isset($path)) {
            $_GET[Config::get('var_action')]  =   array_pop($path);
            if(!empty($path)) {
                $_GET[Config::get('var_controller')] =   array_pop($path);
            }
            if(!empty($path)) {
                $_GET[Config::get('var_module')]  =   array_pop($path);
            }
        }
        return $var;
    }

    // 解析规则路由
    // '路由规则'=>'[模块/控制器/操作]?额外参数1=值1&额外参数2=值2...'
    // '路由规则'=>array('[模块/控制器/操作]','额外参数1=值1&额外参数2=值2...')
    // '路由规则'=>'外部地址'
    // '路由规则'=>array('外部地址','重定向代码')
    // 路由规则中 :开头 表示动态变量
    // 外部地址中可以用动态变量 采用 :1 :2 的方式
    // 'news/:month/:day/:id'=>array('News/read?cate=1','status=1'),
    // 'new/:id'=>array('/new.php?id=:1',301), 重定向
    static private function parseRule($rule,$route,$regx) {
        // 获取路由地址规则
        $url   =  is_array($route)?$route[0]:$route;
        // 获取URL地址中的参数
        $paths = explode('/',$regx);
        // 解析路由规则
        $matches  =  [];
        $rule =  explode('/',$rule);
        foreach ($rule as $item){
            if(0===strpos($item,':')) { // 动态变量获取
                if($pos = strpos($item,'^') ) {
                    $var  =  substr($item,1,$pos-1);
                }elseif(strpos($item,'\\')){
                    $var  =  substr($item,1,-2);
                }else{
                    $var  =  substr($item,1);
                }
                $matches[$var] = array_shift($paths);
            }else{ // 过滤URL中的静态变量
                array_shift($paths);
            }
        }
        if(0=== strpos($url,'/') || 0===strpos($url,'http')) { // 路由重定向跳转
            if(strpos($url,':')) { // 传递动态参数
                $values  =  array_values($matches);
                $url  =  preg_replace('/:(\d+)/e','$values[\\1-1]',$url);
            }
            header("Location: $url", true,(is_array($route) && isset($route[1]))?$route[1]:301);
            exit;
        }else{
            // 解析路由地址
            $var  =  self::parseUrl($url);
            // 解析路由地址里面的动态参数
            $values  =  array_values($matches);
            foreach ($var as $key=>$val){
                if(0===strpos($val,':')) {
                    $var[$key] =  $values[substr($val,1)-1];
                }
            }
            $var   =   array_merge($matches,$var);
            // 解析剩余的URL参数
            if($paths) {
                preg_replace('@(\w+)\/([^\/]+)@e', '$var[strtolower(\'\\1\')]=strip_tags(\'\\2\');', implode('/',$paths));
            }
            // 解析路由自动传人参数
            if(is_array($route) && isset($route[1])) {
                parse_str($route[1],$params);
                $var   =   array_merge($var,$params);
            }
            $_GET   =  array_merge($var,$_GET);
        }
        return true;
    }

    // 解析正则路由
    // '路由正则'=>'[模块/控制器/操作]?参数1=值1&参数2=值2...'
    // '路由正则'=>array('[模块/控制器/操作]?参数1=值1&参数2=值2...','额外参数1=值1&额外参数2=值2...')
    // '路由正则'=>'外部地址'
    // '路由正则'=>array('外部地址','重定向代码')
    // 参数值和外部地址中可以用动态变量 采用 :1 :2 的方式
    // '/new\/(\d+)\/(\d+)/'=>array('News/read?id=:1&page=:2&cate=1','status=1'),
    // '/new\/(\d+)/'=>array('/new.php?id=:1&page=:2&status=1','301'), 重定向
    static private function parseRegex($matches,$route,$regx) {
        // 获取路由地址规则
        $url   =  is_array($route)?$route[0]:$route;
        $url   =  preg_replace('/:(\d+)/e','$matches[\\1]',$url);
        if(0=== strpos($url,'/') || 0===strpos($url,'http')) { // 路由重定向跳转
            header("Location: $url", true,(is_array($route) && isset($route[1]))?$route[1]:301);
            exit;
        }else{
            // 解析路由地址
            $var  =  self::parseUrl($url);
            // 解析剩余的URL参数
            $regx =  substr_replace($regx,'',0,strlen($matches[0]));
            if($regx) {
                preg_replace('@(\w+)\/([^,\/]+)@e', '$var[strtolower(\'\\1\')]=strip_tags(\'\\2\');', $regx);
            }
            // 解析路由自动传人参数
            if(is_array($route) && isset($route[1])) {
                parse_str($route[1],$params);
                $var   =   array_merge($var,$params);
            }
            $_GET   =  array_merge($var,$_GET);
        }
        return true;
    }
}