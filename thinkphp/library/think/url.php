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

class Url
{

    /**
     * URL组装 支持不同URL模式
     * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
     * @param string|array $vars 传入的参数，支持数组和字符串
     * @param string $suffix 伪静态后缀，默认为true表示获取配置值
     * @param boolean $domain 是否显示域名
     * @return string
     */
    public static function build($url = '', $vars = '', $suffix = true, $domain = false)
    {
        // 解析URL和参数
        list($url, $vars, $domain, $anchor) = self::parseUrl($url, $vars, $domain);

        // URL组装
        $depr = Config::get('pathinfo_depr');
        if ($url) {
            if (0 === strpos($url, '/')) {
                // 定义路由
                $route = true;
                $url   = substr($url, 1);
                if ('/' != $depr) {
                    $url = str_replace('/', $depr, $url);
                }
            } else {
                if ('/' != $depr) {
                    // 安全替换
                    $url = str_replace('/', $depr, $url);
                }
                // 解析模块、控制器和操作
                $url           = trim($url, $depr);
                $path          = explode($depr, $url);
                $var           = [];
                $var['action'] = !empty($path) ? array_pop($path) : ACTION_NAME;
                if (!defined('BIND_CONTROLLER')) {
                    $var['controller'] = !empty($path) ? array_pop($path) : CONTROLLER_NAME;
                }
                if (!defined('BIND_MODULE')) {
                    $var['module'] = !empty($path) ? array_pop($path) : MODULE_NAME;
                }
            }
        }
        // URL组装
        $url = Config::get('base_url') . '/' . (isset($route) ? rtrim($url, $depr) : implode($depr, array_reverse($var)));
        // URL后缀
        $suffix = self::parseSuffix($suffix);
        // 参数组装
        if (!empty($vars)) {
            // 添加参数
            if (Config::get('url_common_param')) {
                $vars = urldecode(http_build_query($vars));
                $url .= $suffix . '?' . $vars;
            } else {
                foreach ($vars as $var => $val) {
                    if ('' !== trim($val)) {
                        $url .= $depr . $var . $depr . urlencode($val);
                    }
                }
                $url .= $suffix;
            }
        } else {
            $url .= $suffix;
        }

        if (!empty($anchor)) {
            $url .= '#' . $anchor;
        }
        if ($domain) {
            $url = (self::isSsl() ? 'https://' : 'http://') . $domain . $url;
        }
        return $url;
    }

    // 解析URL后缀
    protected static function parseSuffix($suffix)
    {
        if ($suffix) {
            $suffix = true === $suffix ? Config::get('url_html_suffix') : $suffix;
            if ($pos = strpos($suffix, '|')) {
                $suffix = substr($suffix, 0, $pos);
            }
        }
        return (empty($suffix) || 0 === strpos($suffix, '.')) ? $suffix : '.' . $suffix;
    }

    // 根据路由名称和参数生成URL地址
    public static function route($name, $params = [])
    {
        $url = Route::getRouteUrl($name, $params);
        if (false === $url) {
            $url = self::build($name, $params);
        }
        return $url;
    }

    // 解析URL和参数 域名
    protected static function parseUrl($url, $vars, $domain)
    {
        $info   = parse_url($url);
        $url    = !empty($info['path']) ? $info['path'] : ACTION_NAME;
        $anchor = '';
        if (isset($info['fragment'])) {
            // 解析锚点
            $anchor = $info['fragment'];
            if (false !== strpos($anchor, '?')) {
                // 解析参数
                list($anchor, $info['query']) = explode('?', $anchor, 2);
            }
            if (false !== strpos($anchor, '@')) {
                // 解析域名
                list($anchor, $host) = explode('@', $anchor, 2);
            }
        } elseif (false !== strpos($url, '@')) {
            // 解析域名
            list($url, $host) = explode('@', $info['path'], 2);
        }
        // 解析子域名
        if (isset($host)) {
            $domain = $host . (strpos($host, '.') ? '' : strstr($_SERVER['HTTP_HOST'], '.'));
        } elseif (true === $domain) {
            $domain = $_SERVER['HTTP_HOST'];
            if (Config::get('url_domain_deplay')) {
                // 开启子域名部署
                $domain = 'localhost' == $domain ? 'localhost' : 'www' . strstr($_SERVER['HTTP_HOST'], '.');
                // '子域名'=>array('项目[/分组]');
                foreach (Config::get('url_domain_rules') as $key => $rule) {
                    if (false === strpos($key, '*') && 0 === strpos($url, $rule[0])) {
                        $domain = $key . strstr($domain, '.'); // 生成对应子域名
                        $url    = substr_replace($url, '', 0, strlen($rule[0]));
                        break;
                    }
                }
            }
        }
        // 解析参数
        if (is_string($vars)) {
            // aaa=1&bbb=2 转换成数组
            parse_str($vars, $vars);
        } elseif (!is_array($vars)) {
            $vars = [];
        }
        if (isset($info['query'])) {
            // 解析地址里面参数 合并到vars
            parse_str($info['query'], $params);
            $vars = array_merge($params, $vars);
        }
        return [$url, $vars, $domain, $anchor];
    }

    /**
     * 判断是否SSL协议
     * @return boolean
     */
    public static function isSsl()
    {
        if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
            return true;
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }
        return false;
    }
}
