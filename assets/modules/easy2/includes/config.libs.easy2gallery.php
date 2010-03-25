<?php

$gid = (!empty($_GET['gid']) && is_numeric($_GET['gid'])) ? (int) $_GET['gid'] : ( !empty($gid) ? $gid : 1 );
$show_group = isset($show_group) ? $show_group : 'Gallery'.$gid;

$glibs = array(
        // first array will be the VALUE for &glib parameter of snippet call
        'colorbox' => array (
                // 'alias' will be the VALUE for the library parameter inside module
                'alias' => 'colorbox (jq)',
                // 'regclient will be use for run library files inside <head></head> tag of MODx document
                'regClient' => array (
                        'CSS' => array ( 'screen' => array (MODX_BASE_URL . 'assets/libs/colorbox/colorbox.css' )),
                        'JS' => array (
//                                'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js',
                                MODX_BASE_URL . 'assets/libs/colorbox/jquery.colorbox-min.js',
                                MODX_BASE_URL . 'assets/libs/colorbox/e2g.colorbox.js'
                        )
                ),
                // 'glibact' is used as slide show parameter of image pop-up
                // eg: if ($glib == 'shadowbox') {$l['glibact']='rel="shadowbox['.$show_group.'];player=img"';}
                'glibact' => 'rel="lightbox['.$show_group.']"',
                // 'comments' part will be use for library's comment pop-up iframe
//             if ( $glibs[$glib] ) {
//                $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id='.$row['id'].'" '.$glibs[$glib]['comments'].'>'.$row['comments'].'</a>';
//            }
                'comments' => 'class="iframe"'
        ),
        'fancybox' => array (
                'alias' => 'fancybox (jq)',
                'regClient' => array (
                        'CSS' => array ( 'screen' => array (MODX_BASE_URL . 'assets/libs/fancybox/fancybox.css' )),
                        'JS' => array (
//                                'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js',
                                MODX_BASE_URL . 'assets/libs/fancybox/jquery.js',
                                MODX_BASE_URL . 'assets/libs/fancybox/fancybox.js'
                        )
                ),
                'glibact' => 'rel="lightbox['.$show_group.']"',
                'comments' => 'class="iframe"'
        ),
        'floatbox' => array (
                'alias' => 'floatbox',
                'regClient' => array (
                        'CSS' => array ( 'screen' => array ( MODX_BASE_URL .'assets/libs/floatbox/floatbox.css' )),
                        'JS' => array (
                                MODX_BASE_URL . 'assets/libs/floatbox/floatbox.js'
                        )
                ),
                'glibact' => 'class="floatbox" rev="group:'.$show_group.'"',
                'comments' => 'rel="floatbox" rev="type:iframe width:400 height:250 enableDragResize:true controlPos:tr innerBorder:0"'
        ),
        'highslide' => array (
                'alias' => 'highslide',
                'regClient' => array (
                        'CSS' => array ( 'screen' => array (MODX_BASE_URL . 'assets/libs/highslide/highslide.css' )),
                        'JS' => array (
                                MODX_BASE_URL . 'assets/libs/highslide/highslide.js',
                                MODX_BASE_URL . 'assets/libs/highslide/highslide-settings.js'
                        )
                ),
                'glibact' => 'class="highslide" onclick="return hs.expand(this, {slideshowGroup: \'mygroup\'})"',
                'comments' => 'onclick="return hs.htmlExpand(this, { objectType: \'iframe\'} )"'
        ),
        'lightwindow' => array (
                'alias' => 'lightwindow (pt)',
                'regClient' => array (
                        'CSS' => array ( 'screen' => array (MODX_BASE_URL . 'assets/libs/lightwindow/css/lightwindow.css' )),
                        'JS' => array (
//                                'http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.3/prototype.js',
//                                'http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.2/scriptaculous.js?load=effects',
                                MODX_BASE_URL . 'assets/libs/lightwindow/js/prototype.js',
                                MODX_BASE_URL . 'assets/libs/lightwindow/js/scriptaculous.js?load=effects',
                                MODX_BASE_URL . 'assets/libs/lightwindow/js/lightwindow.src.js'
                        )
                ),
                'glibact' => 'class="lightwindow" rel="Gallery['.$show_group.']" params="lightwindow_type=image"',
                'comments' => 'class="lightwindow" params="lightwindow_type=external,lightwindow_width=400,lightwindow_height=250"'
        ),
        'shadowbox' => array (
                'alias' => 'shadowbox',
                'regClient' => array (
                        'CSS' => array ( 'screen' => array (MODX_BASE_URL . 'assets/libs/shadowbox/shadowbox.css' )),
                        'JS' => array (
                                MODX_BASE_URL . 'assets/libs/shadowbox/shadowbox.js',
                                MODX_BASE_URL . 'assets/libs/shadowbox/shadowbox-settings.js'
                        )
                ),
                'glibact' => 'rel="shadowbox['.$show_group.'];player=img"',
                'comments' => 'rel="shadowbox;width=400;height=250;player=iframe"'
        ),
        'slimbox' => array (
                'alias' => 'slimbox (mt)',
                'regClient' => array (
                        'CSS' => array ( 'screen' => array (
                                        MODX_BASE_URL . 'assets/libs/slimbox/css/slimbox.css',
                                        MODX_BASE_URL . 'assets/libs/highslide/highslide.css' // for comments
                                )
                        ),
                        'JS' => array (
//                            'http://ajax.googleapis.com/ajax/libs/mootools/1.2.2/mootools-yui-compressed.js',
                                MODX_BASE_URL . 'assets/libs/slimbox/js/mootools.js',
                                MODX_BASE_URL . 'assets/libs/slimbox/js/slimbox.js',
                                MODX_BASE_URL . 'assets/libs/highslide/highslide-iframe.js' // for comments
                        )
                ),
                'glibact' => 'rel="lightbox['.$show_group.']"',
                'comments' => 'onclick="return hs.htmlExpand(this, { objectType: \'iframe\'} )"'
        ),
        'slimbox2' => array (
                'alias' => 'slimbox2 (jq)',
                'regClient' => array (
                        'CSS' => array ( 'screen' => array (
                                        MODX_BASE_URL . 'assets/libs/slimbox/css/slimbox.css' ,
                                        MODX_BASE_URL . 'assets/libs/highslide/highslide.css' // for comments
                                )
                        ),
                        'JS' => array (
//                            'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js',
                                MODX_BASE_URL . 'assets/libs/slimbox/js/mootools.js',
                                MODX_BASE_URL . 'assets/libs/slimbox/js/slimbox2.js',
                                MODX_BASE_URL . 'assets/libs/highslide/highslide-iframe.js' // for comments
                        )
                ),
                'glibact' => 'rel="lightbox['.$show_group.']"'
        )
);
