<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

/**
 * App 应用管理
 * @author  liu21st <liu21st@gmail.com>
 */
class App
{

    // 应用调度机制
    private static $dispatch = [];

    /**
     * 执行应用程序
     * @access public
     * @return void
     */
    public static function run()
    {
        // 初始化应用（公共模块）
        self::initModule(COMMON_MODULE, Config::get());

        // 获取配置参数
        $config = Config::get();

        // 注册根命名空间
        if (!empty($config['root_namespace'])) {
            Loader::addNamespace($config['root_namespace']);
        }

        // 加载额外文件
        if (!empty($config['extra_file_list'])) {
            foreach ($config['extra_file_list'] as $file) {
                $file = strpos($file, '.') ? $file : APP_PATH . $file . EXT;
                if (is_file($file)) {
                    include_once $file;
                }
            }
        }

        // 设置系统时区
        date_default_timezone_set($config['default_timezone']);

        // 监听app_init
        APP_HOOK && Hook::listen('app_init');

        // 开启多语言机制
        if ($config['lang_switch_on']) {
            // 获取当前语言
            defined('LANG_SET') or define('LANG_SET', Lang::range());
            // 加载系统语言包
            Lang::load(THINK_PATH . 'lang' . DS . LANG_SET . EXT);
            if (!APP_MULTI_MODULE) {
                Lang::load(APP_PATH . 'lang' . DS . LANG_SET . EXT);
            }
        }

        if (empty(self::$dispatch['type'])) {
            // 未指定调度类型 则进行URL路由检测
            self::route($config);
        }
        // 记录路由信息
        APP_DEBUG && Log::record('[ ROUTE ] ' . var_export(self::$dispatch, true), 'info');
        // 监听app_begin
        APP_HOOK && Hook::listen('app_begin');

        // 根据类型调度
        switch (self::$dispatch['type']) {
            case 'redirect':
                // 执行重定向跳转
                header('Location: ' . self::$dispatch['url'], true, self::$dispatch['status']);
                break;
            case 'module':
                // 模块/控制器/操作
                $data = self::module(self::$dispatch['module'], $config);
                break;
            case 'controller':
                // 执行控制器操作
                $data = Loader::action(self::$dispatch['controller'], self::$dispatch['params']);
                break;
            case 'method':
                // 执行回调方法
                $data = self::invokeMethod(self::$dispatch['method'], self::$dispatch['params']);
                break;
            case 'function':
                // 规则闭包
                $data = self::invokeFunction(self::$dispatch['function'], self::$dispatch['params']);
                break;
            default:
                throw new Exception('dispatch type not support', 10008);
        }
        // 监听app_end
        APP_HOOK && Hook::listen('app_end', $data);
        // 输出数据到客户端
        if (Config::get('response_auto_output')) {
            // 自动响应输出
            return Response::send($data, Response::type(), Config::get('response_return'));
        }
    }

    // 执行函数或者闭包方法 支持参数调用
    public static function invokeFunction($function, $vars = [])
    {
        $reflect = new \ReflectionFunction($function);
        $args    = self::bindParams($reflect, $vars);
        // 记录执行信息
        APP_DEBUG && Log::record('[ RUN ] ' . $reflect->getFileName() . '[ ' . var_export($vars, true) . ' ]', 'info');
        return $reflect->invokeArgs($args);
    }

    // 调用反射执行类的方法 支持参数绑定
    public static function invokeMethod($method, $vars = [])
    {
        if (empty($vars)) {
            // 自动获取请求变量
            switch (REQUEST_METHOD) {
                case 'POST':
                    $vars = array_merge($_GET, $_POST);
                    break;
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $vars);
                    break;
                default:
                    $vars = $_GET;
            }
        }
        if (is_array($method)) {
            $class   = is_object($method[0]) ? $method[0] : new $method[0];
            $reflect = new \ReflectionMethod($class, $method[1]);
        } else {
            // 静态方法
            $reflect = new \ReflectionMethod($method);
        }
        $args = self::bindParams($reflect, $vars);
        // 记录执行信息
        APP_DEBUG && Log::record('[ RUN ] ' . $reflect->getFileName() . '[ ' . var_export($args, true) . ' ]', 'info');
        return $reflect->invokeArgs(isset($class) ? $class : null, $args);
    }

    // 绑定参数
    private static function bindParams($reflect, $vars)
    {
        $args = [];
        // 判断数组类型 数字数组时按顺序绑定参数
        $type = key($vars) === 0 ? 1 : 0;
        if ($reflect->getNumberOfParameters() > 0) {
            $params = $reflect->getParameters();
            foreach ($params as $param) {
                $name = $param->getName();
                if (1 == $type && !empty($vars)) {
                    $args[] = array_shift($vars);
                } elseif (0 == $type && isset($vars[$name])) {
                    $args[] = $vars[$name];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    throw new Exception('method param miss:' . $name, 10004);
                }
            }
            // 全局过滤
            array_walk_recursive($args, 'think\\Input::filterExp');
        }
        return $args;
    }

    // 执行 模块/控制器/操作
    private static function module($result, $config)
    {
        if (APP_MULTI_MODULE) {
            // 多模块部署
            $module = strtolower($result[0] ?: $config['default_module']);
            if ($maps = $config['url_module_map']) {
                if (isset($maps[$module])) {
                    // 记录当前别名
                    define('MODULE_ALIAS', $module);
                    // 获取实际的项目名
                    $module = $maps[MODULE_ALIAS];
                } elseif (array_search($module, $maps)) {
                    // 禁止访问原始项目
                    $module = '';
                }
            }
            // 获取模块名称
            define('MODULE_NAME', strip_tags($module));

            // 模块初始化
            if (MODULE_NAME && !in_array(MODULE_NAME, $config['deny_module_list']) && is_dir(APP_PATH . MODULE_NAME)) {
                define('MODULE_PATH', APP_PATH . MODULE_NAME . DS);
                define('VIEW_PATH', MODULE_PATH . VIEW_LAYER . DS);
                // 初始化模块
                self::initModule(MODULE_NAME, $config);
            } else {
                throw new Exception('module [ ' . MODULE_NAME . ' ] not exists ', 10005);
            }
        } else {
            // 单一模块部署
            define('MODULE_NAME', '');
            define('MODULE_PATH', APP_PATH);
            define('VIEW_PATH', MODULE_PATH . VIEW_LAYER . DS);
        }

        // 获取控制器名
        $controllerName = strip_tags($result[1] ?: Config::get('default_controller'));
        defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', Config::get('url_controller_convert') ? strtolower($controllerName) : $controllerName);

        // 获取操作名
        $actionName = strip_tags($result[2] ?: Config::get('default_action'));
        defined('ACTION_NAME') or define('ACTION_NAME', Config::get('url_action_convert') ? strtolower($actionName) : $actionName);

        // 执行操作
        if (!preg_match('/^[A-Za-z](\/|\.|\w)*$/', CONTROLLER_NAME)) {
            // 安全检测
            throw new Exception('illegal controller name:' . CONTROLLER_NAME, 10000);
        }
        $instance = Loader::controller(CONTROLLER_NAME, '', Config::get('empty_controller'));
        // 获取当前操作名
        $action = ACTION_NAME . Config::get('action_suffix');

        try {
            // 操作方法开始监听
            $call = [$instance, $action];
            APP_HOOK && Hook::listen('action_begin', $call);
            if (!preg_match('/^[A-Za-z](\w)*$/', $action)) {
                // 非法操作
                throw new Exception('illegal action name :' . ACTION_NAME, 10001);
            }
            // 执行操作方法
            $data = self::invokeMethod($call);
        } catch (\ReflectionException $e) {
            // 操作不存在
            if (method_exists($instance, '_empty')) {
                $method = new \ReflectionMethod($instance, '_empty');
                $data   = $method->invokeArgs($instance, [$action, '']);
                APP_DEBUG && Log::record('[ RUN ] ' . $method->getFileName(), 'info');
            } else {
                throw new Exception('method [ ' . (new \ReflectionClass($instance))->getName() . '->' . $action . ' ] not exists ', 10002);
            }
        }
        return $data;
    }

    // 初始化模块
    private static function initModule($module, $config)
    {
        // 定位模块目录
        $module = (COMMON_MODULE == $module || !APP_MULTI_MODULE) ? '' : $module . DS;

        // 加载初始化文件
        if (is_file(APP_PATH . $module . 'init' . EXT)) {
            include APP_PATH . $module . 'init' . EXT;
        } else {
            $path = APP_PATH . $module;
            // 加载模块配置
            $config = Config::load(APP_PATH . $module . 'config' . EXT);

            // 加载应用状态配置
            if ($config['app_status']) {
                $config = Config::load(APP_PATH . $module . $config['app_status'] . EXT);
            }

            // 读取扩展配置文件
            if ($config['extra_config_list']) {
                foreach ($config['extra_config_list'] as $name => $file) {
                    $file = strpos($file, '.') ? $file : $path . $file . EXT;
                    Config::load($file, is_string($name) ? $name : pathinfo($file, PATHINFO_FILENAME));
                }
            }

            // 加载别名文件
            if (is_file($path . 'alias' . EXT)) {
                Loader::addMap(include $path . 'alias' . EXT);
            }

            // 加载行为扩展文件
            if (APP_HOOK && is_file($path . 'tags' . EXT)) {
                Hook::import(include $path . 'tags' . EXT);
            }

            // 加载公共文件
            if (is_file($path . 'common' . EXT)) {
                include $path . 'common' . EXT;
            }

            // 加载当前模块语言包
            if ($config['lang_switch_on'] && $module) {
                Lang::load($path . 'lang' . DS . LANG_SET . EXT);
            }
        }
    }

    // 分析 PATH_INFO
    private static function parsePathinfo(array $config)
    {
        if (isset($_GET[$config['var_pathinfo']])) {
            // 判断URL里面是否有兼容模式参数
            $_SERVER['PATH_INFO'] = $_GET[$config['var_pathinfo']];
            unset($_GET[$config['var_pathinfo']]);
        } elseif (IS_CLI) {
            // CLI模式下 index.php module/controller/action/params/...
            $_SERVER['PATH_INFO'] = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
        }

        // 监听path_info
        APP_HOOK && Hook::listen('path_info');
        // 分析PATHINFO信息
        if (!isset($_SERVER['PATH_INFO'])) {
            foreach ($config['pathinfo_fetch'] as $type) {
                if (!empty($_SERVER[$type])) {
                    $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ?
                    substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
                    break;
                }
            }
        }
    }

    /**
     * URL路由检测（根据PATH_INFO)
     * @access public
     * @param  array $config
     * @throws Exception
     */
    public static function route(array $config)
    {
        // 解析PATH_INFO
        self::parsePathinfo($config);

        if (empty($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = '';
            define('__INFO__', '');
            define('__EXT__', '');
        } else {
            $_SERVER['PATH_INFO'] = trim($_SERVER['PATH_INFO'], '/');
            define('__INFO__', $_SERVER['PATH_INFO']);
            // URL后缀
            define('__EXT__', strtolower(pathinfo($_SERVER['PATH_INFO'], PATHINFO_EXTENSION)));
            // 检测URL禁用后缀
            if ($config['url_deny_suffix'] && preg_match('/\.(' . $config['url_deny_suffix'] . ')$/i', __INFO__)) {
                throw new Exception('url suffix deny');
            }
            // 去除正常的URL后缀
            $_SERVER['PATH_INFO'] = preg_replace($config['url_html_suffix'] ? '/\.(' . trim($config['url_html_suffix'], '.') . ')$/i' : '/\.' . __EXT__ . '$/i', '', __INFO__);
        }

        $depr   = $config['pathinfo_depr'];
        $result = false;
        // 路由检测
        if (APP_ROUTE_ON && !empty($config['url_route_on'])) {
            // 开启路由
            if (!empty($config['route'])) {
                // 注册路由定义文件
                Route::register($config['route']);
            }
            // 路由检测（根据路由定义返回不同的URL调度）
            $result = Route::check($_SERVER['PATH_INFO'], $depr, !IS_CLI ? $config['url_domain_deploy'] : false);
            if (APP_ROUTE_MUST && false === $result && $config['url_route_must']) {
                // 路由无效
                throw new Exception('route not define ');
            }
        }
        if (false === $result) {
            // 路由无效默认分析为模块/控制器/操作/参数...方式URL
            $result = Route::parseUrl($_SERVER['PATH_INFO'], $depr);
        }
        // 注册调度机制
        self::dispatch($result);
    }

    // 指定应用调度
    public static function dispatch($dispatch)
    {
        self::$dispatch = $dispatch;
    }
}
