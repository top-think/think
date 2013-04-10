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
//--------------------------
// ThinkPHP 引导文件
//--------------------------

// 加载基础文件
require dirname(__FILE__).'/base.php';
require CORE_PATH.'loader.php';

// 注册自动加载
Loader::register();
// 导入系统别名
Loader::loadMap(include THINK_PATH.'alias.php');
// 加载应用类
//require CORE_PATH.'app.php';
// 加载错误类
//require CORE_PATH.'error.php';

// 注册错误和异常处理机制
register_shutdown_function(['Think\Error','appShutdown']);
set_error_handler(['Think\Error','appError']);
set_exception_handler(['Think\Error','appException']);

// 导入系统惯例
Config::load(THINK_PATH.'convention.php');

// 日志初始化
Log::init(['type'=>Config::get('log_type'),'log_path'=> Config::get('log_path')]);

// 缓存初始化
Cache::connect(['type'=>'File','temp'=> CACHE_PATH]);

// 注册行为扩展
//Tag::add('content_filter','ContentReplace','Think');
//Tag::add('app_end','ShowPageTrace','Think');
Tag::add('view_template','LocationTemplate','Think');


// 启动session
if(!IS_CLI) {
    Session::init(['prefix'=>'think','auto_start'=>true]);
}
if(is_file(APP_PATH.'build.php')) { // 自动化创建脚本
    Create::build(include APP_PATH.'build.php');
}
// 执行应用
App::run();