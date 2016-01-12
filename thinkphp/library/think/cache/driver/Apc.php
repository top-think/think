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

namespace think\cache\driver;

use think\Cache;
use think\Exception;

/**
 * Apc缓存驱动
 * @author    liu21st <liu21st@gmail.com>
 */
class Apc
{

    protected $options = [
        'expire' => 0,
        'prefix' => '',
        'length' => 0,
    ];
    /*****************************
    需要支持apc_cli模式
     ******************************/
    /**
     * 架构函数
     *
     * @param array $options 缓存参数
     *
     * @throws Exception
     * @access public
     */
    public function __construct($options = [])
    {
        if (!function_exists('apc_cache_info')) {
            throw new Exception('_NOT_SUPPERT_:Apc');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        Cache::$readTimes++;
        return apc_fetch($this->options['prefix'] . $name);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return bool
     */
    public function set($name, $value, $expire = null)
    {
        Cache::$writeTimes++;
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $name = $this->options['prefix'] . $name;
        if ($result = apc_store($name, $value, $expire)) {
            if ($this->options['length'] > 0) {
                // 记录缓存队列
                $queue = apc_fetch('__info__');
                if (!$queue) {
                    $queue = [];
                }
                if (false === array_search($name, $queue)) {
                    array_push($queue, $name);
                }

                if (count($queue) > $this->options['length']) {
                    // 出列
                    $key = array_shift($queue);
                    // 删除缓存
                    apc_delete($key);
                }
                apc_store('__info__', $queue);
            }
        }
        return $result;
    }

    /**删除缓存
     * @access public
     *
     * @param string $name 缓存变量名
     *
     * @return bool|\string[]
     */
    public function rm($name)
    {
        return apc_delete($this->options['prefix'] . $name);
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear()
    {
        return apc_clear_cache('user');
    }
}
