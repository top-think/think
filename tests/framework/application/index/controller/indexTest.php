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
 * @author    mahuan <mahuan@d1web.top>
 */

namespace index\controller;

use think\app;
use think\config;

class indexTest extends \PHPUnit_Framework_TestCase
{
    public function testIndex()
    {
        $this->assertContains('ThinkPHP5', App::run(Config::get()));
        Config::reset();
    }
}
