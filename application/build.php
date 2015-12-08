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

return [
    // 生成运行时目录
    'runtime' => [
        '__dir__' => ['cache', 'log', 'temp'],
    ],
    // 定义index模块的自动生成
    'index'   => [
        '__file__'   => ['config.php', 'alias.php', 'common.php'],
        '__dir__'    => ['behavior', 'controller', 'model', 'view'],
        'controller' => ['Index', 'Test', 'UserType'],
        'model'      => [],
        'view'       => ['index/index'],
    ],
    // 。。。 其他更多的模块定义
];
