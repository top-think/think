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
// $Id$
namespace Think;
class View {

    protected $engine   =   null;       // 模板引擎实例
    protected $data     =   [];    // 模板变量
    protected $config   =   [];    // 视图参数
    
    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name
     * @param mixed $value
     */
    public function assign($name,$value=''){
        if(is_array($name)) {
            $this->data   =  array_merge($this->data,$name);
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
    public function __set($name,$value=''){
        $this->config[$name] = $value;
    }

    public function __construct($config=[]){
        $this->config   =   $config;
        if(!empty($this->config['template_engine'])) {
            $this->engine($this->config['template_engine'],$config['template_options']);
        }
    }
    
    public function engine($engine,$config=[]){
        $class  =   '\\Think\\View\\Driver\\'.ucwords($engine);
        $this->engine =   new $class($config);
        return $this;
    }
    
    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $template 模板文件名
     * @param array $vars 模板输出变量
     * @param string $cacheId 模板缓存标识
     * @param boolean $return 是否返回
     * @return mixed
     */
    public function display($template='',$vars=[],$cacheId='',$return=false) {
        Tag::listen('view_begin',$template);
        // 解析并获取模板内容
        $content = $this->fetch($template,$vars,$cacheId);
        // 输出模板内容
        if($return) {
            return $content;
        }else{
            $this->render($content);
        }
    }

    /**
     * 解析和获取模板内容 用于输出
     * @access protected
     * @param string $template 模板文件名或者内容
     * @param array $vars 模板输出变量
     * @param string $cacheId 模板缓存标识
     * @return string
     */
    protected function fetch($template,$vars=[],$cacheId='') {
        Tag::listen('view_template',$template);
        $vars   =   $vars?$vars:$this->data;
        // 页面缓存
        ob_start();
        ob_implicit_flush(0);
        if($this->engine) { // 指定模板引擎
            $this->engine->fetch($template,$vars,$cacheId);
        }else{  // 原生PHP解析
            extract($vars, EXTR_OVERWRITE);
            is_file($template)?include $template:eval('?>'.$template);
        }
        // 获取并清空缓存
        $content = ob_get_clean();
        Tag::listen('view_filter',$content);
        // 输出模板文件
        return $content;
    }

    /**
     * 视图输出参数设置
     * @access public
     * @param mixed $config
     * @param mixed $value
     */
    public function http($config=[],$value=''){
        if(is_array($config)) {
            $this->config   =   array_merge($this->config,$config);
        }else{
            $this->config[$config]  =   $value;
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
        header('Content-Type:'.$this->config['http_content_type'].'; charset='.$this->config['http_charset']);
        header('Cache-control: '.$this->config['http_cache_control']);  // 页面缓存控制
        header('X-Powered-By:ThinkPHP');
        // 输出模板文件
        echo $content;
    }
}