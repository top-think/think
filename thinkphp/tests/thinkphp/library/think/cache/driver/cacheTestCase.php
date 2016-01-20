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
 * 缓存抽象类，提供一些测试
 * @author simon <mahuan@d1web.top>
 */

namespace tests\thinkphp\library\think\cache\driver;

use think\cache;

abstract class cacheTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * 获取缓存句柄，子类必须有
     * @access protected
     */
    abstract protected function getCacheInstance();
    /**
     * tearDown函数
     */
    protected function tearDown()
    {
    }
    /**
     * 设定一组测试值，包括测试字符串、整数、数组和对象
     * @return  mixed
     * @access public
     */
    public function prepare()
    {
        $cache = $this->getCacheInstance();
        $cache->clear();
        $cache->set('string_test', 'string_test');
        $cache->set('number_test', 11);
        $cache->set('array_test', ['array_test' => 'array_test']);
        return $cache;
    }
    /**
     * 测试缓存设置，包括测试字符串、整数、数组和对象
     * @return  mixed
     * @access public
     */
    public function testSet()
    {
        $cache = $this->getCacheInstance();
        $this->assertTrue($cache->set('string_test', 'string_test'));
        $this->assertTrue($cache->set('number_test', 11));
        $this->assertTrue($cache->set('array_test', ['array_test' => 'array_test']));
    }
    /**
     * 测试缓存读取，包括测试字符串、整数、数组和对象
     * @return  mixed
     * @access public
     */
    public function testGet()
    {
        $cache = $this->prepare();
        $this->assertEquals('string_test', $cache->get('string_test'));
        $this->assertEquals(11, $cache->get('number_test'));
        $array = $cache->get('array_test');
        $this->assertArrayHasKey('array_test', $array);
        $this->assertEquals('array_test', $array['array_test']);
    }
    /**
     * 测试缓存存在情况，包括测试字符串、整数、数组和对象
     * @return  mixed
     * @access public
     */
    public function testExists()
    {
        $cache = $this->prepare();
        $this->assertNotEmpty($cache->get('string_test'));
        $this->assertNotEmpty($cache->get('number_test'));
        $this->assertFalse($cache->get('not_exists'));
    }
    /**
     * 测试缓存不存在情况，包括测试字符串、整数、数组和对象
     * @return  mixed
     * @access public
     */
    public function testGetNonExistent()
    {
        $cache = $this->getCacheInstance();
        $this->assertFalse($cache->get('non_existent_key'));
    }
    /**
     * 测试特殊值缓存，包括测试字符串、整数、数组和对象
     * @return  mixed
     * @access public
     */
    public function testStoreSpecialValues()
    {
        $cache = $this->getCacheInstance();
        $cache->set('null_value', null);
        //清空缓存后，返回null而不是false
        $this->assertTrue(is_null($cache->get('null_value')));
    }
    /**
     * 缓存过期测试
     * @return  mixed
     * @access public
     */
    public function testExpire()
    {
        $cache = $this->getCacheInstance();
        $this->assertTrue($cache->set('expire_test', 'expire_test', 2));
        usleep(500000);
        $this->assertEquals('expire_test', $cache->get('expire_test'));
        usleep(2500000);
        $this->assertFalse($cache->get('expire_test'));
    }
    /**
     * 删除缓存测试
     * @return  mixed
     * @access public
     */
    public function testDelete()
    {
        $cache = $this->prepare();
        $this->assertNotNull($cache->rm('number_test'));
        $this->assertFalse($cache->get('number_test'));
    }
    /**
     * 清空缓存测试
     * @return  mixed
     * @access public
     */
    public function testClear()
    {
        $cache = $this->prepare();
        $this->assertTrue($cache->clear());
        $this->assertFalse($cache->get('number_test'));
    }
}
