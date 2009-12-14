/******************************************************************************
*
*  EASY 2 GALLERY BY Cx2 <inteldesign@mail.ru>
*  VERSION 1.35
*
******************************************************************************/


// SYSTEM VARS
$debug = 0;
$_t = $modx->config['manager_theme'];
$_a = (int) $_GET['a'];
$_i = (int) $_GET['id'];
$index = 'index.php?a='.$_a.'&id='.$_i;


if ($debug == 1) {
    error_reporting(E_ALL);
    $old_error_handler = set_error_handler("error_handler");
}

if (file_exists('../assets/modules/easy2/langs/'.$modx->config['manager_language'].'.inc.php')) {
    include_once '../assets/modules/easy2/langs/'.$modx->config['manager_language'].'.inc.php';
} else {
    include_once '../assets/modules/easy2/langs/english.inc.php';
}

mysql_select_db(str_replace('`', '', $GLOBALS['dbase']));
@mysql_query("{$GLOBALS['database_connection_method']} {$GLOBALS['database_connection_charset']}"); 

//session_start();
// ALERTS / ERRORS
if (!isset($_SESSION['easy2err'])) $_SESSION['easy2err'] = array();
if (!isset($_SESSION['easy2suc'])) $_SESSION['easy2suc'] = array();

require_once '../assets/modules/easy2/config.easy2gallery.php';

// Install
if (is_dir('../assets/modules/easy2/install')) {
    require_once '../assets/modules/easy2/install/index.php';
    exit();
}
$e2g['mdate_format'] = 'd-m-y H:i';

if (!is_dir('../'.$e2g['dir'])) {
    echo '<b style="color:red">'.$lng['dir'].' &quot;'.$e2g['dir'].'&quot; '.$lng['empty'].'</b>';
    exit;
} elseif (!is_dir('../'.$e2g['dir'].'_thumbnails')) {
    if (mkdir('../'.$e2g['dir'].'_thumbnails')) {
        @chmod('../'.$e2g['dir'].'_thumbnails', 0755);
    } else {
        echo '<b style="color:red">'.$lng['_thumb_err'].'</b>';
        exit;
    }
}

$gdir = $e2g['dir'];
$path = '';


$parent_id = (isset($_GET['pid']) && is_numeric($_GET['pid'])) ? (int) $_GET['pid'] : 1;
$p = get_path($parent_id);
foreach ($p as $k => $v) {
    $path .= '<a href="'.$index.'&pid='.$k.'">'.$v.'</a>/';
}
unset ($p[1]);

if (!empty($p)) $gdir .= implode('/', array_keys($p)).'/';


if (!empty($_GET['path'])) {
    $dirs = str_replace('../', '', $_GET['path']);
    $dirs = explode('/', $dirs);
    $cpath = '';
    foreach ($dirs as $v) {
        if (empty($v)) continue;
        $cpath .= $v.'/';
        $path .= '<a href="'.$index.'&pid='.$parent_id.'&path='.$cpath.'">'.$v.'</a>/';
    }

    $gdir .= $cpath;
}

$path .= '</b>';






//
//  ACTIONS
//

$act = empty($_GET['act']) ? '' : $_GET['act'];
switch ($act) {

case 'synchro':
    if(synchro('../'.$e2g['dir'],1,$e2g)) {
        $_SESSION['easy2suc'][] = $lng['synchro_suc'];
    } else {
        $_SESSION['easy2err'][] = $lng['synchro_err'];
    }
    $res = delete_all ('../'.$e2g['dir'].'_thumbnails/');
    if (empty($res['e'])) {
        $_SESSION['easy2suc'][] = $lng['cache_clean'];
    } else {
        $_SESSION['easy2err'][] = $lng['cache_clean_err'];
        $_SESSION['easy2err'] = array_merge($_SESSION['easy2err'], $res['e']);
    }
    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
    exit();

// UPLOADING IMAGES

case 'uploadzip':
    if($_FILES['zip']['error']==0 && $_FILES['zip']['size']>0)  {
        include_once '../assets/modules/easy2/pclzip.lib.php';
        $zip = new PclZip($_FILES['zip']['tmp_name']);
        if (($list = $zip->listContent()) == 0) {
            $_SESSION['easy2err'][] = "Error : ".$zip->errorInfo(true);
        } else {
            $j=sizeof($list);
        }
        if ($zip->extract(PCLZIP_OPT_PATH, '../'.$gdir) == 0) {
            $_SESSION['easy2err'][] = "Error : ".$zip->errorInfo(true);
        } else {
            $_SESSION['easy2suc'][] = $j.' '.$lng['files_uploaded'].'.';
        }
        @unlink($_FILES['zip']['tmp_name']);
        if(synchro('../'.$e2g['dir'],1,$e2g)) {
            $_SESSION['easy2suc'][] = $lng['synchro_suc'];
        } else {
            $_SESSION['easy2err'][] = $lng['synchro_err'];
        }
        $res = delete_all ('../'.$e2g['dir'].'_thumbnails/');
        if (empty($res['e'])) {
            $_SESSION['easy2suc'][] = $lng['cache_clean'];
        } else {
            $_SESSION['easy2err'][] = $lng['cache_clean_err'];
            $_SESSION['easy2err'] = array_merge($_SESSION['easy2err'], $res['e']);
        }
    } else {
        $_SESSION['easy2err'][] = $lng['upload_err'].' ' . $_FILES['img']['name'];
    }
    header ("Location: ".$index.'&pid='.$_GET['pid']);
    exit();

case 'upload':

    $j = 0;
    for ($i = 0; $i < count($_FILES['img']['tmp_name']); $i++) {
        if (!is_uploaded_file($_FILES['img']['tmp_name'][$i])) {
            $_SESSION['easy2err'][] = $lng['upload_err'].' ' . $_FILES['img']['name'][$i];
            continue;
        }

        if (!preg_match('/^image\//i', $_FILES['img']['type'][$i])) {
            $_SESSION['easy2err'][] = $lng['type_err'].' ' . $_FILES['img']['type'][$i];
            continue;
        }

        if (!mysql_query('INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files(dir_id,filename,size,name,description,date_added) VALUES('.$parent_id.', \''.mysql_real_escape_string($_FILES['img']['name'][$i]).'\', '.(int)$_FILES['img']['size'][$i].', \''.mysql_real_escape_string(htmlspecialchars($_POST['name'][$i])).'\', \''.mysql_real_escape_string(htmlspecialchars($_POST['description'][$i])).'\', NOW())')) {
            $_SESSION['easy2err'][] = $lng['db_err'].': ' . mysql_error();
            continue;
        }

        $id = mysql_insert_id();

        $pos = strrpos($_FILES['img']['name'][$i], '.');
        $ext = substr($_FILES['img']['name'][$i], $pos);

        $inf = getimagesize($_FILES['img']['tmp_name'][$i]);
        if (($e2g['maxw'] > 0 || $e2g['maxh'] > 0) && ($inf[0] > $e2g['maxw'] || $inf[1] > $e2g['maxh'])) {
            resize_img ($_FILES['img']['tmp_name'][$i], $inf, $e2g['maxw'], $e2g['maxh'], $e2g['maxthq']);
        }
        move_uploaded_file($_FILES['img']['tmp_name'][$i], '../'.$gdir.$id.$ext);
        @chmod('../'.$gdir.$id.$ext, 0644);
        $j++;
    }
    $_SESSION['easy2suc'][] = $j.' '.$lng['files_uploaded'].'.';

    header ("Location: ".$index.'&pid='.$_GET['pid']);
    exit();



// Create Dirrectory

case 'create_dir':

    require_once '../assets/modules/easy2/TTree.class.php';
    $tree = new TTree();
    $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';

    $_GET['name'] = htmlspecialchars($_GET['name'], ENT_QUOTES);

    if ( ($id = $tree->insert($_GET['name'], $parent_id)) ) {
        if (mkdir('../'.$gdir.$id)) {
            $_SESSION['easy2suc'][] = $lng['directory_created'];
            @chmod('../'.$gdir.$id, 0755);
        } else {
            $_SESSION['easy2err'][] = $lng['directory_create_err'];
            $tree->delete($id);
        }
    } else {
        $_SESSION['easy2err'][] = $tree->error;
    }

    header ("Location: ".$index."&pid=".$parent_id);
    exit();


case 'edit_dir':

    require_once '../assets/modules/easy2/TTree.class.php';
    $tree = new TTree();
    $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';

    $_GET['name'] = htmlspecialchars($_GET['name'], ENT_QUOTES);

    if ($tree->update($_GET['dir_id'], $_GET['name'])) {
        $_SESSION['easy2suc'][] = $lng['updated'];
    } else {
        $_SESSION['easy2err'][] = $lng['update_err'];
        $_SESSION['easy2err'][] = $tree->error;
    }

    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
    exit();


case 'delete_checked':

    $out = '';

    $res = array(
     'fdb'=>array(0, 0),
     'ffp'=>array(0, 0),
     'ddb'=>array(0, 0),
     'dfp'=>array(0, 0),
    );


    // Delete dirs
    if (!empty($_POST['dir'])) {

        require_once '../assets/modules/easy2/TTree.class.php';
        $tree = new TTree();
        $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';

        foreach ($_POST['dir'] as $k => $v) {

            $del_f_res = 0;

            if (is_numeric($k)) {
                $ids = $tree->delete((int) $k);

                $files_id = array();
                $files_res = mysql_query('SELECT id FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id IN('.implode(',', $ids).')');
                while ($l = mysql_fetch_row($files_res)) $files_id[] = $l[0];

                if (count($files_id) > 0) {
                    mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id IN('.implode(',', $files_id).')');
                }


                mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id IN('.implode(',', $ids).')');

                $res['fdb'][0] += mysql_affected_rows();

                if (count($ids) > 0) {
                    $res['ddb'][0] += count($ids);
                } else {
                    $res['ddb'][1]++;
                }
            }

            if (!empty($v)) {
                $v = str_replace('../', '', $v);
                $d = delete_all('../'.$v.'/');

                if (empty($d['e'])) {
                    $res['dfp'][0] += $d['d'];
                    $res['ffp'][0] += $d['f'];
                } else {
                    $res['dfp'][1]++;
                }
            }

            $out .= $k .'=>'. $v.'<br>';
        }

        if ($res['dfp'][0] == 0 && $res['ddb'][0] == 0) {
            $_SESSION['easy2err'][] = $lng['dirs_delete_err'];
        } elseif ($res['dfp'][0] == $res['ddb'][0]) {
            $_SESSION['easy2suc'][] = $res['dfp'][0] . ' '.$lng['dirs_deleted'].'.';
        } else {
            $_SESSION['easy2suc'][] = $res['ddb'][0] . ' '.$lng['dirs_deleted_fdb'].'.';
            $_SESSION['easy2suc'][] = $res['dfp'][0] . ' '.$lng['dirs_deleted_fhdd'].'.';
        }
    }

    // Delete imgages
    if (!empty($_POST['im'])) {

        foreach ($_POST['im'] as $k => $v) {

            if (is_numeric($k)) {

                $files_id = array();
                $files_res = mysql_query('SELECT id FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.(int) $k);
                while ($l = mysql_fetch_row($files_res)) $files_id[] = $l[0];

                if (count($files_id) > 0) {
                    mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id IN('.implode(',', $files_id).')');
                }

                if (mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.(int) $k)) {
                    $res['fdb'][0]++;
                } else {
                    $res['fdb'][1]++;
                }
            }

            if (!empty($v)) {
                $v = str_replace('../', '', $v);
                if (@unlink('../'.$v)) {
                    $res['ffp'][0]++;
                } else {
                    $res['ffp'][1]++;
                }
            }

            $out .= $k .'=>'. $v.'<br>';
        }

        if ($res['ffp'][0] == 0 && $res['fdb'][0] == 0) {
            $_SESSION['easy2err'][] = $lng['files_delete_err'];
        }

    }

    if (!empty($res['ffp']) || !empty($res['fdb'])) {
        if ($res['ffp'][0] == $res['fdb'][0]) {
            $_SESSION['easy2suc'][] = $res['ffp'][0] . ' '.$lng['files_deleted'].'.';
        } else {
            $_SESSION['easy2suc'][] = $res['fdb'][0] . ' '.$lng['files_deleted_fdb'].'.';
            $_SESSION['easy2suc'][] = $res['ffp'][0] . ' '.$lng['files_deleted_fhdd'].'.';
        }
    }

    //print_r($res);

    //$_SESSION['easy2suc'][] = $out;
    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
    exit();



// Delete Dirrectory
case 'delete_dir':

    if (empty($_GET['dir_id']) && empty($_GET['dir_path'])) {
        $_SESSION['easy2err'][] = $lng['dpath_err'];
        break;
    }



    if (is_numeric($_GET['dir_id'])) {
        require_once '../assets/modules/easy2/TTree.class.php';
        $tree = new TTree();
        $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
        $ids = $tree->delete((int) $_GET['dir_id']);

        $files_id = array();
        $res = mysql_query('SELECT id FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id IN('.implode(',', $ids).')');
        while ($l = mysql_fetch_row($res)) {
            $files_id[] = $l[0];
        }

        if (count($files_id) > 0) {
            mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id IN('.implode(',', $files_id).')');
        }




        mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id IN('.implode(',', $ids).')');
    }

    if (!empty($_GET['dir_path'])) {
        $dir_path = str_replace('../', '', $_GET['dir_path']);
        $res = delete_all('../'.$dir_path.'/');
    }

    if (count($ids) > 0 && count($res['e']) == 0) {
        $_SESSION['easy2suc'][] = $lng['dir_delete'];
    } elseif (count($ids) > 0) {
        $_SESSION['easy2suc'][] = $lng['dir_delete_fdb'];
    } elseif (count($res['e']) == 0) {
        $_SESSION['easy2suc'][] = $lng['dir_delete_fhdd'];
    } else {
        if (!empty($res['e'])) $_SESSION['easy2err'] = $res['e'];
        if (!empty($tree->error)) $_SESSION['easy2err'][] = $tree->error;
        $_SESSION['easy2err'][] = $lng['dir_delete_err'];
    }

    if ($res['f'] > 0) {
        $_SESSION['easy2suc'][] = $res['f'] . ' '.$lng['files_deleted'].'.';
    }

    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
    exit();



// Delete file

case 'delete_file':

    if (empty($_GET['file_id']) && empty($_GET['file_path'])) {
        $_SESSION['easy2err'][] = $lng['fpath_err'];
        break;
    }

    $id = (int) $_GET['file_id'];

    if (is_numeric($_GET['file_id'])) {
        $db_res = mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.$id);
        mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id='.$id);
    }

    if (!empty($_GET['file_path'])) {
        $file_path = str_replace('../', '', $_GET['file_path']);
        $f_res = @unlink('../'.$file_path);
    }

    if ($db_res && $f_res) {
        $_SESSION['easy2suc'][] = $lng['file_delete'];
    } elseif ($db_res) {
        $_SESSION['easy2suc'][] = $lng['file_delete_fdb'];
    } elseif ($f_res) {
        $_SESSION['easy2suc'][] = $lng['file_delete_fhdd'];
    } else {
        $_SESSION['easy2err'][] = $lng['file_delete_err'];
    }


    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
    exit();


// Delete comments
case 'delete_comments':

    $cids = array();
    foreach ($_POST['comment'] as $cid) {
        if (!is_numeric($cid)) continue;
        $cids[] = (int) $cid;
    }

    mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE id IN('.implode(',', $cids).')');
    mysql_query('UPDATE '.$modx->db->config['table_prefix'].'easy2_files SET comments=comments-'.count($cids).' WHERE id ='.(int)$_GET['file_id']);

    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
    exit();


// CACHE
case 'clean_cache':

    $res = delete_all ('../'.$gdir.'_thumbnails/');

    if (empty($res['e'])) {
        $_SESSION['easy2suc'][] = $lng['cache_clean'].', '.$lng['files_deleted'].': '.$res['f'].', '.$lng['dirs_deleted'].': '.$res['d'];
    } else {
        $_SESSION['easy2err'][] = $lng['cache_clean_err'].', '.$lng['files_deleted'].': '.$res['f'].', '.$lng['dirs_deleted'].': '.$res['d'];
        $_SESSION['easy2err'] = array_merge($_SESSION['easy2err'], $res['e']);
    }

    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
    exit();


// CONFIG
case 'save_config':

    if (!empty($_POST['clean_cache'])) {
        unset($_POST['clean_cache']);
        $url = $index.'&act=clean_cache';
    } else {
        $url = html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES);
    }

    // CHECK/CREATE DIRS
    $_POST['dir'] = preg_replace('/^\/?(.+)$/', '\\1', $_POST['dir']);
    $dirs = explode('/', substr($_POST['dir'], 0, -1));

    $npath = '..';
    foreach ($dirs as $dir) {
        $npath .= '/'.$dir;
        if (is_dir($npath) || empty($dir)) continue;

        if(mkdir($npath)) {
            @chmod($npath, 0755);
        } else {
            $_SESSION['easy2err'][] = $lng['directory_create_err'].' "'.$npath."'";
        }
    }

    $c = "<?php\r\n\$e2g = array (\r\n";
    foreach($_POST as $k => $v) {
        $c .= "'$k' => ".(is_numeric($v)?$v:"'$v'").",\r\n";
    }
    $c .= ");\r\n?>";

    $f = fopen('../assets/modules/easy2/config.easy2gallery.php', 'w');
    fwrite($f, $c);
    fclose($f);

    $_SESSION['easy2suc'][] = $lng['updated'];

    header ('Location: '.$url);
    exit();


// ADD DIRECTORY
case 'add_dir':


    if(add_all('../'.str_replace('../', '', $_GET['dir_path']), $parent_id, $e2g)) {
        $_SESSION['easy2suc'][] = $lng['dir_edded'];
    } else {
        $_SESSION['easy2err'][] = $lng['dir_edd_err'];
    }


    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
    exit();



// ADD IMAGE
case 'add_file':

    $f = '../'.str_replace('../', '', $_GET['file_path']);

    $inf = getimagesize($f);
    if ($inf[2] <= 3 && is_numeric($_GET['pid'])) {

        // RESIZE
        if (($e2g['maxw'] > 0 || $e2g['maxh'] > 0) && ($inf[0] > $e2g['maxw'] || $inf[1] > $e2g['maxh'])) {
            resize_img($f, $inf, $e2g['maxw'], $e2g['maxh'], $e2g['maxthq']);
        }

        $n = htmlspecialchars(basename($f), ENT_QUOTES);
        $s = filesize($f);

        $q = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files '
           . '(dir_id,filename,size,name,description,date_added) '
           . "VALUES(".(int)$_GET['pid'].",'$n',$s,'','',NOW())";

        //die($q);

        if (mysql_query($q)) {
            $new_name = preg_replace('/\/[^\/]+(\.[^\.]{1,4})$/i', "/".mysql_insert_id()."\\1", $f);
            rename($f, $new_name);
            @chmod($new_name, 0644);
            $_SESSION['easy2suc'][] = $lng['file_added'];
        } else {
            $_SESSION['easy2err'][] = $lng['add_file_err'];
            $_SESSION['easy2err'][] = mysql_error();
        }

    } else {
        $_SESSION['easy2err'][] = $lng['add_file_err'];
    }

    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
    exit();
}




$cl = array(' class="gridAltItem"', ' class="gridItem"');
$i = 0;


$page = empty($_GET['page']) ? '' : $_GET['page'];
$content = '';
switch ($page) {



// EDIT FILE

case 'edit_file':

    if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
        $_SESSION['easy2err'][] = $id['id_err'];
        header ("Location: ".html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if(mysql_query('UPDATE '.$modx->db->config['table_prefix'].'easy2_files SET name = \''.htmlspecialchars($_POST['name'], ENT_QUOTES).'\', description = \''.htmlspecialchars($_POST['description'], ENT_QUOTES).'\', last_modified=NOW() WHERE id='.(int)$_GET['file_id'])) {
            $_SESSION['easy2suc'][] = $lng['updated'];
        } else {
            $_SESSION['easy2err'][] = $lng['update_err'];
        }

        header ('Location: '.$index.'&pid='.$parent_id);
        exit();
    }

    $res = mysql_query('SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.(int)$_GET['file_id']);
    $row = mysql_fetch_array($res, MYSQL_ASSOC);

    //$row['description'] = htmlspecialchars($row['description']);
    //$row['name'] = htmlspecialchars($row['name']);

    $name = $row['id'].substr($row['filename'], strrpos($row['filename'], '.'));

    $content .= '
<p>'.$lng['editing'].' '.$lng['file2'].' <b><a href="javascript:imPreview(\''.$gdir.$name.'\');void(0);">'.$row['filename'].'</a></b> ('.$row['comments'].')
&nbsp; &nbsp; &nbsp;
<a href="'.$index.'&pid='.$parent_id.'">'.$lng['back_to_fmanager'].'</a>
</p>

<table cellspacing="0" cellpadding="0" width="100%">
<tr>
<td valign="top">
<form name="list" action="" method="post">
<table cellspacing="0" cellpadding="0" class="aForm">
 <tr>
  <td><b>'.$lng['name'].':</b></td>
  <td><input name="name" type="text" value="'.$row['name'].'"></td>
 </tr>
 <tr>
  <td colspan="2"><b>'.$lng['description'].':</b><br /><textarea name="description" rows=3>'.$row['description'].'</textarea></td>
 </tr>
</table>
<input type="submit" value="'.$lng['save'].'">
<input type="button" value="'.$lng['cancel'].'" onclick="javascript:document.location.href=\''.$index.'&pid='.$parent_id.'\'">
</form>
</td>
<th width="205" valign="top">
<table cellspacing="0" cellpadding="0" width="200" height="200" style="margin-left:5px">
<tr><th class="imPreview" id="pElt"></th></tr>
</table>
</th>
</tr>
</table>';


    break;



// COMMENTS

case 'comments':

    if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
        $_SESSION['easy2err'][] = $id['id_err'];
        header ("Location: ".html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
        exit();
    }

    $res = mysql_query('SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.(int)$_GET['file_id']);
    $row = mysql_fetch_array($res, MYSQL_ASSOC);

    $name = $row['id'].substr($row['filename'], strrpos($row['filename'], '.'));

    $content .= '
<p>'.$lng['comments'].' '.$lng['file2'].' <b><a href="javascript:imPreview(\''.$gdir.$name.'\');void(0);">'.$row['filename'].'</a></b> ('.$row['comments'].')
&nbsp; &nbsp; &nbsp;
<img src="../assets/modules/easy2/icons/arrow_refresh.png" width="16" height="16" border="0" align="absmiddle">
<a href="'.$index.'&page=comments&file_id='.$_GET['file_id'].'&pid='.$parent_id.'">'.$lng['refresh'].'</a>
&nbsp; &nbsp; &nbsp;
<a href="'.$index.'&pid='.$parent_id.'">'.$lng['back_to_fmanager'].'</a>
</p>

<table cellspacing="0" cellpadding="0" width="100%">
<tr>
<td valign="top">
<form name="list" action="'.$index.'&act=delete_comments&file_id='.$_GET['file_id'].'" method="post">
<table width="100%" cellpadding="2" cellspacing="0" class="grid" style="margin-bottom:10px">
 <tr>
  <td width="20"><input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;"></td>
  <td width="150"><b>'.$lng['info'].'</b></td>
  <td><b>'.$lng['comments'].'</b></td>
 </tr>';

    $res = mysql_query('SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id='.(int)$_GET['file_id'].' ORDER BY id DESC');
    while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
        $content .= '
 <tr'.$cl[$i%2].'>
  <td><input name="comment[]" value="'.$l['id'].'" type="checkbox" style="border:0;padding:0"></td>
  <td valign="top">
   '.$lng['author'].': <b>'.$l['author'].'</b><br>
   '.$lng['date'].': '.$l['date_added']
   .(!empty($l['last_modified'])?'<br>'.$lng['modified'].': '.$l['last_modified']:'')
   .'</td>
  <td valign="top">'.htmlspecialchars($l['comment']).'</td>
 </tr>';
        $i++;
    }

    $content .= '
</table>
<input type="submit" value="'.$lng['delete'].'" name="delete" style="font-weight:bold;color:red" />
</form>
</td>
<th width="205" valign="top">
<table cellspacing="0" cellpadding="0" width="200" height="200" style="margin-left:5px">
<tr><th class="imPreview" id="pElt"></th></tr>
</table>
</th>
</tr>
</table>';


    break;

default:


if (empty($cpath)) {

    // MySQL Dir list
    $res = mysql_query('SELECT cat_id,cat_name FROM '.$modx->db->config['table_prefix'].'easy2_dirs WHERE parent_id='.$parent_id.' AND cat_visible = 1');

    $mdirs = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $mdirs[$l['cat_id']] = $l['cat_name'];
        }
    } else {
        $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
    }

    // MySQL File list
    $res = mysql_query('SELECT id,filename FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id='.$parent_id);
    $mfiles = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $mfiles[$l['id']] = $l['filename'];
        }
    } else {
        $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
    }

}


// Dir list
$dirs = glob('../'.$gdir.'*', GLOB_ONLYDIR);
$content = '
<p>'.$lng['path'].': <a href="'.$index.'&act=edit_dir&dir_id=1&name=" onclick="return editDir(this);"><img src="../assets/modules/easy2/icons/folder_edit.png" width="16" height="16" alt="'.$lng['edit'].'" title="'.$lng['edit'].'" align="absmiddle" border=0></a> <b>'.$path.'</b> &nbsp; &nbsp;
<img src="../assets/modules/easy2/icons/folder_add.png" width="16" height="16" border="0" align="absmiddle">
<a href="'.$index.'&act=create_dir&pid='.$parent_id.'&name=" onclick="return newDir(this);"><b>'.$lng['create_dir'].'</b></a>
 &nbsp; &nbsp;
<img src="../assets/modules/easy2/icons/arrow_refresh.png" width="16" height="16" border="0" align="absmiddle">
<a href="'.$index.'&act=clean_cache"><b>'.$lng['clean_cache'].'</b></a>
</p>
<table cellspacing="0" cellpadding="0" width="100%">
<tr>
<td valign="top">
<form name="list" action="'.$index.'&act=delete_checked&pid='.$parent_id.(!empty($cpath)?'&path='.$cpath:'').'" method="post">
<table width="100%" cellpadding="2" border="0" cellspacing="0" class="grid" style="margin-bottom:10px">
 <tr>
  <td width="25"><input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;"></td>
  <td width="20"> </td>
  <td><b>'.$lng['name'].'</b></td>
  <td width="80"><b>'.$lng['modified'].'</b></td>
  <td width="40"><b>'.$lng['size'].'</b></td>
  <td width="60" align="right"><b>'.$lng['options'].'</b></td>
 </tr>';
if ($dirs!=false) 
foreach ($dirs as $f) {

    $name = basename($f);
    $time = filemtime($f);
    $cnt = count_files($f);

    if ($name == '_thumbnails') continue;

    if (isset($mdirs[$name])) {
        $n = '<a href="'.$index.'&pid='.$name.'"><b>'.$mdirs[$name].'</b></a> ['.$name.']';
        $id = $name;
        unset($mdirs[$name]);
        $ext = '';
        $buttons = '<a href="'.$index.'&act=edit_dir&dir_id='.$id.'&name=" onclick="return editDir(this);"><img src="../assets/modules/easy2/icons/folder_edit.png" width="16" height="16" alt="'.$lng['edit'].'" title="'.$lng['edit'].'" border=0></a>';
    } else {
        $n = '<a href="'.$index.'&pid='.$parent_id.'&path='.(!empty($cpath)?$cpath:'').$name.'" style="color:gray"><b>'.$name.'</b></a>';
        $id = null;
        $ext = '_error';
        if (empty($cpath)) {
            $buttons = '<a href="'.$index.'&act=add_dir&dir_path='.$gdir.$name.'&pid='.$parent_id.'"><img src="../assets/modules/easy2/icons/folder_add.png" width="16" height="16" alt="'.$lng['add_to_db'].'" title="'.$lng['add_to_db'].'" border=0></a>';
        }
    }


    $content .= '
 <tr'.$cl[$i%2].'>
  <td><input name="dir['.(empty($id)?'d'.$i:$id).']" value="'.$gdir.$name.'" type="checkbox" style="border:0;padding:0"></td>
  <td><img src="../assets/modules/easy2/icons/folder'.$ext.'.png" width="16" height="16" border="0"></td>
  <td>'.$n.' ('.$cnt.')</td>
  <td>'.@date($e2g['mdate_format'], $time).'</td>
  <td>---</td>
  <td align="right" nowrap>
   '.$buttons.'
   <a href="'.$index.'&act=delete_dir&dir_path='.$gdir.$name.(empty($id)?'':'&dir_id='.$id).'" onclick="return confirmDeleteFolder();"><img src="../assets/modules/easy2/icons/delete.png" border="0" alt="'.$lng['delete'].'" title="'.$lng['delete'].'" /></a></td>
 </tr>';
   $i++;
}

// Deleted dirs
if (isset($mdirs) && count($mdirs) > 0) {
    foreach ($mdirs as $k => $v) {
        $content .= '
 <tr'.$cl[$i%2].'>
  <td><input name="dir['.$k.']" value="" type="checkbox" style="border:0;padding:0"></td>
  <td><img src="../assets/modules/easy2/icons/folder_delete.png" width="16" height="16" border="0"></td>
  <td><b style="color:red;"><u>'.$v.'</u></b> ['.$k.']</td>
  <td>---</td>
  <td>---</td>
  <td align="right"><a href="'.$index.'&act=delete_dir&dir_id='.$k.'" onclick="return confirmDeleteFolder();"><img src="../assets/modules/easy2/icons/delete.png" border="0" alt="'.$lng['delete'].'" title="'.$lng['delete'].'" /></a></td>
 </tr>';
        $i++;
    }
}


// File list
$files = glob('../'.$gdir.'*.*');
if ($files!=false) 
foreach ($files as $f) {

    $size = round(filesize($f)/1024);
    $time = filemtime($f);

    $name = basename($f);
    $pos = strrpos($name, '.');
    //$ext = substr($name, $pos+1);
    $ext = 'picture';
    $id = substr($name, 0, $pos);


    if (isset($mfiles[$id])) {
        $n = '<a href="javascript:imPreview(\''.$gdir.$name.'\');void(0);">'.$mfiles[$id].'</a> ['.$id.']';
        unset($mfiles[$id]);
        $buttons = '
  <a href="'.$index.'&page=comments&file_id='.$id.'&pid='.$parent_id.'"><img src="../assets/modules/easy2/icons/comments.png" width="16" height="16" alt="'.$lng['comments'].'" title="'.$lng['comments'].'" border=0></a>
  <a href="'.$index.'&page=edit_file&file_id='.$id.'&pid='.$parent_id.'"><img src="../assets/modules/easy2/icons/picture_edit.png" width="16" height="16" alt="'.$lng['edit'].'" title="'.$lng['edit'].'" border=0></a>';
    } else {
        $n = '<a href="javascript:imPreview(\''.$gdir.$name.'\');void(0);" style="color:gray"><b>'.$name.'</b></a>';
        $id = null;
        $ext .= '_error';
        if (empty($cpath)) {
            $buttons = '<a href="'.$index.'&act=add_file&file_path='.$gdir.$name.'&pid='.$parent_id.'"><img src="../assets/modules/easy2/icons/picture_add.png" width="16" height="16" alt="'.$lng['add_to_db'].'" title="'.$lng['add_to_db'].'" border=0></a>';
        }
    }

    $content .= '
 <tr'.$cl[$i%2].'>
  <td><input name="im['.(empty($id)?'f'.$i:$id).']" value="'.$gdir.$name.'" type="checkbox" style="border:0;padding:0"></td>
  <td><img src="../assets/modules/easy2/icons/'.$ext.'.png" width="16" height="16"></td>
  <td>'.$n.'</td>
  <td>'.@date($e2g['mdate_format'], $time).'</td>
  <td>'.$size.'Kb</td>
  <td align="right" nowrap>'.$buttons.'
  <a href="'.$index.'&act=delete_file&file_path='.$gdir.$name.(empty($id)?'':'&file_id='.$id).'" onclick="return confirmDelete();"><img src="../assets/modules/easy2/icons/delete.png" border="0" alt="'.$lng['delete'].'" title="'.$lng['delete'].'" /></a></td>
 </tr>';
    $i++;
}

// Deleted files
if (isset($mfiles) && count($mfiles) > 0) {
    foreach ($mfiles as $k => $v) {
        $p = strrpos($v, '.');
        $content .= '
 <tr'.$cl[$i%2].'>
  <td><input name="im['.$k.']" value="" type="checkbox" style="border:0;padding:0"></td>
  <td><img src="../assets/modules/easy2/icons/picture_delete.png" width="16" height="16" border="0"></td>
  <td><b style="color:red;"><u>'.$v.'</u></b> ['.$k.']</td>
  <td>---</td>
  <td>---</td>
  <td align="right" nowrap>
  <a href="'.$index.'&page=comments&file_id='.$k.'&pid='.$parent_id.'"><img src="../assets/modules/easy2/icons/comments.png" width="16" height="16" alt="'.$lng['comments'].'" title="'.$lng['comments'].'" border=0></a>
  <a href="'.$index.'&act=delete_file&file_id='.$k.'" onclick="return confirmDeleteFolder();"><img src="../assets/modules/easy2/icons/delete.png" border="0" alt="'.$lng['delete'].'" title="'.$lng['delete'].'" /></a></td>
 </tr>';
        $i++;
    }
}
$content .= '</table>
<input type="submit" value="'.$lng['delete'].'" name="delete" style="font-weight:bold;color:red" />

</form>
</td>
<th width="205" valign="top">
<table cellspacing="0" cellpadding="0" width="200" height="200" style="margin-left:5px">
<tr><th class="imPreview" id="pElt"></th></tr>
</table>
</th>
</tr>
</table>';

}

$suc = $err = '';
if (count($_SESSION['easy2err']) > 0) {
    $err = '<p class="warning">'.implode('<br />', $_SESSION['easy2err']).'</p>';
    $_SESSION['easy2err'] = array();
}

if (count($_SESSION['easy2suc']) > 0) {
    $suc = '<p class="success">'.implode('<br />', $_SESSION['easy2suc']).'</p>';
    $_SESSION['easy2suc'] = array();
}


$output = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>easy 2</title>
<link rel="stylesheet" type="text/css" href="media/style/' . $_t . '/style.css" />
<style type="text/css">
<!--
.aForm input {font:11px Tahoma; width:400px}
.aForm td {padding-left:5px}
.aForm {margin-bottom:10px}
.aForm textarea {font:11px Tahoma; width:477px}
.imPreview {border:1px solid #C4C4C4;background:white;padding:4px}
-->
</style>
<meta http-equiv="Content-Type" content="text/html; charset='.$lng['charset'].'" />
<script type="text/javascript" src="media/script/tabpane.js"></script>
<script type="text/javascript">

function confirmDelete() {
 return (confirm("'.$lng['delete_confirm'].'")) ? true:false;
}

function confirmDeleteFolder() {
 return (confirm("'.$lng['delete_folder_confirm'].'")) ? true:false;
}

function addField () {
 var im = document.getElementById("imFields");
 var di = document.createElement("DIV");
 var fi = document.getElementById("firstElt");
 di.innerHTML = \'<a href="#" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" style="color:red;text-decoration:none;"><b style="letter-spacing:4px"> &times; '.$lng['remove_field_btn'].'</b></a>\'+fi.innerHTML;
 im.appendChild(di);
 return true;
}

function newDir (a) {
 var f;
 f=window.prompt("'.$lng['enter_dirname'].':","");
 if (f) a.href+=f;
 return (f) ? true:false;
}

function editDir (a) {
 var f;
 f=window.prompt("'.$lng['enter_new_dirname'].':","");
 if (f) a.href+=f;
 return (f) ? true:false;
}

function uimPreview (imSrc) {

 if (!document.images) return false;

 var im = new Image();
 im.src = imSrc;

 //alert(im.width);
 //file:///C:/../test.jpg

 var w = im.width;
 var h = im.height;

 if ( w > 161 || h > 161 ) {
  ratio = w / h;

  if ( ratio <= 1 ) {
   h = 161;
   w = Math.round(161*ratio);
  } else {
   w = 161;
   h = Math.round(161/ratio);
  }
 }

 //alert(w+"x"+h+":"+ratio);

 var imBox = this.document.getElementById("imBox");
 if ( w == 0 || h == 0 ) {
  imBox.innerHTML = \''.$lng['uim_preview_err'].'\';
 } else {
  imBox.innerHTML = \'<img src="\'+imSrc+\'" width="\'+w+\'" height="\'+h+\'" />\';
 }
 return true;
}

function selectAll (check_var) {
 for (var i=0; i<document.forms["list"].elements.length; i++) {
  var e=document.forms["list"].elements[i];
  if (e.type == "checkbox") e.checked = check_var;
 }
}

function imPreview (imPath) {
 var pElt = this.document.getElementById("pElt");
 pElt.innerHTML = "<img src=\'../assets/modules/easy2/preview.easy2gallery.php?path="+imPath+"\'>";
}

</script>
</head>
<body>
<br />'.$err.$suc.'
<div class="sectionHeader">Easy 2 Gallery</div>

<div class="sectionBody">
 <div class="tab-pane" id="easy2Pane">
<script type="text/javascript">
 tpResources = new WebFXTabPane(document.getElementById("easy2Pane"));
</script>

  <div class="tab-page" id="imManager">
   <h2 class="tab">'.$lng['manager'].'</h2>
<script type="text/javascript">
 tpResources.addTabPage(document.getElementById("imManager"));
</script>
   '.$content.'
   <form action="'.$index.'&act=synchro" method="post"><input type="submit" value="'.$lng['synchro'].'"></form>
   </div>

  <div class="tab-page" id="addForm">
   <h2 class="tab">'.$lng['upload'].'</h2>
<script type="text/javascript">
 tpResources.addTabPage(document.getElementById("addForm"));
</script>
  <p>'.$lng['upload_dir'].': <b>'.$gdir.'</b></p>
  <form name="images" action="'.$index.'&act=upload&pid='.$parent_id.'" method="post" enctype="multipart/form-data">
  <div id="imFields">
   <div id="firstElt">
    <table cellspacing="0" cellpadding="2" class="aForm" height="165">
     <tr>
      <!--th width="165" rowspan="3" class="imPreview" id="imBox">&nbsp;</th-->
      <td width="70"><b>'.$lng['file'].':</b></td>
      <td><input name="img[]" type="file" onchange="//uimPreview(this.value)"></td>
     </tr>
     <tr>
      <td><b>'.$lng['name'].':</b></td>
      <td><input name="name[]" type="text"></td>
     </tr>
     <tr>
      <td colspan="2"><b>'.$lng['description'].':</b><br /><textarea name="description[]" rows=3></textarea></td>
     </tr>
    </table>
   </div>
  </div>
  <input type="submit" value="'.$lng['upload_btn'].'" name="upload_btn">
  <input type="button" value="'.$lng['add_field_btn'].'" onclick="javascript:addField(); void(0);">
  </form>
  <br>
  <form name="zipfile" action="'.$index.'&act=uploadzip&pid='.$parent_id.'" method="post" enctype="multipart/form-data">
  <table cellspacing="0" cellpadding="2">
  <tr><td><b>'.$lng['archive'].': </b></td><td><input name="zip" type="file"></td><td><input type="submit" value="'.$lng['upload_btn'].'"></td></tr>
  </table>
  </form>
  </div>

  <div class="tab-page" id="Config">
   <h2 class="tab">'.$lng['config'].'</h2>
<script type="text/javascript">
 tpResources.addTabPage(document.getElementById("Config"));
</script>
  <form action="'.$index.'&act=save_config" method="post">
  <table cellspacing="0" cellpadding="2" width="100%">
   <tr class="gridAltItem">
    <td width="12%"><b>'.$lng['path'].':</b></td>
    <td width="88%"><input name="dir" type="text" value="'.$e2g['dir'].'"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com1'].'</td>
   </tr>
   <tr>
    <td colspan="2"><br><b class="success" style="font-size:120%">'.$lng['newimgcfg'].'</b></td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['w'].':</b></td>
    <td><input name="maxw" type="text" value="'.$e2g['maxw'].'" size="4"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com2'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['h'].':</b></td>
    <td><input name="maxh" type="text" value="'.$e2g['maxh'].'" size="4"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com3'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['thq'].':</b></td>
    <td><input name="maxthq" type="text" value="'.$e2g['maxthq'].'" size="3"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com4'].'</td>
   </tr>
   <tr>
    <td colspan="2"><br><b class="success" style="font-size:120%">'.$lng['thumbscfg'].'</b></td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['w'].':</b></td>
    <td><input name="w" type="text" value="'.$e2g['w'].'" size="4"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com5'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['h'].':</b></td>
    <td><input name="h" type="text" value="'.$e2g['h'].'" size="4"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com6'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['thq'].':</b></td>
    <td><input name="thq" type="text" value="'.$e2g['thq'].'" size="3"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com7'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['glib'].':</b></td>
    <td><select size="1" name="glib">
      <option value="colorbox"'.(($e2g['glib']=='colorbox')?' selected':'').'>colorbox (jq)</option>
      <option value="fancybox"'.(($e2g['glib']=='fancybox')?' selected':'').'>fancybox (jq)</option>
      <option value="floatbox"'.(($e2g['glib']=='floatbox')?' selected':'').'>floatbox</option>
      <option value="highslide"'.(($e2g['glib']=='highslide')?' selected':'').'>highslide</option>
      <option value="lightwindow"'.(($e2g['glib']=='lightwindow')?' selected':'').'>lightwindow (pt)</option>
      <option value="shadowbox"'.(($e2g['glib']=='shadowbox')?' selected':'').'>shadowbox</option>
      <option value="slimbox"'.(($e2g['glib']=='slimbox')?' selected':'').'>slimbox (mt)</option>
      <option value="slimbox2"'.(($e2g['glib']=='slimbox2')?' selected':'').'>slimbox2 (jq)</option>
     </select></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com10'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['notables'].':</b></td>
    <td><input name="notables" type="checkbox"'.((isset($e2g['notables']) && $e2g['notables']==1)?' checked':'').' value="1" style="border:0"></td>
   </tr>

   <tr>
    <td colspan="2"><br><b class="success" style="font-size:120%">'.$lng['thumbcnt'].'</b></td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['name_len'].':</b></td>
    <td><input name="name_len" type="text" value="'.$e2g['name_len'].'" size="4"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com18'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['cat_name_len'].':</b></td>
    <td><input name="cat_name_len" type="text" value="'.$e2g['cat_name_len'].'" size="4"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com20'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['colls'].':</b></td>
    <td><input name="colls" type="text" value="'.$e2g['colls'].'" size="4"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com8'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['limit'].':</b></td>
    <td><input name="limit" type="text" value="'.$e2g['limit'].'" size="4"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com9'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['order'].':</b></td>
    <td>
      <select size="1" name="orderby">
       <option value="date_added"'.($e2g['orderby']=='date_added'?' selected':'').'>'.$lng['date_added'].'</option>
       <option value="last_modified"'.($e2g['orderby']=='last_modified'?' selected':'').'>'.$lng['last_modified'].'</option>
       <option value="comments"'.($e2g['orderby']=='comments'?' selected':'').'>'.$lng['comments_cnt'].'</option>
       <option value="filename"'.($e2g['orderby']=='filename'?' selected':'').'>'.$lng['filename'].'</option>
       <option value="name"'.($e2g['orderby']=='name'?' selected':'').'>'.$lng['name'].'</option>
       <option value="random"'.($e2g['orderby']=='random'?' selected':'').'>'.$lng['random'].'</option>
      </select>
      <select size="1" name="order">
       <option value="ASC"'.($e2g['order']=='ASC'?' selected':'').'>'.$lng['asc'].'</option>
       <option value="DESC"'.($e2g['order']=='DESC'?' selected':'').'>'.$lng['desc'].'</option>
      </select>
    </td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com16'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['order2'].':</b></td>
    <td>
      <select size="1" name="cat_orderby">
       <option value="cat_id"'.($e2g['cat_orderby']=='cat_id'?' selected':'').'>'.$lng['date_added'].'</option>
       <option value="cat_name"'.($e2g['cat_orderby']=='cat_name'?' selected':'').'>'.$lng['name'].'</option>
       <option value="random"'.($e2g['cat_orderby']=='random'?' selected':'').'>'.$lng['random'].'</option>
      </select>
      <select size="1" name="cat_order">
       <option value="ASC"'.($e2g['cat_order']=='ASC'?' selected':'').'>'.$lng['asc'].'</option>
       <option value="DESC"'.($e2g['cat_order']=='DESC'?' selected':'').'>'.$lng['desc'].'</option>
      </select>
    </td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com21'].'</td>
   </tr>

   <tr>
    <td colspan="2"><br><b class="success" style="font-size:120%">'.$lng['tpl'].'</b></td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['css'].':</b></td>
    <td><input name="css" type="text" value="'.$e2g['css'].'" size="70"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com17'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['gallery'].':</b></td>
    <td><input name="tpl" type="text" value="'.$e2g['tpl'].'" size="70"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com17'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['dir'].':</b></td>
    <td><input name="dir_tpl" type="text" value="'.$e2g['dir_tpl'].'" size="70"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com17'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['thumb'].':</b></td>
    <td><input name="thumb_tpl" type="text" value="'.$e2g['thumb_tpl'].'" size="70"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com17'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['thumb'].' RAND:</b></td>
    <td><input name="rand_tpl" type="text" value="'.$e2g['rand_tpl'].'" size="70"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com17'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['comments'].':</b></td>
    <td><input name="comments_tpl" type="text" value="'.$e2g['comments_tpl'].'" size="70"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com19'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['comments_row'].':</b></td>
    <td><input name="comments_row_tpl" type="text" value="'.$e2g['comments_row_tpl'].'" size="70"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com19'].'</td>
   </tr>


   <tr>
    <td colspan="2"><br><b class="success" style="font-size:120%">'.$lng['wm'].'</b></td>
   </tr>
   <tr>
    <td colspan="2"><b>
     <input name="ewm" type="radio" value="1"'.($e2g['ewm']==1?' checked':'').' style="border:0">
     '.$lng['on'].'
     <input name="ewm" type="radio" value="0"'.($e2g['ewm']==0?' checked':'').' style="border:0">
     '.$lng['off'].'</b></td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['type'].':</b></td>
    <td><select size="1" name="wmtype">
     <option value="text"'.($e2g['wmtype']=='text'?' selected':'').'>'.$lng['text'].'</option>
     <option value="image"'.($e2g['wmtype']=='image'?' selected':'').'>'.$lng['image'].'</option></select>
    </td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com12'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['wmt'].':</b></td>
    <td><input name="wmt" type="text" value="'.$e2g['wmt'].'"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com13'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['wmpos1'].':</b></td>
    <td><select size="1" name="wmpos1">
     <option value="1"'.($e2g['wmpos1']==1?' selected':'').'>'.$lng['pos1'].'</option>
     <option value="2"'.($e2g['wmpos1']==2?' selected':'').'>'.$lng['pos2'].'</option>
     <option value="3"'.($e2g['wmpos1']==3?' selected':'').'>'.$lng['pos3'].'</option></select>
    </td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com14'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['wmpos2'].':</b></td>
    <td><select size="1" name="wmpos2">
     <option value="1"'.($e2g['wmpos2']==1?' selected':'').'>'.$lng['pos4'].'</option>
     <option value="2"'.($e2g['wmpos2']==2?' selected':'').'>'.$lng['pos5'].'</option>
     <option value="3"'.($e2g['wmpos2']==3?' selected':'').'>'.$lng['pos6'].'</option></select>
    </td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com15'].'</td>
   </tr>

   <tr>
    <td colspan="2"><br><b class="success" style="font-size:120%">'.$lng['comments'].'</b></td>
   </tr>
   <tr>
    <td colspan="2"><b>
     <input name="ecm" type="radio" value="1"'.($e2g['ecm']==1?' checked':'').' style="border:0">
     '.$lng['on'].'
     <input name="ecm" type="radio" value="0"'.($e2g['ecm']==0?' checked':'').' style="border:0">
     '.$lng['off'].'</b></td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['ecl'].':</b></td>
    <td><input name="ecl" type="text" value="'.$e2g['ecl'].'" size="3"></td>
   </tr>
   <tr>
    <td colspan="2">'.$lng['cfg_com11'].'</td>
   </tr>
   <tr class="gridAltItem">
    <td><b>'.$lng['captcha'].':</b></td>
    <td><input name="captcha" type="checkbox"'.((isset($e2g['captcha']) && $e2g['captcha']==1)?' checked':'').' value="1" style="border:0"></td>
   </tr>
  </table>
  <br>
  <input type="submit" value="'.$lng['save'].'"> &nbsp; &nbsp; &nbsp;
  <input name="clean_cache" type="checkbox" value="1" style="border:0"> '.$lng['clean_cache'].'
  </form>
  </div>


  <div class="tab-page" id="Help">
   <h2 class="tab">'.$lng['help'].'</h2>
<script type="text/javascript">
 tpResources.addTabPage(document.getElementById("Help"));
</script>'.$lng['easyhelp'].'</div>

 </div>
</div>

</body>
</html>';

return $output;

function delete_all ($path) {

    $res = array('d'=>0, 'f'=>0, 'e'=>array());
    if (!is_dir($path)) return $res;

    $fs = glob($path.'*');
    if ($fs!=false) 
    foreach ($fs as $f) {
        if (is_file($f)) {

            if(@unlink($f)) $res['f']++;
            else $res['e'][] = 'Can not delete file: '.$f;

        } elseif (is_dir($f)) {
            $sres = delete_all($f.'/');

            $res['f'] += $sres['f'];
            $res['d'] += $sres['d'];
            $res['e'] = array_merge($res['e'], $sres['e']);
        }
    }
    if (count($res['e']) == 0 && @rmdir($path)) $res['d']++;
    else $res['e'][] = 'Can not delete directory: '.$f;
    return $res;
}

function add_all ($path, $pid, $cfg) {
    global $modx;

    require_once '../assets/modules/easy2/TTree.class.php';
    $tree = new TTree();
    $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';

    $name = htmlspecialchars(basename($path), ENT_QUOTES);
    if ( !($id = $tree->insert($name, $pid)) ) {
        $_SESSION['easy2err'][] = $tree->error;
        return false;
    }

    $npath = preg_replace('/\/[^\/]+$/i', "/$id", $path);
    rename($path, $npath);
    @chmod($npath, 0755);
    //die($path.'-'.$npath);

    if (!is_dir($npath)) {
        //die ($npath . ' dir not found!');
        return false;
    }

    $fs = glob($npath.'/*');
    if ($fs!=false) 
    foreach ($fs as $f) {
        if (is_dir($f)) {
            if (!add_all ($f, $id, $cfg)) return false;
        } else {
            $inf = getimagesize($f);
            if ($inf[2] > 3) continue;

            // RESIZE
            if (($cfg['maxw'] > 0 || $cfg['maxh'] > 0) && ($inf[0] > $cfg['maxw'] || $inf[1] > $cfg['maxh'])) {
                resize_img($f, $inf, $cfg['maxw'], $cfg['maxh'], $cfg['maxthq']);
            }

            $n = htmlspecialchars(basename($f), ENT_QUOTES);
            $s = filesize($f);

            $q = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files '
            . '(dir_id,filename,size,name,description,date_added) '
            . "VALUES($id,'$n',$s,'','',NOW())";

            if (!mysql_query($q)) {
                $_SESSION['easy2err'][] = $lng['add_file_err'].' "'.$n.'"';
                continue;
            }

            $new_name = preg_replace('/\/[^\/]+(\.[^\.]{1,4})$/i', "/".mysql_insert_id()."\\1", $f);
            rename($f, $new_name);
            @chmod($new_name, 0644);
        }
    }

    return true;
}

function resize_img ($f, $inf, $w, $h, $thq) {
    global $modx;

    // OPEN
    if ($inf[2] == 1) $im = imagecreatefromgif ($f);
    elseif ($inf[2] == 2) $im = imagecreatefromjpeg ($f);
    elseif ($inf[2] == 3) $im = imagecreatefrompng ($f);
    else return false;

    // RESIZE
    if ($inf[0] > $inf[1]) $h = round($inf[1] * $w / $inf[0]);
    else $w = round($inf[0] * $h / $inf[1]);

    // CREATE NEW IMG
    $pic = imagecreatetruecolor($w, $h);
    $bgc = imagecolorallocate($pic, 255, 255, 255);
    imagefill($pic, 0, 0, $bgc);
    imagecopyresampled($pic, $im, 0, 0, 0, 0, $w, $h, $inf[0], $inf[1]);

    // SAVE
    if ($inf[2] == 1) imagegif ($pic, $f);
    elseif ($inf[2] == 2) imagejpeg($pic, $f, $thq);
    elseif ($inf[2] == 3) imagepng ($pic, $f);

    @chmod($f, 0644);

    imagedestroy($pic);
    imagedestroy($im);

    return true;
}

function get_path ($id, $string = false) {
    global $modx;

    $result = array();

    $res = mysql_query('SELECT A.cat_id, A.cat_name FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '.$modx->db->config['table_prefix'].'easy2_dirs B WHERE B.cat_id='.$id.' AND B.cat_left BETWEEN A.cat_left AND A.cat_right ORDER BY A.cat_left');
    while ($l = mysql_fetch_row($res)) {
        $result[$l[0]] = $l[1];
    }

    if (empty($result)) return null;

    if ($string) {
        $result = implode('/', array_keys($result)).'/';
    }

    return $result;
}

function count_files ($path) {
    $cnt = 0;
    if (glob($path.'/*.*')!=false) $cnt = count(glob($path.'/*.*'));

    $sd = glob($path.'/*');
    if ($sd!=false) 
    foreach(glob($path.'/*') as $d) {
        $cnt += count_files($d);
    }

    return $cnt;
}

function error_handler ($errno, $errmsg, $filename, $linenum, $vars) {
   echo '<p>Error '.$errno.': '.$errmsg.'<br>File: '.$filename.' <b>Line:'.$linenum.'</b></p>';
}

function add_file ($f, $pid, $cfg) {
    global $modx;

    $inf = getimagesize($f);
    if ($inf[2] <= 3 && is_numeric($pid)) {

        // RESIZE
        if (($cfg['maxw'] > 0 || $cfg['maxh'] > 0) && ($inf[0] > $cfg['maxw'] || $inf[1] > $cfg['maxh'])) {
            resize_img($f, $inf, $cfg['maxw'], $cfg['maxh'], $cfg['maxthq']);
        }

        $n = htmlspecialchars(basename($f), ENT_QUOTES);
        $s = filesize($f);

        $q = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files '
           . '(dir_id,filename,size,name,description,date_added) '
           . "VALUES(".$pid.",'$n',$s,'','',NOW())";
        if (mysql_query($q)) {
            $new_name = preg_replace('/\/[^\/]+(\.[^\.]{1,4})$/i', "/".mysql_insert_id()."\\1", $f);
            rename($f, $new_name);
            @chmod($new_name, 0644);
        } else {
            $_SESSION['easy2err'][] = $lng['add_file_err'];
            $_SESSION['easy2err'][] = mysql_error();
            return false;
        }

    } else {
        $_SESSION['easy2err'][] = $lng['add_file_err'];
        return false;
    }
    return true;
}
function synchro ($path, $pid, $cfg) {
    global $modx;

    // MySQL Dir list
    $res = mysql_query('SELECT cat_id,cat_name FROM '.$modx->db->config['table_prefix'].'easy2_dirs WHERE parent_id='.$pid.' AND cat_visible = 1');
    $mdirs = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $mdirs[$l['cat_id']] = $l['cat_name'];
        }
    } else {
        $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
        return false;
    }
    // MySQL File list
    $res = mysql_query('SELECT id,filename FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id='.$pid);
    $mfiles = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $mfiles[$l['id']] = $l['filename'];
        }
    } else {
        $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
        return false;
    }
    $fs = glob($path.'/*');
    if ($fs!=false) 
    foreach ($fs as $f) {
        if (is_dir($f)) {
            $name = basename($f);
            if ($name == '_thumbnails') continue;
            if (isset($mdirs[$name])) {
                if (!synchro($f, $name, $cfg)) return false;
                unset($mdirs[$name]);
            } else {
                if (!add_all($f, $pid, $cfg)) return false;
            }
        } else {
            $name = basename($f);
            $pos = strrpos($name, '.');
            $id = substr($name, 0, $pos);
            if (isset($mfiles[$id])) {
                unset($mfiles[$id]);
            } else {
                if (!add_file($f, $pid, $cfg)) return false;
            }
        }
    }
    // Deleted dirs
    if (isset($mdirs) && count($mdirs) > 0) {
        require_once '../assets/modules/easy2/TTree.class.php';
        $tree = new TTree();
        $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
        foreach($mdirs as $key => $value) {
            $ids = $tree->delete($key);
            $files_id = array();
            $res = mysql_query('SELECT id FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id IN('.implode(',', $ids).')');
            if (!$res) {
                $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
                return false;
            }
            while ($l = mysql_fetch_row($res)) {
                $files_id[] = $l[0];
            }
            if (count($files_id) > 0) {
                mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id IN('.implode(',', $files_id).')');
            }
            @mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id IN('.implode(',', $ids).')');
        }
    }
    // Deleted files
    if (isset($mfiles) && count($mfiles) > 0) {
        $mfiles_array = array();
        foreach($mfiles as $key => $value) {
            $mfiles_array[] = $key;
        }
        $db_res = mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id IN('.implode(',', $mfiles_array).')');
        @mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id IN('.implode(',', $mfiles_array).')');
        if (!$db_res) {
            $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
            return false;
        }
    }
    return true;
}
