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

namespace Think;

class Model {
    // 操作状态
    const MODEL_INSERT          =   1;      //  插入模型数据
    const MODEL_UPDATE          =   2;      //  更新模型数据
    const MODEL_BOTH            =   3;      //  包含上面两种方式
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
    // 命名范围定义
    protected $scope           =   [];
    // 字段映射定义
    protected $map             =   [];
    // 是否自动检测数据表字段信息
    protected $autoCheckFields  =   false;

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
            $this->tablePrefix  =   is_null($this->tablePrefix)?'':C('database.prefix');
        }

        // 数据库初始化操作
        // 获取数据库操作对象
        // 当前模型有独立的数据库连接信息
        $this->db(0,$this->connection);
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

    // 回调方法 初始化模型
    protected function _initialize() {}

    /**
     * 对写入到数据库的数据进行处理
     * @access protected
     * @param mixed $data 要操作的数据
     * @return array
     */
     protected function _write_data($data) {
        // 检查字段映射
        if(!empty($this->map)) {
            foreach ($this->map as $key=>$val){
                if(isset($data[$key])) {
                    $data[$val] =   $data[$key];
                    unset($data[$key]);
                }
            }
        }        
        // 检查非数据字段
        if(!empty($this->fields)) {
            foreach ($data as $key=>$val){
                if(!in_array($key,$this->fields,true)){
                    unset($data[$key]);
                }elseif(is_scalar($val) && empty($this->options['bind'][':'.$key])) {
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
        // 回调方法
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
        // 数据处理
        $data       =   $this->_write_data($data);
        // 分析表达式
        $options    =   $this->_parseOptions();        
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
        $data       =   $this->_write_data($data);
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
        // 数据列表读取后的处理
        $resultSet  =   $this->_read_datalist($resultSet);
        return $resultSet;
    }

    /**
     * 数据列表读取后的处理
     * @access protected
     * @param array $data 当前数据
     * @return array
     */
    protected function _read_datalist($resultSet) {
        $resultSet  =   array_map([$this,'_read_data'],$resultSet);
        $this->_after_select($resultSet);
        return $resultSet;
    }
    // 查询成功后的回调方法
    protected function _after_select(&$resultSet) {}

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
     * 分析表达式（可用于查询或者写入操作）
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
            $fields     =   $this->db->getFields($options['table']);
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
                    if(is_scalar($val) && empty($options['bind'][':'.$key])) {
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
        // 数据处理
        $data       =   $this->_read_data($resultSet[0]);
        // 数据对象赋值  
        $this->data         =   $data;
        return $this->data;
    }

    /**
     * 数据读取后的处理
     * @access protected
     * @param array $data 当前数据
     * @return array
     */
    protected function _read_data($data) {
        // 检查字段映射
        if(!empty($this->map)) {
            foreach ($this->map as $key=>$val){
                if(isset($data[$val])) {
                    $data[$key] =   $data[$val];
                    unset($data[$val]);
                }
            }
        }
        $this->_after_find($data);
        return $data;
    }
    // 数据读取成功后的回调方法
    protected function _after_find(&$result) {}

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
        // 过滤创建的数据
        $this->_create_filter($data);
        // 赋值当前数据对象
        $this->data =   $data;
        // 返回创建的数据以供其他调用
        return $data;
     }
    // 数据对象创建后的回调方法
    protected function _create_filter(&$data){}

    /**
     * 切换当前的数据库连接
     * @access public
     * @param integer $linkNum  连接序号
     * @param mixed $config  数据库连接信息
     * @return Model
     */
    public function db($linkNum='',$config=''){
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
            $fields =  $this->fields;
            unset($fields['_pk'],$fields['_type']);
            return $fields;
        }else{
            $fields =   Cache::get(md5($this->getTableName()));
            if(!$fields) {
                $fields =   $this->db->getFields($this->getTableName());
                $this->fields   =   array_keys($fields);
                foreach ($fields as $key=>$val){
                    // 记录字段类型
                    $type[$key]    =   $val['type'];
                    if($val['primary']) {
                        $this->fields['_pk'] = $key;
                    }
                }
                // 记录字段类型信息
                $this->fields['_type'] =  $type;
                Cache::set(md5($this->trueTableName),$this->fields);
                $fields     =   $this->fields;
            }else{
                $this->fields   =   $fields;
            }
            unset($fields['_pk'],$fields['_type']);
            return $fields;            
        }
    }

    /**
     * SQL查询
     * @access public
     * @param string $sql  SQL指令
     * @param array $bind  参数绑定
     * @return mixed
     */
    public function query($sql,$bind=[]) {
        $sql    =   strtr($sql,['__TABLE__'=>$this->getTableName(),'__PREFIX__'=>$this->tablePrefix]);
        return $this->db->query($sql,$bind);
    }

    /**
     * 执行SQL语句
     * @access public
     * @param string $sql  SQL指令
     * @param array $bind  参数绑定
     * @return false | integer
     */
    public function execute($sql,$bind=[]) {
        $sql    =   strtr($sql,['__TABLE__'=>$this->getTableName(),'__PREFIX__'=>$this->tablePrefix]);
        return $this->db->execute($sql,$bind);
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
            if(isset($this->scope['default'])) {
                // 默认的命名范围
                $options    =   $this->scope['default'];
            }else{
                return $this;
            }
        }elseif(is_string($scope)){ // 支持多个命名范围调用 用逗号分割
            $scopes         =   explode(',',$scope);
            $options        =   [];
            foreach ($scopes as $name){
                if(!isset($this->scope[$name])) continue;
                $options    =   array_merge($options,$this->scope[$name]);
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
     * 指定数据表
     * @access public
     * @param string $table 表名
     * @return Model
     */
    public function table($table){
        $this->options['table']     =   $table;
        return $this;
    }

    /**
     * 指定排序
     * @access public
     * @param string $order 排序
     * @return Model
     */
    public function order($order){
        $this->options['order']     =   $order;
        return $this;
    }

    /**
     * 指定group查询
     * @access public
     * @param string $group GROUP
     * @return Model
     */
    public function group($group){
        $this->options['group']     =   $group;
        return $this;
    }

    /**
     * 指定having查询
     * @access public
     * @param string $having having
     * @return Model
     */
    public function having($table){
        $this->options['having']     =   $having;
        return $this;
    }

    /**
     * 指定查询lock
     * @access public
     * @param boolean $lock 是否lock
     * @return Model
     */
    public function lock($lock=false){
        $this->options['lock']     =   $lock;
        return $this;
    }

    /**
     * 指定distinct查询
     * @access public
     * @param string $distinct 是否唯一
     * @return Model
     */
    public function distinct($distinct){
        $this->options['distinct']     =   $distinct;
        return $this;
    }

    /**
     * 指定数据表别名
     * @access public
     * @param string $alias 数据表别名
     * @return Model
     */
    public function alias($alias){
        $this->options['alias']     =   $alias;
        return $this;
    }

    /**
     * 指定写入过滤方法
     * @access public
     * @param string $filter 指定过滤方法
     * @return Model
     */
    public function filter($filter){
        $this->options['filter']     =   $filter;
        return $this;
    }

    /**
     * 指定参数绑定
     * @access public
     * @param array $bind 指定参数绑定
     * @return Model
     */
    public function bind($bind){
        $this->options['bind']     =   $bind;
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
     * 设置字段映射
     * @access public
     * @param array $map 映射
     * @return Model
     */
    public function map($map){
        $this->map =   $map;
        return $this;
    }
}