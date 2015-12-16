<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: dongww <dongww@gmail.com>
// +----------------------------------------------------------------------

namespace think\config\driver;

/**
 * 配置文件解析驱动接口
 *
 * @package think\config\driver
 */
interface DriverInterface
{
    /**
     * 解析配置文件或内容，并以字符串形式返回
     *
     * @param string $config 配置文件路径或字符串
     *
     * @return array 以数组形式返回配置
     */
    public function parse($config);
}
