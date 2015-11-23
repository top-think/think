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

class Loader
{
    // 类名映射
    protected static $map = [];
    // 命名空间
    protected static $namespace = [];

    // 自动加载
    public static function autoload($class)
    {
        // 检查是否定义classmap
        if (isset(self::$map[$class])) {
            include self::$map[$class];
        } else {
            // 命名空间自动加载
            $name = strstr($class, '\\', true);
            if (isset(self::$namespace[$name])) {
                // 注册的命名空间
                $path = dirname(self::$namespace[$name]) . '/';
            } elseif (in_array($name, ['think', 'org', 'behavior', 'com']) || is_dir(LIB_PATH . $name)) {
                // Library目录下面的命名空间自动定位
                $path = LIB_PATH;
            } else {
                // 项目命名空间
                $path = APP_PATH;
            }
            $filename = $path . str_replace('\\', '/', str_replace('\\_', '\\', strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $class), "_")))) . EXT;
            if (is_file($filename)) {
                include $filename;
            }
        }
    }

    // 注册classmap
    public static function addMap($class, $map = '')
    {
        if (is_array($class)) {
            self::$map = array_merge(self::$map, $class);
        } else {
            self::$map[$class] = $map;
        }
    }

    // 注册命名空间
    public static function addNamespace($namespace, $path)
    {
        self::$namespace[$namespace] = $path;
    }

    // 注册自动加载机制
    public static function register($autoload = '')
    {
        spl_autoload_register($autoload ? $autoload : ['think\loader', 'autoload']);
    }

    /**
     * 导入所需的类库 同java的Import 本函数有缓存功能
     * @param string $class 类库命名空间字符串
     * @param string $baseUrl 起始路径
     * @param string $ext 导入的文件扩展名
     * @return boolean
     */
    public static function import($class, $baseUrl = '', $ext = EXT)
    {
        static $_file = [];
        $class        = str_replace(['.', '#'], ['/', '.'], $class);
        if (isset($_file[$class . $baseUrl])) {
            return true;
        } else {
            $_file[$class . $baseUrl] = true;
        }

        $class_strut = explode('/', $class);
        if (empty($baseUrl)) {
            if ('@' == $class_strut[0] || MODULE_NAME == $class_strut[0]) {
                //加载当前项目应用类库
                $class   = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
                $baseUrl = MODULE_PATH;
            } elseif (in_array($class_strut[0], ['think', 'behavior', 'org', 'com'])) {
                // org 第三方公共类库 com 企业公共类库
                $baseUrl = LIB_PATH;
            } elseif (in_array($class_strut[0], ['vendor', 'traits'])) {
                $baseUrl = THINK_PATH;
            } else {
                // 加载其他项目应用类库
                $class   = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
                $baseUrl = APP_PATH . $class_strut[0] . '/';
            }
        }
        if (substr($baseUrl, -1) != '/') {
            $baseUrl .= '/';
        }
        // 如果类存在 则导入类库文件
        $filename = $baseUrl . $class . $ext;
        if (is_file($filename)) {
            include $filename;
            return true;
        }
        return false;
    }

    /**
     * 实例化一个没有模型文件的Model（对应数据表）
     * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
     * @param array $options 模型参数
     * @return Model
     */
    public static function table($name = '', $options = [])
    {
        static $_model = [];
        if (strpos($name, ':')) {
            list($class, $name) = explode(':', $name);
        } else {
            $class = 'think\model';
        }
        $guid = $name . '_' . $class;
        if (!isset($_model[$guid])) {
            $_model[$guid] = new $class($name, $options);
        }
        return $_model[$guid];
    }

    /**
     * 实例化（分层）模型
     * @param string $name Model名称
     * @param string $layer 业务层名称
     * @return Object
     */
    public static function model($name = '', $layer = MODEL_LAYER)
    {
        if (empty($name)) {
            return new Model;
        }
        static $_model = [];
        if (isset($_model[$name . $layer])) {
            return $_model[$name . $layer];
        }
        if (strpos($name, '/')) {
            list($module, $name) = explode('/', $name);
        } else {
            $module = MODULE_NAME;
        }
        $class = $module . '\\' . $layer . '\\' . self::parseName(str_replace('.', '\\', $name), 1);
        if (class_exists($class)) {
            $model = new $class($name);
        } else {
            Log::record('实例化不存在的类：' . $class, 'NOTIC');
            $model = new Model($name);
        }
        $_model[$name . $layer] = $model;
        return $model;
    }

    /**
     * 实例化（分层）控制器 格式：[模块名/]控制器名
     * @param string $name 资源地址
     * @param string $layer 控制层名称
     * @param string $empty 空控制器名称
     * @return Object|false
     */
    public static function controller($name, $layer = '', $empty = '')
    {
        static $_instance = [];
        $layer            = $layer ?: CONTROLLER_LAYER;
        if (isset($_instance[$name . $layer])) {
            return $_instance[$name . $layer];
        }
        if (strpos($name, '/')) {
            list($module, $name) = explode('/', $name);
        } else {
            $module = MODULE_NAME;
        }
        $class = $module . '\\' . $layer . '\\' . self::parseName(str_replace('.', '\\', $name), 1);
        if (class_exists($class)) {
            $action                    = new $class;
            $_instance[$name . $layer] = $action;
            return $action;
        } elseif ($empty && class_exists($module . '\\' . $layer . '\\' . $empty)) {
            $class = $module . '\\' . $layer . '\\' . $empty;
            return new $class;
        } else {
            return false;
        }
    }

    /**
     * 实例化数据库
     * @param mixed $config 数据库配置
     * @param boolean $lite 是否采用lite方式连接
     * @return object
     */
    public static function db($config, $lite = false)
    {
        return Db::instance($config, $lite);
    }

    /**
     * 远程调用模块的操作方法 参数格式 [模块/控制器/]操作
     * @param string $url 调用地址
     * @param string|array $vars 调用参数 支持字符串和数组
     * @param string $layer 要调用的控制层名称
     * @return mixed
     */
    public static function action($url, $vars = [], $layer = CONTROLLER_LAYER)
    {
        $info   = pathinfo($url);
        $action = $info['basename'];
        $module = '.' != $info['dirname'] ? $info['dirname'] : CONTROLLER_NAME;
        $class  = self::controller($module, $layer);
        if ($class) {
            if (is_string($vars)) {
                parse_str($vars, $vars);
            }
            return call_user_func_array([ & $class, $action . Config::get('action_suffix')], $vars);
        } else {
            return false;
        }
    }

    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     * @return object
     */
    public static function instance($class, $method = '')
    {
        static $_instance = [];
        $identify         = $class . $method;
        if (!isset($_instance[$identify])) {
            if (class_exists($class)) {
                $o = new $class();
                if (!empty($method) && method_exists($o, $method)) {
                    $_instance[$identify] = call_user_func_array([ & $o, $method]);
                } else {
                    $_instance[$identify] = $o;
                }

            } else {
                throw new Exception('_CLASS_NOT_EXIST_:' . $class);
            }

        }
        return $_instance[$identify];
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @return string
     */
    public static function parseName($name, $type = 0)
    {
        if ($type) {
            return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {return strtoupper($match[1]);}, $name));
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }
}
