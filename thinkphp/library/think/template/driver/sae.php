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

namespace think\template\driver;

use think\Exception;

class Sae
{
    // mc 对象
    private $mc;
    // 编译缓存内容
    private $contents = [];

    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
        if (!function_exists('memcache_init')) {
            throw new Exception('请在SAE平台上运行代码。');
        }
        $this->mc = @memcache_init();
        if (!$this->mc) {
            throw new Exception('您未开通Memcache服务，请在SAE管理平台初始化Memcache服务');
        }
    }

    // 写入编译缓存
    public function write($cacheFile, $content)
    {
        // 添加写入时间
        $content = time() . $content;
        if (!$this->mc->set($cacheFile, $content, MEMCACHE_COMPRESSED, 0)) {
            throw new Exception('sae mc write error :' . $cacheFile);
        } else {
            $this->contents[$cacheFile] = $content;
            return true;
        }
    }

    // 读取编译编译
    public function read($cacheFile, $vars)
    {
        if (!is_null($vars)) {
            extract($vars, EXTR_OVERWRITE);
        }
        eval('?>' . $this->get($cacheFile, 'content'));
    }

    // 检查编译缓存是否有效
    public function check($template, $cacheFile, $cacheTime)
    {
        $mtime = $this->get($cacheFile, 'mtime');
        if (!$this->get($cacheFile, 'content') || (is_file($template) && filemtime($template) > $mtime)) {
            // 模板文件如果有更新则缓存需要更新
            return false;
        }if (0 != $cacheTime && time() > $mtime + $cacheTime) {
            // 缓存是否在有效期
            return false;
        } else {
            return true;
        }
    }

    /**
     * 读取文件信息
     * @access private
     * @param string $filename  文件名
     * @param string $name  信息名 mtime或者content
     * @return boolean
     */
    private function get($filename, $name)
    {
        if (!isset($this->contents[$filename])) {
            $this->contents[$filename] = $this->mc->get($filename);
        }
        $content = $this->contents[$filename];

        if (false === $content) {
            return false;
        }
        $info = array(
            'mtime'   => substr($content, 0, 10),
            'content' => substr($content, 10),
        );
        return $info[$name];
    }
}
