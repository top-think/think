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
    'app_debug'         =>  true,  // 调试模式
    'app_status'        =>  'debug',// 调试模式状态
    'var_module'        =>  'm',    // 模块变量名
    'var_controller'    =>  'c',    // 控制器变量名
    'var_action'        =>  'a',    // 操作变量名
    'var_pathinfo'      =>  's',    // PATHINFO变量名 用于兼容模式
    'pathinfo_fetch'    =>  'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL',
    'pathinfo_depr'     =>  '/',    // pathinfo分隔符
    'require_module'    =>  true,   // 是否显示模块
    'default_module'    =>  'index',  // 默认模块名
    'require_controller'  =>  true,   // 是否显示控制器
    'default_controller'  =>  'index',    // 默认控制器名
    'default_action'    =>  'index',    // 默认操作名
    'action_suffix'     =>  '', // 操作方法后缀
    'file_ext'          =>  '.php', // 文件后缀
    'url_model'         =>  1,  // URL模式
    'base_url'          =>  $_SERVER["SCRIPT_NAME"],    // 基础URL路径
    'url_html_suffix'   =>  '.html',
    'url_route'         =>  true,   // 是否开启路由
    'url_route_rules'   =>  '',     // 路由规则
    'url_params_bind'   =>  false,  // url变量绑定
    'app_autoload_path' =>  '',     // 自动加载搜索路径
    'app_domain_deploy' =>  false,  // 开启域名部署
    'app_domain_rules'  =>  '',     // 域名部署规则
    'app_doamin_deny'   =>  '',     // 域名禁止列表
    'exception_tmpl'    =>  THINK_PATH.'tpl/think_exception.tpl',// 异常页面的模板文件
    'http_cache_control'    =>  'private',

    /* 错误设置 */
    'ERROR_MESSAGE'         => '页面错误！请稍后再试～',//错误显示信息,非调试模式有效
    'ERROR_PAGE'            => '',	// 错误定向页面
    'SHOW_ERROR_MSG'        => false,    // 显示错误信息
    'TRACE_EXCEPTION'       => false,   // TRACE错误信息是否抛异常 针对trace方法 

    /* 日志设置 */
    'LOG_RECORD'            => false,   // 默认不记录日志
    'LOG_TYPE'              => 'file', // 日志记录类型 0 系统 1 邮件 3 文件 4 SAPI 默认为文件方式
    'LOG_LEVEL'             => 'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
    'LOG_FILE_SIZE'         => 2097152,	// 日志文件大小限制
    'LOG_EXCEPTION_RECORD'  => false,    // 是否记录异常信息日志
    'log_path'              =>  LOG_PATH,

    /* 数据库设置 */
    'DB_TYPE'               => 'mysql',     // 数据库类型
    'DB_HOST'               => 'localhost', // 服务器地址
    'DB_NAME'               => '',          // 数据库名
    'DB_USER'               => 'root',      // 用户名
    'DB_PWD'                => '',          // 密码
    'DB_PORT'               => '',        // 端口
    'db_prefix'             => 'think_',    // 数据库表前缀
    'DB_FIELDTYPE_CHECK'    => false,       // 是否进行字段类型检查
    'DB_FIELDS_CACHE'       => true,        // 启用字段缓存
    'DB_CHARSET'            => 'utf8',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        => 0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        => false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         => 1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           => '', // 指定从服务器序号
    'DB_SQL_BUILD_CACHE'    => false, // 数据库查询的SQL创建缓存
    'DB_SQL_BUILD_QUEUE'    => 'file',   // SQL缓存队列的缓存方式 支持 file xcache和apc
    'DB_SQL_BUILD_LENGTH'   => 20, // SQL缓存的队列长度
    'DB_SQL_LOG'            => false, // SQL执行日志记录
];