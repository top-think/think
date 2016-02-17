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

class Hook
{

    private static $tags = [];

    /**
     * 动态添加行为扩展到某个标签
     * @param string $tag 标签名称
     * @param mixed $behavior 行为名称
     * @param bool $first 是否放到开头执行
     * @return void
     */
    public static function add($tag, $behavior, $first = false)
    {
        if (!isset(self::$tags[$tag])) {
            self::$tags[$tag] = [];
        }
        if (is_array($behavior)) {
            self::$tags[$tag] = array_merge(self::$tags[$tag], $behavior);
        } elseif ($first) {
            array_unshift(self::$tags[$tag], $behavior);
        } else {
            self::$tags[$tag][] = $behavior;
        }
    }

    /**
     * 批量导入插件
     * @param array $data 插件信息
     * @param boolean $recursive 是否递归合并
     * @return void
     */
    public static function import(array $tags, $recursive = true)
    {
        if (!$recursive) {
            // 覆盖导入
            self::$tags = array_merge(self::$tags, $tags);
        } else {
            // 合并导入
            foreach ($tags as $tag => $val) {
                if (!isset(self::$tags[$tag])) {
                    self::$tags[$tag] = [];
                }

                if (!empty($val['_overlay'])) {
                    // 可以针对某个标签指定覆盖模式
                    unset($val['_overlay']);
                    self::$tags[$tag] = $val;
                } else {
                    // 合并模式
                    self::$tags[$tag] = array_merge(self::$tags[$tag], $val);
                }
            }
        }
    }

    /**
     * 获取插件信息
     * @param string $tag 插件位置 留空获取全部
     * @return array
     */
    public static function get($tag = '')
    {
        if (empty($tag)) {
            // 获取全部的插件信息
            return self::$tags;
        } else {
            return self::$tags[$tag];
        }
    }

    /**
     * 监听标签的行为
     * @param string $tag 标签名称
     * @param mixed $params 传入参数
     * @return void
     */
    public static function listen($tag, &$params = null)
    {
        if (isset(self::$tags[$tag])) {
            foreach (self::$tags[$tag] as $name) {

                if (APP_DEBUG) {
                    Debug::remark('behavior_start', 'time');
                }

                $result = self::exec($name, $tag, $params);

                if (APP_DEBUG) {
                    Debug::remark('behavior_end', 'time');
                    Log::record('[ BEHAVIOR ] Run ' . ($name instanceof \Closure ? 'Closure' : $name) . ' @' . $tag . ' [ RunTime:' . Debug::getRangeTime('behavior_start', 'behavior_end') . 's ]', 'info');
                }
                if (false === $result) {
                    // 如果返回false 则中断行为执行
                    return;
                }
            }
        }
        return;
    }

    /**
     * 执行某个行为
     * @param string $class 行为类名称
     * @param string $tag 方法名（标签名）
     * @param Mixed $params 传人的参数
     * @return mixed
     */
    public static function exec($class, $tag = '', &$params = null)
    {
        if ($class instanceof \Closure) {
            return $class($params);
        }
        $obj = new $class();
        return ($tag && is_callable([$obj, $tag])) ? $obj->$tag($params) : $obj->run($params);
    }
}
