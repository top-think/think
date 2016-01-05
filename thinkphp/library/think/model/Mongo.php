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
namespace think\model;

use think\Lang;
use think\Loader;

T('modle/Adv');

/**
 * MongoModel模型类
 * 实现了ODM和ActiveRecords模式
 */
class Mongo extends \think\Model
{
    use \traits\model\Adv;

    // 主键类型
    const TYPE_OBJECT = 1;
    const TYPE_INT    = 2;
    const TYPE_STRING = 3;

    // 主键名称
    protected $pk = '_id';
    // _id 类型 1 Object 采用MongoId对象 2 Int 整形 支持自动增长 3 String 字符串Hash
    protected $_idType = self::TYPE_OBJECT;
    // 主键是否自动增长 支持Int型主键
    protected $_autoInc = false;
    // Mongo默认关闭字段检测 可以动态追加字段
    protected $autoCheckFields = false;

    /**
     * 利用__call方法实现一些特殊的Model方法
     * @access public
     *
     * @param string $method 方法名称
     * @param array  $args   调用参数
     *
     * @return mixed
     * @throws \think\Exception
     */
    public function __call($method, $args)
    {
        if (strtolower(substr($method, 0, 5)) == 'getby') {
            // 根据某个字段获取记录
            $field         = Loader::parseName(substr($method, 5));
            $where[$field] = $args[0];
            return $this->where($where)->find();
        } elseif (strtolower(substr($method, 0, 10)) == 'getfieldby') {
            // 根据某个字段获取记录的某个值
            $name         = Loader::parseName(substr($method, 10));
            $where[$name] = $args[0];
            return $this->where($where)->getField($args[1]);
        } else {
            throw new \think\Exception(__CLASS__ . ':' . $method . Lang::get('_METHOD_NOT_EXIST_'));
        }
    }

    // 写入数据前的回调方法 包括新增和更新
    protected function _before_write(&$data)
    {
        $pk = $this->getPk();
        // 根据主键类型处理主键数据
        if (isset($data[$pk]) && self::TYPE_OBJECT == $this->_idType) {
            $data[$pk] = new MongoId($data[$pk]);
        }
    }

    /**
     * count统计 配合where连贯操作
     * @access public
     * @return integer
     */
    public function count()
    {
        // 分析表达式
        $options = $this->_parseOptions();
        return $this->db->count($options);
    }

    /**
     * 获取下一ID 用于自动增长型
     * @access public
     * @param string $pk 字段名 默认为主键
     * @return mixed
     */
    public function getMongoNextId($pk = '')
    {
        if (empty($pk)) {
            $pk = $this->getPk();
        }
        return $this->db->getMongoNextId($pk);
    }

    // 插入数据前的回调方法
    protected function _before_insert(&$data, $options = [])
    {
        // 写入数据到数据库
        if ($this->_autoInc && self::TYPE_INT == $this->_idType) {
            // 主键自动增长
            $pk = $this->getPk();
            if (!isset($data[$pk])) {
                $data[$pk] = $this->db->mongo_next_id($pk);
            }
        }
    }

    public function clear()
    {
        return $this->db->clear();
    }

    // 查询成功后的回调方法
    protected function _after_select(&$resultSet, $options = [])
    {
        array_walk($resultSet, [$this, 'checkMongoId']);
    }

    /**
     * 获取MongoId
     * @access protected
     * @param array $result 返回数据
     * @return array
     */
    protected function checkMongoId(&$result)
    {
        if (is_object($result['_id'])) {
            $result['_id'] = $result['_id']->__toString();
        }
        return $result;
    }

    // 表达式过滤回调方法
    protected function _options_filter(&$options)
    {
        $id = $this->getPk();
        if (isset($options['where'][$id]) && is_scalar($options['where'][$id]) && self::TYPE_OBJECT == $this->_idType) {
            $options['where'][$id] = new MongoId($options['where'][$id]);
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
        if (is_numeric($options) || is_string($options)) {
            $id               = $this->getPk();
            $where[$id]       = $options;
            $options          = [];
            $options['where'] = $where;
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        $result  = $this->db->find($options);
        if (false === $result) {
            return false;
        }
        if (empty($result)) {
            // 查询结果为空
            return null;
        } else {
            $this->checkMongoId($result);
        }
        $this->data = $result;
        $this->_after_find($this->data, $options);
        return $this->data;
    }

    /**
     * 字段值增长
     * @access public
     * @param string $field  字段名
     * @param integer $step  增长值
     * @return boolean
     */
    public function setInc($field, $step = 1, $lazyTime = 0)
    {
        return $this->setField($field, ['inc', $step]);
    }

    /**
     * 字段值减少
     * @access public
     * @param string $field  字段名
     * @param integer $step  减少值
     * @return boolean
     */
    public function setDec($field, $step = 1, $lazyTime = 0)
    {
        return $this->setField($field, ['inc', '-' . $step]);
    }

    /**
     * 获取一条记录的某个字段值
     * @access public
     * @param string $field  字段名
     * @param string $spea  字段数据间隔符号
     * @return mixed
     */
    public function getField($field, $sepa = null)
    {
        $options['field'] = $field;
        $options          = $this->_parseOptions($options);
        if (strpos($field, ',')) {
            // 多字段
            if (is_numeric($sepa)) {
                // 限定数量
                $options['limit'] = $sepa;
                $sepa             = null; // 重置为null 返回数组
            }
            $resultSet = $this->db->select($options);
            if (!empty($resultSet)) {
                $_field = explode(',', $field);
                $field  = array_keys($resultSet[0]);
                $key    = array_shift($field);
                $key2   = array_shift($field);
                $cols   = [];
                $count  = count($_field);
                foreach ($resultSet as $result) {
                    $name = $result[$key];
                    if (2 == $count) {
                        $cols[$name] = $result[$key2];
                    } else {
                        $cols[$name] = is_null($sepa) ? $result : implode($sepa, $result);
                    }
                }
                return $cols;
            }
        } else {
            // 返回数据个数
            if (true !== $sepa) {
                // 当sepa指定为true的时候 返回所有数据
                $options['limit'] = is_numeric($sepa) ? $sepa : 1;
            }
            // 查找一条记录
            $result = $this->db->find($options);
            if (!empty($result)) {
                if (1 == $options['limit']) {
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
     * 执行Mongo指令
     * @access public
     * @param array $command  指令
     * @return mixed
     */
    public function command($command)
    {
        return $this->db->command($command);
    }

    /**
     * 执行MongoCode
     * @access public
     * @param string $code  MongoCode
     * @param array $args   参数
     * @return mixed
     */
    public function mongoCode($code, $args = [])
    {
        return $this->db->execute($code, $args);
    }

    // 数据库切换后回调方法
    protected function _after_db()
    {
        // 切换Collection
        $this->db->switchCollection($this->getTableName(), $this->dbName);
    }

    /**
     * 得到完整的数据表名 Mongo表名不带dbName
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
        return $this->trueTableName;
    }
}
