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

class Auto
{

    protected $auto = [];

    public function rule($rule)
    {
        $this->auto = $rule;
        return $this;
    }

    /**
     * 自动表单处理
     * @access public
     * @param array $data 创建数据
     * @return mixed
     */
    public function operate($data)
    {
        // 自动填充
        if ($this->auto) {
            foreach ($this->auto as $auto) {
                // 填充因子定义格式
                // array('field','填充内容','附加规则',[额外参数])
                switch (trim($auto[2])) {
                    case 'callback': // 使用回调方法
                        $args = isset($auto[3]) ? (array) $auto[3] : [];
                        if (isset($data[$auto[0]])) {
                            array_unshift($args, $data[$auto[0]]);
                        }
                        $data[$auto[0]] = call_user_func_array($auto[1], $args);
                        break;
                    case 'field': // 用其它字段的值进行填充
                        $data[$auto[0]] = $data[$auto[1]];
                        break;
                    case 'ignore': // 为空忽略
                        if ('' === $data[$auto[0]]) {
                            unset($data[$auto[0]]);
                        }

                        break;
                    case 'string':
                    default: // 默认作为字符串填充
                        $data[$auto[0]] = $auto[1];
                }
                if (false === $data[$auto[0]]) {
                    unset($data[$auto[0]]);
                }

            }
        }
        return $data;
    }
}
