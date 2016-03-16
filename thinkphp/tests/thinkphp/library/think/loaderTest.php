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
 * Loader测试
 * @author    liu21st <liu21st@gmail.com>
 */

namespace tests\thinkphp\library\think;

use think\Loader;

class loaderTest extends \PHPUnit_Framework_TestCase
{

    public function testAutoload()
    {
        $this->assertEquals(true, Loader::autoload('think\Session'));
        $this->assertEquals(false, Loader::autoload('think\COOKIE'));
        $this->assertEquals(false, Loader::autoload('\think\Url'));
        $this->assertEquals(false, Loader::autoload('think\Test'));
        $this->assertEquals(false, Loader::autoload('my\HelloTest'));
    }

    public function testAddMap()
    {
        Loader::addMap('my\hello\Test', 'Test.php');
        $this->assertEquals(false, Loader::autoload('my\hello\Test'));
    }

    public function testAddNamespace()
    {
        Loader::addNamespace('top', __DIR__ . DS . 'loader' . DS);
        $this->assertEquals(true, Loader::autoload('top\test\Hello'));
    }

    public function testImport()
    {
        $this->assertEquals(true, Loader::import('think.log.driver.Sae'));
        $this->assertEquals(false, Loader::import('think.log.driver.MyTest'));
    }

    public function testParseName()
    {
        $this->assertEquals('HelloTest', Loader::parseName('hello_test', 1));
        $this->assertEquals('hello_test', Loader::parseName('HelloTest', 0));
    }

    public function testParseClass()
    {
        $this->assertEquals('app\index\controller\User', Loader::parseClass('index', 'controller', 'user'));
        $this->assertEquals('app\index\controller\user\Type', Loader::parseClass('index', 'controller', 'user.type'));
        $this->assertEquals('app\admin\model\UserType', Loader::parseClass('admin', 'model', 'user_type'));
    }
}
