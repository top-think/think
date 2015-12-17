<?php

/**
 * 用法：
 * T('controller/view');
 * class index
 * {
 *     use \traits\controller\view;
 *     public function index(){
 *         $this->assign();
 *         $this->show();
 *     }
 * }
 */
namespace traits\controller;

use think\Config;

trait View
{
    // 视图类实例
    protected $view = null;

    /**
     * 架构函数 初始化视图类 并采用内置模板引擎
     * @access public
     */
    public function initView()
    {
        // 模板引擎参数
        if (is_null($this->view)) {
            $this->view = new \think\View(Config::get()); // 只能这样写，不然view会冲突
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
        $this->initView();
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
        $this->initView();
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
        $this->initView();
        $this->view->assign($name, $value);
    }
}
