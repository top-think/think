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

namespace think\behavior;

use think\Config;
use think\Debug;
use think\Log;

/**
 * 系统行为扩展：页面Trace显示输出
 * @category   Think
 * @package  Think
 * @subpackage  Behavior
 * @author   liu21st <liu21st@gmail.com>
 */
class ShowPageTrace
{

    // 行为扩展的执行入口必须是run
    public function run(&$params)
    {
        if (!IS_AJAX && Config::get('show_page_trace')) {
            echo $this->showTrace();
        }
    }

    /**
     * 显示页面Trace信息
     * @access private
     */
    private function showTrace()
    {
        // 系统默认显示信息
        $files = get_included_files();
        $info  = [];
        foreach ($files as $key => $file) {
            $info[] = $file . ' ( ' . number_format(filesize($file) / 1024, 2) . ' KB )';
        }
        $trace = [];
        Debug::remark('START', NOW_TIME);
        $base = [
            '请求信息' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . $_SERVER['SERVER_PROTOCOL'] . ' ' . $_SERVER['REQUEST_METHOD'] . ' : ' . $_SERVER['PHP_SELF'],
            '运行时间' => Debug::getUseTime('START', 'END', 6) . 's',
            '内存开销' => MEMORY_LIMIT_ON ? G('START', 'END', 'm') . 'b' : '不支持',
            '查询信息' => N('db_query') . ' queries ' . N('db_write') . ' writes ',
            '文件加载' => count($files),
            '缓存信息' => N('cache_read') . ' gets ' . N('cache_write') . ' writes ',
            '配置加载' => count(Config::get()),
        ];
        // 读取项目定义的Trace文件
        $traceFile = MODULE_PATH . 'trace.php';
        if (is_file($traceFile)) {
            $base = array_merge($base, include $traceFile);
        }
        $debug = Log::getLog();
        $tabs  = Config::get('trace_page_tabs');
        foreach ($tabs as $name => $title) {
            switch (strtoupper($name)) {
                case 'BASE': // 基本信息
                    $trace[$title] = $base;
                    break;
                case 'FILE': // 文件信息
                    $trace[$title] = $info;
                    break;
                default: // 调试信息
                    $name = strtoupper($name);
                    if (strpos($name, '|')) {
// 多组信息
                        $array  = explode('|', $name);
                        $result = [];
                        foreach ($array as $name) {
                            $result += isset($debug[$name]) ? $debug[$name] : [];
                        }
                        $trace[$title] = $result;
                    } else {
                        $trace[$title] = isset($debug[$name]) ? $debug[$name] : '';
                    }
            }
        }
        unset($files, $info, $base, $debug);
        // 调用Trace页面模板
        ob_start();
        include Config::has('tmpl_trace_file') ? Config::get('tmpl_trace_file') : THINK_PATH . 'tpl/page_trace.tpl';
        return ob_get_clean();
    }
}
