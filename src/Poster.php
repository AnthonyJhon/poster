<?php

namespace Poster;

use app\common\utils\PosterUtil;

/**
 * 海报工具
 */
class Poster
{
    /**
     * 当前实例
     * @var
     */
    protected static $instance;

    /**
     * 海报背景
     * @var string
     */
    protected $bg;

    /**
     * 海报字体
     * @var string
     */
    protected $font;

    /**
     * 图片
     * @var
     */
    protected $image;

    /**
     * 构造方法
     * @param string $bg
     * @param string $font
     */
    protected function __construct(string $bg, string $font)
    {
        $this->bg = $bg;
        $this->font = $font;
        $this->image = self::loadImage($this->bg); //加载背景图片
        return $this;
    }

    /**
     * 获取实例对象
     * @param string $bg
     * @param string $font
     * @return PosterUtil
     */
    public static function instance(string $bg, string $font): Poster
    {
        if (is_null(self::$instance)) self::$instance = new static($bg, $font);
        return self::$instance;
    }

    /**
     * 加载图片
     */
    public static function loadImage($image, int $w = 0, int $h = 0, string $model = 'scale')
    {
        // if ($model == 'scale') {
        //     // 等比例缩放
        //     if ($w == 0 && $h != 0) $w = round($h * ($width / $height));
        //     if ($h == 0 && $w != 0) $h = round($w * ($height / $width));
        // } else {
        //     [$w, $h] = [$width, $height];
        // }
        [$width, $height] = getimagesize($image); //获取图片的长宽
        $image = imagecreatefromstring(file_get_contents($image)); //创建一个新图像
        $newImage = imagecreatetruecolor($width, $height);//新建一个真彩色图像
        $color = imagecolorallocate($newImage, 255, 255, 255); //给画布分配颜色
        imagefill($newImage, 0, 0, $color); //填充画布
        // imageColorTransparent($canvasImg, $color); //颜色透明
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $width, $height); //重采样拷贝部分图像并调整大小,将背景图复制到画布
        return $newImage;
    }

    /**
     * 添加图片
     * @param $filename
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     * @param string $model
     * @param bool $isBase64
     * @return PosterUtil
     */
    public function addImage($file, int $x = 0, int $y = 0, int $w = 0, int $h = 0, string $model = 'scale', bool $isBase64 = false): Poster
    {
        // [$width, $height] = getimagesize($filename); //获取图片的长宽
        $content = $isBase64 ? base64_decode($file) : file_get_contents($file);
        $img = imagecreatefromstring($content); //从字符串的图像流中新建图像
        $width = imagesx($img);
        $height = imagesy($img);
        if ($model == 'scale') {
            $w == 0 && $h != 0 && $w = round($h * ($width / $height));
            $h == 0 && $w != 0 && $h = round($w * ($height / $width));
        }
        $thumb = imagecreatetruecolor($w, $h); //新建真彩色图像
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $w, $h, $width, $height); //重采样拷贝部分图像并调整大小
        imagecopymerge($this->image, $thumb, $x, $y, 0, 0, $w, $h, 100); //合并
        return $this;
    }

    /**
     * 添加文本
     * @param $text
     * @param int $w
     * @param int $h
     * @param string $color
     * @param int $size
     * @param int $angle
     * @return $this
     */
    public function addText($text, int $w = 0, int $h = 0, string $color = '#1C2833', int $size = 24, int $angle = 0): Poster
    {

        [$r, $g, $b] = self::hexToRGB($color);
        $color = imagecolorallocate($this->image, $r, $g, $b); //为图像分配颜色
        imagettftext($this->image, $size, $angle, $w, $h, $color, $this->font, $text); // TrueType字体向图像写入文本
        return $this;
    }

    /**
     * 颜色hex值转换成rgb
     * @param string $hex
     * @return array
     */
    public static function hexToRGB(string $hex): array
    {
        if ($hex[0] == '#') $hex = substr($hex, 1);
        if (strlen($hex) == 6) {
            [$r, $g, $b] = [$hex[0] . $hex[1], $hex[2] . $hex[3], $hex[4] . $hex[5]];
        } elseif (strlen($hex) == 3) {
            [$r, $g, $b] = [$hex[0] . $hex[0], $hex[1] . $hex[1], $hex[2] . $hex[2]];
        } else {
            return [0, 0, 0];
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return [$r, $g, $b];
    }

    /**
     * 保存为base64格式图片
     * @return string
     */
    public
    function writeBase64(): string
    {
        ob_start();
        imagepng($this->image);
        $string = ob_get_contents();
        ob_end_clean();
        return base64_encode($string);
    }
}