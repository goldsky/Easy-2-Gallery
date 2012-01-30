<?php

/**
 * Initial start
 */
$e2gSnipCfg = array();

// ROOT directory
$e2gSnipCfg['gdir'] = $e2g['dir'];

// grab the E2G's snippet instance IDs from the plugin
$instance_id = $_SESSION['e2g_instances']++;
$e2gSnipCfg['e2g_instances'] = isset($_GET['sid']) ? $_GET['sid'] : $instance_id;
$e2gSnipCfg['e2g_static_instances'] = $instance_id;
$e2gSnipCfg['e2g_wrapper'] = $e2g['e2g_wrapper'];

/**
 * GALLERY / ALBUM Selection Parameters
 */
// FILE ID
$e2gSnipCfg['fid'] = (isset($_GET['fid']) && is_numeric($_GET['fid'])) ? $_GET['fid'] : (isset($fid) ? $fid : NULL );
$e2gSnipCfg['static_fid'] = isset($fid) ? $fid : NULL;
// RANDOMIZED GALLERY ID
$e2gSnipCfg['rgid'] = isset($rgid) ? $rgid : NULL;
// GALLERY ID
if (!isset($gid) && !isset($fid) && !isset($rgid)) {
    $gid = 1;
}
//$e2gSnipCfg['gid'] = (isset($_GET['gid']) && is_numeric($_GET['gid'])) ? $_GET['gid'] : (isset($gid) ? $gid : 1 );
$e2gSnipCfg['gid'] = (isset($_GET['gid']) && is_numeric($_GET['gid'])) ? $_GET['gid'] : (isset($gid) ? $gid : NULL );
// to get the REAL snippet's gid call
//$e2gSnipCfg['static_gid'] = isset($gid) ? $gid : 1;
$e2gSnipCfg['static_gid'] = isset($gid) ? $gid : NULL;

// TAGS
if (isset($tags))
    $tag = $tags; // compatibility
$e2gSnipCfg['tag'] = isset($_GET['tag']) ? $_GET['tag'] : (isset($tag) ? $tag : NULL );
$e2gSnipCfg['static_tag'] = isset($tag) ? $tag : NULL;

/**
 * ENCODING
 */
$e2gSnipCfg['e2g_encode'] = (isset($e2g_encode)) ? $e2g_encode : $e2g['e2g_encode'];

/**
 * CUSTOM PARAMETER $_GET FOR OTHER SNIPPETS
 */
$e2gSnipCfg['customgetparams'] = isset($customgetparams) ? $customgetparams : NULL;

/**
 * Image resizing
 */
// WIDTH
$e2gSnipCfg['w'] = isset($w) && strtolower($w) == 'auto' ? strtolower($w) : (is_numeric($w) ? (int) $w : $e2g['w']);
//$e2gSnipCfg['w'] = (isset($w) && is_numeric($w)) ? (int) $w : $e2g['w'];
// HEIGHT
$e2gSnipCfg['h'] = isset($h) && strtolower($h) == 'auto' ? strtolower($h) : (is_numeric($h) ? (int) $h : $e2g['h']);
//$e2gSnipCfg['h'] = (isset($h) && is_numeric($h)) ? (int) $h : $e2g['h'];
// JPEG QUALITY
$e2gSnipCfg['thq'] = (isset($thq) && $thq <= 100 && $thq >= 0) ? (int) $thq : $e2g['thq'];

// Folder thumbnail dimension will be the same as the image dimension above,
// if the parameter is not set in the snippet call.
// Folder thumbnail WIDTH
$e2gSnipCfg['folder_w'] = (isset($folder_w) && is_numeric($folder_w)) ? (int) $folder_w : $e2gSnipCfg['w'];
// Folder thumbnail HEIGHT
$e2gSnipCfg['folder_h'] = (isset($folder_h) && is_numeric($folder_h)) ? (int) $folder_h : $e2gSnipCfg['h'];
// Folder thumbnail JPEG QUALITY
$e2gSnipCfg['folder_thq'] = (isset($folder_thq) && $folder_thq <= 100 && $folder_thq >= 0) ? (int) $folder_thq : $e2gSnipCfg['thq'];

/**
 * GRID structure
 * @options : css | table
 */
// COLLS
$e2gSnipCfg['colls'] = (isset($colls) && is_numeric($colls)) ? (int) $colls : $e2g['colls'];
// for compatibility of version upgrading
if (isset($notables) && $notables === '1') {
    $grid = 'div';
} elseif (isset($notables) && $notables === '0') {
    $grid = 'table';
}
// GRID -- previously using NO TABLES
$e2gSnipCfg['grid'] = isset($grid) ? $grid : $e2g['grid'];
// switch 'css' to 'div' since 1.4.9-pl
$e2gSnipCfg['grid'] = $e2gSnipCfg['grid'] === 'css' ? 'div' : $e2gSnipCfg['grid'];

// NO TABLES -- DEPRECATED after 1.4.0 Beta 4!
//$e2gSnipCfg['notables'] = $fid ? 1 : (isset($notables) && is_numeric($notables)) ? $notables : (isset($e2g['notables']) ? $e2g['notables'] : 0);
//$e2gSnipCfg['notables'] = (isset($notables) && is_numeric($notables)) ? $notables : (isset($e2g['notables']) ? $e2g['notables'] : 0);

/**
 * SQL queries
 */
// WHERE CLAUSE
// @todo $where_dir $where_file
$e2gSnipCfg['where_dir'] = isset($where_dir) ? $where_dir : NULL;
$e2gSnipCfg['where_file'] = isset($where_file) ? $where_file : NULL;

// LIMIT
$e2gSnipCfg['limit'] = (isset($limit) && is_numeric($limit)) ? (int) $limit : $e2g['limit'];
// SHOW ONLY: 'images' | 'folders' (under &gid parameter)
$e2gSnipCfg['showonly'] = isset($showonly) ? $showonly : NULL;
if ($e2gSnipCfg['showonly'] == 'dirs')
    $e2gSnipCfg['showonly'] = 'folders';

// PAGE NUMBER
//$gpn = (isset($gpn) && is_numeric($gpn)) ? $gpn : 0;
$e2gSnipCfg['gpn'] = (isset($_GET['gpn']) && is_numeric($_GET['gpn'])) ? (int) $_GET['gpn'] : ((isset($gpn) && is_numeric($gpn)) ? $gpn : 0);

// Thumbnail's ORDER BY
$orderby = (isset($orderby)) ? $orderby : $e2g['orderby'];
$e2gSnipCfg['orderby'] = preg_replace('/[^_a-z]/i', '', $orderby);
// Thumbnail's ORDER
$order = (isset($order)) ? $order : $e2g['order'];
$e2gSnipCfg['order'] = preg_replace('/[^a-z]/i', '', $order);

// Folder's / directory's ORDER BY
$cat_orderby = (isset($cat_orderby)) ? $cat_orderby : $e2g['cat_orderby'];
$e2gSnipCfg['cat_orderby'] = preg_replace('/[^_a-z]/i', '', $cat_orderby);
// Folder's / directory's ORDER
$catOrder = (isset($cat_order)) ? $cat_order : $e2g['cat_order'];
$e2gSnipCfg['cat_order'] = preg_replace('/[^a-z]/i', '', $catOrder);

// Folder's thumbnail ORDER BY
$cat_thumb_orderby = (isset($cat_thumb_orderby)) ? $cat_thumb_orderby : $e2g['cat_thumb_orderby'];
$e2gSnipCfg['cat_thumb_orderby'] = preg_replace('/[^_a-z]/i', '', $cat_thumb_orderby);
// Folder's thumbnail ORDER
$cat_thumb_order = (isset($cat_thumb_order)) ? $cat_thumb_order : $e2g['cat_thumb_order'];
$e2gSnipCfg['cat_thumb_order'] = preg_replace('/[^a-z]/i', '', $cat_thumb_order);

/**
 * GALLERY'S DESCRIPTION OPTION
 * Options: 1 = On
 *          0 = Off
 */
$e2gSnipCfg['gal_desc'] = (isset($gal_desc)) ? $gal_desc : $e2g['gal_desc'];
$e2gSnipCfg['gal_desc_continuous'] = (isset($gal_desc_continuous)) ? $gal_desc_continuous : $e2g['gal_desc_continuous'];

/**
 * TEMPLATES
 */
// GALLERY TEMPLATE
$e2gSnipCfg['tpl'] = (isset($tpl)) ? str_replace('../', '', $tpl) : $e2g['tpl'];
// Description TEMPLATE
$e2gSnipCfg['desc_tpl'] = (isset($desc_tpl)) ? str_replace('../', '', $desc_tpl) : $e2g['desc_tpl'];
// DIR TEMPLATE
$e2gSnipCfg['dir_tpl'] = (isset($dir_tpl)) ? str_replace('../', '', $dir_tpl) : $e2g['dir_tpl'];
// THUMB TEMPLATE
$e2gSnipCfg['thumb_tpl'] = (isset($thumb_tpl)) ? str_replace('../', '', $thumb_tpl) : $e2g['thumb_tpl'];
// THUMB RAND TEMPLATE
$e2gSnipCfg['rand_tpl'] = (isset($rand_tpl)) ? str_replace('../', '', $rand_tpl) : $e2g['rand_tpl'];
// BACK NAVIGATION TEMPLATE
$e2gSnipCfg['back_tpl'] = (isset($back_tpl)) ? str_replace('../', '', $back_tpl) : $e2g['back_tpl'];
// Prev-Up-Next NAVIGATION TEMPLATE
$e2gSnipCfg['prevUpNext_tpl'] = (isset($prevUpNext_tpl)) ? str_replace('../', '', $prevUpNext_tpl) : $e2g['prevUpNext_tpl'];
// Pagination TEMPLATE
$e2gSnipCfg['pagination_tpl'] = (isset($pagination_tpl)) ? str_replace('../', '', $pagination_tpl) : $e2g['pagination_tpl'];
// LANDING PAGE TEMPLATE
$e2gSnipCfg['page_tpl'] = (isset($page_tpl)) ? str_replace('../', '', $page_tpl) : $e2g['page_tpl'];
// LANDING PAGE CSS
$e2gSnipCfg['page_tpl_css'] = (isset($page_tpl_css)) ? str_replace('../', '', $page_tpl_css) : $e2g['page_tpl_css'];
// COMMENT ROW TEMPLATE
$e2gSnipCfg['comments_row_tpl'] = (isset($comments_row_tpl)) ? str_replace('../', '', $comments_row_tpl) : E2G_SNIPPET_PATH . $e2g['comments_row_tpl'];
// LANDINGPAGE'S COMMENT TEMPLATE
$e2gSnipCfg['page_comments_tpl'] = (isset($page_comments_tpl)) ? str_replace('../', '', $page_comments_tpl) : $e2g['page_comments_tpl'];
// LANDINGPAGE'S COMMENT ROW TEMPLATE
$e2gSnipCfg['page_comments_row_tpl'] = (isset($page_comments_row_tpl)) ? str_replace('../', '', $page_comments_row_tpl) : $e2g['page_comments_row_tpl'];
// SLIDESHOW TEMPLATE
$e2gSnipCfg['slideshow-tpl'] = (isset($slideshow_tpl)) ? str_replace('../', '', $slideshow_tpl) : $e2g['ss_tpl'];

/**
 * GALLERY JS
 */
$e2gSnipCfg['js'] = (isset($js)) ? str_replace('../', '', $js) : $e2g['js'];
$e2gSnipCfg['autoload_js'] = isset($autoload_js) ? $autoload_js : NULL;
/**
 * HTML's HEADER BLOCK, FOR JAVASCRIPT LIBRARY'S HEADERS
 */
$e2gSnipCfg['autoload_html'] = isset($autoload_html) ? $autoload_html : NULL;
/**
 * GALLERY CSS
 */
$e2gSnipCfg['css'] = isset($css) ? str_replace('../', '', $css) : $e2g['css'];
$e2gSnipCfg['autoload_css'] = isset($autoload_css) ? $autoload_css : NULL;
/**
 * CSS classes
 */
$e2gSnipCfg['grid_class'] = isset($grid_class) ? $grid_class : $e2g['grid_class'];
if (isset($e2g_currentcrumb_class))
    $crumbs_classCurrent = $e2g_currentcrumb_class; // backward compatibility
$e2gSnipCfg['crumbs_classCurrent'] = isset($crumbs_classCurrent) ? $crumbs_classCurrent : $e2g['crumbs_classCurrent'];
if (isset($e2gback_class))
    $back_class = $e2gback_class; // backward compatibility
$e2gSnipCfg['back_class'] = isset($back_class) ? $back_class : $e2g['back_class'];
if (isset($e2gpnums_class))
    $pagenum_class = $e2gpnums_class; // backward compatibility
$e2gSnipCfg['pagenum_class'] = isset($pagenum_class) ? $pagenum_class : $e2g['pagenum_class'];

/**
 * THUMBNAILS
 */
/**
 * THUMB 'resize-type'
 * @param string settings: 'inner' (cropped) | 'resize' (autofit) | 'shrink' (shrink)
 */
$e2gSnipCfg['resize_type'] = isset($resize_type) ? $resize_type : $e2g['resize_type'];
// THUMB BACKGROUND COLOR
$e2gSnipCfg['thbg_red'] = isset($thbg_red) ? $thbg_red : $e2g['thbg_red'];
$e2gSnipCfg['thbg_green'] = isset($thbg_green) ? $thbg_green : $e2g['thbg_green'];
$e2gSnipCfg['thbg_blue'] = isset($thbg_blue) ? $thbg_blue : $e2g['thbg_blue'];

/**
 * Javascript's pop-up libraries
 */
// GLIB
$e2gSnipCfg['glib'] = isset($glib) ? $glib : $e2g['glib'];
// JAVASCRIPT LIBRARY'S SLIDESHOW GROUP
if (isset($e2gSnipCfg['static_gid']))
    $groupSuffix = $modx->stripAlias($e2gSnipCfg['static_gid']);
elseif (isset($e2gSnipCfg['static_tag']))
    $groupSuffix = $modx->stripAlias($e2gSnipCfg['static_tag']);
else
    $groupSuffix = '1'; // root &gid
$e2gSnipCfg['show_group'] = isset($show_group) ? $show_group : 'Gallery_' . $groupSuffix . '_' . $e2gSnipCfg['e2g_static_instances'];

/**
 * WATERMARKS
 */
// ON/OFF = 0/1
$e2gSnipCfg['ewm'] = isset($ewm) ? $ewm : $e2g['ewm'];
// type: text | image
$e2gSnipCfg['wmtype'] = isset($ewmtype) ? $ewmtype : $e2g['wmtype'];
// the watermark's text | image path
$e2gSnipCfg['wmt'] = isset($ewmt) ? $ewmt : $e2g['wmt'];
// horizontal: 1=left | 2=center | 3=right
$e2gSnipCfg['wmpos1'] = (isset($ewmpos1) && is_numeric($ewmpos1)) ? $ewmpos1 : $e2g['wmpos1'];
// vertical : 1=top | 2=center | 3=bottom
$e2gSnipCfg['wmpos2'] = (isset($ewmpos2) && is_numeric($ewmpos2)) ? $ewmpos2 : $e2g['wmpos2'];
// xy coordinate
$e2gSnipCfg['wmposxy'] = !empty($ewmposxy) ? $ewmposxy : NULL;

/**
 * COMMENTS
 */
$e2gSnipCfg['ecm'] = (isset($ecm) && is_numeric($ecm)) ? $ecm : $e2g['ecm'];
// COMMENT LIMIT
$e2gSnipCfg['ecl'] = (isset($ecl) && is_numeric($ecl)) ? $ecl : $e2g['ecl'];
// COMMENT LIMIT on landingpage
$e2gSnipCfg['ecl_page'] = (isset($ecl_page) && is_numeric($ecl_page)) ? $ecl_page : $e2g['ecl_page'];
// COMMENT's CAPTCHA
if (isset($captcha))
    $recaptcha = $captcha; // backward compatibility
$e2gSnipCfg['recaptcha'] = (isset($recaptcha) && is_numeric($recaptcha)) ? $recaptcha : $e2g['recaptcha'];
$e2gSnipCfg['recaptcha_key_private'] = $e2g['recaptcha_key_private'];
$e2gSnipCfg['recaptcha_key_public'] = $e2g['recaptcha_key_public'];
$e2gSnipCfg['recaptcha_theme'] = $e2g['recaptcha_theme'];
$e2gSnipCfg['recaptcha_theme_custom'] = $e2g['recaptcha_theme_custom'];

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
$e2gSnipCfg['img_src'] = isset($img_src) ? $img_src : $e2g['img_src'];
// slideshow
$e2gSnipCfg['ss_img_src'] = isset($ss_img_src) ? $ss_img_src : $e2g['ss_img_src'];
// landingpage
$e2gSnipCfg['lp_img_src'] = isset($lp_img_src) ? $lp_img_src : 'generated';

/**
 * STAND ALONE SLIDESHOW PARAMETERS
 */
// SLIDESHOW TYPE
$e2gSnipCfg['slideshow'] = isset($slideshow) ? $slideshow : NULL;
// additional configuration options, if this is empty, only as an holder.
$e2gSnipCfg['ss_config'] = isset($ss_config) ? $ss_config : NULL;
// SLIDESHOW PROCESSOR FILE. IF NOT EXIST, WILL USE DEFAULT.
$e2gSnipCfg['ss_indexfile'] = isset($ss_indexfile) ? $ss_indexfile : NULL;
// set the slideshow's CSS path
$e2gSnipCfg['ss_css'] = isset($ss_css) ? $ss_css : NULL;
// set the slideshow's CSS path
$e2gSnipCfg['ss_js'] = isset($ss_js) ? $ss_js : NULL;

/**
 * IF, the image source =`generated`, use these more options
 * SLIDESHOW'S BOX DIMENSION, NOT THUMBNAIL
 */
$e2gSnipCfg['ss_w'] = isset($ss_w) ? $ss_w : $e2g['ss_w']; // width
$e2gSnipCfg['ss_h'] = isset($ss_h) ? $ss_h : $e2g['ss_h']; // mandatory existence height
$e2gSnipCfg['ss_thq'] = (isset($ss_thq) && $ss_thq <= 100 && $ss_thq >= 0) ? (int) $ss_thq : $e2g['ss_thq'];
/**
 * generating the 'resize-type'
 * @param string settings: 'inner' (cropped) | 'resize' (autofit) | 'shrink' (shrink)
 */
$e2gSnipCfg['ss_resize_type'] = isset($ss_resize_type) ? $ss_resize_type : $e2g['ss_resize_type'];
// image's BACKGROUND COLOR
$e2gSnipCfg['ss_red'] = isset($ss_red) ? $ss_red : $e2g['ss_red'];
$e2gSnipCfg['ss_green'] = isset($ss_green) ? $ss_green : $e2g['ss_green'];
$e2gSnipCfg['ss_blue'] = isset($ss_blue) ? $ss_blue : $e2g['ss_blue'];
/**
 * &ss_allowedratio is an allowance ratio of width/height to help distinguishing
 * too tall/wide images while the &ss_w and &ss_h are limited.
 * the format is 'minfloatnumber-maxfloatnumber', eg: '1.0-2.0'
 * to disable this restriction, set &ss_allowedratio=`all`
 */
$e2gSnipCfg['ss_allowedratio'] = (isset($ss_allowedratio) || $ss_allowedratio != '') ? $ss_allowedratio : $e2g['ss_allowedratio'];

// ORDER BY
$ss_orderby = isset($ss_orderby) ? $ss_orderby : $e2g['ss_orderby'];
$e2gSnipCfg['ss_orderby'] = preg_replace('/[^_a-z]/i', '', $ss_orderby);
// ORDER
$ss_order = isset($ss_order) ? $ss_order : $e2g['ss_order'];
$e2gSnipCfg['ss_order'] = preg_replace('/[^a-z]/i', '', $ss_order);
/**
 * to set how many images the slide show should retrieve from the gallery ID.
 * more images mean longer page loading!
 * @options : int | 'none'
 */
$e2gSnipCfg['ss_limit'] = !empty($ss_limit) ? (trim($ss_limit) == 'none' ? trim($ss_limit) : (int) $ss_limit) : $e2g['ss_limit'];

/**
 * set the slideshow's or thumbnail's landing page.
 * @options: document ID.
 */
$e2gSnipCfg['landingpage'] = isset($landingpage) ? $landingpage : (isset($_GET['lp']) ? $_GET['lp'] : NULL);
/**
 * IF, the image source =`generated`, use these more options
 */
$e2gSnipCfg['lp_w'] = isset($lp_w) ? $lp_w : NULL; // width -> NULL: will be retrieved from the getimagesize() function
$e2gSnipCfg['lp_h'] = isset($lp_h) ? $lp_h : NULL; // height -> NULL: will be retrieved from the getimagesize() function
$e2gSnipCfg['lp_thq'] = (isset($lp_thq) && $lp_thq <= 100 && $lp_thq >= 0) ? (int) $lp_thq : $e2g['thq'];
/**
 * landingpage's 'resize-type'
 * @param string settings: 'inner' (cropped) | 'resize' (autofit) | 'shrink' (shrink)
 */
$e2gSnipCfg['lp_resize_type'] = isset($lp_resize_type) ? $lp_resize_type : 'inner';
// image's BACKGROUND COLOR
$e2gSnipCfg['lp_red'] = isset($lp_red) ? $lp_red : $e2g['thbg_red'];
$e2gSnipCfg['lp_green'] = isset($lp_green) ? $lp_green : $e2g['thbg_green'];
$e2gSnipCfg['lp_blue'] = isset($lp_blue) ? $lp_blue : $e2g['thbg_blue'];

/**
 * CRUMBS
 */
$e2gSnipCfg['crumbs'] = ( isset($crumbs) && is_numeric($crumbs) ) ? $crumbs : $e2g['crumbs'];
$e2gSnipCfg['crumbs_use'] = ( isset($crumbs_use) ) ? $crumbs_use : $e2g['crumbs_use'];
$e2gSnipCfg['crumbs_separator'] = isset($crumbs_separator) ? $crumbs_separator : $e2g['crumbs_separator'];
$e2gSnipCfg['crumbs_showHome'] = isset($crumbs_showHome) ? $crumbs_showHome : $e2g['crumbs_showHome'];
$e2gSnipCfg['crumbs_showAsLinks'] = isset($crumbs_showAsLinks) ? $crumbs_showAsLinks : $e2g['crumbs_showAsLinks'];
$e2gSnipCfg['crumbs_showCurrent'] = isset($crumbs_showCurrent) ? $crumbs_showCurrent : $e2g['crumbs_showCurrent'];
$e2gSnipCfg['crumbs_showPrevious'] = isset($crumbs_showPrevious) ? $crumbs_showPrevious : $e2g['crumbs_showPrevious'];

/**
 * Previous/Up/Next Navigation
 */
$e2gSnipCfg['nav_prevUpNext'] = ( isset($nav_prevUpNext) && is_numeric($nav_prevUpNext) ) ? $nav_prevUpNext : $e2g['nav_prevUpNext'];
$e2gSnipCfg['nav_prevUpNextTitle'] = ( isset($nav_prevUpNextTitle) ) ? $nav_prevUpNextTitle : $e2g['nav_prevUpNextTitle'];
$e2gSnipCfg['nav_prevSymbol'] = ( isset($nav_prevSymbol) ) ? $nav_prevSymbol : $e2g['nav_prevSymbol'];
$e2gSnipCfg['nav_upSymbol'] = ( isset($nav_upSymbol) ) ? $nav_upSymbol : $e2g['nav_upSymbol'];
$e2gSnipCfg['nav_nextSymbol'] = ( isset($nav_nextSymbol) ) ? $nav_nextSymbol : $e2g['nav_nextSymbol'];

/**
 * mbstring
 */
$e2gSnipCfg['charset'] = $modx->config['modx_charset'];
$e2gSnipCfg['mbstring'] = function_exists('mb_strlen') && function_exists('mb_substr');

/**
 * Naming convension
 */
// Filename length
$e2gSnipCfg['name_len'] = (isset($name_len) && is_numeric($name_len)) ? (int) $name_len : $e2g['name_len'];
// Directory's name length
$e2gSnipCfg['cat_name_len'] = (isset($cat_name_len) && is_numeric($cat_name_len)) ? (int) $cat_name_len : $e2g['cat_name_len'];

/**
 * plugins interception
 */
if (isset($plugins))
    $plugin = $plugins; // compatibility
$e2gSnipCfg['plugin'] = isset($plugin) ? $plugin : NULL;

// set pagination on or off: 0 | 1
$e2gSnipCfg['pagination'] = ( isset($pagination) && is_numeric($pagination) ) ? $pagination : $e2g['pagination'];
$e2gSnipCfg['pagination_adjacents'] = ( isset($pagination_adjacents) && is_numeric($pagination_adjacents) ) ? $pagination_adjacents : $e2g['pagination_adjacents'];
$e2gSnipCfg['pagination_spread'] = ( isset($pagination_spread) && is_numeric($pagination_spread) ) ? $pagination_spread : $e2g['pagination_spread'];
$e2gSnipCfg['pagination_text_previous'] = isset($pagination_text_previous) ? $pagination_text_previous : $e2g['pagination_text_previous'];
$e2gSnipCfg['pagination_text_next'] = isset($pagination_text_next) ? $pagination_text_next : $e2g['pagination_text_next'];
$e2gSnipCfg['pagination_splitter'] = isset($pagination_splitter) ? $pagination_splitter : $e2g['pagination_splitter'];

// use redirect link instead of popping up the image's iframe or go deeper into the directory's contents
$e2gSnipCfg['use_redirect_link'] = ( isset($use_redirect_link) && $use_redirect_link == '1' ) ? TRUE : FALSE;

// strip HTML tags
$e2gSnipCfg['strip_html_tags'] = ( isset($strip_html_tags) && is_numeric($strip_html_tags) ) ? $strip_html_tags : $e2g['strip_html_tags'];