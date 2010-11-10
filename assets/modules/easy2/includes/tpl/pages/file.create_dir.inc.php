<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<ul class="actionButtons">
    <li>
        <a href="<?php echo $index; ?>&amp;pid=<?php echo $parentId; ?>">
            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_left.png" alt="" /> <?php echo $lng['back']; ?>
        </a>
    </li>
    <li>
        <span class="h2title"><?php echo $lng['dir_create']; ?></span>
    </li>
</ul>
<form name="list" action="<?php echo $index . '&amp;act=create_dir&amp;pid=' . $parentId; ?>" method="post">
    <?php echo $this->_plugin('OnE2GFolderCreateFormPrerender'); ?>
    <table id="dir_create" cellspacing="0" cellpadding="2" class="aForm" >
        <tr>
            <td><b><?php echo $lng['enter_dirname']; ?> :</b></td>
            <td><input name="name" type="text" size="30" /></td>
        </tr>
    </table>
    <div class="tab-pane" id="tabCreateFolderPane">
        <script type="text/javascript">
            tpCreateFolder = new WebFXTabPane(document.getElementById('tabCreateFolderPane'));
        </script>
        <div class="tab-page" id="tabCreateFolderPage">
            <h2 class="tab"><?php echo $lng['general']; ?></h2>
            <script type="text/javascript">
                tpCreateFolder.addTabPage( document.getElementById( 'tabCreateFolderPage') );
            </script>
            <table id="dir_info" cellspacing="0" cellpadding="2" class="aForm" >
                <tr>
                    <td><b><?php echo $lng['enter_new_alias']; ?> :</b></td>
                    <td><input name="alias" type="text" size="30" /></td>
                </tr>
                <tr>
                    <td><b><?php echo $lng['summary']; ?> :</b></td>
                    <td><input name="summary" type="text" size="95" /></td>
                </tr>
                <tr>
                    <td><b><?php echo $lng['tag']; ?> :</b></td>
                    <td><input name="tag" type="text" size="95" /></td>
                </tr>
                <tr>
                    <td valign="top"><b><?php echo $lng['description']; ?> :</b></td>
                    <td><textarea name="description" style="width:500px" class="mceEditor" cols="" rows="5"></textarea></td>
                </tr>
            </table>
        </div>
    </div><?php echo $this->_plugin('OnE2GFolderCreateFormRender'); ?>
    <div style="margin-left: 90px;">
        <input type="submit" value="<?php echo $lng['save']; ?>" />
        <input type="button" value="<?php echo $lng['cancel']; ?>" onclick="document.location.href='<?php echo $index; ?>&amp;pid=<?php echo $parentId; ?>'" />
    </div>
</form>