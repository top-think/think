<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zizhilong <104978@qq.com>
// +----------------------------------------------------------------------

namespace traits\model;

use think\Lang;

trait Bulk
{
    //插入缓存
    private $bulkAddCache = [];
    //插入缓存SQL语句预估长度
    private $bulkAddSize = 0;
    //更新缓存
    private $bulkSaveCache = [];
    private $bulkSaveSize = [];
    /**
     * 进行缓存插入操作
     * @access public
     * @param mixed $data 创建数据,如果传入true则更新所有缓存
     * @param string $type 状态
     * @return mixed
     */
    public function bulkAdd($data = '',$options=[])
    {
        // 如果没有数据传入
        if (empty($data)) {
            $this->error = Lang::get('_DATA_TYPE_INVALID_');
        }
        if($data !== true){
            //如不是提交操作,则进行数据处理和缓存累加
            $data = $this->_write_data($data,'insert');
            $this->bulkAddCache[] = $data;
            //统计所有字段内容长度
            foreach($data as $key=>$val)
            {
                $this->bulkAddSize += strlen($val);
            }
        }
        //得到默认的提交SQL数据长度,此设定根据数据库配置不同得到的性能结果会有差异
        //这个数字是否应该写死还是定义一个公共配置名称,需要核心组讨论
        //发现使用insertAll因为bind生成出来的语句比正常的要长不少.字段名+时间戳+序号,还有优化的余地
        $bulkSize = isset($options['bulkSize']) ? $options['bulkSize'] : 10000;
        //数据累计到一定量,则执行
        if($this->bulkAddSize < $bulkSize && ($data !== true || empty($this->bulkAddCache))){
            return true;
        }
        $options = $this->_parseOptions($options);
        // 写入数据到数据库
        $result = $this->db->insertAll($this->bulkAddCache, $options,false);
        //清空插入缓存
        $this->bulkAddCache = [];
        //清空长度计数
        $this->bulkAddSize = 0;
        //触发了一个实际插入操作,返回成功或失败
        return (false !== $result);
        //插入数据到缓存未导致任何触发
    }
    /**
     * 批量更新操作
     * @access public
     * @param mixed $data 创建数据,如果传入true则更新所有缓存
     * @param string $type 状态
     * @return mixed
     */
     public function bulkSave($data = '',$options=[])
     {
        // 如果没有数据传入
        if (empty($data)) {
            $this->error = Lang::get('_DATA_TYPE_INVALID_');
        }
        $options = $this->_parseOptions();
        $pk = $this->getPk();
        if($data !== true){
            //批量跟新必须是带有主键的数组
            if(!isset($data[$pk])){
                throw new Exception('no have pk field');
            }
            //由于批量更新存在在原值累加的情况,所以会有 array('id'=>1,'val+'=>10);所以不能先做字段检查
            foreach($data as $field=>$val)
            {
                //主键不需要处理
                if($field==$pk){
                    continue;
                }
                //对字段缓存初始化.每个字段的更新都会以独立的SQL进行.
                if(!isset($this->bulkSaveCache[$field])){
                    $this->bulkSaveCache[$field] = [];
                    $this->bulkSaveSize[$field]  = 0;
                }
                //如果同一个记录的同一字段被多次更新
                if(!isset($this->bulkSaveCache[$field][$data[$pk]])){
                	  //计算新增修改增加的SQL长度
                    $this->bulkSaveCache[$field][$data[$pk]] = $val;
                    $this->bulkSaveSize[$field] += strlen($val) + strlen($data[$pk]) * 2+14;
                    continue;
                }
                //如果是自增自减操作
                $operator = '=';
                if(in_array(substr($field,-1,1),['+','-'])){
                    $operator = substr($field,-1,1);
                }
                if($operator == '='){
                	//如果是直接赋值,不能重复写入,因为不确定那个值是正确的
                    throw new Exception('cannot repeat write in');
                }
                else{
                	//在原有修改基础上再进行自增或自减操作
                    $this->bulkSaveCache[$field][$data[$pk]] += ( $operator =='+' ? $val : - $val );
                }
            }
        }
        $bulkSize = isset($options['bulkSize']) ? $options['bulkSize'] : 10000;
        //扫描字段长度.看是否有需要先执行的
        foreach($this->bulkSaveSize as $field=>$size)
        {
            if($size < $bulkSize && ($size== 0 || $data !== true)){
                    continue;
            }
            //复制出插入数组
            $dataSet = $this->bulkSaveCache[$field];
            unset($this->bulkSaveCache[$field]);
            unset($this->bulkSaveSize[$field]);
              //默认运算符
            $operator='=';
            //自增或者自减
            if(in_array(substr($field,-1,1),['+','-']))
            {
                $operator = substr($field,-1,1);
                $field = substr($field,0,-1);
            }
            //批量字段更新
            $this->db->updateFieldAll($field,$pk,$dataSet,$operator,$options);
        }
     }
}
