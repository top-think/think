<?php

use think\facade\Env;

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------
    'app'      => [
        // 应用名称
        'app_name'                => '',
        // 应用地址
        'app_host'                => '',
        // 应用调试模式（环境变量优先）
        'app_debug'               => false,
        // 应用Trace（环境变量优先）
        'app_trace'               => false,
        // 默认输出类型
        'default_return_type'     => 'html',
        // 默认AJAX 数据返回格式,可选json xml ...
        'default_ajax_return'     => 'json',
        // 默认JSONP格式返回的处理方法
        'default_jsonp_handler'   => 'jsonpReturn',
        // 默认JSONP处理方法
        'var_jsonp_handler'       => 'callback',
        // 默认时区
        'default_timezone'        => 'Asia/Shanghai',
        // 是否开启多语言
        'lang_switch_on'          => false,
        // 默认语言
        'default_lang'            => 'zh-cn',
        // 默认验证器
        'default_validate'        => '',

        // 默认跳转页面对应的模板文件
        'dispatch_success_tmpl'   => Env::get('think_path') . 'tpl/dispatch_jump.tpl',
        'dispatch_error_tmpl'     => Env::get('think_path') . 'tpl/dispatch_jump.tpl',

        // +----------------------------------------------------------------------
        // | 异常及错误设置
        // +----------------------------------------------------------------------

        // 异常页面的模板文件
        'exception_tmpl'          => Env::get('think_path') . 'tpl/think_exception.tpl',
        // 错误显示信息,非调试模式有效
        'error_message'           => '页面错误！请稍后再试～',
        // 显示错误信息
        'show_error_msg'          => false,
        // 异常处理handle类 留空使用 \think\exception\Handle
        'exception_handle'        => '',
        // 异常响应输出类型
        'exception_response_type' => 'html',
    ],

    // +----------------------------------------------------------------------
    // | URL及路由设置
    // +----------------------------------------------------------------------
    'route'    => [
        // HTTPS代理标识
        'https_agent_name'      => '',
        // IP代理获取标识
        'http_agent_ip'         => 'HTTP_X_REAL_IP',
        // PATHINFO变量名 用于兼容模式
        'var_pathinfo'          => 's',
        // 兼容PATH_INFO获取
        'pathinfo_fetch'        => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
        // pathinfo分隔符
        'pathinfo_depr'         => '/',
        // URL伪静态后缀
        'url_html_suffix'       => 'html',
        // URL普通方式参数 用于自动生成
        'url_common_param'      => false,
        // 是否开启路由延迟解析
        'url_lazy_route'        => false,
        // 是否强制使用路由
        'url_route_must'        => false,
        // 合并路由规则
        'route_rule_merge'      => false,
        // 路由是否完全匹配
        'route_complete_match'  => false,
        // 使用注解路由
        'route_annotation'      => false,
        // 域名根，如thinkphp.cn
        'url_domain_root'       => '',
        // 是否自动转换URL中的控制器和操作名
        'url_convert'           => true,
        // 默认的路由变量规则
        'default_route_pattern' => '\w+',
        // 表单请求类型伪装变量
        'var_method'            => '_method',
        // 表单ajax伪装变量
        'var_ajax'              => '_ajax',
        // 表单pjax伪装变量
        'var_pjax'              => '_pjax',
        // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
        'request_cache'         => false,
        // 请求缓存有效期
        'request_cache_expire'  => null,
        // 全局请求缓存排除规则
        'request_cache_except'  => [],
        // 默认全局过滤方法
        'default_filter'        => [],
        // 默认控制器名
        'default_controller'    => 'Index',
        // 默认操作名
        'default_action'        => 'index',
        // 操作方法后缀
        'action_suffix'         => '',
    ],

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template' => [
        // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写
        'auto_rule'    => 1,
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 视图基础目录，配置目录为所有模块的视图起始目录
        'view_base'    => '',
        // 当前模板的视图目录 留空为自动获取
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DIRECTORY_SEPARATOR,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'      => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'         => 'File',

        // 日志记录级别
        'level'        => [],
        // 是否记录trace信息到日志
        'record_trace' => false,

        // 以下配置仅对文件方式日志有效
        // 是否为单日志文件
        'single'       => false,
        // 日志文件最大限制
        'file_size'    => 2097152,
        // 日志目录
        'path'         => '',
        // 独立日志文件类型
        'apart_level'  => [],
        // 最大日志数量
        'max_files'    => 0,
        // 是否JSON格式记录
        'json'         => false,
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace'    => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
        'file' => Env::get('think_path') . 'tpl/page_trace.tpl',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'    => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        //'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'  => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
        // Session配置参数
        'options'        => [
        ],
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'   => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    // +----------------------------------------------------------------------
    // | 数据库设置
    // +----------------------------------------------------------------------

    'database' => [
        // 数据库类型
        'type'            => 'mysql',
        // 数据库连接DSN配置
        'dsn'             => '',
        // 服务器地址
        'hostname'        => '127.0.0.1',
        // 数据库名
        'database'        => '',
        // 数据库用户名
        'username'        => 'root',
        // 数据库密码
        'password'        => '',
        // 数据库连接端口
        'hostport'        => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => '',
        // 数据库调试模式
        'debug'           => false,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy'          => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate'     => false,
        // 读写分离后 主服务器数量
        'master_num'      => 1,
        // 指定从服务器序号
        'slave_no'        => '',
        // 是否严格检查字段是否存在
        'fields_strict'   => true,
        // 数据集返回类型
        'resultset_type'  => 'array',
        // 自动写入时间戳字段
        'auto_timestamp'  => false,
        // 时间字段取出后的默认时间格式
        'datetime_format' => 'Y-m-d H:i:s',
        // 是否需要进行SQL性能分析
        'sql_explain'     => false,
        // 查询对象
        'query'           => '\\think\\db\\Query',
    ],

    //分页配置
    'paginate' => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],

    //控制台配置
    'console'  => [
        'name'    => 'Think Console',
        'version' => '0.1',
        'user'    => null,
    ],
];
