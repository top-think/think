<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Haotong Lin <lofanmi@gmail.com>
// +----------------------------------------------------------------------

use think\Input;

class InputTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyStringName()
    {
        Input::$filter = 'trim';
        $input         = ['a' => 'test'];
        $this->assertEquals($input, Input::getData('', $input));
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
        $regexs = explode('|', strtolower($src));
        foreach ($regexs as $value) {
            $expected = $value . ' ';
            Input::filterExp($value);
            $this->assertEquals($expected, $value);
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
        $this->assertFalse(Input::getData('b', $input, $filters, false));
        $filters = 'validate_email';
        $this->assertFalse(Input::getData('b', $input, $filters, false));
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
        $filters  = 'trim,htmlspecialchars';
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
        Input::$filter = 'trim';
        $_GET['get']   = 'get value ';
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
}
