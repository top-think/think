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

namespace think\cache\driver;

/**
 * 文件类型缓存类
 * @author    liu21st <liu21st@gmail.com>
 */
class Simple
{

    protected $options = [
        'prefix' => '',
        'path'   => '',
    ];

    /**
     * 架构函数
     * @access public
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        if (substr($this->options['path'], -1) != '/') {
            $this->options['path'] .= '/';
        }

    }

    /**
     * 取得变量的存储文件名
     * @access private
     * @param string $name 缓存变量名
     * @return string
     */
    private function filename($name)
    {
        return $this->options['path'] . $this->options['prefix'] . md5($name) . '.php';
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        \think\Cache::$readTimes++;
        $filename = $this->filename($name);
        if (is_file($filename)) {
            return include $filename;
        } else {
            return false;
        }
    }

    /**
     * 写入缓存
     * @access   public
     *
     * @param string $name  缓存变量名
     * @param mixed  $value 存储数据
     *
     * @return bool
     * @internal param int $expire 有效时间 0为永久
     */
    public function set($name, $value)
    {
        \think\Cache::$writeTimes++;
        $filename = $this->filename($name);
        // 缓存数据
        $dir = dirname($filename);
        // 目录不存在则创建
        //if (!is_dir($dir))
        //  mkdir($dir,0755,true);
        return file_put_contents($filename, ("<?php\treturn " . var_export($value, true) . ";?>"));
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        return unlink($this->filename($name));
    }

    /**
     * 清除缓存
     * @access   public
     * @return bool
     * @internal param string $name 缓存变量名
     */
    public function clear()
    {
        $filename = $this->filename('*');
        array_map("unlink", glob($filename));
    }
}
