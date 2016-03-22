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
 * Db类测试
 */

namespace tests\thinkphp\library\think;

use \think\Db;

class dbTest extends \PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        Db::connect('mysql://root@127.0.0.1/test#utf8');
        Db::execute('show databases');
    }

}
