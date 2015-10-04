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

/**
 * 系统行为扩展：模板内容输出替换
 */
class ContentReplace 
{

    // 行为扩展的执行入口必须是run
    public function run(&$content)
    {
        $content = $this->templateContentReplace($content);
    }

    /**
     * 模板内容替换
     * @access protected
     * @param string $content 模板内容
     * @return string
     */
    protected function templateContentReplace($content)
    {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            $script_name    =   rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/');
        } else {
            $script_name    =   rtrim($_SERVER['SCRIPT_NAME'],'/');
        }
        define('ROOT_URL',   rtrim(dirname(str_replace("\\","\/",$script_name)),'/'));
        define('MODULE_URL',    ROOT_URL.'/'.(defined('MODULE_ALIAS')?MODULE_ALIAS:MODULE_NAME);
        define('CONTROLLER_URL',  MODULE_URL.'/'.CONTROLLER_NAME;
        define('ACTION_URL',    CONTROLLER_URL.'/'.ACTION_NAME);

        // 系统默认的特殊变量替换
        $replace =  [
            '__ROOT__'      =>  ROOT_URL,       // 当前网站地址
            '__APP__'       =>  MODULE_URL,        // 当前项目地址
            '__CONTROLL__'  =>  CONTROLLER_URL,     // 当前操作地址
            '__URL__'       =>  CONTROLLER_URL,
            '__ACTION__'    =>  ACTION_URL,     // 当前操作地址
            '__SELF__'      =>  $_SERVER['PHP_SELF'],       // 当前页面地址
            '__PUBLIC__'    =>  ROOT_URL.'/Public',// 站点公共目录
        ];
        // 允许用户自定义模板的字符串替换
        if(is_array(Config::get('tmpl_parse_string')) ){
            $replace =  array_merge($replace,Config::get('tmpl_parse_string'));
        }
        $content = str_replace(array_keys($replace),array_values($replace),$content);
        return $content;
    }

}
