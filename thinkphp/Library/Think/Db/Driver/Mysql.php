<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Think\Db\Driver;
use Think\Db\Driver;

/**
 * mysql数据库驱动 
 */
class Mysql extends Driver{

    /**
     * 解析pdo连接的dsn信息
     * @access public
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn($config){
        $dsn  =   'mysql:dbname='.$config['database'].';host='.$config['hostname'];
        if(!empty($config['hostport'])) {
            $dsn  .= ';port='.$config['hostport'];
        }elseif(!empty($config['socket'])){
            $dsn  .= ';unix_socket='.$config['socket'];
        }
        if(!empty($config['charset'])){
            $dsn  .= ';charset='.$config['charset'];
        }
        return $dsn;
    }

    /**
     * 取得数据表的字段信息
     * @access public
     */
    public function getFields($tableName) {
        $this->initConnect(true);
        $sql   = 'SHOW COLUMNS FROM `'.$tableName.'`';
        $result = $this->query($sql);
        $info   =   [];
        if($result) {
            foreach ($result as $key => $val) {
                $info[$val['field']] = [
                    'name'    => $val['field'],
                    'type'    => $val['type'],
                    'notnull' => (bool) ($val['null'] === ''), // not null is empty, null is yes
                    'default' => $val['default'],
                    'primary' => (strtolower($val['key']) == 'pri'),
                    'autoinc' => (strtolower($val['extra']) == 'auto_increment'),
                ];
            }
        }
        return $info;
    }

    /**
     * 取得数据库的表信息
     * @access public
     */
    public function getTables($dbName='') {
        $sql    = !empty($dbName)?'SHOW TABLES FROM '.$dbName:'SHOW TABLES ';
        $result = $this->query($sql);
        $info   =   [];
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

    /**
     * 字段和表名处理
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        $key   =  trim($key);
        if(!preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
           $key = '`'.$key.'`';
        }
        return $key;            
    }

}
