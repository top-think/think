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

namespace think\view\driver;

use think\Template;

class Think
{
    private $template = null;
    public function __construct($config = [])
    {
        $this->template = new Template($config);
    }

    public function fetch($template, $data = [], $cacheId = '')
    {
        if (is_file($template)) {
            $this->template->display($template, $data, $cacheId);
        } else {
            $this->template->fetch($template, $data);
        }
    }
}
