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
        Route::post('hello/:name', 'index/post');
        Route::put('hello/:name', 'index/put');
        Route::delete('hello/:name', 'index/delete');
        Route::any('user/:id', 'index/user');
        $this->assertEquals(['type' => 'module', 'module' => [null, 'index', 'hello']], Route::check('hello/thinkphp'));
        $this->assertEquals(['hello/:name' => ['route' => 'index/hello', 'option' => [], 'pattern' => []]], Route::getRules('GET'));
        Route::register('type/:name', 'index/type', 'PUT|POST');
    }

    public function testResource()
    {
        Route::resource('res', 'index/blog');
        Route::resource(['res' => ['index/blog']]);

        $this->assertEquals(['type' => 'module', 'module' => ['index', 'blog', 'index']], Route::check('res'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'blog', 'create']], Route::check('res/create'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'blog', 'read']], Route::check('res/8'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'blog', 'edit']], Route::check('res/8/edit'));

        Route::resource('blog.comment', 'index/comment');
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'comment', 'read']], Route::check('blog/8/comment/10'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'comment', 'edit']], Route::check('blog/8/comment/10/edit'));
    }

    public function testRest()
    {
        Route::rest('read', ['GET', '/:id', 'look']);
        Route::rest('create', ['GET', '/create', 'add']);
        Route::rest(['read' => ['GET', '/:id', 'look'], 'create' => ['GET', '/create', 'add']]);
        Route::resource('res', 'index/blog');

        $this->assertEquals(['type' => 'module', 'module' => ['index', 'blog', 'add']], Route::check('res/create'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'blog', 'look']], Route::check('res/8'));

    }

    public function testRouteMap()
    {
        Route::map('hello', 'index/hello');
        $this->assertEquals('index/hello', Route::map('hello'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'hello', null]], Route::check('hello'));
    }

    public function testParseUrl()
    {
        $this->assertEquals(['type' => 'module', 'module' => ['hello', null, null]], Route::parseUrl('hello'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'hello', null]], Route::parseUrl('index/hello'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'hello', null]], Route::parseUrl('index/hello?name=thinkphp'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'user', 'hello']], Route::parseUrl('index/user/hello'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'user', 'hello']], Route::parseUrl('index/user/hello/name/thinkphp'));
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'index', 'hello']], Route::parseUrl('index-index-hello', '-'));
    }

    public function testCheckRoute()
    {
        Route::get('hello/:name', 'index/hello');
        Route::get('blog/:id', 'blog/read', [], ['id' => '\d+']);

        $this->assertEquals(false, Route::check('test/thinkphp'));
        $this->assertEquals(false, Route::check('blog/thinkphp'));
        $this->assertEquals(['type' => 'module', 'module' => [null, 'blog', 'read']], Route::check('blog/5'));
        $this->assertEquals(['type' => 'module', 'module' => [null, 'index', 'hello']], Route::check('hello/thinkphp/abc/test'));
    }

    public function testCheckRouteGroup()
    {
        Route::pattern(['id' => '\d+', 'name' => '\w{6,25}']);
        Route::group('group', [':id' => 'index/hello', ':name' => 'index/say']);
        $this->assertEquals(false, Route::check('empty/think'));
        $this->assertEquals(['type' => 'module', 'module' => [null, 'index', 'say']], Route::check('group/think'));
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

    public function testBind()
    {
        Route::bind('module', 'index/blog');
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'blog', 'read']], Route::parseUrl('read/10'));

        Route::get('index/blog/:id', 'index/blog/read');
        $this->assertEquals(['type' => 'module', 'module' => ['index', 'blog', 'read']], Route::check('10'));

        Route::bind('namespace', '\app\index\controller');
        $this->assertEquals(['type' => 'method', 'method' => ['\app\index\controller\blog', 'read'], 'params' => []], Route::check('blog/read'));

        Route::bind('class', '\app\index\controller\blog');
        $this->assertEquals(['type' => 'method', 'method' => ['\app\index\controller\blog', 'read'], 'params' => []], Route::check('read'));
    }

    public function testSsl()
    {
        $this->assertEquals(false, Route::isSsl());
    }

    public function testDomain()
    {
        $_SERVER['HTTP_HOST']   = 'subdomain.thinkphp.cn';
        $_SERVER['REQUEST_URI'] = '';
        Route::domain('subdomain.thinkphp.cn', 'sub?abc=test&status=1');
        Route::checkDomain();
        $this->assertEquals('sub?abc=test&status=1', Route::domain('subdomain.thinkphp.cn'));
        $this->assertEquals('sub', Route::bind('module'));
        $this->assertEquals('test', $_GET['abc']);
        $this->assertEquals(1, $_GET['status']);

        Route::domain('subdomain.thinkphp.cn', function () {return ['type' => 'module', 'module' => 'sub2'];});
        Route::checkDomain();
        $this->assertEquals('sub2', Route::bind('module'));

        Route::domain('subdomain.thinkphp.cn', '\app\index\controller');
        Route::checkDomain();
        $this->assertEquals('\app\index\controller', Route::bind('namespace'));

        Route::domain('subdomain.thinkphp.cn', '@\app\index\controller\blog');
        Route::checkDomain();
        $this->assertEquals('\app\index\controller\blog', Route::bind('class'));

        Route::domain('subdomain.thinkphp.cn', '[sub3]');
        Route::checkDomain();
        $this->assertEquals('sub3', Route::bind('group'));
    }
}
