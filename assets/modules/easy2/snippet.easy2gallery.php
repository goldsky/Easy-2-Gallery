<?php
//set_ini('display_errors', '1');
/**
 * EASY 2 GALLERY
 * Gallery Snippet for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.3.6
 */

// Easy 2 Gallery snippet path
if(!defined('E2G_SNIPPET_PATH')) {
    define('E2G_SNIPPET_PATH', MODX_BASE_PATH . 'assets/modules/easy2/');
}
// Easy 2 Gallery snippet URL
if(!defined('E2G_SNIPPET_URL')) {
    define('E2G_SNIPPET_URL', MODX_BASE_URL . 'assets/modules/easy2/');
}

require E2G_SNIPPET_PATH.'config.easy2gallery.php';

if ( !empty($fid) ) {
    // FILE ID
    $cl_cfg['fid'] = (!empty($_GET['fid']) && is_numeric($_GET['fid'])) ? (int) $_GET['fid'] : ((!empty($fid) && is_numeric($fid)) ? $fid : '');
}
elseif ( !empty($rgid) ) {
    // RANDOMIZED GALLERY ID
    $cl_cfg['rgid'] = (!empty($rgid) && is_numeric($rgid)) ? $rgid : 0;
}
else {
    // GALLERY ID
    $cl_cfg['gid'] = (!empty($_GET['gid']) && is_numeric($_GET['gid'])) ? (int) $_GET['gid'] : ((!empty($gid) && is_numeric($gid)) ? $gid : 1);
}
//die('35<br />-$fid='.$fid
//        .'<br />-$cl_cfg[\'fid\']='.$cl_cfg['fid']
//        .'<br />-$rgid='.$rgid
//        .'<br />-$cl_cfg[\'rgid\']='.$cl_cfg['rgid']
//        .'<br />-$gid='.$gid
//        .'<br />-$cl_cfg[\'gid\']='.$cl_cfg['gid']);

// WIDTH
$cl_cfg['w'] = (!empty($w) && is_numeric($w)) ? (int) $w : $e2g['w'];

// HEIGHT
$cl_cfg['h'] = (!empty($h) && is_numeric($h)) ? (int) $h : $e2g['h'];

// JPEG QUALITY
$cl_cfg['thq'] = (!empty($thq) && $thq<=100 && $thq>=0) ? (int) $thq : $e2g['thq'];

// NAME LENGTH
$cl_cfg['name_len'] = (!empty($name_len) && is_numeric($name_len)) ? (int) $name_len : $e2g['name_len'];

// DIRECTORY NAME LENGTH
$cl_cfg['cat_name_len'] = (!empty($cat_name_len) && is_numeric($cat_name_len)) ? (int) $cat_name_len : $e2g['cat_name_len'];

// COLLS
$cl_cfg['colls'] = (!empty($colls) && is_numeric($colls)) ? (int) $colls : $e2g['colls'];

// NO TABLES
$cl_cfg['notables'] = $fid ? 1 : (isset($notables) && is_numeric($notables)) ? $notables : (isset($e2g['notables']) ? $e2g['notables'] : 0);

// LIMIT
$cl_cfg['limit'] = (!empty($limit) && is_numeric($limit)) ? (int) $limit : $e2g['limit'];

// GLIB
$cl_cfg['glib'] = (!empty($glib)) ? $glib : $e2g['glib'];

// COMMENTS
$cl_cfg['ecm'] = (isset($ecm) && is_numeric($ecm)) ? $ecm : $e2g['ecm'];

// PAGE NUMBER
$gpn = (!empty($gpn) && is_numeric($gpn)) ? $gpn : 0;
$cl_cfg['gpn'] = (!empty($_GET['gpn']) && is_numeric($_GET['gpn'])) ? (int) $_GET['gpn'] : $gpn;

// ORDER BY
/*
 * Options: order by SQL fields.
 */
$orderby = (!empty($orderby)) ? $orderby : $e2g['orderby'];
$cl_cfg['orderby'] = preg_replace('/[^_a-z]/i', '', $orderby);
$cat_orderby = (!empty($cat_orderby)) ? $cat_orderby : $e2g['cat_orderby'];
$cl_cfg['cat_orderby'] = preg_replace('/[^_a-z]/i', '', $cat_orderby);

// ORDER
$order = (!empty($order)) ? $order : $e2g['order'];
$cl_cfg['order'] = preg_replace('/[^a-z]/i', '', $order);
$cat_order = (!empty($cat_order)) ? $cat_order : $e2g['cat_order'];
$cl_cfg['cat_order'] = preg_replace('/[^a-z]/i', '', $cat_order);

// GALLERY CSS
$cl_cfg['css'] = (!empty($css)) ? str_replace('../', '' , $css) : $e2g['css'];

// GALLERY TEMPLATE
$cl_cfg['tpl'] = (!empty($tpl)) ? str_replace('../', '' , $tpl) : $e2g['tpl'];

// DIR TEMPLATE
$cl_cfg['dir_tpl'] = (!empty($dir_tpl)) ? str_replace('../', '', $dir_tpl) : $e2g['dir_tpl'];

// THUMB TEMPLATE
$cl_cfg['thumb_tpl'] = (!empty($thumb_tpl)) ? str_replace('../', '' , $thumb_tpl) : $e2g['thumb_tpl'];

// THUMB RAND TEMPLATE
$cl_cfg['rand_tpl'] = (!empty($rand_tpl)) ? str_replace('../', '' , $rand_tpl) : $e2g['rand_tpl'];

//SLIDESHOW GROUP
$cl_cfg['show_group'] = isset($show_group) ? $show_group : 'Gallery'.$gid;

// CRUMBS
$cl_cfg['crumbs_separator'] = isset($crumbs_separator) ? $crumbs_separator : ' / ';
$cl_cfg['crumbs_showHome'] = isset($crumbs_showHome) ? $crumbs_showHome : 0;
$cl_cfg['crumbs_showAsLinks'] = isset($crumbs_showAsLinks) ? $crumbs_showAsLinks : 1;
$cl_cfg['crumbs_showCurrent'] = isset($crumbs_showCurrent) ? $crumbs_showCurrent : 1;

//mbstring
$cl_cfg['charset'] = $modx->config['modx_charset'];
$cl_cfg['mbstring'] = function_exists('mb_strlen') && function_exists('mb_substr');

/*
 * EXECUTE SNIPPET
*/
if(!class_exists('e2g_snip')) {
    include_once E2G_SNIPPET_PATH . "classes/e2g.snippet.class.php";
}
if (class_exists('e2g_snip')) {
    $e2g = new e2g_snip($cl_cfg);
    $output = $e2g->display();
} else {
    $output = "<h3>error: e2g_snip class not found</h3>";
}
return $output;
?>