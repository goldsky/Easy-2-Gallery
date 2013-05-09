<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// call up the database content first as the comparison subjects
$res = mysql_query('SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $this->sanitizedGets['file_id']);
$row = mysql_fetch_assoc($res);
mysql_free_result($res);

$ext = substr($row['filename'], strrpos($row['filename'], '.'));
$filename = substr($row['filename'], 0, -(strlen($ext)));
?>
<ul class="actionButtons">
    <li>
        <a href="<?php echo $this->e2gModCfg['index'] . (!empty($this->sanitizedGets['tag']) ? '&amp;tag=' . $this->sanitizedGets['tag'] : '&amp;pid=' . $this->e2gModCfg['parent_id']); ?>">
            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_left.png" alt="" /> <?php echo $this->lng['back']; ?>
        </a>
    </li>
    <li>
        <span class="h2title"><?php echo $this->lng['editing']; ?></span>
        <?php echo $this->lng['files']; ?> <b><?php echo $row['filename']; ?> </b> (<?php echo $row['comments'] . ' ' .  mb_strtolower($this->lng['comments']); ?>)
    </li>
</ul>
<?php echo $this->plugin('OnE2GFileEditFormPrerender'); ?>
        <div class="clear">&nbsp;</div>
        <div class="tab-pane" id="tabEditFilePane">
            <script type="text/javascript">
                tpEditFile = new WebFXTabPane(document.getElementById('tabEditFilePane'));
            </script>
            <div class="tab-page" id="tabEditFilePage">
                <h2 class="tab"><?php echo $this->lng['general']; ?></h2>
                <script type="text/javascript">
                    tpEditFile.addTabPage( document.getElementById( 'tabEditFilePage') );
                </script>
                <table border="0" cellspacing="0" cellpadding="2" class="aForm" width="100%">
                    <tr>
                        <td style="vertical-align: top; width: 200px;" nowrap="nowrap">
                            <table>
                                <tr>
                                    <td>
                                        <a href="<?php echo MODX_SITE_URL . $this->e2gModCfg['gdir'] . $row['filename']; ?>"
                                           class="highslide" onclick="return hs.expand(this)">
                                            <img src='<?php echo E2G_MODULE_URL; ?>preview.easy2gallery.php?path=<?php echo $this->e2gModCfg['gdir'] . $row['filename']; ?>' alt="" />
                                        </a>
                                        <hr />
                                        <ul>
                                            <li><b><?php echo $this->lng['date']; ?> :</b> <?php echo $this->getTime($row['date_added'], $row['last_modified'], $this->e2gModCfg['gdir'] . $row['filename']); ?></li>
                                            <li><b><?php echo $this->lng['size']; ?> :</b> <?php echo round($row['size'] / 1024); ?> Kb</li>
                                            <li><b><?php echo $this->lng['w']; ?> :</b> <?php echo $row['width']; ?> px</li>
                                            <li><b><?php echo $this->lng['h']; ?> :</b> <?php echo $row['height']; ?> px</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <form name="list" action="<?php echo $this->e2gModCfg['index'] . '&amp;act=save_file&amp;pid=' . $this->e2gModCfg['parent_id'] . (isset($this->sanitizedGets['tag']) ? '&amp;tag=' . $this->sanitizedGets['tag'] : NULL); ?>" method="post">
                                <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>" />
                                <table id="file_edit" width="100%">
                                    <tr>
                                        <td colspan="3">
                                            <span><b><?php echo $this->lng['file_rename']; ?> : </b></span>
                                            <span><input name="newfilename" type="text" value="<?php echo $filename; ?>" size="20" style="text-align:right;" /> <?php echo $ext; ?></span>
                                            <input type="hidden" name="filename" value="<?php echo $filename; ?>" />
                                            <input type="hidden" name="ext" value="<?php echo $ext; ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo $this->lng['object_id']; ?></b></td>
                                        <td valign="top"><b>:</b></td>
                                        <td><?php echo $row['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo $this->lng['name']; ?></b></td>
                                        <td valign="top"><b>:</b></td>
                                        <td><input name="alias" type="text" value="<?php echo $row['alias']; ?>" style="width: 100%" /></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo $this->lng['summary']; ?></b></td>
                                        <td valign="top"><b>:</b></td>
                                        <td><input name="summary" type="text" value="<?php echo $row['summary']; ?>" style="width: 100%" /></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo $this->lng['tag']; ?></b></td>
                                        <td valign="top"><b>:</b></td>
                                        <td><input name="tag" type="text" value="<?php echo $row['tag']; ?>" style="width: 100%" /></td>
                                    </tr>
                                    <tr>
                                        <td valign="top" ><b><?php echo $this->lng['description']; ?></b></td>
                                        <td valign="top"><b>:</b></td>
                                        <td valign="top" >
                                            <textarea name="description" style="width:100%" class="mceEditor" cols="" rows="4"><?php echo $row['description']; ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td valign="top"><b><?php echo $this->lng['user_permissions']; ?></b></td>
                                        <td valign="top"><b>:</b></td>
                                        <td><?php
        $webGroups = $this->modx->db->makeArray($this->modx->db->query(
                                'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'webgroup_names ORDER BY id ASC'));
        if (count($webGroups) > 0) {
?>
                                    <ul><?php
                                    foreach ($webGroups as $webGroup) {
                                        $checkFileWebGroup = $this->_checkFileWebGroup($row['id'], $webGroup['id']);
?>
                                        <li class="no-bullet"><input type="checkbox" name="webGroups[]" value="<?php echo $webGroup['id']; ?>" <?php
                                        echo $checkFileWebGroup === TRUE ? 'checked="checked"' : '';
?>/><?php echo $webGroup['name']; ?></li><?php } ?>
                                    </ul><?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td valign="top"><b><?php echo $this->lng['redirect_link']; ?></b></td>
                                <td valign="top"><b>:</b></td>
                                <td>
                                    <input name="redirect_link" type="text" value="<?php echo $row['redirect_link']; ?>" style="width: 100%" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <input type="submit" value="<?php echo $this->lng['save']; ?>" />
                                    <input type="button" value="<?php echo $this->lng['cancel']; ?>"
                                           onclick="document.location.href='<?php echo $this->e2gModCfg['index'] . (!empty($this->sanitizedGets['tag']) ? '&amp;tag=' . $this->sanitizedGets['tag'] : '&amp;pid=' . $this->e2gModCfg['parent_id']); ?>'" />
                                </td>
                            </tr>
                        </table>

                    </form>
                </td>
            </tr>
        </table>
    </div><?php echo $this->plugin('OnE2GFileEditFormRender'); ?>
</div>