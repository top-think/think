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
// $Id$

namespace Think;
class Model {
    // 操作状态
    const MODEL_INSERT          =   1;      //  插入模型数据
    const MODEL_UPDATE          =   2;      //  更新模型数据
    const MODEL_BOTH            =   3;      //  包含上面两种方式

    // 当前使用的扩展模型
    private   $_extModel        =   null;
    // 当前数据库操作对象
    protected $db               =   null;
    // 主键名称
    protected $pk               =   'id';
    // 数据表前缀
    protected $tablePrefix      =   '';
    // 模型名称
    protected $name             =   '';
    // 数据库名称
    protected $dbName           =   '';
    //数据库配置
    protected $connection       =   '';
    // 数据表名（不包含表前缀）
    protected $tableName        =   '';
    // 实际数据表名（包含表前缀）
    protected $trueTableName    =   '';
    // 最近错误信息
    protected $error            =   '';
    // 字段信息
    protected $fields           =   [];
    // 数据信息
    protected $data             =   [];
    // 查询表达式参数
    protected $options          =   [];
    protected $_validate        =   [];  // 自动验证定义
    protected $_auto            =   [];  // 自动完成定义
    protected $_map             =   [];  // 字段映射定义
    protected $_scope           =   [];  // 命名范围定义
    // 是否自动检测数据表字段信息
    protected $autoCheckFields  =   false;
    // 是否批处理验证
    protected $patchValidate    =   false;
    // 链操作方法列表
    protected $methods          =   ['table','order','alias','having','group','lock','distinct','auto','filter','validate'];
    // 配置参数
    protected $config           =   [];

    /**
     * 架构函数
     * 取得DB类的实例对象 字段检查
     * @access public
     * @param string $name 模型名称
     * @param array $config 模型配置
     */
    public function __construct($name='',$config=[]) {
        // 模型初始化
        $this->_initialize();
        // 传入模型参数
        if(!empty($name)){
            $this->name =   $name;
        }elseif(empty($this->name)){
            $this->name =   $this->getModelName();
        }
        if(strpos($this->name,'.')) { // 支持 数据库名.模型名的 定义
            list($this->dbName,$this->name) = explode('.',$this->name);
        }

        if(isset($config['table_prefix'])) {
            $this->tablePrefix  =   $config['table_prefix'];
        }
        if(isset($config['connection'])) {
            $this->connection   =   $config['connection'];
        }
        if(isset($config['table_name'])) {
            $this->tableName    =   $config['table_name'];
        }
        if(isset($config['true_table_name'])) {
            $this->trueTableName    =   $config['true_table_name'];
        }
        if(isset($config['db_name'])) {
            $this->dbName    =   $config['db_name'];
        }

        // 设置表前缀
        if(empty($this->tablePrefix)) {
            $this->tablePrefix  =   is_null($this->tablePrefix)?'':C('db_prefix');
        }

        // 数据库初始化操作
        // 获取数据库操作对象
        // 当前模型有独立的数据库连接信息
        $this->db(0,empty($this->connection)?$connection:$this->connection);
    }

    /**
     * 设置数据对象的值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return void
     */
    public function __set($name,$value) {
        // 设置数据对象属性
        $this->data[$name]  =   $value;
    }

    /**
     * 获取数据对象的值
     * @access public
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name) {
        return isset($this->data[$name])?$this->data[$name]:null;
    }

    /**
     * 检测数据对象的值
     * @access public
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name) {
        return isset($this->data[$name]);
    }

    /**
     * 销毁数据对象的值
     * @access public
     * @param string $name 名称
     * @return void
     */
    public function __unset($name) {
        unset($this->data[$name]);
    }

    /**
     * 利用__call方法实现一些特殊的Model方法
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return mixed
     */
    public function __call($method,$args) {
        if(in_array(strtolower($method),$this->methods,true)) {
            // 连贯操作的实现
            $this->options[strtolower($method)] =   $args[0];
            return $this;
        }elseif(in_array(strtolower($method),['count','sum','min','max','avg'],true)){
            // 统计查询的实现
            $field =  isset($args[0])?$args[0]:'*';
            return $this->getField(strtoupper($method).'('.$field.') AS tp_'.$method);
        }elseif(strtolower(substr($method,0,5))=='getby') {
            // 根据某个字段获取记录
            $field   =   parse_name(substr($method,5));
            $where[$field] =  $args[0];
            return $this->where($where)->find();
        }elseif(strtolower(substr($method,0,10))=='getfieldby') {
            // 根据某个字段获取记录的某个值
            $name   =   parse_name(substr($method,10));
            $where[$name] =$args[0];
            return $this->where($where)->getField($args[1]);
        }elseif(isset($this->_scope[$method])){// 命名范围的单独调用支持
            return $this->scope($method,$args[0]);
        }else{
            E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }
    // 回调方法 初始化模型
    protected function _initialize() {}

    /**
     * 对保存到数据库的数据进行处理
     * @access protected
     * @param mixed $data 要操作的数据
     * @return boolean
     */
     protected function _facade($data) {
        // 检查非数据字段
        if(!empty($this->fields)) {
            foreach ($data as $key=>$val){
                if(!in_array($key,$this->fields,true)){
                    unset($data[$key]);
                }elseif(is_scalar($val)) {
                    // 字段类型检查
                    $this->_parseType($data,$key);
                }
            }
        }
        // 安全过滤
        if(!empty($this->options['filter'])) {
            $data = array_map($this->options['filter'],$data);
            unset($this->options['filter']);
        }
        $this->_before_write($data);
        return $data;
     }

    // 写入数据前的回调方法 包括新增和更新
    protected function _before_write(&$data) {}

    /**
     * 新增数据
     * @access public
     * @param mixed $data 数据
     * @param boolean $replace 是否replace
     * @return mixed
     */
    public function add($data='',$replace=false) {
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data           =   $this->data;
                // 重置数据
                $this->data     = [];
            }else{
                $this->error    = L('_DATA_TYPE_INVALID_');
                return false;
            }
        }
        // 分析表达式
        $options    =   $this->_parseOptions();
        // 数据处理
        $data       =   $this->_facade($data);
        if(false === $this->_before_insert($data,$options)) {
            return false;
        }
        // 写入数据到数据库
        $result = $this->db->insert($data,$options,$replace);
        if(false !== $result ) {
            $insertId   =   $this->getLastInsID();
            if($insertId) {
                // 自增主键返回插入ID
                $data[$this->getPk()]  = $insertId;
                $this->_after_insert($data,$options);
                return $insertId;
            }
            $this->_after_insert($data,$options);
        }
        return $result;
    }
    // 插入数据前的回调方法
    protected function _before_insert(&$data,$options) {}
    // 插入成功后的回调方法
    protected function _after_insert($data,$options) {}

    public function addAll($dataList,$replace=false){
        if(empty($dataList)) {
            $this->error = L('_DATA_TYPE_INVALID_');
            return false;
        }
        // 分析表达式
        $options =  $this->_parseOptions();
        // 数据处理
        foreach ($dataList as $key=>$data){
            $dataList[$key] = $this->_facade($data);
        }
        // 写入数据到数据库
        $result = $this->db->insertAll($dataList,$options,$replace);
        if(false !== $result ) {
            $insertId   =   $this->getLastInsID();
            if($insertId) {
                return $insertId;
            }
        }
        return $result;
    }

    /**
     * 通过Select方式添加记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $options 表达式
     * @return boolean
     */
    public function selectAdd($fields='',$table='',$options=[]) {
        // 分析表达式
        $options =  $this->_parseOptions($options);
        // 写入数据到数据库
        if(false === $result = $this->db->selectInsert($fields?$fields:$options['field'],$table?$table:$this->getTableName(),$options)){
            // 数据库插入操作失败
            $this->error = L('_OPERATION_WRONG_');
            return false;
        }else {
            // 插入成功
            return $result;
        }
    }

    /**
     * 保存数据
     * @access public
     * @param mixed $data 数据
     * @return boolean
     */
    public function save($data='') {
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data           =   $this->data;
                // 重置数据
                $this->data     =   [];
            }else{
                $this->error    =   L('_DATA_TYPE_INVALID_');
                return false;
            }
        }
        // 数据处理
        $data       =   $this->_facade($data);
        // 分析表达式
        $options    =   $this->_parseOptions();
        if(false === $this->_before_update($data,$options)) {
            return false;
        }
        if(!isset($options['where']) ) {
            // 如果存在主键数据 则自动作为更新条件
            if(isset($data[$this->getPk()])) {
                $pk                 =   $this->getPk();
                $where[$pk]         =   $data[$pk];
                $options['where']   =   $where;
                $pkValue            =   $data[$pk];
                unset($data[$pk]);
            }else{
                // 如果没有任何更新条件则不执行
                $this->error        =   Lang::get('_OPERATION_WRONG_');
                return false;
            }
        }
        $result     =   $this->db->update($data,$options);
        if(false !== $result) {
            if(isset($pkValue)) $data[$pk]   =  $pkValue;
            $this->_after_update($data,$options);
        }
        return $result;
    }
    // 更新数据前的回调方法
    protected function _before_update(&$data,$options) {}
    // 更新成功后的回调方法
    protected function _after_update($data,$options) {}

    /**
     * 删除数据
     * @access public
     * @param mixed $options 表达式
     * @return mixed
     */
    public function delete($options=[]) {
        if(empty($options) && empty($this->options['where'])) {
            // 如果删除条件为空 则删除当前数据对象所对应的记录
            if(!empty($this->data) && isset($this->data[$this->getPk()]))
                return $this->delete($this->data[$this->getPk()]);
            else
                return false;
        }
        if(is_numeric($options)  || is_string($options)) {
            // 根据主键删除记录
            $pk   =  $this->getPk();
            if(strpos($options,',')) {
                $where[$pk]     =  ['IN', $options];
            }else{
                $where[$pk]     =  $options;
            }
            $pkValue            =  $where[$pk];
            $options            =  [];
            $options['where']   =  $where;
        }
        // 分析表达式
        $options =  $this->_parseOptions($options);
        $result=    $this->db->delete($options);
        if(false !== $result) {
            $data = [];
            if(isset($pkValue)) $data[$pk]   =  $pkValue;
            $this->_after_delete($data,$options);
        }
        // 返回删除记录个数
        return $result;
    }
    // 删除成功后的回调方法
    protected function _after_delete($data,$options) {}

    /**
     * 查询数据集
     * @access public
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function select($options=[]) {
        if(is_string($options) || is_numeric($options)) {
            // 根据主键查询
            $pk   =  $this->getPk();
            if(strpos($options,',')) {
                $where[$pk]     =  ['IN',$options];
            }else{
                $where[$pk]     =  $options;
            }
            $options            =  [];
            $options['where']   =  $where;
        }elseif(false === $options){ // 用于子查询 不查询只返回SQL
            $options            =  [];
            // 分析表达式
            $options            =  $this->_parseOptions($options);
            return  '( '.$this->db->buildSelectSql($options).' )';
        }
        // 分析表达式
        $options    =  $this->_parseOptions($options);
        $resultSet  = $this->db->select($options);
        if(false === $resultSet) {
            return false;
        }
        if(empty($resultSet)) { // 查询结果为空
            return null;
        }
        $this->_after_select($resultSet,$options);
        return $resultSet;
    }
    // 查询成功后的回调方法
    protected function _after_select(&$resultSet,$options) {}

    /**
     * 生成查询SQL 可用于子查询
     * @access public
     * @param array $options 表达式参数
     * @return string
     */
    public function buildSql($options=[]) {
        // 分析表达式
        $options =  $this->_parseOptions($options);
        return  '( '.$this->db->buildSelectSql($options).' )';
    }

    /**
     * 分析表达式
     * @access protected
     * @param array $options 表达式参数
     * @return array
     */
    protected function _parseOptions($options=[]) {
        if(is_array($options))
            $options =  array_merge($this->options,$options);
        // 查询过后清空sql表达式组装 避免影响下次查询
        $this->options  =   [];

        if(!empty($options['alias'])) {
            $options['table']  .=   ' '.$options['alias'];
        }
        // 记录操作的模型名称
        $options['model']       =   $this->name;

        if(isset($options['table'])) {// 动态指定表名
            $fields     =   $this->db->getFields($this->options['table']);
            $fields     =   $fields?array_keys($fields):false;
        }else{
            $options['table']   =   $this->getTableName();
            $fields     =   $this->getDbFields();
        }
        // 字段类型验证
        if(isset($options['where']) && is_array($options['where']) && !empty($fields)) {
            // 对数组查询条件进行字段类型检查
            foreach ($options['where'] as $key=>$val){
                $key            =   trim($key);
                if(in_array($key,$fields,true)){
                    if(is_scalar($val)) {
                        $this->_parseType($options['where'],$key);
                    }
                }elseif('_' != substr($key,0,1) && false === strpos($key,'.') && false === strpos($key,'(') && false === strpos($key,'|') && false === strpos($key,'&')){
                    unset($options['where'][$key]);
                }
            }
        }


        // 表达式过滤
        $this->_options_filter($options);
        return $options;
    }
    // 表达式过滤回调方法
    protected function _options_filter(&$options) {}

    /**
     * 数据类型检测
     * @access protected
     * @param mixed $data 数据
     * @param string $key 字段名
     * @return void
     */
    protected function _parseType(&$data,$key) {
        if(isset($this->fields['_type'][$key])) {
            $fieldType = strtolower($this->fields['_type'][$key]);
            if(false === strpos($fieldType,'bigint') && false !== strpos($fieldType,'int')) {
                $data[$key]   =  intval($data[$key]);
            }elseif(false !== strpos($fieldType,'float') || false !== strpos($fieldType,'double')){
                $data[$key]   =  floatval($data[$key]);
            }elseif(false !== strpos($fieldType,'bool')){
                $data[$key]   =  (bool)$data[$key];
            }
        }
    }

    /**
     * 查询数据
     * @access public
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function find($options=[]) {
        if(is_numeric($options) || is_string($options)) {
            $where[$this->getPk()]  =   $options;
            $options                =   [];
            $options['where']       =   $where;
        }
        // 总是查找一条记录
        $options['limit']   =   1;
        // 分析表达式
        $options            =   $this->_parseOptions($options);
        $resultSet          =   $this->db->select($options);
        if(false === $resultSet) {
            return false;
        }
        if(empty($resultSet)) {// 查询结果为空
            return null;
        }
        $this->data         =   $resultSet[0];
        $this->_after_find($this->data,$options);
        return $this->data;
    }
    // 查询成功的回调方法
    protected function _after_find(&$result,$options) {}

    /**
     * 处理字段映射
     * @access public
     * @param array $data 当前数据
     * @param integer $type 类型 0 写入 1 读取
     * @return array
     */
    public function parseFieldsMap($data,$type=1) {
        // 检查字段映射
        if(!empty($this->_map)) {
            foreach ($this->_map as $key=>$val){
                if($type==1) { // 读取
                    if(isset($data[$val])) {
                        $data[$key] =   $data[$val];
                        unset($data[$val]);
                    }
                }else{
                    if(isset($data[$key])) {
                        $data[$val] =   $data[$key];
                        unset($data[$key]);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 设置记录的某个字段值
     * 支持使用数据库字段和方法
     * @access public
     * @param string|array $field  字段名
     * @param string $value  字段值
     * @return boolean
     */
    public function setField($field,$value='') {
        if(is_array($field)) {
            $data           =   $field;
        }else{
            $data[$field]   =   $value;
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
    public function setInc($field,$step=1) {
        return $this->setField($field,['exp',$field.'+'.$step]);
    }

    /**
     * 字段值减少
     * @access public
     * @param string $field  字段名
     * @param integer $step  减少值
     * @return boolean
     */
    public function setDec($field,$step=1) {
        return $this->setField($field,['exp',$field.'-'.$step]);
    }

    /**
     * 获取一条记录的某个字段值
     * @access public
     * @param string $field  字段名
     * @param string $spea  字段数据间隔符号 NULL返回数组
     * @return mixed
     */
    public function getField($field,$sepa=null) {
        $options['field']       =   $field;
        $options                =   $this->_parseOptions($options);
        $field                  =   trim($field);
        if(strpos($field,',')) { // 多字段
            if(!isset($options['limit'])){
                $options['limit']   =   is_numeric($sepa)?$sepa:'';
            }
            $resultSet          =   $this->db->select($options);
            if(!empty($resultSet)) {
                $_field         =   explode(',', $field);
                $field          =   array_keys($resultSet[0]);
                $key            =   array_shift($field);
                $key2           =   array_shift($field);
                $cols           =   [];
                $count          =   count($_field);
                foreach ($resultSet as $result){
                    $name   =  $result[$key];
                    if(2==$count) {
                        $cols[$name]   =  $result[$key2];
                    }else{
                        $cols[$name]   =  is_string($sepa)?implode($sepa,$result):$result;
                    }
                }
                return $cols;
            }
        }else{   // 查找一条记录
            // 返回数据个数
            if(true !== $sepa) {// 当sepa指定为true的时候 返回所有数据
                $options['limit']   =   is_numeric($sepa)?$sepa:1;
            }
            $result = $this->db->select($options);
            if(!empty($result)) {
                if(true !== $sepa && 1==$options['limit']) return reset($result[0]);
                foreach ($result as $val){
                    $array[]    =   $val[$field];
                }
                return $array;
            }
        }
        return null;
    }

    /**
     * 创建数据对象 但不保存到数据库
     * @access public
     * @param mixed $data 创建数据
     * @param string $type 状态
     * @return mixed
     */
     public function create($data='',$type='') {
        // 如果没有传值默认取POST数据
        if(empty($data)) {
            $data   =   $_POST;
        }elseif(is_object($data)){
            $data   =   get_object_vars($data);
        }
        // 验证数据
        if(empty($data) || !is_array($data)) {
            $this->error = L('_DATA_TYPE_INVALID_');
            return false;
        }

        // 检查字段映射
        $data = $this->parseFieldsMap($data,0);

        // 状态
        $type = $type?$type:(!empty($data[$this->getPk()])?self::MODEL_UPDATE:self::MODEL_INSERT);

        // 检测提交字段的合法性
        if(isset($this->options['field'])) { // $this->field('field1,field2...')->create()
            $fields =   $this->options['field'];
            unset($this->options['field']);
        }elseif($type == self::MODEL_INSERT && isset($this->insertFields)) {
            $fields =   $this->insertFields;
        }elseif($type == self::MODEL_UPDATE && isset($this->updateFields)) {
            $fields =   $this->updateFields;
        }
        if(isset($fields)) {
            if(is_string($fields)) {
                $fields =   explode(',',$fields);
            }
            foreach ($data as $key=>$val){
                if(!in_array($key,$fields)) {
                    unset($data[$key]);
                }
            }
        }

        // 验证完成生成数据对象
        if($this->autoCheckFields) { // 开启字段检测 则过滤非法字段数据
            $fields =   $this->getDbFields();
            foreach ($data as $key=>$val){
                if(!in_array($key,$fields)) {
                    unset($data[$key]);
                }elseif(MAGIC_QUOTES_GPC && is_string($val)){
                    $data[$key] =   stripslashes($val);
                }
            }
        }

        // 赋值当前数据对象
        $this->data =   $data;
        // 返回创建的数据以供其他调用
        return $data;
     }

    /**
     * SQL查询
     * @access public
     * @param string $sql  SQL指令
     * @param mixed $parse  是否需要解析SQL
     * @return mixed
     */
    public function query($sql,$parse=false) {
        if(!is_bool($parse) && !is_array($parse)) {
            $parse = func_get_args();
            array_shift($parse);
        }
        $sql  =   $this->parseSql($sql,$parse);
        return $this->db->query($sql);
    }

    /**
     * 执行SQL语句
     * @access public
     * @param string $sql  SQL指令
     * @param mixed $parse  是否需要解析SQL
     * @return false | integer
     */
    public function execute($sql,$parse=false) {
        if(!is_bool($parse) && !is_array($parse)) {
            $parse = func_get_args();
            array_shift($parse);
        }
        $sql  =   $this->parseSql($sql,$parse);
        return $this->db->execute($sql);
    }

    /**
     * 解析SQL语句
     * @access public
     * @param string $sql  SQL指令
     * @param boolean $parse  是否需要解析SQL
     * @return string
     */
    protected function parseSql($sql,$parse) {
        // 分析表达式
        if(true === $parse) {
            $options =  $this->_parseOptions();
            $sql  =   $this->db->parseSql($sql,$options);
        }elseif(is_array($parse)){ // SQL预处理
            $sql  = vsprintf($sql,$parse);
        }else{
            $sql    =   strtr($sql,['__TABLE__'=>$this->getTableName(),'__PREFIX__'=>$this->tablePrefix]);
        }
        $this->db->setModel($this->name);
        return $sql;
    }

    /**
     * 切换当前的数据库连接
     * @access public
     * @param integer $linkNum  连接序号
     * @param mixed $config  数据库连接信息
     * @param array $params  模型参数
     * @return Model
     */
    public function db($linkNum='',$config='',$params=[]){
        if(''===$linkNum && $this->db) {
            return $this->db;
        }
        static $_linkNum    =   [];
        static $_db = [];
        if(!isset($_db[$linkNum]) || (isset($_db[$linkNum]) && $config && $_linkNum[$linkNum]!=$config) ) {
            // 创建一个新的实例
            if(!empty($config) && is_string($config) && false === strpos($config,'/')) { // 支持读取配置参数
                $config  =  C($config);
            }
            $_db[$linkNum]            =    Db::instance($config);
        }elseif(NULL === $config){
            $_db[$linkNum]->close(); // 关闭数据库连接
            unset($_db[$linkNum]);
            return ;
        }
        if(!empty($params)) {
            if(is_string($params))    parse_str($params,$params);
            foreach ($params as $name=>$value){
                $this->setProperty($name,$value);
            }
        }
        // 记录连接信息
        $_linkNum[$linkNum] =   $config;
        // 切换数据库连接
        $this->db   =    $_db[$linkNum];
        $this->_after_db();
        return $this;
    }
    // 数据库切换后回调方法
    protected function _after_db() {}

    /**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     */
    public function getModelName() {
        if(empty($this->name))
            $this->name =   substr(get_class($this),0,-5);
        return $this->name;
    }

    /**
     * 得到完整的数据表名
     * @access public
     * @return string
     */
    public function getTableName() {
        if(empty($this->trueTableName)) {
            $tableName  = !empty($this->tablePrefix) ? $this->tablePrefix : '';
            if(!empty($this->tableName)) {
                $tableName .= $this->tableName;
            }else{
                $tableName .= parse_name($this->name);
            }
            $this->trueTableName    =   strtolower($tableName);
        }
        return (!empty($this->dbName)?$this->dbName.'.':'').$this->trueTableName;
    }

    /**
     * 启动事务
     * @access public
     * @return void
     */
    public function startTrans() {
        $this->commit();
        $this->db->startTrans();
        return ;
    }

    /**
     * 提交事务
     * @access public
     * @return boolean
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public function rollback() {
        return $this->db->rollback();
    }

    /**
     * 返回模型的错误信息
     * @access public
     * @return string
     */
    public function getError(){
        return $this->error;
    }

    /**
     * 返回数据库的错误信息
     * @access public
     * @return string
     */
    public function getDbError() {
        return $this->db->getError();
    }

    /**
     * 返回最后插入的ID
     * @access public
     * @return string
     */
    public function getLastInsID() {
        return $this->db->getLastInsID();
    }

    /**
     * 返回最后执行的sql语句
     * @access public
     * @return string
     */
    public function getLastSql() {
        return $this->db->getLastSql($this->name);
    }
    // 鉴于getLastSql比较常用 增加_sql 别名
    public function _sql(){
        return $this->getLastSql();
    }

    /**
     * 获取主键名称
     * @access public
     * @return string
     */
    public function getPk() {
        return isset($this->fields['_pk'])?$this->fields['_pk']:$this->pk;
    }

    /**
     * 获取数据表字段信息
     * @access public
     * @return array
     */
    public function getDbFields(){
        if($this->fields) {
            $fields     =  $this->fields;
            unset($fields['_pk'],$fields['_type']);
            return array_keys($fields);
        }else{
            $fields =   Cache::get(md5($this->getTableName()));
            if(!$fields) {
                $fields =   $this->db->getFields($this->getTableName());
                foreach ($fields as $key=>$val){
                    // 记录字段类型
                    $type[$key]    =   $val['type'];
                    if($val['primary']) {
                        $fields['_pk'] = $key;
                    }
                }
                // 记录字段类型信息
                $fields['_type'] =  $type;
                Cache::set(md5($this->trueTableName),$fields);
            }
            if($fields) {
                $this->fields   =   $fields;
                // 永久缓存
                unset($fields['_pk'],$fields['_type']);
                return array_keys($fields);
            }
            return false;
        }
    }

    /**
     * 设置数据对象值
     * @access public
     * @param mixed $data 数据
     * @return Model
     */
    public function data($data=''){
        if('' === $data && !empty($this->data)) {
            return $this->data;
        }
        if(is_object($data)){
            $data   =   get_object_vars($data);
        }elseif(is_string($data)){
            parse_str($data,$data);
        }elseif(!is_array($data)){
            E(L('_DATA_TYPE_INVALID_'));
        }
        $this->data = $data;
        return $this;
    }

    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed $join
     * @return Model
     */
    public function join($join) {
        if(is_array($join)) {
            $this->options['join']      =   $join;
        }elseif(!empty($join)) {
            $this->options['join'][]    =   $join;
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
    public function union($union,$all=false) {
        if(empty($union)) return $this;
        if($all) {
            $this->options['union']['_all']  =   true;
        }
        if(is_object($union)) {
            $union   =  get_object_vars($union);
        }
        // 转换union表达式
        if(is_string($union) ) {
            $options =  $union;
        }elseif(is_array($union)){
            if(isset($union[0])) {
                $this->options['union']  =  array_merge($this->options['union'],$union);
                return $this;
            }else{
                $options =  $union;
            }
        }else{
            E(L('_DATA_TYPE_INVALID_'));
        }
        $this->options['union'][]  =   $options;
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
    public function cache($key=true,$expire=null,$type=''){
        if(false !== $key)
            $this->options['cache']  =  ['key'=>$key,'expire'=>$expire,'type'=>$type];
        return $this;
    }

    /**
     * 指定查询字段 支持字段排除
     * @access public
     * @param mixed $field
     * @param boolean $except 是否排除
     * @return Model
     */
    public function field($field,$except=false){
        if(true === $field) {// 获取全部字段
            $fields     =  $this->getDbFields();
            $field      =  $fields?$fields:'*';
        }elseif($except) {// 字段排除
            if(is_string($field)) {
                $field  =  explode(',',$field);
            }
            $fields     =  $this->getDbFields();
            $field      =  $fields?array_diff($fields,$field):$field;
        }
        $this->options['field']   =   $field;
        return $this;
    }

    /**
     * 调用命名范围
     * @access public
     * @param mixed $scope 命名范围名称 支持多个 和直接定义
     * @param array $args 参数
     * @return Model
     */
    public function scope($scope='',$args=NULL){
        if('' === $scope) {
            if(isset($this->_scope['default'])) {
                // 默认的命名范围
                $options    =   $this->_scope['default'];
            }else{
                return $this;
            }
        }elseif(is_string($scope)){ // 支持多个命名范围调用 用逗号分割
            $scopes         =   explode(',',$scope);
            $options        =   [];
            foreach ($scopes as $name){
                if(!isset($this->_scope[$name])) continue;
                $options    =   array_merge($options,$this->_scope[$name]);
            }
            if(!empty($args) && is_array($args)) {
                $options    =   array_merge($options,$args);
            }
        }elseif(is_array($scope)){ // 直接传入命名范围定义
            $options        =   $scope;
        }
        
        if(is_array($options) && !empty($options)){
            $this->options  =   array_merge($this->options,array_change_key_case($options));
        }
        return $this;
    }

    /**
     * 指定查询条件 支持安全过滤
     * @access public
     * @param mixed $where 条件表达式
     * @param mixed $parse 预处理参数
     * @return Model
     */
    public function where($where,$parse=null){
        if(!is_null($parse) && is_string($where)) {
            if(!is_array($parse)) {
                $parse = func_get_args();
                array_shift($parse);
            }
            $parse = array_map([$this->db,'escapeString'],$parse);
            $where =   vsprintf($where,$parse);
        }elseif(is_object($where)){
            $where  =   get_object_vars($where);
        }
        if(is_string($where) && '' != $where){
            $map    =   [];
            $map['_string']   =   $where;
            $where  =   $map;
        }        
        if(isset($this->options['where'])){
            $this->options['where'] =   array_merge($this->options['where'],$where);
        }else{
            $this->options['where'] =   $where;
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
    public function limit($offset,$length=null){
        $this->options['limit'] =   is_null($length)?$offset:$offset.','.$length;
        return $this;
    }

    /**
     * 指定分页
     * @access public
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     * @return Model
     */
    public function page($page,$listRows=null){
        $this->options['page'] =   is_null($listRows)?$page:$page.','.$listRows;
        return $this;
    }

    /**
     * 查询注释
     * @access public
     * @param string $comment 注释
     * @return Model
     */
    public function comment($comment){
        $this->options['comment'] =   $comment;
        return $this;
    }

    /**
     * 设置模型的属性值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return Model
     */
    public function setProperty($name,$value) {
        if(property_exists($this,$name))
            $this->$name = $value;
        return $this;
    }

}