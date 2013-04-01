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
namespace Think\Log\Driver;

class File {
    
    // 日志记录方式
    const SYSTEM    = 0;
    const MAIL      = 1;
    const FILE      = 3;
    const SAPI      = 4;

    // 日志信息
    protected $log     =  [];
    protected $config  =   array(
        'log_time_format'   =>  '[ c ]',
        'log_file_size'     =>  2097152,
        'log_allow_level'   =>  array('ERR','NOTIC','DEBUG','SQL','INFO'),
    );

    public function __construct($config=[]){
        $this->config   =   array_merge($this->config,$config);
    }

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $record  是否强制记录
     * @return void
     */
    public function record($message,$level='INFO',$record=false) {
        if($record || false !== array_search($level,$this->config['log_allow_level'])) {
            $this->log[$level][] =   "{$level}: {$message}\r\n";
        }
    }

    /**
     * 获取内存中的日志信息
     * @access public
     * @param string $level  日志级别
     * @return array
     */
    public function getLog($level=''){
        return $level?$this->log[$level]:$this->log;
    }

    /**
     * 日志保存
     * @access public
     * @param string $destination  写入目标
     * @param string $level 保存的日志级别
     * @return void
     */
    public function save($destination='',$level='') {
        $log    =   $level?$this->log[$level]:$this->log;
        if(empty($log)) return ;
        if(empty($destination))
            $destination = $this->config['log_path'].date('y_m_d').'.log';

        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if(is_file($destination) && floor($this->config['log_file_size']) <= filesize($destination) )
              rename($destination,dirname($destination).'/'.time().'-'.basename($destination));

        $message    =   date($this->config['log_time_format']).' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['REQUEST_URI']."\r\n";
        if($level) {
            $message    .=   implode('',$log)."\r\n";
            $this->log[$level]  =   [];
        }else{
            foreach($log as $info){
                $message    .=   implode('',$info)."\r\n";
            }
            $this->log = [];
        }
        error_log($message, 3,$destination);
        //clearstatcache();
    }

    /**
     * 日志直接写入
     * @access public
     * @param string $log 日志信息
     * @param string $level  日志级别
     * @param string $destination  写入目标
     * @return void
     */
    public function write($log,$level,$destination='') {
        $now = date($this->config['log_time_format']);
        if(empty($destination))
            $destination = $this->config['log_path'].date('y_m_d').'.log';
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if(is_file($destination) && floor($this->config['log_file_size']) <= filesize($destination) )
              rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        error_log("{$now} {$level}: {$log}\r\n", 3,$destination);
    }
}