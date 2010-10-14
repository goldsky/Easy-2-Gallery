<?php
/**
 * Galleriffic
 * @link http://www.twospy.com/galleriffic/
 */
// just to avoid direct call to this file. it's recommended to always use this.
if (!defined(E2G_SNIPPET_URL) && $slideshow != 'galleriffic') {
    return;
}
// result with no images
elseif ($countSlideshowFiles == 0) {
    $ssDisplay = 'No image inside the gallery id ' . $gid;
    // this slideshow heavily dependent on any image existence.
    return;
}
else {
    // just making a default selection
    if (!isset($ssConfig))
        $ssConfig = 'example-1';

    //** *********************************************** */
    /*            PREPARE THE HTML HEADERS            */
    //** *********************************************** */

//    $modx->regClientCSS(MODX_BASE_URL.'assets/libs/galleriffic/css/basic.css');
    if ($ssConfig == 'example-1') {
        if (!empty($ssCss)) {
            $modx->regClientCSS($ssCss, 'screen');
        } else {
            $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/css/galleriffic-1.css', 'screen');
        }
    }
    if ($ssConfig == 'example-2') {
        if (!empty($ssCss)) {
            $modx->regClientCSS($ssCss, 'screen');
        } else {
            $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/css/galleriffic-2.css', 'screen');
        }
    }
    if ($ssConfig == 'example-3') {
        if (!empty($ssCss)) {
            $modx->regClientCSS($ssCss, 'screen');
        } else {
            $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/css/galleriffic-3.css', 'screen');
        }
    }
    if ($ssConfig == 'example-5') {
        if (!empty($ssCss)) {
            $modx->regClientCSS($ssCss, 'screen');
        } else {
            $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/css/galleriffic-5.css', 'screen');
//            $modx->regClientCSS(MODX_BASE_URL.'assets/libs/galleriffic/css/white.css','screen');
            $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/css/white.css', 'screen');
        }
    }

    if (empty($ssCss)) {
        // defining the dimension in CSS style
        $modx->regClientStartupHTMLBlock('
        <style type="text/css" media="screen">
         div.slideshow img {
            position: absolute;
            left: 0px;
            max-width: ' . $ssW . 'px;
            max-height: ' . $ssH . 'px; /* This should be set to be at least the height of the largest image in the slideshow */
        }

        div.slideshow-container,
        div.loader,
        div.slideshow a.advance-link {
            width: ' . ((int) $ssW + 2) . 'px; /* This should be set to be at least the width of the largest image in the slideshow with padding */
        }

        div.loader,
        div.slideshow a.advance-link,
        div.caption-container {
            height: ' . ((int) $ssH + 2) . 'px; /* This should be set to be at least the height of the largest image in the slideshow with padding */
        }

        div.slideshow-container {
            background-color: ' . ( $ssBg == 'rgb' ? 'rgb(' . $thbgRed . ',' . $thbgGreen . ',' . $thbgBlue . ')' : $ssBg ) . ';
            height: ' . $ssH . 'px;
        }
        </style>');
    }

    // Javascript
    if ($ssConfig == 'example-1') {
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery-1.3.2.js');
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.galleriffic.js');
    }
    if ($ssConfig == 'example-2') {
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery-1.3.2.js');
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.galleriffic.js');
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.opacityrollover.js');
    }
    if ($ssConfig == 'example-3') {
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery-1.3.2.js');
        // Optionally include jquery.history.js for history support
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.history.js');
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.galleriffic.js');
        $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/jquery.opacityrollover.js');
    }
    if ($ssConfig == 'example-5') {
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

    if ($ssConfig == 'example-1') {
        if (!empty($ss_js)) {
            $modx->regClientStartupScript($ss_js);
        } else {
            $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/galleriffic-1.js');
        }
    }
    if ($ssConfig == 'example-2') {
        if (!empty($ss_js)) {
            $modx->regClientStartupScript($ss_js);
        } else {
            $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/galleriffic-2.js');
        }
    }
    if ($ssConfig == 'example-3') {
        if (!empty($ss_js)) {
            $modx->regClientStartupScript($ss_js);
        } else {
            $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleriffic/js/galleriffic-3.js');
        }
    }
    if ($ssConfig == 'example-5') {
        if (!empty($ss_js)) {
            $modx->regClientStartupScript($ss_js);
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

    if ($ssConfig != 'example-5') {
        // start the galleriffic part.
        $ssDisplay = '
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
    if ($ssConfig == 'example-5') {
        $ssDisplay = '
<!-- Start Advanced Gallery Html Containers -->
<div class="navigation-container">
    <div id="thumbs" class="navigation">
        <a class="pageLink prev" style="visibility: hidden;" href="#" title="Previous Page"></a>
        <ul class="thumbs noscript">';
    }

    $j = 0;
    for ($i = 0; $i < $countSlideshowFiles; $i++) {

        if ($ssConfig == 'example-1') {
            $ssDisplay .= '
        <li>
            <a class="thumb" href="' . $_ssFile['resizedimg'][$i] . '" title="' . $_ssFile['title'][$i] . '">' . $_ssFile['title'][$i] . '</a>
        </li>';
            // if there is a image number limitation
            $j++;
            if ($j == $ssLimit)
                break;
        } // if ($ssConfig=='example-1')
        // display the gallery thumbs
        if ($ssConfig == 'example-2' || $ssConfig == 'example-3') {
            $ssDisplay .= '
        <li>
            <a class="thumb" name="' . $_ssFile['title'][$i] . '" href="' . $_ssFile['resizedimg'][$i] . '">
                <img src="' . $_ssFile['thumbsrc'][$i] . '" />
            </a>
            <div class="caption">
                <div class="download">
                    <a href="' . $_ssFile['src'][$i] . '">Download Original</a>
                </div>
                <div class="image-title">' . $_ssFile['title'][$i] . '</div>
                <div class="image-desc">' . $_ssFile['description'][$i] . '</div>
            </div>
        </li>';
            // if there is a image number limitation
            $j++;
            if ($j == $ssLimit)
                break;
        } // if ( $ssConfig=='example-2' )

        if ($ssConfig == 'example-5') {
            $ssDisplay .= '
            <li>
                <a class="thumb" name="' . $_ssFile['title'][$i] . '" href="' . $_ssFile['resizedimg'][$i] . '" title="' . $_ssFile['title'][$i] . '">
                    <img src="' . $_ssFile['thumbsrc'][$i] . '" alt="' . $_ssFile['title'][$i] . '" />
                </a>
                <div class="caption">
                    <div class="image-title">' . $_ssFile['title'][$i] . '</div>
                    <div class="image-desc">' . $_ssFile['description'][$i] . '</div>

                    <div class="download">
                        <a href="' . $_ssFile['src'][$i] . '">Download Original</a>
                    </div>
                </div>
            </li>';
        } // if ( $ssConfig=='example-5' )
    } // for ($i=0;$i<$countSlideshowFiles;$i++)
    // closing the HTML slideshow container
    if ($ssConfig != 'example-5') {
        $ssDisplay .= '
</ul>
</div>';
    }
    if ($ssConfig == 'example-5') {
        $ssDisplay .= '
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
    echo $ssDisplay;
}