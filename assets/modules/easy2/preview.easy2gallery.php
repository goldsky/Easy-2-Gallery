<?php

error_reporting(0);
$timeStart = getmicrotime();

$path = '../../../' . str_replace('../', '', $_GET['path']);
$path = utf8_decode($path);
$w = isset($_GET['mod_w']) ? $_GET['mod_w'] : 190;
$h = isset($_GET['mod_h']) ? $_GET['mod_h'] : 250;

$imgSize = getimagesize($path);
if ($imgSize[2] == 1)
    $im = imagecreatefromgif($path);
elseif ($imgSize[2] == 2)
    $im = imagecreatefromjpeg($path);
elseif ($imgSize[2] == 3)
    $im = imagecreatefrompng($path);
else {
    header('Content-type: image/png');
    $im = @imagecreate($w, $h)
            or die('Cannot Initialize new GD image stream');
    $bgColor = imagecolorallocate($im, 255, 255, 255);
    $textColor = imagecolorallocate($im, 233, 14, 91);
    $textHeight = isset($_GET['th']) ? $_GET['th'] : 2;
    $text = isset($_GET['text']) ? $_GET['text'] : 'Image error';
    $y = $h / 2 - $textHeight * 4;
    $textWidth = imagefontwidth($textHeight) * strlen($text);
    $center = ceil($w / 2);
    $x = $center - (ceil($textWidth / 2));
    imagestring($im, $textHeight, $x, $y, $text, $textColor);
    imagepng($im);
    imagedestroy($im);
    exit();
}

if (isset($_GET['mod_w']) || $_GET['mod_h']) {
    // Shifts
    $x = $y = 0;
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
} elseif (isset($_GET['text'])) {
    header('Content-type: image/png');
    $im = @imagecreate($w, $h)
            or die('Cannot Initialize new GD image stream');
    $bgColor = imagecolorallocate($im, 255, 255, 255);
    $textColor = imagecolorallocate($im, 233, 14, 91);
    $textHeight = isset($_GET['th']) ? $_GET['th'] : 2;
    $text = isset($_GET['text']) ? $_GET['text'] : 'Image error';
    $y = $h / 2 - $textHeight * 4;
    $textWidth = imagefontwidth($textHeight) * strlen($text);
    $center = ceil($w / 2);
    $x = $center - (ceil($textWidth / 2));
    imagestring($im, $textHeight, $x, $y, $text, $textColor);
    imagepng($im);
    imagedestroy($im);
    exit();
} else {
    if ($imgSize[0] / $w > 2 || $imgSize[1] / $h > 2) {
        $tmp_w = 380;
        $tmp_h = round($imgSize[1] * ($tmp_w / $imgSize[0]));

        $temp = imagecreatetruecolor($tmp_w, $tmp_h);
        imagecopyresized($temp, $im, 0, 0, 0, 0, $tmp_w, $tmp_h, $imgSize[0], $imgSize[1]);

        $imgSize[0] = $tmp_w;
        $imgSize[1] = $tmp_h;

        imagedestroy($im);
        $im = $temp;
    }

    if ($imgSize[0] <= $w && $imgSize[1] <= $h) {
        header('Location: ' . $path);
        exit();
    }
    $h = $imgSize[1] * $w / $imgSize[0];
//    if ($imgSize[0] > $imgSize[1]) $h = $imgSize[1] * $w / $imgSize[0];
//    else $w = $imgSize[0] * $h / $imgSize[1];

    $pic = imagecreatetruecolor($w, $h);
    $bgc = imagecolorallocate($pic, 255, 255, 255);
    imagefill($pic, 0, 0, $bgc);
    imagecopyresampled($pic, $im, 0, 0, 0, 0, $w, $h, $imgSize[0], $imgSize[1]);
}

//$text_color = imagecolorallocate($im, 0, 0, 0);
//imagestring($pic, 3, 5, 5, round(getmicrotime() - $timeStart, 4) . 's', $text_color);
if (ob_get_contents ())
    ob_end_clean ();
header('Expires: Fri, 25 December 1980 00:00:00 GMT');
header('Last-Modified: ' .  gmdate('D, d m Y H:i:s ') . 'GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragme: no-cache');
header('Content-type: image/jpeg');
imagejpeg($pic);
imagedestroy($pic);

function getmicrotime() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float) $usec + (float) $sec);
}