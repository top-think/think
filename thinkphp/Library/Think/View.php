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

class View {
    protected $engine = null; // 模板引擎实例
    protected $theme  = '';   // 模板主题名称
    protected $data   = [];   // 模板变量
    protected $config = [     // 视图参数
        'http_output_content' => true,
        'http_content_type'   => 'text/html',
        'http_charset'        => 'utf-8',
        'http_cache_control'  => 'private',
        'http_render_content' => false,
        'theme_on'            => false,
        'auto_detect_theme'   => false,
        'var_theme'           => 't',
        'default_theme'       => 'default',
        'http_cache_id'       => null,
        'view_path'           => '',
        'view_suffix'         => '.html',
    ];
    
    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name  变量名
     * @param mixed $value 变量值
     */
    public function assign($name, $value = ''){
        if(is_array($name)) {
            $this->data = array_merge($this->data, $name);
            return $this;
        }else {
            $this->data[$name] = $value;
        }
    }

    /**
     * 视图参数设置
     * @access public
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value = ''){
        $this->config[$name] = $value;
    }

    public function __construct(array $config = []){
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * 设置当前模板解析的引擎
     * @access public
     * @param string $engine 引擎名称
     * @param array $config 引擎参数
     * @return View
     */
    public function engine($engine, array $config = []){
        $class = '\\Think\\View\\Driver\\' . ucwords($engine);
        $this->engine = new $class($config);
        return $this;
    }
    
    /**
     * 设置当前输出的模板主题
     * @access public
     * @param  mixed $theme 主题名称
     * @return View
     */
    public function theme($theme){
        if(true === $theme) { // 自动侦测
            $this->config['theme_on'] = true;
            $this->config['auto_detect_theme'] = true;
        }elseif(false === $theme){ // 关闭主题
            $this->config['theme_on'] = false;
        }else{ // 指定模板主题
            $this->config['theme_on'] = true;
            $this->theme = $theme;
        }
        return $this;
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $template  模板文件名
     * @param array  $vars      模板输出变量
     * @param string $cache_id  模板缓存标识
     * @return mixed
     */
    public function display($template = '', $vars = [], $cache_id = '') {
        Tag::listen('view_begin', $template);
        // 解析并获取模板内容
        $content = $this->fetch($template, $vars, $cache_id);
        // 输出内容过滤
        Tag::listen('view_filter', $content);
        // 输出模板内容
        if($this->config['http_output_content']) {
            $this->render($content);
        }else{ // 返回解析后的内容
            return $content;
        }
    }

    /**
     * 解析和获取模板内容 用于输出
     * @access protected
     * @param string $template 模板文件名或者内容
     * @param array  $vars     模板输出变量
     * @param string $cache_id 模板缓存标识
     * @return string
     */
    protected function fetch($template, $vars = [], $cache_id='') {
        if(!$this->config['http_render_content']) {
            $template = $this->parseTemplate($template);
            // 模板不存在 抛出异常
            if(!is_file($template)) 
                E('template file not exists:' . $template);
        }
        $vars = $vars ? $vars : $this->data;
        // 页面缓存
        ob_start();
        ob_implicit_flush(0);
        if($this->engine) { // 指定模板引擎
            $this->engine->fetch($template, $vars, $cache_id);
        }else{  // 原生PHP解析
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
    private function parseTemplate($template) {
        if(is_file($template)) {
            return $template;
        }
        $template = str_replace(':', '/', $template);
        // 获取当前主题名称
        $theme = $this->getTemplateTheme();
        // 分析模板文件规则
        if('' == $template) {
            // 如果模板文件名为空 按照默认规则定位
            $template = CONTROLLER_NAME . '/' . ACTION_NAME;
        }elseif(false === strpos($template, '/')){
            $template = CONTROLLER_NAME . '/' . $template;
        }
        return ($this->config['view_path'] ? $this->config['view_path'] : MODULE_PATH . 'View/').$theme.$template.$this->config['view_suffix'];
    }

    /**
     * 获取当前的模板主题
     * @access private
     * @return string
     */
    private function getTemplateTheme() {
        if($this->config['theme_on']) {
            if($this->theme) { // 指定模板主题
                $theme = $this->theme;
            }elseif($this->config['auto_detect_theme']){
                // 自动侦测模板主题
                $t = $this->config['var_theme'];
                if (isset($_GET[$t])){
                    $theme = $_GET[$t];
                }elseif(Cookie::get('think_theme')){
                    $theme = Cookie::get('think_theme');
                }
                if(!is_dir(MODULE_PATH . 'View/' . $theme)) {
                    $theme = $this->config['default_theme'];
                }
                Cookie::set('think_theme', $theme, 864000);
            }else{
                $theme = $this->config['default_theme'];
            }
            return $theme . '/';
        }
        return '';
    }

    /**
     * 视图输出参数设置
     * @access public
     * @param mixed $config
     * @param mixed $value
     */
    public function http($config = [], $value = ''){
        if(is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }else{
            $this->config[$config] = $value;
        }
        return $this;
    }

    /**
     * 输出内容文本可以包括Html
     * @access private
     * @param string $content 输出内容
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @return mixed
     */
    private function render($content){
        // 网页字符编码
        header('Content-Type:'  . $this->config['http_content_type'] . '; charset=' . $this->config['http_charset']);
        header('Cache-control:' . $this->config['http_cache_control']);  // 页面缓存控制
        header('X-Powered-By:ThinkPHP');
        // 输出模板文件
        echo $content;
    }
}
