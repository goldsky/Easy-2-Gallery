<?php

/**
 * EASY 2 GALLERY
 * Gallery Snippet for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@fastmail.fm>
 */
require_once MODX_BASE_PATH . 'assets/modules/easy2/includes/utf8/utf8.php';

// Easy 2 Gallery snippet path
if (!defined('E2G_SNIPPET_PATH')) {
    define('E2G_SNIPPET_PATH', MODX_BASE_PATH . 'assets/modules/easy2/');
}
// Easy 2 Gallery snippet URL
if (!defined('E2G_SNIPPET_URL')) {
    define('E2G_SNIPPET_URL', MODX_BASE_URL . 'assets/modules/easy2/');
}
// Loading the E2G's configurations
if (!isset($e2g)) {
    $query_configs = $modx->db->select('*', $modx->db->config['table_prefix'] . 'easy2_configs');
    if (!$query_configs)
        return FALSE;
    while ($row = mysql_fetch_assoc($query_configs)) {
        $e2g[$row['cfg_key']] = $row['cfg_val'];
    }
}

/**
 * Before continue, check the browser's Javascript availability
 * Appending Style Nodes with Javascript
 * @author Jon Raasch
 * @link http://jonraasch.com/blog/javascript-style-node
 */
$modx->regClientStartupHTMLBlock('
        <style type="text/css">
            div.' . $e2g['e2g_wrapper'] . ' {visibility:hidden}
        </style>
        <script type="text/javascript">
            window.onload = function() {
                var css = document.createElement(\'style\');
                css.type = \'text/css\';
                var styles = \'div.' . $e2g['e2g_wrapper'] . ' {visibility:visible}\';

                if (css.styleSheet) css.styleSheet.cssText = styles;
                else css.appendChild(document.createTextNode(styles));

                document.getElementsByTagName("head")[0].appendChild(css);
            };
        </script>
');

$jsDisabledTplFile = realpath($e2g['jsdisabled_tpl']);
if (!empty($jsDisabledTplFile) && file_exists($jsDisabledTplFile))
    include $jsDisabledTplFile;

// Start retrieving snippet's parameters
$snipParamFile = realpath(E2G_SNIPPET_PATH . 'includes/configs/params.snippet.easy2gallery.php');
if (!empty($snipParamFile) && file_exists($snipParamFile)) {
    require $snipParamFile;
} else {
    return 'Snippet\'s parameters file is missing.';
}

/**
 * EXECUTE SNIPPET
 */
$output = '';

// Load the core class file
if (!class_exists('E2gPub')) {
    $publicClassFile = realpath(E2G_SNIPPET_PATH . 'includes/models/e2g.public.class.php');
    if (!empty($publicClassFile) && file_exists($publicClassFile)) {
        include $publicClassFile;
    } else {
        echo "<h3>Missing Easy 2 Gallery core's class file.</h3>";
        return;
    }
}

// Load the snippet's class file
if (!class_exists('E2gSnippet')) {
    $snippetClassFile = realpath(E2G_SNIPPET_PATH . 'includes/models/e2g.snippet.class.php');
    if (!empty($snippetClassFile) && file_exists($snippetClassFile)) {
        include $snippetClassFile;
    } else {
        echo "<h3>Missing Easy 2 Gallery snippet's class file.</h3>";
        return;
    }
}

// run the snippet
$e2gSnippet = new E2gSnippet($modx, $e2gSnipCfg);

/**
 * 1. '&gid' : full gallery directory (directory - &gid - default)
 * 2. '&fid' : one file only (file - $e2gSnipCfg['fid'])
 * 3. '&rgid' : random file in a directory (random - $e2gSnipCfg['rgid'])
 * 4. '&slideshow' : slideshow by fid-s or rgid-s or gid-s
 */
// to avoid gallery's thumbnails display on the landingpage's page
if ($modx->documentIdentifier != $e2gSnipCfg['landingpage']) {
    if (empty($e2gSnipCfg['gid'])
            && !empty($e2gSnipCfg['fid'])
            && empty($e2gSnipCfg['slideshow'])
    ) {
        return $e2gSnippet->imgFile();
    }
    if (!empty($e2gSnipCfg['rgid'])
            && empty($e2gSnipCfg['slideshow'])
    ) {
        return $e2gSnippet->imgRandom();
    }
    if (empty($e2gSnipCfg['rgid'])
            && empty($e2gSnipCfg['slideshow'])
    ) {
        return $e2gSnippet->thumbsGallery(); // default
    }
}
if (!empty($e2gSnipCfg['slideshow'])) {
    return $e2gSnippet->slideshow($e2gSnipCfg['slideshow']);
}
if (!empty($e2gSnipCfg['landingpage']) && !empty($_GET['fid'])) {
    return $e2gSnippet->landingPage($_GET['fid']);
}

return '';