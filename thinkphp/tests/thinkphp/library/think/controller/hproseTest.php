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
 * Hprose控制器测试
 * @author    Haotong Lin <lofanmi@gmail.com>
 */

namespace tests\thinkphp\library\think\controller;

use think\controller\Hprose as HproseController;

class MyHproseController extends HproseController
{
    public $init = false;

    public function __construct()
    {
        parent::__construct(false);
    }

    protected function _initialize()
    {
        $this->init = true;
    }
}

class hproseTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $c = new MyHproseController;
        $this->assertEquals(true, $c->init);
        $this->assertEquals(null, $c->thinkphp());
        $this->assertEquals(true, $c->isDebugEnabled());
    }
}
