<?php

/**
 * EASY 2 GALLERY
 * Tag cloud snippet for Easy 2 Gallery Module for MODx Evolution
 * @author goldsky <goldsky@fastmail.fm>
 * @package     easy 2 gallery
 * @subpackage  easy 2 tagcloud
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

// PARAMETERS
/**
 * @var string $position    options: above | below | replace (default)
 */
$position = !empty($position) ? $position : 'below';
/**
 * @var string $show        options: dir | file | [empty]
 */
$show = !empty($show) ? $show : '';
/**
 * @var string $class       class name, default: easy-tag-cloud
 */
$class = !empty($class) ? $class : 'easy-tag-cloud';
/**
 * @var string $css         CSS file name for HTML header
 */
$css = !empty($css) ? $css : '';
/**
 * @var string $tag         tag variable
 */
$tag = !empty($_GET['tag']) ? $_GET['tag'] : '';

// QUERIES
$unionTags = '(SELECT DISTINCT cat_tag FROM modx_easy2_dirs) UNION (SELECT DISTINCT tag FROM modx_easy2_files)';
$queryDirTags = 'SELECT DISTINCT cat_tag FROM modx_easy2_dirs';
$queryFileTags = 'SELECT DISTINCT tag FROM modx_easy2_files';

switch ($show) {
    case 'dir':
        $sql = $queryDirTags;
        break;
    case 'file':
    case 'image':
        $sql = $queryFileTags;
        break;
    default :
        $sql = $unionTags;
}

$result = $modx->db->query($sql);

$tagArray = array();
while ($l = mysql_fetch_row($result)) {
    $rowTag = $l[0];
    $rowTags = @explode(',', $rowTag);
    foreach ($rowTags as $v) {
        $tagArray[] = trim($v);
    }
}

$tagArray = array_filter(array_unique($tagArray));
sort($tagArray);

if (isset($tag) && in_array($tag, $tagArray)) {
    $easy2 = $modx->runSnippet('easy2', array(
        'tag' => $tag
            ));
}

if (!empty($css)) {
    $modx->regClientStartupCSS($css);
} else {
    $modx->regClientStartupHTMLBlock('
<style type="text/css">
    div.' . $class . ' {display: block;}
    div.' . $class . ' ul li {
        float: left;
        list-style: none;
        margin-right: 10px;
    }
    div.' . $class . ' ul li a {
        padding-left:10px;
        padding-right:10px;
        text-decoration: none;
        color: black;

        border: 1px solid #ccc;
        -webkit-border-radius: 10px;
        -moz-border-radius: 10px;
        border-radius: 10px;

        background: rgb(238,238,238); /* Old browsers */
        background: -moz-linear-gradient(top, rgba(238,238,238,1) 0%, rgba(204,204,204,1) 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(238,238,238,1)), color-stop(100%,rgba(204,204,204,1))); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top, rgba(238,238,238,1) 0%,rgba(204,204,204,1) 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top, rgba(238,238,238,1) 0%,rgba(204,204,204,1) 100%); /* Opera11.10+ */
        background: -ms-linear-gradient(top, rgba(238,238,238,1) 0%,rgba(204,204,204,1) 100%); /* IE10+ */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#eeeeee", endColorstr="#cccccc",GradientType=0 ); /* IE6-9 */
        background: linear-gradient(top, rgba(238,238,238,1) 0%,rgba(204,204,204,1) 100%); /* W3C */
    }
    div.' . $class . ' ul li a:hover {
        background: rgb(204,204,204); /* Old browsers */
        background: -moz-linear-gradient(top, rgba(204,204,204,1) 0%, rgba(238,238,238,1) 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(204,204,204,1)), color-stop(100%,rgba(238,238,238,1))); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top, rgba(204,204,204,1) 0%,rgba(238,238,238,1) 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top, rgba(204,204,204,1) 0%,rgba(238,238,238,1) 100%); /* Opera11.10+ */
        background: -ms-linear-gradient(top, rgba(204,204,204,1) 0%,rgba(238,238,238,1) 100%); /* IE10+ */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#cccccc", endColorstr="#eeeeee",GradientType=0 ); /* IE6-9 */
        background: linear-gradient(top, rgba(204,204,204,1) 0%,rgba(238,238,238,1) 100%); /* W3C */
    }
</style>
');
}

// Start retrieving snippet's parameters
$snipParamFile = realpath(E2G_SNIPPET_PATH . 'includes/configs/params.snippet.easy2gallery.php');
if (file_exists($snipParamFile)) {
    require $snipParamFile;
} else {
    return 'Snippet\'s parameters file is missing.';
}

$output = '';

if ($position == 'above') {
    $output .= $easy2;
}
$output .= '<div class="' . $class . '"><ul>';
foreach ($tagArray as $tagItem) {
    $output .= '<li><a href="'
            . $modx->makeUrl($modx->documentIdentifier, null, 'sid='. $e2gSnipCfg['e2g_instances'] . '&amp;tag=' . $tagItem . '#' . $e2gSnipCfg['e2g_instances'] . '_' . $tagItem)
            . '">' . $tagItem . '</a></li>';
}

$output .= '</ul></div>';

if ($position == 'below') {
    $output .= $easy2;
}

if ($position == 'replace') {
    $output = $easy2;
}

return $output;