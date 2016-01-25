<?php
namespace think\session\driver;

use think\session\Driver;
use think\Config;

class Memcache extends Driver
{
    protected $handle       = null;
    protected $config       = [
        'expire'        => 3600,                // 有效期
        'timeout'       => 1,                   // 超时时间
        'persistent'    => 0,                   // 是否长连接
        'connections'   => '127.0.0.1:11211',   // 服务器连接配置 OR ['127.0.0.1:11211', '127.0.0.1:11212']
        'session_name'  => '',                  // memcache key前缀
    ];

    /**
     * 打开Session 
     * @access public 
     * @param string $savePath 
     * @param mixed $sessName  
     */
    public function open($savePath, $sessName) {
        $this->handle       = new \Memcache;
        $connections        = is_array($connections) ? $connections : explode(',', $connections);
        foreach ($connections as $connection) {
            list($host, $port)  = explode(':', $connection);
            $this->handle->addServer($host, $port, $this->config['persistent'], 1, $this->config['timeout']);
        }
        return true;
    }

    /**
     * 关闭Session 
     * @access public 
     */
    public function close() {
        $this->gc(ini_get('session.gc_maxlifetime'));
        $this->handle->close();
        $this->handle       = null;
        return true;
    }

    /**
     * 读取Session 
     * @access public 
     * @param string $sessID 
     */
    public function read($sessID) {
        return $this->handle->get($this->config['session_name'].$sessID);
    }

    /**
     * 写入Session 
     * @access public 
     * @param string $sessID 
     * @param String $sessData  
     */
    public function write($sessID, $sessData) {
        return $this->handle->set($this->config['session_name'].$sessID, $sessData, 0, $this->config['expire']);
    }

    /**
     * 删除Session 
     * @access public 
     * @param string $sessID 
     */
    public function destroy($sessID) {
        return $this->handle->delete($this->config['session_name'].$sessID);
    }

    /**
     * Session 垃圾回收
     * @access public 
     * @param string $sessMaxLifeTime 
     */
    public function gc($sessMaxLifeTime) {
        return true;
    }
}
