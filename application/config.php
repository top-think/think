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

// 惯例配置文件
return [
    'app_debug'         =>  true,
    'var_module'        =>  'm',
    'var_controll'      =>  'c',
    'var_action'        =>  'a',
    'var_pathinfo'      =>  's',
    'pathinfo_fetch'    =>  'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL',
    'pathinfo_depr'     =>  '/',
    'require_module'    =>  true,
    'default_module'    =>  'index',
    'require_controller'  =>  true,
    'default_controller'  =>  'index',
    'lib_path'          =>  '',
    'app_path'          =>  '',
    'action_suffix'     =>  '',
    'file_ext'          =>  '.php',
    'url_model'         =>  1,
    'TMPL_EXCEPTION_FILE'   => THINK_PATH.'tpl/think_exception.tpl',// 异常页面的模板文件
    'show_page_trace'       =>  true,
        'TRACE_PAGE_TABS'   => array('BASE'=>'基本','FILE'=>'文件','INFO'=>'流程','ERR|NOTIC'=>'错误','SQL'=>'SQL','DEBUG'=>'调试'), // 页面Trace可定制的选项卡 
        'db_sql_log'=>true,
        'url_params_bind'=>TRUE,
];