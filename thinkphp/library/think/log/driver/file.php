<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think\log\driver;

/**
 * 本地化调试输出到文件
 */
class File
{

    protected $config = [
        'time_format' => ' c ',
        'file_size'   => 2097152,
        'path'        => LOG_PATH,
    ];

    // 实例化并传入参数
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return void
     */
    public function save($log = [])
    {
        $now         = date($this->config['time_format']);
        $destination = $this->config['path'] . date('y_m_d') . '.log';

        !is_dir($this->config['path']) && mkdir($this->config['path'], 0755, true);

        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . DS . time() . '-' . basename($destination));
        }

        // 获取基本信息
        if (isset($_SERVER['HTTP_HOST'])) {
            $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            $current_uri = "cmd:" . implode(' ', $_SERVER['argv']);
        }
        $runtime    = number_format(microtime(true) - START_TIME, 6);
        $reqs       = number_format(1 / $runtime, 2);
        $time_str   = " [运行时间：{$runtime}s] [吞吐率：{$reqs}req/s]";
        $memory_use = number_format((memory_get_usage() - START_MEM) / 1024, 2);
        $memory_str = " [内存消耗：{$memory_use}kb]";
        $file_load  = " [文件加载：" . count(get_included_files()) . "]";

        array_unshift($log, [
            'type' => 'log',
            'msg'  => $current_uri . $time_str . $memory_str . $file_load,
        ]);

        $info = '';
        foreach ($log as $line) {
            $info .= '[' . $line['type'] . '] ' . $line['msg'] . "\r\n";
        }
        error_log("[{$now}] {$_SERVER['SERVER_ADDR']} {$_SERVER['REMOTE_ADDR']} {$_SERVER['REQUEST_URI']}\r\n{$info}\r\n", 3, $destination);
    }

}
