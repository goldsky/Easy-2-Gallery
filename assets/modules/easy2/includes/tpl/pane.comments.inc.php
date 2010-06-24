<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="tab-page" id="commentsManager">
    <h2 class="tab"><?php echo $lng['commentsmgr']; ?></h2>
    <script type="text/javascript">
        tpResources.addTabPage(document.getElementById('commentsManager'));
    </script>
    <div class="tab-pane" id="tabCommentsMgrPane">
        <script type="text/javascript">
            tpResources2 = new WebFXTabPane(document.getElementById('tabCommentsMgrPane'));
        </script>
        <div class="tab-page" id="tabAllComments">
            <h2 class="tab"><?php echo $lng['comments_all_title']; ?></h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabAllComments') );
            </script>
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td valign="top">
                        <form name="listComments" action="<?php echo $index; ?>&act=delete_allcomments" method="post">
                            <table width="100%" cellpadding="5" cellspacing="1" class="grid" style="margin-bottom:10px">
                                <tr>
                                    <td colspan="3">
                                        <input type="checkbox" onclick="selectAllComments(this.checked); void(0);" style="border:0;">
                                        <?php echo $lng['select_all']; ?>
                                    </td>
                                </tr>
                                <?php
                                $res = mysql_query(
                                        'SELECT c.*, f.filename, f.dir_id '
                                        .'FROM '.$modx->db->config['table_prefix'].'easy2_comments AS c '
                                        .'LEFT JOIN '.$modx->db->config['table_prefix'].'easy2_files AS f '
                                        .'ON c.file_id=f.id '
                                        .'WHERE c.STATUS=1 '
                                        .'ORDER BY id DESC');
                                $i=0; // only for row coloring
                                while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                                    $cp = $this->_path_to($l['dir_id']);
                                    unset ($cp[1]);
                                    $cdir='';
                                    if (!empty($cp)) $cdir .= implode( '/', $cp ) . '/';
                                    ?>
                                <tr <?php echo $cl[$i%2]; ?> >
                                    <td valign="top" width="20">
                                        <input name="allcomment[]" value="<?php echo $l['id']; ?>" type="checkbox" style="border:0;padding:0">
                                    </td>
                                    <td valign="top" width="205">
                                        <?php if($cdir) { ?>
                                            <a href="<?php echo $index; ?>&pid=<?php echo $l['dir_id']; ?>&page=openexplorer" onclick="showTab('file')"><?php echo $cdir; ?></a><br />
                                        <?php } ?>
                                            
                                        <a href="<?php echo $index; ?>&page=comments&file_id=<?php echo $l['file_id']; ?>"
                                           onclick="showTab('file')">
                                            <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/comments.png" width="16" height="16"
                                                 alt="<?php echo $lng['comments']; ?>" title="<?php echo $lng['comments']; ?>" border=0>
                                        </a>
                                        <a href="<?php echo $index; ?>&page=edit_file&file_id=<?php echo $l['file_id']; ?>"
                                           onclick="showTab('file')">
                                            <img src="<?php echo E2G_MODULE_URL; ?>includes/icons/picture_edit.png" width="16" height="16"
                                                 alt="<?php echo $lng['edit']; ?>" title="<?php echo $lng['edit']; ?>" border=0>
                                        </a>
                                        <b>
                                                    <a href="javascript:void(0)" onclick="imPreview2('<?php echo '../'.$e2g['dir'].$cdir.$l['filename']; ?>', <?php echo $i; ?>);">
                                                    <?php echo $l['filename']; ?></a>
                                        </b>
                                            [id:<?php echo $l['file_id']; ?>]
                                        <div class="imPreview" id="rowPreview2_<?php echo $i; ?>" style="display:none;"></div>
                                    </td>
                                    <td valign="top">
                                        <div>
                                            <b><?php echo $l['author']; ?></b> (
                                        <a href="mailto:<?php echo $l['email']; ?>"><?php echo $l['email']; ?></a> ,
                                        <?php echo $l['ip_address'];
                                            if($l['ip_address']) {?>
                                        <a href="<?php echo $index
                                                        .'&act=ignore_ip'
                                                        .'&file_id='.$l['file_id']
                                                        .'&ip='.$l['ip_address']
                                                        .'&u='.$l['author']
                                                        .'&e='.$l['email']
                                                   ; ?>"
                                                   onclick="return ignoreIPAddress();">
                                            <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/delete.png" border="0"
                                                 alt="<?php echo $lng['ignore']; ?>" title="<?php echo $lng['ignore']; ?>" />
                                        </a>
                                                <?php } ?> )
                                        </div>
                                        <hr />
                                        <div><em style="font-size:smaller;"><?php echo $l['date_added']; ?></em><br />
                                            <?php echo htmlspecialchars_decode($l['comment'], ENT_QUOTES) ; ?>
                                        </div>
                                    </td>
                                </tr>
                                    <?php
                                    if (isset($cdir)) {
                                        unset($cdir);
                                    }
                                    $i++;
                                }
                                ?>
                            </table>
                            <input type="submit" value="<?php echo $lng['delete']; ?>" name="delete" style="font-weight:bold;color:red" />
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <div class="tab-page" id="tabIgnoredIP">
            <h2 class="tab"><?php echo $lng['ip_ignored_title']; ?></h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabIgnoredIP') );
            </script>
            <form name="listIgnoreIPs" action="<?php echo $index; ?>&act=unignored_all_ip" method="post">
                <table width="100%" cellpadding="5" cellspacing="1" class="grid" style="margin-bottom:10px">
                    <tr>
                        <th width="20"><input type="checkbox" onclick="selectAllIgnoreIPs(this.checked); void(0);" style="border:0;"></th>
                        <th><?php echo $lng['options']; ?></th>
                        <th><?php echo $lng['date']; ?></th>
                        <th><?php echo $lng['ip_address']; ?></th>
                        <th><?php echo $lng['author']; ?></th>
                        <th><?php echo $lng['email']; ?></th>
                    </tr>
                    <?php
                    $ign_ip_res = mysql_query(
                            'SELECT DISTINCT ign_ip_address, ign_date, ign_username, ign_email '
                            .'FROM '.$modx->db->config['table_prefix'].'easy2_ignoredip '
                            .'ORDER BY id DESC');
                    $i=0; // only for row coloring
                    while ($ign = mysql_fetch_array($ign_ip_res, MYSQL_ASSOC)) {
                        ?>
                    <tr <?php echo $cl[$i%2]; ?> >
                        <td valign="top" width="20">
                            <input name="unignored_ip[]" value="<?php echo $ign['ign_ip_address']; ?>" type="checkbox" style="border:0;padding:0"></td>
                        <td width="20">
                            <a href="<?php echo $index
                                        .'&page=comments'
                                        .'&act=unignore_ip'
                                        .'&ip='.$ign['ign_ip_address']
                                   ; ?>"
                               onclick="return unignoreIPAddress();">
                                <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/icon_accept.gif" border="0"
                                     alt="<?php echo $lng['unignore']; ?>" title="<?php echo $lng['unignore']; ?>" />
                            </a>
                        </td>
                        <td><?php echo $ign['ign_date']; ?></td>
                        <td><?php echo $ign['ign_ip_address']; ?></td>
                        <td><?php echo $ign['ign_username']; ?></td>
                        <td><?php echo $ign['ign_email']; ?></td>
                    </tr>
                        <?php
                        $i++;
                    }
                    ?>
                </table>
                <input type="submit" value="<?php echo $lng['unignore']; ?>" name="unignore" style="font-weight:bold;color:red" />
            </form>
        </div>
        <div class="tab-page" id="tabHiddenComments">
            <h2 class="tab"><?php echo $lng['comments_hidden_title']; ?></h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabHiddenComments') );
            </script>
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td valign="top">
                        <form name="listHiddenComments" action="<?php echo $index; ?>&act=delete_allcomments" method="post">
                            <table width="100%" cellpadding="5" cellspacing="1" class="grid" style="margin-bottom:10px">
                                <tr>
                                    <td colspan="3">
                                        <input type="checkbox" onclick="selectAllHiddenComments(this.checked); void(0);" style="border:0;">
                                        <?php echo $lng['select_all']; ?>
                                    </td>
                                </tr>
                                <?php
                                $res = mysql_query(
                                        'SELECT c.*, f.filename, f.dir_id '
                                        .'FROM '.$modx->db->config['table_prefix'].'easy2_comments AS c '
                                        .'LEFT JOIN '.$modx->db->config['table_prefix'].'easy2_files AS f '
                                        .'ON c.file_id=f.id '
                                        .'WHERE c.STATUS=0 '
                                        .'ORDER BY id DESC');
                                $i=0; // only for row coloring
                                while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                                    $cp = $this->_path_to($l['dir_id']);
                                    unset ($cp[1]);
                                    if (!empty($cp)) $cdir .= implode( '/', $cp ) . '/';
                                    ?>





                                <tr <?php echo $cl[$i%2]; ?> >
                                    <td valign="top" width="20">
                                        <input name="allcomment[]" value="<?php echo $l['id']; ?>" type="checkbox" style="border:0;padding:0">
                                    </td>
                                    <td valign="top" width="205">
                                        <?php if($cdir) { ?>
                                            <a href="<?php echo $index; ?>&pid=<?php echo $l['dir_id']; ?>&page=openexplorer" onclick="showTab('file')"><?php echo $cdir; ?></a><br />
                                        <?php } ?>

                                        <a href="<?php echo $index; ?>&page=comments&file_id=<?php echo $l['file_id']; ?>"
                                           onclick="showTab('file')">
                                            <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/comments.png" width="16" height="16"
                                                 alt="<?php echo $lng['comments']; ?>" title="<?php echo $lng['comments']; ?>" border=0>
                                        </a>
                                        <a href="<?php echo $index; ?>&page=edit_file&file_id=<?php echo $l['file_id']; ?>"
                                           onclick="showTab('file')">
                                            <img src="<?php echo E2G_MODULE_URL; ?>includes/icons/picture_edit.png" width="16" height="16"
                                                 alt="<?php echo $lng['edit']; ?>" title="<?php echo $lng['edit']; ?>" border=0>
                                        </a>
                                        <b>
                                                    <a href="javascript:void(0)" onclick="imPreview3('<?php echo '../'.$e2g['dir'].$cdir.$l['filename']; ?>', <?php echo $i; ?>);">
                                                    <?php echo $l['filename']; ?></a>
                                        </b>
                                            [id:<?php echo $l['file_id']; ?>]
                                        <div class="imPreview" id="rowPreview3_<?php echo $i; ?>" style="display:none;" ></div>
                                    </td>
                                    <td valign="top" style="width:100%;">
                                        <div>
                                            <b><?php echo $l['author']; ?></b> (
                                        <a href="mailto:<?php echo $l['email']; ?>"><?php echo $l['email']; ?></a> ,


                                        <?php echo $l['ip_address'];
                                            if($l['ip_address']) {?>
                                        <a href="<?php echo $index
                                                        .'&page=comments'
                                                        .'&act=unignore_ip'
                                                        .'&ip='.$l['ip_address']
                                                   ; ?>"
                                                   onclick="return unignoreIPAddress();">
                                            <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/icon_accept.gif" border="0"
                                                 alt="<?php echo $lng['unignore']; ?>" title="<?php echo $lng['unignore']; ?>" />
                                        </a>
                                                <?php } ?> )
                                        </div>
                                        <hr />
                                        <div><em style="font-size:smaller;"><?php echo $l['date_added']; ?></em><br />
                                            <?php echo htmlspecialchars_decode($l['comment'], ENT_QUOTES) ; ?>
                                        </div>
                                    </td>
                                </tr>
                                    <?php
                                    if (isset($cdir)) {
                                        unset($cdir);
                                    }
                                    $i++;
                                }
                                ?>
                            </table>
                            <input type="submit" value="<?php echo $lng['delete']; ?>" name="delete" style="font-weight:bold;color:red" />
                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>