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

namespace think\session\driver;

use SessionHandler;
use think\Exception;

class Redis extends SessionHandler
{
    protected $handler = null;
    protected $config  = [
        'host'         => '127.0.0.1', // 主机
        'port'         => 6379, // 端口
        'password'     => '', // 密码
        'expire'       => 3600, // 有效期
        'timeout'      => false, // 超时时间
        'persistent'   => true, // 是否长连接
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
        // 检测php环境
        if (!extension_loaded('redis')) {
            throw new Exception('_NOT_SUPPERT_:redis');
        }
        $this->handler = new \Redis;
        // 建立连接
        $func = $this->config['persistent'] ? 'pconnect' : 'connect';
        false === $this->config['timeout'] ?
        $this->handler->$func($this->config['host'], $this->config['port']) :
        $this->handler->$func($this->config['host'], $this->config['port'], $this->config['timeout']);
        if ('' != $this->config['password']) {
            $this->handler->auth($this->config['password']);
        }
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
        return $this->handler->set($this->config['session_name'] . $sessID, $sessData, 0, $this->config['expire']);
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
