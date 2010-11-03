<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<form action="<?php echo $index . '&amp;act=save_web_files_perm'; ?>" method="post">
    <input type="hidden" name="group_id" value="<?php echo $_GET['group_id']; ?>" />
    <ul style="border-bottom: 1px dotted #CCC;">
        <?php
        $e2gFileWebGroupsIds = $this->_fileWebGroupIds($_GET['group_id']);

        $fileWebGroups = $modx->db->makeArray($modx->db->query(
                                'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                . 'ORDER BY id ASC'
                ));
        foreach ($fileWebGroups as $v) {
        ?>
            <li><input type="checkbox" name="fileWebAccess[]" value="<?php echo $v['id']; ?>"
            <?php
            echo in_array($v['id'], $e2gFileWebGroupsIds) ? ' checked="checked"' : '';
            ?> /><?php echo $v['filename']; ?></li>
            <?php } ?>
       </ul>
       <div>
           <input type="submit" value="<?php echo $lng['save']; ?>" />
           <input type="reset" value="<?php echo $lng['reset']; ?>" />
           <input type="button" value="<?php echo $lng['cancel']; ?>" onclick="document.location.href='<?php echo $index; ?>'" />
    </div>
</form>