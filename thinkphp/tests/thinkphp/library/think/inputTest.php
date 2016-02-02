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
 * Input测试
 * @author    Haotong Lin <lofanmi@gmail.com>
 */

namespace tests\thinkphp\library\think;

use think\Input;

class inputTest extends \PHPUnit_Framework_TestCase
{

    public function testInputName()
    {
        $input = ['a' => 'a', 'b' => ['c' => [' one ', 'two']]];
        $this->assertEquals($input, Input::data($input));
        $this->assertEquals($input['a'], Input::data($input, 'a'));
        $this->assertEquals('one', Input::data($input, 'b.c.0/s', 'default', 'trim'));
    }

    public function testDefaultValue()
    {
        $input   = ['a' => 'test'];
        $default = 'default';
        $this->assertEquals($default, Input::data($input, 'b', $default));
        $this->assertEquals($default, Input::get('a', $default));
    }

    public function testStringFilter()
    {
        $input   = ['a' => ' test ', 'b' => ' test<> '];
        $filters = 'trim';
        $this->assertEquals('test', Input::data($input, 'a', '', $filters));
        $filters = 'trim,htmlspecialchars';
        $this->assertEquals('test&lt;&gt;', Input::data($input, 'b', '', $filters));
    }

    public function testArrayFilter()
    {
        $input   = ['a' => ' test ', 'b' => ' test<> '];
        $filters = ['trim'];
        $this->assertEquals('test', Input::data($input, 'a', '', $filters));
        $filters = ['trim', 'htmlspecialchars'];
        $this->assertEquals('test&lt;&gt;', Input::data($input, 'b', '', $filters));
    }

    public function testFilterExp()
    {
        $src    = 'EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN';
        $regexs = explode('|', $src);
        $data   = Input::data($regexs);
        foreach ($regexs as $key => $value) {
            $expected = $value . ' ';
            $this->assertEquals($expected, $data[$key]);
        }
    }

    public function testFiltrateWithRegex()
    {
        $input   = ['a' => 'test1', 'b' => '_test2', 'c' => ''];
        $filters = '/^test/';
        $this->assertEquals('test1', Input::data($input, 'a', '', $filters));
        $default = 'default value';
        $this->assertEquals($default, Input::data($input, 'b', $default, $filters));
        $filters = '/.+/';
        $this->assertEquals('default value', Input::data($input, 'c', $default, $filters));
    }

    public function testFiltrateWithFilterVar()
    {
        $email   = 'abc@gmail.com';
        $error   = 'not email';
        $default = false;
        $input   = ['a' => $email, 'b' => $error];
        $filters = FILTER_VALIDATE_EMAIL;
        $this->assertEquals($email, Input::data($input, 'a', '', $filters));
        $this->assertFalse(Input::data($input, 'b', $default, $filters));
        $filters = 'validate_email';
        $this->assertFalse(Input::data($input, 'b', $default, $filters));
    }

    public function testAllInput()
    {
        $input = [
            'a' => ' trim ',
            'b' => 'htmlspecialchars<>',
            'c' => ' trim htmlspecialchars<> ',
            'd' => 'eXp',
            'e' => 'NEQ',
            'f' => 'gt',
        ];
        $filters  = 'htmlspecialchars,trim';
        $excepted = [
            'a' => 'trim',
            'b' => 'htmlspecialchars&lt;&gt;',
            'c' => 'trim htmlspecialchars&lt;&gt;',
            'd' => 'eXp ',
            'e' => 'NEQ ',
            'f' => 'gt ',
        ];
        $this->assertEquals($excepted, Input::data($input, '', '', $filters));
    }

    public function testTypeCast()
    {
        $_POST = [
            'a' => [1, 2, 3],
            'b' => '1000',
            'c' => '3.14',
            'd' => 'test boolean',
        ];
        $this->assertEquals([1, 2, 3], Input::post('a/a'));
        $this->assertEquals(1000, Input::post('b/d'));
        $this->assertEquals(3.14, Input::post('c/f'));
        $this->assertEquals(true, Input::post('d/b'));
    }

    public function testHasValue()
    {
        $_GET['name']           = 'value';
        $_GET['config']['name'] = 'value';
        $this->assertEquals(true, Input::get('?name'));
        $this->assertEquals(false, Input::get('?id'));
        $this->assertEquals(true, Input::get('?config.name'));
        $this->assertEquals(false, Input::get('?config.id'));
    }

    public function testSuperglobals()
    {
        Input::setFilter('trim');
        $_GET['get'] = 'get value ';
        $this->assertEquals('get value', Input::get('get'));
        $_POST['post'] = 'post value ';
        $this->assertEquals('post value', Input::post('post'));

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('post value', Input::param('post'));
        $this->assertEquals(null, Input::param('get'));
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertEquals('get value', Input::param('get'));
        $this->assertEquals(null, Input::param('post'));
        $this->assertEquals(null, Input::param('put'));
        $_REQUEST = array_merge($_GET, $_POST);
        $this->assertEquals('get value', Input::request('get'));

        //session_start();
        $_SESSION['test'] = 'session value ';
        $this->assertEquals('session value', Input::session('test'));
        //session_destroy();

        $_COOKIE['cookie'] = 'cookie value ';
        $this->assertEquals('cookie value', Input::cookie('cookie'));

        $_SERVER['REQUEST_METHOD'] = 'GET ';
        $this->assertEquals('GET', Input::server('REQUEST_METHOD'));

        $GLOBALS['total'] = 1000;
        $this->assertEquals(1000, Input::globals('total'));

        $this->assertEquals('testing', Input::env('APP_ENV'));

        //$_SERVER['PATH_INFO'] = 'path/info';
        //$path = $_SERVER['PATH_INFO'] ? explode('/', $_SERVER['PATH_INFO'])[0] : '';
        //$this->assertEquals($path, Input::path('0', ''));

        $_FILES = ['file' => ['name' => 'test.png', 'type' => 'image/png', 'tmp_name' => '/tmp/php5Wx0aJ', 'error' => 0, 'size' => 15726]];
        $this->assertEquals('image/png', Input::file('file.type'));

    }

    public function testFilterMerge()
    {
        Input::setFilter('htmlspecialchars');
        $input = ['a' => ' test<> ', 'b' => '<b\\ar />'];
        $this->assertEquals(' test<> ', Input::data($input, 'a', '', ''));
        $filters = ['trim'];
        $this->assertEquals('test<>', Input::data($input, 'a', '', $filters));
        $this->assertEquals('test&lt;&gt;', Input::data($input, 'a', '', $filters, true));
        $filters = 'stripslashes';
        $this->assertEquals("&lt;bar /&gt;", Input::data($input, 'b', '', $filters, true));
    }

}
