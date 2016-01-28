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
            throw new Exception('_NOT_SUPPERT_:memcache or memcached');
        }
        class Memcached extends Memcache
        {
            const OPT_CONNECT_TIMEOUT = 14;
            private $timeout          = 1000;

            public function addServers(array $servers = [])
            {
                if (empty($servers)) {
                    return;
                }

                foreach ($servers as $key => $server) {
                    if (empty($server[0])) {
                        continue;
                    }

                    $this->addServer(
                        $server[0],
                        !empty($server[1]) ? $server[1] : 11211,
                        true,
                        !empty($server[2]) ? $server[1] : 1,
                        $this->timeout > 0 ? ($this->timeout / 1000) : 1
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

namespace think\session\driver {
    use SessionHandler;

    class Memcached extends SessionHandler
    {
        protected $handler = null;
        protected $config  = [
            'host'         => '127.0.0.1', // memcache主机
            'port'         => 1121, // memcache端口
            'expire'       => 3600, // session有效期
            'timeout'      => 0, // 连接超时时间（单位：毫秒）
            'session_name' => '', // memcache key前缀
        ];

        public function __construct($config = [])
        {
            $this->config = array_merge($this->config, $config);
        }

        /**
         * 打开Session
         * @access public
         * @param string $savePath
         * @param mixed $sessName
         */
        public function open($savePath, $sessName)
        {
            $this->handler = new \Memcached;
            // 设置连接超时时间（单位：毫秒）
            if ($this->config['timeout'] > 0) {
                $this->handler->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $this->config['timeout']);
            }
            // 支持集群
            $hosts = explode(',', $this->config['host']);
            $ports = explode(',', $this->config['port']);
            if (empty($ports[0])) {
                $ports[0] = 11211;
            }
            // 建立连接
            $servers = [];
            foreach ((array) $hosts as $i => $host) {
                $servers[] = [$host, (isset($ports[$i]) ? $ports[$i] : $ports[0]), 1];
            }
            $this->handler->addServers($servers);
            return true;
        }

        /**
         * 关闭Session
         * @access public
         */
        public function close()
        {
            $this->gc(ini_get('session.gc_maxlifetime'));
            $this->handler->close();
            $this->handler = null;
            return true;
        }

        /**
         * 读取Session
         * @access public
         * @param string $sessID
         */
        public function read($sessID)
        {
            return $this->handler->get($this->config['session_name'] . $sessID);
        }

        /**
         * 写入Session
         * @access public
         * @param string $sessID
         * @param String $sessData
         */
        public function write($sessID, $sessData)
        {
            return $this->handler->set($this->config['session_name'] . $sessID, $sessData, $this->config['expire']);
        }

        /**
         * 删除Session
         * @access public
         * @param string $sessID
         */
        public function destroy($sessID)
        {
            return $this->handler->delete($this->config['session_name'] . $sessID);
        }

        /**
         * Session 垃圾回收
         * @access public
         * @param string $sessMaxLifeTime
         */
        public function gc($sessMaxLifeTime)
        {
            return true;
        }
    }
}
