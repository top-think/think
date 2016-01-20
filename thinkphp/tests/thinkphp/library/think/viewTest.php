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
 * view测试
 * @author    mahuan <mahuan@d1web.top>
 */

namespace tests\thinkphp\library\think;

class viewTest extends \PHPUnit_Framework_TestCase
{

    /**
     * 句柄测试
     * @return  mixed
     * @access public
     */
    public function testGetInstance()
    {
        \think\Cookie::get('a');
        $view_instance = \think\View::instance();
        $this->assertInstanceOf('\think\view', $view_instance, 'instance方法返回错误');
    }

    /**
     * 测试变量赋值
     * @return  mixed
     * @access public
     */
    public function testAssign()
    {
        $view_instance = \think\View::instance();
        $data          = $view_instance->assign(array('key' => 'value'));
        $data          = $view_instance->assign('key2', 'value2');
        //测试私有属性
        $expect_data = array('key' => 'value', 'key2' => 'value2');
        $this->assertAttributeEquals($expect_data, 'data', $view_instance);
    }

    /**
     *  测试配置
     * @return  mixed
     * @access public
     */
    public function testConfig()
    {
        $view_instance = \think\View::instance();
        $data          = $view_instance->config('key2', 'value2');
        $data          = $view_instance->config('key3', 'value3');
        $data          = $view_instance->config('key3', 'value_cover');
        //不应包含value
        $data = $view_instance->config(array('key' => 'value'));
        //基础配置替换
        $data = $view_instance->config(array('view_path' => 'view_path'));
        //目标结果
        $this->assertAttributeContains('value2', "config", $view_instance);
        $this->assertAttributeContains('value_cover', "config", $view_instance);
        $this->assertAttributeNotContains('value', "config", $view_instance);
        $this->assertAttributeContains('view_path', "config", $view_instance);
    }

    /**
     *  测试引擎设置
     * @return  mixed
     * @access public
     */
    public function testEngine()
    {
        $view_instance = \think\View::instance();
        $data          = $view_instance->engine('php');
        $this->assertAttributeEquals('php', 'engine', $view_instance);
        //测试模板引擎驱动
        $data         = $view_instance->engine('think');
        $think_engine = new \think\view\driver\Think;
        $this->assertAttributeEquals($think_engine, 'engine', $view_instance);
    }

    /**
     *  测试引擎设置
     * @return  mixed
     * @access public
     */
    public function testTheme()
    {
        $view_instance = \think\View::instance();
        $data          = $view_instance->theme(true);
        //反射类取出私有属性的值
        $reflection = new \ReflectionClass('\think\View');
        $property   = $reflection->getProperty('config');
        $property->setAccessible(true);
        $config_value = $property->getValue($view_instance);

        $this->assertTrue($config_value['theme_on']);
        $this->assertTrue($config_value['auto_detect_theme']);

        //关闭主题测试
        $data         = $view_instance->theme(false);
        $config_value = $property->getValue($view_instance);
        $this->assertFalse($config_value['theme_on']);

        //指定主题测试
        $data         = $view_instance->theme('theme_name');
        $config_value = $property->getValue($view_instance);
        $this->assertTrue($config_value['theme_on']);
        $this->assertAttributeEquals('theme_name', 'theme', $view_instance);
    }

    /**
     *  测试引擎设置
     * @return  mixed
     * @access public
     */
    public function testParseTemplate()
    {
        $view_instance = \think\View::instance();
        $method        = new \ReflectionMethod('\think\View', 'ParseTemplate');
        $method->setAccessible(true);
        $this->assertEquals('/theme_name/index/template_name.html', $method->invoke($view_instance, 'template_name'));
    }

    /**
     *  测试引擎设置
     * @return  mixed
     * @access public
     */
    public function testGetThemePath()
    {
        $view_instance = \think\View::instance();
        $method        = new \ReflectionMethod('\think\View', 'getThemePath');
        $method->setAccessible(true);
        $this->assertEquals('/theme_name/', $method->invoke($view_instance));
    }
}
