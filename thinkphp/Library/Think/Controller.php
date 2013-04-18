<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Think;

class Controller {
    // 视图类实例
    protected $view = null;

    /**
     * 架构函数 初始化视图类 并采用内置模板引擎
     * @access public
     */
    public function __construct(){
        // 模板引擎参数
        $config = [
            'tpl_path'   => MODULE_PATH  . 'View/',
            'cache_path' => RUNTIME_PATH . 'Cache/',
        ];
        $this->view = new View();
        $this->view->engine('think', $config);
        
        //控制器初始化
        if(method_exists($this, '_initialize'))
            $this->_initialize();
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param string $cache_id 模板缓存标识
     * @return mixed
     */
    public function display($template = '', $vars = [], $cache_id = ''){
        $this->view->display($template, $vars, $cache_id);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array  $vars    模板输出变量
     * @return mixed
     */
    public function show($content, $vars = []){
        $this->view->http('http_render_content', true)->display($content, $vars);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name  要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    public function assign($name, $value = ''){
        $this->view->assign($name, $value);
    }

    public function __set($name, $value){
        return $this->assign($name, $value);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data, $type='') {
        if(empty($type)) $type = C('default_ajax_return');
        switch (strtoupper($type)){
            case 'JSON':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML':
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/javascript; charset=utf-8');
                $handler = isset($_GET[C('var_jsonp_handler')]) ? $_GET[C('var_jsonp_handler')] : C('default_jsonp_handler');
                exit($handler . '(' . json_encode($data) . ');');  
            case 'SCRIPT':
                // 返回可执行的js脚本
                header('Content-Type:application/javascript; charset=utf-8');
                exit($data);
            case 'HTML':
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            case 'TEXT':
                // 返回可执行的js脚本
                header('Content-Type:text/plain; charset=utf-8');
                exit($data);
            default:
                // 用于扩展其他返回格式数据
                Tag::listen('ajax_return', $data);
        }
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed  $ajax    是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message, $jumpUrl = '', $ajax = false) {
        $this->dispatchJump($message, 0, $jumpUrl, $ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed  $ajax    是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message, $jumpUrl = '', $ajax = false) {
        $this->dispatchJump($message, 1, $jumpUrl, $ajax);
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
    private function dispatchJump($message, $status = 1, $jumpUrl = '', $ajax = false) {
        if(true === $ajax || IS_AJAX) {// AJAX提交
            $data           = is_array($ajax) ? $ajax : [];
            $data['info']   = $message;
            $data['status'] = $status;
            $data['url']    = $jumpUrl;
            $this->ajaxReturn($data);
        }
        if(is_int($ajax)) $this->view->assign('waitSecond', $ajax);
        if(!empty($jumpUrl)) $this->view->assign('jumpUrl', $jumpUrl);
        // 提示标题
        $this->view->assign('msgTitle', $status ? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        $this->view->assign('status', $status);   // 状态
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON',false);
        if($status) { //发送成功信息
            $this->view->assign('message', $message);// 提示信息
            // 成功操作后默认停留1秒
            $this->view->assign('waitSecond', '1');
            // 默认操作成功自动返回操作前页面
            if(!$jumpUrl) $this->view->assign("jumpUrl", $_SERVER["HTTP_REFERER"]);
            $this->display(C('success_tmpl'));
        }else{
            $this->view->assign('error', $message);// 提示信息
            //发生错误时候默认停留3秒
            $this->view->assign('waitSecond', '3');
            // 默认发生错误的话自动返回上页
            if(!$jumpUrl) $this->view->assign('jumpUrl', 'javascript:history.back(-1);');
            $this->display(C('error_tmpl'));
            // 中止执行  避免出错后继续执行
            exit ;
        }
    }
}
