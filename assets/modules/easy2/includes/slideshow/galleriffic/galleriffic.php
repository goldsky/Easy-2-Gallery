<?php
// just to avoid direct call to this file. it's recommended to always use this.
if ( !defined(E2G_SNIPPET_URL) && $slideshow!='galleriffic') {
    die();
}

//http://www.twospy.com/galleriffic/
else {
//    $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/css/basic.css');
    if ($ss_config=='example-1') {
        $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/css/galleriffic-1.css');
    }
    if ($ss_config=='example-2') {
        $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/css/galleriffic-2.css');
    }
    if ($ss_config=='example-5') {
        $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/css/galleriffic-5.css');
//        $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/css/white.css');
        $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/css/black.css');
    }

    // defining the dimension in CSS style
    $modx->regClientStartupHTMLBlock('
        <style type="text/css" media="screen">
         div.slideshow img {
            position: absolute;
            left: 0px;
            max-width: '.$ss_w.'px;
            max-height: '.$ss_h.'px; /* This should be set to be at least the height of the largest image in the slideshow */
        }
        div.slideshow-container {
            background-color: '.$ss_bg.';
        }
        </style>');

    // overiding a custom CSS if there is any
    if (!($ss_css)) {
        $modx->regClientCSS($ss_css);
    }

    // Javascript
    if ($ss_config=='example-1') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery-1.3.2.js');
        // Optionally include jquery.history.js for history support
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery.galleriffic.js');
    }
    if ($ss_config=='example-2') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery-1.3.2.js');
        // Optionally include jquery.history.js for history support
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery.galleriffic.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery.opacityrollover.js');
    }
    if ($ss_config=='example-5') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery-1.3.2.js');
        // Optionally include jquery.history.js for history support
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery.history.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery.galleriffic.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery.opacityrollover.js');
    }
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">jQuery.noConflict();</script>');
    // header
    $modx->regClientStartupHTMLBlock('
        <!-- We only want the thunbnails to display when javascript is disabled -->
        <script type="text/javascript">document.write(\'<style>.noscript { display: none; }</style>\');</script>');

    if ($ss_config=='example-1') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/galleriffic-1.js');
    }
    if ( $ss_config=='example-2' ) {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/galleriffic-2.js');
    }

    if ( $ss_config=='example-5' ) {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/galleriffic-5.js');
    }

    if ( $ss_config != 'example-5' ) {
    // start the galleriffic part.
    $ss_display = '
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
    if ( $ss_config == 'example-5' ) {
    $ss_display = '
<!-- Start Advanced Gallery Html Containers -->
<div class="navigation-container">
    <div id="thumbs" class="navigation">
        <a class="pageLink prev" style="visibility: hidden;" href="#" title="Previous Page"></a>
        <ul class="thumbs noscript">';
    }

    $j=0;
    for ($i=0;$i<$count;$i++) {

        if ($ss_config=='example-1') {
            $ss_display .= '
        <li>
            <a class="thumb" href="'.$images[$i].'" title="'.$title[$i].'">' . ( $title[$i]!='' ? $title[$i] : $filename[$i] ) . '</a>
        </li>';
            // if there is a image number limitation
            $j++;
            if ($j==$ss_limit) break;
        } // if ($ss_config=='example-1')

        // holding the thumbnail path and dimension
        if ($ss_config!='example-1') {
            $name[$i] = $title[$i]!='' ? $title[$i] : $filename[$i];
                    $path = $this->_get_path($dirid[$i]);
            if (count($path) > 1) {
                unset($path[1]);
                $path = implode('/', array_values($path)).'/';
            } else {
                $path = '';
            }

            $w = $row['w'] = $this->cl_cfg['w'];
            $h = $row['h'] = $this->cl_cfg['h'];
            $thq = $this->cl_cfg['thq'];

            $thumbsrc[$i] = $this->_get_thumb($e2g['dir'], $path.$filename[$i], $w, $h, $thq);
        }

        // display the gallery thumbs
        if ( $ss_config=='example-2' ) {
            $ss_display .= '
        <li>
            <a class="thumb" name="'.$name[$i].'" href="'.$images[$i].'">
                <img src="'.$thumbsrc[$i].'" />
            </a>
            <div class="caption">
                <div class="download">
                    <a href="'.$images[$i].'">Download Original</a>
                </div>
                <div class="image-title">'.$name[$i].'</div>
                <div class="image-desc">'.$description[$i].'</div>
            </div>
        </li>';
            // if there is a image number limitation
            $j++;
            if ($j==$ss_limit) break;
        } // if ( $ss_config=='example-2' )
        
        if ( $ss_config=='example-5' ) {
            $ss_display .= '
            <li>
                <a class="thumb" name="'.$name[$i].'" href="'.$images[$i].'" title="'.$name[$i].'">
                    <img src="'.$thumbsrc[$i].'" alt="'.$name[$i].'" />
                </a>
                <div class="caption">
                    <div class="image-title">'.$name[$i].'</div>
                    <div class="image-desc">'.$description[$i].'</div>

                    <div class="download">
                        <a href="'.$images[$i].'">Download Original</a>
                    </div>
                </div>
            </li>';
        } // if ( $ss_config=='example-5' )

    } // for ($i=0;$i<$count;$i++)

    // closing the HTML slideshow container
    if ( $ss_config!='example-5' ) {
        $ss_display .= '
</ul>
</div>';
    }
    if ( $ss_config=='example-5' ) {
        $ss_display .= '
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


} // else