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
    public function __construct($config=[]){
        $this->template =   new Template($config);
    }

    public function fetch($template,$data=[]){
        $this->template->display($template,$data);
    }

}