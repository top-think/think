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
 * Config类测试
 * @author    7IN0SAN9 <me@7in0.me>
 */

namespace think;

use think\app;
use think\config;

class configTest extends \PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        App::run(Config::get());
        $this->assertTrue(Config::has('url_route_on'));
        $this->assertEquals(1, Config::get('url_route_on'));
        Config::set('url_route_on', false);
        $this->assertEquals(0, Config::get('url_route_on'));
        Config::range('test');
        $this->assertFalse(Config::has('url_route_on'));
        Config::reset();
    }
}
