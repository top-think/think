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
namespace Think;

/**
 * ThinkPHP 数据库中间层实现类
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 */
class Db {

    static private  $instance    =   [];
    /**
     * 取得数据库类实例
     * @static
     * @access public
     * @return mixed 返回数据库驱动类
     */
    public static function getInstance($config=[]) {
        $md5    =   md5(serialize($config));
        if(!isset(self::$instance[$md5])) {
            $options    =   self::parseConfig($config);
            $class      =   'Think\\Db\\Driver\\'.ucwords($options['dbms']);
            if(class_exists($class)) {
                self::$instance[$md5]    =   new $class($options);
            }else{
                Error::halt('_DB_TYPE_INVALID_:'.$options['dbms']);
            }
        }
        return self::$instance[$md5];
    }

    /**
     * Lite版本数据库引擎 仅支持原生SQL 包括query和execute方法
     * @static
     * @access public
     * @return mixed 返回数据库驱动类
     */
    public static function lite($config=[]) {
        static $_instance   =   [];
        $md5    =   md5(serialize($config));
        if(!isset($_instance[$md5])) {
            $_instance[$md5]    =   new Think\Db\Lite(self::parseConfig($config));
        }
        return $_instance[$md5];
    }

    static public function parseConfig($config=[]){
        if(empty($config)) {
            $config =   Config::get();
        }
        if(is_string($config)) {
            return self::parseDsn($config);
        }
        return    [
              'dbms'      =>  $config['db_type'],
              'dsn'       =>  $config['db_dsn'],
              'username'  =>  $config['db_user'],
              'password'  =>  $config['db_pwd'],
              'hostname'  =>  $config['db_host'],
              'hostport'  =>  $config['db_port'],
              'database'  =>  $config['db_name'],
              'params'    =>  $config['db_params'],
              'charset'   =>  $config['db_charset'],
              'deploy'    =>  $config['db_deploy'],
              'socket'    =>  $config['db_unix_socket'],
         ];
    }

    /**
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName?param1=val1&param2=val2#utf8
     * @static
     * @access public
     * @param string $dsnStr
     * @return array
     */
    static public function parseDsn($dsnStr) {
        if( empty($dsnStr) ){return false;}
        $info = parse_url($dsnStr);
        if(!$info) {
            return false;
        }
        $dsn = [
            'dbms'      =>  $info['scheme'],
            'username'  =>  isset($info['user']) ? $info['user'] : '',
            'password'  =>  isset($info['pass']) ? $info['pass'] : '',
            'hostname'  =>  isset($info['host']) ? $info['host'] : '',
            'hostport'  =>  isset($info['port']) ? $info['port'] : '',
            'database'  =>  isset($info['path']) ? substr($info['path'],1) : '',
            'charset'   =>  isset($info['fragment'])?$info['fragment']:'',
        ];
        $dsn['dsn'] =  ''; // 兼容配置信息数组
        if(isset($info['query'])) {
            parse_str($info['query'],$dsn['params']);
        }else{
            $dsn['params']  =   [];
        }
        return $dsn;
     }
}