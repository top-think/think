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

class Github extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://github.com/login/oauth/authorize';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://github.com/login/oauth/access_token';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://api.github.com/';

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微博API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET')
    {
        /* Github 调用公共参数 */
        $params = [];
        $header = ["Authorization: bearer {$this->token['access_token']}"];

        $data = $this->http($this->url($api), $this->param($params, $param), $method, $header);
        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $result 获取access_token的方法的返回值
     */
    protected function parseToken($result)
    {
        parse_str($result, $data);
        if ($data['access_token'] && $data['token_type']) {
            $data['openid'] = $this->getOpenId();
            return $data;
        } else {
            throw new \Exception("获取 Github ACCESS_TOKEN出错：未知错误");
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

        $data = $this->call('user');
        return !empty($data['id']) ? $data['id'] : null;
    }

    /**
     * 获取当前登录的用户信息
     * @return array
     */
    public function getOauthInfo()
    {
        $data = $this->call('user');

        if (empty($data['code'])) {
            $userInfo['type']   = 'GITHUB';
            $userInfo['name']   = $data['login'];
            $userInfo['nick']   = $data['name'];
            $userInfo['avatar'] = $data['avatar_url'];
            return $userInfo;
        } else {
            E("获取Github用户信息失败：{$data}");
        }
    }

}
