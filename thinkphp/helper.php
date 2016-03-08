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

//------------------------
// ThinkPHP 助手函数
//-------------------------

// 获取多语言变量
function L($name, $vars = [], $lang = '')
{
    return \think\Lang::get($name, $vars, $lang);
}

// 获取配置参数
function C($name = '', $value = null, $range = '')
{
    if (is_null($value) && is_string($name)) {
        return \think\Config::get($name, $range);
    } else {
        return \think\Config::set($name, $value, $range);
    }
}

// 获取输入数据 支持默认值和过滤
function I($key, $default = null, $filter = '', $merge = false)
{
    if (0 === strpos($key, '?')) {
        $key = substr($key, 1);
        $has = '?';
    } else {
        $has = '';
    }
    if ($pos = strpos($key, '.')) {
        // 指定参数来源
        $method = substr($key, 0, $pos);
        if (in_array($method, ['get', 'post', 'put', 'delete', 'param', 'request', 'session', 'cookie', 'server', 'globals', 'env', 'path', 'file'])) {
            $key = substr($key, $pos + 1);
        } else {
            $method = 'param';
        }
    } else {
        // 默认为自动判断
        $method = 'param';
    }
    return \think\Input::$method($has . $key, $default, $filter, $merge);
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
        \think\Debug::remark($start);
    } else {
        return 'm' == $dec ? \think\Debug::getRangeMem($start, $end) : \think\Debug::getRangeTime($start, $end, $dec);
    }
}

/**
 * 实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string|null $tablePrefix 表前缀 null表示自动获取配置
 * @param mixed $connection 数据库连接信息
 * @return \Think\Model
 */
function M($name = '', $tablePrefix = null, $connection = '')
{
    return \think\Loader::table($name, ['prefix' => $tablePrefix, 'connection' => $connection]);
}

/**
 * 实例化Model
 * @param string $name Model名称
 * @param string $layer 业务层名称
 * @return object
 */
function D($name = '', $layer = MODEL_LAYER)
{
    return \think\Loader::model($name, $layer);
}

/**
 * 实例化数据库类
 * @param array $config 数据库配置参数
 * @return object
 */
function db($config = [])
{
    return \think\Db::connect($config);
}

/**
 * 实例化控制器 格式：[模块/]控制器
 * @param string $name 资源地址
 * @param string $layer 控制层名称
 * @return object
 */
function A($name, $layer = CONTROLLER_LAYER)
{
    return \think\Loader::controller($name, $layer);
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
    return \think\Loader::action($url, $vars, $layer);
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
    return \think\Loader::import($class, $baseUrl, $ext);
}

/**
 * 快速导入第三方框架类库 所有第三方框架的类库文件统一放到 系统的Vendor目录下面
 * @param string $class 类库
 * @param string $ext 类库后缀
 * @return boolean
 */
function vendor($class, $ext = EXT)
{
    return \think\Loader::import($class, VENDOR_PATH, $ext);
}

/**
 * 快速导入Traits
 * @param string $class trait库
 * @param string $ext 类库后缀
 * @return boolean
 */
function T($class, $ext = EXT)
{
    return \think\Loader::import($class, TRAIT_PATH, $ext);
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
    throw new \think\Exception($msg, $code);
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
    return \think\Debug::dump($var, $echo, $label);
}

/**
 * 渲染输出Widget
 * @param string $name Widget名称
 * @param array $data 传人的参数
 * @return mixed
 */
function W($name, $data = [])
{
    return \think\Loader::action($name, $data, 'widget');
}

function U($url = '', $vars = '', $suffix = true, $domain = false)
{
    return \think\Url::build($url, $vars, $suffix, $domain);
}

function session($name, $value = '')
{
    if (is_array($name)) {
        // 初始化
        \think\Session::init($name);
    } elseif (is_null($name)) {
        // 清除
        \think\Session::clear($value);
    } elseif ('' === $value) {
        // 获取
        return \think\Session::get($name);
    } elseif (is_null($value)) {
        // 删除session
        return \think\Session::delete($name);
    } else {
        // 设置session
        return \think\Session::set($name, $value);
    }
}

function cookie($name, $value = '')
{
    if (is_array($name)) {
        // 初始化
        \think\Cookie::init($name);
    } elseif (is_null($name)) {
        // 清除
        \think\Cookie::clear($value);
    } elseif ('' === $value) {
        // 获取
        return \think\Cookie::get($name);
    } elseif (is_null($value)) {
        // 删除session
        return \think\Cookie::delete($name);
    } else {
        // 设置session
        return \think\Cookie::set($name, $value);
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
        \think\Cache::connect($options);
    } elseif (is_array($name)) {
        // 缓存初始化
        return \think\Cache::connect($name);
    }
    if ('' === $value) {
        // 获取缓存
        return \think\Cache::get($name);
    } elseif (is_null($value)) {
        // 删除缓存
        return \think\Cache::rm($name);
    } else {
        // 缓存数据
        if (is_array($options)) {
            $expire = isset($options['expire']) ? $options['expire'] : null; //修复查询缓存无法设置过期时间
        } else {
            $expire = is_numeric($options) ? $options : null; //默认快捷缓存设置过期时间
        }
        return \think\Cache::set($name, $value, $expire);
    }
}

/**
 * 记录日志信息
 * @param mixed $log log信息 支持字符串和数组
 * @param string $level 日志级别
 * @return void|array
 */
function trace($log = '[think]', $level = 'log')
{
    if ('[think]' === $log) {
        return \think\Log::getLog();
    } else {
        \think\Log::record($log, $level);
    }
}

/**
 * 渲染模板输出
 * @param string $template 模板文件
 * @param array $vars 模板变量
 * @return string
 */
function V($template = '', $vars = [])
{
    return \think\View::instance(\think\Config::get())->fetch($template, $vars);
}
