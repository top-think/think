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

class Input
{
    // 全局过滤规则
    static $filter = null;

    /**
     * 获取get变量
     * @access public
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function get($name = '', $default = null, $filter = '')
    {
        return self::getData($name, $_GET, $filter, $default);
    }

    /**
     * 获取post变量
     * @access public
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function post($name = '', $default = null, $filter = '')
    {
        return self::getData($name, $_POST, $filter, $default);
    }

    /**
     * 获取put变量
     * @access public
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function put($name = '', $default = null, $filter = '')
    {
        static $_PUT = null;
        if (is_null($_PUT)) {
            parse_str(file_get_contents('php://input'), $_PUT);
        }
        return self::getData($name, $_PUT, $filter, $default);
    }

    /**
     * 获取post变量
     * @access public
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function param($name = '', $default = null, $filter = '')
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                return self::post($name, $default, $filter);
            case 'PUT':
                return self::put($name, $default, $filter);
            default:
                return self::get($name, $default, $filter);
        }
    }

    /**
     * 获取request变量
     * @access public
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function request($name = '', $default = null, $filter = '')
    {
        return self::getData($name, $_REQUEST, $filter, $default);
    }

    /**
     * 获取session变量
     * @access public
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function session($name = '', $default = null, $filter = '')
    {
        return self::getData($name, $_SESSION, $filter, $default);
    }

    /**
     * 获取cookie变量
     * @access public
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function cookie($name = '', $default = null, $filter = '')
    {
        return self::getData($name, $_COOKIE, $filter, $default);
    }

    /**
     * 获取post变量
     * @access public
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function server($name = '', $default = null, $filter = '')
    {
        return self::getData(strtoupper($name), $_SERVER, $filter, $default);
    }

    /**
     * 获取GLOBALS变量
     * @access public
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function globals($name = '', $default = null, $filter = '')
    {
        return self::getData($name, $GLOBALS, $filter, $default);
    }

    /**
     * 获取环境变量
     * @access public
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function env($name = '', $default = null, $filter = '')
    {
        return self::getData(strtoupper($name), $_ENV, $filter, $default);
    }

    /**
     * 获取系统变量 支持过滤和默认值
     * @access public
     * @param string $method 输入数据类型
     * @param array $args 参数 [key,filter,default]
     * @return mixed
     */
    private static function getData($name, $input, $filter, $default)
    {
        if (strpos($name, '/')) {
            // 指定修饰符
            list($name, $type) = explode('/', $name, 2);
        } else {
            // 默认强制转换为字符串
            $type = 's';
        }
        $filters = isset($filter) ? $filter : self::$filter;
        if ('' == $name) {
            // 获取全部变量
            $data = $input;
            if ($filters) {
                if (is_string($filters)) {
                    $filters = explode(',', $filters);
                }
                foreach ($filters as $filter) {
                    $data = self::filter($filter, $data); // 参数过滤
                }
            }
        } elseif (isset($input[$name])) {
            // 取值操作
            $data = $input[$name];
            if ($filters) {
                if (is_string($filters)) {
                    if (0 === strpos($filters, '/')) {
                        if (1 !== preg_match($filters, (string) $data)) {
                            // 支持正则验证
                            return $default;
                        }
                    } else {
                        $filters = explode(',', $filters);
                    }
                } elseif (is_int($filters)) {
                    $filters = [$filters];
                }

                if (is_array($filters)) {
                    foreach ($filters as $filter) {
                        if (function_exists($filter)) {
                            $data = is_array($data) ? self::filter($filter, $data) : $filter($data); // 参数过滤
                        } else {
                            $data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));
                            if (false === $data) {
                                return $default;
                            }
                        }
                    }
                }
            }
            if (!empty($type)) {
                switch (strtolower($type)) {
                    case 'a': // 数组
                        $data = (array) $data;
                        break;
                    case 'd': // 数字
                        $data = (int) $data;
                        break;
                    case 'f': // 浮点
                        $data = (float) $data;
                        break;
                    case 'b': // 布尔
                        $data = (boolean) $data;
                        break;
                    case 's': // 字符串
                    default:
                        $data = (string) $data;
                }
            }
        } else {
            // 变量默认值
            $data = $default;
        }
        is_array($data) && array_walk_recursive($data, 'self::filterExp');
        return $data;
    }

    // 过滤表单中的表达式
    public static function filterExp(&$value)
    {
        // TODO 其他安全过滤

        // 过滤查询特殊字符
        if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
            $value .= ' ';
        }
    }

    public static function filter($filter, $data)
    {
        $result = [];
        foreach ($data as $key => $val) {
            $result[$key] = is_array($val)
            ? self::filter($filter, $val)
            : call_user_func($filter, $val);
        }
        return $result;
    }
}
