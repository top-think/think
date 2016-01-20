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
 * Cookie测试
 * @author    Haotong Lin <lofanmi@gmail.com>
 */

namespace tests\thinkphp\library\think;

use ReflectionClass;

class cookieTest extends \PHPUnit_Framework_TestCase
{
    protected $ref;

    protected $default = [
        // cookie 名称前缀
        'prefix'   => '',
        // cookie 保存时间
        'expire'   => 0,
        // cookie 保存路径
        'path'     => '/',
        // cookie 有效域名
        'domain'   => '',
        //  cookie 启用安全传输
        'secure'   => false,
        // httponly设置
        'httponly' => '',
    ];

    protected function setUp()
    {
        $reflectedClass          = new ReflectionClass('\think\Cookie');
        $reflectedPropertyConfig = $reflectedClass->getProperty('config');
        $reflectedPropertyConfig->setAccessible(true);
        $reflectedPropertyConfig->setValue($this->default);
        $this->ref = $reflectedPropertyConfig;
    }

    public function testInit()
    {
        $config = [
            // cookie 名称前缀
            'prefix'   => 'think_',
            // cookie 保存时间
            'expire'   => 0,
            // cookie 保存路径
            'path'     => '/path/to/test/',
            // cookie 有效域名
            'domain'   => '.thinkphp.cn',
            //  cookie 启用安全传输
            'secure'   => true,
            // httponly设置
            'httponly' => '1',
        ];
        \think\Cookie::init($config);

        $this->assertEquals(
            array_merge($this->default, array_change_key_case($config)),
            $this->ref->getValue()
        );
    }

    public function testPrefix()
    {
        $this->assertEquals($this->default['prefix'], \think\Cookie::prefix());

        $prefix = '_test_';
        $this->assertNotEquals($prefix, \think\Cookie::prefix());
        \think\Cookie::prefix($prefix);

        $config = $this->ref->getValue();
        $this->assertEquals($prefix, $config['prefix']);
    }

    public function testSet()
    {
        $value = 'value';

        $name = 'name1';
        \think\Cookie::set($name, $value, 10);
        $this->assertEquals($value, $_COOKIE[$this->default['prefix'] . $name]);

        $name = 'name2';
        \think\Cookie::set($name, $value, null);
        $this->assertEquals($value, $_COOKIE[$this->default['prefix'] . $name]);

        $name = 'name3';
        \think\Cookie::set($name, $value, 'expire=100&prefix=pre_');
        $this->assertEquals($value, $_COOKIE['pre_' . $name]);

        $name  = 'name4';
        $value = ['_test_中文_'];
        \think\Cookie::set($name, $value);
        $this->assertEquals('think:' . json_encode([urlencode('_test_中文_')]), $_COOKIE[$name]);
    }

    public function testGet()
    {
        $_COOKIE = [
            'a'       => 'b',
            'pre_abc' => 'c',
            'd'       => 'think:' . json_encode([urlencode('_test_中文_')]),
        ];
        $this->assertEquals('b', \think\Cookie::get('a'));
        $this->assertEquals(null, \think\Cookie::get('does_not_exist'));
        $this->assertEquals('c', \think\Cookie::get('abc', 'pre_'));
        $this->assertEquals(['_test_中文_'], \think\Cookie::get('d'));
    }

    public function testDelete()
    {
        $_COOKIE = [
            'a'       => 'b',
            'pre_abc' => 'c',
        ];
        $this->assertEquals('b', \think\Cookie::get('a'));
        \think\Cookie::delete('a');
        $this->assertEquals(null, \think\Cookie::get('a'));

        $this->assertEquals('c', \think\Cookie::get('abc', 'pre_'));
        \think\Cookie::delete('abc', 'pre_');
        $this->assertEquals(null, \think\Cookie::get('abc', 'pre_'));
    }

    public function testClear()
    {
        $_COOKIE = [];
        $this->assertEquals(null, \think\Cookie::clear());

        $_COOKIE = ['a' => 'b'];
        \think\Cookie::clear();
        $this->assertEquals(null, $_COOKIE);

        $_COOKIE = [
            'a'       => 'b',
            'pre_abc' => 'c',
        ];
        \think\Cookie::clear('pre_');
        $this->assertEquals(['a' => 'b'], $_COOKIE);
    }
}
