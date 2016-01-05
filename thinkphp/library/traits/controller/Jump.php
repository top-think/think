<?php

/**
 * 用法：
 * T('controller/Jump');
 * class index
 * {
 *     use \traits\controller\Jump;
 *     public function index(){
 *         $this->error();
 *         $this->redirect();
 *     }
 * }
 */
namespace traits\controller;

use think\Response;

trait Jump
{
    /**
     * 操作错误跳转的快捷方法
     * @access public
     * @param mixed $msg 提示信息
     * @param mixed $data 返回的数据
     * @param mixed $url 跳转的URL地址
     * @param mixed $wait 跳转等待时间
     * @return void
     */
    public function error($msg = '', $data = '', $url = '', $wait = 3)
    {
        return Response::error($msg, $data, $url, $wait);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access public
     * @param mixed $msg 提示信息
     * @param mixed $data 返回的数据
     * @param mixed $url 跳转的URL地址
     * @param mixed $wait 跳转等待时间
     * @return void
     */
    public function success($msg = '', $data = '', $url = '', $wait = 3)
    {
        return Response::success($msg, $data, $url, $wait);
    }

    /**
     * URL重定向
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array|int $params 其它URL参数或http code
     * @return void
     */
    public function redirect($url, $params = [])
    {
        return Response::redirect($url, $params);
    }

}
