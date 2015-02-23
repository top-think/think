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

class Cache {
    /**
     * 操作句柄
     * @var object
     * @access protected
     */
    static protected $handler  =    null;

    /**
     * 连接缓存
     * @access public
     * @param array $options  配置数组
     * @return object
     */
    static public function connect($options=[]) {
        $type   =   !empty($options['type'])?$options['type']:'File';
        $class  =   'Think\\Cache\\Driver\\'.ucwords($type);
        self::$handler = new $class($options);
        return self::$handler;
    }

    static public function __callStatic($method, $params){
        return call_user_func_array(array(self::$handler, $method), $params);
    }
}
