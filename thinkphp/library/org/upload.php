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

namespace think;

use think\Image;

class Upload
{
    protected $config = [
        'max_size'            => -1, // 上传文件的最大值
        'support_multi'       => true, // 是否支持多文件上传
        'allow_exts'          => [], // 允许上传的文件后缀 留空不作后缀检查
        'allow_types'         => [], // 允许上传的文件类型 留空不做检查
        'thumb'               => false, // 使用对上传图片进行缩略图处理
        'thumb_max_width'     => '', // 缩略图最大宽度
        'thumb_max_height'    => '', // 缩略图最大高度
        'thumb_prefix'        => 'thumb_', // 缩略图前缀
        'thumb_suffix'        => '',
        'thumb_path'          => '', // 缩略图保存路径
        'thumb_file'          => '', // 缩略图文件名
        'thumb_ext'           => '', // 缩略图扩展名
        'thumb_remove_origin' => false, // 是否移除原图
        'zip_images'          => false, // 压缩图片文件上传
        'auto_sub'            => false, // 启用子目录保存文件
        'sub_type'            => 'hash', // 子目录创建方式 可以使用hash date custom
        'sub_dir'             => '', // 子目录名称 subType为custom方式后有效
        'date_format'         => 'Ymd',
        'hash_level'          => 1, // hash的目录层次
        'save_path'           => '', // 上传文件保存路径
        'auto_check'          => true, // 是否自动检查附件
        'upload_replace'      => false, // 存在同名是否覆盖
        'save_rule'           => 'uniqid', // 上传文件命名规则
        'hash_type'           => 'md5_file', // 上传文件Hash规则函数名
    ];

    // 错误信息
    private $error = '';
    // 上传成功的文件信息
    private $uploadFileInfo;

    public function __get($name)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
        return null;
    }

    public function __set($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * 架构函数
     * @access public
     * @param array $config  上传参数
     */
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 上传一个文件
     * @access protected
     * @param mixed $name 数据
     * @param string $value  数据表名
     * @return string
     */
    protected function save($file)
    {
        $filename = $file['save_path'] . $file['savename'];
        if (!$this->upload_replace && is_file($filename)) {
            // 不覆盖同名文件
            $this->error = '文件已经存在！' . $filename;
            return false;
        }
        // 如果是图像文件 检测文件格式
        if (in_array(strtolower($file['extension']), ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
            $info = getimagesize($file['tmp_name']);
            if (false === $info || ('gif' == strtolower($file['extension']) && empty($info['bits']))) {
                $this->error = '非法图像文件';
                return false;
            }
        }
        if (!move_uploaded_file($file['tmp_name'], $this->autoCharset($filename, 'utf-8', 'gbk'))) {
            $this->error = '文件上传保存错误！';
            return false;
        }
        if ($this->thumb && in_array(strtolower($file['extension']), ['gif', 'jpg', 'jpeg', 'bmp', 'png'])) {
            $image = getimagesize($filename);
            if (false !== $image) {
                //是图像文件生成缩略图
                $thumbWidth   = explode(',', $this->thumb_max_width);
                $thumbHeight  = explode(',', $this->thumb_max_height);
                $thumb_prefix = explode(',', $this->thumb_prefix);
                $thumb_suffix = explode(',', $this->thumb_suffix);
                $thumb_file   = explode(',', $this->thumb_file);
                $thumb_path   = $this->thumb_path ? $this->thumb_path : dirname($filename) . '/';
                $thumb_ext    = $this->thumb_ext ? $this->thumb_ext : $file['extension']; //自定义缩略图扩展名
                // 生成图像缩略图
                for ($i = 0, $len = count($thumbWidth); $i < $len; $i++) {
                    if (!empty($thumb_file[$i])) {
                        $thumbname = $thumb_file[$i];
                    } else {
                        $prefix    = isset($thumb_prefix[$i]) ? $thumb_prefix[$i] : $thumb_prefix[0];
                        $suffix    = isset($thumb_suffix[$i]) ? $thumb_suffix[$i] : $thumb_suffix[0];
                        $thumbname = $prefix . basename($filename, '.' . $file['extension']) . $suffix;
                    }
                    Image::thumb($filename, $thumb_path . $thumbname . '.' . $thumb_ext, '', $thumbWidth[$i], $thumbHeight[$i], true);
                }
                if ($this->thumb_remove_origin) {
                    // 生成缩略图之后删除原图
                    unlink($filename);
                }
            }
        }
        if ($this->zipImags) {
            // TODO 对图片压缩包在线解压

        }
        return true;
    }

    /**
     * 上传所有文件
     * @access public
     * @param string $savePath  上传文件保存路径
     * @return string
     */
    public function upload($savePath = '')
    {
        //如果不指定保存文件名，则由系统默认
        if (empty($savePath)) {
            $savePath = $this->save_path;
        }

        // 检查上传目录
        if (!is_dir($savePath)) {
            // 检查目录是否编码后的
            if (is_dir(base64_decode($savePath))) {
                $savePath = base64_decode($savePath);
            } else {
                // 尝试创建目录
                if (!mkdir($savePath)) {
                    $this->error = '上传目录' . $savePath . '不存在';
                    return false;
                }
            }
        } else {
            if (!is_writeable($savePath)) {
                $this->error = '上传目录' . $savePath . '不可写';
                return false;
            }
        }
        $fileInfo = [];
        $isUpload = false;

        // 获取上传的文件信息
        // 对$_FILES数组信息处理
        $files = $this->dealFiles($_FILES);
        foreach ($files as $key => $file) {
            //过滤无效的上传
            if (!empty($file['name'])) {
                //登记上传文件的扩展信息
                if (!isset($file['key'])) {
                    $file['key'] = $key;
                }

                $file['extension'] = $this->getExt($file['name']);
                $file['savepath']  = $savePath;
                $file['savename']  = $this->getSaveName($file);

                // 自动检查附件
                if ($this->auto_check) {
                    if (!$this->check($file)) {
                        return false;
                    }

                }

                //保存上传文件
                if (!$this->save($file)) {
                    return false;
                }

                if (function_exists($this->hash_type)) {
                    $fun          = $this->hash_type;
                    $file['hash'] = $fun($this->autoCharset($file['savepath'] . $file['savename'], 'utf-8', 'gbk'));
                }
                //上传成功后保存文件信息，供其他地方调用
                unset($file['tmp_name'], $file['error']);
                $fileInfo[] = $file;
                $isUpload   = true;
            }
        }
        if ($isUpload) {
            $this->uploadFileInfo = $fileInfo;
            return true;
        } else {
            $this->error = '没有选择上传文件';
            return false;
        }
    }

    /**
     * 上传单个上传字段中的文件 支持多附件
     * @access public
     * @param array $file  上传文件信息
     * @param string $savePath  上传文件保存路径
     * @return string
     */
    public function uploadOne($file, $savePath = '')
    {
        //如果不指定保存文件名，则由系统默认
        if (empty($savePath)) {
            $savePath = $this->save_path;
        }

        // 检查上传目录
        if (!is_dir($savePath)) {
            // 尝试创建目录
            if (!mkdir($savePath, 0777, true)) {
                $this->error = '上传目录' . $savePath . '不存在';
                return false;
            }
        } else {
            if (!is_writeable($savePath)) {
                $this->error = '上传目录' . $savePath . '不可写';
                return false;
            }
        }
        //过滤无效的上传
        if (!empty($file['name'])) {
            $fileArray = [];
            if (is_array($file['name'])) {
                $keys  = array_keys($file);
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++) {
                    foreach ($keys as $key) {
                        $fileArray[$i][$key] = $file[$key][$i];
                    }

                }
            } else {
                $fileArray[] = $file;
            }
            $info = [];
            foreach ($fileArray as $key => $file) {
                //登记上传文件的扩展信息
                $file['extension'] = $this->getExt($file['name']);
                $file['savepath']  = $savePath;
                $file['savename']  = $this->getSaveName($file);
                // 自动检查附件
                if ($this->auto_check) {
                    if (!$this->check($file)) {
                        return false;
                    }

                }
                //保存上传文件
                if (!$this->save($file)) {
                    return false;
                }

                if (function_exists($this->hash_type)) {
                    $fun          = $this->hash_type;
                    $file['hash'] = $fun($this->autoCharset($file['savepath'] . $file['savename'], 'utf-8', 'gbk'));
                }
                unset($file['tmp_name'], $file['error']);
                $info[] = $file;
            }
            // 返回上传的文件信息
            return $info;
        } else {
            $this->error = '没有选择上传文件';
            return false;
        }
    }

    /**
     * 转换上传文件数组变量为正确的方式
     * @access protected
     * @param array $files  上传的文件变量
     * @return array
     */
    protected function dealFiles($files)
    {
        $fileArray = [];
        $n         = 0;
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $keys  = array_keys($file);
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++) {
                    $fileArray[$n]['key'] = $key;
                    foreach ($keys as $_key) {
                        $fileArray[$n][$_key] = $file[$_key][$i];
                    }
                    $n++;
                }
            } else {
                $fileArray[$key] = $file;
            }
        }
        return $fileArray;
    }

    /**
     * 获取错误代码信息
     * @access public
     * @param string $errorNo  错误号码
     * @return void
     */
    protected function error($errorNo)
    {
        switch ($errorNo) {
            case 1:
                $this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
                break;
            case 2:
                $this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                break;
            case 3:
                $this->error = '文件只有部分被上传';
                break;
            case 4:
                $this->error = '没有文件被上传';
                break;
            case 6:
                $this->error = '找不到临时文件夹';
                break;
            case 7:
                $this->error = '文件写入失败';
                break;
            default:
                $this->error = '未知上传错误！';
        }
        return;
    }

    /**
     * 根据上传文件命名规则取得保存文件名
     * @access protected
     * @param string $filename 数据
     * @return string
     */
    protected function getSaveName($filename)
    {
        $rule = $this->save_rule;
        if (empty($rule)) {
//没有定义命名规则，则保持文件名不变
            $saveName = $filename['name'];
        } else {
            if (function_exists($rule)) {
                //使用函数生成一个唯一文件标识号
                $saveName = $rule() . "." . $filename['extension'];
            } else {
                //使用给定的文件名作为标识号
                $saveName = $rule . "." . $filename['extension'];
            }
        }
        if ($this->auto_sub) {
            // 使用子目录保存文件
            $filename['savename'] = $saveName;
            $saveName             = $this->getSubName($filename) . $saveName;
        }
        return $saveName;
    }

    /**
     * 获取子目录的名称
     * @access protected
     * @param array $file  上传的文件信息
     * @return string
     */
    protected function getSubName($file)
    {
        switch ($this->sub_type) {
            case 'custom':
                $dir = $this->sub_dir;
                break;
            case 'date':
                $dir = date($this->date_format, time()) . '/';
                break;
            case 'hash':
            default:
                $name = md5($file['savename']);
                $dir  = '';
                for ($i = 0; $i < $this->hash_level; $i++) {
                    $dir .= $name{$i} . '/';
                }
                break;
        }
        if (!is_dir($file['savepath'] . $dir)) {
            mkdir($file['savepath'] . $dir, 0777, true);
        }
        return $dir;
    }

    /**
     * 检查上传的文件
     * @access protected
     * @param array $file 文件信息
     * @return boolean
     */
    protected function check($file)
    {
        if (0 !== $file['error']) {
            //文件上传失败
            //捕获错误代码
            $this->error($file['error']);
            return false;
        }
        //文件上传成功，进行自定义规则检查
        //检查文件大小
        if (!$this->checkSize($file['size'])) {
            $this->error = '上传文件大小不符！';
            return false;
        }

        //检查文件Mime类型
        if (!$this->checkType($file['type'])) {
            $this->error = '上传文件MIME类型不允许！';
            return false;
        }
        //检查文件类型
        if (!$this->checkExt($file['extension'])) {
            $this->error = '上传文件类型不允许';
            return false;
        }

        //检查是否合法上传
        if (!$this->checkUpload($file['tmp_name'])) {
            $this->error = '非法上传文件！';
            return false;
        }
        return true;
    }

    // 自动转换字符集 支持数组转换
    protected function autoCharset($fContents, $from = 'gbk', $to = 'utf-8')
    {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to   = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
            //如果编码相同或者非字符串标量则不转换
            return $fContents;
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    }

    /**
     * 检查上传的文件类型是否合法
     * @access protected
     * @param string $type 数据
     * @return boolean
     */
    protected function checkType($type)
    {
        if (!empty($this->allow_types)) {
            return in_array(strtolower($type), $this->allow_types);
        }

        return true;
    }

    /**
     * 检查上传的文件后缀是否合法
     * @access protected
     * @param string $ext 后缀名
     * @return boolean
     */
    protected function checkExt($ext)
    {
        if (!empty($this->allow_exts)) {
            return in_array(strtolower($ext), $this->allow_exts, true);
        }

        return true;
    }

    /**
     * 检查文件大小是否合法
     * @access protected
     * @param integer $size 数据
     * @return boolean
     */
    protected function checkSize($size)
    {
        return !($size > $this->max_size) || (-1 == $this->max_size);
    }

    /**
     * 检查文件是否非法提交
     * @access protected
     * @param string $filename 文件名
     * @return boolean
     */
    protected function checkUpload($filename)
    {
        return is_uploaded_file($filename);
    }

    /**
     * 取得上传文件的后缀
     * @access protected
     * @param string $filename 文件名
     * @return boolean
     */
    protected function getExt($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * 取得上传文件的信息
     * @access public
     * @return array
     */
    public function getUploadFileInfo()
    {
        return $this->uploadFileInfo;
    }

    /**
     * 取得最后一次错误信息
     * @access public
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->error;
    }
}
