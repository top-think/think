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
            'GET'       =>  [],
            'POST'      =>  [],
            'PUT'       =>  [],
            'DELETE'    =>  [],
            '*'         =>  [],
        ];

    // 映射规则
    static private $map =   [];

    // 添加URL映射规则
    static public function map($map,$route=''){
        if(is_array($map)) {
            self::$map  =   array_merge(self::$map,$map);
        }else{
            self::$map[$map]    =   $route;
        }
    }

    // 注册某个路由规则
    static public function register($rule,$route='',$type='GET'){
        if(is_array($type)) {
            foreach ($type as $val){
                self::$rules[$val][$rule] =    $route;
            }
        }elseif(is_array($rule)) {
            self::$rules[$type] =    array_merge(self::$rules[$type],$rule);
        }else{
            self::$rules[$type][$rule] =    $route;
        }
    }

    // 导入路由规则 
    static public function import($rules,$type='GET'){
        self::$rules[$type]    =   array_merge(self::$rules[$type],$rules);
    }

    // 添加一条任意请求的路由规则
    static public function any($rule,$route=''){
        self::register($rule,$route,'*');
    }

    // 添加一条get请求的路由规则
    static public function get($rule,$route=''){
        self::register($rule,$route,'GET');
    }

    // 添加一条post请求的路由规则
    static public function post($rule,$route=''){
        self::register($rule,$route,'POST');
    }

    // 添加一条put请求的路由规则
    static public function put($rule,$route=''){
        self::register($rule,$route,'PUT');
    }

    // 添加一条delete请求的路由规则
    static public function delete($rule,$route=''){
        self::register($rule,$route,'DELETE');
    }

    // 检测URL路由
    static public function check($regx) {
        // 优先检测是否存在PATH_INFO
        if(empty($regx)) $regx  =   '/' ;
        // 分隔符替换 确保路由定义使用统一的分隔符
        $regx = str_replace(Config::get('pathinfo_depr'),'/',$regx);
        if(isset(self::$map[$regx])) { // URL映射
            return self::parseUrl(self::$map[$regx]);
        }
        // 路由规则检测
        $rules  =   self::$rules[$_SERVER['REQUEST_METHOD']];
        if(!empty(self::$rules['*'])) {
            $rules  =   array_merge(self::$rules['*'],$rules);
        }
        if(!empty($rules)) {
            foreach ($rules as $rule=>$route){
                if(0===strpos($rule,'/') && preg_match($rule,$regx,$matches)) { // 正则路由
                    if($route instanceof \Closure) {
                        // 执行闭包并中止
                        self::invokeRegx($route,$matches);
                        exit;
                    }else{
                        return self::parseRegex($matches,$route,$regx);
                    }
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
                        if($var = self::match($regx,$rule)){
                            if($route instanceof \Closure) {
                                // 执行闭包并中止
                                self::invokeRule($route,$var);
                                exit;
                            }else{
                                return self::parseRule($rule,$route,$regx);
                            }
                        }
                    }
                }
            }
        }
        return self::parseUrl($regx);
    }

    // 执行正则匹配下的闭包方法 支持参数调用
    static private function invokeRegx($closure,$var=[]) {
        $reflect    =   new \ReflectionFunction($closure);
        $params     =   $reflect->getParameters();
        $args       =   [];
        array_shift($var);
        foreach ($params as $param){
            $name = $param->getName();
            if(!empty($var)) {
                $args[] =  array_shift($var);
            }elseif($param->isDefaultValueAvailable()){
                $args[] = $param->getDefaultValue();
            }
        }
        $reflect->invokeArgs($args);
    }

    // 执行规则匹配下的闭包方法 支持参数调用
    static private function invokeRule($closure,$var=[]) {
        $reflect    =   new \ReflectionFunction($closure);
        $params     =   $reflect->getParameters();
        $args       =   [];
        foreach ($params as $param){
            $name = $param->getName();
            if(isset($var[$name])) {
                $args[] =  $var[$name];
            }elseif($param->isDefaultValueAvailable()){
                $args[] = $param->getDefaultValue();
            }
        }
        $reflect->invokeArgs($args);
    }

    // 解析模块的URL地址
    static private function parseUrl($url) {
        if('/'==$url) {
            return ;
        }
        $paths  = explode('/',$url);
        $var_c  =   Config::get('var_controller');
        $var_a  =   Config::get('var_action');
        if(Config::get('require_controller') && !isset($_GET[$var_c])) {
            $_GET[$var_c]  =   array_shift($paths);
        }
        if(!isset($_GET[$var_a])) {
            $_GET[$var_a]  =   array_shift($paths);
        }
        // 解析剩余的URL参数
        $var  =  [];
        preg_replace('@(\w+)\/([^\/]+)@e', '$var[\'\\1\']=strip_tags(\'\\2\');', implode('/',$paths));
        $_GET   =  array_merge($var,$_GET);
    }

    // 解析规范的路由地址
    // 地址格式 [控制器/操作?]参数1=值1&参数2=值2...
    static private function parseRoute($url) {
        $var  =  [];
        if(false !== strpos($url,'?')) { // [控制器/操作?]参数1=值1&参数2=值2...
            $info   =  parse_url($url);
            $path   = explode('/',$info['path']);
            parse_str($info['query'],$var);
        }elseif(strpos($url,'/')){ // [控制器/操作]
            $path = explode('/',$url);
        }else{ // 参数1=值1&参数2=值2...
            parse_str($url,$var);
        }
        if(isset($path)) {
            $_GET[Config::get('var_action')]  =   array_pop($path);
            if(!empty($path)) {
                $_GET[Config::get('var_controller')] =   array_pop($path);
            }
        }
        return $var;
    }

    // 检测URL和规则路由是否匹配
    static private function match($regx,$rule) {
        $m1     =   explode('/',$regx);
        $m2     =   explode('/',$rule);
        $var    =   [];
        foreach ($m2 as $key=>$val){
            if(0===strpos($val,':')) {// 动态变量
                if(strpos($val,'\\')) {
                    $type = substr($val,-1);
                    if('d'==$type && !is_numeric($m1[$key])) {
                        return false;
                    }
                    $name  =  substr($val,1,-2);
                }elseif($pos = strpos($val,'^')){
                    $array   =  explode('|',substr(strstr($val,'^'),1));
                    if(in_array($m1[$key],$array)) {
                        return false;
                    }
                    $name  =  substr($val,1,$pos-1);
                }else{
                    $name  =  substr($val,1);
                }
                $var[$name]  =   $m1[$key];
            }elseif(0 !== strcasecmp($val,$m1[$key])){
                return false;
            }
        }
        // 成功匹配后返回URL中的动态变量数组
        return $var;
    }

    // 解析规则路由
    // '路由规则'=>'[控制器/操作]?额外参数1=值1&额外参数2=值2...'
    // '路由规则'=>array('[控制器/操作]','额外参数1=值1&额外参数2=值2...')
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
            $var  =  self::parseRoute($url);
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
    }

    // 解析正则路由
    // '路由正则'=>'[控制器/操作]?参数1=值1&参数2=值2...'
    // '路由正则'=>array('[控制器/操作]?参数1=值1&参数2=值2...','额外参数1=值1&额外参数2=值2...')
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
            $var  =  self::parseRoute($url);
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
    }
}