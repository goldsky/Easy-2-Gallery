<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<form name="list" action="<?php echo $index . '&amp;act=save_file&amp;pid=' . $parentId; ?>" method="post">
    <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>" />
    
    <?php echo $this->_plugin('OnE2GFileEditFormPrerender'); ?>
    
    <table width="100%">
        <tr>
            <td width="50%" style="vertical-align: top;">
                <div><?php echo $lng['editing']; ?> <?php echo $lng['files']; ?> <b><?php echo $row['filename']; ?> </b> (<?php echo $row['comments'] . ' ' . strtolower($lng['comments']); ?>)
                    &nbsp; &nbsp; &nbsp;
                    <a href="<?php echo $index; ?>&amp;pid=<?php echo $parentId; ?>"><?php echo $lng['back']; ?></a>
                </div>
                <div>
                    <span><b><?php echo $lng['file_rename']; ?></b></span>
                    <span><input name="newfilename" type="text" value="<?php echo $filename; ?>" size="20" style="text-align:right;" /> <?php echo $ext; ?></span>
                    <input type="hidden" name="filename" value="<?php echo $filename; ?>" />
                    <input type="hidden" name="ext" value="<?php echo $ext; ?>" />
                </div>
            </td>
            <td width="50%" style="vertical-align: top;">
                <div style="padding:2px;background-color: #eee;font-weight: bold;">
                    <a href="javascript:;" onclick="imPreview4('<?php echo $gdir . $row['filename']; ?>');void(0);">
                        <?php
                        echo $lng['uim_preview'];
                        ?>
                    </a>
                </div>
                <div class="imPreview2" id="pElt4"></div>
            </td>
        </tr>
    </table>

    <div class="clear">&nbsp;</div>
    <div class="tab-pane" id="tabEditFilePane">
        <script type="text/javascript">
            tpEditFile = new WebFXTabPane(document.getElementById('tabEditFilePane'));
        </script>
        <div class="tab-page" id="tabEditFilePage">
            <h2 class="tab"><?php echo $lng['general']; ?></h2>
            <script type="text/javascript">
                tpEditFile.addTabPage( document.getElementById( 'tabEditFilePage') );
            </script>
            <table id="file_edit" cellspacing="0" cellpadding="2" class="aForm">
                <tr>
                    <td><b><?php echo $lng['object_id']; ?></b></td>
                    <td valign="top"><b>:</b></td>
                    <td><?php echo $row['id']; ?></td>
                </tr>
                <tr>
                    <td><b><?php echo $lng['name']; ?></b></td>
                    <td valign="top"><b>:</b></td>
                    <td><input name="name" type="text" value="<?php echo $row['name']; ?>" size="95" /></td>
                </tr>
                <tr>
                    <td><b><?php echo $lng['summary']; ?></b></td>
                    <td valign="top"><b>:</b></td>
                    <td><input name="summary" type="text" value="<?php echo $row['summary']; ?>" size="95" /></td>
                </tr>
                <tr>
                    <td><b><?php echo $lng['tag']; ?></b></td>
                    <td valign="top"><b>:</b></td>
                    <td><input name="tag" type="text" value="<?php echo $row['tag']; ?>" size="95" /></td>
                </tr>
                <tr>
                    <td valign="top" ><b><?php echo $lng['description']; ?></b></td>
                    <td valign="top"><b>:</b></td>
                    <td valign="top" >
                        <textarea name="description" style="width:500px" class="mceEditor" cols="" rows="4"><?php echo $row['description']; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td valign="top"><b><?php echo $lng['user_permissions']; ?></b></td>
                    <td valign="top"><b>:</b></td>
                    <td><?php
    $webGroups = $modx->db->makeArray($modx->db->query(
                            'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'webgroup_names ORDER BY id ASC'));
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
            </table>
        </div>
        <?php echo $this->_plugin('OnE2GFileEditFormRender'); ?>
                        <div style="margin-left: 80px;">
                            <input type="submit" value="<?php echo $lng['save']; ?>" />
                            <input type="button" value="<?php echo $lng['cancel']; ?>" onclick="document.location.href='<?php echo $index; ?>&amp;pid=<?php echo $parentId; ?>'" />
        </div>
    </div>
</form>