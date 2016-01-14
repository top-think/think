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

class Model
{
    // 操作状态
    const MODEL_INSERT    = 1; //  新增
    const MODEL_UPDATE    = 2; //  更新
    const MODEL_BOTH      = 3; //  全部
    const EXISTS_VALIDATE = 0; // 存在就验证
    const MUST_VALIDATE   = 1; // 必须验证
    const VALUE_VALIDATE  = 2; // 有值就验证
    // 当前数据库操作对象
    protected $db = null;
    // 数据库对象池
    private $_db = [];
    // 主键名称
    protected $pk = 'id';
    // 数据表前缀
    protected $tablePrefix = '';
    // 模型名称
    protected $name = '';
    // 数据库名称
    protected $dbName = '';
    //数据库配置
    protected $connection = '';
    // 数据表名（不包含表前缀）
    protected $tableName = '';
    // 实际数据表名（包含表前缀）
    protected $trueTableName = '';
    // 最近错误信息
    protected $error = '';
    // 字段信息
    protected $fields = [];
    // 数据信息
    protected $data = [];
    // 查询表达式参数
    protected $options = [];
    // 命名范围定义
    protected $scope = [];
    // 字段映射定义
    protected $map = [];

    /**
     * 架构函数
     * @access public
     * @param string $name 模型名称
     * @param array $config 模型配置
     */
    public function __construct($name = '', $config = [])
    {
        // 模型初始化
        $this->_initialize();
        // 传入模型参数
        if (!empty($name)) {
            $this->name = $name;
        } elseif (empty($this->name)) {
            $this->name = $this->getModelName();
        }
        if (strpos($this->name, '.')) {
            // 支持 数据库名.模型名的 定义
            list($this->dbName, $this->name) = explode('.', $this->name);
        }

        $this->tablePrefix = !empty($config['prefix']) ? $config['prefix'] : Config::get('database.prefix');
        if (!empty($config['connection'])) {
            $this->connection = $config['connection'];
        }
        if (!empty($config['table_name'])) {
            $this->tableName = $config['table_name'];
        }
        if (!empty($config['true_table_name'])) {
            $this->trueTableName = $config['true_table_name'];
        }
        if (!empty($config['db_name'])) {
            $this->dbName = $config['db_name'];
        }

        // 数据库初始化操作
        // 获取数据库操作对象
        // 当前模型有独立的数据库连接信息
        $this->db(0, $this->connection);
    }

    /**
     * 设置数据对象的值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return void
     */
    public function __set($name, $value)
    {
        // 设置数据对象属性
        $this->data[$name] = $value;
    }

    /**
     * 获取数据对象的值
     * @access public
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * 检测数据对象的值
     * @access public
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * 销毁数据对象的值
     * @access public
     * @param string $name 名称
     * @return void
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

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
            throw new \think\Exception(__CLASS__ . ':' . $method . ' method not exist');
        }
    }

    // 回调方法 初始化模型
    protected function _initialize()
    {}

    /**
     * 对写入到数据库的数据进行处理
     * @access protected
     * @param mixed $data 要操作的数据
     * @return array
     */
    protected function _write_data($data)
    {
        // 检查字段映射
        if (!empty($this->map)) {
            foreach ($this->map as $key => $val) {
                if (isset($data[$key])) {
                    $data[$val] = $data[$key];
                    unset($data[$key]);
                }
            }
        }
        // 检查非数据字段
        if (!empty($this->fields)) {
            foreach ($data as $key => $val) {
                if (!in_array($key, $this->fields, true)) {
                    unset($data[$key]);
                } elseif (is_scalar($val) && empty($this->options['bind'][':' . $key])) {
                    // 字段类型检查
                    $this->_parseType($data, $key);
                }
            }
        }
        // 安全过滤
        if (!empty($this->options['filter'])) {
            $data = array_map($this->options['filter'], $data);
            unset($this->options['filter']);
        }
        // 回调方法
        $this->_before_write($data);
        return $data;
    }
    // 写入数据前的回调方法 包括新增和更新
    protected function _before_write(&$data)
    {}

    /**
     * 新增数据
     * @access public
     * @param mixed $data 数据
     * @param boolean $replace 是否replace
     * @return mixed
     */
    public function add($data = '', $replace = false)
    {
        if (empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if (!empty($this->data)) {
                $data = $this->data;
                // 重置数据
                $this->data = [];
            } else {
                $this->error = Lang::get('_DATA_TYPE_INVALID_');
                return false;
            }
        }
        // 数据处理
        $data = $this->_write_data($data);
        // 分析表达式
        $options = $this->_parseOptions();
        if (false === $this->_before_insert($data, $options)) {
            return false;
        }
        // 写入数据到数据库
        $result = $this->db->insert($data, $options, $replace);
        if (false !== $result && is_numeric($result)) {
            $pk = $this->getPk();
            // 增加复合主键支持
            if (is_array($pk)) {
                return $result;
            }
            $insertId = $this->getLastInsID();
            if ($insertId) {
                // 自增主键返回插入ID
                $data[$pk] = $insertId;
                if (false === $this->_after_insert($data, $options)) {
                    return false;
                }
                return $insertId;
            }
            if (false === $this->_after_insert($data, $options)) {
                return false;
            }
        }
        return $result;
    }
    // 插入数据前的回调方法
    protected function _before_insert(&$data, $options = [])
    {}
    // 插入成功后的回调方法
    protected function _after_insert($data, $options = [])
    {}

    public function addAll($dataList, $options = [], $replace = false)
    {
        if (empty($dataList)) {
            $this->error = Lang::get('_DATA_TYPE_INVALID_');
            return false;
        }
        // 数据处理
        foreach ($dataList as $key => $data) {
            $dataList[$key] = $this->_write_data($data);
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 写入数据到数据库
        $result = $this->db->insertAll($dataList, $options, $replace);
        if (false !== $result) {
            $insertId = $this->getLastInsID();
            if ($insertId) {
                return $insertId;
            }
        }
        return $result;
    }

    /**
     * 保存数据
     * @access public
     * @param mixed $data 数据
     * @return boolean
     */
    public function save($data = '')
    {
        if (empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if (!empty($this->data)) {
                $data = $this->data;
                // 重置数据
                $this->data = [];
            } else {
                $this->error = Lang::get('_DATA_TYPE_INVALID_');
                return false;
            }
        }
        // 数据处理
        $data = $this->_write_data($data);
        if (empty($data)) {
            // 没有数据则不执行
            $this->error = Lang::get('_DATA_TYPE_INVALID_');
            return false;
        }
        // 分析表达式
        $options = $this->_parseOptions();
        $pk      = $this->getPk();
        if (!isset($options['where'])) {
            // 如果存在主键数据 则自动作为更新条件
            if (is_string($pk) && isset($data[$pk])) {
                $where[$pk] = $data[$pk];
                unset($data[$pk]);
            } elseif (is_array($pk)) {
                // 增加复合主键支持
                foreach ($pk as $field) {
                    if (isset($data[$field])) {
                        $where[$field] = $data[$field];
                    } else {
                        // 如果缺少复合主键数据则不执行
                        $this->error = Lang::get('_OPERATION_WRONG_');
                        return false;
                    }
                    unset($data[$field]);
                }
            }
            if (!isset($where)) {
                // 如果没有任何更新条件则不执行
                $this->error = Lang::get('_OPERATION_WRONG_');
                return false;
            } else {
                $options['where'] = $where;
            }
        }
        if (isset($options['where'][$pk])) {
            $pkValue = $options['where'][$pk];
        }
        if (false === $this->_before_update($data, $options)) {
            return false;
        }
        $result = $this->db->update($data, $options);
        if (false !== $result && is_numeric($result)) {
            if (isset($pkValue)) {
                $data[$pk] = $pkValue;
            }

            $this->_after_update($data, $options);
        }
        return $result;
    }
    // 更新数据前的回调方法
    protected function _before_update(&$data, $options = [])
    {}
    // 更新成功后的回调方法
    protected function _after_update($data, $options = [])
    {}

    /**
     * 删除数据
     * @access public
     * @param mixed $options 表达式
     * @return mixed
     */
    public function delete($options = [])
    {
        $pk = $this->getPk();
        if (empty($options) && empty($this->options['where'])) {
            // 如果删除条件为空 则删除当前数据对象所对应的记录
            if (!empty($this->data) && isset($this->data[$pk])) {
                return $this->delete($this->data[$pk]);
            } else {
                return false;
            }
        }
        if (is_numeric($options) || is_string($options)) {
            // 根据主键删除记录
            if (strpos($options, ',')) {
                $where[$pk] = ['IN', $options];
            } else {
                $where[$pk] = $options;
            }
            $options          = [];
            $options['where'] = $where;
        }
        // 根据复合主键删除记录
        if (is_array($options) && (count($options) > 0) && is_array($pk)) {
            $count = 0;
            foreach (array_keys($options) as $key) {
                if (is_int($key)) {
                    $count++;
                }
            }
            if (count($pk) == $count) {
                $i = 0;
                foreach ($pk as $field) {
                    $where[$field] = $options[$i];
                    unset($options[$i++]);
                }
                $options['where'] = $where;
            } else {
                return false;
            }
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        if (empty($options['where'])) {
            // 如果条件为空 不进行删除操作 除非设置 1=1
            return false;
        }
        if (isset($options['where'][$pk])) {
            $pkValue = $options['where'][$pk];
        }
        $result = $this->db->delete($options);
        if (false !== $result && is_numeric($result)) {
            $data = [];
            if (isset($pkValue)) {
                $data[$pk] = $pkValue;
            }
            $this->_after_delete($data, $options);
        }
        // 返回删除记录个数
        return $result;
    }
    // 删除成功后的回调方法
    protected function _after_delete($data, $options = [])
    {}

    /**
     * 查询数据集
     * @access public
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function select($options = [])
    {
        $pk = $this->getPk();
        if (is_string($options) || is_numeric($options)) {
            // 根据主键查询
            if (strpos($options, ',')) {
                $where[$pk] = ['IN', $options];
            } else {
                $where[$pk] = $options;
            }
            $options          = [];
            $options['where'] = $where;
        } elseif (is_array($options) && (count($options) > 0) && is_array($pk)) {
            // 根据复合主键查询
            $count = 0;
            foreach (array_keys($options) as $key) {
                if (is_int($key)) {
                    $count++;
                }
            }
            if (count($pk) == $count) {
                $i = 0;
                foreach ($pk as $field) {
                    $where[$field] = $options[$i];
                    unset($options[$i++]);
                }
                $options['where'] = $where;
            } else {
                return false;
            }
        } elseif (false === $options) {
            // 用于子查询 不查询只返回SQL
            $options['fetch_sql'] = true;
        }

        // 分析表达式
        $options = $this->_parseOptions($options);
        // 判断查询缓存
        if (isset($options['cache'])) {
            $cache = $options['cache'];
            $key   = is_string($cache['key']) ? $cache['key'] : md5(serialize($options));
            $data  = Cache::get($key);
            if (false !== $data) {
                return $data;
            }
        }
        $resultSet = $this->db->select($options);
        if (false === $resultSet) {
            return false;
        }

        if (!empty($resultSet)) {
            // 有查询结果
            if (is_string($resultSet)) {
                return $resultSet;
            }

            // 数据列表读取后的处理
            $resultSet = $this->_read_datalist($resultSet, $options);
            // 回调
            $this->_after_select($resultSet, $options);
            if (isset($options['index'])) {
                // 对数据集进行索引
                $index = explode(',', $options['index']);
                foreach ($resultSet as $result) {
                    $_key = $result[$index[0]];
                    if (isset($index[1]) && isset($result[$index[1]])) {
                        $cols[$_key] = $result[$index[1]];
                    } else {
                        $cols[$_key] = $result;
                    }
                }
                $resultSet = $cols;
            }
        }

        if (isset($cache)) {
            Cache::set($key, $resultSet, $cache['expire']);
        }

        return $resultSet;
    }

    /**
     * 数据列表读取后的处理
     * @access protected
     * @param array $data 当前数据
     * @return array
     */
    protected function _read_datalist($resultSet, $options)
    {
        $resultSet = array_map([$this, '_read_data'], $resultSet);
        return $resultSet;
    }
    // 查询成功后的回调方法
    protected function _after_select(&$resultSet, $options = [])
    {}

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
        // 判断查询缓存
        if (isset($options['cache'])) {
            $cache = $options['cache'];
            $key   = is_string($cache['key']) ? $cache['key'] : md5($sepa . serialize($options));
            $data  = Cache::get($key);
            if (false !== $data) {
                return $data;
            }
        }
        if (strpos($field, ',')) {
            // 多字段
            if (!isset($options['limit'])) {
                $options['limit'] = is_numeric($sepa) ? $sepa : '';
            }
            $resultSet = $this->db->select($options);
            if (!empty($resultSet)) {
                if (is_string($resultSet)) {
                    return $resultSet;
                }
                $_field = explode(',', $field);
                $field  = array_keys($resultSet[0]);
                $key1   = array_shift($field);
                $key2   = array_shift($field);
                $cols   = array();
                $count  = count($_field);
                foreach ($resultSet as $result) {
                    $name = $result[$key1];
                    if (2 == $count) {
                        $cols[$name] = $result[$key2];
                    } else {
                        $cols[$name] = is_string($sepa) ? implode($sepa, array_slice($result, 1)) : $result;
                    }
                }
                if (isset($cache)) {
                    Cache::set($key, $cols, $cache['expire']);
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
                if (is_string($result)) {
                    return $result;
                }
                if (true !== $sepa && 1 == $options['limit']) {
                    $data = reset($result[0]);
                    if (isset($cache)) {
                        Cache::set($key, $data, $cache['expire']);
                    }
                    return $data;
                }
                foreach ($result as $val) {
                    $array[] = $val[$field];
                }
                if (isset($cache)) {
                    Cache::set($key, $array, $cache['expire']);
                }
                return $array;
            }
        }
        return null;
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
     * 字段值(延迟)增长
     * @access public
     * @param string $field  字段名
     * @param integer $step  增长值
     * @param integer $lazyTime  延时时间(s)
     * @return boolean
     */
    public function setInc($field, $step = 1, $lazyTime = 0)
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
            if (empty($step)) {
                return true; // 等待下次写入
            } elseif ($step < 0) {
                $step = '-' . $step;
            }
        }
        return $this->setField($field, ['exp', $field . '+' . $step]);
    }

    /**
     * 字段值（延迟）减少
     * @access public
     * @param string $field  字段名
     * @param integer $step  减少值
     * @param integer $lazyTime  延时时间(s)
     * @return boolean
     */
    public function setDec($field, $step = 1, $lazyTime = 0)
    {
        $condition = $this->options['where'];
        if (empty($condition)) {
            // 没有条件不做任何更新
            return false;
        }
        if ($lazyTime > 0) {
            // 延迟写入
            $guid = md5($this->name . '_' . $field . '_' . serialize($condition));
            $step = $this->lazyWrite($guid, -$step, $lazyTime);
            if (empty($step)) {
                return true; // 等待下次写入
            } elseif ($step > 0) {
                $step = '-' . $step;
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
        if (false !== ($value = Cache::get($guid))) {
            // 存在缓存写入数据
            if (NOW_TIME > Cache::get($guid . '_time') + $lazyTime) {
                // 延时更新时间到了，删除缓存数据 并实际写入数据库
                Cache::rm($guid);
                Cache::rm($guid . '_time');
                return $value + $step;
            } else {
                // 追加数据到缓存
                Cache::set($guid, $value + $step, 0);
                return false;
            }
        } else {
            // 没有缓存数据
            Cache::set($guid, $step, 0);
            // 计时开始
            Cache::set($guid . '_time', NOW_TIME, 0);
            return false;
        }
    }

    /**
     * 生成查询SQL 可用于子查询
     * @access public
     * @param array $options 表达式参数
     * @return string
     */
    public function buildSql($options = [])
    {
        return '( ' . $this->fetchSql(true)->select() . ' )';
    }

    /**
     * 分析表达式（可用于查询或者写入操作）
     * @access protected
     * @param array $options 表达式参数
     * @return array
     */
    protected function _parseOptions($options = [])
    {
        if (is_array($options)) {
            $options = array_merge($this->options, $options);
        }

        // 记录操作的模型名称
        $options['model'] = $this->name;

        if (isset($options['table'])) {
            // 动态指定表名
            $fields = $this->db->getFields($options['table']);
            $fields = $fields ? array_keys($fields) : false;
        } else {
            $options['table'] = $this->getTableName();
            $fields           = $this->getDbFields();
        }
        if (!empty($options['alias'])) {
            $options['table'] .= ' ' . $options['alias'];
        }
        // 字段类型验证
        if (isset($options['where']) && is_array($options['where']) && !empty($fields)) {
            // 对数组查询条件进行字段类型检查
            foreach ($options['where'] as $key => $val) {
                $key = trim($key);
                if (in_array($key, $fields, true)) {
                    if (is_scalar($val) && empty($options['bind'][':' . $key])) {
                        $this->_parseType($options['where'], $key);
                    }
                }
            }
        }
        // 查询过后清空sql表达式组装 避免影响下次查询
        $this->options = [];
        // 表达式过滤
        $this->_options_filter($options);
        return $options;
    }
    // 表达式过滤回调方法
    protected function _options_filter(&$options)
    {}

    /**
     * 数据类型检测
     * @access protected
     * @param mixed $data 数据
     * @param string $key 字段名
     * @return void
     */
    protected function _parseType(&$data, $key)
    {
        if (!isset($this->options['bind'][':' . $key]) && isset($this->fields['_type'][$key])) {
            $fieldType = strtolower($this->fields['_type'][$key]);
            if (false !== strpos($fieldType, 'enum')) {
                // 支持ENUM类型优先检测
            } elseif (false === strpos($fieldType, 'bigint') && false !== strpos($fieldType, 'int')) {
                $data[$key] = intval($data[$key]);
            } elseif (false !== strpos($fieldType, 'float') || false !== strpos($fieldType, 'double')) {
                $data[$key] = floatval($data[$key]);
            } elseif (false !== strpos($fieldType, 'bool')) {
                $data[$key] = (bool) $data[$key];
            } elseif (false !== strpos($fieldType, 'json') && is_array($data[$key])) {
                $data[$key] = json_encode($data[$key]);
            }
        }
    }

    /**
     * 查询数据
     * @access public
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function find($options = [])
    {
        $pk = $this->getPk();
        if (is_numeric($options) || is_string($options)) {
            $where[$pk]       = $options;
            $options          = [];
            $options['where'] = $where;
        }
        // 根据复合主键查找记录
        if (is_array($options) && is_array($pk) && (count($options) > 0)) {
            // 根据复合主键查询
            $count = 0;
            foreach (array_keys($options) as $key) {
                if (is_int($key)) {
                    $count++;
                }
            }
            if (count($pk) == $count) {
                $i = 0;
                foreach ($pk as $field) {
                    $where[$field] = $options[$i];
                    unset($options[$i++]);
                }
                $options['where'] = $where;
            } else {
                return false;
            }
        }
        // 总是查找一条记录
        $options['limit'] = 1;
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 判断查询缓存
        if (isset($options['cache'])) {
            $cache = $options['cache'];
            $key   = is_string($cache['key']) ? $cache['key'] : md5(serialize($options));
            $data  = Cache::get($key);
            if (false !== $data) {
                $this->data = $data;
                return $data;
            }
        }
        $resultSet = $this->db->select($options);
        if (false === $resultSet) {
            return false;
        }
        if (empty($resultSet)) {
            // 查询结果为空
            return null;
        }
        if (is_string($resultSet)) {
            return $resultSet;
        }
        // 数据处理
        $data = $this->_read_data($resultSet[0], $options);
        // 数据对象赋值
        $this->data = $data;
        // 回调
        $this->_after_find($data, $options);
        if (isset($cache)) {
            Cache::set($key, $data, $cache['expire']);
        }
        return $this->data;
    }

    /**
     * 数据读取后的处理
     * @access protected
     * @param array $data 当前数据
     * @return array
     */
    protected function _read_data($data, $options = [])
    {
        // 检查字段映射
        if (!empty($this->map)) {
            foreach ($this->map as $key => $val) {
                if (isset($data[$val])) {
                    $data[$key] = $data[$val];
                    unset($data[$val]);
                }
            }
        }
        return $data;
    }
    // 数据读取成功后的回调方法
    protected function _after_find(&$result, $options = [])
    {}

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
            $data = $_POST;
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
            foreach ($data as $key => $val) {
                if (!in_array($key, $fields)) {
                    unset($data[$key]);
                }
            }
        }
        // 过滤创建的数据
        $this->_create_filter($data);
        // 赋值当前数据对象
        $this->data = $data;
        // 返回创建的数据以供其他调用
        return $data;
    }
    // 数据对象创建后的回调方法
    protected function _create_filter(&$data)
    {}

    /**
     * 切换当前的数据库连接
     * @access public
     * @param mixed $linkId  连接标识
     * @param mixed $config  数据库连接信息
     * @return Model
     */
    public function db($linkId = '', $config = '')
    {
        if ('' === $linkId && $this->db) {
            return $this->db;
        }

        if (!isset($this->_db[$linkId])) {
            // 创建一个新的实例
            if (is_string($linkId) && '' == $config) {
                $config = Config::get($linkId);
            } elseif (!empty($config) && is_string($config) && false === strpos($config, '/')) {
                // 支持读取配置参数
                $config = Config::get($config);
            }
            $this->_db[$linkId] = Db::instance($config);
        } elseif (null === $config) {
            $this->_db[$linkId]->close(); // 关闭数据库连接
            unset($this->_db[$linkId]);
            return;
        }

        // 切换数据库连接
        $this->db = $this->_db[$linkId];
        $this->_after_db();
        return $this;
    }
    // 数据库切换后回调方法
    protected function _after_db()
    {}

    /**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     */
    public function getModelName()
    {
        if (empty($this->name)) {
            $this->name = basename(get_class($this));
        }
        return $this->name;
    }

    /**
     * 得到完整的数据表名
     * @access public
     * @return string
     */
    public function getTableName()
    {
        if (empty($this->trueTableName)) {
            $tableName = !empty($this->tablePrefix) ? $this->tablePrefix : '';
            if (!empty($this->tableName)) {
                $tableName .= $this->tableName;
            } else {
                $tableName .= Loader::parseName($this->name);
            }
            $this->trueTableName = strtolower($tableName);
        }
        return (!empty($this->dbName) ? $this->dbName . '.' : '') . $this->trueTableName;
    }

    /**
     * 返回模型的错误信息
     * @access public
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 返回数据库的错误信息
     * @access public
     * @return string
     */
    public function getDbError()
    {
        return $this->db->getError();
    }

    /**
     * 返回最后插入的ID
     * @access public
     * @return string
     */
    public function getLastInsID()
    {
        return $this->db->getLastInsID();
    }

    /**
     * 返回最后执行的sql语句
     * @access public
     * @return string
     */
    public function getLastSql()
    {
        return $this->db->getLastSql($this->name);
    }

    /**
     * 获取主键名称
     * @access public
     * @return string
     */
    public function getPk()
    {
        return isset($this->fields['_pk']) ? $this->fields['_pk'] : $this->pk;
    }

    /**
     * 获取数据表字段信息
     * @access public
     * @return array
     */
    public function getDbFields()
    {
        if ($this->fields) {
            $fields = $this->fields;
            unset($fields['_pk'], $fields['_type']);
            return $fields;
        } else {
            $tableName = $this->getTableName();
            $fields    = Cache::get(md5($tableName));
            if (!$fields) {
                $fields       = $this->db->getFields($tableName);
                $this->fields = array_keys($fields);
                foreach ($fields as $key => $val) {
                    // 记录字段类型
                    $type[$key] = $val['type'];
                    if (!empty($val['primary'])) {
                        // 增加复合主键支持
                        if (!empty($this->fields['_pk'])) {
                            if (is_string($this->fields['_pk'])) {
                                $this->pk            = [$this->fields['_pk']];
                                $this->fields['_pk'] = $this->pk;
                            }
                            $this->pk[]            = $key;
                            $this->fields['_pk'][] = $key;
                        } else {
                            $this->pk            = $key;
                            $this->fields['_pk'] = $key;
                        }
                    }
                }
                // 记录字段类型信息
                $this->fields['_type'] = $type;
                Cache::set(md5($tableName), $this->fields);
                $fields = $this->fields;
            } else {
                $this->fields = $fields;
            }
            unset($fields['_pk'], $fields['_type']);
            return $fields;
        }
    }

    /**
     * SQL查询
     * @access public
     * @param string $sql  SQL指令
     * @return mixed
     */
    public function query($sql)
    {
        $sql = $this->parseSql($sql);
        return $this->db->query($sql);
    }

    /**
     * 执行SQL语句
     * @access public
     * @param string $sql  SQL指令
     * @return false | integer
     */
    public function execute($sql)
    {
        $sql = $this->parseSql($sql);
        return $this->db->execute($sql);
    }

    /**
     * 解析SQL语句
     * @access public
     * @param string $sql  SQL指令
     * @return string
     */
    protected function parseSql($sql)
    {
        // 分析表达式
        $sql    = strtr($sql, ['__TABLE__' => $this->getTableName(), '__PREFIX__' => $this->tablePrefix]);
        $prefix = $this->tablePrefix;
        $sql    = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {return $prefix . strtolower($match[1]);}, $sql);
        $this->db->setModel($this->name);
        return $sql;
    }

    /**
     * 设置数据对象值
     * @access public
     * @param mixed $data 数据
     * @return Model
     */
    public function data($data = '')
    {
        if ('' === $data && !empty($this->data)) {
            return $this->data;
        }
        if (is_object($data)) {
            $data = get_object_vars($data);
        } elseif (is_string($data)) {
            parse_str($data, $data);
        } elseif (!is_array($data)) {
            throw new Exception('data type invalid', 10300);
        }
        $this->data = $data;
        return $this;
    }

    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed $join
     * @param string $type JOIN类型
     * @return Model
     */
    public function _join($join, $type = 'INNER')
    {
        $prefix = $this->tablePrefix;
        if (is_array($join)) {
            foreach ($join as $key => &$_join) {
                $_join = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {return $prefix . strtolower($match[1]);}, $_join);
                $_join = false !== stripos($_join, 'JOIN') ? $_join : $type . ' JOIN ' . $_join;
            }
            $this->options['join'] = $join;
        } elseif (!empty($join)) {
            //将__TABLE_NAME__字符串替换成带前缀的表名
            $join = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {return $prefix . strtolower($match[1]);}, $join);
            $this->options['join'][] = false !== stripos($join, 'JOIN') ? $join : $type . ' JOIN ' . $join;
        }
        return $this;
    }

    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed $join 关联的表名
     * @param mixed $condition 条件
     * @param string $type JOIN类型
     * @return Model
     */
    public function join($join, $condition = null, $type = 'INNER')
    {
        if (empty($join)) {
            return $this;
        }

        if (empty($condition)) {
            if (is_array($join) && is_array($join[0])) {
                // 如果为组数，则循环调用join
                foreach ($join as $key => $value) {
                    if (is_array($value) && 2 <= count($value)) {
                        $this->join($value[0], $value[1], isset($value[2]) ? $value[2] : $type);
                    }
                }
            } else {
                $this->_join($join, $condition); // 兼容原来的join写法
            }
        } elseif (in_array(strtoupper($condition), array('INNER', 'LEFT', 'RIGHT', 'ALL'))) {
            $this->_join($join, $condition); // 兼容原来的join写法
        } else {
            $prefix = $this->tablePrefix;
            // 传入的表名为数组
            if (is_array($join)) {
                if (0 !== key($join)) {
                    // 键名为表名，键值作为表的别名
                    $table = key($join) . ' ' . current($join);
                } else {
                    $table = current($join);
                }
                if (isset($join[1])) {
                    // 第二个元素为字符串则把第二元素作为表前缀
                    if (is_string($join[1])) {
                        $table = $join[1] . $table;
                    }
                } else {
                    // 加上默认的表前缀
                    $table = $prefix . $table;
                }
            } else {
                $join = trim($join);
                if (0 === strpos($join, '__')) {
                    //将__TABLE_NAME__字符串替换成带前缀的表名
                    $table = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {
                        return $prefix . strtolower($match[1]);
                    }, $join);
                } elseif (false === strpos($join, '(') && 0 !== strpos($join, $prefix)) {
                    // 传入的表名中不带有'('并且不以默认的表前缀开头时加上默认的表前缀
                    $table = $prefix . $join;
                } else {
                    $table = $join;
                }
            }
            if (is_array($condition)) {
                $condition = implode(' AND ', $condition);
            }
            $this->options['join'][] = strtoupper($type) . ' JOIN ' . $table . ' ON ' . $condition;
        }
        return $this;
    }

    /**
     * 查询SQL组装 union
     * @access public
     * @param mixed $union
     * @param boolean $all
     * @return Model
     */
    public function union($union, $all = false)
    {
        if (empty($union)) {
            return $this;
        }

        if ($all) {
            $this->options['union']['_all'] = true;
        }
        if (is_object($union)) {
            $union = get_object_vars($union);
        }
        // 转换union表达式
        if (is_string($union)) {
            $prefix = $this->tablePrefix;
            //将__TABLE_NAME__字符串替换成带前缀的表名
            $options = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {return $prefix . strtolower($match[1]);}, $union);
        } elseif (is_array($union)) {
            if (isset($union[0])) {
                $this->options['union'] = array_merge($this->options['union'], $union);
                return $this;
            } else {
                $options = $union;
            }
        } else {
            throw new Exception('data type invalid', 10300);
        }
        $this->options['union'][] = $options;
        return $this;
    }

    /**
     * 查询缓存
     * @access public
     * @param mixed $key
     * @param integer $expire
     * @param string $type
     * @return Model
     */
    public function cache($key = true, $expire = null, $type = '')
    {
        // 增加快捷调用方式 cache(10) 等同于 cache(true, 10)
        if (is_numeric($key) && is_null($expire)) {
            $expire = $key;
            $key    = true;
        }
        if (false !== $key) {
            $this->options['cache'] = ['key' => $key, 'expire' => $expire, 'type' => $type];
        }
        return $this;
    }

    /**
     * 指定查询字段 支持字段排除
     * @access public
     * @param mixed $field
     * @param boolean $except 是否排除
     * @return Model
     */
    public function field($field, $except = false)
    {
        if (true === $field) {
            // 获取全部字段
            $fields = $this->getDbFields();
            $field  = $fields ?: '*';
        } elseif ($except) {
            // 字段排除
            if (is_string($field)) {
                $field = explode(',', $field);
            }
            $fields = $this->getDbFields();
            $field  = $fields ? array_diff($fields, $field) : $field;
        }
        $this->options['field'] = $field;
        return $this;
    }

    /**
     * 调用命名范围
     * @access public
     * @param mixed $scope 命名范围名称 支持多个 和直接定义
     * @param array $args 参数
     * @return Model
     */
    public function scope($scope = '', $args = null)
    {
        if ('' === $scope) {
            if (isset($this->scope['default'])) {
                // 默认的命名范围
                $options = $this->scope['default'];
            } else {
                return $this;
            }
        } elseif (is_string($scope)) {
            // 支持多个命名范围调用 用逗号分割
            $scopes  = explode(',', $scope);
            $options = [];
            foreach ($scopes as $name) {
                if (!isset($this->scope[$name])) {
                    continue;
                }

                $options = array_merge($options, $this->scope[$name]);
            }
            if (!empty($args) && is_array($args)) {
                $options = array_merge($options, $args);
            }
        } elseif (is_array($scope)) {
            // 直接传入命名范围定义
            $options = $scope;
        }

        if (is_array($options) && !empty($options)) {
            $this->options = array_merge($this->options, array_change_key_case($options));
        }
        return $this;
    }

    /**
     * 指定查询条件
     * @access public
     * @param mixed $where 条件表达式
     * @return Model
     */
    public function where($where)
    {
        if (is_string($where) && '' != $where) {
            $map            = [];
            $map['_string'] = $where;
            $where          = $map;
        }
        if (isset($this->options['where'])) {
            $this->options['where'] = array_merge($this->options['where'], $where);
        } else {
            $this->options['where'] = $where;
        }
        return $this;
    }

    /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return Model
     */
    public function limit($offset, $length = null)
    {
        if (is_null($length) && strpos($offset, ',')) {
            list($offset, $length) = explode(',', $offset);
        }
        $this->options['limit'] = intval($offset) . ($length ? ',' . intval($length) : '');
        return $this;
    }

    /**
     * 指定分页
     * @access public
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     * @return Model
     */
    public function page($page, $listRows = null)
    {
        if (is_null($listRows) && strpos($page, ',')) {
            list($page, $listRows) = explode(',', $page);
        }
        $this->options['page'] = [intval($page), intval($listRows)];
        return $this;
    }

    /**
     * 指定数据表
     * @access public
     * @param string $table 表名
     * @return Model
     */
    public function table($table)
    {
        $prefix = $this->tablePrefix;
        if (is_array($table)) {
            $this->options['table'] = $table;
        } elseif (!empty($table)) {
            //将__TABLE_NAME__替换成带前缀的表名
            $table = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {return $prefix . strtolower($match[1]);}, $table);
            $this->options['table'] = $table;
        }
        return $this;
    }

    /**
     * USING支持 用于多表删除
     * @access public
     * @param mixed $using
     * @return Model
     */
    public function using($using)
    {
        $prefix = $this->tablePrefix;
        if (is_array($using)) {
            $this->options['using'] = $using;
        } elseif (!empty($using)) {
            //将__TABLE_NAME__替换成带前缀的表名
            $using = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {return $prefix . strtolower($match[1]);}, $using);
            $this->options['using'] = $using;
        }
        return $this;
    }

    /**
     * 指定排序 order('id','desc') 或者 order(['id'=>'desc','create_time'=>'desc'])
     * @access public
     * @param string|array $field 排序字段
     * @param string $order 排序
     * @return Model
     */
    public function order($field, $order = null)
    {
        if (is_string($field) && !empty($field) && is_null($order)) {
            $this->options['order'][] = $field;
        } elseif (is_string($field) && !empty($field) && is_string($order)) {
            $this->options['order'][$field] = $order;
        } elseif (is_array($field) && !empty($field)) {
            $this->options['order'] = $field;
        }
        return $this;
    }

    /**
     * 指定group查询
     * @access public
     * @param string $group GROUP
     * @return Model
     */
    public function group($group)
    {
        $this->options['group'] = $group;
        return $this;
    }

    /**
     * 指定having查询
     * @access public
     * @param string $having having
     * @return Model
     */
    public function having($having)
    {
        $this->options['having'] = $having;
        return $this;
    }

    /**
     * 指定查询lock
     * @access public
     * @param boolean $lock 是否lock
     * @return Model
     */
    public function lock($lock = false)
    {
        $this->options['lock'] = $lock;
        return $this;
    }

    /**
     * 指定distinct查询
     * @access public
     * @param string $distinct 是否唯一
     * @return Model
     */
    public function distinct($distinct)
    {
        $this->options['distinct'] = $distinct;
        return $this;
    }

    /**
     * 指定数据表别名
     * @access public
     * @param string $alias 数据表别名
     * @return Model
     */
    public function alias($alias)
    {
        $this->options['alias'] = $alias;
        return $this;
    }

    /**
     * 指定写入过滤方法
     * @access public
     * @param string $filter 指定过滤方法
     * @return Model
     */
    public function filter($filter)
    {
        $this->options['filter'] = $filter;
        return $this;
    }

    /**
     * 对数据集进行索引
     * @access public
     * @param string $index 索引名称
     * @return Model
     */
    public function index($index)
    {
        $this->options['index'] = $index;
        return $this;
    }

    /**
     * 指定强制索引
     * @access public
     * @param string $force 索引名称
     * @return Model
     */
    public function force($force)
    {
        $this->options['force'] = $force;
        return $this;
    }

    /**
     * 参数绑定
     * @access public
     * @param string $key  参数名
     * @param mixed $value  绑定的变量及绑定参数
     * @return Model
     */
    public function bind($key, $value = false)
    {
        if (is_array($key)) {
            $this->options['bind'] = $key;
        } else {
            $num = func_num_args();
            if ($num > 2) {
                $params = func_get_args();
                array_shift($params);
                $this->options['bind'][$key] = $params;
            } else {
                $this->options['bind'][$key] = $value;
            }
        }
        return $this;
    }

    /**
     * 查询注释
     * @access public
     * @param string $comment 注释
     * @return Model
     */
    public function comment($comment)
    {
        $this->options['comment'] = $comment;
        return $this;
    }

    /**
     * 获取执行的SQL语句
     * @access public
     * @param boolean $fetch 是否返回sql
     * @return Model
     */
    public function fetchSql($fetch = true)
    {
        $this->options['fetch_sql'] = $fetch;
        return $this;
    }

    /**
     * 设置字段映射
     * @access public
     * @param mixed $map 映射名称或者映射数据
     * @param string $name 映射的字段
     * @return Model
     */
    public function map($map, $name = '')
    {
        if (is_array($map)) {
            $this->map = array_merge($this->map, $map);
        } else {
            $this->map[$map] = $name;
        }
        return $this;
    }

    /**
     * 设置从主服务器读取数据
     * @access public
     * @return Model
     */
    public function master()
    {
        $this->options['master'] = true;
        return $this;
    }
}
