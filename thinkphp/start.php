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

// 加载环境变量配置文件
if (is_file(ROOT_PATH . 'env' . EXT)) {
    $env = include ROOT_PATH . 'env' . EXT;
    foreach ($env as $key => $val) {
        $name = ENV_PREFIX . $key;
        putenv("$name=$val");
    }
}
// 自动识别调试模式
if (!defined('APP_DEBUG')) {
    $debug = getenv(ENV_PREFIX . 'APP_DEBUG');
    define('APP_DEBUG', $debug);
}

// 加载模式定义文件
$mode = require MODE_PATH . APP_MODE . EXT;

// 加载模式命名空间定义
if (isset($mode['namespace'])) {
    Loader::addNamespace($mode['namespace']);
}

// 注册自动加载
Loader::register();

// 加载模式别名定义
if (isset($mode['alias'])) {
    Loader::addMap($mode['alias']);
}

// 注册错误和异常处理机制
Error::register();

// 加载模式配置文件
if (isset($mode['config'])) {
    is_array($mode['config']) ? Config::set($mode['config']) : Config::load($mode['config']);
}

// 加载模式行为定义
if (APP_HOOK && isset($mode['tags'])) {
    Hook::import($mode['tags']);
}

// 是否自动运行
if (APP_AUTO_RUN) {
    App::run();
}
