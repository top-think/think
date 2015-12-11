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

use think\Lang;

class Imagick
{
    /**
     * 图像资源对象
     *
     * @var resource
     */
    private $im;

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
        if (!extension_loaded('Imagick')) {
            throw new \Exception(Lang::get('_NOT_SUPPERT_') . ':Imagick');
        }
        $imgname && $this->open($imgname);
    }

    /**
     * 打开一张图像
     *
     * @param  string $imgname 图像路径
     */
    public function open($imgname)
    {
        //检测图像文件
        if (!is_file($imgname)) {
            throw new \Exception('不存在的图像文件');
        }

        //销毁已存在的图像
        empty($this->im) || $this->im->destroy();

        //载入图像
        $this->im = new \Imagick(realpath($imgname));

        //设置图像信息
        $this->info = [
            'width'  => $this->im->getImageWidth(),
            'height' => $this->im->getImageHeight(),
            'type'   => strtolower($this->im->getImageFormat()),
            'mime'   => $this->im->getImageMimeType(),
        ];
    }

    /**
     * 保存图像
     *
     * @param  string  $imgname   图像保存名称
     * @param  string  $type      图像类型
     * @param  boolean $interlace 是否对JPEG类型图像设置隔行扫描
     */
    public function save($imgname, $type = null, $interlace = true)
    {
        if (empty($this->im)) {
            throw new \Exception('没有可以被保存的图像资源');
        }

        //设置图片类型
        if (is_null($type)) {
            $type = $this->info['type'];
        } else {
            $type = strtolower($type);
            $this->im->setImageFormat($type);
        }

        //JPEG图像设置隔行扫描
        if ('jpeg' == $type || 'jpg' == $type) {
            $this->im->setImageInterlaceScheme(1);
        }

        //去除图像配置信息
        $this->im->stripImage();

        //保存图像
        $imgname = realpath(dirname($imgname)) . '/' . basename($imgname); //强制绝对路径
        if ('gif' == $type) {
            $this->im->writeImages($imgname, true);
        } else {
            $this->im->writeImage($imgname);
        }
    }

    /**
     * 返回图像宽度
     *
     * @return integer 图像宽度
     */
    public function width()
    {
        if (empty($this->im)) {
            throw new \Exception('没有指定图像资源');
        }

        return $this->info['width'];
    }

    /**
     * 返回图像高度
     *
     * @return integer 图像高度
     */
    public function height()
    {
        if (empty($this->im)) {
            throw new \Exception('没有指定图像资源');
        }

        return $this->info['height'];
    }

    /**
     * 返回图像类型
     *
     * @return string 图像类型
     */
    public function type()
    {
        if (empty($this->im)) {
            throw new \Exception('没有指定图像资源');
        }

        return $this->info['type'];
    }

    /**
     * 返回图像MIME类型
     *
     * @return string 图像MIME类型
     */
    public function mime()
    {
        if (empty($this->im)) {
            throw new \Exception('没有指定图像资源');
        }

        return $this->info['mime'];
    }

    /**
     * 返回图像尺寸数组 0 - 图像宽度，1 - 图像高度
     *
     * @return array 图像尺寸
     */
    public function size()
    {
        if (empty($this->im)) {
            throw new \Exception('没有指定图像资源');
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
     */
    public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null)
    {
        if (empty($this->im)) {
            throw new \Exception('没有可以被裁剪的图像资源');
        }

        //设置保存尺寸
        empty($width) && $width   = $w;
        empty($height) && $height = $h;

        //裁剪图片
        if ('gif' == $this->info['type']) {
            $img = $this->im->coalesceImages();
            $this->im->destroy(); //销毁原图

            //循环裁剪每一帧
            do {
                $this->_crop($w, $h, $x, $y, $width, $height, $img);
            } while ($img->nextImage());

            //压缩图片
            $this->im = $img->deconstructImages();
            $img->destroy(); //销毁零时图片
        } else {
            $this->_crop($w, $h, $x, $y, $width, $height);
        }
    }

    /**
     * 裁剪图片，内部调用
     *
     */
    private function _crop($w, $h, $x, $y, $width, $height, $img = null)
    {
        is_null($img) && $img = $this->im;

        //裁剪
        $info = $this->info;
        if (0 != $x || 0 != $y || $w != $info['width'] || $h != $info['height']) {
            $img->cropImage($w, $h, $x, $y);
            $img->setImagePage($w, $h, 0, 0); //调整画布和图片一致
        }

        //调整大小
        if ($w != $width || $h != $height) {
            $img->scaleImage($width, $height);
        }

        //设置缓存尺寸
        $this->info['width']  = $w;
        $this->info['height'] = $h;
    }

    /**
     * 生成缩略图
     *
     * @param  integer $width  缩略图最大宽度
     * @param  integer $height 缩略图最大高度
     * @param  integer $type   缩略图裁剪类型
     */
    public function thumb($width, $height, $type = THINKIMAGE_THUMB_SCALE)
    {
        if (empty($this->im)) {
            throw new \Exception('没有可以被缩略的图像资源');
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
                    return;
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
                $posx = ($width - $w * $scale) / 2;
                $posy = ($height - $h * $scale) / 2;

                //创建一张新图像
                $newimg = new Imagick();
                $newimg->newImage($width, $height, 'white', $this->info['type']);

                if ('gif' == $this->info['type']) {
                    $imgs = $this->im->coalesceImages();
                    $img  = new Imagick();
                    $this->im->destroy(); //销毁原图

                    //循环填充每一帧
                    do {
                        //填充图像
                        $image = $this->_fill($newimg, $posx, $posy, $neww, $newh, $imgs);

                        $img->addImage($image);
                        $img->setImageDelay($imgs->getImageDelay());
                        $img->setImagePage($width, $height, 0, 0);

                        $image->destroy(); //销毁零时图片

                    } while ($imgs->nextImage());

                    //压缩图片
                    $this->im->destroy();
                    $this->im = $img->deconstructImages();
                    $imgs->destroy(); //销毁零时图片
                    $img->destroy(); //销毁零时图片

                } else {
                    //填充图像
                    $img = $this->_fill($newimg, $posx, $posy, $neww, $newh);
                    //销毁原图
                    $this->im->destroy();
                    $this->im = $img;
                }

                //设置新图像属性
                $this->info['width']  = $width;
                $this->info['height'] = $height;
                return;

            /* 固定 */
            case THINKIMAGE_THUMB_FIXED:
                $x = $y = 0;
                break;

            default:
                throw new \Exception('不支持的缩略图裁剪类型');
        }

        /* 裁剪图像 */
        $this->crop($w, $h, $x, $y, $width, $height);
    }

    /**
     * 填充指定图像，内部使用
     *
     */
    private function _fill($newimg, $posx, $posy, $neww, $newh, $img = null)
    {
        is_null($img) && $img = $this->im;

        /* 将指定图片绘入空白图片 */
        $draw = new ImagickDraw();
        $draw->composite($img->getImageCompose(), $posx, $posy, $neww, $newh, $img);
        $image = $newimg->clone();
        $image->drawImage($draw);
        $draw->destroy();

        return $image;
    }

    /**
     * 添加水印
     *
     * @param  string  $source 水印图片路径
     * @param  integer $locate 水印位置
     * @param  integer $alpha  水印透明度
     */
    public function water($source, $locate = THINKIMAGE_WATER_SOUTHEAST)
    {
        //资源检测
        if (empty($this->im)) {
            throw new \Exception('没有可以被添加水印的图像资源');
        }

        if (!is_file($source)) {
            throw new \Exception('水印图像不存在');
        }

        //创建水印图像资源
        $water = new Imagick(realpath($source));
        $info  = [$water->getImageWidth(), $water->getImageHeight()];

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
                    throw new \Exception('不支持的水印位置类型');
                }
        }

        //创建绘图资源
        $draw = new ImagickDraw();
        $draw->composite($water->getImageCompose(), $x, $y, $info[0], $info[1], $water);

        if ('gif' == $this->info['type']) {
            $img = $this->im->coalesceImages();
            $this->im->destroy(); //销毁原图

            do {
                //添加水印
                $img->drawImage($draw);
            } while ($img->nextImage());

            //压缩图片
            $this->im = $img->deconstructImages();
            $img->destroy(); //销毁零时图片

        } else {
            //添加水印
            $this->im->drawImage($draw);
        }

        //销毁水印资源
        $draw->destroy();
        $water->destroy();
    }

    /**
     * 图像添加文字
     *
     * @param  string  $text   添加的文字
     * @param  string  $font   字体路径
     * @param  integer $size   字号
     * @param  string  $color  文字颜色
     * @param  integer $locate 文字写入位置
     * @param  integer $offset 文字相对当前位置的偏移量
     * @param  integer $angle  文字倾斜角度
     */
    public function text($text, $font, $size, $color = '#00000000',
        $locate = THINKIMAGE_WATER_SOUTHEAST, $offset = 0, $angle = 0) {
        //资源检测
        if (empty($this->im)) {
            throw new \Exception('没有可以被写入文字的图像资源');
        }

        if (!is_file($font)) {
            throw new \Exception("不存在的字体文件：{$font}");
        }

        //获取颜色和透明度
        if (is_array($color)) {
            $color = array_map('dechex', $color);
            foreach ($color as &$value) {
                $value = str_pad($value, 2, '0', STR_PAD_LEFT);
            }
            $color = '#' . implode('', $color);
        } elseif (!is_string($color) || 0 !== strpos($color, '#')) {
            throw new \Exception('错误的颜色值');
        }
        $col = substr($color, 0, 7);
        $alp = strlen($color) == 9 ? substr($color, -2) : 0;

        //获取文字信息
        $draw = new ImagickDraw();
        $draw->setFont(realpath($font));
        $draw->setFontSize($size);
        $draw->setFillColor($col);
        $draw->setFillAlpha(1 - hexdec($alp) / 127);
        $draw->setTextAntialias(true);
        $draw->setStrokeAntialias(true);

        $metrics = $this->im->queryFontMetrics($draw, $text);

        /* 计算文字初始坐标和尺寸 */
        $x = 0;
        $y = $metrics['ascender'];
        $w = $metrics['textWidth'];
        $h = $metrics['textHeight'];

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
                    throw new \Exception('不支持的文字位置类型');
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

        /* 写入文字 */
        if ('gif' == $this->info['type']) {
            $img = $this->im->coalesceImages();
            $this->im->destroy(); //销毁原图
            do {
                $img->annotateImage($draw, $x + $ox, $y + $oy, $angle, $text);
            } while ($img->nextImage());

            //压缩图片
            $this->im = $img->deconstructImages();
            $img->destroy(); //销毁零时图片

        } else {
            $this->im->annotateImage($draw, $x + $ox, $y + $oy, $angle, $text);
        }
        $draw->destroy();
    }

    /**
     * 析构方法，用于销毁图像资源
     *
     */
    public function __destruct()
    {
        empty($this->im) || $this->im->destroy();
    }

}
