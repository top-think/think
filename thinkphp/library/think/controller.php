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

namespace think;

use think\View;

class Controller
{
    // 视图类实例
    protected $view   = null;
    protected $config = [
        'before_action_list' => [],
        'success_tmpl'       => '',
        'error_tmpl'         => '',
    ];

    /**
     * 架构函数 初始化视图类 并采用内置模板引擎
     * @access public
     */
    public function __construct($config = [])
    {
        // 模板引擎参数
        $this->view = new View();
        $this->config(empty($config) ? Config::get() : $config);

        // 控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
        // 前置操作方法
        // 支持 ['action1','action2'] 或者 ['action1'=>['only'=>'index'],'action2'=>'except'=>'login']
        $list = $this->config['before_action_list'];
        if ($list) {
            foreach ($list as $method => $options) {
                is_numeric($method) ?
                $this->beforeAction($options) :
                $this->beforeAction($method, $options);
            }
        }
    }

    /**
     * 设置控制器参数
     * @access public
     * @param array $config 视图参数
     * @return View
     */
    public function config($config = [])
    {
        if (is_array($config)) {
            foreach ($this->config as $key => $val) {
                if (isset($config[$key])) {
                    $this->config[$key] = $config[$key];
                }
            }
            return $this;
        }
    }

    /**
     * 前置操作
     * @access protected
     * @param string    $method     前置操作方法名
     * @param array     $options    调用参数 ['only'=>[...]] 或者['except'=>[...]]
     */
    protected function beforeAction($method, $options = [])
    {
        if (isset($options['only'])) {
            if (!in_array(ACTION_NAME, $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (in_array(ACTION_NAME, $options['except'])) {
                return;
            }
        }

        if (method_exists($this, $method)) {
            call_user_func([$this, $method]);
        }
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param string $cache_id 模板缓存标识
     * @return mixed
     */
    public function fetch($template = '', $vars = [], $cache_id = '')
    {
        return $this->view->fetch($template, $vars, $cache_id);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array  $vars    模板输出变量
     * @return mixed
     */
    public function show($content, $vars = [])
    {
        return $this->view->fetch($content, $vars, '', true);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name  要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    public function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data, $type = '')
    {
        Response::returnData($data, $type);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed  $ajax    是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message, $jumpUrl = '', $ajax = false)
    {
        return $this->dispatchJump($message, 0, $jumpUrl, $ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed  $ajax    是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message, $jumpUrl = '', $ajax = false)
    {
        return $this->dispatchJump($message, 1, $jumpUrl, $ajax);
    }

    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @access private
     * @param string  $message 提示信息
     * @param Boolean $status  状态
     * @param string  $jumpUrl 页面跳转地址
     * @param mixed   $ajax    是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    private function dispatchJump($message, $status = 1, $jumpUrl = '', $ajax = false)
    {
        if (true === $ajax || IS_AJAX) {
            // AJAX提交
            $data           = is_array($ajax) ? $ajax : [];
            $data['info']   = $message;
            $data['status'] = $status;
            $data['url']    = $jumpUrl;
            $this->ajaxReturn($data);
        }
        // 模板变量
        $data = [];
        if (is_int($ajax)) {
            $data['waitSecond'] = $ajax;
        }

        if (!empty($jumpUrl)) {
            $data['jumpUrl'] = $jumpUrl;
        }

        // 提示标题
        $data['msgTitle'] = Lang::get($status ? '_OPERATION_SUCCESS_' : '_OPERATION_FAIL_');
        $data['status']   = $status; // 状态

        //保证输出不受静态缓存影响
        Config::set('html_cache_on', false);
        if ($status) {
            //发送成功信息
            $data['message'] = $message; // 提示信息
            // 成功操作后默认停留1秒
            $data['waitSecond'] = '1';
            // 默认操作成功自动返回操作前页面
            if (!$jumpUrl) {
                $data["jumpUrl"] = $_SERVER["HTTP_REFERER"];
            }

            return $this->display(Config::get('success_tmpl'), $data);
        } else {
            $data['error'] = $message; // 提示信息
            //发生错误时候默认停留3秒
            $data['waitSecond'] = '3';
            // 默认发生错误的话自动返回上页
            if (!$jumpUrl) {
                $data['jumpUrl'] = 'javascript:history.back(-1);';
            }

            return $this->display(Config::get('error_tmpl'), $data);
        }
    }
}
