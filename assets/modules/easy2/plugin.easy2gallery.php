<?php
global $e;
$e = &$modx->Event;

switch ($e->name) {
    case 'OnWebPageInit':
        $_SESSION['e2g_instances'] = 1;
        break;
    case 'OnWebPageComplete':
        unset($_SESSION['e2g_instances']);
        break;
    case 'OnDocFormRender':
        /**
         * need a patch in the mutate_content.dynamic.php, line 1166
         * @link http://modxcms.com/forums/index.php/topic,52295.msg303089.html#msg303089
         */
?>
        <div class="tab-page" id="tabEasy2Gallery">
            <h2 class="tab">Easy 2 Gallery</h2>
            <script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabEasy2Gallery" ) );</script>
            <div>
        <?php
        ob_start();
        include MODX_BASE_PATH . 'assets/modules/easy2/index.php';
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
        ?>
    </div>
</div>
<?php
        break;
    default :
        break;
}

return;