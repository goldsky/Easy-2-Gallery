<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="tab-page" id="imManager">
    <h2 class="tab"><?php echo ucfirst($lng['files']); ?></h2>
    <script type="text/javascript">
        tpResources.addTabPage(document.getElementById('imManager'));
    </script>
    <?php
    if (isset($_GET['page'])) {
        if ($_GET['page']=='create_dir') include_once E2G_MODULE_PATH . 'includes/tpl/page.create_dir.inc.php';
        elseif ($_GET['page']=='edit_dir') include_once E2G_MODULE_PATH . 'includes/tpl/page.edit_dir.inc.php';
        elseif ($_GET['page']=='edit_file') include_once E2G_MODULE_PATH . 'includes/tpl/page.edit_file.inc.php';
        elseif ($_GET['page']=='comments') include_once E2G_MODULE_PATH . 'includes/tpl/page.comments.inc.php';
    }
    // default page
    else include_once E2G_MODULE_PATH . 'includes/tpl/page.default.inc.php';
    ?>
    <?php //echo $content; ?>
</div>