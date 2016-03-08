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

/**
 * Shared Memory缓存驱动
 * @author    lloydzhou <lloydzhou@qq.com>
 */
class Shmcache
{

    protected $handler = null;
    protected $options = [
        'expire' => 3600, // 3600s ~ 1h
        'key' => 'shm_cache_key',
        'memsize' => 10000, // bytes
        'perm' => 0644,
    ];
    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->handler = shm_attach($this->options['key'], $this->options['memsize'], $this->options['perm']);
    }

    /**
     * 析构函数
     * @access public
     */
    public function __destruct(){
        shm_detach($this->handler);
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
        $variable_key = crc32($name);
        if ($this->handler && shm_has_var($this->handler, $variable_key)){
            $data = shm_get_var($this->handler, $variable_key);
            if (is_array($data) && $data[0] > time())
                return $data[1];
        }
        return null;
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

        // set expire and value, variable_key should to be integer
        return shm_put_var($this->handler, crc32($name), array(time() + $expire, $value));
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool|\string[]
     */
    public function rm($name)
    {
        return shm_remove_var($this->handler, crc32($name));
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear()
    {
        return shm_remove($this->handler);
    }
}
