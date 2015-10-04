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

use think\Lang;

class Validate
{
    // 自动验证定义
    protected $validate = [];
    // 是否批处理验证
    protected $patchValidate = false;
    protected $error         = '';

    public function rule($rule)
    {
        $this->validate = $rule;
        return $this;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * 自动表单验证
     * @access protected
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return boolean
     */
    public function valid($data, $rule = [])
    {
        $validate = $rule ? $rule : $this->validate;
        // 属性验证
        if ($validate) {
            // 如果设置了数据自动验证则进行数据验证
            if ($this->patchValidate) {
                // 重置验证错误信息
                $this->error = [];
            }
            foreach ($validate as $key => $val) {
                // 验证因子定义格式
                // array(field,rule,message,condition,type,params)
                // 判断是否需要执行验证
                if (0 == strpos($val[2], '{%') && strpos($val[2], '}'))
                // 支持提示信息的多语言 使用 {%语言定义} 方式
                {
                    $val[2] = Lang::get(substr($val[2], 2, -1));
                }

                $val[3] = isset($val[3]) ? $val[3] : 0;
                $val[4] = isset($val[4]) ? $val[4] : 'regex';
                // 判断验证条件
                if (1 == $val[3] || (2 == $val[3] && '' != trim($data[$val[0]])) || (0 == $val[3] && isset($data[$val[0]]))) {
                    if (false === $this->_validationField($data, $val)) {
                        return false;
                    }

                }
            }
            // 批量验证的时候最后返回错误
            if (!empty($this->error)) {
                return false;
            }

        }
        return true;
    }

    /**
     * 验证表单字段 支持批量验证
     * 如果批量验证返回错误的数组信息
     * @access protected
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationField($data, $val)
    {
        if (false === $this->_validationFieldItem($data, $val)) {
            if ($this->patchValidate) {
                $this->error[$val[0]] = $val[2];
            } else {
                $this->error = $val[2];
                return false;
            }
        }
        return;
    }

    /**
     * 根据验证因子验证字段
     * @access protected
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationFieldItem($data, $val)
    {
        switch (strtolower(trim($val[4]))) {
            case 'callback': // 调用方法进行验证
                $args = isset($val[5]) ? (array) $val[5] : [];
                if (is_string($val[0]) && strpos($val[0], ',')) {
                    $val[0] = explode(',', $val[0]);
                }

                if (is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field) {
                        $_data[$field] = $data[$field];
                    }

                    array_unshift($args, $_data);
                } else {
                    array_unshift($args, $data[$val[0]]);
                }
                return call_user_func_array($val[1], $args);
            case 'confirm': // 验证两个字段是否相同
                return $data[$val[0]] == $data[$val[1]];
            default: // 检查附加规则
                return $this->check($data[$val[0]], $val[1], $val[4]);
        }
    }

    /**
     * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
     * @access public
     * @param string $value 验证数据
     * @param mixed $rule 验证表达式
     * @param string $type 验证方式 默认为正则验证
     * @return boolean
     */
    public function check($value, $rule, $type = 'regex')
    {
        $type = strtolower(trim($type));
        switch ($type) {
            case 'in': // 验证是否在某个指定范围之内 逗号分隔字符串或者数组
            case 'notin':
                $range = is_array($rule) ? $rule : explode(',', $rule);
                return 'in' == $type ? in_array($value, $range) : !in_array($value, $range);
            case 'between': // 验证是否在某个范围
            case 'notbetween': // 验证是否不在某个范围
                if (is_array($rule)) {
                    $min = $rule[0];
                    $max = $rule[1];
                } else {
                    list($min, $max) = explode(',', $rule);
                }
                return 'between' == $type ? $value >= $min && $value <= $max : $value < $min || $value > $max;
            case 'equal': // 验证是否等于某个值
            case 'notequal': // 验证是否等于某个值
                return 'equal' == $type ? $value == $rule : $value != $rule;
            case 'length': // 验证长度
                $length = mb_strlen($value, 'utf-8'); // 当前数据长度
                if (strpos($rule, ',')) {
                    // 长度区间
                    list($min, $max) = explode(',', $rule);
                    return $length >= $min && $length <= $max;
                } else {
                    // 指定长度
                    return $length == $rule;
                }
            case 'expire':
                list($start, $end) = explode(',', $rule);
                if (!is_numeric($start)) {
                    $start = strtotime($start);
                }

                if (!is_numeric($end)) {
                    $end = strtotime($end);
                }

                return NOW_TIME >= $start && NOW_TIME <= $end;
            case 'ip_allow': // IP 操作许可验证
                return in_array($_SERVER['REMOTE_ADDR'], explode(',', $rule));
            case 'ip_deny': // IP 操作禁止验证
                return !in_array($_SERVER['REMOTE_ADDR'], explode(',', $rule));
            case 'regex':
            default: // 默认使用正则验证 可以使用验证类中定义的验证名称
                // 检查附加规则
                return $this->regex($value, $rule);
        }
    }

    /**
     * 使用正则验证数据
     * @access public
     * @param string $value  要验证的数据
     * @param string $rule 验证规则
     * @return boolean
     */
    public function regex($value, $rule)
    {
        $validate = [
            'require'  => '/.+/',
            'email'    => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url'      => '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'number'   => '/^\d+$/',
            'zip'      => '/^\d{6}$/',
            'integer'  => '/^[-\+]?\d+$/',
            'double'   => '/^[-\+]?\d+(\.\d+)?$/',
            'english'  => '/^[A-Za-z]+$/',
        ];
        // 检查是否有内置的正则表达式
        if (isset($validate[strtolower($rule)])) {
            $rule = $validate[strtolower($rule)];
        }

        return preg_match($rule, $value) === 1;
    }
}
