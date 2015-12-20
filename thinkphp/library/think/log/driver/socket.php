<?php
/**
 * github: https://github.com/luofei614/SocketLog
 * @author luofei614<weibo.com/luofei614>
 */
namespace think\log\driver;

class Socket
{
    public $port = 1116; //SocketLog 服务的http的端口号

    protected $config = [
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

    protected $css = [
        'sql'           => 'color:#009bb4;',
        'sql_warn'      => 'color:#009bb4;font-size:14px;',
        'error_handler' => 'color:#f4006b;font-size:14px;',
        'page'          => 'color:#40e2ff;background:#171717;',
        'big'           => 'font-size:20px;color:red;',
    ];

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    public function save($logs = [])
    {
        if (!$this->check()) {
            return;
        }
        $runtime    = number_format(microtime(true) - START_TIME, 6);
        $reqs       = number_format(1 / $runtime, 2);
        $time_str   = " [运行时间：{$runtime}s][吞吐率：{$reqs}req/s]";
        $memory_use = number_format((memory_get_usage() - START_MEM) / 1024, 2);
        $memory_str = " [内存消耗：{$memory_use}kb]";
        $file_load  = " [文件加载：" . count(get_included_files()) . "]";

        if (isset($_SERVER['HTTP_HOST'])) {
            $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            $current_uri = "cmd:" . implode(' ', $_SERVER['argv']);
        }
        array_unshift($logs, [
            'type' => 'group',
            'msg'  => $current_uri . $time_str . $memory_str . $file_load,
            'css'  => $this->css['page'],
        ]);

        $logs[] = [
            'type' => 'groupCollapsed',
            'msg'  => 'included_files',
            'css'  => '',
        ];
        $logs[] = [
            'type' => 'log',
            'msg'  => implode("\n", get_included_files()),
            'css'  => '',
        ];
        $logs[] = [
            'type' => 'groupEnd',
            'msg'  => '',
            'css'  => '',
        ];

        $logs[] = [
            'type' => 'groupEnd',
            'msg'  => '',
            'css'  => '',
        ];

        foreach ($logs as &$log) {
            if (in_array($log['type'], ['sql', 'debug', 'info'])) {
                $log['type'] = 'log';
            }
        }
        $tabid = $this->getClientArg('tabid');
        if (!$client_id = $this->getClientArg('client_id')) {
            $client_id = '';
        }
        if ($force_client_id = $this->config['force_client_id']) {
            $client_id = $force_client_id;
        }
        $logs = [
            'tabid'           => $tabid,
            'client_id'       => $client_id,
            'logs'            => $logs,
            'force_client_id' => $force_client_id,
        ];
        $msg     = json_encode($logs);
        $address = '/' . $client_id; //将client_id作为地址， server端通过地址判断将日志发布给谁
        $this->send($this->config['host'], $msg, $address);
    }

    protected function check()
    {
        $tabid = $this->getClientArg('tabid');
        //是否记录日志的检查
        if (!$tabid && !$this->config['force_client_id']) {
            return false;
        }
        //用户认证
        $allow_client_ids = $this->config['allow_client_ids'];
        if (!empty($allow_client_ids)) {
            if (!$tabid && in_array($this->config['force_client_id'], $allow_client_ids)) {
                return true;
            }

            $client_id = $this->getClientArg('client_id');
            if (!in_array($client_id, $allow_client_ids)) {
                return false;
            }
        }
        return true;
    }

    protected function getClientArg($name)
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

    /**
     * @param null $host - $host of socket server
     * @param string $message - 发送的消息
     * @param string $address - 地址
     * @return bool
     */
    protected function send($host, $message = '', $address = '/')
    {
        $url = 'http://' . $host . ':' . $this->port . $address;
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

}
