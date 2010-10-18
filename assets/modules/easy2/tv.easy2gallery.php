<?php
die('This is not intended as a direct included file. Instead, copy-paste the content into the appropriate TVs.');

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
    while ($l = mysql_fetch_array($queryDir, MYSQL_ASSOC)) {
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





/**
 * @snippet getE2GTree
 * @package easy 2 gallery's TV
 * @link http://community.modx-cms.ru/blog/tips_and_tricks/784.html
 */
$rootName = $modx->db->getValue($modx->db->select('`cat_name`', $modx->db->config['table_prefix'] . 'easy2_dirs', '`cat_id`=1'));

$dirs = "SELECT `cat_id`, `cat_name`, `cat_level`
        FROM  `" . $modx->db->config['table_prefix'] . "easy2_dirs`
        WHERE  `cat_visible` = 1
        AND `cat_id` > 1
        ORDER BY `cat_left`, `cat_id` ASC";

$dirResult = $modx->db->query($dirs);
//$folderIcon = htmlspecialchars("<img src='../assets/modules/easy2/includes/icons/folder.png' />");
//$folderIcon = '<img src="../assets/modules/easy2/includes/icons/folder.png" alt="" />';

if ($modx->db->getRecordCount($dirResult) >= 1) {
    $output = $folderIcon . $rootName . '==1||';
    $nbsp = chr(0xC2) . chr(0xA0);
    while ($row = $modx->db->getRow($dirResult)) {
        if ($row['cat_level'] > 1) {
            $output .= str_repeat($nbsp, ($row['cat_level'] - 1) * 3);
            $output .= "|--";
        }
        $output .= $folderIcon . $row['cat_name'] . "==gid:" . $row['cat_id'] . "||";

        $files = "SELECT `id`, `filename`
        FROM  `" . $modx->db->config['table_prefix'] . "easy2_files`
        WHERE  `dir_id` = " . $row['cat_id'] . "
        ORDER BY `id`, `filename` ASC";
        $fileResult = $modx->db->query($files);
        while ($frow = $modx->db->getRow($fileResult)) {
            $output .= str_repeat($nbsp, $row['cat_level'] * 3);
            $output .= "|--";
            $output .= $frow['filename'] . "==fid:" . $frow['id'] . "||";
        }
    }
    $output = substr($output, 0, strlen($output) - 2);
} else {
    $output = 'Error ocured';
}
return $output;







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
} else
    $gid = '1';

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