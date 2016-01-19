<?php

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用模式状态
    'app_status'            => '',
    // 扩展配置文件
    'extra_config_list'     => ['database', 'route'],
    // 扩展函数文件
    'extra_file_list'       => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type'   => 'html',
    // 默认语言
    'default_lang'          => 'zh-cn',
    // response是否返回方式
    'response_return'       => false,
    // 默认AJAX 数据返回格式,可选JSON XML ...
    'default_ajax_return'   => 'JSON',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler' => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'     => 'callback',
    // 默认时区
    'default_timezone'      => 'PRC',
    // 是否开启多语言
    'lang_switch_on'        => false,
    // 支持的多语言列表
    'lang_list'             => ['zh-cn'],
    // 语言变量
    'lang_detect_var'       => 'lang',
    // 语言cookie变量
    'lang_cookie_var'       => 'think_lang',

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'        => 'index',
    // 禁止访问模块
    'deny_module_list'      => [COMMON_MODULE, 'runtime'],
    // 默认控制器名
    'default_controller'    => 'Index',
    // 默认操作名
    'default_action'        => 'index',
    // 默认的空控制器名
    'empty_controller'      => 'Error',
    // 操作方法后缀
    'action_suffix'         => '',
    // 操作绑定到类
    'action_bind_class'     => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'          => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'        => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'         => '/',
    // 获取当前页面地址的系统变量 默认为REQUEST_URI
    'url_request_uri'       => 'REQUEST_URI',
    // 基础URL路径
    'base_url'              => $_SERVER["SCRIPT_NAME"],
    // URL伪静态后缀
    'url_html_suffix'       => '.html',
    // URL普通方式参数 用于自动生成
    'url_common_param'      => false,
    // url变量绑定
    'url_params_bind'       => true,
    // URL变量绑定的类型 0 按变量名绑定 1 按变量顺序绑定
    'url_parmas_bind_type'  => 0,
    //url禁止访问的后缀
    'url_deny_suffix'       => 'ico|png|gif|jpg',
    // 是否开启路由
    'url_route_on'          => true,
    // 是否强制使用路由
    'url_route_must'        => false,
    // URL模块映射
    'url_module_map'        => [],
    // 域名部署
    'url_domain_deploy'     => false,
    // 域名根，如.thinkphp.cn
    'url_domain_root'       => '',
    // 是否开启 rest 操作方法
    'url_rest_action'       => false,

    // +----------------------------------------------------------------------
    // | 视图及模板设置
    // +----------------------------------------------------------------------

    // 默认跳转页面对应的模板文件
    'dispatch_jump_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    // 默认的模板引擎
    'template_engine'       => 'Think',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'        => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',
    // 错误显示信息,非调试模式有效
    'error_message'         => '页面错误！请稍后再试～',
    // 错误定向页面
    'error_page'            => '',
    // 显示错误信息
    'show_error_msg'        => false,

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'                   => [
        'type' => 'File', // 支持 file socket trace sae
        'path' => LOG_PATH,
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                 => [
        'type'   => 'File',
        'path'   => CACHE_PATH,
        'prefix' => '',
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    // 是否使用session
    'use_session'           => true,
    'session'               => [
        'id'             => '',
        'var_session_id' => '', // SESSION_ID的提交变量,解决flash上传跨域
        'prefix'         => 'think',
        'type'           => '',
        'auto_start'     => true,
    ],

    // +----------------------------------------------------------------------
    // | 数据库设置
    // +----------------------------------------------------------------------

    // 是否启用多状态数据库配置 如果启用的话 需要跟随app_status配置不同的数据库信息
    'use_db_switch'         => false,
    'db_fields_strict'      => true,
    'database'              => [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => 'localhost',
        // 数据库名
        'database'    => '',
        // 数据库用户名
        'username'    => 'root',
        // 数据库密码
        'password'    => '',
        // 数据库连接端口
        'hostport'    => '',
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
        // 数据库调试模式
        'debug'       => false,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy'      => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate' => false,
        // 读写分离后 主服务器数量
        'master_num'  => 1,
        // 指定从服务器序号
        'slave_no'    => '',
    ],

];
