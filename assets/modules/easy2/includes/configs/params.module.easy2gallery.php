<?php

if (!defined('E2G_MODULE_PATH')) {
    die();
}

// ENCODING
$e2gModCfg['e2g_encode'] = $e2g['e2g_encode'];

// SET UP THE PATH
$e2gModCfg['dir'] = $e2g['dir'];
$e2gModCfg['gdir'] =  $e2g['dir'];
$e2gModCfg['path'] = isset($_GET['path']) ? $_GET['path'] : '';
$e2gModCfg['parent_id'] = ( isset($_GET['pid']) && is_numeric($_GET['pid']) ) ? (int) $_GET['pid'] : 1;
$e2gModCfg['tag'] = isset($_GET['tag']) ? $_GET['tag'] : NULL;

/**
 * SYSTEM VARS
 */
// Easy 2 Gallery's debug parameter
$e2gModCfg['e2g_debug'] = $e2g['e2g_debug'];
// override MODx's debug variable
$debug = 0;
// MODx's manager theme
$e2gModCfg['_t'] = isset($_t) ? $_t :  $modx->config['manager_theme'];
// MODx's action ID
$e2gModCfg['_a'] = isset($_a) ? (int) $_a : (int) $_GET['a'];
// MODx's module ID
$e2gModCfg['_i'] = isset($_i) ? (int) $_i : (int) $_GET['id'];
// E2G's module pages
$e2gModCfg['e2gpg'] = isset($_GET['e2gpg']) ? (int) $_GET['e2gpg'] : '1';
// E2G's module views
$_SESSION['mod_view'] = isset($_GET['view']) ? $_GET['view'] : (isset($_SESSION['mod_view']) ? $_SESSION['mod_view'] : $e2g['mod_view']);

$pageConfigFile = realpath(E2G_MODULE_PATH . 'includes/configs/config.pages.easy2gallery.php');
//if (empty($pageConfigFile) || !file_exists($pageConfigFile)) {
//    return __LINE__ . ' : ' . $this->lng['config_file_err_missing'];
//}
require $pageConfigFile;

// Module's pages
$e2gModCfg['e2gPages'] = $e2gPages;
// Default gallery template
$e2gModCfg['file_thumb_gal_tpl'] = $e2gFilePageTpls['file_thumb_gal_tpl'];
// Default dir template
$e2gModCfg['file_thumb_dir_tpl'] = $e2gFilePageTpls['file_thumb_dir_tpl'];
// Default thumb template
$e2gModCfg['file_thumb_file_tpl'] = $e2gFilePageTpls['file_thumb_file_tpl'];
// Default table template
$e2gModCfg['file_default_table_tpl'] = $e2gFilePageTpls['file_default_table_tpl'];
// Default table's row template for dirs
$e2gModCfg['file_default_table_row_dir_tpl'] = $e2gFilePageTpls['file_default_table_row_dir_tpl'];
// Default table's row template for files
$e2gModCfg['file_default_table_row_file_tpl'] = $e2gFilePageTpls['file_default_table_row_file_tpl'];
// Tagging table template
$e2gModCfg['file_tag_table_tpl'] = $e2gFilePageTpls['file_tag_table_tpl'];
// Tagging table's row template for dirs
$e2gModCfg['file_tag_table_row_dir_tpl'] = $e2gFilePageTpls['file_tag_table_row_dir_tpl'];
// Tagging table's row template for files
$e2gModCfg['file_tag_table_row_file_tpl'] = $e2gFilePageTpls['file_tag_table_row_file_tpl'];
// Thumb's width
$e2gModCfg['mod_w'] = $e2g['mod_w'];
// Thumb's height
$e2gModCfg['mod_h'] = $e2g['mod_h'];
// Thumb's quality
$e2gModCfg['mod_thq'] = $e2g['mod_thq'];


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
        , 'tag' => (isset($_GET['tag']) ? $_GET['tag'] : null)
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
$e2gModCfg['index'] = isset($index) ? $index : MODX_MANAGER_URL . 'index.php?a=' . $e2gModCfg['_a']
        . '&amp;id=' . $e2gModCfg['_i']
        . '&amp;e2gpg=' . $e2gModCfg['e2gpg']
//        .(isset($_GET['page'])? '&amp;page='.$_GET['page']:null)
        . ( $alienparams != '' ? $alienparams : '' );

// blank page's href
$e2gModCfg['blank_index'] = MODX_MANAGER_URL . 'index.php?a=' . $e2gModCfg['_a']
        . '&amp;id=' . $e2gModCfg['_i']
        . ( $alienparams != '' ? $alienparams : '' );