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
    
    $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery-1.3.2.js');
    $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery.galleriffic.js');

    if ( $ss_config=='example-2' || $ss_config=='example-5' ) {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery.opacityrollover.js');
    }
    if ( $ss_config=='example-5' ) {
        // Optionally include jquery.history.js for history support
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/galleriffic/js/jquery.history.js');
    }
    // if you want to use this, uncomment this.
    if ( $ss_config=='example-2' || $ss_config=='example-5' ) {
        $modx->regClientStartupHTMLBlock('<script type="text/javascript">jQuery.noConflict();</script>');
    }

    // header
    $modx->regClientStartupHTMLBlock('
        <!-- We only want the thunbnails to display when javascript is disabled -->
        <script type="text/javascript">
            document.write(\'<style>.noscript { display: none; }</style>\');
        </script>
            ');

    if ($ss_config=='example-1') {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // We only want these styles applied when javascript is enabled
                $(\'div.navigation\').css({\'width\' : \'300px\', \'float\' : \'left\'});
                $(\'div.content\').css(\'display\', \'block\');

                $(document).ready(function() {
                    // Initialize Minimal Galleriffic Gallery
                    $(\'#thumbs\').galleriffic({
                        imageContainerSel:      \'#slideshow\',
                        controlsContainerSel:   \'#controls\'
                    });
                });
            });
        </script>');
    }
    if ( $ss_config=='example-2' ) {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // We only want these styles applied when javascript is enabled
                $(\'div.navigation\').css({\'width\' : \'300px\', \'float\' : \'left\'});
                $(\'div.content\').css(\'display\', \'block\');

                // Initially set opacity on thumbs and add
                // additional styling for hover effect on thumbs
                var onMouseOutOpacity = 0.67;
                $(\'#thumbs ul.thumbs li\').opacityrollover({
                    mouseOutOpacity:   onMouseOutOpacity,
                    mouseOverOpacity:  1.0,
                    fadeSpeed:         \'fast\',
                    exemptionSelector: \'.selected\'
                });

                // Initialize Advanced Galleriffic Gallery
                var gallery = $(\'#thumbs\').galleriffic({
                    delay:                     2500,
                    numThumbs:                 15,
                    preloadAhead:              10,
                    enableTopPager:            true,
                    enableBottomPager:         true,
                    maxPagesToShow:            7,
                    imageContainerSel:         \'#slideshow\',
                    controlsContainerSel:      \'#controls\',
                    captionContainerSel:       \'#caption\',
                    loadingContainerSel:       \'#loading\',
                    renderSSControls:          true,
                    renderNavControls:         true,
                    playLinkText:              \'Play Slideshow\',
                    pauseLinkText:             \'Pause Slideshow\',
                    prevLinkText:              \'&lsaquo; Previous Photo\',
                    nextLinkText:              \'Next Photo &rsaquo;\',
                    nextPageLinkText:          \'Next &rsaquo;\',
                    prevPageLinkText:          \'&lsaquo; Prev\',
                    enableHistory:             false,
                    autoStart:                 false,
                    syncTransitions:           true,
                    defaultTransitionDuration: 900,
                    onSlideChange:             function(prevIndex, nextIndex) {
                        // \'this\' refers to the gallery, which is an extension of $(\'#thumbs\')
                        this.find(\'ul.thumbs\').children()
                            .eq(prevIndex).fadeTo(\'fast\', onMouseOutOpacity).end()
                            .eq(nextIndex).fadeTo(\'fast\', 1.0);
                    },
                    onPageTransitionOut:       function(callback) {
                        this.fadeTo(\'fast\', 0.0, callback);
                    },
                    onPageTransitionIn:        function() {
                        this.fadeTo(\'fast\', 1.0);
                    }
                });
            });
        </script>
        ');
    } // if ( $ss_config=='example-2' )

    if ( $ss_config=='example-5' ) {
        $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // We only want these styles applied when javascript is enabled
                $(\'div.content\').css(\'display\', \'block\');

                // Initially set opacity on thumbs and add
                // additional styling for hover effect on thumbs
                var onMouseOutOpacity = 0.67;
                $(\'#thumbs ul.thumbs li, div.navigation a.pageLink\').opacityrollover({
                    mouseOutOpacity:   onMouseOutOpacity,
                    mouseOverOpacity:  1.0,
                    fadeSpeed:         \'fast\',
                    exemptionSelector: \'.selected\'
                });

                // Initialize Advanced Galleriffic Gallery
                var gallery = $(\'#thumbs\').galleriffic({
                    delay:                     2500,
                    numThumbs:                 10,
                    preloadAhead:              10,
                    enableTopPager:            false,
                    enableBottomPager:         false,
                    imageContainerSel:         \'#slideshow\',
                    controlsContainerSel:      \'#controls\',
                    captionContainerSel:       \'#caption\',
                    loadingContainerSel:       \'#loading\',
                    renderSSControls:          true,
                    renderNavControls:         true,
                    playLinkText:              \'Play Slideshow\',
                    pauseLinkText:             \'Pause Slideshow\',
                    prevLinkText:              \'&lsaquo; Previous Photo\',
                    nextLinkText:              \'Next Photo &rsaquo;\',
                    nextPageLinkText:          \'Next &rsaquo;\',
                    prevPageLinkText:          \'&lsaquo; Prev\',
                    enableHistory:             true,
                    autoStart:                 false,
                    syncTransitions:           true,
                    defaultTransitionDuration: 900,
                    onSlideChange:             function(prevIndex, nextIndex) {
                        // \'this\' refers to the gallery, which is an extension of $(\'#thumbs\')
                        this.find(\'ul.thumbs\').children()
                            .eq(prevIndex).fadeTo(\'fast\', onMouseOutOpacity).end()
                            .eq(nextIndex).fadeTo(\'fast\', 1.0);

                        // Update the photo index display
                        this.$captionContainer.find(\'div.photo-index\')
                            .html(\'Photo \'+ (nextIndex+1) +\' of \'+ this.data.length);
                    },
                    onPageTransitionOut:       function(callback) {
                        this.fadeTo(\'fast\', 0.0, callback);
                    },
                    onPageTransitionIn:        function() {
                        var prevPageLink = this.find(\'a.prev\').css(\'visibility\', \'hidden\');
                        var nextPageLink = this.find(\'a.next\').css(\'visibility\', \'hidden\');

                        // Show appropriate next / prev page links
                        if (this.displayedPage > 0)
                            prevPageLink.css(\'visibility\', \'visible\');

                        var lastPage = this.getNumPages() - 1;
                        if (this.displayedPage < lastPage)
                            nextPageLink.css(\'visibility\', \'visible\');

                        this.fadeTo(\'fast\', 1.0);
                    }
                });

                /**************** Event handlers for custom next / prev page links **********************/

                gallery.find(\'a.prev\').click(function(e) {
                    gallery.previousPage();
                    e.preventDefault();
                });

                gallery.find(\'a.next\').click(function(e) {
                    gallery.nextPage();
                    e.preventDefault();
                });

                /****************************************************************************************/

                /**** Functions to support integration of galleriffic with the jquery.history plugin ****/

                // PageLoad function
                // This function is called when:
                // 1. after calling $.historyInit();
                // 2. after calling $.historyLoad();
                // 3. after pushing "Go Back" button of a browser
                function pageload(hash) {
                    // alert("pageload: " + hash);
                    // hash doesn\'t contain the first # character.
                    if(hash) {
                        $.galleriffic.gotoImage(hash);
                    } else {
                        gallery.gotoIndex(0);
                    }
                }

                // Initialize history plugin.
                // The callback is called at once by present location.hash.
                $.historyInit(pageload, "advanced.html");

                // set onlick event for buttons using the jQuery 1.3 live method
                $("a[rel=\'history\']").live(\'click\', function(e) {
                    if (e.button != 0) return true;

                    var hash = this.href;
                    hash = hash.replace(/^.*#/, \'\');

                    // moves to a new page.
                    // pageload is called at once.
                    // hash don\'t contain "#", "?"
                    $.historyLoad(hash);

                    return false;
                });

                /****************************************************************************************/
            });
        </script>
        ');
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
        }
    }

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