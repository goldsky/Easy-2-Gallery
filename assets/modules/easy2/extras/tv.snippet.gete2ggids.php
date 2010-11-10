<?php

die('This is not intended as a direct included file.
    Instead, copy-paste the content into the appropriate TVs.');

/**
 * @snippet getE2Gids
 * @package easy 2 gallery's snippet
 */
if (!isset($give))
    return NULL;

$combinedIds = $modx->getTemplateVarOutput(array('e2g-galleries'));
if (empty($combinedIds))
    return NULL;

$xpldIds = @explode(',', $combinedIds['e2g-galleries']);
$gids = array();
$gid = '';
$fids = array();
$fid = '';

foreach ($xpldIds as $xpldId) {
    $id = @explode(':', $xpldId);
    if ($id[0] == 'gid')
        $gids[] = $id[1];
    if ($id[0] == 'fid')
        $fids[] = $id[1];
}

$params = array();

if (!empty($gids)) {
    $gid = @implode(',', $gids);
} else {
    $gid = '1';
}

if (!empty($fids)) {
    $fid = @implode(',', $fids);
}

if ($give == 'gid') {
    if (isset($setPlaceholer)) {
        $modx->setPlaceholder($setPlaceholer, $gid);
    }
    return $gid;
}
if ($give == 'fid') {
    if (isset($setPlaceholer)) {
        $modx->setPlaceholder($setPlaceholer, $fid);
    }
    return $fid;
}