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
use think\facade\Env;

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用名称
    'app_name'                => '',
    // 应用地址
    'app_host'                => '',
    // 应用调试模式
    'app_debug'               => false,
    // 应用Trace
    'app_trace'               => false,
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
];
