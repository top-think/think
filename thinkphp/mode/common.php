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
    ],

];
