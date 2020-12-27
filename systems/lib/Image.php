<?php
/**
 * Image
 *
 * Easy image handling using PHP 5  and the GDlib
 *
 * @copyright     Copyright 2010-2011, ushi <ushi@porkbox.net>
 * @link          https://github.com/ushis/PHP-Image-Class
 * @license       DBAD License (http://philsturgeon.co.uk/code/dbad-license)
 */

namespace Systems\Lib;

/**
 * Image processor
 */
class Image
{

    /**
     * Image resource
     *
     * @var resource
     * @access private
     */
    private $image;

    /**
     * Type of image
     *
     * @var string
     * @access private
     */
    private $type = 'png';

    /**
     * Width of the image in pixel
     *
     * @var integer
     * @access private
     */
    private $width;

    /**
     * Height of the image in pixel
     *
     * @var integer
     * @access private
     */
    private $height;

    /**
     * Return infos about the image
     *
     * @return array image infos
     * @access public
     */
    public function getInfos($what = null)
    {
        if ($what !== null && in_array($what, ['width', 'height', 'type'])) {
            return $this->{$what};
        }

        return array(
            'width' => $this->width,
            'height' => $this->height,
            'type' => $this->type,
            'resource' => $this->image,
        );
    }

    /**
     * Get color in rgb
     *
     * @param string $hex Color in hexadecimal code
     * @return array Color in rgb
     * @access public
     */
    private function hex2rgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        $hex = (preg_match('/^([a-fA-F0-9]{3})|([a-fA-F0-9]{6})$/', $hex)) ? $hex : '000';

        switch (strlen($hex)) {
            case 3:
                $rgb['r'] = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
                $rgb['g'] = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
                $rgb['b'] = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
                break;

            case 6:
                $rgb['r'] = hexdec(substr($hex, 0, 2));
                $rgb['g'] = hexdec(substr($hex, 2, 2));
                $rgb['b'] = hexdec(substr($hex, 4, 2));
                break;
        }

        return $rgb;
    }

    /**
     * Creates image resource from file
     *
     * @param string $path Path to an image
     * @return boolean true if resource was created
     * @access public
     */
    public function load($path)
    {
        if (empty($path)) {
            return false;
        }

        $file = @fopen($path, 'r');

        if (!$file) {
            return false;
        }

        fclose($file);
        $info = getimagesize($path);

        switch ($info[2]) {
            case 1:
                $this->image = imagecreatefromgif($path);
                $this->type = 'gif';
                break;

            case 2:
                $this->image = imagecreatefromjpeg($path);
                $this->type = 'jpg';
                break;

            case 3:
                $this->image = imagecreatefrompng($path);
                $this->type = 'png';
                imagealphablending($this->image, false);
                imagesavealpha($this->image, true);
                break;

            default:
                return false;
        }

        $this->width = $info[0];
        $this->height = $info[1];
        return true;
    }

    /**
    * Creates image resource with background
    *
    * @param integer $width Width of the image
    * @param integer $height Height of the image
    * @param string $background Background color in hexadecimal code
    * @return boolean true if resource was created
    * @access public
    */
    public function create($width, $height, $background = null)
    {
        if ($width > 0 && $height > 0) {
            $this->image = imagecreatetruecolor($width, $height);
            $this->width = $width;
            $this->height = $height;
            if (preg_match('/^([a-fA-F0-9]{3})|([a-fA-F0-9]{6})$/', $background)) {
                $rgb = $this->hex2rgb($background);
                $background = imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
                imagefill($this->image, 0, 0, $background);
            } else {
                imagealphablending($this->image, false);
                $black = imagecolorallocate($this->image, 0, 0, 0);
                imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $black);
                imagecolortransparent($this->image, $black);
                imagealphablending($this->image, true);
            }
            return true;
        }

        return false;
    }

    /**
     * Resizes the image
     *
     * @param integer $width New width
     * @param integer $height New height
     * @return boolean true if image was resized
     * @access public
     */
    public function resize($width, $height = 0)
    {
        if ($width <= 0 && $height <= 0) {
            return false;
        } elseif ($width > 0 && $height <= 0) {
            $height = $this->height*$width/$this->width;
        } elseif ($width <= 0 && $height > 0) {
            $width = $this->width*$height/$this->height;
        }

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        $this->image = $image;
        $this->width = $width;
        $this->height = $height;
        return true;
    }

    /**
     * Crops a part of the image
     *
     * @param integer $x X-coordinate
     * @param integer $y Y-coordinate
     * @param integer $width Width of cutout
     * @param integer $height Height of cutout
     * @access public
     */
    public function crop($x, $y, $width, $height)
    {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagecopyresampled($image, $this->image, 0, 0, $x, $y, $width, $height, $width, $height);
        $this->image = $image;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Resizes and crops image to specified size
     *
     * @param int $width New width
     * @param int $height New height
     * @access public
     * @return void
     */
    public function fit($width, $height)
    {
        if ($this->width > $this->height) {
            $this->resize(0, $height);
            $this->crop(($this->width - $width) / 2, 0, $width, $height);
        } else {
            $this->resize($width);
            $this->crop(0, ($this->height - $height) / 2, $width, $height);
        }
    }

    /**
     * Rotates image
     *
     * @param integer $angle in degree
     * @access public
     */
    public function rotate($angle)
    {
        $this->image = imagerotate($this->image, $angle, 0);
    }

    /**
    * Creates rectangle
    *
    * @param integer $x1 X1-coordinate
    * @param integer $y1 Y1-coordinate
    * @param integer $x2 X2-coordinate
    * @param integer $y2 Y2-coordinate
    * @param string $color Color in hexadecimal code
    * @access public
    */
    public function rectangle($x1, $y1, $x2, $y2, $color)
    {
        $rgb = $this->hex2rgb($color);
        $color = imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
        imagefilledrectangle($this->image, $x1, $y1, $x2, $y2, $color);
    }
    /**
    * Creates ellipse
    *
    * @param integer $x X-coordinate
    * @param integer $y Y-coordinate
    * @param integer $width Width of ellipse
    * @param integer $height Height of ellipse
    * @param string $color Color in hexadecimal code
    * @access public
    */
    public function ellipse($x, $y, $width, $height, $color)
    {
        $rgb = $this->hex2rgb($color);
        $color = imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
        imagefilledellipse($this->image, $x, $y, $width, $height, $color);
    }
    /**
    * Creates polygon
    *
    * @param array $points Coordinates of the vertices
    * @param string $color Color in hexadecimal code
    * @access public
    */
    public function polygon($points, $color)
    {
        $rgb = $this->hex2rgb($color);
        $color = imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
        $num = count($points)/2;
        imagefilledpolygon($this->image, $points, $num, $color);
    }
    /**
    * Draws a line
    *
    * @param array $points Coordinates of the vertices
    * @param string $color Color in hexadecimal code
    * @access public
    */
    public function line($points, $color)
    {
        $rgb = $this->hex2rgb($color);
        $color = imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
        imageline($this->image, $points[0], $points[1], $points[2], $points[3], $color);
    }

    /**
     * Writes on image
     *
     * @param integer $x X-coordinate
     * @param integer $y Y-coordinate
     * @param string $font Path to ttf
     * @param integer $size Font size
     * @param integer $angle in degree
     * @param string $color Color in hexadecimal code
     * @param string $text Text
     * @access public
     */
    public function write($x, $y, $font, $size, $angle, $color, $text)
    {
        $rgb = $this->hex2rgb($color);
        $color = imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']);
        imagettftext($this->image, $size, $angle, $x, $y, $color, $font, $text);
    }

    /**
     * Merges image with another
     *
     * @param Image $img object
     * @param mixed $x X-coordinate
     * @param mixed $y Y-coordinate
     * @access public
     */
    public function merge($img, $x, $y)
    {
        $infos = $img->getInfos();

        switch ($x) {
            case 'left':
                $x = 0;
                break;

            case 'right':
                $x = $this->width-$infos['width'];
                break;

            default:
                $x = $x;
        }

        switch ($y) {
            case 'top':
                $y = 0;
                break;

            case 'bottom':
                $y = $this->height-$infos['height'];
                break;

            default:
                $y = $y;
        }

        imagealphablending($this->image, true);
        imagecopy($this->image, $infos['resource'], $x, $y, 0, 0, $infos['width'], $infos['height']);
    }

    /**
     * Shows image
     *
     * @param string $type Filetype
     * @access public
     */
    public function show($type = 'png')
    {
        $type = ($type != 'gif' && $type != 'jpeg' && $type != 'png') ? $this->type : $type;

        switch ($type) {
            case 'gif':
                header('Content-type: image/gif');
                imagegif($this->image);
                break;

            case 'jpeg':
                header('Content-type: image/jpeg');
                imagejpeg($this->image, '', 100);
                break;

            default:
                header('Content-type: image/png');
                imagepng($this->image);
        }
    }

    /**
     * Saves image
     *
     * @param string $path Path to location
     * @param string $type Filetype
     * @return boolean true if image was saved
     * @access public
     */
    public function save($path, $quality = 90)
    {
        $dir = dirname($path);
        $type = pathinfo($path, PATHINFO_EXTENSION);

        if (!file_exists($dir) || !is_dir($dir)) {
            return false;
        }

        if (!is_writable($dir)) {
            return false;
        }

        if ($type != 'gif' && $type != 'jpeg' && $type != 'jpg' && $type != 'png') {
            $type = $this->type;
            $path .= '.'.$type;
        }

        switch ($type) {
            case 'gif':
                imagegif($this->image, $path);
                break;

            case 'jpeg': case 'jpg':
                imagejpeg($this->image, $path, $quality);
                break;

            default:
                imagepng($this->image, $path);
        }

        return true;
    }
}
