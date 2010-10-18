<?php

//echo __LINE__ . ': $countSlideshowFiles = ' . $countSlideshowFiles . '<br />';
//echo '<pre>';
//print_r($countSlideshowFiles);
//echo '</pre>';
//die();

/**
 * Simple jQuery Slideshow
 * @link http://jonraasch.com/blog/a-simple-jquery-slideshow
 */
// just to avoid direct call to this file. it's recommended to always use this.
if (!defined(E2G_SNIPPET_URL) && $slideshow != 'simple') {
    return;
}
// result with no images
elseif ($countSlideshowFiles == 0) {
    $ssDisplay = 'No image inside the gallery id ' . $gid;
    // this slideshow heavily dependent on any image existence.
    return;
}

if (!empty($ssCss)) {
    $modx->regClientCSS($ssCss, 'screen');
} else {
    $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/simplejquery/simple.css', 'screen');
    // amend dimension variables into CSS
    $modx->regClientStartupHTMLBlock('
        <style type="text/css" media="screen">
        #slideshow {
            width: ' . $ssW . 'px;
            height: ' . $ssH . 'px;
            background-color: ' . $ssBg . ';
         }
        </style>
            ');
}

$modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/jquery/jquery-1.4.2.min.js');

if (!empty($ssJs)) {
    $modx->regClientStartupScript($ssJs);
} else {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/simplejquery/simple.js');
}

// start create the slideshow box
$ssDisplay = '<div id="slideshow"><div>';
$j = 0;
for ($i = 0; $i < $countSlideshowFiles; $i++) {
    $dim = getimagesize($_ssFile['src'][$i]);
    $width[$i] = $dim[0];
    $height[$i] = $dim[1];
    $imageRatio[$i] = $width[$i] / $height[$i];

//        echo $ssW/$ssH .'=>'. $imageRatio[$i].'<br />';
    $ssDisplay .= '
                <img src="' . utf8_encode($_ssFile['src'][$i]) . '" alt="" title="' . $_ssFile['title'][$i] . '" '
            . ( $i == 0 ? 'class="active" ' : '' )
            . ( ( ($ssW / $ssH) < $imageRatio[$i] ) ?
                    'height="' . $ssH . 'px" style="left:' . (($ssW - ($width[$i] * $ssH / $height[$i])) / 2) . 'px;" ' :
                    'width="' . $ssW . 'px" style="top:' . (($ssH - ($height[$i] * $ssW / $width[$i])) / 2) . 'px;" ' )
            . '/>';

    // if there is a image number limitation
    $j++;
    if ($j == $ssLimit)
        break;
}
// end the slideshow box
$ssDisplay .= '</div></div>';
echo $ssDisplay;