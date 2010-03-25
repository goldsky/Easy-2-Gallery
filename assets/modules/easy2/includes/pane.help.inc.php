<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="tab-page" id="tabHelp">
    <span class="tab"><?php echo $lng['help']; ?></span>
    <script type="text/javascript">
        tpResources.addTabPage( document.getElementById( 'tabHelp' ) );
    </script>
    <div class="tab-pane" id="tabHelpPane">
        <script type="text/javascript">
            tpResources2 = new WebFXTabPane( document.getElementById( 'tabHelpPane' ) );
        </script>
        <div class="tab-page" id="tabHelpParameters">
            <h2 class="tab"><?php echo $lng['paramhelptitle']; ?></h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabHelpParameters' ) );
            </script>
            <?php echo $lng['paramhelpcontent']; ?>
        </div>
        <div class="tab-page" id="tabHelpTemplates">
            <h2 class="tab"><?php echo $lng['tplhelptitle']; ?></h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabHelpTemplates' ) );
            </script>
            <?php echo $lng['tplhelpcontent']; ?>
        </div>
        <div class="tab-page" id="tabHelpInfo">
            <h2 class="tab"><?php echo $lng['moreinfotitle']; ?></h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabHelpInfo' ) );
            </script>
            <?php echo $lng['moreinfocontent']; ?>
        </div>
    </div>
</div>