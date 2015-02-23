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

namespace think;

class Config {
    static private  $_config =   [];   // 配置参数
    static private  $_range =   '_sys_';   // 参数作用域

    // 设定配置参数的作用域
    static public function range($range){
        self::$_range   =   $range;
    }

    // 解析其他格式的配置参数
    static public function parse($config,$type='',$range=''){
        if(empty($type)) {
            $type   =   substr(strrchr($config, '.'),1);
        }
        $class  =   '\Think\Config\Driver\\'.ucwords($type);
        self::set((new $class())->parse($config),'',$range);
    }

    // 加载配置文件
    static public function load($file,$range=''){
        return self::set(include $file,'',$range);
    }

    // 检测配置是否存在
    static public function has($name,$range=''){
        $range  =   $range ? $range : self::$_range;
        $name   =   strtolower($name);

        if (!strpos($name, '.')) {
            return isset(self::$_config[$range][$name]);
        }else{
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            return isset(self::$_config[$range][$name[0]][$name[1]]);
        }
    }

    // 获取配置参数 为空则获取所有配置
    static public function get($name=null,$range='') {
        $range  =   $range ? $range : self::$_range;
        // 无参数时获取所有
        if (empty($name)) {
            return self::$_config[$range];
        }
        $name = strtolower($name);
        if (!strpos($name, '.')) {
            return isset(self::$_config[$range][$name]) ? self::$_config[$range][$name] : null;
        }else{
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            return isset(self::$_config[$range][$name[0]][$name[1]]) ? self::$_config[$range][$name[0]][$name[1]] : null;
        }
    }
    
    // 设置配置参数 name为数组则为批量设置
    static public function set($name, $value=null,$range='') {
        $range  =   $range ? $range : self::$_range;
        if(!isset(self::$_config[$range])) {
            self::$_config[$range]  =   [];
        }
        if (is_string($name)) {
            $name = strtolower($name);
            if (!strpos($name, '.')) {
                self::$_config[$range][$name] = $value;
            }else{
                // 二维数组设置和获取支持
                $name = explode('.', $name);
                self::$_config[$range][$name[0]][$name[1]] = $value;                
            }
            return;
        }elseif (is_array($name)){         
            // 批量设置
            self::$_config[$range] = array_merge(self::$_config[$range], array_change_key_case($name));
            return self::$_config[$range];
        }else{
            return null; // 避免非法参数
        }
    }
}
