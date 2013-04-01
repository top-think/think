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

/**
 * ThinkApp 应用管理
 * @author    liu21st <liu21st@gmail.com>
 */
class App {

    static private $config =   [];

    /**
     * 执行应用程序
     * @access public
     * @return void
     */
    static public function run() {
        // 监听app_init
        Tag::listen('app_init');
        // 加载全局初始化文件
        if(is_file(APP_PATH.'init'.EXT)) {
            include APP_PATH.'init'.EXT;
            $config =   Config::get();
        }else{
            // 检测项目（或模块）配置文件
            if(is_file(APP_PATH.'config'.EXT)) {
                $config   =   Config::set(include APP_PATH.'config'.EXT);
            }
            // 加载别名文件
            if(is_file(APP_PATH.'alias'.EXT)) {
                Loader::loadMap(include APP_PATH.'alias'.EXT);
            }
            // 加载公共文件
            if(is_file(APP_PATH.'common'.EXT)) {
                include APP_PATH.'common'.EXT;
            }
            if(is_file(APP_PATH.'tags'.EXT)) {
                // 行为扩展文件
                Tag::import(include APP_PATH.'tags'.EXT);
            }
        }
        // 应用URL调度
        self::dispatch($config);

        // 执行操作
        $instance = Loader::controller(CONTROLLER_NAME);
        if(!$instance) {
            // 是否定义empty控制器
            $instance = Loader::controller('empty');
            if(!$instance){
                E('controller not exists :'.CONTROLLER_NAME,404);
            }
        }

        // 获取当前操作名
        $action =  ACTION_NAME.$config['action_suffix'];
        try{
            // 操作方法开始监听
            $call  =   array($instance,$action);
            Tag::listen('action_begin',$call);
            if(!preg_match('/^[A-Za-z](\w)*$/',$action)){
                // 非法操作
                throw new \ReflectionException();
            }
            //执行当前操作
            $method =   new \ReflectionMethod($instance, $action);
            if($method->isPublic()) {
                // URL参数绑定检测
                if($config['url_params_bind'] && $method->getNumberOfParameters()>0){
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'POST':
                            $vars    =  array_merge($_GET,$_POST);
                            break;
                        case 'PUT':
                            parse_str(file_get_contents('php://input'), $vars);
                            break;
                        default:
                            $vars  =  $_GET;
                    }
                    $params =  $method->getParameters();
                    foreach ($params as $param){
                        $name = $param->getName();
                        if(isset($vars[$name])) {
                            $args[] =  $vars[$name];
                        }elseif($param->isDefaultValueAvailable()){
                            $args[] = $param->getDefaultValue();
                        }else{
                            E('_PARAM_ERROR_:'.$name);
                        }
                    }
                    $method->invokeArgs($instance,$args);
                }else{
                    $method->invoke($instance);
                }
                // 操作方法执行完成监听
                Tag::listen('action_end',$call);
            }else{
                // 操作方法不是Public 抛出异常
                throw new \ReflectionException();
            }
        } catch (\ReflectionException $e) {
            // 操作不存在
            if(method_exists($instance,'_empty')) {
                $method = new \ReflectionMethod($instance,'_empty');
                $method->invokeArgs($instance,array($action,''));
            }else{
                E('action not exists :'.ACTION_NAME,404);
            }
        }
        // 监听app_end
        Tag::listen('app_end');
        return ;
    }

    /**
     * URL调度
     * @access public
     * @return void
     */
    static public function dispatch($config) {
        $var_m  =   $config['var_module'];
        $var_c  =   $config['var_controller'];
        $var_a  =   $config['var_action'];
        $var_p  =   $config['var_pathinfo'];
        if(!empty($_GET[$var_p])) { // 判断URL里面是否有兼容模式参数
            $_SERVER['PATH_INFO']   = $_GET[$var_p];
            unset($_GET[$var_p]);
        }elseif(IS_CLI){ // CLI模式下 index.php module/controller/action/params/...
            $_SERVER['PATH_INFO']   =   isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';
        }

        // 开启子域名部署 支持二级和三级域名
        if($config['app_domain_deploy']) {
            $rules = $config['app_domain_rules'];
            if(isset($rules[$_SERVER['HTTP_HOST']])) { // 完整域名或者IP配置
                $rule =  $rules[$_SERVER['HTTP_HOST']];
            }else{// 子域名配置
                $domain = array_slice(explode('.',$_SERVER['HTTP_HOST']),0,-2);
                if(!empty($domain)) {
                    $subDomain    = implode('.',$domain);
                    $domain2 = array_pop($domain); // 二级域名
                    if($domain) { // 存在三级域名
                        $domain3   =  array_pop($domain);
                    }
                    if($subDomain && isset($rules[$subDomain])) { // 子域名配置
                        $rule =  $rules[$subDomain];
                    }elseif(isset($rules['*.'.$domain2]) && !empty($domain3)){ // 泛三级域名
                        $rule =  $rules['*.'.$domain2];
                        $panDomain = $domain3;
                    }elseif(isset($rules['*']) && !empty($domain2)){ // 泛二级域名
                        if('www' != $domain2 && !in_array($domain2,$config['app_doamin_deny'])) {
                            $rule =  $rules['*'];
                            $panDomain = $domain2;
                        }
                    }
                }
            }
            if(!empty($rule)) {
                // 子域名部署规则 '子域名'=>array('模块名'[,'var1=a&var2=b&var3=*']);
                $_GET[$var_m]  =   $rule[0];
                if(isset($rule[1])) { // 传入参数
                    parse_str($rule[1],$parms);
                    if(isset($panDomain)) {
                        $pos =  array_search('*',$parms);
                        if(false !== $pos) {
                            // 泛域名作为参数
                            $parms[$pos] =  $panDomain;
                        }
                    }
                    $_GET   =  array_merge($_GET,$parms);
                }
            }
        }
        // 监听path_info
        Tag::listen('path_info');
        // 分析PATHINFO信息
        if(empty($_SERVER['PATH_INFO']) && $_SERVER['SCRIPT_NAME'] != $_SERVER['PHP_SELF']) {
            $types   =  explode(',',$config['pathinfo_fetch']);
            foreach ($types as $type){
                if(0===strpos($type,':')) {// 支持函数判断
                    $_SERVER['PATH_INFO'] =   call_user_func(substr($type,1));
                    break;
                }elseif(!empty($_SERVER[$type])) {
                    $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type],$_SERVER['SCRIPT_NAME']))?
                        substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME']))   :  $_SERVER[$type];
                    break;
                }
            }
        }
        // 定位模块
        $part =  pathinfo($_SERVER['PATH_INFO']);
        define('__EXT__', isset($part['extension'])?strtolower($part['extension']):'');
        $_SERVER['PATH_INFO'] = trim(preg_replace('/\.('.trim($config['url_html_suffix'],'.').')$/i', '',$_SERVER['PATH_INFO']),'/');
        if($_SERVER['PATH_INFO']) {
            $paths = explode($config['pathinfo_depr'],$_SERVER['PATH_INFO']);
            // 获取URL中的模块名
            if($config['require_module'] && !isset($_GET[$var_m])) {
                $_GET[$var_m]           =   array_shift($paths);
                $_SERVER['PATH_INFO']   =   implode('/',$paths);
            }
        }

        // 获取模块名称
        define('MODULE_NAME',strtolower(isset($_GET[$var_m])?$_GET[$var_m]:$config['default_module']));

        // 模块初始化
        if(MODULE_NAME && is_dir(APP_PATH.MODULE_NAME)) {
            define('MODULE_PATH',APP_PATH.MODULE_NAME.'/');
            Tag::listen('app_begin');
            // 加载模块初始化文件
            if(is_file(MODULE_PATH.'init'.EXT)) {
                include MODULE_PATH.'init'.EXT;
                $config =   Config::get();
            }else{
                // 检测项目（或模块）配置文件
                if(is_file(MODULE_PATH.'config'.EXT)) {
                    $config   =   Config::set(include MODULE_PATH.'config'.EXT);
                }
                if($config['app_status'] && is_file(MODULE_PATH.$config['app_status'].EXT)) {
                    // 加载对应的项目配置文件
                    $config   =   Config::set(include MODULE_PATH.$config['app_status'].EXT);
                }
                // 加载别名文件
                if(is_file(MODULE_PATH.'alias'.EXT)) {
                    Loader::loadMap(include MODULE_PATH.'alias'.EXT);
                }
                // 加载公共文件
                if(is_file(MODULE_PATH.'common'.EXT)) {
                    include MODULE_PATH.'common'.EXT;
                }
                if(is_file(MODULE_PATH.'tags'.EXT)) {
                    // 行为扩展文件
                    Tag::import(include MODULE_PATH.'tags'.EXT);
                }
            }
            $var_c  =   $config['var_controller'];
            $var_a  =   $config['var_action'];
        }else{
            E('module not exists :'.MODULE_NAME,404);
        }
        // 路由检测和控制器、操作解析
        Route::check($_SERVER['PATH_INFO']);

        // 获取控制器名
        define('CONTROLLER_NAME', strtolower(isset($_GET[$var_c])?$_GET[$var_c]:$config['default_controller']));

        // 获取操作名
        define('ACTION_NAME',   strtolower(isset($_GET[$var_a])?$_GET[$var_a]:$config['default_action']));

        unset($_GET[$var_a],$_GET[$var_c],$_GET[$var_m]);
        //保证$_REQUEST正常取值
        $_REQUEST = array_merge($_POST,$_GET);
    }

}