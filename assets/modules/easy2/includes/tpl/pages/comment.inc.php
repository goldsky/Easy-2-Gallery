<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
$filesLink = $e2gPages['files']['link'];
?>
<div class="tab-pane" id="tabCommentsMgrPane">
    <script type="text/javascript">
        tpCommentsMgr = new WebFXTabPane(document.getElementById('tabCommentsMgrPane'));
    </script>
    <div class="tab-page" id="tabAllComments">
        <h2 class="tab"><?php echo $lng['comments_all_title']; ?></h2>
        <script type="text/javascript">
            tpCommentsMgr.addTabPage( document.getElementById( 'tabAllComments') );
        </script>
        <?php
        if (isset($_GET['page'])) {
            if ($_GET['page'] == 'edit_file')
                include_once E2G_MODULE_PATH . 'includes/tpl/pages/page.file.edit_file.inc.php';
            elseif ($_GET['page'] == 'comments')
                include_once E2G_MODULE_PATH . 'includes/tpl/pages/page.file.comments.inc.php';
        }
        // default page
        else {
        ?>
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td valign="top">
                        <form name="listComments" action="" method="post">
                            <table width="100%" cellpadding="5" cellspacing="1" class="grid" style="margin-bottom:10px">
                                <tr>
                                    <td colspan="3">
                                        <!-- Table headers -->
                                        <input type="checkbox" onclick="selectAllComments(this.checked); void(0);" style="border:0;" />
                                    <?php echo $lng['select_all']; ?>&nbsp;&nbsp; | &nbsp;
                                    <?php
                                    echo (!$_GET['filter'] ? '<b>' : '<a href="' . $index . '" style="text-decoration: underline;">');
                                    echo $lng['all'];
                                    echo (!$_GET['filter'] ? '</b>' : '</a>');
                                    ?>
                                    &nbsp;&nbsp;
                                    <?php
                                    echo ($_GET['filter'] == 'comments_approved' ? '<b>' : '<a href="' . $index . '&amp;filter=comments_approved" style="text-decoration: underline;">');
                                    echo $lng['approved'];
                                    echo ($_GET['filter'] == 'comments_approved' ? '</b>' : '</a>');
                                    ?>
                                    &nbsp;&nbsp;
                                    <?php
                                    echo ($_GET['filter'] == 'comments_unapproved' ? '<b>' : '<a href="' . $index . '&amp;filter=comments_unapproved" style="text-decoration: underline;">');
                                    echo $lng['approved_not'];
                                    echo ($_GET['filter'] == 'comments_unapproved' ? '</b>' : '</a>');
                                    ?>
                                    &nbsp;&nbsp;
                                    <?php
                                    echo ($_GET['filter'] == 'comments_visible' ? '<b>' : '<a href="' . $index . '&amp;filter=comments_visible" style="text-decoration: underline;">');
                                    echo $lng['visible'];
                                    echo ($_GET['filter'] == 'comments_visible' ? '</b>' : '</a>');
                                    ?>
                                    &nbsp;&nbsp;
                                    <?php
                                    echo ($_GET['filter'] == 'comments_hidden' ? '<b>' : '<a href="' . $index . '&amp;filter=comments_hidden" style="text-decoration: underline;">');
                                    echo $lng['hidden'];
                                    echo ($_GET['filter'] == 'comments_hidden' ? '</b>' : '</a>');
                                    ?>
                                </td>
                            </tr>
                            <?php
                                    /**
                                     * The comment rows
                                     */
                                    $selectComments = 'SELECT c.*, f.filename, f.dir_id, i.ign_ip_address '
                                            . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments AS c '
                                            . 'LEFT JOIN ' . $modx->db->config['table_prefix'] . 'easy2_files AS f '
                                            . 'ON c.file_id=f.id '
                                            . 'LEFT JOIN ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip AS i '
                                            . 'ON i.ign_ip_address=c.ip_address ';
                                    if (isset($_GET['filter'])) {
                                        $selectComments .= 'WHERE ';
                                        if ($_GET['filter'] == 'comments_approved')
                                            $selectComments .= 'c.approved=1 ';
                                        if ($_GET['filter'] == 'comments_unapproved')
                                            $selectComments .= 'c.approved=0 ';
                                        if ($_GET['filter'] == 'comments_visible')
                                            $selectComments .= 'c.status=1 ';
                                        if ($_GET['filter'] == 'comments_hidden')
                                            $selectComments .= 'c.status=0 ';
                                    }
                                    $selectComments .= 'ORDER BY id DESC';
                                    $queryComments = mysql_query($selectComments);
                                    if (!$queryComments)
                                        die(mysql_error());

                                    // for table row class looping
                                    $rowClass = array(' class="gridAltItem"', ' class="gridItem"');
                                    $rowNum = 0; // only for row coloring
                                    while ($l = mysql_fetch_array($queryComments, MYSQL_ASSOC)) {
                                        $filePath = str_replace('%2F', '/', rawurlencode($gdir . $path['string'] . $l['filename']));
                            ?>
                                        <tr <?php echo $rowClass[$rowNum % 2]; ?> >
                                            <td valign="top" width="20">
                                                <input name="comments[]" value="<?php echo $l['id']; ?>" type="checkbox" style="border:0;padding:0" />
                                            </td>
                                            <td valign="top" width="205">
                                    <?php if (!empty($cp)) {
                                    ?>
                                            <a href="<?php echo $filesLink; ?>&amp;pid=<?php echo $l['dir_id']; ?>&amp;page=openexplorer"><?php echo $path['string']; ?></a><br />
                                    <?php } ?>
                                        <a href="<?php echo $index . $filtered; ?>&amp;page=comments&amp;file_id=<?php echo $l['file_id']; ?>&amp;pid=<?php echo $l['dir_id']; ?>">
                                            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/comments.png" width="16" height="16"
                                                 alt="<?php echo $lng['comments']; ?>" title="<?php echo $lng['comments']; ?>" border="0" />
                                        </a>
                                        <a href="<?php echo $filesLink; ?>&amp;page=edit_file&amp;file_id=<?php echo $l['file_id']; ?>&amp;pid=<?php echo $l['dir_id']; ?>">
                                            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/picture_edit.png" width="16" height="16"
                                                 alt="<?php echo $lng['edit']; ?>" title="<?php echo $lng['edit']; ?>" border="0" />
                                        </a>
                                        <b>
                                            <a href="javascript:void(0)" onclick="imPreview2('<?php echo $filePath; ?>', <?php echo $rowNum; ?>);">
                                            <?php echo $l['filename']; ?></a>
                                    </b>
                                    [id:<?php echo $l['file_id']; ?>]
                                    <div class="imPreview" id="rowPreview2_<?php echo $rowNum; ?>" style="display:none;"></div>
                                </td>
                                <td valign="top" class="com_row">
                                    <div>
                                        <b><?php echo $l['author']; ?></b> (
                                        <a href="mailto:<?php echo $l['email']; ?>"><?php echo $l['email']; ?></a> ,
                                        <?php
                                            echo $l['ip_address'];
                                            if ($l['ign_ip_address']) {
                                        ?>
                                                <a href="<?php
                                                echo $index
                                                . $filtered
                                                . '&amp;page=comments'
                                                . '&amp;act=unignore_ip'
                                                . '&amp;ip=' . $l['ip_address']
                                                ;
                                        ?>"
                                                onclick="return unignoreIPAddress();">
                                                 <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/flag_green.gif" border="0"
                                                      alt="<?php echo $lng['unignore']; ?>" title="<?php echo $lng['unignore']; ?>" />
                                             </a>
                                        <?php } else {
                                        ?>
                                                <a href="<?php
                                                echo $index
                                                . $filtered
                                                . '&amp;act=ignore_ip'
                                                . '&amp;file_id=' . $l['file_id']
                                                . '&amp;ip=' . $l['ip_address']
                                                . '&amp;u=' . $l['author']
                                                . '&amp;e=' . $l['email']
                                                ;
                                        ?>"
                                                onclick="return ignoreIPAddress();">
                                                 <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/flag_red.gif" border="0"
                                                      alt="<?php echo $lng['ignore']; ?>" title="<?php echo $lng['ignore']; ?>" />
                                             </a>
                                        <?php } ?> )
                                        </div>
                                        <hr />
                                        <div class="com_box">
                                            <em style="font-size:smaller;">
                                            <?php
                                            /**
                                             * The comment's information
                                             */
                                            echo $l['date_added'];
                                            echo ' | ' . ($l['approved'] == 1 ? '<span style="color:green;">' . $lng['approved'] . '</span>' : '<span style="color:red;">' . $lng['approved_not'] . '</span>');
                                            echo ' | ' . ($l['status'] == 1 ? '<span style="color:green;">' . $lng['visible'] . '</span>' : '<span style="color:red;">' . $lng['hidden'] . '</span>');
                                            if ($l['date_edited'] != null || $l['date_edited'] != '') {
                                                echo ' | edited: ' . $l['date_edited'];
                                                $editor = $modx->getLoginUserName($l['edited_by']);
                                                echo ', by: ' . $editor;
                                            }
                                            ?>

                                        </em>
                                        <p>
                                            <?php if (isset($_GET['act']) && $_GET['act'] == 'com_edit' && $_GET['comid'] == $l['id']) {
                                            ?>
                                                <textarea name="comment" cols="" rows="3" style="width:90%;"><?php echo htmlspecialchars_decode($l['comment'], ENT_QUOTES); ?></textarea>
                                                <input type="hidden" name="comid" value="<?php echo $l['id']; ?>" />
                                            <?php
                                            }
                                            else
                                                echo htmlspecialchars_decode($l['comment'], ENT_QUOTES);
                                            ?>
                                        </p>
                                        <div class="com_action">
                                            <?php
                                            /**
                                             * The comment's save/cancel actions on editing
                                             */
                                            if (isset($_GET['act']) && $_GET['act'] == 'com_edit' && $_GET['comid'] == $l['id']) {
                                            ?>
                                                <a href="javascript:void(0)" onclick="savecomment(1)"><?php echo $lng['save']; ?></a> |
                                                <a href="javascript:history.go(-1)"><?php echo $lng['cancel']; ?></a>
                                            <?php
                                            }
                                            if (!isset($_GET['act'])) {
                                                if ($l['approved'] != '1') {
                                            ?>
                                                    <a href="<?php echo $index . $filtered; ?>&amp;act=com_approve&amp;comid=<?php echo $l['id']; ?>"><?php echo $lng['approve']; ?></a> |
                                            <?php
                                                }
                                                if ($l['status'] == '1') {
                                            ?>
                                                    <a href="<?php echo $index . $filtered; ?>&amp;act=com_hide&amp;comid=<?php echo $l['id']; ?>"><?php echo $lng['hide']; ?></a> |
                                            <?php } else {
                                            ?>
                                                    <a href="<?php echo $index . $filtered; ?>&amp;act=com_unhide&amp;comid=<?php echo $l['id']; ?>"><?php echo $lng['hide_not']; ?></a> |
                                            <?php } ?>
                                                <a href="<?php echo $index . $filtered; ?>&amp;act=com_edit&amp;comid=<?php echo $l['id']; ?>"><?php echo $lng['edit']; ?></a> |
                                                <a href="<?php
                                                echo $index
                                                . $filtered
                                                . '&amp;act=ignore_ip'
                                                . '&amp;file_id=' . $l['file_id']
                                                . '&amp;ip=' . $l['ip_address']
                                                . '&amp;u=' . $l['author']
                                                . '&amp;e=' . $l['email']
                                                ;
                                            ?>"
                                                onclick="return ignoreIPAddress();"><?php echo $lng['spam']; ?></a> |
                                             <a href="<?php echo $index . $filtered; ?>&amp;act=com_delete&amp;comid=<?php echo $l['id']; ?>"><?php echo $lng['delete']; ?></a>
                                         </div>
                                        <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                                            if (isset($path['string'])) {
                                                unset($path['string']);
                                            }
                                            $rowNum++;
                                        }
                            ?>
                                    </table>
                                    <ul class="actionButtons">
                                        <li><b><?php echo $lng['withselected']; ?>: </b>
                                            <select name="listCommentActions">
                                                <option value="">&nbsp;</option>
                                                <option value="approve"><?php echo $lng['approve']; ?></option>
                                                <option value="unapprove"><?php echo $lng['approve_not']; ?></option>
                                                <option value="unhide"><?php echo $lng['hide_not']; ?></option>
                                                <option value="hide"><?php echo $lng['hide']; ?></option>
                                                <option value="delete"><?php echo $lng['delete']; ?></option>
                                            </select>
                                            <a href="javascript:void(0)" onclick="submitcomment(1)">
                                                <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/action_go.gif" alt="" />
                                                <span><?php echo $lng['go']; ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </form>
                            </td>
                        </tr>
                    </table>
        <?php } ?>
                                </div>
                                <div class="tab-page" id="tabIgnoredIP">
                                    <h2 class="tab"><?php echo $lng['ip_ignored_title']; ?></h2>
                                    <script type="text/javascript">
                                        tpCommentsMgr.addTabPage( document.getElementById( 'tabIgnoredIP') );
                                    </script>
                                    <form name="listIgnoreIPs" action="<?php echo $index . $filtered; ?>&amp;act=unignored_all_ips" method="post">
                                        <table width="100%" cellpadding="5" cellspacing="1" class="grid" style="margin-bottom:10px">
                                            <tr>
                                                <th width="20"><input type="checkbox" onclick="selectAllIgnoreIPs(this.checked); void(0);" style="border:0;" /></th>
                                                <th><?php echo $lng['actions']; ?></th>
                                                <th><?php echo $lng['date']; ?></th>
                                                <th><?php echo $lng['ip_address']; ?></th>
                                                <th><?php echo $lng['author']; ?></th>
                                                <th><?php echo $lng['email']; ?></th>
                                            </tr>
                <?php
                                    $ign_ip_res = mysql_query(
                                                    'SELECT DISTINCT ign_ip_address, ign_date, ign_username, ign_email '
                                                    . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                                                    . 'ORDER BY id DESC');
                                    $rowNum = 0; // only for row coloring
                                    while ($ign = mysql_fetch_array($ign_ip_res, MYSQL_ASSOC)) {
                ?>
                                        <tr <?php echo $rowClass[$rowNum % 2]; ?> >
                                            <td valign="top" width="20">
                                                <input name="unignored_ip[]" value="<?php echo $ign['ign_ip_address']; ?>" type="checkbox" style="border:0;padding:0" /></td>
                                            <td width="20">
                                                <a href="<?php
                                        echo $index
                                        . $filtered
                                        . '&amp;page=comments'
                                        . '&amp;act=unignore_ip'
                                        . '&amp;ip=' . $ign['ign_ip_address']
                                        ;
                ?>"
                                        onclick="return unignoreIPAddress();">
                                         <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/flag_green.gif" border="0"
                                              alt="<?php echo $lng['unignore']; ?>" title="<?php echo $lng['unignore']; ?>" />
                                     </a>
                                 </td>
                                 <td><?php echo $ign['ign_date']; ?></td>
                                 <td><?php echo $ign['ign_ip_address']; ?></td>
                                 <td><?php echo $ign['ign_username']; ?></td>
                                 <td><?php echo $ign['ign_email']; ?></td>
                             </tr>
                <?php
                                        $rowNum++;
                                    }
                ?>
                                </table>
                                <input type="submit" value="<?php echo $lng['unignore']; ?>" name="unignore" style="font-weight:bold;color:red" />
        </form>
    </div>
</div>