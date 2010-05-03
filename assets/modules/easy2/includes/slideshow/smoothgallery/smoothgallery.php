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
    $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/css/jd.gallery.css','screen');
    $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/css/smoothgallery.css','screen');
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
        $modx->regClientCSS(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/css/ReMooz.css','screen');
    }
    if (!empty($ss_css)) {
        $modx->regClientCSS($ss_css,'screen');
    }


    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">jQuery.noConflict();</script>');

    if ($ss_config=='fullgallery') {
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.js');
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
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/History.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/History.Routing.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.set.js');
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
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools.v1.11.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.v2.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.set.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.transitions.js');
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
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.js');
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
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools.v1.11.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/HistoryManager.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.v2.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.set.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.transitions.js');
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
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools.v1.11.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/HistoryManager.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.v2.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.set.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.transitions.js');
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
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools.v1.11.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/HistoryManager.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.v2.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.set.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.transitions.js');
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
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.transitions.js');
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
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.transitions.js');
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
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2.1-core-yc.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/mootools-1.2-more.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/ReMooz.js');
        $modx->regClientStartupScript(E2G_SNIPPET_URL.'includes/slideshow/smoothgallery/scripts/jd.gallery.js');
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

        // ------------- open slideshow wrapper ------------- //
        $ss_display .= '
<div id="myGallery">';

        // ------------- start the images looping ------------- //
        $j=0;
        for ($i=0;$i<$count;$i++) {
            $ss_display .= '
    <div class="imageElement">
        <h3>'.$name[$i].'</h3>
        <p>'.$description[$i].'</p>
        <a href="[~[*id*]~]?fid='.$fileid[$i].'" title="open image" class="open"></a>
        <img src="'.$slide_images[$i].'" class="full" alt="" />
        <img src="'.$thumbsrc[$i].'" class="thumbnail" alt="" />
    </div>';

            // if there is a image number limitation
            $j++;
            if ($j==$ss_limit) break;
        }
        // ------------- end the images looping ------------- //

        // ------------- close slideshow wrapper ------------- //
        $ss_display .= '
</div>';

    } // if ( $ss_config=='fullgallery' || $ss_config=='timedgallery' )

    /**************************************************/
    /*             THE galleryset CONFIG              */
    /**************************************************/

    if ($ss_config=='galleryset') {
        $cat_orderby = $this->cl_cfg['cat_orderby'];
        $cat_order = $this->cl_cfg['cat_order'];
        if (!empty($gid)) {

            // ************** select directories ************** //

            $selectdir = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                    . 'WHERE parent_id IN (' . $gid . ') '
                    . 'AND cat_visible = 1 '
                    . 'ORDER BY ' . $cat_orderby . ' ' . $cat_order . ' '
                    . ( $ss_limit == 'none' ? '' : 'LIMIT 0,'.$ss_limit.' ' )
            ;
            $querydir = mysql_query($selectdir) or die('307 '.mysql_error());
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
                    $query = mysql_query($select) or die('320 '.$select.mysql_error());
                    $countimg[$k] = mysql_num_rows($query);

                    while ($fetch = mysql_fetch_array($query)) {
                        $path = $this->_get_path($fetch['dir_id']);
                        if (count($path) > 1) {
                            unset($path[1]);
                            $path = implode('/', array_values($path)).'/';
                        } else {
                            $path = '';
                        }
                        $fileid[$k][] = $fetch['id'];
                        $dirid[$k][] = $fetch['dir_id'];
                        $images[$k][] = $e2g['dir'].$path.$fetch['filename'];
                        $filename[$k][] = $fetch['filename'];
                        $title[$k][] = $fetch['name'];
                        $description[$k][] = $fetch['description'];
                        $thumbsrc[$k][] = $this->_get_thumb($cl_cfg, $gdir, $path.$fetch['filename'], $w, $h, $thq);
                        $slide_images[$k][] = $this->_get_thumb($cl_cfg, $gdir, $path.$fetch['filename'], $ss_w, $ss_h, $thq);
                    }
                }
            }
        }
        // ------------- open slideshow wrapper ------------- //
        $ss_display .= '
<div id="myGallerySet">';
        // ------------- start the images looping ------------- //
        foreach ($galleries as $k => $v ) {
            $ss_display .= '
    <div id="gallery1" class="galleryElement">
        <h2>'.$v['cat_name'].'</h2>';

            $j=0;
            for ($i=0;$i<$countimg[$k];$i++) {
                $name[$k][$i] = ($title[$k][$i]!='' ? $title[$k][$i] : $filename[$k][$i]);
                $ss_display .= '
        <div class="imageElement">
            <h3>'.$name[$k][$i].'</h3>
            <p>'.$description[$k][$i].'</p>
            <a href="[~[*id*]~]?fid='.$fileid[$k][$i].'" title="open image" class="open"></a>
            <img src="'.$slide_images[$k][$i].'" class="full" alt="" />
            <img src="'.$thumbsrc[$k][$i].'" class="thumbnail" />
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

        // ------------- open slideshow wrapper ------------- //
        $ss_display .= '
<div id="myGallery">';

        // ------------- start the images looping ------------- //
        $j=0;
        for ($i=0;$i<$count;$i++) {
            $dim = getimagesize(utf8_decode($images[$i]));
            $width[$i] = $dim[0];
            $height[$i] = $dim[1];
            $image_ratio[$i] = $width[$i]/$height[$i];

            if ($ss_allowedratio != 'none') {
                // skipping ratio exclusion
                if ( $ss_minratio > $image_ratio[$i] || $ss_maxratio < $image_ratio[$i] ) continue;
            }

            $ss_display .= '
    <div class="imageElement">
        <h3>'.$name[$i].'</h3>
        <p>'.$description[$i].'</p>
        <a href="'.str_replace('%2F','/',rawurlencode(utf8_decode($images[$i]))).'" title="open image" class="open"></a>
        <img src="'.$slide_images[$i].'" class="full" alt="" '
            . ( ( ($ss_w/$ss_h) < $image_ratio[$i] ) ? 'height="'.$ss_h.'px" ' : 'width="'.$ss_w.'px" ' )
                    .'/>
        <img src="'.$thumbsrc[$i].'" class="thumbnail" alt="" />
    </div>';

            // if there is a image number limitation
            $j++;
            if ($j==$ss_limit) break;
        }
        // ------------- end the images looping ------------- //

        // ------------- close slideshow wrapper ------------- //
        $ss_display .= '
</div>';

    } // if ( $ss_config=='fullgallery' || $ss_config=='timedgallery' )
}

