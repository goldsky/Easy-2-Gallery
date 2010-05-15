<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

/**
 * EASY 2 GALLERY
 * Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
 */

class e2g_mod {
    private $e2gmod_cl;
    public $e2g;
    public $lng;

    public function  __construct($e2gmod_cl, $e2g, $lng) {
        $this->e2gmod_cl = $e2gmod_cl;
        $this->e2g = $e2g;
        $this->lng = $lng;
        $this->_explore($e2gmod_cl, $e2g, $lng);
        $this->_echo_memory_usage();
    }

    private function _explore($e2gmod_cl, $e2g, $lng) {
        global $modx;
        $e2g['mdate_format'] = 'd-m-y H:i';
        $parent_id = $this->e2gmod_cl['parent_id'];
        $index = $this->e2gmod_cl['index'];
        $gdir = $this->e2gmod_cl['gdir'];

        // CREATE PATH
        $p = $this->_path_to($parent_id);
        foreach ($p as $k => $v) {
            $path .= '<a href="' . $index . '&pid=' . $k . '">' . $v . '</a>/';
        }
        unset ($p[1]);
        if (!empty($p)) $gdir .= implode( '/', $p ) . '/';

        if (!empty($_GET['path'])) {
            $dirs = str_replace('../', '', $_GET['path']);
            $dirs = explode('/', $dirs);
            $cpath = '';
            foreach ($dirs as $v) {
                if (empty($v)) continue;
                $cpath .= $v . '/';
                $path .= '<a href="' . $index . '&pid=' . $parent_id . '&path=' . $cpath . '">' . $v . '</a>';
            }
            $gdir .= $cpath;
        }
        $path .= '</b>';

        // decoding UTF-8 the path, as the default URL address
        $gdir = utf8_decode($gdir);

        /*
         * GALLERY ACTIONS
        */
        $act = empty($_GET['act']) ? '' : $_GET['act'];
        switch ($act) {
            case 'synchro':
                if($this->_synchro( MODX_BASE_PATH . $e2g['dir'],1,$e2g)) {
                    $_SESSION['easy2suc'][] = $lng['synchro_suc'];
                } else {
                    $_SESSION['easy2err'][] = $lng['synchro_err'];
                }
                $res = $this->_delete_all ( MODX_BASE_PATH . $e2g['dir'].'_thumbnails/');
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
                if($_FILES['zip']['error']==0 && $_FILES['zip']['size']>0) {
                    include_once E2G_MODULE_PATH . 'classes/pclzip.lib.php';
                    $zip = new PclZip($_FILES['zip']['tmp_name']);
                    if (($list = $zip->listContent()) == 0) {
                        $_SESSION['easy2err'][] = "Error : ".$zip->errorInfo(TRUE);
                    } else {
                        $j=sizeof($list);
                    }
                    /*
                     * PclZip PCLZIP_CB_PRE_EXTRACT to convert filenames while extracting the content.
                    */
                    if (!function_exists('zipPreExtractCallBack')) {
                        function zipPreExtractCallBack($p_event, &$p_header) {
                            $info = pathinfo($p_header['filename']);
                            // limit the unzipped images
                            if ($info['extension'] == 'jpeg' || $info['extension'] == 'jpg' || $info['extension'] == 'gif' || $info['extension'] == 'png') {
                                $get_host_iconv_encode = iconv_get_encoding("internal_encoding");
                                $get_file_mb_encode = mb_detect_encoding( $info['basename'] );
                                $p_header['filename'] = iconv( $get_host_iconv_encode , $get_file_mb_encode.'//TRANSLIT//IGNORE' , $p_header['filename'] );
                                return 1;
                            }
                            // other file extension will not be unzipped
                            else return 0;
                        }
                    }
                    $extract = $zip->extract(PCLZIP_OPT_PATH, '../'.$gdir
                            , PCLZIP_CB_PRE_EXTRACT, 'zipPreExtractCallBack'
                    );
                    if ( $extract == 0 ) {
                        $_SESSION['easy2err'][] = "Error : ".$zip->errorInfo(TRUE);
                    } else {
                        $_SESSION['easy2suc'][] = $j.' '.$lng['files_uploaded'].'.';
                    }
                    @unlink($_FILES['zip']['tmp_name']);
                    if( $this->_synchro('../'.$e2g['dir'],1,$e2g ) ) {
                        $_SESSION['easy2suc'][] = $lng['synchro_suc'];
                    } else {
                        $_SESSION['easy2err'][] = $lng['synchro_err'];
                    }
                    $res = $this->_delete_all ('../'.$e2g['dir'].'_thumbnails/' );
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

                    /*
                     * CHECK the existing filenames inside the system.
                     * If exists, amend the filename with number
                    */
                    $selectcheck = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                            . 'WHERE filename=\''.$_FILES['img']['name'][$i].'\'';
                    $querycheck = mysql_query($selectcheck);
                    if ($querycheck) {
                        while ($rowcheck = mysql_fetch_array($querycheck)) {
                            $existingname[$rowcheck['filename']] = $rowcheck['filename'];
                        }
                    }
                    if (isset($existingname[$_FILES['img']['name'][$i]])) {
                        $filteredname = $this->_single_name($_FILES['img']['name'][$i]);
                    } else {
                        $filteredname = $_FILES['img']['name'][$i];
                    }
                    $q = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files'
                            .'( dir_id, filename, size, name, description, date_added ) '
                            .'VALUES('
                            .'\''.$parent_id.'\''
                            .', \''.mysql_real_escape_string($filteredname)
                            .'\', '.(int)$_FILES['img']['size'][$i]
                            .', \''.mysql_real_escape_string(htmlspecialchars($_POST['name'][$i]))
                            .'\', \''.mysql_real_escape_string(htmlspecialchars($_POST['description'][$i]))
                            .'\', NOW())';
                    if (!mysql_query($q)) {
                        $_SESSION['easy2err'][] = $lng['db_err'].': ' . mysql_error();
                        continue;
                    }
                    $inf = getimagesize($_FILES['img']['tmp_name'][$i]);
                    if ( ( ($e2g['maxw'] > 0) && ($inf[0] > $e2g['maxw']) ) || ( ($e2g['maxh'] > 0) && ($inf[1] > $e2g['maxh']) ) ) {
                        $this->_resize_img ($_FILES['img']['tmp_name'][$i], $inf, $e2g['maxw'], $e2g['maxh'], $e2g['maxthq']);
                    }
                    if (! move_uploaded_file($_FILES['img']['tmp_name'][$i], '../'.$gdir.utf8_decode($filteredname))) {
                        $_SESSION['easy2err'][] = '177 '.$lng['upload_err'].': '.'../'.$gdir.utf8_decode($filteredname);
                    }
                    @chmod('../'.$gdir.utf8_decode($filteredname), 0644);
                    $j++;
                }
                $_SESSION['easy2suc'][] = $j.' '.$lng['files_uploaded'].'.';
                header ("Location: ".$index.'&pid='.$_GET['pid']);
                exit();
            // Multiple deletion
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
                    require_once E2G_MODULE_PATH . 'classes/TTree.class.php';
                    $tree = new TTree();
                    $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
                    foreach ($_POST['dir'] as $k => $v) {
                        $del_f_res = 0;
                        if (is_numeric($k)) {
                            $ids = $tree->delete((int) $k);
                            $files_id = array();
                            $files_res = mysql_query(
                                    'SELECT id FROM '.$modx->db->config['table_prefix'].'easy2_files '
                                    .'WHERE dir_id IN('.implode(',', $ids).')');
                            while ($l = mysql_fetch_row($files_res)) $files_id[] = $l[0];

                            if (count($files_id) > 0) {
                                mysql_query(
                                        'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments '
                                        .'WHERE file_id IN('.implode(',', $files_id).')');
                            }
                            mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files '
                                    .'WHERE dir_id IN('.implode(',', $ids).')');
                            $res['fdb'][0] += mysql_affected_rows();
                            if (count($ids) > 0) {
                                $res['ddb'][0] += count($ids);
                            } else {
                                $res['ddb'][1]++;
                            }
                        }
                        if (!empty($v)) {
                            $v = str_replace('../', '', utf8_decode($v));
                            $d = $this->_delete_all('../'.$v.'/');

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
                // Delete images
                if (!empty($_POST['im'])) {
                    foreach ($_POST['im'] as $k => $v) {
                        if (is_numeric($k)) {
                            $files_id = array();
                            $files_res = mysql_query('SELECT id FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.(int) $k);
                            while ($l = mysql_fetch_row($files_res)) $files_id[] = $l[0];
                            if (count($files_id) > 0) {
                                mysql_query(
                                        'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments '
                                        .'WHERE file_id IN('.implode(',', $files_id).')');
                            }
                            if (mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.(int) $k)) {
                                $res['fdb'][0]++;
                            } else {
                                $res['fdb'][1]++;
                            }
                        }
                        if (!empty($v)) {
                            $v = str_replace('../', '', utf8_decode($v));
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
//                $_SESSION['easy2suc'][] = $out;
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
            // Delete Directory
            case 'delete_dir':
                if (empty($_GET['dir_id']) && empty($_GET['dir_path'])) {
                    $_SESSION['easy2err'][] = $lng['dpath_err'];
                    break;
                }
                if (is_numeric($_GET['dir_id'])) {
                    require_once E2G_MODULE_PATH . 'classes/TTree.class.php';
                    $tree = new TTree();
                    $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
                    $ids = $tree->delete((int) $_GET['dir_id']);
                    $files_id = array();
                    $res = mysql_query(
                            'SELECT id FROM '.$modx->db->config['table_prefix'].'easy2_files '
                            .'WHERE dir_id IN('.implode(',', $ids).')'
                    );
                    while ($l = mysql_fetch_row($res)) {
                        $files_id[] = $l[0];
                    }
                    if (count($files_id) > 0) {
                        mysql_query(
                                'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments '
                                .'WHERE file_id IN('.implode(',', $files_id).')');
                    }
                    mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files '
                            .'WHERE dir_id IN('.implode(',', $ids).')');
                }
                if (!empty($_GET['dir_path'])) {
                    $dir_path = str_replace('../', '', utf8_decode($_GET['dir_path']));
                    $res = $this->_delete_all('../'.$dir_path.'/');
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
                    $file_path = str_replace('../', '', utf8_decode($_GET['file_path']));
                    $f_res = @unlink('../'.$file_path);
                }
                if ($db_res && $f_res) {
                    $_SESSION['easy2suc'][] = $lng['file_delete'];
                } elseif ($db_res) {
                    $_SESSION['easy2suc'][] = $lng['file_delete_fdb'];
                } elseif ($f_res) {
                    $_SESSION['easy2suc'][] = $lng['file_delete_fhdd'];
                } else {
                    $_SESSION['easy2err'][] = $lng['file_delete_err'].': '.$file_path;
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
                mysql_query(
                        'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments '
                        .'WHERE id IN('.implode(',', $cids).')');
                mysql_query(
                        'UPDATE '.$modx->db->config['table_prefix'].'easy2_files '
                        .'SET comments=comments-'.count($cids).' WHERE id ='.(int)$_GET['file_id']);
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
            // Delete comments from comments manager
            case 'delete_allcomments':
                foreach ($_POST['allcomment'] as $eachcid) {
                    if (!is_numeric($eachcid)) continue;
                    mysql_query(
                            'UPDATE '.$modx->db->config['table_prefix'].'easy2_files AS f '
                            .'LEFT JOIN '.$modx->db->config['table_prefix'].'easy2_comments AS c '
                            .'ON f.id=c.file_id '
                            .'SET f.comments=f.comments-1 '
                            .'WHERE c.id='.(int)$eachcid);
                    mysql_query(
                            'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments '
                            .'WHERE id ='.(int)$eachcid);
                }
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
            // CACHE
            case 'clean_cache':
                $res = $this->_delete_all ('../'.$gdir.'_thumbnails/');
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
                    if ( $this->is_validfolder($npath) || empty($dir)) continue;

                    if(mkdir($npath)) {
                        @chmod($npath, 0755);
                    } else {
                        $_SESSION['easy2err'][] = $lng['directory_create_err'].' "'.$npath."'";
                    }
                }
                $c = "<?php\r\n\$e2g = array (\r\n";
                foreach($_POST as $k => $v) {
                    $c .= "'$k' => ".(is_numeric($v)?$v:"'".addslashes($v)."'").",\r\n";
                }
                $c .= ");\r\n?>";
                $f = fopen(E2G_MODULE_PATH . 'config.easy2gallery.php', 'w');
                fwrite($f, $c);
                fclose($f);
                $_SESSION['easy2suc'][] = $lng['updated'];
                header ('Location: '.$url);
                exit();
            // ADD DIRECTORY
            case 'add_dir':
                if( $this->_add_all('../'.str_replace('../', '', utf8_decode($_GET['dir_path']).'/' ), $parent_id, $e2g ) ) {
                    $_SESSION['easy2suc'][] = $lng['dir_edded'];
                } else {
                    $_SESSION['easy2err'][] = $lng['dir_edd_err'];
                }
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
            // ADD IMAGE
            case 'add_file':
                $f = '../'.str_replace('../', '', utf8_decode($_GET['file_path']));
                $inf = getimagesize($f);
                if ($inf[2] <= 3 && is_numeric($_GET['pid'])) {
                    // RESIZE
                    if ( ( ($e2g['maxw'] > 0) && ($inf[0] > $e2g['maxw']) ) || ( ($e2g['maxh'] > 0) && ($inf[1] > $e2g['maxh']) ) ) {
                        $this->_resize_img($f, $inf, $e2g['maxw'], $e2g['maxh'], $e2g['maxthq']);
                    }
                    $n = $this->_basename_safe($f);
                    $s = filesize($f);
                    $q = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files '
                            . '(dir_id,filename,size,name,description,date_added) '
                            . "VALUES(".(int)$_GET['pid'].",'$n',$s,'','',NOW())";
                    if (mysql_query($q)) {
                        @chmod($f, 0644);
                        $_SESSION['easy2suc'][] = $lng['file_added'];
                    } else {
                        $_SESSION['easy2err'][] = $lng['add_file_err'].'<br />'.mysql_error().'<br />'.$q;
                    }
                } else {
                    $_SESSION['easy2err'][] = $lng['add_file_err'];
                }
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
            // IGNORE IP ADDRESS IN IMAGE COMMENTS
            case 'ignore_ip':
                $insert = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_ignoredip '
                        . '(ign_date, ign_ip_address, ign_username, ign_email) '
                        . 'VALUES(NOW(),\''.$_GET['ip'].'\',\''.$_GET['u'].'\',\''.$_GET['e'].'\')';
                if (mysql_query($insert)) {
                    $update = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_comments '
                            .'SET STATUS=\'0\' '
                            .'WHERE ip_address=\''.$_GET['ip'].'\'';
                    mysql_query($update);
                    $_SESSION['easy2suc'][] = $lng['ip_ignored_suc'];
                } else {
                    $_SESSION['easy2err'][] = $lng['ip_ignored_err'].'<br />'.mysql_error();
                }
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
            // UNIGNORE IP ADDRESS IN IMAGE COMMENTS
            case 'unignore_ip':
                $delete = 'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_ignoredip '
                        . 'WHERE ign_ip_address =\''.$_GET['ip'].'\'';
                if (mysql_query($delete)) {
                    $update = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_comments '
                            .'SET STATUS=\'1\' '
                            .'WHERE ip_address=\''.$_GET['ip'].'\'';
                    mysql_query($update);
                    $_SESSION['easy2suc'][] = $lng['ip_unignored_suc'];
                } else {
                    $_SESSION['easy2err'][] = $lng['ip_unignored_err'].'<br />'.mysql_error();
                }
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
            // Delete comments from comments manager
            case 'unignored_all_ip':
                foreach ($_POST['unignored_ip'] as $uignIPs) {
                    mysql_query(
                            'UPDATE '.$modx->db->config['table_prefix'].'easy2_comments '
                            .'SET STATUS=\'1\' '
                            .'WHERE ip_address=\''.$uignIPs.'\'') or die('501 '.mysql_error());
                    mysql_query(
                            'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_ignoredip '
                            .'WHERE ign_ip_address =\''.$uignIPs.'\'') or die('504 '.mysql_error());
                }
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
        } // switch ($act)

        // for table row class looping
        $cl = array(' class="gridAltItem"', ' class="gridItem"');
        $i = 0;
        $page = empty($_GET['page']) ? '' : $_GET['page'];

        /*
         * RESET MODULE INTERFACE CONTENT
        */
        $content = '';

        /*
         * PAGE ACTION
        */
        switch ($page) {
            // Create Directory
            case 'create_dir':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // check names against bad characters
                    if ($this->_has_bad_char($_POST['name'])) {
                        $_SESSION['easy2err'][] = $lng['badchars'];
                    } else {
                        require_once E2G_MODULE_PATH . 'classes/TTree.class.php';
                        $tree = new TTree();
                        $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
                        $dirname = htmlspecialchars($_POST['name'], ENT_QUOTES);
                        if ( ($id = $tree->insert($dirname, $parent_id)) ) {
                            $q = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_dirs '
                                    .'SET '
                                    .'cat_alias = \''.htmlspecialchars(trim($_POST['alias']), ENT_QUOTES).'\''
                                    .', cat_description = \''.htmlspecialchars(trim($_POST['description']), ENT_QUOTES).'\''
                                    .', last_modified=NOW() '
                                    .'WHERE cat_id='.$id;
                            mysql_query($q);
                            if (mkdir('../'.$gdir.utf8_decode($dirname))) {
                                $_SESSION['easy2suc'][] = $lng['directory_created'];
                                @chmod('../'.$gdir.utf8_decode($dirname), 0755);

                                // goldsky -- adds a cover file
                                $indexFile = '../'.$gdir.utf8_decode($dirname)."/index.html";
                                $fh = fopen($indexFile, 'w') or die("can't open file");
                                $stringData = $lng['indexfile'];
                                fwrite($fh, $stringData);
                                fclose($fh);
                                @chmod($indexFile, 0644);
                            } else {
                                $_SESSION['easy2err'][] = $lng['directory_create_err'];
                                $tree->delete($id);
                            }
                        } else {
                            $_SESSION['easy2err'][] = $tree->error;
                        }
                        header ("Location: ".$index."&pid=".$parent_id);
                        exit();
                    }
                }
                $content .= '
<p>'.$lng['create_dir'].'&nbsp; &nbsp; &nbsp;
    <a href="'.$index.'&pid='.$parent_id.'">'.$lng['back_to_fmanager'].'</a>
</p>
<form name="list" action="" method="post">
    <table cellspacing="0" cellpadding="2" class="aForm" >
        <tr>
            <td><b>'.$lng['create_dir'].' :</b></td>
            <td><input name="name" type="text" size="30"></td>
        </tr>
        <tr>
            <td><b>'.$lng['enter_new_alias'].' :</b></td>
            <td><input name="alias" type="text" size="30"></td>
        </tr>
        <tr>
            <td valign="top"><b>'.$lng['description'].' :</b></td>
            <td><textarea name="description" style="width:500px"></textarea></td>
        </tr>
        <tr><td></td>
            <td><input type="submit" value="'.$lng['save'].'">
                <input type="button" value="'.$lng['cancel'].'" onclick="javascript:document.location.href=\''.$index.'&pid='.$parent_id.'\'">
            </td>
        </tr>
    </table>
</form>
';
                break;
            // EDIT DIRECTORY
            case 'edit_dir' :
                if (empty($_GET['dir_id']) || !is_numeric($_GET['dir_id'])) {
                    $_SESSION['easy2err'][] = $id['id_err'];
                    header ("Location: ".html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }
                $res = mysql_query('SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_dirs WHERE cat_id='.(int)$_GET['dir_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // check names against bad characters
                    if ($this->_has_bad_char($_POST['newdirname'])) {
                        $_SESSION['easy2err'][] = $lng['badchars'];
                    } else {
                        $q = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_dirs '
                                .'SET '
                                .'cat_alias = \''.htmlspecialchars(trim($_POST['alias']), ENT_QUOTES).'\''
                                .', cat_description = \''.htmlspecialchars(trim($_POST['description']), ENT_QUOTES).'\''
                                .', last_modified=NOW() '
                                .'WHERE cat_id='.(int)$_GET['dir_id'];
                        $qResult = mysql_query($q) or die('637'.mysql_error());
                        if($qResult) {
                            // rename dir
                            if( $row['cat_id']!='1' && $_POST['newdirname'] != $row['cat_name'] ) {
                                rename('../'.$gdir.utf8_decode($row['cat_name']), '../'.$gdir.utf8_decode($_POST['newdirname']));
                                @chmod('../'.$gdir.utf8_decode($_POST['newdirname']), 0755);
                                $renamedir = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_dirs '
                                        .'SET '
                                        .'cat_name = \''.htmlspecialchars(trim($_POST['newdirname']), ENT_QUOTES).'\''
                                        .', last_modified=NOW() '
                                        .'WHERE cat_id='.(int)$_GET['dir_id'];
                                mysql_query($renamedir);
                            }
                            $_SESSION['easy2suc'][] = $lng['updated'];
                        } else {
                            $_SESSION['easy2err'][] = $lng['update_err'].'<br />'.mysql_error();
                        }
                    }
                    header ('Location: '.$index.'&pid='.$parent_id);
                    exit();
                }

                $content .= '
<p>'.$lng['editing'].' '.$lng['dir'].' <b>'.$row['cat_name'].'</b>
    &nbsp; &nbsp; &nbsp;
    <a href="'.$index.'&pid='.$parent_id.'">'.$lng['back_to_fmanager'].'</a>
</p>
<form name="list" action="" method="post">
    <table cellspacing="0" cellpadding="2" class="aForm" >
        ';
                // DO NOT CHANGE THE ROOT FOLDER'S NAME FROM HERE, USE CONFIG INSTEAD.
                if($row['cat_id']!='1') {
                    $content .= '
        <tr>
            <td><b>'.$lng['enter_new_dirname'].' :</b></td>
            <td><input name="newdirname" type="text" value="'.$row['cat_name'].'" size="30"></td>
        </tr>
        ';
                }
                $content .= '
        <tr>
            <td><b>'.$lng['enter_new_alias'].' :</b></td>
            <td><input name="alias" type="text" value="'.$row['cat_alias'].'" size="30"></td>
        </tr>
        <tr>
            <td valign="top"><b>'.$lng['description'].' :</b></td>
            <td><textarea name="description" style="width:500px">'.$row['cat_description'].'</textarea></td>
        </tr>
        <tr><td></td>
            <td><input type="submit" value="'.$lng['save'].'">
                <input type="button" value="'.$lng['cancel'].'" onclick="javascript:document.location.href=\''.$index.'&pid='.$parent_id.'\'">
            </td>
        </tr>
    </table>
</form>
';
                break;
            case 'edit_file':
                if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
                    $_SESSION['easy2err'][] = $id['id_err'];
                    header ("Location: ".html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }
                $res = mysql_query('SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.(int)$_GET['file_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);

                $ext = substr($row['filename'], strrpos($row['filename'], '.'));
                $filename = substr($row['filename'], 0, -(strlen($ext)));

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // check names against bad characters
                    if ($this->_has_bad_char($_POST['newfilename'])) {
                        $_SESSION['easy2err'][] = $lng['badchars'].': '.$_POST['newfilename'];
                        header ("Location: ".html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    } else {
                        $q = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_files '
                                .'SET name = \''.htmlspecialchars(trim($_POST['name']), ENT_QUOTES).'\''
                                .', description = \''.htmlspecialchars(trim($_POST['description']), ENT_QUOTES).'\''
                                .', last_modified=NOW() '
                                .'WHERE id='.(int)$_GET['file_id'];
                        $qResult = mysql_query($q);
                        if($qResult) {
                            // rename file
                            if( $_POST['newfilename'] != $filename ) {
                                rename('../'.$gdir.utf8_decode($row['filename']) , '../'.$gdir.utf8_decode($_POST['newfilename'].$ext))
                                        or die('713');
                                chmod('../'.$gdir.utf8_decode($_POST['newfilename'].$ext), 0644) or die('714');
                                $renamefile = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_files '
                                        .'SET filename = \''.htmlspecialchars(trim($_POST['newfilename']).$ext, ENT_QUOTES).'\''
                                        .', last_modified=NOW() '
                                        .'WHERE id='.(int)$_GET['file_id'];
                                mysql_query($renamefile);
                            }
                            $_SESSION['easy2suc'][] = $lng['updated'];
                        } else {
                            $_SESSION['easy2err'][] = $lng['update_err'];
                        }
                        header ('Location: '.$index.'&pid='.$parent_id);
                    }
                    exit();
                }

                $content .= '
<p>'.$lng['editing'].' '.$lng['file2'].' <b>'.$row['filename'].' <a href="javascript:imPreview(\''.utf8_encode($gdir).$row['filename'].'\');void(0);">'.$lng['uim_preview'].'</a></b> ('.$row['comments'].' comments)
    &nbsp; &nbsp; &nbsp;
    <a href="'.$index.'&pid='.$parent_id.'">'.$lng['back_to_fmanager'].'</a>
</p>

<table cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td valign="top">
            <form name="list" action="" method="post">
                <table cellspacing="0" cellpadding="2" class="aForm">
                    <tr>
                        <td nowrap="nowrap"><b>'.ucfirst($lng['renamefile']).' :</b></td>
                        <td><input name="newfilename" type="text" value="'.$filename.'" size="30" style="text-align:right;"> '.$ext.'</td>
                    </tr>
                    <tr>
                        <td><b>'.$lng['name'].' :</b></td>
                        <td><input name="name" type="text" value="'.$row['name'].'" size="30"></td>
                    </tr>
                    <tr>
                        <td valign="top"><b>'.$lng['description'].' :</b></td>
                        <td>
                            <textarea name="description" style="width:500px">'.$row['description'].'</textarea>
                        </td>
                    </tr>
                    <tr><td></td>
                        <td><input type="submit" value="'.$lng['save'].'">
                            <input type="button" value="'.$lng['cancel'].'" onclick="javascript:document.location.href=\''.$index.'&pid='.$parent_id.'\'">
                        </td>
                    </tr>
                </table>
            </form>
        </td>
        <th width="205" valign="top">
            <table cellspacing="0" cellpadding="0" style="margin-left:5px; border: 1px solid #ccc;width:200px; height:200px; ">
                <tr><th class="imPreview" id="pElt"></th></tr>
            </table>
        </th>
    </tr>
</table>
';
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
                $content .= '
<p>'.$lng['comments'].' '.$lng['file2'].': '.utf8_encode($gdir).' <b><a href="javascript:imPreview(\''.utf8_encode($gdir).$row['filename'].'\');void(0);">'.$row['filename'].'</a></b> ('.$row['comments'].')
    &nbsp; &nbsp; &nbsp;
    <img src="' . E2G_MODULE_URL . 'icons/arrow_refresh.png" width="16" height="16" border="0" align="absmiddle">
    <a href="'.$index.'&page=comments&file_id='.$_GET['file_id'].'&pid='.$parent_id.'">'.$lng['refresh'].'</a>
    &nbsp; &nbsp; &nbsp;
    <a href="'.$index.'&pid='.$parent_id.'">'.$lng['back_to_fmanager'].'</a>
</p>
<table cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td valign="top">
            <form name="list" action="'.$index.'&act=delete_comments&file_id='.$_GET['file_id'].'" method="post">
                <table width="100%" cellpadding="5" cellspacing="1" class="grid" style="margin-bottom:10px">
                    <tr>
                        <td width="20"><input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;"></td>
                        <td align="center"><b>'.$lng['date'].'</b></td>
                        <td align="center"><b>'.$lng['author'].'</b></td>
                        <td align="center"><b>'.$lng['useremail'].'</b></td>
                        <td align="center"><b>'.$lng['ipaddress'].'</b></td>
                        <td align="center"><b>'.$lng['comments'].'</b></td>
                    </tr>
                    ';
                $res = mysql_query(
                        'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_comments '
                        .'WHERE file_id='.(int)$_GET['file_id'].' ORDER BY id DESC'
                );
                while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                    $content .= '
                    <tr'.$cl[$i%2].'>
                        <td nowrap="nowrap"><input name="comment[]" value="'.$l['id'].'" type="checkbox" style="border:0;padding:0"></td>
                        <td nowrap="nowrap">'.$l['date_added'].'</td>
                        <td nowrap="nowrap">'.$l['author'].'</td>
                        <td nowrap="nowrap"><a href="mailto:'.$l['email'].'">'.$l['email'].'</a></td>
                        <td nowrap="nowrap">'.$l['ip_address']
                            .' <a href="'.$index.'&act=ignore_ip&file_id='.$l['file_id'].'&comment_id='.$l['id'].'"
                               onclick="return ignoreIPAddress();">
                                <img src="' . E2G_MODULE_URL . 'icons/delete.png" border="0"
                                     alt="'.$lng['ignore'].'" title="'.$lng['ignore'].'" />
                            </a>
                        </td>
                        <td valign="top" style="width:100%;">'.htmlspecialchars($l['comment']).'</td>
                    </tr>
                 ';
                    $i++;
                }
                $content .= '
                </table>
                <input type="submit" value="'.$lng['delete'].'" name="delete" style="font-weight:bold;color:red" />
            </form>
        </td>
        <th width="205" valign="top">
            <table cellspacing="0" cellpadding="0" style="margin-left:5px; border: 1px solid #ccc;width:200px; height:200px; ">
                <tr><th class="imPreview" id="pElt"></th></tr>
            </table>
        </th>
    </tr>
</table>
';
                break;
            case 'openexplorer':
                header ('Location: '.$index.'&pid='.$parent_id);
                exit();
                break;
            default:
                if (empty($cpath)) {
                    // MySQL Dir list
                    $q = 'SELECT cat_id,cat_name,cat_visible '
                            .'FROM '.$modx->db->config['table_prefix'].'easy2_dirs'.' '
                            .'WHERE parent_id = '.$parent_id;
                    $res = mysql_query($q);
                    $mdirs = array();
                    if ($res) {
                        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                            // goldsky -- store the array to be connected between db <--> fs
                            $mdirs[$l['cat_name']]['id'] = $l['cat_id'];
                            $mdirs[$l['cat_name']]['name'] = $l['cat_name'];
                            $mdirs[$l['cat_name']]['cat_visible'] = $l['cat_visible'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
                    }
                    // MySQL File list
                    $q = 'SELECT id,filename,status FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id = '.$parent_id ;
                    $res = mysql_query($q);
                    $mfiles = array();
                    if ($res) {
                        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                            // goldsky -- store the array to be connected between db <--> fs
                            $mfiles[$l['filename']]['id'] = $l['id'];
                            $mfiles[$l['filename']]['name'] = $l['filename'];
                            $mfiles[$l['filename']]['status'] = $l['status'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
                    }
                }

                // Dir list
                $dirs = @glob('../'.$gdir.'*', GLOB_ONLYDIR);
                if(is_array($dirs)) natsort($dirs);
                $content = '
<div id="e2g_topmenu">
    <ul class="actionButtons">
        <li id="Button1">
            <a href="'.$index.'&act=synchro">
                <img src="media/style/MODxCarbon/images/icons/refresh.png" /> '.$lng['synchro'].'
            </a>
        </li>
        <li id="Button2">
            <a href="'.$index.'&act=clean_cache">
                <img src="media/style/MODxCarbon/images/icons/trash.png" /> '.$lng['clean_cache'].'
            </a>
        </li>
        <li id="Button3">
            <a href="'.$index.'&page=create_dir&pid='.$parent_id.'">
                <img src="media/style/MODxCarbon/images/icons/folder_add.png" /> '.$lng['create_dir'].'
            </a>
        </li>
    </ul>
</div>
<p>'.$lng['path'].': <a href="'.$index.'&page=edit_dir&dir_id='.$parent_id.'&pid='.$parent_id.'">
        <img src="' . E2G_MODULE_URL . 'icons/folder_edit.png" width="16" height="16"
             alt="'.$lng['edit'].'" title="'.$lng['edit'].'" align="absmiddle" border=0>
    </a> <b>'.$path.'</b>
</p>';
                // Description of the current directory
                $qdesc = 'SELECT cat_description '
                        .'FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                        .'WHERE cat_id = '.$parent_id;
                $resultdesc = mysql_result(mysql_query($qdesc),0 ,0);
                $content .= '
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="60" valign="top">'.$lng['description'].': </td>
        <td>'.$resultdesc.'</td>
    </tr>
</table>
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
                    </tr>
                    ';
                if ($dirs!=FALSE) {
                    foreach ($dirs as $f) {
                        $name = $this->_basename_safe($f);
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
                                <img src="' . E2G_MODULE_URL . 'icons/folder_edit.png" width="16" height="16"
                                    alt="'.$lng['edit'].'" title="'.$lng['edit'].'" border=0>
                                        </a>';
                        } else {
                            $n = '<a href="'.$index.'&pid='.$parent_id.'&path='.(!empty($cpath)?$cpath:'').$name.'" style="color:gray"><b>'.$name.'</b></a>';
                            $id = null;
                            $ext = '_error';
                            if (empty($cpath)) {
                                $buttons = '<a href="'.$index.'&act=add_dir&dir_path='.utf8_encode($gdir).$name.'&pid='.$parent_id.'">
                                    <img src="' . E2G_MODULE_URL . 'icons/folder_add.png" width="16" height="16"
                                        alt="'.$lng['add_to_db'].'" title="'.$lng['add_to_db'].'" border=0>
                                            </a>';
                            }
                            else $buttons = '';
                        }

                        // print out the content
                        $content .= '
                    <tr'.$cl[$i%2].'>
                        <td>
                            <input name="dir['.(empty($id)?'d'.$i:$id).']" value="'.utf8_encode($gdir).$name.'"
                                   type="checkbox" style="border:0;padding:0">
                        </td>
                        <td><img src="' . E2G_MODULE_URL . 'icons/folder'.$ext.'.png" width="16" height="16" border="0"></td>
                        <td>'.$n.' ('.$cnt.')</td>
                        <td>'.@date($e2g['mdate_format'], $time).'</td>
                        <td>---</td>
                        <td align="right" nowrap>
                            '.$buttons.'
                            <a href="'.$index.'&act=delete_dir&dir_path='.utf8_encode($gdir).$name.(empty($id)?'':'&dir_id='.$id).'"
                               onclick="return confirmDeleteFolder();">
                                <img src="' . E2G_MODULE_URL . 'icons/delete.png" border="0"
                                     alt="'.$lng['delete'].'" title="'.$lng['delete'].'" />
                            </a>
                        </td>
                    </tr>
                    ';
                        $i++;
                    }
                    // Deleted dirs
                    if (isset($mdirs) && count($mdirs) > 0) {
                        foreach ($mdirs as $k => $v) {
                            $content .= '
                    <tr'.$cl[$i%2].'>
                        <td><input name="dir['.$v['id'].']" value="" type="checkbox" style="border:0;padding:0"></td>
                        <td><img src="' . E2G_MODULE_URL . 'icons/folder_delete.png" width="16" height="16" border="0"></td>
                        <td><b style="color:red;"><u>'.$v['name'].'</u></b> ['.$v['id'].']</td>
                        <td>---</td>
                        <td>---</td>
                        <td align="right">
                            <a href="'.$index.'&act=delete_dir&dir_id='.$v['id'].'"
                               onclick="return confirmDeleteFolder();">
                                <img src="' . E2G_MODULE_URL . 'icons/delete.png" border="0"
                                     alt="'.$lng['delete'].'" title="'.$lng['delete'].'" />
                            </a>
                        </td>
                    </tr>
                    ';
                            $i++;
                        }
                    }
                }

                // File list
                $mfiles = isset($mfiles) ? $mfiles : array();
                $excludefiles = array(
                        '../'.$gdir.'index.htm',
                        '../'.$gdir.'index.html',
                        '../'.$gdir.'Thumbs.db',
                        '../'.$gdir.'index.php'
                );
                $files = array_diff(@glob('../'.$gdir.'*.*'), $excludefiles);
                if(is_array($files)) natsort($files);
                if ($files!=FALSE)
                    foreach ($files as $f) {
                        if ($this->is_validfolder($f)) continue;
                        $size = round(filesize($f)/1024);
                        $time = filemtime($f);
                        $name = $this->_basename_safe($f);
                        $ext = 'picture';
                        $id = $mfiles[$name]['id'];
                        if (isset($mfiles[$name])) {
                            if ($mfiles[$name]['status']==1) {
                                $n = '<a href="javascript:imPreview(\''.utf8_encode($gdir).$name.'\');void(0);">'.$name.'</a> [id: '.$mfiles[$name]['id'].']';
                            } else {
                                $n = '<a href="javascript:imPreview(\''.utf8_encode($gdir).$name.'\');void(0);"><i>'.$name.'</i></a> [id: '.$mfiles[$name]['id'].'] <i>('.$lng['hidden'].')</i>';
                            }
                            unset($mfiles[$name]);
                            $buttons = '
 <a href="'.$index.'&page=comments&file_id='.$id.'&pid='.$parent_id.'">
     <img src="' . E2G_MODULE_URL . 'icons/comments.png" width="16" height="16" alt="'.$lng['comments'].'" title="'.$lng['comments'].'" border=0>
 </a>
 <a href="'.$index.'&page=edit_file&file_id='.$id.'&pid='.$parent_id.'">
     <img src="' . E2G_MODULE_URL . 'icons/picture_edit.png" width="16" height="16" alt="'.$lng['edit'].'" title="'.$lng['edit'].'" border=0>
 </a>';
                        } else {
                            $n = '<a href="javascript:imPreview(\''.utf8_encode($gdir).$name.'\');void(0);" style="color:gray"><b>'.$name.'</b></a>';
                            $id = null;
                            $ext .= '_error';
                            if (empty($cpath)) {
                                $buttons = '<a href="'.$index.'&act=add_file&file_path='.utf8_encode($gdir).$name.'&pid='.$parent_id.'">
                                    <img src="' . E2G_MODULE_URL . 'icons/picture_add.png" width="16" height="16"
                                        alt="'.$lng['add_to_db'].'" title="'.$lng['add_to_db'].'" border=0>
                                            </a>';
                            }
                            else $buttons = '';
                        }
                        $content .= '
                    <tr'.$cl[$i%2].'>
                        <td>
                            <input name="im['.(empty($id)?'f'.$i:$id).']" value="'.utf8_encode($gdir).$name.'"
                                   type="checkbox" style="border:0;padding:0">
                        </td>
                        <td><img src="' . E2G_MODULE_URL . 'icons/'.$ext.'.png" width="16" height="16"></td>
                        <td>'.$n.'</td>
                        <td>'.@date($e2g['mdate_format'], $time).'</td>
                        <td>'.$size.'Kb</td>
                        <td align="right" nowrap>'.$buttons.'
                            <a href="'.$index.'&act=delete_file&file_path='.utf8_encode($gdir).$name.(empty($id)?'':'&file_id='.$id).'"
                               onclick="return confirmDelete();">
                                <img src="' . E2G_MODULE_URL . 'icons/delete.png" border="0"
                                     alt="'.$lng['delete'].'" title="'.$lng['delete'].'" />
                            </a>
                        </td>
                    </tr>
                    ';
                        $i++;
                    }
                // Deleted files
                $mfiles = isset($mfiles) ? $mfiles : array();
                $name = isset($name) ? $name : array();

                if (isset($mfiles) && count($mfiles) > 0) {
                    foreach ($mfiles as $k => $v) {
                        $p = strrpos($v, '.');
                        $content .= '
                    <tr'.$cl[$i%2].'>
                        <td><input name="im['.$v['id'].']" value="" type="checkbox" style="border:0;padding:0"></td>
                        <td><img src="' . E2G_MODULE_URL . 'icons/picture_delete.png" width="16" height="16" border="0"></td>
                        <td><b style="color:red;"><u>'.$v['name'].'</u></b> ['.$v['id'].']</td>
                        <td>---</td>
                        <td>---</td>
                        <td align="right" nowrap>
                            <a href="'.$index.'&page=comments&file_id='.$v['id'].'&pid='.$parent_id.'">
                                <img src="' . E2G_MODULE_URL . 'icons/comments.png" width="16" height="16"
                                     alt="'.$lng['comments'].'" title="'.$lng['comments'].'" border=0>
                            </a>
                            <a href="'.$index.'&act=delete_file&file_id='.$v['id'].'"
                               onclick="return confirmDeleteFolder();">
                                <img src="' . E2G_MODULE_URL . 'icons/delete.png" border="0"
                                     alt="'.$lng['delete'].'" title="'.$lng['delete'].'" />
                            </a>
                        </td>
                    </tr>
                    ';
                        $i++;
                    }
                }

                $content .= '
                </table>
                <div id="e2g_bottommenu">
                    <ul class="actionButtons">
                        '.$lng['withselected'].':
                        <li id="Button4">
                            <a name="delete" href="javascript:document.list.submit()" style="font-weight:bold;color:red">
                                <img src="media/style/MODxCarbon/images/icons/delete.png" /> '.$lng['delete'].'
                            </a>
                        </li>
                        <li id="Button5">
                            <a href="'.$index.'&act=move2folder">
                                <img src="media/style/MODxCarbon/images/icons/sort.png" /> '.$lng['movetofolder'].'
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
';
        } // switch ($page)
        $suc = $err = '';
        if (count($_SESSION['easy2err']) > 0) {
            $err = '<p class="warning">'.implode('<br />', $_SESSION['easy2err']).'</p>';
            $_SESSION['easy2err'] = array();
        }
        if (count($_SESSION['easy2suc']) > 0) {
            $suc = '<p class="success">'.implode('<br />', $_SESSION['easy2suc']).'</p>';
            $_SESSION['easy2suc'] = array();
        }
        /*
         * MODULE's interface
        */
        include_once E2G_MODULE_PATH . 'includes/pane.main.inc.php';
    }

    /*
     * private function _delete_all()
     * To delete all files/folders that have been selected with the checkbox
     * @param string $path file's/folder's path
    */
    private function _delete_all ($path) {
        $res = array('d'=>0, 'f'=>0, 'e'=>array());
        if (!$this->is_validfolder($path)) return $res;
        $fs = glob($path.'*');
        if ($fs!=FALSE) {
            foreach ($fs as $f) {
//                if ($this->is_validfile($f)) {
                // using original file check, because it will delete not only images.
                if (is_file($f)) {
                    if(@unlink($f)) $res['f']++;
                    else $res['e'][] = 'Can not delete file: '.$f;
                } elseif ($this->is_validfolder($f)) {
                    $sres = $this->_delete_all($f.'/');

                    $res['f'] += $sres['f'];
                    $res['d'] += $sres['d'];
                    $res['e'] = array_merge($res['e'], $sres['e']);
                }
            }
        }
        if (count($res['e']) == 0 && @rmdir($path)) $res['d']++;
        else $res['e'][] = 'Can not delete directory: '.$f;
        return $res;
    }

    /*
     * private function _add_all()
     * To add all files from the upload form
     * @param string $path file's/folder's path
     * @param int $pid current parent ID
     * $param string $cfg module's configuration
    */
    private function _add_all ($path, $pid, $cfg) {
        global $modx;
        require_once E2G_MODULE_PATH . 'classes/TTree.class.php';
        $tree = new TTree();
        $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
        $name = $this->_basename_safe($path);
        if ( !($id = $tree->insert($name, $pid)) ) {
            $_SESSION['easy2err'][] = $tree->error;
            return FALSE;
        }
        if (!$this->is_validfolder($path)) {
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
        natsort($fs);
        if ($fs!=FALSE) {
            // goldsky -- alter the maximum execution time
            set_time_limit(0);

            foreach ($fs as $f) {
                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_start();

                if ($this->is_validfolder($f)) {
                    // goldsky -- if the path is a dir, go deeper as $path==$f
                    if (!$this->_add_all ($f.'/', $id, $cfg)) return FALSE;
                } else {
                    $inf = getimagesize($f);
                    if ($inf[2] > 3) continue;
                    // RESIZE
                    if ( ( ($e2g['maxw'] > 0) && ($inf[0] > $e2g['maxw']) ) || ( ($e2g['maxh'] > 0) && ($inf[1] > $e2g['maxh']) ) ) {
                        $this->_resize_img($f, $inf, $cfg['maxw'], $cfg['maxh'], $cfg['maxthq']);
                    }
                    $n = $this->_basename_safe($f);
                    $s = filesize($f);
                    $q = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files '
                            . '(dir_id,filename,size,name,description,date_added) '
                            . "VALUES($id,'$n',$s,'','',NOW())";
                    if (!mysql_query($q)) {
                        $_SESSION['easy2err'][] = $lng['add_file_err'].' "'.$n.'"';
                        continue;
                    }

                    /*
                     * goldsky -- if there is no index.html inside folders, this will create it.
                    */
                    if (!file_exists($path.'/index.html')) {
                        // goldsky -- adds a cover file
                        $indexFile = $path."/index.html";
                        $fh = fopen($indexFile, 'w') or die("can't open file");
//                    $stringData = $lng['synchro_indexfile'];
                        $stringData = '<h2>Unauthorized access</h2>You\'re not allowed to access file folder';
                        fwrite($fh, $stringData);
                        fclose($fh);
                        @chmod($indexFile, 0644);
                    }
                }

                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_end_clean();
            }
            return TRUE;
        }
    }

    /*
     * private function _resize_img()
     * To resize image by configuration settings
     * @param string $f   :file's/folder's name
     * @param int    $inf :getimagesize($f);
     * @param int    $w   :width
     * @param int    $h   :height
     * @param int    $thq :thumbnail quality
    */
    private function _resize_img ($f, $inf, $w, $h, $thq) {
        global $modx;
        // OPEN
        if ($inf[2] == 1) $im = imagecreatefromgif ($f);
        elseif ($inf[2] == 2) $im = imagecreatefromjpeg ($f);
        elseif ($inf[2] == 3) $im = imagecreatefrompng ($f);
        else return FALSE;
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
        return TRUE;
    }

    /*
     * private function _path_to()
     * To add all file from the upload form
     * @param int $id gets ID
     * @param string $string current parent ID
     * This returns ID. The folder's name is retrieved in the line 76.
    */
    private function _path_to ($id, $string = FALSE) {
        global $modx;
        $result = array();
        $q = 'SELECT A.cat_id, A.cat_name '
                .'FROM '.$modx->db->config['table_prefix'].'easy2_dirs A, '
                .$modx->db->config['table_prefix'].'easy2_dirs B '
                .'WHERE B.cat_id='.$id.' '
                .'AND B.cat_left BETWEEN A.cat_left AND A.cat_right '
                .'ORDER BY A.cat_left';
        $res = mysql_query($q);
        while ($l = mysql_fetch_row($res)) {
            $result[$l[0]] = $l[1];
        }
        if (empty($result)) return null;
        if ($string) {
            $result = implode('/', array_keys($result)).'/';
        }
        return $result;
    }

    /*
     * private function _count_files()
     * To calculate the directory content
     * @param string $path folder's/dir's path
    */
    private function _count_files ($path) {
        $cnt = 0;
        $excludefiles = array(
                $path.'/index.htm',
                $path.'/index.html',
                $path.'/Thumbs.db',
                $path.'/index.php'
        );
        if (glob($path.'/*.*')!=FALSE) $cnt = count(array_diff(glob($path.'/*.*'), $excludefiles));
        $sd = glob($path.'/*');
        if ($sd!=FALSE)
            foreach(glob($path.'/*') as $d) {
                $cnt += $this->_count_files($d);
            }
        return $cnt;
    }

    /*
     * private function _add_file()
     * To add all file from the upload form
     * @param string $f filename
     * @param int $pid current parent ID
     * $param string $cfg module's configuration
    */
    private function _add_file ($f, $pid, $cfg) {
        global $modx;
        $inf = getimagesize($f);
        if ($inf[2] <= 3 && is_numeric($pid)) {
            // RESIZE
            if ( ( ($cfg['maxw'] > 0) && ($inf[0] > $cfg['maxw']) ) || ( ($cfg['maxh'] > 0) && ($inf[1] > $cfg['maxh']) ) ) {
                $this->_resize_img($f, $inf, $cfg['maxw'], $cfg['maxh'], $cfg['maxthq']);
            }
            $n = $this->_basename_safe($f);
            $s = filesize($f);
            $q = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files '
                    . '(dir_id,filename,size,name,description,date_added) '
                    . "VALUES(".$pid.",'$n',$s,'','',NOW())";
            if (!mysql_query($q)) {
                $_SESSION['easy2err'][] = $lng['add_file_err'].'<br/>'.mysql_error();
                return FALSE;
            }
        } else {
            $_SESSION['easy2err'][] = $lng['add_file_err'];
            return FALSE;
        }
        return TRUE;
    }

    /*
     * private function _synchro()
     * To synchronize between physical gallery contents and database
     * @param string $path path to file or folder
     * @param int $pid current parent ID
     * @param string $cfg module's configuration
    */
    private function _synchro ($path, $pid, $cfg) {
        global $modx;
        // goldsky -- alter the maximum execution time
        set_time_limit(0);
        $time_start = microtime(TRUE);
        /*
         * STORE variable arrays for synchronizing comparison
        */
        // MySQL Dir list
        $res = mysql_query(
                'SELECT cat_id,cat_name FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                .'WHERE parent_id='.$pid.' AND cat_visible = 1');
        $mdirs = array();
        if ($res) {
            while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                $mdirs[$l['cat_name']]['id'] = $l['cat_id']; // goldsky -- to be connected between db <--> fs
                $mdirs[$l['cat_name']]['name'] = $l['cat_name'];
            }
        } else {
            $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
            return FALSE;
        }
        // MySQL File list
        $res = mysql_query('SELECT id,filename FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id='.$pid);
        $mfiles = array();
        if ($res) {
            while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                $mfiles[$l['filename']]['id'] = $l['id'];
                $mfiles[$l['filename']]['name'] = $l['filename'];
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
        $fs = array_diff(glob($path.'*'), $excludefiles); // goldsky -- DO NOT USE a slash here!
        natsort($fs);
        /*
         * READ the real physical objects, store into database
        */
        if ($fs!=FALSE) {
            foreach ($fs as $f) {
                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_start();

                $name = $this->_basename_safe($f);
                if ($this->is_validfolder($f)) { // as a folder/directory
                    if ($name == '_thumbnails') continue;

                    /*
                     * goldsky -- if there is no index.html inside folders, this will create it.
                    */
                    if (!file_exists($f.'/index.html')) {
                        // goldsky -- adds a cover file
                        $indexFile = $f."/index.html";
                        $fh = fopen($indexFile, 'w');
                        if (!$fh)  $_SESSION['easy2err'][] = "Could not open file ".$indexFile;
                        else {
    //                                $stringData = $lng['synchro_indexfile'];
                            $stringData = '<h2>Unauthorized access</h2>You\'re not allowed to access file folder';
                            fwrite($fh, $stringData);
                            fclose($fh);
                            @chmod($indexFile, 0644);
                        }
                    }

                    if (isset($mdirs[$name])) {
                        if (!$this->_synchro($f.'/', $mdirs[$name]['id'], $cfg)) return FALSE;
                        unset($mdirs[$name]);
                    } else { // as ALL folder and file children of the current directory
                        /*
                         * INSERT folder's and file's names into database
                        */
                        if (!$this->_add_all($f.'/', $pid, $cfg)) return FALSE;
                    }
                } elseif ($this->is_validfile($f)) { // as a file in the current directory
                    if (isset($mfiles[$name])) {
                        // goldsky -- add the resizing of old images
                        if ($cfg['resizeoldimg']==1) {
                            $inf = getimagesize($f);
                            if ($inf[2] <= 3) {
                                // RESIZE
                                if ( ( ($cfg['maxw'] > 0) && ($inf[0] > $cfg['maxw']) ) || ( ($cfg['maxh'] > 0) && ($inf[1] > $cfg['maxh']) ) ) {
                                    $this->_resize_img($f, $inf, $cfg['maxw'], $cfg['maxh'], $cfg['maxthq']);
                                }
                                $n = $this->_basename_safe($f);
                                $s = filesize($f);
                                $q = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_files '
                                        . 'SET size=\''.$s.'\', date_added=NOW() '
                                        ;
                                if (!mysql_query($q)) {
                                    $_SESSION['easy2err'][] = $lng['rez_file_err'].'<br/>'.mysql_error();
                                    return FALSE;
                                }
                            }
                        }
                        // goldsky -- if this already belongs to a file in the record, skip it!
                        unset($mfiles[$name]);
                    } else {
                        /*
                         * INSERT filename into database
                        */
                        if (!$this->_add_file($f, $pid, $cfg)) return FALSE;
                    }
                }
                /*
                 * goldsky -- File/folder may exists, but NOT a valid folder, NOT a valid file,
                 * probably has an unallowed extension or strange characters.
                */
                else continue;

                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_end_clean();
            }
        }
        /*
         * UNMATCHED comparisons action
        */
        // Deleted physical dirs, DELETE record from database
        if (isset($mdirs) && count($mdirs) > 0) {
            require_once E2G_MODULE_PATH . 'classes/TTree.class.php';
            $tree = new TTree();
            $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
            foreach($mdirs as $key => $value) {
                $ids = $tree->delete($value['id']);
                $files_id = array();
                $res = mysql_query('SELECT id FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id IN('.implode(',', $ids).')');
                if (!$res) {
                    $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
                    return FALSE;
                }
                while ($l = mysql_fetch_row($res)) {
                    $files_id[] = $l[1];
                }
                if (count($files_id) > 0) {
                    mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id IN('.implode(',', $files_id).')');
                }
                @mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id IN('.implode(',', $ids).')');
            }
        }

        // Deleted physical files, DELETE record from database
        if (isset($mfiles) && count($mfiles) > 0) {
            $mfiles_array = array();
            foreach($mfiles as $key => $value) {
                $mfiles_array[] = $value['id'];
            }
            $db_res = mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id IN('.implode(',', $mfiles_array).')');
            @mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id IN('.implode(',', $mfiles_array).')');
            if (!$db_res) {
                $_SESSION['easy2err'][] = 'MySQL ERROR: '.mysql_error();
                return FALSE;
            }
        }

        $time_end = microtime(TRUE);
        $timetotal = $time_end - $time_start;
        if ( $e2g['debug']== '1' ) {
            $_SESSION['easy2suc'][] = "Syncronized $path in $timetotal seconds\n";
        }
        return TRUE;
    }

    /*
     * to check the existance of filename/folder in the file system.
     * if exists, this will add numbering into the uploaded files.
    */
    private function _single_name($name) {
        global $modx;
        $selectcheck = 'SELECT filename FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE filename = \''.$name.'\'';
        $querycheck = @mysql_query($selectcheck);
        while ($rowcheck = @mysql_fetch_array($querycheck)) {
            $fetchrow[$rowcheck['filename']] = $rowcheck['filename'];
        }
        if ( isset( $fetchrow[$name]) ) {
            $ext = substr($name, strrpos($name, '.'));
            $filename = substr($name, 0, -(strlen($ext)));
            $oldsuffix = end(@explode('_',$filename));
            $prefixfilename = substr($filename, 0, -(strlen($oldsuffix))-1);
            if (is_numeric($oldsuffix)) {
                $notnumbersuffix = '';
                $newnumbersuffix = (int)$oldsuffix+1;
            } else {
                $notnumbersuffix = '_'.$oldsuffix;
                $newnumbersuffix = 1;
            }
            $newfilename = ( $prefixfilename!='' ? $prefixfilename.$notnumbersuffix : $filename ).'_'.$newnumbersuffix.$ext;
            $_SESSION['easy2suc'][] = $name .' exists, file was renamed to be '.$newfilename;
        }

        // recursive check
        $selectcheck2 = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE filename=\''.$newfilename.'\'';
        $querycheck2 = @mysql_query($selectcheck2);
        while ($rowcheck2 = @mysql_fetch_array($querycheck2)) {
            $fetchrow2[$rowcheck2['filename']] = $rowcheck2['filename'];
        }
        if ( isset( $fetchrow2[$newfilename]) ) {
            $newfilename2 = $this->_single_name($newfilename);
            if (!$newfilename2) {
                $_SESSION['easy2err'][] = $name .' exists, but file could not be renamed to be '.$newfilename;
            } else $newfilename = $newfilename2;
        }

        return $newfilename;
    }

    /*
     * to check the valid characters in names
     * TRUE means BAD!
    */
    private function _has_bad_char($characters) {
        $bad_chars = array (
                "U+0000", "/", "\\", ":", "*", "?", "'", "\"", "<", ">", "|", ";"
                , "@", "=", "#", "&", "!", "*", "'", "(", ")", ",", "{", "}", ","
                , "^" , "~" , "[" , "]" , "`"
        );
        foreach ($bad_chars as $bad_char) {
            if (strstr($characters, $bad_char)) return TRUE;
        }
    }

    /*
     * Too much memory swallowed. Need a meter in here.
     * http://www.php.net/manual/en/function.memory-get-usage.php#93012
    */
    private function _echo_memory_usage() {
        $mem_usage = memory_get_usage(true);
        echo '<span class="e2g_grayedHeader"><a>memory usage: ';
        if ($mem_usage < 1024)
            echo $mem_usage." bytes";
        elseif ($mem_usage < 1048576)
            echo round($mem_usage/1024,2)." kilobytes";
        else
            echo round($mem_usage/1048576,2)." megabytes";
        echo "</a></span>";
    }

    /*
     * goldsky -- use this for debuging with: die(is_validfile($filename, 1));
     *
    */
    public function is_validfile ( $filename, $debug=0 ) {
        $f = $this->_basename_safe($filename);
        if ($this->is_validfolder($filename)) {
            if ($debug==1) {
                return '<b style="color:red;">'.$filename.'</b> is not a file, it\'s a valid folder.';
            }
            else return FALSE;
        }
        elseif ( $f != '' && !$this->is_validfolder($filename) ) {
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
    public function is_validfolder($foldername, $debug=0) {
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
     * Replace the basename function with this to grab non-unicode character.
     * http://drupal.org/node/278425#comment-2571500
     */
    private function _basename_safe($path) {
        $path = rtrim($path,'/');
        $path = explode('/',$path);

        // encoding
        $endpath = end($path);
        $encodinghtml= htmlspecialchars(UTF8_encode($endpath), ENT_QUOTES);
        return $encodinghtml;
    }
}