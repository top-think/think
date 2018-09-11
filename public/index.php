<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/topthink/framework/src/library/think/Loader.php';

// 注册框架自动加载
Loader::register();

// 如果需要使用composer自动加载
// Loader::register(true);
// 或者纯粹使用composer自动加载
// require __DIR__ . '/../vendor/autoload.php';

// 执行应用并响应
(new App())->run()->send();
