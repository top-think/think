<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * Apc缓存驱动测试
 * @author    mahuan <mahuan@d1web.top>
 */
namespace tests\framework\thinkphp\library\think\cache\driver;


class apcTest extends CacheTestCase
{
    private $_cacheInstance = null;
    /**
     * @return ApcCache
     */
    protected function getCacheInstance()
    {
        if (!extension_loaded("apc")) {
            $this->markTestSkipped("APC没有安装，已跳过测试！");
        } elseif ('cli' === PHP_SAPI && !ini_get('apc.enable_cli')) {
            $this->markTestSkipped("APC模块没有开启，已跳过测试！");
        }

        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new \think\cache\driver\Apc();
        }

        return $this->_cacheInstance;
    }
}
