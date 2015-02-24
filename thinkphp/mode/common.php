<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
   
/**
 * ThinkPHP 普通模式定义
 */

return [
    // 配置文件
    'config'    =>  [
        'app_debug'             =>  true,   // 调试模式
        'app_status'            =>  'debug',// 应用模式状态
        'var_module'            =>  'm',    // 模块变量名
        'var_controller'        =>  'c',    // 控制器变量名
        'var_action'            =>  'a',    // 操作变量名
        'var_pathinfo'          =>  's',    // PATHINFO变量名 用于兼容模式
        'pathinfo_fetch'        =>  'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL',
        'pathinfo_depr'         =>  '/',    // pathinfo分隔符
        'require_module'        =>  true,   // 是否显示模块
        'default_module'        =>  'index',  // 默认模块名
        'default_controller'    =>  'index',    // 默认控制器名
        'default_action'        =>  'index',    // 默认操作名
        'action_suffix'         =>  '', // 操作方法后缀
        'url_model'             =>  1,  // URL模式
        'base_url'              =>  $_SERVER["SCRIPT_NAME"],    // 基础URL路径
        'url_html_suffix'       =>  '.html',
        'url_params_bind'       =>  false,  // url变量绑定
        'exception_tmpl'        =>  THINK_PATH.'Tpl/think_exception.tpl',// 异常页面的模板文件
        'error_tmpl'            =>  THINK_PATH.'Tpl/dispatch_jump.tpl', // 默认错误跳转对应的模板文件
        'success_tmpl'          =>  THINK_PATH.'Tpl/dispatch_jump.tpl', // 默认成功跳转对应的模板文件
        'default_ajax_return'   =>  'JSON',  // 默认AJAX 数据返回格式,可选JSON XML ...
        'default_jsonp_handler' =>  'jsonpReturn', // 默认JSONP格式返回的处理方法
        'var_jsonp_handler'     =>  'callback',
        'template_engine'       =>  'think',
        'common_module'         =>  'Common',
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
    ],

    // 别名定义
    'alias'     =>  [
        'think\App'                     =>  CORE_PATH . 'app'.EXT,
        'think\Log'                     =>  CORE_PATH . 'log'.EXT,
        'think\log\driver\File'         =>  CORE_PATH . 'log/driver/file'.EXT,
        'think\Config'                  =>  CORE_PATH . 'config'.EXT,
        'think\Route'                   =>  CORE_PATH . 'route'.EXT,
        'think\Exception'               =>  CORE_PATH . 'exception'.EXT,
        'think\Model'                   =>  CORE_PATH . 'model'.EXT,
        'think\Db'                      =>  CORE_PATH . 'db'.EXT,
        'think\Db\Driver'               =>  CORE_PATH . 'db/driver'.EXT,
        'think\Template'                =>  CORE_PATH . 'template'.EXT,
        'think\view\driver\Think'       =>  CORE_PATH . 'view\driver\think'.EXT,
        'think\template\driver\File'    =>  CORE_PATH . 'template\driver\file'.EXT,
        'think\Error'                   =>  CORE_PATH . 'error'.EXT,
        'think\Cache'                   =>  CORE_PATH . 'cache'.EXT,
        'think\cache\driver\File'       =>  CORE_PATH . 'cache/driver/file'.EXT,
        'think\Hook'                    =>  CORE_PATH . 'hook'.EXT,
        'think\Session'                 =>  CORE_PATH . 'session'.EXT,
        'think\Cookie'                  =>  CORE_PATH . 'cookie'.EXT,
        'think\Controller'              =>  CORE_PATH . 'controller'.EXT,
        'think\View'                    =>  CORE_PATH . 'view'.EXT,
        'think\Url'                     =>  CORE_PATH . 'url'.EXT,
        'think\Debug'                   =>  CORE_PATH . 'debug'.EXT,
        'think\Input'                   =>  CORE_PATH . 'input'.EXT,
        'think\Parser'                  =>  CORE_PATH . 'parser'.EXT,
        'think\Lang'                    =>  CORE_PATH . 'lang'.EXT,
    ],

    'init'       =>  [],
];
