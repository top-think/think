<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think\controller;

/**
 * ThinkPHP JsonRPC控制器类
 */
abstract class Jsonrpc
{

    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
        //控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }

        //导入类库
        \think\Loader::import('vendor.jsonrpc.jsonRPCServer');
        // 启动server
        \jsonRPCServer::handle($this);
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method, $args)
    {}
}
