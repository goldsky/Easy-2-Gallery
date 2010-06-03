<?php
// just to avoid direct call to this file. it's recommended to always use this.
if ( !defined(IN_PARSER_MODE) ) {
    die('<h2>Unauthorized access</h2>You\'re not allowed to access file folder');
}
/*
 * Available file's $_plug array : [id]
 *                                 [dir_id]
 *                                 [filename]
 *                                 [size]
 *                                 [name]
 *                                 [description]
 *                                 [date_added]
 *                                 [last_modified]
 *                                 [comments]
 *                                 [status]
*/

if (!empty($plugin_css)) {
    $modx->regClientCSS($plugin_css,'screen');
} else {
    $modx->regClientCSS(E2G_SNIPPET_URL.'plugins/starrating/starrating.css','screen');
}
$modx->regClientCSS(E2G_SNIPPET_URL.'plugins/starrating/jquery.rating.css','screen');
$modx->regClientStartupScript(E2G_SNIPPET_URL.'plugins/starrating/jquery.js');
$modx->regClientStartupScript(E2G_SNIPPET_URL.'plugins/starrating/jquery.rating.js');

$w = $cl_cfg['w'];
$modx->regClientStartupHTMLBlock('
<style type="text/css" media="screen">
    .starbg {
        width: '.$w.'px;
    }
</style>
            ');

$_plug_displays[] = '
    <span class="starbg">
        <input class="star" type="radio" name="starrating_'.$_plug['id'].'" value="20" />
        <input class="star" type="radio" name="starrating_'.$_plug['id'].'" value="40" />
        <input class="star" type="radio" name="starrating_'.$_plug['id'].'" value="60" />
        <input class="star" type="radio" name="starrating_'.$_plug['id'].'" value="80" />
        <input class="star" type="radio" name="starrating_'.$_plug['id'].'" value="100" />
    </span>';