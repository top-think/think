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
        'taglib_begin'       => '<', // 标签库标签开始标记
        'taglib_end'         => '>', // 标签库标签结束标记
        'taglib_load'        => true, // 是否使用内置标签库之外的其它标签库，默认自动检测
        'taglib_build_in'    => 'cx', // 内置标签库名称(标签使用不必指定标签库名称),以逗号分隔 注意解析顺序
        'taglib_pre_load'    => '', // 需要额外加载的标签库(须指定标签库名称)，多个以逗号分隔
        'display_cache'      => false, // 模板渲染缓存
        'cache_id'           => '', // 模板缓存ID
        'tpl_replace_string' => [],
    ];

    private $literal   = [];
    private $block     = [];
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
        $class         = '\\think\\template\\driver\\' . ucwords($type);
        $this->storage = new $class();
    }

    /**
     * 字符串替换 避免正则混淆
     * @access private
     * @param string $str
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

    public function config($config)
    {
        $this->config = array_merge($this->config, $config);
    }

    public function get($name)
    {
        return $this->data[$name];
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
            // 缓存无效
            // 模板编译
            $this->compiler(file_get_contents($template), $cacheFile);
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
            // 缓存无效
            // 模板编译
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
     * @return boolen
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

    public function isCache($cacheId)
    {
        if ($cacheId && $this->config['display_cache']) {
            // 缓存页面输出
            return Cache::get($cacheId) ? true : false;
        }
        return null;
    }

    /**
     * 编译模板文件内容
     * @access private
     * @param string $content 模板内容
     * @param string $cacheFile 缓存文件名
     * @return void
     */
    private function compiler($content, $cacheFile)
    {
        // 模板解析
        $content = $this->parse($content);
        // 还原被替换的Literal标签
        $content = preg_replace_callback('/<!--###literal(\d+)###-->/is', function ($matches) {
            return $this->restoreLiteral($matches[1]);
        }, $content);
        // 添加安全代码
        $content = '<?php if (!defined(\'THINK_PATH\')) exit();?>' . $content;
        if ($this->config['strip_space']) {
            /* 去除html空格与换行 */
            $find    = ['~>\s+<~', '~>(\s+\n|\r)~'];
            $replace = ['><', '>'];
            $content = preg_replace($find, $replace, $content);
        }
        // 优化生成的php代码
        $content = str_replace('?><?php', '', $content);
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
     * @return string
     */
    public function parse($content)
    {
        // 内容为空不解析
        if (empty($content)) {
            return '';
        }
        $begin = $this->config['taglib_begin'];
        $end   = $this->config['taglib_end'];
        // 检查include语法
        $content = $this->parseInclude($content);
        // 检查PHP语法
        $content = $this->parsePhp($content);
        // 首先替换literal标签内容
        $content = preg_replace_callback('/' . $begin . 'literal' . $end . '(.*?)' . $begin . '\/literal' . $end . '/is', function ($matches) {
            return $this->parseLiteral($matches[1]);
        }, $content);

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
        // 解析普通模板标签 {tagName}
        $content = preg_replace_callback('/(' . $this->config['tpl_begin'] . ')([^\d\s' . $this->config['tpl_begin'] . $this->config['tpl_end'] . '].+?)(' . $this->config['tpl_end'] . ')/is', function ($matches) {
            return $this->parseTag($matches[2], $matches[0]);
        }, $content);
        return $content;
    }

    // 检查PHP语法
    private function parsePhp($content)
    {
        if (ini_get('short_open_tag')) {
            // 开启短标签的情况要将<?标签用echo方式输出 否则无法正常输出xml标识
            $content = preg_replace('/(<\?(?!php|=|$))/i', '<?php echo \'\\1\'; ?>' . "\n", $content);
        }
        // PHP语法检查
        if ($this->config['tpl_deny_php'] && false !== strpos($content, '<?php')) {
            throw new Exception('not allow php tag', 11600);
        }
        return $content;
    }

    // 解析模板中的include标签
    private function parseInclude($content)
    {
        // 解析继承
        $content = $this->parseExtend($content);
        // 解析布局
        $content = $this->parseLayout($content);
        // 读取模板中的include标签
        $find = preg_match_all('/' . $this->config['taglib_begin'] . 'include\s(.+?)\s*?\/' . $this->config['taglib_end'] . '/is', $content, $matches);
        if ($find) {
            for ($i = 0; $i < $find; $i++) {
                $include = $matches[1][$i];
                $array   = $this->parseXmlAttrs($include);
                $file    = $array['file'];
                unset($array['file']);
                $content = str_replace($matches[0][$i], $this->parseIncludeItem($file, $array), $content);
            }
        }
        return $content;
    }

    // 解析模板中的布局标签
    private function parseLayout($content)
    {
        // 读取模板中的布局标签
        $find = preg_match('/' . $this->config['taglib_begin'] . 'layout\s(.+?)\s*?\/' . $this->config['taglib_end'] . '/is', $content, $matches);
        if ($find) {
            //替换Layout标签
            $content = str_replace($matches[0], '', $content);
            //解析Layout标签
            $array = $this->parseXmlAttrs($matches[1]);
            // 读取布局模板
            $layoutFile = (defined('THEME_PATH') && substr_count($array['name'], '/') < 2 ? THEME_PATH : $this->config['tpl_path']) . $array['name'] . $this->config['tpl_suffix'];
            $replace    = isset($array['replace']) ? $array['replace'] : $this->config['layout_item'];
            // 替换布局的主体内容
            $content = str_replace($replace, $content, file_get_contents($layoutFile));
        } else {
            $content = str_replace('{__NOLAYOUT__}', '', $content);
        }
        return $content;
    }

    // 解析模板中的extend标签
    private function parseExtend($content)
    {
        $begin = $this->config['taglib_begin'];
        $end   = $this->config['taglib_end'];
        // 读取模板中的继承标签
        $find = preg_match('/' . $begin . 'extend\s(.+?)\s*?\/' . $end . '/is', $content, $matches);
        if ($find) {
            //替换extend标签
            $content = str_replace($matches[0], '', $content);
            // 记录页面中的block标签
            preg_replace_callback('/' . $begin . 'block\sname=(.+?)\s*?' . $end . '(.*?)' . $begin . '\/block' . $end . '/is', function ($matches) {
                return $this->parseBlock($matches[1], $matches[2]);
            }, $content);
            // 读取继承模板
            $array   = $this->parseXmlAttrs($matches[1]);
            $content = $this->parseTemplateName($array['name']);
            // 替换block标签
            $content = preg_replace_callback('/' . $begin . 'block\sname=(.+?)\s*?' . $end . '(.*?)' . $begin . '\/block' . $end . '/is', function ($matches) {
                return $this->replaceBlock($matches[1], $matches[2]);
            }, $content);
        } else {
            $content = preg_replace_callback('/' . $begin . 'block\sname=(.+?)\s*?' . $end . '(.*?)' . $begin . '\/block' . $end . '/is', function ($matches) {
                return stripslashes($matches[2]);
            }, $content);
        }
        return $content;
    }

    /**
     * 分析XML属性
     * @access private
     * @param string $attrs  XML属性字符串
     * @return array
     */
    private function parseXmlAttrs($attrs)
    {
        $xml = '<tpl><tag ' . $attrs . ' /></tpl>';
        $xml = simplexml_load_string($xml);
        if (!$xml) {
            throw new Exception('template tag define error', 11601);
        }
        $xml   = (array) ($xml->tag->attributes());
        $array = array_change_key_case($xml['@attributes']);
        return $array;
    }

    /**
     * 替换页面中的literal标签
     * @access private
     * @param string $content  模板内容
     * @return string
     */
    private function parseLiteral($content)
    {
        if (trim($content) == '') {
            return '';
        }
        $content           = stripslashes($content);
        $i                 = count($this->literal);
        $parseStr          = "<!--###literal{$i}###-->";
        $this->literal[$i] = $content;
        return $parseStr;
    }

    /**
     * 还原被替换的literal标签
     * @access private
     * @param string $tag  literal标签序号
     * @return string
     */
    private function restoreLiteral($tag)
    {
        // 还原literal标签
        $parseStr = $this->literal[$tag];
        // 销毁literal记录
        unset($this->literal[$tag]);
        return $parseStr;
    }

    /**
     * 记录当前页面中的block标签
     * @access private
     * @param string $name block名称
     * @param string $content  模板内容
     * @return string
     */
    private function parseBlock($name, $content)
    {
        $this->block[$name] = $content;
        return '';
    }

    /**
     * 替换继承模板中的block标签
     * @access private
     * @param string $name  block名称
     * @param string $content  模板内容
     * @return string
     */
    private function replaceBlock($name, $content)
    {
        // 替换block标签 没有重新定义则使用原来的
        $replace = isset($this->block[$name]) ? $this->block[$name] : $content;
        return stripslashes($replace);
    }

    /**
     * 搜索模板页面中包含的TagLib库
     * 并返回列表
     * @access private
     * @param string $content  模板内容
     * @return array
     */
    private function getIncludeTagLib(&$content)
    {
        //搜索是否有TagLib标签
        $find = preg_match('/' . $this->config['taglib_begin'] . 'taglib\s(.+?)(\s*?)\/' . $this->config['taglib_end'] . '\W/is', $content, $matches);
        if ($find) {
            //替换TagLib标签
            $content = str_replace($matches[0], '', $content);
            //解析TagLib标签
            $array = $this->parseXmlAttrs($matches[1]);
            return explode(',', $array['name']);
        }
        return [];
    }

    /**
     * TagLib库解析
     * @access private
     * @param string $tagLib 要解析的标签库
     * @param string $content 要解析的模板内容
     * @param boolen $hide 是否隐藏标签库前缀
     * @return void
     */
    protected function parseTagLib($tagLib, &$content, $hide = false)
    {
        $begin     = $this->config['taglib_begin'];
        $end       = $this->config['taglib_end'];
        $className = '\\think\\template\\taglib\\' . strtolower($tagLib);
        $tLib      = new $className($this);
        //$that       =   $this;
        foreach ($tLib->getTags() as $name => $val) {
            $tags = [$name];
            if (isset($val['alias'])) {
                // 别名设置
                $tags   = explode(',', $val['alias']);
                $tags[] = $name;
            }
            $level    = isset($val['level']) ? $val['level'] : 1;
            $closeTag = isset($val['close']) ? $val['close'] : true;
            foreach ($tags as $tag) {
                $parseTag = !$hide ? $tagLib . ':' . $tag : $tag; // 实际要解析的标签名称
                if (!method_exists($tLib, '_' . $tag)) {
                    // 别名可以无需定义解析方法
                    $tag = $name;
                }
                $n1 = empty($val['attr']) ? '(\s*?)' : '\s([^' . $end . ']*)';
                if (!$closeTag) {
                    $patterns = '/' . $begin . $parseTag . $n1 . '\/(\s*?)' . $end . '/is';
                    $content  = preg_replace_callback($patterns, function ($matches) use ($tLib, $tagLib, $tag) {
                        return $this->parseXmlTag($tLib, $tagLib, $tag, $matches[1], $matches[2]);
                    }, $content);
                } else {
                    $patterns = '/' . $begin . $parseTag . $n1 . $end . '(.*?)' . $begin . '\/' . $parseTag . '(\s*?)' . $end . '/is';
                    for ($i = 0; $i < $level; $i++) {
                        $content = preg_replace_callback($patterns, function ($matches) use ($tLib, $tagLib, $tag) {
                            return $this->parseXmlTag($tLib, $tagLib, $tag, $matches[1], $matches[2]);
                        }, $content);
                    }
                }
            }
        }
    }

    /**
     * 解析标签库的标签
     * 需要调用对应的标签库文件解析类
     * @access private
     * @param object $tLib 模板引擎实例
     * @param string $tagLib  标签库名称
     * @param string $tag  标签名
     * @param string $attr  标签属性
     * @param string $content  标签内容
     * @return string
     */
    private function parseXmlTag($tLib, $tagLib, $tag, $attr, $content)
    {
        $attr    = stripslashes($attr);
        $content = stripslashes($content);
        if (ini_get('magic_quotes_sybase')) {
            $attr = str_replace('\"', '\'', $attr);
        }
        $parse   = '_' . $tag;
        $content = trim($content);
        $tags    = $tLib->parseXmlAttr($attr, $tag);
        return $tLib->$parse($tags, $content);
    }

    /**
     * 模板标签解析
     * 格式： {TagName:args [|content] }
     * @access private
     * @param string $tagStr 标签内容
     * @param string $content 原始内容
     * @return string
     */
    private function parseTag($tagStr, $content)
    {
        $tagStr = stripslashes($tagStr);

        //还原非模板标签
        if (!preg_match('/^[\s|\d]/is', $tagStr)) {
            $flag  = substr($tagStr, 0, 1);
            $flag2 = substr($tagStr, 1, 1);
            $name  = substr($tagStr, 1);
            if ('$' == $flag && '.' != $flag2 && '(' != $flag2) {
                //解析模板变量 格式 {$varName}
                return $this->parseVar($name);
            } elseif ('-' == $flag || '+' == $flag) {
                // 输出计算
                return '<?php echo ' . $flag . $name . ';?>';
            } elseif (':' == $flag) {
                // 输出某个函数的结果
                return '<?php echo ' . $name . ';?>';
            } elseif ('~' == $flag) {
                // 执行某个函数
                return '<?php ' . $name . ';?>';
            } elseif (substr($tagStr, 0, 2) == '//' || (substr($tagStr, 0, 2) == '/*' && substr($tagStr, -2) == '*/')) {
                //注释标签
                return '';
            }
        }
        // 非法标签直接返回
        return $content;
    }

    /**
     * 模板变量解析,支持使用函数
     * 格式： {$varname|function1|function2=arg1,arg2}
     * @access private
     * @param string $varStr 变量数据
     * @return string
     */
    private function parseVar($varStr)
    {
        $varStr               = trim($varStr);
        static $_varParseList = [];
        //如果已经解析过该变量字串，则直接返回变量值
        if (isset($_varParseList[$varStr])) {
            return $_varParseList[$varStr];
        }
        $parseStr = '';
        if (!empty($varStr)) {
            $varArray = explode('|', $varStr);
            //取得变量名称
            $var = array_shift($varArray);
            if ('Think.' == substr($var, 0, 6)) {
                // 所有以Think.打头的以特殊变量对待 无需模板赋值就可以输出
                $name = $this->parseThinkVar($var);
            } elseif (false !== strpos($var, '.')) {
                //支持 {$var.property}
                $vars = explode('.', $var);
                $var  = array_shift($vars);
                $name = '$' . $var;
                if (count($vars) > 1) {
                    foreach ($vars as $key => $val) {
                        $name .= '["' . $val . '"]';
                    }
                } else {
                    // 一维自动识别对象和数组
                    $name = 'is_array($' . $var . ')?$' . $var . '["' . $vars[0] . '"]:$' . $var . '->' . $vars[0];
                }
            } elseif (false !== strpos($var, '[')) {
                //支持 {$var['key']} 方式输出数组
                $name = "$" . $var;
            } elseif (false !== strpos($var, ':') && false === strpos($var, '(') && false === strpos($var, '::') && false === strpos($var, '?')) {
                //支持 {$var:property} 方式输出对象的属性
                $vars = explode(':', $var);
                $var  = str_replace(':', '->', $var);
                $name = "$" . $var;
            } else {
                $name = "$$var";
            }
            //对变量使用函数
            if (count($varArray) > 0) {
                $name = $this->parseVarFunction($name, $varArray);
            }
            $parseStr = '<?php echo (' . $name . '); ?>';
        }
        $_varParseList[$varStr] = $parseStr;
        return $parseStr;
    }

    /**
     * 对模板变量使用函数
     * 格式 {$varname|function1|function2=arg1,arg2}
     * @access private
     * @param string $name 变量名
     * @param array $varArray  函数列表
     * @return string
     */
    private function parseVarFunction($name, $varArray)
    {
        //对变量使用函数
        $length = count($varArray);
        //取得模板禁止使用函数列表
        $template_deny_funs = explode(',', $this->config['tpl_deny_func_list']);
        for ($i = 0; $i < $length; $i++) {
            $args = explode('=', $varArray[$i], 2);
            //模板函数过滤
            $fun = strtolower(trim($args[0]));
            switch ($fun) {
                case 'default': // 特殊模板函数
                    $name = '(' . $name . ')?(' . $name . '):' . $args[1];
                    break;
                default: // 通用模板函数
                    if (!in_array($fun, $template_deny_funs)) {
                        if (isset($args[1])) {
                            if (strstr($args[1], '###')) {
                                $args[1] = str_replace('###', $name, $args[1]);
                                $name    = "$fun($args[1])";
                            } else {
                                $name = "$fun($name,$args[1])";
                            }
                        } else if (!empty($args[0])) {
                            $name = "$fun($name)";
                        }
                    }
            }
        }
        return $name;
    }

    /**
     * 特殊模板变量解析
     * 格式 以 $Think. 打头的变量属于特殊模板变量
     * @access private
     * @param string $varStr  变量字符串
     * @return string
     */
    private function parseThinkVar($varStr)
    {
        $vars     = explode('.', $varStr);
        $vars[1]  = strtoupper(trim($vars[1]));
        $parseStr = '';
        if (count($vars) >= 3) {
            $vars[2] = trim($vars[2]);
            switch ($vars[1]) {
                case 'SERVER':
                    $parseStr = '$_SERVER[\'' . strtoupper($vars[2]) . '\']';
                    break;
                case 'GET':
                    $parseStr = '$_GET[\'' . $vars[2] . '\']';
                    break;
                case 'POST':
                    $parseStr = '$_POST[\'' . $vars[2] . '\']';
                    break;
                case 'COOKIE':
                    if (isset($vars[3])) {
                        $parseStr = '$_COOKIE[\'' . $vars[2] . '\'][\'' . $vars[3] . '\']';
                    } else {
                        $parseStr = '\\think\\cookie::get(\'' . $vars[2] . '\')';
                    }
                    break;
                case 'SESSION':
                    if (isset($vars[3])) {
                        $parseStr = '$_SESSION[\'' . $vars[2] . '\'][\'' . $vars[3] . '\']';
                    } else {
                        $parseStr = '\\think\\session::get(\'' . $vars[2] . '\')';
                    }
                    break;
                case 'ENV':
                    $parseStr = '$_ENV[\'' . strtoupper($vars[2]) . '\']';
                    break;
                case 'REQUEST':
                    $parseStr = '$_REQUEST[\'' . $vars[2] . '\']';
                    break;
                case 'CONST':
                    $parseStr = strtoupper($vars[2]);
                    break;
                case 'LANG':
                    $parseStr = '\\think\\lang::get("' . $vars[2] . '")';
                    break;
                case 'CONFIG':
                    if (isset($vars[3])) {
                        $vars[2] .= '.' . $vars[3];
                    }
                    $parseStr = '\\think\\config::get("' . $vars[2] . '")';
                    break;
                default:
                    break;
            }
        } else if (count($vars) == 2) {
            switch ($vars[1]) {
                case 'NOW':
                    $parseStr = "date('Y-m-d g:i a',time())";
                    break;
                case 'VERSION':
                    $parseStr = 'THINK_TEMPLATE_VERSION';
                    break;
                case 'LDELIM':
                    $parseStr = $this->config['tpl_begin'];
                    break;
                case 'RDELIM':
                    $parseStr = $this->config['tpl_end'];
                    break;
                default:
                    if (defined($vars[1])) {
                        $parseStr = $vars[1];
                    }
            }
        }
        return $parseStr;
    }

    /**
     * 加载公共模板并缓存 和当前模板在同一路径，否则使用相对路径
     * @access private
     * @param string $tmplPublicName  公共模板文件名
     * @param array $vars  要传递的变量列表
     * @return string
     */
    private function parseIncludeItem($tmplPublicName, $vars = [])
    {
        // 分析模板文件名并读取内容
        $parseStr = $this->parseTemplateName($tmplPublicName);
        // 替换变量
        foreach ($vars as $key => $val) {
            if (strpos($parseStr, '[' . $key . ']')) {
                $parseStr = str_replace('[' . $key . ']', $val, $parseStr);
            }
        }
        // 再次对包含文件进行模板分析
        return $this->parseInclude($parseStr);
    }

    /**
     * 分析加载的模板文件并读取内容 支持多个模板文件读取
     * @access private
     * @param string $tmplPublicName  模板文件名
     * @return string
     */
    private function parseTemplateName($templateName)
    {
        if (substr($templateName, 0, 1) == '$') {
            //支持加载变量文件名
            $templateName = $this->get(substr($templateName, 1));
        }
        $array    = explode(',', $templateName);
        $parseStr = '';
        foreach ($array as $templateName) {
            $template = $this->parseTemplateFile($templateName);
            // 获取模板文件内容
            $parseStr .= file_get_contents($template);
        }
        return $parseStr;
    }

    private function parseTemplateFile($template)
    {
        if (false === strpos($template, '.')) {
            return (defined('THEME_PATH') && substr_count($template, '/') < 2 ? THEME_PATH : $this->config['tpl_path']) . $template . $this->config['tpl_suffix'];
        } else {
            return $template;
        }
    }
}
