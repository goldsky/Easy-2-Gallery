<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<p><?php echo $lng['editing']; ?> <?php echo $lng['dir']; ?> <b><?php echo $row['cat_name']; ?></b>
    &nbsp; &nbsp; &nbsp;
    <a href="<?php echo $index; ?>&pid=<?php echo $parent_id; ?>"><?php echo ucfirst($lng['back']); ?></a>
</p>
<form name="list" action="" method="post">
    <table cellspacing="0" cellpadding="2" class="aForm" >
        <?php
        // DO NOT CHANGE THE ROOT FOLDER'S NAME FROM HERE, USE CONFIG INSTEAD.
        if($row['cat_id']!='1') {
            ?>
        <tr>
            <td><b><?php echo $lng['enter_new_dirname']; ?> :</b></td>
            <td><input name="newdirname" type="text" value="<?php echo $row['cat_name']; ?>" size="30"></td>
        </tr>
            <?php
        }
        ?>
        <tr>
            <td><b><?php echo $lng['enter_new_alias']; ?> :</b></td>
            <td><input name="alias" type="text" value="<?php echo $row['cat_alias']; ?>" size="30"></td>
        </tr>
        <tr>
            <td><b><?php echo ucfirst($lng['tag']); ?> :</b></td>
            <td><input name="tag" type="text" value="<?php echo $row['cat_tag']; ?>" size="95"></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['description']; ?> :</b></td>
            <td><textarea name="description" style="width:500px" cols="" rows=""><?php echo $row['cat_description']; ?></textarea></td>
        </tr>
        <tr><td></td>
            <td><input type="submit" value="<?php echo $lng['save']; ?>">
                <input type="button" value="<?php echo $lng['cancel']; ?>" onclick="javascript:document.location.href='<?php echo $index; ?>&pid=<?php echo $parent_id; ?>'">
            </td>
        </tr>
    </table>
</form>