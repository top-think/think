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

class Input
{
    // 全局过滤规则
    public static $filters;

    /**
     * 获取get变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function get($name = '', $default = null, $filter = null, $merge = false)
    {
        return self::data($_GET, $name, $default, $filter, $merge);
    }

    /**
     * 获取post变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function post($name = '', $default = null, $filter = null, $merge = false)
    {
        return self::data($_POST, $name, $default, $filter, $merge);
    }

    /**
     * 获取put变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function put($name = '', $default = null, $filter = null, $merge = false)
    {
        static $_PUT = null;
        if (is_null($_PUT)) {
            parse_str(file_get_contents('php://input'), $_PUT);
        }
        return self::data($_PUT, $name, $default, $filter, $merge);
    }
    
    /**
     * 获取delete变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function delete($name = '', $default = null, $filter = null, $merge = false)
    {
        static $_DELETE = null;
        if (is_null($_DELETE)) {
            parse_str(file_get_contents('php://input'), $_DELETE);
            $_DELETE = array_merge($_DELETE, $_GET);
        }
        return self::data($_DELETE, $name, $default, $filter, $merge);
    }

    /**
     * 根据请求方法获取变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function param($name = '', $default = null, $filter = null, $merge = false)
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $method = 'post';
                break;
            case 'PUT':
                $method = 'put';
                break;
            case 'DELETE':
                $method = 'delete';
                break;
            default:
                $method = 'get';
        }
        return self::$method($name, $default, $filter, $merge);
    }

    /**
     * 获取request变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function request($name = '', $default = null, $filter = null, $merge = false)
    {
        return self::data($_REQUEST, $name, $default, $filter, $merge);
    }

    /**
     * 获取session变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function session($name = '', $default = null, $filter = null, $merge = false)
    {
        return self::data($_SESSION, $name, $default, $filter, $merge);
    }

    /**
     * 获取cookie变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function cookie($name = '', $default = null, $filter = null, $merge = false)
    {
        return self::data($_COOKIE, $name, $default, $filter, $merge);
    }

    /**
     * 获取post变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function server($name = '', $default = null, $filter = null, $merge = false)
    {
        return self::data($_SERVER, strtoupper($name), $default, $filter, $merge);
    }

    /**
     * 获取GLOBALS变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function globals($name = '', $default = null, $filter = null, $merge = false)
    {
        return self::data($GLOBALS, $name, $default, $filter, $merge);
    }

    /**
     * 获取环境变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function env($name = '', $default = null, $filter = null, $merge = false)
    {
        return self::data($_ENV, strtoupper($name), $default, $filter, $merge);
    }

    /**
     * 获取PATH_INFO
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function path($name = '', $default = null, $filter = null, $merge = false)
    {
        if (!empty($_SERVER['PATH_INFO'])) {
            $depr  = \think\Config::get('pathinfo_depr');
            $input = explode($depr, trim($_SERVER['PATH_INFO'], $depr));
            return self::data($input, $name, $default, $filter, $merge);
        } else {
            return $default;
        }
    }

    /**
     * 获取$_FILES
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function file($name = '', $default = null, $filter = null, $merge = false)
    {
        return self::data($_FILES, $name, $default, $filter, $merge);
    }

    /**
     * 获取变量 支持过滤和默认值
     * @param array $input 数据源
     * @param string $name 字段名
     * @param mixed $default 默认值
     * @param mixed $filter 过滤函数
     * @param boolean $merge 是否与默认的过虑方法合并
     * @return mixed
     */
    public static function data($input, $name = '', $default = null, $filter = null, $merge = false)
    {
        if (0 === strpos($name, '?')) {
            return self::has(substr($name, 1), $input);
        }
        if (!empty($input)) {
            $data = $input;
            $name = (string) $name;
            if ('' != $name) {
                // 解析name
                list($name, $type) = static::parseName($name);
                // 按.拆分成多维数组进行判断
                foreach (explode('.', $name) as $val) {
                    if (isset($data[$val])) {
                        $data = $data[$val];
                    } else {
                        // 无输入数据，返回默认值
                        return $default;
                    }
                }
            }

            // 解析过滤器
            $filters = static::parseFilter($filter, $merge);
            // 为方便传参把默认值附加在过滤器后面
            $filters[] = $default;
            if (is_array($data)) {
                array_walk_recursive($data, 'self::filter', $filters);
            } else {
                self::filter($data, $name ?: 0, $filters);
            }
            if (isset($type) && $data !== $default) {
                // 强制类型转换
                static::typeCast($data, $type);
            }
        } else {
            $data = $default;
        }
        return $data;
    }

    /**
     * 判断一个变量是否设置
     * @param string $name
     * @param array $data
     * @return bool
     */
    public static function has($name, $data)
    {
        foreach (explode('.', $name) as $val) {
            if (!isset($data[$val])) {
                return false;
            } else {
                $data = $data[$val];
            }
        }
        return true;
    }

    /**
     * 设置默认的过滤函数
     * @param string|array $name
     * @return array
     */
    public static function setFilter($name)
    {
        if (is_string($name)) {
            $name = explode(',', $name);
        }
        static::$filters = $name;
    }

    /**
     * 过滤表单中的表达式
     * @param string $value
     * @return void
     */
    public static function filterExp(&$value)
    {
        // TODO 其他安全过滤

        // 过滤查询特殊字符
        if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
            $value .= ' ';
        }
    }

    /**
     * 递归过滤给定的值
     * @param mixed $value 键值
     * @param mixed $key 键名
     * @param array $filters 过滤方法+默认值
     * @return mixed
     */
    private static function filter(&$value, $key, $filters)
    {
        // 分离出默认值
        $default = array_pop($filters);
        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                // 调用函数过滤
                $value = call_user_func($filter, $value);
            } else {
                $begin = substr($filter, 0, 1);
                if (in_array($begin, ['/', '#', '~']) && $begin == $end = substr($filter, -1)) {
                    // 正则过滤
                    if (!preg_match($filter, $value)) {
                        // 匹配不成功返回默认值
                        $value = $default;
                        break;
                    }
                } else {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $value = filter_var($value, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $value) {
                        // 不通过过滤器则返回默认值
                        $value = $default;
                        break;
                    }
                }
            }
        }
        self::filterExp($value);
    }

    /**
     * 解析name
     * @param string $name
     * @return array 返回name和类型
     */
    private static function parseName($name)
    {
        return strpos($name, '/') ? explode('/', $name, 2) : [$name, 's'];
    }

    /**
     * 解析过滤器
     * @param mixed $filter
     * @return array
     */
    private static function parseFilter($filter, $merge = false)
    {
        if (is_null($filter)) {
            $result = self::getFilter();
        } elseif (empty($filter)) {
            $result = [];
        } else {
            if (is_array($filter)) {
                $result = $filter;
            } elseif (is_string($filter) && strpos($filter, ',')) {
                $result = explode(',', $filter);
            } else {
                $result = [$filter];
            }
            if ($merge) {
                // 与默认的过滤函数合并
                $result = array_merge(self::getFilter(), array_diff($result, self::getFilter()));
            }
        }
        return $result;
    }

    /**
     * 获取过滤方法
     * @return array
     */
    private static function getFilter()
    {
        if (is_null(static::$filters)) {
            // 从配置项中读取
            $filters         = \think\Config::get('default_filter');
            static::$filters = empty($filters) ? [] : (is_array($filters) ? $filters : explode(',', $filters));
        }
        return static::$filters;
    }

    /**
     * 强类型转换
     * @param string $data
     * @param string $type
     * @return mixed
     */
    private static function typeCast(&$data, $type)
    {
        switch (strtolower($type)) {
            // 数组
            case 'a':
                $data = (array) $data;
                break;
            // 数字
            case 'd':
                $data = (int) $data;
                break;
            // 浮点
            case 'f':
                $data = (float) $data;
                break;
            // 布尔
            case 'b':
                $data = (boolean) $data;
                break;
            // 字符串
            case 's':
            default:
                $data = (string) $data;
        }
    }
}
