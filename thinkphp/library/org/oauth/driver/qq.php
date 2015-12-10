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

namespace org\oauth\driver;

use org\oauth\Driver;

class Qq extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://graph.qq.com/oauth2.0/authorize';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://graph.qq.com/oauth2.0/token';

    /**
     * 获取request_code的额外参数,可在配置中修改 URL查询字符串格式
     * @var srting
     */
    protected $authorize = 'scope=get_user_info,add_share';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://graph.qq.com/';

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微博API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET')
    {
        /* 腾讯QQ调用公共参数 */
        $params = [
            'oauth_consumer_key' => $this->AppKey,
            'access_token'       => $this->token['access_token'],
            'openid'             => $this->openid(),
            'format'             => 'json',
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
        parse_str($result, $data);
        if ($data['access_token'] && $data['expires_in']) {
            $data['openid'] = $this->getOpenId();
            return $data;
        } else {
            throw new \Exception("获取腾讯QQ ACCESS_TOKEN 出错：{$result}");
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

        if ($data['access_token']) {
            $data = $this->http($this->url('oauth2.0/me'), ['access_token' => $data['access_token']]);
            $data = json_decode(trim(substr($data, 9), " );\n"), true);
            if (isset($data['openid'])) {
                return $data['openid'];
            }

        }
        return null;
    }

    public function getOauthInfo()
    {
        $data = $this->call('user/get_user_info');

        if (0 == $data['ret']) {
            $userInfo['type']   = 'QQ';
            $userInfo['name']   = $data['nickname'];
            $userInfo['nick']   = $data['nickname'];
            $userInfo['avatar'] = $data['figureurl_2'];
            return $userInfo;
        } else {
            E("获取腾讯QQ用户信息失败：{$data['msg']}");
        }
    }
}
