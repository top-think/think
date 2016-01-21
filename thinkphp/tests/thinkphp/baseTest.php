<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Haotong Lin <lofanmi@gmail.com>
// +----------------------------------------------------------------------

/**
 * 保证运行环境正常
 */
class baseTest extends \PHPUnit_Framework_TestCase
{
    public function testConstants()
    {
        $this->assertNotEmpty(START_TIME);
        $this->assertNotEmpty(START_MEM);
        $this->assertNotEmpty(THINK_VERSION);
        $this->assertNotEmpty(DS);
        $this->assertNotEmpty(THINK_PATH);
        $this->assertNotEmpty(LIB_PATH);
        $this->assertNotEmpty(EXTEND_PATH);
        $this->assertNotEmpty(MODE_PATH);
        $this->assertNotEmpty(CORE_PATH);
        $this->assertNotEmpty(ORG_PATH);
        $this->assertNotEmpty(TRAIT_PATH);
        $this->assertNotEmpty(APP_PATH);
        $this->assertNotEmpty(APP_NAMESPACE);
        $this->assertNotEmpty(COMMON_MODULE);
        $this->assertNotEmpty(RUNTIME_PATH);
        $this->assertNotEmpty(DATA_PATH);
        $this->assertNotEmpty(LOG_PATH);
        $this->assertNotEmpty(CACHE_PATH);
        $this->assertNotEmpty(TEMP_PATH);
        $this->assertNotEmpty(VENDOR_PATH);
        $this->assertNotEmpty(EXT);
        $this->assertNotEmpty(MODEL_LAYER);
        $this->assertNotEmpty(VIEW_LAYER);
        $this->assertNotEmpty(CONTROLLER_LAYER);
        $this->assertTrue(is_bool(APP_DEBUG));
        $this->assertTrue(is_bool(APP_HOOK));
        $this->assertNotEmpty(ENV_PREFIX);
        $this->assertTrue(is_bool(IS_API));
        $this->assertTrue(is_bool(APP_AUTO_BUILD));
        $this->assertNotEmpty(APP_MODE);
        $this->assertTrue(!is_null(IS_CGI));
        $this->assertTrue(!is_null(IS_WIN));
        $this->assertTrue(!is_null(IS_CLI));
        $this->assertTrue(is_bool(IS_AJAX));
        $this->assertNotEmpty(NOW_TIME);
        $this->assertNotEmpty(REQUEST_METHOD);
        $this->assertTrue(is_bool(IS_GET));
        $this->assertTrue(is_bool(IS_POST));
        $this->assertTrue(is_bool(IS_PUT));
        $this->assertTrue(is_bool(IS_DELETE));
    }
}
