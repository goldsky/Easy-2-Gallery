<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.menu.top.inc.php';

$parent = array();
// Description of the current directory
$qdesc = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
        . 'WHERE cat_id = ' . $parentId;

$resultdesc = mysql_query($qdesc);
while ($l = mysql_fetch_array($resultdesc)) {
    $parent['cat_id'] = $l['cat_id'];
    $parent['cat_alias'] = $l['cat_alias'];
    $parent['cat_summary'] = $l['cat_summary'];
    $parent['cat_tag'] = $l['cat_tag'];
    $parent['cat_description'] = $l['cat_description'];
    $parent['cat_visible'] = $l['cat_visible'];
    $parent['cat_redirect_link'] = $l['cat_redirect_link'];
}
mysql_free_result($resultdesc);
?>
<table cellspacing="2" cellpadding="0">
        <tr>
            <td valign="top"><b>ID</b></td>
            <td valign="top">:</td>
            <td><?php echo $parent['cat_id']; ?></td>
        </tr>
    <tr>
        <td valign="top"><b><?php echo $lng['path']; ?></b></td>
        <td valign="top">:</td>
        <td>
            <?php
            // signature of non recorded directory is an additional &path parameter in the address bar
            // otherwise then it's a recorded one.
            if (!isset($_GET['path'])) {
                echo $this->_actionIcon('edit_dir', array(
                    'page' => 'edit_dir'
                    , 'dir_id' => $parentId
                )); } ?>
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
                if ($parent['cat_visible'] == 1)
                    echo htmlspecialchars_decode($lng['visible'], ENT_QUOTES);
                else
                    echo '<span style="color:red;font-weight:bold;font-style:italic;">' . htmlspecialchars_decode($lng['hidden'], ENT_QUOTES) . '</span>';
            ?>
            </td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['enter_new_alias']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo $parent['cat_alias']; ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['summary']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo htmlspecialchars_decode($parent['cat_summary'], ENT_QUOTES); ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['tag']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo $this->_createTagLinks($parent['cat_tag']); ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['description']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo htmlspecialchars_decode($parent['cat_description'], ENT_QUOTES); ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['redirect_link']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo $parent['cat_redirect_link']; ?></td>
        </tr>

            <?php } ?>
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