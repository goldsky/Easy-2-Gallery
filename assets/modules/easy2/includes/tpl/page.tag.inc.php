<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// Dir list
//$dirs = @glob('../'.$this->_e2g_decode($gdir).'*', GLOB_ONLYDIR);
//if(is_array($dirs)) natsort($dirs);

include_once E2G_MODULE_PATH . 'includes/tpl/menu.top.inc.php';
?>
<a href="<?php echo $index; ?>"><?php echo $lng['back']; ?></a>
<table cellspacing="2" cellpadding="0">
    <tr>
        <td valign="top"><b><?php echo $lng['tag']; ?></b></td>
        <td valign="top">:</td>
        <td>
            <?php echo '<a href="'.$index.'&page=tag&tag='.$_get_tag.'">'.$_get_tag.'</a>'; ?>
        </td>
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
                        <td><b><?php echo $lng['path']; ?></b></td>
                        <td><b><?php echo  $lng['dir'].' / '.$lng['filename']; ?></b></td>
                        <td><b><?php echo $lng['alias'].' / '.$lng['name']; ?></b></td>
                        <td><b><?php echo $lng['tag']; ?></b></td>
                        <td width="80"><b><?php echo $lng['modified']; ?></b></td>
                        <td width="40"><b><?php echo $lng['size']; ?></b></td>
                        <td width="60" align="right"><b><?php echo $lng['options']; ?></b></td>
                    </tr>

                    <?php

                    /******************************************************************/
                    /***************** FOLDERS/DIRECTORIES/GALLERIES ******************/
                    /******************************************************************/
                    if (count($mdirs)>0) {
                        if(is_array($mdirs)) natsort($mdirs);

                        foreach ($mdirs as $f) {
                            $name = $this->_basename_safe($f['name']);
                            $name = $this->_e2g_encode($name);

                            $cp = $this->_path_to($mdirs[$name]['parent_id']);
                            unset ($cp[1]);
                            $cdir='';
                            if (!empty($cp)) $cdir .= implode( '/', $cp ) . '/';

                            $alias = $mdirs[$name]['alias'];
                            $tag = $mdirs[$name]['cat_tag'];
                            $sanitized_tags = @explode(',', $tag);
                            for ($c=0;$c<count($sanitized_tags);$c++) {
                                $sanitized_tags[$c] = trim($sanitized_tags[$c]);
                            }

                            $time = ($mdirs[$name]['last_modified']==''? '---':strtotime($mdirs[$name]['last_modified']));

                            $cnt = mysql_result(mysql_query(
                                    'SELECT COUNT(id) FROM '.$modx->db->config['table_prefix'].'easy2_files '
                                    .'WHERE dir_id = '.$mdirs[$name]['id']
                                    ),0,0);

                            if (($mdirs[$name]['cat_visible']==1)) {
                                $n = '<a href="'.$index.'&pid='.$mdirs[$name]['id'].'"><b>'.$mdirs[$name]['name'].'</b></a> [id: '.$mdirs[$name]['id'].']';
                            } else {
                                $n = '<a href="'.$index.'&pid='.$mdirs[$name]['id'].'"><i>'.$mdirs[$name]['name'].'</i></a> [id: '.$mdirs[$name]['id'].'] <i>('.$lng['invisible'].')</i>';
                            }
                            $id = $mdirs[$name]['id'];
                            $ext = '';
                            // edit name
                            $buttons = '<a href="'.$index.'&page=edit_dir&dir_id='.$id.'&name='.$name.'&pid='.$mdirs[$name]['parent_id'].'">
                            <img src="' . E2G_MODULE_URL . 'includes/icons/folder_edit.png" width="16" height="16"
                                alt="'.$lng['edit'].'" title="'.$lng['edit'].'" border=0>
                                    </a>';
                            // print out the content
                            ?>

                    <tr<?php echo $cl[$i%2]; ?>>
                        <td valign="top">
                            <input name="dir[<?php echo (empty($id)?'d'.$i:$id); ?>]" value="<?php echo $gdir.$name; ?>"
                                   type="checkbox" style="border:0;padding:0">
                        </td>
                        <td valign="top"><img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/folder<?php echo $ext;?>.png" width="16" height="16" border="0" alt="" /></td>
                        <td valign="top">
                            <?php if($cdir) { ?>
                            <a href="<?php echo $index; ?>&pid=<?php echo $mdirs[$name]['parent_id']; ?>&page=openexplorer" onclick="showTab('file')"><?php echo $cdir; ?></a><br />
                            <?php } ?>
                        </td>
                        <td valign="top"><?php echo $n; ?> (<?php echo $cnt; ?>)</td>
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
                        <td valign="top">---</td>
                        <td align="right" nowrap>
                                    <?php echo $buttons; ?>
                            <a href="<?php echo $index; ?>&act=delete_dir&dir_path=<?php echo $gdir.$cdir.$name.(empty($id)?'':'&dir_id='.$id); ?>"
                               onclick="return confirmDeleteFolder();">
                                <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/delete.png" border="0"
                                     alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                            </a>
                        </td>
                    </tr>
                            <?php
                            $i++;
                        } // foreach ($mdirs as $f)
                    } // if (count($mdirs)>0)

                    /******************************************************************/
                    /************* FILE content for the current directory *************/
                    /******************************************************************/
                    
                    $mfiles = isset($mfiles) ? $mfiles : array();
                    if (count($mfiles)>0) {
                        if(is_array($mfiles)) natsort($mfiles);
                        foreach ($mfiles as $f) {
                            $name = $this->_basename_safe($f['name']);
                            $name = $this->_e2g_encode($name);
                            $n_stat = $mfiles[$name]['status']==1 ? '' : '<i>('.$lng['hidden'].')</i>';
                            $cp = $this->_path_to($mfiles[$name]['dir_id']);
                            unset ($cp[1]);
                            $cdir='';
                            if (!empty($cp)) $cdir .= implode( '/', $cp ) . '/';

                            $alias = $mfiles[$name]['alias'];
                            $time =  ($mfiles[$name]['last_modified']==''? '---':strtotime($mfiles[$name]['last_modified']));
                            $tag = $mfiles[$name]['tag'];
                            $sanitized_tags = @explode(',', $tag);
                            for ($c=0;$c<count($sanitized_tags);$c++) {
                                $sanitized_tags[$c] = trim($sanitized_tags[$c]);
                            }
                            $ext = 'picture';
                            $n = $mfiles[$name]['status']==1 ? $name : '<i>'.$name.'</i>';
                            $n_stat = $mfiles[$name]['status']==1 ? '' : '<i>('.$lng['hidden'].')</i>';
                            $tag = $mfiles[$name]['tag'];
                            $id = $mfiles[$name]['id'];
                            $buttons = '
 <a href="'.$index.'&page=comments&file_id='.$id.'&pid='.$mfiles[$name]['dir_id'].'">
     <img src="' . E2G_MODULE_URL . 'includes/icons/comments.png" width="16" height="16" alt="'.$lng['comments'].'" title="'.$lng['comments'].'" border=0>
 </a>
 <a href="'.$index.'&page=edit_file&file_id='.$id.'&pid='.$mfiles[$name]['dir_id'].'">
     <img src="' . E2G_MODULE_URL . 'includes/icons/picture_edit.png" width="16" height="16" alt="'.$lng['edit'].'" title="'.$lng['edit'].'" border=0>
 </a>';
                            // content
                            ?>
                    <tr<?php echo $cl[$i%2]; ?>>
                        <td valign="top">
                            <input name="im[<?php echo (empty($id)?'f'.$i:$id) ;?>]" value="<?php echo $gdir.$cdir.$name;?>"
                                   type="checkbox" style="border:0;padding:0">
                        </td>
                        <td valign="top"><img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/<?php echo $ext ; ?>.png" width="16" height="16" alt="" /></td>
                        <td valign="top">
                                    <?php if($cdir) { ?>
                            <a href="<?php echo $index; ?>&pid=<?php echo $mfiles[$name]['dir_id']; ?>&page=openexplorer" onclick="showTab('file')"><?php echo $cdir; ?></a><br />
                                        <?php } ?>
                        </td>
                        <td valign="top">
                            <div>
                                <a href="javascript:void(0)" onclick="imPreview('<?php echo $gdir.$cdir.$name; ?>', <?php echo $i; ?>);"><?php echo $n; ?>
                                </a> <?php echo '[id: '.$id.']'; ?> <?php echo $n_stat; ?>
                            </div>
                            <div class="imPreview" id="rowPreview_<?php echo $i; ?>" style="display:none;"></div>
                                        
                                        <?php //echo $n; ?>

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
                        <td valign="top"><?php echo $size; ?>Kb</td>
                        <td align="right" nowrap valign="top"><?php echo $buttons; ?>
                            <a href="<?php echo $index; ?>&act=delete_file&file_path=<?php echo $gdir.$cdir.$name.(empty($id)?'':'&file_id='.$id); ?>"
                               onclick="return confirmDelete();">
                                <img src="<?php echo E2G_MODULE_URL ; ?>includes/icons/delete.png" border="0"
                                     alt="<?php echo $lng['delete']; ?>" title="<?php echo $lng['delete']; ?>" />
                            </a>
                        </td>
                    </tr>
                            <?php
                            $i++;
                        } // foreach ($files as $f)
                    } // if (count($mfiles)>0)
                    ?>
                </table>
                <?php include_once E2G_MODULE_PATH . 'includes/tpl/menu.bottom.inc.php'; ?>
            </form>
        </td>
    </tr>
</table>