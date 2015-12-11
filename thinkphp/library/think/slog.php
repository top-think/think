<?php
/**
 * github: https://github.com/luofei614/SocketLog
 * @author luofei614<weibo.com/luofei614>
 */
namespace think;

use think\Exception;

class Slog
{
    public static $start_time   = 0;
    public static $start_memory = 0;
    public static $port         = 1116; //SocketLog 服务的http的端口号
    public static $log_types    = ['log', 'info', 'error', 'warn', 'table', 'group','groupCollapsed','groupEnd','alert'];

    protected static $config = [
        'enable'              => true, //是否记录日志的开关
        'host'                => 'localhost',
        //是否显示利于优化的参数，如果允许时间，消耗内存等
        'optimize'            => false,
        'show_included_files' => false,
        'error_handler'       => false,
        //日志强制记录到配置的client_id
        'force_client_id'     => '',
        //限制允许读取日志的client_id
        'allow_client_ids'    => [],
    ];

    protected static $logs = [];

    protected static $css = [
        'sql'           => 'color:#009bb4;',
        'sql_warn'      => 'color:#009bb4;font-size:14px;',
        'error_handler' => 'color:#f4006b;font-size:14px;',
        'page'          => 'color:#40e2ff;background:#171717;',
        'big'           => 'font-size:20px;color:red;',
    ];

    public static function sql($sql, $pdo)
    {

        if (is_object($pdo)) {
            if (!self::check()) {
                return;
            }
            $css = self::$css['sql'];
            if (preg_match('/^SELECT /i', $sql)) {
                //explain
                try {
                    $obj = $pdo->query("EXPLAIN " . $sql);
                    if (is_object($obj) && method_exists($obj, 'fetch')) {
                        $arr = $obj->fetch(\PDO::FETCH_ASSOC);
                        self::sqlexplain($arr, $sql, $css);
                    }
                } catch (Exception $e) {

                }
            }
            self::sqlwhere($sql, $css);
            self::trace($sql, 2, $css);
        } else {
            throw new Exception('SocketLog can not support this database link');
        }

    }

    public static function trace($msg, $trace_level = 2, $css = '')
    {
        if (!self::check()) {
            return;
        }
        self::record('groupCollapsed', $msg, $css);
        $traces = debug_backtrace(false);
        $traces = array_reverse($traces);
        $max    = count($traces) - $trace_level;
        for ($i = 0; $i < $max; $i++) {
            $trace     = $traces[$i];
            $fun       = isset($trace['class']) ? $trace['class'] . '::' . $trace['function'] : $trace['function'];
            $file      = isset($trace['file']) ? $trace['file'] : 'unknown file';
            $line      = isset($trace['line']) ? $trace['line'] : 'unknown line';
            $trace_msg = '#' . $i . '  ' . $fun . ' called at [' . $file . ':' . $line . ']';
            if (!empty($trace['args'])) {
                self::record('groupCollapsed', $trace_msg);
                self::record('log', $trace['args']);
                self::record('groupEnd');
            } else {
                self::record('log', $trace_msg);
            }
        }
        self::record('groupEnd');
    }

    private static function sqlexplain($arr, &$sql, &$css)
    {
        $arr = array_change_key_case($arr, CASE_LOWER);
        if (false !== strpos($arr['extra'], 'Using filesort')) {
            $sql .= ' <---################[Using filesort]';
            $css = self::$css['sql_warn'];
        }
        if (false !== strpos($arr['extra'], 'Using temporary')) {
            $sql .= ' <---################[Using temporary]';
            $css = self::$css['sql_warn'];
        }
    }
    private static function sqlwhere(&$sql, &$css)
    {
        //判断sql语句是否有where
        if (preg_match('/^UPDATE |DELETE /i', $sql) && !preg_match('/WHERE.*(=|>|<|LIKE|IN)/i', $sql)) {
            $sql .= '<---###########[NO WHERE]';
            $css = self::$css['sql_warn'];
        }
    }

    /**
     * 接管报错
     */
    public static function registerErrorHandler()
    {
        if (!self::check()) {
            return;
        }

        set_error_handler([__CLASS__, 'error_handler']);
        register_shutdown_function([__CLASS__, 'fatalError']);
    }

    public static function error_handler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_WARNING:
                $severity = 'E_WARNING';
                break;
            case E_NOTICE:
                $severity = 'E_NOTICE';
                break;
            case E_USER_ERROR:
                $severity = 'E_USER_ERROR';
                break;
            case E_USER_WARNING:
                $severity = 'E_USER_WARNING';
                break;
            case E_USER_NOTICE:
                $severity = 'E_USER_NOTICE';
                break;
            case E_STRICT:
                $severity = 'E_STRICT';
                break;
            case E_RECOVERABLE_ERROR:
                $severity = 'E_RECOVERABLE_ERROR';
                break;
            case E_DEPRECATED:
                $severity = 'E_DEPRECATED';
                break;
            case E_USER_DEPRECATED:
                $severity = 'E_USER_DEPRECATED';
                break;
            case E_ERROR:
                $severity = 'E_ERR';
                break;
            case E_PARSE:
                $severity = 'E_PARSE';
                break;
            case E_CORE_ERROR:
                $severity = 'E_CORE_ERROR';
                break;
            case E_COMPILE_ERROR:
                $severity = 'E_COMPILE_ERROR';
                break;
            case E_USER_ERROR:
                $severity = 'E_USER_ERROR';
                break;
            default:
                $severity = 'E_UNKNOWN_ERROR_' . $errno;
                break;
        }
        $msg = "{$severity}: {$errstr} in {$errfile} on line {$errline} -- SocketLog error handler";
        self::trace($msg, 2, self::$css['error_handler']);
    }

    public static function fatalError()
    {
        // 保存日志记录
        if ($e = error_get_last()) {
            self::error_handler($e['type'], $e['message'], $e['file'], $e['line']);
            self::sendLog();
        }
    }

    protected static function check()
    {
        if (!SLOG_ON) {
            return false;
        }
        $tabid = self::getClientArg('tabid');
        //是否记录日志的检查
        if (!$tabid && !self::$config['force_client_id']) {
            return false;
        }
        //用户认证
        $allow_client_ids = self::$config['allow_client_ids'];
        if (!empty($allow_client_ids)) {
            if (!$tabid && in_array(self::$config['force_client_id'], $allow_client_ids)) {
                return true;
            }

            $client_id = self::getClientArg('client_id');
            if (!in_array($client_id, $allow_client_ids)) {
                return false;
            }
        }
        return true;
    }

    protected static function getClientArg($name)
    {
        static $args = [];

        $key = 'HTTP_USER_AGENT';

        if (isset($_SERVER['HTTP_SOCKETLOG'])) {
            $key = 'HTTP_SOCKETLOG';
        }

        if (!isset($_SERVER[$key])) {
            return null;
        }
        if (empty($args)) {
            if (!preg_match('/SocketLog\((.*?)\)/', $_SERVER[$key], $match)) {
                $args = ['tabid' => null];
                return null;
            }
            parse_str($match[1], $args);
        }
        if (isset($args[$name])) {
            return $args[$name];
        }
        return null;
    }

    //设置配置
    public static function config($config)
    {
        $config       = array_merge(self::$config, $config);
        self::$config = $config;
        if (self::check()) {
            if ($config['optimize']) {
                self::$start_time   = microtime(true);
                self::$start_memory = memory_get_usage();
            }

            if ($config['error_handler']) {
                self::registerErrorHandler();
            }
        }
    }

    //获得配置
    public static function getConfig($name)
    {
        return isset(self::$config[$name]) ? self::$config[$name] : null;
    }

    //记录日志
    public static function record($type, $msg = '', $css = '')
    {
        if (!self::check()) {
            return;
        }

        self::$logs[] = [
            'type' => $type,
            'msg'  => $msg,
            'css'  => isset(self::$css[$css]) ? self::$css[$css] : $css,
        ];
    }

    /**
     * @param null $host - $host of socket server
     * @param string $message - 发送的消息
     * @param string $address - 地址
     * @return bool
     */
    public static function send($host, $message = '', $address = '/')
    {
        $url = 'http://' . $host . ':' . self::$port . $address;
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $headers = [
            "Content-Type: application/json;charset=UTF-8",
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //设置header
        $txt = curl_exec($ch);
        return true;
    }

    public static function sendLog()
    {
        if (!self::check()) {
            return;
        }

        $time_str   = '';
        $memory_str = '';
        if (self::$start_time) {
            $runtime  = microtime(true) - self::$start_time;
            $reqs     = number_format(1 / $runtime, 2);
            $time_str = "[运行时间：{$runtime}s][吞吐率：{$reqs}req/s]";
        }
        if (self::$start_memory) {
            $memory_use = number_format((memory_get_usage() - self::$start_memory) / 1024, 2);
            $memory_str = "[内存消耗：{$memory_use}kb]";
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            $current_uri = "cmd:" . implode(' ', $_SERVER['argv']);
        }
        array_unshift(self::$logs, [
            'type' => 'group',
            'msg'  => $current_uri . $time_str . $memory_str,
            'css'  => self::$css['page'],
        ]);

        if (self::$config['show_included_files']) {
            self::$logs[] = [
                'type' => 'groupCollapsed',
                'msg'  => 'included_files',
                'css'  => '',
            ];
            self::$logs[] = [
                'type' => 'log',
                'msg'  => implode("\n", get_included_files()),
                'css'  => '',
            ];
            self::$logs[] = [
                'type' => 'groupEnd',
                'msg'  => '',
                'css'  => '',
            ];
        }

        self::$logs[] = [
            'type' => 'groupEnd',
            'msg'  => '',
            'css'  => '',
        ];

        $tabid = self::getClientArg('tabid');
        if (!$client_id = self::getClientArg('client_id')) {
            $client_id = '';
        }
        if ($force_client_id = self::$config['force_client_id']) {
            $client_id = $force_client_id;
        }
        $logs = [
            'tabid'           => $tabid,
            'client_id'       => $client_id,
            'logs'            => self::$logs,
            'force_client_id' => $force_client_id,
        ];
        $msg     = @json_encode($logs);
        $address = '/' . $client_id; //将client_id作为地址， server端通过地址判断将日志发布给谁
        self::send(self::$config['host'], $msg, $address);

    }

    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::$log_types)) {
            array_unshift($args, $method);
            return call_user_func_array(['\think\Slog', 'record'], $args);
        }
    }

}
