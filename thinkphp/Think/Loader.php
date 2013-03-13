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
use Think\Config;

class Loader {
    // 类名映射
    static protected $map      =    [];
    // 命名空间
    static protected $namespace    =    [
        'Think'     =>  CORE_PATH,
        'Vendor'    =>  VENDOR_PATH,
    ];

    static public function autoload($class){
        // 检查是否定义classmap
        if(isset(self::$map[$class])) {
            include self::$map[$class];
            return ;
        }else{ // 命名空间自动加载
            $find   =   false;
            foreach (self::$namespace as $name=>$path){
                if(0 === stripos($class,$name)) {
                    $find   =   true;
                    break;
                }
            }
            $path   =   $find?dirname($path).'/':APP_PATH;
            $filename   =   $path.str_replace('\\','/',$class).'.php';
            if(is_file($filename)) {
                include $filename;
                return ;
            }
        }
        // 扫描模块目录
        /*
        //if(defined(\MODULE_PATH)) {
            $dir    =   glob(MODULE_PATH.'*');
            foreach ($dir as $path){
                if(false === strpos($path,'.') && $pos = strripos($class,basename($path))) {
                    $name   =   parse_name(substr($class,0,$pos));
                    if(is_file($path.'/'.$name.EXT)) {
                        include $path.'/'.$name.EXT;
                        return ;
                    }
                }
            }
        //}*/
    }

    // 注册classmap
    static public function addMap($class,$map){
        self::$map[$class]  =   $map;
    }

    // 注册命名空间
    static public function addNamespace($namespace,$path){
        self::$namespace[$namespace]    =   $path;
    }

    // 加载classmap
    static public function loadMap($map){
        self::$map  =   array_merge(self::$map,$map);
    }

    static public function register($autoload=''){
        spl_autoload_register($autoload?$autoload:['Think\Loader','autoload']);
    }

    /**
     * 导入所需的类库 同java的Import 本函数有缓存功能
     * @param string $class 类库命名空间字符串
     * @param string $baseUrl 起始路径
     * @param string $ext 导入的文件扩展名
     * @return boolean
     */
    static public function import($class, $baseUrl = '', $ext= EXT ) {
        static $_file = [];
        $class = str_replace(array('.', '#'), array('/', '.'), $class);
        if (isset($_file[$class . $baseUrl]))
            return true;
        else
            $_file[$class . $baseUrl] = true;
        $class_strut     = explode('/', $class);
        if (empty($baseUrl)) {
            if ('@' == $class_strut[0] || MODULE_NAME == $class_strut[0]) {
                //加载当前项目应用类库
                $class   =  substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
                $baseUrl =  MODULE_PATH;
            }elseif (in_array(strtolower($class_strut[0]), array('org','com'))) {
                // org 第三方公共类库 com 企业公共类库
                $baseUrl =  LIB_PATH;
            }elseif('vendor' == strtolower($class_strut[0])){
                $baseUrl =  VENDOR_PATH;
            }else { // 加载其他项目应用类库
                $class   = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
                $baseUrl = APP_PATH . $class_strut[0] .'/';
            }
        }
        if (substr($baseUrl, -1) != '/')
            $baseUrl    .= '/';
        // 如果类不存在 则导入类库文件
        $filename   =   $baseUrl . $class . $ext;
        if(is_file($filename)) {
            include $filename;
            return true;
        }
        return false; 
    }

    /**
     * M函数用于实例化一个没有模型文件的Model
     * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
     * @param string $tablePrefix 表前缀
     * @param mixed $connection 数据库连接信息
     * @return Model
     */
    static public function table($name='', $tablePrefix='',$connection='') {
        static $_model  = [];
        if(strpos($name,':')) {
            list($class,$name)    =  explode(':',$name);
        }else{
            $class      =   'Think\Model';
        }
        $guid           =   $tablePrefix . $name . '_' . $class;
        if (!isset($_model[$guid]))
            $_model[$guid] = new $class($name,$tablePrefix,$connection);
        return $_model[$guid];
    }

    /**
     * D函数用于实例化Model
     * @param string $name Model名称
     * @param string $layer 业务层名称
     * @return Think\Model
     */
    static public function model($name='',$layer='Model') {
        if(empty($name)) return new Model;
        static $_model  =   [];
        if(isset($_model[$name.$layer]))   return $_model[$name.$layer];
        if(strpos($name,'/')) {
            list($module,$name) =   explode('/',$name);
        }else{
            $module =   MODULE_NAME;
        }
        $class      =   $module.'\\'.$layer.'\\'.parse_name($name,1).$layer;
        if(class_exists($class)) {
            $model      =   new $class($name);
        }else {
            $model      =   new Model($name);
        }
        $_model[$name.$layer]  =  $model;
        return $model;
    }

    /**
     * A函数用于实例化控制器 格式：[分组/]模块
     * @param string $name 资源地址
     * @param string $layer 控制层名称
     * @return Action|false
     */
    static public function controll($name,$layer='') {
        $layer      =   $layer?$layer:'Controll';
        static $_instance  =   [];
        if(isset($_instance[$name.$layer]))   return $_instance[$name.$layer];
        if(strpos($name,'/')) {
            list($module,$name) =   explode('/',$name);
        }else{
            $module =   MODULE_NAME;
        }
        $class      =   $module.'\\'.$layer.'\\'.parse_name($name,1).$layer;
        if(class_exists($class)) {
            $action  =   new $class();
            $_instance[$name.$layer]    =   $action;
            return $action;
        }else{
            return false;
        }
    }

    /**
     * 实例化数据库
     * @param string $name 资源地址
     * @param string $layer 控制层名称
     * @return Action|false
     */
    static public function db($config) {
        return Db::getInstance($config);
    }

    /**
     * 远程调用模块的操作方法 参数格式 [模块/控制器/]操作
     * @param string $url 调用地址
     * @param string|array $vars 调用参数 支持字符串和数组 
     * @param string $layer 要调用的控制层名称
     * @return mixed
     */
    static public function action($url,$vars=[],$layer='') {
        $info   =   pathinfo($url);
        $action =   $info['basename'];
        $module =   '.' != $info['dirname']?$info['dirname']:CONTROLL_NAME;
        $class  =   self::controll($module,$layer);
        if($class){
            if(is_string($vars)) {
                parse_str($vars,$vars);
            }
            return call_user_func_array(array(&$class,$action.Config::get('action_suffix')),$vars);
        }else{
            return false;
        }
    }

    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     * @return object
     */
    static public function instance($class,$method='') {
        $identify   =   $class.$method;
        if(!isset(self::$_instance[$identify])) {
            if(class_exists($class)){
                $o = new $class();
                if(!empty($method) && method_exists($o,$method))
                    self::$_instance[$identify] = call_user_func_array(array(&$o, $method));
                else
                    self::$_instance[$identify] = $o;
            }
            else
                Error::halt(Lang::get('_CLASS_NOT_EXIST_').':'.$class);
        }
        return self::$_instance[$identify];
    }

}