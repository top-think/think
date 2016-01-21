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

class Session
{

    protected static $prefix = '';

    /**
     * 设置或者获取session作用域（前缀）
     *
     * @param string $prefix
     * @return string|void
     */
    public static function prefix($prefix = '')
    {
        if (empty($prefix) && null !== $prefix) {
            return self::$prefix;
        } else {
            self::$prefix = $prefix;
        }
    }

    /**
     * session初始化
     *
     * @param array $config
     * @return void
     */
    public static function init(array $config = [])
    {
        $isDoStart = false;
        if (isset($config['use_trans_sid'])) {
            ini_set('session.use_trans_sid', $config['use_trans_sid'] ? 1 : 0);
        }

        // 启动session
        if (!empty($config['auto_start']) && PHP_SESSION_ACTIVE != session_status()) {
            ini_set('session.auto_start', 0);
            $isDoStart = true;
        }

        if (isset($config['prefix'])) {
            self::$prefix = $config['prefix'];
        }
        if ($config['var_session_id'] && isset($_REQUEST[$config['var_session_id']])) {
            session_id($_REQUEST[$config['var_session_id']]);
        } elseif (isset($config['id']) && !empty($config['id'])) {
            session_id($config['id']);
        }
        if (isset($config['name'])) {
            session_name($config['name']);
        }
        if (isset($config['path'])) {
            session_save_path($config['path']);
        }
        if (isset($config['domain'])) {
            ini_set('session.cookie_domain', $config['domain']);
        }
        if (isset($config['expire'])) {
            ini_set('session.gc_maxlifetime', $config['expire']);
            ini_set('session.cookie_lifetime', $config['expire']);
        }

        if (isset($config['use_cookies'])) {
            ini_set('session.use_cookies', $config['use_cookies'] ? 1 : 0);
        }
        if (isset($config['cache_limiter'])) {
            session_cache_limiter($config['cache_limiter']);
        }
        if (isset($config['cache_expire'])) {
            session_cache_expire($config['cache_expire']);
        }
        if (!empty($config['type'])) {
            // 读取session驱动
            $class = (!empty($config['namespace']) ? $config['namespace'] : '\\think\\session\\driver\\') . ucwords($config['type']);

            // 检查驱动类
            if (!class_exists($class) || !session_set_save_handler(new $class())) {
                throw new \think\Exception('error session handler', 11700);
            }
        }
        if ($isDoStart) {
            session_start();
        }
    }

    /**
     * session设置
     *
     * @param string $name
     *            session名称
     * @param mixed $value
     *            session值
     * @param string $prefix
     *            作用域（前缀）
     * @return void
     */
    public static function set($name, $value = '', $prefix = '')
    {
        $prefix = $prefix ? $prefix : self::$prefix;
        if (strpos($name, '.')) {
            // 二维数组赋值
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                $_SESSION[$prefix][$name1][$name2] = $value;
            } else {
                $_SESSION[$name1][$name2] = $value;
            }
        } elseif ($prefix) {
            $_SESSION[$prefix][$name] = $value;
        } else {
            $_SESSION[$name] = $value;
        }
    }

    /**
     * session获取
     *
     * @param string $name
     *            session名称
     * @param string $prefix
     *            作用域（前缀）
     * @return mixed
     */
    public static function get($name = '', $prefix = '')
    {
        $prefix = $prefix ? $prefix : self::$prefix;
        if ('' == $name) {
            // 获取全部的session
            $value = $prefix ? $_SESSION[$prefix] : $_SESSION;
        } elseif ($prefix) {
            // 获取session
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                $value               = isset($_SESSION[$prefix][$name1][$name2]) ? $_SESSION[$prefix][$name1][$name2] : null;
            } else {
                $value = isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null;
            }
        } else {
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                $value               = isset($_SESSION[$name1][$name2]) ? $_SESSION[$name1][$name2] : null;
            } else {
                $value = isset($_SESSION[$name]) ? $_SESSION[$name] : null;
            }
        }
        return $value;
    }

    /**
     * 删除session数据
     *
     * @param string $name
     *            session名称
     * @param string $prefix
     *            作用域（前缀）
     * @return void
     */
    public static function delete($name, $prefix = '')
    {
        $prefix = $prefix ? $prefix : self::$prefix;
        if (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                unset($_SESSION[$prefix][$name1][$name2]);
            } else {
                unset($_SESSION[$name1][$name2]);
            }
        } else {
            if ($prefix) {
                unset($_SESSION[$prefix][$name]);
            } else {
                unset($_SESSION[$name]);
            }
        }
    }

    /**
     * 清空session数据
     *
     * @param string $prefix
     *            作用域（前缀）
     * @return void
     */
    public static function clear($prefix = '')
    {
        $prefix = $prefix ? $prefix : self::$prefix;
        if ($prefix) {
            unset($_SESSION[$prefix]);
        } else {
            $_SESSION = [];
        }
    }

    /**
     * 判断session数据
     *
     * @param string $name
     *            session名称
     * @param string $prefix
     *
     * @return bool
     * @internal param mixed $value session值
     */
    public static function has($name, $prefix = '')
    {
        $prefix = $prefix ? $prefix : self::$prefix;
        if (strpos($name, '.')) {
            // 支持数组
            list($name1, $name2) = explode('.', $name);
            return $prefix ? isset($_SESSION[$prefix][$name1][$name2]) : isset($_SESSION[$name1][$name2]);
        } else {
            return $prefix ? isset($_SESSION[$prefix][$name]) : isset($_SESSION[$name]);
        }
    }

    /**
     * 暂停session
     *
     * @return void
     */
    public static function pause()
    {
        // 暂停session
        session_write_close();
    }

    /**
     * 启动session
     *
     * @return void
     */
    public static function start()
    {
        session_start();
    }

    /**
     * 销毁session
     *
     * @return void
     */
    public static function destroy()
    {
        $_SESSION = [];
        session_unset();
        session_destroy();
    }

    /**
     * 重新生成session_id
     *
     * @return void
     */
    private static function regenerate()
    {
        session_regenerate_id();
    }
}
