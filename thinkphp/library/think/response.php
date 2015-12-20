<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

class Response
{

    /**
     * 返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回数据格式
     * @param bool $exit 是否终止执行
     * @return void
     */
    public static function returnData($data, $type = '', $exit = true)
    {
        $headers = [
            'json'   => 'application/json',
            'xml'    => 'text/xml',
            'html'   => 'text/html',
            'jsonp'  => 'application/javascript',
            'script' => 'application/javascript',
            'text'   => 'text/plain',
        ];
        $type = strtolower($type);
        if (!headers_sent() && isset($headers[$type])) {
            header('Content-Type:' . $headers[$type] . '; charset=utf-8');
        }

        switch ($type) {
            case 'json':
                // 返回JSON数据格式到客户端 包含状态信息
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                break;
            case 'xml':
                // 返回xml格式数据
                $data = \org\Transform::xmlEncode($data);
                break;
            case 'jsonp':
                // 返回JSON数据格式到客户端 包含状态信息
                $handler = !empty($_GET[Config::get('var_jsonp_handler')]) ? $_GET[Config::get('var_jsonp_handler')] : Config::get('default_jsonp_handler');
                $data    = $handler . '(' . \org\Transform::jsonEncode($data) . ');';
                break;
        }
        //header('Content-Length:' . strlen($data));
        if ($exit) {
            exit($data);
        } else {
            echo $data;
        }
    }

    /**
     * 返回封装后的API数据到客户端
     * @access public
     * @param mixed $data 要返回的数据
     * @param integer $code 返回的code
     * @param mixed $msg 提示信息
     * @param string $type 返回数据格式
     * @return void
     */
    public static function result($data, $code = 0, $msg = '', $type = '')
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => NOW_TIME,
            'data' => $data,
        ];
        self::returnData($result, $type, true);
    }

    /**
     * 返回封装后的API数据到客户端
     * @access public
     * @param mixed $msg 要返回的数据
     * @param mixed $data 返回的code
     * @param mixed $url 提示信息
     * @param mixed $wait 返回数据格式
     * @return void
     */
    public static function success($msg = '', $data = '', $url = '', $wait = 3)
    {
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url ? $url : $_SERVER["HTTP_REFERER"],
            'wait' => $wait,
        ];
        $type = IS_AJAX ? Config::get('default_ajax_return') : Config::get('default_return_type');
        if ('html' == $type) {
            $view   = new \think\View();
            $result = $view->fetch(Config::get('dispatch_jump_tmpl'), $result);
        }
        self::returnData($result, $type, true);
    }

    /**
     * 返回封装后的API数据到客户端
     * @access public
     * @param mixed $msg 要返回的数据
     * @param mixed $data 返回的code
     * @param mixed $url 提示信息
     * @param mixed $wait 返回数据格式
     * @return void
     */
    public static function error($msg = '', $data = '', $url = '', $wait = 3)
    {
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url ? $url : 'javascript:history.back(-1);',
            'wait' => $wait,
        ];
        $type = IS_AJAX ? Config::get('default_ajax_return') : Config::get('default_return_type');
        if ('html' == $type) {
            $view   = new \think\View();
            $result = $view->fetch(Config::get('dispatch_jump_tmpl'), $result);
        }
        self::returnData($result, $type, true);
    }

    /**
     * URL重定向
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @return void
     */
    public static function redirect($url, $params = [])
    {
        $url = Url::build($url, $params);
        header('Location: ' . $url);
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

    // 发送Http状态信息
    public static function sendHttpStatus($status)
    {
        static $_status = [
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ', // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded',
        ];
        if (isset($_status[$status])) {
            header('HTTP/1.1 ' . $status . ' ' . $_status[$status]);
            // 确保FastCGI模式下正常
            header('Status:' . $status . ' ' . $_status[$status]);
        }
    }

}
