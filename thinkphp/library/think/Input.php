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
    public static $filter = null;

    /**
     * 获取get变量
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
     * 根据请求方法获取变量
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function param($name = '', $default = null, $filter = '')
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $result = self::post($name, $default, $filter);
                break;
            case 'PUT':
                $result = self::put($name, $default, $filter);
                break;
            default:
                $result = self::get($name, $default, $filter);
        }
        return $result;
    }

    /**
     * 获取request变量
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
     * @param $name
     * @param $input
     * @param $filter
     * @param $default
     * @return mixed
     */
    public static function getData($name, $input, $filter = '', $default = null)
    {
        // 解析name
        list($name, $type) = static::parseName($name);
        // 解析过滤器
        $filters = static::parseFilters($filter);
        // 解析值
        if ('' === $name) {
            // 过滤所有输入
            $data = $input;
            // 对数组应用过滤器
            foreach ($filters as $filter) {
                $data = self::filter($filter, $data);
            }
            // 递归过滤表达式
            array_walk_recursive($data, 'self::filterExp');
            // 返回结果
            return $data;
        } elseif (isset($input[$name])) {
            // 过滤name指定的输入
            $data = $input[$name];
        } else {
            // 无输入数据, 下面直接返回默认值
            return $default;
        }

        // 强制类型转换
        $data = static::typeCast($data, $type);

        // 正则过滤
        $regex = static::regexFilter($data, $filter);
        if (false === $regex) {
            // 过滤器是正则表达式, 但数据无匹配
            // 返回默认值
            $data = $default;
        } elseif (!is_null($regex)) {
            // 数据合法，对结果进行强类型转换
            $data = static::typeCast($regex, $type);
        } else {
            // 假如值为数组
            if (is_array($data)) {
                // 递归过滤表达式
                array_walk_recursive($data, 'self::filterExp');
            }
            foreach ($filters as $filter) {
                if (is_callable($filter)) {
                    $data = is_array($data) ? self::filter($filter, $data) : call_user_func($filter, $data); // 参数过滤
                } else {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $data) {
                        // 不通过过滤器则返回默认值
                        return $default;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 过滤表单中的表达式
     * @param string &$value
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
     * @param string $filter
     * @param mixed  $data
     * @return mixed
     */
    public static function filter($filter, $data)
    {
        $result = [];
        foreach ($data as $key => $val) {
            $result[$key] = is_array($val) ? self::filter($filter, $val) : call_user_func($filter, $val);
        }
        return $result;
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
     * @param mixed $filters
     * @return array
     */
    private static function parseFilters($filters)
    {
        $filters = ('' === $filters) ? static::$filter : $filters;

        if (empty($filters)) {
            $result = [];
        } elseif (is_string($filters)) {
            $result = explode(',', $filters);
        } elseif (is_array($filters)) {
            $result = $filters;
        } else {
            $result = [$filters];
        }
        return $result;
    }

    /**
     * 正则过滤
     * @param string $input
     * @param string $filter
     * @return string|false
     */
    private static function regexFilter($input, $filter)
    {
        if (empty($filter) || is_array($input)) {
            return null;
        }
        $begin = $filter[0];
        $end   = $filter[strlen($filter) - 1];
        if (
            ('/' === $begin && '/' === $end) ||
            ('#' === $begin && '#' === $end) ||
            ('~' === $begin && '~' === $end)
        ) {
            return !preg_match($filter, $input) ? false : $input;
        }
        return null;
    }

    /**
     * 强类型转换
     * @param string $data
     * @param string $type
     * @return mixed
     */
    private static function typeCast($data, $type)
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
        return $data;
    }
}
