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
 * 系统行为扩展：定位模板文件
 */
class LocationTemplate
{
    // 行为扩展的执行入口必须是run
    public function run(&$templateFile)
    {
        // 自动定位模板文件
        if (!is_file($templateFile)) {
            $templateFile = $this->parseTemplateFile($templateFile);
        }
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param string $templateFile 文件名
     * @return string
     */
    private function parseTemplateFile($template)
    {
        $template = str_replace(':', '/', $template);
        if ('' == $template) {
            // 如果模板文件名为空 按照默认规则定位
            $template = CONTROLLER_NAME . '/' . ACTION_NAME;
        } elseif (false === strpos($template, '/')) {
            $template = CONTROLLER_NAME . '/' . $template;
        } elseif (false === strpos($template, '.')) {
            $template = $template;
        }
        $templateFile = MODULE_PATH . 'view/' . $template . '.html';
        return $templateFile;
    }
}
