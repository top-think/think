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
class Debug {

    static protected $_info       =   [];
    static protected $_mem        =   [];

    /**
     * 记录时间（微秒）和内存使用情况
     * @param string $name 标记位置
     * @param mixed $value 标记值 留空则取当前 time 表示仅记录时间 否则同时记录时间和内存
     * @return mixed
     */
    static public function remark($name,$value='') {
         // 记录时间和内存使用
        self::$_info[$name]  =  is_float($value)?$value:microtime(TRUE);
        if('time' != $value && function_exists('memory_get_usage')) {
            self::$_mem['mem'][$name]  = is_float($value)?$value:memory_get_usage();
            self::$_mem['peak'][$name] = function_exists('memory_get_peak_usage')?memory_get_peak_usage(): self::$_mem['mem'][$name];
        }
    }

    /**
     * 统计某个区间的时间（微秒）使用情况
     * @param string $start 开始标签
     * @param string $end 结束标签
     * @param integer|string $dec 小数位或者m 
     * @return mixed
     */
    static public function getUseTime($start,$end,$dec=6) {
        if(!isset(self::$_info[$end])) self::$_info[$end]   =  microtime(TRUE);
        return number_format((self::$_info[$end]-self::$_info[$start]),$dec);
    }

    /**
     * 记录内存使用情况
     * @param string $start 开始标签
     * @param string $end 结束标签
     * @param integer|string $dec 小数位或者m 
     * @return mixed
     */
    static public function getUseMem($start,$end,$dec=2) {
        if(!isset(self::$_mem['mem'][$end])) 
            self::$_mem['mem'][$end]     =  memory_get_usage();
        $size   =   self::$_mem['mem'][$end]-self::$_mem['mem'][$start];
        $a = array('B', 'KB', 'MB', 'GB', 'TB');
        $pos = 0;
        while ($size >= 1024) {
             $size /= 1024;
               $pos++;
        }
        return round($size,$dec)." ".$a[$pos];
    }

    /**
     * 统计内存峰值情况
     * @param string $start 开始标签
     * @param string $end 结束标签
     * @param integer|string $dec 小数位或者m 
     * @return mixed
     */
    static public function getMemPeak($start,$end,$dec=2) {
        if(!isset(self::$_mem['peak'][$end])) self::$_mem['peak'][$end]     =  function_exists('memory_get_peak_usage')?memory_get_peak_usage():memory_get_usage();
        $size   =   self::$_mem['peak'][$end]-self::$_mem['peak'][$start];
        $a = array('B', 'KB', 'MB', 'GB', 'TB');
        $pos = 0;
        while ($size >= 1024) {
             $size /= 1024;
               $pos++;
        }
        return round($size,$dec)." ".$a[$pos];
    }

    /**
     * 浏览器友好的变量输出
     * @param mixed $var 变量
     * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
     * @param string $label 标签 默认为空
     * @return void|string
     */
    static public function dump($var, $echo=true, $label=null) {
        $label = ($label === null) ? '' : rtrim($label) . ':';
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
        if(IS_CLI) {
            $output = PHP_EOL . $label. $output . PHP_EOL;
        }else{
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_QUOTES);
            }
            $output = '<pre>' . $label . $output . '</pre>';
        }
        if ($echo) {
            echo($output);
            return null;
        }else
            return $output;
    }
}