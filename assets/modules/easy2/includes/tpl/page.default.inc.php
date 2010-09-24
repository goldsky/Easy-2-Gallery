<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// Dir list
$dirs = @glob('../'.$this->_e2g_decode($gdir).'*', GLOB_ONLYDIR);
if(is_array($dirs)) natsort($dirs);

include_once E2G_MODULE_PATH . 'includes/tpl/menu.top.inc.php';

$dir=array();
// Description of the current directory
$qdesc = 'SELECT * '
        .'FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
        .'WHERE cat_id = '.$parent_id;

$resultdesc = mysql_query($qdesc);
while ($l = mysql_fetch_array($resultdesc)) {
    $dir[$parent_id]['alias'] = $l['cat_alias'];
    $dir[$parent_id]['summary'] = $l['cat_summary'];
    $dir[$parent_id]['tag'] = $l['cat_tag'];
    $dir[$parent_id]['desc'] = $l['cat_description'];
}
?>
<table cellspacing="2" cellpadding="0">
    <tr>
        <td valign="top"><b><?php echo $lng['path']; ?></b></td>
        <td valign="top">:</td>
        <td>
            <?php
            // signature of non recorded directory = &path in the address bar
            if (!isset($_GET['path'])) {
            ?>
            <a href="<?php echo $index; ?>&page=edit_dir&dir_id=<?php echo $parent_id; ?>&pid=<?php echo $this->_get_dir_info($parent_id, 'parent_id'); ?>">
                <img src="<?php echo  E2G_MODULE_URL ; ?>includes/icons/folder_edit.png" width="16" height="16"
                     alt="<?php echo $lng['edit']; ?>" title="<?php echo $lng['edit']; ?>" align="absmiddle" border=0>
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
        <td valign="top"><b><?php echo $lng['enter_new_alias']; ?></b></td>
        <td valign="top">:</td>
        <td><?php echo $dir[$parent_id]['alias']; ?></td>
    </tr>
    <tr>
        <td valign="top"><b><?php echo $lng['summary']; ?></b></td>
        <td valign="top">:</td>
        <td><?php echo htmlspecialchars_decode($dir[$parent_id]['summary'], ENT_QUOTES); ?></td>
    </tr>
    <tr>
        <td valign="top"><b><?php echo $lng['tag']; ?></b></td>
        <td valign="top">:</td>
        <td>
            <?php
            $multiple_tags = @explode(',', $dir[$parent_id]['tag']);
            $count_tags = count($multiple_tags);
            for ($c=0;$c<$count_tags;$c++) {
                echo '<a href="'.$index.'&page=tag&tag='.trim($multiple_tags[$c]).'">'.trim($multiple_tags[$c]).'</a>';
                if ($c<($count_tags-1)) echo ', ';
            }
            ?>
        </td>
    </tr>
    <tr>
        <td valign="top"><b><?php echo $lng['description']; ?></b></td>
        <td valign="top">:</td>
        <td><?php echo htmlspecialchars_decode($dir[$parent_id]['desc'], ENT_QUOTES); ?></td>
    </tr>
    <?php } ?>
</table>
<br />
<!--div>
    <a href="javascript:void(0);" id="displayText" onclick="showAllImages();">
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
                        <td width="25"><input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;"></td>
                        <th width="20"><?php echo $lng['type']; ?></th>
                        <th><?php echo $lng['dir'].' / '.$lng['filename']; ?></th>
                        <th><?php echo $lng['alias'].' / '.$lng['name']; ?></th>
                        <th><?php echo $lng['tag']; ?></th>
                        <th width="80"><?php echo $lng['modified']; ?></th>
                        <th width="40"><?php echo $lng['size']; ?></th>
                        <th width="40">W (px)</th>
                        <th width="40">H (px)</th>
                        <th width="60" align="right"><?php echo $lng['options']; ?></th>
                    </tr>

                    <?php

                    /******************************************************************/
                    /***************** FOLDERS/DIRECTORIES/GALLERIES ******************/
                    /******************************************************************/

                    if ($dirs!=FALSE) {
                        foreach ($dirs as $f) {
                            $name = $this->_basename_safe($f);
                            $name = $this->_e2g_encode($name);
                            $alias = $mdirs[$name]['alias'];
                            $tag = $mdirs[$name]['cat_tag'];
                            $time = filemtime($f);
                            $cnt = $this->_count_files($f);
                            if ($name == '_thumbnails') continue;
                            if ( isset($mdirs[$name]) ) {
                                if (($mdirs[$name]['cat_visible']==1)) {
                                    $n = '<a href="'.$index.'&pid='.$mdirs[$name]['id'].'"><b>'.$mdirs[$name]['name'].'</b></a> [id: '.$mdirs[$name]['id'].']';
                                } else {
                                    $n = '<a href="'.$index.'&pid='.$mdirs[$name]['id'].'"><i>'.$mdirs[$name]['name'].'</i></a> [id: '.$mdirs[$name]['id'].'] <i>('.$lng['invisible'].')</i>';
                                }
                                $id = $mdirs[$name]['id'];
                                $ext = '';
                                // edit name
                                $buttons = '<a href="'.$index.'&page=edit_dir&dir_id='.$id.'&name='.$name.'&pid='.$parent_id.'">
                                <img src="' . E2G_MODULE_URL . 'includes/icons/folder_edit.png" width="16" height="16"
                                    alt="'.$lng['edit'].'" title="'.$lng['edit'].'" border=0>
                                        </a>';
                                unset($mdirs[$name]);
                            } else {
                                $n = '<a href="'.$index.'&pid='.$parent_id.'&path='.(!empty($cpath)?$cpath:'').$name.'" style="color:gray"><b>'.$name.'</b></a>';
                                $id = null;
                                $ext = '_error';
                                if (empty($cpath)) {
                                    $buttons = '<a href="'.$index.'&act=add_dir&dir_path='.$gdir.$name.'&pid='.$parent_id.'">
                                    <img src="' . E2G_MODULE_URL . 'includes/icons/folder_add.png" width="16" height="16"
                                        alt="'.$lng['add_to_db'].'" title="'.$lng['add_to_db'].'" border=0>
                                            </a>';
                                }
                                else $buttons = '';
                            }

                            // print out the content
                            ?>
                    <tr<?php echo $cl[$i%2]; ?>>
                        <td>
                            <input name="dir[<?php echo (empty($id)?'d'.$i:$id); ?>]" value="<?php echo $gdir.$name; ?>"
                                   type="checkbox" style="border:0;padding:0">
                        </td>
                        <td><img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/folder<?php echo $ext;?>.png" width="16" height="16" border="0" alt="" /></td>
                        <td><?php echo $n; ?> (<?php echo $cnt; ?>)</td>
                        <td><?php echo $alias; ?></td>
                        <td>
                                    <?php
                                    $multiple_tags = @explode(',', $tag);
                                    $count_tags = count($multiple_tags);
                                    for ($c=0;$c<$count_tags;$c++) {
                                        echo '<a href="'.$index.'&page=tag&tag='.trim($multiple_tags[$c]).'">'.trim($multiple_tags[$c]).'</a>';
                                        if ($c<($count_tags-1)) echo ', ';
                                    }
                                    ?>
                        </td>
                        <td><?php echo @date($e2g['mdate_format'], $time); ?></td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td align="right" nowrap>
                                    <?php echo $buttons; ?>
                            <a href="<?php echo $index; ?>&act=delete_dir&dir_path=<?php echo $gdir.$name.(empty($id)?'':'&dir_id='.$id); ?>"
                               onclick="return confirmDeleteFolder();">
                                <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/delete.png" border="0"
                                     alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                            </a>
                        </td>
                    </tr>
                            <?php
                            $i++;
                        } // foreach ($dirs as $f)

                        // Deleted dirs
                        if (isset($mdirs) && count($mdirs) > 0) {
                            foreach ($mdirs as $k => $v) {
                                ?>
                    <tr<?php echo $cl[$i%2]; ?>>
                        <td><input name="dir[<?php echo $v['id']; ?>]" value="" type="checkbox" style="border:0;padding:0"></td>
                        <td><img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/folder_delete.png" width="16" height="16" border="0" alt="" /></td>
                        <td><b style="color:red;"><u><?php echo $v['name']; ?></u></b> [<?php echo $v['id']; ?>]</td>
                        <td><?php echo $alias; ?></td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td align="right">
                            <a href="<?php echo $index; ?>&act=delete_dir&dir_id=<?php echo $v['id']; ?>"
                               onclick="return confirmDeleteFolder();">
                                <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/delete.png" border="0"
                                     alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                            </a>
                        </td>
                    </tr>
                                <?php
                                $i++;
                            } // foreach ($mdirs as $k => $v)
                        } // if (isset($mdirs) && count($mdirs) > 0)
                    } // if ($dirs!=FALSE)


                    /******************************************************************/
                    /************* FILE content for the current directory *************/
                    /******************************************************************/

                    $mfiles = isset($mfiles) ? $mfiles : array();

                    $files = @glob('../'.$this->_e2g_decode($gdir).'*.*');
                    if(is_array($files)) natsort($files);

                    if ($files!=FALSE)
                        foreach ($files as $f) {
                            if ($this->is_validfolder($f)) continue;
                            if (!$this->is_validfile($f)) continue;
                            $size = round(filesize($f) / 1024);
                            list($width, $height) = @getimagesize($f);
                            $time = filemtime($f);
                            $name = $this->_basename_safe($f);
                            $name = $this->_e2g_encode($name);
                            $alias = $mfiles[$name]['alias'];
                            $tag = $mfiles[$name]['tag'];

                            $ext = 'picture';
                            if (isset($mfiles[$name])) {
                                $n = $mfiles[$name]['status']==1 ? $name : '<i>'.$name.'</i>';
                                $n_stat = $mfiles[$name]['status']==1 ? '' : '<i>('.$lng['hidden'].')</i>';
                                $tag = $mfiles[$name]['tag'];
                                $id = $mfiles[$name]['id'];
                                unset($mfiles[$name]);
                                $buttons = '
 <a href="'.$index.'&page=comments&file_id='.$id.'&pid='.$parent_id.'">
     <img src="' . E2G_MODULE_URL . 'includes/icons/comments.png" width="16" height="16" alt="'.$lng['comments'].'" title="'.$lng['comments'].'" border=0>
 </a>
 <a href="'.$index.'&page=edit_file&file_id='.$id.'&pid='.$parent_id.'">
     <img src="' . E2G_MODULE_URL . 'includes/icons/picture_edit.png" width="16" height="16" alt="'.$lng['edit'].'" title="'.$lng['edit'].'" border=0>
 </a>';
                            } else {
                                $n = '<span style="color:gray"><b>'.$name.'</b></span>';
                                $id = null;
                                $ext .= '_error';
                                if (empty($cpath)) {
                                    $buttons = '
<a href="'.$index.'&act=add_file&file_path='.$gdir.$name.'&pid='.$parent_id.'">
    <img src="' . E2G_MODULE_URL . 'includes/icons/picture_add.png" width="16" height="16" alt="'.$lng['add_to_db'].'" title="'.$lng['add_to_db'].'" border=0>
</a>';
                                }
                                else $buttons = '';
                            }
                            // content
                            ?>
                    <tr<?php echo $cl[$i%2]; ?>>
                        <td valign="top">
                            <input name="im[<?php echo (empty($id)?'f'.$i:$id) ;?>]" value="<?php echo $gdir.$name;?>"
                                   type="checkbox" style="border:0;padding:0">
                        </td>
                        <td valign="top"><img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/<?php echo $ext ; ?>.png" width="16" height="16" alt="" /></td>
                        <td>
                            <div>
                                <a href="javascript:void(0)" onclick="imPreview('<?php echo $gdir.$name; ?>', <?php echo $i; ?>);"><?php echo $n; ?>
                                </a> <?php if ($id!=null) echo '[id: '.$id.']'; ?> <?php echo $n_stat; ?>
                            </div>
                            <div class="imPreview" id="rowPreview_<?php echo $i; ?>" style="display:none;"></div>
                        </td>
                        <td valign="top"><?php echo $alias; ?></td>
                        <td valign="top">
                                    <?php
                                    $multiple_tags = @explode(',', $tag);
                                    $count_tags = count($multiple_tags);
                                    for ($c=0;$c<$count_tags;$c++) {
                                        echo '<a href="'.$index.'&page=tag&tag='.trim($multiple_tags[$c]).'">'.trim($multiple_tags[$c]).'</a>';
                                        if ($c<($count_tags-1)) echo ', ';
                                    }
                                    ?>
                        </td>
                        <td valign="top"><?php echo @date($e2g['mdate_format'], $time); ?></td>
                        <td valign="top" style="text-align:right;"><?php echo $size; ?>Kb</td>
                        <td valign="top" style="text-align:right;"><?php echo $width; ?></td>
                        <td valign="top" style="text-align:right;"><?php echo $height; ?></td>
                        <td align="right" nowrap valign="top">
                        <?php echo $buttons; ?>
                            <a href="<?php echo $index; ?>&act=delete_file&file_path=<?php echo $gdir.$name.(empty($id)?'':'&file_id='.$id); ?>"
                               onclick="return confirmDelete();">
                                <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/delete.png" border="0"
                                     alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                            </a>
                        </td>
                    </tr>
                            <?php
                            $i++;
                        } // foreach ($files as $f)
                    // Deleted files
                    $mfiles = isset($mfiles) ? $mfiles : array();
                    $name = isset($name) ? $name : array();

                    if (isset($mfiles) && count($mfiles) > 0) {
                        foreach ($mfiles as $k => $v) {
                            ?>
                    <tr<?php echo $cl[$i%2]; ?>>
                        <td><input name="im[<?php echo $v['id']; ?>]" value="" type="checkbox" style="border:0;padding:0"></td>
                        <td><img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/picture_delete.png" width="16" height="16" border="0" alt="" /></td>
                        <td><b style="color:red;"><u><?php echo $v['name']; ?></u></b> [<?php echo $v['id']; ?>]</td>
                        <td><?php echo $alias; ?></td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td style="text-align:center;">---</td>
                        <td align="right" nowrap>
                            <a href="<?php echo $index; ?>&page=comments&file_id=<?php echo $v['id']; ?>&pid=<?php echo $parent_id; ?>">
                                <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/comments.png" width="16" height="16"
                                     alt="<?php echo $lng['comments']; ?>" title="<?php echo $lng['comments']; ?>" border=0>
                            </a>
                            <a href="<?php echo $index; ?>&act=delete_file&file_id=<?php echo $v['id']; ?>"
                               onclick="return confirmDeleteFolder();">
                                <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/delete.png" border="0"
                                     alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                            </a>
                        </td>
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