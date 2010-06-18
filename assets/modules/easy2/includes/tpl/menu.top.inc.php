<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div id="e2g_topmenu">
    <form name="topmenu" action="" method="post">
        <ul class="actionButtons">
            <li>
                <a href="<?php echo $index; ?>&act=synchro">
                    <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/refresh.png" alt="" /> <?php echo $lng['synchro']; ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $index; ?>&act=clean_cache">
                    <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/trash.png" alt="" /> <?php echo $lng['clean_cache']; ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $index; ?>&page=create_dir&pid=<?php echo $parent_id; ?>">
                    <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/folder_add.png" alt="" /> <?php echo $lng['dir_create']; ?>
                </a>
            </li>
            <!--li>
                <a href="<?php echo $index; ?>&page=search_all&pid=<?php echo $parent_id; ?>">
                    <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/preview.png" alt="" /> <?php echo $lng['search']; ?>
                </a>
            </li-->
        </ul>
        <ul class="actionButtons">
            <li>
                <?php echo $lng['gotofolder']; ?>:
                <select name="newparent" onchange="submitform(1)">
                    <option value=""></option>
                    <?php echo $this->_get_folder_options(0,1); ?>
                </select>
            </li>
            <li>
                <?php echo $lng['tag']; ?>:
                <select name="opentag" onchange="submitform(2)">
                    <option value=""></option>
                    <?php echo $this->_get_tag_options($_get_tag); ?>
                </select>
            </li>
        </ul>
    </form>
</div>