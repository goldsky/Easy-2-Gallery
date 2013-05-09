<?php

die('This is not intended as a direct included file.
    Instead, copy-paste the content into the appropriate TVs.');

/**
 * get folders structure for DropDown List Menu TV
 * http://modxcms.com/forums/index.php/topic,50212.msg293621.html#msg293621
 */
isset($parentId) ? "" : $parentId = 0;

$selectDir = 'SELECT parent_id, cat_id, cat_name, cat_level '
        . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
        . 'WHERE parent_id=' . $parentId . ' '
;

$queryDir = mysql_query($selectDir);
$numDir = @mysql_num_rows($queryDir);

$childrenDirs = array();
if ($queryDir) {
    while ($l = mysql_fetch_assoc($queryDir)) {
        $childrenDirs[$l['cat_id']]['parent_id'] = $l['parent_id'];
        $childrenDirs[$l['cat_id']]['cat_id'] = $l['cat_id'];
        $childrenDirs[$l['cat_id']]['cat_name'] = $l['cat_name'];
        $childrenDirs[$l['cat_id']]['cat_level'] = $l['cat_level'];
    }
}
mysql_free_result($queryDir);

$output = (isset($output) ? $output : '');

foreach ($childrenDirs as $childDir) {
    // DISPLAY
    // **************** ONLY START MARGIN **************** //

    for ($k = 1; $k < $childDir['cat_level']; $k++) {
        if ($k == 1)
            $output .= '&nbsp;&nbsp;&nbsp;';
        else
            $output .= '&nbsp;|&nbsp;&nbsp;';
    }
    for ($k = 1; $k < $childDir['cat_level']; $k++) {
        if ($k == 1)
            $output .= '&nbsp;|';
    }
    if ($childDir['cat_level'] > 1)
        $output .= '--';

    // ***************** ONLY END MARGIN ***************** //

    $output .= ' ' . $childDir['cat_name'] . ' [id:' . $childDir['cat_id'] . ']==gid' . $childDir['cat_id'] . '||';

    //**************************************************** //
    // GET SUB-FOLDERS
    $selectSub = 'SELECT parent_id, cat_id, cat_name '
            . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
            . 'WHERE parent_id=' . $childDir['cat_id'] . ' '
            . 'ORDER BY cat_name ASC'
    ;
    $querySub = mysql_query($selectSub);
    if ($querySub) {
        $numsub = @mysql_num_rows($querySub);
        if ($numsub > 0) {
            $output .= $modx->runSnippet('getE2GTree', array('parentid' => $childDir['cat_id']));
        }
    }
    mysql_free_result($querySub);
    //**************************************************** //
}

return $output;