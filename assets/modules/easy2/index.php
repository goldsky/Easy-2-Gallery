<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

/**
 * EASY 2 GALLERY
 * Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.3.6
 */

// Easy 2 Gallery version
define('E2G_VERSION', '1.3.6 - Beta 3');

// Easy 2 Gallery module path
if(!defined('E2G_MODULE_PATH')) {
    define('E2G_MODULE_PATH', MODX_BASE_PATH . 'assets/modules/easy2/');
}
// Easy 2 Gallery module URL
if(!defined('E2G_MODULE_URL')) {
    define('E2G_MODULE_URL', MODX_BASE_URL . 'assets/modules/easy2/');
}

// SYSTEM VARS
$e2gmod_cl['debug'] = $debug = 0;
$e2gmod_cl['_t'] = $_t = $modx->config['manager_theme'];
$e2gmod_cl['_a'] = $_a = (int) $_GET['a'];
$e2gmod_cl['_i'] = $_i = (int) $_GET['id'];
$e2gmod_cl['index'] = $index = 'index.php?a='.$_a.'&id='.$_i;
if ($debug == 1) {
    error_reporting(E_ALL);
    $old_error_handler = set_error_handler("error_handler");
}

if (file_exists( E2G_MODULE_PATH . 'langs/'.$modx->config['manager_language'].'.inc.php')) {
    include E2G_MODULE_PATH . 'langs/'.$modx->config['manager_language'].'.inc.php';
} else {
    include E2G_MODULE_PATH . 'langs/english.inc.php';
}

mysql_select_db(str_replace('`', '', $GLOBALS['dbase']));
@mysql_query("{$GLOBALS['database_connection_method']} {$GLOBALS['database_connection_charset']}");

//// ALERTS / ERRORS
//if (!isset( $_SESSION['easy2err'] ) ) $_SESSION['easy2err'] = array();
//if (!isset( $_SESSION['easy2suc'] ) ) $_SESSION['easy2suc'] = array();

require E2G_MODULE_PATH . 'config.easy2gallery.php';
// Install
if (is_dir( E2G_MODULE_PATH . 'install')) {
    require_once E2G_MODULE_PATH . 'install/index.php';
    exit();
}
//$e2g['mdate_format'] = 'd-m-y H:i';
//if (!is_dir( MODX_BASE_PATH . $e2g['dir'])) {
//    echo '<b style="color:red">'.$lng['dir'].' &quot;'.$e2g['dir'].'&quot; '.$lng['empty'].'</b>';
//    exit;
//} elseif (!is_dir( MODX_BASE_PATH . $e2g['dir'] . '_thumbnails' ) ) {
//    if (mkdir( MODX_BASE_PATH . $e2g['dir'] . '_thumbnails' ) ) {
//        @chmod( MODX_BASE_PATH . $e2g['dir'] . '_thumbnails', 0755 );
//    } else {
//        echo '<b style="color:red">' . $lng['_thumb_err'] . '</b>';
//        exit;
//    }
//}
//
//$gdir = $e2g['dir'];
//$path = '';
//$parent_id = ( isset( $_GET['pid'] ) && is_numeric( $_GET['pid'] ) ) ? (int) $_GET['pid'] : 1;

/*
 * EXECUTE MODULE
*/

//include_once 'includes/pane.main.inc.php';
if(!class_exists('e2g_mod')) {
    include_once E2G_MODULE_PATH . "classes/e2g.module.class.php";
}
if (class_exists('e2g_mod')) {
    $e2g_mod = new e2g_mod($e2gmod_cl, $e2g, $lng);
    $output = $e2g_mod;
//    $output = $e2g_mod->explore();
} else {
    $output = "<h3>error: e2g_snip class not found</h3>";
}

return $output;