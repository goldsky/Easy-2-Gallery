<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<p><?php echo $lng['dir_create'];?>&nbsp; &nbsp; &nbsp;
    <a href="<?php echo $index;?>&amp;pid=<?php echo $parentId;?>"><?php echo $lng['back'];?></a>
</p>