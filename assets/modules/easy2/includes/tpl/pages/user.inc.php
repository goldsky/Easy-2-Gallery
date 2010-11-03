<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<p><?php echo htmlspecialchars_decode($lng['user_page_desc']); ?></p>
<!--p>TODO: create plugin on WebUsr* &amp; User*</p-->

<div id="e2g_topmenu">
    <form name="topmenu" action="" method="post">
        <ul class="actionButtons">
            <li>
                <a href="<?php echo $index; ?>&amp;act=synchro_users">
                    <img src="<?php echo MODX_MANAGER_URL; ?>media/style/MODxCarbon/images/icons/refresh.png" alt="" /> <?php echo $lng['synchro']; ?>
                </a>
            </li>
        </ul>
    </form>
</div>

<div class="tab-pane" id="tabAccessPermissions">
    <script type="text/javascript">
        tpAccPerm = new WebFXTabPane(document.getElementById('tabAccessPermissions'));
    </script>
    <div class="tab-page" id="tabMemberGroups">
        <h2 class="tab"><?php echo $lng['user_groups']; ?></h2>
        <script type="text/javascript">
            tpAccPerm.addTabPage( document.getElementById( 'tabMemberGroups') );
        </script>
        <table style="width: 100%;">
            <tr>
                <td class="tdLeft">
                    <div class="curveBox">
                        <div class="h2title"><?php echo $lng['user_mgr_groups']; ?></div>
                        <div><?php echo htmlspecialchars_decode($lng['user_mgr_groups_desc']); ?></div>
                        <?php
                        /**
                         * Display the Manager Access to the Module's pages (and features)
                         */
                        $mgrGroups = $modx->db->makeArray($modx->db->query(
                                                'SELECT e.*, m.name FROM ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr e '
                                                . 'LEFT JOIN ' . $modx->db->config['table_prefix'] . 'membergroup_names m '
                                                . 'ON e.membergroup_id = m.id '
                                                . 'ORDER BY e.membergroup_id ASC'
                                ));
                        if (count($mgrGroups) > 0) {
                        ?>
                            <ul class="curveBoxList">
                            <?php
                            foreach ($mgrGroups as $mgrGroup) {
                                // for unsynchronized groups
                                if (!isset($mgrGroup['id'])) {
                                    continue;
                                }
                            ?>
                                <li><span class="h3title"><?php echo $mgrGroup['name']; ?></span>
                                    <div class="tab-pane" id="tabUserMgrGroups_<?php echo $mgrGroup['id']; ?>">
                                        <script type="text/javascript">
                                            tpMgr = new WebFXTabPane(document.getElementById('tabUserMgrGroups_<?php echo $mgrGroup['id']; ?>'));
                                        </script>
                                        <div class="tab-page" id="tabUserMgrMembers_<?php echo $mgrGroup['id']; ?>">
                                            <h2 class="tab"><?php echo $lng['user_members']; ?></h2>
                                            <script type="text/javascript">
                                                tpMgr.addTabPage( document.getElementById( 'tabUserMgrMembers_<?php echo $mgrGroup['id']; ?>') );
                                            </script>
                                        <?php
                                        $mgrGroupUsers = $modx->db->makeArray($modx->db->query(
                                                                'SELECT mu.* FROM ' . $modx->db->config['table_prefix'] . 'manager_users mu '
                                                                . 'LEFT JOIN ' . $modx->db->config['table_prefix'] . 'member_groups mg '
                                                                . 'ON mu.id = mg.member '
                                                                . 'WHERE mg.user_group = ' . $mgrGroup['membergroup_id'] . ' '
                                                                . 'ORDER BY mu.id ASC'
                                                ));
                                        if (count($mgrGroupUsers) > 0) {
                                        ?>
                                            <ul class="curveBoxList">
                                            <?php
                                            foreach ($mgrGroupUsers as $mgrGroupUser) {
                                            ?>
                                                <li class="e2g_button">
                                                <?php
                                                if ($modx->hasPermission('edit_user')) {
                                                ?>
                                                    <a href="<?php echo MODX_MANAGER_URL; ?>index.php?a=12&amp;id=<?php echo $mgrGroupUser['id']; ?>">
                                                    <?php
                                                }
                                                echo $mgrGroupUser['username'];
                                                if ($modx->hasPermission('edit_user')) {
                                                    ?>
                                                </a>
                                                <?php } ?>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                        <?php } ?>
                                    </div>
                                    <div class="tab-page" id="tabUserMgrPermissions_<?php echo $mgrGroup['id']; ?>">
                                        <h2 class="tab"><?php echo $lng['user_access']; ?></h2>
                                        <script type="text/javascript">
                                            tpMgr.addTabPage( document.getElementById( 'tabUserMgrPermissions_<?php echo $mgrGroup['id']; ?>') );
                                        </script>

                                        <?php
                                        if ($_GET['page'] == 'edit_mgrPerm' && $_GET['group_id'] == $mgrGroup['id']) {
                                            include E2G_MODULE_PATH . 'includes/tpl/pages/user.mgr.perm_edit.inc.php';
                                        } else {
                                        ?>
                                            <a href="<?php echo $index; ?>&amp;page=edit_mgrPerm&amp;group_id=<?php echo $mgrGroup['id']; ?>"><?php echo $lng['edit']; ?></a><br />
                                        <?php
                                            $e2gMgrGroupIds = $modx->db->getValue($modx->db->query(
                                                                    'SELECT permissions FROM ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr '
                                                                    . 'WHERE membergroup_id=\'' . $mgrGroup['id'] . '\''
                                                    ));
                                            $e2gMgrGroupIdsArrays = array();
                                            $e2gMgrGroupIdsArrays = @explode(',', $e2gMgrGroupIds);

                                            // loading the hyperlinks ($e2gPages)
                                            require_once E2G_MODULE_PATH . 'includes/configs/config.pages.easy2gallery.php';

                                            foreach ($e2gPages as $k => $v) {
                                                $e2gPageAccess[$v['access']] = $v['lng'];
                                            }

                                            $i = 0;
                                            foreach ($e2gMgrGroupIdsArrays as $v) {
                                                $i++;
                                                if (isset($e2gPageAccess[$v]))
                                                    echo $e2gPageAccess[$v];
                                                if ($i < (count($e2gMgrGroupIdsArrays)))
                                                    echo ', ';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </li>
                            <?php } ?>
                                </ul>
                        <?php } ?>
                            </div>
                        </td>
                        <td class="tdRight">
                            <div class="curveBox">
                                <div class="h2title"><?php echo $lng['user_web_groups']; ?></div>
                                <div><?php echo htmlspecialchars_decode($lng['user_web_groups_desc']); ?></div>
                        <?php
                                $webGroups = $modx->db->makeArray($modx->db->query(
                                                        'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'webgroup_names ORDER BY id ASC'));
                                if (count($webGroups) > 0) {
                        ?>
                                    <ul class="curveBoxList">
                            <?php
                                    foreach ($webGroups as $webGroup) {
                                        // for unsynchronized groups
                                        if (!isset($webGroup['id'])) {
                                            continue;
                                        }
                            ?>
                                        <li><span class="h3title"><?php echo $webGroup['name']; ?></span>

                                            <div class="tab-pane" id="tabWebGroups_<?php echo $webGroup['id']; ?>">
                                                <script type="text/javascript">
                                                    tpWeb = new WebFXTabPane(document.getElementById('tabWebGroups_<?php echo $webGroup['id']; ?>'));
                                                </script>
                                                <div class="tab-page" id="tabWebMembers_<?php echo $webGroup['id']; ?>">
                                                    <h2 class="tab"><?php echo $lng['user_members']; ?></h2>
                                                    <script type="text/javascript">
                                                        tpWeb.addTabPage( document.getElementById( 'tabWebMembers_<?php echo $webGroup['id']; ?>') );
                                                    </script>
                                        <?php
                                        $webGroupUsers = $modx->db->makeArray($modx->db->query(
                                                                'SELECT wu.* FROM ' . $modx->db->config['table_prefix'] . 'web_users wu '
                                                                . 'LEFT JOIN ' . $modx->db->config['table_prefix'] . 'web_groups wg '
                                                                . 'ON wu.id = wg.webuser '
                                                                . 'WHERE wg.webgroup = ' . $webGroup['id'] . ' '
                                                                . 'ORDER BY wu.id ASC'
                                                ));
                                        if (count($webGroupUsers) > 0) {
                                        ?>
                                            <ul class="curveBoxList">
                                            <?php
                                            foreach ($webGroupUsers as $webGroupUser) {
                                            ?>
                                                <li class="e2g_button">
                                                <?php
                                                if ($modx->hasPermission('edit_web_user')) {
                                                ?>
                                                    <a href="<?php echo MODX_MANAGER_URL; ?>index.php?a=88&amp;id=<?php echo $webGroupUser['id']; ?>">
                                                    <?php
                                                }
                                                echo $webGroupUser['username'];

                                                if ($modx->hasPermission('edit_web_user')) {
                                                    ?>
                                                </a>
                                                <?php } ?>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                        <?php } ?>
                                    </div>

                                    <div class="tab-page" id="tabWebPermissions_<?php echo $webGroup['id']; ?>">
                                        <h2 class="tab"><?php echo $lng['user_permissions']; ?></h2>
                                        <script type="text/javascript">
                                            tpWeb.addTabPage( document.getElementById( 'tabWebPermissions_<?php echo $webGroup['id']; ?>') );
                                        </script>
                                        <?php
                                        if ($_GET['page'] == 'edit_webPerm' && $_GET['group_id'] == $webGroup['id']) {
                                            include E2G_MODULE_PATH . 'includes/tpl/pages/user.web.perm_edit.inc.php';
                                        ?>
                                            <p>
                                                <a href="<?php echo $index; ?>&amp;page=edit_webFilesPerm&amp;group_id=<?php echo $webGroup['id']; ?>"><?php echo $lng['edit']; ?></a>
                                                <b><?php echo $lng['files']; ?></b>
                                            </p>
                                        <?php
                                        } elseif ($_GET['page'] == 'edit_webFilesPerm' && $_GET['group_id'] == $webGroup['id']) {
                                        ?>
                                            <p>
                                                <a href="<?php echo $index; ?>&amp;page=edit_webPerm&amp;group_id=<?php echo $webGroup['id']; ?>"><?php echo $lng['edit']; ?></a>
                                                <b><?php echo $lng['folders']; ?></b>
                                            <p>
                                            <?php
                                            include E2G_MODULE_PATH . 'includes/tpl/pages/user.web.perm_editfile.inc.php';
                                        } else {
                                            ?>
                                        <p>
                                            <a href="<?php echo $index; ?>&amp;page=edit_webPerm&amp;group_id=<?php echo $webGroup['id']; ?>"><?php echo $lng['edit']; ?></a>
                                            <b><?php echo $lng['folders']; ?></b>
                                        <p>
                                            <?php
                                            /**
                                             * Display the directories/folders access
                                             */
                                            $dirWebAccessArray = $modx->db->makeArray($modx->db->query(
                                                                    'SELECT d.cat_name FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs d '
                                                                    . 'LEFT JOIN ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access a '
                                                                    . 'ON a.id=d.cat_id '
                                                                    . 'WHERE a.type=\'dir\' '
                                                                    . 'AND a.webgroup_id =\'' . $webGroup['id'] . '\' '
                                                                    . 'ORDER BY d.cat_name ASC'
                                                    ));
                                            if (!empty($dirWebAccessArray)) {
                                                $inheritParentPermission = FALSE;
                                                if (in_array('0', $dirWebAccessArray, true))
                                                    $inheritParentPermission = TRUE;

                                                if ($inheritParentPermission === TRUE) {
                                                    echo $lng['permission_inherit_parent'];
                                                }

                                                $countdirWebAccessArray = count($dirWebAccessArray);
                                                $i = 0;
                                                if ($inheritParentPermission === TRUE) {
                                                    echo ', ';
                                                }
                                                foreach ($dirWebAccessArray as $webDir) {
                                                    $i++;
                                                    echo $webDir['cat_name'];
                                                    if ($i < $countdirWebAccessArray)
                                                        echo ', ';
                                                }
                                            }
                                            ?>
                                        <p>
                                            <a href="<?php echo $index; ?>&amp;page=edit_webFilesPerm&amp;group_id=<?php echo $webGroup['id']; ?>"><?php echo $lng['edit']; ?></a>
                                            <b><?php echo $lng['files']; ?></b>
                                        </p>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </li>
                            <?php } ?>
                                </ul>
                        <?php } ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
    <?php
                                /**
                                 * TODO making roles for module's accessibility
                                 */
    ?>
                                <!--div class="tab-page" id="tabRoleSettings">
                                    <h2 class="tab"><?php echo $lng['user_roles']; ?></h2>
                                    <script type="text/javascript">
                                        tpAccPerm.addTabPage( document.getElementById( 'tabRoleSettings') );
                                    </script>

                                    <table style="width: 100%;">
                                        <tr>
                                            <td class="tdLeft">
                                                <div class="curveBox">
                                                    <div class="h2title"><?php echo $lng['users_mgr_roles']; ?></div>
    <?php
                                $mgrRoles = $modx->db->makeArray($modx->db->query(
                                                        'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'user_roles ORDER BY id ASC'));
                                if (count($mgrRoles) > 0) {
    ?>
                                                                    <ul><?php
                                    foreach ($mgrRoles as $mgrRole)
                                        echo '<li>' . $mgrRole['name'] . ' - <em>' . $mgrRole['description'] . '</em></li>';
    ?>
                                                                </ul><?php } ?>
                                            </div>
                                        </td>
                                        <td class="tdRight">
                                            <div class="curveBox">
                                                <div class="h2title"><?php echo $lng['user_e2g_mgr_roles']; ?></div>
                                                <div><?php echo htmlspecialchars_decode($lng['user_e2g_mgr_roles_desc']); ?></div>
<ul>
<li>Create</li>
<li>Edit</li>
<li>Save</li>
<li>Delete</li>
</ul>
</div>
</td>
</tr>
</table>
</div-->

</div>