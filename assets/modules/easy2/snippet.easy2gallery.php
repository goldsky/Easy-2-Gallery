<?php

/**
 * EASY 2 GALLERY
 * Gallery Snippet for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
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
    while ($row = mysql_fetch_array($query_configs)) {
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

if (file_exists(realpath($e2g['jsdisabled_tpl'])))
    include $e2g['jsdisabled_tpl'];

// Start retrieving snippet's parameters
if (file_exists(realpath(E2G_SNIPPET_PATH . 'includes/configs/params.snippet.easy2gallery.php'))) {
    require E2G_SNIPPET_PATH . 'includes/configs/params.snippet.easy2gallery.php';
} else {
    return 'Snippet\'s parameters file is missing.';
}

/**
 * EXECUTE SNIPPET
 */
$output = '';

// Load the core class file
if (!class_exists('E2gPub')) {
    if (file_exists(realpath(E2G_SNIPPET_PATH . 'includes/classes/e2g.public.class.php'))) {
        include E2G_SNIPPET_PATH . 'includes/classes/e2g.public.class.php';
    } else {
        echo "<h3>Missing Easy 2 Gallery core's class file.</h3>";
        return;
    }
}

// Load the snippet's class file
if (!class_exists('E2gSnippet')) {
    if (file_exists(realpath(E2G_SNIPPET_PATH . 'includes/classes/e2g.snippet.class.php'))) {
        include E2G_SNIPPET_PATH . "includes/classes/e2g.snippet.class.php";
    } else {
        echo "<h3>Missing Easy 2 Gallery snippet's class file.</h3>";
        return;
    }
}

// run the snippet
if (class_exists('E2gPub') && class_exists('E2gSnippet')) {
    $e2gSnippet = new E2gSnippet($modx, $e2gSnipCfg);
    $e2gSnippet->e2gpub_cfg = $e2gSnipCfg;
    $output = $e2gSnippet->display();
}
else {
    $output = "<b>Error: Easy 2 Gallery's snippet class not found</b>";
}

// Using a web access may result an empty output.
echo $output;