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

class Lang
{
    // 语言参数
    private static $lang = [];
    // 语言作用域
    private static $range = 'zh-cn';

    // 设定语言参数的作用域（语言）
    public static function range($range = '')
    {
        if ('' == $range) {
            return self::$range;
        } else {
            self::$range = $range;
        }
    }

    /**
     * 设置语言定义(不区分大小写)
     * @param string|array $name 语言变量
     * @param string $value 语言值
     * @param string $range 作用域
     * @return mixed
     */
    public static function set($name, $value = null, $range = '')
    {
        $range = $range ?: self::$range;
        // 批量定义
        if (!isset(self::$lang[$range])) {
            self::$lang[$range] = [];
        }
        if (is_array($name)) {
            return self::$lang[$range] = array_merge(self::$lang[$range], array_change_key_case($name));
        } else {
            return self::$lang[$range][strtolower($name)] = $value;
        }
    }

    /**
     * 加载语言定义(不区分大小写)
     * @param string $file 语言文件
     * @param string $range 作用域
     * @return mixed
     */
    public static function load($file, $range = '')
    {
        $range = $range ?: self::$range;
        if (!isset(self::$lang[$range])) {
            self::$lang[$range] = [];
        }
        // 批量定义
        if (is_string($file)) {
            $file = [$file];
        }
        $lang = [];
        foreach ($file as $_file) {
            $_lang = is_file($_file) ? include $_file : [];
            $lang  = array_merge($lang, array_change_key_case($_lang));
        }
        if (!empty($lang)) {
            self::$lang[$range] = array_merge(self::$lang[$range], $lang);
        }
        return self::$lang[$range];
    }

    /**
     * 获取语言定义(不区分大小写)
     * @param string|null $name 语言变量
     * @param array $vars 变量替换
     * @param string $range 作用域
     * @return mixed
     */
    public static function get($name = null, $vars = [], $range = '')
    {
        $range = $range ?: self::$range;
        // 空参数返回所有定义
        if (empty($name)) {
            return self::$lang[$range];
        }
        $key   = strtolower($name);
        $value = isset(self::$lang[$range][$key]) ? self::$lang[$range][$key] : $name;
        if (is_array($vars) && !empty($vars)) {
            // 支持变量
            $replace = array_keys($vars);
            foreach ($replace as &$v) {
                $v = '{$' . $v . '}';
            }
            $value = str_replace($replace, $vars, $value);
        }
        return $value;
    }

    /**
     * 自动侦测设置获取语言选择
     * @return void
     */
    public static function detect()
    {
        // 自动侦测设置获取语言选择
        $langCookieVar = Config::get('lang_cookie_var');
        $langDetectVar = Config::get('lang_detect_var');
        if (isset($_GET[$langDetectVar])) {
            // url中设置了语言变量
            $langSet = strtolower($_GET[$langDetectVar]);
            \think\Cookie::set($langCookieVar, $langSet, 3600);
        } elseif (\think\Cookie::get($langCookieVar)) {
            // 获取上次用户的选择
            $langSet = strtolower(\think\Cookie::get($langCookieVar));
        } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // 自动侦测浏览器语言
            preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
            $langSet = strtolower($matches[1]);
            \think\Cookie::set($langCookieVar, $langSet, 3600);
        }
        if (in_array($langSet, \think\Config::get('lang_list'))) {
            // 合法的语言
            self::$range = $langSet;
        }
    }
}
