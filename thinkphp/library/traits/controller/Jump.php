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
     * @param string $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param integer $wait 跳转等待时间
     * @return mixed
     */
    public function error($msg = '', $url = null, $data = '', $wait = 3)
    {
        return Response::error($msg, $data, $url, $wait);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access public
     * @param mixed $msg 提示信息
     * @param string $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param integer $wait 跳转等待时间
     * @return mixed
     */
    public function success($msg = '', $url = null, $data = '', $wait = 3)
    {
        return Response::success($msg, $data, $url, $wait);
    }

    /**
     * 返回封装后的API数据到客户端
     * @access public
     * @param mixed $data 要返回的数据
     * @param integer $code 返回的code
     * @param mixed $msg 提示信息
     * @param string $type 返回数据格式
     * @return mixed
     */
    public function result($data, $code = 0, $msg = '', $type = '')
    {
        return Response::result($data, $code, $msg, $type);
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
        Response::redirect($url, $params);
    }

}
