<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$tag = isset($tag) ? $tag : $_GET['tag'];

include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.menu.top.inc.php';
?>
<ul class="actionButtons">
    <li>
        <a href="<?php echo $index; ?>">
            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_left.png" alt="" /> <?php echo $lng['back']; ?>
        </a>
    </li>
</ul>

<table cellspacing="2" cellpadding="0">
    <tr>
        <td valign="top"><span class="h2title"><?php echo $lng['tag']; ?></span></td>
        <td valign="top">:</td>
        <td>
            <a href="<?php echo $index; ?>&amp;tag=<?php echo $tag; ?>"><?php echo $tag ; ?></a>
        </td>
    </tr>
</table>
<br />
<?php
$modView = $_SESSION['mod_view'];
switch ($modView) {
    case 'list':
        ob_start();
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.tag.view_list.inc.php';
        $obGetContents = ob_get_contents();
        ob_end_clean();
        break;
    case 'thumbnails':
        ob_start();
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.tag.view_thumb.inc.php';
        $obGetContents = ob_get_contents();
        ob_end_clean();
        break;
    default:
        break;
}

if (isset($_GET['view']))
    header("Location: " . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));

echo $obGetContents;