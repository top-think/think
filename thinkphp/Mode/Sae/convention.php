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
 * SAE模式惯例配置文件
 * 该文件请不要修改，如果要覆盖惯例配置的值，可在应用配置文件中设定和惯例不符的配置项
 * 配置名称大小写任意，系统会统一转换成小写
 * 所有配置参数都可以在生效前动态改变
 */
defined('THINK_PATH') or exit();
$st =   new SaeStorage();
return array(
    //SAE下固定mysql配置
    'DB_TYPE'           =>  'mysql',     // 数据库类型
    'DB_DEPLOY_TYPE'    =>  1,
    'DB_RW_SEPARATE'    =>  true,
    'DB_HOST'           =>  SAE_MYSQL_HOST_M.','.SAE_MYSQL_HOST_S, // 服务器地址
    'DB_NAME'           =>  SAE_MYSQL_DB,        // 数据库名
    'DB_USER'           =>  SAE_MYSQL_USER,    // 用户名
    'DB_PWD'            =>  SAE_MYSQL_PASS,         // 密码
    'DB_PORT'           =>  SAE_MYSQL_PORT,        // 端口
    //更改模板替换变量，让普通能在所有平台下显示
    'TMPL_PARSE_STRING' =>  array(
        // __PUBLIC__/upload  -->  /Public/upload -->http://appname-public.stor.sinaapp.com/upload
        '/Public/upload'    =>  $st->getUrl('public','upload')
    ),
    'LOG_TYPE'          =>  'Sae',
    'DATA_CACHE_TYPE'   =>  'Memcachesae',
    'CHECK_APP_DIR'     =>  false,
    'FILE_UPLOAD_TYPE'  =>  'Sae',
);
