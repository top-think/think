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
class IndexControll {

    public function index(){
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
        //$view->engine('think',$config['template_options']);
        $view->display();
    }

}