<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<p><?php echo $lng['editing']; ?> <?php echo $lng['files']; ?> <b><?php echo $row['filename']; ?> <a href="javascript:imPreview4('<?php echo $gdir.$row['filename']; ?>');void(0);"><?php echo $lng['uim_preview']; ?></a></b> (<?php echo $row['comments'].' '. strtolower($lng['comments']); ?>)
    &nbsp; &nbsp; &nbsp;
    <a href="<?php echo $index; ?>&pid=<?php echo $parent_id; ?>"><?php echo $lng['Back']; ?></a>
</p>

<table cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td valign="top">
            <form name="list" action="" method="post">
                <table cellspacing="0" cellpadding="2" class="aForm">
                    <tr>
                        <td nowrap="nowrap"><b><?php echo ucfirst($lng['file_rename']); ?> :</b></td>
                        <td><input name="newfilename" type="text" value="<?php echo $filename; ?>" size="30" style="text-align:right;"> <?php echo $ext; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng['name']; ?> :</b></td>
                        <td><input name="name" type="text" value="<?php echo $row['name']; ?>" size="30"></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng['tags']; ?> :</b></td>
                        <td><input name="tags" type="text" value="<?php echo $row['tags']; ?>" size="95"></td>
                    </tr>
                    <tr>
                        <td valign="top"><b><?php echo $lng['description']; ?> :</b></td>
                        <td>
                            <textarea name="description" style="width:500px" cols="" rows=""><?php echo $row['description']; ?></textarea>
                        </td>
                    </tr>
                    <tr><td></td>
                        <td><input type="submit" value="<?php echo $lng['save']; ?>">
                            <input type="button" value="<?php echo $lng['cancel']; ?>" onclick="javascript:document.location.href='<?php echo $index; ?>&pid=<?php echo $parent_id; ?>'">
                        </td>
                    </tr>
                </table>
            </form>
        </td>
        <th width="205" valign="top">
            <table cellspacing="0" cellpadding="0" style="margin-left:5px; border: 1px solid #ccc;width:200px; height:200px; ">
                <tr><th class="imPreview" id="pElt4"></th></tr>
            </table>
        </th>
    </tr>
</table>