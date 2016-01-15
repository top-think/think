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

trait Adv
{
    protected $optimLock = 'lock_version';
    //protected $serializeField = [];
    //protected $readonlyField  = [];
    //protected $partition      = [];

    /**
     * 利用__call方法重载 实现一些特殊的Model方法 （魔术方法）
     * @access public
     * @param string $method 方法名称
     * @param mixed $args 调用参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (strtolower(substr($method, 0, 3)) == 'top') {
            // 获取前N条记录
            $count = substr($method, 3);
            array_unshift($args, $count);
            return call_user_func_array([ & $this, 'topN'], $args);
        } else {
            return parent::__call($method, $args);
        }
    }

    /**
     * 对保存到数据库的数据进行处理
     * @access protected
     * @param mixed $data 要操作的数据
     * @return boolean
     */
    protected function _before_write(&$data)
    {
        // 检查序列化字段
        $data = $this->serializeField($data);
    }

    // 查询成功后的回调方法
    protected function _after_find(&$result, $options = [])
    {
        // 检查序列化字段
        $this->checkSerializeField($result);
        // 缓存乐观锁
        $this->cacheLockVersion($result);
    }

    // 查询数据集成功后的回调方法
    protected function _after_select(&$resultSet, $options = [])
    {
        // 检查序列化字段
        $resultSet = $this->checkListSerializeField($resultSet);
    }

    // 写入前的回调方法
    protected function _before_insert(&$data, $options = [])
    {
        // 记录乐观锁
        $data = $this->recordLockVersion($data);
    }

    // 更新前的回调方法
    protected function _before_update(&$data, $options = [])
    {
        // 检查乐观锁
        $pk = $this->getPK();
        if (isset($options['where'][$pk])) {
            $id = $options['where'][$pk];
            if (!$this->checkLockVersion($id, $data)) {
                return false;
            }
        }
        // 检查只读字段
        $data = $this->checkReadonlyField($data);
    }

    /**
     * 记录乐观锁
     * @access protected
     * @param array $data 数据对象
     * @return array
     */
    protected function recordLockVersion($data)
    {
        // 记录乐观锁
        if ($this->optimLock && !isset($data[$this->optimLock])) {
            if (in_array($this->optimLock, $this->fields, true)) {
                $data[$this->optimLock] = 0;
            }
        }
        return $data;
    }

    /**
     * 缓存乐观锁
     * @access protected
     * @param array $data 数据对象
     * @return void
     */
    protected function cacheLockVersion($data)
    {
        if ($this->optimLock) {
            if (isset($data[$this->optimLock]) && isset($data[$this->getPk()])) {
                // 只有当存在乐观锁字段和主键有值的时候才记录乐观锁
                $_SESSION[$this->name . '_' . $data[$this->getPk()] . '_lock_version'] = $data[$this->optimLock];
            }
        }
    }

    /**
     * 检查乐观锁
     * @access protected
     * @param inteter $id  当前主键
     * @param array $data  当前数据
     * @return mixed
     */
    protected function checkLockVersion($id, &$data)
    {
        // 检查乐观锁
        $identify = $this->name . '_' . $id . '_lock_version';
        if ($this->optimLock && isset($_SESSION[$identify])) {
            $lock_version        = $_SESSION[$identify];
            $vo                  = $this->field($this->optimLock)->find($id);
            $_SESSION[$identify] = $lock_version;
            $curr_version        = $vo[$this->optimLock];
            if (isset($curr_version)) {
                if ($curr_version > 0 && $lock_version != $curr_version) {
                    // 记录已经更新
                    $this->error = 'record has update';
                    return false;
                } else {
                    // 更新乐观锁
                    $save_version = $data[$this->optimLock];
                    if ($save_version != $lock_version + 1) {
                        $data[$this->optimLock] = $lock_version + 1;
                    }
                    $_SESSION[$identify] = $lock_version + 1;
                }
            }
        }
        return true;
    }

    /**
     * 查找前N个记录
     * @access public
     * @param integer $count 记录个数
     * @param array $options 查询表达式
     * @return array
     */
    public function topN($count, $options = [])
    {
        $options['limit'] = $count;
        return $this->select($options);
    }

    /**
     * 查询符合条件的第N条记录
     * 0 表示第一条记录 -1 表示最后一条记录
     * @access public
     * @param integer $position 记录位置
     * @param array $options 查询表达式
     * @return mixed
     */
    public function getN($position = 0, $options = [])
    {
        if ($position >= 0) {
            // 正向查找
            $options['limit'] = $position . ',1';
            $list             = $this->select($options);
            return $list ? $list[0] : false;
        } else {
            // 逆序查找
            $list = $this->select($options);
            return $list ? $list[count($list) - abs($position)] : false;
        }
    }

    /**
     * 获取满足条件的第一条记录
     * @access public
     * @param array $options 查询表达式
     * @return mixed
     */
    public function first($options = [])
    {
        return $this->getN(0, $options);
    }

    /**
     * 获取满足条件的最后一条记录
     * @access public
     * @param array $options 查询表达式
     * @return mixed
     */
    public function last($options = [])
    {
        return $this->getN(-1, $options);
    }

    /**
     * 返回数据
     * @access public
     * @param array $data 数据
     * @param string $type 返回类型 默认为数组
     * @return mixed
     */
    public function returnResult($data, $type = 'array')
    {

        switch ($type) {
            case 'array':
                return $data;
            case 'object':
                return (object) $data;
            default: // 允许用户自定义返回类型
                if (class_exists($type)) {
                    return new $type($data);
                } else {
                    throw new \think\Exception(' class not exist :' . $type);
                }
        }
    }

    /**
     * 返回数据列表
     * @access protected
     * @param array $resultSet 数据
     * @param string $type 返回类型 默认为数组
     * @return void
     */
    protected function returnResultSet(&$resultSet, $type = '')
    {
        foreach ($resultSet as $key => $data) {
            $resultSet[$key] = $this->returnResult($data, $type);
        }
        return $resultSet;
    }

    /**
     * 检查序列化数据字段
     * @access protected
     * @param array $data 数据
     * @return array
     */
    protected function serializeField(&$data)
    {
        // 检查序列化字段
        if (!empty($this->serializeField)) {
            // 定义方式  $this->serializeField = ['ser'=>['name','email']];
            foreach ($this->serializeField as $key => $val) {
                if (empty($data[$key])) {
                    $serialize = [];
                    foreach ($val as $name) {
                        if (isset($data[$name])) {
                            $serialize[$name] = $data[$name];
                            unset($data[$name]);
                        }
                    }
                    if (!empty($serialize)) {
                        $data[$key] = serialize($serialize);
                    }
                }
            }
        }
        return $data;
    }

    // 检查返回数据的序列化字段
    protected function checkSerializeField(&$result)
    {
        // 检查序列化字段
        if (!empty($this->serializeField)) {
            foreach ($this->serializeField as $key => $val) {
                if (isset($result[$key])) {
                    $serialize = unserialize($result[$key]);
                    foreach ($serialize as $name => $value) {
                        $result[$name] = $value;
                    }

                    unset($serialize, $result[$key]);
                }
            }
        }
        return $result;
    }

    // 检查数据集的序列化字段
    protected function checkListSerializeField(&$resultSet)
    {
        // 检查序列化字段
        if (!empty($this->serializeField)) {
            foreach ($this->serializeField as $key => $val) {
                foreach ($resultSet as $k => $result) {
                    if (isset($result[$key])) {
                        $serialize = unserialize($result[$key]);
                        foreach ($serialize as $name => $value) {
                            $result[$name] = $value;
                        }

                        unset($serialize, $result[$key]);
                        $resultSet[$k] = $result;
                    }
                }
            }
        }
        return $resultSet;
    }

    /**
     * 检查只读字段
     * @access protected
     * @param array $data 数据
     * @return array
     */
    protected function checkReadonlyField(&$data)
    {
        if (!empty($this->readonlyField)) {
            foreach ($this->readonlyField as $key => $field) {
                if (isset($data[$field])) {
                    unset($data[$field]);
                }
            }
        }
        return $data;
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
