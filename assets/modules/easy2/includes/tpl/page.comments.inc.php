<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<p><?php echo $lng['comments']; ?> <?php echo $lng['files']; ?>: <?php echo $gdir; ?> <b><a href="javascript:imPreview('<?php echo $gdir.$row['filename']; ?>');void(0);"><?php echo $row['filename']; ?></a></b> (<?php echo $row['comments']; ?>)
    &nbsp; &nbsp; &nbsp;
    <img src="<?php echo  E2G_MODULE_URL ; ?>includes/icons/arrow_refresh.png" width="16" height="16" border="0" align="absmiddle" alt="" />
    <a href="<?php echo $index; ?>&page=comments&file_id=<?php echo $_GET['file_id']; ?>&pid=<?php echo $parent_id; ?>"><?php echo $lng['update']; ?></a>
    &nbsp; &nbsp; &nbsp;
    <a href="<?php echo $index; ?>&pid=<?php echo $parent_id; ?>"><?php echo $lng['back']; ?></a>
</p>
<table cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td valign="top">
            <form name="list" action="<?php echo $index; ?>&act=delete_comments&file_id=<?php echo $_GET['file_id']; ?>" method="post">
                <table width="100%" cellpadding="5" cellspacing="1" class="grid" style="margin-bottom:10px">
                    <tr>
                        <td width="20"><input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;"></td>
                        <td align="center"><b><?php echo $lng['date']; ?></b></td>
                        <td align="center"><b><?php echo $lng['author']; ?></b></td>
                        <td align="center"><b><?php echo $lng['email']; ?></b></td>
                        <td align="center"><b><?php echo $lng['ip_address']; ?></b></td>
                        <td align="center"><b><?php echo $lng['comments']; ?></b></td>
                    </tr>
                <?php
                $res = mysql_query(
                        'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_comments '
                        .'WHERE file_id='.(int)$_GET['file_id'].' ORDER BY id DESC'
                );
                while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                    ?>
                    <tr<?php echo $cl[$i%2]; ?>>
                        <td nowrap="nowrap"><input name="comment[]" value="<?php echo $l['id']; ?>" type="checkbox" style="border:0;padding:0"></td>
                        <td nowrap="nowrap"><?php echo $l['date_added']; ?></td>
                        <td nowrap="nowrap"><?php echo $l['author']; ?></td>
                        <td nowrap="nowrap"><a href="mailto:<?php echo $l['email']; ?>"><?php echo $l['email']; ?></a></td>
                        <td nowrap="nowrap"><?php echo $l['ip_address']; ?>
                            <a href="<?php echo $index; ?>&act=ignore_ip&file_id=<?php echo $l['file_id']; ?>&comment_id=<?php echo $l['id']; ?>"
                               onclick="return ignoreIPAddress();">
                                <img src="<?php echo  E2G_MODULE_URL ; ?>includes/icons/delete.png" border="0"
                                     alt="<?php echo $lng['ignore']; ?>" title="<?php echo $lng['ignore']; ?>" />
                            </a>
                        </td>
                        <td valign="top" style="width:100%;"><?php echo htmlspecialchars($l['comment']); ?></td>
                    </tr>
                 <?php
                    $i++;
                }
                ?>
                </table>
                <input type="submit" value="<?php echo $lng['delete']; ?>" name="delete" style="font-weight:bold;color:red" />
            </form>
        </td>
        <th width="205" valign="top">
            <table cellspacing="0" cellpadding="0" style="margin-left:5px; border: 1px solid #ccc;width:200px; height:200px; ">
                <tr><th class="imPreview" id="pElt"></th></tr>
            </table>
        </th>
    </tr>
</table>