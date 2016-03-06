<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://zjzit.cn>
// +----------------------------------------------------------------------

namespace think;

use think\exception\ErrorException;

class Error
{
    /**
     * 注册异常处理
     * @return void
     */
    public static function register()
    {
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }

    /**
     * Exception Handler
     * @param  \Exception $exception
     * @return bool  true-禁止往下传播已处理过的异常
     */
    public static function appException($exception)
    {
        /* 非API模式下的部署模式，跳转到指定的 Error Page */
        if (!(APP_DEBUG || IS_API)) {
            $error_page = Config::get('error_page');
            if (!empty($error_page)) {
                header("Location: {$error_page}");
            }
        }

        // 收集异常数据
        if (APP_DEBUG) {
            // 调试模式，获取详细的错误信息
            $data = [
                'name'    => get_class($exception),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTrace(),
                'code'    => self::getCode($exception),
                'source'  => self::getSourceCode($exception),
                'datas'   => self::getExtendData($exception),

                'tables'  => [
                    'GET Data'              => $_GET,
                    'POST Data'             => $_POST,
                    'Files'                 => $_FILES,
                    'Cookies'               => $_COOKIE,
                    'Session'               => isset($_SESSION) ? $_SESSION : [],
                    'Server/Request Data'   => $_SERVER,
                    'Environment Variables' => $_ENV,
                    'ThinkPHP Constants'    => self::getTPConst(),
                ],
            ];
            $log = "[{$data['code']}]{$data['message']}[{$data['file']}:{$data['line']}]";
        } else {
            // 部署模式仅显示 Code 和 Message
            $data = [
                'code'    => $exception->getCode(),
                'message' => Config::get('show_error_msg') ? $exception->getMessage() : Config::get('error_message'),
            ];
            $log = "[{$data['code']}]{$data['message']}";
        }

        // 记录异常日志
        Log::record($log, 'error');
        // 输出错误信息
        self::output($exception, $data);
        // 禁止往下传播已处理过的异常
        return true;
    }

    /**
     * Error Handler
     * @param  integer $errno   错误编号
     * @param  integer $errstr  详细错误信息
     * @param  string  $errfile 出错的文件
     * @param  integer $errline 出错行号
     * @return bool  true-禁止往下传播已处理过的异常
     */
    public static function appError($errno, $errstr, $errfile = null, $errline = 0, array $errcontext = [])
    {
        if ($errno & Config::get('exception_ignore_type')) {
            // 忽略的异常记录到日志
            Log::record("[{$errno}]{$errstr}[{$errfile}:{$errline}]", 'notice');
        } else {
            // 将错误信息托管至 think\exception\ErrorException
            throw new ErrorException($errno, $errstr, $errfile, $errline, $errcontext);
            // 禁止往下传播已处理过的异常
            return true;
        }
    }

    /**
     * Shutdown Handler
     * @return bool true-禁止往下传播已处理过的异常; false-未处理的异常继续传播
     */
    public static function appShutdown()
    {
        // 写入日志
        Log::save();

        if ($error = error_get_last()) {
            // 将错误信息托管至think\ErrorException
            $exception = new ErrorException(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );

            /**
             * Shutdown handler 中的异常将不被往下传播
             * 所以，这里我们必须手动传播而不能像 Error handler 中那样 throw
             */
            self::appException($exception);
            // 禁止往下传播已处理过的异常
            return true;
        }
        return false;
    }

    /**
     * 输出异常信息
     * @param  \Exception $exception
     * @param  Array      $vars      异常信息
     * @return void
     */
    public static function output($exception, array $vars)
    {
        http_response_code($exception instanceof Exception ? $exception->getHttpStatus() : 500);

        $type = Config::get('default_return_type');

        if (IS_API && 'html' != $type) {
            // 异常信息输出监听
            APP_HOOK && Hook::listen('error_output', $data);
            // 输出异常内容
            Response::send($data, $type, Config::get('response_return'));
        } else {
            //ob_end_clean();
            extract($vars);
            include Config::get('exception_tmpl');
        }
    }

    /**
     * 获取错误编码
     * ErrorException则使用错误级别作为错误编码
     * @param  \Exception $exception
     * @return integer                错误编码
     */
    private static function getCode($exception)
    {
        $code = $exception->getCode();
        if (!$code && $exception instanceof ErrorException) {
            $code = $exception->getSeverity();
        }
        return $code;
    }

    /**
     * 获取出错文件内容
     * 获取错误的前9行和后9行
     * @param  \Exception $exception
     * @return array                 错误文件内容
     */
    private static function getSourceCode($exception)
    {
        // 读取前9行和后9行
        $line  = $exception->getLine();
        $first = ($line - 9 > 0) ? $line - 9 : 1;

        try {
            $contents = file($exception->getFile());
            $source   = [
                'first'  => $first,
                'source' => array_slice($contents, $first - 1, 19),
            ];
        } catch (Exception $e) {
            $source = [];
        }
        return $source;
    }

    /**
     * 获取异常扩展信息
     * 用于非调试模式html返回类型显示
     * @param  \Exception $exception
     * @return array                 异常类定义的扩展数据
     */
    private static function getExtendData($exception)
    {
        $data = [];
        if ($exception instanceof Exception) {
            $data = $exception->getData();
        }
        return $data;
    }

    /**
     * 获取ThinkPHP常量列表
     * @return array 常量列表
     */
    private static function getTPConst()
    {
        return [
            'THINK_VERSION'    => defined('THINK_VERSION') ? THINK_VERSION : 'undefined',
            'THINK_PATH'       => defined('THINK_PATH') ? THINK_PATH : 'undefined',
            'LIB_PATH'         => defined('LIB_PATH') ? LIB_PATH : 'undefined',
            'EXTEND_PATH'      => defined('EXTEND_PATH') ? EXTEND_PATH : 'undefined',
            'MODE_PATH'        => defined('MODE_PATH') ? MODE_PATH : 'undefined',
            'CORE_PATH'        => defined('CORE_PATH') ? CORE_PATH : 'undefined',
            'TRAIT_PATH'       => defined('TRAIT_PATH') ? TRAIT_PATH : 'undefined',
            'APP_PATH'         => defined('APP_PATH') ? APP_PATH : 'undefined',
            'RUNTIME_PATH'     => defined('RUNTIME_PATH') ? RUNTIME_PATH : 'undefined',
            'LOG_PATH'         => defined('LOG_PATH') ? LOG_PATH : 'undefined',
            'CACHE_PATH'       => defined('CACHE_PATH') ? CACHE_PATH : 'undefined',
            'TEMP_PATH'        => defined('TEMP_PATH') ? TEMP_PATH : 'undefined',
            'VENDOR_PATH'      => defined('VENDOR_PATH') ? VENDOR_PATH : 'undefined',
            'MODULE_PATH'      => defined('MODULE_PATH') ? MODULE_PATH : 'undefined',
            'VIEW_PATH'        => defined('VIEW_PATH') ? VIEW_PATH : 'undefined',
            'APP_NAMESPACE'    => defined('APP_NAMESPACE') ? APP_NAMESPACE : 'undefined',
            'COMMON_MODULE'    => defined('COMMON_MODULE') ? COMMON_MODULE : 'undefined',
            'APP_MULTI_MODULE' => defined('APP_MULTI_MODULE') ? APP_MULTI_MODULE : 'undefined',
            'MODULE_ALIAS'     => defined('MODULE_ALIAS') ? MODULE_ALIAS : 'undefined',
            'MODULE_NAME'      => defined('MODULE_NAME') ? MODULE_NAME : 'undefined',
            'CONTROLLER_NAME'  => defined('CONTROLLER_NAME') ? CONTROLLER_NAME : 'undefined',
            'ACTION_NAME'      => defined('ACTION_NAME') ? ACTION_NAME : 'undefined',
            'MODEL_LAYER'      => defined('MODEL_LAYER') ? MODEL_LAYER : 'undefined',
            'VIEW_LAYER'       => defined('VIEW_LAYER') ? VIEW_LAYER : 'undefined',
            'CONTROLLER_LAYER' => defined('CONTROLLER_LAYER') ? CONTROLLER_LAYER : 'undefined',
            'APP_DEBUG'        => defined('APP_DEBUG') ? APP_DEBUG : 'undefined',
            'APP_HOOK'         => defined('APP_HOOK') ? APP_HOOK : 'undefined',
            'ENV_PREFIX'       => defined('ENV_PREFIX') ? ENV_PREFIX : 'undefined',
            'IS_API'           => defined('IS_API') ? IS_API : 'undefined',
            'APP_AUTO_RUN'     => defined('APP_AUTO_RUN') ? APP_AUTO_RUN : 'undefined',
            'APP_MODE'         => defined('APP_MODE') ? APP_MODE : 'undefined',
            'REQUEST_METHOD'   => defined('REQUEST_METHOD') ? REQUEST_METHOD : 'undefined',
            'IS_CGI'           => defined('IS_CGI') ? IS_CGI : 'undefined',
            'IS_WIN'           => defined('IS_WIN') ? IS_WIN : 'undefined',
            'IS_CLI'           => defined('IS_CLI') ? IS_CLI : 'undefined',
            'IS_AJAX'          => defined('IS_AJAX') ? IS_AJAX : 'undefined',
            'IS_GET'           => defined('IS_GET') ? IS_GET : 'undefined',
            'IS_POST'          => defined('IS_POST') ? IS_POST : 'undefined',
            'IS_PUT'           => defined('IS_PUT') ? IS_PUT : 'undefined',
            'IS_DELETE'        => defined('IS_DELETE') ? IS_DELETE : 'undefined',
            'NOW_TIME'         => defined('NOW_TIME') ? NOW_TIME : 'undefined',
            'LANG_SET'         => defined('LANG_SET') ? LANG_SET : 'undefined',
            'EXT'              => defined('EXT') ? EXT : 'undefined',
            'DS'               => defined('DS') ? DS : 'undefined',
            '__INFO__'         => defined('__INFO__') ? __INFO__ : 'undefined',
            '__EXT__'          => defined('__EXT__') ? __EXT__ : 'undefined',
        ];
    }
}
