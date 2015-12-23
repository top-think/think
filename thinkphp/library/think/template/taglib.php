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

namespace think\template;

use think\Exception;

/**
 * ThinkPHP标签库TagLib解析基类
 * @category   Think
 * @package  Think
 * @subpackage  Template
 * @author    liu21st <liu21st@gmail.com>
 */
class TagLib
{

    /**
     * 标签库定义XML文件
     * @var string
     * @access protected
     */
    protected $xml  = '';
    protected $tags = []; // 标签定义
    /**
     * 标签库名称
     * @var string
     * @access protected
     */
    protected $tagLib = '';

    /**
     * 标签库标签列表
     * @var string
     * @access protected
     */
    protected $tagList = [];

    /**
     * 标签库分析数组
     * @var string
     * @access protected
     */
    protected $parse = [];

    /**
     * 标签库是否有效
     * @var string
     * @access protected
     */
    protected $valid = false;

    /**
     * 当前模板对象
     * @var object
     * @access protected
     */
    protected $tpl;

    protected $comparison = [' nheq ' => ' !== ', ' heq ' => ' === ', ' neq ' => ' != ', ' eq ' => ' == ', ' egt ' => ' >= ', ' gt ' => ' > ', ' elt ' => ' <= ', ' lt ' => ' < '];

    public function __construct($template)
    {
        $this->tpl = $template;
    }

    /**
     * TagLib标签属性分析 返回标签属性数组
     * @access   public
     *
     * @param $attr
     * @param $tag
     *
     * @return array
     * @throws Exception
     * @internal param string $tagStr 标签内容
     */
    public function parseXmlAttr($attr, $tag)
    {
        if ('' == trim($attr)) {
            return [];
        }
        //XML解析安全过滤
        $attr = str_replace('&', '___', $attr);
        $xml  = '<tpl><tag ' . $attr . ' /></tpl>';
        $xml  = simplexml_load_string($xml);
        if (!$xml) {
            throw new Exception('_XML_TAG_ERROR_ : ' . $attr);
        }
        $xml   = (array) ($xml->tag->attributes());
        $array = array_change_key_case($xml['@attributes']);
        if (!is_array($array)) {
            return [];
        }

        $tag = strtolower($tag);
        if (isset($this->tags[$tag]['attr'])) {
            $attrs = explode(',', $this->tags[$tag]['attr']);
            if (isset($this->tags[strtolower($tag)]['must'])) {
                $must = explode(',', $this->tags[$tag]['must']);
            } else {
                $must = [];
            }
            foreach ($attrs as $name) {
                if (isset($array[$name])) {
                    $array[$name] = str_replace('___', '&', $array[$name]);
                } elseif (false !== array_search($name, $must)) {
                    throw new Exception('_PARAM_ERROR_:' . $name);
                }
            }
        }

        return $array;
    }

    /**
     * 解析条件表达式
     * @access public
     * @param string $condition 表达式标签内容
     * @return array
     */
    public function parseCondition($condition)
    {
        $condition = str_ireplace(array_keys($this->comparison), array_values($this->comparison), $condition);
        $condition = preg_replace('/\$(\w+):(\w+)\s/is', '$\\1->\\2 ', $condition);
        $condition = preg_replace('/\$(\w+)\.(\w+)\s/is', '$\\1["\\2"] ', $condition);

        if (false !== strpos($condition, '$Think')) {
            $condition = preg_replace('/(\$Think.*?)\s/ies', "\$this->parseThinkVar('\\1');", $condition);
        }

        return $condition;
    }

    /**
     * 自动识别构建变量
     * @access public
     * @param string $name 变量描述
     * @return string
     */
    public function autoBuildVar($name)
    {
        if ('Think.' == substr($name, 0, 6)) {
            // 特殊变量
            return $this->parseThinkVar($name);
        } elseif (strpos($name, '.')) {
            $vars = explode('.', $name);
            $var  = array_shift($vars);
            $name = '$' . $var;
            foreach ($vars as $key => $val) {
                if (0 === strpos($val, '$')) {
                    $name .= '["{' . $val . '}"]';
                } else {
                    $name .= '["' . $val . '"]';
                }
            }
        } elseif (strpos($name, ':')) {
            // 额外的对象方式支持
            $name = '$' . str_replace(':', '->', $name);
        } elseif (!defined($name)) {
            $name = '$' . $name;
        }
        return $name;
    }

    /**
     * 用于标签属性里面的特殊模板变量解析
     * 格式 以 Think. 打头的变量属于特殊模板变量
     * @access public
     * @param string $varStr  变量字符串
     * @return string
     */
    public function parseThinkVar($varStr)
    {
        $vars     = explode('.', $varStr);
        $vars[1]  = strtoupper(trim($vars[1]));
        $parseStr = '';
        if (count($vars) >= 3) {
            $vars[2] = trim($vars[2]);
            switch ($vars[1]) {
                case 'SERVER':$parseStr = '$_SERVER[\'' . $vars[2] . '\']';
                    break;
                case 'GET':$parseStr = '$_GET[\'' . $vars[2] . '\']';
                    break;
                case 'POST':$parseStr = '$_POST[\'' . $vars[2] . '\']';
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
                case 'ENV':$parseStr = '$_ENV[\'' . $vars[2] . '\']';
                    break;
                case 'REQUEST':$parseStr = '$_REQUEST[\'' . $vars[2] . '\']';
                    break;
                case 'CONST':$parseStr = strtoupper($vars[2]);
                    break;
                case 'LANG':
                    $parseStr = '\\think\\Lang::get("' . $vars[2] . '")';
                    break;
                case 'CONFIG':
                    if (isset($vars[3])) {
                        $vars[2] .= '.' . $vars[3];
                    }
                    $parseStr = '\\think\\config::get("' . $vars[2] . '")';
                    break;
            }
        } else if (count($vars) == 2) {
            switch ($vars[1]) {
                case 'NOW':$parseStr = "date('Y-m-d g:i a',time())";
                    break;
                case 'VERSION':$parseStr = 'THINK_VERSION';
                    break;
                default:if (defined($vars[1])) {
                        $parseStr = $vars[1];
                    }

            }
        }
        return $parseStr;
    }

    /**
     * 对模板变量使用函数
     * 格式 {$varname|function1|function2=arg1,arg2}
     * @access protected
     * @param string $name 变量名
     * @param array $varArray  函数列表
     * @return string
     */
    protected function parseVarFunction($name, $varArray)
    {
        //对变量使用函数
        $length = count($varArray);
        for ($i = 0; $i < $length; $i++) {
            $args = explode('=', $varArray[$i], 2);
            //模板函数过滤
            $fun = strtolower(trim($args[0]));
            switch ($fun) {
                case 'default':    // 特殊模板函数
                    $name = '(' . $name . ')?(' . $name . '):' . $args[1];
                    break;
                default:    // 通用模板函数
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
        return $name;
    }

    // 获取标签定义
    public function getTags()
    {
        return $this->tags;
    }
}
