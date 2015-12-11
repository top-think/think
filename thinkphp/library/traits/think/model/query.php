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

trait Query
{

    /**
     * 启动事务
     * @access public
     * @return void
     */
    public function startTrans()
    {
        $this->commit();
        $this->db->startTrans();
        return;
    }

    /**
     * 提交事务
     * @access public
     * @return boolean
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public function rollback()
    {
        return $this->db->rollback();
    }

    /**
     * 解析SQL语句
     * @access public
     * @param string $sql  SQL指令
     * @param boolean $parse  是否需要解析SQL
     * @return string
     */
    public function parseSql($sql, $parse)
    {
        // 分析表达式
        if (true === $parse) {
            $options = $this->_parseOptions();
            $sql     = $this->db->parseSql($sql, $options);
        } elseif (is_array($parse)) {
            // SQL预处理
            $sql = vsprintf($sql, $parse);
        } else {
            $sql = strtr($sql, ['__TABLE__' => $this->getTableName(), '__PREFIX__' => $this->tablePrefix]);
        }
        return $sql;
    }

    /**
     * 批处理执行SQL语句
     * 批处理的指令都认为是execute操作
     * @access public
     * @param array $sql  SQL批处理指令
     * @return boolean
     */
    public function patchQuery($sql = [])
    {
        if (!is_array($sql)) {
            return false;
        }
        // 自动启动事务支持
        $this->startTrans();
        try {
            foreach ($sql as $_sql) {
                $result = $this->execute($_sql);
                if (false === $result) {
                    // 发生错误自动回滚事务
                    $this->rollback();
                    return false;
                }
            }
            // 提交事务
            $this->commit();
        } catch (\think\exception $e) {
            $this->rollback();
        }
        return true;
    }
}
