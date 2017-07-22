<?php

class Captcha
{

    protected $width;
    protected $height;
    protected $size;
    protected $number;
    protected $font;

    // Generate captcha image
    function __construct()
    {
        global $config;

        // Print HTTP headers and prevent caching on client side
        header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-type: image/png');

        $this->width = 88;
        $this->height = 38;
        $this->size = 20;
        $this->number = rand(1000, 9999);
        $this->font = site_root . '/lib/fonts/' . $config['captcha_font'] . '.ttf';

        // Determine captcha block identifier
        $blockName = isset($_POST['id']) ? $_POST['id'] : '';

        // Generate captchaID dynamically for ACTIVE plugins
        if ( trim($blockName) and pluginIsActive($blockName)) {
            $_SESSION['captcha.'.$blockName] = md5($this->number);
        } else {
            // Prepare general captcha
            $_SESSION['captcha'] = md5($this->number);
        }

        $im = imagecreatetruecolor($this->width, $this->height);
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 150, 150, 150);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, $this->width * 2 , $this->height * 2, $black);
        imagettftext($im, $this->size, 8, 16, 30, $grey, $this->font, $this->number);
        imagettftext($im, $this->size, 8, 12, 34, $white, $this->font, $this->number);
        ImagePNG($im);
        imagedestroy($im);
    }
}
