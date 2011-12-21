<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// call up the database content first as the comparison subjects
$queryDir = mysql_query('SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs WHERE cat_id=' . (int) $_GET['dir_id']);
$row = mysql_fetch_array($queryDir, MYSQL_ASSOC);
mysql_free_result($queryDir);
?>
<ul class="actionButtons">
    <li>
        <a href="<?php echo $this->e2gModCfg['index']; ?>&amp;pid=<?php echo $this->e2gModCfg['parent_id']; ?>">
            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_left.png" alt="" /> <?php echo $this->lng['back']; ?>
        </a>
    </li>
    <li>
        <span class="h2title"><?php echo $this->lng['editing']; ?> </span><?php echo $this->lng['dir']; ?> <b><?php echo $row['cat_name']; ?></b>
    </li>
</ul>
<form name="list" action="<?php
echo $this->e2gModCfg['index'] . '&amp;act=save_dir&amp;pid=' . $this->e2gModCfg['parent_id'];
echo isset($_GET['tag']) ? '&amp;tag=' . $_GET['tag'] : NULL;
?>" method="post">
          <?php // This 'pid' hidden input is for page returning ?>
    <input type="hidden" name="pid" value="<?php echo $_GET['pid']; ?>" />
    <input type="hidden" name="parent_id" value="<?php echo $row['parent_id']; ?>" />
    <input type="hidden" name="cat_id" value="<?php echo $row['cat_id']; ?>" />
    <input type="hidden" name="cat_name" value="<?php echo $row['cat_name']; ?>" />

    <?php
          echo $this->plugin('OnE2GFolderEditFormPrerender');

          // DO NOT CHANGE THE ROOT FOLDER'S NAME FROM HERE, USE CONFIG INSTEAD.
          if ($row['cat_id'] != '1') {
    ?>
              <div>
                  <span><b><?php echo $this->lng['enter_new_dirname']; ?></b></span>
                  <span><b>:</b></span>
                  <span><input name="new_cat_name" type="text" value="<?php echo $row['cat_name']; ?>" size="30" /></span>
              </div>
    <?php
          }
    ?>
          <div class="clear">&nbsp;</div>
          <div class="tab-pane" id="tabEditFolderPane">
              <script type="text/javascript">
                  tpEditFolder = new WebFXTabPane(document.getElementById('tabEditFolderPane'));
              </script>
              <div class="tab-page" id="tabEditFolderPage">
                  <h2 class="tab"><?php echo $this->lng['general']; ?></h2>
                  <script type="text/javascript">
                      tpEditFolder.addTabPage( document.getElementById( 'tabEditFolderPage') );
                  </script>
                  <table id="dir_edit" cellspacing="0" cellpadding="2" class="aForm" >
                      <tr>
                          <td><b><?php echo $this->lng['object_id']; ?></b></td>
                          <td valign="top"><b>:</b></td>
                          <td><?php echo $row['cat_id']; ?></td>
                      </tr>
                      <tr>
                          <td><b><?php echo $this->lng['enter_new_alias']; ?></b></td>
                          <td valign="top"><b>:</b></td>
                          <td><input name="alias" type="text" value="<?php echo $row['cat_alias']; ?>" size="95" /></td>
                      </tr>
                      <tr>
                          <td><b><?php echo $this->lng['summary']; ?></b></td>
                          <td valign="top"><b>:</b></td>
                          <td><input name="summary" type="text" value="<?php echo $row['cat_summary']; ?>" size="95" /></td>
                      </tr>
                      <tr>
                          <td><b><?php echo $this->lng['tag']; ?></b></td>
                          <td valign="top"><b>:</b></td>
                          <td><input name="tag" type="text" value="<?php echo $row['cat_tag']; ?>" size="95" /></td>
                      </tr>
                      <tr>
                          <td valign="top"><b><?php echo $this->lng['description']; ?></b></td>
                          <td valign="top"><b>:</b></td>
                          <td><textarea name="description" style="width:500px" class="mceEditor" cols="" rows="4"><?php echo $row['cat_description']; ?></textarea></td>
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
                            $checkDirWebGroup = $this->_checkDirWebGroup($row['cat_id'], $webGroup['id']);
    ?>
                            <li class="no-bullet"><input type="checkbox" name="webGroups[]" value="<?php echo $webGroup['id']; ?>" <?php
                            echo $checkDirWebGroup === TRUE ? 'checked="checked"' : '';
    ?>/><?php echo $webGroup['name']; ?></li><?php } ?>
                        </ul><?php } ?>
                    </td>
                </tr>
                <tr>
                    <td valign="top"><b><?php echo $this->lng['redirect_link']; ?></b></td>
                    <td valign="top"><b>:</b></td>
                    <td>
                        <input name="cat_redirect_link" type="text" value="<?php echo $row['cat_redirect_link']; ?>" size="95" />
                    </td>
                </tr>
                <tr>
                    <td valign="top"><b><?php echo $this->lng['dir_thumb']; ?></b></td>
                    <td valign="top"><b>:</b></td>
                    <td>
                        <input name="thumb_id" type="text" value="<?php echo $row['cat_thumb_id']; ?>" size="5" /> <i><?php echo $this->lng['dir_thumb_desc']; ?></i>
                    </td>
                </tr>
            </table>
        </div>
    </div><?php echo $this->plugin('OnE2GFolderEditFormRender'); ?>
    <div style="margin-left: 80px;">
        <input type="submit" value="<?php echo $this->lng['save']; ?>" />
        <input type="button" value="<?php echo $this->lng['cancel']; ?>" onclick="document.location.href='<?php
                                                     echo $this->e2gModCfg['index'];
                                                     if ($_GET['tag'])
                                                         echo '&amp;tag=' . $_GET['tag'];
                                                     else
                                                         echo '&amp;pid=' . $this->e2gModCfg['parent_id']; ?>'" />
    </div>
</form>