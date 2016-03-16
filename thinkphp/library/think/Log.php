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

namespace think;

class Log
{
    const LOG    = 'log';
    const ERROR  = 'error';
    const INFO   = 'info';
    const SQL    = 'sql';
    const NOTICE = 'notice';
    const ALERT  = 'alert';

    // 日志信息
    protected static $log = [];
    // 日志类型
    protected static $type = ['log', 'error', 'info', 'sql', 'notice', 'alert'];
    // 日志写入驱动
    protected static $driver = null;
    // 通知发送驱动
    protected static $alarm = null;

    // 日志初始化
    public static function init($config = [])
    {
        $type  = isset($config['type']) ? $config['type'] : 'File';
        $class = (!empty($config['namespace']) ? $config['namespace'] : '\\think\\log\\driver\\') . ucwords($type);
        unset($config['type']);
        self::$driver = new $class($config);
        // 记录初始化信息
        APP_DEBUG && Log::record('[ LOG ] INIT ' . $type . ': ' . var_export($config, true), 'info');
    }

    // 通知初始化
    public static function alarm($config = [])
    {
        $type  = isset($config['type']) ? $config['type'] : 'Email';
        $class = (!empty($config['namespace']) ? $config['namespace'] : '\\think\\log\\alarm\\') . ucwords($type);
        unset($config['type']);
        self::$alarm = new $class($config['alarm']);
        // 记录初始化信息
        APP_DEBUG && Log::record('[ CACHE ] ALARM ' . $type . ': ' . var_export($config, true), 'info');
    }

    /**
     * 获取全部日志信息
     * @return array
     */
    public static function getLog()
    {
        return self::$log;
    }

    /**
     * 记录调试信息
     * @param mixed $msg 调试信息
     * @param string $type 信息类型
     * @return void
     */
    public static function record($msg, $type = 'log')
    {
        if (!is_string($msg)) {
            $msg = var_export($msg, true);
        }
        self::$log[] = ['type' => $type, 'msg' => $msg];
    }

    /**
     * 清空日志信息
     * @return void
     */
    public static function clear()
    {
        self::$log = [];
    }

    /**
     * 保存调试信息
     * @return bool
     */
    public static function save()
    {
        if (is_null(self::$driver)) {
            self::init(Config::get('log'));
        }
        return self::$driver->save(self::$log);
    }

    /**
     * 实时写入日志信息 并支持行为
     * @param mixed $msg 调试信息
     * @param string $type 信息类型
     * @return bool
     */
    public static function write($msg, $type = 'log')
    {
        if (!is_string($msg)) {
            $msg = var_export($msg, true);
        }
        // 封装日志信息
        $log[] = ['type' => $type, 'msg' => $msg];

        // 监听log_write
        APP_HOOK && Hook::listen('log_write', $log);
        if (is_null(self::$driver)) {
            self::init(Config::get('log'));
        }
        // 写入日志
        return self::$driver->save($log);
    }

    /**
     * 发送预警通知
     * @param mixed $msg 调试信息
     * @return void
     */
    public static function send($msg)
    {
        self::$alarm && self::$alarm->send($msg);
    }

    // 静态调用
    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::$type)) {
            array_push($args, $method);
            return call_user_func_array('\\think\\Log::record', $args);
        }
    }

}
