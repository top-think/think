<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
namespace Think;
class Error {

    /**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    static public function appException($e) {
        $error = array();
        $trace = $e->getTrace();
        $error['message']   = $e->getMessage();
        $error['file']      = $e->getFile();
        $error['class']     = isset($trace[0]['class'])?$trace[0]['class']:'';
        $error['function']  = isset($trace[0]['function'])?$trace[0]['function']:'';
        $error['line']      = $e->getLine();
        $error['trace']     = '';
        $time = date('y-m-d H:i:m');
        foreach ($trace as $t) {
            $error['trace'] .= '[' . $time . '] ' . $t['file'] . ' (' . $t['line'] . ') ';
            $error['trace'] .= $t['class'] . $t['type'] . $t['function'] . '(';
            $error['trace'] .= implode(', ', $t['args']);
            $error['trace'] .=')<br/>';
        }
        self::halt($error);
    }

    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline) {
      switch ($errno) {
          case E_ERROR:
          case E_PARSE:
          case E_CORE_ERROR:
          case E_COMPILE_ERROR:
          case E_USER_ERROR:
            ob_end_clean();
            $errorStr = "$errstr ".$errfile." 第 $errline 行.";
            Log::write("[$errno] ".$errorStr,'ERROR');
            self::halt($errorStr);
            break;
          case E_STRICT:
          case E_USER_WARNING:
          case E_USER_NOTICE:
          default:
            $errorStr = "[$errno] $errstr ".$errfile." 第 $errline 行.";
            Log::record($errorStr,'NOTIC');
            break;
      }
    }

    /**
     * 应用关闭处理
     * @param mixed $error 错误
     * @return void
     */
    static public function appShutdown(){
        // 记录日志
        Log::save();
        if ($e = error_get_last()) {
            self::appError($e['type'],$e['message'],$e['file'],$e['line']);
        }
    }

    /**
     * 错误输出
     * @param mixed $error 错误
     * @return void
     */
    static public function halt($error) {
        if(IS_CLI) {
            exit($error);
        }
        $e = array();
        if (Config::get('app_debug')) {
            //调试模式下输出错误信息
            if (!is_array($error)) {
                $trace          = debug_backtrace();
                $e['message']   = $error;
                $e['file']      = $trace[0]['file'];
                $e['class']     = isset($trace[0]['class'])?$trace[0]['class']:'';
                $e['function']  = isset($trace[0]['function'])?$trace[0]['function']:'';
                $e['line']      = $trace[0]['line'];
                $traceInfo      = '';
                $time = date('y-m-d H:i:m');
                foreach ($trace as $t) {
                    $traceInfo .= '[' . $time . '] ' . $t['file'] . ' (' . $t['line'] . ') ';
                    $traceInfo .= $t['class'] . $t['type'] . $t['function'] . '(';
                    $traceInfo .= implode(', ', $t['args']);
                    $traceInfo .=')<br/>';
                }
                $e['trace']     = $traceInfo;
            } else {
                $e              = $error;
            }
        } else {
            //否则定向到错误页面
            $error_page         = Config::get('error_page');
            if (!empty($error_page)) {
                redirect($error_page);
            } else {
                if (Config::get('show_error_msg'))
                    $e['message'] = is_array($error) ? $error['message'] : $error;
                else
                    $e['message'] = C('error_message');
            }
        }
        // 包含异常页面模板
        include Config::get('exception_tmpl');
        exit;
    }
}