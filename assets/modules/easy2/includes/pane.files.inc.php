<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="tab-page" id="imManager">
    <h2 class="tab"><?php echo $lng['manager']; ?></h2>
    <script type="text/javascript">
        tpResources.addTabPage(document.getElementById('imManager'));
    </script>
    <br />
    <?php echo $content; ?>
</div>