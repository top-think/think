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
class Tag {

    static private $tags =   [];

    /**
     * 动态添加行为扩展到某个标签
     * @param string $tag 标签名称
     * @param mixed $behavior 行为名称
     * @return void
     */
    static public function add($tag,$behavior) {
        if(is_array($hehavior)) {
            self::$tags[$tag] =   array_merge(self::$tags[$tag],$hehavior);
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
    static public function listen($tag, &$params=NULL) {
        if(isset(self::$tags[$tag])) {
            foreach (self::$tags[$tag] as $val) {
                Debug::remark('behavior_start','time');
                $result =   self::exec($val, $params);
                Debug::remark('behavior_end','time');
                Log::record('Run '.$val.' Behavior [ RunTime:'.Debug::getUseTime('behavior_start','behavior_end').'s ]','INFO');
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
     * @param Mixed $params 传人的参数
     * @return void
     */
    static public function exec($name, &$params=NULL) {
        if($name instanceof \Closure) {
            return $name($params);
        }
        if(false === strpos($name,'\\')) {
            $class      =  '\\'.ucwords(MODULE_NAME).'\\Behavior\\'.$name;
        }else{
            $class      =  $name;
        }
        if(class_exists($class)) {
            $behavior   = new $class();
            return $behavior->run($params);
        }
        return ;
    }

}