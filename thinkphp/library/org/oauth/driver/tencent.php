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

class Tencent extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://open.t.qq.com/cgi-bin/oauth2/authorize';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://open.t.qq.com/cgi-bin/oauth2/access_token';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://open.t.qq.com/api/';

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微博API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET', $multi = false)
    {
        /* 腾讯微博调用公共参数 */
        $params = [
            'oauth_consumer_key' => $this->AppKey,
            'access_token'       => $this->token['access_token'],
            'openid'             => $this->openid(),
            'clientip'           => $_SERVER['REMOTE_ADDR'],
            'oauth_version'      => '2.a',
            'scope'              => 'all',
            'format'             => 'json',
        ];

        $data = $this->http($this->url($api), $this->param($params, $param), $method, $multi);
        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $result 获取access_token的方法的返回值
     */
    protected function parseToken($result)
    {
        parse_str($result, $data);
        $data = array_merge($data, ['openid' => $_GET['openid'], 'openkey' => $_GET['openkey']]);
        if ($data['access_token'] && $data['expires_in'] && $data['openid']) {
            return $data;
        } else {
            throw new \Exception("获取腾讯微博 ACCESS_TOKEN 出错：{$result}");
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
        $data = $this->call('users.getInfo');

        if (!isset($data['error_code'])) {
            $userInfo['type']   = 'RENREN';
            $userInfo['name']   = $data[0]['name'];
            $userInfo['nick']   = $data[0]['name'];
            $userInfo['avatar'] = $data[0]['headurl'];
            return $userInfo;
        } else {
            E("获取人人网用户信息失败：{$data['error_msg']}");
        }
    }

}
