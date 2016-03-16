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
    private $links = [];
    // 主键名称
    protected $pk = null;
    // 数据表前缀
    protected $tablePrefix = null;
    // 模型名称
    protected $name = '';
    // 数据库名称
    protected $dbName = '';
    // 数据表字段大小写
    protected $attrCase = null;
    //数据库配置
    protected $connection = [];
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
    // 数据副本
    protected $duplicate = [];
    // 查询表达式参数
    protected $options = [];
    // 命名范围定义
    protected $scope = [];
    // 字段映射定义
    protected $map = [];
    // 字段验证规则定义
    protected $rule = [];

    /**
     * 架构函数
     * @access public
     * @param string $name 模型名称
     * @param array $config 模型配置
     */
    public function __construct($name = '', array $config = [])
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

        if (!empty($config['prefix'])) {
            $this->tablePrefix = $config['prefix'];
        } elseif (isset($config['prefix']) && '' === $config['prefix']) {
            $this->tablePrefix = '';
        } elseif (is_null($this->tablePrefix)) {
            $this->tablePrefix = Config::get('database.prefix');
        }
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

        if (is_null($this->attrCase)) {
            $this->attrCase = Config::get('db_attr_case');
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
     * @throws \think\Exception
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
            throw new Exception(__CLASS__ . ':' . $method . ' method not exist');
        }
    }

    // 回调方法 初始化模型
    protected function _initialize()
    {}

    /**
     * 对写入到数据库的数据进行处理
     * @access protected
     * @param mixed $data 要操作的数据
     * @param string $type insert 或者 update
     * @return array
     * @throws \think\Exception
     */
    protected function _write_data($data, $type)
    {
        if (empty($data)) {
            if (!empty($this->data)) {
                // 没有传递数据，获取当前数据对象的值
                $data = $this->data;
                // 重置数据
                $this->data = [];
            } else {
                throw new Exception('invalid data');
            }
        }
        if (!empty($this->duplicate) && 'update' == $type) {
            // 存在数据副本
            foreach ($data as $key => $val) {
                // 去除相同数据
                if (isset($this->duplicate[$key]) && $val == $this->duplicate[$key]) {
                    unset($data[$key]);
                }
            }
            if (empty($data)) {
                // 没有数据变化
                return [];
            } else {
                // 更新操作保留主键信息
                $pk = $this->getPk();
                if (is_array($pk)) {
                    foreach ($pk as $key) {
                        if (isset($this->duplicate[$key])) {
                            $data[$key] = $this->duplicate[$key];
                        }
                    }
                } elseif (isset($this->duplicate[$pk])) {
                    $data[$pk] = $this->duplicate[$pk];
                }
            }
            // 重置副本
            $this->duplicate = [];
        }
        // 检查字段映射
        if (!empty($this->map)) {
            foreach ($this->map as $key => $val) {
                if (isset($data[$key])) {
                    $data[$val] = $data[$key];
                    unset($data[$key]);
                }
            }
        }

        // 数据自动验证
        if (!$this->dataValidate($data)) {
            return false;
        }

        // 数据自动填充
        $this->dataFill($data);

        $fields = $this->getFields();
        // 检查非数据字段
        if (!empty($fields)) {
            foreach ($data as $key => $val) {
                if (!in_array($key, $fields, true)) {
                    if (Config::get('db_fields_strict')) {
                        throw new Exception(' fields not exists :[ ' . $key . ' ]');
                    }
                    unset($data[$key]);
                } elseif (is_scalar($val) && !isset($this->options['bind'][$key])) {
                    // 字段类型检查
                    $this->_parseType($data, $key, $this->options['bind']);
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
        if (empty($data)) {
            // 没有数据则不执行
            throw new Exception('no data to write');
        }
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
     * @throws \think\Exception
     */
    public function add($data = '', $replace = false)
    {
        // 数据处理
        $data = $this->_write_data($data, 'insert');
        if (false === $data) {
            return false;
        }
        // 分析表达式
        $options = $this->_parseOptions();
        if (false === $this->_before_insert($data, $options)) {
            return false;
        }
        // 写入数据到数据库
        $result = $this->db->insert($data, $options, $replace);
        if (false !== $result && is_numeric($result)) {
            $pk = $this->getPk($options['table']);
            // 增加复合主键支持
            if (is_array($pk)) {
                return $result;
            }
            $insertId = $this->getLastInsID();
            if ($insertId) {
                // 自增主键返回插入ID
                $data[$pk] = $insertId;
            }
            if (false === $this->_after_insert($data, $options)) {
                return false;
            }
        }
        return !empty($insertId) ? $insertId : $result;
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
            throw new Exception('no data to write');
        }
        // 数据处理
        foreach ($dataList as &$data) {
            $data = $this->_write_data($data, 'insert');
            if (false === $data) {
                return false;
            }
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 写入数据到数据库
        $result = $this->db->insertAll($dataList, $options, $replace);
        if (false !== $result) {
            $insertId = $this->getLastInsID();
        }
        return !empty($insertId) ? $insertId : $result;
    }

    /**
     * 保存数据
     * @access public
     * @param mixed $data 数据
     * @return boolean
     * @throws \think\Exception
     */
    public function save($data = '')
    {
        // 数据处理
        $data = $this->_write_data($data, 'update');
        if (false == $data) {
            return false;
        }
        // 分析表达式
        $options = $this->_parseOptions();
        $pk      = $this->getPk($options['table']);
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
                        throw new Exception('miss complex primary data');
                    }
                    unset($data[$field]);
                }
            }
            if (!isset($where)) {
                // 如果没有任何更新条件则不执行
                throw new Exception('no data to update without where');
            } else {
                $options['where'] = $where;
            }
        }
        if (is_string($pk) && isset($options['where'][$pk])) {
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
     * @throws \think\Exception
     */
    public function delete($options = [])
    {
        $pk = $this->getPk();
        if (empty($options) && empty($this->options['where'])) {
            // 如果删除条件为空 则删除当前数据对象所对应的记录
            if (!empty($this->data) && is_string($pk) && isset($this->data[$pk])) {
                return $this->delete($this->data[$pk]);
            } else {
                throw new Exception('no data to delete without where');
            }
        }
        if (!empty($options) && empty($options['where'])) {
            // AR模式分析主键条件
            $this->parsePkWhere($options);
        }

        // 分析表达式
        $options = $this->_parseOptions($options);
        if (empty($options['where'])) {
            // 如果条件为空 不进行删除操作 除非设置 1=1
            throw new Exception('no data to delete without where');
        }
        if (is_string($pk) && isset($options['where'][$pk])) {
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
     * @throws \think\Exception
     */
    public function select($options = [])
    {
        if (false === $options) {
            // 用于子查询 不查询只返回SQL
            $options['fetch_sql'] = true;
        } elseif (!empty($options) && empty($options['where'])) {
            // AR模式主键条件分析
            $this->parsePkWhere($options);
        }

        // 分析表达式
        $options = $this->_parseOptions($options);
        // 判断查询缓存
        if (isset($options['cache'])) {
            $cache = $options['cache'];
            if (!isset($cache['key']) || !is_string($cache['key'])) {
                $cache['key'] = md5(serialize($options));
            }
            $cache['expire'] = isset($cache['expire']) ? $cache['expire'] : null;
            $data            = Cache::get($cache['key']);
            if (false !== $data) {
                return $data;
            }
        }
        $resultSet = $this->db->select($options);

        if (!empty($resultSet)) {
            // 有查询结果
            if (is_string($resultSet)) {
                return $resultSet;
            }

            // 数据列表读取后的处理
            $resultSet = $this->_read_data_list($resultSet, $options);
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
            Cache::set($cache['key'], $resultSet, $cache['expire']);
        }
        return $resultSet;
    }

    /**
     * 把主键值转换为查询条件 支持复合主键
     * @access public
     * @param mixed $options 表达式参数
     * @return void
     * @throws \think\Exception
     */
    protected function parsePkWhere(&$options)
    {
        $pk = $this->getPk();
        if (is_string($pk)) {
            // 根据主键查询
            if (is_array($options)) {
                // 判断是否索引数组
                if (0 === key($options)) {
                    $where[$pk] = ['in', $options];
                } else {
                    return;
                }
            } else {
                $where[$pk] = strpos($options, ',') ? ['IN', $options] : $options;
            }
            $options          = [];
            $options['where'] = $where;
        } elseif (is_array($pk) && is_array($options) && !empty($options)) {
            // 根据复合主键查询
            $array = array_intersect_key($options, $pk);
            if (count($pk) == count($array)) {
                $options          = array_diff_key($options, $array);
                $options['where'] = array_combine($pk, $array);
            } else {
                throw new Exception('miss complex primary data');
            }
        }
        return;
    }

    /**
     * 数据列表读取后的处理
     * @access protected
     * @param array $resultSet 当前数据
     * @return array
     */
    protected function _read_data_list($resultSet, $options)
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
     * @param string $field 字段名
     * @param null   $sepa  字段数据间隔符号 NULL返回数组
     * @return array|mixed|null
     */
    public function getField($field, $sepa = null)
    {
        $options['field'] = $field;
        $options          = $this->_parseOptions($options);
        $field            = trim($field);
        // 判断查询缓存
        if (isset($options['cache'])) {
            $cache        = $options['cache'];
            $cache['key'] = is_string($cache['key']) ? $cache['key'] : md5($sepa . serialize($options));
            $data         = Cache::get($cache['key']);
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
                $field = array_keys($resultSet[0]);
                $cols  = [];
                $count = count($field);
                foreach ($resultSet as $result) {
                    $name = $result[$field[0]];
                    if (2 == $count) {
                        $cols[$name] = $result[$field[1]];
                    } else {
                        $cols[$name] = is_string($sepa) ? implode($sepa, array_slice($result, 1)) : $result;
                    }
                }
                if (isset($cache)) {
                    Cache::set($cache['key'], $cols, $cache['expire']);
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
                        Cache::set($cache['key'], $data, $cache['expire']);
                    }
                    return $data;
                }
                $array = [];
                foreach ($result as $val) {
                    $array[] = $val[$field];
                }
                if (isset($cache)) {
                    Cache::set($cache['key'], $array, $cache['expire']);
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
        // 更新某个字段的时候 忽略数据副本
        $this->duplicate = [];
        return $this->save($data);
    }

    /**
     * 字段值(延迟)增长
     * @access public
     * @param string $field  字段名
     * @param integer $step  增长值
     * @param integer $lazyTime  延时时间(s)
     * @return boolean
     * @throws \think\Exception
     */
    public function setInc($field, $step = 1, $lazyTime = 0)
    {
        $condition = !empty($this->options['where']) ? $this->options['where'] : [];
        if (empty($condition)) {
            // 没有条件不做任何更新
            throw new Exception('no data to update');
        }
        if ($lazyTime > 0) {
            // 延迟写入
            $guid = md5($this->name . '_' . $field . '_' . serialize($condition));
            $step = $this->lazyWrite($guid, $step, $lazyTime);
            if (empty($step)) {
                return true; // 等待下次写入
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
     * @throws \think\Exception
     */
    public function setDec($field, $step = 1, $lazyTime = 0)
    {
        $condition = !empty($this->options['where']) ? $this->options['where'] : [];
        if (empty($condition)) {
            // 没有条件不做任何更新
            throw new Exception('no data to update');
        }
        if ($lazyTime > 0) {
            // 延迟写入
            $guid = md5($this->name . '_' . $field . '_' . serialize($condition));
            $step = $this->lazyWrite($guid, -$step, $lazyTime);
            if (empty($step)) {
                return true; // 等待下次写入
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
     * @return string
     */
    public function buildSql()
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
            $fields = $this->getTableInfo('fields', $options['table']);
        } else {
            $options['table'] = $this->getTableName();
            $fields           = $this->getFields();
        }
        if (!empty($options['alias'])) {
            $options['table'] .= ' ' . $options['alias'];
        }
        // 字段类型验证
        if (isset($options['where']) && is_array($options['where']) && !empty($fields)) {
            // 对数组查询条件进行字段类型检查
            foreach ($options['where'] as $key => $val) {
                $key = trim($key);
                if (in_array($key, $fields, true) && is_scalar($val) && empty($options['bind'][$key])) {
                    $this->_parseType($options['where'], $key, $options['bind'], $options['table']);
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
     * 数据类型检测和自动转换
     * @access protected
     * @param array $data 数据
     * @param string $key 字段名
     * @param array $bind 参数绑定列表
     * @param string $tableName 表名
     * @return void
     */
    protected function _parseType(&$data, $key, &$bind, $tableName = '')
    {
        $binds = $this->getTableInfo('bind', $tableName);
        $type  = $this->getTableInfo('type', $tableName);
        // 强制类型转换
        if (false !== strpos($type[$key], 'int')) {
            $data[$key] = (int) $data[$key];
        } elseif (false !== strpos($type[$key], 'float') || false !== strpos($type[$key], 'double')) {
            $data[$key] = (float) $data[$key];
        } elseif (false !== strpos($type[$key], 'bool')) {
            $data[$key] = (bool) $data[$key];
        }
        if (':' == substr($data[$key], 0, 1) && isset($bind[substr($data[$key], 1)])) {
            // 已经绑定 无需再次绑定 请确保bind方法优先执行
            return;
        }
        $bind[$key] = [$data[$key], isset($binds[$key]) ? $binds[$key] : \PDO::PARAM_STR];
        $data[$key] = ':' . $key;
    }

    /**
     * 查询数据
     * @access public
     * @param mixed $options 表达式参数
     * @return mixed
     * @throws \think\Exception
     */
    public function find($options = [])
    {
        if (!empty($options) && empty($options['where'])) {
            // AR模式主键条件分析
            $this->parsePkWhere($options);
        }
        // 总是查找一条记录
        $options['limit'] = 1;
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 判断查询缓存
        if (isset($options['cache'])) {
            $cache = $options['cache'];
            if (!isset($cache['key']) || !is_string($cache['key'])) {
                $cache['key'] = md5(serialize($options));
            }
            $cache['expire'] = isset($cache['expire']) ? $cache['expire'] : null;
            $data            = Cache::get($cache['key']);
            if (false !== $data) {
                $this->data = $data;
                return $data;
            }
        }
        $resultSet = $this->db->select($options);

        if (empty($resultSet)) {
            // 查询结果为空
            return null;
        }
        if (is_string($resultSet)) {
            return $resultSet;
        }
        // 数据处理
        $data = $this->_read_data($resultSet[0], $options);
        // 回调
        $this->_after_find($data, $options);
        if (isset($cache)) {
            Cache::set($cache['key'], $data, $cache['expire']);
        }
        // 数据对象赋值
        $this->data = $data;
        // 数据副本
        $this->duplicate = $data;
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
     * @return mixed
     * @throws \think\Exception
     */
    public function create($data = '')
    {
        // 如果没有传值默认取POST数据
        if (empty($data)) {
            $data = \think\Input::post();
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        }
        // 验证数据
        if (empty($data) || !is_array($data)) {
            throw new Exception('invalid data type');
        }

        // 检测提交字段的合法性
        if (isset($this->options['field'])) {
            // $this->field('field1,field2...')->create()
            $fields = $this->options['field'];
            unset($this->options['field']);
            if (is_string($fields)) {
                $fields = explode(',', $fields);
            }
            foreach ($data as $key => $val) {
                if (!in_array($key, $fields)) {
                    unset($data[$key]);
                }
            }
        }

        // 数据自动验证
        if (!$this->dataValidate($data)) {
            return false;
        }

        // 数据自动填充
        $this->dataFill($data);

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
     * 数据自动验证
     * @access protected
     * @param array $data  数据
     * @return void
     */
    protected function dataValidate(&$data)
    {
        if (!empty($this->options['validate'])) {
            if (!empty($this->rule)) {
                Validate::rule($this->rule);
            }
            if (!Validate::check($data, $this->options['validate'])) {
                $this->error = Validate::getError();
                return false;
            }
            $this->options['validate'] = null;
        }
        return true;
    }

    /**
     * 数据自动填充
     * @access protected
     * @param array $data  数据
     * @return void
     */
    protected function dataFill(&$data)
    {
        if (!empty($this->options['auto'])) {
            // 获取自动完成规则
            list($rules, $options, $scene) = $this->getDataRule($this->options['auto']);

            foreach ($rules as $key => $val) {
                if (is_numeric($key) && is_array($val)) {
                    $key = array_shift($val);
                }
                if (!empty($scene) && !in_array($key, $scene)) {
                    continue;
                }
                // 数据自动填充
                $this->fillItem($key, $val, $data, $options);
            }
            $this->options['auto'] = null;
        }
    }

    /**
     * 获取数据自动完成的规则定义
     * @access protected
     * @param mixed $rules  数据规则
     * @return array
     */
    protected function getDataRule($rules)
    {
        if (is_string($rules)) {
            // 读取配置定义
            $config = Config::get('auto');
            if (strpos($rules, '.')) {
                list($name, $group) = explode('.', $rules);
            } else {
                $name = $rules;
            }
            $rules = isset($config[$name]) ? $config[$name] : [];
            if (isset($config['__all__'])) {
                $rules = array_merge($config['__all__'], $rules);
            }
        }
        if (isset($rules['__option__'])) {
            // 参数设置
            $options = $rules['__option__'];
            unset($rules['__option__']);
        } else {
            $options = [];
        }
        if (isset($group) && isset($options['scene'][$group])) {
            // 如果设置了适用场景
            $scene = $options['scene'][$group];
            if (is_string($scene)) {
                $scene = explode(',', $scene);
            }
        } else {
            $scene = [];
        }
        return [$rules, $options, $scene];
    }

    /**
     * 数据自动填充
     * @access protected
     * @param string $key  字段名
     * @param mixed $val  填充规则
     * @param array $data  数据
     * @param array $options  参数
     * @return void
     */
    protected function fillItem($key, $val, &$data, $options = [])
    {
        // 获取数据 支持二维数组
        if (strpos($key, '.')) {
            list($name1, $name2) = explode('.', $key);
            $value               = isset($data[$name1][$name2]) ? $data[$name1][$name2] : null;
        } else {
            $value = isset($data[$key]) ? $data[$key] : null;
        }
        if ((isset($options['value_fill']) && in_array($key, is_string($options['value_fill']) ? explode(',', $options['value_fill']) : $options['value_fill']) && '' == $value)
            || (isset($options['exists_fill']) && in_array($key, is_string($options['exists_fill']) ? explode(',', $options['exists_fill']) : $options['exists_fill']) && is_null($value))) {
            // 不满足自动填充条件
            return;
        }
        if ($val instanceof \Closure) {
            $result = call_user_func_array($val, [$value, &$data]);
        } elseif (isset($val[0]) && $val[0] instanceof \Closure) {
            $result = call_user_func_array($val[0], [$value, &$data]);
        } elseif (!is_array($val)) {
            $result = $val;
        } else {
            $rule   = isset($val[0]) ? $val[0] : $val;
            $type   = isset($val[1]) ? $val[1] : 'value';
            $params = isset($val[2]) ? (array) $val[2] : [];
            switch ($type) {
                case 'behavior':
                    Hook::exec($rule, '', $data);
                    return;
                case 'callback':
                    array_unshift($params, $value);
                    $result = call_user_func_array($rule, $params);
                    break;
                case 'serialize':
                    if (is_string($rule)) {
                        $rule = explode(',', $rule);
                    }
                    $serialize = [];
                    foreach ($rule as $name) {
                        if (strpos($name, '.')) {
                            list($name1, $name2) = explode('.', $name);
                            if (isset($data[$name1][$name2])) {
                                $serialize[$name] = $data[$name1][$name2];
                                unset($data[$name1][$name2]);
                            }
                        } elseif (isset($data[$name])) {
                            $serialize[$name] = $data[$name];
                            unset($data[$name]);
                        }
                    }
                    $fun    = !empty($params['type']) ? $params['type'] : 'serialize';
                    $result = $fun($serialize);
                    break;
                case 'ignore':
                    if ($rule === $value) {
                        if (strpos($key, '.')) {
                            unset($data[$name1][$name2]);
                        } else {
                            unset($data[$key]);
                        }
                    }
                    return;
                case 'value':
                default:
                    $result = $rule;
                    break;
            }
        }
        if (strpos($key, '.')) {
            $data[$name1][$name2] = $result;
        } else {
            $data[$key] = $result;
        }
    }

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

        if (!isset($this->links[$linkId])) {
            // 创建一个新的实例
            if (is_string($linkId) && '' == $config) {
                $config = Config::get($linkId);
            }
            $this->links[$linkId] = Db::connect($config);
        } elseif (null === $config) {
            $this->links[$linkId]->close(); // 关闭数据库连接
            unset($this->links[$linkId]);
            return;
        }

        // 切换数据库连接
        $this->db = $this->links[$linkId];
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
            // 解决非windows环境下获取不到basename的bug(xiaobo.sun modify 20160215)
            $this->name = basename(str_replace('\\', '/', get_class($this)));
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
     * 获取当前主键名称
     * @access public
     * @param string $tableName  数据表名 留空自动获取
     * @return mixed
     */
    public function getPk($tableName = '')
    {
        if (is_null($this->pk)) {
            $this->pk = $this->getTableInfo('pk', $tableName);
        }
        return $this->pk;
    }

    /**
     * 获取当前字段信息
     * @access public
     * @param string $tableName  数据表名 留空自动获取
     * @return array
     */
    public function getFields($tableName = '')
    {
        if (empty($this->fields)) {
            $this->fields = $this->getTableInfo('fields', $tableName);
        }
        return $this->fields;
    }

    /**
     * 获取数据表信息
     * @access public
     * @param string $fetch 获取信息类型 包括 fields type bind pk
     * @param string $tableName  数据表名 留空自动获取
     * @return mixed
     */
    public function getTableInfo($fetch = '', $tableName = '')
    {
        if (!$tableName) {
            $tableName = isset($this->options['table']) ? $this->options['table'] : $this->getTableName();
        }
        if (is_array($tableName)) {
            $tableName = key($tableName) ?: current($tableName);
        }
        if (strpos($tableName, ',')) {
            // 多表不获取字段信息
            return false;
        }
        $guid   = md5($tableName);
        $result = Cache::get($guid);
        if (!$result) {
            $info = $this->db->getFields($tableName);
            // 字段大小写转换
            switch ($this->attrCase) {
                case \PDO::CASE_LOWER:
                    $info = array_change_key_case($info);
                    break;
                case \PDO::CASE_UPPER:
                    $info = array_change_key_case($info, CASE_UPPER);
                    break;
                case \PDO::CASE_NATURAL:
                default:
                    // 不做转换
            }

            $fields = array_keys($info);
            $bind   = $type   = [];
            foreach ($info as $key => $val) {
                // 记录字段类型
                $type[$key] = $val['type'];
                if (preg_match('/(int|double|float|decimal|real|numeric|serial)/is', $val['type'])) {
                    $bind[$key] = \PDO::PARAM_INT;
                } elseif (preg_match('/bool/is', $val['type'])) {
                    $bind[$key] = \PDO::PARAM_BOOL;
                } else {
                    $bind[$key] = \PDO::PARAM_STR;
                }
                if (!empty($val['primary'])) {
                    $pk[] = $key;
                }
            }
            if (isset($pk)) {
                // 设置主键
                $pk = count($pk) > 1 ? $pk : $pk[0];
            } else {
                $pk = null;
            }
            // 记录字段类型信息
            $result = ['fields' => $fields, 'bind' => $bind, 'type' => $type, 'pk' => $pk];
            !APP_DEBUG && Cache::set($guid, $result, 0);
        }
        return $fetch ? $result[$fetch] : $result;
    }

    /**
     * SQL查询
     * @access public
     * @param string $sql  SQL指令
     * @param array $bind 参数绑定
     * @return mixed
     */
    public function query($sql, $bind = [])
    {
        $sql = $this->parseSql($sql);
        return $this->db->query($sql, $bind);
    }

    /**
     * 执行SQL语句
     * @access public
     * @param string $sql  SQL指令
     * @param array $bind 参数绑定
     * @return false | integer
     */
    public function execute($sql, $bind = [])
    {
        $sql = $this->parseSql($sql);
        return $this->db->execute($sql, $bind);
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
        $sql = strtr($sql, ['__TABLE__' => $this->getTableName(), '__PREFIX__' => $this->tablePrefix]);
        $sql = $this->parseSqlTable($sql);
        $this->db->setModel($this->name);
        return $sql;
    }

    /**
     * 设置数据对象值
     * @access public
     * @param mixed $data 数据
     * @return Model
     * @throws \think\Exception
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
        if (is_array($join)) {
            foreach ($join as $key => &$_join) {
                $_join = $this->parseSqlTable($_join);
                $_join = false !== stripos($_join, 'JOIN') ? $_join : $type . ' JOIN ' . $_join;
            }
            $this->options['join'] = $join;
        } elseif (!empty($join)) {
            $join                    = $this->parseSqlTable($join);
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
            if (is_array($join) && is_array(current($join))) {
                // 如果为组数，则循环调用join
                foreach ($join as $key => $value) {
                    if (is_array($value) && 2 <= count($value)) {
                        $this->join($value[0], $value[1], isset($value[2]) ? $value[2] : $type);
                    }
                }
            } else {
                $this->_join($join, $condition); // 兼容原来的join写法
            }
        } elseif (in_array(strtoupper($condition), ['INNER', 'LEFT', 'RIGHT', 'ALL'])) {
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
                    $table = $this->parseSqlTable($join);
                } elseif (false === strpos($join, '(') && !empty($prefix) && 0 !== strpos($join, $prefix)) {
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
     * @throws \think\Exception
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
        } elseif (is_string($union)) {
            // 转换union表达式
            $union = (array) $union;
        }
        if (is_array($union)) {
            if (isset($union[0])) {
                foreach ($union as &$val) {
                    $val = $this->parseSqlTable($val);
                }
                $this->options['union'] = isset($this->options['union']) ? array_merge($this->options['union'], $union) : $union;
            } else {
                $this->options['union'][] = $union;
            }
        } else {
            throw new Exception('data type invalid', 10300);
        }
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
            $fields = $this->getFields();
            $field  = $fields ?: '*';
        } elseif ($except) {
            // 字段排除
            if (is_string($field)) {
                $field = explode(',', $field);
            }
            $fields = $this->getFields();
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
        } else {
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
        if (is_array($table)) {
            $this->options['table'] = $table;
        } elseif (!empty($table)) {
            $this->options['table'] = $this->parseSqlTable($table);
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
        if (is_array($using)) {
            $this->options['using'] = $using;
        } elseif (!empty($using)) {
            $this->options['using'] = $this->parseSqlTable($using);
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
        if (!empty($field)) {
            if (!is_array($field)) {
                $field = empty($order) ? [$field] : [(string) $field => (string) $order];
            }
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
     * @param mixed $key  参数名
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
     * 设置字段验证
     * @access public
     * @param mixed $field 字段名或者验证规则 true表示自动读取
     * @param array|null $rule 验证规则
     * @return Model
     */
    public function validate($field = true, $rule = null)
    {
        if (is_array($field) || is_null($rule)) {
            $this->options['validate'] = true === $field ? $this->name : $field;
        } else {
            $this->options['validate'][$field] = $rule;
        }
        return $this;
    }

    /**
     * 设置字段完成
     * @access public
     * @param mixed $field 字段名或者自动完成规则 true 表示自动读取
     * @param array|null $rule 完成规则
     * @return Model
     */
    public function auto($field = true, $rule = null)
    {
        if (is_array($field) || is_null($rule)) {
            $this->options['auto'] = true === $field ? $this->name : $field;
        } else {
            $this->options['auto'][$field] = $rule;
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

    /**
     * 将SQL语句中的__TABLE_NAME__字符串替换成带前缀的表名（小写）
     * @access protected
     * @param string $sql sql语句
     * @return string
     */
    protected function parseSqlTable($sql)
    {
        if (false !== strpos($sql, '__')) {
            $prefix = $this->tablePrefix;
            $sql    = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix . strtolower($match[1]);
            }, $sql);
        }
        return $sql;
    }

    /**
     * 获取属性值
     * @access protected
     * @param string $property 属性名
     * @return mixed
     */
    public function getProperty($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }
}
