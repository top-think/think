<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Think\Controller;
abstract class Rest {

    protected   $_method    =   ''; // 当前请求类型
    protected   $_type      =   ''; // 当前资源类型
    // 输出类型
    protected   $restMethodList           =   'get|post|put|delete';
    protected   $restDefaultMethod        =   'get';
    protected   $restTypeList             =   'html|xml|json|rss';
    protected   $restDefaultType          =   'html';
    protected   $restOutputType           =   [ // REST允许输出的资源类型列表
            'xml'   =>  'application/xml',
            'json'  =>  'application/json',
            'html'  =>  'text/html',
        ];
    
   /**
     * 架构函数 取得模板对象实例
     * @access public
     */
    public function __construct() {
        // 资源类型检测
        if(''==__EXT__) { // 自动检测资源类型
            $this->_type   =  $this->getAcceptType();
        }elseif(!preg_match('/\('.$this->restTypeList.')$/i',__EXT__)) {
            // 资源类型非法 则用默认资源类型访问
            $this->_type   =  $this->restDefaultType;
        }else{
            $this->_type   =  __EXT__;
        }
        // 请求方式检测
        $method  =  strtolower($_SERVER['REQUEST_METHOD']);
        if(false === stripos($this->restMethodList,$method)) {
            // 请求方式非法 则用默认请求方法
            $method = $this->restDefaultMethod;
        }
        $this->_method = $method;
    }

    /**
     * REST 调用
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function _empty($method,$args) {
        if(method_exists($this,$method.'_'.$this->_method.'_'.$this->_type)) { // RESTFul方法支持
            $fun  =  $method.'_'.$this->_method.'_'.$this->_type;
        }elseif($this->_method == $this->restDefaultMethod && method_exists($this,$method.'_'.$this->_type) ){
            $fun  =  $method.'_'.$this->_type;
        }elseif($this->_type == $this->restDefaultType && method_exists($this,$method.'_'.$this->_method) ){
            $fun  =  $method.'_'.$this->_method;
        }
        if(isset($fun)) {
            $this->$fun();
        }else{
            // 抛出异常
            E(L('_ERROR_ACTION_:').ACTION_NAME);
        }
    }

    /**
     * 设置页面输出的CONTENT_TYPE和编码
     * @access public
     * @param string $type content_type 类型对应的扩展名
     * @param string $charset 页面输出编码
     * @return void
     */
    public function setContentType($type, $charset='utf-8'){
        if(headers_sent()) return;
        $type = strtolower($type);
        if(isset($this->restOutputType[$type])) //过滤content_type
            header('Content-Type: '.$this->restOutputType[$type].'; charset='.$charset);
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @param integer $code HTTP状态
     * @return void
     */
    protected function response($data,$type='',$code=200) {
        $this->sendHttpStatus($code);
        exit($this->encodeData($data,strtolower($type)));
    }

    /**
     * 编码数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @return void
     */
    protected function encodeData($data,$type='') {
        if(empty($data))  return '';
        if('json' == $type) {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data);
        }elseif('xml' == $type){
            // 返回xml格式数据
            $data = xml_encode($data);
        }elseif('php'==$type){
            $data = serialize($data);
        }// 默认直接输出
        $this->setContentType($type);
        header('Content-Length: ' . strlen($data));
        return $data;
    }

    // 发送Http状态信息
    protected function sendHttpStatus($status) {
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
            302 => 'Moved Temporarily ',  // 1.1
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
            509 => 'Bandwidth Limit Exceeded'
        ];
        if(isset($_status[$code])) {
            header('HTTP/1.1 '.$code.' '.$_status[$code]);
            // 确保FastCGI模式下正常
            header('Status:'.$code.' '.$_status[$code]);
        }
    }

    /**
     * 获取当前请求的Accept头信息
     * @return string
     */
    protected function getAcceptType(){
        $type = [
            'html'  =>  'text/html,application/xhtml+xml,*/*',
            'xml'   =>  'application/xml,text/xml,application/x-xml',
            'json'  =>  'application/json,text/x-json,application/jsonrequest,text/json',
            'js'    =>  'text/javascript,application/javascript,application/x-javascript',
            'css'   =>  'text/css',
            'rss'   =>  'application/rss+xml',
            'yaml'  =>  'application/x-yaml,text/yaml',
            'atom'  =>  'application/atom+xml',
            'pdf'   =>  'application/pdf',
            'text'  =>  'text/plain',
            'png'   =>  'image/png',
            'jpg'   =>  'image/jpg,image/jpeg,image/pjpeg',
            'gif'   =>  'image/gif',
            'csv'   =>  'text/csv'
        ];
        
        foreach($type as $key=>$val){
            $array   =  explode(',',$val);
            foreach($array as $k=>$v){
                if(stristr($_SERVER['HTTP_ACCEPT'], $v)) {
                    return $key;
                }
            }
        }
        return false;
    }
}