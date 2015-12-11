<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace org\image\driver;

use Exception;

class Gd
{
    /**
     * 图像资源对象
     * 
     * @var resource
     */
    private $im;

    /**
     * @var $git org\image\driver\Gif
     */
    private $gif;

    /**
     * 图像信息，包括 width, height, type, mime, size
     * 
     * @var array
     */
    private $info;

    /**
     * 构造方法，可用于打开一张图像
     * 
     * @param string $imgname 图像路径
     */
    public function __construct($imgname = null)
    {
        $imgname && $this->open($imgname);
    }

    /**
     * 打开一张图像
     *
     * @param  string $imgname 图像路径
     *
     * @return $this
     * @throws \Exception
     */
    public function open($imgname)
    {
        //检测图像文件
        if (!is_file($imgname)) {
            throw new \Exception('不存在的图像文件');
        }

        //获取图像信息
        $info = getimagesize($imgname);

        //检测图像合法性
        if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            throw new Exception('非法图像文件');
        }

        //设置图像信息
        $this->info = [
            'width'  => $info[0],
            'height' => $info[1],
            'type'   => image_type_to_extension($info[2], false),
            'mime'   => $info['mime'],
        ];

        //销毁已存在的图像
        empty($this->im) || imagedestroy($this->im);

        //打开图像
        if ('gif' == $this->info['type']) {
            $class     = '\\Think\\Image\\Driver\\Gif';
            $this->gif = new $class($imgname);
            $this->im  = imagecreatefromstring($this->gif->image());
        } else {
            $fun      = "imagecreatefrom{$this->info['type']}";
            $this->im = $fun($imgname);
        }
        return $this;
    }

    /**
     * 保存图像
     *
     * @param  string  $imgname   图像保存名称
     * @param  string  $type      图像类型
     * @param  boolean $interlace 是否对JPEG类型图像设置隔行扫描
     *
     * @return $this
     * @throws Exception
     */
    public function save($imgname, $type = null, $interlace = true)
    {
        if (empty($this->im)) {
            throw new Exception('没有可以被保存的图像资源');
        }

        //自动获取图像类型
        if (is_null($type)) {
            $type = $this->info['type'];
        } else {
            $type = strtolower($type);
        }

        //JPEG图像设置隔行扫描
        if ('jpeg' == $type || 'jpg' == $type) {
            $type = 'jpeg';
            imageinterlace($this->im, $interlace);
        }

        //保存图像
        if ('gif' == $type && !empty($this->gif)) {
            $this->gif->save($imgname);
        } else {
            $fun = "image{$type}";
            $fun($this->im, $imgname);
        }
        return $this;
    }

    /**
     * 返回图像宽度
     * @return int 图像宽度
     * @throws Exception
     */
    public function width()
    {
        if (empty($this->im)) {
            throw new Exception('没有指定图像资源');
        }

        return $this->info['width'];
    }

    /**
     * 返回图像高度
     * @return int 图像高度
     * @throws Exception
     */
    public function height()
    {
        if (empty($this->im)) {
            throw new Exception('没有指定图像资源');
        }

        return $this->info['height'];
    }

    /**
     * 返回图像类型
     * @return string 图像类型
     * @throws Exception
     */
    public function type()
    {
        if (empty($this->im)) {
            throw new Exception('没有指定图像资源');
        }

        return $this->info['type'];
    }

    /**
     * 返回图像MIME类型
     * @return string 图像MIME类型
     * @throws Exception
     */
    public function mime()
    {
        if (empty($this->im)) {
            throw new Exception('没有指定图像资源');
        }

        return $this->info['mime'];
    }

    /**
     * 返回图像尺寸数组 0 - 图像宽度，1 - 图像高度
     * @return array 图像尺寸
     * @throws Exception
     */
    public function size()
    {
        if (empty($this->im)) {
            throw new Exception('没有指定图像资源');
        }

        return [$this->info['width'], $this->info['height']];
    }

    /**
     * 裁剪图像
     *
     * @param  integer $w      裁剪区域宽度
     * @param  integer $h      裁剪区域高度
     * @param  integer $x      裁剪区域x坐标
     * @param  integer $y      裁剪区域y坐标
     * @param  integer $width  图像保存宽度
     * @param  integer $height 图像保存高度
     *
     * @return $this
     * @throws Exception
     */
    public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null)
    {
        if (empty($this->im)) {
            throw new Exception('没有可以被裁剪的图像资源');
        }

        //设置保存尺寸
        empty($width) && $width   = $w;
        empty($height) && $height = $h;

        do {
            //创建新图像
            $img = imagecreatetruecolor($width, $height);
            // 调整默认颜色
            $color = imagecolorallocate($img, 255, 255, 255);
            imagefill($img, 0, 0, $color);

            //裁剪
            imagecopyresampled($img, $this->im, 0, 0, $x, $y, $width, $height, $w, $h);
            imagedestroy($this->im); //销毁原图

            //设置新图像
            $this->im = $img;
        } while (!empty($this->gif) && $this->gifNext());

        $this->info['width']  = $width;
        $this->info['height'] = $height;
        return $this;
    }

    /**
     * 生成缩略图
     *
     * @param  integer $width  缩略图最大宽度
     * @param  integer $height 缩略图最大高度
     * @param int      $type   缩略图裁剪类型
     *
     * @return $this
     * @throws Exception
     */
    public function thumb($width, $height, $type = THINKIMAGE_THUMB_SCALING)
    {
        if (empty($this->im)) {
            throw new Exception('没有可以被缩略的图像资源');
        }

        //原图宽度和高度
        $w = $this->info['width'];
        $h = $this->info['height'];

        /* 计算缩略图生成的必要参数 */
        switch ($type) {
            /* 等比例缩放 */
            case THINKIMAGE_THUMB_SCALING:
                //原图尺寸小于缩略图尺寸则不进行缩略
                if ($w < $width && $h < $height) {
                    return false;
                }

                //计算缩放比例
                $scale = min($width / $w, $height / $h);

                //设置缩略图的坐标及宽度和高度
                $x      = $y      = 0;
                $width  = $w * $scale;
                $height = $h * $scale;
                break;

            /* 居中裁剪 */
            case THINKIMAGE_THUMB_CENTER:
                //计算缩放比例
                $scale = max($width / $w, $height / $h);

                //设置缩略图的坐标及宽度和高度
                $w = $width / $scale;
                $h = $height / $scale;
                $x = ($this->info['width'] - $w) / 2;
                $y = ($this->info['height'] - $h) / 2;
                break;

            /* 左上角裁剪 */
            case THINKIMAGE_THUMB_NORTHWEST:
                //计算缩放比例
                $scale = max($width / $w, $height / $h);

                //设置缩略图的坐标及宽度和高度
                $x = $y = 0;
                $w = $width / $scale;
                $h = $height / $scale;
                break;

            /* 右下角裁剪 */
            case THINKIMAGE_THUMB_SOUTHEAST:
                //计算缩放比例
                $scale = max($width / $w, $height / $h);

                //设置缩略图的坐标及宽度和高度
                $w = $width / $scale;
                $h = $height / $scale;
                $x = $this->info['width'] - $w;
                $y = $this->info['height'] - $h;
                break;

            /* 填充 */
            case THINKIMAGE_THUMB_FILLED:
                //计算缩放比例
                if ($w < $width && $h < $height) {
                    $scale = 1;
                } else {
                    $scale = min($width / $w, $height / $h);
                }

                //设置缩略图的坐标及宽度和高度
                $neww = $w * $scale;
                $newh = $h * $scale;
                $x    = $this->info['width'] - $w;
                $y    = $this->info['height'] - $h;
                $posx = ($width - $w * $scale) / 2;
                $posy = ($height - $h * $scale) / 2;

                do {
                    //创建新图像
                    $img = imagecreatetruecolor($width, $height);
                    // 调整默认颜色
                    $color = imagecolorallocate($img, 255, 255, 255);
                    imagefill($img, 0, 0, $color);

                    //裁剪
                    imagecopyresampled($img, $this->im, $posx, $posy, $x, $y, $neww, $newh, $w, $h);
                    imagedestroy($this->im); //销毁原图
                    $this->im = $img;
                } while (!empty($this->gif) && $this->gifNext());

                $this->info['width']  = $width;
                $this->info['height'] = $height;
                return $this;

            /* 固定 */
            case THINKIMAGE_THUMB_FIXED:
                $x = $y = 0;
                break;

            default:
                throw new Exception('不支持的缩略图裁剪类型');
        }

        /* 裁剪图像 */
        $this->crop($w, $h, $x, $y, $width, $height);
        return $this;
    }

    /**
     * 添加水印
     *
     * @param  string $source 水印图片路径
     * @param int     $locate 水印位置
     *
     * @return $this
     * @throws Exception
     * @internal param int $alpha 水印透明度
     */
    public function water($source, $locate = THINKIMAGE_WATER_SOUTHEAST)
    {
        //资源检测
        if (empty($this->im)) {
            throw new Exception('没有可以被添加水印的图像资源');
        }

        if (!is_file($source)) {
            throw new Exception('水印图像不存在');
        }

        //获取水印图像信息
        $info = getimagesize($source);
        if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            throw new Exception('非法水印文件');
        }

        //创建水印图像资源
        $fun   = 'imagecreatefrom' . image_type_to_extension($info[2], false);
        $water = $fun($source);

        //设定水印图像的混色模式
        imagealphablending($water, true);

        /* 设定水印位置 */
        switch ($locate) {
            /* 右下角水印 */
            case THINKIMAGE_WATER_SOUTHEAST:
                $x = $this->info['width'] - $info[0];
                $y = $this->info['height'] - $info[1];
                break;

            /* 左下角水印 */
            case THINKIMAGE_WATER_SOUTHWEST:
                $x = 0;
                $y = $this->info['height'] - $info[1];
                break;

            /* 左上角水印 */
            case THINKIMAGE_WATER_NORTHWEST:
                $x = $y = 0;
                break;

            /* 右上角水印 */
            case THINKIMAGE_WATER_NORTHEAST:
                $x = $this->info['width'] - $info[0];
                $y = 0;
                break;

            /* 居中水印 */
            case THINKIMAGE_WATER_CENTER:
                $x = ($this->info['width'] - $info[0]) / 2;
                $y = ($this->info['height'] - $info[1]) / 2;
                break;

            /* 下居中水印 */
            case THINKIMAGE_WATER_SOUTH:
                $x = ($this->info['width'] - $info[0]) / 2;
                $y = $this->info['height'] - $info[1];
                break;

            /* 右居中水印 */
            case THINKIMAGE_WATER_EAST:
                $x = $this->info['width'] - $info[0];
                $y = ($this->info['height'] - $info[1]) / 2;
                break;

            /* 上居中水印 */
            case THINKIMAGE_WATER_NORTH:
                $x = ($this->info['width'] - $info[0]) / 2;
                $y = 0;
                break;

            /* 左居中水印 */
            case THINKIMAGE_WATER_WEST:
                $x = 0;
                $y = ($this->info['height'] - $info[1]) / 2;
                break;

            default:
                /* 自定义水印坐标 */
                if (is_array($locate)) {
                    list($x, $y) = $locate;
                } else {
                    throw new Exception('不支持的水印位置类型');
                }
        }

        do {
            //添加水印
            $src = imagecreatetruecolor($info[0], $info[1]);
            // 调整默认颜色
            $color = imagecolorallocate($src, 255, 255, 255);
            imagefill($src, 0, 0, $color);

            imagecopy($src, $this->im, 0, 0, $x, $y, $info[0], $info[1]);
            imagecopy($src, $water, 0, 0, 0, 0, $info[0], $info[1]);
            imagecopymerge($this->im, $src, $x, $y, 0, 0, $info[0], $info[1], 100);

            //销毁零时图片资源
            imagedestroy($src);
        } while (!empty($this->gif) && $this->gifNext());

        //销毁水印资源
        imagedestroy($water);
        return $this;
    }

    /**
     * 图像添加文字
     *
     * @param  string  $text   添加的文字
     * @param  string  $font   字体路径
     * @param  integer $size   字号
     * @param  string  $color  文字颜色
     * @param int      $locate 文字写入位置
     * @param  integer $offset 文字相对当前位置的偏移量
     * @param  integer $angle  文字倾斜角度
     *
     * @return $this
     * @throws Exception
     */
    public function text($text, $font, $size, $color = '#00000000',
        $locate = THINKIMAGE_WATER_SOUTHEAST, $offset = 0, $angle = 0) {
        //资源检测
        if (empty($this->im)) {
            throw new Exception('没有可以被写入文字的图像资源');
        }

        if (!is_file($font)) {
            throw new Exception("不存在的字体文件：{$font}");
        }

        //获取文字信息
        $info = imagettfbbox($size, $angle, $font, $text);
        $minx = min($info[0], $info[2], $info[4], $info[6]);
        $maxx = max($info[0], $info[2], $info[4], $info[6]);
        $miny = min($info[1], $info[3], $info[5], $info[7]);
        $maxy = max($info[1], $info[3], $info[5], $info[7]);

        /* 计算文字初始坐标和尺寸 */
        $x = $minx;
        $y = abs($miny);
        $w = $maxx - $minx;
        $h = $maxy - $miny;

        /* 设定文字位置 */
        switch ($locate) {
            /* 右下角文字 */
            case THINKIMAGE_WATER_SOUTHEAST:
                $x += $this->info['width'] - $w;
                $y += $this->info['height'] - $h;
                break;

            /* 左下角文字 */
            case THINKIMAGE_WATER_SOUTHWEST:
                $y += $this->info['height'] - $h;
                break;

            /* 左上角文字 */
            case THINKIMAGE_WATER_NORTHWEST:
                // 起始坐标即为左上角坐标，无需调整
                break;

            /* 右上角文字 */
            case THINKIMAGE_WATER_NORTHEAST:
                $x += $this->info['width'] - $w;
                break;

            /* 居中文字 */
            case THINKIMAGE_WATER_CENTER:
                $x += ($this->info['width'] - $w) / 2;
                $y += ($this->info['height'] - $h) / 2;
                break;

            /* 下居中文字 */
            case THINKIMAGE_WATER_SOUTH:
                $x += ($this->info['width'] - $w) / 2;
                $y += $this->info['height'] - $h;
                break;

            /* 右居中文字 */
            case THINKIMAGE_WATER_EAST:
                $x += $this->info['width'] - $w;
                $y += ($this->info['height'] - $h) / 2;
                break;

            /* 上居中文字 */
            case THINKIMAGE_WATER_NORTH:
                $x += ($this->info['width'] - $w) / 2;
                break;

            /* 左居中文字 */
            case THINKIMAGE_WATER_WEST:
                $y += ($this->info['height'] - $h) / 2;
                break;

            default:
                /* 自定义文字坐标 */
                if (is_array($locate)) {
                    list($posx, $posy) = $locate;
                    $x += $posx;
                    $y += $posy;
                } else {
                    throw new Exception('不支持的文字位置类型');
                }
        }

        /* 设置偏移量 */
        if (is_array($offset)) {
            $offset        = array_map('intval', $offset);
            list($ox, $oy) = $offset;
        } else {
            $offset = intval($offset);
            $ox     = $oy     = $offset;
        }

        /* 设置颜色 */
        if (is_string($color) && 0 === strpos($color, '#')) {
            $color = str_split(substr($color, 1), 2);
            $color = array_map('hexdec', $color);
            if (empty($color[3]) || $color[3] > 127) {
                $color[3] = 0;
            }
        } elseif (!is_array($color)) {
            throw new Exception('错误的颜色值');
        }

        do {
            /* 写入文字 */
            $col = imagecolorallocatealpha($this->im, $color[0], $color[1], $color[2], $color[3]);
            imagettftext($this->im, $size, $angle, $x + $ox, $y + $oy, $col, $font, $text);
        } while (!empty($this->gif) && $this->gifNext());
        return $this;
    }

    /* 切换到GIF的下一帧并保存当前帧，内部使用 */
    private function gifNext()
    {
        ob_start();
        ob_implicit_flush(0);
        imagegif($this->im);
        $img = ob_get_clean();

        $this->gif->image($img);
        $next = $this->gif->nextImage();

        if ($next) {
            $this->im = imagecreatefromstring($next);
            return $next;
        } else {
            $this->im = imagecreatefromstring($this->gif->image());
            return false;
        }
    }

    /**
     * 析构方法，用于销毁图像资源
     */
    public function __destruct()
    {
        empty($this->im) || imagedestroy($this->im);
    }
}
