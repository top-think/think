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
        Config::parse('isTrue=1', 'test');
        Config::range('test');
        $this->assertTrue(Config::has('isTrue'));
        $this->assertEquals(1, Config::get('isTrue'));
        Config::set('isTrue', false);
        $this->assertEquals(0, Config::get('isTrue'));
        Config::reset();
    }
}
