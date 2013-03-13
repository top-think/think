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

if(version_compare(PHP_VERSION,'5.4.0','<'))  die('require PHP > 5.4.0 !');
define('MAGIC_QUOTES_GPC',false);

//  版本信息
define('THINK_VERSION', '4.0beta');
// 系统常量
defined('THINK_PATH') 	or  define('THINK_PATH',    dirname(__FILE__).'/');
defined('CORE_PATH') 	or  define('CORE_PATH',     THINK_PATH.'Think/');
defined('APP_PATH') 	or  define('APP_PATH',      dirname($_SERVER['SCRIPT_FILENAME']).'/');
defined('LIB_PATH')     or  define('LIB_PATH',      APP_PATH.'Library/');
defined('RUNTIME_PATH') or  define('RUNTIME_PATH',  realpath(APP_PATH).'/Runtime/');
defined('DATA_PATH')    or  define('DATA_PATH',     RUNTIME_PATH.'Data/');
defined('LOG_PATH')     or  define('LOG_PATH',      RUNTIME_PATH.'Log/');
defined('CACHE_PATH')   or  define('CACHE_PATH',    RUNTIME_PATH.'Temp/');
defined('VENDOR_PATH')  or  define('VENDOR_PATH',   THINK_PATH.'Vendor/');
defined('EXT')          or  define('EXT',           '.php');
defined('APP_DEBUG') 	or  define('APP_DEBUG',false); // 是否调试模式
defined('RUNTIME_FILE') or  define('RUNTIME_FILE',  RUNTIME_PATH.'~runtime.php');

// 为了方便导入第三方类库 设置Vendor目录到include_path
set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);
// 环境常量
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
define('IS_CGI',strpos(PHP_SAPI, 'cgi')=== 0 ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);
define('IS_AJAX',       (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false);
define('NOW_TIME',      $_SERVER['REQUEST_TIME']);

// 获取多语言变量
function L($name){
    return \Think\Lang::get($name);
}

// 获取配置参数
function C($name='',$range='') {
    return \Think\Config::get($name,$range);
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m 
 * @return mixed
 */
function G($name) {
    Think\Debug::remark($name);
}

/**
 * 设置和获取统计数据
 * 使用方法:
 * <code>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </code> 
 * @param string $key 标识位置
 * @param integer $step 步进值
 * @return mixed
 */
function N($key, $step=0) {
    static $_num    = array();
    if (!isset($_num[$key])) {
        $_num[$key] = 0;
    }
    if (empty($step))
        return $_num[$key];
    else
        $_num[$key] = $_num[$key] + (int) $step;
}

/**
 * M函数用于实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return Model
 */
function M($name='', $tablePrefix='',$connection='') {
    return Think\Loader::table($name,$tablePrefix,$connection);
}

/**
 * D函数用于实例化Model
 * @param string $name Model名称
 * @param string $layer 业务层名称
 * @return ThinkModel
 */
function D($name='',$layer='model') {
    return Think\Loader::model($name,$layer);
}

/**
 * A函数用于实例化控制器 格式：[分组/]模块
 * @param string $name 资源地址
 * @param string $layer 控制层名称
 * @return Action|false
 */
function A($name,$layer='') {
    return Think\Loader::controll($name,$layer);
}

/**
 * 远程调用模块的操作方法 参数格式 [模块/控制器/]操作
 * @param string $url 调用地址
 * @param string|array $vars 调用参数 支持字符串和数组 
 * @param string $layer 要调用的控制层名称
 * @return mixed
 */
function R($url,$vars=array(),$layer='') {
    return Think\Loader::action($url,$vars,$layer);
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * 导入所需的类库 同java的Import 本函数有缓存功能
 * @param string $class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return boolean
 */
function import($class, $baseUrl = '', $ext= EXT ) {
    return Think\Loader::import($class,$baseUrl,$ext);
}

/**
 * 自定义异常处理
 * @param string $msg 异常消息
 * @param string $type 异常类型 默认为ThinkException
 * @param integer $code 异常代码 默认为0
 * @return void
 */
function throw_exception($msg, $type='Think\Exception', $code=0) {
    if (class_exists($type))
        throw new $type($msg, $code, true);
    else
        Think\Error::halt($msg);        // 异常类型不存在则输出错误信息字串
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @return void|string
 */
function dump($var, $echo=true, $label=null) {
    return Think\Debug::dump($var,$echo,$label);
}

/**
 * 404处理 
 * 调试模式会抛异常 
 * 部署模式下面传入url参数可以指定跳转页面，否则发送404信息
 * @param string $msg 提示信息
 * @param string $url 跳转URL地址
 * @return void
 */
function _404($msg='',$url='') {
    Think\Config::get('app_debug') && throw_exception($msg);
    if($msg) Think\Log::record($msg,'ERR');
    $url    =   $url?$url:Think\Config::get('url_404_redirect');
    if($url) {
        redirect($url);
    }else{
        header('HTTP/1.1 404 Not Found');
        // 确保FastCGI模式下正常
        header('Status:404 Not Found');
        exit;
    }
}

/**
 * 渲染输出Widget
 * @param string $name Widget名称
 * @param array $data 传人的参数
 * @return void
 */
function W($name, $data=array()) {
    echo R($name,$data,'Widget');
}

/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    //多行URL地址支持
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

/**
 * 缓存管理
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function S($name,$value='',$options=null) {
    static $cache   =   null;
    if(is_array($options)){
        // 缓存操作的同时初始化
        Think\Cache::connect($options);
    }elseif(is_array($name)) { // 缓存初始化
        Think\Cache::connect($name);
    }elseif(is_null($cache)) { // 自动初始化
        Think\Cache::connect();
        $cache  =   true;
    }
    if(''=== $value){ // 获取缓存
        return Think\Cache::get($name);
    }elseif(is_null($value)) { // 删除缓存
        return Think\Cache::rm($name);
    }else { // 缓存数据
        $expire     =   is_numeric($options)?$options:NULL;
        return Think\Cache::set($name, $value, $expire);
    }
}

// 过滤表单中的表达式
function filter_exp(&$value){
    if (in_array(strtolower($value),array('exp','or'))){
        $value .= ' ';
    }
}