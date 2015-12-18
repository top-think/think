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

/**
 * ThinkPHP 数据库中间层实现类
 */
class Db
{
    //  数据库连接实例
    private static $instance = [];
    //  当前数据库连接实例
    private static $_instance = null;

    /**
     * 取得数据库类实例
     * @static
     * @access public
     * @param mixed $config 连接配置
     * @param boolean $lite 是否lite方式
     * @return Object 返回数据库驱动类
     */
    public static function instance($config = [], $lite = false)
    {
        $md5 = md5(serialize($config));
        if (!isset(self::$instance[$md5])) {
            // 解析连接参数 支持数组和字符串
            $options = self::parseConfig($config);
            // 如果采用lite方式 仅支持原生SQL 包括query和execute方法
            $class                = $lite ? '\\think\\db\\Lite' : '\\think\\db\\driver\\' . ucwords($options['type']);
            self::$instance[$md5] = new $class($options);
        }
        self::$_instance = self::$instance[$md5];
        return self::$_instance;
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
            if (Config::get('use_db_switch')) {
                $status =   Config::get('app_status');
                $config =   $config[$status?:'default'];
            }
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
        if (empty($dsnStr)) {return false;}
        $info = parse_url($dsnStr);
        if (!$info) {
            return false;
        }
        $dsn = [
            'type'     => $info['scheme'],
            'username' => isset($info['user']) ? $info['user'] : '',
            'password' => isset($info['pass']) ? $info['pass'] : '',
            'hostname' => isset($info['host']) ? $info['host'] : '',
            'hostport' => isset($info['port']) ? $info['port'] : '',
            'database' => isset($info['path']) ? substr($info['path'], 1) : '',
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
        return call_user_func_array([self::$_instance, $method], $params);
    }
}
