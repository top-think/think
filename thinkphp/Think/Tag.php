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
     * 给某个tag注册行为
     * @param string $tag 标签名称
     * @param array $behaviors 行为集
     * @return void
     */
    public static function register($tag,$behaviors) {
        if(isset(self::$tags[$tag])) {
            self::$tags[$tag] =   array_merge(self::$tags[$tag],$behaviors);
        }else{
            self::$tags[$tag] =   $behaviors;
        }
    }

    /**
     * 动态添加行为扩展到某个标签
     * @param string $tag 标签名称
     * @param mixed $behavior 行为名称
     * @return void
     */
    public static function add($tag,$behavior,$range='') {
        $range  =   $range?$range:ucwords(MODULE_NAME);
        self::$tags[$tag][] =   [$behavior,$range];
    }

    /**
     * 批量导入行为
     * @param array $tags 标签行为
     * @return void
     */
    public static function import($tags) {
        self::$tags =   array_merge(self::$tags,$tags);
    }

    /**
     * 监听标签的行为
     * @param string $tag 标签名称
     * @param mixed $params 传入参数
     * @return void
     */
    public static function listen($tag, &$params=NULL) {
        Log::record($tag,'INFO');
        Debug::remark($tag,'time');
        if(isset(self::$tags[$tag])) {
            foreach (self::$tags[$tag] as $val) {
                Debug::remark($val[0].'start','time');
                $result =   self::exec($val[0], $params,$val[1]);
                Log::record('Run '.$val[0].' Behavior [ RunTime:'.Debug::getUseTime($val[0].'start',$val[0].'end').'s ]','INFO');
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
    public static function exec($name, &$params=NULL,$range='') {
        $class      =  '\\'.$range.'\Behavior\\'.$name;
        if(class_exists($class)) {
            $behavior   = new $class();
            return $behavior->run($params);
        }
        return ;
    }

}