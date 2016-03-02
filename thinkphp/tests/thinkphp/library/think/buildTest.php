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

/**
 * build测试
 * @author    刘志淳 <chun@engineer.com>
 */

namespace tests\thinkphp\library\think;

use think\Build;

class buildTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $build = [
            // Test run directory
            '__dir__'  => ['runtime/cache', 'runtime/log', 'runtime/temp', 'runtime/template'],
            '__file__' => ['common.php'],

            // Test generation module
            'demo'     => [
                '__file__'   => ['common.php'],
                '__dir__'    => ['behavior', 'controller', 'model', 'view'],
                'controller' => ['Index', 'Test', 'UserType'],
                'model'      => ['User', 'UserType'],
                'view'       => ['index/index'],
            ],
        ];
        Build::run($build);

        self::build_file_exists($build);
    }

    protected static function build_file_exists($build)
    {
        foreach ($build as $module => $list) {
            if ('__dir__' == $module && '__file__' == $module) {
                foreach ($list as $file) {
                    $this->assertFileExists(APP_PATH . $file);
                }
            } else {
                self::build_file_exists($build[$module]);
            }
        }
    }
}
