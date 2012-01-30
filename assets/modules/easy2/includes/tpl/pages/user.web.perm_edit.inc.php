<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<form action="<?php echo $this->e2gModCfg['index'] . '&amp;act=save_web_dirs_perm'; ?>" method="post">
    <input type="hidden" name="group_id" value="<?php echo $this->sanitizedGets['group_id']; ?>" />
    <ul style="border-bottom: 1px dotted #CCC;">
        <?php
        $e2gDirWebGroupIds = $this->_dirWebGroupIds($this->sanitizedGets['group_id']);

        $dirWebGroups = $this->modx->db->makeArray($this->modx->db->query(
                                'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                                . 'ORDER BY cat_id ASC'
                ));
        foreach ($dirWebGroups as $v) {
        ?>
            <li><input type="checkbox" name="dirWebAccess[]" value="<?php echo $v['cat_id']; ?>"
            <?php
            echo in_array($v['cat_id'], $e2gDirWebGroupIds) ? ' checked="checked"' : '';
            ?> /><?php echo $v['cat_name']; ?></li>
            <?php } ?>
       </ul>
       <div>
           <input type="submit" value="<?php echo $this->lng['save']; ?>" />
           <input type="reset" value="<?php echo $this->lng['reset']; ?>" />
           <input type="button" value="<?php echo $this->lng['cancel']; ?>" onclick="document.location.href='<?php echo $this->e2gModCfg['index']; ?>'" />
    </div>
</form>