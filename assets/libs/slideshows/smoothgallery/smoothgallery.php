<?php

/**
 * @link http://smoothgallery.jondesign.net/
 */
// just to avoid direct call to this file. it's recommended to always use this.
if (!defined(E2G_SNIPPET_URL) && $slideshow != 'smoothgallery') {
    return;
}

// just making a default selection
if (!isset($ssConfig))
    $ssConfig = 'fullgallery';

//**************************************************/
//*            PREPARE THE HTML HEADERS            */
//**************************************************/
$modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/css/jd.gallery.css', 'screen');
$modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/css/smoothgallery.css', 'screen');
// defining the dimension in CSS style
$modx->regClientStartupHTMLBlock('
        <style type="text/css" media="screen">
        #myGallery {
            width: ' . $ssW . 'px !important;
            height: ' . $ssH . 'px !important;
            background-color: ' . ( $ssBg == 'rgb' ? 'rgb(' . $thbgRed . ',' . $thbgGreen . ',' . $thbgBlue . ')' : $ssBg ) . ';
        }
        #myGallery img .imageElement .full {
            max-width: ' . $ssW . 'px !important;
            max-height: ' . $ssH . 'px !important;
        }
        </style>');
if ($ssConfig == 'zoom') {
    $modx->regClientCSS(MODX_BASE_URL . 'assets/libs/slideshows/smoothgallery/css/ReMooz.css', 'screen');
}
if (!empty($ssCss)) {
    $modx->regClientCSS($ssCss, 'screen');
}


$modx->regClientStartupHTMLBlock('
        <script type="text/javascript">jQuery.noConflict();</script>');

if ($ssConfig == 'fullgallery') {
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
if ($ssConfig == 'galleryset') {
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
if ($ssConfig == 'timedgallery') {
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
if ($ssConfig == 'simpletimedslideshow') {
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
if ($ssConfig == 'simpleshowcaseslideshow') {
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
if ($ssConfig == 'timedimageswitchers') {
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
if ($ssConfig == 'slidingtransition') {
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
if ($ssConfig == 'horcontinuous') {
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
if ($ssConfig == 'vercontinuous') {
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
if ($ssConfig == 'zoom') {
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
//*             THE fullgallery CONFIG             */
//*             THE timedgallery CONFIG            */
//*         THE simpletimedslideshow CONFIG        */
//*       THE simpleshowcaseslideshow CONFIG       */
//*         THE timedimageswitchers CONFIG         */
//*          THE slidingtransition CONFIG          */
//*            THE horcontinuous CONFIG            */
//*            THE vercontinuous CONFIG            */
//**************************************************/

if ($ssConfig == 'fullgallery'
        || $ssConfig == 'timedgallery'
        || $ssConfig == 'simpletimedslideshow'
        || $ssConfig == 'simpleshowcaseslideshow'
        || $ssConfig == 'timedimageswitchers'
        || $ssConfig == 'slidingtransition'
        || $ssConfig == 'horcontinuous'
        || $ssConfig == 'vercontinuous'
) {
    // result with no images
    if ($countSlideshowFiles == 0) {
        $ssDisplay = 'No image inside the gallery id ' . $gid;
        // this slideshow heavily dependent on any image existence.
        return FALSE;
    }

    // ------------- open slideshow wrapper ------------- //
    $ssDisplay .= '
<div id="myGallery">';

    // ------------- start the images looping ------------- //
    $j = 0;
    for ($i = 0; $i < $countSlideshowFiles; $i++) {
        $ssDisplay .= '
    <div class="imageElement">
        <h3>' . $_ssFile['title'][$i] . '</h3>
        <p>' . $_ssFile['description'][$i] . '</p>
        <a href="'
                // making flexible FURL or not
                . $modx->makeUrl(
                        $modx->documentIdentifier
                        , $modx->aliases
                        , 'fid=' . $_ssFile['id'][$i])
                . '" title="open image" class="open"></a>
        <img src="' . $_ssFile['resizedimg'][$i] . '" class="full" alt="" />
        <img src="' . $_ssFile['thumbsrc'][$i] . '" class="thumbnail" alt="" />
    </div>';

        // if there is a image number limitation
        $j++;
        if ($j == $ssLimit)
            break;
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $ssDisplay .= '
</div>';
} // if ( $ssConfig=='fullgallery' || $ssConfig=='timedgallery' )
//**************************************************/
//*             THE galleryset CONFIG              */
//**************************************************/

if ($ssConfig == 'galleryset') {
    $cat_orderBy = $this->e2gsnip_cfg['cat_orderby'];
    $cat_order = $this->e2gsnip_cfg['cat_order'];
    $_ssFile = array();

    if (!empty($gid)) {

        // ************** select directories ************** //

        $selectDir = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE parent_id IN (' . $gid . ') '
                . 'AND cat_visible = 1 '
                . 'ORDER BY ' . $cat_orderBy . ' ' . $cat_order . ' '
                . ( $ssLimit == 'none' ? '' : 'LIMIT 0,' . $ssLimit . ' ' )
        ;
        $queryDir = mysql_query($selectDir);
        if (!$queryDir)
            return $ssDisplay = __LINE__ . ' : ' . mysql_error();
        $countDir = mysql_num_rows($queryDir);
        while ($fetchdir = mysql_fetch_array($queryDir)) {
            $galleries[$fetchdir['cat_id']]['cat_id'] = $fetchdir['cat_id'];
            $galleries[$fetchdir['cat_id']]['cat_name'] = $fetchdir['cat_name'];
        }

        // ************** select images ************** //

        if (isset($galleries)) {
            foreach ($galleries as $k => $v) {
                $select = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'WHERE dir_id = ' . $k . ' ';

                if ($ssAllowedRatio != 'all') {
                    $select .= 'AND width/height >=' . floatval($ssMinRatio) . ' AND width/height<=' . floatval($ssMaxRatio) .' ';
                }

                $select .= 'AND status = 1 '
                        . 'ORDER BY ' . $ssOrderBy . ' ' . $ssOrder . ' '
                        . ( $ssLimit == 'none' ? '' : 'LIMIT 0,' . $ssLimit . ' ' )
                ;
                $query = mysql_query($select) or die(__LINE__ . ' ' . mysql_error() . '<br />' . $select);
                $countImg[$k] = mysql_num_rows($query);
                // for an empty folder
                if ($countImg[$k] == 0)
                    unset($galleries[$k]);

                while ($fetch = mysql_fetch_array($query)) {
                    $path = $this->_getPath($fetch['dir_id']);
                    if (count($path) > 1) {
                        unset($path[1]);
                        $path = implode('/', array_values($path)) . '/';
                    } else {
                        $path = '';
                    }
                    $_ssFile['id'][$k][] = $fetch['id'];
                    $_ssFile['dirid'][$k][] = $fetch['dir_id'];
                    $_ssFile['src'][$k][] = $gdir . $path . $fetch['filename'];
                    $_ssFile['filename'][$k][] = $fetch['filename'];
                    $_ssFile['title'][$k][] = $fetch['name'];
                    $_ssFile['description'][$k][] = $fetch['description'];
                    $_ssFile['thumbsrc'][$k][] = $this->_imgShaper($gdir, $path . $fetch['filename'], $w, $h, $thq);
                    $_ssFile['resizedimg'][$k][] = $this->_imgShaper($gdir, $path . $fetch['filename'], $ssW, $ssH, $thq);
                }
            }
        }
    }
    // ------------- open slideshow wrapper ------------- //
    $ssDisplay .= '
<div id="myGallerySet">';
    // ------------- start the images looping ------------- //
    if (!is_array($galleries)) { // something wrong! escape!
        $ssDisplay = 'There is no gallery inside ID:' . $gid;
        return;
    }
    foreach ($galleries as $gk => $gv) {
        $ssDisplay .= '
    <div id="gallery1" class="galleryElement">
        <h2>' . $gv['cat_name'] . '</h2>';

        $j = 0;
        for ($i = 0; $i < $countImg[$gk]; $i++) {
            $_ssFile['title'][$gk][$i] = ($_ssFile['title'][$gk][$i] != '' ? $_ssFile['title'][$gk][$i] : $_ssFile['filename'][$gk][$i]);
            $ssDisplay .= '
        <div class="imageElement">
            <h3>' . $_ssFile['title'][$gk][$i] . '</h3>
            <p>' . $_ssFile['description'][$gk][$i] . '</p>
            <a href="'
                    // making flexible FURL or not
                    . $modx->makeUrl(
                            $modx->documentIdentifier
                            , $modx->aliases
                            , 'fid=' . $_ssFile['id'][$gk][$i])
                    . '" title="open image" class="open"></a>
            <img src="' . $_ssFile['resizedimg'][$gk][$i] . '" class="full" alt="" />
            <img src="' . $_ssFile['thumbsrc'][$gk][$i] . '" class="thumbnail" alt="" />
        </div>';
            // if there is a image number limitation
            $j++;
            if ($j == $ssLimit)
                break;
        }

        $ssDisplay .= '
    </div>';
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $ssDisplay .= '
</div>';
} // if ($ssConfig=='galleryset')
//**************************************************/
//*                 THE zoom CONFIG                */
//**************************************************/

if ($ssConfig == 'zoom') {

    // result with no images
    if ($countSlideshowFiles == 0) {
        $ssDisplay = 'No image inside the gallery';
        // this slideshow heavily dependent on any image existence.
        return FALSE;
    }
    // ------------- open slideshow wrapper ------------- //
    $ssDisplay .= '
<div id="myGallery">';

    // ------------- start the images looping ------------- //
    $j = 0;
    for ($i = 0; $i < $countSlideshowFiles; $i++) {
        $dim = getimagesize($this->_e2gDecode($_ssFile['src'][$i]));
        $width[$i] = $dim[0];
        $height[$i] = $dim[1];
        $imageRatio[$i] = $width[$i] / $height[$i];

        $ssDisplay .= '
    <div class="imageElement">
        <h3>' . $_ssFile['title'][$i] . '</h3>
        <p>' . $_ssFile['description'][$i] . '</p>
        <a href="' . str_replace('%2F', '/', rawurlencode($this->_e2gDecode($_ssFile['src'][$i]))) . '" title="open image" class="open"></a>
        <img src="' . $_ssFile['resizedimg'][$i] . '" class="full" alt="" '
                . ( ( ($ssW / $ssH) < $imageRatio[$i] ) ? 'height="' . $ssH . 'px" ' : 'width="' . $ssW . 'px" ' )
                . '/>
        <img src="' . $_ssFile['thumbsrc'][$i] . '" class="thumbnail" alt="" />
    </div>';

        // if there is a image number limitation
        $j++;
        if ($j == $ssLimit)
            break;
    }
    // ------------- end the images looping ------------- //
    // ------------- close slideshow wrapper ------------- //
    $ssDisplay .= '
</div>';
} // if ($ssConfig == 'zoom')

echo $ssDisplay;