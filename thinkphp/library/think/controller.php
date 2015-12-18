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

T('controller/view');

class Controller
{
    use \traits\controller\view;

    /**
     * 前置操作方法列表
     * @var beforeActionList
     * @access protected
     */
    protected $beforeActionList = [];

    /**
     * 架构函数 初始化视图类 并采用内置模板引擎
     * @access public
     * @param array $config
     */
    public function __construct($config = [])
    {
        // 控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
        // 前置操作方法
        // 支持 ['action1','action2'] 或者 ['action1'=>['only'=>'index'],'action2'=>['except'=>'login']]
        $list = $this->beforeActionList;
        if ($list) {
            foreach ($list as $method => $options) {
                is_numeric($method) ?
                $this->beforeAction($options) :
                $this->beforeAction($method, $options);
            }
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
}
