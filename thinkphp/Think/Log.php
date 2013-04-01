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

namespace Think;
class Log {

    static protected $handler   =   null;

    // 日志初始化
    static public function init($config=[]){
        if(!empty($config['type'])) { // 读取log驱动
            $class      = 'Think\\Log\\Driver\\'. ucwords(strtolower($config['type']));
            // 检查驱动类
            if(class_exists($class)) {
                unset($config['type']);
                self::$handler = new $class($config);
                return self::$handler;
            }else {
                // 类没有定义
                E(L('_CLASS_NOT_EXIST_').': ' . $class);
            }
        }
    }
    
    // 调用驱动类的方法
	static public function __callStatic($method, $params){dump($params);
		return call_user_func_array(array(self::$handler, $method), $params);
	}

}