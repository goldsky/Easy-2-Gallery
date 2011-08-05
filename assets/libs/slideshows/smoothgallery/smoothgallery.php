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
 * @link http://smoothgallery.jondesign.net/
 */
// just to avoid direct call to this file. it's recommended to always use this.
if (!defined('E2G_SNIPPET_URL') && $slideshow != 'smoothgallery') {
    return;
}

// just making a default selection
if (!isset($ssParams['ss_config']))
    $ssParams['ss_config'] = 'fullgallery';

//**************************************************/
//*            PREPARE THE HTML HEADERS            */
//**************************************************/
$modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/css/jd.gallery.css', 'screen');
$modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/css/smoothgallery.css', 'screen');
// defining the dimension in CSS style
$modx->regClientStartupHTMLBlock('
        <style type="text/css" media="screen">
        #myGallery {
            width: ' . $ssParams['ss_w'] . 'px !important;
            height: ' . $ssParams['ss_h'] . 'px !important;
            background-color: ' . ( $ssParams['ss_bg'] == 'rgb' ? 'rgb(' . $ssParams['thbg_red'] . ',' . $ssParams['thbg_green'] . ',' . $ssParams['thbg_blue'] . ')' : $ssParams['ss_bg'] ) . ';
        }
        #myGallery img .imageElement .full {
            max-width: ' . $ssParams['ss_w'] . 'px !important;
            max-height: ' . $ssParams['ss_h'] . 'px !important;
        }
        </style>');
if ($ssParams['ss_config'] == 'zoom') {
    $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/css/ReMooz.css', 'screen');
}
if (!empty($ssParams['ss_css'])) {
    $modx->regClientCSS($ssParams['ss_css'], 'screen');
}

$modx->regClientStartupHTMLBlock('
        <script type="text/javascript">jQuery.noConflict();</script>');

if ($ssParams['ss_config'] == 'fullgallery') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2-more.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            function startGallery() {
                var myGallery = new gallery($(\'myGallery\'), {
                    timed: false
                });
            }
            window.addEvent(\'domready\',startGallery);
        </script>
        ');
}
if ($ssParams['ss_config'] == 'galleryset') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2-more.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/History.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/History.Routing.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.set.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            window.addEvent(\'domready\', function() {
                document.myGallerySet = new gallerySet($(\'myGallerySet\'), {
                    timed: false
                });
            });
        </script>
        ');
}
if ($ssParams['ss_config'] == 'timedgallery') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools.v1.11.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.v2.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.set.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            function startGallery() {
                var myGallery = new gallery($(\'myGallery\'), {
                    timed: true
                });
            }
            window.onDomReady(startGallery);
        </script>
        ');
}
if ($ssParams['ss_config'] == 'simpletimedslideshow') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2-more.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            function startGallery() {
                var myGallery = new gallery($(\'myGallery\'), {
                    timed: true,
                    showArrows: false,
                    showCarousel: false
                });
            }
            window.addEvent(\'domready\', startGallery);
        </script>
        ');
}
if ($ssParams['ss_config'] == 'simpleshowcaseslideshow') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools.v1.11.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/HistoryManager.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.v2.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.set.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            function startGallery() {
                var myGallery = new gallery($(\'myGallery\'), {
                    timed: false,
                    showArrows: true,
                    showCarousel: false,
                    embedLinks: false
                });
                document.gallery = myGallery;
            }
            window.onDomReady(startGallery);
        </script>
        ');
}
if ($ssParams['ss_config'] == 'timedimageswitchers') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools.v1.11.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/HistoryManager.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.v2.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.set.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
			function startGallery() {
				var myGallery = new gallery($(\'myGallery\'), {
					timed: true,
					showArrows: false,
					showInfopane: false,
					showCarousel: false,
					embedLinks: true,
					delay: 4000
				});
				document.gallery = myGallery;
			}
			window.onDomReady(startGallery);
        </script>
        ');
}
if ($ssParams['ss_config'] == 'slidingtransition') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools.v1.11.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/HistoryManager.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.v2.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.set.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            function startGallery() {
                var myGallery = new gallery($(\'myGallery\'), {
                    timed: false,
                    useHistoryManager: true,
                    defaultTransition: "fadeslideleft"
                });
                HistoryManager.start();
            }
            window.addEvent(\'domready\', startGallery);
        </script>
        ');
}
if ($ssParams['ss_config'] == 'horcontinuous') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2-more.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
			function startGallery() {
				var myGallery = new gallery($(\'myGallery\'), {
					timed: false,
					defaultTransition: "continuoushorizontal"
				});
			}
			window.addEvent(\'domready\', startGallery);
        </script>
        ');
}
if ($ssParams['ss_config'] == 'vercontinuous') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2-more.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
			function startGallery() {
				var myGallery = new gallery($(\'myGallery\'), {
					timed: false,
					defaultTransition: "continuousvertical"
				});
			}
			window.addEvent(\'domready\', startGallery);
        </script>
        ');
}
if ($ssParams['ss_config'] == 'zoom') {
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/mootools-1.2-more.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/ReMooz.js');
    $modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/scripts/jd.gallery.js');
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
			function startGallery() {
				var myGallery = new gallery($(\'myGallery\'), {
					timed: false,
					useReMooz: true,
					embedLinks: false
				});
			}
			window.addEvent(\'domready\',startGallery);
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
//*             THE fullgallery CONFIG             */
//*             THE timedgallery CONFIG            */
//*         THE simpletimedslideshow CONFIG        */
//*       THE simpleshowcaseslideshow CONFIG       */
//*         THE timedimageswitchers CONFIG         */
//*          THE slidingtransition CONFIG          */
//*            THE horcontinuous CONFIG            */
//*            THE vercontinuous CONFIG            */
//**************************************************/

if ($ssParams['ss_config'] == 'fullgallery'
        || $ssParams['ss_config'] == 'timedgallery'
        || $ssParams['ss_config'] == 'simpletimedslideshow'
        || $ssParams['ss_config'] == 'simpleshowcaseslideshow'
        || $ssParams['ss_config'] == 'timedimageswitchers'
        || $ssParams['ss_config'] == 'slidingtransition'
        || $ssParams['ss_config'] == 'horcontinuous'
        || $ssParams['ss_config'] == 'vercontinuous'
) {

// this slideshow heavily dependent on any image existence, returns with no images
    if ($ssFiles['count'] == 0) {
        return 'No image inside the specified id(s),'
        . (!empty($ssParams['gid']) ? ' gid:' . $ssParams['gid'] : '')
        . (!empty($ssParams['fid']) ? ' fid:' . $ssParams['fid'] : '');
    }

    // ------------- open slideshow wrapper ------------- //
    $output .= '
<div id="myGallery">';

    // ------------- start the images looping ------------- //
    for ($i = 0; $i < $ssFiles['count']; $i++) {
        $output .= '
    <div class="imageElement">
        <h3>' . $ssFiles['title'][$i] . '</h3>
        <p>' . $ssFiles['description'][$i] . '</p>
        <a href="'
                // making flexible FURL or not
                . $modx->makeUrl(
                        $modx->documentIdentifier
                        , $modx->aliases
                        , 'sid=' . $ssParams['sid']
                        . '&fid=' . $ssFiles['id'][$i]
                )
                . '#' . $ssParams['sid'] . '_' . $ssFiles['id'][$i] . '"'
                . ' title="open image" class="open"></a>
        <img src="' . $ssFiles['resizedimg'][$i] . '" class="full" alt="" />
        <img src="' . $ssFiles['thumbsrc'][$i] . '" class="thumbnail" alt="" />
    </div>';
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $output .= '
</div>';
} // if ( $ssParams['ss_config']=='fullgallery' || $ssParams['ss_config']=='timedgallery' )
//**************************************************/
//*             THE galleryset CONFIG              */
//**************************************************/

if ($ssParams['ss_config'] == 'galleryset') {
    $ssFiles = array();
    if (!empty($ssParams['gid'])) {

        // ************** select directories ************** //

        $selectDir = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE parent_id IN (' . $ssParams['gid'] . ') '
                . 'AND cat_visible = 1 '
                . 'ORDER BY ' . $ssParams['cat_orderby'] . ' ' . $ssParams['cat_order'] . ' '
                . ( $ssParams['ss_limit'] == 'none' ? '' : 'LIMIT 0,' . $ssParams['ss_limit'] . ' ' )
        ;
        $queryDir = mysql_query($selectDir);
        if (!$queryDir)
            return $output = __LINE__ . ' : ' . mysql_error();
        $countDir = mysql_num_rows($queryDir);
        while ($fetchdir = mysql_fetch_array($queryDir)) {
            $galleries[$fetchdir['cat_id']]['cat_id'] = $fetchdir['cat_id'];
            $galleries[$fetchdir['cat_id']]['cat_name'] = $fetchdir['cat_name'];
        }

        // ************** select images ************** //

        if (isset($galleries)) {
            foreach ($galleries as $k => $v) {
                $select = $this->_fileSqlStatement('*', $ssParams['ss_allowedratio'], $k);
                $select .= 'ORDER BY ' . $ssParams['ss_orderby'] . ' ' . $ssParams['ss_order'] . ' '
                        . ( $ssParams['ss_limit'] == 'none' ? '' : 'LIMIT 0,' . $ssParams['ss_limit'] . ' ' )
                ;
                $query = mysql_query($select) or die(__LINE__ . ' ' . mysql_error() . '<br />' . $select);
                $countImg[$k] = mysql_num_rows($query);
                // for an empty folder
                if ($countImg[$k] == 0)
                    unset($galleries[$k]);

                while ($fetch = mysql_fetch_assoc($query)) {
                    $ssRows = $this->_processSlideshowFiles($fetch);
                    if ($ssRows === FALSE)
                        continue;
                    foreach ($ssRows as $key => $val) {
                        $ssFiles[$key][$k][] = $val;
                    }
                }
            }
        }
    }
    // ------------- open slideshow wrapper ------------- //
    $output .= '
<div id="myGallerySet">';
    // ------------- start the images looping ------------- //
    if (!is_array($galleries)) { // something wrong! escape!
        return 'There is no gallery inside ID:' . $ssParams['gid'];

    }
    foreach ($galleries as $gk => $gv) {
        $output .= '
    <div id="gallery1" class="galleryElement">
        <h2>' . $gv['cat_name'] . '</h2>';

        for ($i = 0; $i < $countImg[$gk]; $i++) {
            $ssFiles['title'][$gk][$i] = ($ssFiles['title'][$gk][$i] != '' ? $ssFiles['title'][$gk][$i] : $ssFiles['filename'][$gk][$i]);
            $output .= '
        <div class="imageElement">
            <h3>' . $ssFiles['title'][$gk][$i] . '</h3>
            <p>' . $ssFiles['description'][$gk][$i] . '</p>
            <a href="'
                    // making flexible FURL or not
                    . $modx->makeUrl(
                            $modx->documentIdentifier
                            , $modx->aliases
                            , 'fid=' . $ssFiles['id'][$gk][$i])
                    . '" title="open image" class="open"></a>
            <img src="' . $ssFiles['resizedimg'][$gk][$i] . '" class="full" alt="" />
            <img src="' . $ssFiles['thumbsrc'][$gk][$i] . '" class="thumbnail" alt="" />
        </div>';
        }

        $output .= '
    </div>';
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $output .= '
</div>';
} // if ($ssParams['ss_config']=='galleryset')
//**************************************************/
//*                 THE zoom CONFIG                */
//**************************************************/

if ($ssParams['ss_config'] == 'zoom') {
    // this slideshow heavily dependent on any image existence, returns with no images
    if ($ssFiles['count'] == 0) {
        return 'No image inside the specified id(s),'
        . (!empty($ssParams['gid']) ? ' gid:' . $ssParams['gid'] : '')
        . (!empty($ssParams['fid']) ? ' fid:' . $ssParams['fid'] : '');
    }

    // ------------- open slideshow wrapper ------------- //
    $output .= '
<div id="myGallery">';

    // ------------- start the images looping ------------- //
    for ($i = 0; $i < $ssFiles['count']; $i++) {
        $dim = getimagesize($this->e2gDecode($ssFiles['src'][$i]));
        $width[$i] = $dim[0];
        $height[$i] = $dim[1];
        $imageRatio[$i] = $width[$i] / $height[$i];

        $output .= '
    <div class="imageElement">
        <h3>' . $ssFiles['title'][$i] . '</h3>
        <p>' . strip_tags($ssFiles['description'][$i], '<a>') . '</p>
        <a href="' . str_replace('%2F', '/', rawurlencode($this->e2gDecode($ssFiles['src'][$i]))) . '" title="open image" class="open"></a>
        <img src="' . $ssFiles['resizedimg'][$i] . '" class="full" alt="" '
                . ( ( ($ssParams['ss_w'] / $ssParams['ss_h']) < $imageRatio[$i] ) ? 'height="' . $ssParams['ss_h'] . 'px" ' : 'width="' . $ssParams['ss_w'] . 'px" ' )
                . '/>
        <img src="' . $ssFiles['thumbsrc'][$i] . '" class="thumbnail" alt="" />
    </div>';
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $output .= '
</div>';
} // if ($ssParams['ss_config'] == 'zoom')

return $output;