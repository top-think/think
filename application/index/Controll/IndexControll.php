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

namespace Index\Controll;
use Think\View;
use Think\Image;
class IndexControll {

    public function __call($name,$args){
        echo ("call");
    }

    public function ddd_get(){
        $this->ttt();
        echo ("get"); 
    }

    public function ybefore_index(){
        echo ("before");
    }
    public function index(){echo ("hello");exit;
        Image::open('./1.gif')->save('./2.gif');
        $User = M('form')->find(); echo m('form')->_sql();
        //$config['template_engine']  =   'think';
        $config['template_options'] =   array(
            'tpl_path'         =>   MODULE_PATH.'view/',
            'cache_path'    =>   MODULE_PATH.'cache/',
            'cache_type'    =>  '',
            'cache_options' =>  array(
                'temp'          =>  APP_PATH.'runtime/temp/',
                ),
        );
        $config['http_content_type']    =   'text/html';
        $config['http_charset'] =   'utf-8';
        $view   =   new View($config);
        $view->assign('user',$User);
        //$view->engine('think',$config['template_options']);
        $view->display();
    }

    public function test(){        
        $verify = new ThinkVerify('think');
        $verify->create();
    }
}