<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// call up the database content first as the comparison subjects
$res = mysql_query('SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $_GET['file_id']);
$row = mysql_fetch_array($res, MYSQL_ASSOC);
mysql_free_result($res);

$ext = substr($row['filename'], strrpos($row['filename'], '.'));
$filename = substr($row['filename'], 0, -(strlen($ext)));
?>
<ul class="actionButtons">
    <li>
        <a href="<?php
echo $index;
if (isset($_GET['tag'])) {
?>&amp;tag=<?php
           echo $_GET['tag'];
       } else {
?>&amp;pid=<?php
           echo $parentId;
       }
?>">
            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_left.png" alt="" /> <?php echo $lng['back']; ?>
        </a>
    </li>
    <li>
        <span class="h2title"><?php echo $lng['editing']; ?></span>
<?php echo $lng['files']; ?> <b><?php echo $row['filename']; ?> </b> (<?php echo $row['comments'] . ' ' . strtolower($lng['comments']); ?>)
    </li>
</ul>
<form name="list" action="<?php
       echo $index . '&amp;act=save_file&amp;pid=' . $parentId;
       echo isset($_GET['tag']) ? '&amp;tag=' . $_GET['tag'] : NULL;
?>" method="post">
     <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>" />

<?php echo $this->_plugin('OnE2GFileEditFormPrerender'); ?>

       <table width="100%">
           <tr>
               <td width="50%" style="vertical-align: top;">
                   <div>
                       <span><b><?php echo $lng['file_rename']; ?></b></span>
                       <span><input name="newfilename" type="text" value="<?php echo $filename; ?>" size="20" style="text-align:right;" /> <?php echo $ext; ?></span>
                       <input type="hidden" name="filename" value="<?php echo $filename; ?>" />
                       <input type="hidden" name="ext" value="<?php echo $ext; ?>" />
                   </div>
               </td>
               <td width="50%" style="vertical-align: top;">
                   <div style="padding:2px;background-color: #eee;font-weight: bold;">
                       <a href="javascript:;" onclick="imPreview4('<?php echo $gdir . $row['filename']; ?>');void(0);"><?php echo $lng['uim_preview']; ?>
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
                       <td><input name="alias" type="text" value="<?php echo $row['alias']; ?>" size="95" /></td>
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
        </div><?php echo $this->_plugin('OnE2GFileEditFormRender'); ?>
        <div style="margin-left: 80px;">
            <input type="submit" value="<?php echo $lng['save']; ?>" />
            <input type="button" value="<?php echo $lng['cancel']; ?>" onclick="document.location.href='<?php echo $index; ?>&amp;pid=<?php echo $parentId; ?>'" />
        </div>
    </div>
</form>