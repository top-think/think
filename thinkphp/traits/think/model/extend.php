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

namespace traits\think\model;

use think\Lang;
use think\Loader;

trait Extend
{

    protected $partition = [];

    /**
     * 利用__call方法实现一些特殊的Model方法
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (in_array(strtolower($method), ['count', 'sum', 'min', 'max', 'avg'], true)) {
            // 统计查询的实现
            $field = isset($args[0]) ? $args[0] : '*';
            return $this->getField(strtoupper($method) . '(' . $field . ') AS tp_' . $method);
        } elseif (strtolower(substr($method, 0, 5)) == 'getby') {
            // 根据某个字段获取记录
            $field         = Loader::parseName(substr($method, 5));
            $where[$field] = $args[0];
            return $this->where($where)->find();
        } elseif (strtolower(substr($method, 0, 10)) == 'getfieldby') {
            // 根据某个字段获取记录的某个值
            $name         = Loader::parseName(substr($method, 10));
            $where[$name] = $args[0];
            return $this->where($where)->getField($args[1]);
        } elseif (isset($this->scope[$method])) {
            // 命名范围的单独调用支持
            return $this->scope($method, $args[0]);
        } else {
            throw new \think\Exception(__CLASS__ . ':' . $method . Lang::get('_METHOD_NOT_EXIST_'));
            return;
        }
    }

    /**
     * 设置记录的某个字段值
     * 支持使用数据库字段和方法
     * @access public
     * @param string|array $field  字段名
     * @param string $value  字段值
     * @return boolean
     */
    public function setField($field, $value = '')
    {
        if (is_array($field)) {
            $data = $field;
        } else {
            $data[$field] = $value;
        }
        return $this->save($data);
    }

    /**
     * 字段值增长
     * @access public
     * @param string $field  字段名
     * @param integer $step  增长值
     * @return boolean
     */
    public function setInc($field, $step = 1)
    {
        return $this->setField($field, ['exp', $field . '+' . $step]);
    }

    /**
     * 字段值减少
     * @access public
     * @param string $field  字段名
     * @param integer $step  减少值
     * @return boolean
     */
    public function setDec($field, $step = 1)
    {
        return $this->setField($field, ['exp', $field . '-' . $step]);
    }

    /**
     * 获取一条记录的某个字段值
     * @access public
     * @param string $field  字段名
     * @param string $spea  字段数据间隔符号 NULL返回数组
     * @return mixed
     */
    public function getField($field, $sepa = null)
    {
        $options['field'] = $field;
        $options          = $this->_parseOptions($options);
        $field            = trim($field);
        if (strpos($field, ',')) {
            // 多字段
            if (!isset($options['limit'])) {
                $options['limit'] = is_numeric($sepa) ? $sepa : '';
            }
            $resultSet = $this->db->select($options);
            if (!empty($resultSet)) {
                $field = array_keys($resultSet[0]);
                $key   = array_shift($field);
                $key2  = array_shift($field);
                $cols  = [];
                $count = count(explode(',', $field));
                foreach ($resultSet as $result) {
                    $name = $result[$key];
                    if (2 == $count) {
                        $cols[$name] = $result[$key2];
                    } else {
                        $cols[$name] = is_string($sepa) ? implode($sepa, $result) : $result;
                    }
                }
                return $cols;
            }
        } else {
            // 查找一条记录
            // 返回数据个数
            if (true !== $sepa) {
                // 当sepa指定为true的时候 返回所有数据
                $options['limit'] = is_numeric($sepa) ? $sepa : 1;
            }
            $result = $this->db->select($options);
            if (!empty($result)) {
                if (true !== $sepa && 1 == $options['limit']) {
                    return reset($result[0]);
                }
                foreach ($result as $val) {
                    $array[] = $val[$field];
                }
                return $array;
            }
        }
        return null;
    }

    /**
     * 字段值延迟增长
     * @access public
     * @param string $field  字段名
     * @param integer $step  增长值
     * @param integer $lazyTime  延时时间(s)
     * @return boolean
     */
    public function setLazyInc($field, $step = 1, $lazyTime = 0)
    {
        $condition = $this->options['where'];
        if (empty($condition)) {
            // 没有条件不做任何更新
            return false;
        }
        if ($lazyTime > 0) {
            // 延迟写入
            $guid = md5($this->name . '_' . $field . '_' . serialize($condition));
            $step = $this->lazyWrite($guid, $step, $lazyTime);
            if (false === $step) {
                // 等待下次写入
                return true;
            }
        }
        return $this->setField($field, ['exp', $field . '+' . $step]);
    }

    /**
     * 字段值延迟减少
     * @access public
     * @param string $field  字段名
     * @param integer $step  减少值
     * @param integer $lazyTime  延时时间(s)
     * @return boolean
     */
    public function setLazyDec($field, $step = 1, $lazyTime = 0)
    {
        $condition = $this->options['where'];
        if (empty($condition)) {
            // 没有条件不做任何更新
            return false;
        }
        if ($lazyTime > 0) {
            // 延迟写入
            $guid = md5($this->name . '_' . $field . '_' . serialize($condition));
            $step = $this->lazyWrite($guid, $step, $lazyTime);
            if (false === $step) {
                // 等待下次写入
                return true;
            }
        }
        return $this->setField($field, ['exp', $field . '-' . $step]);
    }

    /**
     * 延时更新检查 返回false表示需要延时
     * 否则返回实际写入的数值
     * @access public
     * @param string $guid  写入标识
     * @param integer $step  写入步进值
     * @param integer $lazyTime  延时时间(s)
     * @return false|integer
     */
    protected function lazyWrite($guid, $step, $lazyTime)
    {
        if (false !== ($value = F($guid))) {
            // 存在缓存写入数据
            if (time() > S($guid . '_time') + $lazyTime) {
                // 延时更新时间到了，删除缓存数据 并实际写入数据库
                S($guid, null);
                S($guid . '_time', null);
                return $value + $step;
            } else {
                // 追加数据到缓存
                S($guid, $value + $step);
                return false;
            }
        } else {
            // 没有缓存数据
            S($guid, $step);
            // 计时开始
            S($guid . '_time', time());
            return false;
        }
    }

    /**
     * 得到分表的的数据表名
     * @access public
     * @param array $data 操作的数据
     * @return string
     */
    public function getPartitionTableName($data = [])
    {
        // 对数据表进行分区
        if (isset($data[$this->partition['field']])) {
            $field = $data[$this->partition['field']];
            switch ($this->partition['type']) {
                case 'id':
                    // 按照id范围分表
                    $step = $this->partition['expr'];
                    $seq  = floor($field / $step) + 1;
                    break;
                case 'year':
                    // 按照年份分表
                    if (!is_numeric($field)) {
                        $field = strtotime($field);
                    }
                    $seq = date('Y', $field) - $this->partition['expr'] + 1;
                    break;
                case 'mod':
                    // 按照id的模数分表
                    $seq = ($field % $this->partition['num']) + 1;
                    break;
                case 'md5':
                    // 按照md5的序列分表
                    $seq = (ord(substr(md5($field), 0, 1)) % $this->partition['num']) + 1;
                    break;
                default:
                    if (function_exists($this->partition['type'])) {
                        // 支持指定函数哈希
                        $fun = $this->partition['type'];
                        $seq = (ord(substr($fun($field), 0, 1)) % $this->partition['num']) + 1;
                    } else {
                        // 按照字段的首字母的值分表
                        $seq = (ord($field{0}) % $this->partition['num']) + 1;
                    }
            }
            return $this->getTableName() . '_' . $seq;
        } else {
            // 当设置的分表字段不在查询条件或者数据中
            // 进行联合查询，必须设定 partition['num']
            $tableName = [];
            for ($i = 0; $i < $this->partition['num']; $i++) {
                $tableName[] = 'SELECT * FROM ' . $this->getTableName() . '_' . ($i + 1);
            }
            return '( ' . implode(" UNION ", $tableName) . ') AS ' . $this->name;
        }
    }
}
