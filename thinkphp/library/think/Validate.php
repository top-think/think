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

    // 当前验证场景
    protected $scene = null;

    // 正则表达式 regex = ['zip'=>'\d{6}',...]
    protected $regex = [];

    // 验证参数
    protected $config = [
        // 有值才验证 value_validate = [name1,name2,...]
        'value_validate'  => [],
        // 存在就验证 exists_validate = [name1,name2,...]
        'exists_validate' => [],
        // 验证场景 scene = ['edit'=>'name1,name2,...']
        'scene'           => [],
    ];
    // 验证失败错误信息
    protected $error = [];

    // 批量验证
    protected $batch = false;

    /**
     * 架构函数
     * @access public
     * @param array $rules 验证规则
     * @param array $message 验证提示信息
     * @param array $config 验证参数
     */
    public function __construct(array $rules = [], $message = [], $config = [])
    {
        $this->rule    = array_merge($this->rule, $rules);
        $this->message = array_merge($this->message, $message);
        $this->config  = array_merge($this->config, $config);
        if (is_string($this->config['value_validate'])) {
            $this->config['value_validate'] = explode(',', $this->config['value_validate']);
        }
        if (is_string($this->config['exists_validate'])) {
            $this->config['exists_validate'] = explode(',', $this->config['exists_validate']);
        }
    }

    /**
     * 实例化验证
     * @access public
     * @param array $rules 验证规则
     * @param array $message 验证提示信息
     * @param array $config 验证参数
     * @return object
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
     * @return void
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
     * @return void
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
     * @return void
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
     * 传入验证参数
     * @access public
     * @param string|array $name  参数名或者数组
     * @param mixed $value 参数值
     * @return void
     */
    public function config($name, $value = null)
    {
        if (is_array($name)) {
            $this->config = array_merge($this->config, $name);
        } else {
            $this->config[$name] = $value;
        }
        return $this;
    }

    /**
     * 设置验证场景
     * @access public
     * @param string $name  场景名
     * @param mixed $fields 要验证的字段
     * @return void
     */
    public function scene($name, $fields = null)
    {
        if (is_null($fields)) {
            // 设置当前场景
            $this->scene = $name;
        } else {
            // 设置验证场景
            $this->config['scene'][$name] = $fields;
        }
        return $this;
    }

    /**
     * 设置批量验证
     * @access public
     * @param bool $batch  是否批量验证
     * @return void
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

        foreach ($rules as $key => $rule) {
            if (strpos($key, '|')) {
                // 支持 字段|描述 用于返回默认错误
                list($key, $title) = explode('|', $key);
            } else {
                $title = $key;
            }
            // 场景检测
            if (!empty($scene) && !in_array($key, $scene)) {
                continue;
            }
            // 获取数据 支持二维数组
            $value = $this->getDataValue($data, $key);

            if ((isset($this->config['value_validate']) && in_array($key, $this->config['value_validate']) && '' == $value)
                || (isset($this->config['exists_validate']) && in_array($key, $this->config['exists_validate']) && is_null($value))) {
                // 不满足自动验证条件
                continue;
            }

            // 验证字段规则
            $result = $this->checkItem($key, $value, $rule, $data, $title);

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
     * @return string|true
     */
    protected function checkItem($field, $value, $rules, &$data, $title = '')
    {
        if ($rules instanceof \Closure) {
            // 匿名函数验证 支持传入当前字段和所有字段两个数据
            $result = call_user_func_array($rules, [$value, &$data]);
        } else {
            // 支持多规则验证 require|in:a,b,c|... 或者 ['require','in'=>'a,b,c',...]
            if (is_string($rules)) {
                $rules = explode('|', $rules);
            }
            $error = [];
            foreach ($rules as $key => $rule) {
                if ($rule instanceof \Closure) {
                    $result = call_user_func_array($rules, [$value, &$data]);
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
                    // 验证类型
                    $callback = isset($this->type[$type]) ? $this->type[$type] : [$this, $type];
                    // 验证数据
                    $result = call_user_func_array($callback, [$value, $rule, &$data, $field]);
                }

                if (false === $result) {
                    // 验证失败 返回错误信息
                    if (isset($this->message[$field . '.' . $info])) {
                        $error[] = $this->message[$field . '.' . $info];
                    } elseif (isset($this->message[$field . '.'])) {
                        $error[] = $this->message[$field . '.'];
                    } else {
                        $error[] = ($title ?: $field) . '错误';
                    }
                } elseif (is_string($result)) {
                    $error[] = $result;
                } elseif (is_array($result)) {
                    // 自定义错误信息数组
                    return $result;
                }
            }
            if (!empty($error)) {
                $result = implode(',', $error);
            }
        }
        // 验证失败返回错误信息
        return $result;
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
     * @param mixed $rule  验证规则 格式：数据表,字段名,排除ID
     * @param array $data  数据
     * @return bool
     */
    protected function unique($value, $rule, $data)
    {
        $rule  = explode(',', $rule);
        $model = Loader::table($rule[0]);
        $pk    = $model->getPk();
        $field = isset($rule[1]) ? $rule[1] : $key;
        if (isset($rule[2])) {
            $except = $rule[2];
        } elseif (isset($data[$pk])) {
            $except = $data[$pk];
        }
        $map[$field] = $value;
        if (isset($except)) {
            $map[$pk] = ['neq', $except];
        }
        if ($model->where($map)->find()) {
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
     * 验证某个字段的值等于某个值的时候必须
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
     * 获取数据验证的场景
     * @access protected
     * @param string $scene  验证场景
     * @return array
     */
    protected function getScene($scene = '')
    {
        if (empty($scene)) {
            // 读取指定场景
            $scene = $this->scene;
        }

        if (!empty($scene) && isset($this->config['scene'][$scene])) {
            // 如果设置了验证适用场景
            $scene = $this->config['scene'][$scene];
            if (is_string($scene)) {
                $scene = explode(',', $scene);
            }
        } else {
            $scene = [];
        }
        return $scene;
    }
}
