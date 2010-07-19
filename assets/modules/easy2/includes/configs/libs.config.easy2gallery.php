<?php

$glibs = array(
        // first array will be the VALUE for &glib parameter of snippet call
        'colorbox' => array (
        // 'alias' is the VALUE for the library's options inside module
                'alias' => 'colorbox 1.3.8 (jq)',
                // 'regclient will be used to run library files inside <head></head> tag of MODx document
                'regClient' => array (
                        'CSS' => array ( 'screen' => array (
                                        MODX_BASE_URL . 'assets/libs/colorbox/colorbox.css'
                                )
                        )
                        ,'JS' => array (
                                MODX_BASE_URL . 'assets/libs/jquery/jquery-1.4.2.min.js'
                                , MODX_BASE_URL . 'assets/libs/colorbox/jquery.colorbox-min.js'
                                , MODX_BASE_URL . 'assets/libs/colorbox/e2g.colorbox.js'
                        )
                )

                // 'glibact' is used as slideshow parameter of image pop-up
                // remember the template:
                // <a href="[+easy2:link+]" title="[+easy2:title+]-[+easy2:summary+]" [+easy2:glibact+]>
                , 'glibact' => 'class="cboxElement" rel="group['.$show_group.']"'

                // 'comments' part will be use for library's comment pop-up iframe
                // if ( $glibs[$glib] ) {
                //    $row['comments'] = '<a href="' . E2G_SNIPPET_URL . 'comments.easy2gallery.php?id='.$row['id'].'" '.$glibs[$glib]['comments'].'>'.$row['comments'].'</a>';
                // }
                , 'comments' => 'class="iframe"'
        )
        ,'fancybox' => array (
                'alias' => 'fancybox 1.3.1 (jq)'
                , 'regClient' => array (
                        'CSS' => array ( 'screen' => array (
                                        MODX_BASE_URL . 'assets/libs/fancybox/jquery.fancybox-1.3.1.css'
                                )
                        )
                        , 'JS' => array (
                                "http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"
                                , MODX_BASE_URL . 'assets/libs/fancybox/jquery.fancybox-1.3.1.pack.js'
                                , MODX_BASE_URL . 'assets/libs/fancybox/jquery.easing-1.3.pack.js'
                                , MODX_BASE_URL . 'assets/libs/fancybox/jquery.mousewheel-3.0.2.pack.js'
                                , MODX_BASE_URL . 'assets/libs/fancybox/e2g.fancybox.js'
                        )
                        , 'htmlblock' => array ('
                            <script type="text/javascript">
                            $(document).ready(function() {
                                $("a[rel='.$show_group.']").fancybox({
                                    \'padding\'         : 10,
                                    \'margin\'          : 0,
                                    \'transitionIn\'    : \'elastic\',
                                    \'transitionOut\'   : \'elastic\',
                                    \'titlePosition\'   : \'over\',
                                    \'type\'            : \'image\',
                                    \'titleFormat\'     : function(title, currentArray, currentIndex, currentOpts) {
                                        return \'<span id="fancybox-title-over">Image \' + (currentIndex + 1) + \' / \' + currentArray.length + (title.length ? \' &nbsp; \' + title : \'\') + \'</span>\';
                                    }
                                });
                            });
                             </script>'
                        )
                )
                , 'glibact' => 'class="'.$show_group.'" rel="'.$show_group.'"'
                , 'comments' => 'class="comment"'
        )
        , 'floatbox' => array (
                'alias' => 'floatbox 4.04'
                , 'regClient' => array (
                        'CSS' => array ( 'screen' => array (
                                        MODX_BASE_URL .'assets/libs/floatbox/floatbox.css'
                                )
                        )
                        , 'JS' => array (
                                MODX_BASE_URL . 'assets/libs/floatbox/floatbox.js'
                                , MODX_BASE_URL . 'assets/libs/floatbox/e2g.options.js'
                        )
                )
                , 'glibact' => 'class="floatbox" data-fb-options="doSlideshow:false group:'.$show_group.' type:img caption:#description_'.$fid.'"'
                , 'comments' => 'class="floatbox" data-fb-options="width:400 height:320 enableDragResize:true controlPos:tr innerBorder:0"'
        )
        , 'highslide' => array (
                'alias' => 'highslide 4.1.8'
                , 'regClient' => array (
                        'CSS' => array ( 'screen' => array (
                                        MODX_BASE_URL . 'assets/libs/highslide/highslide.css'
                                )
                        )
                        , 'JS' => array (
                                MODX_BASE_URL . 'assets/libs/highslide/highslide-full.js'
                                , MODX_BASE_URL . 'assets/libs/highslide/e2g.highslide.js'
                        )
                        , 'htmlblock' => array ('
                            <script type="text/javascript">
                                hs.addSlideshow({
                                    slideshowGroup: \''.$show_group.'\',
                                    interval: 5000,
                                    repeat: false,
                                    useControls: true,
                                    fixedControls: \'fit\',
                                    overlayOptions: {
                                        opacity: .6,
                                        position: \'bottom center\'
                                    }
                                });
                             </script>'
                        )
                )
                , 'glibact' => 'class="highslide" onclick="return hs.expand(this, {slideshowGroup: \''.$show_group.'\'})"'
                , 'comments' => 'onclick="return hs.htmlExpand(this, { objectType: \'iframe\' } )"'
        )
        , 'lightwindow' => array (
                'alias' => 'lightwindow 2.0 (pt)'
                , 'regClient' => array (
                        'CSS' => array ( 'screen' => array (
                                        MODX_BASE_URL . 'assets/libs/lightwindow/css/lightwindow.css'
                                )
                        )
                        , 'JS' => array (
//                                'http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.3/prototype.js',
//                                'http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.2/scriptaculous.js?load=effects',
                                MODX_BASE_URL . 'assets/libs/lightwindow/js/prototype.js'
                                , MODX_BASE_URL . 'assets/libs/lightwindow/js/scriptaculous.js?load=effects'
                                , MODX_BASE_URL . 'assets/libs/lightwindow/js/lightwindow.src.js'
                        )
                )
                , 'glibact' => 'class="lightwindow" rel="Gallery['.$show_group.']" params="lightwindow_type=image"'
                , 'comments' => 'class="lightwindow" params="lightwindow_type=external,lightwindow_width=400,lightwindow_height=250"'
        )
        , 'shadowbox' => array (
                'alias' => 'shadowbox 3.0.3 (base)'
                , 'regClient' => array (
                        'CSS' => array ( 'screen' => array (MODX_BASE_URL . 'assets/libs/shadowbox/shadowbox.css' ))
                        , 'JS' => array (
                                MODX_BASE_URL . 'assets/libs/shadowbox/shadowbox.js'
                                , MODX_BASE_URL . 'assets/libs/shadowbox/e2g.shadowbox.js'
                        )
                )
                , 'glibact' => 'rel="shadowbox['.$show_group.'];player=img"'
                , 'comments' => 'rel="shadowbox;width=400;height=250;player=iframe"'
        )
        , 'slimbox' => array (
                'alias' => 'slimbox 1.71 (mt)'
                , 'regClient' => array (
                        'CSS' => array ( 'screen' => array (
                                        MODX_BASE_URL . 'assets/libs/slimbox-1.71/css/slimbox.css'
                                        , MODX_BASE_URL . 'assets/libs/highslide/highslide.css' // for comments
                                )
                        )
                        , 'JS' => array (
//                            'http://ajax.googleapis.com/ajax/libs/mootools/1.2.2/mootools-yui-compressed.js',
                                MODX_BASE_URL . 'assets/libs/slimbox-1.71/js/mootools.js'
                                , MODX_BASE_URL . 'assets/libs/slimbox-1.71/js/slimbox.js'
                                , MODX_BASE_URL . 'assets/libs/highslide/highslide-iframe.js' // for comments
                        )
                )
                , 'glibact' => 'rel="lightbox['.$show_group.']"'
                , 'comments' => 'onclick="return hs.htmlExpand(this, { objectType: \'iframe\'} )"'
        )
        , 'slimbox2' => array (
                'alias' => 'slimbox2 2.04 (jq)'
                , 'regClient' => array (
                        'CSS' => array ( 'screen' => array (
                                        MODX_BASE_URL . 'assets/libs/slimbox-2.04/css/slimbox2.css'
                                        , MODX_BASE_URL . 'assets/libs/highslide/highslide.css' // for comments
                                )
                        )
                        , 'JS' => array (
                                MODX_BASE_URL . 'assets/libs/jquery/jquery-1.4.2.min.js'
                                , MODX_BASE_URL . 'assets/libs/slimbox-2.04/js/slimbox2.js'
                                , MODX_BASE_URL . 'assets/libs/highslide/highslide-iframe.js' // for comments
                        )
                )
                , 'glibact' => 'rel="lightbox['.$show_group.']"'
                , 'comments' => 'onclick="return hs.htmlExpand(this, { objectType: \'iframe\'} )"'
        )
);
