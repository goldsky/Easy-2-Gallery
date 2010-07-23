<?php
/**
 * Initial start
 */
$e2gsnip_cfg = array();

// ROOT directory
$e2gsnip_cfg['gdir'] = $e2g['dir'];

// sanitize $_GET
if (isset($_GET)) {
    foreach ($_GET as $k=>$v) {
        if (!is_numeric($v)) {
            $_GET[$k] = $modx->stripAlias($v);
        }
    }
}

// grab the E2G's snippet instance IDs from the plugin
$instance_id = $_SESSION['e2g_instances']++;
$e2gsnip_cfg['e2g_instances'] = (!empty($_GET['sid'])) ? $_GET['sid'] : $instance_id;
$e2gsnip_cfg['e2g_static_instances'] = $instance_id;

/**
 * GALLERY / ALBUM Selection Parameters
 */
if ( !empty($fid) ) {
    // FILE ID
    $e2gsnip_cfg['fid'] = (!empty($_GET['fid']) && is_numeric($_GET['fid'])) ? $_GET['fid'] : ( !empty($fid) ? $fid : null );
    $e2gsnip_cfg['static_fid'] = ( !empty($fid) ? $fid : null );
}
elseif ( !empty($rgid) ) {
    // RANDOMIZED GALLERY ID
    $e2gsnip_cfg['rgid'] = !empty($rgid) ? $rgid : null;
}
else {
    // GALLERY ID
    $e2gsnip_cfg['gid'] = (!empty($_GET['gid']) && is_numeric($_GET['gid'])) ? $_GET['gid'] : ( !empty($gid) ? $gid : 1 );
    // to get the REAL snippet's gid call
    $e2gsnip_cfg['static_gid'] = !empty($gid) ? $gid : 1 ;
}
// TAGS
if (isset($tags)) $tag=$tags; // compatibility
$e2gsnip_cfg['tag'] = !empty($_GET['tag']) ? $_GET['tag'] : ( !empty($tag) ? $tag : null );
$e2gsnip_cfg['static_tag'] = !empty($tag) ? $tag : null;

/**
 * ENCODING
 */
$e2gsnip_cfg['e2g_encode'] = (isset($e2g_encode)) ? $e2g_encode : $e2g['e2g_encode'];

/**
 * CUSTOM PARAMETER $_GET FOR OTHER SNIPPETS
 */
if ( !empty($customgetparams)) {
    $e2gsnip_cfg['customgetparams'] = !empty($customgetparams) ? $customgetparams : NULL;
}

/**
 * Image resizing
 */
// WIDTH
$e2gsnip_cfg['w'] = (!empty($w) && is_numeric($w)) ? (int) $w : $e2g['w'];
// HEIGHT
$e2gsnip_cfg['h'] = (!empty($h) && is_numeric($h)) ? (int) $h : $e2g['h'];
// JPEG QUALITY
$e2gsnip_cfg['thq'] = (!empty($thq) && $thq<=100 && $thq>=0) ? (int) $thq : $e2g['thq'];

/**
 * Naming convension
 */
// NAME LENGTH
$e2gsnip_cfg['name_len'] = (!empty($name_len) && is_numeric($name_len)) ? (int) $name_len : $e2g['name_len'];
// DIRECTORY NAME LENGTH
$e2gsnip_cfg['cat_name_len'] = (!empty($cat_name_len) && is_numeric($cat_name_len)) ? (int) $cat_name_len : $e2g['cat_name_len'];

/**
 * GRID structure
 * @options : css | table
 */
// COLLS
$e2gsnip_cfg['colls'] = (!empty($colls) && is_numeric($colls)) ? (int) $colls : $e2g['colls'];
// for compatibility of version upgrading
if (isset($notables) && $notables==1) $grid = 'css';
elseif (isset($notables) && $notables==0) $grid = 'table';
// GRID -- previously using NO TABLES
$e2gsnip_cfg['grid'] = (isset($grid) ? $grid : $e2g['grid']);
// NO TABLES -- DEPRECATED after 1.4.0 Beta 4!
//$e2gsnip_cfg['notables'] = $fid ? 1 : (isset($notables) && is_numeric($notables)) ? $notables : (isset($e2g['notables']) ? $e2g['notables'] : 0);
//$e2gsnip_cfg['notables'] = (isset($notables) && is_numeric($notables)) ? $notables : (isset($e2g['notables']) ? $e2g['notables'] : 0);

/**
 * SQL queries
 */
// LIMIT
$e2gsnip_cfg['limit'] = (!empty($limit) && is_numeric($limit)) ? (int) $limit : $e2g['limit'];
// SHOW ONLY: 'images' | 'folders' (under &gid parameter)
$e2gsnip_cfg['showonly'] = (!empty($showonly)) ? $showonly : NULL ;
// PAGE NUMBER
//$gpn = (!empty($gpn) && is_numeric($gpn)) ? $gpn : 0;
$e2gsnip_cfg['gpn'] = (!empty($_GET['gpn']) && is_numeric($_GET['gpn'])) ? (int) $_GET['gpn'] : ((!empty($gpn) && is_numeric($gpn)) ? $gpn : 0);
// ORDER BY
$orderby = (!empty($orderby)) ? $orderby : $e2g['orderby'];
$e2gsnip_cfg['orderby'] = preg_replace('/[^_a-z]/i', '', $orderby);
$cat_orderby = (!empty($cat_orderby)) ? $cat_orderby : $e2g['cat_orderby'];
$e2gsnip_cfg['cat_orderby'] = preg_replace('/[^_a-z]/i', '', $cat_orderby);
// ORDER
$order = (!empty($order)) ? $order : $e2g['order'];
$e2gsnip_cfg['order'] = preg_replace('/[^a-z]/i', '', $order);
$cat_order = (!empty($cat_order)) ? $cat_order : $e2g['cat_order'];
$e2gsnip_cfg['cat_order'] = preg_replace('/[^a-z]/i', '', $cat_order);

/**
 * GALLERY'S DESCRIPTION OPTION
 * Options: 1 = On
 *          0 = Off
 */
$e2gsnip_cfg['gal_desc'] = (!empty($gal_desc)) ? $gal_desc : 0;

/**
 * TEMPLATES
 */
// GALLERY TEMPLATE
$e2gsnip_cfg['tpl'] = (!empty($tpl)) ? str_replace('../', '' , $tpl) : $e2g['tpl'];
// DIR TEMPLATE
$e2gsnip_cfg['dir_tpl'] = (!empty($dir_tpl)) ? str_replace('../', '', $dir_tpl) : $e2g['dir_tpl'];
// THUMB TEMPLATE
$e2gsnip_cfg['thumb_tpl'] = (!empty($thumb_tpl)) ? str_replace('../', '' , $thumb_tpl) : $e2g['thumb_tpl'];
// THUMB RAND TEMPLATE
$e2gsnip_cfg['rand_tpl'] = (!empty($rand_tpl)) ? str_replace('../', '' , $rand_tpl) : $e2g['rand_tpl'];
// LANDING PAGE TEMPLATE
$e2gsnip_cfg['page_tpl'] = (!empty($page_tpl)) ? str_replace('../', '' , $page_tpl) : $e2g['page_tpl'];
// LANDING PAGE CSS
$e2gsnip_cfg['page_tpl_css'] = (!empty($page_tpl_css)) ? str_replace('../', '' , $page_tpl_css) : $e2g['page_tpl_css'];
// COMMENT ROW TEMPLATE
$e2gsnip_cfg['comments_row_tpl'] = (!empty($comments_row_tpl)) ? str_replace('../', '' , $comments_row_tpl) : E2G_SNIPPET_PATH.$e2g['comments_row_tpl'];
// LANDINGPAGE'S COMMENT TEMPLATE
$e2gsnip_cfg['page_comments_tpl'] = (!empty($page_comments_tpl)) ? str_replace('../', '' , $page_comments_tpl) : $e2g['page_comments_tpl'];
// LANDINGPAGE'S COMMENT ROW TEMPLATE
$e2gsnip_cfg['page_comments_row_tpl'] = (!empty($page_comments_row_tpl)) ? str_replace('../', '' , $page_comments_row_tpl) : $e2g['page_comments_row_tpl'];

/**
 * GALLERY JS
 */
$e2gsnip_cfg['js'] = (!empty($js)) ? str_replace('../', '' , $js) : $e2g['js'];
/**
 * GALLERY CSS
 */
$e2gsnip_cfg['css'] = (!empty($css)) ? str_replace('../', '' , $css) : $e2g['css'];
/**
 * CSS classes
 */
$e2gsnip_cfg['grid_class'] = (isset($grid_class) ? $grid_class : $e2g['grid_class']);
if (isset($e2g_currentcrumb_class)) $crumbs_classCurrent = $e2g_currentcrumb_class; // backward compatibility
$e2gsnip_cfg['crumbs_classCurrent'] = (isset($crumbs_classCurrent) ? $crumbs_classCurrent : $e2g['crumbs_classCurrent']);
if (isset($e2gback_class)) $back_class = $e2gback_class; // backward compatibility
$e2gsnip_cfg['back_class'] = (isset($back_class) ? $back_class : $e2g['back_class']);
if (isset($e2gpnums_class)) $pagenum_class = $e2gpnums_class; // backward compatibility
$e2gsnip_cfg['pagenum_class'] = (isset($pagenum_class) ? $pagenum_class : $e2g['pagenum_class']);

/**
 * THUMBNAILS
 */
/**
 * THUMB 'resize-type'
 * @param string settings: 'inner' (cropped) | 'resize' (autofit) | 'shrink' (shrink)
 */
$e2gsnip_cfg['resize_type'] = isset($resize_type) ? $resize_type : $e2g['resize_type'];
// THUMB BACKGROUND COLOR
$e2gsnip_cfg['thbg_red'] = isset($thbg_red) ? $thbg_red : $e2g['thbg_red'];
$e2gsnip_cfg['thbg_green'] = isset($thbg_green) ? $thbg_green : $e2g['thbg_green'];
$e2gsnip_cfg['thbg_blue'] = isset($thbg_blue) ? $thbg_blue : $e2g['thbg_blue'];

/**
 * Javascript's pop-up libraries
 */
// GLIB
$e2gsnip_cfg['glib'] = (!empty($glib)) ? $glib : $e2g['glib'];
// JAVASCRIPT LIBRARY'S SLIDESHOW GROUP
if (isset($static_gid)) $group_suffix = $modx->stripAlias($static_gid);
elseif (isset($static_tag)) $group_suffix = $modx->stripAlias($static_tag);
else $group_suffix='1'; // root &gid
$e2gsnip_cfg['show_group'] = isset($show_group) ? $show_group : 'Gallery_'.$group_suffix.'_'.$e2gsnip_cfg['e2g_static_instances'];

/**
 * WATERMARKS
 */
// ON/OFF = 0/1
$e2gsnip_cfg['ewm'] = isset($ewm) ? $ewm : $e2g['ewm'];
// type: text | image
$e2gsnip_cfg['wmtype'] = isset($ewmtype) ? $ewmtype : $e2g['wmtype'];
// the watermark's text | image path
$e2gsnip_cfg['wmt'] = isset($ewmt) ? $ewmt : $e2g['wmt'];
// horizontal: 1=left | 2=center | 3=right
$e2gsnip_cfg['wmpos1'] = (isset($ewmpos1) && is_numeric($ewmpos1)) ? $ewmpos1 : $e2g['wmpos1'];
// vertical : 1=top | 2=center | 3=bottom
$e2gsnip_cfg['wmpos2'] = (isset($ewmpos2) && is_numeric($ewmpos2)) ? $ewmpos2 : $e2g['wmpos2'];

/**
 * COMMENTS
 */
$e2gsnip_cfg['ecm'] = (isset($ecm) && is_numeric($ecm)) ? $ecm : $e2g['ecm'];
// COMMENT LIMIT
$e2gsnip_cfg['ecl'] = (isset($ecl) && is_numeric($ecl)) ? $ecl : $e2g['ecl'];
// COMMENT LIMIT on landingpage
$e2gsnip_cfg['ecl_page'] = (isset($ecl_page) && is_numeric($ecl_page)) ? $ecl_page : $e2g['ecl_page'];
// COMMENT's CAPTCHA
if (isset($captcha)) $recaptcha = $captcha; // backward compatibility
$e2gsnip_cfg['recaptcha'] = (isset($recaptcha) && is_numeric($recaptcha)) ? $recaptcha : $e2g['recaptcha'];
$e2gsnip_cfg['recaptcha_key_private'] = $e2g['recaptcha_key_private'];
$e2gsnip_cfg['recaptcha_key_public'] = $e2g['recaptcha_key_public'];
$e2gsnip_cfg['recaptcha_theme'] = $e2g['recaptcha_theme'];
$e2gsnip_cfg['recaptcha_theme_custom'] = $e2g['recaptcha_theme_custom'];

/**
 * Image Source
 * Because of the polemic between the size of the server's capacity,
 * and the image effect in slideshows and landingpage,
 * the image source can be selected between options: 'original' | 'generated'
 * The 'generated' images uses thumbnail creator to make a new file under
 * the _thumbnails folder.
 * WATERMARK (if ON) can be applied for the 'generated' images.
 */
// thumbnails
$e2gsnip_cfg['img_src'] = (isset($img_src) ? $img_src : 'generated');
// slideshow
$e2gsnip_cfg['ss_img_src'] = (isset($ss_img_src) ? $ss_img_src : 'generated');
// landingpage
$e2gsnip_cfg['lp_img_src'] = (isset($lp_img_src) ? $lp_img_src : 'generated');

/**
 * STAND ALONE SLIDESHOW PARAMETERS
 */
// SLIDESHOW TYPE
$e2gsnip_cfg['slideshow'] = isset($slideshow) ? $slideshow : NULL;
// additional configuration options, if this is empty, only as an holder.
$e2gsnip_cfg['ss_config'] = isset($ss_config) ? $ss_config : NULL ;
// SLIDESHOW PROCESSOR FILE. IF NOT EXIST, WILL USE DEFAULT.
$e2gsnip_cfg['ss_indexfile'] = isset($ss_indexfile) ? $ss_indexfile : NULL;
// set the slideshow's CSS path
$e2gsnip_cfg['ss_css'] = isset($ss_css) ? $ss_css : NULL ;
// set the slideshow's CSS path
$e2gsnip_cfg['ss_js'] = isset($ss_js) ? $ss_js : NULL ;

/**
 * IF, the image source =`generated`, use these more options
 * SLIDESHOW'S BOX DIMENSION, NOT THUMBNAIL
 */
$e2gsnip_cfg['ss_w'] = isset($ss_w) ? $ss_w : $e2g['ss_w']; // width
$e2gsnip_cfg['ss_h'] = isset($ss_h) ? $ss_h : $e2g['ss_h']; // mandatory existence height
$e2gsnip_cfg['ss_thq'] = (!empty($ss_thq) && $ss_thq<=100 && $ss_thq>=0) ? (int) $ss_thq : $e2g['ss_thq'];
/**
 * generating the 'resize-type'
 * @param string settings: 'inner' (cropped) | 'resize' (autofit) | 'shrink' (shrink)
 */
$e2gsnip_cfg['ss_resize_type'] = isset($ss_resize_type) ? $ss_resize_type : $e2g['ss_resize_type'];
// image's BACKGROUND COLOR
$e2gsnip_cfg['ss_bg'] = isset($ss_bg) ? $ss_bg : $e2g['ss_bg']; // image's background color
// OR, if &ss_bg=`rgb` , use below:
$e2gsnip_cfg['ss_red'] = isset($ss_red) ? $ss_red : $e2g['ss_red'];
$e2gsnip_cfg['ss_green'] = isset($ss_green) ? $ss_green : $e2g['ss_green'];
$e2gsnip_cfg['ss_blue'] = isset($ss_blue) ? $ss_blue : $e2g['ss_blue'];
/**
 * &ss_allowedratio is an allowance ratio of width/height to help distinguishing
 * too tall/wide images while the &ss_w and &ss_h are limited.
 * the format is 'minfloatnumber-maxfloatnumber', eg: '1.0-2.0'
 * to disable this restriction, set &ss_allowedratio=`none`
 */
$e2gsnip_cfg['ss_allowedratio'] = (isset($ss_allowedratio) || $ss_allowedratio!='') ? $ss_allowedratio : $e2g['ss_allowedratio'];

// ORDER BY
$ss_orderby = (!empty($ss_orderby)) ? $ss_orderby : $e2g['ss_orderby'];
$e2gsnip_cfg['ss_orderby'] = preg_replace('/[^_a-z]/i', '', $ss_orderby);
// ORDER
$ss_order = (!empty($ss_order)) ? $ss_order : $e2g['ss_order'];
$e2gsnip_cfg['ss_order'] = preg_replace('/[^a-z]/i', '', $ss_order);
/**
 * to set how many images the slide show should retrieve from the gallery ID.
 * more images mean longer page loading!
 * @options : int | 'none'
 */
$e2gsnip_cfg['ss_limit'] = isset($ss_limit) ? $ss_limit : $e2g['ss_limit'] ;

/**
 * set the slideshow's or thumbnail's landing page.
 * @options: document ID.
 */
$e2gsnip_cfg['landingpage'] = (isset($landingpage) ? $landingpage : (!empty($_GET['lp']) ? $_GET['lp'] : NULL)) ;
/**
 * IF, the image source =`generated`, use these more options
 */
$e2gsnip_cfg['lp_w'] = isset($lp_w) ? $lp_w : null; // width -> null: will be retrieved from the getimagesize() function
$e2gsnip_cfg['lp_h'] = isset($lp_h) ? $lp_h : null; // height -> null: will be retrieved from the getimagesize() function
$e2gsnip_cfg['lp_thq'] = (!empty($lp_thq) && $lp_thq<=100 && $lp_thq>=0) ? (int) $lp_thq : $e2g['thq'];
/**
 * landingpage's 'resize-type'
 * @param string settings: 'inner' (cropped) | 'resize' (autofit) | 'shrink' (shrink)
 */
$e2gsnip_cfg['lp_resize_type'] = isset($lp_resize_type) ? $lp_resize_type : 'inner';
// image's BACKGROUND COLOR
$e2gsnip_cfg['lp_bg'] = isset($lp_bg) ? $lp_bg : 'white'; // image's background color
// OR, if &lp_bg=`rgb` , use below:
$e2gsnip_cfg['lp_red'] = isset($lp_red) ? $lp_red : $e2g['thbg_red'];
$e2gsnip_cfg['lp_green'] = isset($lp_green) ? $lp_green : $e2g['thbg_green'];
$e2gsnip_cfg['lp_blue'] = isset($lp_blue) ? $lp_blue : $e2g['thbg_blue'];

/**
 * CRUMBS
 */
$e2gsnip_cfg['crumbs'] = ( isset($crumbs) && is_numeric($crumbs) ) ? $crumbs : $e2g['crumbs'] ;
$e2gsnip_cfg['crumbs_use'] = ( isset($crumbs_use) ) ? $crumbs_use : $e2g['crumbs_use'] ;
$e2gsnip_cfg['crumbs_separator'] = isset($crumbs_separator) ? $crumbs_separator : ' / ';
$e2gsnip_cfg['crumbs_showHome'] = isset($crumbs_showHome) ? $crumbs_showHome : 0;
$e2gsnip_cfg['crumbs_showAsLinks'] = isset($crumbs_showAsLinks) ? $crumbs_showAsLinks : 1;
$e2gsnip_cfg['crumbs_showCurrent'] = isset($crumbs_showCurrent) ? $crumbs_showCurrent : 1;
$e2gsnip_cfg['crumbs_showPrevious'] = isset($crumbs_showPrevious) ? $crumbs_showPrevious : 0;

/**
 * mbstring
 */
$e2gsnip_cfg['charset'] = $modx->config['modx_charset'];
$e2gsnip_cfg['mbstring'] = function_exists('mb_strlen') && function_exists('mb_substr');

/**
 * plugins interception
 */
if (isset($plugins)) $plugin=$plugins; // compatibility
$e2gsnip_cfg['plugin'] = isset($plugin) ? $plugin : null;

// set pagination on or off: 0 | 1
$e2gsnip_cfg['pagination'] = ( isset($pagination) && is_numeric($pagination) ) ? $pagination : $e2g['pagination'] ;