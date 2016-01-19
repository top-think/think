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

/**
 * ThinkPHP分离出来的模板引擎
 * 支持XML标签和普通标签的模板解析
 * 编译型模板引擎 支持动态缓存
 */
class Template
{
    // 模板变量
    protected $data = [];
    // 引擎配置
    protected $config = [
        'tpl_path'           => VIEW_PATH, // 模板路径
        'tpl_suffix'         => '.html', // 默认模板文件后缀
        'cache_suffix'       => '.php', // 默认模板缓存后缀
        'tpl_deny_func_list' => 'echo,exit', // 模板引擎禁用函数
        'tpl_deny_php'       => false, // 默认模板引擎是否禁用PHP原生代码
        'tpl_begin'          => '{', // 模板引擎普通标签开始标记
        'tpl_end'            => '}', // 模板引擎普通标签结束标记
        'strip_space'        => false, // 是否去除模板文件里面的html空格与换行
        'tpl_cache'          => true, // 是否开启模板编译缓存,设为false则每次都会重新编译
        'compile_type'       => 'file', // 模板编译类型
        'cache_prefix'       => '', // 模板缓存前缀标识，可以动态改变
        'cache_time'         => 0, // 模板缓存有效期 0 为永久，(以数字为值，单位:秒)
        'layout_item'        => '{__CONTENT__}', // 布局模板的内容替换标识
        'taglib_begin'       => '{', // 标签库标签开始标记
        'taglib_end'         => '}', // 标签库标签结束标记
        'taglib_load'        => true, // 是否使用内置标签库之外的其它标签库，默认自动检测
        'taglib_build_in'    => 'cx', // 内置标签库名称(标签使用不必指定标签库名称),以逗号分隔 注意解析顺序
        'taglib_pre_load'    => '', // 需要额外加载的标签库(须指定标签库名称)，多个以逗号分隔
        'display_cache'      => false, // 模板渲染缓存
        'cache_id'           => '', // 模板缓存ID
        'tpl_replace_string' => [],
        'tpl_var_identify'   => 'array', // .语法变量识别，array|object|'', 为空时自动识别
        'namespace'          => '\\think\\template\\driver\\',
    ];

    private $literal   = [];
    protected $storage = null;

    /**
     * 架构函数
     * @access public
     */
    public function __construct(array $config = [])
    {
        $this->config['cache_path']   = RUNTIME_PATH . 'template' . DS;
        $this->config                 = array_merge($this->config, empty($config) ? (array) Config::get('template') : $config);
        $this->config['taglib_begin'] = $this->stripPreg($this->config['taglib_begin']);
        $this->config['taglib_end']   = $this->stripPreg($this->config['taglib_end']);
        $this->config['tpl_begin']    = $this->stripPreg($this->config['tpl_begin']);
        $this->config['tpl_end']      = $this->stripPreg($this->config['tpl_end']);

        // 初始化模板编译存储器
        $type          = $this->config['compile_type'] ? $this->config['compile_type'] : 'File';
        $class         = $this->config['namespace'] . ucwords($type);
        $this->storage = new $class();
    }

    /**
     * 字符串替换 避免正则混淆
     * @access private
     * @param string $str
     * @return string
     */
    private function stripPreg($str)
    {
        return str_replace(
            ['{', '}', '(', ')', '|', '[', ']', '-', '+', '*', '.', '^', '?'],
            ['\{', '\}', '\(', '\)', '\|', '\[', '\]', '\-', '\+', '\*', '\.', '\^', '\?'],
            $str);
    }

    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name
     * @param mixed $value
     * @return void
     */
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
        } else {
            $this->data[$name] = $value;
        }
    }

    /**
     * 模板引擎参数赋值
     * @access public
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    }

    /**
     * 模板引擎配置项
     * @access public
     * @param array $config
     * @return void|array
     */
    public function config($config)
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        } elseif (isset($this->config[$config])) {
            return $this->config[$config];
        }
    }

    /**
     * 模板变量获取
     * @access public
     * @param  string $name 变量名
     * @return string|false
     */
    public function get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : false;
    }

    /**
     * 渲染模板文件
     * @access public
     * @param string $template 模板文件
     * @param array  $vars 模板变量
     * @param array $config 模板参数
     * @return void
     */
    public function display($template, $vars = [], $config = [])
    {
        if ($vars) {
            $this->data = $vars;
        }
        if ($config) {
            $this->config($config);
        }
        $template  = $this->parseTemplateFile($template);
        $cacheFile = $this->config['cache_path'] . $this->config['cache_prefix'] . md5($template) . $this->config['cache_suffix'];
        if (!$this->checkCache($template, $cacheFile)) {
            // 缓存无效 重新模板编译
            $content = file_get_contents($template);
            $this->compiler($content, $cacheFile);
        }
        // 页面缓存
        ob_start();
        ob_implicit_flush(0);
        // 读取编译存储
        $this->storage->read($cacheFile, $this->data);
        // 获取并清空缓存
        $content = ob_get_clean();
        if (!empty($this->config['cache_id']) && $this->config['display_cache']) {
            // 缓存页面输出
            Cache::set($this->config['cache_id'], $content, $this->config['cache_time']);
        }
        echo $content;
    }

    /**
     * 渲染模板内容
     * @access public
     * @param string $content 模板内容
     * @param array  $vars 模板变量
     * @return void
     */
    public function fetch($content, $vars = [])
    {
        if ($vars) {
            $this->data = $vars;
        }
        $cacheFile = $this->config['cache_path'] . $this->config['cache_prefix'] . md5($content) . $this->config['cache_suffix'];
        if (!$this->checkCache($content, $cacheFile)) {
            // 缓存无效 模板编译
            $this->compiler($content, $cacheFile);
        }
        // 读取编译存储
        $this->storage->read($cacheFile, $this->data);
    }

    /**
     * 检查编译缓存是否有效
     * 如果无效则需要重新编译
     * @access private
     * @param string $template  模板文件名
     * @param string $cacheFile 缓存文件名
     * @return boolean
     */
    private function checkCache($template, $cacheFile)
    {
        if (!$this->config['tpl_cache']) {
            // 优先对配置设定检测
            return false;
        }
        // 检查编译存储是否有效
        return $this->storage->check($template, $cacheFile, $this->config['cache_time']);
    }

    /**
     * 检查编译缓存是否存在
     * @access public
     * @param string $cacheId 缓存的id
     * @return boolean
     */
    public function isCache($cacheId)
    {
        if ($cacheId && $this->config['display_cache']) {
            // 缓存页面输出
            return Cache::get($cacheId) ? true : false;
        }
        return false;
    }

    /**
     * 编译模板文件内容
     * @access private
     * @param string $content 模板内容
     * @param string $cacheFile 缓存文件名
     * @return void
     */
    private function compiler(&$content, $cacheFile)
    {
        // 模板解析
        $this->parse($content);
        // 添加安全代码
        $content = '<?php if (!defined(\'THINK_PATH\')) exit();?>' . $content;
        if ($this->config['strip_space']) {
            /* 去除html空格与换行 */
            $find    = ['~>\s+<~', '~>(\s+\n|\r)~'];
            $replace = ['><', '>'];
            $content = preg_replace($find, $replace, $content);
        }
        // 优化生成的php代码
        $content = preg_replace('/\?>\s*<\?php\s?/is', '', $content);
        // 模板过滤输出
        $replace = $this->config['tpl_replace_string'];
        $content = str_replace(array_keys($replace), array_values($replace), $content);
        // 编译存储
        $this->storage->write($cacheFile, $content);
        return;
    }

    /**
     * 模板解析入口
     * 支持普通标签和TagLib解析 支持自定义标签库
     * @access public
     * @param string $content 要解析的模板内容
     * @return void
     */
    public function parse(&$content)
    {
        // 内容为空不解析
        if (empty($content)) {
            return;
        }
        // 替换literal标签内容
        $this->parseLiteral($content);
        // 解析继承
        $this->parseExtend($content);
        // 解析布局
        $this->parseLayout($content);
        // 检查include语法
        $this->parseInclude($content);
        // 替换包含文件中literal标签内容
        $this->parseLiteral($content);
        // 检查PHP语法
        $this->parsePhp($content);

        // 获取需要引入的标签库列表
        // 标签库只需要定义一次，允许引入多个一次
        // 一般放在文件的最前面
        // 格式：<taglib name="html,mytag..." />
        // 当TAGLIB_LOAD配置为true时才会进行检测
        if ($this->config['taglib_load']) {
            $tagLibs = $this->getIncludeTagLib($content);
            if (!empty($tagLibs)) {
                // 对导入的TagLib进行解析
                foreach ($tagLibs as $tagLibName) {
                    $this->parseTagLib($tagLibName, $content);
                }
            }
        }
        // 预先加载的标签库 无需在每个模板中使用taglib标签加载 但必须使用标签库XML前缀
        if ($this->config['taglib_pre_load']) {
            $tagLibs = explode(',', $this->config['taglib_pre_load']);
            foreach ($tagLibs as $tag) {
                $this->parseTagLib($tag, $content);
            }
        }
        // 内置标签库 无需使用taglib标签导入就可以使用 并且不需使用标签库XML前缀
        $tagLibs = explode(',', $this->config['taglib_build_in']);
        foreach ($tagLibs as $tag) {
            $this->parseTagLib($tag, $content, true);
        }
        // 解析普通模板标签 {$tagName}
        $this->parseTag($content);

        // 还原被替换的Literal标签
        $this->parseLiteral($content, true);
        return;
    }

    /**
     * 检查PHP语法
     * @access private
     * @param string $content 要解析的模板内容
     * @return void
     */
    private function parsePhp(&$content)
    {
        if (ini_get('short_open_tag')) {
            // 开启短标签的情况要将<?标签用echo方式输出 否则无法正常输出xml标识
            $content = preg_replace('/(<\?(?!php|=|$))/i', '<?php echo \'\\1\'; ?>' . "\n", $content);
        }
        // PHP语法检查
        if ($this->config['tpl_deny_php'] && false !== strpos($content, '<?php')) {
            throw new Exception('not allow php tag', 11600);
        }
        return;
    }

    /**
     * 解析模板中的布局标签
     * @access private
     * @param string $content 要解析的模板内容
     * @return void
     */
    private function parseLayout(&$content)
    {
        // 读取模板中的布局标签
        if (preg_match($this->getRegex('layout'), $content, $matches)) {
            // 替换Layout标签
            $content = str_replace($matches[0], '', $content);
            // 解析Layout标签
            $array = $this->parseAttr($matches[0]);
            //if (!C('LAYOUT_ON') || C('LAYOUT_NAME') != $array['name']) {
            // 读取布局模板
            $layoutFile = (defined('THEME_PATH') && substr_count($array['name'], '/') < 2 ? THEME_PATH : $this->config['tpl_path']) . $array['name'] . $this->config['tpl_suffix'];
            if (is_file($layoutFile)) {
                $replace = isset($array['replace']) ? $array['replace'] : $this->config['layout_item'];
                // 替换布局的主体内容
                $content = str_replace($replace, $content, file_get_contents($layoutFile));
            }
            //}
        } else {
            $content = str_replace('{__NOLAYOUT__}', '', $content);
        }
        return;
    }

    /**
     * 解析模板中的include标签
     * @access private
     * @param  string $content 要解析的模板内容
     * @return void
     */
    private function parseInclude(&$content)
    {
        $regex      = $this->getRegex('include');
        $funReplace = function ($template) use (&$funReplace, &$regex, &$content) {
            if (preg_match_all($regex, $template, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $array = $this->parseAttr($match[0]);
                    $file  = $array['file'];
                    unset($array['file']);
                    // 分析模板文件名并读取内容
                    $parseStr = $this->parseTemplateName($file);
                    // 替换变量
                    foreach ($array as $k => $v) {
                        $parseStr = str_replace('[' . $k . ']', $v, $parseStr);
                    }
                    // 再次对包含文件进行模板分析
                    $funReplace($parseStr);
                    $content = str_replace($match[0], $parseStr, $content);
                }
                unset($matches);
            }
        };
        // 替换模板中的include标签
        $funReplace($content);
        return;
    }

    /**
     * 解析模板中的extend标签
     * @access private
     * @param  string $content 要解析的模板内容
     * @return void
     */
    private function parseExtend(&$content)
    {
        $regex  = $this->getRegex('extend');
        $array  = $blocks  = $extBlocks  = [];
        $extend = '';
        $fun    = function ($template) use (&$fun, &$regex, &$array, &$extend, &$blocks, &$extBlocks) {
            if (preg_match($regex, $template, $matches)) {
                if (!isset($array[$matches['name']])) {
                    $array[$matches['name']] = 1;
                    // 读取继承模板
                    $extend = $this->parseTemplateName($matches['name']);
                    // 递归检查继承
                    $fun($extend);
                    // 取得block标签内容
                    $blocks = array_merge($blocks, $this->parseBlock($template));
                    return;
                }
            } else {
                // 取得顶层模板block标签内容
                $extBlocks = $this->parseBlock($template);
                if (empty($extend)) {
                    // 无extend标签但有block标签的情况
                    $extend = $template;
                }
            }
        };

        $fun($content);
        if (!empty($extend)) {
            if ($extBlocks) {
                foreach ($extBlocks as $name => $v) {
                    $replace = isset($blocks[$name]) ? $blocks[$name]['content'] : $v['content'];
                    $extend  = str_replace($v['begin']['tag'] . $v['content'] . $v['end']['tag'], $replace, $extend);
                }
            }
            $content = $extend;
        }
        return;
    }

    /**
     * 替换页面中的literal标签
     * @access private
     * @param  string $content 模板内容
     * @param  boolean $restore 是否为还原
     * @return void
     */
    private function parseLiteral(&$content, $restore = false)
    {
        $regex = $this->getRegex($restore ? 'restoreliteral' : 'literal');
        if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
            if (!$restore) {
                // 替换literal标签
                foreach ($matches as $i => $match) {
                    $this->literal[] = substr($match[0], strlen($match[1]), -strlen($match[2]));
                    $content         = str_replace($match[0], "<!--###literal{$i}###-->", $content);
                }
            } else {
                // 还原literal标签
                foreach ($matches as $i => $match) {
                    $content = str_replace($match[0], $this->literal[$i], $content);
                }
                // 销毁literal记录
                unset($this->literal);
            }
            unset($matches);
        }
        return;
    }

    /**
     * 获取模板中的block标签
     * @access private
     * @param  string $content 模板内容
     * @return array
     */
    private function parseBlock(&$content)
    {
        $regex = $this->getRegex('block');
        $array = [];
        if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            $right = [];
            foreach ($matches as $match) {
                if (empty($match['name'][0])) {
                    if (!empty($right)) {
                        $begin                 = array_pop($right);
                        $end                   = ['offset' => $match[0][1], 'tag' => $match[0][0]];
                        $start                 = $begin['offset'] + strlen($begin['tag']);
                        $len                   = $end['offset'] - $start;
                        $array[$begin['name']] = [
                            'begin'   => $begin,
                            'content' => substr($content, $start, $len),
                            'end'     => $end,
                        ];
                    } else {
                        continue;
                    }
                } else {
                    $right[] = [
                        'name'   => $match[2][0],
                        'offset' => $match[0][1],
                        'tag'    => $match[0][0],
                    ];
                }
            }
            unset($right, $matches);
        }
        return $array;
    }

    /**
     * 搜索模板页面中包含的TagLib库
     * 并返回列表
     * @access private
     * @param  string $content 模板内容
     * @return array|null
     */
    private function getIncludeTagLib(&$content)
    {
        // 搜索是否有TagLib标签
        if (preg_match($this->getRegex('taglib'), $content, $matches)) {
            // 替换TagLib标签
            $content = str_replace($matches[0], '', $content);
            return explode(',', $matches['name']);
        }
        return null;
    }

    /**
     * TagLib库解析
     * @access public
     * @param  string $tagLib 要解析的标签库
     * @param  string $content 要解析的模板内容
     * @param  boolean $hide 是否隐藏标签库前缀
     * @return void
     */
    public function parseTagLib($tagLib, &$content, $hide = false)
    {
        if (strpos($tagLib, '\\')) {
            // 支持指定标签库的命名空间
            $className = $tagLib;
            $tagLib    = substr($tagLib, strrpos($tagLib, '\\') + 1);
        } else {
            $className = '\\think\\template\\taglib\\' . ucwords($tagLib);
        }
        $tLib = new $className($this);
        $tLib->parseTag($content, $hide ? '' : $tagLib);
        return;
    }

    /**
     * 分析标签属性
     * @access public
     * @param  string $str 属性字符串
     * @param  string $name 不为空时返回指定的属性名
     * @return array
     */
    public function parseAttr($str, $name = null)
    {
        $regex = '/\s+(?>(?<name>\w+)\s*)=(?>\s*)([\"\'])(?<value>(?:(?!\\2).)*)\\2/is';
        $array = [];
        if (preg_match_all($regex, $str, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $array[$match['name']] = $match['value'];
            }
            unset($matches);
        }
        if (!empty($name) && isset($array[$name])) {
            return $array[$name];
        } else {
            return $array;
        }
    }

    /**
     * 模板标签解析
     * 格式： {TagName:args [|content] }
     * @access private
     * @param  string $content 要解析的模板内容
     * @return void
     */
    private function parseTag(&$content)
    {
        $regex = $this->getRegex('tag');
        if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $str  = stripslashes($match[1]);
                $flag = substr($str, 0, 1);
                switch ($flag) {
                    case '$':    // 解析模板变量 格式 {$varName}
                        // 是否带有?号
                        if (false !== $pos = strpos($str, '?')) {
                            $array = preg_split('/([!=]={1,2}|(?<!-)[><]={0,1})/', substr($str, 0, $pos), 2, PREG_SPLIT_DELIM_CAPTURE);
                            $name = $array[0];
                            $this->parseVar($name);
                            $this->parseVarFunction($name);

                            $str   = trim(substr($str, $pos + 1));
                            $this->parseVar($str);
                            $first = substr($str, 0, 1);
                            if (isset($array[1])) {
                                $this->parseVar($array[2]);
                                $name .= $array[1] . $array[2];
                                if ('=' == $first) {
                                    // {$varname?='xxx'} $varname为真时才输出xxx
                                    $str = '<?php if(' . $name . ') echo ' . substr($str, 1) . '; ?>';
                                } else {
                                    $str = '<?php echo (' . $name . ')?' . $str . '; ?>';
                                }
                            } elseif (')' == substr($name, -1, 1)) {
                                // $name为对象或是自动识别，或者含有函数
                                switch ($first) {
                                    case '?':
                                        $str = '<?php echo ' . $name . ' ? ' . $name . ' : ' . substr($str, 1) . '; ?>';
                                        break;
                                    case '=':
                                        $str = '<?php if(' . $name . ') echo ' . substr($str, 1) . '; ?>';
                                        break;
                                    default:
                                        $str = '<?php echo ' . $name . '?' . $str . '; ?>';
                                }
                            } else {
                                // $name为数组
                                switch ($first) {
                                    case '?':
                                        // {$varname??'xxx'} $varname有定义则输出$varname,否则输出xxx
                                        $str = '<?php echo isset(' . $name . ') ? ' . $name . ' : ' . substr($str, 1) . '; ?>';
                                        break;
                                    case '=':
                                        // {$varname?='xxx'} $varname为真时才输出xxx
                                        $str = '<?php if(!empty(' . $name . ')) echo ' . substr($str, 1) . '; ?>';
                                        break;
                                    case ':':
                                        // {$varname?:'xxx'} $varname为真时输出$varname,否则输出xxx
                                        $str = '<?php echo !empty(' . $name . ')?' . $name . $str . '; ?>';
                                        break;
                                    default:
                                        if (strpos($str, ':')) {
                                            // {$varname ? 'a' : 'b'} $varname为真时输出a,否则输出b
                                            $str = '<?php echo !empty(' . $name . ')?' . $str . '; ?>';
                                        } else {
                                            $str = '<?php echo ' . $name . '?' . $str . '; ?>';
                                        }
                                }
                            }
                        } else {
                            $this->parseVar($str);
                            $this->parseVarFunction($str);
                            $str = '<?php echo ' . $str . '; ?>';
                        }
                        break;
                    case ':':    // 输出某个函数的结果
                        $str = substr($str, 1);
                        $this->parseVar($str);
                        $str = '<?php echo ' . $str . '; ?>';
                        break;
                    case '~':    // 执行某个函数
                        $str = substr($str, 1);
                        $this->parseVar($str);
                        $str = '<?php ' . $str . '; ?>';
                        break;
                    case '-':
                    case '+':    // 输出计算
                        $str = substr($str, 1);
                        $this->parseVar($str);
                        $str = '<?php echo ' . $flag .  $str . '; ?>';
                        break;
                    case '/':    // 注释标签
                        $flag2 = substr($str, 1, 1);
                        if ('/' == $flag2 || ('*' == $flag2 && substr(rtrim($str), -2) == '*/')) {
                            $str = '';
                        }
                        break;
                    default:
                        // 未识别的标签直接返回
                        $str = $this->config['tpl_begin'] . $str . $this->config['tpl_end'];
                        break;
                }
                $content = str_replace($match[0], $str, $content);
            }
            unset($matches);
        }
        return;
    }

    /**
     * 模板变量解析,支持使用函数
     * 格式： {$varname|function1|function2=arg1,arg2}
     * @access public
     * @param  string $varStr 变量数据
     * @return void
     */
    public function parseVar(&$varStr)
    {
        $varStr = trim($varStr);
        if (preg_match_all('/\$[a-zA-Z_](?>\w*)(?:[:\.][a-zA-Z_](?>\w*))+/', $varStr, $matches, PREG_OFFSET_CAPTURE)) {
            static $_varParseList = [];
            while ($matches[0]) {
                $match = array_pop($matches[0]);
                //如果已经解析过该变量字串，则直接返回变量值
                if (isset($_varParseList[$match[0]])) {
                    $parseStr = $_varParseList[$match[0]];
                } else {
                    if (strpos($match[0], '.')) {
                        $vars  = explode('.', $match[0]);
                        $first = array_shift($vars);
                        if ('$Think' == $first) {
                            // 所有以Think.打头的以特殊变量对待 无需模板赋值就可以输出
                            $parseStr = $this->parseThinkVar($vars);
                        } else {
                            switch ($this->config['tpl_var_identify']) {
                                case 'array':    // 识别为数组
                                    $parseStr = $first . '[\'' . implode('\'][\'', $vars) . '\']';
                                    break;
                                case 'obj':    // 识别为对象
                                    $parseStr = $first . '->' . implode('->', $vars);
                                    break;
                                default:    // 自动判断数组或对象
                                    $parseStr = '(is_array(' . $first . ')?' . $first . '[\'' . implode('\'][\'', $vars) . '\']:' . $first . '->' . implode('->', $vars) . ')';
                            }
                        }
                    } else {
                        $parseStr = str_replace(':', '->', $match[0]);
                    }
                    $_varParseList[$match[0]] = $parseStr;
                }
                $varStr = substr_replace($varStr, $parseStr, $match[1], strlen($match[0]));
            }
            unset($matches);
        }
        return;
    }

    /**
     * 对模板中使用了函数的变量进行解析
     * 格式 {$varname|function1|function2=arg1,arg2}
     * @access public
     * @param  string $varStr 变量字符串
     * @return void
     */
    public function parseVarFunction(&$varStr)
    {
        if (false == strpos($varStr, '|')) {
            return;
        }
        static $_varFunctionList = [];
        //如果已经解析过该变量字串，则直接返回变量值
        if (isset($_varFunctionList[$varStr])) {
            $varStr = $_varFunctionList[$varStr];
        } else {
            $varArray = explode('|', $varStr);
            // 取得变量名称
            $name = array_shift($varArray);
            // 对变量使用函数
            $length = count($varArray);
            // 取得模板禁止使用函数列表
            $template_deny_funs = explode(',', $this->config['tpl_deny_func_list']);
            for ($i = 0; $i < $length; $i++) {
                $args = explode('=', $varArray[$i], 2);
                // 模板函数过滤
                $fun = trim($args[0]);
                switch ($fun) {
                    case 'default':    // 特殊模板函数
                        if (false === strpos($name, '(')) {
                            $name = '(isset(' . $name . ') && (' . $name . ' !== \'\')?' . $name . ':' . $args[1] . ')';
                        } else {
                            $name = '(' . $name . ' !== \'\'?' . $name . ':' . $args[1] . ')';
                        }
                        break;
                    default:    // 通用模板函数
                        if (!in_array($fun, $template_deny_funs)) {
                            if (isset($args[1])) {
                                if (strstr($args[1], '###')) {
                                    $args[1] = str_replace('###', $name, $args[1]);
                                    $name    = "$fun($args[1])";
                                } else {
                                    $name = "$fun($name,$args[1])";
                                }
                            } else {
                                if (!empty($args[0])) {
                                    $name = "$fun($name)";
                                }
                            }
                        }
                }
            }
            $varStr = $name;
        }
        return;
    }

    /**
     * 特殊模板变量解析
     * 格式 以 $Think. 打头的变量属于特殊模板变量
     * @access public
     * @param  array $vars 变量数组
     * @return string
     */
    public function parseThinkVar(&$vars)
    {
        $vars[0]  = strtoupper(trim($vars[0]));
        $parseStr = '';
        if (count($vars) >= 2) {
            $vars[1] = trim($vars[1]);
            switch ($vars[0]) {
                case 'SERVER':
                    $parseStr = '$_SERVER[\'' . strtoupper($vars[1]) . '\']';
                    break;
                case 'GET':
                    $parseStr = '$_GET[\'' . $vars[1] . '\']';
                    break;
                case 'POST':
                    $parseStr = '$_POST[\'' . $vars[1] . '\']';
                    break;
                case 'COOKIE':
                    if (isset($vars[2])) {
                        $parseStr = '$_COOKIE[\'' . $vars[1] . '\'][\'' . $vars[2] . '\']';
                    } else {
                        $parseStr = '\\think\\cookie::get(\'' . $vars[1] . '\')';
                    }
                    break;
                case 'SESSION':
                    if (isset($vars[2])) {
                        $parseStr = '$_SESSION[\'' . $vars[1] . '\'][\'' . $vars[2] . '\']';
                    } else {
                        $parseStr = '\\think\\session::get(\'' . $vars[1] . '\')';
                    }
                    break;
                case 'ENV':
                    $parseStr = '$_ENV[\'' . strtoupper($vars[1]) . '\']';
                    break;
                case 'REQUEST':
                    $parseStr = '$_REQUEST[\'' . $vars[1] . '\']';
                    break;
                case 'CONST':
                    $parseStr = strtoupper($vars[1]);
                    break;
                case 'LANG':
                    $parseStr = '\\think\\lang::get(\'' . $vars[1] . '\')';
                    break;
                case 'CONFIG':
                    if (isset($vars[2])) {
                        $vars[1] .= '.' . $vars[2];
                    }
                    $parseStr = '\\think\\config::get(\'' . $vars[1] . '\')';
                    break;
                default:
                    break;
            }
        } else {
            if (count($vars) == 1) {
                switch ($vars[0]) {
                    case 'NOW':
                        $parseStr = "date('Y-m-d g:i a',time())";
                        break;
                    case 'VERSION':
                        $parseStr = 'THINK_VERSION';
                        break;
                    case 'LDELIM':
                        $parseStr = $this->config['tpl_begin'];
                        break;
                    case 'RDELIM':
                        $parseStr = $this->config['tpl_end'];
                        break;
                    default:
                        if (defined($vars[0])) {
                            $parseStr = $vars[0];
                        }
                }
            }
        }
        return $parseStr;
    }

    /**
     * 分析加载的模板文件并读取内容 支持多个模板文件读取
     * @access private
     * @param  string $templateName 模板文件名
     * @return string
     */
    private function parseTemplateName($templateName)
    {
        if ('$' == substr($templateName, 0, 1)) {
            //支持加载变量文件名
            $templateName = $this->get(substr($templateName, 1));
        }
        $array    = explode(',', $templateName);
        $parseStr = '';
        foreach ($array as $templateName) {
            if (empty($templateName)) {
                continue;
            }
            $template = $this->parseTemplateFile($templateName);
            // 获取模板文件内容
            $parseStr .= file_get_contents($template);
        }
        return $parseStr;
    }

    /**
     * 解析模板文件名
     * @access private
     * @param  string $template 文件名
     * @return string
     */
    private function parseTemplateFile($template)
    {
        if (false === strpos($template, '.')) {
            // 跨模块支持
            $template = strpos($template, '@') ?
                APP_PATH . str_replace('@', '/' . basename($this->config['tpl_path']) . '/', $template) . $this->config['tpl_suffix'] :
                (defined('THEME_PATH') && substr_count($template, '/') < 2 ? THEME_PATH : $this->config['tpl_path']) . $template . $this->config['tpl_suffix'];
        }
        return $template;
    }

    /**
     * 按标签生成正则
     * @access private
     * @param  string $tagName 标签名
     * @return string
     */
    private function getRegex($tagName)
    {
        $begin  = $this->config['taglib_begin'];
        $end    = $this->config['taglib_end'];
        $single = strlen(ltrim($begin, '\\')) == 1 && strlen(ltrim($end, '\\')) == 1 ? true : false;
        $regex  = '';
        switch ($tagName) {
            case 'block':
                if ($single) {
                    $regex = $begin . '(?:' . $tagName . '\b(?>(?:(?!name=).)*)\bname=([\'\"])(?<name>[\w\/\:@,]+)\\1(?>[^' . $end . ']*)|\/' . $tagName . ')' . $end;
                } else {
                    $regex = $begin . '(?:' . $tagName . '\b(?>(?:(?!name=).)*)\bname=([\'\"])(?<name>[\w\/\:@,]+)\\1(?>(?:(?!' . $end . ').)*)|\/' . $tagName . ')' . $end;
                }
                break;
            case 'literal':
                if ($single) {
                    $regex = '(' . $begin . $tagName . '\b(?>[^' . $end . ']*)' . $end . ')';
                    $regex .= '(?:(?>[^' . $begin . ']*)(?>(?!' . $begin . '(?>' . $tagName . '\b[^' . $end . ']*|\/' . $tagName . ')' . $end . ')' . $begin . '[^' . $begin . ']*)*)';
                    $regex .= '(' . $begin . '\/' . $tagName . $end . ')';
                } else {
                    $regex = '(' . $begin . $tagName . '\b(?>(?:(?!' . $end . ').)*)' . $end . ')';
                    $regex .= '(?:(?>(?:(?!' . $begin . ').)*)(?>(?!' . $begin . '(?>' . $tagName . '\b(?>(?:(?!' . $end . ').)*)|\/' . $tagName . ')' . $end . ')' . $begin . '(?>(?:(?!' . $begin . ').)*))*)';
                    $regex .= '(' . $begin . '\/' . $tagName . $end . ')';
                }
                break;
            case 'restoreliteral':
                $regex = '<!--###literal(\d+)###-->';
                break;
            case 'include':
                $name = 'file';
            case 'taglib':
            case 'layout':
            case 'extend':
                if (empty($name)) {
                    $name = 'name';
                }
                if ($single) {
                    $regex = $begin . $tagName . '\b(?>(?:(?!' . $name . '=).)*)\b' . $name . '=([\'\"])(?<name>[\w\/\.\:@,\\\\]+)\\1(?>[^' . $end . ']*)' . $end;
                } else {
                    $regex = $begin . $tagName . '\b(?>(?:(?!' . $name . '=).)*)\b' . $name . '=([\'\"])(?<name>[\w\/\.\:@,\\\\]+)\\1(?>(?:(?!' . $end . ').)*)' . $end;
                }
                break;
            case 'tag':
                $begin = $this->config['tpl_begin'];
                $end   = $this->config['tpl_end'];
                if (strlen(ltrim($begin, '\\')) == 1 && strlen(ltrim($end, '\\')) == 1) {
                    $regex = $begin . '((?:[\$\:\-\+~][\$a-wA-w_][\w\.\:\[\(\*\/\-\+\%_]|\/[\*\/])(?>[^' . $end . ']*))' . $end;
                } else {
                    $regex = $begin . '((?:[\$\:\-\+~][\$a-wA-w_][\w\.\:\[\(\*\/\-\+\%_]|\/[\*\/])(?>(?:(?!' . $end . ').)*))' . $end;
                }
                break;
        }
        return '/' . $regex . '/is';
    }
}
