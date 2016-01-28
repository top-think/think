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

namespace {
    // 检测php环境
    if (!extension_loaded('memcached')) {
        if (!extension_loaded('memcache')) {
            throw new Exception('_NOT_SUPPERT_:memcache or memcachd');
        }
        class Memcached extends Memcache
        {
            const OPT_CONNECT_TIMEOUT = 14;
            private $timeout = 1000;

            public function addServers(array $servers = [])
            {
                if(empty($servers)) return;
                foreach ($servers as $key => $server) {
                    if (empty($server[0])) continue;
                    $this->addServer(
                        $server[0], 
                        !empty($server[1]) ? $server[1] : 11211,
                        true, 
                        !empty($server[2]) ? $server[1] : 1,
                        $this->timeout > 0 ? ($this->timeout/1000) : 1
                    );
                }
            }

            public function setOption(int $option, mixed $value)
            {
                switch ($option) {
                    case self::OPT_CONNECT_TIMEOUT:
                        $this->timeout = $value;
                        break;
                    default:
                        break;
                }
            }

            public function set(string $key, mixed $value, $expiration = 0)
            {
                return $this->set($key, $value, MEMCACHE_COMPRESSED, $expiration);
            }
        }
    }
}

namespace think\cache\driver {
    use think\Cache;
    use think\Exception;

    class Memcached
    {
        protected $handler = null;
        protected $options = [
            'host'    => '127.0.0.1',
            'port'    => 11211,
            'expire'  => 0,
            'timeout' => 0, // 超时时间（单位：毫秒）
            'length'  => 0,
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
            $this->handler = new \Memcached;
            // 设置连接超时时间（单位：毫秒）
            if ($this->options['timeout'] > 0) {
                $this->handler->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $this->options['timeout']);
            }
            // 支持集群
            $hosts = explode(',', $this->options['host']);
            $ports = explode(',', $this->options['port']);
            if (empty($ports[0])) {
                $ports[0] = 11211;
            }
            // 建立连接
            $servers = [];
            foreach ((array) $hosts as $i => $host) {
                $servers[] = [$host, (isset($ports[$i]) ? $ports[$i] : $ports[0]), 1];
            }
            $this->handler->addServers($servers);
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
            return $this->handler->get($this->options['prefix'] . $name);
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
            if ($this->handler->set($name, $value, $expire)) {
                if ($this->options['length'] > 0) {
                    // 记录缓存队列
                    $queue = $this->handler->get('__info__');
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
                        $this->handler->delete($key);
                    }
                    $this->handler->set('__info__', $queue);
                }
                return true;
            }
            return false;
        }

        /**
         * 删除缓存
         *
         * @param    string  $name 缓存变量名
         * @param bool|false $ttl
         *
         * @return bool
         */
        public function rm($name, $ttl = false)
        {
            $name = $this->options['prefix'] . $name;
            return false === $ttl ?
            $this->handler->delete($name) :
            $this->handler->delete($name, $ttl);
        }

        /**
         * 清除缓存
         * @access public
         * @return bool
         */
        public function clear()
        {
            return $this->handler->flush();
        }
    }
}