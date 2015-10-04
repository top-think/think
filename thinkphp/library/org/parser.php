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

// 内容解析类
class Parser
{

    private static $handler = [];

    // 解析内容
    public static function parse($content, $type)
    {
        if (!isset(self::$handler[$type])) {
            $class                = '\\think\\parser\\driver\\' . strtolower($type);
            self::$handler[$type] = new $class();
        }
        return self::$handler[$type]->parse($content);
    }

    // 调用驱动类的方法
    public static function __callStatic($method, $params)
    {
        return self::parse($params[0], $method);
    }
}
