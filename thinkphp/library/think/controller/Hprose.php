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

use Hprose\Http\Server as HproseHttpServer;

/**
 * ThinkPHP Hprose控制器类
 */
abstract class Hprose
{
    /**
     * HproseHttpServer实例
     * @access public
     */
    public $server;

    protected $allowMethodList = '';
    protected $crossDomain     = false;
    protected $P3P             = false;
    protected $get             = true;
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
        // 实例化HproseHttpServer
        $this->server = new HproseHttpServer();
        if ($this->allowMethodList) {
            $methods = $this->allowMethodList;
        } else {
            $methods = get_class_methods($this);
            $methods = array_diff($methods, array('__construct', '__call', '_initialize'));
        }
        $this->server->addMethods($methods, $this);
        if (APP_DEBUG || $this->debug) {
            $this->server->setDebugEnabled(true);
        }
        // Hprose设置
        $this->server->setCrossDomainEnabled($this->crossDomain);
        $this->server->setP3PEnabled($this->P3P);
        $this->server->setGetEnabled($this->get);
        // 启动server
        if ($autoStart) {
            $this->server->start();
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
