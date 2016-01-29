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

namespace think\cache\driver;

use think\Cache;

/**
 * Secache缓存驱动
 * @author    liu21st <liu21st@gmail.com>
 */
class Secache
{

    protected $handler = null;
    protected $options = [
        'project' => '',
        'path'    => '',
        'expire'  => 0,
        'prefix'  => '',
        'length'  => 0,
    ];

    /**
     * 架构函数
     * @param array $options 缓存参数
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

        $this->handler = new SecacheClient;
        $this->handler->workat($this->options['path'] . $this->options['project']);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        Cache::$readTimes++;
        $name = $this->options['prefix'] . $name;
        $key  = md5($name);
        $this->handler->fetch($key, $return);
        return $return;
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value)
    {
        Cache::$writeTimes++;
        $name = $this->options['prefix'] . $name;
        $key  = md5($name);
        if ($result = $this->handler->store($key, $value)) {
            if ($this->options['length'] > 0) {
                // 记录缓存队列
                $queue = $this->handler->fetch(md5('__info__'));
                if (!$queue) {
                    $queue = [];
                }
                if (false === array_search($key, $queue)) {
                    array_push($queue, $key);
                }

                if (count($queue) > $this->options['length']) {
                    // 出列
                    $key = array_shift($queue);
                    // 删除缓存
                    $this->handler->delete($key);
                }
                $this->handler->store(md5('__info__'), $queue);
            }
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name)
    {
        $name = $this->options['prefix'] . $name;
        $key  = md5($name);
        return $this->handler->delete($key);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear()
    {
        return $this->handler->_format(true);
    }

}

if (!defined('SECACHE_SIZE')) {
    define('SECACHE_SIZE', '15M');
}
class SecacheClient
{

    public $idx_node_size    = 40;
    public $data_base_pos    = 262588; //40+20+24*16+16*16*16*16*4;
    public $schema_item_size = 24;
    public $header_padding   = 20; //保留空间 放置php标记防止下载
    public $info_size        = 20; //保留空间 4+16 maxsize|ver

    //40起 添加20字节保留区域
    public $idx_seq_pos   = 40; //id 计数器节点地址
    public $dfile_cur_pos = 44; //id 计数器节点地址
    public $idx_free_pos  = 48; //id 空闲链表入口地址

    public $idx_base_pos  = 444; //40+20+24*16
    public $min_size      = 10240; //10M最小值
    public $schema_struct = array('size', 'free', 'lru_head', 'lru_tail', 'hits', 'miss');
    public $ver           = '$Rev: 3 $';
    public $name          = '系统默认缓存(文件型)';

    public function workat($file)
    {

        $this->_file       = $file . '.php';
        $this->_bsize_list = array(
            512        => 10,
            3 << 10    => 10,
            8 << 10    => 10,
            20 << 10   => 4,
            30 << 10   => 2,
            50 << 10   => 2,
            80 << 10   => 2,
            96 << 10   => 2,
            128 << 10  => 2,
            224 << 10  => 2,
            256 << 10  => 2,
            512 << 10  => 1,
            1024 << 10 => 1,
        );

        $this->_node_struct = array(
            'next'      => array(0, 'V'),
            'prev'      => array(4, 'V'),
            'data'      => array(8, 'V'),
            'size'      => array(12, 'V'),
            'lru_right' => array(16, 'V'),
            'lru_left'  => array(20, 'V'),
            'key'       => array(24, 'H*'),
        );

        if (!file_exists($this->_file)) {
            $this->create();
        } else {
            $this->_rs = fopen($this->_file, 'rb+') || $this->triggerError('Can\'t open the cachefile: ' . realpath($this->_file), E_USER_ERROR);
            $this->_seek($this->header_padding);
            $info = unpack('V1max_size/a*ver', fread($this->_rs, $this->info_size));
            if ($info['ver'] != $this->ver) {
                $this->_format(true);
            } else {
                $this->max_size = $info['max_size'];
            }
        }

        $this->idx_node_base    = $this->data_base_pos + $this->max_size;
        $this->_block_size_list = array_keys($this->_bsize_list);
        sort($this->_block_size_list);
        return true;
    }

    public function create()
    {
        $this->_rs = fopen($this->_file, 'wb+') || $this->triggerError('Can\'t open the cachefile: ' . realpath($this->_file), E_USER_ERROR);
        fseek($this->_rs, 0);
        fputs($this->_rs, '<' . '?php exit()?' . '>');
        return $this->_format();
    }

    public function _puts($offset, $data)
    {
        if ($offset < $this->max_size * 1.5) {
            $this->_seek($offset);
            return fputs($this->_rs, $data);
        } else {
            $this->triggerError('Offset over quota:' . $offset, E_USER_ERROR);
        }
    }

    public function _seek($offset)
    {
        return fseek($this->_rs, $offset);
    }

    public function clear()
    {
        return $this->_format(true);
    }

    public function fetch($key, &$return)
    {
        $locked = false;
        if ($this->lock(false)) {
            $locked = true;
        }

        if ($this->search($key, $offset)) {
            $info      = $this->_get_node($offset);
            $schema_id = $this->_get_size_schema_id($info['size']);
            if (false === $schema_id) {
                if ($locked) {
                    $this->unlock();
                }

                return false;
            }

            $this->_seek($info['data']);
            $data   = fread($this->_rs, $info['size']);
            $return = unserialize($data);

            if (false === $return) {
                if ($locked) {
                    $this->unlock();
                }

                return false;
            }

            if ($locked) {
                $this->_lru_push($schema_id, $info['offset']);
                $this->_set_schema($schema_id, 'hits', $this->_get_schema($schema_id, 'hits') + 1);
                return $this->unlock();
            } else {
                return true;
            }
        } else {
            if ($locked) {
                $this->unlock();
            }

            return false;
        }
    }

    /**
     * lock
     * 如果flock不管用，请继承本类，并重载此方法
     *
     * @param mixed $is_block 是否阻塞
     * @access public
     * @return bool
     */
    public function lock($is_block, $whatever = false)
    {
        return flock($this->_rs, $is_block ? LOCK_EX : LOCK_EX + LOCK_NB);
    }

    /**
     * unlock
     * 如果flock不管用，请继承本类，并重载此方法
     *
     * @access public
     * @return bool
     */
    public function unlock()
    {
        return flock($this->_rs, LOCK_UN);
    }

    public function delete($key, $pos = false)
    {
        if ($pos || $this->search($key, $pos)) {
            if ($info = $this->_get_node($pos)) {
                //删除data区域
                if ($info['prev']) {
                    $this->_set_node($info['prev'], 'next', $info['next']);
                    $this->_set_node($info['next'], 'prev', $info['prev']);
                } else {
                    //改入口位置
                    $this->_set_node($info['next'], 'prev', 0);
                    $this->_set_node_root($key, $info['next']);
                }
                $this->_free_dspace($info['size'], $info['data']);
                $this->_lru_delete($info);
                $this->_free_node($pos);
                return $info['prev'];
            }
        }
        return false;
    }

    public function store($key, $value)
    {

        if ($this->lock(true)) {
            //save data
            $data = serialize($value);
            $size = strlen($data);

            //get list_idx
            $has_key   = $this->search($key, $list_idx_offset);
            $schema_id = $this->_get_size_schema_id($size);
            if (false === $schema_id) {
                $this->unlock();
                return false;
            }
            if ($has_key) {
                $hdseq = $list_idx_offset;

                $info = $this->_get_node($hdseq);
                if ($this->_get_size_schema_id($info['size']) == $schema_id) {
                    $dataoffset = $info['data'];
                } else {
                    //破掉原有lru
                    $this->_lru_delete($info);
                    if (!($dataoffset = $this->_dalloc($schema_id))) {
                        $this->unlock();
                        return false;
                    }
                    $this->_free_dspace($info['size'], $info['data']);
                    $this->_set_node($hdseq, 'lru_left', 0);
                    $this->_set_node($hdseq, 'lru_right', 0);
                }

                $this->_set_node($hdseq, 'size', $size);
                $this->_set_node($hdseq, 'data', $dataoffset);
            } else {

                if (!($dataoffset = $this->_dalloc($schema_id))) {
                    $this->unlock();
                    return false;
                }
                $hdseq = $this->_alloc_idx(array(
                    'next'      => 0,
                    'prev'      => $list_idx_offset,
                    'data'      => $dataoffset,
                    'size'      => $size,
                    'lru_right' => 0,
                    'lru_left'  => 0,
                    'key'       => $key,
                ));

                if ($list_idx_offset > 0) {
                    $this->_set_node($list_idx_offset, 'next', $hdseq);
                } else {
                    $this->_set_node_root($key, $hdseq);
                }
            }

            if ($dataoffset > $this->max_size) {
                $this->triggerError('alloc datasize:' . $dataoffset, E_USER_WARNING);
                return false;
            }
            $this->_puts($dataoffset, $data);

            $this->_set_schema($schema_id, 'miss', $this->_get_schema($schema_id, 'miss') + 1);

            $this->_lru_push($schema_id, $hdseq);
            $this->unlock();
            return true;
        } else {
            $this->triggerError("Couldn't lock the file !", E_USER_WARNING);
            return false;
        }

    }

    /**
     * search
     * 查找指定的key
     * 如果找到节点则$pos=节点本身 返回true
     * 否则 $pos=树的末端 返回false
     *
     * @param mixed $key
     * @access public
     * @return mixed
     */
    public function search($key, &$pos)
    {
        return $this->_get_pos_by_key($this->_get_node_root($key), $key, $pos);
    }

    public function _get_size_schema_id($size)
    {
        foreach ($this->_block_size_list as $k => $block_size) {
            if ($size <= $block_size) {
                return $k;
            }
        }
        return false;
    }

    public function _parse_str_size($str_size, $default)
    {
        if (preg_match('/^([0-9]+)\s*([gmk]|)$/i', $str_size, $match)) {
            switch (strtolower($match[2])) {
                case 'g':
                    if ($match[1] > 1) {
                        $this->triggerError('Max cache size 1G', E_USER_ERROR);
                    }
                    $size = $match[1] << 30;
                    break;
                case 'm':
                    $size = $match[1] << 20;
                    break;
                case 'k':
                    $size = $match[1] << 10;
                    break;
                default:
                    $size = $match[1];
            }
            if ($size <= 0) {
                $this->triggerError('Error cache size ' . $this->max_size, E_USER_ERROR);
                return false;
            } elseif ($size < 10485760) {
                return 10485760;
            } else {
                return $size;
            }
        } else {
            return $default;
        }
    }

    public function _format($truncate = false)
    {
        if ($this->lock(true, true)) {

            if ($truncate) {
                $this->_seek(0);
                ftruncate($this->_rs, $this->idx_node_base);
            }

            $this->max_size = $this->_parse_str_size(SECACHE_SIZE, 15728640); //default:15m
            $this->_puts($this->header_padding, pack('V1a*', $this->max_size, $this->ver));

            ksort($this->_bsize_list);
            $ds_offset = $this->data_base_pos;
            $i         = 0;
            foreach ($this->_bsize_list as $size => $count) {

                //将预分配的空间注册到free链表里
                $count *= min(3, floor($this->max_size / 10485760));
                $next_free_node = 0;
                for ($j = 0; $j < $count; $j++) {
                    $this->_puts($ds_offset, pack('V', $next_free_node));
                    $next_free_node = $ds_offset;
                    $ds_offset += intval($size);
                }

                $code = pack(str_repeat('V1', count($this->schema_struct)), $size, $next_free_node, 0, 0, 0, 0);

                $this->_puts(60 + $i * $this->schema_item_size, $code);
                $i++;
            }
            $this->_set_dcur_pos($ds_offset);

            $this->_puts($this->idx_base_pos, str_repeat("\0", 262144));
            $this->_puts($this->idx_seq_pos, pack('V', 1));
            $this->unlock();
            return true;
        } else {
            $this->triggerError("Couldn't lock the file !", E_USER_ERROR);
            return false;
        }
    }

    public function _get_node_root($key)
    {
        $this->_seek(hexdec(substr($key, 0, 4)) * 4 + $this->idx_base_pos);
        $a              = fread($this->_rs, 4);
        list(, $offset) = unpack('V', $a);
        return $offset;
    }

    public function _set_node_root($key, $value)
    {
        return $this->_puts(hexdec(substr($key, 0, 4)) * 4 + $this->idx_base_pos, pack('V', $value));
    }

    public function _set_node($pos, $key, $value)
    {

        if (!$pos) {
            return false;
        }

        if (isset($this->_node_struct[$key])) {
            return $this->_puts($pos * $this->idx_node_size + $this->idx_node_base + $this->_node_struct[$key][0], pack($this->_node_struct[$key][1], $value));
        } else {
            return false;
        }
    }

    public function _get_pos_by_key($offset, $key, &$pos)
    {
        if (!$offset) {
            $pos = 0;
            return false;
        }

        $info = $this->_get_node($offset);

        if ($info['key'] == $key) {
            $pos = $info['offset'];
            return true;
        } elseif ($info['next'] && $info['next'] != $offset) {
            return $this->_get_pos_by_key($info['next'], $key, $pos);
        } else {
            $pos = $offset;
            return false;
        }
    }

    public function _lru_delete($info)
    {

        if ($info['lru_right']) {
            $this->_set_node($info['lru_right'], 'lru_left', $info['lru_left']);
        } else {
            $this->_set_schema($this->_get_size_schema_id($info['size']), 'lru_tail', $info['lru_left']);
        }

        if ($info['lru_left']) {
            $this->_set_node($info['lru_left'], 'lru_right', $info['lru_right']);
        } else {
            $this->_set_schema($this->_get_size_schema_id($info['size']), 'lru_head', $info['lru_right']);
        }

        return true;
    }

    public function _lru_push($schema_id, $offset)
    {
        $lru_head = $this->_get_schema($schema_id, 'lru_head');
        $lru_tail = $this->_get_schema($schema_id, 'lru_tail');

        if ((!$offset) || ($lru_head == $offset)) {
            return;
        }

        $info = $this->_get_node($offset);

        $this->_set_node($info['lru_right'], 'lru_left', $info['lru_left']);
        $this->_set_node($info['lru_left'], 'lru_right', $info['lru_right']);

        $this->_set_node($offset, 'lru_right', $lru_head);
        $this->_set_node($offset, 'lru_left', 0);

        $this->_set_node($lru_head, 'lru_left', $offset);
        $this->_set_schema($schema_id, 'lru_head', $offset);

        if (0 == $lru_tail) {
            $this->_set_schema($schema_id, 'lru_tail', $offset);
        } elseif ($lru_tail == $offset && $info['lru_left']) {
            $this->_set_schema($schema_id, 'lru_tail', $info['lru_left']);
        }
        return true;
    }

    public function _get_node($offset)
    {
        $this->_seek($offset * $this->idx_node_size + $this->idx_node_base);
        $info           = unpack('V1next/V1prev/V1data/V1size/V1lru_right/V1lru_left/H*key', fread($this->_rs, $this->idx_node_size));
        $info['offset'] = $offset;
        return $info;
    }

    public function _lru_pop($schema_id)
    {
        if ($node = $this->_get_schema($schema_id, 'lru_tail')) {
            $info = $this->_get_node($node);
            if (!$info['data']) {
                return false;
            }
            $this->delete($info['key'], $info['offset']);
            if (!$this->_get_schema($schema_id, 'free')) {
                $this->triggerError('pop lru,But nothing free...', E_USER_ERROR);
            }
            return $info;
        } else {
            return false;
        }
    }

    public function _dalloc($schema_id, $lru_freed = false)
    {

        if ($free = $this->_get_schema($schema_id, 'free')) {
            //如果lru里有链表
            $this->_seek($free);
            list(, $next) = unpack('V', fread($this->_rs, 4));
            $this->_set_schema($schema_id, 'free', $next);
            return $free;
        } elseif ($lru_freed) {
            $this->triggerError('Bat lru poped freesize', E_USER_ERROR);
            return false;
        } else {
            $ds_offset = $this->_get_dcur_pos();
            $size      = $this->_get_schema($schema_id, 'size');

            if ($size + $ds_offset > $this->max_size) {
                if ($info = $this->_lru_pop($schema_id)) {
                    return $this->_dalloc($schema_id, $info);
                } else {
                    $this->triggerError('Can\'t alloc dataspace', E_USER_ERROR);
                    return false;
                }
            } else {
                $this->_set_dcur_pos($ds_offset + $size);
                return $ds_offset;
            }
        }
    }

    public function _get_dcur_pos()
    {
        $this->_seek($this->dfile_cur_pos);
        list(, $ds_offset) = unpack('V', fread($this->_rs, 4));
        return $ds_offset;
    }
    public function _set_dcur_pos($pos)
    {
        return $this->_puts($this->dfile_cur_pos, pack('V', $pos));
    }

    public function _free_dspace($size, $pos)
    {

        if ($pos > $this->max_size) {
            $this->triggerError('free dspace over quota:' . $pos, E_USER_ERROR);
            return false;
        }

        $schema_id = $this->_get_size_schema_id($size);
        if ($free = $this->_get_schema($schema_id, 'free')) {
            $this->_puts($free, pack('V1', $pos));
        } else {
            $this->_set_schema($schema_id, 'free', $pos);
        }
        $this->_puts($pos, pack('V1', 0));
    }

    public function _dfollow($pos, &$c)
    {
        $c++;
        $this->_seek($pos);
        list(, $next) = unpack('V1', fread($this->_rs, 4));
        if ($next) {
            return $this->_dfollow($next, $c);
        } else {
            return $pos;
        }
    }

    public function _free_node($pos)
    {
        $this->_seek($this->idx_free_pos);
        list(, $prev_free_node) = unpack('V', fread($this->_rs, 4));
        $this->_puts($pos * $this->idx_node_size + $this->idx_node_base, pack('V', $prev_free_node) . str_repeat("\0", $this->idx_node_size - 4));
        return $this->_puts($this->idx_free_pos, pack('V', $pos));
    }

    public function _alloc_idx($data)
    {
        $this->_seek($this->idx_free_pos);
        list(, $list_pos) = unpack('V', fread($this->_rs, 4));
        if ($list_pos) {

            $this->_seek($list_pos * $this->idx_node_size + $this->idx_node_base);
            list(, $prev_free_node) = unpack('V', fread($this->_rs, 4));
            $this->_puts($this->idx_free_pos, pack('V', $prev_free_node));

        } else {
            $this->_seek($this->idx_seq_pos);
            list(, $list_pos) = unpack('V', fread($this->_rs, 4));
            $this->_puts($this->idx_seq_pos, pack('V', $list_pos + 1));
        }
        return $this->_create_node($list_pos, $data);
    }

    public function _create_node($pos, $data)
    {
        $this->_puts($pos * $this->idx_node_size + $this->idx_node_base
            , pack('V1V1V1V1V1V1H*', $data['next'], $data['prev'], $data['data'], $data['size'], $data['lru_right'], $data['lru_left'], $data['key']));
        return $pos;
    }

    public function _set_schema($schema_id, $key, $value)
    {
        $info = array_flip($this->schema_struct);
        return $this->_puts(60 + $schema_id * $this->schema_item_size + $info[$key] * 4, pack('V', $value));
    }

    public function _get_schema($id, $key)
    {
        $info = array_flip($this->schema_struct);

        $this->_seek(60 + $id * $this->schema_item_size);
        unpack('V1' . implode('/V1', $this->schema_struct), fread($this->_rs, $this->schema_item_size));

        $this->_seek(60 + $id * $this->schema_item_size + $info[$key] * 4);
        list(, $value) = unpack('V', fread($this->_rs, 4));
        return $value;
    }

    public function _all_schemas()
    {
        $schema = [];
        for ($i = 0; $i < 16; $i++) {
            $this->_seek(60 + $i * $this->schema_item_size);
            $info = unpack('V1' . implode('/V1', $this->schema_struct), fread($this->_rs, $this->schema_item_size));
            if ($info['size']) {
                $info['id'] = $i;
                $schema[$i] = $info;
            } else {
                return $schema;
            }
        }
    }

    public function schemaStatus()
    {
        $return = [];
        foreach ($this->_all_schemas() as $k => $schemaItem) {
            if ($schemaItem['free']) {
                $this->_dfollow($schemaItem['free'], $schemaItem['freecount']);
            }
            $return[] = $schemaItem;
        }
        return $return;
    }

    public function status(&$curBytes, &$totalBytes)
    {
        $totalBytes = $curBytes = 0;
        $hits       = $miss       = 0;

        $schemaStatus = $this->schemaStatus();
        $totalBytes   = $this->max_size;
        $freeBytes    = $this->max_size - $this->_get_dcur_pos();

        foreach ($schemaStatus as $schema) {
            $freeBytes += $schema['freecount'] * $schema['size'];
            $miss += $schema['miss'];
            $hits += $schema['hits'];
        }
        $curBytes = $totalBytes - $freeBytes;

        $return[] = array('name' => '缓存命中', 'value' => $hits);
        $return[] = array('name' => '缓存未命中', 'value' => $miss);
        return $return;
    }

    public function triggerError($errstr, $errno)
    {
        triggerError($errstr, $errno);
    }

}
