<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace think\oauth\driver;

use think\oauth\Driver;

class Baidu extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://openapi.baidu.com/oauth/2.0/authorize';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://openapi.baidu.com/oauth/2.0/token';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://openapi.baidu.com/rest/2.0/';

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    百度API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET')
    {
        /* 百度调用公共参数 */
        $params = array(
            'access_token' => $this->token['access_token'],
        );

        $data = $this->http($this->url($api), $this->param($params, $param), $method);
        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $result 获取access_token的方法的返回值
     */
    protected function parseToken($result)
    {
        $data = json_decode($result, true);
        if ($data['access_token'] && $data['expires_in'] && $data['refresh_token']) {
            $data['openid'] = $this->openid();
            return $data;
        } else {
            throw new \Exception("获取百度ACCESS_TOKEN出错：{$data['error']}");
        }

    }

    /**
     * 获取当前授权应用的openid
     * @return string
     */
    public function getOpenId()
    {
        if (!empty($this->token['openid'])) {
            return $this->token['openid'];
        }

        $data = $this->call('passport/users/getLoggedInUser');
        return !empty($data['uid']) ? $data['uid'] : null;
    }

    /**
     * 获取当前登录的用户信息
     * @return array
     */
    public function getOauthInfo()
    {
        $data = $this->call('passport/users/getLoggedInUser');

        if (!empty($data['uid'])) {
            $userInfo['type']   = 'BAIDU';
            $userInfo['name']   = $data['uid'];
            $userInfo['nick']   = $data['uname'];
            $userInfo['avatar'] = "http://tb.himg.baidu.com/sys/portrait/item/{$data['portrait']}";
            return $userInfo;
        } else {
            throw new \Exception("获取百度用户信息失败：{$data['error_msg']}");
        }
    }

}
