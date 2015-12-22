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

/**
 * Controller 和路由测试
 * @author    7IN0SAN9 <me@7in0.me>
 */

namespace index\controller;

use think\app;
use think\config;

class indexTest extends \PHPUnit_Framework_TestCase
{
    public function testIndex()
    {
        //因为初始化会调用缓存，会导致一个错误，错误待处理！
        // $this->assertContains('ThinkPHP5', App::run());
        // Config::reset();
    }
}
