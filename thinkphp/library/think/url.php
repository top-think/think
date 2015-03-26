<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

class Url {

    static public function param($num,$default=''){
        $paths = explode(Config::get('url_pathinfo_depr'),trim($_SERVER['PATH_INFO'],'/'));
        return isset($paths[$num])?$paths[$num]:$default;
    }

    static public function route($route){
    }

    /**
     * URL组装 支持不同URL模式
     * @param string $url URL表达式，格式：'[分组/模块/操作#锚点@域名]?参数1=值1&参数2=值2...'
     * @param string|array $vars 传入的参数，支持数组和字符串
     * @param string $suffix 伪静态后缀，默认为true表示获取配置值
     * @param boolean $domain 是否显示域名
     * @return string
     */
    static public function build($url='',$vars='',$suffix=true,$domain=false) {
        // 解析URL
        $info   =  parse_url($url);
        $url    =  !empty($info['path'])?$info['path']:ACTION_NAME;
        if(isset($info['fragment'])) { // 解析锚点
            $anchor =   $info['fragment'];
            if(false !== strpos($anchor,'?')) { // 解析参数
                list($anchor,$info['query']) = explode('?',$anchor,2);
            }        
            if(false !== strpos($anchor,'@')) { // 解析域名
                list($anchor,$host)    =   explode('@',$anchor, 2);
            }
        }elseif(false !== strpos($url,'@')) { // 解析域名
            list($url,$host)    =   explode('@',$info['path'], 2);
        }
        // 解析子域名
        if(isset($host)) {
            $domain = $host.(strpos($host,'.')?'':strstr($_SERVER['HTTP_HOST'],'.'));
        }elseif($domain===true){
            $domain = $_SERVER['HTTP_HOST'];
            if(Config::get('app_sub_domain_deplay') ) { // 开启子域名部署
                $domain = $domain=='localhost'?'localhost':'www'.strstr($_SERVER['HTTP_HOST'],'.');
                // '子域名'=>array('项目[/分组]');
                foreach (Config::get('app_sub_domain_rules') as $key => $rule) {
                    if(false === strpos($key,'*') && 0=== strpos($url,$rule[0])) {
                        $domain = $key.strstr($domain,'.'); // 生成对应子域名
                        $url    =  substr_replace($url,'',0,strlen($rule[0]));
                        break;
                    }
                }
            }
        }

        // 解析参数
        if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
            parse_str($vars,$vars);
        }elseif(!is_array($vars)){
            $vars = [];
        }
        if(isset($info['query'])) { // 解析地址里面参数 合并到vars
            parse_str($info['query'],$params);
            $vars = array_merge($params,$vars);
        }

        // URL组装
        $depr = Config::get('pathinfo_depr');
        if($url) {
            if(0=== strpos($url,'/')) {// 定义路由
                $route      =   true;
                $url        =   substr($url,1);
                if('/' != $depr) {
                    $url    =   str_replace('/',$depr,$url);
                }
            }else{
                if('/' != $depr) { // 安全替换
                    $url    =   str_replace('/',$depr,$url);
                }
                // 解析分组、模块和操作
                $url        =   trim($url,$depr);
                $path       =   explode($depr,$url);
                $var        =   [];
                $var[Config::get('var_action')]       =   !empty($path)?array_pop($path):ACTION_NAME;
                if(!defined('BIND_CONTROLLER')){
                    $var[Config::get('var_controller')]       =   !empty($path)?array_pop($path):CONTROLLER_NAME;
                }
                if(!defined('BIND_MODULE')){
                    $var[Config::get('var_module')]    =   !empty($path)?array_pop($path):MODULE_NAME;
                }
            }
        }

        if(Config::get('url_model') == 0) { // 普通模式URL转换
            $url        =   Config::get('base_url').'?'.http_build_query(array_reverse($var));
            if(!empty($vars)) {
                $vars   =   urldecode(http_build_query($vars));
                $url   .=   '&'.$vars;
            }
        }else{ // PATHINFO模式或者兼容URL模式
            if(isset($route)) {
                $url    =   Config::get('base_url').'/'.rtrim($url,$depr);
            }else{
                $url    =   Config::get('base_url').'/'.implode($depr,array_reverse($var));
            }
            if(!empty($vars)) { // 添加参数
                foreach ($vars as $var => $val){
                    if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
                }                
            }
            if($suffix) {
                $suffix   =  $suffix===true?Config::get('url_html_suffix'):$suffix;
                if($pos = strpos($suffix, '|')){
                    $suffix = substr($suffix, 0, $pos);
                }
                if($suffix && '/' != substr($url,-1)){
                    $url  .=  '.'.ltrim($suffix,'.');
                }
            }
        }
        if(isset($anchor)){
            $url  .= '#'.$anchor;
        }
        if($domain) {
            $url   =  (self::is_ssl()?'https://':'http://').$domain.$url;
        }
        return $url;
    }

    /**
     * 判断是否SSL协议
     * @return boolean
     */
    static public function is_ssl() {
        if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
            return true;
        }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
            return true;
        }
        return false;
    }
}
