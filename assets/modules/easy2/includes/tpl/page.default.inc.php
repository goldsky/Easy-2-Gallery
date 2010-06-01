<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// Dir list
$dirs = @glob('../'.$this->_e2g_decode($gdir).'*', GLOB_ONLYDIR);
if(is_array($dirs)) natsort($dirs);

?>
<div id="e2g_topmenu">
    <form name="topmenu" action="" method="post">
        <ul class="actionButtons">
            <li id="Button1">
                <a href="<?php echo $index; ?>&act=synchro">
                    <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/refresh.png" alt="" /> <?php echo $lng['synchro']; ?>
                </a>
            </li>
            <li id="Button2">
                <a href="<?php echo $index; ?>&act=clean_cache">
                    <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/trash.png" alt="" /> <?php echo $lng['clean_cache']; ?>
                </a>
            </li>
            <li id="Button3">
                <a href="<?php echo $index; ?>&page=create_dir&pid=<?php echo $parent_id; ?>">
                    <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/folder_add.png" alt="" /> <?php echo $lng['create_dir']; ?>
                </a>
            </li>
            <!--li id="Button4">
                <a href="<?php echo $index; ?>&page=search_all&pid=<?php echo $parent_id; ?>">
                    <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/preview.png" alt="" /> <?php echo $lng['search']; ?>
                </a>
            </li-->
            <li id="Button5">
                <?php echo $lng['gotofolder']; ?>:
                <select name="newparent" onchange="submitform(1)">
                    <?php echo $this->_getfolderoptions(0,1); ?>
                </select>
            </li>
        </ul>
    </form>
</div>
<?php
// Description of the current directory
$qdesc = 'SELECT * '
        .'FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
        .'WHERE cat_id = '.$parent_id;
$resultdesc = mysql_query($qdesc);
while ($l = mysql_fetch_array($resultdesc)) {
    $dirtitle[$parent_id] = $l['cat_alias'];
    $dirtags[$parent_id] = $l['cat_tags'];
    $dirdesc[$parent_id] = $l['cat_description'];
}
?>
<table cellspacing="2" cellpadding="0">
    <tr>
        <td valign="top"><b><?php echo $lng['path']; ?></b></td>
        <td valign="top">:</td>
        <td>
            <a href="<?php echo $index; ?>&page=edit_dir&dir_id=<?php echo $parent_id; ?>&pid=<?php echo $this->_get_dir_info($parent_id, 'parent_id'); ?>">
                <img src="<?php echo  E2G_MODULE_URL ; ?>includes/icons/folder_edit.png" width="16" height="16"
                     alt="<?php echo $lng['edit']; ?>" title="<?php echo $lng['edit']; ?>" align="absmiddle" border=0>
            </a> <b><?php echo $path; ?></b>
        </td>
    </tr>
    <tr>
        <td valign="top"><b><?php echo $lng['enter_new_alias']; ?></b></td>
        <td valign="top">:</td>
        <td><?php echo $dirtitle[$parent_id]; ?></td>
    </tr>
    <tr>
        <td valign="top"><b><?php echo $lng['tags']; ?></b></td>
        <td valign="top">:</td>
        <td><?php echo $dirtags[$parent_id]; ?></td>
    </tr>
    <tr>
        <td valign="top"><b><?php echo $lng['description']; ?></b></td>
        <td valign="top">:</td>
        <td><?php echo $dirdesc[$parent_id]; ?></td>
    </tr>
</table>
<br />
<table cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td valign="top">
            <form name="list" action="" method="post">
                <table width="100%" cellpadding="2" border="0" cellspacing="0" class="grid" style="margin-bottom:10px">
                    <tr>
                        <td width="25"><input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;"></td>
                        <td width="20"> </td>
                        <td><b><?php echo $lng['name']; ?></b></td>
                        <td width="80"><b><?php echo $lng['modified']; ?></b></td>
                        <td width="40"><b><?php echo $lng['size']; ?></b></td>
                        <td width="60" align="right"><b><?php echo $lng['options']; ?></b></td>
                    </tr>

                    <?php
                    if ($dirs!=FALSE) {
                        foreach ($dirs as $f) {
                            $name = $this->_basename_safe($f);
                            $name = $this->_e2g_encode($name);
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
                                unset($mdirs[$name]);
                                $ext = '';
                                // edit name
                                $buttons = '<a href="'.$index.'&page=edit_dir&dir_id='.$id.'&name='.$name.'&pid='.$parent_id.'">
                                <img src="' . E2G_MODULE_URL . 'includes/icons/folder_edit.png" width="16" height="16"
                                    alt="'.$lng['edit'].'" title="'.$lng['edit'].'" border=0>
                                        </a>';
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
                        <td><?php echo @date($e2g['mdate_format'], $time); ?></td>
                        <td>---</td>
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
                        <td>---</td>
                        <td>---</td>
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


                    // File list
                    $mfiles = isset($mfiles) ? $mfiles : array();

                    $files = @glob('../'.$this->_e2g_decode($gdir).'*.*');
                    if(is_array($files)) natsort($files);

                    if ($files!=FALSE)
                        foreach ($files as $f) {
                            if ($this->is_validfolder($f)) continue;
                            if (!$this->is_validfile($f)) continue;
                            $size = round(filesize($f)/1024);
                            $time = filemtime($f);
                            $name = $this->_basename_safe($f);
                            $name = $this->_e2g_encode($name);
                            $ext = 'picture';
                            $id = $mfiles[$name]['id'];
                            if (isset($mfiles[$name])) {
                                if ($mfiles[$name]['status']==1) {
                                    $n = '<a href="javascript:imPreview(\''.$gdir.$name.'\');void(0);">'.$name.'</a> [id: '.$mfiles[$name]['id'].']';
                                } else {
                                    $n = '<a href="javascript:imPreview(\''.$gdir.$name.'\');void(0);"><i>'.$name.'</i></a> [id: '.$mfiles[$name]['id'].'] <i>('.$lng['hidden'].')</i>';
                                }
                                unset($mfiles[$name]);
                                $buttons = '
 <a href="'.$index.'&page=comments&file_id='.$id.'&pid='.$parent_id.'">
     <img src="' . E2G_MODULE_URL . 'includes/icons/comments.png" width="16" height="16" alt="'.$lng['comments'].'" title="'.$lng['comments'].'" border=0>
 </a>
 <a href="'.$index.'&page=edit_file&file_id='.$id.'&pid='.$parent_id.'">
     <img src="' . E2G_MODULE_URL . 'includes/icons/picture_edit.png" width="16" height="16" alt="'.$lng['edit'].'" title="'.$lng['edit'].'" border=0>
 </a>';
                            } else {
                                $n = '<a href="javascript:imPreview(\''.$gdir.$name.'\');void(0);" style="color:gray"><b>'.$name.'</b></a>';
                                $id = null;
                                $ext .= '_error';
                                if (empty($cpath)) {
                                    $buttons = '<a href="'.$index.'&act=add_file&file_path='.$gdir.$name.'&pid='.$parent_id.'">
                                    <img src="' . E2G_MODULE_URL . 'includes/icons/picture_add.png" width="16" height="16"
                                        alt="'.$lng['add_to_db'].'" title="'.$lng['add_to_db'].'" border=0>
                                            </a>';
                                }
                                else $buttons = '';
                            }
                            // content
                            ?>
                    <tr<?php echo $cl[$i%2]; ?>>
                        <td>
                            <input name="im[<?php echo (empty($id)?'f'.$i:$id) ;?>]" value="<?php echo $gdir.$name;?>"
                                   type="checkbox" style="border:0;padding:0">
                        </td>
                        <td><img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/<?php echo $ext ; ?>.png" width="16" height="16" alt="" /></td>
                        <td><?php echo $n; ?></td>
                        <td><?php echo @date($e2g['mdate_format'], $time); ?></td>
                        <td><?php echo $size; ?>Kb</td>
                        <td align="right" nowrap><?php echo $buttons; ?>
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
                        <td>---</td>
                        <td>---</td>
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
                <div id="e2g_bottommenu">
                    <ul class="actionButtons">
                        <?php echo $lng['withselected']; ?>:<br /><br />
                        <li id="Button6">
                            <a name="delete" href="javascript: submitform(2)" style="font-weight:bold;color:red">
                                <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/delete.png" alt="" /> <?php echo $lng['delete']; ?>
                            </a>
                        </li>
                        <li id="Button7">
                            <a name="download" href="javascript: submitform(3)">
                                <img src="<?php echo E2G_MODULE_URL; ?>includes/icons/page_white_compressed.png" alt="" /> <?php echo $lng['download']; ?>
                            </a>
                        </li>
                        <li id="Button8">
                            <!--select name="listactions">
                                <option value="move"><?php echo $lng['move']; ?></option>
                                <option value="copy"><?php echo $lng['copy']; ?></option>
                            </select-->
                            <?php echo $lng['movetofolder']; ?> :
                            <select name="newparent">
                                <option value="">&nbsp;</option>';

                                <?php echo $this->_getfolderoptions(0); ?>

                            </select>
                            and
                            <select name="gotofolder">
                                <option value="gothere">go there</option>
                                <option value="stayhere">stay here</option>
                            </select>
                            <a name="move" href="javascript: submitform(4)">
                                <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/sort.png" alt="" /> <?php echo $lng['go']; ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </form>
        </td>
        <th width="205" valign="top">
            <table cellspacing="0" cellpadding="0" style="margin-left:5px; border: 1px solid #ccc;width:200px; height:200px; ">
                <tr><th class="imPreview" id="pElt"></th></tr>
            </table>
        </th>
    </tr>
</table>