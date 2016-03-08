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
 * shmcache缓存驱动测试
 * @author    lloydzhou <lloydzhou@qq.com>
 */

namespace tests\thinkphp\library\think\cache\driver;

class shmcacheTest extends cacheTestCase
{
    private $_cacheInstance = null;
    /**
     * 基境缓存类型
     */
    protected function setUp()
    {
        \think\Cache::connect(array('type' => 'shmcache', 'expire' => 2));
    }
    /**
     * @return shmCache
     */
    protected function getCacheInstance()
    {
        if (null === $this->_cacheInstance) {
            $this->_cacheInstance = new \think\cache\driver\Shmcache(['length' => 3]);
        }
        return $this->_cacheInstance;
    }
}
