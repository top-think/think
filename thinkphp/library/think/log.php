<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

class Log
{
    // 日志信息
    protected static $log     = [];
    protected static $level   = ['ERR', 'NOTIC', 'DEBUG', 'SQL', 'INFO'];
    protected static $storage = null;

    // 日志初始化
    public static function init($config = [])
    {
        $type  = isset($config['type']) ? $config['type'] : 'File';
        $class = '\\think\\log\\driver\\' . strtolower($type);
        unset($config['type']);
        self::$storage = new $class($config);
    }

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $record  是否强制记录
     * @return void
     */
    public static function record($message, $level = 'INFO')
    {
        self::$log[$level][] = "{$level}: {$message}";
    }

    /**
     * 获取内存中的日志信息
     * @access public
     * @param string $level  日志级别
     * @return array
     */
    public static function getLog($level = '')
    {
        return $level ? self::$log[$level] : self::$log;
    }

    /**
     * 日志保存
     * @access public
     * @param string $destination  写入目标
     * @param string $level 保存的日志级别
     * @return void
     */
    public static function save($destination = '', $level = '')
    {
        $log = self::getLog($level);
        if (empty($log)) {
            return;
        }
        $message = '';
        if ($level) {
            $message .= implode("\r\n", $log);
            self::$log[$level] = [];
        } else {
            foreach ($log as $info) {
                $message .= implode("\r\n", $info) . "\r\n";
            }
            self::$log = [];
        }
        self::$storage && self::$storage->write($message, $destination);
    }

    /**
     * 日志直接写入
     * @access public
     * @param string $log 日志信息
     * @param string $level  日志级别
     * @param string $destination  写入目标
     * @return void
     */
    public static function write($log, $level = '', $destination = '')
    {
        self::$storage && self::$storage->write("{$level}: {$log}", $destination);
    }
}
