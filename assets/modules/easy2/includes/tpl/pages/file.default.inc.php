<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.menu.top.inc.php';

$parent = array();
// Description of the current directory
$qdesc = 'SELECT * '
        . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
        . 'WHERE cat_id = ' . $parentId;

$resultdesc = mysql_query($qdesc);
while ($l = mysql_fetch_array($resultdesc)) {
    $parent['alias'] = $l['cat_alias'];
    $parent['summary'] = $l['cat_summary'];
    $parent['tag'] = $l['cat_tag'];
    $parent['desc'] = $l['cat_description'];
    $parent['visible'] = $l['cat_visible'];
}
mysql_free_result($resultdesc);
?>
<table cellspacing="2" cellpadding="0">
    <tr>
        <td valign="top"><b><?php echo $lng['path']; ?></b></td>
        <td valign="top">:</td>
        <td>
            <?php
            // signature of non recorded directory is an additional &path parameter in the address bar
            // otherwise then it's a recorded one.
            if (!isset($_GET['path'])) {
            ?>
                <a href="<?php echo $index; ?>&amp;page=edit_dir&amp;dir_id=<?php echo $parentId; ?>&amp;pid=<?php echo $parentId; ?>">
                    <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/folder_edit.png" width="16" height="16"
                         alt="<?php echo $lng['edit']; ?>" title="<?php echo $lng['edit']; ?>" align="middle" border="0" />
                </a>
            <?php } ?>
            <b><?php echo $path['link']; ?></b>
        </td>
    </tr>
    <?php
            // signature of non recorded directory = &path in the address bar
            if (!isset($_GET['path'])) {
    ?>
                <tr>
                    <td valign="top"><b><?php echo $lng['visible']; ?></b></td>
                    <td valign="top">:</td>
                    <td>
            <?php
                if ($parent['visible'] == 1)
                    echo htmlspecialchars_decode($lng['visible'], ENT_QUOTES);
                else
                    echo '<span style="color:red;font-weight:bold;font-style:italic;">' . htmlspecialchars_decode($lng['hidden'], ENT_QUOTES) . '</span>';
            ?>
            </td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['enter_new_alias']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo $parent['alias']; ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['summary']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo htmlspecialchars_decode($parent['summary'], ENT_QUOTES); ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['tag']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo $this->_createTagLinks($parent['tag']); ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['description']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo htmlspecialchars_decode($parent['desc'], ENT_QUOTES); ?></td>
        </tr><?php } ?>
</table>

<br />
<?php
$modView = $_SESSION['mod_view'];
switch ($modView) {
    case 'list':
        ob_start();
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.default.view_list.inc.php';
        $obGetContents = ob_get_contents();
        ob_end_clean();
        break;
    case 'thumbnails':
        ob_start();
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.default.view_thumb.inc.php';
        $obGetContents = ob_get_contents();
        ob_end_clean();
        break;
    default:
        break;
}

if (isset($_GET['view']))
    header("Location: " . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));

echo $obGetContents;