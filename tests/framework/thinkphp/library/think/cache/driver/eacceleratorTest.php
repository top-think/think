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
 * eaccelerator缓存驱动测试
 * @author    mahuan <mahuan@d1web.top>
 */
namespace tests\framework\thinkphp\library\think\cache\driver;


class eacceleratorTest extends CacheTestCase
{
    private $_cacheInstance = null;

    /**
     * 基境缓存类型
     */
    protected function setUp()
    {
        S(array('type'=>'eaccelerator','expire'=>2));
    }

    /**
     * @return eacceleratorCache
     */
    protected function getCacheInstance()
    {
        if(PHP_VERSION > 5.4){
            $this->markTestSkipped("eaccelerator暂且不能使用在5.5版本以上");
        }
        if (!extension_loaded("eaccelerator")) {
            $this->markTestSkipped("eacceleratorTest没有安装，已跳过测试！");
        }
        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new \think\cache\driver\Eaccelerator();
        }
        return $this->_cacheInstance;
    }
}
