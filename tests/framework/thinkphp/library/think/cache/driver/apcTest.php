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
 * Apc缓存驱动测试
 * @author    mahuan <mahuan@d1web.top>
 */

namespace think\cache\driver;

use think\app;
use think\cache;
use think\config;

class apcTest extends \PHPUnit_Framework_TestCase
{
    //设定基境
    public function setUp()
    {
        //验证模块是否加载
        if (!extension_loaded('apc')) {
            $this->markTestSkipped('apc扩展不可用！');
        };
    }
    /**
     * 测试操作缓存
     */
    public function testApc()
    {
        App::run(Config::get());
        $this->assertInstanceOf(
            '\think\cache\driver\Apc',
            Cache::connect(['type' => 'apc', 'expire' => 1])
        );
        $this->assertTrue(Cache::set('key', 'value'));
        $this->assertEquals('value', Cache::get('key'));
        $this->assertTrue(Cache::rm('key'));
        $this->assertFalse(Cache::get('key'));
        $this->assertTrue(Cache::clear('key'));
        Config::reset();
    }
}
