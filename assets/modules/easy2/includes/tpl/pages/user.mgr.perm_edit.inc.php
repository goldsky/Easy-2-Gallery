<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<form action="<?php echo $this->e2gModCfg['index'] . '&amp;act=save_mgr_permissions'; ?>" method="post">
    <input type="hidden" name="group_id" value="<?php echo $this->sanitizedGets['group_id']; ?>" />
    <ul>
        <?php
        $e2gMgrGroupIds = $this->modx->db->getValue($this->modx->db->query(
                                'SELECT permissions FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_users_mgr '
                                . 'WHERE id=\'' . $this->sanitizedGets['group_id'] . '\''
                ));
        $e2gMgrGroupIdsArrays = array();
        $e2gMgrGroupIdsArrays = @explode(',', $e2gMgrGroupIds);

        foreach ($this->e2gModCfg['e2gPages'] as $v) {
            // the dashboard is always be checked / shown
            if ($v['title'] == 'dashboard') {
//                echo ' checked="checked"';
                continue;
            }
        ?>
            <li style="float: none;">
                <input type="checkbox" name="mgrAccess[]" value="<?php echo $v['access']; ?>"
            <?php
            echo in_array($v['access'], $e2gMgrGroupIdsArrays) ? ' checked="checked"' : '';
            ?> /><?php echo $v['lng']; ?></li>
            <?php } ?>
       </ul>
       <div>
           <input type="submit" value="<?php echo $this->lng['save']; ?>" />
           <input type="reset" value="<?php echo $this->lng['reset']; ?>" />
           <input type="button" value="<?php echo $this->lng['cancel']; ?>" onclick="document.location.href='<?php echo $this->e2gModCfg['index']; ?>'" />
    </div>
</form>