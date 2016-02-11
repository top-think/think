<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP 普通模式定义
 */
return [
    // 命名空间
    'namespace' => [
        'think'       => LIB_PATH . 'think' . DS,
        'behavior'    => LIB_PATH . 'behavior' . DS,
        'traits'      => LIB_PATH . 'traits' . DS,
        APP_NAMESPACE => APP_PATH,
    ],

    // 配置文件
    'config'    => THINK_PATH . 'convention' . EXT,

    // 别名定义
    'alias'     => [
        'think\App'                  => CORE_PATH . 'App' . EXT,
        'think\Build'                => CORE_PATH . 'Build' . EXT,
        'think\Cache'                => CORE_PATH . 'Cache' . EXT,
        'think\Config'               => CORE_PATH . 'Config' . EXT,
        'think\Controller'           => CORE_PATH . 'Controller' . EXT,
        'think\Cookie'               => CORE_PATH . 'Cookie' . EXT,
        'think\Db'                   => CORE_PATH . 'Db' . EXT,
        'think\Debug'                => CORE_PATH . 'Debug' . EXT,
        'think\Error'                => CORE_PATH . 'Error' . EXT,
        'think\Exception'            => CORE_PATH . 'Exception' . EXT,
        'think\Hook'                 => CORE_PATH . 'Hook' . EXT,
        'think\Input'                => CORE_PATH . 'Input' . EXT,
        'think\Lang'                 => CORE_PATH . 'Lang' . EXT,
        'think\Log'                  => CORE_PATH . 'Log' . EXT,
        'think\Model'                => CORE_PATH . 'Model' . EXT,
        'think\Response'             => CORE_PATH . 'Response' . EXT,
        'think\Route'                => CORE_PATH . 'Route' . EXT,
        'think\Session'              => CORE_PATH . 'Session' . EXT,
        'think\Template'             => CORE_PATH . 'Template' . EXT,
        'think\Url'                  => CORE_PATH . 'Url' . EXT,
        'think\View'                 => CORE_PATH . 'View' . EXT,
        'think\db\Driver'            => CORE_PATH . 'db' . DS . 'Driver' . EXT,
        'think\view\driver\Think'    => CORE_PATH . 'view' . DS . 'driver' . DS . 'Think' . EXT,
        'think\template\driver\File' => CORE_PATH . 'template' . DS . 'driver' . DS . 'File' . EXT,
        'think\log\driver\File'      => CORE_PATH . 'log' . DS . 'driver' . DS . 'File' . EXT,
        'think\cache\driver\File'    => CORE_PATH . 'cache' . DS . 'driver' . DS . 'File' . EXT,
    ],
];
