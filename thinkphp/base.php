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

// 开始运行时间和内存使用
define('START_TIME', microtime(true));
define('START_MEM', memory_get_usage());
//  版本信息
define('THINK_VERSION', '5.0.0beta');
// 系统常量
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('THINK_PATH') or define('THINK_PATH', dirname(__FILE__) . DS);
defined('LIB_PATH') or define('LIB_PATH', THINK_PATH . 'library' . DS);
defined('MODE_PATH') or define('MODE_PATH', THINK_PATH . 'mode' . DS); // 系统应用模式目录
defined('CORE_PATH') or define('CORE_PATH', LIB_PATH . 'think' . DS);
defined('ORG_PATH') or define('ORG_PATH', LIB_PATH . 'org' . DS);
defined('TRAIT_PATH') or define('TRAIT_PATH', LIB_PATH . 'traits' . DS);
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
defined('COMMON_MODULE') or define('COMMON_MODULE', 'common');
defined('RUNTIME_PATH') or define('RUNTIME_PATH', realpath(APP_PATH) . DS . 'runtime' . DS);
defined('DATA_PATH') or define('DATA_PATH', RUNTIME_PATH . 'data' . DS);
defined('LOG_PATH') or define('LOG_PATH', RUNTIME_PATH . 'log' . DS);
defined('CACHE_PATH') or define('CACHE_PATH', RUNTIME_PATH . 'cache' . DS);
defined('TEMP_PATH') or define('TEMP_PATH', RUNTIME_PATH . 'temp' . DS);
defined('VENDOR_PATH') or define('VENDOR_PATH', THINK_PATH . 'vendor' . DS);
defined('EXT') or define('EXT', '.php');
defined('MODEL_LAYER') or define('MODEL_LAYER', 'model');
defined('VIEW_LAYER') or define('VIEW_LAYER', 'view');
defined('CONTROLLER_LAYER') or define('CONTROLLER_LAYER', 'controller');
defined('APP_DEBUG') or define('APP_DEBUG', false); // 是否调试模式
defined('APP_HOOK') or define('APP_HOOK', false); // 是否开启HOOK
defined('ENV_PREFIX') or define('ENV_PREFIX', 'T_'); // 环境变量的配置前缀
defined('IS_API') or define('IS_API', false); // 是否API接口
defined('SLOG_ON') or define('SLOG_ON', false); // 是否开启socketLog
defined('IN_UNIT_TEST') or define('IN_UNIT_TEST', false); // 是否为单元测试

// 应用模式 默认为普通模式
defined('APP_MODE') or define('APP_MODE', function_exists('saeAutoLoader') ? 'sae' : 'common');

// 环境常量
define('IS_CGI', strpos(PHP_SAPI, 'cgi') === 0 ? 1 : 0);
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);
define('IS_AJAX', (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false);
define('NOW_TIME', $_SERVER['REQUEST_TIME_FLOAT']);
define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);

// 获取多语言变量
function L($name, $vars = [], $lang = '')
{
    return think\Lang::get($name, $vars, $lang);
}

// 获取配置参数
function C($name = '', $value = null, $range = '')
{
    if (is_null($value) && is_string($name)) {
        return think\Config::get($name, $range);
    } else {
        return think\Config::set($name, $value, $range);
    }
}

// 获取输入数据 支持默认值和过滤
function I($key, $default = null, $filter = '')
{
    if (strpos($key, '.')) {
        // 指定参数来源
        list($method, $key) = explode('.', $key, 2);
    } else {
        // 默认为自动判断
        $method = 'param';
    }
    return think\Input::$method($key, $default, $filter);
}

/**
 * 记录时间（微秒）和内存使用情况
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer $dec 小数位
 * @return mixed
 */
function G($start, $end = '', $dec = 6)
{
    if ('' == $end) {
        think\Debug::remark($start);
    } else {
        return 'm' == $dec ? think\Debug::getUseMem($start, $end) : think\Debug::getUseTime($start, $end, $dec);
    }
}

/**
 * 实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return \Think\Model
 */
function M($name = '', $tablePrefix = '', $connection = '')
{
    return think\Loader::table($name, ['prefix' => $tablePrefix, 'connection' => $connection]);
}

/**
 * 实例化Model
 * @param string $name Model名称
 * @param string $layer 业务层名称
 * @return object
 */
function D($name = '', $layer = MODEL_LAYER)
{
    return think\Loader::model($name, $layer);
}

/**
 * 实例化数据库类
 * @param array $config 数据库配置参数
 * @param boolean $lite 是否lite连接
 * @return object
 */
function db($config = [], $lite = false)
{
    return think\Db::instance($config, $lite);
}

/**
 * 实例化控制器 格式：[模块/]控制器
 * @param string $name 资源地址
 * @param string $layer 控制层名称
 * @return object
 */
function A($name, $layer = CONTROLLER_LAYER)
{
    return think\Loader::controller($name, $layer);
}

/**
 * 调用模块的操作方法 参数格式 [模块/控制器/]操作
 * @param string $url 调用地址
 * @param string|array $vars 调用参数 支持字符串和数组
 * @param string $layer 要调用的控制层名称
 * @return mixed
 */
function R($url, $vars = [], $layer = CONTROLLER_LAYER)
{
    return think\Loader::action($url, $vars, $layer);
}

/**
 * 导入所需的类库 同java的Import 本函数有缓存功能
 * @param string $class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return boolean
 */
function import($class, $baseUrl = '', $ext = EXT)
{
    return think\Loader::import($class, $baseUrl, $ext);
}

/**
 * 快速导入第三方框架类库 所有第三方框架的类库文件统一放到 系统的Vendor目录下面
 * @param string $class 类库
 * @param string $ext 类库后缀
 * @return boolean
 */
function vendor($class, $ext = EXT)
{
    return think\Loader::import($class, VENDOR_PATH, $ext);
}

/**
 * 快速导入Traits
 * @param string $class trait库
 * @param string $ext 类库后缀
 * @return boolean
 */
function T($class, $ext = EXT)
{
    return think\Loader::import($class, TRAIT_PATH, $ext);
}

/**
 * 抛出异常处理
 *
 * @param string  $msg  异常消息
 * @param integer $code 异常代码 默认为0
 *
 * @throws \think\Exception
 */
function E($msg, $code = 0)
{
    throw new think\Exception($msg, $code);
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为true 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @return void|string
 */
function dump($var, $echo = true, $label = null)
{
    return think\Debug::dump($var, $echo, $label);
}

/**
 * 渲染输出Widget
 * @param string $name Widget名称
 * @param array $data 传人的参数
 * @return mixed
 */
function W($name, $data = [])
{
    return think\Loader::action($name, $data, 'Widget');
}

function U($url, $vars = '', $suffix = true, $domain = false)
{
    return think\Url::build($url, $vars, $suffix, $domain);
}

function session($name, $value = '')
{
    if (is_array($name)) {
        // 初始化
        think\Session::init($name);
    } elseif (is_null($name)) {
        // 清除
        think\Session::clear($value);
    } elseif ('' === $value) {
        // 获取
        return think\Session::get($name);
    } elseif (is_null($value)) {
        // 删除session
        return think\Session::delete($name);
    } else {
        // 设置session
        return think\Session::set($name, $value);
    }
}

function cookie($name, $value = '')
{
    if (is_array($name)) {
        // 初始化
        think\Cookie::init($name);
    } elseif (is_null($name)) {
        // 清除
        think\Cookie::clear($value);
    } elseif ('' === $value) {
        // 获取
        return think\Cookie::get($name);
    } elseif (is_null($value)) {
        // 删除session
        return think\Cookie::delete($name);
    } else {
        // 设置session
        return think\Cookie::set($name, $value);
    }
}

/**
 * 缓存管理
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function S($name, $value = '', $options = null)
{
    if (is_array($options)) {
        // 缓存操作的同时初始化
        think\Cache::connect($options);
    } elseif (is_array($name)) {
        // 缓存初始化
        return think\Cache::connect($name);
    }
    if ('' === $value) {
        // 获取缓存
        return think\Cache::get($name);
    } elseif (is_null($value)) {
        // 删除缓存
        return think\Cache::rm($name);
    } else {
        // 缓存数据
        if (is_array($options)) {
            $expire = isset($options['expire']) ? $options['expire'] : null; //修复查询缓存无法设置过期时间
        } else {
            $expire = is_numeric($options) ? $options : null; //默认快捷缓存设置过期时间
        }
        return think\Cache::set($name, $value, $expire);
    }
}

/**
 * 添加Trace记录到SocketLog
 * @param mixed $log log信息 支持字符串和数组
 * @param string $level 日志级别
 * @param string $css 样式
 * @return void|array
 */
function trace($log, $level = 'log', $css = '')
{
    if ('trace' == $level) {
        \think\Slog::trace($log, 2, $css);
    } else {
        \think\Slog::record($level, $log, $css);
    }
}
