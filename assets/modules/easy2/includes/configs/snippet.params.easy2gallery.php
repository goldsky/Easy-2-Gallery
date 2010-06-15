<?php

$e2gsnip_cfg = array();

// ROOT directory
$e2gsnip_cfg['gdir'] = $e2g['dir'];

// sanitize $_GET
if (isset($_GET)) {
    foreach ($_GET as $v) {
        if (!is_numeric($v)) {
            $v = filter_var($v, FILTER_SANITIZE_STRING);
        }
    }
}

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

// ENCODING
$e2gsnip_cfg['e2g_encode'] = (isset($e2g_encode)) ? $e2g_encode : $e2g['e2g_encode'];

// CUSTOM PARAMETER $_GET FOR OTHER SNIPPETS
if ( !empty($customgetparams)) {
    $e2gsnip_cfg['customgetparams'] = !empty($customgetparams) ? $customgetparams : NULL;
}

// WIDTH
$e2gsnip_cfg['w'] = (!empty($w) && is_numeric($w)) ? (int) $w : $e2g['w'];

// HEIGHT
$e2gsnip_cfg['h'] = (!empty($h) && is_numeric($h)) ? (int) $h : $e2g['h'];

// JPEG QUALITY
$e2gsnip_cfg['thq'] = (!empty($thq) && $thq<=100 && $thq>=0) ? (int) $thq : $e2g['thq'];

// NAME LENGTH
$e2gsnip_cfg['name_len'] = (!empty($name_len) && is_numeric($name_len)) ? (int) $name_len : $e2g['name_len'];

// DIRECTORY NAME LENGTH
$e2gsnip_cfg['cat_name_len'] = (!empty($cat_name_len) && is_numeric($cat_name_len)) ? (int) $cat_name_len : $e2g['cat_name_len'];

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

// LIMIT
$e2gsnip_cfg['limit'] = (!empty($limit) && is_numeric($limit)) ? (int) $limit : $e2g['limit'];

// SHOW ONLY: 'images' | 'folders' (under &gid parameter)
$e2gsnip_cfg['showonly'] = (!empty($showonly)) ? $showonly : NULL ;

// GLIB
$e2gsnip_cfg['glib'] = (!empty($glib)) ? $glib : $e2g['glib'];

// COMMENTS
$e2gsnip_cfg['ecm'] = (isset($ecm) && is_numeric($ecm)) ? $ecm : $e2g['ecm'];
// COMMENT LIMIT
$e2gsnip_cfg['ecl'] = (isset($ecl) && is_numeric($ecl)) ? $ecl : $e2g['ecl'];
// COMMENT LIMIT on landingpage
$e2gsnip_cfg['ecl_page'] = (isset($ecl_page) && is_numeric($ecl_page)) ? $ecl_page : $e2g['ecl_page'];
// COMMENT's CAPTCHA
$e2gsnip_cfg['captcha'] = (isset($captcha) && is_numeric($captcha)) ? $captcha : $e2g['captcha'];

// PAGE NUMBER
//$gpn = (!empty($gpn) && is_numeric($gpn)) ? $gpn : 0;
$e2gsnip_cfg['gpn'] = (!empty($_GET['gpn']) && is_numeric($_GET['gpn'])) ? (int) $_GET['gpn'] : ((!empty($gpn) && is_numeric($gpn)) ? $gpn : 0);

// ORDER BY
/*
 * Options: order by SQL fields.
*/
$orderby = (!empty($orderby)) ? $orderby : $e2g['orderby'];
$e2gsnip_cfg['orderby'] = preg_replace('/[^_a-z]/i', '', $orderby);
$cat_orderby = (!empty($cat_orderby)) ? $cat_orderby : $e2g['cat_orderby'];
$e2gsnip_cfg['cat_orderby'] = preg_replace('/[^_a-z]/i', '', $cat_orderby);

// ORDER
$order = (!empty($order)) ? $order : $e2g['order'];
$e2gsnip_cfg['order'] = preg_replace('/[^a-z]/i', '', $order);
$cat_order = (!empty($cat_order)) ? $cat_order : $e2g['cat_order'];
$e2gsnip_cfg['cat_order'] = preg_replace('/[^a-z]/i', '', $cat_order);

// GALLERY CSS
$e2gsnip_cfg['css'] = (!empty($css)) ? str_replace('../', '' , $css) : $e2g['css'];
// GALLERY JS
$e2gsnip_cfg['js'] = (!empty($js)) ? str_replace('../', '' , $js) : $e2g['js'];

// GALLERY TEMPLATE
$e2gsnip_cfg['tpl'] = (!empty($tpl)) ? str_replace('../', '' , $tpl) : $e2g['tpl'];
/*
 * GALLERY'S DESCRIPTION OPTION
 * Options: 1 = On
 *          0 = Off
*/
$e2gsnip_cfg['gal_desc'] = (!empty($gal_desc)) ? $gal_desc : 0;

// DIR TEMPLATE
$e2gsnip_cfg['dir_tpl'] = (!empty($dir_tpl)) ? str_replace('../', '', $dir_tpl) : $e2g['dir_tpl'];

// THUMB TEMPLATE
$e2gsnip_cfg['thumb_tpl'] = (!empty($thumb_tpl)) ? str_replace('../', '' , $thumb_tpl) : $e2g['thumb_tpl'];

// THUMB RAND TEMPLATE
$e2gsnip_cfg['rand_tpl'] = (!empty($rand_tpl)) ? str_replace('../', '' , $rand_tpl) : $e2g['rand_tpl'];

// LANDING PAGE TEMPLATE
$e2gsnip_cfg['page_tpl'] = (!empty($page_tpl)) ? str_replace('../', '' , $page_tpl) : $e2g['page_tpl'];
// LANDING PAGE TEMPLATE
$e2gsnip_cfg['page_tpl_css'] = (!empty($page_tpl_css)) ? str_replace('../', '' , $page_tpl_css) : $e2g['page_tpl_css'];

// COMMENT ROW TEMPLATE
$e2gsnip_cfg['comments_row_tpl'] = (!empty($comments_row_tpl)) ? str_replace('../', '' , $comments_row_tpl) : E2G_SNIPPET_PATH.$e2g['comments_row_tpl'];

// LANDINGPAGE'S COMMENT TEMPLATE
$e2gsnip_cfg['page_comments_tpl'] = (!empty($page_comments_tpl)) ? str_replace('../', '' , $page_comments_tpl) : $e2g['page_comments_tpl'];

// LANDINGPAGE'S COMMENT ROW TEMPLATE
$e2gsnip_cfg['page_comments_row_tpl'] = (!empty($page_comments_row_tpl)) ? str_replace('../', '' , $page_comments_row_tpl) : $e2g['page_comments_row_tpl'];

// CSS classes
$e2gsnip_cfg['grid_class'] = (isset($grid_class) ? $grid_class : $e2g['grid_class']);
$e2gsnip_cfg['e2g_currentcrumb_class'] = (isset($e2g_currentcrumb_class) ? $e2g_currentcrumb_class : $e2g['e2g_currentcrumb_class']);
$e2gsnip_cfg['e2gback_class'] = (isset($e2gback_class) ? $e2gback_class : $e2g['e2gback_class']);
$e2gsnip_cfg['e2gpnums_class'] = (isset($e2gpnums_class) ? $e2gpnums_class : $e2g['e2gpnums_class']);

// THUMB 'resize-type' settings: 'inner' (cropped) | 'resize' (autofit) | 'shrink' (shrink)
$e2gsnip_cfg['resize_type'] = isset($resize_type) ? $resize_type : $e2g['resize_type'];

// THUMB BACKGROUND COLOR
$e2gsnip_cfg['thbg_red'] = isset($thbg_red) ? $thbg_red : $e2g['thbg_red'];
$e2gsnip_cfg['thbg_green'] = isset($thbg_green) ? $thbg_green : $e2g['thbg_green'];
$e2gsnip_cfg['thbg_blue'] = isset($thbg_blue) ? $thbg_blue : $e2g['thbg_blue'];

// JAVASCRIPT LIBRARY'S SLIDESHOW GROUP
$e2gsnip_cfg['show_group'] = isset($show_group) ? $show_group : 'Gallery'.$gid;

/*
 * STAND ALONE SLIDESHOW PARAMETERS
*/

// SLIDESHOW TYPE
$e2gsnip_cfg['slideshow'] = isset($slideshow) ? $slideshow : NULL;
// SLIDESHOW PROCESSOR FILE. IF NOT EXIST, WILL USE DEFAULT.
$e2gsnip_cfg['ss_indexfile'] = isset($ss_indexfile) ? $ss_indexfile : NULL;
// SLIDESHOW'S BOX DIMENSION, NOT THUMBNAIL
$e2gsnip_cfg['ss_w'] = isset($ss_w) ? $ss_w : '400'; // width
$e2gsnip_cfg['ss_h'] = isset($ss_h) ? $ss_h : '300'; // mandatory existence height
$e2gsnip_cfg['ss_bg'] = isset($ss_bg) ? $ss_bg : 'white'; // slideshow background color

/*
 * additional configuration options, if there is any.
 * this is empty, only as an holder.
*/
$e2gsnip_cfg['ss_config'] = isset($ss_config) ? $ss_config : NULL ;

/*
 * &ss_allowedratio is an allowance ratio of width/height to help distinguishing
 * too tall/wide images while the &ss_w and &ss_h are limited.
 * the format is 'minfloatnumber-maxfloatnumber', eg: '1.0-2.0'
 * to disable this restriction, set &ss_allowedratio=`none`
*/
$e2gsnip_cfg['ss_allowedratio'] = isset($ss_allowedratio) ? $ss_allowedratio :
        (0.75*$e2gsnip_cfg['ss_w']/$e2gsnip_cfg['ss_h']).'-'.(1.25*$e2gsnip_cfg['ss_w']/$e2gsnip_cfg['ss_h']);
/*
 * to set how many images the slide show should retrieve from the gallery ID.
 * more images mean longer page loading!
 * @options : int | 'none'
 * @default : (int)6
*/
$e2gsnip_cfg['ss_limit'] = isset($ss_limit) ? $ss_limit : '6' ;
/*
 * set the slideshow's CSS path
*/
$e2gsnip_cfg['ss_css'] = isset($ss_css) ? $ss_css : NULL ;
/*
 * set the slideshow's CSS path
*/
$e2gsnip_cfg['ss_js'] = isset($ss_js) ? $ss_js : NULL ;
/*
 * set the slideshow's landing page.
 * @options: document ID.
*/
$e2gsnip_cfg['landingpage'] = (isset($landingpage) ? $landingpage : (!empty($_GET['lp']) ? $_GET['lp'] : NULL)) ;

// CRUMBS
$e2gsnip_cfg['crumbs_separator'] = isset($crumbs_separator) ? $crumbs_separator : ' / ';
$e2gsnip_cfg['crumbs_showHome'] = isset($crumbs_showHome) ? $crumbs_showHome : 0;
$e2gsnip_cfg['crumbs_showAsLinks'] = isset($crumbs_showAsLinks) ? $crumbs_showAsLinks : 1;
$e2gsnip_cfg['crumbs_showCurrent'] = isset($crumbs_showCurrent) ? $crumbs_showCurrent : 1;
$e2gsnip_cfg['crumbs_showPrevious'] = isset($crumbs_showPrevious) ? $crumbs_showPrevious : 0;

//mbstring
$e2gsnip_cfg['charset'] = $modx->config['modx_charset'];
$e2gsnip_cfg['mbstring'] = function_exists('mb_strlen') && function_exists('mb_substr');

/*
 * plugins interception
*/
if (isset($plugins)) $plugin=$plugins; // compatibility
$e2gsnip_cfg['plugin'] = isset($plugin) ? $plugin : null;