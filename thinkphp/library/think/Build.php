<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think;

class Build
{
    public static function run($build)
    {
        // 锁定
        $lockfile = APP_PATH . 'build.lock';
        if (is_writable($lockfile)) {
            return;
        } elseif (!touch($lockfile)) {
            throw new Exception('应用目录[' . APP_PATH . ']不可写，目录无法自动生成！<BR>请手动生成项目目录~', 10006);
        }
        foreach ($build as $module => $list) {
            if ('__dir__' == $module) {
                // 创建目录列表
                self::buildDir($list);
            } elseif ('__file__' == $module) {
                // 创建文件列表
                self::buildFile($list);
            } else {
                // 创建模块
                self::buildModule($module, $list);
            }
        }
        // 解除锁定
        unlink($lockfile);
    }

    // 创建目录
    protected static function buildDir($list)
    {
        foreach ($list as $dir) {
            if (!is_dir(APP_PATH . $dir)) {
                // 创建目录
                mkdir(APP_PATH . $dir, 0777, true);
            }
        }
    }

    // 创建文件
    protected static function buildFile($list)
    {
        foreach ($list as $file) {
            if (!is_dir(APP_PATH . dirname($file))) {
                // 创建目录
                mkdir(APP_PATH . dirname($file), 0777, true);
            }
            if (!is_file(APP_PATH . $file)) {
                file_put_contents(APP_PATH . $file, 'php' == pathinfo($file, PATHINFO_EXTENSION) ? "<?php\n" : '');
            }
        }
    }

    // 创建模块
    protected static function buildModule($module, $list)
    {
        $module = APP_MULTI_MODULE ? $module : '';
        if (!is_dir(APP_PATH . $module)) {
            // 创建模块目录
            mkdir(APP_PATH . $module);
        }
        if (basename(RUNTIME_PATH) != $module) {
            // 创建配置文件和公共文件
            self::buildCommon($module);
            // 创建模块的默认页面
            self::buildHello($module);
        }
        // 创建子目录和文件
        foreach ($list as $path => $file) {
            $modulePath = APP_PATH . $module . DS;
            if ('__dir__' == $path) {
                // 生成子目录
                foreach ($file as $dir) {
                    if (!is_dir($modulePath . $dir)) {
                        // 创建目录
                        mkdir($modulePath . $dir, 0777, true);
                    }
                }
            } elseif ('__file__' == $path) {
                // 生成（空白）文件
                foreach ($file as $name) {
                    if (!is_file($modulePath . $name)) {
                        file_put_contents($modulePath . $name, 'php' == pathinfo($name, PATHINFO_EXTENSION) ? "<?php\n" : '');
                    }
                }
            } else {
                // 生成相关MVC文件
                foreach ($file as $val) {
                    $filename  = $modulePath . $path . DS . $val . EXT;
                    $namespace = APP_NAMESPACE . '\\' . ($module ? $module . '\\' : '') . $path;
                    switch ($path) {
                        case CONTROLLER_LAYER:    // 控制器
                            $content = "<?php\nnamespace {$namespace};\n\nclass {$val} {\n\n}";
                            break;
                        case MODEL_LAYER:    // 模型
                            $content = "<?php\nnamespace {$namespace};\n\nclass {$val} extends \Think\Model{\n\n}";
                            break;
                        case VIEW_LAYER:    // 视图
                            $filename = $modulePath . $path . DS . $val . '.html';
                            if (!is_dir(dirname($filename))) {
                                // 创建目录
                                mkdir(dirname($filename), 0777, true);
                            }
                            $content = '';
                            break;
                        default:
                            // 其他文件
                            $content = "<?php\nnamespace {$namespace};\n\nclass {$val} {\n\n}";
                    }

                    if (!is_file($filename)) {
                        file_put_contents($filename, $content);
                    }
                }
            }
        }
    }

    // 创建欢迎页面
    protected static function buildHello($module)
    {
        $filename = APP_PATH . ($module ? $module . DS : '') . CONTROLLER_LAYER . DS . Config::get('default_controller') . EXT;
        if (!is_file($filename)) {
            $content = file_get_contents(THINK_PATH . 'tpl' . DS . 'default_index.tpl');
            $content = str_replace(['{$app}', '{$module}', '{layer}'], [APP_NAMESPACE, $module ? $module . '\\' : '', CONTROLLER_LAYER], $content);
            if (!is_dir(dirname($filename))) {
                mkdir(dirname($filename), 0777, true);
            }
            file_put_contents($filename, $content);
        }
    }

    // 创建模块公共文件
    protected static function buildCommon($module)
    {
        $filename = APP_PATH . ($module ? $module . DS : '') . 'config.php';
        if (!is_file($filename)) {
            file_put_contents($filename, "<?php\nreturn [\n\n];");
        }
    }
}
