<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$tag = isset($tag) ? $tag : $this->sanitizedGets['tag'];

include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.menu.top.inc.php';
?>
<ul class="actionButtons">
    <li>
        <a href="<?php echo $this->e2gModCfg['index']; ?>">
            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_left.png" alt="" /> <?php echo $this->lng['back']; ?>
        </a>
    </li>
</ul>

<table cellspacing="2" cellpadding="0">
    <tr>
        <td valign="top"><span class="h2title"><?php echo $this->lng['tag']; ?></span></td>
        <td valign="top">:</td>
        <td>
            <a href="<?php echo $this->e2gModCfg['index']; ?>&amp;tag=<?php echo $tag; ?>"><?php echo $tag; ?></a>
        </td>
    </tr>
</table>
<br />
<form name="list" action="" method="post">
    <?php
    $modView = $_SESSION['mod_view'];
    switch ($modView) {
        case 'list':
    ?>
            <div id="list"></div>
            <script type="text/javascript">viewTagGrid('<?php echo $this->galleryPath['string'] ?>','<?php echo $tag ?>');</script>
    <?php
            break;
        case 'thumbnails':
            if (!isset($this->sanitizedGets['path'])): ?>
                <input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;" /><?php
                echo $this->lng['select_all'];
            endif;
    ?>
            <div id="thumbnail" class="e2g_wrapper"></div>
            <script type="text/javascript">viewTagThumbnails('<?php echo $this->galleryPath['string'] ?>','<?php echo $tag ?>');</script>
    <?php
            break;
        default:
            break;
    }

    include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.menu.bottom.inc.php';
    ?>
</form>
<?php
    if (isset($this->sanitizedGets['view']))
        header("Location: " . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));