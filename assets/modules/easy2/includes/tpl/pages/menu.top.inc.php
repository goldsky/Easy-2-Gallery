<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div id="e2g_topmenu">
    <form name="topmenu" action="" method="post">
        <ul class="actionButtons">
            <li>
                <a href="<?php echo $index; ?>&amp;act=synchro">
                    <img src="<?php echo MODX_MANAGER_URL; ?>media/style/MODxCarbon/images/icons/refresh.png" alt="" /> <?php echo $lng['synchro']; ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $index; ?>&amp;act=clean_cache">
                    <img src="<?php echo MODX_MANAGER_URL; ?>media/style/MODxCarbon/images/icons/trash.png" alt="" /> <?php echo $lng['clean_cache']; ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $index; ?>&amp;page=create_dir&amp;pid=<?php echo $parentId; ?>">
                    <img src="<?php echo MODX_MANAGER_URL; ?>media/style/MODxCarbon/images/icons/folder_add.png" alt="" /> <?php echo $lng['dir_create']; ?>
                </a>
            </li>
            <?php
            if ($userRole == '1'
                    || in_array($e2gPages['upload']['access'], $userPermissionsArray)
            ) {
            ?>                        
                <li>
                    <a href="<?php echo $blankIndex; ?>&amp;e2gpg=<?php echo $e2gPages['upload']['e2gpg']; ?>&amp;pid=<?php echo $parentId; ?>">
                        <img src="<?php echo MODX_MANAGER_URL; ?>media/style/MODxCarbon/images/icons/add.png" alt="" /> <?php echo $lng['upload']; ?>
                    </a>
                </li>
            <?php } ?>
            <li class="views">
                <a href="<?php echo $index; ?>&amp;view=list<?php
            echo isset($_GET['pid']) ? '&amp;pid=' . $parentId : '';
            echo isset($_GET['path']) ? '&amp;path=' . $_GET['path'] : '';
            ?>" title="list">
                    <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/table.png" alt="" />
                </a>&nbsp;
                <a href="<?php echo $index; ?>&amp;view=thumbnails<?php
                   echo isset($_GET['pid']) ? '&amp;pid=' . $parentId : '';
                   echo isset($_GET['path']) ? '&amp;path=' . $_GET['path'] : '';
            ?>" title="thumbnails">
                    <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/slides.png" alt="" />
                </a>
            </li>
        </ul>
        <ul class="actionButtons">
            <li style="float:right">
                <?php echo $lng['gotofolder']; ?>:
                   <select name="newparent" onchange="submitform(1)">
                       <option value="">&nbsp;</option>
                    <?php echo $this->_folderOptions(0, 1); ?>
                </select>
            </li>
            <li style="float:right">
                <?php echo $lng['tag']; ?>:
                    <select name="opentag" onchange="submitform(2)">
                        <option value="">&nbsp;</option>
                    <?php echo $this->_tagOptions($tag); ?>
                </select>
            </li>
        </ul>
    </form>
</div>
<div style="clear:both;"></div>