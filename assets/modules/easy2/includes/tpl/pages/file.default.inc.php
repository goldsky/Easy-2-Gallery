<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.menu.top.inc.php';

$parent = array();
// Description of the current directory
$qdesc = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
        . 'WHERE cat_id = ' . $this->e2gModCfg['parent_id'];

$resultdesc = mysql_query($qdesc);
while ($l = mysql_fetch_assoc($resultdesc)) {
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
<ul class="modButtons">
    <li>
        <?php
        if (!isset($this->sanitizedGets['path'])) {
            echo $this->actionIcon('edit_dir', array(
                'page' => 'edit_dir'
                , 'dir_id' => $this->e2gModCfg['parent_id']
                , 'pid' => $this->e2gModCfg['parent_id']
            ));
        }
        ?>
    </li>
    <?php if ($parent['cat_id'] !== '1') {?>
    <li>
        <?php
        $gdir = $this->e2g['dir'] . $this->galleryPath['string'];
        $dir_path = $gdir . (!empty($this->sanitizedGets['path']) ? $this->sanitizedGets['path'] : $parent['cat_name']);

        echo $this->actionIcon('delete_dir', array(
            'act' => 'delete_dir'
            , 'dir_path' => $dir_path
            , 'dir_id' => $parent['cat_id']
            , 'pid' => $this->e2gModCfg['parent_id']
                ), 'onclick="return confirmDeleteFolder();"', $this->e2gModCfg['index']);

        ?>
    </li>
    <?php } ?>
</ul>
<table cellspacing="2" cellpadding="0">
    <?php
// signature of non recorded directory is an additional &path parameter in the address bar
// otherwise then it's a recorded one.
    if (!isset($this->sanitizedGets['path'])) {
        ?>
        <tr>
            <td valign="top"><b>ID</b></td>
            <td valign="top">:</td>
            <td><?php echo $parent['cat_id']; ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td valign="top"><b><?php echo $this->lng['path']; ?></b></td>
        <td valign="top">:</td>
        <td><b><?php echo $this->galleryPath['link']; ?></b></td>
    </tr>
    <?php if (!isset($this->sanitizedGets['path'])) { ?>
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
        </tr>
        <tr>
            <td valign="top"><b><?php echo $this->lng['user_web_groups']; ?></b></td>
            <td valign="top">:</td>
            <td><?php
            // checks the restricted web access
            $webGroupNames = $this->webGroupNames($parent['cat_id'], 'dir');
            if (!empty($webGroupNames)) {
                $webGroupNames = implode(', ', $webGroupNames);
                $dirIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/icon_padlock.gif" width="16"
                    height="16" alt="lock" title="' . $lng['access'] . ': ' . $webGroupNames . '" border="0" />
                        ';
                echo $dirIcon . ' ' . $webGroupNames;
            }
                ?></td>
        </tr><?php } ?>
</table><br />

<form name="list" action="" method="post">
    <?php
    $processorFile = isset($this->sanitizedGets['path']) ? 'rescanhd' : 'dbfiles';
    switch ($_SESSION['mod_view']) {
        case 'list':
            ?>
            <div id="grid"></div>
            <script type="text/javascript">
                viewDefaultGrid('<?php echo $this->galleryPath['string'] ?>','<?php echo $this->e2gModCfg['parent_id'] ?>','<?php echo $processorFile; ?>');
            </script>
            <?php
            break;
        case 'thumbnails':
            if (!isset($this->sanitizedGets['path'])) {
                ?>
                <input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;" />
                <?php
                echo $this->lng['select_all'];
            }
            ?>
            <div id="thumbnail" class="e2g_wrapper"></div>
            <script type="text/javascript">
                viewDefaultThumbnails('<?php echo (!empty($this->sanitizedGets['path']) ? $this->sanitizedGets['path'] . '/' : $this->galleryPath['string']) ?>'
                ,'<?php echo $this->e2gModCfg['parent_id'] ?>','<?php echo $processorFile; ?>');
            </script>
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