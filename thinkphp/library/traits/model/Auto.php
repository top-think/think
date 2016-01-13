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

namespace traits\model;

use think\Lang;

trait Auto
{
    //protected $validate = []; // 自动验证定义
    //protected $auto     = []; // 自动完成定义

    /**
     * 创建数据对象 但不保存到数据库
     * @access public
     * @param mixed $data 创建数据
     * @param string $type 状态
     * @return mixed
     */
    public function create($data = '', $type = '')
    {
        // 如果没有传值默认取POST数据
        if (empty($data)) {
            $data = \think\Input::post();
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        }
        // 验证数据
        if (empty($data) || !is_array($data)) {
            $this->error = Lang::get('_DATA_TYPE_INVALID_');
            return false;
        }

        // 状态
        $type = $type ? $type : (!empty($data[$this->getPk()]) ? self::MODEL_UPDATE : self::MODEL_INSERT);
        $type = 1 << ($type - 1);
        // 字段列表
        $keys = array_keys($data);

        // 检测提交字段的合法性
        if (isset($this->options['field'])) {
            // $this->field('field1,field2...')->create()
            $fields = $this->options['field'];
            unset($this->options['field']);
        } elseif (self::MODEL_INSERT == $type && isset($this->insertFields)) {
            $fields = $this->insertFields;
        } elseif (self::MODEL_UPDATE == $type && isset($this->updateFields)) {
            $fields = $this->updateFields;
        }
        if (isset($fields)) {
            if (is_string($fields)) {
                $fields = explode(',', $fields);
            }
            // 判断令牌验证字段
            if (Config::get('token_on')) {
                $fields[] = Config::get('token_name');
            }

            foreach ($keys as $i => $key) {
                if (!in_array($key, $fields)) {
                    unset($keys[$i]);
                    unset($data[$key]);
                }
            }
        }

        // 数据自动验证
        if (!$this->autoValidation($data, $type)) {
            return false;
        }

        // 验证完成生成数据对象
        if ($this->autoCheckFields && empty($this->options['link'])) {
            // 开启字段检测并且没有关联表 则过滤非法字段数据
            $fields = $this->getDbFields();
            foreach ($keys as $i => $key) {
                if (!in_array($key, $fields)) {
                    unset($data[$key]);
                }
            }
        }

        // 创建完成对数据进行自动处理
        $this->autoOperation($data, $type);
        // 验证后的回调方法
        $this->_after_create($data, $this->options);
        // 赋值当前数据对象
        $this->data = $data;
        // 返回创建的数据以供其他调用
        return $data;
    }

    // 创建数据对象后的回调方法
    protected function _after_create(&$data, $options)
    {
    }

    /**
     * 使用正则验证数据
     * @access public
     * @param string $value 要验证的数据
     * @param string $rule 验证规则
     * @return boolean
     */
    public function regex($value, $rule)
    {
        static $validate = [
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

    /**
     * 自动表单处理
     * @access public
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return mixed
     */
    private function autoOperation(&$data, $type)
    {
        if (isset($this->options['auto'])) {
            if (false === $this->options['auto']) {
                // 关闭自动完成
                return;
            } else {
                $_auto = $this->options['auto'];
                unset($this->options['auto']);
                if (empty($_auto) && !empty($this->auto)) {
                    $_auto = $this->auto;
                }
            }
        } elseif (!empty($this->auto)) {
            $_auto = $this->auto;
        }
        // 自动填充
        if (!empty($_auto)) {
            foreach ($_auto as $key => $val) {
                if (!is_numeric($key) && is_array(current($val)) && isset($data[$key])) {
                    foreach ($val as $k => $v) {
                        $this->_operationField($data[$key], $v, $type);
                    }
                } else {
                    $this->_operationField($data, $val, $type);
                }
            }
        }
        return;
    }

    /**
     * 填充表单字段
     * @access private
     * @param array $data 创建数据
     * @param array $auto 填充因子
     * @param string $type 创建类型
     * @return boolean
     */
    private function _operationField(&$data, &$auto, $type)
    {
        // 填充因子定义格式
        // array('field','填充内容','填充时间','附加规则',[额外参数])
        if (empty($auto[2])) {
            $flags = 1 << (self::MODEL_INSERT - 1);
        } elseif (is_array($auto[2])) {
            $flags = 0;
            foreach ($auto[2] as $v) {
                $flags = $flags | 1 << ($v - 1);
            }
        } else {
            $flags = 3 == $auto[2] ? 3 : 1 << ($auto[2] - 1);
        }
        // 检查填充条件
        if ($flags & $type) {
            switch (trim($auto[3])) {
                case 'function':    //  使用函数进行填充 字段的值作为参数
                case 'callback':    // 使用回调方法
                    $args = isset($auto[4]) ? (array)$auto[4] : [];
                    if (is_string($auto[0]) && strpos($auto[0], ',')) {
                        $auto[0] = explode(',', $auto[0]);
                    }
                    if (is_array($auto[0])) {
                        // 支持多个字段验证
                        foreach ($auto[0] as $field) {
                            $_data[$field] = isset($data[$field]) ? $data[$field] : null;
                        }
                        array_unshift($args, $_data);
                    } else {
                        array_unshift($args, isset($data[$auto[0]]) ? $data[$auto[0]] : null);
                    }
                    if ('function' == $auto[3]) {
                        $data[$auto[0]] = call_user_func_array($auto[1], $args);
                    } else {
                        $data[$auto[0]] = call_user_func_array([& $this, $auto[1]], $args);
                    }
                    break;
                case 'field':   // 用其它字段的值进行填充
                    $data[$auto[0]] = $data[$auto[1]];
                    break;
                case 'ignore':  // 为空忽略
                    if ($auto[1] === $data[$auto[0]]) {
                        unset($data[$auto[0]]);
                    }
                    break;
                case 'string':
                default:    // 默认作为字符串填充
                    $data[$auto[0]] = $auto[1];
            }
            if (isset($data[$auto[0]]) && false === $data[$auto[0]]) {
                unset($data[$auto[0]]);
            }
        }
    }

    /**
     * 自动表单验证
     * @access protected
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return boolean
     */
    protected function autoValidation(&$data, $type)
    {
        if (isset($this->options['validate'])) {
            if (false === $this->options['validate']) {
                // 关闭自动验证
                return true;
            } else {
                $_validate = $this->options['validate'];
                unset($this->options['validate']);
                if (empty($_validate) && !empty($this->validate)) {
                    $_validate = $this->validate;
                }
            }
        } elseif (!empty($this->validate)) {
            $_validate = $this->validate;
        }
        // 属性验证
        if (!empty($_validate)) {
            // 如果设置了数据自动验证则进行数据验证
            if ($this->patchValidate) {
                // 重置验证错误信息
                $this->error = [];
            }
            foreach ($_validate as $key => $val) {
                if (!is_numeric($key) && is_array(current($val)) && isset($data[$key])) {
                    foreach ($val as $k => $v) {
                        if (false === $this->_validationField($data[$key], $v, $type)) {
                            return false;
                        }
                    }
                } else {
                    if (false === $this->_validationField($data, $val, $type)) {
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
     * @param string $type 创建类型
     * @return boolean
     */
    protected function _validationField(&$data, &$val, $type)
    {
        // 如果是批量验证,并且当前字段已经有规则验证没有通过则跳过
        if ($this->patchValidate && isset($this->error[$val[0]])) {
            return true;
        }
        // 验证因子定义格式
        // [field,rule,message,condition,type,when,params]
        if (empty($val[5])) {
            $flags = 1 << (self::MODEL_BOTH - 1);
        } elseif (is_array($val[5])) {
            $flags = 0;
            foreach ($val[5] as $v) {
                $flags = $flags | 1 << ($v - 1);
            }
        } else {
            $flags = 3 == $val[5] ? 3 : 1 << ($val[5] - 1);
        }
        // 判断是否需要执行验证
        if ($flags & $type) {
            if (0 == strpos($val[2], '{%') && strpos($val[2], '}')) {
                // 支持提示信息的多语言 使用 {%语言定义} 方式
                $val[2] = Lang::get(substr($val[2], 2, -1));
            }
            $val[3] = isset($val[3]) ? $val[3] : self::EXISTS_VALIDATE;
            $val[4] = isset($val[4]) ? $val[4] : 'regex';
            $status   = true;
            // 判断验证条件
            switch ($val[3]) {
                case self::MUST_VALIDATE:   // 必须验证 不管表单是否有设置该字段
                    $status = $this->_validationFieldItem($data, $val);
                    break;
                case self::VALUE_VALIDATE:    // 值不为空的时候才验证
                    if ('' != trim($data[$val[0]])) {
                        $status = $this->_validationFieldItem($data, $val);
                    }
                    break;
                default:    // 默认表单存在该字段就验证
                    if (isset($data[$val[0]])) {
                        $status = $this->_validationFieldItem($data, $val);
                    }
            }
            if (false === $status) {
                if ($this->patchValidate) {
                    $this->error[$val[0]] = $val[2];
                } else {
                    $this->error = $val[2];
                    return false;
                }
            }
        }
        return true;
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
            case 'function': // 使用函数进行验证
            case 'callback': // 调用方法进行验证
                $args = isset($val[6]) ? (array)$val[6] : [];
                if (is_string($val[0]) && strpos($val[0], ',')) {
                    $val[0] = explode(',', $val[0]);
                }
                if (is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field) {
                        $_data[$field] = isset($data[$field]) ? $data[$field] : null;
                    }
                    array_unshift($args, $_data);
                } else {
                    array_unshift($args, isset($data[$val[0]]) ? $data[$val[0]] : null);
                }
                return call_user_func_array('function' == $val[4] ? $val[1] : [& $this, $val[1]], $args);
            case 'confirm': // 验证两个字段是否相同
                return $data[$val[0]] == $data[$val[1]];
            case 'unique': // 验证某个值是否唯一
                if (is_string($val[0])) {
                    $val[0] = explode(',', $val[0]);
                }
                $map = [];
                if (is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field) {
                        if (!isset($data[$field])) {
                            return false;
                        }
                        $map[$field] = $data[$field];
                    }
                }
                $pk = $this->getPk();
                if (!empty($data[$pk]) && is_string($pk)) {
                    // 完善编辑的时候验证唯一
                    $map[$pk] = ['neq', $data[$pk]];
                }
                $options = $this->options;
                if ($this->where($map)->find()) {
                    return false;
                }
                $this->options = $options;
                return true;
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
            case 'filter': // 使用filter_var验证
                $result = filter_var($value, is_int($rule) ? $rule : filter_id($rule));
                return false === $result ? false : true;
            case 'regex':
            default: // 默认使用正则验证 可以使用验证类中定义的验证名称
                // 检查附加规则
                return $this->regex($value, $rule);
        }
    }

    /**
     * 指定自动完成
     * @access public
     * @param array $auto 自动完成设置
     * @return Model
     */
    public function auto($auto)
    {
        $this->options['auto'] = $auto;
        return $this;
    }

    /**
     * 指定自动验证
     * @access public
     * @param array $validate 自动验证设置
     * @return Model
     */
    public function validate($validate)
    {
        $this->options['validate'] = $validate;
        return $this;
    }
}
