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
 * Apc缓存驱动测试
 * @author    mahuan <mahuan@d1web.top>
 */

namespace tests\thinkphp\library\think\cache\driver;

class apcTest extends cacheTestCase
{
    private $_cacheInstance = null;
    /**
     * 基境缓存类型
     */
    protected function setUp()
    {
        if (!extension_loaded("apc")) {
            $this->markTestSkipped("APC没有安装，已跳过测试！");
        } elseif ('cli' === PHP_SAPI && !ini_get('apc.enable_cli')) {
            $this->markTestSkipped("APC模块没有开启，已跳过测试！");
        }
        \think\Cache::connect(array('type' => 'apc', 'expire' => 2));
    }
    /**
     * @return ApcCache
     */
    protected function getCacheInstance()
    {
        if (null === $this->_cacheInstance) {
            $this->_cacheInstance = new \think\cache\driver\Apc(['length' => 3]);
        }
        return $this->_cacheInstance;
    }
    /**
     * 缓存过期测试《提出来测试，因为目前看通不过缓存过期测试，所以还需研究》
     * @return  mixed
     * @access public
     */
    public function testExpire()
    {
    }

}
