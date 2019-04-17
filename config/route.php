<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'          => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'        => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'         => '/',
    // HTTPS代理标识
    'https_agent_name'      => '',
    // URL伪静态后缀
    'url_html_suffix'       => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'      => true,
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
    // 是否开启路由缓存
    'route_check_cache'     => false,
    // 路由缓存连接参数
    'route_cache_option'    => [],
    // 路由缓存Key
    'route_check_cache_key' => '',
    // 访问控制器层名称
    'controller_layer'      => 'controller',
    // 空控制器名
    'empty_controller'      => 'Error',
    // 是否使用控制器后缀
    'controller_suffix'     => false,
    // 默认的路由变量规则
    'default_route_pattern' => '[\w\.]+',
    // 域名根，如thinkphp.cn
    'url_domain_root'       => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'           => true,
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
    // 默认控制器名
    'default_controller'    => 'Index',
    // 默认操作名
    'default_action'        => 'index',
    // 操作方法后缀
    'action_suffix'         => '',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler' => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'     => 'callback',
];
