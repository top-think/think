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
class File
{

    protected $options = [
        'expire'        => 0,
        'cache_subdir'  => false,
        'path_level'    => 1,
        'prefix'        => '',
        'length'        => 0,
        'path'          => '',
        'data_compress' => false,
    ];

    /**
     * 架构函数
     * @access public
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        if (substr($this->options['path'], -1) != '/') {
            $this->options['path'] .= '/';
        }
        $this->init();
    }

    /**
     * 初始化检查
     * @access private
     * @return boolen
     */
    private function init()
    {
        // 创建项目缓存目录
        if (!is_dir($this->options['path'])) {
            if (!mkdir($this->options['path'], 0755, true)) {
                return false;
            }
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
        $name = md5($name);
        if ($this->options['cache_subdir']) {
            // 使用子目录
            $dir = '';
            $len = $this->options['path_level'];
            for ($i = 0; $i < $len; $i++) {
                $dir .= $name{$i} . '/';
            }
            if (!is_dir($this->options['path'] . $dir)) {
                mkdir($this->options['path'] . $dir, 0755, true);
            }
            $filename = $dir . $this->options['prefix'] . $name . '.php';
        } else {
            $filename = $this->options['prefix'] . $name . '.php';
        }
        return $this->options['path'] . $filename;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        $filename = $this->filename($name);
        if (!is_file($filename)) {
            return false;
        }
        \think\Cache::$readTimes++;
        $content = file_get_contents($filename);
        if (false !== $content) {
            $expire = (int) substr($content, 8, 12);
            if (0 != $expire && time() > filemtime($filename) + $expire) {
                //缓存过期删除缓存文件
                unlink($filename);
                return false;
            }
            $content = substr($content, 20, -3);
            if ($this->options['data_compress'] && function_exists('gzcompress')) {
                //启用数据压缩
                $content = gzuncompress($content);
            }
            $content = unserialize($content);
            return $content;
        } else {
            return false;
        }
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间 0为永久
     * @return boolen
     */
    public function set($name, $value, $expire = null)
    {
        \think\Cache::$writeTimes++;
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $filename = $this->filename($name);
        $data     = serialize($value);
        if ($this->options['data_compress'] && function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data, 3);
        }
        $data   = "<?php\n//" . sprintf('%012d', $expire) . $data . "\n?>";
        $result = file_put_contents($filename, $data);
        if ($result) {
            if ($this->options['length'] > 0) {
                // 记录缓存队列
                $queue_file = dirname($filename) . '/__info__.php';
                $queue      = unserialize(file_get_contents($queue_file));
                if (!$queue) {
                    $queue = [];
                }
                if (false === array_search($name, $queue)) {
                    array_push($queue, $name);
                }

                if (count($queue) > $this->options['length']) {
                    // 出列
                    $key = array_shift($queue);
                    // 删除缓存
                    unlink($this->filename($key));
                }
                file_put_contents($queue_file, serialize($queue));
            }
            clearstatcache();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name)
    {
        return unlink($this->filename($name));
    }

    /**
     * 清除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function clear()
    {
        $path = $this->options['temp'];
        if ($dir = opendir($path)) {
            while ($file = readdir($dir)) {
                $check = is_dir($file);
                if (!$check) {
                    unlink($path . $file);
                }

            }
            closedir($dir);
            return true;
        }
    }
}
