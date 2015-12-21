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

namespace think\cache\driver;

/**
 * 数据库方式缓存驱动
 *    CREATE TABLE think_cache (
 *      cachekey varchar(255) NOT NULL,
 *      expire int(11) NOT NULL,
 *      data blob,
 *      datacrc int(32),
 *      UNIQUE KEY `cachekey` (`cachekey`)
 *    );
 * @author    liu21st <liu21st@gmail.com>
 */
class Db
{

    protected $handler = null;
    protected $options = [
        'db'     => '',
        'table'  => '',
        'prefix' => '',
        'expire' => 0,
        'length' => 0,
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
        $this->handler = \Think\Db::instance();
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        \think\Cache::$readTimes++;
        $name   = $this->options['prefix'] . addslashes($name);
        $result = $this->handler->query('SELECT `data`,`datacrc` FROM `' . $this->options['table'] . '` WHERE `cachekey`=\'' . $name . '\' AND (`expire` =0 OR `expire`>' . time() . ') LIMIT 0,1');
        if (false !== $result) {
            $result  = $result[0];
            $content = $result['data'];
            if (function_exists('gzcompress')) {
                //启用数据压缩
                $content = gzuncompress($content);
            }
            $content = unserialize($content);
            return $content;
        } else {
            return false;
        }
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value, $expire = null)
    {
        \think\Cache::$writeTimes++;
        $data = serialize($value);
        $name = $this->options['prefix'] . addslashes($name);
        if (function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data, 3);
        }
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $expire = (0 == $expire) ? 0 : (time() + $expire); //缓存有效期为0表示永久缓存
        $result = $this->handler->query('select `cachekey` from `' . $this->options['table'] . '` where `cachekey`=\'' . $name . '\' limit 0,1');
        if (!empty($result)) {
            //更新记录
            $result = $this->handler->execute('UPDATE ' . $this->options['table'] . ' SET data=\'' . $data . '\' ,expire=' . $expire . ' WHERE `cachekey`=\'' . $name . '\'');
        } else {
            //新增记录
            $result = $this->handler->execute('INSERT INTO ' . $this->options['table'] . ' (`cachekey`,`data`,`expire`) VALUES (\'' . $name . '\',\'' . $data . '\',' . $expire . ')');
        }
        if ($result) {
            if ($this->options['length'] > 0) {
                // 记录缓存队列
                $result = $this->handler->query('SELECT `data`,`datacrc` FROM `' . $this->options['table'] . '` WHERE `cachekey`=\'__info__\' AND `expire` =0 LIMIT 0,1');
                $queue  = xcache_get('__info__');
                if (!$result) {
                    $this->handler->execute('INSERT INTO ' . $this->options['table'] . ' (`cachekey`,`data`,`expire`) VALUES (\'__info__\',\'\',0)');
                    $queue = [];
                } else {
                    $queue = unserialize($result[0]['data']);
                }
                if (false === array_search($name, $queue)) {
                    array_push($queue, $name);
                }

                if (count($queue) > $this->options['length']) {
                    // 出列
                    $key = array_shift($queue);
                    // 删除缓存
                    $this->handler->execute('DELETE FROM `' . $this->options['table'] . '` WHERE `cachekey`=\'' . $key . '\'');
                }
                $this->handler->execute('UPDATE ' . $this->options['table'] . ' SET data=\'' . serialize($queue) . '\' ,expire=0 WHERE `cachekey`=\'__info__\'');
                xcache_set('__info__', $queue);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name)
    {
        $name = $this->options['prefix'] . addslashes($name);
        return $this->handler->execute('DELETE FROM `' . $this->options['table'] . '` WHERE `cachekey`=\'' . $name . '\'');
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear()
    {
        return $this->handler->execute('TRUNCATE TABLE `' . $this->options['table'] . '`');
    }

}
