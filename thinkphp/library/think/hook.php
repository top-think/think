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

class Hook {

    static private $tags =   [];

    /**
     * 动态添加行为扩展到某个标签
     * @param string $tag 标签名称
     * @param mixed $behavior 行为名称
     * @return void
     */
    static public function add($tag,$behavior) {
        if(is_array($behavior)) {
            self::$tags[$tag]   =   array_merge(self::$tags[$tag],$behavior);
        }else{
            self::$tags[$tag][] =   $behavior;
        }
    }

    /**
     * 批量导入行为
     * @param array $tags 标签行为
     * @return void
     */
    static public function import($tags) {
        self::$tags =   array_merge(self::$tags,$tags);
    }

    /**
     * 监听标签的行为
     * @param string $tag 标签名称
     * @param mixed $params 传入参数
     * @return void
     */
    static public function listen($tag, &$params=null) {
        if(isset(self::$tags[$tag])) {
            foreach (self::$tags[$tag] as $name) {

                Config::get('app_debug') && Debug::remark('behavior_start','time');

                $result =   self::exec($name, $tag,$params);

                if(Config::get('app_debug')){
                    Debug::remark('behavior_end','time');
                    Log::record('Run '.$name.' [ RunTime:'.Debug::getUseTime('behavior_start','behavior_end').'s ]','INFO');
                }
                if(false === $result) {
                    // 如果返回false 则中断行为执行
                    return ;
                }
            }
        }
        return;
    }

    /**
     * 执行某个行为
     * @param string $name 行为名称
     * @param string $tag 方法名（标签名）    
     * @param Mixed $params 传人的参数
     * @return void
     */
    static public function exec($name, $tag,&$params=null) {
        if($name instanceof \Closure) {
            return $name($params);
        }
        $addon   = new $name();
        return method_exists($addon,$tag) ? $addon->$tag($params): $addon->run($params);
    }
}
