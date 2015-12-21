<?php

namespace tests\framework\thinkphp\library\think\cache\driver;

use think\app;
use think\cache;

    /**
     * 缓存首相类，提供一些测试
     * @author simon <mahuan@d1web.top>
     */
abstract class CacheTestCase extends \PHPUnit_Framework_TestCase
{
    
    /**
     * 获取缓存句柄，子类必须有
     * @access protected
     */
    abstract protected function getCacheInstance();

    /**
     * 基境设定
     */
    protected function setUp()
    {
    }

    /**
     * tearDown函数
     */
    protected function tearDown()
    {
    }

    /**
     * 设定一组测试值，包括测试字符串、整数、浮点数、数组和对象
     * @return  mixed 
     * @access public
     */
    public function prepare()
    {
        $cache = $this->getCacheInstance();

        $cache->clear();
        S('string_test', 'string_test');
        S('number_test', 11);
        S('array_test', ['array_test' => 'array_test']);

        return $cache;
    }

    /**
     * 测试缓存设置，包括测试字符串、整数、浮点数、数组和对象
     * @return  mixed 
     * @access public
     */
    public function testSet()
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue(S('string_test', 'string_test'));
        $this->assertTrue(S('number_test', 11));
        $this->assertTrue(S('float_test', 11.11));
        $this->assertTrue(S('array_test', ['array_test' => 'array_test']));
    }

    /**
     * 测试缓存读取，包括测试字符串、整数、浮点数、数组和对象
     * @return  mixed 
     * @access public
     */
    public function testGet()
    {
        $cache = $this->prepare();

        $this->assertEquals('string_test', S('string_test'));

        $this->assertEquals(11, S('number_test'));

        $this->assertEquals(11.11, S('float_test'));

        $array = S('array_test');
        $this->assertArrayHasKey('array_test', $array);
        $this->assertEquals('array_test', $array['array_test']);
    }

    /**
     * 测试缓存存在情况，包括测试字符串、整数、浮点数、数组和对象
     * @return  mixed 
     * @access public
     */
    public function testExists()
    {
        $cache = $this->prepare();

        $this->assertTrue($cache->exists('string_test'));
        // check whether exists affects the value
        $this->assertEquals('string_test', S('string_test'));

        $this->assertTrue($cache->exists('number_test'));
        $this->assertTrue($cache->exists('float_test'));
        $this->assertFalse($cache->exists('not_exists'));
    }

    /**
     * 测试缓存不存在情况，包括测试字符串、整数、浮点数、数组和对象
     * @return  mixed 
     * @access public
     */
    public function testGetNonExistent()
    {
        $cache = $this->getCacheInstance();

        $this->assertFalse(S('non_existent_key'));
    }

    /**
     * 测试特殊值缓存，包括测试字符串、整数、浮点数、数组和对象
     * @return  mixed 
     * @access public
     */
    public function testStoreSpecialValues()
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue(S('null_value', null));
        $this->assertNull(S('null_value'));

        $this->assertTrue(S('bool_value', true));
        $this->assertTrue(S('bool_value'));
    }

    /**
     * 缓存过期测试
     * @return  mixed 
     * @access public
     */
    public function testExpire()
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue(S('expire_test', 'expire_test', 2));
        usleep(500000);
        $this->assertEquals('expire_test', S('expire_test'));
        usleep(2500000);
        $this->assertFalse(S('expire_test'));
    }

    /**
     * 删除缓存测试
     * @return  mixed 
     * @access public
     */
    public function testDelete()
    {
        $cache = $this->prepare();

        $this->assertNotNull(S('number_test'));
        $this->assertTrue(S('number_test',null));
        $this->assertFalse(S('number_test'));
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
        $this->assertFalse(S('number_test'));
    }
}
