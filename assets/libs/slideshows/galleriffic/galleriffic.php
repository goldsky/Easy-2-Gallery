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
 * Galleriffic
 * @link http://www.twospy.com/galleriffic/
 */
// just to avoid direct call to this file. it's recommended to always use this.
if (!defined('E2G_SNIPPET_URL') && $slideshow != 'galleriffic') {
    return;
}

// this slideshow heavily dependent on any image existence, returns with no images
if ($ssFiles['count'] == 0) {
    return 'No image inside the specified id(s),'
    . (!empty($ssParams['gid']) ? ' gid:' . $ssParams['gid'] : '')
    . (!empty($ssParams['fid']) ? ' fid:' . $ssParams['fid'] : '');
}

// just making a default selection
if (!isset($ssParams['ss_config']))
    $ssParams['ss_config'] = 'example-1';

// initiate the returned variable
$output = '';

//** *********************************************** */
/*            PREPARE THE HTML HEADERS            */
//** *********************************************** */
//    $modx->regClientCSS(MODX_BASE_URL.'assets/libs/slideshows/galleriffic/css/basic.css');
if ($ssParams['ss_config'] == 'example-1') {
    if (!empty($ssFiles['ss_css'])) {
        $modx->regClientCSS($ssFiles['ss_css'], 'screen');
    } else {
        $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/css/galleriffic-1.css', 'screen');
    }
}
if ($ssParams['ss_config'] == 'example-2') {
    if (!empty($ssFiles['ss_css'])) {
        $modx->regClientCSS($ssFiles['ss_css'], 'screen');
    } else {
        $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/css/galleriffic-2.css', 'screen');
    }
}
if ($ssParams['ss_config'] == 'example-3') {
    if (!empty($ssFiles['ss_css'])) {
        $modx->regClientCSS($ssFiles['ss_css'], 'screen');
    } else {
        $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/css/galleriffic-3.css', 'screen');
    }
}
if ($ssParams['ss_config'] == 'example-5') {
    if (!empty($ssFiles['ss_css'])) {
        $modx->regClientCSS($ssFiles['ss_css'], 'screen');
    } else {
        $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/css/galleriffic-5.css', 'screen');
        $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/css/white.css', 'screen');
    }
}

if (empty($ssFiles['ss_css'])) {
    // defining the dimension in CSS style
    $modx->regClientStartupHTMLBlock('
        <style type="text/css" media="screen">
         div.slideshow img {
            position: absolute;
            left: 0px;
            max-width: ' . $ssParams['ss_w'] . 'px;
            max-height: ' . $ssParams['ss_h'] . 'px; /* This should be set to be at least the height of the largest image in the slideshow */
        }

        div.slideshow-container,
        div.loader,
        div.slideshow a.advance-link {
            width: ' . ((int) $ssParams['ss_w'] + 2) . 'px; /* This should be set to be at least the width of the largest image in the slideshow with padding */
        }

        div.loader,
        div.slideshow a.advance-link,
        div.caption-container {
            height: ' . ((int) $ssParams['ss_h'] + 2) . 'px; /* This should be set to be at least the height of the largest image in the slideshow with padding */
        }

        div.slideshow-container {
            background-color: ' . ( $ssParams['ss_bg'] == 'rgb' ? 'rgb(' . $ssParams['ss_red'] . ',' . $ssParams['ss_green'] . ',' . $ssParams['ss_blue'] . ')' : $ssParams['ss_bg'] ) . ';
            height: ' . $ssParams['ss_h'] . 'px;
        }
        </style>');
}

// Javascript
if ($ssParams['ss_config'] == 'example-1') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery-1.3.2.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.galleriffic.js');
}
if ($ssParams['ss_config'] == 'example-2') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery-1.3.2.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.galleriffic.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.opacityrollover.js');
}
if ($ssParams['ss_config'] == 'example-3') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery-1.3.2.js');
    // Optionally include jquery.history.js for history support
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.history.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.galleriffic.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.opacityrollover.js');
}
if ($ssParams['ss_config'] == 'example-5') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery-1.3.2.js');
    // Optionally include jquery.history.js for history support
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.history.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.galleriffic.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.opacityrollover.js');
}
$modx->regClientStartupHTMLBlock('
        <script type="text/javascript">jQuery.noConflict();</script>');
// header
$modx->regClientStartupHTMLBlock('
        <!-- We only want the thunbnails to display when javascript is disabled -->
        <script type="text/javascript">document.write(\'<style>.noscript { display: none; }</style>\');</script>');

if ($ssParams['ss_config'] == 'example-1') {
    if (!empty($ssParams['ss_js'])) {
        $modx->regClientStartupScript($ssParams['ss_js']);
    } else {
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/galleriffic-1.js');
    }
}
if ($ssParams['ss_config'] == 'example-2') {
    if (!empty($ssParams['ss_js'])) {
        $modx->regClientStartupScript($ssParams['ss_js']);
    } else {
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/galleriffic-2.js');
    }
}
if ($ssParams['ss_config'] == 'example-3') {
    if (!empty($ssParams['ss_js'])) {
        $modx->regClientStartupScript($ssParams['ss_js']);
    } else {
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/galleriffic-3.js');
    }
}
if ($ssParams['ss_config'] == 'example-5') {
    if (!empty($ssParams['ss_js'])) {
        $modx->regClientStartupScript($ssParams['ss_js']);
    } else {
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/galleriffic-5.js');
    }
}

//** *********************************************** */
//** *********************************************** */
//** *                                            ** */
//** *           THE SLIDESHOW DISPLAY            ** */
//** *                                            ** */
//** *********************************************** */
//** *********************************************** */

if ($ssParams['ss_config'] != 'example-5') {
    // start the galleriffic part.
    $output = '
<div id="gallery" class="content">
    <div id="controls" class="controls"></div>
    <div class="slideshow-container">
        <div id="loading" class="loader"></div>
        <div id="slideshow" class="slideshow"></div>
    </div>
    <div id="caption" class="caption-container"></div>
</div>
<div id="thumbs" class="navigation">
    <ul class="thumbs noscript">';
}
if ($ssParams['ss_config'] == 'example-5') {
    $output = '
<!-- Start Advanced Gallery Html Containers -->
<div class="navigation-container">
    <div id="thumbs" class="navigation">
        <a class="pageLink prev" style="visibility: hidden;" href="#" title="Previous Page"></a>
        <ul class="thumbs noscript">';
}

for ($i = 0; $i < $ssFiles['count']; $i++) {

    if ($ssParams['ss_config'] == 'example-1') {
        $output .= '
        <li>
            <a class="thumb" href="' . $ssFiles['resizedimg'][$i] . '" title="' . $ssFiles['title'][$i] . '">' . $ssFiles['title'][$i] . '</a>
        </li>';
        // if there is a image number limitation
    } // if ($ssParams['ss_config']=='example-1')
    // display the gallery thumbs
    if ($ssParams['ss_config'] == 'example-2' || $ssParams['ss_config'] == 'example-3') {
        $output .= '
        <li>
            <a class="thumb" name="' . $ssFiles['title'][$i] . '" href="' . $ssFiles['resizedimg'][$i] . '">
                <img src="' . $ssFiles['thumbsrc'][$i] . '" />
            </a>
            <div class="caption">
                <div class="download">
                    <a href="' . $ssFiles['src'][$i] . '">Download Original</a>
                </div>
                <div class="image-title">' . $ssFiles['title'][$i] . '</div>
                <div class="image-desc">' . $ssFiles['description'][$i] . '</div>
            </div>
        </li>';
        // if there is a image number limitation
    } // if ( $ssParams['ss_config']=='example-2' )

    if ($ssParams['ss_config'] == 'example-5') {
        $output .= '
            <li>
                <a class="thumb" name="' . $ssFiles['title'][$i] . '" href="' . $ssFiles['resizedimg'][$i] . '" title="' . $ssFiles['title'][$i] . '">
                    <img src="' . $ssFiles['thumbsrc'][$i] . '" alt="' . $ssFiles['title'][$i] . '" />
                </a>
                <div class="caption">
                    <div class="image-title">' . $ssFiles['title'][$i] . '</div>
                    <div class="image-desc">' . $ssFiles['description'][$i] . '</div>

                    <div class="download">
                        <a href="' . $ssFiles['src'][$i] . '">Download Original</a>
                    </div>
                </div>
            </li>';
    } // if ( $ssParams['ss_config']=='example-5' )
} // for ($i=0;$i<$ssFiles['count'];$i++)
// closing the HTML slideshow container
if ($ssParams['ss_config'] != 'example-5') {
    $output .= '
</ul>
</div>';
}
if ($ssParams['ss_config'] == 'example-5') {
    $output .= '
        </ul>
        <a class="pageLink next" style="visibility: hidden;" href="#" title="Next Page"></a>
    </div>
</div>
<div class="content">
    <div class="slideshow-container">
        <div id="controls" class="controls"></div>
        <div id="loading" class="loader"></div>
        <div id="slideshow" class="slideshow"></div>
    </div>
    <div id="caption" class="caption-container">
        <div class="photo-index"></div>
    </div>
</div>
<!-- End Gallery Html Containers -->';
}

return $output;