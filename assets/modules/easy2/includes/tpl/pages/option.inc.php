<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="tab-page" id="Option">
    <div class="tab-pane" id="tabOptionPane">
        <script type="text/javascript">
            tpOption = new WebFXTabPane(document.getElementById('tabOptionPane'));
        </script>
        <div class="tab-page" id="tabOptionGeneral">
            <h2 class="tab"><?php echo $this->lng['general'] ; ?></h2>
            <script type="text/javascript">
                tpOption.addTabPage( document.getElementById( 'tabOptionGeneral') );
            </script>
            General Module's Options here, like:<br />
            <ul>
                <li>How many last uploads on the Dashboard?</li>
                <li>Manager theme?</li>
            </ul>
        </div>
        <div class="tab-page" id="tabOptionSettings">
            <h2 class="tab"><?php echo $this->lng['settings'] ; ?></h2>
            <script type="text/javascript">
                tpOption.addTabPage( document.getElementById( 'tabOptionSettings') );
            </script>
            All settings for each of Module's pages
        </div>
    </div>
</div>