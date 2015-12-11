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

namespace org\oauth;

abstract class Driver
{

    /**
     * oauth版本
     * @var string
     */
    protected $version = '2.0';

    /**
     * 申请应用时分配的app_key
     * @var string
     */
    protected $appKey = '';

    /**
     * 申请应用时分配的 app_secret
     * @var string
     */
    protected $appSecret = '';

    /**
     * 授权类型 response_type 目前只能为code
     * @var string
     */
    protected $responseType = 'code';

    /**
     * grant_type 目前只能为 authorization_code
     * @var string
     */
    protected $grantType = 'authorization_code';

    /**
     * 获取request_code请求的URL
     * @var string
     */
    protected $getRequestCodeURL = '';

    /**
     * 获取access_token请求的URL
     * @var string
     */
    protected $getAccessTokenURL = '';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = '';

    /**
     * 授权后获取到的TOKEN信息
     * @var array
     */
    protected $token = null;

    /**
     * 回调页面URL  可以通过配置文件配置
     * @var string
     */
    protected $callback = '';

    /**
     * 构造方法，配置应用信息
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->appKey    = $config['app_key'];
        $this->appSecret = $config['app_secret'];
        $this->authorize = isset($config['authorize']) ? $config['authorize'] : '';
        $this->callback  = isset($config['callback']) ? $config['callback'] : '';
    }

    // 跳转到授权登录页面
    public function login($callback = '')
    {
        if ($callback) {
            $this->callback = $callback;
        }
        //跳转到授权页面
        header('Location: ' . $this->getRequestCodeURL());
        exit;
    }

    /**
     * 请求code
     */
    public function getRequestCodeURL()
    {
        //Oauth 标准参数
        $params = [
            'client_id'     => $this->appKey,
            'redirect_uri'  => $this->callback,
            'response_type' => $this->responseType,
        ];

        //获取额外参数
        if ($this->authorize) {
            parse_str($this->authorize, $_param);
            if (is_array($_param)) {
                $params = array_merge($params, $_param);
            } else {
                throw new \Exception('AUTHORIZE配置不正确！');
            }
        }
        return $this->getRequestCodeURL . '?' . http_build_query($params);
    }

    /**
     * 获取access_token
     * @param string $code 授权登录成功后得到的code信息
     */
    public function getAccessToken($code)
    {
        $params = [
            'client_id'     => $this->appKey,
            'client_secret' => $this->appSecret,
            'grant_type'    => $this->grantType,
            'redirect_uri'  => $this->callback,
            'code'          => $code,
        ];
        // 获取token信息
        $data = $this->http($this->getAccessTokenURL, $params, 'POST');
        // 解析token
        $this->token = $this->parseToken($data);
        return $this->token;
    }

    /**
     * 设置access_token
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * 合并默认参数和额外参数
     * @param array $params  默认参数
     * @param array/string $param 额外参数
     * @return array:
     */
    protected function param($params, $param)
    {
        if (is_string($param)) {
            parse_str($param, $param);
        }

        return array_merge($params, $param);
    }

    /**
     * 获取指定API请求的URL
     * @param  string $api API名称
     * @param  string $fix api后缀
     * @return string      请求的完整URL
     */
    protected function url($api, $fix = '')
    {
        return $this->apiBase . $api . $fix;
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
    protected function http($url, $params, $method = 'GET', $header = [], $multi = false)
    {
        $opts = [
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header,
        ];

        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params                   = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL]        = $url;
                $opts[CURLOPT_POST]       = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new \Exception('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            throw new \Exception('请求发生错误：' . $error);
        }

        return $data;
    }

    /**
     * 抽象方法，在SNSSDK中实现
     * 组装接口调用参数 并调用接口
     */
    abstract protected function call($api, $param = '', $method = 'GET', $multi = false);

    /**
     * 抽象方法，在SNSSDK中实现
     * 解析access_token方法请求后的返回值
     */
    abstract protected function parseToken($result);

    /**
     * 抽象方法，在SNSSDK中实现
     * 获取当前授权用户的SNS标识
     */
    abstract public function getOpenId();

    /**
     * 抽象方法
     * 获取当前授权用户的用户信息
     */
    abstract public function getOauthInfo();
}
