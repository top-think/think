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

// 为了方便导入第三方类库 设置Vendor目录到include_path
set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);
// 环境常量
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
define('IS_CGI',strpos(PHP_SAPI, 'cgi')=== 0 ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);
define('IS_AJAX',       (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false);
define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
define('IS_POST',       REQUEST_METHOD =='POST' ? true : false);
define('IS_PUT',        REQUEST_METHOD =='PUT' ? true : false);
define('IS_DELETE',     REQUEST_METHOD =='DELETE' ? true : false);

// 获取多语言变量
function L($name){
    return Think\Lang::get($name);
}

// 获取配置参数
function C($name='',$range='') {
    return Think\Config::get($name,$range);
}

// 获取输入数据 支持默认值和过滤
function I($key,$default='',$filter='') {
    if(strpos($key,'.')) { // 指定参数来源
        list($method,$key) =   explode('.',$key);
    }else{ // 默认为自动判断
        $method =   'param';
    }
    return Think\Input::$method($key,$filter,$default);
}

/**
 * 记录时间（微秒）和内存使用情况
 * @param string $label 记录标签
 * @return void
 */
function G($label) {
    Think\Debug::remark($label);
}

/**
 * 实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return Model
 */
function M($name='', $tablePrefix='',$connection='') {
    return Think\Loader::table($name,['table_prefix'=>$tablePrefix,'connection'=>$connection]);
}

/**
 * 实例化Model
 * @param string $name Model名称
 * @param string $layer 业务层名称
 * @return object
 */
function D($name='',$layer='model') {
    return Think\Loader::model($name,$layer);
}

/**
 * 实例化控制器 格式：[分组/]模块
 * @param string $name 资源地址
 * @param string $layer 控制层名称
 * @return object
 */
function A($name,$layer='') {
    return Think\Loader::controller($name,$layer);
}

/**
 * 调用模块的操作方法 参数格式 [模块/控制器/]操作
 * @param string $url 调用地址
 * @param string|array $vars 调用参数 支持字符串和数组 
 * @param string $layer 要调用的控制层名称
 * @return mixed
 */
function R($url,$vars=array(),$layer='') {
    return Think\Loader::action($url,$vars,$layer);
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
 * 抛出异常处理
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @return void
 */
function E($msg, $code=0,$url='') {
    if(404 == $code && !C('app_debug')) {
        if($msg) Think\Log::record($msg,'ERR');
        $url    =   $url?$url:C('url_404_redirect');
        if($url) {
            header('Location: ' . $url);
        }else{
            header('HTTP/1.1 404 Not Found');
            // 确保FastCGI模式下正常
            header('Status:404 Not Found');
        }
        exit;
    }
    throw new Think\Exception($msg, $code);
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
 * 渲染输出Widget
 * @param string $name Widget名称
 * @param array $data 传人的参数
 * @return void
 */
function W($name, $data=array()) {
    echo R($name,$data,'Widget');
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
    if(is_array($options)){// 缓存操作的同时初始化
        $cache  =   Think\Cache::connect($options);
    }elseif(is_array($name)) { // 缓存初始化
        $cache  =   Think\Cache::connect($name);
        return $cache;
    }elseif(is_null($cache)) {// 自动初始化
        $cache  =   Think\Cache::connect();
    }
    if(''=== $value){ // 获取缓存
        return $cache->get($name);
    }elseif(is_null($value)) { // 删除缓存
        return $cache->rm($name);
    }else { // 缓存数据
        if(is_array($options)) {
            $expire =   isset($options['expire'])?$options['expire']:NULL;	//修复查询缓存无法设置过期时间
        }else{
            $expire =   is_numeric($options)?$options:NULL;	//默认快捷缓存设置过期时间
        }
        return $cache->set($name, $value, $expire);
    }
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