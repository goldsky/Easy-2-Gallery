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
if (file_exists(E2G_SNIPPET_PATH . 'includes/configs/config.easy2gallery.php')) {
    require E2G_SNIPPET_PATH . 'includes/configs/config.easy2gallery.php';
} else {
    return 'Missing config file.';
}

// Before continue, check the browser's Javascript
echo '
<noscript>
    <style type="text/css">
        #' . $e2g['e2g_wrapper'] . ' {display:none;}
    </style>';
if (file_exists($e2g['jsdisabled_tpl']))
    include_once $e2g['jsdisabled_tpl'];
echo '
</noscript>';

if (file_exists(E2G_SNIPPET_PATH . 'includes/configs/snippet.params.easy2gallery.php')) {
    require E2G_SNIPPET_PATH . 'includes/configs/snippet.params.easy2gallery.php';
} else {
    return 'Missing snippet\'s parameters file.';
}

/**
 * EXECUTE SNIPPET
 */
if (!class_exists('e2g_pub')) {
    include E2G_SNIPPET_PATH . "includes/classes/e2g.public.class.php";
}
if (!class_exists('e2g_snip')) {
    include E2G_SNIPPET_PATH . "includes/classes/e2g.snippet.class.php";
}
if (class_exists('e2g_pub') && class_exists('e2g_snip')) {
    $e2g = new e2g_snip($e2gsnip_cfg);
    $e2g->e2gpub_cfg = $e2gsnip_cfg;
    $output = $e2g->display();
} else {
    $output = "<h3>error: e2g_snip class not found</h3>";
}
return $output;
?>