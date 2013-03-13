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
        // 加载全局公共文件和配置

        // 检测项目（或模块）配置文件
        if(is_file(APP_PATH.'config'.EXT)) {
            $config   =   Config::set(include APP_PATH.'config'.EXT);
        }
        // 加载别名文件
        if(is_file(APP_PATH.'alias'.EXT)) {
            Loader::import(include APP_PATH.'alias'.EXT);
        }
        // 加载公共文件
        if(is_file(APP_PATH.'common'.EXT)) {
            include APP_PATH.'common'.EXT;
        }
        
        if(is_file(APP_PATH.'tags'.EXT)) {
            // 行为扩展文件
            Tag::import(include APP_PATH.'tags'.EXT);
        }
        // 应用URL调度
        self::dispatch($config);

        // 执行操作
        $instance = Loader::controll(CONTROLL_NAME);
        if(!$instance) {
            // 是否定义empty控制器
            $instance = Loader::controll('empty');
            if(!$instance){
                _404('controll not exists :'.CONTROLL_NAME);
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
                            _404('_PARAM_ERROR_:'.$name);
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
                throw new ReflectionException();
            }
        } catch (ReflectionException $e) {
            // 操作不存在
            if(method_exists($instance,'_empty')) {
                $method = new ReflectionMethod($instance,'_empty');
                $method->invokeArgs($instance,array($action,''));
            }else{
                _404('action not exists :'.ACTION_NAME);
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
        $var_module     =   $config['var_module'];
        $var_controll   =   $config['var_controll'];
        $var_action     =   $config['var_action'];
        $var_pathinfo   =   $config['var_pathinfo'];
        if(!empty($_GET[$var_pathinfo])) { // 判断URL里面是否有兼容模式参数
            $_SERVER['PATH_INFO']   = $_GET[$var_pathinfo];
            unset($_GET[$var_pathinfo]);
        }elseif(IS_CLI){ // CLI模式下 index.php module/controll/action/params/...
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
                $_GET[$var_module]  =   $rule[0];
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
        if(!empty($_SERVER['PATH_INFO'])) {
            // 监听path_info
            Tag::listen('path_info');
            $part =  pathinfo($_SERVER['PATH_INFO']);
            define('__EXT__', isset($part['extension'])?strtolower($part['extension']):'');
            $_SERVER['PATH_INFO'] = preg_replace('/\.('.trim($config['url_html_suffix'],'.').')$/i', '',$_SERVER['PATH_INFO']);
            $paths = explode($config['pathinfo_depr'],trim($_SERVER['PATH_INFO'],'/'));
            if($config['require_module'] && !isset($_GET[$var_module])) {
                $_GET[$var_module]  =   array_shift($paths);
            }
        }elseif(isset($_GET[$var_module]) && !$config['require_module']) {
            unset($_GET[$var_module]);
        }
        // 获取模块名称
        define('MODULE_NAME',strtolower(isset($_GET[$var_module])?$_GET[$var_module]:$config['default_module']));

        // 加载模块的公共文件和配置
        if(is_dir(APP_PATH.MODULE_NAME)) {
            define('MODULE_PATH',APP_PATH.MODULE_NAME.'/');
            Tag::listen('app_begin');
            
            // 检测项目（或模块）配置文件
            if(is_file(MODULE_PATH.'config'.EXT)) {
                $config   =   Config::set(include MODULE_PATH.'config'.EXT);
            }
            if(Config::has('app_status')) {
                // 读取应用状态配置文件
                $status  =  Config::get('app_status');
                // 加载对应的项目配置文件
                if(is_file(MODULE_PATH.$status.EXT))
                    $config   =   Config::set(include MODULE_PATH.$status.EXT);
            }
            // 加载别名文件
            if(is_file(MODULE_PATH.'alias'.EXT)) {
                Loader::import(include MODULE_PATH.'alias'.EXT);
            }
            // 加载公共文件
            if(is_file(MODULE_PATH.'common'.EXT)) {
                include MODULE_PATH.'common'.EXT;
            }
            if(is_file(MODULE_PATH.'tags'.EXT)) {
                // 行为扩展文件
                Tag::import(include MODULE_PATH.'tags'.EXT);
            }
            $var_controll   =   $config['var_controll'];
            $var_action     =   $config['var_action'];
        }else{
            _404('module not exists :'.MODULE_NAME);
        }

        if(!empty($_SERVER['PATH_INFO'])) {
            Tag::listen('path_info');
            $url   =   trim(substr_replace($_SERVER['PATH_INFO'],'',0,strlen($_GET[$var_module])+1),'/');
            // 模块路由检测
            if($config['url_route'] && !Route::check($url,$config['url_route_rules'])){
                $paths = explode($config['pathinfo_depr'],$url);
                if($config['require_controll'] && !isset($_GET[$var_controll])) {
                    $_GET[$var_controll]    =   array_shift($paths);
                }
                if(!isset($_GET[$var_action])) {
                    $_GET[$var_action]  =   array_shift($paths);
                }
                // 解析剩余的URL参数
                $var  =  [];
                preg_replace('@(\w+)\/([^\/]+)@e', '$var[\'\\1\']=strip_tags(\'\\2\');', implode('/',$paths));
                $_GET   =  array_merge($var,$_GET);
            }
        }elseif(isset($_GET[$var_controll]) && !$config['require_controll']) {
            unset($_GET[$var_controll]);
        }

        // 获取控制器名
        define('CONTROLL_NAME', strtolower(isset($_GET[$var_controll])?$_GET[$var_controll]:$config['default_controll']));

        // 获取操作名
        define('ACTION_NAME',   strtolower(isset($_GET[$var_action])?$_GET[$var_action]:$config['default_action']));

        unset($_GET[$var_action],$_GET[$var_controll],$_GET[$var_module]);
        //保证$_REQUEST正常取值
        $_REQUEST = array_merge($_POST,$_GET);
    }

}