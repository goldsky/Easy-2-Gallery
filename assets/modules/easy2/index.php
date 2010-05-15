<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

/**
 * EASY 2 GALLERY
 * Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus
 * @author goldsky <goldsky@modx-id.com>
 */

header('Content-Type: text/html; charset=UTF-8');
iconv_set_encoding("internal_encoding", "UTF-8");

// Easy 2 Gallery version
define('E2G_VERSION', '1.4.0 - Beta 4');

// Easy 2 Gallery module path
if(!defined('E2G_MODULE_PATH')) {
    define('E2G_MODULE_PATH', MODX_BASE_PATH . 'assets/modules/easy2/');
}
// Easy 2 Gallery module URL
if(!defined('E2G_MODULE_URL')) {
    define('E2G_MODULE_URL', MODX_BASE_URL . 'assets/modules/easy2/');
}

require_once E2G_MODULE_PATH . 'includes/utf8/utf8.php';

// LANGUAGE
if (file_exists( E2G_MODULE_PATH . 'langs/'.$modx->config['manager_language'].'.inc.php')) {
    include E2G_MODULE_PATH . 'langs/'.$modx->config['manager_language'].'.inc.php';
} else {
    include E2G_MODULE_PATH . 'langs/english.inc.php';
}

mysql_select_db(str_replace('`', '', $GLOBALS['dbase']));
@mysql_query("{$GLOBALS['database_connection_method']} {$GLOBALS['database_connection_charset']}");

// ALERTS / ERRORS
if (!isset( $_SESSION['easy2err'] ) ) $_SESSION['easy2err'] = array();
if (!isset( $_SESSION['easy2suc'] ) ) $_SESSION['easy2suc'] = array();

// CONFIGURATIONS
if (file_exists( E2G_MODULE_PATH . 'config.easy2gallery.php' )) {
    require_once E2G_MODULE_PATH . 'config.easy2gallery.php';
    foreach ($e2g as $ck => $cv) {
        $keyconf[$ck] = $ck;
        $valconf[$ck] = $cv;
    }
}
// the default config will replace blank value of config's.
if (file_exists( E2G_MODULE_PATH . 'default.config.easy2gallery.php' )) {
    require_once E2G_MODULE_PATH . 'default.config.easy2gallery.php';
    foreach ($def_e2g as $dk => $dv) {
        if ($valconf[$dk]=='') {
            $e2g[$dk] = $dv;
        }
    }
    $def_e2g = array();
    unset($def_e2g);
}

// CHECKING THE _thumbnails FOLDER
if (!is_dir( MODX_BASE_PATH . $e2g['dir'])) {
    echo '<b style="color:red">'.$lng['dir'].' &quot;'.$e2g['dir'].'&quot; '.$lng['empty'].'</b>';
    exit;
} elseif (!is_dir( MODX_BASE_PATH . $e2g['dir'] . '_thumbnails' ) ) {
    if (mkdir( MODX_BASE_PATH . $e2g['dir'] . '_thumbnails' ) ) {
        @chmod( MODX_BASE_PATH . $e2g['dir'] . '_thumbnails', 0755 );
    } else {
        echo '<b style="color:red">' . $lng['_thumb_err'] . '</b>';
        exit;
    }
}

// SET UP THE PATH
$e2gmod_cl['gdir'] = ( isset($gdir) ? $gdir : $e2g['dir'] );
$e2gmod_cl['path'] = ( isset($path) ? $path : '' );
$e2gmod_cl['parent_id'] = ( isset( $_GET['pid'] ) && is_numeric( $_GET['pid'] ) ) ? (int) $_GET['pid'] : 1;

// SYSTEM VARS
$e2gmod_cl['debug'] = ( isset($debug) ? $debug : 0 );
$e2gmod_cl['_t'] = ( isset($_t) ? $_t : $modx->config['manager_theme'] );
$e2gmod_cl['_a'] = ( isset($_a) ? $_a : (int) $_GET['a'] );
$e2gmod_cl['_i'] = ( isset($_i) ? $_i : (int) $_GET['id'] );
$e2gmod_cl['index'] = ( isset($index) ? $index : 'index.php?a='.$e2gmod_cl['_a'].'&id='.$e2gmod_cl['_i'] );

// ERROR REPORTING
if ($e2gmod_cl['debug'] == 1) {
    error_reporting(E_ALL);
    $old_error_handler = set_error_handler("error_handler");
}
/*
 * To handle error
 * @param int      $errno error number
 * @param string   $errmsg error message
 * @param string   $filename filename
 * @param int      $linenum line number
 * @param string   $vars ???
*/
function error_handler ($errno, $errmsg, $filename, $linenum, $vars) {
    echo '<p>Error '.$errno.': '.$errmsg.'<br>File: '.$filename.' <b>Line:'.$linenum.'</b></p>';
}

// INSTALL
if (is_dir( E2G_MODULE_PATH . 'install')) {
    require_once E2G_MODULE_PATH . 'install/index.php';
    exit();
}

/*
 * EXECUTE MODULE
*/

if(!class_exists('e2g_mod')) {
    include_once(E2G_MODULE_PATH . "classes/e2g.module.class.php");
}
if (class_exists('e2g_mod')) {
    $e2g_mod = new e2g_mod($e2gmod_cl, $e2g, $lng);
    $output = $e2g_mod;
} else {
    $output = "<h3>error: e2g_mod class not found</h3>";
}

return $output;