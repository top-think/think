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

namespace think\db\driver;

use think\db\Driver;

/**
 * Oracle数据库驱动
 */
class Oracle extends Driver
{

    private $table       = '';
    protected $selectSql = 'SELECT * FROM (SELECT thinkphp.*, rownum AS numrow FROM (SELECT  %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%) thinkphp ) %LIMIT%%COMMENT%';

    /**
     * 解析pdo连接的dsn信息
     * @access public
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn($config)
    {
        $dsn = 'oci:dbname=' . $config['database'];
        if (!empty($config['charset'])) {
            $dsn .= ';charset=' . $config['charset'];
        }
        return $dsn;
    }

    /**
     * 执行语句
     * @access public
     * @param string $str  sql指令
     * @return integer
     */
    public function execute($str, $bind = [])
    {
        $this->initConnect(true);
        if (!$this->_linkID) {
            return false;
        }

        $this->queryStr = $str;
        if (!empty($bind)) {
            $this->queryStr .= '[ ' . print_r($bind, true) . ' ]';
        }
        $flag = false;
        if (preg_match("/^\s*(INSERT\s+INTO)\s+(\w+)\s+/i", $str, $match)) {
            $this->table = C("DB_SEQUENCE_PREFIX") . str_ireplace(C("DB_PREFIX"), "", $match[2]);
            $flag        = (boolean) $this->query("SELECT * FROM user_sequences WHERE sequence_name='" . strtoupper($this->table) . "'");
        }
        //释放前次的查询结果
        if (!empty($this->PDOStatement)) {
            $this->free();
        }

        $this->executeTimes++;
        // 记录开始执行时间
        $this->debug(true);
        $this->PDOStatement = $this->_linkID->prepare($str);
        if (false === $this->PDOStatement) {
            $this->error();
            return false;
        }
        try {
            $result = $this->PDOStatement->execute($bind);
            $this->debug(false);
            if (false === $result) {
                $this->error();
                return false;
            } else {
                $this->numRows = $this->PDOStatement->rowCount();
                if ($flag || preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $str)) {
                    $this->lastInsID = $this->_linkID->lastInsertId();
                }
                return $this->numRows;
            }
        } catch (\PDOException $e) {
            $this->error();
            return false;
        }
    }

    /**
     * 取得数据表的字段信息
     * @access public
     *
     * @param $tableName
     *
     * @return array
     */
    public function getFields($tableName)
    {
        list($tableName) = explode(' ', $tableName);
        $result          = $this->query("select a.column_name,data_type,decode(nullable,'Y',0,1) notnull,data_default,decode(a.column_name,b.column_name,1,0) pk "
            . "from user_tab_columns a,(select column_name from user_constraints c,user_cons_columns col "
            . "where c.constraint_name=col.constraint_name and c.constraint_type='P'and c.table_name='" . strtoupper($tableName)
            . "') b where table_name='" . strtoupper($tableName) . "' and a.column_name=b.column_name(+)");
        $info = [];
        if ($result) {
            foreach ($result as $key => $val) {
                $info[strtolower($val['column_name'])] = [
                    'name'    => strtolower($val['column_name']),
                    'type'    => strtolower($val['data_type']),
                    'notnull' => $val['notnull'],
                    'default' => $val['data_default'],
                    'primary' => $val['pk'],
                    'autoinc' => $val['pk'],
                ];
            }
        }
        return $info;
    }

    /**
     * 取得数据库的表信息（暂时实现取得用户表信息）
     * @access   public
     * @return array
     * @internal param string $dbName
     */
    public function getTables()
    {
        $result = $this->query("select table_name from user_tables");
        $info   = [];
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

    /**
     * limit
     * @access public
     * @return string
     */
    public function parseLimit($limit)
    {
        $limitStr = '';
        if (!empty($limit)) {
            $limit = explode(',', $limit);
            if (count($limit) > 1) {
                $limitStr = "(numrow>" . $limit[0] . ") AND (numrow<=" . ($limit[0] + $limit[1]) . ")";
            } else {
                $limitStr = "(numrow>0 AND numrow<=" . $limit[0] . ")";
            }

        }
        return $limitStr ? ' WHERE ' . $limitStr : '';
    }
    /**
     * 设置锁机制
     * @access protected
     * @param bool|false $lock
     *
     * @return string
     */
    protected function parseLock($lock = false)
    {
        if (!$lock) {
            return '';
        }

        return ' FOR UPDATE NOWAIT ';
    }

    /**
     * 字段和表名处理
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey($key)
    {
        $key = trim($key);
        if (strpos($key, '$.') && false === strpos($key, '(')) {
            // JSON字段支持
            list($field, $name) = explode($key, '$.');
            $key                = $field . '."' . $name . '"';
        }
        return $key;
    }

    /**
     * 随机排序
     * @access protected
     * @return string
     */
    protected function parseRand()
    {
        return 'DBMS_RANDOM.value';
    }
}
