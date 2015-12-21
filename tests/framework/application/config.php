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
// $Id$

return [
    'url_route_on' => true,
    'slog'         => [
        'host'                => '111.202.76.133',
        //是否显示利于优化的参数，如果允许时间，消耗内存等
        'optimize'            => true,
        'show_included_files' => true,
        'error_handler'       => true,
        //日志强制记录到配置的client_id
        'force_client_id'     => '',
        //限制允许读取日志的client_id
        'allow_client_ids'    => [],
    ],
];
