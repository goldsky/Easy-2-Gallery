<?php

$ssParams = $this->_getSlideShowParams();
$ssFiles = $this->_getSlideShowFiles();

//echo __LINE__ . ' : $slideshow = ' . $slideshow . '<br />';
//echo __LINE__ . ' : $ssParams = ' . $ssParams . '<br />';
//echo '<pre>';
//print_r($ssParams);
//echo '</pre>';
//echo __LINE__ . ' : $ssFiles = ' . $ssFiles . '<br />';
//echo '<pre>';
//print_r($ssFiles);
//echo '</pre>';
//die();

/**
 * Simple jQuery Slideshow
 * @link http://jonraasch.com/blog/a-simple-jquery-slideshow
 */
// just to avoid direct call to this file. it's recommended to always use this.
if (!defined('E2G_SNIPPET_URL') || $slideshow != 'simple') {
    return;
}

// this slideshow heavily dependent on any image existence, returns with no images
if ($ssFiles['count'] == 0) {
    return 'No image inside the specified id(s),'
    . (!empty($ssParams['gid']) ? ' gid:' . $ssParams['gid'] : '')
    . (!empty($ssParams['fid']) ? ' fid:' . $ssParams['fid'] : '');
}

if (!empty($ssParams['ss_css'])) {
    $modx->regClientCSS($ssParams['ss_css'], 'screen');
} else {
    $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/simplejquery/simple.css', 'screen');
    // amend dimension variables into CSS
    $modx->regClientStartupHTMLBlock('
        <style type="text/css" media="screen">
        #slideshow {
            width: ' . $ssParams['ss_w'] . 'px;
            height: ' . $ssParams['ss_h'] . 'px;
            background-color: ' . $ssParams['ss_bg'] . ';
         }
        </style>
            ');
}

$modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/jquery/jquery-1.4.2.min.js');

if (!empty($ssParams['ss_js'])) {
    $modx->regClientStartupScript($ssParams['ss_js']);
} else {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/simplejquery/simple.js');
}

// start create the slideshow box
$output = '<div id="slideshow"><div>';
for ($i = 0; $i < $ssFiles['count']; $i++) {
    $dim = getimagesize($ssFiles['src'][$i]);
    $width[$i] = $dim[0];
    $height[$i] = $dim[1];
    $imageRatio[$i] = $width[$i] / $height[$i];

//        echo $ssParams['ss_w']/$ssParams['ss_h'] .'=>'. $imageRatio[$i].'<br />';
    $output .= '
                <img src="' . utf8_encode($ssFiles['resizedimg'][$i])
            . '" alt="' . $ssFiles['title'][$i]
            . '" title="' . $ssFiles['title'][$i]
            . '" ' . ( $i == 0 ? 'class="active" ' : '' )
            . '/>
                ';

    // if there is a image number limitation
}
// end the slideshow box
$output .= '</div></div>';
return $output;