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
 * Debug测试
 * @author    大漠 <zhylninc@gmail.com>
 */

namespace tests\thinkphp\library\think;

use think\Debug;

class debugTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Debug
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Debug();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {}

    /**
     * @covers think\Debug::remark
     * @todo Implement testRemark().
     */
    public function testRemark()
    {
        $name  = "testremarkkey";
        $value = "testremarkval";
        \think\Debug::remark($name);
    }

    /**
     * @covers think\Debug::getRangeTime
     * @todo Implement testGetRangeTime().
     */
    public function testGetRangeTime()
    {
        $start = "testGetRangeTimeStart";
        $end   = "testGetRangeTimeEnd";
        \think\Debug::remark($start);
        usleep(20000);
        // \think\Debug::remark($end);

        $time = \think\Debug::getRangeTime($start, $end);
        $this->assertLessThan(0.03, $time);
        //$this->assertEquals(0.03, ceil($time));
    }

    /**
     * @covers think\Debug::getUseTime
     * @todo Implement testGetUseTime().
     */
    public function testGetUseTime()
    {
        $time = \think\Debug::getUseTime();
        $this->assertLessThan(2.5, $time);
    }

    /**
     * @covers think\Debug::getThroughputRate
     * @todo Implement testGetThroughputRate().
     */
    public function testGetThroughputRate()
    {
        usleep(100000);
        $throughputRate = \think\Debug::getThroughputRate();
        $this->assertLessThan(10, $throughputRate);
    }

    /**
     * @covers think\Debug::getRangeMem
     * @todo Implement testGetRangeMem().
     */
    public function testGetRangeMem()
    {
        $start = "testGetRangeMemStart";
        $end   = "testGetRangeMemEnd";
        \think\Debug::remark($start);
        $str = "";
        for ($i = 0; $i < 10000; $i++) {
            $str .= "mem";
        }

        $rangeMem = \think\Debug::getRangeMem($start, $end);

        $this->assertLessThan(33, explode(" ", $rangeMem)[0]);
    }

    /**
     * @covers think\Debug::getUseMem
     * @todo Implement testGetUseMem().
     */
    public function testGetUseMem()
    {
        $useMem = \think\Debug::getUseMem();

        $this->assertLessThan(13, explode(" ", $useMem)[0]);
    }

    /**
     * @covers think\Debug::getMemPeak
     * @todo Implement testGetMemPeak().
     */
    public function testGetMemPeak()
    {
        $start = "testGetMemPeakStart";
        $end   = "testGetMemPeakEnd";
        \think\Debug::remark($start);
        $str = "";
        for ($i = 0; $i < 100000; $i++) {
            $str .= "mem";
        }
        $memPeak = \think\Debug::getMemPeak($start, $end);

        // echo "\r\n" . $memPeak . "\r\n";

        $this->assertLessThan(238, explode(" ", $memPeak)[0]);
    }

    /**
     * @covers think\Debug::getFile
     * @todo Implement testGetFile().
     */
    public function testGetFile()
    {
        $count = \think\Debug::getFile();

        $this->assertEquals(count(get_included_files()), $count);

        $info = \think\Debug::getFile(true);
        $this->assertEquals(count(get_included_files()), count($info));

        $this->assertContains("KB", $info[0]);
    }

    /**
     * @covers think\Debug::dump
     * @todo Implement testDump().
     */
    public function testDump()
    {
        $var        = array();
        $var["key"] = "val";
        $output     = \think\Debug::dump($var, false, $label = "label");

        if (IS_WIN) {
            $this->assertEquals("(1) {\\n  'key' =>\\n  string(3) \\\"val\\\"\\n}\\n\\r\\n\"", end(explode("array", json_encode($output))));
        } else {
            $this->assertEquals("(1) {\\n  'key' =>\\n  string(3) \\\"val\\\"\\n}\\n\\n\"", end(explode("array", json_encode($output))));
        }
    }
}
