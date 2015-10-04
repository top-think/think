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

class View
{
    // 模板引擎实例
    protected $engine = null; 
    // 模板主题名称
    protected $theme  = ''; 
    // 模板变量
    protected $data   = []; 
    // 视图参数
    protected $config = [ 
        'theme_on'          => false,
        'auto_detect_theme' => false,
        'var_theme'         => 't',
        'default_theme'     => 'default',
        'http_cache_id'     => null,
        'view_path'         => '',
        'view_suffix'       => '.html',
        'view_depr'         => '/',
        'view_layer'        => VIEW_LAYER,
        'engine_type'       => 'think',
    ];

    public function __construct(array $config = [])
    {
        $this->config(empty($config) ? Config::get() : $config);
        $this->engine($this->config['engine_type']);
    }

    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name  变量名
     * @param mixed $value 变量值
     */
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
            return $this;
        } else {
            $this->data[$name] = $value;
        }
    }

    /**
     * 设置视图参数
     * @access public
     * @param array $config 视图参数
     * @return View
     */
    public function config(array $config = [])
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
     * 设置当前模板解析的引擎
     * @access public
     * @param string $engine 引擎名称
     * @param array $config 引擎参数
     * @return View
     */
    public function engine($engine, array $config = [])
    {
        $class        = '\\think\\view\\driver\\' . strtolower($engine);
        $this->engine = new $class($config);
        return $this;
    }

    /**
     * 设置当前输出的模板主题
     * @access public
     * @param  mixed $theme 主题名称
     * @return View
     */
    public function theme($theme)
    {
        if (true === $theme) {
            // 自动侦测
            $this->config['theme_on']          = true;
            $this->config['auto_detect_theme'] = true;
        } elseif (false === $theme) {
            // 关闭主题
            $this->config['theme_on'] = false;
        } else {
            // 指定模板主题
            $this->config['theme_on'] = true;
            $this->theme              = $theme;
        }
        return $this;
    }

    /**
     * 解析和获取模板内容 用于输出
     * @access public
     * @param string $template 模板文件名或者内容
     * @param array  $vars     模板输出变量
     * @param string $cache_id 模板缓存标识
     * @return string
     */
    public function fetch($template = '', $vars = [], $cache_id = '', $renderContent = false)
    {
        if (!$renderContent) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
            // 模板不存在 抛出异常
            if (!is_file($template)) {
                throw new Exception('template file not exists:' . $template);
            }
        }
        $vars = $vars ? $vars : $this->data;
        // 页面缓存
        ob_start();
        ob_implicit_flush(0);
        if ($this->engine) {
            // 指定模板引擎
            $this->engine->fetch($template, $vars, $cache_id);
        } else {
            // 原生PHP解析
            extract($vars, EXTR_OVERWRITE);
            is_file($template) ? include $template : eval('?>' . $template);
        }
        // 获取并清空缓存
        return ob_get_clean();
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    private function parseTemplate($template)
    {
        if (is_file($template)) {
            return $template;
        }
        $depr     = $this->config['view_depr'];
        $template = str_replace(':', $depr, $template);

        // 获取当前模块
        $module = MODULE_NAME;
        if (strpos($template, '@')) {
            // 跨模块调用模版文件
            list($module, $template) = explode('@', $template);
        }
        // 获取当前主题的模版路径
        defined('THEME_PATH') || define('THEME_PATH', $this->getThemePath($module));

        // 分析模板文件规则
        if ('' == $template) {
            // 如果模板文件名为空 按照默认规则定位
            $template = CONTROLLER_NAME . $depr . ACTION_NAME;
        } elseif (false === strpos($template, $depr)) {
            $template = CONTROLLER_NAME . $depr . $template;
        }
        return THEME_PATH . $template . $this->config['view_suffix'];
    }

    /**
     * 获取当前的模板主题
     * @access private
     * @return string
     */
    private function getTemplateTheme($module)
    {
        if ($this->config['theme_on']) {
            if ($this->theme) {
                // 指定模板主题
                $theme = $this->theme;
            } elseif ($this->config['auto_detect_theme']) {
                // 自动侦测模板主题
                $t = $this->config['var_theme'];
                if (isset($_GET[$t])) {
                    $theme = $_GET[$t];
                } elseif (Cookie::get('think_theme')) {
                    $theme = Cookie::get('think_theme');
                }
                if (!is_dir(APP_PATH . $module . '/' . $this->config['view_layer'] . '/' . $theme)) {
                    $theme = $this->config['default_theme'];
                }
                Cookie::set('think_theme', $theme, 864000);
            } else {
                $theme = $this->config['default_theme'];
            }
            return $theme . '/';
        }
        return '';
    }

    /**
     * 获取当前的模板路径
     * @access protected
     * @param  string $module 模块名
     * @return string
     */
    protected function getThemePath($module = MODULE_NAME)
    {
        // 获取当前主题名称
        $theme = $this->getTemplateTheme($module);
        // 获取当前主题的模版路径
        $tmplPath = $this->config['view_path']; // 模块设置独立的视图目录
        if (!$tmplPath) {
            // 定义TMPL_PATH 则改变全局的视图目录到模块之外
            $tmplPath = defined('TMPL_PATH') ? TMPL_PATH . $module . '/' : APP_PATH . $module . '/' . $this->config['view_layer'] . '/';
        }
        return $tmplPath . $theme;
    }

}
