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
 * 控制器测试
 * @author    Haotong Lin <lofanmi@gmail.com>
 */

namespace tests\thinkphp\library\think;

use ReflectionClass;
use think\Controller;
use think\View;

require_once CORE_PATH . '../../helper.php';

class Foo extends Controller
{
    public $test = 'test';

    public function _initialize()
    {
        $this->test = 'abcd';
    }
}

class Bar extends Controller
{
    public $test = 1;

    public $beforeActionList = ['action1', 'action2'];

    public function action1()
    {
        $this->test += 2;
        return 'action1';
    }

    public function action2()
    {
        $this->test += 4;
        return 'action2';
    }
}

class Baz extends Controller
{
    public $test = 1;

    public $beforeActionList = [
        'action1' => ['only' => 'index'],
        'action2' => ['except' => 'index'],
        'action3' => ['only' => 'abcd'],
        'action4' => ['except' => 'abcd'],
    ];

    public function action1()
    {
        $this->test += 2;
        return 'action1';
    }

    public function action2()
    {
        $this->test += 4;
        return 'action2';
    }

    public function action3()
    {
        $this->test += 8;
        return 'action2';
    }

    public function action4()
    {
        $this->test += 16;
        return 'action2';
    }
}

define('ACTION_NAME', 'index');

class controllerTest extends \PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $foo = new Foo;
        $this->assertEquals('abcd', $foo->test);
    }

    public function testBeforeAction()
    {
        $obj = new Bar;
        $this->assertEquals(7, $obj->test);

        $obj = new Baz;
        $this->assertEquals(19, $obj->test);
    }

    private function getView($controller)
    {
        $view     = new View();
        $rc       = new ReflectionClass(get_class($controller));
        $property = $rc->getProperty('view');
        $property->setAccessible(true);
        $property->setValue($controller, $view);
        return $view;
    }

    public function testFetch()
    {
        $controller      = new Foo;
        $view            = $this->getView($controller);
        $template        = dirname(__FILE__) . '/display.html';
        $viewFetch       = $view->fetch($template, ['name' => 'ThinkPHP']);
        $controllerFetch = $controller->fetch($template, ['name' => 'ThinkPHP']);
        $this->assertEquals($controllerFetch, $viewFetch);
    }

    public function testShow()
    {
        $controller      = new Foo;
        $view            = $this->getView($controller);
        $template        = dirname(__FILE__) . '/display.html';
        $viewFetch       = $view->show($template, ['name' => 'ThinkPHP']);
        $controllerFetch = $controller->show($template, ['name' => 'ThinkPHP']);
        $this->assertEquals($controllerFetch, $viewFetch);
    }

    public function testAssign()
    {
        $controller = new Foo;
        $view       = $this->getView($controller);
        $controller->assign('abcd', 'dcba');
        $controller->assign(['key1' => 'value1', 'key2' => 'value2']);
        $expect = ['abcd' => 'dcba', 'key1' => 'value1', 'key2' => 'value2'];
        $this->assertAttributeEquals($expect, 'data', $view);
    }

    public function testEngine()
    {
        $controller   = new Foo;
        $view         = $this->getView($controller);
        $view->engine = null;
        $this->assertEquals(null, $view->engine);
        $controller->engine('php');
        $this->assertEquals('php', $view->engine);
    }
}
