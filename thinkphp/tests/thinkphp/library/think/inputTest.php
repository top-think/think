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
    public function testEmptyStringName()
    {
        $input = ['a' => 'test'];
        $this->assertEquals($input, Input::getData('', $input, 'trim'));
    }

    public function testInputName()
    {
        $input = ['a' => 'test'];
        $this->assertEquals($input['a'], Input::getData('a', $input));
    }

    public function testDefaultValue()
    {
        $input   = ['a' => 'test'];
        $default = 'default';
        $this->assertEquals($default, Input::getData('foo', $input, null, $default));
    }

    public function testStringFilter()
    {
        $input   = ['a' => ' test ', 'b' => ' test<> '];
        $filters = 'trim';
        $this->assertEquals('test', Input::getData('a', $input, $filters));
        $filters = 'trim,htmlspecialchars';
        $this->assertEquals('test&lt;&gt;', Input::getData('b', $input, $filters));
    }

    public function testArrayFilter()
    {
        $input   = ['a' => ' test ', 'b' => ' test<> '];
        $filters = ['trim'];
        $this->assertEquals('test', Input::getData('a', $input, $filters));
        $filters = ['trim', 'htmlspecialchars'];
        $this->assertEquals('test&lt;&gt;', Input::getData('b', $input, $filters));
    }

    public function testFilterExp()
    {
        $src    = 'EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN';
        $regexs = explode('|', $src);
        $data   = Input::getData('', $regexs);
        foreach ($regexs as $key => $value) {
            $expected = $value . ' ';
            $this->assertEquals($expected, $data[$key]);
        }
    }

    public function testFiltrateWithRegex()
    {
        $input   = ['a' => 'test1', 'b' => '_test2'];
        $filters = '/^test/';
        $this->assertEquals('test1', Input::getData('a', $input, $filters));
        $default = 'default value';
        $this->assertEquals($default, Input::getData('b', $input, $filters, $default));
    }

    public function testFiltrateWithFilterVar()
    {
        $email   = 'abc@gmail.com';
        $error   = 'not email';
        $default = false;
        $input   = ['a' => $email, 'b' => $error];
        $filters = FILTER_VALIDATE_EMAIL;
        $this->assertEquals($email, Input::getData('a', $input, $filters));
        $this->assertFalse(Input::getData('b', $input, $filters, $default));
        $filters = 'validate_email';
        $this->assertFalse(Input::getData('b', $input, $filters, $default));
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
        $this->assertEquals($excepted, Input::getData('', $input, $filters));
    }

    public function testTypeCast()
    {
        $input = [
            'a' => [1, 2, 3],
            'b' => '1000',
            'c' => '3.14',
            'd' => 'test boolean',
        ];
        $this->assertEquals([1, 2, 3], Input::getData('a/a', $input));
        $this->assertEquals(1000, Input::getData('b/d', $input));
        $this->assertEquals(3.14, Input::getData('c/f', $input));
        $this->assertEquals(true, Input::getData('d/b', $input));
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

        session_start();
        $_SESSION['test'] = 'session value ';
        $this->assertEquals('session value', Input::session('test'));
        session_destroy();

        $_COOKIE['cookie'] = 'cookie value ';
        $this->assertEquals('cookie value', Input::cookie('cookie'));

        $_SERVER['REQUEST_METHOD'] = 'GET ';
        $this->assertEquals('GET', Input::server('REQUEST_METHOD'));

        $this->assertEquals('testing', Input::env('APP_ENV'));
    }

    public function testFilterCover()
    {
        Input::setFilter('htmlspecialchars');
        $input   = ['a' => ' test<> ', 'b' => '<b\\ar />'];
        $filters = ['trim'];
        $this->assertEquals('test&lt;&gt;', Input::getData('a', $input, $filters));
        $filters = ['trim', false];
        $this->assertEquals('test<>', Input::getData('a', $input, $filters));
        $filters = 'stripslashes';
        $this->assertEquals("&lt;bar /&gt;", Input::getData('b', $input, $filters));
        $filters = 'stripslashes,0';
        $this->assertEquals("<bar />", Input::getData('b', $input, $filters));
    }

}
