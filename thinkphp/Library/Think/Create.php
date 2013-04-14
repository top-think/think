<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

namespace Think;
class Create {
    static public function build($build) {
        // 锁定
        $lockfile	 =	 APP_PATH.'create.lock';
        if(is_writable($lockfile)) {
            return ;
        } else {
            if(!touch($lockfile)){
                header('Content-Type:text/html; charset=utf-8');
                exit('目录 [ '.APP_PATH.' ] 不可写！');
            }
        }
        foreach ($build as $module=>$list){
            if(!is_dir(APP_PATH.$module)) {// 创建模块目录
                mkdir(APP_PATH.$module);
            }
            // 创建配置文件和公共文件
            self::buildCommonFile($module);
            // 创建欢迎页面
            self::buildHelloController($module);

            // 创建子目录和文件
            foreach($list as $path=>$file){
                if(is_int($path)) {
                    // 生成文件
                    if(!is_file(APP_PATH.$module.'/'.$file)) {
                        file_put_contents(APP_PATH.$module.'/'.$file,"<?php\n");
                    }
                }else{
                    // 创建模块的子目录
                    if(!is_dir(APP_PATH.$module.'/'.$path)){
                        mkdir(APP_PATH.$module.'/'.$path);
                    }
                    foreach($file as $val){
                        switch($path) {
                        case 'Controller':// 控制器
                            $filename   =   ucwords($val).$path;
                            if(!is_file(APP_PATH.$module.'/'.$path.'/'.$filename.'.php')) {
                                file_put_contents(APP_PATH.$module.'/'.$path.'/'.$filename.'.php',"<?php\nnamespace {$module}\\{$path};\nclass {$filename} {\n}");
                            }
                            break;
                        case 'Model': // 模型
                            $filename   =   ucwords($val).$path;
                            if(!is_file(APP_PATH.$module.'/'.$path.'/'.$filename.'.php')) {
                                file_put_contents(APP_PATH.$module.'/'.$path.'/'.$filename.'.php',"<?php\nnamespace {$module}\\{$path};\nclass {$filename} extends \Think\Model{\n}");
                            }
                            break;
                        case 'View': // 视图
                            break;
                        default:
                            $filename   =   ucwords($val).$path;
                            if(!is_file(APP_PATH.$module.'/'.$path.'/'.$filename.'.php')) {
                                file_put_contents(APP_PATH.$module.'/'.$path.'/'.$filename.'.php',"<?php\nnamespace {$module}\\{$path};\nclass {$filename} {\n}");
                            }
                        }

                    }
                }
            }
        }
        // 解除锁定
        unlink($lockfile);
    }

    // 创建欢迎页面
    static public function buildHelloController($module) {
        if(!is_file(APP_PATH.$module.'/Controller/IndexController.php')) {
            $content    =   file_get_contents(THINK_PATH.'Tpl/default_index.tpl');
            $content    =   str_replace('{$module}',$module,$content);
            if(!is_dir(APP_PATH.$module.'/Controller')) {
                mkdir(APP_PATH.$module.'/Controller');
            }
            file_put_contents(APP_PATH.$module.'/Controller/IndexController.php',$content);
        }
    }

    // 创建模块公共文件
    static public function buildCommonFile($module){
        if(!is_file(APP_PATH.$module.'/common.php')) {
            file_put_contents(APP_PATH.$module.'/common.php',"<?php\n");
        }
        if(!is_file(APP_PATH.$module.'/config.php')) {
            file_put_contents(APP_PATH.$module.'/config.php',"<?php\nreturn [\n];");
        }
        if(!is_file(APP_PATH.$module.'/alias.php')) {
            file_put_contents(APP_PATH.$module.'/alias.php',"<?php\nreturn [\n];");
        }
    }
}