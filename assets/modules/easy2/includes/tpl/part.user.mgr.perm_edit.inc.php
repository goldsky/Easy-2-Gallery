<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<form action="<?php echo $index . '&amp;act=save_mgr_permissions'; ?>" method="post">
    <input type="hidden" name="group_id" value="<?php echo $_GET['group_id']; ?>" />
    <ul>
        <?php
        $e2gMgrGroupIds = $modx->db->getValue($modx->db->query(
                                'SELECT permissions FROM ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr '
                                . 'WHERE membergroup_id=\'' . $_GET['group_id'] . '\''
                ));
        $e2gMgrGroupIdsArrays = array();
        $e2gMgrGroupIdsArrays = @explode(',', $e2gMgrGroupIds);

        require_once E2G_MODULE_PATH . 'includes/configs/config.pages.easy2gallery.php';
        foreach ($e2gPages as $v) {
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
           <input type="submit" value="<?php echo $lng['save']; ?>" />
           <input type="reset" value="<?php echo $lng['reset']; ?>" />
           <input type="button" value="<?php echo $lng['cancel']; ?>" onclick="document.location.href='<?php echo $index; ?>'" />
    </div>
</form>