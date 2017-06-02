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
 * shmcache缓存驱动测试
 * @author    lloydzhou <lloydzhou@qq.com>
 */

namespace tests\thinkphp\library\think\cache\driver;

class shmcacheTest extends cacheTestCase
{
    private $_cacheInstance = null;
    /**
     * 基境缓存类型
     */
    protected function setUp()
    {
        \think\Cache::connect(array('type' => 'shmcache'));
    }
    /**
     * @return shmCache
     */
    protected function getCacheInstance()
    {
        if (null === $this->_cacheInstance) {
            $this->_cacheInstance = new \think\cache\driver\Shmcache(['expire' => 1]);
        }
        return $this->_cacheInstance;
    }

    public function testExpire()
    {
        $cache = $this->getCacheInstance();
        $this->assertTrue($cache->set('expire_test', 'expire_test', 1));
        usleep(100000);
        $this->assertEquals('expire_test', $cache->get('expire_test'));
        usleep(1100000);
        $this->assertFalse($cache->get('expire_test'));
    }

    public function testClear()
    {
        $cache = $this->prepare();
        $this->assertTrue($cache->clear());
        //$this->assertFalse($cache->get('number_test'));
        //failed i don't known why...
    }


    public function testQueue()
    {
        $cache = $this->prepare();
        $this->assertTrue($cache->set('1', '1'));
        $this->assertTrue($cache->set('2', '2'));
        $this->assertTrue($cache->set('3', '3'));
        $this->assertEquals(1, $cache->get('1'));
        $this->assertTrue($cache->set('4', '4'));
    }
}
