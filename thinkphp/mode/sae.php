<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: luofei614 <weibo.com/luofei614>
// +----------------------------------------------------------------------
      
/**
 * ThinkPHP SAE应用模式定义文件
 */
$st =   new SaeStorage();
return [
    // 配置文件
    'config'    =>  [
        /* 数据库设置 */
        'database'    =>  [
            'type'              =>  'mysql',     // 数据库类型
            'dsn'               =>  '', // 
            'hostname'          =>  SAE_MYSQL_HOST_M.','.SAE_MYSQL_HOST_S, // 服务器地址
            'database'          =>  SAE_MYSQL_DB,          // 数据库名
            'username'          =>  SAE_MYSQL_USER,      // 用户名
            'password'          =>  SAE_MYSQL_PASS,          // 密码
            'hostport'          =>  SAE_MYSQL_PORT,        // 端口               
            'params'            =>  [], // 数据库连接参数        
            'charset'           =>  'utf8',      // 数据库编码默认采用utf8  
            'prefix'            =>  '',    // 数据库表前缀
            'debug'             =>  false, // 数据库调试模式
            'deploy'            =>  1, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'rw_separate'       =>  true,       // 数据库读写是否分离 主从式有效
            'master_num'        =>  1, // 读写分离后 主服务器数量
            'slave_no'          =>  '', // 指定从服务器序号
        ],    
        'log_type'          =>  'Sae',
        'data_cache_type'   =>  'Memcachesae',
        'check_app_dir'     =>  false,
        'file_upload_type'  =>  'Sae',
    ],

    // 别名定义
    'alias'     =>  [
        'think\Log'                     => CORE_PATH . 'log'.EXT,
        'think\log\driver\File'         => CORE_PATH . 'log/driver/file'.EXT,
        'think\log\driver\Sae'          => CORE_PATH . 'log/driver/sae'.EXT,        
        'think\Exception'               => CORE_PATH . 'exception'.EXT,
        'think\Model'                   => CORE_PATH . 'model'.EXT,
        'think\Db'                      => CORE_PATH . 'db'.EXT,
        'think\Template'                => CORE_PATH . 'template'.EXT,
        'think\Cache'                   => CORE_PATH . 'cache'.EXT,
        'think\cache\driver\File'       => CORE_PATH . 'cache/driver/file'.EXT,
        'think\cache\driver\Memcache'   => CORE_PATH . 'cache/driver/memcache'.EXT,        
    ],

];
