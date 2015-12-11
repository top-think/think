<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 杨维杰 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace org\oauth\driver;

use org\oauth\Driver;

class Diandian extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://api.diandian.com/oauth/authorize';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://api.diandian.com/oauth/token';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://api.diandian.com/v1/';

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    点点网API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET')
    {
        /* 点点网调用公共参数 */
        $params = [
            'access_token' => $this->token['access_token'],
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
        if ($data['access_token'] && $data['expires_in'] && $data['token_type'] && $data['uid']) {
            $data['openid'] = $data['uid'];
            unset($data['uid']);
            return $data;
        } else {
            throw new \Exception("获取点点网ACCESS_TOKEN出错：{$data['error']}");
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

        return null;
    }

    /**
     * 获取当前登录的用户信息
     * @return array
     */
    public function getOauthInfo()
    {
        $data = $this->call('user/info');

        if (!empty($data['meta']['status']) && 200 == $data['meta']['status']) {
            $userInfo['type']   = 'DIANDIAN';
            $userInfo['name']   = $data['response']['name'];
            $userInfo['nick']   = $data['response']['name'];
            $userInfo['avatar'] = "https://api.diandian.com/v1/blog/{$data['response']['blogs'][0]['blogUuid']}/avatar/144";
            return $userInfo;
        } else {
            E("获取点点用户信息失败：{$data}");
        }
    }

}
