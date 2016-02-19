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

/**
 * ThinkPHP 数据库中间层实现类
 */
class Db
{
    //  数据库连接实例
    private static $instances = [];
    //  当前数据库连接实例
    private static $instance = null;
    // 查询次数
    public static $queryTimes = 0;
    // 执行次数
    public static $executeTimes = 0;

    /**
     * 数据库初始化 并取得数据库类实例
     * @static
     * @access public
     * @param mixed $config 连接配置
     * @return Object 返回数据库驱动类
     */
    public static function connect($config = [])
    {
        $md5 = md5(serialize($config));
        if (!isset(self::$instances[$md5])) {
            // 解析连接参数 支持数组和字符串
            $options = self::parseConfig($config);
            if (empty($options['type'])) {
                throw new Exception('db type error');
            }
            $class                 = (!empty($options['namespace']) ? $options['namespace'] : '\\think\\db\\driver\\') . ucwords($options['type']);
            self::$instances[$md5] = new $class($options);
            // 记录初始化信息
            APP_DEBUG && Log::record('[ DB ] INIT ' . $options['type'] . ':' . var_export($options, true), 'info');
        }
        self::$instance = self::$instances[$md5];
        return self::$instance;
    }

    /**
     * 数据库连接参数解析
     * @static
     * @access private
     * @param mixed $config
     * @return array
     */
    private static function parseConfig($config)
    {
        if (empty($config)) {
            $config = Config::get('database');
        } elseif (is_string($config) && false === strpos($config, '/')) {
            // 支持读取配置参数
            $config = Config::get($config);
        }
        if (is_string($config)) {
            return self::parseDsn($config);
        } else {
            return $config;
        }
    }

    /**
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName?param1=val1&param2=val2#utf8
     * @static
     * @access private
     * @param string $dsnStr
     * @return array
     */
    private static function parseDsn($dsnStr)
    {
        $info = parse_url($dsnStr);
        if (!$info) {
            return [];
        }
        $dsn = [
            'type'     => $info['scheme'],
            'username' => isset($info['user']) ? $info['user'] : '',
            'password' => isset($info['pass']) ? $info['pass'] : '',
            'hostname' => isset($info['host']) ? $info['host'] : '',
            'hostport' => isset($info['port']) ? $info['port'] : '',
            'database' => !empty($info['path']) ? ltrim($info['path'], '/') : '',
            'charset'  => isset($info['fragment']) ? $info['fragment'] : 'utf8',
        ];

        if (isset($info['query'])) {
            parse_str($info['query'], $dsn['params']);
        } else {
            $dsn['params'] = [];
        }
        return $dsn;
    }

    // 调用驱动类的方法
    public static function __callStatic($method, $params)
    {
        if (is_null(self::$instance)) {
            // 自动初始化数据库
            self::connect();
        }
        return call_user_func_array([self::$instance, $method], $params);
    }
}
