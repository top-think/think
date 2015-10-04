<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\controller;

abstract class phprpc
{
    /**
     * PHPRpc控制器架构函数
     * @access public
     */
    public function __construct()
    {
        //导入类库
        think\loader::import('vendor.phprpc.phprpc_server');
        //实例化phprpc
        $server = new \PHPRPC_Server();
        $server->add($this);
        if (APP_DEBUG) {
            $server->setDebugMode(true);
        }
        $server->setEnableGZIP(true);
        $server->start();
        //C('PHPRPC_COMMENT',$server->comment());
        echo $server->comment();
    }

}
