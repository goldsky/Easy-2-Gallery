<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="pane">
    <?php
    if (isset($_GET['page'])) {
        if ($_GET['page'] == 'create_dir')
            include_once E2G_MODULE_PATH . 'includes/tpl/pages/page.create_dir.inc.php';
        elseif ($_GET['page'] == 'edit_dir')
            include_once E2G_MODULE_PATH . 'includes/tpl/pages/page.edit_dir.inc.php';
        elseif ($_GET['page'] == 'edit_file')
            include_once E2G_MODULE_PATH . 'includes/tpl/pages/page.edit_file.inc.php';
        elseif ($_GET['page'] == 'comments')
            include_once E2G_MODULE_PATH . 'includes/tpl/pages/page.comments.inc.php';
        elseif ($_GET['page'] == 'tag')
            include_once E2G_MODULE_PATH . 'includes/tpl/pages/page.tag.inc.php';
    }
    // default page
    else
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/page.default.inc.php';
    ?>
</div>