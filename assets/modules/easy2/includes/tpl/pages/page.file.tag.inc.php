<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$tag = $_GET['tag'];
$readTag = $this->_readTag($tag);

include_once E2G_MODULE_PATH . 'includes/tpl/pages/menu.top.inc.php';
?>
<ul class="actionButtons">
    <li>
        <a href="<?php echo $index; ?>">
            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_left.png" alt="" /> <?php echo $lng['back']; ?>
        </a>
    </li>
</ul>

<table cellspacing="2" cellpadding="0">
    <tr>
        <td valign="top"><b><?php echo $lng['tag']; ?></b></td>
        <td valign="top">:</td>
        <td>
            <?php echo '<a href="' . $index . '&amp;tag=' . $tag . '">' . $tag . '</a>'; ?>
        </td>
    </tr>
</table>
<br />
<form name="list" action="" method="post">
    <table width="100%" cellpadding="2" border="0" cellspacing="0" class="grid" style="margin-bottom:10px">
        <tr>
            <td width="25"><input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;"></td>
            <th><?php echo $lng['path']; ?></th>
            <th width="20"><?php echo $lng['type']; ?></th>
            <th><?php echo $lng['dir'] . ' / ' . $lng['filename']; ?></th>
            <th><?php echo $lng['alias'] . ' / ' . $lng['name']; ?></th>
            <th><?php echo $lng['tag']; ?></th>
            <th width="80"><?php echo $lng['modified']; ?></th>
            <th width="40"><?php echo $lng['size']; ?></th>
            <th width="60" align="right"><?php echo $lng['actions']; ?></th>
        </tr>

        <?php
            //******************************************************************/
            //***************** FOLDERS/DIRECTORIES/GALLERIES ******************/
            //******************************************************************/
            if (count($readTag['dir']) > 0) {
                if (is_array($readTag['dir']))
                    natsort($readTag['dir']);

                foreach ($readTag['dir'] as $f) {
                    $name = $this->_basenameSafe($f['name']);
                    $name = $this->_e2gEncode($name);
//                            $path = $this->_getPath($readTag['dir'][$name]['parent_id']);
                    $alias = $readTag['dir'][$name]['alias'];
                    $tag = $readTag['dir'][$name]['cat_tag'];
                    $sanitized_tags = @explode(',', $tag);
                    for ($c = 0; $c < count($sanitized_tags); $c++) {
                        $sanitized_tags[$c] = trim($sanitized_tags[$c]);
                    }

                    $time = ($readTag['dir'][$name]['last_modified'] == '' ? '---' : strtotime($readTag['dir'][$name]['last_modified']));

                    $countFiles = mysql_result(mysql_query(
                                            'SELECT COUNT(id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                            . 'WHERE dir_id = ' . $readTag['dir'][$name]['id']
                                    ), 0, 0);

                    if (($readTag['dir'][$name]['cat_visible'] == 1)) {
                        $n = '<a href="' . $index . '&amp;pid=' . $readTag['dir'][$name]['id'] . '"><b>' . $readTag['dir'][$name]['name'] . '</b></a> [id: ' . $readTag['dir'][$name]['id'] . ']';
                    } else {
                        $n = '<a href="' . $index . '&amp;pid=' . $readTag['dir'][$name]['id'] . '"><i>' . $readTag['dir'][$name]['name'] . '</i></a> [id: ' . $readTag['dir'][$name]['id'] . '] <i>(' . $lng['invisible'] . ')</i>';
                    }
                    $id = $readTag['dir'][$name]['id'];
                    $ext = '';
                    // edit name
                    $buttons = '<a href="' . $index . '&amp;page=edit_dir&dir_id=' . $id . '&name=' . $name . '&amp;pid=' . $readTag['dir'][$name]['parent_id'] . '">
                            <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_edit.png" width="16" height="16"
                                alt="' . $lng['edit'] . '" title="' . $lng['edit'] . '" border=0>
                                    </a>';
                    // print out the content
        ?>

                    <tr<?php echo $rowClass[$rowNum % 2]; ?>>
                        <td valign="top">
                            <input name="dir[<?php echo (empty($id) ? 'd' . $rowNum : $id); ?>]" value="<?php echo $gdir . $name; ?>"
                                   type="checkbox" style="border:0;padding:0">
                        </td><td valign="top">
                <?php if ($path['string'] != '') {
                ?>
                        <a href="<?php echo $index; ?>&amp;pid=<?php echo $readTag['dir'][$name]['parent_id']; ?>&amp;page=openexplorer" onclick="showTab('file')"><?php echo $path['string']; ?></a><br />
                <?php } ?>
                </td>
                <td valign="top"><img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/folder<?php echo $ext; ?>.png" width="16" height="16" border="0" alt="" /></td>
                <td valign="top"><?php echo $n; ?> (<?php echo $countFiles; ?>)</td>
                <td valign="top"><?php echo $alias; ?></td>
                <td valign="top"><?php echo $this->_createTagLinks($tag); ?></td>
                <td valign="top" nowrap="nowrap"><?php echo @date($e2g['mod_date_format'], $time); ?></td>
                <td valign="top">---</td>
                <td align="right" nowrap="nowrap">
                <?php echo $buttons; ?>
                    <a href="<?php echo $index; ?>&amp;act=delete_dir&dir_path=<?php echo $gdir . $path['string'] . $name . (empty($id) ? '' : '&dir_id=' . $id); ?>"
                       onclick="return confirmDeleteFolder();">
                        <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/delete.png" border="0"
                             alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                    </a>
                </td>
            </tr>
        <?php
                    $rowNum++;
                } // foreach ($readTag['dir'] as $f)
            } // if (count($readTag['dir'])>0)
            //******************************************************************/
            //************* FILE content for the current directory *************/
            //******************************************************************/

            $readTag['file'] = isset($readTag['file']) ? $readTag['file'] : array();
            if (count($readTag['file']) > 0) {
                if (is_array($readTag['file']))
                    natsort($readTag['file']);
                foreach ($readTag['file'] as $f) {
                    $name = $this->_basenameSafe($f['name']);
                    $name = $this->_e2gEncode($name);
                    $n_stat = $readTag['file'][$name]['status'] == 1 ? '' : '<i>(' . $lng['hidden'] . ')</i>';
//                            $path = $this->_getPath($readTag['file'][$name]['dir_id']);
                    $alias = $readTag['file'][$name]['alias'];
                    $time = ($readTag['file'][$name]['last_modified'] == '' ? '---' : strtotime($readTag['file'][$name]['last_modified']));
                    $tag = $readTag['file'][$name]['tag'];
                    $sanitized_tags = @explode(',', $tag);
                    for ($c = 0; $c < count($sanitized_tags); $c++) {
                        $sanitized_tags[$c] = trim($sanitized_tags[$c]);
                    }
                    $ext = 'picture';
                    $n = $readTag['file'][$name]['status'] == 1 ? $name : '<i>' . $name . '</i>';
                    $n_stat = $readTag['file'][$name]['status'] == 1 ? '' : '<i>(' . $lng['hidden'] . ')</i>';
                    $tag = $readTag['file'][$name]['tag'];
                    $id = $readTag['file'][$name]['id'];
                    $buttons = '
<a href="' . $index . '&amp;page=comments&amp;file_id=' . $id . '&amp;pid=' . $readTag['file'][$name]['dir_id'] . '">
    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/comments.png" width="16" height="16" alt="' . $lng['comments'] . '" title="' . $lng['comments'] . '" border=0>
</a>
<a href="' . $index . '&amp;page=edit_file&amp;file_id=' . $id . '&amp;pid=' . $readTag['file'][$name]['dir_id'] . '">
    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_edit.png" width="16" height="16" alt="' . $lng['edit'] . '" title="' . $lng['edit'] . '" border=0>
</a>';
                    // content
        ?>
                    <tr<?php echo $rowClass[$rowNum % 2]; ?>>
                        <td valign="top">
                            <input name="im[<?php echo (empty($id) ? 'f' . $rowNum : $id); ?>]" value="<?php echo $gdir . $path['string'] . $name; ?>"
                                   type="checkbox" style="border:0;padding:0">
                        </td><td valign="top">
                <?php if ($path['string'] != '') {
                ?>
                        <a href="<?php echo $index; ?>&amp;pid=<?php echo $readTag['file'][$name]['dir_id']; ?>&amp;page=openexplorer" onclick="showTab('file')"><?php echo $path['string']; ?></a><br />
                <?php } ?>
                </td>
                <td valign="top"><img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/<?php echo $ext; ?>.png" width="16" height="16" alt="" /></td>
                <td valign="top">
                    <div>
                        <a href="javascript:void(0)" onclick="imPreview('<?php echo $gdir . $path['string'] . $name; ?>', <?php echo $rowNum; ?>);"><?php echo $n; ?>
                        </a> <?php echo '[id: ' . $id . ']'; ?> <?php echo $n_stat; ?>
                    </div>
                    <div class="imPreview" id="rowPreview_<?php echo $rowNum; ?>" style="display:none;"></div>

                <?php //echo $n;  ?>

                </td>
                <td valign="top"><?php echo $alias; ?></td>
                <td valign="top">
                <?php
                    $multiple_tags = @explode(',', $tag);
                    $count_tags = count($multiple_tags);
                    for ($c = 0; $c < $count_tags; $c++) {
                        echo '<a href="' . $index . '&amp;tag=' . trim($multiple_tags[$c]) . '">' . trim($multiple_tags[$c]) . '</a>';
                        if ($c < ($count_tags - 1))
                            echo ', ';
                    }
                ?>
                </td>
                <td valign="top" nowrap="nowrap"><?php echo @date($e2g['mod_date_format'], $time); ?></td>
                <td valign="top"><?php echo $size; ?>Kb</td>
                <td align="right" nowrap="nowrap" valign="top"><?php echo $buttons; ?>
                    <a href="<?php echo $index; ?>&amp;act=delete_file&amp;file_path=<?php echo $gdir . $path['string'] . $name . (empty($id) ? '' : '&amp;file_id=' . $id); ?>"
                       onclick="return confirmDelete();">
                        <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/delete.png" border="0"
                             alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                    </a>
                </td>
            </tr>
        <?php
                    $rowNum++;
                } // foreach ($files as $f)
            } // if (count($readTag['file'])>0)
        ?>
        </table>
    <?php include_once E2G_MODULE_PATH . 'includes/tpl/pages/menu.bottom.inc.php'; ?>
</form>