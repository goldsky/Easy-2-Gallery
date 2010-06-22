<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

/**
 * EASY 2 GALLERY
 * Gallery Module Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
 */
//require_once E2G_MODULE_PATH . 'includes/utf8/utf8.php';

class e2g_mod extends e2g_pub {
    private $e2gmod_cfg;
    public $e2g;
    public $lng;

    public function  __construct($e2gmod_cfg, $e2g, $lng) {
        set_time_limit(0);

        $this->e2gmod_cfg = $e2gmod_cfg;
//        $this->e2g = $e2g;
//        $this->lng = $lng;
//        $this->_explore($e2g, $lng);
    }

    /**
     * The main file explorer function
     * @param array $e2g The values from the config file
     * @param array $lng The language string from the language file
     * @return string The module's pages.
     */
    public function explore($e2g, $lng) {
        global $modx;
        $e2g['mdate_format'] = 'd-m-y H:i';
        $e2g_debug = $this->e2gmod_cfg['e2g_debug'];
        $parent_id = $this->e2gmod_cfg['parent_id'];
        $index = $this->e2gmod_cfg['index'];
        $gdir = $this->e2gmod_cfg['gdir'];

        $path = (isset($path)?$path:'');
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

        /*
         * GALLERY ACTIONS
        */
        $act = empty($_GET['act']) ? '' : $_GET['act'];
        switch ($act) {
            case 'synchro':
                if($this->_synchro( MODX_BASE_PATH . $e2g['dir'],1,$e2g, $lng)) {
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['synchro_suc'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['synchro_err'];
                }
                $this->_clean_cache($e2g['dir'], $lng);
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();

            // UPLOADING IMAGES
            case 'uploadzip':
                if($_FILES['zip']['error']==0 && $_FILES['zip']['size']>0) {
//                    move_uploaded_file( $_FILES['zip']['tmp_name'],
//                            '../'.$this->_e2g_decode($gdir.$_FILES['zip']['name']));
//
//                    $zip_file = MODX_BASE_PATH.$this->_e2g_decode($gdir.$_FILES['zip']['name']);

                    if( !$err=$this->_unzip( realpath( $_FILES['zip']['tmp_name'] )
                    , realpath( MODX_BASE_PATH.$this->_e2g_decode( $gdir ) )
                    , $lng) ) {
                        $_SESSION['easy2err'][] = __LINE__. ' <span class="warning"><b>'.$lng['upload_err']
                                .($err===0? 'Missing zip library (php_zip.dll / zip.so)':'').'</b></span><br /><br />';
                    }

//                    @unlink( '../'.$this->_e2g_decode($gdir.$_FILES['zip']['name']) );
                    @unlink($_FILES['zip']['tmp_name']);
                    if( $this->_synchro('../'.$e2g['dir'],1,$e2g, $lng ) ) {
                        $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['synchro_suc'];
                    } else {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['synchro_err'];
                    }
                    $res = $this->_delete_all ('../'.$e2g['dir'].'_thumbnails/' );
                    if (empty($res['e'])) {
                        $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['cache_clean'];
                    } else {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['cache_clean_err'];
                        $_SESSION['easy2err'] = array_merge($_SESSION['easy2err'], $res['e']);
                    }
                } else {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['upload_err'].': ' . $this->_file_upload_error_message($_FILES['zip']['error']);
                }
                header ("Location: ".$index.'&pid='.$_GET['pid']);
                exit();

            case 'upload':
                $j = 0;
                for ($i = 0; $i < count($_FILES['img']['tmp_name']); $i++) {
                    if (!is_uploaded_file($_FILES['img']['tmp_name'][$i])) {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['upload_err'].' ' . $_FILES['img']['name'][$i];
                        continue;
                    }
                    if (!preg_match('/^image\//i', $_FILES['img']['type'][$i])) {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['type_err'].' ' . $_FILES['img']['type'][$i];
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
                    mysql_free_result($querycheck);

                    if (isset($existingname[$_FILES['img']['name'][$i]])) {
                        $filteredname = $this->_single_file($_FILES['img']['name'][$i]);
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
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['db_err'].': ' . mysql_error();
                        continue;
                    }

                    $inf = getimagesize($_FILES['img']['tmp_name'][$i]);
                    if ( ( ($e2g['maxw'] > 0) && ($inf[0] > $e2g['maxw']) ) || ( ($e2g['maxh'] > 0) && ($inf[1] > $e2g['maxh']) ) ) {
                        $this->_resize_img ($_FILES['img']['tmp_name'][$i], $inf, $e2g['maxw'], $e2g['maxh'], $e2g['maxthq']);
                    }
                    if (! move_uploaded_file($_FILES['img']['tmp_name'][$i], '../'.$this->_e2g_decode($gdir.$filteredname))) {
                        $_SESSION['easy2err'][] = __LINE__.' : '.$lng['upload_err'].' : '.'../'.$this->_e2g_decode($gdir.$filteredname);
                    }
                    chmod('../'.$this->_e2g_decode($gdir.$filteredname), 0644)
                            or $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];
                    $j++;
                }
                $_SESSION['easy2suc'][] = __LINE__.' : '. $j.' '.$lng['files_uploaded'].'.';
                header ("Location: ".$index.'&pid='.$_GET['pid']);
                exit();

            // Multiple deletion
            case 'delete_checked':
                $out = '';
                // COUNTER
                $res = array(
                        'fdb'=>array(0, 0),
                        'ffp'=>array(0, 0),
                        'ddb'=>array(0, 0),
                        'dfp'=>array(0, 0),
                );
                // Delete dirs
                if (!empty($_POST['dir'])) {
                    require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
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
                            mysql_free_result($files_res);

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
                            $v = str_replace('../', '', $this->_e2g_decode($v));
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
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['dirs_delete_err'];
                    } elseif ($res['dfp'][0] == $res['ddb'][0]) {
                        $_SESSION['easy2suc'][] = __LINE__.' : '. $res['dfp'][0] . ' '.$lng['dirs_deleted'].'.';
                    } else {
                        $_SESSION['easy2suc'][] = __LINE__.' : '. $res['ddb'][0] . ' '.$lng['dirs_deleted_fdb'].'.';
                        $_SESSION['easy2suc'][] = __LINE__.' : '. $res['dfp'][0] . ' '.$lng['dirs_deleted_fhdd'].'.';
                    }
                }
                // Delete images
                if (!empty($_POST['im'])) {
                    foreach ($_POST['im'] as $k => $v) {
                        if (is_numeric($k)) {
                            $files_id = array();
                            $files_res = mysql_query('SELECT id FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.(int) $k);
                            while ($l = mysql_fetch_row($files_res)) $files_id[] = $l[0];
                            mysql_free_result($files_res);

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
                            $v = str_replace('../', '', $this->_e2g_decode($v));
                            if (@unlink('../'.$v)) {
                                $res['ffp'][0]++;
                            } else {
                                $res['ffp'][1]++;
                            }
                        }
                        $out .= $k .'=>'. $v.'<br>';
                    }
                    if ($res['ffp'][0] == 0 && $res['fdb'][0] == 0) {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['files_delete_err'];
                    }
                }
                if (!empty($res['ffp']) || !empty($res['fdb'])) {
                    if ($res['ffp'][0] == $res['fdb'][0]) {
                        $_SESSION['easy2suc'][] = __LINE__.' : '. $res['ffp'][0] . ' '.$lng['files_deleted'].'.';
                    } else {
                        $_SESSION['easy2suc'][] = __LINE__.' : '. $res['fdb'][0] . ' '.$lng['files_deleted_fdb'].'.';
                        $_SESSION['easy2suc'][] = __LINE__.' : '. $res['ffp'][0] . ' '.$lng['files_deleted_fhdd'].'.';
                    }
                }
//                $_SESSION['easy2suc'][] = __LINE__.' : '. $out;
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();

            // DOWNLOAD FILES / FOLDERS
            case 'download_checked':
                $zipcontent = '';
                $_zipcontent = array();
                if (!empty($_POST['dir']) || !empty($_POST['im'])) {
                    foreach ($_POST['dir'] as $k => $v) {
                        $_zipcontents[] = realpath(MODX_BASE_PATH.$v);
                    }
                    foreach ($_POST['im'] as $k => $v) {
                        $_zipcontents[] = realpath(MODX_BASE_PATH.$v);
                    }

                    $dirName = MODX_BASE_PATH.$gdir;
                    $dirUrl = MODX_BASE_URL.$gdir;
                    $zipName = $dirName.$this->_get_dir_info($_GET['pid'], 'cat_name').'.zip';

                    // delete existing zip file if there is any.
                    @unlink($zipName);

                    foreach ($_zipcontents as $_zipcontent) {
                        //http://www.php.net/manual/en/function.ziparchive-addemptydir.php#91221
                        $zip = new ZipArchive();

                        $zip->open($zipName, ZipArchive::CREATE);

                        if (is_dir($_zipcontent)) {
                            $_zipcontent = realpath($_zipcontent);
                            if (substr($_zipcontent, -1) != DIRECTORY_SEPARATOR) {
                                $_zipcontent.= DIRECTORY_SEPARATOR;
                            }

                            $dirStack = array($_zipcontent);
                            //Find the index where the last dir starts
                            $cutFrom = strrpos(substr($_zipcontent, 0, -1), DIRECTORY_SEPARATOR)+1;

                            while (!empty($dirStack)) {
                                $currentDir = array_pop($dirStack);
                                $filesToAdd = array();

                                $dir = dir($currentDir);
                                while (false !== ($node = $dir->read())) {
                                    if (($node == '..') || ($node == '.')) {
                                        continue;
                                    }
                                    if (is_dir($currentDir . $node)) {
                                        array_push($dirStack, $currentDir . $node . DIRECTORY_SEPARATOR);
                                    }
                                    if ($this->_is_validfile($currentDir . $node)) {
                                        $filesToAdd[] = $node;
                                    }
                                }

                                $localDir = substr($currentDir, $cutFrom);
                                $zip->addEmptyDir($localDir);

                                foreach ($filesToAdd as $file) {
                                    $zip->addFile($currentDir . $file, $localDir . $file);
                                }
                            }
                        }
                        elseif ($this->_is_validfile($_zipcontent)) {
                            $_zipcontent = realpath($_zipcontent);
                            $basename = end(@explode(DIRECTORY_SEPARATOR,$_zipcontent));
                            $zip->addFile($_zipcontent,$basename);
                        }
                        $zip->close();
                        $zipbasename = str_replace(' ','',(end(@explode('/',$zipName))));
                    }
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename='.$zipbasename);
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($zipName));
                    ob_clean();
                    flush();
                    readfile($zipName);
                    @unlink($zipName);
                    exit();
                }
                else {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['zip_select_none'];
                }
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();

            // MOVE TO A NEW DIRECTORY
            case 'move_checked':
                $out = '';
                // COUNTER
                $res = array(
                        'fdb'=>array(0, 0),
                        'ffp'=>array(0, 0),
                        'ddb'=>array(0, 0),
                        'dfp'=>array(0, 0),
                );
                // MOVING DIRS
                if (!empty($_POST['dir']) && !empty($_POST['newparent'])) {
                    require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
                    $tree = new TTree();
                    $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
                    foreach ($_POST['dir'] as $k => $v) {

                        //************* FILE SYSTEM UPDATE *************//
                        if (!empty($v)) {
                            $oldpath = $newpath = array();

                            $oldpath['origin'] = str_replace('../', '', $v );
                            $oldpath['basename'] = $this->_basename_safe($v);
                            $oldpath['decoded'] = str_replace('../', '', $this->_e2g_decode($v) );
                            $oldpath['chmod'] = chmod( MODX_BASE_PATH . $oldpath['decoded'] , 0755 );

                            $newparent = $this->_path_to($_POST['newparent']);
                            unset ($newparent[1]);
                            $newdir = $this->e2gmod_cfg['gdir'];
                            if (!empty($newparent)) $newdir .= implode( '/', $newparent ) .'/' ;
                            $newpath['origin'] = $newdir.$this->_basename_safe($v);
                            $newpath['basename'] = $this->_basename_safe($newpath['origin']);
                            $newpath['decoded'] = $this->_e2g_decode($newpath['origin']);
                            $newpath['chmod'] = chmod( MODX_BASE_PATH . $newpath['decoded'] , 0755 );

                            // CHECK THE OLD FOLDER'S PERMISSION FIRST
                            if (!$oldpath['chmod']) {
                                $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'].' oldpath '.$oldpath['origin'];

                                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                                continue;
//                                exit;
                            } else {

                                //************* DATABASE UPDATE *************//
                                if (is_numeric($k)) {
                                    $ids = $tree->replace((int) $k, (int) $_POST['newparent']);
                                    // goldsky -- the same result with this:
                                    // $ids = $tree->update((int) $k, $this->_basename_safe($v), (int) $_POST['newparent']);
                                    if (!$ids) {
                                        if (!empty($res['e'])) $_SESSION['easy2err'] = $res['e'];
                                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['dir_move_err'].'
                                            from: <span style="color:blue;">'.$oldpath['origin'].'</span>
                                            to: <span style="color:blue;">'.$newpath['origin'].'</span>';
                                        if (!empty($tree->error)) $_SESSION['easy2err'][] = __LINE__.' : Error: '. $tree->error;

                                        header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                                        continue;
//                                        exit();
                                    }
                                    if ($e2g_debug==1)
                                        if (!empty($tree->reports)) {
                                            $_SESSION['easy2suc'][] = __LINE__.' : '. __LINE__ .' : '.__METHOD__;
                                            foreach ($tree->reports as $tree_report) $_SESSION['easy2suc'][] = __LINE__.' : '. __LINE__.' : '. $tree_report;
                                        }
                                    if (count($ids) > 0) {
                                        $res['ddb'][0] += count($ids);
                                    } else {
                                        $res['ddb'][1]++;
                                    }

                                } // if (is_numeric($k))

                                //************* CONTINUE FILE SYSTEM UPDATE *************//
                                $movedir = $this->_move_all( MODX_BASE_PATH.$oldpath['decoded'], MODX_BASE_PATH.$newpath['decoded'] );
                                if(!$movedir) {
                                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['dir_move_err'].' "'.$newpath['origin']."'";
                                } else {
                                    if (!$newpath['chmod']) {
                                        $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'].' => '. MODX_BASE_PATH.$newpath['decoded'];
                                    } else $_SESSION['easy2suc'][] = __LINE__.' : '. __LINE__.' : chmod successfull => '. MODX_BASE_PATH.$newpath['decoded'];

                                    if ($e2g_debug==1)
                                        $_SESSION['easy2suc'][] = __LINE__.' : '. __LINE__.' : '. $lng['dir_move_suc'].'
                                            from: <span style="color:blue;">'.$oldpath['origin'].'</span>
                                            to: <span style="color:blue;">'.$newpath['origin'].'</span>';

                                    if (empty($movedir['e'])) {
                                        $res['dfp'][0] += $movedir['d'];
                                        $res['ffp'][0] += $movedir['f'];
                                    } else {
                                        $res['dfp'][1]++;
                                    }
                                }
                                //************** END OF FILE SYSTEM UPDATE **************//
                            }
                            $oldpath = $newpath = array();
                            unset($oldpath,$newpath);
                        } // if (!empty($v))
                        $out .= $k .'=>'. $v.'<br>';
                    } // foreach ($_POST['dir'] as $k => $v)

                    if ($res['dfp'][0] == 0 && $res['ddb'][0] == 0) {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['dirs_move_err'];
                    } elseif ($res['dfp'][0] == $res['ddb'][0]) {
                        $_SESSION['easy2suc'][] = __LINE__.' : ' . $res['dfp'][0] . ' '.$lng['dirs_moved'].'.';
                    } else {
                        $_SESSION['easy2suc'][] = __LINE__.' : ' . $res['ddb'][0] . ' '.$lng['dirs_moved_fdb'].'.';
                        $_SESSION['easy2suc'][] = __LINE__.' : ' . $res['dfp'][0] . ' '.$lng['dirs_moved_fhdd'].'.';
                    }

                    // ****************** list names ****************** //
                    if ( !empty($res['dir']) || !empty($res['file']) ) {
                        for ($i=0;$i<count($res['dir']);$i++) {
                            $_SESSION['easy2suc'][] = __LINE__.' : '. __LINE__.' dir: '. $res['dir'][$i];
                        }
                        for ($i=0;$i<count($res['file']);$i++) {
                            $_SESSION['easy2suc'][] = __LINE__.' : '. __LINE__.' file: '. $res['file'][$i];
                        }
                    }
                    // ****************** list names ****************** //
                } // if (!empty($_POST['dir']))

                // MOVING IMAGES
                if (!empty($_POST['im']) && !empty($_POST['newparent'])) {
                    foreach ($_POST['im'] as $k => $v) {
                        // update the database
                        if (is_numeric($k)) {
                            $files = array();
                            $files_res = mysql_query(
                                    'SELECT id, dir_id '
                                    .'FROM '.$modx->db->config['table_prefix'].'easy2_files '
                                    .'WHERE id='.(int) $k
                                    );
                            while ($l = mysql_fetch_array($files_res)) {
                                $files[$l['id']]['dir_id'] = $l['dir_id'];
                            }
                            mysql_free_result($files_res);

                            // reject moving to the same new parent
                            if ( $_POST['newparent'] == $files[$k]['dir_id'] ) {
                                $_SESSION['easy2err'][] = __LINE__.' : '. $lng['file_to_same_dir_err'];
                                continue;
                            }

                            // reject overwrite
                            $files_check_s = 'SELECT A.id ai, B.filename bf '
                                    .'FROM '.$modx->db->config['table_prefix'].'easy2_files A, '
                                    .$modx->db->config['table_prefix'].'easy2_files B '
                                    .'WHERE A.filename=B.filename '
                                    .'AND A.id='.(int) $k .' '
                                    .'AND B.dir_id='.(int) $_POST['newparent']
                                    ;
                            $files_check_q = mysql_query($files_check_s);
                            while ($f = mysql_fetch_array($files_check_q)) {
                                $files_check[$f['ai']]['filename'] = $f['bf'];
                            }
                            mysql_free_result($files_check_q);
                            if ( isset( $files_check[$k]['filename'] ) ) {
                                $_SESSION['easy2err'][] = __LINE__.' : '. $lng['file_move_err']
                                .' <span style="color:red;">'.$this->_basename_safe($v).'</span>, '. $lng['file_exists'] .'.';
                                continue;
                            }

                            $updatefile = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_files '
                                    .' SET dir_id='.$_POST['newparent'].' '
                                    .' WHERE id='.(int) $k;

                            if (mysql_query($updatefile)) {
                                $res['fdb'][0]++;
                            } else {
                                $res['fdb'][1]++;
                            }
                            mysql_free_result($updatefile);
                        }
                        // move the file
                        if (!empty($v)) {
                            $v = str_replace('../', '', $this->_e2g_decode($v));
                            $oldfile['origin'] = str_replace('../', '', $v );
                            $oldfile['basename'] = $this->_basename_safe($v);
                            $oldfile['decoded'] = str_replace('../', '', $this->_e2g_decode($v) );
                            $oldfile['chmod'] = chmod( MODX_BASE_PATH . $oldfile['decoded'] , 0755 );

                            $newparent = $this->_path_to($_POST['newparent']);
                            unset ($newparent[1]);
                            $newdir = $this->e2gmod_cfg['gdir'];
                            if (!empty($newparent)) $newdir .= implode( '/', $newparent ) .'/' ;
                            $newfile['origin'] = $newdir.$this->_basename_safe($v);
                            $newfile['basename'] = $this->_basename_safe($newfile['origin']);
                            $newfile['decoded'] = $this->_e2g_decode($newfile['origin']);
                            $newfile['chmod'] = chmod( MODX_BASE_PATH . $newfile['decoded'] , 0755 );

                            if (is_file('../' . $newfile['decoded'])) {
                                $_SESSION['easy2err'][] = __LINE__.' : '. $lng['file_move_err']
                                .' <span style="color:red;">'.$this->_basename_safe($v).'</span>, '. $lng['file_exists'] .'.';
                                continue;
                            } else {
                                $movefile =  @rename('../'.$oldfile['decoded'], '../' . $newfile['decoded'] ) ;
                                if ($movefile) {
                                    $res['ffp'][0]++;
                                } else {
                                    $res['ffp'][1]++;
                                }
                            }

                            $oldfile = $newfile = array();
                            unset($oldfile,$newfile);
                        }
                    }
                } // if (!empty($_POST['im']))

                $res = $this->_delete_all ('../'.$e2g['dir'].'_thumbnails/' );
                if (empty($res['e'])) {
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['cache_clean'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['cache_clean_err'];
                    $_SESSION['easy2err'] = array_merge($_SESSION['easy2err'], $res['e']);
                }

                if ( empty($_POST['dir']) && empty($_POST['im']) ) {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['select_dirfile'];
                    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                }
                elseif ($_POST['newparent']=='') {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['select_newdir'];
                    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                }
                /*
                 * REDIRECT PAGE TO THE SELECTED OPTION
                */
                elseif ( (isset($_POST['dir']) || isset($_POST['im']))
                        && !empty($_POST['newparent'])
                        && ($_POST['gotofolder']=='gothere')
                        && ($movedir || $movefile) ) {
                    header ('Location: '.$index.'&pid='.$_POST['newparent']);
                }
                else header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();

            // Delete Directory
            case 'delete_dir':
                if (empty($_GET['dir_id']) && empty($_GET['dir_path'])) {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['dpath_err'];
                    break;
                }
                if (is_numeric($_GET['dir_id'])) {
                    require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
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
                    mysql_free_result($res);

                    if (count($files_id) > 0) {
                        mysql_query(
                                'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments '
                                .'WHERE file_id IN('.implode(',', $files_id).')');
                    }
                    mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files '
                            .'WHERE dir_id IN('.implode(',', $ids).')');
                }
                if (!empty($_GET['dir_path'])) {
                    $dir_path = str_replace('../', '', $this->_e2g_decode($_GET['dir_path']));
                    $res = $this->_delete_all('../'.$dir_path.'/');
                }
                if (count($ids) > 0 && count($res['e']) == 0) {
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['dir_delete'];
                } elseif (count($ids) > 0) {
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['dir_delete_fdb'];
                } elseif (count($res['e']) == 0) {
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['dir_delete_fhdd'];
                } else {
                    if (!empty($res['e'])) $_SESSION['easy2err'] = $res['e'];
                    if (!empty($tree->error)) $_SESSION['easy2err'][] = __LINE__.' : '. $tree->error;
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['dir_delete_err'];
                }
                if ($res['f'] > 0) {
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $res['f'] . ' '.$lng['files_deleted'].'.';
                }
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                
            // Delete file
            case 'delete_file':
                if (empty($_GET['file_id']) && empty($_GET['file_path'])) {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['fpath_err'];
                    break;
                }
                $id = (int) $_GET['file_id'];
                if (is_numeric($_GET['file_id'])) {
                    $db_res = mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.$id);
                    mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id='.$id);
                }
                if (!empty($_GET['file_path'])) {
                    $file_path = str_replace('../', '', $this->_e2g_decode($_GET['file_path']));
                    $f_res = @unlink('../'.$file_path);
                }
                if ($db_res && $f_res) {
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['file_delete'];
                } elseif ($db_res) {
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['file_delete_fdb'];
                } elseif ($f_res) {
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['file_delete_fhdd'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['file_delete_err'].': '.$file_path;
                }
                mysql_free_result($db_res);

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
                $this->_clean_cache($this->_e2g_decode($gdir), $lng);
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
                    if ( $this->_is_validfolder($npath) || empty($dir)) continue;

                    if(mkdir($npath)) {
                        chmod($npath, 0755) or $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];
                    } else {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['dir_create_err'].' "'.$npath."'";
                    }
                }
                ksort($_POST);
                $c = "<?php\r\n\$e2g = array (\r\n";
                foreach($_POST as $k => $v) {
                    $c .= "        '$k' => ".(is_numeric($v)?$v:"'".addslashes($v)."'").",\r\n";
                }
                $c .= ");\r\n?>";
                $f = fopen(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php', 'w');
                fwrite($f, $c);
                fclose($f);
                $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['updated'];

                header ('Location: '.$url);
                exit();
                
            // TRANSLATION
            case 'save_lang':
                ksort($_POST);
                $c = "<?php\r\n\$e2g_lang['".$_GET['lang']."'] = array (\r\n";
                foreach($_POST as $k => $v) {
                    $c .= "        '$k' => '".htmlspecialchars($v,ENT_QUOTES)."',\r\n";
                }
                $c .= ");\r\n?>";
                $f = fopen( E2G_MODULE_PATH . 'includes/langs/'.$_GET['langfile'] , 'w' );
                fwrite($f, $c);
                fclose($f);
                $_SESSION['easy2suc'][] = __LINE__.' : '. 'Language file is updated.';
                header ('Location: '.$index);
                exit();
                
            // ADD DIRECTORY
            case 'add_dir':
                if( $this->_add_all('../'.str_replace('../', '', $this->_e2g_decode($_GET['dir_path']).'/' ), $parent_id, $e2g, $lng) ) {
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['dir_added'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['dir_add_err'];
                }
                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                
            // ADD IMAGE
            case 'add_file':
                $f = '../'.str_replace('../', '', $this->_e2g_decode($_GET['file_path']));
                $inf = getimagesize($f);
                if ($inf[2] <= 3 && is_numeric($_GET['pid'])) {
                    // RESIZE
                    if ( ( ($e2g['maxw'] > 0) && ($inf[0] > $e2g['maxw']) ) || ( ($e2g['maxh'] > 0) && ($inf[1] > $e2g['maxh']) ) ) {
                        $this->_resize_img($f, $inf, $e2g['maxw'], $e2g['maxh'], $e2g['maxthq']);
                    }
                    $n = $this->_basename_safe($f);
                    $n = $this->_e2g_encode($n);
                    $s = filesize($f);
                    $q = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files '
                            . '(dir_id,filename,size,name,description,date_added) '
                            . "VALUES(".(int)$_GET['pid'].",'$n',$s,'','',NOW())";
                    if (mysql_query($q)) {
                        chmod($f, 0644) or $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];
                        $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['file_added'];
                    } else {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['file_add_err'].'<br />'.mysql_error().'<br />'.$q;
                    }
                } else {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['file_add_err'];
                }

                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                
            // Ignore ip address in image comments
            case 'ignore_ip':
                $insert = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_ignoredip '
                        . '(ign_date, ign_ip_address, ign_username, ign_email) '
                        . 'VALUES(NOW(),\''.$_GET['ip'].'\',\''.$_GET['u'].'\',\''.$_GET['e'].'\')';
                if (mysql_query($insert)) {
                    $update = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_comments '
                            .'SET STATUS=\'0\' '
                            .'WHERE ip_address=\''.$_GET['ip'].'\'';
                    mysql_query($update);
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['ip_ignored_suc'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['ip_ignored_err'].'<br />'.mysql_error();
                }

                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                
            // Unignore ip address in image comments
            case 'unignore_ip':
                $delete = 'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_ignoredip '
                        . 'WHERE ign_ip_address =\''.$_GET['ip'].'\'';
                if (mysql_query($delete)) {
                    $update = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_comments '
                            .'SET STATUS=\'1\' '
                            .'WHERE ip_address=\''.$_GET['ip'].'\'';
                    mysql_query($update);
                    $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['ip_unignored_suc'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $lng['ip_unignored_err'].'<br />'.mysql_error();
                }

                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                
            // Delete comments from comments manager
            case 'unignored_all_ip':
                foreach ($_POST['unignored_ip'] as $uignIPs) {
                    mysql_query(
                            'UPDATE '.$modx->db->config['table_prefix'].'easy2_comments '
                            .'SET STATUS=\'1\' '
                            .'WHERE ip_address=\''.$uignIPs.'\'') or die(__LINE__.' : '.mysql_error());
                    mysql_query(
                            'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_ignoredip '
                            .'WHERE ign_ip_address =\''.$uignIPs.'\'') or die(__LINE__.' : '.mysql_error());
                }

                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();

            // Add tag to the selected objects
            case 'tag_add_checked':
                if (empty($_POST['tag_input'])) {
                    $_SESSION['easy2err'][] = __LINE__.' : '.$lng['tag_err_novalue'];
                    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }

                if (empty($_POST['dir']) && empty($_POST['im'])) {
                    $_SESSION['easy2err'][] = __LINE__.': '.$lng['select_dirfile'];
                    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }

                // store the multiple tag input as an array
                $xpld_tag_inputs = explode(',', $_POST['tag_input']);
                for ($c=0;$c<count($xpld_tag_inputs);$c++) {
                    $xpld_tag_inputs[$c] = htmlspecialchars(trim($xpld_tag_inputs[$c]), ENT_QUOTES);
                }

                // Folders
                if (!empty($_POST['dir'])) {
                    foreach ($_POST['dir'] as $k => $v) {
                        if (!empty($v)) {
                            // check the existing value first
                            $select_dir_tag = 'SELECT cat_tag FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                                    .'WHERE cat_id='.$k
                                    ;
                            $query_dir_tag= mysql_query($select_dir_tag);
                            if ($query_dir_tag) {
                                while ($l = mysql_fetch_array($query_dir_tag)) {
                                    $dir_tags = $l['cat_tag'];
                                }
                            } else {
                                $_SESSION['easy2err'][] = __LINE__.': '.mysql_error();
                                $_SESSION['easy2err'][] = __LINE__.': '.$select_dir_tag;
                            }

                            $xpld_dir_tags = array();
                            $xpld_dir_tags = explode(',',$dir_tags);

                            for ($c=0;$c<count($xpld_dir_tags);$c++) {
                                $xpld_dir_tags[$c] = trim($xpld_dir_tags[$c]);
                            }

                            $new_tags = $int_tags = array();
                            $int_tags = array_intersect($xpld_dir_tags, $xpld_tag_inputs);
                            $new_tags = array_unique(array_merge($xpld_dir_tags, $xpld_tag_inputs ));

                            // clean ups
                            foreach ($new_tags as $tag_k => $tag_v) {
                                if (empty($new_tags[$tag_k])) unset($new_tags[$tag_k]);
                            }
                            sort($new_tags, SORT_LOCALE_STRING);

                            if (count($int_tags)>0) {
                                $impld_int_tag = @implode(', ', $int_tags);
                                $_SESSION['easy2err'][] = __LINE__.': '.$lng['tag_err_exist'].': '.$impld_int_tag.' ('.$this->_basename_safe($v).')';
                            }

                            // store the new value of file's tag
                            if (count($new_tags)>0) {
                                $new_tags = implode(', ', $new_tags);
                                $update_dir_tag = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_dirs '
                                        .'SET cat_tag=\''.$new_tags.'\' '
                                        .', last_modified=NOW() '
                                        .'WHERE cat_id='.$k
                                        ;
                                $query_new_tag = mysql_query($update_dir_tag);
                                if (!$update_dir_tag) {
                                    $_SESSION['easy2err'][] = __LINE__.': '.mysql_error();
                                    $_SESSION['easy2err'][] = __LINE__.': '.$update_dir_tag;
                                }
                            }
                        } // if (!empty($v))
                    } // foreach ($_POST['dir'] as $k => $v)
                    $_SESSION['easy2suc'][] = __LINE__.' : '.$lng['tag_suc_new'];
                } // if (!empty($_POST['dir']))

                // Files
                if (!empty($_POST['im'])) {
                    foreach ($_POST['im'] as $k => $v) {
                        if (!empty($v)) {
                            // check the existing value first
                            $select_file_tag = 'SELECT tag FROM '.$modx->db->config['table_prefix'].'easy2_files '
                                    .'WHERE id='.$k
                                    ;
                            $query_file_tag= mysql_query($select_file_tag);
                            if ($query_file_tag) {
                                while ($l = mysql_fetch_array($query_file_tag)) {
                                    $file_tags = $l['tag'];
                                }
                            } else {
                                $_SESSION['easy2err'][] = __LINE__.': '.mysql_error();
                                $_SESSION['easy2err'][] = __LINE__.': '.$select_file_tag;
                            }

                            $xpld_file_tags = array();
                            $xpld_file_tags = explode(',',$file_tags);

                            for ($c=0;$c<count($xpld_file_tags);$c++) {
                                $xpld_file_tags[$c] = htmlspecialchars(trim($xpld_file_tags[$c]), ENT_QUOTES);
                            }

                            $new_tags = $int_tags = array();
                            $int_tags = array_intersect($xpld_file_tags, $xpld_tag_inputs);
                            $new_tags = array_unique(array_merge($xpld_file_tags, $xpld_tag_inputs ));

                            // clean ups
                            foreach ($new_tags as $tag_k => $tag_v) {
                                if (empty($new_tags[$tag_k])) unset($new_tags[$tag_k]);
                            }
                            sort($new_tags, SORT_LOCALE_STRING);

                            if (count($int_tags)>0) {
                                $int_tags = implode(', ', $int_tags);
                                $_SESSION['easy2err'][] = __LINE__.': '.$lng['tag_err_exist'].': '.$int_tags.' ('.$this->_basename_safe($v).')';
                            }

                            // store the new value of file's tag
                            if (count($new_tags)>0) {
                                $new_tags = implode(', ', $new_tags);
                                $update_file_tag = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_files '
                                        .'SET tag=\''.$new_tags.'\' '
                                        .', last_modified=NOW() '
                                        .'WHERE id='.$k
                                        ;
                                $query_new_tag = mysql_query($update_file_tag);
                                if (!$query_new_tag) {
                                    $_SESSION['easy2err'][] = __LINE__.': '.mysql_error();
                                    $_SESSION['easy2err'][] = __LINE__.': '.$update_file_tag;
                                }
                            }
                        } // if (!empty($v))
                    } // foreach ($_POST['im'] as $k => $v)
                    $_SESSION['easy2suc'][] = __LINE__.' : '.$lng['tag_suc_new'];
                } // if (!empty($_POST['im']))

                header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();

            // Remove tag from the selected objects
            case 'tag_remove_checked':
                if (empty($_POST['tag_input'])) {
                    $_SESSION['easy2err'][] = __LINE__.' : '.$lng['tag_err_novalue'];
                    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }

                if (empty($_POST['dir']) && empty($_POST['im'])) {
                    $_SESSION['easy2err'][] = __LINE__.': '.$lng['select_dirfile'];
                    header ('Location: '.html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }

                // store the multiple tag input as an array
                $xpld_tag_inputs = explode(',', $_POST['tag_input']);
                for ($c=0;$c<count($xpld_tag_inputs);$c++) {
                    $xpld_tag_inputs[$c] = htmlspecialchars(trim($xpld_tag_inputs[$c]), ENT_QUOTES);
                }

                // Folders
                if (!empty($_POST['dir'])) {
                    foreach ($_POST['dir'] as $k => $v) {
                        if (!empty($v)) {
                            // check the existing value first
                            $select_dir_tag = 'SELECT cat_tag FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                                    .'WHERE cat_id='.$k
                                    ;
                            $query_dir_tag= mysql_query($select_dir_tag);
                            if ($query_dir_tag) {
                                while ($l = mysql_fetch_array($query_dir_tag)) {
                                    $dir_tags = $l['cat_tag'];
                                }
                            } else {
                                $_SESSION['easy2err'][] = __LINE__.': '.mysql_error();
                                $_SESSION['easy2err'][] = __LINE__.': '.$select_dir_tag;
                            }

                            $xpld_dir_tags = array();
                            $xpld_dir_tags = explode(',',$dir_tags);

                            for ($c=0;$c<count($xpld_dir_tags);$c++) {
                                $xpld_dir_tags[$c] = trim($xpld_dir_tags[$c]);
                            }

                            $new_tags = $int_tags = array();
                            $int_tags = array_diff($xpld_tag_inputs, $xpld_dir_tags);
                            $new_tags = array_unique(array_diff($xpld_dir_tags, $xpld_tag_inputs ));

                            // clean ups
                            foreach ($new_tags as $tag_k => $tag_v) {
                                if (empty($new_tags[$tag_k])) unset($new_tags[$tag_k]);
                            }
                            sort($new_tags, SORT_LOCALE_STRING);

                            if (count($int_tags)>0) {
                                $int_tags = @implode(', ', $int_tags);
                                $_SESSION['easy2err'][] = __LINE__.': '.$lng['tag_err_noexist'].': '.$int_tags.' ('.$this->_basename_safe($v).')';
                            }

                            // store the new value of file's tag
                            $new_tags = implode(', ', $new_tags);
                            $update_dir_tag = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_dirs '
                                    .'SET cat_tag=\''.$new_tags.'\' '
                                    .', last_modified=NOW() '
                                    .'WHERE cat_id='.$k
                                    ;
                            $query_new_tag = mysql_query($update_dir_tag);
                            if (!$update_dir_tag) {
                                $_SESSION['easy2err'][] = __LINE__.': '.mysql_error();
                                $_SESSION['easy2err'][] = __LINE__.': '.$update_dir_tag;
                            }
                        } // if (!empty($v))
                    } // foreach ($_POST['dir'] as $k => $v)
                    $_SESSION['easy2suc'][] = __LINE__.' : '.$lng['tag_suc_new'];
                } // if (!empty($_POST['dir']))

                // Files
                if (!empty($_POST['im'])) {
                    foreach ($_POST['im'] as $k => $v) {
                        if (!empty($v)) {
                            // check the existing value first
                            $select_file_tag = 'SELECT tag FROM '.$modx->db->config['table_prefix'].'easy2_files '
                                    .'WHERE id='.$k
                                    ;
                            $query_file_tag= mysql_query($select_file_tag);
                            if ($query_file_tag) {
                                while ($l = mysql_fetch_array($query_file_tag)) {
                                    $file_tags = $l['tag'];
                                }
                            } else {
                                $_SESSION['easy2err'][] = __LINE__.': '.mysql_error();
                                $_SESSION['easy2err'][] = __LINE__.': '.$select_file_tag;
                            }

                            $xpld_file_tags = array();
                            $xpld_file_tags = explode(',',$file_tags);

                            for ($c=0;$c<count($xpld_file_tags);$c++) {
                                $xpld_file_tags[$c] = trim($xpld_file_tags[$c]);
                            }

                            $new_tags = $int_tags = array();
                            $int_tags = array_diff($xpld_tag_inputs, $xpld_file_tags);
                            $new_tags = array_unique(array_diff($xpld_file_tags, $xpld_tag_inputs ));

                            // clean ups
                            foreach ($new_tags as $tag_k => $tag_v) {
                                if (empty($new_tags[$tag_k])) unset($new_tags[$tag_k]);
                            }
                            sort($new_tags, SORT_LOCALE_STRING);
                            if (count($int_tags)>0) {
                                $int_tags = implode(', ', $int_tags);
                                $_SESSION['easy2err'][] = __LINE__.': '.$lng['tag_err_noexist'].': '.$int_tags.' ('.$this->_basename_safe($v).')';
                            }

                            // store the new value of file's tag
                            $new_tags = implode(', ', $new_tags);
                            $update_file_tag = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_files '
                                    .'SET tag=\''.$new_tags.'\' '
                                    .', last_modified=NOW() '
                                    .'WHERE id='.$k
                                    ;
                            $query_new_tag = mysql_query($update_file_tag);
                            if (!$query_new_tag) {
                                $_SESSION['easy2err'][] = __LINE__.': '.mysql_error();
                                $_SESSION['easy2err'][] = __LINE__.': '.$update_file_tag;
                            }
                        } // if (!empty($v))
                    } // foreach ($_POST['im'] as $k => $v)
                    $_SESSION['easy2suc'][] = __LINE__.' : '.$lng['tag_suc_new'];
                } // if (!empty($_POST['im']))

                $this->_clean_cache($e2g['dir'], $lng);
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
//        $content = '';

        /*
         * PAGE ACTION
        */
        switch ($page) {
            // Create Directory
            case 'create_dir':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // check names against bad characters
                    if ($this->_has_bad_char($_POST['name'])) {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['char_bad'];
                    } else {
                        $dirname = htmlspecialchars($_POST['name'], ENT_QUOTES);
                        $mkdir = mkdir('../'.$this->_e2g_decode($gdir.$dirname));
                        if ($mkdir) {
                            $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['dir_created'].' : '.$gdir.$dirname;
                            chmod('../'.$this->_e2g_decode($gdir.$dirname), 0755)
                                    or $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];

                            // goldsky -- adds a cover file
                            $indexFile = '../'.$this->_e2g_decode($gdir.$dirname)."/index.html";
                            $fh = fopen($indexFile, 'w') or die(__LINE__.' : '."can't open file");
                            $stringData = $lng['indexfile'];
                            fwrite($fh, $stringData);
                            fclose($fh);
                            chmod($indexFile, 0644) or $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];

                            require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
                            $tree = new TTree();
                            $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
                            if ( ($id = $tree->insert($dirname, $parent_id)) ) {
                                $q = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_dirs '
                                        .'SET '
                                        .'cat_alias = \''.htmlspecialchars(trim($_POST['alias']), ENT_QUOTES).'\''
                                        .', cat_tag = \''.htmlspecialchars(trim($_POST['tag']), ENT_QUOTES).'\''
                                        .', cat_description = \''.htmlspecialchars(trim($_POST['description']), ENT_QUOTES).'\''
                                        .', last_modified=NOW() '
                                        .'WHERE cat_id='.$id;
                                mysql_query($q);
                            } else {
                                $_SESSION['easy2err'][] = __LINE__.' : '. $tree->error;
                                $tree->delete($id);
                            }
                        } else {
                            $_SESSION['easy2err'][] = __LINE__.' : '. $lng['dir_create_err'];
                            $_SESSION['easy2err'][] = __LINE__.' : '.$this->_e2g_decode($gdir.$dirname);
                        }

                        header ("Location: ".$index."&pid=".$parent_id);
                        exit();
                    }
                } // if ($_SERVER['REQUEST_METHOD'] == 'POST')
                //the page content is rendered in ../tpl/page.create_dir.inc.php
                break;

            // EDIT DIRECTORY
            case 'edit_dir' :
                if (empty($_GET['dir_id']) || !is_numeric($_GET['dir_id'])) {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $id['id_err'];

                    header ("Location: ".html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }
                $res = mysql_query('SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_dirs WHERE cat_id='.(int)$_GET['dir_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);
                mysql_free_result($res);

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // check names against bad characters
                    if ($this->_has_bad_char($_POST['newdirname'])) {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['char_bad'];
                    } else {
                        // check the CHMOD permission first, EXCLUDE the root gallery
                        if ($row['cat_id']!=1) {
                            $chmodolddir = chmod('../'.$this->_e2g_decode($gdir.$row['cat_name']), 0755);
                            $renamedir = rename('../'.$this->_e2g_decode($gdir.$row['cat_name']), '../'.$this->_e2g_decode($gdir.$_POST['newdirname']));
                            $chmodnewdir = chmod('../'.$this->_e2g_decode($gdir.$_POST['newdirname']), 0755);
                        }
                        if (!$chmodolddir && ($row['cat_id']!=1)) {
                            $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];
                            $_SESSION['easy2err'][] = __LINE__.' : '.'../'.$this->_e2g_decode($gdir.$row['cat_name']);
                        }
                        elseif (!$renamedir && ($row['cat_id']!=1)) {
                            $_SESSION['easy2err'][] = __LINE__.' : '.$lng['update_err'];
                            $_SESSION['easy2err'][] = __LINE__.' : '.$this->_e2g_decode($gdir.$_POST['newdirname']);
                        }
                        else {
                            if (!$chmodnewdir && ($row['cat_id']!=1)) {
                                $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];
                            }
                            $q = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_dirs SET ';
                            if( $row['cat_id']!='1' && $_POST['newdirname'] != $row['cat_name'] ) {
                                $q .= 'cat_name = \''.htmlspecialchars(trim($_POST['newdirname']), ENT_QUOTES).'\', '; // trailing comma!
                            }
                            $q .= 'cat_alias = \''.htmlspecialchars(trim($_POST['alias']), ENT_QUOTES).'\''
                                    .', cat_summary = \''.htmlspecialchars(trim($_POST['summary']), ENT_QUOTES).'\''
                                    .', cat_tag = \''.htmlspecialchars(trim($_POST['tag']), ENT_QUOTES).'\''
                                    .', cat_description = \''.htmlspecialchars(trim($_POST['description']), ENT_QUOTES).'\''
                                    .', last_modified=NOW() '
                                    .'WHERE cat_id='.(int)$_GET['dir_id'];
                            $qResult = mysql_query($q);
                            if($qResult) $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['updated'];
                            else {
                                $_SESSION['easy2err'][] = __LINE__.' : '.$lng['update_err'];
                                $_SESSION['easy2err'][] = __LINE__.' : '. mysql_error();
                            }
                            mysql_free_result($qResult);
                        }
                    }

                    header ('Location: '.$index.'&pid='.$parent_id);
                    exit();
                } // if ($_SERVER['REQUEST_METHOD'] == 'POST')
                //the page content is rendered in ../tpl/page.edit_dir.inc.php
                break;

            case 'edit_file':
                if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $id['id_err'];

                    header ("Location: ".html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }
                $res = mysql_query('SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.(int)$_GET['file_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);
                mysql_free_result($res);

                $ext = substr($row['filename'], strrpos($row['filename'], '.'));
                $filename = substr($row['filename'], 0, -(strlen($ext)));

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // check names against bad characters
                    if ($this->_has_bad_char($_POST['newfilename'])) {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['char_bad'].': '.$_POST['newfilename'];

                        header ("Location: ".html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    } else {
                        // check the CHMOD permission first
                        $chmodoldfile = chmod('../'.$this->_e2g_decode($gdir.$row['filename']), 0644);
                        $renamefile = rename('../'.$this->_e2g_decode($gdir.$row['filename']) , '../'.$this->_e2g_decode($gdir.$_POST['newfilename'].$ext));
                        $chmodnewfile = chmod('../'.$this->_e2g_decode($gdir.$_POST['newfilename'].$ext), 0644);

                        if (!$chmodoldfile) {
                            $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];
                        } elseif (!$renamefile) {
                            $_SESSION['easy2err'][] = __LINE__.' : '.$lng['update_err'];
                            $_SESSION['easy2err'][] = __LINE__.' : '.$this->_e2g_decode($gdir.$_POST['newfilename'].$ext);
                        } else {
                            if (!$chmodnewfile) {
                                $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];
                            }
                            $q = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_files SET ';
                            if( $_POST['newfilename'] != $filename ) {
                                $q .= 'filename = \''.htmlspecialchars(trim($_POST['newfilename']).$ext, ENT_QUOTES).'\', '; // trailing comma!
                            }
                            $q .= 'name = \''.htmlspecialchars(trim($_POST['name']), ENT_QUOTES).'\''
                                    .', summary = \''.htmlspecialchars(trim($_POST['summary']), ENT_QUOTES).'\''
                                    .', tag = \''.htmlspecialchars(trim($_POST['tag']), ENT_QUOTES).'\''
                                    .', description = \''.htmlspecialchars(trim($_POST['description']), ENT_QUOTES).'\''
                                    .', last_modified=NOW() '
                                    .'WHERE id='.(int)$_GET['file_id'];
                            $qResult = mysql_query($q);
                            if($qResult) $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['updated'];
                            else {
                                $_SESSION['easy2err'][] = __LINE__.' : '.$lng['update_err'];
                                $_SESSION['easy2err'][] = __LINE__.' : '. mysql_error();
                            }
                            mysql_free_result($qResult);
                        }

                        header ('Location: '.$index.'&pid='.$parent_id);
                    }
                    exit();
                } // if ($_SERVER['REQUEST_METHOD'] == 'POST')
                //the page content is rendered in ../tpl/page.edit_file.inc.php
                break;

            // COMMENTS
            case 'comments':
                if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
                    $_SESSION['easy2err'][] = __LINE__.' : '. $id['id_err'];

                    header ("Location: ".html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }
                $res = mysql_query('SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id='.(int)$_GET['file_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);
                mysql_free_result($res);
                //the page content is rendered in ../tpl/page.comments.inc.php
                break;

            case 'openexplorer':
                if (isset($_POST['newparent'])) $parent_id=$_POST['newparent'];

                header ('Location: '.$index.'&pid='.$parent_id);
                exit();
                break;

            case 'tag':
                // display list by tag
                if (isset($_GET['tag'])) {
                    $_get_tag = trim($_GET['tag']);

            /******************************************************************/
            /***************** FOLDERS/DIRECTORIES/GALLERIES ******************/
            /******************************************************************/
                    $q = 'SELECT parent_id,cat_id,cat_name,cat_alias,cat_tag,cat_visible,last_modified FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                            .'WHERE cat_tag LIKE \'%'.$_get_tag.'%\' '
                            .'ORDER BY cat_name ASC';
                    $res = mysql_query($q);
                    $mdirs = array();
                    if ($res) {
                        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                            // goldsky -- store the array to be connected between db <--> fs
                            $mdirs[$l['cat_name']]['parent_id'] = $l['parent_id'];
                            $mdirs[$l['cat_name']]['id'] = $l['cat_id'];
                            $mdirs[$l['cat_name']]['name'] = $l['cat_name'];
                            $mdirs[$l['cat_name']]['alias'] = $l['cat_alias'];
                            $mdirs[$l['cat_name']]['cat_tag'] = $l['cat_tag'];
                            $mdirs[$l['cat_name']]['cat_visible'] = $l['cat_visible'];
                            $mdirs[$l['cat_name']]['last_modified'] = $l['last_modified'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
                    }
                    mysql_free_result($res);

            /******************************************************************/
            /************* FILE content for the current directory *************/
            /******************************************************************/
                    $q = 'SELECT id,dir_id,filename,name,tag,last_modified,status FROM '.$modx->db->config['table_prefix'].'easy2_files '
                            .'WHERE tag LIKE \'%'.$_get_tag.'%\' ';
                    $res = mysql_query($q);
                    $mfiles = array();
                    if ($res) {
                        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                            // goldsky -- store the array to be connected between db <--> fs
                            $mfiles[$l['filename']]['id'] = $l['id'];
                            $mfiles[$l['filename']]['dir_id'] = $l['dir_id'];
                            $mfiles[$l['filename']]['name'] = $l['filename'];
                            $mfiles[$l['filename']]['alias'] = $l['name'];
                            $mfiles[$l['filename']]['tag'] = $l['tag'];
                            $mfiles[$l['filename']]['last_modified'] = $l['last_modified'];
                            $mfiles[$l['filename']]['status'] = $l['status'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
                        $_SESSION['easy2err'][] = __LINE__.' : '.$q;
                    }
                    mysql_free_result($res);

                }
                //the page content is rendered in ../tpl/page.default.inc.php
                break;

            default:
                // display list by ROOT id
                if (empty($cpath)) {
                    // MySQL Dir list
                    $q = 'SELECT cat_id,cat_name,cat_alias, cat_tag, cat_visible '
                            .'FROM '.$modx->db->config['table_prefix'].'easy2_dirs'.' '
                            .'WHERE parent_id = '.$parent_id.' '
                            .'ORDER BY cat_name ASC'
                    ;
                    $res = mysql_query($q);
                    $mdirs = array();
                    if ($res) {
                        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                            // goldsky -- store the array to be connected between db <--> fs
                            $mdirs[$l['cat_name']]['id'] = $l['cat_id'];
                            $mdirs[$l['cat_name']]['name'] = $l['cat_name'];
                            $mdirs[$l['cat_name']]['alias'] = $l['cat_alias'];
                            $mdirs[$l['cat_name']]['cat_tag'] = $l['cat_tag'];
                            $mdirs[$l['cat_name']]['cat_visible'] = $l['cat_visible'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
                    }
                    mysql_free_result($res);

                    // MySQL File list
                    $q = 'SELECT id, filename, name, tag, status '
                            .'FROM '.$modx->db->config['table_prefix'].'easy2_files '
                            .'WHERE dir_id = '.$parent_id ;
                    $res = mysql_query($q);
                    $mfiles = array();
                    if ($res) {
                        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                            // goldsky -- store the array to be connected between db <--> fs
                            $mfiles[$l['filename']]['id'] = $l['id'];
                            $mfiles[$l['filename']]['name'] = $l['filename'];
                            $mfiles[$l['filename']]['alias'] = $l['name'];
                            $mfiles[$l['filename']]['tag'] = $l['tag'];
                            $mfiles[$l['filename']]['status'] = $l['status'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
                        $_SESSION['easy2err'][] = __LINE__.' : '.$q;
                    }
                    mysql_free_result($res);

                }
            //the page content is rendered in ../tpl/page.default.inc.php

        } // switch ($page)

        /*
         * MODULE's pages
        */
        include_once E2G_MODULE_PATH . 'includes/tpl/pane.main.inc.php';

        $this->_echo_memory_usage($lng);
    }

    /**
     * To delete all files/folders that have been selected with the checkbox
     * @param string $path file's/folder's path
     */
    private function _delete_all ($path) {
        $res = array('d'=>0, 'f'=>0, 'e'=>array());
        if (!$this->_is_validfolder($path)) return $res;
        $fs = glob($path.'*');
        if ($fs!=FALSE) {
            foreach ($fs as $f) {
//                if ($this->_is_validfile($f)) {
                // using original file check, because it will delete not only images.
                if (is_file($f)) {
                    if(@unlink($f)) $res['f']++;
                    else $res['e'][] = __LINE__.' : '.$lng['file_del_err'].': '.$f;
                } elseif ($this->_is_validfolder($f)) {
                    $sres = $this->_delete_all($f.'/');

                    $res['f'] += $sres['f'];
                    $res['d'] += $sres['d'];
                    $res['e'] = array_merge($res['e'], $sres['e']);
                }
            }
        }
        if (count($res['e']) == 0 && @rmdir($path)) $res['d']++;
        else $res['e'][] = __LINE__.' : '.$lng['dir_delete_err'].': '.$path;
        return $res;
    }

    /**
     * move all content to a new parent
     */
    private function _move_all ($oldpath, $newpath) {
        $res = array('d'=>0, 'f'=>0, 'e'=>array());
        if (!$this->_is_validfolder($oldpath)) return $res;

        $fs = glob($oldpath.'/*');
        if ($fs!=FALSE) {
            foreach ($fs as $f) {
                if (is_file($f)) {
                    $res['file'][] = $f;
                    $res['f']++;
                }
                elseif ($this->_is_validfolder($f)) {
                    $res['dir'][] = $f;
                    $res['d'] ++;
                    // $res = result (file/dir/error)
                    // $sres = result summary (file/dir/error)
                    $res['f'] += $sres['f'];
                    $res['d'] += $sres['d'];
                    $res['e'] = array_merge($res['e'], $sres['e']);

                    $oldbasename = $this->_basename_safe($f);
                    $fnewpath .= '/'.$oldbasename;
                    $sres = $this->_move_all($f, $newpath.$fnewpath);
                }
            }
        }
        if (@rename($oldpath, $newpath)) $res['d']++;
        else $res['e'][] = __LINE__.' : '.$lng['dir_move_err'].': '.$oldpath;

        // only returns the result calculation array
        return $res;
    }

    /**
     * To add all files from the upload form
     * @param string $path file's/folder's path
     * @param int $pid current parent ID
     * $param string $cfg module's configuration
     */
    private function _add_all ($path, $pid, $cfg, $lng) {
        global $modx;
        require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
        $tree = new TTree();
        $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
        $name = $this->_basename_safe($path);
        $name = $this->_e2g_encode($name);
        if ( !($id = $tree->insert($name, $pid)) ) {
            $_SESSION['easy2err'][] = __LINE__.' : '. $tree->error;
            return FALSE;
        }
        if (!$this->_is_validfolder($path)) {
            $_SESSION['easy2err'][] = __LINE__.' : '.$path;
            return FALSE;
        }
        /*
         * goldsky -- if there is no index.html inside folders, this will create it.
        */
        if (!file_exists($path.'/index.html')) {
            // goldsky -- adds a cover file
            $indexFile = $path."/index.html";
            $fh = fopen($indexFile, 'w') or die(__LINE__.' : '."can't open file");
            $stringData = $lng['indexfile'];
            fwrite($fh, $stringData);
            fclose($fh);
            chmod($indexFile, 0644) or $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];
        }

        $fs = @glob($path.'*');
        if (is_array($fs)) natsort($fs);
        // goldsky -- alter the maximum execution time
        set_time_limit(0);
        if ($fs!=FALSE)
            foreach ($fs as $f) {
                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_start();

                if ($this->_is_validfolder($f)) {
                    // goldsky -- if the path is a dir, go deeper as $path==$f
                    if (!$this->_add_all ($f.'/', $id, $cfg, $lng)) {
                        $_SESSION['easy2err'][] = __LINE__.' : '.$f;
                        return FALSE;
                    }
                }
                elseif ($this->_is_validfile($f)) {
                    $inf = getimagesize($f);
                    if ($inf[2] > 3) continue;
                    // RESIZE
                    if ( ( ($e2g['maxw'] > 0) && ($inf[0] > $e2g['maxw']) ) || ( ($e2g['maxh'] > 0) && ($inf[1] > $e2g['maxh']) ) ) {
                        $this->_resize_img($f, $inf, $cfg['maxw'], $cfg['maxh'], $cfg['maxthq']);
                    }
                    $n = $this->_basename_safe($f);
                    $n = $this->_e2g_encode($n);
                    $s = filesize($f);
                    $q = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files '
                            . '(dir_id,filename,size,name,description,date_added) '
                            . "VALUES($id,'$n',$s,'','',NOW())";
                    if (!mysql_query($q)) {
                        $_SESSION['easy2err'][] = __LINE__.' : '. $lng['file_add_err'].' "'.$n.'"';
                        continue;
                    }
                }
                else continue;
                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_end_clean();
            }
        return TRUE;
    }

    /**
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
        else {
            $_SESSION['easy2err'][] = __LINE__.' : '.$f;
            return FALSE;
        }
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

    /**
     * To add all file from the upload form
     * @param int $id gets ID
     * @param string $string current parent ID
     * @return int This returns ID. The folder's name is retrieved in the line 76.
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
        mysql_free_result($res);
        if (empty($result)) return null;
        if ($string) {
            $result = implode('/', array_keys($result)).'/';
        }
        return $result;
    }

    /**
     * To calculate the directory content
     * @param string $path folder's/dir's path
     */
    private function _count_files ($path) {
        $cnt = 0;
        /*
         * @todo : create more reliable process on file filtering
         *         don't use array_diff
        */
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

    /**
     * To add all file from the upload form
     * @param string $f filename
     * @param int $pid current parent ID
     * $param string $cfg module's configuration
     */
    private function _add_file ($f, $pid, $cfg, $lng) {
        global $modx;
        $inf = getimagesize($f);
        if ($inf[2] <= 3 && is_numeric($pid)) {
            // RESIZE
            if ( ( ($cfg['maxw'] > 0) && ($inf[0] > $cfg['maxw']) ) || ( ($cfg['maxh'] > 0) && ($inf[1] > $cfg['maxh']) ) ) {
                $this->_resize_img($f, $inf, $cfg['maxw'], $cfg['maxh'], $cfg['maxthq']);
            }
            $n = $this->_basename_safe($f);
            $n = $this->_e2g_encode($n);
            $s = filesize($f);
            $q = 'INSERT INTO '.$modx->db->config['table_prefix'].'easy2_files '
                    . '(dir_id,filename,size,name,description,date_added) '
                    . "VALUES(".$pid.",'$n',$s,'','',NOW())";
            if (!mysql_query($q)) {
                $_SESSION['easy2err'][] = __LINE__.' : '. $lng['file_add_err'].'<br/>'.mysql_error();
                return FALSE;
            }
        } else {
            $_SESSION['easy2err'][] = __LINE__.' : '. $lng['file_add_err'];
            return FALSE;
        }
        return TRUE;
    }

    /**
     * To synchronize between physical gallery contents and database
     * @param string $path path to file or folder
     * @param int $pid current parent ID
     * @param string $cfg module's configuration
     */
    private function _synchro ($path, $pid, $cfg, $lng) {
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
            $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
            $_SESSION['easy2err'][] = __LINE__.' : '.$res;
            return FALSE;
        }
        mysql_free_result($res);
        // MySQL File list
        $res = mysql_query('SELECT id,filename FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id='.$pid);
        $mfiles = array();
        if ($res) {
            while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                $mfiles[$l['filename']]['id'] = $l['id'];
                $mfiles[$l['filename']]['name'] = $l['filename'];
            }
        } else {
            $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
            $_SESSION['easy2err'][] = __LINE__.' : '.$res;
            return FALSE;
        }
        mysql_free_result($res);

        /*
         * goldsky -- if there is no index.html inside folders, this will create it.
        */
        if (!file_exists($path.'index.html')) {
            // goldsky -- adds a cover file
            $indexFile = $path."index.html";
            $fh = fopen($indexFile, 'w');
            if (!$fh)  $_SESSION['easy2err'][] = __LINE__." : Could not open file ".$indexFile;
            else {
                $stringData = $lng['indexfile'];
                fwrite($fh, $stringData);
                fclose($fh);
                chmod($indexFile, 0644) or $_SESSION['easy2err'][] = __LINE__.' : '.$lng['chmod_err'];
            }
        }

        $fs = @glob($path.'*'); // goldsky -- DO NOT USE a slash here!
        if (is_array($fs)) natsort($fs);
        /*
         * READ the real physical objects, store into database
        */
        if ($fs!=FALSE) {
            foreach ($fs as $f) {
                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_start();

                $name = $this->_basename_safe($f);
                $name = $this->_e2g_encode($name);
                if ($this->_is_validfolder($f)) { // as a folder/directory
                    if ($name == '_thumbnails') continue;
                    if (isset($mdirs[$name])) {
                        if (!$this->_synchro($f.'/', $mdirs[$name]['id'], $cfg, $lng)) {
                            $_SESSION['easy2err'][] = __LINE__.' : '.$f;
                            return FALSE;
                        }
                        unset($mdirs[$name]);
                    } else { // as ALL folder and file children of the current directory
                        /*
                         * INSERT folder's and file's names into database
                        */
                        if (!$this->_add_all($f.'/', $pid, $cfg, $lng)) {
                            $_SESSION['easy2err'][] = __LINE__.' : '.$f;
                            return FALSE;
                        }
                    }
                }
                // as a allowed file in the current directory
                elseif ($this->_is_validfile($f)) {
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
                                $n = $this->_e2g_encode($n);
                                $s = filesize($f);
                                $q = 'UPDATE '.$modx->db->config['table_prefix'].'easy2_files '
                                        . 'SET size=\''.$s.'\', date_added=NOW() '
                                ;
                                if (!mysql_query($q)) {
                                    $_SESSION['easy2err'][] = __LINE__.' : '.$lng['resize_err'];
                                    $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
                                    $_SESSION['easy2err'][] = __LINE__.' : '.$q;
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
                        if (!$this->_add_file($f, $pid, $cfg, $lng)) {
                            $_SESSION['easy2err'][] = __LINE__.' : '.$f;
                            return FALSE;
                        }
                    }
                }
                /*
                 * goldsky -- File/folder may exists, but NOT a valid folder or a valid file,
                 * probably has an unallowed extension or strange characters.
                */
                else continue;

                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_end_clean();
            }
        } // if ($fs!=FALSE)
        /*
         * UNMATCHED comparisons action
        */
        // Deleted physical dirs, DELETE record from database
        if (isset($mdirs) && count($mdirs) > 0) {
            require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
            $tree = new TTree();
            $tree->table = $modx->db->config['table_prefix'].'easy2_dirs';
            foreach($mdirs as $key => $value) {
                $ids = $tree->delete($value['id']);
                $files_id = array();
                $q = 'SELECT id FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE dir_id IN('.implode(',', $ids).')';
                $res = mysql_query($q);
                if (!$res) {
                    $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
                    $_SESSION['easy2err'][] = __LINE__.' : '.$q;
                    return FALSE;
                }
                while ($l = mysql_fetch_row($res)) {
                    $files_id[] = $l[1];
                }
                mysql_free_result($res);
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
            $qfiles = 'DELETE FROM '.$modx->db->config['table_prefix'].'easy2_files WHERE id IN('.implode(',', $mfiles_array).')';
            $db_res = mysql_query($qfiles);
            @mysql_query('DELETE FROM '.$modx->db->config['table_prefix'].'easy2_comments WHERE file_id IN('.implode(',', $mfiles_array).')');
            if (!$db_res) {
                $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
                $_SESSION['easy2err'][] = __LINE__.' : '.$qfiles;
                return FALSE;
            }
            mysql_free_result($db_res);
        }

        $time_end = microtime(TRUE);
        $timetotal = $time_end - $time_start;
        if ( $e2g['e2g_debug']== 1 ) {
            $_SESSION['easy2suc'][] = __LINE__.' : '. "Syncronized $path in $timetotal seconds\n";
        }
        return TRUE;
    }

    /**
     * to check the existance of filename/folder in the file system.<br />
     * if exists, this will add numbering into the uploaded files.
     */
    private function _single_file($name) {
        global $modx;
        $selectcheck = 'SELECT filename FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE filename = \''.$name.'\'';
        $querycheck = @mysql_query($selectcheck);
        while ($rowcheck = @mysql_fetch_array($querycheck)) {
            $fetchrow[$rowcheck['filename']] = $rowcheck['filename'];
        }
        mysql_free_result($querycheck);
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
            $_SESSION['easy2suc'][] = __LINE__.' : '. $name .' exists, file was renamed to be '.$newfilename;
        }

        // recursive check
        $selectcheck2 = 'SELECT * FROM '.$modx->db->config['table_prefix'].'easy2_files '
                . 'WHERE filename=\''.$newfilename.'\'';
        $querycheck2 = @mysql_query($selectcheck2);
        while ($rowcheck2 = @mysql_fetch_array($querycheck2)) {
            $fetchrow2[$rowcheck2['filename']] = $rowcheck2['filename'];
        }
        mysql_free_result($querycheck2);
        if ( isset( $fetchrow2[$newfilename]) ) {
            $newfilename2 = $this->_single_file($newfilename);
            if (!$newfilename2) {
                $_SESSION['easy2err'][] = __LINE__.' : '. $name .' exists, but file could not be renamed to be '.$newfilename;
            } else $newfilename = $newfilename2;
        }

        return $newfilename;
    }

    /**
     * to check the valid characters in names.<br />
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

    /**
     * Too much memory swallowed. Need a meter in here.
     * @link http://www.php.net/manual/en/function.memory-get-usage.php#93012
     */
    private function _echo_memory_usage($lng) {
        $mem_usage = memory_get_usage(true);
        echo '<span class="e2g_grayedHeader"><a>'.$lng['memory_usage'].': ';
        if ($mem_usage < 1024)
            echo $mem_usage." bytes";
        elseif ($mem_usage < 1048576)
            echo round($mem_usage/1024,2).' '.$lng['kilobytes'];
        else
            echo round($mem_usage/1048576,2).' '.$lng['megabytes'];
        echo "</a></span>";
    }

    /**
     * Replace the basename function with this to grab non-unicode character.
     * @link http://drupal.org/node/278425#comment-2571500
     */
    private function _basename_safe($path) {
        return parent::basename_safe($path);
    }
    
    /**
     * To check the specified resource is a valid file.<br />
     * It will be checked against the folder validation first.
     * @author goldsky <goldsky@modx-id.com>
     */
    private function _is_validfile ($filename) {
        return parent::is_validfile($filename);
    }

    /**
     * To check the specified resource is a valid folder, although it has a DOT in it.
     * @author goldsky <goldsky@modx-id.com>
     */
    private function _is_validfolder($foldername) {
        return parent::is_validfolder($foldername);
    }

    /**
     * Unicode character encoding work around.<br />
     * For human reading.<br />
     * The value is set from the module's config page.
     *
     * @link http://a4esl.org/c/charset.html
     * @param string $text the string to be encoded
     * @return string returns the encoding
     */
    private function _e2g_encode($text, $callback=false) {
        return parent::e2g_encode($text, $callback);
    }

    /**
     * Unicode character decoding work around.<br />
     * For file system reading.<br />
     * The value is set from the module's config page.
     *
     * @link http://a4esl.org/c/charset.html
     * @param string $text the string to be decoded
     * @return string returns the decoding
     */
    private function _e2g_decode($text, $callback=false) {
        return parent::e2g_decode($text, $callback);
    }

    /**
     * get folders structure for select options.
     * @author goldsky
     */
    private function _get_folder_options($parentid, $selected=0, $jsactions=null ) {
        global $modx;
        $e2g_debug = $this->e2gmod_cfg['e2g_debug'];

        $selectdir = 'SELECT parent_id, cat_id, cat_name, cat_level '
                .'FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                .'WHERE parent_id='.$parentid.' '
        ;

        $querydir = mysql_query($selectdir);
        $numdir = @mysql_num_rows($querydir);

        $childrendirs = array();
        if ($querydir) {
            while ($l = mysql_fetch_array($querydir, MYSQL_ASSOC)) {
                $childrendirs[$l['cat_id']]['parent_id'] = $l['parent_id'];
                $childrendirs[$l['cat_id']]['cat_id'] = $l['cat_id'];
                $childrendirs[$l['cat_id']]['cat_name'] = $l['cat_name'];
                $childrendirs[$l['cat_id']]['cat_level'] = $l['cat_level'];
            }
        } else {
            $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
            if ($e2g_debug==1)
                $_SESSION['easy2err'][] = __LINE__.' : '.$selectdir;
        }
        mysql_free_result($querydir);

        $output = (isset($output)?$output:'');
        foreach ($childrendirs as $childdir) {
            // DISPLAY
            $output .= '
                            <option value="'.$childdir['cat_id'].'"'
                    . ( ( $childdir['cat_id']==1 ) ?  ' style="background-color:#ddd;"' : '' )
                    . ( isset($jsactions) ? ' '.$jsactions : '' )
                    . ( ( $childdir['cat_id'] == $_GET['pid'] && $selected!=0 ) ? ' selected="selected"': '' )
                    .'>';

            // **************** ONLY START MARGIN **************** //
            for ($k=1; $k<$childdir['cat_level'] ; $k++) {
                if ($k==1) $output .= '&nbsp;&nbsp;&nbsp;';
                else $output .= '&nbsp;|&nbsp;&nbsp;';
            }
            for ($k=1; $k<$childdir['cat_level'] ; $k++) {
                if ($k==1) $output .= '&nbsp;|';
            }
            if ($childdir['cat_level'] > 1) $output .= '--';
            // ***************** ONLY END MARGIN ***************** //

            $output .= '&nbsp;'.$childdir['cat_name'].' [id:'.$childdir['cat_id'].']</option>';

            /*********************************************************/
            // GET SUB-FOLDERS
            $selectsub = 'SELECT parent_id, cat_id, cat_name '
                    .'FROM '.$modx->db->config['table_prefix'].'easy2_dirs '
                    .'WHERE parent_id='.$childdir['cat_id'].' '
                    .'ORDER BY cat_name ASC'
            ;
            $querysub = mysql_query($selectsub);
            if (!$querysub) {
                $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
                $_SESSION['easy2err'][] = __LINE__.' : '.$selectsub;
            } else {
                $numsub = @mysql_num_rows($querysub);
                if ($numsub > 0) {
                    $output .= $this->_get_folder_options($childdir['cat_id'],$selected,$jsactions);
                }
            }
            mysql_free_result($querysub);
            /*********************************************************/
        } // foreach ($childrendirs as $childdir)
        return $output;
    }

    /**
     * To return an options selection for tag
     * @global mixed $modx
     * @return string option selection
     */
    private function _get_tag_options($_get_tag) {
        global $modx;
        $e2g_debug = $this->e2gmod_cfg['e2g_debug'];

        // Directory
        $select_dir_tags = 'SELECT DISTINCT cat_tag FROM '.$modx->db->config['table_prefix'].'easy2_dirs ';
        $query_dir_tags = mysql_query($select_dir_tags);
        $num_dir_tags = mysql_num_rows($query_dir_tags);

        if ($query_dir_tags)
            while ($l = mysql_fetch_array($query_dir_tags)) {
                if ($l['cat_tag']=='' || $l['cat_tag']==null) continue;
                $tag_options[] = $l['cat_tag'];
        } else {
            $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
            if ($e2g_debug==1)
                $_SESSION['easy2err'][] = __LINE__.' : '.$select_dir_tags;
        }

        // File
        $select_file_tags = 'SELECT DISTINCT tag FROM '.$modx->db->config['table_prefix'].'easy2_files ';
        $query_file_tags = mysql_query($select_file_tags);
        $num_file_tags = mysql_num_rows($query_file_tags);

        if ($query_file_tags)
            while ($l = mysql_fetch_array($query_file_tags)) {
                if ($l['tag']=='' || $l['tag']==null) continue;
                $tag_options[] = $l['tag'];
        } else {
            $_SESSION['easy2err'][] = __LINE__.' MySQL ERROR: '.mysql_error();
            if ($e2g_debug==1)
                $_SESSION['easy2err'][] = __LINE__.' : '.$select_file_tags;
        }

        $single_tag_options=array();
        for ($i=0;$i<count($tag_options);$i++) {
            $xpld_tag_options = @explode(',', $tag_options[$i]);
            foreach ($xpld_tag_options as $xpld_tag) {
                $xpld_tag = trim($xpld_tag);
                // recursive check of existing value
                if (!in_array($xpld_tag, $single_tag_options)) $single_tag_options[] = $xpld_tag;
            }
        }
        sort($single_tag_options);
        for ($i=0;$i<count($single_tag_options);$i++) {
            $output .= '<option value="'.$single_tag_options[$i].'"'
                       . ( $_get_tag==$single_tag_options[$i] ? ' selected="selected"' : '' )
                       .'>'.$single_tag_options[$i].'</option>
                    ';
        }
        return $output;
    }

    /**
     * function get_dir_info
     * function to get directory's information
     * @param int $dirid = gallery's ID
     */
    private function _get_dir_info($dirid,$field) {
        return parent::get_dir_info($dirid, $field);
    }

    /**
     * @author Schoschie (nh t ngin dott de)
     * @link http://www.php.net/manual/en/features.file-upload.errors.php#90522
     * @param int $error_code
     * @return string The error message
     */
    private function _file_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }

    /**
    *
    * Unzip for Easy 2 Gallery : Unicode friendly, success/error reports.
    * @param string $file filename
    * @param string $path starting path
    * @return bool true/false
    * @author goldsky (goldsky@modx-id.com)
    * @author Raymond (modx)
    */
    private function _unzip($file, $path, $lng) {
        $e2g_encode = $this->e2gmod_cfg['e2g_encode'];
        $e2g_debug = $this->e2gmod_cfg['e2g_debug'];
        
        if ($e2g_encode == 'UTF-8 (Rin)') {
            include_once E2G_MODULE_PATH.'includes/UTF8-2.1.0/UTF8.php';
            include_once E2G_MODULE_PATH.'includes/UTF8-2.1.0/ReflectionTypehint.php';
        }

        // added by Raymond

        $r = substr($path,strlen($path)-1,1);
        if ($e2g_encode == 'UTF-8 (Rin)') {
            $r = UTF8::substr($path,UTF8::strlen($path)-1,1);
        }
        if ($r!='\\' && $r!='/') $path .='/';

        if (!extension_loaded('zip')) {
            if (strtoupper(substr(PHP_OS, 0,3) == 'WIN')) {
                if(!@dl('php_zip.dll')) return 0;
            } else {
                if(!@dl('zip.so')) return 0;
            }
        }
        // end mod

        $zip = new ZipArchive;
        $zip_open = $zip->open($file);
        if ( $zip_open === TRUE ) {
            $file_count = 0;
            $dir_count = 0;

            if ( $zip->numFiles > 0) {
                for($i = 0; $i < $zip->numFiles; $i++) {
                    ob_start();

                    $zip_entry_name = $zip->getNameIndex($i);
                    $zip_content = $zip->getFromIndex($i);

                    /*
                     * ENCODING OPTIONS TO GET FILENAMES AND END SLASH
                     */
                    if ($e2g_encode == 'none') {
                        $r = substr($zip_entry_name,strlen($zip_entry_name)-1,1);
                    }
                    if ($e2g_encode == 'UTF-8') {
                        $zip_entry_name = utf8_decode($zip_entry_name);
                        $r = substr($zip_entry_name,strlen($zip_entry_name)-1,1);
                    }
                    if ($e2g_encode == 'UTF-8 (Rin)') {
                        /**
                         * using Unicode conversion class.
                         * @todo Need more work work on i18n stuff
                         */
                        $mb_detect_encoding = mb_detect_encoding($zip_open);

                        // fixedmachine -- http://modxcms.com/forums/index.php/topic,49266.msg292206.html#msg292206
						if($mb_detect_encoding != 'ASCII' && $mb_detect_encoding != 'UTF-8'){
							if(!$mb_detect_encoding){
								$zip_entry_name = UTF8::convert_from( $zip_entry_name, "ASCII" );
							}
							else {
								$zip_entry_name = UTF8::convert_from( $zip_entry_name, $mb_detect_encoding );
							}
						}
                        $r = UTF8::substr($zip_entry_name,UTF8::strlen($zip_entry_name)-1,1);
                    }

                    // DETECT the directory entry
                    if ($r == '/') {
                        // creates directory
                        if(!is_dir( $path . $zip_entry_name )) {
                            @mkdir( $path . $zip_entry_name, 0777);
                            $dir_count++;
                            if ($e2g_debug)
                                $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['dirs_uploaded'] .' '. $path . $zip_entry_name;
                        }
                    }
                    else {
                        // creates/copy the file
                        $fd = fopen( $path . $zip_entry_name, 'w');
                        if ($fd) {
                            fwrite($fd, $zip_content );
                            fclose($fd);
                            @chmod( $path . $zip_entry_name, 0644);
                            $file_count++;
                            if ($e2g_debug)
                                $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['files_uploaded'] .' '. $path . $zip_entry_name;
                        } else {
                            $_SESSION['easy2err'][] = __LINE__.': '. $lng['zip_create_err'] .' <b>'. $path . $zip_entry_name.'</b>';
                        }
                    }
                    ob_end_clean();
                } // for($i = 0; $i < $zip->numFiles; $i++)
            } // if ( $zip->numFiles > 0)

            $zip->close();
            $_SESSION['easy2suc'][] = __LINE__.' : '. $dir_count.' '.$lng['dirs_uploaded'].'.';
            $_SESSION['easy2suc'][] = __LINE__.' : '. $file_count.' '.$lng['files_uploaded'].'.';

            return true;
        } else {
            $_SESSION['easy2err'][] = __LINE__.' Error : '. $lng['zip_open_err'] .' <b>'. $path . $file.'</b>';
        }
    }

    /**
     * To delete all of the thumbnail folder's content
     */
    private function _clean_cache($dir, $lng) {
        $res = $this->_delete_all ('../'.$dir.'_thumbnails/' );
        if (empty($res['e'])) {
            $_SESSION['easy2suc'][] = __LINE__.' : '. $lng['cache_clean'].', '.$lng['files_deleted'].': '.$res['f'].', '.$lng['dirs_deleted'].': '.$res['d'];
        } else {
            $_SESSION['easy2err'][] = __LINE__.' : '. $lng['cache_clean_err'].', '.$lng['files_deleted'].': '.$res['f'].', '.$lng['dirs_deleted'].': '.$res['d'];
            $_SESSION['easy2err'] = array_merge($_SESSION['easy2err'], $res['e']);
        }
        return $res;
    }

} // END OF class e2g_mod