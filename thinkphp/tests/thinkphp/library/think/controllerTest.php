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

require_once CORE_PATH . '../../helper.php';

class Foo extends \think\Controller
{
    public $test = 'test';

    public function _initialize()
    {
        $this->test = 'abcd';
    }
}

class Bar extends \think\Controller
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

class Baz extends \think\Controller
{
    public $test = 1;

    public $beforeActionList = [
        'action1' => ['only' => ['index']],
        'action2' => ['except' => ['index']],
        'action3' => ['only' => ['abcd']],
        'action4' => ['except' => ['abcd']],
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
}
