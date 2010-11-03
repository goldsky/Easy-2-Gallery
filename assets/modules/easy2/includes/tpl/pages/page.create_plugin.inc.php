<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<p><?php echo $lng['dir_create']; ?></p>

<ul class="actionButtons">
    <li>
        <a href="<?php echo $index; ?>&amp;pid=<?php echo $parentId; ?>">
            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_left.png" alt="" /> <?php echo $lng['back']; ?>
        </a>
    </li>
</ul>