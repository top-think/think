<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
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
     * @param string $url URL表达式，
     * 格式：'[模块/控制器/操作]?参数1=值1&参数2=值2...'
     * @控制器/操作?参数1=值1&参数2=值2...
     * \\命名空间类\\方法?参数1=值1&参数2=值2...
     * @param string|array $vars 传入的参数，支持数组和字符串
     * @param string $suffix 伪静态后缀，默认为true表示获取配置值
     * @param boolean $domain 是否显示域名
     * @return string
     */
    public static function build($url = '', $vars = '', $suffix = true, $domain = true)
    {
        // 检测是否存在路由别名
        if ($aliasUrl = Route::getRouteUrl($url, $vars)) {
            return $aliasUrl;
        }
        // 解析参数
        if (is_string($vars)) {
            // aaa=1&bbb=2 转换成数组
            parse_str($vars, $vars);
        } elseif (!is_array($vars)) {
            $vars = [];
        }

        if (strpos($url, '?')) {
            list($url, $params) = explode('?', $url);
            parse_str($params, $params);
            $vars = array_merge($params, $vars);
        }

        // 检测路由
        $match = self::checkRoute($url, $vars, $domain, $type);
        if (false === $match) {
            // 路由不存在 直接解析
            if (false !== strpos($url, '\\')) {
                // 解析到类
                $url = ltrim(str_replace('\\', '/', $url), '/');
            } elseif (0 === strpos($url, '@')) {
                // 解析到控制器
                $url = substr($url, 1);
            } else {
                // 解析到 模块/控制器/操作
                $path = explode('/', $url);
                $len  = count($path);
                if (2 == $len) {
                    $url = (APP_MULTI_MODULE ? MODULE_NAME . '/' : '') . $url;
                } elseif (1 == $len) {
                    $url = (APP_MULTI_MODULE ? MODULE_NAME . '/' : '') . CONTROLLER_NAME . '/' . $url;
                }
            }
        } else {
            // 处理路由规则中的特殊内容
            $url = str_replace(['\\d', '$'], '', $match);
        }
        // 检测URL绑定
        $type = Route::bind('type');
        if ($type) {
            $bind = Route::bind($type);
            if (0 === strpos($url, $bind)) {
                $url = substr($url, strlen($bind) + 1);
            }
        }
        // 还原URL分隔符
        $depr = Config::get('pathinfo_depr');
        $url  = str_replace('/', $depr, $url);
        // 替换变量
        $params = [];
        foreach ($vars as $key => $val) {
            if (false !== strpos($url, '[:' . $key . ']')) {
                $url = str_replace('[:' . $key . ']', $val, $url);
            } elseif (false !== strpos($url, ':' . $key)) {
                $url = str_replace(':' . $key, $val, $url);
            } else {
                $params[$key] = $val;
            }
        }
        // URL组装
        $url = Config::get('base_url') . '/' . $url;
        // URL后缀
        $suffix = self::parseSuffix($suffix);
        // 参数组装
        if (!empty($params)) {
            // 添加参数
            if (Config::get('url_common_param')) {
                $vars = urldecode(http_build_query($vars));
                $url .= $suffix . '?' . $vars;
            } else {
                foreach ($params as $var => $val) {
                    if ('' !== trim($val)) {
                        $url .= $depr . $var . $depr . urlencode($val);
                    }
                }
                $url .= $suffix;
            }
        } else {
            $url .= $suffix;
        }

        if ($domain) {
            if (true === $domain) {
                $domain = $_SERVER['HTTP_HOST'];
                if (Config::get('url_domain_deploy')) {
                    // 开启子域名部署
                    $domain = 'localhost' == $domain ? 'localhost' : 'www' . strstr($_SERVER['HTTP_HOST'], '.');
                    foreach (Route::domain() as $key => $rule) {
                        $rule = is_array($rule) ? $rule[0] : $rule;
                        if (false === strpos($key, '*') && 0 === strpos($url, $rule)) {
                            $domain = $key . strstr($domain, '.'); // 生成对应子域名
                            $url    = substr_replace($url, '', 0, strlen($rule));
                            break;
                        }
                    }
                }
            }
            $url = (self::isSsl() ? 'https://' : 'http://') . $domain . $url;
        }
        return $url;
    }

    protected static function checkRoute($url, $vars, $domain)
    {
        // 获取路由定义
        $rules = Route::any();
        // 全局变量规则
        $pattern = Route::pattern();
        foreach ($rules as $rule => $val) {
            if (!empty($val['routes'])) {
                // 匹配到路由分组
                foreach ($val['routes'] as $key => $route) {
                    if (is_numeric($key)) {
                        $key = array_shift($route);
                    }
                    $check  = isset($route[2]) ? array_merge($pattern, $route[2]) : $pattern;
                    $route  = $route[0];
                    $route  = is_array($route) ? $route[0] : $route;
                    $result = $rule . Config::get('pathinfo_depr') . $key;
                    if ($route == $url && self::checkPattern($result, $vars, $check)) {
                        return $result;
                    }
                }
            } else {
                if (is_numeric($rule)) {
                    $rule = array_shift($val);
                }
                $route = $val['route'];
                $route = is_array($route) ? $route[0] : $route;
                $check = isset($val['pattern']) ? array_merge($pattern, $val['pattern']) : $pattern;
                if ($route == $url && self::checkPattern($rule, $vars, $check)) {
                    return $rule;
                }
            }
        }
        return false;
    }

    // 检测变量规则
    protected static function checkPattern($rule, $vars, $pattern)
    {
        // 检测路由规则中的变量
        // 检测是否设置了参数分隔符
        if ($depr = Config::get('url_params_depr')) {
            $rule = str_replace($depr, '/', $rule);
        }
        // 提取路由规则中的变量
        $var   = [];
        $array = explode('/', $rule);
        foreach ($array as $val) {
            $optional = false;
            if (0 === strpos($val, '[:')) {
                // 可选参数
                $val      = substr($val, 1, -1);
                $optional = true;
            }
            if (0 === strpos($val, ':')) {
                // URL变量
                if (strpos($val, '\\')) {
                    $name = substr($val, 1, -2);
                } else {
                    $name = substr($val, 1);
                }
                if (!$optional && !isset($vars[$name])) {
                    // 变量未设置
                    return false;
                }
            }
        }
        foreach ($vars as $name => $val) {
            if (isset($pattern[$name]) && !preg_match('/^' . $pattern[$name] . '$/', $val)) {
                // 检查变量规则
                return false;
            }
        }
        return true;
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
