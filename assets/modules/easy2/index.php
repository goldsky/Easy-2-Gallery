<?php

/**
 * EASY 2 GALLERY
 * Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@modx-id.com>
 */
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// Easy 2 Gallery version
if (!defined('E2G_VERSION') || 'E2G_VERSION' !== '1.4.0 - RC 4') {
    define('E2G_VERSION', '1.4.0 - RC 4');
}

// Easy 2 Gallery module path
if (!defined('E2G_MODULE_PATH')) {
    define('E2G_MODULE_PATH', MODX_BASE_PATH . 'assets/modules/easy2/');
}
// Easy 2 Gallery module URL
if (!defined('E2G_MODULE_URL')) {
    define('E2G_MODULE_URL', MODX_SITE_URL . 'assets/modules/easy2/');
}

require_once E2G_MODULE_PATH . 'includes/utf8/utf8.php';

// LANGUAGE
if (file_exists(realpath(E2G_MODULE_PATH . 'includes/langs/' . $modx->config['manager_language'] . '.inc.php'))) {
    require_once E2G_MODULE_PATH . 'includes/langs/' . $modx->config['manager_language'] . '.inc.php';

    // if there is a blank language parameter, english will fill it as the default.
    foreach ($e2g_lang[$modx->config['manager_language']] as $olk => $olv) {
        $oldLangKey[$olk] = $olk; // other languages
        $oldLangVal[$olk] = $olv;
    }

    include_once E2G_MODULE_PATH . 'includes/langs/english.inc.php';
    foreach ($e2g_lang['english'] as $enk => $env) {
        if (!isset($oldLangKey[$enk])) {
            $e2g_lang[$modx->config['manager_language']][$enk] = $env;
        }
    }

    $lng = $e2g_lang[$modx->config['manager_language']];
} else {
    require_once E2G_MODULE_PATH . 'includes/langs/english.inc.php';
    $lng = $e2g_lang['english'];
}

//mysql_select_db(str_replace('`', '', $GLOBALS['dbase']));
//@mysql_query("{$GLOBALS['database_connection_method']} {$GLOBALS['database_connection_charset']}");
//
// ALERTS / ERRORS
if (!isset($_SESSION['easy2err']))
    $_SESSION['easy2err'] = array();
if (!isset($_SESSION['easy2suc']))
    $_SESSION['easy2suc'] = array();

/**
 * Create a smooth conversion between file based config to database base
 */
// CONFIGURATIONS from the previous version installation
if (file_exists(realpath(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php'))) {
    require_once E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php';
    foreach ($e2g as $ck => $cv) {
        $configsKey[$ck] = $ck;
        $configsVal[$ck] = $cv;
    }
}

// CONFIGURATIONS
if (!isset($e2g)) {
    $upgradeCheck = 'SHOW TABLES LIKE \'' . $modx->db->config['table_prefix'] . 'easy2_configs\' ';
    $upgradeCheckValue = $modx->db->getValue($modx->db->query($upgradeCheck));
    if (!empty($upgradeCheckValue)) {
        $configsQuery = $modx->db->select('*', $modx->db->config['table_prefix'] . 'easy2_configs');
        if ($configsQuery) {
            while ($row = mysql_fetch_array($configsQuery)) {
                $configsKey[$row['cfg_key']] = $row['cfg_key'];
                $e2g[$row['cfg_key']] = $row['cfg_val'];
            }
        }
    }
}

// the default config will replace any blank value of config's.
if (file_exists(realpath(E2G_MODULE_PATH . 'includes/configs/default.config.easy2gallery.php'))) {
    require_once E2G_MODULE_PATH . 'includes/configs/default.config.easy2gallery.php';
    foreach ($e2gDefault as $dk => $dv) {
        if (!isset($configsKey[$dk])) {
            $e2g[$dk] = $dv;
        }
    }
    $e2gDefault = array();
    unset($e2gDefault);
}

// CHECKING THE root and _thumbnails FOLDERs
if (!is_dir(MODX_BASE_PATH . $e2g['dir'])) {
    // INSTALL
    if (is_dir(E2G_MODULE_PATH . 'install')) {
        require_once E2G_MODULE_PATH . 'install/index.php';
        exit();
    } else {
        $_SESSION['easy2err'][] = '<b style="color:red">' . $lng['dir'] . ' &quot;' . $e2g['dir'] . '&quot; ' . $lng['empty'] . '</b>';
//    exit;
    }
} elseif (!is_dir(MODX_BASE_PATH . $e2g['dir'] . '_thumbnails')) {
    if (mkdir(MODX_BASE_PATH . $e2g['dir'] . '_thumbnails')) {
        @chmod(MODX_BASE_PATH . $e2g['dir'] . '_thumbnails', 0755);
    } else {
        $_SESSION['easy2err'][] = '<b style="color:red">' . $lng['_thumb_err'] . '</b>';
        exit;
    }
}

// ENCODING
$e2gModCfg['e2g_encode'] = $e2g['e2g_encode'];

// SET UP THE PATH
$e2gModCfg['dir'] = $e2g['dir'];
//$e2gModCfg['gdir'] = ( isset($gdir) ? $gdir : $e2g['dir'] );
$e2gModCfg['gdir'] = ( isset($gdir) ? $gdir : $e2g['dir'] );
//$e2gModCfg['path'] = ( isset($path) ? $path : '' );
//$e2gModCfg['path'] = ( isset($_GET['path']) ? $_GET['path'] : '' );
$e2gModCfg['parent_id'] = ( isset($_GET['pid']) && is_numeric($_GET['pid']) ) ? (int) $_GET['pid'] : 1;

/**
 * SYSTEM VARS
 */
// Easy 2 Gallery's debug parameter
$e2gModCfg['e2g_debug'] = $e2g['e2g_debug'];
// override MODx's debug variable
$debug = 0;
// MODx's manager theme
$e2gModCfg['_t'] = ( isset($_t) ? $_t : $modx->config['manager_theme'] );
// MODx's action ID
$e2gModCfg['_a'] = ( isset($_a) ? $_a : (int) $_GET['a'] );
// MODx's module ID
$e2gModCfg['_i'] = ( isset($_i) ? $_i : (int) $_GET['id'] );
// E2G's module pages
$e2gModCfg['e2gpg'] = ( isset($_GET['e2gpg']) ? (int) $_GET['e2gpg'] : '1' );
// E2G's module views
$_SESSION['mod_view'] = isset($_GET['view']) ? $_GET['view'] : (isset($_SESSION['mod_view']) ? $_SESSION['mod_view'] : $e2g['mod_view']);

require E2G_MODULE_PATH . 'includes/configs/config.pages.easy2gallery.php';
// Module's pages
$e2gModCfg['e2gPages'] = $e2gPages;
// Gallery template
$e2gModCfg['mod_tpl_gal'] = '../' . $e2gFilePageTemplates['mod_tpl_gal'];
// Dir template
$e2gModCfg['mod_tpl_dir'] = '../' . $e2gFilePageTemplates['mod_tpl_dir'];
// Thumb template
$e2gModCfg['mod_tpl_thumb'] = '../' . $e2gFilePageTemplates['mod_tpl_thumb'];
// Table template
$e2gModCfg['mod_tpl_table'] = '../' . $e2gFilePageTemplates['mod_tpl_table'];
// Table's row template for dirs
$e2gModCfg['mod_tpl_table_row_dir'] = '../' . $e2gFilePageTemplates['mod_tpl_table_row_dir'];
// Table's row template for files
$e2gModCfg['mod_tpl_table_row_file'] = '../' . $e2gFilePageTemplates['mod_tpl_table_row_file'];
// Thumb's width
$e2gModCfg['mod_w'] = $e2g['mod_w'];
// Thumb's height
$e2gModCfg['mod_h'] = $e2g['mod_h'];
// Thumb's quality
$e2gModCfg['mod_thq'] = $e2g['mod_thq'];

/* * ********************************
 *        thumbnails order        *
 * ******************************** */
// Thumbnail's ORDER BY
$e2gModCfg['orderby'] = preg_replace('/[^_a-z]/i', '', $e2g['orderby']);
// Thumbnail's ORDER
$e2gModCfg['order'] = preg_replace('/[^a-z]/i', '', $e2g['order']);

// Folder's / directory's ORDER BY
$e2gModCfg['cat_orderby'] = preg_replace('/[^_a-z]/i', '', $e2g['cat_orderby']);
// Folder's / directory's ORDER
$e2gModCfg['cat_order'] = preg_replace('/[^a-z]/i', '', $e2g['cat_order']);

// Folder's thumbnail ORDER BY
$e2gModCfg['cat_thumb_orderby'] = preg_replace('/[^_a-z]/i', '', $e2g['cat_thumb_orderby']);
// Folder's thumbnail ORDER
$e2gModCfg['cat_thumb_order'] = preg_replace('/[^a-z]/i', '', $e2g['cat_thumb_order']);

// E2G's module ID
$e2gModCfg['mod_id'] = !empty($e2g['mod_id']) ? $e2g['mod_id'] : $_GET['id'];
// E2G's plugin ID
$e2gModCfg['plugin_id'] = !empty($e2g['plugin_id']) ? $e2g['plugin_id'] : null;

/**
 * If Easy 2 Gallery is included inside another module,
 * append its params into the address bar
 */
$alienarray = array();
$alienparams = '';
if ($e2gModCfg['mod_id'] != $_GET['id']) {
    // exclude ALL e2g's internal $_GET params to identify other module's $_GET params
    $diff = array(
        'a' => (isset($_GET['a']) ? $_GET['a'] : null)
        , 'id' => (isset($_GET['id']) ? $_GET['id'] : null)
        , 'pid' => (isset($_GET['pid']) ? $_GET['pid'] : null)
        , 'dir_id' => (isset($_GET['dir_id']) ? $_GET['dir_id'] : null)
        , 'file_id' => (isset($_GET['file_id']) ? $_GET['file_id'] : null)
        , 'lang' => (isset($_GET['lang']) ? $_GET['lang'] : null)
        , 'langfile' => (isset($_GET['langfile']) ? $_GET['langfile'] : null)
        , 'dir_path' => (isset($_GET['dir_path']) ? $_GET['dir_path'] : null)
        , 'file_path' => (isset($_GET['file_path']) ? $_GET['file_path'] : null)
        , 'path' => (isset($_GET['path']) ? $_GET['path'] : null)
        , 'act' => (isset($_GET['act']) ? $_GET['act'] : null)
        , 'page' => (isset($_GET['page']) ? $_GET['page'] : null)
        , 'view' => (isset($_GET['view']) ? $_GET['view'] : null)
        , 'e2gpg' => (isset($_GET['e2gpg']) ? $_GET['e2gpg'] : null)
        , 'group_id' => (isset($_GET['group_id']) ? $_GET['group_id'] : null)
        , 'filter' => (isset($_GET['filter']) ? $_GET['filter'] : null)
        , 'ip' => (isset($_GET['ip']) ? $_GET['ip'] : null)
        , 'u' => (isset($_GET['u']) ? $_GET['u'] : null)
        , 'e' => (isset($_GET['e']) ? $_GET['e'] : null)
    );
    $aliendiff = array_diff_key($_GET, $diff);
    foreach ($aliendiff as $k => $v) {
        $alienparams .= '&amp;' . $k . '=' . $v;
    }
    $diff = array();
}

// module's href
$e2gModCfg['index'] = ( isset($index) ? $index : MODX_MANAGER_URL . 'index.php?a=' . $e2gModCfg['_a']
                . '&amp;id=' . $e2gModCfg['_i']
                . '&amp;e2gpg=' . $e2gModCfg['e2gpg']
//        .(isset($_GET['page'])? '&amp;page='.$_GET['page']:null)
        )
        . ( $alienparams != '' ? $alienparams : '' );

// blank page's href
$e2gModCfg['blank_index'] = MODX_MANAGER_URL . 'index.php?a=' . $e2gModCfg['_a']
        . '&amp;id=' . $e2gModCfg['_i']
        . ( $alienparams != '' ? $alienparams : '' );

// ERROR REPORTING
if ($e2gModCfg['e2g_debug'] == 1) {
    error_reporting(E_ALL);
    $old_error_handler = set_error_handler("error_handler");
}

/**
 * To handle error
 * @param int      $errno error number
 * @param string   $errmsg error message
 * @param string   $filename filename
 * @param int      $linenum line number
 * @param string   $vars ???
 */
function error_handler($errno, $errmsg, $filename, $linenum, $vars) {
    echo '<p>Error ' . $errno . ': ' . $errmsg . '<br>File: ' . $filename . ' <b>Line:' . $linenum . '</b></p>';
}

// INSTALL
if (is_dir(E2G_MODULE_PATH . 'install')) {
    require_once E2G_MODULE_PATH . 'install/index.php';
    exit();
}

/**
 * EXECUTE MODULE
 */
$e2gPubClassFile = E2G_MODULE_PATH . 'includes/classes/e2g.public.class.php';
if (!class_exists('E2gPub') && file_exists(realpath($e2gPubClassFile))) {
    include_once $e2gPubClassFile;
} else {
    $output = 'Missing $e2gPubClassFile';
}

$e2gModClassFile = E2G_MODULE_PATH . 'includes/classes/e2g.module.class.php';
if (!class_exists('E2gMod') && file_exists(realpath($e2gModClassFile))) {
    include_once $e2gModClassFile;
} else {
    $output = 'Missing $e2gModClassFile';
}

if (class_exists('E2gPub') && class_exists('E2gMod')) {
    $e2gModule = new E2gMod($modx, $e2gModCfg, $e2g, $lng);
    $e2gModule->e2gpub_cfg = $e2gModCfg;
    $e2gModule->e2gpub_e2g = $e2g;
    $e2gModule->e2gpub_lng = $lng;
    $output = $e2gModule->explore($e2g);
}

return $output;