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

class Sohu extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://api.sohu.com/oauth2/authorize';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://api.sohu.com/oauth2/token';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://api.sohu.com/rest/';

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    搜狐API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET')
    {
        /* 搜狐调用公共参数 */
        $params = [
            'access_token' => $this->token['access_token'],
        ];

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
        if ($data['access_token'] && $data['expires_in'] && $data['refresh_token'] && $data['open_id']) {
            $data['openid'] = $data['open_id'];
            unset($data['open_id']);
            return $data;
        } else {
            throw new \Exception("获取搜狐ACCESS_TOKEN出错：{$data['error']}");
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
        $data = $this->call('i/prv/1/user/get-basic-info');

        if ('success' == $data['message'] && !empty($data['data'])) {
            $userInfo['type']   = 'SOHU';
            $userInfo['name']   = $data['data']['open_id'];
            $userInfo['nick']   = $data['data']['nick'];
            $userInfo['avatar'] = $data['data']['icon'];
            return $userInfo;
        } else {
            E("获取搜狐用户信息失败：{$data['message']}");
        }
    }

}
