<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<p><?php echo $lng['dir_create'];?>&nbsp; &nbsp; &nbsp;
    <a href="<?php echo $index;?>&pid=<?php echo $parent_id;?>"><?php echo $lng['Back'];?></a>
</p>
<form name="list" action="" method="post">
    <table cellspacing="0" cellpadding="2" class="aForm" >
        <tr>
            <td><b><?php echo $lng['dir_create'];?> :</b></td>
            <td><input name="name" type="text" size="30" /></td>
        </tr>
        <tr>
            <td><b><?php echo $lng['enter_new_alias'];?> :</b></td>
            <td><input name="alias" type="text" size="30" /></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['description'];?> :</b></td>
            <td><textarea name="description" style="width:500px" cols="" rows=""></textarea></td>
        </tr>
        <tr><td></td>
            <td><input type="submit" value="<?php echo $lng['save'];?>" />
                <input type="button" value="<?php echo $lng['cancel'];?>" onclick="javascript:document.location.href='<?php echo $index;?>&pid=<?php echo $parent_id;?>'" />
            </td>
        </tr>
    </table>
</form>