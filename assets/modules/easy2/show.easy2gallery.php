<?php

/******************************************************************************
*
*  EASY 2 GALLERY BY Cx2 <inteldesign@mail.ru>
*  VERSION 1.1
*
******************************************************************************/

error_reporting(0);

if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    sh_err ('Error :-(');
}

// MODx config
require_once '../../../manager/includes/config.inc.php';
mysql_connect($database_server, $database_user, $database_password) or
sh_err('MySQL connect error');
mysql_select_db(str_replace('`', '', $dbase));
@mysql_query("{$database_connection_method} {$database_connection_charset}"); 

require_once './config.easy2gallery.php';
$id = (int) $_GET['id'];
$res = mysql_query('SELECT * FROM '.$table_prefix.'easy2_files WHERE id='.$id);
if (!$res) sh_err('MySQL query error');

$row = mysql_fetch_array($res, MYSQL_ASSOC);
$ext = substr($row['filename'], strrpos($row['filename'], '.'));
$filename2send = $row['filename']; 

$res = mysql_query('SELECT A.cat_id, A.cat_name FROM '.$table_prefix.'easy2_dirs A, '.$table_prefix.'easy2_dirs B WHERE B.cat_id='.$row['dir_id'].' AND B.cat_left BETWEEN A.cat_left AND A.cat_right AND A.cat_level > 0 ORDER BY A.cat_left');
if (!$res) sh_err('MySQL query error');

$path = '';
while ($l = mysql_fetch_row($res)) {
    $path .= $l[0].'/';
}

$fp = '../../../'.$e2g['dir'].$path.$row['id'].$ext;
$filesize2sent=filesize($fp);

if ($e2g['ewm'] != 0) {
    // WATERMARK

    $inf = getimagesize($fp);

    if ($inf[2] == 1) $im = imagecreatefromgif ($fp);
    elseif ($inf[2] == 2) $im = imagecreatefromjpeg ($fp);
    elseif ($inf[2] == 3) $im = imagecreatefrompng ($fp);
    else sh_err ('Imagecreate error');

    if ($e2g['wmtype'] == 'text') {
        // X
        $len = strlen($e2g['wmt']);
        if ($e2g['wmpos1'] == 3) $x = $inf[0] - 10 - ($len * 6);
        elseif ($e2g['wmpos1'] == 2) $x = ($inf[0] - ($len * 6)) / 2;
        else $x = 10;

        // Y
        if ($e2g['wmpos2'] == 3) $y = $inf[1] - 20;
        elseif ($e2g['wmpos2'] == 2) $y = ($inf[1]/ 2) - 5;
        else $y = 10;

        $text_color = imagecolorallocate($im, 0, 0, 0);
        imagestring($im, 2, $x-1, $y,  $e2g['wmt'], $text_color);
        imagestring($im, 2, $x+1, $y,  $e2g['wmt'], $text_color);
        imagestring($im, 2, $x, $y-1,  $e2g['wmt'], $text_color);
        imagestring($im, 2, $x, $y+1,  $e2g['wmt'], $text_color);
        imagestring($im, 2, $x+1, $y+1,  $e2g['wmt'], $text_color);
        imagestring($im, 2, $x-1, $y-1,  $e2g['wmt'], $text_color);

        $text_color = imagecolorallocate($im, 255, 255, 255);
        imagestring($im, 2, $x, $y,  $e2g['wmt'], $text_color);
    } elseif ($e2g['wmtype'] == 'image') {

        $wmfp = '../../../'.str_replace('../', '', $e2g['wmt']);
        if (!file_exists($wmfp)) {
            sh_err ('WM file not found');
        }

        $wminfo = getimagesize($wmfp);

        if ($wminfo[2] == 1) $wmi = imagecreatefromgif ($wmfp);
        elseif ($wminfo[2] == 2) $wmi = imagecreatefromjpeg ($wmfp);
        elseif ($wminfo[2] == 3) $wmi = imagecreatefrompng($wmfp);
        else sh_err ('WM error');

        imageAlphaBlending($wmi, false);
        imageSaveAlpha($wmi, true);
        $wm_w = imageSX($wmi);
        $wm_h = imageSY($wmi);

        // X
        $len = strlen($e2g['wmt']);
        if ($e2g['wmpos1'] == 3) $x = $inf[0] - 10 - $wm_w;
        elseif ($e2g['wmpos1'] == 2) $x = ($inf[0] - $wm_w) / 2;
        else $x = 10;

        // Y
        if ($e2g['wmpos2'] == 3) $y = $inf[1] - 10 - $wm_h;
        elseif ($e2g['wmpos2'] == 2) $y = ($inf[1]/ 2) - $wm_h;
        else $y = 10;


        imagecopy($im, $wmi, $x, $y, 0, 0, $wm_w, $wm_h);
        imagedestroy($wmi);
    }

    // SAVE
    //header("Content-type: image/jpeg");
    //imagejpeg($im);
    //imagedestroy($im);

    ob_start();
    header('Last-Modified: '.date('r'));
    header('Accept-Ranges: bytes');
    header('Content-Length: '.$filesize2sent);
    header('Content-type: image/jpeg');
    header('Content-Disposition: inline; filename="'.$filename2send.'"');
    imagejpeg($im);
    ob_end_flush();
    imagedestroy($im);

} else {
    header ('Location: '.$fp);
    //readfile ('../../../'.$e2g['dir'].$path.$row['id'].$ext);
    exit();
}





function sh_err ($text) {
    header("Content-type: image/png");
    $im = @imagecreate(300, 200)
    or die("Cannot Initialize new GD image stream");
    $background_color = imagecolorallocate($im, 255, 255, 255);
    $text_color = imagecolorallocate($im, 0, 0, 0);
    imagestring($im, 5, 75, 95,  $text, $text_color);
    imagepng($im);
    imagedestroy($im);
    exit();
}

?>
