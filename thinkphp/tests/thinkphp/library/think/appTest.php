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
 * app类测试
 * @author    Haotong Lin <lofanmi@gmail.com>
 */

namespace tests\thinkphp\library\think;

use ReflectionClass;
use think\App;
use think\Config;

function func_trim($value)
{
    return trim($value);
}

function func_strpos($haystack, $needle)
{
    return strpos($haystack, $needle);
}

class AppInvokeMethodTestClass
{
    public static function staticRun($string)
    {
        return $string;
    }

    public function run($string)
    {
        return $string;
    }
}

class appTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        Config::set('root_namespace', ['/path/']);

        App::run();

        $expectOutputString = '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
        $this->expectOutputString($expectOutputString);

        $rc = new ReflectionClass('\think\Loader');
        $ns = $rc->getProperty('namespace');
        $ns->setAccessible(true);
        $this->assertEquals(true, in_array('/path/', $ns->getValue()));

        $this->assertEquals(true, function_exists('L'));
        $this->assertEquals(true, function_exists('C'));
        $this->assertEquals(true, function_exists('I'));

        $this->assertEquals(Config::get('default_timezone'), date_default_timezone_get());

    }

    // function调度
    public function testInvokeFunction()
    {
        $args1 = ['a b c '];
        $this->assertEquals(
            trim($args1[0]),
            App::invokeFunction('tests\thinkphp\library\think\func_trim', $args1)
        );

        $args2 = ['abcdefg', 'g'];
        $this->assertEquals(
            strpos($args2[0], $args2[1]),
            App::invokeFunction('tests\thinkphp\library\think\func_strpos', $args2)
        );
    }

    // 类method调度
    public function testInvokeMethod()
    {
        $_GET   = ['thinkphp'];
        $result = App::invokeMethod(['tests\thinkphp\library\think\AppInvokeMethodTestClass', 'run']);
        $this->assertEquals('thinkphp', $result);

        $_GET   = ['thinkphp'];
        $result = App::invokeMethod('tests\thinkphp\library\think\AppInvokeMethodTestClass::staticRun');
        $this->assertEquals('thinkphp', $result);
    }
}
