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

define('HAS_ONE', 1);
define('BELONGS_TO', 2);
define('HAS_MANY', 3);
define('MANY_TO_MANY', 4);

trait Relation
{
    // 关联定义
    //protected $link = [];

    /**
     * 得到关联的数据表名
     * @access public
     * @return string
     */
    public function getRelationTableName($relation)
    {
        $relationTable = !empty($this->tablePrefix) ? $this->tablePrefix : '';
        $relationTable .= $this->tableName ? $this->tableName : $this->name;
        $relationTable .= '_' . $relation->getModelName();
        return strtolower($relationTable);
    }

    // 查询成功后的回调方法
    protected function _after_find(&$result, $options = [])
    {
        // 获取关联数据 并附加到结果中
        if (!empty($options['link'])) {
            $this->getRelation($result, $options['link']);
        }

    }

    // 查询数据集成功后的回调方法
    protected function _after_select(&$result, $options = [])
    {
        // 获取关联数据 并附加到结果中
        if (!empty($options['link'])) {
            $this->getRelations($result, $options['link']);
        }

    }

    // 写入成功后的回调方法
    protected function _after_insert($data, $options = [])
    {
        // 关联写入
        if (!empty($options['link'])) {
            $this->opRelation('ADD', $data, $options['link']);
        }

    }

    // 更新成功后的回调方法
    protected function _after_update($data, $options = [])
    {
        // 关联更新
        if (!empty($options['link'])) {
            $this->opRelation('SAVE', $data, $options['link']);
        }

    }

    // 删除成功后的回调方法
    protected function _after_delete($data, $options = [])
    {
        // 关联删除
        if (!empty($options['link'])) {
            $this->opRelation('DEL', $data, $options['link']);
        }

    }

    // 表单验证成功后的回调方法
    protected function _after_create(&$data, $options = [])
    {
        // 获取关联数据 并附加到结果中
        if (!empty($options['link'])) {
            $this->crRelation($data, $options['link']);
        }
    }

    /**
     * 对保存到数据库的数据进行处理
     * @access protected
     * @param mixed $data 要操作的数据
     * @return boolean
     */
    protected function _write_data($data)
    {
        $this->_before_write($data);
        return $data;
    }

    /**
     * 获取返回数据集的关联记录
     * @access protected
     * @param array $resultSet  返回数据
     * @param string|array $name  关联名称
     * @return array
     */
    protected function getRelations(&$resultSet, $name = '')
    {
        // 获取记录集的主键列表
        foreach ($resultSet as $key => $val) {
            $val             = $this->getRelation($val, $name);
            $resultSet[$key] = $val;
        }
        return $resultSet;
    }

    /**
     * 获取返回数据的关联记录
     * @access protected
     * @param mixed $result  返回数据
     * @param string|array $name  关联名称
     * @param boolean $return 是否返回关联数据本身
     * @return array
     */
    protected function getRelation(&$result, $name = '', $return = false)
    {
        if (!empty($this->link)) {
            foreach ($this->link as $key => $val) {
                $mappingName = !empty($val['mapping_name']) ? $val['mapping_name'] : $key; // 映射名称
                if (empty($name) || true === $name || $mappingName == $name || (is_array($name) && in_array($mappingName, $name))) {
                    $mappingType      = !empty($val['mapping_type']) ? $val['mapping_type'] : $val; //  关联类型
                    $mappingClass     = !empty($val['class_name']) ? $val['class_name'] : $key; //  关联类名
                    $mappingFields    = !empty($val['mapping_fields']) ? $val['mapping_fields'] : '*'; // 映射字段
                    $mappingCondition = !empty($val['condition']) ? $val['condition'] : '1=1'; // 关联条件
                    $mappingKey       = !empty($val['mapping_key']) ? $val['mapping_key'] : $this->getPk(); // 关联键名
                    if (strtoupper($mappingClass) == strtoupper($this->name)) {
                        // 自引用关联 获取父键名
                        $mappingFk = !empty($val['parent_key']) ? $val['parent_key'] : 'parent_id';
                    } else {
                        $mappingFk = !empty($val['foreign_key']) ? $val['foreign_key'] : strtolower($this->name) . '_id'; //  关联外键
                    }
                    // 获取关联模型对象
                    $model = D($mappingClass);
                    switch ($mappingType) {
                        case HAS_ONE:
                            $pk = $result[$mappingKey];
                            $mappingCondition .= " AND {$mappingFk}='{$pk}'";
                            $relationData = $model->where($mappingCondition)->field($mappingFields)->find();
                            break;
                        case BELONGS_TO:
                            if (strtoupper($mappingClass) == strtoupper($this->name)) {
                                // 自引用关联 获取父键名
                                $mappingFk = !empty($val['parent_key']) ? $val['parent_key'] : 'parent_id';
                            } else {
                                $mappingFk = !empty($val['foreign_key']) ? $val['foreign_key'] : strtolower($model->getModelName()) . '_id'; //  关联外键
                            }
                            $fk = $result[$mappingFk];
                            $mappingCondition .= " AND {$model->getPk()}='{$fk}'";
                            $relationData = $model->where($mappingCondition)->field($mappingFields)->find();
                            break;
                        case HAS_MANY:
                            $pk = $result[$mappingKey];
                            $mappingCondition .= " AND {$mappingFk}='{$pk}'";
                            $mappingOrder = !empty($val['mapping_order']) ? $val['mapping_order'] : '';
                            $mappingLimit = !empty($val['mapping_limit']) ? $val['mapping_limit'] : '';
                            // 延时获取关联记录
                            $relationData = $model->where($mappingCondition)->field($mappingFields)->order($mappingOrder)->limit($mappingLimit)->select();
                            break;
                        case MANY_TO_MANY:
                            $pk                   = $result[$mappingKey];
                            $mappingCondition     = " {$mappingFk}='{$pk}'";
                            $mappingOrder         = $val['mapping_order'];
                            $mappingLimit         = $val['mapping_limit'];
                            $mappingRelationFk    = $val['relation_foreign_key'] ? $val['relation_foreign_key'] : $model->getModelName() . '_id';
                            $mappingRelationTable = $val['relation_table'] ? $val['relation_table'] : $this->getRelationTableName($model);
                            $sql                  = "SELECT b.{$mappingFields} FROM {$mappingRelationTable} AS a, " . $model->getTableName() . " AS b WHERE a.{$mappingRelationFk} = b.{$model->getPk()} AND a.{$mappingCondition}";
                            if (!empty($val['condition'])) {
                                $sql .= ' AND ' . $val['condition'];
                            }
                            if (!empty($mappingOrder)) {
                                $sql .= ' ORDER BY ' . $mappingOrder;
                            }
                            if (!empty($mappingLimit)) {
                                $sql .= ' LIMIT ' . $mappingLimit;
                            }
                            $relationData = $this->query($sql);
                            break;
                    }
                    if (!$return) {
                        if (isset($val['as_fields']) && in_array($mappingType, [HAS_ONE, BELONGS_TO])) {
                            // 支持直接把关联的字段值映射成数据对象中的某个字段
                            // 仅仅支持HAS_ONE BELONGS_TO
                            $fields = explode(',', $val['as_fields']);
                            foreach ($fields as $field) {
                                if (strpos($field, ':')) {
                                    list($relationName, $nick) = explode(':', $field);
                                    $result[$nick]             = $relationData[$relationName];
                                } else {
                                    $result[$field] = $relationData[$field];
                                }
                            }
                        } else {
                            $result[$mappingName] = $relationData;
                        }
                        unset($relationData);
                    } else {
                        return $relationData;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 操作关联数据
     * @access protected
     * @param string $opType  操作方式 ADD SAVE DEL
     * @param mixed $data  数据对象
     * @param string $name 关联名称
     * @return mixed
     */
    protected function opRelation($opType, $data = '', $name = '')
    {
        $result = false;
        if (empty($data) && !empty($this->data)) {
            $data = $this->data;
        } elseif (!is_array($data)) {
            // 数据无效返回
            return false;
        }
        if (!empty($this->link)) {
            // 遍历关联定义
            foreach ($this->link as $key => $val) {
                // 操作制定关联类型
                $mappingName = $val['mapping_name'] ? $val['mapping_name'] : $key; // 映射名称
                if (empty($name) || true === $name || $mappingName == $name || (is_array($name) && in_array($mappingName, $name))) {
                    // 操作制定的关联
                    $mappingType  = !empty($val['mapping_type']) ? $val['mapping_type'] : $val; //  关联类型
                    $mappingClass = !empty($val['class_name']) ? $val['class_name'] : $key; //  关联类名
                    $mappingKey   = !empty($val['mapping_key']) ? $val['mapping_key'] : $this->getPk(); // 关联键名
                    // 当前数据对象主键值
                    $pk = $data[$mappingKey];
                    if (strtoupper($mappingClass) == strtoupper($this->name)) {
                        // 自引用关联 获取父键名
                        $mappingFk = !empty($val['parent_key']) ? $val['parent_key'] : 'parent_id';
                    } else {
                        $mappingFk = !empty($val['foreign_key']) ? $val['foreign_key'] : strtolower($this->name) . '_id'; //  关联外键
                    }
                    if (!empty($val['condition'])) {
                        $mappingCondition = $val['condition'];
                    } else {
                        $mappingCondition             = [];
                        $mappingCondition[$mappingFk] = $pk;
                    }
                    // 获取关联model对象
                    $model       = D($mappingClass);
                    $mappingData = isset($data[$mappingName]) ? $data[$mappingName] : false;
                    if (!empty($mappingData) || 'DEL' == $opType) {
                        switch ($mappingType) {
                            case HAS_ONE:
                                switch (strtoupper($opType)) {
                                    case 'ADD': // 增加关联数据
                                        $mappingData[$mappingFk] = $pk;
                                        $result                  = $model->add($mappingData);
                                        break;
                                    case 'SAVE': // 更新关联数据
                                        $result = $model->where($mappingCondition)->save($mappingData);
                                        break;
                                    case 'DEL': // 根据外键删除关联数据
                                        $result = $model->where($mappingCondition)->delete();
                                        break;
                                }
                                break;
                            case BELONGS_TO:
                                break;
                            case HAS_MANY:
                                switch (strtoupper($opType)) {
                                    case 'ADD': // 增加关联数据
                                        $model->startTrans();
                                        foreach ($mappingData as $val) {
                                            $val[$mappingFk] = $pk;
                                            $result          = $model->add($val);
                                        }
                                        $model->commit();
                                        break;
                                    case 'SAVE': // 更新关联数据
                                        $model->startTrans();
                                        $pk = $model->getPk();
                                        foreach ($mappingData as $vo) {
                                            if (isset($vo[$pk])) {
                                                // 更新数据
                                                $mappingCondition = "$pk ={$vo[$pk]}";
                                                $result           = $model->where($mappingCondition)->save($vo);
                                            } else {
                                                // 新增数据
                                                $vo[$mappingFk] = $data[$mappingKey];
                                                $result         = $model->add($vo);
                                            }
                                        }
                                        $model->commit();
                                        break;
                                    case 'DEL': // 删除关联数据
                                        $result = $model->where($mappingCondition)->delete();
                                        break;
                                }
                                break;
                            case MANY_TO_MANY:
                                $mappingRelationFk    = $val['relation_foreign_key'] ? $val['relation_foreign_key'] : $model->getModelName() . '_id'; // 关联
                                $mappingRelationTable = $val['relation_table'] ? $val['relation_table'] : $this->getRelationTableName($model);
                                if (is_array($mappingData)) {
                                    $ids = [];
                                    foreach ($mappingData as $vo) {
                                        $ids[] = $vo[$mappingKey];
                                    }

                                    $relationId = implode(',', $ids);
                                }
                                switch (strtoupper($opType)) {
                                    case 'ADD': // 增加关联数据
                                    case 'SAVE': // 更新关联数据
                                        if (isset($relationId)) {
                                            $this->startTrans();
                                            // 删除关联表数据
                                            $this->table($mappingRelationTable)->where($mappingCondition)->delete();
                                            // 插入关联表数据
                                            $sql    = 'INSERT INTO ' . $mappingRelationTable . ' (' . $mappingFk . ',' . $mappingRelationFk . ') SELECT a.' . $this->getPk() . ',b.' . $model->getPk() . ' FROM ' . $this->getTableName() . ' AS a ,' . $model->getTableName() . " AS b where a." . $this->getPk() . ' =' . $pk . ' AND  b.' . $model->getPk() . ' IN (' . $relationId . ") ";
                                            $result = $model->execute($sql);
                                            if (false !== $result)
                                            // 提交事务
                                            {
                                                $this->commit();
                                            } else
                                            // 事务回滚
                                            {
                                                $this->rollback();
                                            }

                                        }
                                        break;
                                    case 'DEL': // 根据外键删除中间表关联数据
                                        $result = $this->table($mappingRelationTable)->where($mappingCondition)->delete();
                                        break;
                                }
                                break;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 关联数据验证
     * @access protected
     * @param mixed $data  数据对象
     * @param string $name 关联名称
     * @return mixed
     */
    protected function crRelation(&$data, $name = '')
    {
        if (empty($data) && !empty($this->data)) {
            $data = $this->data;
        } elseif(!is_array($data)) {
            // 数据无效返回
            return false;
        }
        if (!empty($this->_link)) {
            $fields = $this->getDbFields();
            // 遍历关联定义
            foreach ($this->_link as $key => $val) {
                // 操作制定关联类型
                $mappingName =  !empty($val['mapping_name']) ? $val['mapping_name'] : $key; // 映射名称
                if (empty($name) || true === $name || $mappingName == $name || (is_array($name) && in_array($mappingName,$name)) ) {
                    // 操作制定的关联
                    $mappingType = !empty($val['mapping_type']) ? $val['mapping_type'] : $val;  // 关联类型
                    $mappingClass = !empty($val['class_name']) ? $val['class_name'] : $key; // 关联类名
                    $mappingKey =!empty($val['mapping_key']) ? $val['mapping_key'] : $this->getPk(); // 关联键名
                    if (strtoupper($mappingClass) == strtoupper($this->name) || !isset($data[$mappingName])) {
                        continue; // 自引用关联或提交关联数据跳过
                    }
                    // 获取关联model对象
                    $model = D($mappingClass);
                    $_data = $data[$mappingName];
                    unset($data[$key]);
                    if ($_data = $model->token(false)->create($_data)) {
                        $data[$mappingName] = $_data;
                        $fields[] = $mappingName;
                    } else {
                        $error = $model->getError();
                        if ($this->patchValidate) {
                            $this->error[$mappingName] = $error;
                        } else {
                            $this->error = $error;
                            return false;
                        }
                    }
                }
            }

            // 过滤非法字段数据
            $diff = array_diff(array_keys($data), $fields);
            foreach ($diff as $key) {
                unset($data[$key]);
            }
        }
    }

    /**
     * 进行关联查询
     * @access public
     * @param mixed $name 关联名称
     * @return Model
     */
    public function relation($name)
    {
        $this->options['link'] = $name;
        $this->autoCheckFields = false;
        return $this;
    }

    /**
     * 关联数据获取 仅用于查询后
     * @access public
     * @param string $name 关联名称
     * @return array
     */
    public function relationGet($name)
    {
        if (empty($this->data)) {
            return false;
        }

        return $this->getRelation($this->data, $name, true);
    }
}
