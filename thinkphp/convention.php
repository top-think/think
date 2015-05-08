<?php

return [
    'app_status'            =>  'debug',// 应用模式状态
    'var_pathinfo'          =>  's',    // PATHINFO变量名 用于兼容模式
    'extra_config_list'     =>  [],
    'pathinfo_fetch'        =>  'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL',
    'pathinfo_depr'         =>  '/',    // pathinfo分隔符
    'require_module'        =>  true,   // 是否显示模块
    'default_module'        =>  'index',  // 默认模块名
    'default_controller'    =>  'index',    // 默认控制器名
    'default_action'        =>  'index',    // 默认操作名
    'action_suffix'         =>  '', // 操作方法后缀
    'url_model'             =>  1,  // URL模式
    'url_request_uri'       =>  'REQUEST_URI', // 获取当前页面地址的系统变量 默认为REQUEST_URI    
    'base_url'              =>  $_SERVER["SCRIPT_NAME"],    // 基础URL路径
    'url_html_suffix'       =>  '.html',
    'url_params_bind'       =>  TRUE,  // url变量绑定
    'exception_tmpl'        =>  THINK_PATH.'Tpl/think_exception.tpl',// 异常页面的模板文件
    'error_tmpl'            =>  THINK_PATH.'Tpl/dispatch_jump.tpl', // 默认错误跳转对应的模板文件
    'success_tmpl'          =>  THINK_PATH.'Tpl/dispatch_jump.tpl', // 默认成功跳转对应的模板文件
    'default_ajax_return'   =>  'JSON',  // 默认AJAX 数据返回格式,可选JSON XML ...
    'default_jsonp_handler' =>  'jsonpReturn', // 默认JSONP格式返回的处理方法
    'var_jsonp_handler'     =>  'callback',
    'template_engine'       =>  'think',
    'common_module'         =>  'common',
    'action_bind_class'     =>  false,
    'url_module_map'        =>  [],

    /* 错误设置 */
    'error_message'     =>  '页面错误！请稍后再试～',//错误显示信息,非调试模式有效
    'error_page'        =>  '', // 错误定向页面
    'show_error_msg'    =>  false,    // 显示错误信息

    'log'               =>  [
        'type'              =>  'File',      
        'path'              =>  LOG_PATH,
    ],

    'cache'             =>  [
        'type'              =>  'File',
        'path'              =>  CACHE_PATH,      
        'prefix'            =>  '',
        'expire'            =>  0,
    ],

    'session'               =>  [
        'prefix'            =>  'think',
        'type'              =>  '',
        'auto_start'        =>  true,        
    ],

    /* 数据库设置 */
    'database'    =>  [
        'type'              =>  'mysql',     // 数据库类型
        'dsn'               =>  '', // 
        'hostname'          =>  'localhost', // 服务器地址
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