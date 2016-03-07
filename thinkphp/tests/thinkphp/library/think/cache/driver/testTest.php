<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * Test缓存驱动测试
 * @author    刘志淳 <chun@engineer.com>
 */

namespace tests\thinkphp\library\think\cache\driver;

use think\Cache;

class testTest extends \PHPUnit_Framework_TestCase
{
    /**
     * 测试缓存读取
     * @return  mixed
     * @access public
     */
    public function testGet()
    {
        $cache = Cache::connect(['type' => 'Test']);

        $this->assertFalse($cache->get('test'));
    }

    /**
     * 测试缓存设置
     * @return  mixed
     * @access public
     */
    public function testSet()
    {
        $cache = Cache::connect(['type' => 'Test']);

        $this->assertTrue($cache->set('test', 'test'));
    }

    /**
     * 删除缓存测试
     * @return  mixed
     * @access public
     */
    public function testRm()
    {
        $cache = Cache::connect(['type' => 'Test']);

        $this->assertTrue($cache->rm('test'));
    }

    /**
     * 清空缓存测试
     * @return  mixed
     * @access public
     */
    public function testClear()
    {
        $cache = Cache::connect(['type' => 'Test']);

        $this->assertTrue($cache->clear());
    }
}
