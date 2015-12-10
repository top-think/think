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

class Taobao extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://oauth.taobao.com/authorize';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://oauth.taobao.com/token';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://eco.taobao.com/router/rest';

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微博API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET')
    {
        /* 淘宝网调用公共参数 */
        $params = [
            'method'       => $api,
            'access_token' => $this->token['access_token'],
            'format'       => 'json',
            'v'            => '2.0',
        ];
        $data = $this->http($this->url(''), $this->param($params, $param), $method);
        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $result 获取access_token的方法的返回值
     */
    protected function parseToken($result)
    {
        $data = json_decode($result, true);
        if ($data['access_token'] && $data['expires_in'] && $data['taobao_user_id']) {
            $data['openid'] = $data['taobao_user_id'];
            unset($data['taobao_user_id']);
            return $data;
        } else {
            throw new \Exception("获取淘宝网ACCESS_TOKEN出错：{$data['error']}");
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
        $fields = 'user_id,nick,sex,buyer_credit,avatar,has_shop,vip_info';
        $data   = $this->call('taobao.user.buyer.get', "fields={$fields}");

        if (!empty($data['user_buyer_get_response']['user'])) {
            $user               = $data['user_buyer_get_response']['user'];
            $userInfo['type']   = 'TAOBAO';
            $userInfo['name']   = $user['user_id'];
            $userInfo['nick']   = $user['nick'];
            $userInfo['avatar'] = $user['avatar'];
            return $userInfo;
        } else {
            E("获取淘宝网用户信息失败：{$data['error_response']['msg']}");
        }
    }

}
