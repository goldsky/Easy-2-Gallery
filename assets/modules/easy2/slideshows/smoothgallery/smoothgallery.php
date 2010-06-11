<?php
// just to avoid direct call to this file. it's recommended to always use this.
if ( !defined(E2G_SNIPPET_URL) && $slideshow!='smoothgallery') {
    return;
}

// http://smoothgallery.jondesign.net/
else {

    /**************************************************/
    /*            PREPARE THE HTML HEADERS            */
    /**************************************************/
    $modx->regClientCSS(E2G_SNIPPET_URL.'slideshows/smoothgallery/css/jd.gallery.css','screen');
    $modx->regClientCSS(E2G_SNIPPET_URL.'slideshows/smoothgallery/css/smoothgallery.css','screen');
    // defining the dimension in CSS style
    $modx->regClientStartupHTMLBlock('
        <style type="text/css" media="screen">
        #myGallery {
            width: '.$ss_w.'px !important;
            height: '.$ss_h.'px !important;
            background-color: '.$ss_bg.';
        }
        #myGallery img .imageElement .full {
            max-width: '.$ss_w.'px !important;
            max-height: '.$ss_h.'px !important;
        }
        </style>');
    if ($ss_config=='zoom') {
        $modx->regClientCSS(E2G_SNIPPET_URL.'slideshows/smoothgallery/css/ReMooz.css','screen');
    }
    if (!empty($ss_css)) {
        $modx->regClientCSS($ss_css,'screen');
    }


    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">jQuery.noConflict();</script>');

    if ($ss_config=='fullgallery') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.js');
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
    if ($ss_config=='galleryset') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/History.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/History.Routing.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.set.js');
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
    if ($ss_config=='timedgallery') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools.v1.11.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.v2.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.set.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
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
    if ($ss_config=='simpletimedslideshow') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.js');
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
    if ($ss_config=='simpleshowcaseslideshow') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools.v1.11.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/HistoryManager.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.v2.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.set.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
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
    if ($ss_config=='timedimageswitchers') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools.v1.11.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/HistoryManager.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.v2.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.set.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
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
    if ($ss_config=='slidingtransition') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools.v1.11.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/HistoryManager.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.v2.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.set.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
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
    if ($ss_config=='horcontinuous') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
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
    if ($ss_config=='vercontinuous') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.transitions.js');
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
    if ($ss_config=='zoom') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/ReMooz.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'slideshows/smoothgallery/scripts/jd.gallery.js');
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
    /*             THE fullgallery CONFIG             */
    /*             THE timedgallery CONFIG            */
    /*         THE simpletimedslideshow CONFIG        */
    /*       THE simpleshowcaseslideshow CONFIG       */
    /*         THE timedimageswitchers CONFIG         */
    /*          THE slidingtransition CONFIG          */
    /*            THE horcontinuous CONFIG            */
    /*            THE vercontinuous CONFIG            */
    /**************************************************/

    if ( $ss_config=='fullgallery'
            || $ss_config=='timedgallery'
            || $ss_config=='simpletimedslideshow'
            || $ss_config=='simpleshowcaseslideshow'
            || $ss_config=='timedimageswitchers'
            || $ss_config=='slidingtransition'
            || $ss_config=='horcontinuous'
            || $ss_config=='vercontinuous'
    ) {
        // result with no images
        if ($count == 0) {
            $ss_display = 'No image inside the gallery id '.$gid;
            // this slideshow heavily dependent on any image existence.
            return;
        } else {
            // ------------- open slideshow wrapper ------------- //
            $ss_display .= '
<div id="myGallery">';

            // ------------- start the images looping ------------- //
            $j=0;
            for ($i=0;$i<$count;$i++) {
                $ss_display .= '
    <div class="imageElement">
        <h3>'.$_ssfile['title'][$i].'</h3>
        <p>'.$_ssfile['description'][$i].'</p>
        <a href="'
                // making flexible FURL or not
                . $modx->makeUrl($modx->documentIdentifier
                        , $modx->documentAliases
                        , 'fid='.$_ssfile['id'][$i])
                .'" title="open image" class="open"></a>
        <img src="'.$_ssfile['resizedimg'][$i].'" class="full" alt="" />
        <img src="'.$_ssfile['thumbsrc'][$i].'" class="thumbnail" alt="" />
    </div>';
//die(__LINE__.': '.$_ssfile['resizedimg'][$i]);
                // if there is a image number limitation
                $j++;
                if ($j==$ss_limit) break;
            }
            // ------------- end the images looping ------------- //

            // ------------- close slideshow wrapper ------------- //
            $ss_display .= '
</div>';
        }
    } // if ( $ss_config=='fullgallery' || $ss_config=='timedgallery' )

    /**************************************************/
    /*             THE galleryset CONFIG              */
    /**************************************************/

    if ($ss_config=='galleryset') {
        $cat_orderby = $this->e2gsnip_cfg['cat_orderby'];
        $cat_order = $this->e2gsnip_cfg['cat_order'];
        $_ssfile = array();
        unset($_ssfile);
        
        if (!empty($gid)) {

            // ************** select directories ************** //

            $selectdir = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                    . 'WHERE parent_id IN (' . $gid . ') '
                    . 'AND cat_visible = 1 '
                    . 'ORDER BY ' . $cat_orderby . ' ' . $cat_order . ' '
                    . ( $ss_limit == 'none' ? '' : 'LIMIT 0,'.$ss_limit.' ' )
            ;
            $querydir = mysql_query($selectdir);
            if (!$querydir) return $ss_display = __LINE__.' : '.mysql_error();
            $countdir = mysql_num_rows($querydir);
            while ($fetchdir = mysql_fetch_array($querydir)) {
                $galleries[$fetchdir['cat_id']]['cat_id'] = $fetchdir['cat_id'];
                $galleries[$fetchdir['cat_id']]['cat_name'] = $fetchdir['cat_name'];
            }

            // ************** select images ************** //

            if (isset($galleries)) {
                foreach ($galleries as $k => $v ) {
                    $select = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                            . 'WHERE dir_id = ' . $k . ' '
                            . 'AND status = 1 '
                            . 'ORDER BY ' . $orderby . ' ' . $order . ' '
                            . ( $ss_limit == 'none' ? '' : 'LIMIT 0,'.$ss_limit.' ' )
                    ;
                    $query = mysql_query($select) or die('319 '.$select.mysql_error());
                    $countimg[$k] = mysql_num_rows($query);
                    // for an empty folder
                    if ($countimg[$k]==0) continue;
                    
                    while ($fetch = mysql_fetch_array($query)) {
                        $path = $this->_get_path($fetch['dir_id']);
                        if (count($path) > 1) {
                            unset($path[1]);
                            $path = implode('/', array_values($path)).'/';
                        } else {
                            $path = '';
                        }
                        $_ssfile['id'][$k][] = $fetch['id'];
                        $_ssfile['dirid'][$k][] = $fetch['dir_id'];
                        $_ssfile['src'][$k][] = $gdir.$path.$fetch['filename'];
                        $_ssfile['filename'][$k][] = $fetch['filename'];
                        $_ssfile['title'][$k][] = $fetch['name'];
                        $_ssfile['description'][$k][] = $fetch['description'];
                        $_ssfile['thumbsrc'][$k][] = $this->_get_thumb($gdir, $path.$fetch['filename'], $w, $h, $thq);
                        $_ssfile['resizedimg'][$k][] = $this->_get_thumb($gdir, $path.$fetch['filename'], $ss_w, $ss_h, $thq);
                    }
                }
            }
        }
        // ------------- open slideshow wrapper ------------- //
        $ss_display .= '
<div id="myGallerySet">';
        // ------------- start the images looping ------------- //
        if(!is_array($galleries)) { // something wrong! escape!
            $ss_display = 'There is no gallery inside ID:'.$gid;
            return;
        }
        foreach ($galleries as $k => $v ) {
            $ss_display .= '
    <div id="gallery1" class="galleryElement">
        <h2>'.$v['cat_name'].'</h2>';

            $j=0;
            for ($i=0;$i<$countimg[$k];$i++) {
                $_ssfile['title'][$k][$i] = ($_ssfile['title'][$k][$i]!='' ? $_ssfile['title'][$k][$i] : $_ssfile['filename'][$k][$i]);
                $ss_display .= '
        <div class="imageElement">
            <h3>'.$_ssfile['title'][$k][$i].'</h3>
            <p>'.$_ssfile['description'][$k][$i].'</p>
            <a href="'
                // making flexible FURL or not
                . $modx->makeUrl($modx->documentIdentifier
                        , $modx->documentAliases
                        , 'fid='.$_ssfile['id'][$k][$i])
                .'" title="open image" class="open"></a>
            <img src="'.$_ssfile['resizedimg'][$k][$i].'" class="full" alt="" />
            <img src="'.$_ssfile['thumbsrc'][$k][$i].'" class="thumbnail" alt="" />
        </div>';
                // if there is a image number limitation
                $j++;
                if ($j==$ss_limit) break;
            }

            $ss_display .= '
    </div>';
        }
        // ------------- end the images looping ------------- //

        // ------------- close slideshow wrapper ------------- //
        $ss_display .= '
</div>';
    } // if ($ss_config=='galleryset')

    /**************************************************/
    /*                 THE zoom CONFIG                */
    /**************************************************/

    if ( $ss_config=='zoom' ) {
        // result with no images
        if ($count == 0) {
            $ss_display = 'No image inside the gallery';
            // this slideshow heavily dependent on any image existence.
            return;
        } else {
            // ------------- open slideshow wrapper ------------- //
            $ss_display .= '
<div id="myGallery">';

            // ------------- start the images looping ------------- //
            $j=0;
            for ($i=0;$i<$count;$i++) {
                $dim = getimagesize(utf8_decode($_ssfile['src'][$i]));
                $width[$i] = $dim[0];
                $height[$i] = $dim[1];
                $image_ratio[$i] = $width[$i]/$height[$i];

                if ($ss_allowedratio != 'none') {
                    // skipping ratio exclusion
                    if ( $ss_minratio > $image_ratio[$i] || $ss_maxratio < $image_ratio[$i] ) continue;
                }

                $ss_display .= '
    <div class="imageElement">
        <h3>'.$_ssfile['title'][$i].'</h3>
        <p>'.$_ssfile['description'][$i].'</p>
        <a href="'.str_replace('%2F','/',rawurlencode(utf8_decode($_ssfile['src'][$i]))).'" title="open image" class="open"></a>
        <img src="'.$_ssfile['resizedimg'][$i].'" class="full" alt="" '
                        . ( ( ($ss_w/$ss_h) < $image_ratio[$i] ) ? 'height="'.$ss_h.'px" ' : 'width="'.$ss_w.'px" ' )
                        .'/>
        <img src="'.$_ssfile['thumbsrc'][$i].'" class="thumbnail" alt="" />
    </div>';
                
                // if there is a image number limitation
                $j++;
                if ($j==$ss_limit) break;
            }
            // ------------- end the images looping ------------- //

            // ------------- close slideshow wrapper ------------- //
            $ss_display .= '
</div>';
        }
    } // if ( $ss_config=='fullgallery' || $ss_config=='timedgallery' )
}

