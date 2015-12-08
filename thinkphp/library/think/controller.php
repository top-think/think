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
     *
     * @param array $config
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
        }
        return $this;
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
        return $this->view->show($content, $vars);
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
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        if ('html' == Config::get('default_return_type')) {
            return $this->fetch(Config::get('dispatch_jump_tmpl'), $result);
        } else {
            return $result;
        }
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
