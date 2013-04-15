<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Think\Db;
use Think\Config;
use Think\Debug;
use Think\Log;
use PDO;

class Lite {
    // PDO操作实例
    protected $PDOStatement = null;
    // 当前操作所属的模型名
    protected $model      = '_think_';
    // 当前SQL指令
    protected $queryStr   = '';
    protected $modelSql   = [];
    // 最后插入ID
    protected $lastInsID  = null;
    // 返回或者影响记录数
    protected $numRows    = 0;
    // 事务指令数
    protected $transTimes = 0;
    // 错误信息
    protected $error      = '';
    // 数据库连接ID 支持多个连接
    protected $linkID     = [];
    // 当前连接ID
    protected $_linkID    = null;
    // 当前查询ID
    protected $queryID    = null;
    // 数据库连接参数配置
    protected $config     = [];

    protected $queryTimes   =   0;
    protected $executeTimes =   0;
    // PDO连接参数
	protected $options = [
        PDO::ATTR_CASE              =>  PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           =>  PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      =>  PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES =>  false,
	];

    /**
     * 架构函数 读取数据库配置信息
     * @access public
     * @param array $config 数据库配置数组
     */
    public function __construct($config=''){
        if(!empty($config)) {
            $this->config   =   $config;
            if(empty($this->config['params'])) {
                $this->config['params'] =   [];
            }
            $this->config['params'] =   $this->options+$this->config['params'];
        }
    }

    /**
     * 连接数据库方法
     * @access public
     */
    public function connect($config='',$linkNum=0) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;
            try{
                if(empty($config['dsn'])) {
                    $config['dsn']  =   $config['dbms'].':dbname='.$config['database'].';host='.$config['hostname'];
                    if(!empty($config['hostport'])) {
                        $config['dsn']  .= ';port='.$config['hostport'];
                    }elseif(!empty($config['unix_socket'])){
                        $config['dsn']  .= ';unix_socket='.$config['unix_socket'];
                    }
                }
                $this->linkID[$linkNum] = new PDO( $config['dsn'], $config['username'], $config['password'],$config['params']);
            }catch (\PDOException $e) {
                E($e->getMessage());
            }
            if(!empty($config['charset'])) {
                $this->linkID[$linkNum]->exec('SET NAMES '.$config['charset']);
            }
            // 注销数据库连接配置信息
            if(1 != $config['deploy']) $this->config  =   [];
        }
        return $this->linkID[$linkNum];
    }

    /**
     * 释放查询结果
     * @access public
     */
    public function free() {
        $this->PDOStatement = null;
    }

    /**
     * 执行查询 返回数据集
     * @access public
     * @param string $str  sql指令
     * @return mixed
     */
    public function query($str,$bind=[]) {
        $this->initConnect(false);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        //释放前次的查询结果
        if ( !empty($this->PDOStatement) ) $this->free();
        $this->queryTimes++;
        $this->debug(true);
        $this->PDOStatement = $this->_linkID->prepare($str);
        if(false === $this->PDOStatement)
            E($this->error());
        $result =   $this->PDOStatement->execute($bind);
        $this->debug(false);
        if ( false === $result ) {
            $this->error();
            return false;
        } else {
            return $this->getResult();
        }
    }

    /**
     * 执行语句
     * @access public
     * @param string $str  sql指令
     * @return integer
     */
    public function execute($str,$bind=[]) {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        //释放前次的查询结果
        if ( !empty($this->PDOStatement) ) $this->free();
        $this->executeTimes++;
        // 记录开始执行时间
        $this->debug(true);
        $this->PDOStatement	=	$this->_linkID->prepare($str);
        if(false === $this->PDOStatement) {
            E($this->error());
        }
        $result	=	$this->PDOStatement->execute($bind);
        $this->debug(false);
        if ( false === $result) {
            $this->error();
            return false;
        } else {
            $this->numRows = $this->PDOStatement->rowCount();
            if(preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $str)) {
                $this->lastInsID = $this->getLastInsertId();
            }
            return $this->numRows;
        }
    }

    /**
     * 获取最后插入id
     * @access public
     * @return integer
     */
    public function getLastInsertId() {
        return $this->_linkID->lastInsertId();
    }

    /**
     * 启动事务
     * @access public
     * @return void
     */
    public function startTrans() {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        //数据rollback 支持
        if ($this->transTimes == 0) {
            $this->_linkID->beginTransaction();
        }
        $this->transTimes++;
        return ;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return boolen
     */
    public function commit() {
        if ($this->transTimes > 0) {
            $result = $this->_linkID->commit();
            $this->transTimes = 0;
            if(!$result){
                $this->error();
                return false;
            }
        }
        return true;
    }

    /**
     * 事务回滚
     * @access public
     * @return boolen
     */
    public function rollback() {
        if ($this->transTimes > 0) {
            $result = $this->_linkID->rollback();
            $this->transTimes = 0;
            if(!$result){
                $this->error();
                return false;
            }
        }
        return true;
    }

    /**
     * 获得所有的查询数据
     * @access private
     * @return array
     */
    private function getResult() {
        //返回数据集
        $result =   $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);
        $this->numRows = count( $result );
        return $result;
    }

    /**
     * 获得查询次数
     * @access public
     * @param boolean $execute 是否包含所有查询
     * @return integer
     */
    public function getQueryTimes($execute=false){
        return $execute?$this->queryTimes+$this->executeTimes:$this->queryTimes;
    }

    /**
     * 获得执行次数
     * @access public
     * @return integer
     */
    public function getExecuteTimes(){
        return $this->executeTimes;
    }

    /**
     * 关闭数据库
     * @access public
     */
    public function close() {
        $this->_linkID = null;
    }

    /**
     * 数据库错误信息
     * 并显示当前的SQL语句
     * @access public
     * @return string
     */
    public function error() {
        if($this->PDOStatement) {
            $error = $this->PDOStatement->errorInfo();
            $this->error = $error[1].':'.$error[2];
        }else{
            $this->error = '';
        }
        if('' != $this->queryStr){
            $this->error .= "\n [ SQL语句 ] : ".$this->queryStr;
        }
        Log::record($this->error,'ERR');
        return $this->error;
    }

    /**
     * 获取最近一次查询的sql语句 
     * @param string $model  模型名
     * @access public
     * @return string
     */
    public function getLastSql($model='') {
        return $model?$this->modelSql[$model]:$this->queryStr;
    }

    /**
     * 获取最近插入的ID
     * @access public
     * @return string
     */
    public function getLastInsID() {
        return $this->lastInsID;
    }

    /**
     * 获取最近的错误信息
     * @access public
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * 设置当前操作模型
     * @access public
     * @param string $model  模型名
     * @return void
     */
    public function setModel($model){
        $this->model =  $model;
    }

    /**
     * 数据库调试 记录当前SQL
     * @access protected
     * @param boolean $start  调试开始标记 true 开始 false 结束
     */
    protected function debug($start) {
        if($this->config['debug']) {// 开启数据库调试模式
            if($start) {
                Debug::remark('queryStartTime','time');
            }else{
                $this->modelSql[$this->model]   =  $this->queryStr;
                $this->model  =   '_think_';
                // 记录操作结束时间
                Debug::remark('queryEndTime','time');
                Log::record($this->queryStr.' [ RunTime:'.Debug::getUseTime('queryStartTime','queryEndTime').'s ]','SQL');
            }
        }
    }

    /**
     * 初始化数据库连接
     * @access protected
     * @param boolean $master 主服务器
     * @return void
     */
    protected function initConnect($master=true) {
        if(1 == $this->config['deploy'])
            // 采用分布式数据库
            $this->_linkID = $this->multiConnect($master);
        else
            // 默认单数据库
            if ( !$this->_linkID ) $this->_linkID = $this->connect();
    }

    /**
     * 连接分布式服务器
     * @access protected
     * @param boolean $master 主服务器
     * @return void
     */
    protected function multiConnect($master=false) {
        static $_config = [];
        if(empty($_config)) {
            // 缓存分布式数据库配置解析
            foreach ($this->config as $key=>$val){
                $_config[$key]      =   explode(',',$val);
            }
        }
        // 数据库读写是否分离
        if(Config::get('db_rw_separate')){
            // 主从式采用读写分离
            if($master)
                // 主服务器写入
                $r  =   floor(mt_rand(0,Config::get('db_master_num')-1));
            else{
                if(is_numeric(Config::get('db_slave_no'))) {// 指定服务器读
                    $r = Config::get('db_slave_no');
                }else{
                    // 读操作连接从服务器
                    $r = floor(mt_rand(Config::get('db_master_num'),count($_config['hostname'])-1));   // 每次随机连接的数据库
                }
            }
        }else{
            // 读写操作不区分服务器
            $r = floor(mt_rand(0,count($_config['hostname'])-1));   // 每次随机连接的数据库
        }
        $db_config = [
            'username'  =>  isset($_config['username'][$r])?$_config['username'][$r]:$_config['username'][0],
            'password'  =>  isset($_config['password'][$r])?$_config['password'][$r]:$_config['password'][0],
            'hostname'  =>  isset($_config['hostname'][$r])?$_config['hostname'][$r]:$_config['hostname'][0],
            'hostport'  =>  isset($_config['hostport'][$r])?$_config['hostport'][$r]:$_config['hostport'][0],
            'database'  =>  isset($_config['database'][$r])?$_config['database'][$r]:$_config['database'][0],
            'dsn'       =>  isset($_config['dsn'][$r])?$_config['dsn'][$r]:$_config['dsn'][0],
            'params'    =>  isset($_config['params'][$r])?$_config['params'][$r]:$_config['params'][0],
        ];
        return $this->connect($db_config,$r);
    }

   /**
     * 析构方法
     * @access public
     */
    public function __destruct() {
        // 释放查询
        if ($this->queryID){
            $this->free();
        }
        // 关闭连接
        $this->close();
    }
}