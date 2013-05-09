<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$res = mysql_query('SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $this->sanitizedGets['file_id']);
$row = mysql_fetch_assoc($res);
mysql_free_result($res);
?>
<ul class="actionButtons">
    <li>
        <a href="<?php
echo $this->e2gModCfg['index'];
if (isset($this->sanitizedGets['tag'])) {
?>&amp;tag=<?php
           echo $this->sanitizedGets['tag'];
       } else {
?>&amp;pid=<?php
           echo $this->e2gModCfg['parent_id'];
       }
?>">
            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_left.png" alt="" /> <?php echo $this->lng['back']; ?>
        </a>
    </li>
    <li>
        <a href="<?php echo $this->e2gModCfg['index']; ?>&amp;page=comments&amp;file_id=<?php
           echo $this->sanitizedGets['file_id'];
           if (isset($this->sanitizedGets['tag'])) {
?>&amp;tag=<?php
               echo $this->sanitizedGets['tag'];
           } else {
?>&amp;pid=<?php
               echo $this->e2gModCfg['parent_id'];
           }
?>">
            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_refresh.png" width="16" height="16" border="0" alt="" /> <?php echo $this->lng['update']; ?>
        </a>
    </li>
    <li>
        <span class="h2title"><?php echo $this->lng['comments']; ?></span>
        <?php echo $this->lng['files']; ?>: <?php echo $this->e2gModCfg['gdir']; ?><?php echo $row['filename']; ?>
       </li>
   </ul>
   <form name="fileComments" action="" method="post">

       <table width="100%">
           <tr>
               <td width="50%" style="vertical-align: top;">
                   <div>&nbsp;</div>
               </td>
               <td width="50%" style="vertical-align: top;">
                   <div style="padding:2px;background-color: #eee;font-weight: bold;">
                       <a href="javascript:;" onclick="imPreview5('<?php echo $this->e2gModCfg['gdir'] . $row['filename']; ?>');void(0);"><?php echo $this->lng['uim_preview']; ?>
                       </a>
                   </div>
                   <div class="imPreview2" id="pElt5"></div>
               </td>
           </tr>
       </table>

       <table width="100%" cellpadding="5" cellspacing="1" class="grid" style="margin-bottom:10px">
           <tr>
               <td colspan="3">
                   <input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;">
                <?php echo $this->lng['select_all']; ?>
            </td>
        </tr>
        <?php
                $res = mysql_query(
                                'SELECT c.*, i.ign_ip_address'
                                . ' FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments c'
                                . ' LEFT JOIN ' . $this->modx->db->config['table_prefix'] . 'easy2_ignoredip AS i'
                                . ' ON i.ign_ip_address=c.ip_address'
                                . ' WHERE file_id=' . (int) $this->sanitizedGets['file_id']
                                . ' ORDER BY id DESC'
                );
                $rowNum = 0; // only for row coloring
                while ($l = mysql_fetch_assoc($res)) {
        ?>
                    <tr <?php echo $rowClass[$rowNum % 2]; ?> >
                        <td valign="top" width="20">
                            <input name="comments[]" value="<?php echo $l['id']; ?>" type="checkbox" style="border:0;padding:0">
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
                        echo $this->e2gModCfg['index']
                        . '&amp;page=comments'
                        . '&amp;act=unignore_ip'
                        . '&amp;ip=' . $l['ip_address']
                        ;
                    ?>"
                        onclick="return unignoreIPAddress();">
                         <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/flag_green.gif" border="0"
                              alt="<?php echo $this->lng['unignore']; ?>" title="<?php echo $this->lng['unignore']; ?>" />
                     </a>
                    <?php
                    } else {
                    ?>
                        <a href="
                    <?php
                        echo $this->e2gModCfg['index']
                        . '&amp;act=ignore_ip'
                        . '&amp;file_id=' . $l['file_id']
                        . '&amp;ip=' . $l['ip_address']
                        . '&amp;u=' . $l['author']
                        . '&amp;e=' . $l['email']
                        ;
                    ?>"
                        onclick="return ignoreIPAddress();">
                         <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/flag_red.gif" border="0"
                              alt="<?php echo $this->lng['ignore']; ?>" title="<?php echo $this->lng['ignore']; ?>" />
                     </a>
                    <?php } ?> )
                </div>
                <hr />
                <div class="com_box">
                    <em style="font-size:smaller;">
                        <?php
                        echo $l['date_added'];
                        echo ' | ' . ($l['approved'] == 1 ? '<span style="color:green;">Approved</span>' : '<span style="color:red;">Unapproved</span>');
                        echo ' | ' . ($l['status'] == 1 ? '<span style="color:green;">Visible</span>' : '<span style="color:red;">Hidden</span>');
                        if ($l['date_edited'] != null || $l['date_edited'] != '') {
                            echo ' | edited: ' . $l['date_edited'];
                            $editor = $this->modx->getLoginUserName($l['edited_by']);
                            echo ', by: ' . $editor;
                        }
                        ?>
                    </em>
                    <p>
                        <?php
                        if (isset($this->sanitizedGets['act']) && $this->sanitizedGets['act'] == 'com_edit' && $this->sanitizedGets['comid'] == $l['id']) {
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
                        if (isset($this->sanitizedGets['act']) && $this->sanitizedGets['act'] == 'com_edit' && $this->sanitizedGets['comid'] == $l['id']) {
                        ?>
                            <a href="javascript:void(0)" onclick="savecomment(3)">Save</a> |
                            <a href="javascript:history.go(-1);">Cancel</a>
                        <?php
                        }
                        if (!isset($this->sanitizedGets['act'])) {
                            if ($l['approved'] != '1') {
                        ?>
                                <a href="<?php echo $this->e2gModCfg['index']; ?>&amp;act=com_approve&amp;comid=<?php echo $l['id']; ?>"><?php echo $this->lng['approve']; ?></a> |
                        <?php
                            }
                            if ($l['status'] == '1') {
                        ?>
                                <a href="<?php echo $this->e2gModCfg['index']; ?>&amp;act=com_hide&amp;comid=<?php echo $l['id']; ?>"><?php echo $this->lng['hide']; ?></a> |
                        <?php
                            } else {
                        ?>
                                <a href="<?php echo $this->e2gModCfg['index']; ?>&amp;act=com_unhide&amp;comid=<?php echo $l['id']; ?>"><?php echo $this->lng['hide_not']; ?></a> |
                        <?php } ?>
                            <a href="<?php echo $this->e2gModCfg['index']; ?>&amp;page=comments&amp;file_id=11&amp;pid=3&amp;act=com_edit&amp;comid=<?php echo $l['id']; ?>"><?php echo $this->lng['edit']; ?></a> |
                            <a href="<?php
                            echo $this->e2gModCfg['index']
                            . '&amp;act=ignore_ip'
                            . '&amp;file_id=' . $l['file_id']
                            . '&amp;ip=' . $l['ip_address']
                            . '&amp;u=' . $l['author']
                            . '&amp;e=' . $l['email']
                            ;
                        ?>"
                            onclick="return ignoreIPAddress();"><?php echo $this->lng['spam']; ?></a> |
                         <a href="<?php echo $this->e2gModCfg['index']; ?>&amp;act=com_delete&amp;comid=<?php echo $l['id']; ?>"><?php echo $this->lng['delete']; ?></a>
                     </div>
                    <?php } ?>
                    </div>
                </td>
            </tr><?php $rowNum++;
                    } ?>
                </table>
                <ul class="actionButtons">
                    <li><b><?php echo $this->lng['withselected']; ?>: </b>
                        <select name="listCommentActions">
                            <option value="">&nbsp;</option>
                            <option value="approve"><?php echo $this->lng['approve']; ?></option>
                            <option value="unapprove"><?php echo $this->lng['approve_not']; ?></option>
                            <option value="unhide"><?php echo $this->lng['hide_not']; ?></option>
                            <option value="hide"><?php echo $this->lng['hide']; ?></option>
                            <option value="delete"><?php echo $this->lng['delete']; ?></option>
                        </select>
                        <a href="javascript:void(0)" onclick="submitcomment(3)">
                            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/action_go.gif" alt="" />
                            <span><?php echo $this->lng['go']; ?></span>
            </a>
        </li>
    </ul>
</form>