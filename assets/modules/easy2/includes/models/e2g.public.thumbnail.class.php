<?php

/**
 * EASY 2 GALLERY
 * Gallery Image Creator Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@fastmail.fm>
 */
class E2gThumb {

    /**
     * Inherit MODx functions
     * @var mixed modx's API
     */
    public $modx;

    /**
     * The configurations in an array
     * @var mixed all the module's settings
     */
    public $e2gThumbCfg;

    /**
     * Image creator
     * @param string    $modx           Inherit MODx functions
     * @param string    $e2gThumbCfg    The configurations in an array
     */
    public function __construct($modx, $e2gThumbCfg) {
        $this->modx = & $modx;
        $this->e2gThumbCfg = $e2gThumbCfg;
    }

    /**
     * To get and create thumbnails
     * @param  string $gdir             root dir
     * @param  string $path             directory path of each of thumbnail
     * @param  int    $w                thumbnail width
     * @param  int    $h                thumbnail height
     * @param  int    $thq              thumbnail quality
     * @param  string $resizeType       'inner' | 'resize'
     *                                  'inner' = crop the thumbnail
     *                                  'resize' = autofit the thumbnail
     * @param  int    $red              Red in RGB
     * @param  int    $green            Green in RGB
     * @param  int    $blue             Blue in RGB
     * @param  bool   $createWaterMark  create water mark
     * @param  string $thumbPath        thumbnail's path for saving
     *
     * @return mixed FALSE/the thumbnail's path
     */
    public function imgShaper(
    $gdir
    , $path
    , $w
    , $h
    , $thq
    , $resizeType=NULL
    , $red=NULL
    , $green=NULL
    , $blue=NULL
    , $createWaterMark = 0
    , $thumbPath = NULL
    ) {

        if (!file_exists(realpath($gdir . $path))) {
            return FALSE;
        }

        /**
         * If there is no the image's thumbnail inside the thumbnail's path,
         * CREATE THE THUMBNAIL
         */
        if (!file_exists(realpath($gdir . $thumbPath))) {
            // Apache's timeout: 300 secs
            if (function_exists('ini_get') && !ini_get('safe_mode')) {
                if (function_exists('set_time_limit')) {
                    set_time_limit(300);
                }
                if (function_exists('ini_set')) {
                    if (ini_get('max_execution_time') !== 300) {
                        ini_set('max_execution_time', 300);
                    }
                }
            }

            ob_start();

            $imgSize = @getimagesize($gdir . $path);
            if (!$imgSize) {
                return FALSE;
            }
            if ($imgSize[2] == 1)
                $im = imagecreatefromgif($gdir . $path);
            elseif ($imgSize[2] == 2)
                $im = imagecreatefromjpeg($gdir . $path);
            elseif ($imgSize[2] == 3)
                $im = imagecreatefrompng($gdir . $path);
            else {
                return FALSE;
            }

            if (strtolower($w) === 'auto' && strtolower($h) === 'auto') {
                return FALSE;
            }
            if (strtolower($w) === 'auto') {
                $w = $h * $imgSize[0]/$imgSize[1];
            }
            if (strtolower($h) === 'auto') {
                $h = $w * $imgSize[1]/$imgSize[0];
            }

            if ($imgSize[0] / $w > 2.00 || $imgSize[1] / $h > 2.00) {
                $tmp_w = $w * 2.00;
                $tmp_h = round($imgSize[1] * ($tmp_w / $imgSize[0]), 2);

                $temp = imagecreatetruecolor($tmp_w, $tmp_h);
                imagecopyresized($temp, $im, 0, 0, 0, 0, $tmp_w, $tmp_h, $imgSize[0], $imgSize[1]);

                $imgSize[0] = $tmp_w;
                $imgSize[1] = $tmp_h;

                imagedestroy($im);
                $im = $temp;
            }

            // Shifts
            $x = $y = 0;

            /**
             * $resizeType == 'inner'
             * trim to default dimensions
             */
            if ($resizeType == 'inner') {
                // Dimensions
                $w2 = $w;
                $h2 = $h;

                if (($imgSize[0] / $imgSize[1]) > ($w / $h)) {
                    $w2 = round($imgSize[0] * $h / $imgSize[1], 2);
                    $x = ($w2 - $w) / 2.00 * (-1.00);
                } else {
                    $h2 = round($imgSize[1] * $w / $imgSize[0], 2);
                    $y = ($h2 - $h) / 2.00 * (-1.00);
                }

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, $x, $y, 0, 0, $w2, $h2, $imgSize[0], $imgSize[1]);
            } elseif ($resizeType == 'shrink') {
                /**
                 * $resizeType == 'shrink'
                 * ugly shrink to default dimensions
                 */
                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, 0, 0, 0, 0, $w, $h, $imgSize[0], $imgSize[1]);
            } elseif ($resizeType == 'resize') {
                /**
                 * $resizeType == 'resize'
                 * resize image with original proportional dimensions
                 */
                // Dimensions
                $w2 = $w;
                $h2 = $h;

                if ($w > $h) {          // landscape thumbnail box
                    $w2 = round($imgSize[0] * $h / $imgSize[1], 2);
                    $x = abs($w - $w2) / 2.00;
                } elseif ($w == $h) {     // square thumbnail box
                    if ($imgSize[0] < $imgSize[1]) {// portrait image
                        $w2 = round($imgSize[0] * $h / $imgSize[1], 2);
                        $x = abs($w - $w2) / 2.00;
                    } elseif ($imgSize[0] == $imgSize[1]) {
                        $w2 = $w;
                        $h2 = $h;
                        $x = 0;
                        $y = 0;
                    } else {              // landscape image
                        $h2 = round($imgSize[1] * $w / $imgSize[0], 2);
                        $y = abs($h - $h2) / 2.00;
                    }
                } else {                  // portrait thumbnail box
                    $h2 = round($imgSize[1] * $w / $imgSize[0], 2);
                    $y = abs($h - $h2) / 2.00;
                }

                $pic = imagecreatetruecolor($w, $h);
                $bgc = imagecolorallocate($pic, $red, $green, $blue);
                imagefill($pic, 0, 0, $bgc);
                imagecopyresampled($pic, $im, $x, $y, 0, 0, $w2, $h2, $imgSize[0], $imgSize[1]);
            } else {
                return FALSE;
            }

            /**
             * make directory of thumbnails
             */
            $dirs = explode('/', $path);
            $npath = $gdir . '_thumbnails';
            $count = count($dirs) - 1;
            for ($c = 0; $c < $count; $c++) {
                $npath .= '/' . $dirs[$c];
                if (is_dir($npath)) {
                    continue;
                } elseif (!@mkdir($npath)) {
                    return FALSE;
                }
                @chmod($npath, 0755);
            }

            /**
             * create the thumbnails
             */
            imagejpeg($pic, $gdir . $thumbPath, $thq);
            /**
             * if set, this will create watermark
             */
            if ($createWaterMark == 1) {
                $this->watermark($gdir . $thumbPath);
            }
            @chmod($gdir . $thumbPath, 0644);

            /**
             * image cache destroy
             */
            imagedestroy($pic);
            imagedestroy($im);

            // goldsky -- adds output buffer to avoid PHP's memory limit
            ob_end_clean();
        }

        // goldsky -- only to switch between localhost and live site.
//        if (strpos($_SERVER['DOCUMENT_ROOT'], '/') === (int) 0) {
        if (strtoupper(substr(PHP_OS, 0, 3) != 'WIN')) {
            $urlEncoding = $gdir . str_replace('%2F', '/', rawurlencode($thumbPath));
        } else {
            $urlEncoding = $gdir . $thumbPath;
        }

        return $urlEncoding;
    }

    /**
     * Generating the watermark
     * @param string $fp file path
     * @return mixed image output
     */
    public function watermark($fp) {
        // Apache's timeout: 300 secs
        if (function_exists('ini_get') && !ini_get('safe_mode')) {
            if (function_exists('set_time_limit')) {
                set_time_limit(300);
            }
            if (function_exists('ini_set')) {
                if (ini_get('max_execution_time') !== 300) {
                    ini_set('max_execution_time', 300);
                }
            }
        }

        $inf = @getimagesize($fp);

        if ($inf[2] == 1)
            $im = imagecreatefromgif($fp);
        elseif ($inf[2] == 2)
            $im = imagecreatefromjpeg($fp);
        elseif ($inf[2] == 3)
            $im = imagecreatefrompng($fp);
        else
            return 'Imagecreate error';

        if ($this->e2gThumbCfg['wmtype'] == 'text') {
            if (!empty($this->e2gThumbCfg['wmposxy'])) {
                $xy = @explode(',', $this->e2gThumbCfg['wmposxy']);
                $x = intval(trim($xy[0]));
                $y = intval(trim($xy[1]));
            } else {
                // X
                $len = strlen($this->e2gThumbCfg['wmt']);
                if ($this->e2gThumbCfg['wmpos1'] == 3)
                    $x = $inf[0] - 10 - ($len * 6);
                elseif ($this->e2gThumbCfg['wmpos1'] == 2)
                    $x = ($inf[0] - ($len * 6)) / 2;
                else
                    $x = 10;

                // Y
                if ($this->e2gThumbCfg['wmpos2'] == 3)
                    $y = $inf[1] - 20;
                elseif ($this->e2gThumbCfg['wmpos2'] == 2)
                    $y = ($inf[1] / 2) - 5;
                else
                    $y = 10;
            }


            $text_color = imagecolorallocate($im, 0, 0, 0);
            imagestring($im, 2, $x - 1, $y, $this->e2gThumbCfg['wmt'], $text_color);
            imagestring($im, 2, $x + 1, $y, $this->e2gThumbCfg['wmt'], $text_color);
            imagestring($im, 2, $x, $y - 1, $this->e2gThumbCfg['wmt'], $text_color);
            imagestring($im, 2, $x, $y + 1, $this->e2gThumbCfg['wmt'], $text_color);
            imagestring($im, 2, $x + 1, $y + 1, $this->e2gThumbCfg['wmt'], $text_color);
            imagestring($im, 2, $x - 1, $y - 1, $this->e2gThumbCfg['wmt'], $text_color);

            $text_color = imagecolorallocate($im, 255, 255, 255);
            imagestring($im, 2, $x, $y, $this->e2gThumbCfg['wmt'], $text_color);
        } elseif ($this->e2gThumbCfg['wmtype'] == 'image') {
            $wmfp = str_replace('../', '', $this->e2gThumbCfg['wmt']);
            if (!file_exists(realpath($wmfp))) {
                return 'Water Mark file not found';
            }

            $wminfo = @getimagesize($wmfp);

            if ($wminfo[2] == 1)
                $wmi = imagecreatefromgif($wmfp);
            elseif ($wminfo[2] == 2)
                $wmi = imagecreatefromjpeg($wmfp);
            elseif ($wminfo[2] == 3)
                $wmi = imagecreatefrompng($wmfp);
            else
                return 'Water Mark error';

            imageAlphaBlending($wmi, FALSE);
            imageSaveAlpha($wmi, TRUE);
            $wm_w = imageSX($wmi);
            $wm_h = imageSY($wmi);

            if (!empty($this->e2gThumbCfg['wmposxy'])) {
                $xy = @explode(',', $this->e2gThumbCfg['wmposxy']);
                $x = intval(trim($xy[0]));
                $y = intval(trim($xy[1]));
            } else {
                // X
                $len = strlen($this->e2gThumbCfg['wmt']);
                if ($this->e2gThumbCfg['wmpos1'] == 3)
                    $x = $inf[0] - 10 - $wm_w;
                elseif ($this->e2gThumbCfg['wmpos1'] == 2)
                    $x = ($inf[0] - $wm_w) / 2;
                else
                    $x = 10;

                // Y
                if ($this->e2gThumbCfg['wmpos2'] == 3)
                    $y = $inf[1] - 10 - $wm_h;
                elseif ($this->e2gThumbCfg['wmpos2'] == 2)
                    $y = ($inf[1] / 2) - $wm_h;
                else
                    $y = 10;
            }

            imagecopy($im, $wmi, $x, $y, 0, 0, $wm_w, $wm_h);
            imagedestroy($wmi);
        }
        return imagejpeg($im, $fp);
    }

}