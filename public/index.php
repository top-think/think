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

// 使用composer自动加载
require __DIR__ . '/../vendor/autoload.php';

// 不使用composer自动加载
// 加载基础文件
//require __DIR__ . '/../thinkphp/base.php';

// 执行应用并响应
(new App())->run()->send();
