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
     * 测试读取缓存
     */
    public function testGet()
    {
        \think\Cache::connect(['type' => 'apc', 'expire' => 1]);
        $this->assertTrue(\think\Cache::set('key', 'value'));
        $this->assertEquals('value', \think\Cache::get('key'));
        $this->assertTrue(\think\Cache::rm('key'));
        $this->assertFalse(\think\Cache::get('key'));
        $this->assertTrue(\think\Cache::clear('key'));
        Config::reset();
    }
}
