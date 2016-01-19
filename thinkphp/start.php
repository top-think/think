<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

// ThinkPHP 引导文件
// 加载基础文件
require __DIR__ . '/base.php';
require CORE_PATH . 'Loader.php';

// 注册自动加载
Loader::register();

// 注册错误和异常处理机制
register_shutdown_function('think\Error::appShutdown');
set_error_handler('think\Error::appError');
set_exception_handler('think\Error::appException');

// 加载模式定义文件
$mode = require MODE_PATH . APP_MODE . EXT;

// 加载模式别名定义
if (isset($mode['alias'])) {
    Loader::addMap(is_array($mode['alias']) ? $mode['alias'] : include $mode['alias']);
}

// 加载模式配置文件
if (isset($mode['config'])) {
    is_array($mode['config']) ? Config::set($mode['config']) : Config::load($mode['config']);
}

// 加载模式行为定义
if (APP_HOOK && isset($mode['tags'])) {
    Hook::import(is_array($mode['tags']) ? $mode['tags'] : include $mode['tags']);
}

// 自动生成
if (APP_AUTO_BUILD && is_file(APP_PATH . 'build.php')) {
    Build::run(include APP_PATH . 'build.php');
}
