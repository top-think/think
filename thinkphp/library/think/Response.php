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

namespace think;

class Response
{
    // 输出数据的转换方法
    protected static $tramsform = null;
    // 输出数据的类型
    protected static $type = '';
    // 输出数据
    protected static $data = '';
    // 是否exit
    protected static $isExit = false;

    /**
     * 发送数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回数据格式
     * @param bool $return 是否返回数据
     * @return mixed
     */
    public static function send($data = '', $type = '', $return = false)
    {
        $type = strtolower($type ?: self::$type);

        $headers = [
            'json'   => 'application/json',
            'xml'    => 'text/xml',
            'html'   => 'text/html',
            'jsonp'  => 'application/javascript',
            'script' => 'application/javascript',
            'text'   => 'text/plain',
        ];

        if (!headers_sent() && !headers_list() && isset($headers[$type])) {
            header('Content-Type:' . $headers[$type] . '; charset=utf-8');
        }

        $data = $data ?: self::$data;
        if (is_callable(self::$tramsform)) {
            $data = call_user_func_array(self::$tramsform, [$data]);
        } else {
            switch ($type) {
                case 'json':
                    // 返回JSON数据格式到客户端 包含状态信息
                    $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                    break;
                case 'jsonp':
                    // 返回JSON数据格式到客户端 包含状态信息
                    $handler = !empty($_GET[Config::get('var_jsonp_handler')]) ? $_GET[Config::get('var_jsonp_handler')] : Config::get('default_jsonp_handler');
                    $data    = $handler . '(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ');';
                    break;
                case '':
                case 'html':
                case 'text':
                    // 不做处理
                    break;
                default:
                    // 用于扩展其他返回格式数据
                    APP_HOOK && Hook::listen('return_data', $data);
            }
        }

        if ($return) {
            return $data;
        }

        echo $data;
        self::isExit() && exit();
    }

    /**
     * 转换控制器输出的数据
     * @access public
     * @param mixed $callback 调用的转换方法
     * @return void
     */
    public static function tramsform($callback)
    {
        self::$tramsform = $callback;
    }

    /**
     * 输出类型设置
     * @access public
     * @param string $type 输出内容的格式类型
     * @return mixed
     */
    public static function type($type = null)
    {
        if (is_null($type)) {
            return self::$type ?: Config::get('default_return_type');
        }
        self::$type = $type;
    }

    /**
     * 输出数据设置
     * @access public
     * @param mixed $data 输出数据
     * @return void
     */
    public static function data($data)
    {
        self::$data = $data;
    }

    /**
     * 输出是否exit设置
     * @access public
     * @param bool $exit 是否退出
     * @return mixed
     */
    public static function isExit($exit = null)
    {
        if (is_null($exit)) {
            return self::$isExit;
        }
        self::$isExit = (boolean) $exit;
    }

    /**
     * 返回封装后的API数据到客户端
     * @access public
     * @param mixed $data 要返回的数据
     * @param integer $code 返回的code
     * @param string $msg 提示信息
     * @param string $type 返回数据格式
     * @return mixed
     */
    public static function result($data, $code = 0, $msg = '', $type = '')
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => NOW_TIME,
            'data' => $data,
        ];
        if ($type) {
            self::type($type);
        }

        return $result;
    }

    /**
     * 操作成功跳转的快捷方法
     * @access public
     * @param mixed $msg 提示信息
     * @param mixed $data 返回的数据
     * @param string $url 跳转的URL地址
     * @param integer $wait 跳转等待时间
     * @return mixed
     */
    public static function success($msg = '', $data = '', $url = null, $wait = 3)
    {
        $code = 1;
        if (is_numeric($msg)) {
            $code = $msg;
            $msg  = '';
        }
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => is_null($url) ? $_SERVER["HTTP_REFERER"] : $url,
            'wait' => $wait,
        ];

        $type = IS_AJAX ? Config::get('default_ajax_return') : Config::get('default_return_type');

        if ('html' == $type) {
            $result = \think\View::instance()->fetch(Config::get('dispatch_success_tmpl'), $result);
        }
        self::type($type);
        return $result;
    }

    /**
     * 操作错误跳转的快捷方法
     * @access public
     * @param mixed $msg 提示信息
     * @param mixed $data 返回的数据
     * @param string $url 跳转的URL地址
     * @param integer $wait 跳转等待时间
     * @return mixed
     */
    public static function error($msg = '', $data = '', $url = null, $wait = 3)
    {
        $code = 0;
        if (is_numeric($msg)) {
            $code = $msg;
            $msg  = '';
        }
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => is_null($url) ? 'javascript:history.back(-1);' : $url,
            'wait' => $wait,
        ];

        $type = IS_AJAX ? Config::get('default_ajax_return') : Config::get('default_return_type');

        if ('html' == $type) {
            $result = \think\View::instance()->fetch(Config::get('dispatch_error_tmpl'), $result);
        }
        self::type($type);
        return $result;
    }

    /**
     * URL重定向
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array|int $params 其它URL参数或http code
     * @return void
     */
    public static function redirect($url, $params = [])
    {
        $http_response_code = 301;
        if (is_int($params) && in_array($params, [301, 302])) {
            $http_response_code = $params;
            $params             = [];
        }
        $url = preg_match('/^(https?:|\/)/', $url) ? $url : Url::build($url, $params);
        header('Location: ' . $url, true, $http_response_code);
    }

    /**
     * 设置响应头
     * @access protected
     * @param string $name 参数名
     * @param string $value 参数值
     * @return void
     */
    public static function header($name, $value)
    {
        header($name . ':' . $value);
    }

}
