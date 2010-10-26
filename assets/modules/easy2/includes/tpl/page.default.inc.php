<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// Dir list
$dirs = @glob('../' . $this->_e2gDecode($gdir) . '*', GLOB_ONLYDIR);
if (is_array($dirs))
    natsort($dirs);

include_once E2G_MODULE_PATH . 'includes/tpl/menu.top.inc.php';

$dir = array();
// Description of the current directory
$qdesc = 'SELECT * '
        . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
        . 'WHERE cat_id = ' . $parentId;

$resultdesc = mysql_query($qdesc);
while ($l = mysql_fetch_array($resultdesc)) {
    $dir['alias'] = $l['cat_alias'];
    $dir['summary'] = $l['cat_summary'];
    $dir['tag'] = $l['cat_tag'];
    $dir['desc'] = $l['cat_description'];
    $dir['visible'] = $l['cat_visible'];
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
                <a href="<?php echo $index; ?>&amp;page=edit_dir&amp;dir_id=<?php echo $parentId; ?>&amp;pid=<?php echo $this->_getDirInfo($parentId, 'parent_id'); ?>">
                    <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/folder_edit.png" width="16" height="16"
                         alt="<?php echo $lng['edit']; ?>" title="<?php echo $lng['edit']; ?>" align="middle" border="0" />
                </a>
            <?php } ?>
            <b><?php echo $path; ?></b>
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
                if ($dir['visible'] == 1)
                    echo htmlspecialchars_decode($lng['visible'], ENT_QUOTES);
                else
                    echo '<span style="color:red;font-weight:bold;font-style:italic;">' . htmlspecialchars_decode($lng['hidden'], ENT_QUOTES) . '</span>';
            ?>
            </td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['enter_new_alias']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo $dir['alias']; ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['summary']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo htmlspecialchars_decode($dir['summary'], ENT_QUOTES); ?></td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['tag']; ?></b></td>
            <td valign="top">:</td>
            <td>
            <?php
                if ($dir['tag'] != '') {
                    $multipleTags = @explode(',', $dir['tag']);
                    $countTags = count($multipleTags);
                    for ($c = 0; $c < $countTags; $c++) {
                        echo '<a href="' . $index . '&amp;page=tag&amp;tag=' . trim($multipleTags[$c]) . '">' . trim($multipleTags[$c]) . '</a>';
                        if ($c < ($countTags - 1))
                            echo ', ';
                    }
                }
            ?>
            </td>
        </tr>
        <tr>
            <td valign="top"><b><?php echo $lng['description']; ?></b></td>
            <td valign="top">:</td>
            <td><?php echo htmlspecialchars_decode($dir['desc'], ENT_QUOTES); ?></td>
        </tr>
    <?php } ?>
        </table>
        <br />
        <!--div>
            <a href="javascript:;" id="displayText" onclick="showAllImages();">
                <span style="float: left;width: 1.2em;">+</span> Show all images
            </a>
        </div>
        <div id="toggleText" style="display: none;">
            test
        </div-->
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td valign="top">
                    <form name="list" action="" method="post">
                        <table width="100%" cellpadding="2" border="0" cellspacing="0" class="grid" style="margin-bottom:10px">
                            <tr>
                                <td width="25"><input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;" /></td>
                                <th width="60"><?php echo $lng['actions']; ?></th>
                                <th width="20"><?php echo $lng['type']; ?></th>
                                <th><?php echo $lng['dir'] . ' / ' . $lng['filename']; ?></th>
                                <th><?php echo $lng['alias'] . ' / ' . $lng['name']; ?></th>
                                <th><?php echo $lng['tag']; ?></th>
                                <th width="100"><?php echo $lng['date']; ?></th>
                                <th width="50"><?php echo $lng['size']; ?> (Kb)</th>
                                <th width="40">W (px)</th>
                                <th width="40">H (px)</th>
                            </tr>

                    <?php
                    //******************************************************************/
                    //***************** FOLDERS/DIRECTORIES/GALLERIES ******************/
                    //******************************************************************/

                    if ($dirs != FALSE) {
                        foreach ($dirs as $dirPath) {
                            $dirName = $this->_basenameSafe($dirPath);
                            $dirName = $this->_e2gEncode($dirName);
                            $dirname = urldecode($dirName);
                            $dirpath = str_replace('%2F', '/', rawurlencode($gdir . $dirName));
                            $dirAlias = $mdirs[$dirName]['alias'];
                            $dirTag = $mdirs[$dirName]['cat_tag'];
                            $dirTime = (strtotime($mdirs[$dirName]['last_modified']) != '') ? strtotime($mdirs[$dirName]['last_modified']) : strtotime($mdirs[$dirName]['date_added']);
                            $countFiles = $this->_countFiles($dirPath);
                            if ($dirName == '_thumbnails')
                                continue;
                            if (isset($mdirs[$dirName])) {
                                $id = $mdirs[$dirName]['id'];
                                $ext = '';
                                if ($mdirs[$dirName]['cat_visible'] == 1) {
                                    $n = '<a href="' . $index . '&amp;pid=' . $mdirs[$dirName]['id'] . '"><b>' . $mdirs[$dirName]['name'] . '</b></a> [id: ' . $mdirs[$dirName]['id'] . ']';
                                    $buttons = '
<a href="' . $index . '&amp;act=hide_dir&amp;dir_id=' . $id . '&amp;name=' . $dirname . '&amp;pid=' . $parentId . '">
    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_opened.png" width="16"
        height="16" alt="' . $lng['visible'] . '" title="' . $lng['visible'] . '" border="0" />
</a>';
                                } else {
                                    $n = '<a href="' . $index . '&amp;pid=' . $mdirs[$dirName]['id'] . '"><i>' . $mdirs[$dirName]['name'] . '</i></a> [id: ' . $mdirs[$dirName]['id'] . '] <i>(' . $lng['hidden'] . ')</i>';
                                    $buttons = '
<a href="' . $index . '&amp;act=show_dir&amp;dir_id=' . $id . '&amp;name=' . $dirname . '&amp;pid=' . $parentId . '">
    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16"
        height="16" alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />
</a>';
                                }
                                // edit name
                                $buttons .= '
<a href="' . $index . '&amp;page=edit_dir&amp;dir_id=' . $id . '&amp;name=' . $dirname . '&amp;pid=' . $parentId . '">
    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_edit.png" width="16"
        height="16" alt="' . $lng['edit'] . '" title="' . $lng['edit'] . '" border="0" />
</a>';
                                unset($mdirs[$dirName]);
                            } else {

                                /**
                                 * Existed dir in file system, but not yet inserted into database
                                 */
                                $n = '<a href="' . $index . '&amp;pid=' . $parentId . '&amp;path=' . (!empty($cpath) ? $cpath : '') . $dirName . '" style="color:gray"><b>' . $dirName . '</b></a>';
                                $id = null;
                                $ext = '_error';
                                if (empty($cpath)) {
                                    $buttons = '
<a href="' . $index . '&amp;act=add_dir&amp;dir_path=' . $dirpath . '&amp;pid=' . $parentId . '">
    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_add.png" width="16"
        height="16" alt="' . $lng['add_to_db'] . '" title="' . $lng['add_to_db'] . '" border="0" />
</a>';
                                }
                                else
                                    $buttons = '';
                            }

                            // print out the content
                    ?>
                            <tr<?php echo $cl[$i % 2]; ?>>
                                <td>
                                    <input name="dir[<?php echo (empty($id) ? 'd' . $i : $id); ?>]" value="<?php echo rawurldecode($dirpath); ?>"
                                           type="checkbox" style="border:0;padding:0" />
                                </td>
                                <td align="right" nowrap="nowrap" style="border-right: 1px dotted #cccccc;border-left: 1px dotted #cccccc;" valign="top">
                            <?php echo $buttons; ?>
                            <a href="<?php echo $index; ?>&amp;act=delete_dir&amp;dir_path=<?php echo $dirpath . (empty($id) ? '' : '&amp;dir_id=' . $id); ?>"
                               onclick="return confirmDeleteFolder();">
                                <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/delete.png" border="0"
                                     alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                            </a>
                        </td>
                        <td><img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/folder<?php echo $ext; ?>.png" width="16" height="16" border="0" alt="" /></td>
                        <td><?php echo $n; ?> (<?php echo $countFiles; ?>)</td>
                        <td><?php echo ($dirAlias != '' ? $dirAlias : '&nbsp;'); ?></td>
                        <td>
                            <?php
                            if (!empty($dirTag)) {
                                $multipleTags = @explode(',', $dirTag);
                                $countTags = count($multipleTags);
                                for ($c = 0; $c < $countTags; $c++) {
                                    echo '<a href="' . $index . '&amp;page=tag&amp;tag=' . trim($multipleTags[$c]) . '">' . trim($multipleTags[$c]) . '</a>';
                                    if ($c < ($countTags - 1))
                                        echo ', ';
                                }
                            } else {
                                echo '&nbsp;';
                            }
                            ?>
                        </td>
                        <td><?php echo @date($e2g['mdate_format'], $dirTime); ?></td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                    </tr>
                    <?php
                            $i++;
                        } // foreach ($dirs as $dirPath)

                        /**
                         * Deleted dirs from file system, but still exists in database
                         */
                        if (isset($mdirs) && count($mdirs) > 0) {
                            foreach ($mdirs as $k => $v) {
                    ?>
                                <tr<?php echo $cl[$i % 2]; ?>>
                                    <td><input name="dir[<?php echo $v['id']; ?>]" value="" type="checkbox" style="border:0;padding:0" /></td>
                                    <td align="right" nowrap="nowrap" style="border-right: 1px dotted #cccccc;border-left: 1px dotted #cccccc;" valign="top">
                                        <a href="<?php echo $index; ?>&amp;act=delete_dir&amp;dir_id=<?php echo $v['id']; ?>"
                                           onclick="return confirmDeleteFolder();">
                                            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/delete.png" border="0"
                                                 alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                                        </a>
                                    </td>
                                    <td><img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/folder_delete.png" width="16" height="16" border="0" alt="" /></td>
                                    <td><b style="color:red;"><u><?php echo $v['name']; ?></u></b> [<?php echo $v['id']; ?>]</td>
                                    <td><?php echo $v['cat_tag']; ?></td>
                                    <td style="text-align:center;">---</td>
                                    <td style="text-align:center;">---</td>
                                    <td style="text-align:center;">---</td>
                                    <td style="text-align:center;">---</td>
                                    <td style="text-align:center;">---</td>
                                </tr>
                    <?php
                                $i++;
                            } // foreach ($mdirs as $k => $v)
                        } // if (isset($mdirs) && count($mdirs) > 0)
                    } // if ($dirs!=FALSE)
                    //******************************************************************/
                    //************* FILE content for the current directory *************/
                    //******************************************************************/

                    $mfiles = isset($mfiles) ? $mfiles : array();

                    $files = @glob('../' . $this->_e2gDecode($gdir) . '*.*');
                    if (is_array($files))
                        natsort($files);

                    if ($files != FALSE) {
                        foreach ($files as $filePath) {
                            if ($this->validFolder($filePath))
                                continue;
                            if (!$this->validFile($filePath))
                                continue;

                            $filename = $this->_basenameSafe($filePath);
                            $filename = $this->_e2gEncode($filename);
                            $urlDecodeFilename = urldecode($filename);
                            $filePathRawUrlEncoded = str_replace('%2F', '/', rawurlencode($gdir . $filename));
                            $alias = $mfiles[$filename]['alias'];
                            $tag = $mfiles[$filename]['tag'];

                            $ext = 'picture';
                            if (isset($mfiles[$filename])) {
                                $id = $mfiles[$filename]['id'];
                                $tag = $mfiles[$filename]['tag'];
                                $size = round($mfiles[$filename]['size'] / 1024);
                                $width = $mfiles[$filename]['width'];
                                $height = $mfiles[$filename]['height'];
                                $time = (strtotime($mfiles[$filename]['last_modified']) != '') ? strtotime($mfiles[$filename]['last_modified']) : strtotime($mfiles[$filename]['date_added']);

                                if ($mfiles[$filename]['status'] == 1) {
                                    $n = $filename;
                                    $n_stat = '';
                                    $buttons = '
 <a href="' . $index . '&amp;act=hide_file&amp;file_id=' . $id . '&amp;pid=' . $parentId . '">
     <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_opened.png" width="16" height="16" alt="' . $lng['visible'] . '" title="' . $lng['visible'] . '" border="0" />
 </a>';
                                } else {
                                    $n = '<i>' . $filename . '</i>';
                                    $n_stat = '<i>(' . $lng['hidden'] . ')</i>';
                                    $buttons = '
 <a href="' . $index . '&amp;act=show_file&amp;file_id=' . $id . '&amp;pid=' . $parentId . '">
     <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16" height="16" alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />
 </a>';
                                }
                                $buttons .= '
 <a href="' . $index . '&amp;page=comments&amp;file_id=' . $id . '&amp;pid=' . $parentId . '">
     <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/comments.png" width="16" height="16" alt="' . $lng['comments'] . '" title="' . $lng['comments'] . '" border="0" />
 </a>
 <a href="' . $index . '&amp;page=edit_file&amp;file_id=' . $id . '&amp;pid=' . $parentId . '">
     <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_edit.png" width="16" height="16" alt="' . $lng['edit'] . '" title="' . $lng['edit'] . '" border="0" />
 </a>';

                                unset($mfiles[$filename]);
                            } else {
                                /**
                                 * Existed files in file system, but not yet inserted into database
                                 */
                                $size = round(filesize($filePath) / 1024);
                                list($width, $height) = @getimagesize($filePath);
                                $time = filemtime($filePath);
                                clearstatcache();

                                $n = '<span style="color:gray"><b>' . $filename . '</b></span>';
                                $id = null;
                                $ext .= '_error';
                                if (empty($cpath)) {
                                    $buttons = '
<a href="' . $index . '&amp;act=add_file&amp;file_path=' . $filePathRawUrlEncoded . '&amp;pid=' . $parentId . '">
    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_add.png" width="16" height="16" alt="' . $lng['add_to_db'] . '" title="' . $lng['add_to_db'] . '" border="0" />
</a>';
                                }
                                else
                                    $buttons = '';
                            }
                            // content
                    ?>
                            <tr<?php echo $cl[$i % 2]; ?>>
                                <td valign="top">
                                    <input name="im[<?php echo (empty($id) ? 'f' . $i : $id); ?>]" value="<?php echo rawurldecode($filePathRawUrlEncoded); ?>"
                                           type="checkbox" style="border:0;padding:0" />
                                </td>
                                <td align="right" nowrap="nowrap" style="border-right: 1px dotted #cccccc;border-left: 1px dotted #cccccc;" valign="top">
                            <?php echo $buttons; ?>
                            <a href="<?php echo $index; ?>&amp;act=delete_file&amp;file_path=<?php echo $filePathRawUrlEncoded . (empty($id) ? '' : '&amp;file_id=' . $id); ?>"
                               onclick="return confirmDelete();">
                                <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/delete.png" border="0"
                                     alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                            </a>
                        </td>
                        <td valign="top"><img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/<?php echo $ext; ?>.png" width="16" height="16" alt="" /></td>
                        <td>
                            <div>
                                <a href="javascript:void(0)" onclick="imPreview('<?php echo $filePathRawUrlEncoded; ?>', <?php echo $i; ?>);"><?php echo $n; ?>
                                </a> <?php if ($id != null)
                                echo '[id: ' . $id . ']'; ?> <?php echo $n_stat; ?>
                            </div>
                            <div class="imPreview" id="rowPreview_<?php echo $i; ?>" style="display:none;"></div>
                        </td>
                        <td valign="top"><?php echo ($alias != '' ? $alias : '&nbsp;'); ?></td>
                        <td valign="top"><?php
                                if (!empty($tag)) {
                                    $multipleTags = @explode(',', $tag);
                                    $countTags = count($multipleTags);
                                    for ($c = 0; $c < $countTags; $c++) {
                                        echo '<a href="' . $index . '&amp;page=tag&amp;tag=' . trim($multipleTags[$c]) . '">' . trim($multipleTags[$c]) . '</a>';
                                        if ($c < ($countTags - 1))
                                            echo ', ';
                                    }
                                } else {
                                    echo '&nbsp;';
                                } ?>
                            </td>
                            <td valign="top"><?php echo @date($e2g['mdate_format'], $time); ?></td>
                            <td valign="top" style="text-align:right;"><?php echo $size; ?></td>
                            <td valign="top" style="text-align:right;"><?php echo $width; ?></td>
                            <td valign="top" style="text-align:right;"><?php echo $height; ?></td>
                        </tr>
                    <?php
                                $i++;
                            } // foreach ($files as $filePath)
                        } // if ($files != FALSE)

                        /**
                         * Deleted files from file system, but still exists in database
                         */
                        $mfiles = isset($mfiles) ? $mfiles : array();
                        $filename = isset($filename) ? $filename : array();

                        if (isset($mfiles) && count($mfiles) > 0) {
                            foreach ($mfiles as $k => $v) {
                    ?>
                                <tr<?php echo $cl[$i % 2]; ?>>
                                    <td><input name="im[<?php echo $v['id']; ?>]" value="" type="checkbox" style="border:0;padding:0" /></td>
                                    <td align="right" nowrap="nowrap" style="border-right: 1px dotted #cccccc;border-left: 1px dotted #cccccc;" valign="top">
                                        <a href="<?php echo $index; ?>&amp;page=comments&amp;file_id=<?php echo $v['id']; ?>&amp;pid=<?php echo $parentId; ?>">
                                            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/comments.png" width="16" height="16"
                                                 alt="<?php echo $lng['comments']; ?>" title="<?php echo $lng['comments']; ?>" border="0" />
                                        </a>
                                        <a href="<?php echo $index; ?>&amp;act=delete_file&amp;file_id=<?php echo $v['id']; ?>"
                                           onclick="return confirmDeleteFolder();">
                                            <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/delete.png" border="0"
                                                 alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                                        </a>
                                    </td>
                                    <td><img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/picture_delete.png" width="16" height="16" border="0" alt="" /></td>
                                    <td><b style="color:red;"><u><?php echo $v['name']; ?></u></b> [<?php echo $v['id']; ?>]</td>
                                    <td><?php echo ($v['alias'] != '' ? $v['alias'] : '&nbsp;'); ?></td>
                                    <td valign="top"><?php
                                if (!empty($v['tag'])) {
                                    $multipleTags = @explode(',', $v['tag']);
                                    $countTags = count($multipleTags);
                                    for ($c = 0; $c < $countTags; $c++) {
                                        echo '<a href="' . $index . '&amp;page=tag&amp;tag=' . trim($multipleTags[$c]) . '">' . trim($multipleTags[$c]) . '</a>';
                                        if ($c < ($countTags - 1))
                                            echo ', ';
                                    }
                                } else {
                                    echo '&nbsp;';
                                } ?>
                            </td>
                            <td valign="top" style="text-align:center;">---</td>
                            <td valign="top" style="text-align:center;">---</td>
                            <td valign="top" style="text-align:right;">---</td>
                            <td valign="top" style="text-align:right;">---</td>
                        </tr>
                    <?php
                                $i++;
                            }
                        }
                    ?>

                    </table>
                <?php include_once E2G_MODULE_PATH . 'includes/tpl/menu.bottom.inc.php'; ?>

            </form>
        </td>
    </tr>
</table>