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

$_SESSION['e2g_instances'];
// Before continue, check the browser's Javascript
// We need the CSS selector
$modx->regClientStartupScript(MODX_BASE_URL . 'assets/libs/sizzle/sizzle.js');
$modx->regClientStartupHTMLBlock('
        <style type="text/css">
            div.' . $e2g['e2g_wrapper'] . ' {
                display:none;
            }
        </style>
        <script type="text/javascript">
            window.onload = function() {
                var e2g_divwrapper = Sizzle(\'div[id^=' . $e2g['e2g_wrapper'] . ']\');
                for (var i = 0; i < e2g_divwrapper.length; i++) {
                    e2g_divwrapper[i].style.display="block";
                }
            };
        </script>
    ');

if (file_exists($e2g['jsdisabled_tpl']))
    include $e2g['jsdisabled_tpl'];

// Start retrieving snippet's parameters
if (file_exists(E2G_SNIPPET_PATH . 'includes/configs/params.snippet.easy2gallery.php')) {
    require E2G_SNIPPET_PATH . 'includes/configs/params.snippet.easy2gallery.php';
} else {
    return 'Snippet\'s parameters file is missing.';
}

/**
 * EXECUTE SNIPPET
 */
$output = '';

// Load the core class file
if (!class_exists('e2g_pub')) {
    if (file_exists(E2G_SNIPPET_PATH . 'includes/classes/e2g.public.class.php')) {
        include E2G_SNIPPET_PATH . 'includes/classes/e2g.public.class.php';
    } else {
        echo "<h3>Missing Easy 2 Gallery core's class file.</h3>";
        return;
    }
}

// Load the snippet's class file
if (!class_exists('e2g_snip')) {
    if (file_exists(E2G_SNIPPET_PATH . 'includes/classes/e2g.public.class.php')) {
        include E2G_SNIPPET_PATH . "includes/classes/e2g.snippet.class.php";
    } else {
        echo "<h3>Missing Easy 2 Gallery snippet's class file.</h3>";
        return;
    }
}

// run the snippet
if (class_exists('e2g_pub') && class_exists('e2g_snip')) {
    $e2g = new e2g_snip($modx, $e2gsnip_cfg);
    $e2g->e2gpub_cfg = $e2gsnip_cfg;
    $output = $e2g->display();
}
else {
    $output = "<b>Error: Easy 2 Gallery's snippet class not found</b>";
}

// Using a web access may result empty output.
//if ($output == '')
//    $output = '<b>Empty output or wrong parameter(s)</b>';

echo $output;