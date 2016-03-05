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
 * Rpc控制器测试
 * @author    Haotong Lin <lofanmi@gmail.com>
 */

namespace tests\thinkphp\library\think\controller;

use think\controller\Rpc as RpcController;

class MyRpcController extends RpcController
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

class rpcTest extends \PHPUnit_Framework_TestCase
{
    protected $preserveGlobalState = false;

    /**
     * @runInSeparateProcess
     */
    public function testAll()
    {
        $c = new MyRpcController;
        $this->assertEquals(true, $c->init);
        $this->assertEquals(null, $c->thinkphp());
        $this->assertEquals(false, $c->add(null));
    }
}
