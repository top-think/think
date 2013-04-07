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
    static public function build($install) {
        // 锁定
        $lockfile	 =	 APP_PATH.'install.lock';
        if(is_writable($lockfile)) {
            return ;
        } else {
            touch($lockfile);
        }
        foreach ($install as $module=>$list){
            if(!is_dir(APP_PATH.$module)) {// 创建模块目录
                mkdir(APP_PATH.$module);
            }
            foreach($list as $path=>$file){
                if(is_int($path)) {
                    // 生成文件
                    if(!is_file(APP_PATH.$module.'/'.$file)) {
                        file_put_contents(APP_PATH.$module.'/'.$file,"<?php\nreturn [\n];\n?>");
                    }
                }else{
                    if(!is_dir(APP_PATH.$module.'/'.$path)){
                        mkdir(APP_PATH.$module.'/'.$path);
                    }
                    foreach($file as $val){
                        switch($path) {
                        case 'Controller':
                            $filename   =   ucwords($val).$path;
                            if(!is_file(APP_PATH.$module.'/'.$path.'/'.$filename.'.php')) {
                                file_put_contents(APP_PATH.$module.'/'.$path.'/'.$filename.'.php',"<?php\nnamespace {$module}\\{$path}\nclass {$filename} {\n}");
                            }
                            break;
                        case 'Model':
                            $filename   =   ucwords($val).$path;
                            if(!is_file(APP_PATH.$module.'/'.$path.'/'.$filename.'.php')) {
                                file_put_contents(APP_PATH.$module.'/'.$path.'/'.$filename.'.php',"<?php\nnamespace {$module}\\{$path}\nclass {$filename} extends Model{\n}");
                            }
                            break;
                        case 'View':
                            break;
                        default:
                            $filename   =   ucwords($val).$path;
                            if(!is_file(APP_PATH.$module.'/'.$path.'/'.$filename.'.php')) {
                                file_put_contents(APP_PATH.$module.'/'.$path.'/'.$filename.'.php',"<?php\nnamespace {$module}\\{$path}\nclass {$filename} {\n}");
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
    static public function buildHelloControll() {
        $content = file_get_contents(THINK_PATH.'Tpl/default_index.tpl');
        file_put_contents(LIB_PATH.'Action/IndexAction.class.php',$content);
    }
}