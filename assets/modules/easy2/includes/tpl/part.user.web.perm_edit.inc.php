<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<form action="<?php echo $index . '&amp;act=save_web_dirs_perm'; ?>" method="post">
    <input type="hidden" name="group_id" value="<?php echo $_GET['group_id']; ?>" />
    <ul style="border-bottom: 1px dotted #CCC;">
        <?php
        $e2gDirWebGroupIds = $this->_dirWebGroupIds($_GET['group_id']);

        $dirWebGroups = $modx->db->makeArray($modx->db->query(
                                'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                                . 'ORDER BY cat_id ASC'
                ));
        foreach ($dirWebGroups as $v) {
        ?>
            <li><input type="checkbox" name="webDirsAccess[]" value="<?php echo $v['cat_id']; ?>"
            <?php
            echo in_array($v['cat_id'], $e2gDirWebGroupIds) ? ' checked="checked"' : '';
            ?> /><?php echo $v['cat_name']; ?></li>
            <?php } ?>
       </ul>
       <div>
           <input type="submit" value="<?php echo $lng['save']; ?>" />
           <input type="reset" value="<?php echo $lng['reset']; ?>" />
           <input type="button" value="<?php echo $lng['cancel']; ?>" onclick="document.location.href='<?php echo $index; ?>'" />
    </div>
</form>