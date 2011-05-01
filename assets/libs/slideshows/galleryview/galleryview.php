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
 * Galleryview
 * @link http://spaceforaname.com/galleryview
 */
// just to avoid direct call to this file. it's recommended to always use this.
if (!defined('E2G_SNIPPET_URL') && $slideshow != 'galleryview') {
    return FALSE;
}

// this slideshow heavily dependent on any image existence, returns with no images
if ($ssFiles['count'] == 0) {
    return 'No image inside the specified id(s),'
    . (!empty($ssParams['gid']) ? ' gid:' . $ssParams['gid'] : '')
    . (!empty($ssParams['fid']) ? ' fid:' . $ssParams['fid'] : '');
}

// just making a default selection
if (!isset($ssParams['ss_config'])) {
    $ssParams['ss_config'] = 'gallerylight';
}

//**************************************************/
/*            PREPARE THE HTML HEADERS            */
//**************************************************/

if (!empty($ssParams['ss_css'])) {
    $modx->regClientCSS($ssParams['ss_css'], 'screen');
} else {
    $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleryview/galleryview.css', 'screen');
}
if ($ssParams['ss_config'] == 'polaroid') {
    $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/galleryview/themes/polaroid/polaroid.css', 'screen');
}
if (empty($ssParams['ss_css'])) {
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
        </style>
        ');
}
$modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/jquery/jquery-1.3.2.min.js');
$modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleryview/jquery.easing.1.3.js');
$modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleryview/jquery.galleryview-1.1.js');
$modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/galleryview/jquery.timers-1.1.2.js');
$modx->regClientStartupHTMLBlock('
        <script type="text/javascript">jQuery.noConflict();</script>');
if ($ssParams['ss_config'] == 'gallerylight') {
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: ' . $ssParams['ss_w'] . ',
                    panel_height: ' . $ssParams['ss_h'] . ',
                    frame_width: ' . $ssParams['w'] . ',
                    frame_height: ' . $ssParams['h'] . '
                });
            });
        </script>
        ');
}
if ($ssParams['ss_config'] == 'gallerydark') {
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: ' . $ssParams['ss_w'] . ',
                    panel_height: ' . $ssParams['ss_h'] . ',
                    frame_width: ' . $ssParams['w'] . ',
                    frame_height: ' . $ssParams['h'] . ',
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
        </script>
        ');
}
if ($ssParams['ss_config'] == 'topfilmstrip') {
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: ' . $ssParams['ss_w'] . ',
                    panel_height: ' . $ssParams['ss_h'] . ',
                    frame_width: ' . $ssParams['w'] . ',
                    frame_height: ' . $ssParams['h'] . ',
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
        </script>
        ');
}
if ($ssParams['ss_config'] == 'polaroid') {
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: ' . $ssParams['ss_w'] . ',
                    panel_height: ' . $ssParams['ss_h'] . ',
                    frame_width: ' . $ssParams['w'] . ',
                    frame_height: ' . $ssParams['h'] . ',
                    transition_speed: 1200,
                    background_color: \'transparent\',
                    border: \'none\',
                    easing: \'easeOutBounce\',
                    nav_theme: \'dark\'
                });
            });
        </script>
        ');
}
if ($ssParams['ss_config'] == 'filmstrip') {
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    filmstrip_size: 4,
                    frame_width: ' . $ssParams['w'] . ',
                    frame_height: ' . $ssParams['h'] . ',
                    background_color: \'transparent\',
                    nav_theme: \'dark\',
                    border: \'none\',
                    show_captions:true,
                    caption_text_color: \'black\'
                });
            });
        </script>
        ');
}
if ($ssParams['ss_config'] == 'panel') {
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: ' . $ssParams['ss_w'] . ',
                    panel_height: ' . $ssParams['ss_h'] . ',
                    transition_speed: 1500,
                    transition_interval: 5000,
                    nav_theme: \'dark\',
                    border: \'1px solid white\',
                    pause_on_hover: true
                });
            });
        </script>
        ');
}

// override with own settings
if (!empty($ssParams['ss_js'])) {
    $modx->regClientStartupScript($ssParams['ss_js']);
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

if ($ssParams['ss_config'] == 'gallerylight'
        || $ssParams['ss_config'] == 'gallerydark'
        || $ssParams['ss_config'] == 'topfilmstrip'
) {
    // ------------- open slideshow wrapper ------------- //
    $output = '
    <div id="photos" class="galleryview">';

    // ------------- start the images looping ------------- //
    for ($i = 0; $i < $ssFiles['count']; $i++) {
        $output .= '
        <div class="panel">
            <img src="' . $ssFiles['resizedimg'][$i] . '" alt="' . $ssFiles['title'][$i] . '" title="' . $ssFiles['title'][$i] . '" />
            <div class="panel-overlay">
                <div class="panel-title">' . $ssFiles['title'][$i] . '</div>
                <div class="panel-description">' . $ssFiles['description'][$i] . '</div>
            </div>
        </div>
        ';
    }
    // ------------- end the images looping ------------- //

    $output .= '
      <ul class="filmstrip">
      ';

    // ------------- start the images looping ------------- //
    for ($i = 0; $i < $ssFiles['count']; $i++) {
        $output .= '
            <li style="list-style-type: none;"><img src="' . $ssFiles['thumbsrc'][$i] . '" alt="' . $ssFiles['title'][$i] . '" title="' . $ssFiles['title'][$i] . '" /></li>
            ';
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $output .= '
        </ul>
    </div>
    ';
}

//**************************************************/
//*              THE polaroid CONFIG               */
//**************************************************/

if ($ssParams['ss_config'] == 'polaroid') {
    // ------------- open slideshow wrapper ------------- //
    $output = '
    <div id="gallery_wrap">
        <div id="polaroid_overlay">&nbsp;</div>
        <div id="photos" class="galleryview">
        ';

    // ------------- start the images looping ------------- //
    for ($i = 0; $i < $ssFiles['count']; $i++) {
        $output .= '
        <div class="panel">
            <img src="' . $ssFiles['resizedimg'][$i] . '" alt="' . $ssFiles['title'][$i] . '" title="' . $ssFiles['title'][$i] . '" />
            <div class="panel-overlay">
                <div class="panel-title">' . $ssFiles['title'][$i] . '</div>
                <div class="panel-description">' . $ssFiles['description'][$i] . '</div>
            </div>
        </div>
        ';
    }
    // ------------- end the images looping ------------- //

    $output .= '
        <ul class="filmstrip">
        ';

    // ------------- start the images looping ------------- //
    for ($i = 0; $i < $ssFiles['count']; $i++) {
        $output .= '
            <li><img src="' . $ssFiles['thumbsrc'][$i] . '" alt="' . $ssFiles['title'][$i] . '" title="' . $ssFiles['title'][$i] . '" /></li>
            ';
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $output .= '
        </ul>
        </div>
    </div>
    ';
}

//**************************************************/
//*              THE filmstrip CONFIG              */
//**************************************************/

if ($ssParams['ss_config'] == 'filmstrip') {
    // ------------- open slideshow wrapper ------------- //
    $output = '
    <div id="photos" class="galleryview">
        <ul class="filmstrip">
        ';

    // ------------- start the images looping ------------- //
    for ($i = 0; $i < $ssFiles['count']; $i++) {
        $output .= '
            <li>
                <a target="_self" href="'
                . $modx->makeUrl(
                        $modx->documentIdentifier,
                        $modx->aliases,
                        'sid=' . $ssParams['sid']
                        . '&fid=' . $ssFiles['id'][$i]
                )
                . '#' . $ssParams['sid'] . '_' . $ssFiles['id'][$i] . '">
                    <img src="' . $ssFiles['thumbsrc'][$i] . '" alt="' . $ssFiles['alias'][$i] . '" title="' . $ssFiles['title'][$i] . '" />
                </a>
            </li>
            ';
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $output .= '
        </ul>
    </div>
    ';
}

//**************************************************/
//*                THE panel CONFIG                */
//**************************************************/

if ($ssParams['ss_config'] == 'panel') {
    // ------------- open slideshow wrapper ------------- //
    $output = '
    <div id="photos" class="galleryview">
    ';

    // ------------- start the images looping ------------- //
    for ($i = 0; $i < $ssFiles['count']; $i++) {
        $output .= '
        <div class="panel">
            <img src="' . $ssFiles['resizedimg'][$i] . '" alt="' . $ssFiles['title'][$i] . '" />
            <div class="panel-overlay">
                <div class="panel-title">' . $ssFiles['title'][$i] . '</div>
                <div class="panel-description">' . $ssFiles['description'][$i] . '</div>
            </div>
        </div>
        ';
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $output .= '
    </div>
    ';
}

return $output;