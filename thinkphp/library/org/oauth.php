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

namespace think;

// oauth登录接口
// <code>
// Oauth::connect('qq',['app_key'=>'','app_secret'=>'','callback'=>'','authorize'=>'']); // 链接QQ登录
// Oauth::login(); // 跳转到授权登录页面 或者 Oauth::login($callbackUrl);
// Oauth::call('api','params'); // 调用API接口
// </code>
class Oauth
{

    /**
     * 操作句柄
     * @var object
     * @access protected
     */
    protected static $handler = null;

    /**
     * 连接oauth
     * @access public
     * @param string $type  Oauth类型
     * @param array $options  配置数组
     * @return object
     */
    public static function connect($type, $options = [])
    {
        $class         = 'think\\oauth\\driver\\' . strtolower($type);
        self::$handler = new $class($options);
        return self::$handler;
    }

    // 跳转到授权登录页面
    public static function login($callback = '')
    {
        self::$handler->login($callback);
    }

    // 获取access_token
    public static function getAccessToken($code)
    {
        self::$handler->getAccessToken($code);
    }

    // 设置保存过的token信息
    public static function setToken($token)
    {
        self::$handler->setToken($token);
    }

    // 获取oauth用户信息
    public static function getOauthInfo()
    {
        return self::$handler->getOauthInfo();
    }

    // 获取openid信息
    public static function getOpenId()
    {
        return self::$handler->getOpenId();
    }

    // 调用oauth接口API
    public static function call($api, $param = '', $method = 'GET')
    {
        return self::$handler->call($api, $param, $method);
    }
}
