<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// Dir list
//$dirs = @glob('../'.$this->_e2gDecode($gdir).'*', GLOB_ONLYDIR);
//if(is_array($dirs)) natsort($dirs);

include_once E2G_MODULE_PATH . 'includes/tpl/pages/menu.top.inc.php';
?>
<a href="<?php echo $index; ?>"><?php echo $lng['back']; ?></a>
<table cellspacing="2" cellpadding="0">
    <tr>
        <td valign="top"><b><?php echo $lng['tag']; ?></b></td>
        <td valign="top">:</td>
        <td>
            <?php echo '<a href="' . $index . '&amp;page=tag&tag=' . $tag . '">' . $tag . '</a>'; ?>
        </td>
    </tr>
</table>
<br />
<table cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td valign="top">
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
                    if (count($mdirs) > 0) {
                        if (is_array($mdirs))
                            natsort($mdirs);

                        foreach ($mdirs as $f) {
                            $name = $this->_basenameSafe($f['name']);
                            $name = $this->_e2gEncode($name);
//                            $path = $this->_getPath($mdirs[$name]['parent_id']);
                            $alias = $mdirs[$name]['alias'];
                            $tag = $mdirs[$name]['cat_tag'];
                            $sanitized_tags = @explode(',', $tag);
                            for ($c = 0; $c < count($sanitized_tags); $c++) {
                                $sanitized_tags[$c] = trim($sanitized_tags[$c]);
                            }

                            $time = ($mdirs[$name]['last_modified'] == '' ? '---' : strtotime($mdirs[$name]['last_modified']));

                            $countFiles = mysql_result(mysql_query(
                                                    'SELECT COUNT(id) FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                                    . 'WHERE dir_id = ' . $mdirs[$name]['id']
                                            ), 0, 0);

                            if (($mdirs[$name]['cat_visible'] == 1)) {
                                $n = '<a href="' . $index . '&amp;pid=' . $mdirs[$name]['id'] . '"><b>' . $mdirs[$name]['name'] . '</b></a> [id: ' . $mdirs[$name]['id'] . ']';
                            } else {
                                $n = '<a href="' . $index . '&amp;pid=' . $mdirs[$name]['id'] . '"><i>' . $mdirs[$name]['name'] . '</i></a> [id: ' . $mdirs[$name]['id'] . '] <i>(' . $lng['invisible'] . ')</i>';
                            }
                            $id = $mdirs[$name]['id'];
                            $ext = '';
                            // edit name
                            $buttons = '<a href="' . $index . '&amp;page=edit_dir&dir_id=' . $id . '&name=' . $name . '&amp;pid=' . $mdirs[$name]['parent_id'] . '">
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
                                <a href="<?php echo $index; ?>&amp;pid=<?php echo $mdirs[$name]['parent_id']; ?>&amp;page=openexplorer" onclick="showTab('file')"><?php echo $path['string']; ?></a><br />
                            <?php } ?>
                        </td>
                        <td valign="top"><img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/folder<?php echo $ext; ?>.png" width="16" height="16" border="0" alt="" /></td>
                        <td valign="top"><?php echo $n; ?> (<?php echo $countFiles; ?>)</td>
                        <td valign="top"><?php echo $alias; ?></td>
                        <td valign="top">
                            <?php
                            $multiple_tags = @explode(',', $tag);
                            $count_tags = count($multiple_tags);
                            for ($c = 0; $c < $count_tags; $c++) {
                                echo '<a href="' . $index . '&amp;page=tag&tag=' . trim($multiple_tags[$c]) . '">' . trim($multiple_tags[$c]) . '</a>';
                                if ($c < ($count_tags - 1))
                                    echo ', ';
                            }
                            ?>
                        </td>
                        <td valign="top"><?php echo @date($e2g['mod_date_format'], $time); ?></td>
                        <td valign="top">---</td>
                        <td align="right" nowrap>
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
                        } // foreach ($mdirs as $f)
                    } // if (count($mdirs)>0)
                    //******************************************************************/
                    //************* FILE content for the current directory *************/
                    //******************************************************************/

                    $mfiles = isset($mfiles) ? $mfiles : array();
                    if (count($mfiles) > 0) {
                        if (is_array($mfiles))
                            natsort($mfiles);
                        foreach ($mfiles as $f) {
                            $name = $this->_basenameSafe($f['name']);
                            $name = $this->_e2gEncode($name);
                            $n_stat = $mfiles[$name]['status'] == 1 ? '' : '<i>(' . $lng['hidden'] . ')</i>';
//                            $path = $this->_getPath($mfiles[$name]['dir_id']);
                            $alias = $mfiles[$name]['alias'];
                            $time = ($mfiles[$name]['last_modified'] == '' ? '---' : strtotime($mfiles[$name]['last_modified']));
                            $tag = $mfiles[$name]['tag'];
                            $sanitized_tags = @explode(',', $tag);
                            for ($c = 0; $c < count($sanitized_tags); $c++) {
                                $sanitized_tags[$c] = trim($sanitized_tags[$c]);
                            }
                            $ext = 'picture';
                            $n = $mfiles[$name]['status'] == 1 ? $name : '<i>' . $name . '</i>';
                            $n_stat = $mfiles[$name]['status'] == 1 ? '' : '<i>(' . $lng['hidden'] . ')</i>';
                            $tag = $mfiles[$name]['tag'];
                            $id = $mfiles[$name]['id'];
                            $buttons = '
<a href="' . $index . '&amp;page=comments&amp;file_id=' . $id . '&amp;pid=' . $mfiles[$name]['dir_id'] . '">
    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/comments.png" width="16" height="16" alt="' . $lng['comments'] . '" title="' . $lng['comments'] . '" border=0>
</a>
<a href="' . $index . '&amp;page=edit_file&amp;file_id=' . $id . '&amp;pid=' . $mfiles[$name]['dir_id'] . '">
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
                                <a href="<?php echo $index; ?>&amp;pid=<?php echo $mfiles[$name]['dir_id']; ?>&amp;page=openexplorer" onclick="showTab('file')"><?php echo $path['string']; ?></a><br />
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
                                echo '<a href="' . $index . '&amp;page=tag&tag=' . trim($multiple_tags[$c]) . '">' . trim($multiple_tags[$c]) . '</a>';
                                if ($c < ($count_tags - 1))
                                    echo ', ';
                            }
                            ?>
                        </td>
                        <td valign="top"><?php echo @date($e2g['mod_date_format'], $time); ?></td>
                        <td valign="top"><?php echo $size; ?>Kb</td>
                        <td align="right" nowrap valign="top"><?php echo $buttons; ?>
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
                    } // if (count($mfiles)>0)
                    ?>
                </table>
                <?php include_once E2G_MODULE_PATH . 'includes/tpl/pages/menu.bottom.inc.php'; ?>
            </form>
        </td>
    </tr>
</table>