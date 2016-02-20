<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\view\driver;

use think\Template;

class Think
{
    private $template = null;
    public function __construct($config = [])
    {
        $this->template = new Template($config);
    }

    public function fetch($template, $data = [], $cache = [])
    {
        if (is_file($template)) {
            $this->template->display($template, $data, $cache);
        } else {
            $this->template->fetch($template, $data);
        }
    }
    
    /**
     * 修改模板引擎配置项
     * @access public
     * @param array|string $config
     * @return string|array
     */
    public function config($config)
    {
        if(is_array($config)){
            $this->template->config($config);
            return $this;
        }else{
            return $this->template->config($config);
        }
    }
}
