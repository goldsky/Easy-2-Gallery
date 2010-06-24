<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

/**
 * EASY 2 GALLERY
 * Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus
 * @author goldsky <goldsky@modx-id.com>
 */

// Easy 2 Gallery version
if (!defined('E2G_VERSION') || 'E2G_VERSION' !== '1.4.0 - RC 1') {
    define('E2G_VERSION', '1.4.0 - RC 1');
}

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
if (file_exists( E2G_MODULE_PATH . 'includes/langs/'.$modx->config['manager_language'].'.inc.php')) {
    require_once E2G_MODULE_PATH . 'includes/langs/'.$modx->config['manager_language'].'.inc.php';

    // if there is a blank language parameter, english will fill it as the default.
    foreach ($e2g_lang[$modx->config['manager_language']] as $olk => $olv) {
        $olangkey[$olk] = $olk; // other languages
        $olangval[$olk] = $olv;
    }

    include_once E2G_MODULE_PATH . 'includes/langs/english.inc.php';
    foreach ($e2g_lang['english'] as $enk => $env) {
        if ( !isset($olangkey[$enk]) ) {
            $e2g_lang[$modx->config['manager_language']][$enk] = $env;
        }
    }

    $lng = $e2g_lang[$modx->config['manager_language']];
} else {
    require_once E2G_MODULE_PATH . 'includes/langs/english.inc.php';
    $lng = $e2g_lang['english'];
}

mysql_select_db(str_replace('`', '', $GLOBALS['dbase']));
@mysql_query("{$GLOBALS['database_connection_method']} {$GLOBALS['database_connection_charset']}");

// ALERTS / ERRORS
if (!isset( $_SESSION['easy2err'] ) ) $_SESSION['easy2err'] = array();
if (!isset( $_SESSION['easy2suc'] ) ) $_SESSION['easy2suc'] = array();

// CONFIGURATIONS
if (!file_exists(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php')) {
    // if config file has not been created, yet, this will create one
    $createconfig = fopen(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php', 'w+');
} else {
    require_once E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php';
    foreach ($e2g as $ck => $cv) {
        $confkey[$ck] = $ck;
        $confval[$ck] = $cv;
    }
}

// the default config will replace any blank value of config's.
if (file_exists( E2G_MODULE_PATH . 'includes/configs/default.config.easy2gallery.php' )) {
    require_once E2G_MODULE_PATH . 'includes/configs/default.config.easy2gallery.php';
    // if config file is missing, this will create one
    if (isset($createconfig)) {
        $c = "<?php\r\n\$e2g = array (\r\n";
        foreach($def_e2g as $dk => $dv) {
            $c .= "        '$dk' => ".(is_numeric($dv)?$dv:"'".addslashes($dv)."'").",\r\n";
        }
        $c .= ");\r\n?>";
        fwrite($createconfig, $c);
        fclose($createconfig);
        unset($createconfig);
    }

    foreach ($def_e2g as $dk => $dv) {
        if ( !isset($confkey[$dk]) ) {
            $e2g[$dk] = $dv;
        }
    }
    $def_e2g = array();
    unset($def_e2g);
}

// CHECKING THE root and _thumbnails FOLDERs
if (!is_dir( MODX_BASE_PATH . $e2g['dir']) ) {
    // INSTALL
    if (is_dir( E2G_MODULE_PATH . 'install')) {
        require_once E2G_MODULE_PATH . 'install/index.php';
        exit();
    } else {
    $_SESSION['easy2err'][] = '<b style="color:red">'.$lng['dir'].' &quot;'.$e2g['dir'].'&quot; '.$lng['empty'].'</b>';
//    exit;
    }
} elseif (!is_dir( MODX_BASE_PATH . $e2g['dir'] . '_thumbnails' ) ) {
    if (mkdir( MODX_BASE_PATH . $e2g['dir'] . '_thumbnails' ) ) {
        @chmod( MODX_BASE_PATH . $e2g['dir'] . '_thumbnails', 0755 );
    } else {
        $_SESSION['easy2err'][] = '<b style="color:red">' . $lng['_thumb_err'] . '</b>';
        exit;
    }
}

// ENCODING
$e2gmod_cfg['e2g_encode'] = $e2g['e2g_encode'];

// SET UP THE PATH
$e2gmod_cfg['gdir'] = ( isset($gdir) ? $gdir : $e2g['dir'] );
$e2gmod_cfg['path'] = ( isset($path) ? $path : '' );
$e2gmod_cfg['parent_id'] = ( isset( $_GET['pid'] ) && is_numeric( $_GET['pid'] ) ) ? (int) $_GET['pid'] : 1;

/*
 * SYSTEM VARS
 */
// Easy 2 Gallery's debug parameter
$e2gmod_cfg['e2g_debug'] = $e2g['e2g_debug'];
// override MODx's debug variable
$debug = 0;
// MODx's manager theme
$e2gmod_cfg['_t'] = ( isset($_t) ? $_t : $modx->config['manager_theme'] );
// MODx's action ID
$e2gmod_cfg['_a'] = ( isset($_a) ? $_a : (int) $_GET['a'] );
// MODx's module ID
$e2gmod_cfg['_i'] = ( isset($_i) ? $_i : (int) $_GET['id'] );
// module's href
$e2gmod_cfg['index'] = ( isset($index) ? $index : 'index.php?a='.$e2gmod_cfg['_a'].'&id='.$e2gmod_cfg['_i'] );

// ERROR REPORTING
if ($e2gmod_cfg['e2g_debug'] == 1) {
//    error_reporting(E_ALL);
//    $old_error_handler = set_error_handler("error_handler");
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

if(!class_exists('e2g_pub')) {
    include_once(E2G_MODULE_PATH . "includes/classes/e2g.public.class.php");
}
if(!class_exists('e2g_mod')) {
    include_once(E2G_MODULE_PATH . "includes/classes/e2g.module.class.php");
}
if (class_exists('e2g_pub') && class_exists('e2g_mod')) {
    $e2g_mod = new e2g_mod($e2gmod_cfg, $e2g, $lng);
    $e2g_mod->e2gpub_cfg = $e2gmod_cfg;
    $e2g_mod->e2gpub_e2g = $e2g;
    $e2g_mod->e2gpub_lng = $lng;
    $output = $e2g_mod->explore($e2g, $lng);
} else {
    $output = "<h3>error: required class not found</h3>";
}

return $output;