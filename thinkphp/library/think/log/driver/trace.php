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
 * 页面Trace调试 需要设置 'response_exit' => false 才能生效
 */
class Trace
{
    protected $tabs   = ['base' => '基本', 'file' => '文件', 'warn|error' => '错误', 'sql' => 'SQL', 'info|debug|log' => '调试'];
    protected $config = [
        'trace_file' => '',
    ];

    // 实例化并传入参数
    public function __construct($config = [])
    {
        $this->config['trace_file'] = THINK_PATH . 'tpl/page_trace.tpl';
        $this->config               = array_merge($this->config, $config);
    }

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return void
     */
    public function save($log = [])
    {
        if (IS_AJAX || IS_CLI || IS_API) {
            // ajax cli api方式下不输出
            return;
        }
        // 获取基本信息
        $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $runtime     = number_format(microtime(true) - START_TIME, 6);
        $reqs        = number_format(1 / $runtime, 2);

        // 页面Trace信息
        $base = [
            '请求信息' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . $_SERVER['SERVER_PROTOCOL'] . ' ' . $_SERVER['REQUEST_METHOD'] . ' : ' . $current_uri,
            '运行时间' => "{$runtime}s [ 吞吐率：{$reqs}req/s ]",
            '内存消耗' => number_format((memory_get_usage() - START_MEM) / 1024, 2) . 'kb',
            '查询信息' => \think\Db::$queryTimes . ' queries ' . \think\Db::$executeTimes . ' writes ',
            '缓存信息' => \think\Cache::$readTimes . ' reads,' . \think\Cache::$writeTimes . ' writes',
            '文件加载' => count(get_included_files()),
            '配置加载' => count(\think\Config::get()),
            '会话信息' => 'SESSION_ID=' . session_id(),
        ];

        $info = \think\Debug::getFile(true);

        // 获取调试日志
        $debug = [];
        foreach ($log as $line) {
            $debug[$line['type']][] = $line['msg'];
        }

        // 页面Trace信息
        $trace = [];
        foreach ($this->tabs as $name => $title) {
            $name = strtolower($name);
            switch ($name) {
                case 'base':    // 基本信息
                    $trace[$title] = $base;
                    break;
                case 'file':    // 文件信息
                    $trace[$title] = $info;
                    break;
                default:    // 调试信息
                    if (strpos($name, '|')) {
                        // 多组信息
                        $names  = explode('|', $name);
                        $result = [];
                        foreach ($names as $name) {
                            $result = array_merge($result, isset($debug[$name]) ? $debug[$name] : []);
                        }
                        $trace[$title] = $result;
                    } else {
                        $trace[$title] = isset($debug[$name]) ? $debug[$name] : '';
                    }
            }
        }
        // 调用Trace页面模板
        ob_start();
        include $this->config['trace_file'];
        echo ob_get_clean();
    }

}
