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

namespace think\controller;

use think\Response;

abstract class Rest
{

    protected $_method = ''; // 当前请求类型
    protected $_type   = ''; // 当前资源类型
    // 输出类型
    protected $restMethodList    = 'get|post|put|delete';
    protected $restDefaultMethod = 'get';
    protected $restTypeList      = 'html|xml|json|rss';
    protected $restDefaultType   = 'html';
    protected $restOutputType    = [ // REST允许输出的资源类型列表
        'xml'  => 'application/xml',
        'json' => 'application/json',
        'html' => 'text/html',
    ];

    /**
     * 架构函数 取得模板对象实例
     * @access public
     */
    public function __construct()
    {
        // 资源类型检测
        if ('' == __EXT__) {
            // 自动检测资源类型
            $this->_type = $this->getAcceptType();
        } elseif (!preg_match('/\(' . $this->restTypeList . '\)$/i', __EXT__)) {
            // 资源类型非法 则用默认资源类型访问
            $this->_type = $this->restDefaultType;
        } else {
            $this->_type = __EXT__;
        }
        // 请求方式检测
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if (false === stripos($this->restMethodList, $method)) {
            // 请求方式非法 则用默认请求方法
            $method = $this->restDefaultMethod;
        }
        $this->_method = $method;
    }

    /**
     * REST 调用
     * @access public
     *
     * @param string $method 方法名
     * @param array  $args   参数
     *
     * @return mixed
     * @throws \think\Exception
     */
    public function _empty($method, $args)
    {
        if (method_exists($this, $method . '_' . $this->_method . '_' . $this->_type)) {
            // RESTFul方法支持
            $fun = $method . '_' . $this->_method . '_' . $this->_type;
        } elseif ($this->_method == $this->restDefaultMethod && method_exists($this, $method . '_' . $this->_type)) {
            $fun = $method . '_' . $this->_type;
        } elseif ($this->_type == $this->restDefaultType && method_exists($this, $method . '_' . $this->_method)) {
            $fun = $method . '_' . $this->_method;
        }
        if (isset($fun)) {
            return $this->$fun();
        } else {
            // 抛出异常
            throw new \Exception('error action :' . ACTION_NAME);
        }
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @param integer $code HTTP状态
     * @return void
     */
    protected function response($data, $type = '', $code = 200)
    {
        Response::sendHttpStatus($code);
        Response::data($data);
        if ($type) {
            Response::type($type);
        }
    }

    /**
     * 获取当前请求的Accept头信息
     * @return string
     */
    public static function getAcceptType()
    {
        if (!isset($_SERVER['HTTP_ACCEPT'])) {
            return false;
        }

        $type = [
            'html' => 'text/html,application/xhtml+xml,*/*',
            'xml'  => 'application/xml,text/xml,application/x-xml',
            'json' => 'application/json,text/x-json,application/jsonrequest,text/json',
            'js'   => 'text/javascript,application/javascript,application/x-javascript',
            'css'  => 'text/css',
            'rss'  => 'application/rss+xml',
            'yaml' => 'application/x-yaml,text/yaml',
            'atom' => 'application/atom+xml',
            'pdf'  => 'application/pdf',
            'text' => 'text/plain',
            'png'  => 'image/png',
            'jpg'  => 'image/jpg,image/jpeg,image/pjpeg',
            'gif'  => 'image/gif',
            'csv'  => 'text/csv',
        ];

        foreach ($type as $key => $val) {
            $array = explode(',', $val);
            foreach ($array as $k => $v) {
                if (stristr($_SERVER['HTTP_ACCEPT'], $v)) {
                    return $key;
                }
            }
        }

        return false;
    }
}
