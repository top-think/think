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

use PHPRPC_Server;

/**
 * ThinkPHP RPC控制器类
 */
abstract class Rpc
{
    /**
     * PHPRPC_Server实例
     * @access public
     */
    public $server;

    protected $allowMethodList = '';
    protected $debug           = false;

    /**
     * 架构函数
     * @access public
     */
    public function __construct($autoStart = true)
    {
        // 控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
        // 实例化phprpc
        $this->server = new PHPRPC_Server;
        if ($this->allowMethodList) {
            $methods = $this->allowMethodList;
        } else {
            $methods = get_class_methods($this);
            $methods = array_diff($methods, array('__construct', '__call', '_initialize'));
        }
        $this->server->add($methods, $this);

        if (APP_DEBUG || $this->debug) {
            $this->server->setDebugMode(true);
        }
        $this->server->setEnableGZIP(true);
        // 启动server
        if ($autoStart) {
            $this->server->start();
            echo $this->server->comment();
        }
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!method_exists($this->server, $method)) {
            return null;
        }
        return call_user_func_array([$this->server, $method], $args);
    }
}
