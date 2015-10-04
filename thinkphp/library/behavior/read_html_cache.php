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

/**
 * 系统行为扩展：静态缓存读取
 * @category   Think
 * @package  Think
 * @subpackage  Behavior
 * @author   liu21st <liu21st@gmail.com>
 */
class ReadHtmlCache
{
    protected $options = [
        'HTML_CACHE_ON'    => false,
        'HTML_CACHE_TIME'  => 60,
        'HTML_CACHE_RULES' => [],
        'HTML_FILE_SUFFIX' => '.html',
    ];

    // 行为扩展的执行入口必须是run
    public function run(&$params)
    {
        // 开启静态缓存
        if (C('HTML_CACHE_ON')) {
            $cacheTime = self::requireHtmlCache();
            if (false !== $cacheTime && self::checkHTMLCache(HTML_FILE_NAME, $cacheTime)) {
                //静态页面有效
                // 读取静态页面输出
                readfile(HTML_FILE_NAME);
                exit();
            }
        }
    }

    // 判断是否需要静态缓存
    private static function requireHtmlCache()
    {
        // 分析当前的静态规则
        $htmls = C('HTML_CACHE_RULES'); // 读取静态规则
        if (!empty($htmls)) {
            $htmls = array_change_key_case($htmls);
            // 静态规则文件定义格式 actionName=>array('静态规则','缓存时间','附加规则')
            // 'read'=>array('{id},{name}',60,'md5') 必须保证静态规则的唯一性 和 可判断性
            // 检测静态规则
            $moduleName = strtolower(MODULE_NAME);
            $actionName = strtolower(ACTION_NAME);
            if (isset($htmls[$moduleName . ':' . $actionName])) {
                $html = $htmls[$moduleName . ':' . $actionName]; // 某个模块的操作的静态规则
            } elseif (isset($htmls[$moduleName . ':'])) {
// 某个模块的静态规则
                $html = $htmls[$moduleName . ':'];
            } elseif (isset($htmls[$actionName])) {
                $html = $htmls[$actionName]; // 所有操作的静态规则
            } elseif (isset($htmls['*'])) {
                $html = $htmls['*']; // 全局静态规则
            } elseif (isset($htmls['empty:index']) && !class_exists(MODULE_NAME . 'Action')) {
                $html = $htmls['empty:index']; // 空模块静态规则
            } elseif (isset($htmls[$moduleName . ':_empty']) && self::isEmptyAction(MODULE_NAME, ACTION_NAME)) {
                $html = $htmls[$moduleName . ':_empty']; // 空操作静态规则
            }
            if (!empty($html)) {
                // 解读静态规则
                $rule = $html[0];
                // 以$_开头的系统变量
                $rule = preg_replace('/{\$(_\w+)\.(\w+)\|(\w+)}/e', "\\3(\$\\1['\\2'])", $rule);
                $rule = preg_replace('/{\$(_\w+)\.(\w+)}/e', "\$\\1['\\2']", $rule);
                // {ID|FUN} GET变量的简写
                $rule = preg_replace('/{(\w+)\|(\w+)}/e', "\\2(\$_GET['\\1'])", $rule);
                $rule = preg_replace('/{(\w+)}/e', "\$_GET['\\1']", $rule);
                // 特殊系统变量
                $rule = str_ireplace(
                    ['{:app}', '{:module}', '{:action}', '{:group}'],
                    [APP_NAME, MODULE_NAME, ACTION_NAME, defined('GROUP_NAME') ? GROUP_NAME : ''],
                    $rule);
                // {|FUN} 单独使用函数
                $rule = preg_replace('/{|(\w+)}/e', "\\1()", $rule);
                if (!empty($html[2])) {
                    $rule = $html[2]($rule);
                }
                // 应用附加函数
                $cacheTime = isset($html[1]) ? $html[1] : C('HTML_CACHE_TIME'); // 缓存有效期
                // 当前缓存文件
                define('HTML_FILE_NAME', HTML_PATH . $rule . C('HTML_FILE_SUFFIX'));
                return $cacheTime;
            }
        }
        // 无需缓存
        return false;
    }

    /**
     * 检查静态HTML文件是否有效
     * 如果无效需要重新更新
     * @access public
     * @param string $cacheFile  静态文件名
     * @param integer $cacheTime  缓存有效期
     * @return boolen
     */
    public static function checkHTMLCache($cacheFile = '', $cacheTime = '')
    {
        if (!is_file($cacheFile)) {
            return false;
        } elseif (filemtime(C('TEMPLATE_NAME')) > filemtime($cacheFile)) {
            // 模板文件如果更新静态文件需要更新
            return false;
        } elseif (!is_numeric($cacheTime) && function_exists($cacheTime)) {
            return $cacheTime($cacheFile);
        } elseif (0 != $cacheTime && NOW_TIME > filemtime($cacheFile) + $cacheTime) {
            // 文件是否在有效期
            return false;
        }
        //静态文件有效
        return true;
    }

    //检测是否是空操作
    private static function isEmptyAction($module, $action)
    {
        $className = $module . 'Action';
        $class     = new $className;
        return !method_exists($class, $action);
    }

}
