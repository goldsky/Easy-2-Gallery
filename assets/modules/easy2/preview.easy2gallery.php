<?php

 error_reporting(0);
 $time_start = getmicrotime();

 $path = '../../../'.str_replace('../', '', $_GET['path']);
 $path = utf8_decode($path);
 $w = 190;
 $h = 250;

 $i = getimagesize($path);
 if ($i[2] == 1) $im = imagecreatefromgif ($path);
 elseif ($i[2] == 2) $im = imagecreatefromjpeg ($path);
 elseif ($i[2] == 3) $im = imagecreatefrompng ($path);
 else {
     header ("Content-type: image/jpeg");
     $im = @imagecreate (50, 100);
     $bg = imagecolorallocate ($im, 255, 255, 255);
     $tc = imagecolorallocate ($im, 233, 14, 91);
     imagestring ($im, 1, 5, 5,  "Error", $text_color);
     imagejpeg ($im);
     exit();
 }

 if ($i[0]/$w > 2 || $i[1]/$h > 2) {
     $tmp_w = 380;
     $tmp_h = round($i[1] * ($tmp_w/$i[0]));

     $temp = imagecreatetruecolor ($tmp_w, $tmp_h);
     imagecopyresized ($temp, $im, 0, 0, 0, 0, $tmp_w, $tmp_h, $i[0], $i[1]);

     $i[0] = $tmp_w;
     $i[1] = $tmp_h;

     imagedestroy($im);
     $im = $temp;
 }

 if ($i[0] <= $w && $i[1] <= $h) {
     header ('Location: '.$path);
     exit();
 }
 $h = $i[1] * $w / $i[0];
 //if ($i[0] > $i[1]) $h = $i[1] * $w / $i[0];
 //else $w = $i[0] * $h / $i[1];

 $pic = imagecreatetruecolor($w, $h);
 $bgc = imagecolorallocate($pic, 255, 255, 255);
 imagefill($pic, 0, 0, $bgc);
 imagecopyresampled($pic, $im, 0, 0, 0, 0, $w, $h, $i[0], $i[1]);

 //$text_color = imagecolorallocate($im, 0, 0, 0);
 //imagestring($pic, 3, 5,5, round(getmicrotime() - $time_start, 4).'s', $text_color);


 header ("Content-type: image/jpeg");
 imagejpeg($pic);
 imagedestroy($pic);
 function getmicrotime(){
     list($usec, $sec) = explode(" ",microtime());
     return ((float)$usec + (float)$sec);
 }


?>
