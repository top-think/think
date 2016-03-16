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
 * Response测试
 * @author    大漠 <zhylninc@gmail.com>
 */

namespace tests\thinkphp\library\think;

use think\Config;
use think\Response;

class responseTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \think\Response
     */
    protected $object;

    protected $default_return_type;

    protected $default_ajax_return;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        // 1.
        // restore_error_handler();
        // Warning: Cannot modify header information - headers already sent by (output started at PHPUnit\Util\Printer.php:173)
        // more see in https://www.analysisandsolutions.com/blog/html/writing-phpunit-tests-for-wordpress-plugins-wp-redirect-and-continuing-after-php-errors.htm

        // 2.
        // the Symfony used the HeaderMock.php

        // 3.
        // not run the eclipse will held, and travis-ci.org Searching for coverage reports
        // **> Python coverage not found
        // **> No coverage report found.
        // add the
        // /**
        // * @runInSeparateProcess
        // */
        if (!$this->default_return_type) {
            $this->default_return_type = Config::get('default_return_type');
        }
        if (!$this->default_ajax_return) {
            $this->default_ajax_return = Config::get('default_ajax_return');
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        Config::set('default_ajax_return', $this->default_ajax_return);
        Config::set('default_return_type', $this->default_return_type);
        Response::type(Config::get('default_return_type')); // 会影响其他测试
    }

    /**
     * @covers think\Response::send
     * @todo Implement testSend().
     */
    public function testSend()
    {
        $dataArr        = array();
        $dataArr["key"] = "value";
        //$dataArr->key   = "val";

        $result = Response::send($dataArr, "", true);
        $this->assertArrayHasKey("key", $result);

        $result = Response::send($dataArr, "json", true);
        $this->assertEquals('{"key":"value"}', $result);

        $handler                                = "callback";
        $_GET[Config::get('var_jsonp_handler')] = $handler;
        $result                                 = Response::send($dataArr, "jsonp", true);
        $this->assertEquals('callback({"key":"value"});', $result);

        Response::tramsform(function () {

            return "callbackreturndata";
        });

        $result = Response::send($dataArr, "", true);
        $this->assertEquals("callbackreturndata", $result);
        $_GET[Config::get('var_jsonp_handler')] = "";
    }

    /**
     * @covers think\Response::tramsform
     * @todo Implement testTramsform().
     */
    public function testTramsform()
    {
        Response::tramsform(function () {

            return "callbackreturndata";
        });
        $dataArr = [];
        $result  = Response::send($dataArr, "", true);
        $this->assertEquals("callbackreturndata", $result);

        Response::tramsform(null);
    }

    /**
     * @covers think\Response::type
     * @todo Implement testType().
     */
    public function testType()
    {
        $type = "json";
        Response::type($type);

        $result = Response::type();
        $this->assertEquals($type, $result);
        Response::type($type);
    }

    /**
     * @covers think\Response::data
     * @todo Implement testData().
     */
    public function testData()
    {
        $data = "data";
        Response::data($data);
        Response::data(null);
    }

    /**
     * @covers think\Response::isExit
     * @todo Implement testIsExit().
     */
    public function testIsExit()
    {
        $isExit = true;
        Response::isExit($isExit);

        $result = Response::isExit();
        $this->assertTrue($isExit, $result);
        Response::isExit(false);
    }

    /**
     * @covers think\Response::result
     * @todo Implement testResult().
     */
    public function testResult()
    {
        $data   = "data";
        $code   = "1001";
        $msg    = "the msg";
        $type   = "json";
        $result = Response::result($data, $code, $msg, $type);

        $this->assertEquals($code, $result["code"]);
        $this->assertEquals($msg, $result["msg"]);
        $this->assertEquals($data, $result["data"]);
        $this->assertEquals($_SERVER['REQUEST_TIME'], $result["time"]);
        $this->assertEquals($type, Response::type());
    }

    /**
     * @covers think\Response::success
     * @todo Implement testSuccess().
     */
    public function testSuccess()
    {
        // round 1
        $msg  = 1001;
        $data = "data";

        $url = "www.HTTP_REFERER.com";
        if (isset($_SERVER["HTTP_REFERER"])) {
            $HTTP_REFERER = $_SERVER["HTTP_REFERER"];
        }
        $_SERVER["HTTP_REFERER"] = $url;
        Config::set('default_return_type', "json");

        $result = Response::success($msg, $data);

        $this->assertEquals($msg, $result["code"]);

        $this->assertEquals($data, $result["data"]);
        $this->assertEquals($url, $result["url"]);
        $this->assertEquals("json", Response::type());
        $this->assertEquals(3, $result["wait"]);

        // round 2
        $msg = "the msg";
        $url = "www.thinkphptestsucess.com";

        $result = Response::success($msg, $data, $url);

        $this->assertEquals($msg, $result["msg"]);
        $this->assertEquals($url, $result["url"]);

        // round 3 异常在travis-ci中未能重现
        // $this->setExpectedException('\think\Exception');
        // FIXME 静态方法mock
        // $oMockView = $this->getMockBuilder('\think\View')->setMethods(array(
        // 'fetch'
        // ))->getMock();

        // $oMockView->expects($this->any())->method('fetch')->will($this->returnValue('content'));

        // Config::set('default_return_type', "html");
        // $result = Response::success($msg, $data, $url);

        // FIXME 静态方法mock
        // $this->assertEquals('content', $result);
        if (isset($HTTP_REFERER)) {
            $_SERVER["HTTP_REFERER"] = $HTTP_REFERER;
        }

    }

    /**
     * @covers think\Response::error
     * @todo Implement testError().
     */
    public function testError()
    {
        // round 1
        $msg  = 1001;
        $data = "data";

        Config::set('default_return_type', "json");

        $result = Response::error($msg, $data);

        $this->assertEquals($msg, $result["code"]);
        $this->assertEquals($data, $result["data"]);
        $this->assertEquals('javascript:history.back(-1);', $result["url"]);
        $this->assertEquals("json", Response::type());
        $this->assertEquals(3, $result["wait"]);

        // round 2
        $msg = "the msg";
        $url = "www.thinkphptesterror.com";

        $result = Response::error($msg, $data, $url);

        $this->assertEquals($msg, $result["msg"]);
        $this->assertEquals($url, $result["url"]);

        // round 3 异常在travis-ci中未能重现
        // $this->setExpectedException('\think\Exception');
        // FIXME 静态方法mock
        // $oMockView = $this->getMockBuilder('\think\View')->setMethods(array(
        // 'fetch'
        // ))->getMock();

        // $oMockView->expects($this->any())->method('fetch')->will($this->returnValue('content'));

        // Config::set('default_return_type', "html");

        // $result = Response::error($msg, $data, $url);

        // FIXME 静态方法mock
        // $this->assertEquals('content', $result);
    }

    /**
     * @#runInSeparateProcess
     * @covers think\Response::redirect
     * @todo Implement testRedirect().
     */
    public function testRedirect()
    {
        // $url = "http://www.testredirect.com";
        // $params = array();
        // $params[] = 301;

        // // FIXME 静态方法mock Url::build
        // // echo "\r\n" . json_encode(xdebug_get_headers()) . "\r\n";
        // Response::redirect($url, $params);

        // $this->assertContains('Location: ' . $url, xdebug_get_headers());
    }

    /**
     * @#runInSeparateProcess
     * @covers think\Response::header
     * @todo Implement testHeader().
     */
    public function testHeader()
    {
        // $name = "Location";
        // $url = "http://www.testheader.com/";
        // Response::header($name, $url);
        // $this->assertContains($name . ': ' . $url, xdebug_get_headers());
    }

}
