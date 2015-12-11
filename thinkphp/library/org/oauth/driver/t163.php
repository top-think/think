<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace org\oauth\driver;

use org\oauth\Driver;

class T163 extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://api.t.163.com/oauth2/authorize';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://api.t.163.com/oauth2/access_token';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://api.t.163.com/';

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微博API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET')
    {
        /* 新浪微博调用公共参数 */
        $params = [
            'oauth_token' => $this->token['access_token'],
        ];

        $data = $this->http($this->url($api, '.json'), $this->param($params, $param), $method);
        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $result 获取access_token的方法的返回值
     */
    protected function parseToken($result)
    {
        $data = json_decode($result, true);
        if ($data['uid'] && $data['access_token'] && $data['expires_in'] && $data['refresh_token']) {
            $data['openid'] = $data['uid'];
            unset($data['uid']);
            return $data;
        } else {
            throw new \Exception("获取网易微博ACCESS_TOKEN出错：{$data['error']}");
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

        $data = $this->call('users/show');
        return !empty($data['id']) ? $data['id'] : null;
    }

    /**
     * 获取当前登录的用户信息
     * @return array
     */
    public function getOauthInfo()
    {
        $data = $this->call('users/show');

        if (0 == $data['error_code']) {
            $userInfo['type']   = 'T163';
            $userInfo['name']   = $data['name'];
            $userInfo['nick']   = $data['screen_name'];
            $userInfo['avatar'] = str_replace('w=48&h=48', 'w=180&h=180', $data['profile_image_url']);
            return $userInfo;
        } else {
            E("获取网易微博用户信息失败：{$data['error']}");
        }
    }

}
