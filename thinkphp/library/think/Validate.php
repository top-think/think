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

class Validate
{
    // 实例
    protected static $instance = null;

    // 自定义的验证类型
    protected $type = [];

    // 验证类型别名
    protected $alias = [
        '>' => 'gt', '>=' => 'egt', '<' => 'lt', '<=' => 'elt', '=' => 'eq', 'same' => 'eq',
    ];

    // 当前验证的规则
    protected $rule = [];

    // 验证提示信息
    protected $message = [];

    // 验证规则默认提示信息
    protected $typeMsg = [
        'require'    => ':attribute不能为空',
        'number'     => ':attribute必须是数字',
        'float'      => ':attribute必须是浮点数',
        'boolean'    => ':attribute必须是布尔值',
        'email'      => ':attribute格式不符',
        'array'      => ':attribute必须是数组',
        'accepted'   => ':attribute必须是yes、on或者1',
        'date'       => ':attribute格式不符合',
        'alpha'      => ':attribute只能是字母',
        'alphaNum'   => ':attribute只能是字母和数字',
        'alphaDash'  => ':attribute只能是字母、数字和下划线_及破折号-',
        'activeUrl'  => ':attribute不是有效的域名或者IP',
        'url'        => ':attribute不是有效的URL地址',
        'ip'         => ':attribute不是有效的IP地址',
        'dateFormat' => ':attribute必须使用日期格式 :rule',
        'in'         => ':attribute必须在 :rule 范围内',
        'notIn'      => ':attribute不能在 :rule 范围内',
        'between'    => ':attribute只能在 :1 - :2 之间',
        'notBetween' => ':attribute不能在 :1 - :2 之间',
        'length'     => ':attribute长度不符合要求 :rule',
        'max'        => ':attribute长度不能超过 :rule',
        'min'        => ':attribute长度不能小于 :rule',
        'after'      => ':attribute日期不能小于 :rule',
        'before'     => ':attribute日期不能超过 :rule',
        'expire'     => '不在有效期内 :rule',
        'allowIp'    => '不允许的IP访问',
        'denyIp'     => '禁止的IP访问',
        'confirm'    => ':attribute和字段 :rule 不一致',
        'egt'        => ':attribute必须大于等于 :rule',
        'gt'         => ':attribute必须大于 :rule',
        'elt'        => ':attribute必须小于等于 :rule',
        'lt'         => ':attribute必须小于 :rule',
        'eq'         => ':attribute必须等于 :rule',
        'unique'     => ':attribute已存在',
        'regex'      => ':attribute不符合指定规则',
    ];

    // 当前验证场景
    protected $currentScene = null;

    // 正则表达式 regex = ['zip'=>'\d{6}',...]
    protected $regex = [];

    // 验证场景 scene = ['edit'=>'name1,name2,...']
    protected $scene = [];

    // 验证失败错误信息
    protected $error = [];

    // 批量验证
    protected $batch = false;

    /**
     * 架构函数
     * @access public
     * @param array $rules 验证规则
     * @param array $message 验证提示信息
     */
    public function __construct(array $rules = [], $message = [])
    {
        $this->rule    = array_merge($this->rule, $rules);
        $this->message = array_merge($this->message, $message);
    }

    /**
     * 实例化验证
     * @access public
     * @param array $rules 验证规则
     * @param array $message 验证提示信息
     * @param array $config 验证参数
     * @return Validate
     */
    public static function make($rules = [], $message = [], $config = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($rules, $message, $config);
        }
        return self::$instance;
    }

    /**
     * 添加字段验证规则
     * @access protected
     * @param string|array $name  字段名称或者规则数组
     * @param mixed $rule  验证规则
     * @return Validate
     */
    public function rule($name, $rule = '')
    {
        if (is_array($name)) {
            $this->rule = array_merge($this->rule, $name);
        } else {
            $this->rule[$name] = $rule;
        }
        return $this;
    }

    /**
     * 注册验证（类型）规则
     * @access public
     * @param string $type  验证规则类型
     * @param mixed $callback callback方法(或闭包)
     * @return Validate
     */
    public function extend($type, $callback = null)
    {
        if (is_array($type)) {
            $this->type = array_merge($this->type, $type);
        } else {
            $this->type[$type] = $callback;
        }
        return $this;
    }

    /**
     * 设置提示信息
     * @access public
     * @param string|array $name  字段名称
     * @param string $message 提示信息
     * @return Validate
     */
    public function message($name, $message = '')
    {
        if (is_array($name)) {
            $this->message = array_merge($this->message, $name);
        } else {
            $this->message[$name] = $message;
        }
        return $this;
    }

    /**
     * 获取验证规则的默认提示信息
     * @access protected
     * @param string|array $type  验证规则类型名称或者数组
     * @param string $msg  验证提示信息
     * @return Validate
     */
    public function setTypeMsg($type, $msg = null)
    {
        if (is_array($type)) {
            $this->typeMsg = array_merge($this->typeMsg, $type);
        } else {
            $this->typeMsg[$type] = $msg;
        }
        return $this;
    }

    /**
     * 设置验证场景
     * @access public
     * @param string|array $name  场景名或者场景设置数组
     * @param mixed $fields 要验证的字段
     * @return Validate
     */
    public function scene($name, $fields = null)
    {
        if (is_array($name)) {
            $this->scene = array_merge($this->scene, $name);
        }if (is_null($fields)) {
            // 设置当前场景
            $this->currentScene = $name;
        } else {
            // 设置验证场景
            $this->scene[$name] = $fields;
        }
        return $this;
    }

    /**
     * 设置批量验证
     * @access public
     * @param bool $batch  是否批量验证
     * @return Validate
     */
    public function batch($batch = true)
    {
        $this->batch = $batch;
        return $this;
    }

    /**
     * 数据自动验证
     * @access public
     * @param array $data  数据
     * @param mixed $rules  验证规则
     * @param string $scene 验证场景
     * @return bool
     */
    public function check(&$data, $rules = [], $scene = '')
    {
        $this->error = [];

        if (empty($rules)) {
            // 读取验证规则
            $rules = $this->rule;
        }

        // 分析验证规则
        $scene = $this->getScene($scene);
        // 读取提示信息
        if (isset($rules['__message__'])) {
            $this->message($rules['__message__']);
            unset($rules['__message__']);
        }

        foreach ($rules as $key => $item) {
            // field => rule1|rule2... field=>['rule1','rule2',...]
            if (is_numeric($key)) {
                // [field,rule1|rule2,msg1|msg2]
                $key  = $item[0];
                $rule = $item[1];
                if (isset($item[2])) {
                    $msg = is_string($item[2]) ? explode('|', $item[2]) : $item[2];
                } else {
                    $msg = [];
                }
            } else {
                $rule = $item;
                $msg  = [];
            }
            if (strpos($key, '|')) {
                // 字段|描述 用于指定属性名称
                list($key, $title) = explode('|', $key);
            } else {
                $title = $key;
            }
            // 场景检测
            if (!empty($scene)) {
                if ($scene instanceof \Closure && !call_user_func_array($scene, [$key, &$data])) {
                    continue;
                } elseif (is_array($scene) && !in_array($key, $scene)) {
                    continue;
                }
            }

            // 获取数据 支持二维数组
            $value = $this->getDataValue($data, $key);

            // 字段验证
            $result = $this->checkItem($key, $value, $rule, $data, $title, $msg);

            if (true !== $result) {
                // 没有返回true 则表示验证失败
                if (!empty($this->batch)) {
                    // 批量验证
                    if (is_array($result)) {
                        $this->error = array_merge($this->error, $result);
                    } else {
                        $this->error[$key] = $result;
                    }
                } else {
                    $this->error = $result;
                    return false;
                }
            }
        }
        return !empty($this->error) ? false : true;
    }

    /**
     * 验证单个字段规则
     * @access protected
     * @param string $field  字段名
     * @param mixed $value  字段值
     * @param mixed $rules  验证规则
     * @param array $data  数据
     * @param string $title  字段描述
     * @param array $msg  提示信息
     * @return mixed
     */
    protected function checkItem($field, $value, $rules, &$data, $title = '', $msg = [])
    {
        if ($rules instanceof \Closure) {
            // 匿名函数验证 支持传入当前字段和所有字段两个数据
            $result = call_user_func_array($rules, [$value, &$data]);
        } else {
            // 支持多规则验证 require|in:a,b,c|... 或者 ['require','in'=>'a,b,c',...]
            if (is_string($rules)) {
                $rules = explode('|', $rules);
            }
            $i = 0;
            foreach ($rules as $key => $rule) {
                if ($rule instanceof \Closure) {
                    $result = call_user_func_array($rule, [$value, &$data]);
                } else {
                    // 判断验证类型
                    if (is_numeric($key) && strpos($rule, ':')) {
                        list($type, $rule) = explode(':', $rule, 2);
                        if (isset($this->alias[$type])) {
                            // 判断别名
                            $type = $this->alias[$type];
                        }
                        $info = $type;
                    } elseif (is_numeric($key)) {
                        $type = 'is';
                        $info = $rule;
                    } else {
                        $info = $type = $key;
                    }
                    // 如果不是require 有数据才会行验证
                    if (0 === strpos($info, 'require') || !empty($value)) {
                        // 验证类型
                        $callback = isset($this->type[$type]) ? $this->type[$type] : [$this, $type];
                        // 验证数据
                        $result = call_user_func_array($callback, [$value, $rule, &$data, $field]);
                    } else {
                        $result = true;
                    }
                }

                if (false === $result) {
                    // 验证失败 返回错误信息
                    if (isset($msg[$i])) {
                        $message = $msg[$i];
                    } else {
                        $message = $this->getRuleMsg($field, $title, $info, $rule);
                    }
                    return $message;
                } elseif (true !== $result) {
                    // 返回自定义错误信息
                    return $result;
                }
                $i++;
            }
        }
        return true !== $result ? $result : true;
    }

    /**
     * 验证是否和某个字段的值一致
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @param array $data  数据
     * @return bool
     */
    protected function confirm($value, $rule, $data)
    {
        return $this->getDataValue($data, $rule) == $value;
    }

    /**
     * 验证是否大于等于某个值
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function egt($value, $rule)
    {
        return $value >= $rule;
    }

    /**
     * 验证是否大于某个值
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function gt($value, $rule)
    {
        return $value > $rule;
    }

    /**
     * 验证是否小于等于某个值
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function elt($value, $rule)
    {
        return $value <= $rule;
    }

    /**
     * 验证是否小于某个值
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function lt($value, $rule)
    {
        return $value < $rule;
    }

    /**
     * 验证是否等于某个值
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function eq($value, $rule)
    {
        return $value == $rule;
    }

    /**
     * 验证字段值是否为有效格式
     * @access protected
     * @param mixed $value  字段值
     * @param string $rule  验证规则
     * @return bool
     */
    protected function is($value, $rule)
    {
        switch ($rule) {
            case 'require':
                // 必须
                $result = !empty($value) && '0' != $value;
                break;
            case 'accepted':
                // 接受
                $result = in_array($value, ['1', 'on', 'yes']);
                break;
            case 'date':
                // 是否是一个有效日期
                $result = false !== strtotime($value);
                break;
            case 'alpha':
                // 只允许字母
                $result = $this->regex($value, '/^[A-Za-z]+$/');
                break;
            case 'alphaNum':
                // 只允许字母和数字
                $result = $this->regex($value, '/^[A-Za-z0-9]+$/');
                break;
            case 'alphaDash':
                // 只允许字母、数字和下划线 破折号
                $result = $this->regex($value, '/^[A-Za-z0-9\-\_]+$/');
                break;
            case 'activeUrl':
                // 是否为有效的网址
                $result = checkdnsrr($value);
                break;
            case 'ip':
                // 是否为IP地址
                $result = $this->filter($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
                break;
            case 'url':
                // 是否为一个URL地址
                $result = $this->filter($value, FILTER_VALIDATE_URL);
                break;
            case 'float':
                // 是否为float
                $result = $this->filter($value, FILTER_VALIDATE_FLOAT);
                break;
            case 'number':
            case 'integer':
                // 是否为整形
                $result = $this->filter($value, FILTER_VALIDATE_INT);
                break;
            case 'email':
                // 是否为邮箱地址
                $result = $this->filter($value, FILTER_VALIDATE_EMAIL);
                break;
            case 'boolean':
                // 是否为布尔值
                $result = $this->filter($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'array':
                // 是否为数组
                $result = is_array($value);
                break;
            default:
                // 正则验证
                $result = $this->regex($value, $rule);
        }
        return $result;
    }

    /**
     * 验证时间和日期是否符合指定格式
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function dateFormat($value, $rule)
    {
        $info = date_parse_from_format($rule, $value);
        return 0 == $info['warning_count'] && 0 == $info['error_count'];
    }

    /**
     * 验证是否唯一
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则 格式：数据表,字段名,排除ID,主键名
     * @param array $data  数据
     * @param string $field  验证字段名
     * @return bool
     */
    protected function unique($value, $rule, $data, $field)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        $model = Loader::table($rule[0]);
        $field = isset($rule[1]) ? $rule[1] : $field;

        if (strpos($field, '|')) {
            // 支持多个字段验证
            $fields = explode('|', $field);
            foreach ($fields as $field) {
                $map[$field] = $data[$field];
            }
        } elseif (strpos($field, '=')) {
            parse_str($field, $map);
        } else {
            $map[$field] = $data[$field];
        }

        $key = strval(isset($rule[3]) ? $rule[3] : $model->getPk());
        if (isset($rule[2])) {
            $map[$key] = ['neq', $rule[2]];
        } elseif (isset($data[$key])) {
            $map[$key] = ['neq', $data[$key]];
        }

        if ($model->where($map)->field($key)->find()) {
            return false;
        }
        return true;
    }

    /**
     * 使用行为类验证
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @param array $data  数据
     * @return mixed
     */
    protected function behavior($value, $rule, $data)
    {
        return Hook::exec($rule, '', $data);
    }

    /**
     * 使用filter_var方式验证
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function filter($value, $rule)
    {
        if (is_int($rule)) {
            $param = null;
        } elseif (is_string($rule) && strpos($rule, ',')) {
            list($rule, $param) = explode(',', $rule);
        } elseif (is_array($rule)) {
            $param = isset($rule[1]) ? $rule[1] : null;
        }
        return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
    }

    /**
     * 验证某个字段等于某个值的时候必须
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @param array $data  数据
     * @return bool
     */
    protected function requireIf($value, $rule, $data)
    {
        list($field, $val) = explode(',', $rule);
        if ($this->getDataValue($data, $field) == $val) {
            return !empty($value);
        } else {
            return true;
        }
    }

    /**
     * 验证某个字段有值的情况下必须
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @param array $data  数据
     * @return bool
     */
    protected function requireWith($value, $rule, $data)
    {
        $val = $this->getDataValue($data, $rule);
        if (!empty($val)) {
            return !empty($value);
        } else {
            return true;
        }
    }

    /**
     * 验证是否在范围内
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function in($value, $rule)
    {
        return in_array($value, is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * 验证是否不在某个范围
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function notIn($value, $rule)
    {
        return !in_array($value, is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * between验证数据
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function between($value, $rule)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        list($min, $max) = $rule;
        return $value >= $min && $value <= $max;
    }

    /**
     * 使用notbetween验证数据
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function notBetween($value, $rule)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        list($min, $max) = $rule;
        return $value < $min || $value > $max;
    }

    /**
     * 验证数据长度
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function length($value, $rule)
    {
        $length = strlen((string) $value); // 当前数据长度
        if (strpos($rule, ',')) {
            // 长度区间
            list($min, $max) = explode(',', $rule);
            return $length >= $min && $length <= $max;
        } else {
            // 指定长度
            return $length == $rule;
        }
    }

    /**
     * 验证数据最大长度
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function max($value, $rule)
    {
        $length = strlen((string) $value);
        return $length <= $rule;
    }

    /**
     * 验证数据最小长度
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function min($value, $rule)
    {
        $length = strlen((string) $value);
        return $length >= $rule;
    }

    /**
     * 验证日期
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function after($value, $rule)
    {
        return strtotime($value) >= strtotime($rule);
    }

    /**
     * 验证日期
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function before($value, $rule)
    {
        return strtotime($value) <= strtotime($rule);
    }

    /**
     * 验证有效期
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    protected function expire($value, $rule)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        list($start, $end) = $rule;
        if (!is_numeric($start)) {
            $start = strtotime($start);
        }

        if (!is_numeric($end)) {
            $end = strtotime($end);
        }
        return NOW_TIME >= $start && NOW_TIME <= $end;
    }

    /**
     * 验证IP许可
     * @access protected
     * @param string $value  字段值
     * @param mixed $rule  验证规则
     * @return mixed
     */
    protected function allowIp($value, $rule)
    {
        return in_array($_SERVER['REMOTE_ADDR'], is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * 验证IP禁用
     * @access protected
     * @param string $value  字段值
     * @param mixed $rule  验证规则
     * @return mixed
     */
    protected function denyIp($value, $rule)
    {
        return !in_array($_SERVER['REMOTE_ADDR'], is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * 使用正则验证数据
     * @access protected
     * @param mixed $value  字段值
     * @param mixed $rule  验证规则 正则规则或者预定义正则名
     * @return mixed
     */
    protected function regex($value, $rule)
    {
        if (isset($this->regex[$rule])) {
            $rule = $this->regex[$rule];
        }
        if (0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            // 不是正则表达式则两端补上/
            $rule = '/^' . $rule . '$/';
        }
        return 1 === preg_match($rule, (string) $value);
    }

    // 获取错误信息
    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取数据值
     * @access protected
     * @param array $data  数据
     * @param string $key  数据标识 支持二维
     * @return mixed
     */
    protected function getDataValue($data, $key)
    {
        if (strpos($key, '.')) {
            // 支持二维数组验证
            list($name1, $name2) = explode('.', $key);
            $value               = isset($data[$name1][$name2]) ? $data[$name1][$name2] : null;
        } else {
            $value = isset($data[$key]) ? $data[$key] : null;
        }
        return $value;
    }

    /**
     * 获取验证规则的错误提示信息
     * @access protected
     * @param string $attribute  字段英文名
     * @param string $title  字段描述名
     * @param string $type  验证规则名称
     * @param mixed $rule  验证规则数据
     * @return string
     */
    protected function getRuleMsg($attribute, $title, $type, $rule)
    {
        if (isset($this->message[$attribute . '.' . $type])) {
            $msg = $this->message[$attribute . '.' . $type];
        } elseif (isset($this->message[$attribute])) {
            $msg = $this->message[$attribute];
        } elseif (isset($this->typeMsg[$type])) {
            $msg = $this->typeMsg[$type];
        } else {
            $msg = $title . '规则错误';
        }
        // TODO 多语言支持
        if (is_string($msg) && false !== strpos($msg, ':')) {
            // 变量替换
            if (strpos($rule, ',')) {
                $array = array_pad(explode(',', $rule), 3, '');
            } else {
                $array = array_pad([], 3, '');
            }
            $msg = str_replace(
                [':attribute', ':rule', ':1', ':2', ':3'],
                [$title, (string) $rule, $array[0], $array[1], $array[2]],
                $msg);
        }
        return $msg;
    }

    /**
     * 获取数据验证的场景
     * @access protected
     * @param string $scene  验证场景
     * @return array
     */
    protected function getScene($scene = '')
    {
        if (empty($scene)) {
            // 读取指定场景
            $scene = $this->currentScene;
        }

        if (!empty($scene) && isset($this->scene[$scene])) {
            // 如果设置了验证适用场景
            $scene = $this->scene[$scene];
            if (is_string($scene)) {
                $scene = explode(',', $scene);
            }
        } else {
            $scene = [];
        }
        return $scene;
    }
}
