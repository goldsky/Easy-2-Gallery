<?php
// just to avoid direct call to this file. it's recommended to always use this.
if ( !defined(E2G_SNIPPET_URL) && $slideshow!='galleryview') {
    return;
}
// result with no images
elseif ($count == 0) {
    $ss_display = 'No image inside the gallery id '.$gid;
    // this slideshow heavily dependent on any image existence.
    return;
}
//http://spaceforaname.com/galleryview
else {

    /**************************************************/
    /*            PREPARE THE HTML HEADERS            */
    /**************************************************/

    if (!empty($ss_css)) {
        $modx->regClientCSS($ss_css,'screen');
    } else {
        $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/galleryview/galleryview.css','screen');
    }
    if ($ss_config=='polaroid') {
        $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/galleryview/themes/polaroid/polaroid.css','screen');
    }
    if (empty($ss_css)) {
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
    $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleryview/jquery.easing.1.3.js');
    $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleryview/jquery.galleryview-1.1.js');
    $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleryview/jquery.timers-1.1.2.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">jQuery.noConflict();</script>');
    if ($ss_config=='gallerylight') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: '.$ss_w.',
                    panel_height: '.$ss_h.',
                    frame_width: '.$w.',
                    frame_height: '.$w.'
                });
            });
        </script>');
    }
    if ($ss_config=='gallerydark') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: '.$ss_w.',
                    panel_height: '.$ss_h.',
                    frame_width: '.$w.',
                    frame_height: '.$h.',
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
    if ($ss_config=='topfilmstrip') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: '.$ss_w.',
                    panel_height: '.$ss_h.',
                    frame_width: '.$w.',
                    frame_height: '.$h.',
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
    if ($ss_config=='polaroid') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: '.$ss_w.',
                    panel_height: '.$ss_h.',
                    frame_width: '.$w.',
                    frame_height: '.$h.',
                    transition_speed: 1200,
                    background_color: \'transparent\',
                    border: \'none\',
                    easing: \'easeOutBounce\',
                    nav_theme: \'dark\'
                });
            });
        </script>');
    }
    if ($ss_config=='filmstrip') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    filmstrip_size: 4,
                    frame_width: '.$w.',
                    frame_height: '.$h.',
                    background_color: \'transparent\',
                    nav_theme: \'dark\',
                    border: \'none\',
                    show_captions:true,
                    caption_text_color: \'black\'
                });
            });
        </script>');
    }
    if ($ss_config=='panel') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery(\'#photos\').galleryView({
                    panel_width: '.$ss_w.',
                    panel_height: '.$ss_h.',
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
    if (!empty($ss_js)) {
        $modx->regClientStartupScript($ss_js);
    }
    
    /**************************************************/
    /**************************************************/
    /***                                            ***/
    /***           THE SLIDESHOW DISPLAY            ***/
    /***                                            ***/
    /**************************************************/
    /**************************************************/

    /**************************************************/
    /*             THE gallerylight CONFIG            */
    /*             THE gallerydark CONFIG             */
    /*             THE topfilmstrip CONFIG            */
    /**************************************************/

    if ($ss_config=='gallerylight' || $ss_config=='gallerydark' || $ss_config=='topfilmstrip') {
        // ------------- open slideshow wrapper ------------- //
        $ss_display = '
    <div id="photos" class="galleryview">';

        // ------------- start the images looping ------------- //
        $j=0;
        for ($i=0;$i<$count;$i++) {
            $ss_display .= '
        <div class="panel">
            <img src="'.$_ssfile['resizedimg'][$i].'" alt="" />
            <div class="panel-overlay">
                <div class="panel-title">'.$_ssfile['title'][$i].'</div>
                <div class="panel-description">'.$_ssfile['description'][$i].'</div>
            </div>
        </div>';
            $j++;
            if ($j==$ss_limit) break;
        }
        // ------------- end the images looping ------------- //

        $ss_display .= '
      <ul class="filmstrip">';

        // ------------- start the images looping ------------- //
        $j=0;
        for ($i=0;$i<$count;$i++) {
            $ss_display .= '
            <li><img src="'.$_ssfile['thumbsrc'][$i].'" alt="" title="'.$_ssfile['title'][$i].'" /></li>';
            $j++;
            if ($j==$ss_limit) break;
        }
        // ------------- end the images looping ------------- //

        // ------------- close slideshow wrapper ------------- //
        $ss_display .= '
        </ul>
    </div>';
    }

    /**************************************************/
    /*              THE polaroid CONFIG               */
    /**************************************************/

    if ($ss_config=='polaroid') {
        // ------------- open slideshow wrapper ------------- //
        $ss_display = '
    <div id="gallery_wrap">
        <div id="polaroid_overlay">&nbsp;</div>
        <div id="photos" class="galleryview">';

        // ------------- start the images looping ------------- //
        $j=0;
        for ($i=0;$i<$count;$i++) {
            $ss_display .= '
        <div class="panel">
            <img src="'.$_ssfile['resizedimg'][$i].'" alt="" />
            <div class="panel-overlay">
                <div class="panel-title">'.$_ssfile['title'][$i].'</div>
                <div class="panel-description">'.$_ssfile['description'][$i].'</div>
            </div>
        </div>';
            $j++;
            if ($j==$ss_limit) break;
        }
        // ------------- end the images looping ------------- //

        $ss_display .= '
        <ul class="filmstrip">';

        // ------------- start the images looping ------------- //
        $j=0;
        for ($i=0;$i<$count;$i++) {
            $ss_display .= '
            <li><img src="'.$_ssfile['thumbsrc'][$i].'" alt="" title="'.$_ssfile['title'][$i].'" /></li>';
            $j++;
            if ($j==$ss_limit) break;
        }
        // ------------- end the images looping ------------- //

        // ------------- close slideshow wrapper ------------- //
        $ss_display .= '
        </ul>
        </div>
    </div>';
    }

    /**************************************************/
    /*              THE filmstrip CONFIG               */
    /**************************************************/

    if ($ss_config=='filmstrip') {
        // ------------- open slideshow wrapper ------------- //
        $ss_display = '
    <div id="photos" class="galleryview">
        <ul class="filmstrip">';

        // ------------- start the images looping ------------- //
        $j=0;
        for ($i=0;$i<$count;$i++) {
            $ss_display .= '
            <li><a target="_self" href="'.$modx->makeUrl($landingpage).'&fid='.$_ssfile['id'][$i].'&lp='.$landingpage.'">
                    <img src="'.$_ssfile['thumbsrc'][$i].'" alt="'.$_ssfile['name'][$i].'" title="'.$_ssfile['title'][$i].'" />
                </a>
            </li>';
            $j++;
            if ($j==$ss_limit) break;
        }
        // ------------- end the images looping ------------- //

        // ------------- close slideshow wrapper ------------- //
        $ss_display .= '
        </ul>
    </div>';
    }
    
    /**************************************************/
    /*                THE panel CONFIG                */
    /**************************************************/

    if ($ss_config=='panel') {
        // ------------- open slideshow wrapper ------------- //
        $ss_display = '
    <div id="photos" class="galleryview">';

        // ------------- start the images looping ------------- //
        $j=0;
        for ($i=0;$i<$count;$i++) {
            $ss_display .= '
        <div class="panel">
            <img src="'.$_ssfile['resizedimg'][$i].'" alt="" />
            <div class="panel-overlay">
                <div class="panel-title">'.$_ssfile['title'][$i].'</div>
                <div class="panel-description">'.$_ssfile['description'][$i].'</div>
            </div>
        </div>';
            $j++;
            if ($j==$ss_limit) break;
        }
        // ------------- end the images looping ------------- //

        // ------------- close slideshow wrapper ------------- //
        $ss_display .= '
    </div>';
    }
}