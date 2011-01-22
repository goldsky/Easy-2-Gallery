<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.menu.top.inc.php';

$parent = array();
// Description of the current directory
$qdesc = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
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
        <td valign="top"><b><?php echo $this->lng['path']; ?></b></td>
        <td valign="top">:</td>
        <td>
            <?php
            // signature of non recorded directory is an additional &path parameter in the address bar
            // otherwise then it's a recorded one.
            if (!isset($_GET['path'])) {
                echo $this->actionIcon('edit_dir', array(
                    'page' => 'edit_dir'
                    , 'dir_id' => $parentId
                    , 'pid' => $parentId
                ));
            }
            ?>
            <b><?php echo $path['link']; ?></b>
        </td>
    </tr><?php
            // signature of non recorded directory = &path in the address bar
            if (!isset($_GET['path'])) : ?>
                <tr>
                    <td valign="top"><b><?php echo $this->lng['visible']; ?></b></td>
                    <td valign="top">:</td>
                    <td>
            <?php
                if ($parent['cat_visible'] == 1)
                    echo htmlspecialchars_decode($this->lng['visible'], ENT_QUOTES);
                else
                    echo '<span style="color:red;font-weight:bold;font-style:italic;">' . htmlspecialchars_decode($this->lng['hidden'], ENT_QUOTES) . '</span>';
            ?>
            </td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $this->lng['enter_new_alias']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo $parent['cat_alias']; ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $this->lng['summary']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo htmlspecialchars_decode($parent['cat_summary'], ENT_QUOTES); ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $this->lng['tag']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo $this->createTagLinks($parent['cat_tag']); ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $this->lng['description']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo htmlspecialchars_decode($parent['cat_description'], ENT_QUOTES); ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $this->lng['redirect_link']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo $parent['cat_redirect_link']; ?></td>
        </tr><?php endif; ?>
    </table><br />

    <form name="list" action="" method="post"><?php
                $modView = $_SESSION['mod_view'];
                switch ($modView) {
                    case 'list':
            ?>
                        <div id="list"></div>
                        <script type="text/javascript">viewDefaultGrid('<?php echo $path['string'] ?>','<?php echo $parentId ?>');</script>
    <?php
                        break;
                    case 'thumbnails':
                        if (!isset($_GET['path'])): ?>
                            <input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;" /><?php
                            echo $this->lng['select_all'];
                        endif;
    ?>
                        <div id="thumbnail" class="e2g_wrapper"></div>
                        <script type="text/javascript">viewDefaultThumbnails('<?php echo $path['string'] ?>','<?php echo $parentId ?>');</script>
    <?php
                        break;
                    default:
                        break;
                }

                include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.menu.bottom.inc.php';
    ?>
            </form>
<?php
                if (isset($_GET['view']))
                    header("Location: " . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));