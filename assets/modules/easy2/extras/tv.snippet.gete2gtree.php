<?php

die('This is not intended as a direct included file.
    Instead, copy-paste the content into the appropriate TVs.');

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