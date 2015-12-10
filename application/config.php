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


think\Route::get('new/:id','New/read'); // 定义GET请求路由规则
think\Route::post('new/:id','New/update'); // 定义POST请求路由规则

return [
    'url_route_on' => true,
    'slog'                  => [
        'host'                => '111.202.76.133',
        //是否显示利于优化的参数，如果允许时间，消耗内存等
        'optimize'            => true,
        'show_included_files' => true,
        'error_handler'       => true,
        //日志强制记录到配置的client_id
        'force_client_id'     => isset($_REQUEST['slog_force_client_id'])?$_REQUEST['slog_force_client_id']:'',
        //限制允许读取日志的client_id
        'allow_client_ids'    => array(),
    ],
];

