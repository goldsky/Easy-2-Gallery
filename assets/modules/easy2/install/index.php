<?php

if (file_exists('../assets/modules/easy2/install/langs/'.$modx->config['manager_language'].'.inc.php')) {
    include '../assets/modules/easy2/install/langs/'.$modx->config['manager_language'].'.inc.php';
} else {
    include '../assets/modules/easy2/install/langs/english.inc.php';
}

if (isset($_GET['p']) && $_GET['p'] == 'del_inst_dir') {

    delete_all (MODX_BASE_PATH.'assets/modules/easy2/install/');
    header('Location: '.$index);
    exit();

} elseif (!empty($_POST)) {

    $ms = array('suc' => array(), 'err' => array());

    // DIRS
    $_POST['path'] = str_replace('../', '', $_POST['path']);
    if (empty($_POST['path'])) {
        $_SESSION['easy2err'][] = $lngi['empty_dir'];
        chref($index);
    }

    // CHECK/CREATE DIRS
    $_POST['path'] = preg_replace('/^\/?(.+)$/', '\\1', $_POST['path']);
    $dirs = explode('/', substr($_POST['path'], 0, -1));
    $npath = '..';
    foreach ($dirs as $dir) {
        $npath .= '/'.$dir;
        if (is_dir($npath) || empty($dir)) continue;

        if(!mkdir($npath, 0777)) {
            $_SESSION['easy2err'][] = $lngi['create_dir_err'].' "'.$npath."'";
            chref($index);
        }
    }

    $_SESSION['easy2dir'] = substr($npath, 3).'/';
    $_SESSION['easy2suc'][] = $lngi['dir_created'];

    // CHECK/CREATE TABLES
    // mysql_list_fields()
    // GET All Tables

    $res = mysql_list_tables(str_replace('`', '', $GLOBALS['dbase']));
    $tab = array();
    while ($row = mysql_fetch_row($res)) {
        $tab[$row[0]] = $row[0];
    }

    // easy2_dirs CHECK
    if (isset($tab['easy2_dirs'])) {
        if (!mysql_query('RENAME TABLE easy2_dirs TO '.$GLOBALS['table_prefix'].'easy2_dirs')) {
            $_SESSION['easy2err'][] = $lngi['table'].' easy2_dirs '.$lngi['rename_err'].'<br />'.mysql_error();
            chref($index);
        }
    }

    // easy2_dirs CREATE
    if (mysql_query('CREATE TABLE IF NOT EXISTS '.$GLOBALS['table_prefix'].'easy2_dirs (
parent_id int(10) unsigned NOT NULL default \'0\',
cat_id int(10) unsigned NOT NULL auto_increment,
cat_left int(10) unsigned NOT NULL default \'0\',
cat_right int(10) unsigned NOT NULL default \'0\',
cat_level int(10) unsigned NOT NULL default \'0\',
cat_name varchar(255) NOT NULL default \'\',
cat_visible tinyint(4) NOT NULL default \'1\',
PRIMARY KEY  (cat_id),
KEY cat_left (cat_left)
) TYPE=MyISAM')) {
        $_SESSION['easy2suc'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_dirs '.$lngi['created'];
    } else {
        $_SESSION['easy2err'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_dirs '.$lngi['create_err'].'<br />'.mysql_error();
        chref($index);
    }

    $res = mysql_query('SELECT cat_right FROM '.$GLOBALS['table_prefix'].'easy2_dirs WHERE cat_id=1');
    if (mysql_num_rows($res) == 0) {

        if (mysql_query('INSERT INTO '.$GLOBALS['table_prefix']."easy2_dirs VALUES (0,1,1,2,0,'Easy 2',1)")) {
            $_SESSION['easy2suc'][] = $lngi['data'].' '.$GLOBALS['table_prefix'].'easy2_dirs '.$lngi['added'];
        } else {
            $_SESSION['easy2err'][] = $lngi['data'].' '.$GLOBALS['table_prefix'].'easy2_dirs '.$lngi['add_err'].'<br />'.mysql_error();
            chref($index);
        }
    }
    //list($r) = mysql_fetch_row($res);

    // easy2_comments CHECK
    if (isset($tab['easy2_comments'])) {
        if (!mysql_query('RENAME TABLE easy2_comments TO '.$GLOBALS['table_prefix'].'easy2_comments')) {
            $_SESSION['easy2err'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_comments '.$lngi['rename_err'].'<br />'.mysql_error();
            chref($index);
        }
    }

    // easy2_comments CREATE
    if (mysql_query('CREATE TABLE IF NOT EXISTS '.$GLOBALS['table_prefix'].'easy2_comments (
id int(10) unsigned NOT NULL auto_increment,
file_id int(10) unsigned NOT NULL default \'0\',
author varchar(64) NOT NULL default \'\',
email varchar(64) NOT NULL default \'\',
comment text NOT NULL,
date_added datetime NOT NULL default \'0000-00-00 00:00:00\',
last_modified datetime default NULL,
status tinyint(3) unsigned NOT NULL default \'1\',
PRIMARY KEY  (id),
KEY file_id (file_id)
) TYPE=MyISAM')) {
        $_SESSION['easy2suc'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_comments '.$lngi['created'];
    } else {
        $_SESSION['easy2err'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_comments '.$lngi['create_err'].'<br />'.mysql_error();
        chref($index);
    }

    // easy2_comments CHECK
    if (isset($tab['easy2_files'])) {
        if (!mysql_query('RENAME TABLE easy2_files TO '.$GLOBALS['table_prefix'].'easy2_files')) {
            $_SESSION['easy2err'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_files '.$lngi['rename_err'].'<br />'.mysql_error();
            chref($index);
        }
    }

    // easy2_comments CREATE
    if (mysql_query('CREATE TABLE IF NOT EXISTS '.$GLOBALS['table_prefix'].'easy2_files (
id int(10) unsigned NOT NULL auto_increment,
dir_id int(10) unsigned NOT NULL default \'0\',
filename varchar(255) NOT NULL default \'\',
size varchar(32) NOT NULL default \'\',
name varchar(255) NOT NULL default \'\',
description text NOT NULL,
date_added datetime NOT NULL default \'0000-00-00 00:00:00\',
last_modified datetime default NULL,
comments int(10) unsigned NOT NULL default \'0\',
status tinyint(3) unsigned NOT NULL default \'1\',
PRIMARY KEY  (id)
) TYPE=MyISAM')) {
        $_SESSION['easy2suc'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_files '.$lngi['created'];
    } else {
        $_SESSION['easy2err'][] = $lngi['table'].' '.$GLOBALS['table_prefix'].'easy2_files '.$lngi['create_err'].'<br />'.mysql_error();
        chref($index);
    }

    // MODULE

    // $mod = file_get_contents('../assets/modules/easy2/install/module.easy2gallery.php'); // goldsky -- use file instead
    $mod = 'include_once MODX_BASE_PATH . \'assets/modules/easy2/index.php\';';
    // $res = mysql_query('UPDATE '.$GLOBALS['table_prefix'].'site_modules SET modulecode = \''.mysql_escape_string($mod).'\' WHERE name =\'easy2\''); // goldsky -- unleashed the name dependence
    $res = mysql_query('UPDATE '.$GLOBALS['table_prefix'].'site_modules SET modulecode = \''.mysql_escape_string($mod).'\' WHERE id =\''.$_GET['id'].'\'');
    if ($res) {
        $_SESSION['easy2suc'][] = $lngi['mod_updated'];
    } else {
        $_SESSION['easy2err'][] = $lngi['mod_update_err'].'<br />'.mysql_error();
        chref($index);
    }

    // SNIPPET

    $snippet = 'include MODX_BASE_PATH . \'assets/modules/easy2/snippet.easy2gallery.php\';';

    $res = mysql_query('SELECT id FROM '.$GLOBALS['table_prefix'].'site_snippets WHERE name =\'easy2\'');
    if (mysql_num_rows($res) > 0) {
        $sql = 'UPDATE '.$GLOBALS['table_prefix'].'site_snippets SET snippet=\''.mysql_escape_string($snippet).'\' WHERE name =\'easy2\' LIMIT 1';
        if (mysql_query($sql)) {
            $_SESSION['easy2suc'][] = $lngi['snippet_updated'];
        } else {
            $_SESSION['easy2err'][] = $lngi['snippet_update_err'];
            chref($index);
        }
    } else {
        $sql = "INSERT INTO ".$GLOBALS['table_prefix']."site_snippets "
                ."(name, description, snippet, moduleguid, locked, properties, category) "
                ."VALUES('easy2', 'Easy 2 Gallery', '".mysql_escape_string($snippet)."', '', '1','', '0');";

        if (mysql_query($sql)) {
            $_SESSION['easy2suc'][] = $lngi['snippet_added'];
        } else {
            $_SESSION['easy2err'][] = $lngi['snippet_add_err'];
            chref($index);
        }
    }

    /*
     * goldsky -- add the file's/folder's names restoration from the previous installation version.
    */
    if(restore( MODX_BASE_PATH . $e2g['dir'],1)) {
        $_SESSION['easy2suc'][] = $lngi['restore_suc'];
    } else {
        $_SESSION['easy2err'][] = $lngi['restore_err'];
        chref($index);
    }

    $_SESSION['easy2suc']['success'] = '<br /><br /><br />'.$lngi['success']
            .'<br /><br /><input type="button" value="'.$lngi['del_inst_dir'].'" onclick="document.location.href=\''.$index.'&p=del_inst_dir\'">';

    // SAVE DIR
    if (empty($e2g)) {
        require MODX_BASE_PATH.'assets/modules/easy2/config.easy2gallery.php';
    }

    $e2g['dir'] = $_SESSION['easy2dir'];
    $c = "<?php\r\n\$e2g = array (\r\n";
    foreach($e2g as $k => $v) {
        $c .= "'$k' => ".(is_numeric($v)?$v:"'$v'").",\r\n";
    }
    $c .= ");\r\n?>";

    $f = fopen( MODX_BASE_PATH.'assets/modules/easy2/config.easy2gallery.php', 'w' );
    fwrite($f, $c);
    fclose($f);

    unset($_SESSION['easy2dir']);

    chref($index);

} else {
    $content = '<br />
<form method="post">
<table cellspacing="0" cellpadding="0">
<tr>
<td width="50"><b>'.$lngi['path'].':</b></td>
<td><input name="path" type="text" style="width:100%" value="assets/gallery/"></td>
</tr>
</table>
'.$lngi['comment1'].'
<p><br />'.$lngi['comment'].'</p><br />
<input type="submit" value="'.$lngi['ok'].'">
</form>';
}

$suc = $err = '';
if (count($_SESSION['easy2err']) > 0) {
    $err = '<p class="warning">'.implode('<br />', $_SESSION['easy2err']).'</p>';
    $_SESSION['easy2err'] = array();
    $err .= '<br /><br /><a href="#" onclick="document.location.href=\''.$index.'\'"><b>'.$lngi['back'].'</b></a>';
}
if (count($_SESSION['easy2suc']) > 0) {
    $suc = '<p class="success">'.implode('<br />', $_SESSION['easy2suc']).'</p>';
    $_SESSION['easy2suc'] = array();
}
if (!empty($suc) || !empty($err)) $content = $suc.$err;

$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Easy 2 Gallery '.E2G_VERSION.' installation</title>
<link rel="stylesheet" type="text/css" href="media/style/' . $_t . '/style.css" />
<script type="text/javascript" src="media/script/tabpane.js"></script>
</head>
<body>
<div class="sectionHeader">Easy 2 Gallery '.E2G_VERSION.' installation</div>
<div class="sectionBody">
 <div class="tab-pane" id="easy2Pane">
<script type="text/javascript">
 tpResources = new WebFXTabPane(document.getElementById("easy2Pane"));
</script>
  <div class="tab-page" id="install">
   <h2 class="tab">'.$lng['install'].'</h2>
<script type="text/javascript">
 tpResources.addTabPage(document.getElementById("install"));
</script>
   '.$content.'</div>
 </div>
</div>
</body>
</html>';

echo $out;

function chref ($href) {
    $_SESSION['easy2ms'] = $ms;
    header('Location: '.$href);
    exit();
}

/*
 * function restore()
 * To restore file's and folder's name of previous version's installation
 * @param string $path path to file or folder
 * @param int $pid current parent ID
*/

function restore ($path, $pid) {
    global $modx;
    if (!include MODX_BASE_PATH.'assets/modules/easy2/config.easy2gallery.php') die('289');
    $time_start = microtime(TRUE);
    /*
     * STORE variable arrays for synchronizing comparison
    */
    // MySQL Dir list
    $res = mysql_query('SELECT cat_id,cat_name,parent_id FROM '.$GLOBALS['table_prefix'].'easy2_dirs WHERE parent_id='.$pid);

    $odirs = array();
    $ndirs = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $odirs[$l['cat_id']]['id'] = $l['cat_id'];
            $odirs[$l['cat_id']]['name'] = $l['cat_name'];
            // goldsky -- switch the array parameter after renaming
            $ndirs[$l['cat_name']]['id'] = $l['cat_id'];
            $ndirs[$l['cat_name']]['name'] = $l['cat_name'];
            $ndirs[$l['cat_name']]['parent_id'] = $l['parent_id'];
        }
    } else {
        $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
        return FALSE;
    }

    // MySQL File list
    $res = mysql_query('SELECT id,filename,size FROM ' . $GLOBALS['table_prefix'] . 'easy2_files WHERE dir_id='.$pid);

    $ofiles = array();
    $nfiles = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $ofiles[$l['id']]['id'] = $l['id'];
            $ofiles[$l['id']]['name'] = $l['filename'];
            $ofiles[$l['id']]['size'] = $l['size'];
            // goldsky -- switch the array parameter after renaming
            $nfiles[$l['filename']]['id'] = $l['id'];
            $nfiles[$l['filename']]['name'] = $l['filename'];
            $nfiles[$l['filename']]['size'] = $l['size'];
        }
    } else {
        $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
        return FALSE;
    }

    // goldsky -- adding some files exclusion
    $excludefiles = array(
            $path.'index.htm',
            $path.'index.html',
            $path.'Thumbs.db',
            $path.'index.php'
    );

    $fs = array_diff( glob( $path.'*' ) , $excludefiles ); // goldsky -- do not add a slash again!

    /*
     * READ the real physical objects, renaming them back
    */
    if ( FALSE !== $fs ) {
        // goldsky -- alter the maximum execution time
        set_time_limit(0);

        foreach ($fs as $f) {
            // goldsky -- adds output buffer to avoid PHP's memory limit
            ob_start();

            // to skip ROOT folder
            $basee2gdir = basename(MODX_BASE_PATH.$e2g['dir']);

            /*
             * goldsky -- restore FOLDER's name of previous version's process here!
            */
            $name = basename($f);
            $obasename = $odirs[$name]['name'];
            $nbasename = $ndirs[$name]['name'];
            if ($name == '_thumbnails' && $name != $basee2gdir && $name != '' ) continue;

//            if ( $name == $odirs[$name]['id']
//                    && $name != $odirs[$name]['name']
//                    && is_validfolder($f)
//            ) {
            if ( is_validfolder($f) ) {
                if (isset($odirs[$name])) {
                    $nf = MODX_BASE_PATH.$e2g['dir'].$obasename;
                    if (!rename( $f, $nf )) $_SESSION['easy2err'][] = "Could not rename path ".$f;
                    else {
                        @chmod( $nf, 0755 );
                        $_SESSION['easy2suc'][] = 'Successful on renaming path " '.$name.' " to be " '.$obasename.' "';
                    }

                    $nf2 = MODX_BASE_PATH.$e2g['dir'].$odirs[$name]['name'];
                    restore_all( $nf2.'/', $odirs[$name]['id'] );

                    unset($odirs[$name]);
                }
                if (isset($ndirs[$name])) {
                    unset($ndirs[$name]);
                }
            }
//            }
            if (is_validfile($f)) {
                $fbasename = basename($f);

                $s = filesize($f);
                // goldsky -- $ext returns '.jpg', including the dot
                $ext = substr($fbasename, strrpos((string)$fbasename, '.'));
                $trimmedname = rtrim($fbasename, $ext); // goldsky -- split the extension, to gain the file's ID
                $nfilename = MODX_BASE_PATH.$e2g['dir'].$ofiles[$trimmedname]['name'];

                // goldsky -- if it belongs to an existing one, skip it
                if ( $fbasename == $nfiles[$trimmedname]['name'] ) {
                    continue;
                }
                // goldsky -- as a PREVIOUS NAME file in the current directory only
                elseif ( $fbasename == $ofiles[$trimmedname]['id'].$ext
                        && $fbasename != $ofiles[$trimmedname]['name']
                        && $s == $ofiles[$trimmedname]['size']
                ) {
                    if (!rename($f, $nfilename )) $_SESSION['easy2err'][] = $lngi['rename_file_err'];
                    else {
                        @chmod($nfilename, 0644);
                        $_SESSION['easy2suc'][] = 'Successful on renaming file " '.$fbasename.' " to be " '.$ofiles[$trimmedname]['name'].' "';
                    }
                }
            } // if (is_validfile($f))

            /*
             * goldsky -- File/folder may exists, but NOT a valid folder, NOT a valid file,
             * probably has an unallowed extension or strange characters.
            */
            else continue;
            // goldsky -- adds output buffer to avoid PHP's memory limit
            ob_end_clean();
        } // foreach ($fs as $f)
    } // if ( FALSE !== $fs )

    $time_end = microtime(TRUE);
    $time = $time_end - $time_start;
    $_SESSION['easy2suc'][] = "Restored $path in $time seconds\n";

    return TRUE;
}

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

/*
 * goldsky -- use this for debuging with: die(is_validfile($filename, 1));
 *
*/
function is_validfile ( $filename, $debug=0 ) {
    $f = basename($filename);
    if (is_validfolder($filename)) {
        if ($debug==1) {
            return '<b style="color:red;">'.$filename.'</b> is not a file, it\'s a valid folder.';
        }
        else return FALSE;
    }
    elseif ( $f != '' && !is_validfolder($filename) ) {
        if (file_exists($filename)) {
            $size = getimagesize($filename);
            $fp = fopen($filename, "rb");
            $allowedext = array(
                    'image/jpeg' => TRUE,
                    'image/gif' => TRUE,
                    'image/png' => TRUE
            );
            if ( $allowedext[$size["mime"]] && $fp ) {
                if ($debug==1) {
                    $fileinfo = 'Filename <b style="color:red;">'.$f.'</b> is a valid image file: '.$size["mime"].' - '.$size[3];
                }
                else return TRUE;
            } else {
                if ($debug==1) $fileinfo = 'Filename <b style="color:red;">'.$f.'</b> is an invalid image file: '.$size[2].' - '.$size[3];
                else return FALSE;
            }
        }
        else {
            if ($debug==1) $fileinfo .= 'Filename <b style="color:red;">'.$f.'</b> is NOT exists.<br />';
            else return FALSE;
        }
        if ($debug==1) return $fileinfo;
        else return TRUE;
    }
    else continue;
}

/*
 * goldsky -- use this for debuging with: die(is_validfolder($foldername, 1));
*/
function is_validfolder($foldername, $debug=0) {
    $openfolder = @opendir($foldername);
    if (!$openfolder) {
        if ($debug==1) return '<b style="color:red;">'.$foldername.'</b> is NOT a valid folder.';
        else return FALSE;
    } else {
        if ($debug==1) {
            echo '<h2>' . $foldername . '</h2>';
            echo '<ul>';
            $file = array();
            while ( ( FALSE !== ( $file = readdir ( $openfolder ) ) ) ) {
                if ( $file != "." && $file != ".." ) {
                    if (filetype($file)=='dir') {
                        echo '<li>dir: <b style="color:green;">'.$file.'</b></li>';
                    }
                    else echo "<li> $file </li>";
                    clearstatcache();
                }
            }
            echo '</ul>';
        }
        closedir ( $openfolder );
    }
    if ($debug==1) return '<br /><b style="color:red;">'.$foldername.'</b> is a valid folder.';
    else return TRUE;
}


/*
 * function restore_all()
 * To LOOP restore file's and folder's name of previous version's installation
 * @param string $path path to file or folder
 * @param int $pid current parent ID
*/

function restore_all ($path, $pid) {
    global $modx;

    /*
     * STORE variable arrays for synchronizing comparison
    */
    // MySQL Dir list
    $res = mysql_query('SELECT cat_id,cat_name FROM '.$GLOBALS['table_prefix'].'easy2_dirs WHERE parent_id='.$pid);

    $odirs = array();
    $ndirs = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $odirs[$l['cat_id']]['id'] = $l['cat_id'];
            $odirs[$l['cat_id']]['name'] = $l['cat_name'];
            // goldsky -- switch the array parameter after renaming
            $ndirs[$l['cat_name']]['id'] = $l['cat_id'];
            $ndirs[$l['cat_name']]['name'] = $l['cat_name'];
        }
    } else {
        $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
        return FALSE;
    }

    // MySQL File list
    $res = mysql_query('SELECT id,filename,size FROM ' . $GLOBALS['table_prefix'] . 'easy2_files WHERE dir_id='.$pid);

    $ofiles = array();
    $nfiles = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $ofiles[$l['id']]['id'] = $l['id'];
            $ofiles[$l['id']]['name'] = $l['filename'];
            $ofiles[$l['id']]['size'] = $l['size'];
            // goldsky -- switch the array parameter after renaming
            $nfiles[$l['filename']]['id'] = $l['id'];
            $nfiles[$l['filename']]['name'] = $l['filename'];
            $nfiles[$l['filename']]['size'] = $l['size'];
        }
    } else {
        $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
        return FALSE;
    }

    if (!is_validfolder($path)) {
        return FALSE;
    }
    // goldsky -- add some files exclusion
    $excludefiles = array(
            $path.'index.htm',
            $path.'index.html',
            $path.'Thumbs.db',
            $path.'index.php'
    );
    $fs = array_diff(glob($path.'*'), $excludefiles);
    if ( FALSE !== $fs )
    // goldsky -- alter the maximum execution time
        set_time_limit(0);

    foreach ($fs as $f) {
        // goldsky -- adds output buffer to avoid PHP's memory limit
        ob_start();

        $name = basename($f);
        $obasename = $odirs[$name]['name'];
        $nbasename = $ndirs[$name]['name'];

        if (is_validfolder($f)) {
            if (isset($odirs[$name])) {
                $nf = $path.$obasename;
                if (!rename( $f, $nf )) $_SESSION['easy2err'][] = "Could not rename path ".$f;
                else {
                    @chmod( $nf, 0755 );
                    $_SESSION['easy2suc'][] = 'Successful on renaming path " '.$name.' " to be " '.$obasename.' "';
                }

                $nf = $path.$odirs[$name]['name'];
                if (!restore_all( $nf.'/', $odirs[$name]['id'] )) return FALSE;

                unset($odirs[$name]);
            }
            elseif (isset($ndirs[$name])) {
                unset($ndirs[$name]);
            }
            else continue;
        }
        elseif (is_validfile($f)) {
            $fbasename = basename($f);

            $s = filesize($f);
            // goldsky -- $ext returns '.jpg', including the dot
            $ext = substr($fbasename, strrpos((string)$fbasename, '.'));
            $trimmedname = rtrim($fbasename, $ext); // goldsky -- split the extension, to gain the file's ID
            $nfilename = $path.$ofiles[$trimmedname]['name'];
            // goldsky -- if it belongs to an existing one, skip it
            if ( $fbasename == $nfiles[$trimmedname]['name'] ) {
                continue;
            }
            if (!rename($f, $nfilename )) $_SESSION['easy2err'][] = $lngi['rename_file_err'];
            else {
                @chmod($nfilename, 0644);
                $_SESSION['easy2suc'][] = 'Successful on renaming file " '.$fbasename.' " to be " '.$ofiles[$trimmedname]['name'].' "';
            }
        }
        // goldsky -- adds output buffer to avoid PHP's memory limit
        ob_end_clean();
    }
    return TRUE;
}

?>