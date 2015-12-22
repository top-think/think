<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace org;

/* 缩略图相关常量定义 */
define('THINKIMAGE_THUMB_SCALING', 1); //常量，标识缩略图等比例缩放类型
define('THINKIMAGE_THUMB_FILLED', 2); //常量，标识缩略图缩放后填充类型
define('THINKIMAGE_THUMB_CENTER', 3); //常量，标识缩略图居中裁剪类型
define('THINKIMAGE_THUMB_NORTHWEST', 4); //常量，标识缩略图左上角裁剪类型
define('THINKIMAGE_THUMB_SOUTHEAST', 5); //常量，标识缩略图右下角裁剪类型
define('THINKIMAGE_THUMB_FIXED', 6); //常量，标识缩略图固定尺寸缩放类型

/* 水印相关常量定义 */
define('THINKIMAGE_WATER_NORTHWEST', 1); //常量，标识左上角水印
define('THINKIMAGE_WATER_NORTH', 2); //常量，标识上居中水印
define('THINKIMAGE_WATER_NORTHEAST', 3); //常量，标识右上角水印
define('THINKIMAGE_WATER_WEST', 4); //常量，标识左居中水印
define('THINKIMAGE_WATER_CENTER', 5); //常量，标识居中水印
define('THINKIMAGE_WATER_EAST', 6); //常量，标识右居中水印
define('THINKIMAGE_WATER_SOUTHWEST', 7); //常量，标识左下角水印
define('THINKIMAGE_WATER_SOUTH', 8); //常量，标识下居中水印
define('THINKIMAGE_WATER_SOUTHEAST', 9); //常量，标识右下角水印

/**
 * 图片处理驱动类，可配置图片处理库
 * 目前支持GD库和imagick
 * @author 麦当苗儿 <zuojiazi.cn@gmail.com>
 */
class Image
{
    /**
     * 图片资源
     * @var resource
     */
    private static $im;

    /**
     * 初始化方法，用于实例化一个图片处理对象
     *
     * @param string $type 要使用的类库，默认使用GD库
     * @param null   $imgname
     *
     * @return resource
     */
    public static function init($type = 'Gd', $imgname = null)
    {
        /* 引入处理库，实例化图片处理对象 */
        $class    = '\\org\\image\\driver\\' . strtolower($type);
        self::$im = new $class($imgname);
        return self::$im;
    }

    // 调用驱动类的方法
    public static function __callStatic($method, $params)
    {
        self::$im || self::init();
        return call_user_func_array([self::$im, $method], $params);
    }
}
