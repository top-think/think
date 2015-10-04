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
    // 配置文件
    'config' => THINK_PATH . 'convention' . EXT,

    // 别名定义
    'alias'  => [
        'think\App'                  => CORE_PATH . 'app' . EXT,
        'think\Log'                  => CORE_PATH . 'log' . EXT,
        'think\log\driver\File'      => CORE_PATH . 'log/driver/file' . EXT,
        'think\Config'               => CORE_PATH . 'config' . EXT,
        'think\Route'                => CORE_PATH . 'route' . EXT,
        'think\Exception'            => CORE_PATH . 'exception' . EXT,
        'think\Model'                => CORE_PATH . 'model' . EXT,
        'think\Db'                   => CORE_PATH . 'db' . EXT,
        'think\Db\Driver'            => CORE_PATH . 'db/driver' . EXT,
        'think\Template'             => CORE_PATH . 'template' . EXT,
        'think\view\driver\Think'    => CORE_PATH . 'view\driver\think' . EXT,
        'think\template\driver\File' => CORE_PATH . 'template\driver\file' . EXT,
        'think\Error'                => CORE_PATH . 'error' . EXT,
        'think\Cache'                => CORE_PATH . 'cache' . EXT,
        'think\cache\driver\File'    => CORE_PATH . 'cache/driver/file' . EXT,
        'think\Hook'                 => CORE_PATH . 'hook' . EXT,
        'think\Session'              => CORE_PATH . 'session' . EXT,
        'think\Cookie'               => CORE_PATH . 'cookie' . EXT,
        'think\Controller'           => CORE_PATH . 'controller' . EXT,
        'think\View'                 => CORE_PATH . 'view' . EXT,
        'think\Url'                  => CORE_PATH . 'url' . EXT,
        'think\Debug'                => CORE_PATH . 'debug' . EXT,
        'think\Input'                => CORE_PATH . 'input' . EXT,
        'think\Parser'               => CORE_PATH . 'parser' . EXT,
        'think\Lang'                 => CORE_PATH . 'lang' . EXT,
    ],

    'init'   => [],
];
