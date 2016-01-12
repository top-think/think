<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 测试入口文件

define('IN_UNIT_TEST', true);

$_SERVER['REQUEST_METHOD'] = 'GET';

// 定义项目测试基础路径
define('TEST_PATH', __DIR__ . '/');

// 定义项目路径
define('APP_PATH', __DIR__ . '/../application/');
// 开启调试模式
define('APP_DEBUG', true);

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
