<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: luofei614 <weibo.com/luofei614>
// +----------------------------------------------------------------------

/**
 * ThinkPHP SAE应用模式定义文件
 */
return [
    // 配置文件
    'config' => array_merge(include THINK_PATH . 'convention' . EXT, [
        /* 数据库设置 */
        'database'         => [
            // 数据库类型
            'type'        => 'mysql',
            'dsn'         => '', //
            // 服务器地址
            'hostname'    => SAE_MYSQL_HOST_M . ',' . SAE_MYSQL_HOST_S,
            // 数据库名
            'database'    => SAE_MYSQL_DB,
            // 用户名
            'username'    => SAE_MYSQL_USER,
            // 密码
            'password'    => SAE_MYSQL_PASS,
            // 端口
            'hostport'    => SAE_MYSQL_PORT,
            // 数据库连接参数
            'params'      => [],
            // 数据库编码默认采用utf8
            'charset'     => 'utf8',
            // 数据库表前缀
            'prefix'      => '',
            // 数据库调试模式
            'debug'       => false,
            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'      => 1,
            // 数据库读写是否分离 主从式有效
            'rw_separate' => true,
            // 读写分离后 主服务器数量
            'master_num'  => 1,
            // 指定从服务器序号
            'slave_no'    => '',
        ],
        'log'              => [
            'type' => 'Sae',
        ],
        'cache'            => [
            'type'   => 'Sae',
            'path'   => CACHE_PATH,
            'prefix' => '',
            'expire' => 0,
        ],
        'file_upload_type' => 'Sae',
        'template'         => [
            'compile_type' => 'Sae',
        ],
        'compile_type'     => 'Sae',
    ]),

    // 别名定义
    'alias'  => [
        'think\App'                  => CORE_PATH . 'app' . EXT,
        'think\Cache'                => CORE_PATH . 'cache' . EXT,
        'think\Config'               => CORE_PATH . 'config' . EXT,
        'think\Controller'           => CORE_PATH . 'controller' . EXT,
        'think\Cookie'               => CORE_PATH . 'cookie' . EXT,
        'think\Create'               => CORE_PATH . 'create' . EXT,
        'think\Db'                   => CORE_PATH . 'db' . EXT,
        'think\Debug'                => CORE_PATH . 'debug' . EXT,
        'think\Error'                => CORE_PATH . 'error' . EXT,
        'think\Exception'            => CORE_PATH . 'exception' . EXT,
        'think\Hook'                 => CORE_PATH . 'hook' . EXT,
        'think\Input'                => CORE_PATH . 'input' . EXT,
        'think\Lang'                 => CORE_PATH . 'lang' . EXT,
        'think\Log'                  => CORE_PATH . 'log' . EXT,
        'think\Model'                => CORE_PATH . 'model' . EXT,
        'think\Response'             => CORE_PATH . 'response' . EXT,
        'think\Route'                => CORE_PATH . 'route' . EXT,
        'think\Session'              => CORE_PATH . 'session' . EXT,
        'think\Template'             => CORE_PATH . 'template' . EXT,
        'think\Url'                  => CORE_PATH . 'url' . EXT,
        'think\View'                 => CORE_PATH . 'view' . EXT,
        'think\db\Driver'            => CORE_PATH . 'db' . DS . 'driver' . EXT,
        'think\view\driver\Think'    => CORE_PATH . 'view' . DS . 'driver' . DS . 'think' . EXT,
        'think\template\driver\File' => CORE_PATH . 'template' . DS . 'driver' . DS . 'file' . EXT,
        'think\log\driver\File'      => CORE_PATH . 'log' . DS . 'driver' . DS . 'file' . EXT,
        'think\cache\driver\File'    => CORE_PATH . 'cache' . DS . 'driver' . DS . 'file' . EXT,
        'think\log\driver\Sae'       => CORE_PATH . 'log' . DS . 'driver' . DS . 'sae' . EXT,
        'think\cache\driver\Sae'     => CORE_PATH . 'cache' . DS . 'driver' . DS . 'sae' . EXT,
        'think\template\driver\Sae'  => CORE_PATH . 'template' . DS . 'driver' . DS . 'sae' . EXT,
    ],

];
