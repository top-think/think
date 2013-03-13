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
namespace Think\View\Driver;
use Think\Template;
class Think {
    private $template   =   null;
    public function __construct($config=array()){
        $tpl = new Template($config);
        $this->template =   $tpl;
        //$tpl->tpl_path  =   MODULE_PATH.'view/';
        //$tpl->cache_path    =   MODULE_PATH.'cache/';
    }

    public function fetch($template,$data=array()){
        $this->template->assign($data);
        $this->template->display($template);
    }

}