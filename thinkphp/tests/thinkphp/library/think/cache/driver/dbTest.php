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
 * 数据库缓存驱动测试
 * @author    mahuan <mahuan@d1web.top>
 */

namespace tests\thinkphp\library\think\cache\driver;

class dbTest extends cacheTestCase
{
    private $_cacheInstance = null;

    /**
     * 基境缓存类型
     */
    protected function setUp()
    {
        //数据库缓存测试因为缺少数据库单元测试所以暂时跳过
        $this->markTestSkipped("暂时跳过测试。");
        \think\Cache::connect(array('type' => 'db', 'expire' => 2));
    }

    /**
     * @return DbCache
     */
    protected function getCacheInstance()
    {
        if (null === $this->_cacheInstance) {
            $this->_cacheInstance = new \think\cache\driver\Db();
        }
        return $this->_cacheInstance;
    }
}
