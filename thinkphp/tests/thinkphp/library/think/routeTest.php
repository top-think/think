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
 * Route测试
 * @author    liu21st <liu21st@gmail.com>
 */

namespace tests\thinkphp\library\think;

use think\Route;

class routeTest extends \PHPUnit_Framework_TestCase
{

    public function testRegister()
    {
        Route::get('hello/:name', 'index/hello');
        Route::get(['hello/:name' => 'index/hello']);
        $this->assertEquals(['type' => 'module', 'module' => [null, 'index', 'hello']], Route::check('hello/thinkphp'));
        $this->assertEquals(['hello/:name' => ['route' => 'index/hello', 'option' => [], 'pattern' => []]], Route::getRules('GET'));
    }

    public function testRouteMap()
    {
        Route::map('hello', 'index/hello');
        //$this->assertEquals('index/hello',Route::map('hello'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'hello', null]], Route::check('hello'));
    }

    public function testParseUrl()
    {
        $this->assertEquals(['type' => 'module', 'module' => ['hello', null, null]], Route::parseUrl('hello'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'hello', null]], Route::parseUrl('index/hello'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'user', 'hello']], Route::parseUrl('index/user/hello'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'index', 'hello']], Route::parseUrl('index-index-hello', '-'));
    }

    public function testCheckRoute()
    {
        Route::get('hello/:name', 'index/hello');
        Route::get('blog/:id', 'blog/read', [], ['id' => '\d+']);

        $this->assertEquals(false, Route::check('test/thinkphp'));
        $this->assertEquals(false, Route::check('blog/thinkphp'));
        $this->assertEquals(['type' => 'module', 'module' => [null, 'blog', 'read']], Route::check('blog/5'));
        $this->assertEquals(['type' => 'module', 'module' => [null, 'index', 'hello']], Route::check('hello/thinkphp'));
    }

    public function testCheckRouteGroup()
    {
        Route::pattern(['id' => '\d+', 'name' => '\w{6,25}']);
        Route::group('group', [':id' => 'index/hello', ':name' => 'index/say']);
        $this->assertEquals(false, Route::check('group/think'));
        $this->assertEquals(['type' => 'module', 'module' => [null, 'index', 'hello']], Route::check('group/10'));
        $this->assertEquals(['type' => 'module', 'module' => [null, 'index', 'say']], Route::check('group/thinkphp'));
    }

    public function testRouteToModule()
    {
        Route::get('hello/:name', 'index/hello');
        Route::get('blog/:id', 'blog/read', [], ['id' => '\d+']);
        $this->assertEquals(false, Route::check('test/thinkphp'));
        $this->assertEquals(false, Route::check('blog/thinkphp'));
        $this->assertEquals(['type' => 'module', 'module' => [null, 'index', 'hello']], Route::check('hello/thinkphp'));
        $this->assertEquals(['type' => 'module', 'module' => [null, 'blog', 'read']], Route::check('blog/5'));
    }

    public function testRouteToController()
    {
        Route::get('say/:name', '@app\index\controller\index\hello');
        $this->assertEquals(['type' => 'controller', 'controller' => 'app\index\controller\index\hello', 'params' => ['name' => 'thinkphp']], Route::check('say/thinkphp'));
    }

    public function testRouteToMethod()
    {
        Route::get('user/:name', '\app\index\service\User::get', [], ['name' => '\w+']);
        Route::get('info/:name', ['\app\index\model\Info', 'getInfo'], [], ['name' => '\w+']);
        $this->assertEquals(['type' => 'method', 'method' => '\app\index\service\User::get', 'params' => ['name' => 'thinkphp']], Route::check('user/thinkphp'));
        $this->assertEquals(['type' => 'method', 'method' => ['\app\index\model\Info', 'getInfo'], 'params' => ['name' => 'thinkphp']], Route::check('info/thinkphp'));
    }

    public function testRouteToRedirect()
    {
        Route::get('art/:id', '/article/read/id/:id', [], ['id' => '\d+']);
        $this->assertEquals(['type' => 'redirect', 'url' => '/article/read/id/8', 'status' => 301], Route::check('art/8'));
    }

}
