<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Think;

// 内容解析类
class Transform {
    static private $handler = [];

    /**
     * 初始化解析驱动
     * @static 
     * @access private
     * @param  string $type 驱动类型
     */
    static private function init($type){
        if(!isset(self::$handler[$type])) {
            $class = '\\Think\\Transform\\Driver\\' . ucwords($type);
            self::$handler[$type] = new $class();
        }
    }

    /**
     * 编码内容
     * @static 
     * @access public
     * @param  mixed  $content 要编码的数据
     * @param  string $type    数据类型
     * @param  array  $config  XML配置参数，JSON格式生成无此参数
     * @return string          编码后的数据
     */
    static public function encode($content, $type, array $config = []){
        self::init($type);
        return self::$handler[$type]->encode($content, $config);
    }

    /**
     * 解码数据
     * @param  string  $content 要解码的数据
     * @param  string  $type    数据类型
     * @param  boolean $assoc   是否返回数组
     * @param  array   $config  XML配置参数，JSON格式解码无此参数
     * @return mixed            解码后的数据
     */
    static public function decode($content, $type, $assoc = true, array $config = []){
        self::init($type);
        return self::$handler[$type]->decode($content, $assoc, $config);
    }

    // 调用驱动类的方法
    // Transform::xmlEncode('abc')
    // Transform::jsonDecode('abc', true);
    static public function __callStatic($method, $params){
        $type   = substr($method, 0, strlen($method) - 6);
        $method = strtolower(substr($method, -6));

        switch ($method) {
            case 'encode':
                $config = empty($params[1]) ? []   : $params[1];
                return self::encode($params[0], $type, $config);
            case 'decode':
                $assoc  = empty($params[1]) ? true : $params[1];
                $config = empty($params[2]) ? []   : $params[2];
                return self::decode($params[0], $type, $assoc, $config);
            default:
                throw new Exception("call to undefined method {$method}");
        }
    }
}
