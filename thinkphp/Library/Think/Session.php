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

namespace Think;

class Session {
    static protected $prefix    =   '';

    /**
     * 设置或者获取session作用域（前缀）
     * @param string $prefix
     * @return string|void
     */
    static public function prefix($prefix=''){
        if(empty($prefix)) {
            return self::$config['prefix'];
        }else{
            self::$config['prefix']   =   $prefix;
        }
    }

    /**
     * session初始化
     * @param array $config
     * @return void
     */
    static public function init($config=[]) {
        if(isset($config['prefix'])) self::$prefix  =   $config['prefix'];
        if(isset($config['var_session_id']) && isset($_REQUEST[$config['var_session_id']])){
            session_id($_REQUEST[$config['var_session_id']]);
        }elseif(isset($config['id'])) {
            session_id($config['id']);
        }
        ini_set('session.auto_start', 0);
        if(isset($config['name']))            session_name($config['name']);
        if(isset($config['path']))            session_save_path($config['path']);
        if(isset($config['domain']))          ini_set('session.cookie_domain', $config['domain']);
        if(isset($config['expire']))          ini_set('session.gc_maxlifetime', $config['expire']);
        if(isset($config['use_trans_sid']))   ini_set('session.use_trans_sid', $config['use_trans_sid']?1:0);
        if(isset($config['use_cookies']))     ini_set('session.use_cookies', $config['use_cookies']?1:0);
        if(isset($config['cache_limiter']))   session_cache_limiter($config['cache_limiter']);
        if(isset($config['cache_expire']))    session_cache_expire($config['cache_expire']);
        if(!empty($config['type'])) { // 读取session驱动
            $class      = 'Think\\Session\\Driver\\'. ucwords(strtolower($config['type']));
            // 检查驱动类
            session_set_save_handler(new $class());
        }
        // 启动session
        if($config['auto_start'])  session_start();
    }

    /**
     * session设置
     * @param string $name session名称
     * @param mixed $value session值
     * @param string $prefix 作用域（前缀）
     * @return void
     */
    static public function set($name,$value='',$prefix='') {
        $prefix =   $prefix?$prefix:self::$prefix;
        if($prefix){
            if (!is_array($_SESSION[$prefix])) {
                $_SESSION[$prefix] = [];
            }
            $_SESSION[$prefix][$name]   =  $value;
        }else{
            $_SESSION[$name]  =  $value;
        }
    }

    /**
     * session获取
     * @param string $name session名称
     * @param string $prefix 作用域（前缀）
     * @return mixed
     */
    static public function get($name,$prefix='') {
        $prefix   =  $prefix?$prefix:self::$prefix;
        if($prefix){ // 获取session
            return isset($_SESSION[$prefix][$name])?$_SESSION[$prefix][$name]:null;
        }else{
            return isset($_SESSION[$name])?$_SESSION[$name]:null;
        }
    }

    /**
     * 删除session数据
     * @param string $name session名称
     * @param string $prefix 作用域（前缀）
     * @return void
     */
    static public function delete($name,$prefix='') {
        $prefix   =  $prefix?$prefix:$this->prefix;
        if($prefix){
            unset($_SESSION[$prefix][$name]);
        }else{
            unset($_SESSION[$name]);
        }
    }

    /**
     * 清空session数据
     * @param string $prefix 作用域（前缀）
     * @return void
     */
    static public function clear($prefix='') {
        $prefix   =  $prefix?$prefix:self::$prefix;
        if($prefix) {
            unset($_SESSION[$prefix]);
        }else{
            $_SESSION = [];
        }
    }

    /**
     * 判断session数据
     * @param string $name session名称
     * @param mixed $value session值
     * @return boolean
     */
    static public function has($name,$prefix='') {
        $prefix   =  $prefix?$prefix:self::$prefix;
        if($prefix){
            return isset($_SESSION[$prefix][$name]);
        }else{
            return isset($_SESSION[$name]);
        }
    }

    /**
     * session管理
     * @param string $name session操作名称
     * @return void
     */
    static public function operate($name) {
        if('pause'==$name){ // 暂停session
            session_write_close();
        }elseif('start'==$name){ // 启动session
            session_start();
        }elseif('destroy'==$name){ // 销毁session
            $_SESSION =  [];
            session_unset();
            session_destroy();
        }elseif('regenerate'==$name){ // 重新生成id
            session_regenerate_id();
        }
    }

    static public function __callStatic($name,$args) {
        self::operate($name);
    }
}