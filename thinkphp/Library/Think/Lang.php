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

namespace Think;

class Lang {
    static private  $_lang  =   [];         // 语言参数
    static private  $_range =   '_sys_';   // 作用域

    // 设定语言参数的作用域
    static public function range($range){
        self::$_range   =   $range;
    }

    /**
     * 设置语言定义(不区分大小写)
     * @param string|array $name 语言变量
     * @param string $value 语言值
     * @param string $range 作用域
     * @return mixed
     */
    static public function set($name, $value=null,$range='') {
        $range  =   $range?$range:self::$_range;
        // 批量定义
        if (is_array($name)){
            return self::$_lang[$range] = array_merge(self::$_lang[$range], array_change_key_case($name));
        }else{
            return self::$_lang[$range][strtolower($name)] = $value;
        }
    }

    /**
     * 获取语言定义(不区分大小写)
     * @param string|null $name 语言变量
     * @param string $range 作用域
     * @return mixed
     */
    static public function get($name=null, $range='') {
        $range  =   $range?$range:self::$_range;
        // 空参数返回所有定义
        if (empty($name))
            return self::$_lang[$range];
        $name = strtolower($name);
        return isset(self::$_lang[$range][$name]) ? self::$_lang[$range][$name] : $name;
    }
}