<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;
use think\Exception;

/**
 * App 应用管理
 * @author  liu21st <liu21st@gmail.com>
 */
class App {

    /**
     * 执行应用程序
     * @access public
     * @return void
     */
    static public function run($config) {

        // 日志初始化
        Log::init($config['log']);

        // 缓存初始化
        Cache::connect($config['cache']);

        // 加载框架底层语言包
        is_file(THINK_PATH.'Lang/'.strtolower($config['default_lang']).EXT) AND Lang::set(include THINK_PATH.'Lang/'.strtolower($config['default_lang']).EXT);

        if(is_file(APP_PATH.'build.php')) { // 自动化创建脚本
            Create::build(include APP_PATH.'build.php');
        }
        // 监听app_init
        Hook::listen('app_init');

        // 初始化公共模块
        define('COMMON_PATH', APP_PATH . $config['common_module'].'/');

        self::initModule(COMMON_PATH,$config);

        // 启动session
        if(!IS_CLI) {
            Session::init($config['session']);
        }

        // 应用URL调度
        self::dispatch($config);

        // 监听app_run
        Hook::listen('app_run');

        // 执行操作
        if(!preg_match('/^[A-Za-z](\/|\w)*$/',CONTROLLER_NAME)){ // 安全检测
            $instance  =  false;
        }elseif($config['action_bind_class']){
            // 操作绑定到类：模块\controller\控制器\操作
            if(is_dir(MODULE_PATH.CONTROLLER_LAYER.'/'.CONTROLLER_NAME)){
                $namespace  =   MODULE_NAME.'\\'.CONTROLLER_LAYER.'\\'.CONTROLLER_NAME.'\\';
            }else{
                // 空控制器
                $namespace  =   MODULE_NAME.'\\'.CONTROLLER_LAYER.'\\empty\\';                    
            }
            $actionName     =   strtolower(ACTION_NAME);
            if(class_exists($namespace.$actionName)){
                $class   =  $namespace.$actionName;
            }elseif(class_exists($namespace.'_empty')){
                // 空操作
                $class   =  $namespace.'_empty';
            }else{
                throw new Exception('_ERROR_ACTION_:'.ACTION_NAME);
            }
            $instance  =  new $class;
            // 操作绑定到类后 固定执行run入口
            $action     =   'run';
        }else{
            $instance   =   Loader::controller(CONTROLLER_NAME);
            // 获取当前操作名
            $action     =   ACTION_NAME . $config['action_suffix'];
        }
        if(!$instance) {
            throw new Exception('[ ' . MODULE_NAME . '\\'.CONTROLLER_LAYER.'\\' . parse_name(CONTROLLER_NAME, 1) . ' ] not exists');
        }

        try{
            // 操作方法开始监听
            $call = [$instance, $action];
            Hook::listen('action_begin', $call);
            if(!preg_match('/^[A-Za-z](\w)*$/', $action)){
                // 非法操作
                throw new \ReflectionException();
            }
            //执行当前操作
            $method = new \ReflectionMethod($instance, $action);
            if($method->isPublic()) {
                // URL参数绑定检测
                if($config['url_params_bind'] && $method->getNumberOfParameters() > 0){
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'POST':
                            $vars = array_merge($_GET, $_POST);
                            break;
                        case 'PUT':
                            parse_str(file_get_contents('php://input'), $vars);
                            break;
                        default:
                            $vars = $_GET;
                    }
                    $params = $method->getParameters();
                    $paramsBindType     =   $config['url_parmas_bind_type'];
                    foreach ($params as $param){
                        $name = $param->getName();
                        if( 1 == $paramsBindType && !empty($vars) ){
                            $args[] =   array_shift($vars);
                        }if(0 == $paramsBindType && isset($vars[$name])) {
                            $args[] = $vars[$name];
                        }elseif($param->isDefaultValueAvailable()){
                            $args[] = $param->getDefaultValue();
                        }else{
                            E('_PARAM_ERROR_:' . $name);
                        }
                    }
                    array_walk_recursive($args,'Input::filterExp');
                    $method->invokeArgs($instance, $args);
                }else{
                    $method->invoke($instance);
                }
                // 操作方法执行完成监听
                Hook::listen('action_end', $call);
            }else{
                // 操作方法不是Public 抛出异常
                throw new \ReflectionException();
            }
        } catch (\ReflectionException $e) {
            // 操作不存在
            if(method_exists($instance, '_empty')) {
                $method = new \ReflectionMethod($instance, '_empty');
                $method->invokeArgs($instance, [$action, '']);
            }else{
                throw new Exception('[ ' . (new \ReflectionClass($instance))->getName() . ':' . $action . ' ] not exists ', 404);
            }
        }
        // 监听app_end
        Hook::listen('app_end');
        return ;
    }

    /**
     * 初始化模块
     * @access private
     * @return void
     */
    static private function initModule($path,&$config){
        // 加载初始化文件
        if(is_file( $path . 'init' . EXT )) {
            include $path . 'init' . EXT;
        }else{
            // 检测配置文件
            if(is_file($path . 'config' . EXT)) {
                $config =   Config::set(include $path . 'config' . EXT );
            }

            // 检测额外配置
            if($config['extra_config_list']){
                foreach($config['extra_config_list'] as $conf){
                    if(is_file($path . $conf . EXT)) {
                        $config =   Config::set(include $path . $conf . EXT );
                    }
                }
            }

            // 加载应用状态配置文件
            if($config['app_status'] && is_file($path . $config['app_status'] . EXT)) {
                $config =   Config::set(include $path . $config['app_status'] . EXT);
            }
            // 加载别名文件
            if(is_file($path . 'alias' . EXT)) {
                Loader::addMap(include $path . 'alias' . EXT);
            }
            // 加载公共文件
            if(is_file( $path . 'common' . EXT)) {
                include $path . 'common' . EXT;
            }
            // 加载行为扩展文件
            if(is_file($path . 'tags' . EXT)) {
                Hook::import(include $path . 'tags' . EXT);
            }
        }
    }

    /**
     * URL调度
     * @access public
     * @return void
     */
    static public function dispatch($config) {
        if(isset($_GET[$config['var_pathinfo']])) { // 判断URL里面是否有兼容模式参数
            $_SERVER['PATH_INFO'] = $_GET[$config['var_pathinfo']];
            unset($_GET[$config['var_pathinfo']]);
        }elseif(IS_CLI){ // CLI模式下 index.php module/controller/action/params/...
            $_SERVER['PATH_INFO'] = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
        }
        
        // 检测域名部署
        if(!IS_CLI && isset($config['sub_domain_deploy']) && $config['sub_domain_deploy']) {
            Route::checkDomain();
        }

        // 监听path_info
        Hook::listen('path_info');
        // 分析PATHINFO信息
        if(!isset($_SERVER['PATH_INFO']) && $_SERVER['SCRIPT_NAME'] != $_SERVER['PHP_SELF']) {
            $types = explode(',', $config['pathinfo_fetch']);
            foreach ($types as $type){
                if(0 === strpos($type, ':')) {// 支持函数判断
                    $_SERVER['PATH_INFO'] = call_user_func(substr($type,1));
                    break;
                }elseif(!empty($_SERVER[$type])) {
                    $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ?
                        substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
                    break;
                }
            }
        }

        if(empty($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = '';
            define('__INFO__','');
            define('__EXT__','');
        }else{
            $_SERVER['PATH_INFO']  =   trim($_SERVER['PATH_INFO'],'/');
            define('__INFO__',$_SERVER['PATH_INFO']);
            // URL后缀
            define('__EXT__', strtolower(pathinfo($_SERVER['PATH_INFO'],PATHINFO_EXTENSION)));
            $_SERVER['PATH_INFO'] = __INFO__;     
            if(__INFO__ && !defined('BIND_MODULE')){
                if($config['url_deny_suffix'] && preg_match('/\.('.$config['url_deny_suffix'].')$/i', __INFO__)){
                    exit;
                }
                $paths = explode($config['pathinfo_depr'], __INFO__,2);
                // 获取URL中的模块名
                if($config['require_module'] && !isset($_GET[VAR_MODULE])) {
                    $_GET[VAR_MODULE]     = array_shift($paths);
                    $_SERVER['PATH_INFO'] = implode('/', $paths);
                }
            }
            // 去除URL后缀
            $_SERVER['PATH_INFO']   =   preg_replace($config['url_html_suffix']? '/\.('.trim($config['url_html_suffix'],'.').')$/i' : '/\.'.__EXT__.'$/i', '', $_SERVER['PATH_INFO']);
        }

        // URL常量
        define('__SELF__',strip_tags($_SERVER[$config['url_request_uri']]));

        // 获取模块名称
        define('MODULE_NAME', defined('BIND_MODULE')? BIND_MODULE : self::getModule($config));

        // 模块初始化
        if(MODULE_NAME && $config['common_module'] != MODULE_NAME && is_dir( APP_PATH . MODULE_NAME )) {
            Hook::listen('app_begin');
            define('MODULE_PATH', APP_PATH . MODULE_NAME . '/');
            define('VIEW_PATH', MODULE_PATH.VIEW_LAYER.'/');
            
            // 初始化模块
            self::initModule(MODULE_PATH,$config);
        }else{
            throw new Exception('module not exists :' . MODULE_NAME);
        }
        // 路由检测和控制器、操作解析
        Route::check($_SERVER['PATH_INFO'],$config['pathinfo_depr']);

        // 获取控制器名
        define('CONTROLLER_NAME', strip_tags(strtolower(isset($_GET[VAR_CONTROLLER]) ? $_GET[VAR_CONTROLLER] : $config['default_controller'])));

        // 获取操作名
        define('ACTION_NAME', strip_tags(strtolower(isset($_GET[VAR_ACTION]) ? $_GET[VAR_ACTION] : $config['default_action'])));

        unset($_GET[VAR_ACTION], $_GET[VAR_CONTROLLER], $_GET[VAR_MODULE]);
        //保证$_REQUEST正常取值
        $_REQUEST = array_merge($_POST, $_GET , $_COOKIE);
    }

    static private function getModule($config){
        $module     =   strtolower(isset($_GET[VAR_MODULE]) ? $_GET[VAR_MODULE] : $config['default_module']);
        if($maps = $config['url_module_map']) {
            if(isset($maps[$module])) {
                // 记录当前别名
                define('MODULE_ALIAS',$module);
                // 获取实际的项目名
                $module =   $maps[MODULE_ALIAS];
            }elseif(array_search($module,$maps)){
                // 禁止访问原始项目
                $module =   '';
            }
        }
        return strip_tags($module);
    }
}
