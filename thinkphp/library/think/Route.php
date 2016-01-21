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

class Route
{
    // 路由规则
    private static $rules = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'DELETE' => [],
        'HEAD'   => [],
        '*'      => [],
    ];

    // REST路由操作方法定义
    private static $rest = [
        'index'  => ['GET', '', 'index'],
        'create' => ['GET', '/create', 'create'],
        'read'   => ['GET', '/:id', 'read'],
        'edit'   => ['GET', '/:id/edit', 'edit'],
        'save'   => ['POST', '', 'save'],
        'update' => ['PUT', '/:id', 'update'],
        'delete' => ['DELETE', '/:id', 'delete'],
    ];

    // URL映射规则
    private static $map = [];
    // 子域名部署规则
    private static $domain = [];
    // 子域名
    private static $subDomain = '';
    // 变量规则
    private static $pattern = [];
    // 域名绑定
    private static $bind = [];

    // 添加URL映射规则
    public static function map($map = '', $route = '')
    {
        return self::setting('map', $map, $route);
    }

    // 添加变量规则
    public static function pattern($name = '', $rule = '')
    {
        return self::setting('pattern', $name, $rule);
    }

    // 添加子域名部署规则
    public static function domain($domain = '', $rule = '')
    {
        return self::setting('domain', $domain, $rule);
    }

    // 属性设置
    private static function setting($var, $name = '', $value = '')
    {
        if (is_array($name)) {
            self::${$var} = self::${$var}+$name;
        } elseif (empty($name)) {
            return self::${$var};
        } else {
            self::${$var}[$name] = $value;
        }
    }

    // 对路由进行绑定和获取绑定信息
    public static function bind($type, $bind = '')
    {
        if ('' == $bind) {
            return isset(self::$bind[$type]) ? self::$bind[$type] : null;
        } else {
            self::$bind = ['type' => $type, $type => $bind];
        }
    }

    // 注册路由规则
    public static function register($rule, $route = '', $type = '*', $option = [], $pattern = [])
    {
        if (strpos($type, '|')) {
            foreach (explode('|', $type) as $val) {
                self::register($rule, $route, $val, $option);
            }
        } else {
            if (is_array($rule)) {
                // 检查域名部署
                if (isset($rule['__domain__'])) {
                    self::domain($rule['__domain__']);
                    unset($rule['__domain__']);
                }
                // 检查变量规则
                if (isset($rule['__pattern__'])) {
                    self::pattern($rule['__pattern__']);
                    unset($rule['__pattern__']);
                }
                // 检查路由映射
                if (isset($rule['__map__'])) {
                    self::map($rule['__map__']);
                    unset($rule['__map__']);
                }
                // 检查资源路由
                if (isset($rule['__rest__'])) {
                    self::resource($rule['__rest__']);
                    unset($rule['__rest__']);
                }

                foreach ($rule as $key => $val) {
                    if (is_numeric($key)) {
                        $key = array_shift($val);
                    }
                    if (0 === strpos($key, '[')) {
                        if (empty($val)) {
                            break;
                        }
                        $key    = substr($key, 1, -1);
                        $result = ['routes' => $val, 'option' => $option, 'pattern' => $pattern];
                    } elseif (is_array($val)) {
                        $result = ['route' => $val[0], 'option' => $val[1], 'pattern' => $val[2]];
                    } else {
                        $result = ['route' => $val, 'option' => $option, 'pattern' => $pattern];
                    }
                    self::$rules[$type][$key] = $result;
                }
            } else {
                if (0 === strpos($rule, '[')) {
                    $rule   = substr($rule, 1, -1);
                    $result = ['routes' => $route, 'option' => $option, 'pattern' => $pattern];
                } elseif (is_array($route)) {
                    $result = ['route' => !empty($route[0]) ? $route[0] : '', 'option' => !empty($route[1]) ? $route[1] : '', 'pattern' => !empty($route[2]) ? $route[2] : ''];
                } else {
                    $result = ['route' => $route, 'option' => $option, 'pattern' => $pattern];
                }
                self::$rules[$type][$rule] = $result;
            }
        }
    }

    // 路由分组
    public static function group($name, $routes = [], $type = '*', $option = [], $pattern = [])
    {
        self::$rules[$type][$name] = ['routes' => $routes, 'option' => $option, 'pattern' => $pattern];
    }

    // 注册任意请求的路由规则
    public static function any($rule, $route = '', $option = [], $pattern = [])
    {
        self::register($rule, $route, '*', $option, $pattern);
    }

    // 注册get请求的路由规则
    public static function get($rule, $route = '', $option = [], $pattern = [])
    {
        self::register($rule, $route, 'GET', $option, $pattern);
    }

    // 注册post请求的路由规则
    public static function post($rule, $route = '', $option = [], $pattern = [])
    {
        self::register($rule, $route, 'POST', $option, $pattern);
    }

    // 注册put请求的路由规则
    public static function put($rule, $route = '', $option = [], $pattern = [])
    {
        self::register($rule, $route, 'PUT', $option, $pattern);
    }

    // 注册delete请求的路由规则
    public static function delete($rule, $route = '', $option = [], $pattern = [])
    {
        self::register($rule, $route, 'DELETE', $option, $pattern);
    }

    // 注册资源路由
    public static function resource($rule, $route = '', $option = [], $pattern = [])
    {
        if (is_array($rule)) {
            foreach ($rule as $key => $val) {
                if (is_array($val)) {
                    list($val, $option, $pattern) = array_pad($val, 3, []);
                }
                self::resource($key, $val, $option, $pattern);
            }
        } else {
            if (strpos($rule, '.')) {
                // 注册嵌套资源路由
                $array = explode('.', $rule);
                $last  = array_pop($array);
                $item  = [];
                foreach ($array as $val) {
                    $item[] = $val . '/:' . (isset($option['var'][$val]) ? $option['var'][$val] : $val . '_id');
                }
                $rule = implode('/', $item) . '/' . $last;
            }
            // 注册资源路由
            foreach (self::$rest as $key => $val) {
                if ((isset($option['only']) && !in_array($key, $option['only']))
                    || (isset($option['except']) && in_array($key, $option['except']))) {
                    continue;
                }
                if (strpos($val[1], ':id') && isset($option['var'][$rule])) {
                    $val[1] = str_replace(':id', ':' . $option['var'][$rule], $val[1]);
                }
                self::register($rule . $val[1] . '$', $route . '/' . $val[2], $val[0], $option, $pattern);
            }
        }
    }

    // rest方法定义和修改
    public static function rest($name, $resource = [])
    {
        if (is_array($name)) {
            self::$rest = array_merge(self::$rest, $name);
        } else {
            self::$rest[$name] = $resource;
        }
    }

    // 获取路由定义
    public static function getRules($method = '')
    {
        if ($method) {
            return self::$rules[$method];
        } else {
            return self::$rules['*'] + self::$rules['GET'] + self::$rules['POST'] + self::$rules['PUT'] + self::$rules['DELETE'];
        }
    }

    // 检测子域名部署
    public static function checkDomain()
    {
        // 域名规则
        $rules = self::$domain;
        // 开启子域名部署 支持二级和三级域名
        if (!empty($rules)) {
            if (isset($rules[$_SERVER['HTTP_HOST']])) {
                // 完整域名或者IP配置
                $rule = $rules[$_SERVER['HTTP_HOST']];
            } else {
                $rootDomain = Config::get('url_domain_root');
                if ($rootDomain) {
                    // 配置域名根 例如 thinkphp.cn 163.com.cn 如果是国家级域名 com.cn net.cn 之类的域名需要配置
                    $domain = explode('.', rtrim(stristr($_SERVER['HTTP_HOST'], $rootDomain, true), '.'));
                } else {
                    $domain = explode('.', $_SERVER['HTTP_HOST'], -2);
                }
                // 子域名配置
                if (!empty($domain)) {
                    // 当前子域名
                    $subDomain       = implode('.', $domain);
                    self::$subDomain = $subDomain;
                    $domain2         = array_pop($domain);
                    if ($domain) {
                        // 存在三级域名
                        $domain3 = array_pop($domain);
                    }
                    if ($subDomain && isset($rules[$subDomain])) {
                        // 子域名配置
                        $rule = $rules[$subDomain];
                    } elseif (isset($rules['*.' . $domain2]) && !empty($domain3)) {
                        // 泛三级域名
                        $rule      = $rules['*.' . $domain2];
                        $panDomain = $domain3;
                    } elseif (isset($rules['*']) && !empty($domain2)) {
                        // 泛二级域名
                        if ('www' != $domain2) {
                            $rule      = $rules['*'];
                            $panDomain = $domain2;
                        }
                    }
                }
            }
            if (!empty($rule)) {
                // 子域名部署规则
                if ($rule instanceof \Closure) {
                    // 执行闭包
                    $reflect    = new \ReflectionFunction($rule);
                    self::$bind = $reflect->invokeArgs([]);
                    return;
                }

                if (strpos($rule, '?')) {
                    // 传入其它参数
                    $array  = parse_url($rule);
                    $result = $array['path'];
                    parse_str($array['query'], $params);
                    if (isset($panDomain)) {
                        $pos = array_search('*', $params);
                        if (false !== $pos) {
                            // 泛域名作为参数
                            $params[$pos] = $panDomain;
                        }
                    }
                    $_GET = array_merge($_GET, $params);
                } else {
                    $result = $rule;
                }

                if (0 === strpos($result, '\\')) {
                    // 绑定到命名空间 例如 \app\index\behavior
                    self::$bind = ['type' => 'namespace', 'namespace' => $result];
                } elseif (0 === strpos($result, '@')) {
                    // 绑定到类 例如 \app\index\controller\User
                    self::$bind = ['type' => 'class', 'class' => substr($result, 1)];
                } elseif (0 === strpos($result, '[')) {
                    // 绑定到分组 例如 [user]
                    self::$bind = ['type' => 'group', 'group' => substr($result, 1, -1)];
                } else {
                    // 绑定到模块/控制器 例如 index/user
                    self::$bind = ['type' => 'module', 'module' => $result];
                }
            }
        }
    }

    // 检测URL路由
    public static function check($url, $depr = '/', $checkDomain = false)
    {
        // 分隔符替换 确保路由定义使用统一的分隔符
        if ('/' != $depr) {
            $url = str_replace($depr, '/', $url);
        }

        // 优先检测是否存在PATH_INFO
        if (empty($url)) {
            $url = '/';
        }

        if (isset(self::$map[$url])) {
            // URL映射
            return self::parseUrl(self::$map[$url], $depr);
        }

        // 获取当前请求类型的路由规则
        $rules = self::$rules[REQUEST_METHOD];

        if (!empty(self::$rules['*'])) {
            // 合并任意请求的路由规则
            $rules = array_merge(self::$rules['*'], $rules);
        }

        // 检测域名部署
        if ($checkDomain) {
            self::checkDomain();
        }

        // 检测URL绑定
        $return = self::checkUrlBind($url, $rules);
        if ($return) {
            return $return;
        }

        // 路由规则检测
        if (!empty($rules)) {
            foreach ($rules as $rule => $val) {
                $option  = $val['option'];
                $pattern = $val['pattern'];

                // 参数有效性检查
                if (!self::checkOption($option)) {
                    continue;
                }

                if (!empty($val['routes'])) {
                    // 分组路由
                    if (0 !== strpos($url, $rule)) {
                        continue;
                    }
                    // 匹配到路由分组
                    foreach ($val['routes'] as $key => $route) {
                        if (is_numeric($key)) {
                            $key = array_shift($route);
                        }
                        $url1 = substr($url, strlen($rule) + 1);
                        // 检查规则路由
                        if (is_array($route)) {
                            $option1 = $route[1];
                            // 检查参数有效性
                            if (!self::checkOption($option1)) {
                                continue;
                            }
                            $pattern = array_merge($pattern, isset($route[2]) ? $route[2] : []);
                            $route   = $route[0];
                        }
                        $result = self::checkRule($key, $route, $url1, $pattern);
                        if (false !== $result) {
                            return $result;
                        }
                    }
                } else {
                    if (is_numeric($rule)) {
                        $rule = array_shift($val);
                    }
                    // 单项路由
                    $route = !empty($val['route']) ? $val['route'] : '';
                    // 规则路由
                    $result = self::checkRule($rule, $route, $url, $pattern);
                    if (false !== $result) {
                        return $result;
                    }
                }
            }
        }
        return false;
    }

    // 检测URL绑定
    private static function checkUrlBind(&$url, &$rules)
    {
        if (!empty(self::$bind['type'])) {
            // 如果有URL绑定 则进行绑定检测
            switch (self::$bind['type']) {
                case 'class':
                    // 绑定到类
                    $array = explode('/', $url, 2);
                    if (!empty($array[1])) {
                        self::parseUrlParams($array[1]);
                    }
                    return ['type' => 'method', 'method' => [self::$bind['class'], $array[0] ?: Config::get('default_action')], 'params' => []];
                case 'namespace':
                    // 绑定到命名空间
                    $array  = explode('/', $url, 3);
                    $class  = !empty($array[0]) ? $array[0] : Config::get('default_controller');
                    $method = !empty($array[1]) ? $array[1] : Config::get('default_action');
                    if (!empty($array[2])) {
                        self::parseUrlParams($array[2]);
                    }
                    return ['type' => 'method', 'method' => [self::$bind['namespace'] . '\\' . $class, $method], 'params' => []];
                case 'module':
                    // 如果有模块/控制器绑定 针对路由到 模块/控制器 有效
                    $url = self::$bind['module'] . '/' . $url;
                    break;
                case 'group':
                    // 绑定到路由分组
                    $key = self::$bind['group'];
                    if (array_key_exists($key, $rules)) {
                        $rules = [$key => $rules[self::$bind['group']]];
                    }
            }
        }
        return false;
    }

    // 路由参数有效性检查
    private static function checkOption($option)
    {
        // 请求类型检测
        if ((isset($option['method']) && false === stripos($option['method'], REQUEST_METHOD))
            || (isset($option['ext']) && false === stripos($option['ext'], __EXT__)) // 伪静态后缀检测
             || (isset($option['domain']) && !in_array($option['domain'], [$_SERVER['HTTP_HOST'], self::$subDomain])) // 域名检测
             || (!empty($option['https']) && !self::isSsl()) // https检测
             || (!empty($option['behavior']) && false === Hook::exec($option['behavior'])) // 行为检测
             || (!empty($option['callback']) && is_callable($option['callback']) && false === call_user_func($option['callback'])) // 自定义检测
        ) {
            return false;
        }
        return true;
    }

    /**
     * 检查规则路由
     */
    private static function checkRule($rule, $route, $url, $pattern)
    {
        // 检查完整规则定义
        if (isset($pattern['__url__']) && !preg_match('/^' . $pattern['__url__'] . '/', $url)) {
            return false;
        }
        // 检测是否设置了参数分隔符
        if ($depr = Config::get('url_params_depr')) {
            $url  = str_replace($depr, '/', $url);
            $rule = str_replace($depr, '/', $rule);
        }
        $len1 = substr_count($url, '/');
        $len2 = substr_count($rule, '/');
        if ($len1 >= $len2 || strpos($rule, '[')) {
            if ('$' == substr($rule, -1, 1)) {
                // 完整匹配
                if ($len1 != $len2) {
                    return false;
                } else {
                    $rule = substr($rule, 0, -1);
                }
            }
            $pattern = array_merge(self::$pattern, $pattern);
            if (false !== $match = self::match($url, $rule, $pattern)) {
                if ($route instanceof \Closure) {
                    // 执行闭包
                    return ['type' => 'function', 'function' => $route, 'params' => $match];
                }
                return self::parseRule($rule, $route, $url);
            }
        }
        return false;
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
        } else {
            return false;
        }
    }

    // 解析模块的URL地址 [模块/控制器/操作?]参数1=值1&参数2=值2...
    public static function parseUrl($url, $depr = '/')
    {
        if (isset(self::$bind['module'])) {
            // 如果有模块/控制器绑定
            $url = self::$bind['module'] . '/' . $url;
        }
        // 分隔符替换 确保路由定义使用统一的分隔符
        if ('/' != $depr) {
            $url = str_replace($depr, '/', $url);
        }
        $result = self::parseRoute($url, true);
        if (!empty($result['var'])) {
            $_GET = array_merge($result['var'], $_GET);
        }
        return ['type' => 'module', 'module' => $result['route']];
    }

    // 解析规范的路由地址
    // 地址格式 [模块/控制器/操作?]参数1=值1&参数2=值2...
    private static function parseRoute($url, $reverse = false)
    {
        $var = [];
        if (false !== strpos($url, '?')) {
            // [模块/控制器/操作?]参数1=值1&参数2=值2...
            $info = parse_url($url);
            $path = explode('/', $info['path'], 4);
            parse_str($info['query'], $var);
        } elseif (strpos($url, '/')) {
            // [模块/控制器/操作]
            $path = explode('/', $url, 4);
        } elseif (false !== strpos($url, '=')) {
            // 参数1=值1&参数2=值2...
            parse_str($url, $var);
        } else {
            $path = [$url];
        }
        $route = [null, null, null];
        if (isset($path)) {
            // 解析path额外的参数
            if (!empty($path[3])) {
                preg_replace_callback('/([^\/]+)\/([^\/]+)/', function ($match) use (&$var) {
                    $var[strtolower($match[1])] = strip_tags($match[2]);
                }, array_pop($path));
            }
            // 解析[模块/控制器/操作]
            if ($reverse) {
                $module     = APP_MULTI_MODULE ? array_shift($path) : null;
                $controller = !empty($path) ? array_shift($path) : null;
                $action     = !empty($path) ? array_shift($path) : null;
            } else {
                $action     = array_pop($path);
                $controller = !empty($path) ? array_pop($path) : null;
                $module     = APP_MULTI_MODULE && !empty($path) ? array_pop($path) : null;
                // REST 操作方法支持
                if ('[rest]' == $action) {
                    $action = REQUEST_METHOD;
                }
            }
            $route = [$module, $controller, $action];
        }
        return ['route' => $route, 'var' => $var];
    }

    // 检测URL和规则路由是否匹配
    private static function match($url, $rule, $pattern)
    {
        $m1  = explode('/', $url);
        $m2  = explode('/', $rule);
        $var = [];
        foreach ($m2 as $key => $val) {
            if (0 === strpos($val, '[:')) {
                // 可选参数
                $val = substr($val, 1, -1);
            }
            if (0 === strpos($val, ':')) {
                // URL变量
                $name = substr($val, 1);
                if (isset($m1[$key]) && isset($pattern[$name]) && !preg_match('/^' . $pattern[$name] . '$/', $m1[$key])) {
                    // 检查变量规则
                    return false;
                }
                $var[$name] = $m1[$key];
            } elseif (0 !== strcasecmp($val, $m1[$key])) {
                return false;
            }
        }
        // 成功匹配后返回URL中的动态变量数组
        return $var;
    }

    // 解析规则路由
    private static function parseRule($rule, $route, $pathinfo)
    {
        // 获取URL地址中的参数
        $paths = explode('/', $pathinfo);
        // 获取路由地址规则
        $url = is_array($route) ? $route[0] : $route;
        // 解析路由规则
        $matches = [];
        $rule    = explode('/', $rule);
        foreach ($rule as $item) {
            $fun = '';
            if (0 === strpos($item, '[:')) {
                $item = substr($item, 1, -1);
            }
            if (0 === strpos($item, ':')) {
                $var           = substr($item, 1);
                $matches[$var] = array_shift($paths);
            } else {
                // 过滤URL中的静态变量
                array_shift($paths);
            }
        }
        // 替换路由地址中的变量
        foreach ($matches as $key => $val) {
            if (false !== strpos($url, ':' . $key)) {
                $url = str_replace(':' . $key, $val, $url);
                unset($matches[$key]);
            }
        }
        if (0 === strpos($url, '/') || 0 === strpos($url, 'http')) {
            // 路由到重定向地址
            $result = ['type' => 'redirect', 'url' => $url, 'status' => (is_array($route) && isset($route[1])) ? $route[1] : 301];
        } elseif (0 === strpos($url, '\\')) {
            // 路由到方法
            $result = ['type' => 'method', 'method' => is_array($route) ? [$url, $route[1]] : $url, 'params' => $matches];
        } elseif (0 === strpos($url, '@')) {
            // 路由到控制器
            $result = ['type' => 'controller', 'controller' => substr($url, 1), 'params' => $matches];
        } else {
            // 解析路由地址
            $result = self::parseRoute($url);
            $var    = array_merge($matches, $result['var']);
            // 解析剩余的URL参数
            self::parseUrlParams(implode('/', $paths), $var);
            // 路由到模块/控制器/操作
            $result = ['type' => 'module', 'module' => $result['route']];
        }
        return $result;
    }

    // 解析URL地址中的参数到$_GET
    private static function parseUrlParams($url, $var)
    {
        if ($url) {
            preg_replace_callback('/(\w+)\/([^\/]+)/', function ($match) use (&$var) {
                $var[strtolower($match[1])] = strip_tags($match[2]);
            }, $url);
        }
        $_GET = array_merge($var, $_GET);
    }

}
