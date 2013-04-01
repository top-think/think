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
// 内容解析类
class Parser {

    // 解析内容
    static public function parse($content,$type){
        $class  =   '\Think\Parser\Driver\\'.ucwords($type);
        if(class_exists($class)) {
            $parse  =   new $class();
            return $parse->parse($content);
        }else{
            E(L('_CLASS_NOT_EXIST_').': ' . $class);
        }
    }

    // 调用驱动类的方法
	static public function __callStatic($method, $params){
        return self::parse($params[0],$method);
	}
}