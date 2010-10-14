<?php
/**
 * Galleryview
 * @link http://spaceforaname.com/galleryview
 */

// just to avoid direct call to this file. it's recommended to always use this.
if (!defined(E2G_SNIPPET_URL) && $slideshow != 'galleryview') {
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
        $ssConfig = 'gallerylight';

    //**************************************************/
    /*            PREPARE THE HTML HEADERS            */
    //**************************************************/

    if (!empty($ssCss)) {
        $modx->regClientCSS($ssCss, 'screen');
    } else {
        $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleryview/galleryview.css', 'screen');
    }
    if ($ssConfig == 'polaroid') {
        $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleryview/themes/polaroid/polaroid.css', 'screen');
    }
    if (empty($ssCss)) {
        // defining the dimension in CSS style
        $modx->regClientStartupHTMLBlock('
        <style type="text/css" media="screen">
         div.panel-title {
            font-size : 20px;
            margin: .3em 0;
        }
        div.panel-description {
            font-style: italic;
            line-height: 1.2em;
        }
        </style>');
    }
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/jquery/jquery-1.3.2.min.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleryview/jquery.easing.1.3.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleryview/jquery.galleryview-1.1.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleryview/jquery.timers-1.1.2.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">jQuery.noConflict();</script>');
    if ($ssConfig == 'gallerylight') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: ' . $ssW . ',
                    panel_height: ' . $ssH . ',
                    frame_width: ' . $w . ',
                    frame_height: ' . $w . '
                });
            });
        </script>');
    }
    if ($ssConfig == 'gallerydark') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: ' . $ssW . ',
                    panel_height: ' . $ssH . ',
                    frame_width: ' . $w . ',
                    frame_height: ' . $h . ',
                    overlay_color: \'#222\',
                    overlay_text_color: \'white\',
                    caption_text_color: \'#222\',
                    background_color: \'transparent\',
                    border: \'none\',
                    nav_theme: \'light\',
                    easing: \'easeInOutQuad\',
                    pause_on_hover: true
                });
            });
        </script>');
    }
    if ($ssConfig == 'topfilmstrip') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: ' . $ssW . ',
                    panel_height: ' . $ssH . ',
                    frame_width: ' . $w . ',
                    frame_height: ' . $h . ',
                    transition_speed: 1200,
                    background_color: \'#222\',
                    border: \'none\',
                    easing: \'easeInOutBack\',
                    pause_on_hover: true,
                    nav_theme: \'custom\',
                    overlay_height: 52,
                    filmstrip_position: \'top\',
                    overlay_position: \'top\'
                });
            });
        </script>');
    }
    if ($ssConfig == 'polaroid') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: ' . $ssW . ',
                    panel_height: ' . $ssH . ',
                    frame_width: ' . $w . ',
                    frame_height: ' . $h . ',
                    transition_speed: 1200,
                    background_color: \'transparent\',
                    border: \'none\',
                    easing: \'easeOutBounce\',
                    nav_theme: \'dark\'
                });
            });
        </script>');
    }
    if ($ssConfig == 'filmstrip') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    filmstrip_size: 4,
                    frame_width: ' . $w . ',
                    frame_height: ' . $h . ',
                    background_color: \'transparent\',
                    nav_theme: \'dark\',
                    border: \'none\',
                    show_captions:true,
                    caption_text_color: \'black\'
                });
            });
        </script>');
    }
    if ($ssConfig == 'panel') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: ' . $ssW . ',
                    panel_height: ' . $ssH . ',
                    transition_speed: 1500,
                    transition_interval: 5000,
                    nav_theme: \'dark\',
                    border: \'1px solid white\',
                    pause_on_hover: true
                });
            });
        </script>');
    }

    // override with own settings
    if (!empty($ssJs)) {
        $modx->regClientStartupScript($ssJs);
    }

    //**************************************************/
    //**************************************************/
    //***                                            ***/
    //***           THE SLIDESHOW DISPLAY            ***/
    //***                                            ***/
    //**************************************************/
    //**************************************************/

    //**************************************************/
    /*             THE gallerylight CONFIG             */
    /*             THE gallerydark CONFIG              */
    /*             THE topfilmstrip CONFIG             */
    //**************************************************/

    if ($ssConfig == 'gallerylight' || $ssConfig == 'gallerydark' || $ssConfig == 'topfilmstrip') {
        // ------------- open slideshow wrapper ------------- //
        $ssDisplay = '
    <div id="photos" class="galleryview">';

        // ------------- start the images looping ------------- //
        $j = 0;
        for ($i = 0; $i < $countSlideshowFiles; $i++) {
            $ssDisplay .= '
        <div class="panel">
            <img src="' . $_ssFile['resizedimg'][$i] . '" alt="" />
            <div class="panel-overlay">
                <div class="panel-title">' . $_ssFile['title'][$i] . '</div>
                <div class="panel-description">' . $_ssFile['description'][$i] . '</div>
            </div>
        </div>';
            $j++;
            if ($j == $ssLimit)
                break;
        }
        // ------------- end the images looping ------------- //

        $ssDisplay .= '
      <ul class="filmstrip">';

        // ------------- start the images looping ------------- //
        $j = 0;
        for ($i = 0; $i < $countSlideshowFiles; $i++) {
            $ssDisplay .= '
            <li><img src="' . $_ssFile['thumbsrc'][$i] . '" alt="" title="' . $_ssFile['title'][$i] . '" /></li>';
            $j++;
            if ($j == $ssLimit)
                break;
        }
        // ------------- end the images looping ------------- //
        // ------------- close slideshow wrapper ------------- //
        $ssDisplay .= '
        </ul>
    </div>';
    }

    //**************************************************/
    //*              THE polaroid CONFIG               */
    //**************************************************/

    if ($ssConfig == 'polaroid') {
        // ------------- open slideshow wrapper ------------- //
        $ssDisplay = '
    <div id="gallery_wrap">
        <div id="polaroid_overlay">&nbsp;</div>
        <div id="photos" class="galleryview">';

        // ------------- start the images looping ------------- //
        $j = 0;
        for ($i = 0; $i < $countSlideshowFiles; $i++) {
            $ssDisplay .= '
        <div class="panel">
            <img src="' . $_ssFile['resizedimg'][$i] . '" alt="" />
            <div class="panel-overlay">
                <div class="panel-title">' . $_ssFile['title'][$i] . '</div>
                <div class="panel-description">' . $_ssFile['description'][$i] . '</div>
            </div>
        </div>';
            $j++;
            if ($j == $ssLimit)
                break;
        }
        // ------------- end the images looping ------------- //

        $ssDisplay .= '
        <ul class="filmstrip">';

        // ------------- start the images looping ------------- //
        $j = 0;
        for ($i = 0; $i < $countSlideshowFiles; $i++) {
            $ssDisplay .= '
            <li><img src="' . $_ssFile['thumbsrc'][$i] . '" alt="" title="' . $_ssFile['title'][$i] . '" /></li>';
            $j++;
            if ($j == $ssLimit)
                break;
        }
        // ------------- end the images looping ------------- //
        // ------------- close slideshow wrapper ------------- //
        $ssDisplay .= '
        </ul>
        </div>
    </div>';
    }

    //**************************************************/
    //*              THE filmstrip CONFIG              */
    //**************************************************/

    if ($ssConfig == 'filmstrip') {
        // ------------- open slideshow wrapper ------------- //
        $ssDisplay = '
    <div id="photos" class="galleryview">
        <ul class="filmstrip">';

        // ------------- start the images looping ------------- //
        $j = 0;
        $landingpage = $landingpage != '' ? $landingpage : $modx->documentIdentifier;
        for ($i = 0; $i < $countSlideshowFiles; $i++) {
            $ssDisplay .= '
            <li><a target="_self" href="' . $modx->makeUrl($landingpage, null, 'fid=' . $_ssFile['id'][$i]) . '&lp=' . $landingpage . '">
                    <img src="' . $_ssFile['thumbsrc'][$i] . '" alt="' . $_ssFile['name'][$i] . '" title="' . $_ssFile['title'][$i] . '" />
                </a>
            </li>';
            $j++;
            if ($j == $ssLimit)
                break;
        }
        // ------------- end the images looping ------------- //
        // ------------- close slideshow wrapper ------------- //
        $ssDisplay .= '
        </ul>
    </div>';
    }

    //**************************************************/
    //*                THE panel CONFIG                */
    //**************************************************/

    if ($ssConfig == 'panel') {
        // ------------- open slideshow wrapper ------------- //
        $ssDisplay = '
    <div id="photos" class="galleryview">';

        // ------------- start the images looping ------------- //
        $j = 0;
        for ($i = 0; $i < $countSlideshowFiles; $i++) {
            $ssDisplay .= '
        <div class="panel">
            <img src="' . $_ssFile['resizedimg'][$i] . '" alt="" />
            <div class="panel-overlay">
                <div class="panel-title">' . $_ssFile['title'][$i] . '</div>
                <div class="panel-description">' . $_ssFile['description'][$i] . '</div>
            </div>
        </div>';
            $j++;
            if ($j == $ssLimit)
                break;
        }
        // ------------- end the images looping ------------- //
        // ------------- close slideshow wrapper ------------- //
        $ssDisplay .= '
    </div>';
    }
    echo $ssDisplay;
}