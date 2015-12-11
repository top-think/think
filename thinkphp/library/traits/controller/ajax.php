<?php

/**
 * 用法：
 * T('controller/ajax');
 * class index
 * {
 *     use \traits\controller\ajax;
 *     public function index(){
 *         $this->result();
 *     }
 * }
 */
namespace traits\controller;

trait Ajax
{
    /**
     * 返回封装后的API数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param string $msg 提示信息
     * @param integer $code 返回的code
     * @param string $url 重定向地址
     * @param integer  $wait  跳转等待时间
     * @return void
     */
    public function result($data = '', $msg = '', $code = 0, $url = '', $wait = 0)
    {
        return [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param integer  $wait  跳转等待时间
     * @return void
     */
    protected function error($message, $jumpUrl = '', $wait = 5)
    {
        $jumpUrl = $jumpUrl ?: 'javascript:history.back(-1);';
        return $this->result('', $message, 0, $jumpUrl, $wait);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param integer  $wait  跳转等待时间
     * @return void
     */
    protected function success($message, $jumpUrl = '', $wait = 3)
    {
        $jumpUrl = $jumpUrl ?: $_SERVER["HTTP_REFERER"];
        return $this->result('', $message, 1, $jumpUrl, $wait);
    }

}
