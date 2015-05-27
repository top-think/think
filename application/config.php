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

// 惯例配置文件
return [

    'url_model'           => 1,
    'exception_tmpl'      => THINK_PATH.'tpl/think_exception.tpl',// 异常页面的模板文件
    'show_page_trace'     => true,
    'trace_page_tabs'     => ['BASE'=>'基本','FILE'=>'文件','INFO'=>'流程','ERR|NOTIC'=>'错误','SQL'=>'SQL','DEBUG'=>'调试'], // 页面Trace可定制的选项卡 
    'db_sql_log'          => true,
    'url_params_bind'     => true,

    /* 数据库设置 */
    'database'    =>  [
        'type'              =>  'mysql',     // 数据库类型
        'hostname'          =>  '127.0.0.1', // 服务器地址
        'database'          =>  '',          // 数据库名
        'username'          =>  'root',      // 用户名
        'password'          =>  '',          // 密码
        'hostport'          =>  '',        // 端口               
        'params'            =>  [], // 数据库连接参数        
        'charset'           =>  'utf8',      // 数据库编码默认采用utf8  
        'prefix'            =>  '',    // 数据库表前缀
        'debug'             =>  false, // 数据库调试模式
        'deploy'            =>  0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'rw_separate'       =>  false,       // 数据库读写是否分离 主从式有效
        'master_num'        =>  1, // 读写分离后 主服务器数量
        'slave_no'          =>  '', // 指定从服务器序号
    ],
];
