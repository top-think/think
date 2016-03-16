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
 * 调试输出到SAE
 */
class Sae
{
    protected $config = [
        'log_time_format' => ' c ',
    ];

    // 实例化并传入参数
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return bool
     */
    public function save(array $log = [])
    {
        static $is_debug = null;
        $now             = date($this->config['log_time_format']);
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

        $logstr = "[{$now}] {$_SERVER['SERVER_ADDR']} {$_SERVER['REMOTE_ADDR']} {$_SERVER['REQUEST_URI']}\r\n{$info}\r\n";
        if (is_null($is_debug)) {
            $appSettings = [];
            preg_replace_callback('@(\w+)\=([^;]*)@', function ($match) use (&$appSettings) {
                $appSettings[$match['1']] = $match['2'];
            }, $_SERVER['HTTP_APPCOOKIE']);
            $is_debug = in_array($_SERVER['HTTP_APPVERSION'], explode(',', $appSettings['debug'])) ? true : false;
        }
        if ($is_debug) {
            sae_set_display_errors(false); //记录日志不将日志打印出来
        }
        sae_debug($logstr);
        if ($is_debug) {
            sae_set_display_errors(true);
        }
        return true;
    }

}
