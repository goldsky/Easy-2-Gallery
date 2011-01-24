<?php
// SYSTEM VARS
$debug = 0;                             // MODx's debug variable
$_t = $modx->config['manager_theme'];   // MODx's manager theme
$_a = (int) $_GET['a'];                 // MODx's action ID
$_i = (int) $_GET['id'];                // MODx's module ID
$index = 'index.php?a=' . $_a . '&id=' . $_i . (!empty($e2g['mod_id']) ? '&amp;e2g_id=' . $e2g['mod_id'] : '');

if (file_exists(realpath('../assets/modules/easy2/includes/langs/' . $modx->config['manager_language'] . '.inst.inc.php'))) {
    include '../assets/modules/easy2/includes/langs/' . $modx->config['manager_language'] . '.inst.inc.php';
    $lngi = $e2g_lang[$modx->config['manager_language']];
} else {
    include '../assets/modules/easy2/includes/langs/english.inst.inc.php';
    $lngi = $e2g_lang['english'];
}

/**
 * Functions for this installer
 */

/**
 * Redirector
 * @param string $href hyperlink address
 */
function chref($href) {
    $_SESSION['easy2ms'] = $ms;
    header('Location: ' . $href);
    exit();
}

/* * *
 * function restore()
 * To restore file's and folder's name of previous version's installation
 * @param string $path path to file or folder
 * @param int $pid current parent ID
 */

function restore($path, $pid) {
    global $modx;
    $_restore['d'] = 0;
    $_restore['f'] = 0;

    if (file_exists(realpath(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php'))) {
        require E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php';
    } else {
        require E2G_MODULE_PATH . 'includes/configs/default.config.easy2gallery.php';
    }

    $timeStart = microtime(TRUE);
    /**
     * STORE variable arrays for synchronizing comparison
     */
    // MySQL Dir list
    $res = mysql_query('SELECT cat_id,cat_name,parent_id FROM ' . $GLOBALS['table_prefix'] . 'easy2_dirs WHERE parent_id=' . $pid);

    $oldDirs = array();
    $newDirs = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $oldDirs[$l['cat_id']]['id'] = $l['cat_id'];
            $oldDirs[$l['cat_id']]['name'] = $l['cat_name'];
            // goldsky -- switch the array parameter after renaming
            $newDirs[$l['cat_name']]['id'] = $l['cat_id'];
            $newDirs[$l['cat_name']]['name'] = $l['cat_name'];
            $newDirs[$l['cat_name']]['parent_id'] = $l['parent_id'];
        }
    } else {
        $_SESSION['easy2err'][] = __LINE__ . ': ' . 'MySQL ERROR: ' . mysql_error();
        return FALSE;
    }

    // MySQL File list
    $res = mysql_query('SELECT id,filename,size FROM ' . $GLOBALS['table_prefix'] . 'easy2_files WHERE dir_id=' . $pid);

    $oldFiles = array();
    $newFiles = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $oldFiles[$l['id']]['id'] = $l['id'];
            $oldFiles[$l['id']]['name'] = $l['filename'];
            $oldFiles[$l['id']]['size'] = $l['size'];
            // goldsky -- switch the array parameter after renaming
            $newFiles[$l['filename']]['id'] = $l['id'];
            $newFiles[$l['filename']]['name'] = $l['filename'];
            $newFiles[$l['filename']]['size'] = $l['size'];
        }
    } else {
        $_SESSION['easy2err'][] = __LINE__ . ': ' . 'MySQL ERROR: ' . mysql_error();
        return FALSE;
    }

    $fs = array();
    $fs = @glob($path . '*'); // goldsky -- DO NOT USE a slash here!
    natsort($fs);

    /**
     * READ the real physical objects, renaming them back
     */
    if (FALSE !== $fs) {
        // goldsky -- alter the maximum execution time
        set_time_limit(0);

        foreach ($fs as $f) {
            // goldsky -- adds output buffer to avoid PHP's memory limit
            ob_start();

            // to skip ROOT folder
            $baseDir = basename(MODX_BASE_PATH . $e2g['dir']);

            /**
             * goldsky -- restore FOLDER's name of previous version's process here!
             */
            $name = basename($f);
            $oldBasename = $oldDirs[$name]['name'];
            $newBasename = $newDirs[$name]['name'];
            if ($name == '_thumbnails' && $name != $baseDir && $name != '')
                continue;

            if (validFolder($f)) {
                if (isset($oldDirs[$name])) {
                    $nf = MODX_BASE_PATH . $e2g['dir'] . $oldBasename;
                    if (!rename($f, $nf))
                        $_SESSION['easy2err'][] = __LINE__ . ': ' . "Could not rename path " . $f;
                    else {
                        $_restore['d']++;
                        @chmod($nf, 0755);
                        $_SESSION['easy2suc'][] = __LINE__ . ': ' . 'Successful on renaming path " ' . $name . ' " to be " ' . $oldBasename . ' "';
                    }

                    $subFolder = MODX_BASE_PATH . $e2g['dir'] . $oldDirs[$name]['name'];
                    restoreAll($subFolder . '/', $oldDirs[$name]['id']);

                    unset($oldDirs[$name]);
                }
                if (isset($newDirs[$name])) {
                    unset($newDirs[$name]);
                }
            }
            if (validFile($f)) {
                $fbasename = basename($f);

                $s = filesize($f);
                // goldsky -- $ext returns '.jpg', including the dot
                $ext = substr($fbasename, strrpos((string) $fbasename, '.'));
                $trimmedName = rtrim($fbasename, $ext); // goldsky -- split the extension, to gain the file's ID
                $newFilename = MODX_BASE_PATH . $e2g['dir'] . $oldFiles[$trimmedName]['name'];

                // goldsky -- if it belongs to an existing one, skip it
                if ($fbasename == $newFiles[$trimmedName]['name']) {
                    continue;
                }
                // goldsky -- as a PREVIOUS NAME file in the current directory only
                elseif ($fbasename == $oldFiles[$trimmedName]['id'] . $ext
                        && $fbasename != $oldFiles[$trimmedName]['name']
                        && $s == $oldFiles[$trimmedName]['size']
                ) {
                    if (!rename($f, $newFilename))
                        $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['rename_file_err'];
                    else {
                        $_restore['f']++;
                        @chmod($newFilename, 0644);
                        $_SESSION['easy2suc'][] = __LINE__ . ': '
                                . 'Successful on renaming file " '
                                . $fbasename . ' " to be " ' . $oldFiles[$trimmedName]['name'] . ' "';
                    }
                }
            } // if (validFile($f))

            /**
             * goldsky -- File/folder may exists, but NOT a valid folder, NOT a valid file,
             * probably has an unallowed extension or strange characters.
             */
            else
                continue;
            // goldsky -- adds output buffer to avoid PHP's memory limit
            ob_end_clean();
        } // foreach ($fs as $f)
    } // if ( FALSE !== $fs )

    $timeEnd = microtime(TRUE);
    $timeTotal = $timeEnd - $timeStart;

    if ($_restore['d'] != 0 || $_restore['f'] != 0)
        $_SESSION['easy2suc'][] = __LINE__ . ': ' . "Restored $path in $timeTotal seconds\n";

    return $_restore;
}

function deleteAll($path) {

    $res = array('d' => 0, 'f' => 0, 'e' => array());
    if (!is_dir($path))
        return $res;

    $fs = glob($path . '*');
    if ($fs != false)
        foreach ($fs as $f) {
            if (is_file($f)) {

                if (@unlink($f))
                    $res['f']++;
                else
                    $res['e'][] = 'Can not delete file: ' . $f;
            } elseif (is_dir($f)) {
                $sres = deleteAll($f . '/');

                $res['f'] += $sres['f'];
                $res['d'] += $sres['d'];
                $res['e'] = array_merge($res['e'], $sres['e']);
            }
        }
    if (count($res['e']) == 0 && @rmdir($path))
        $res['d']++;
    else
        $res['e'][] = 'Can not delete directory: ' . $f;
    return $res;
}

/**
 *
 * To check the specified resource is a valid file.<br />
 * It will be checked against the folder validation first.
 * @author goldsky <goldsky@modx-id.com>
 * @param string $filename the filename
 */
function validFile($filename, $e2g_debug=0) {
    $f = basename($filename);
    if (validFolder($filename)) {
        if ($e2g['e2g_debug'] == 1) {
            return '<b style="color:red;">' . $filename . '</b> is not a file, it\'s a valid folder.';
        }
        else
            return FALSE;
    }
    elseif ($f != '' && !validFolder($filename)) {
        if (file_exists(realpath($filename))) {
            $size = getimagesize($filename);
            $fp = fopen($filename, "rb");
            $allowedExt = array(
                'image/jpeg' => TRUE,
                'image/gif' => TRUE,
                'image/png' => TRUE
            );
            if ($allowedExt[$size["mime"]] && $fp) {
                if ($e2g['e2g_debug'] == 1) {
                    $fileinfo = 'Filename <b style="color:red;">' . $f . '</b> is a valid image file: ' . $size["mime"] . ' - ' . $size[3];
                }
                else
                    return TRUE;
            } else {
                if ($e2g['e2g_debug'] == 1)
                    $fileinfo = 'Filename <b style="color:red;">' . $f . '</b> is an invalid image file: ' . $size[2] . ' - ' . $size[3];
                else
                    return FALSE;
            }
        }
        else {
            if ($e2g['e2g_debug'] == 1)
                $fileinfo .= 'Filename <b style="color:red;">' . $f . '</b> is NOT exists.<br />';
            else
                return FALSE;
        }
        if ($e2g['e2g_debug'] == 1)
            return $fileinfo;
        else
            return TRUE;
    }
    else
        continue;
}

/**
 * To check the specified resource is a valid folder, although it has a DOT in it.
 * @author goldsky <goldsky@modx-id.com>
 * @param string $foldername the folder's name
 */
function validFolder($folderName, $e2g_debug=0) {
    $openFolder = @opendir($folderName);
    if (!$openFolder) {
        if ($e2g['e2g_debug'] == 1)
            return '<b style="color:red;">' . $folderName . '</b> is NOT a valid folder.';
        else
            return FALSE;
    } else {
        if ($e2g['e2g_debug'] == 1) {
            echo '<h2>' . $folderName . '</h2>';
            echo '<ul>';
            $file = array();
            while (( FALSE !== ( $file = readdir($openFolder) ))) {
                if ($file != "." && $file != "..") {
                    if (filetype($file) == 'dir') {
                        echo '<li>dir: <b style="color:green;">' . $file . '</b></li>';
                    }
                    else
                        echo "<li> $file </li>";
                    clearstatcache();
                }
            }
            echo '</ul>';
        }
        closedir($openFolder);
    }
    if ($e2g['e2g_debug'] == 1)
        return '<br /><b style="color:red;">' . $folderName . '</b> is a valid folder.';
    else
        return TRUE;
}

/**
 * To LOOP restore file's and folder's name of previous version's installation
 * @param string $path path to file or folder
 * @param int $pid current parent ID
 */
function restoreAll($path, $pid) {
    global $modx;

    /**
     * STORE variable arrays for synchronizing comparison
     */
    // MySQL Dir list
    $res = mysql_query('SELECT cat_id,cat_name FROM ' . $GLOBALS['table_prefix'] . 'easy2_dirs WHERE parent_id=' . $pid);

    $oldDirs = array();
    $newDirs = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $oldDirs[$l['cat_id']]['id'] = $l['cat_id'];
            $oldDirs[$l['cat_id']]['name'] = $l['cat_name'];
            // goldsky -- switch the array parameter after renaming
            $newDirs[$l['cat_name']]['id'] = $l['cat_id'];
            $newDirs[$l['cat_name']]['name'] = $l['cat_name'];
        }
    } else {
        $_SESSION['easy2err'][] = __LINE__ . ': ' . 'MySQL ERROR: ' . mysql_error();
        return FALSE;
    }

    // MySQL File list
    $res = mysql_query('SELECT id,filename,size FROM ' . $GLOBALS['table_prefix'] . 'easy2_files WHERE dir_id=' . $pid);

    $oldFiles = array();
    $newFiles = array();
    if ($res) {
        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $oldFiles[$l['id']]['id'] = $l['id'];
            $oldFiles[$l['id']]['name'] = $l['filename'];
            $oldFiles[$l['id']]['size'] = $l['size'];
            // goldsky -- switch the array parameter after renaming
            $newFiles[$l['filename']]['id'] = $l['id'];
            $newFiles[$l['filename']]['name'] = $l['filename'];
            $newFiles[$l['filename']]['size'] = $l['size'];
        }
    } else {
        $_SESSION['easy2err'][] = __LINE__ . ': ' . 'MySQL ERROR: ' . mysql_error();
        return FALSE;
    }

    if (!validFolder($path)) {
        return FALSE;
    }

    $fs = array();
    $fs = @glob($path . '*'); // goldsky -- DO NOT USE a slash here!
    natsort($fs);

    if (FALSE !== $fs)
    // goldsky -- alter the maximum execution time
        set_time_limit(0);

    foreach ($fs as $f) {
        // goldsky -- adds output buffer to avoid PHP's memory limit
        ob_start();

        $name = basename($f);
        $oldBasename = $oldDirs[$name]['name'];
        $newBasename = $newDirs[$name]['name'];

        if (validFolder($f)) {
            if (isset($oldDirs[$name])) {
                $nf = $path . $oldBasename;
                if (!rename($f, $nf))
                    $_SESSION['easy2err'][] = __LINE__ . ': ' . "Could not rename path " . $f;
                else {
                    $_restore['d']++;
                    @chmod($nf, 0755);
                    $_SESSION['easy2suc'][] = __LINE__ . ': ' . 'Successful on renaming path " ' . $name . ' " to be " ' . $oldBasename . ' "';
                }

                $nf = $path . $oldDirs[$name]['name'];
                if (!restoreAll($nf . '/', $oldDirs[$name]['id']))
                    return FALSE;

                unset($oldDirs[$name]);
            }
            elseif (isset($newDirs[$name])) {
                unset($newDirs[$name]);
            }
            else
                continue;
        }
        elseif (validFile($f)) {
            $fbasename = basename($f);

            $s = filesize($f);
            // goldsky -- $ext returns '.jpg', including the dot
            $ext = substr($fbasename, strrpos((string) $fbasename, '.'));
            $trimmedName = rtrim($fbasename, $ext); // goldsky -- split the extension, to gain the file's ID
            $newFilename = $path . $oldFiles[$trimmedName]['name'];
            // goldsky -- if it belongs to an existing one, skip it
            if ($fbasename == $newFiles[$trimmedName]['name']) {
                continue;
            }
            if (!rename($f, $newFilename))
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['rename_file_err'];
            else {
                $_restore['f']++;
                @chmod($newFilename, 0644);
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . 'Successful on renaming file " ' . $fbasename . ' " to be " ' . $oldFiles[$trimmedName]['name'] . ' "';
            }
        }
        else
            continue;
        // goldsky -- adds output buffer to avoid PHP's memory limit
        ob_end_clean();
    }
    return $_restore;
}

/* * *
 *
 * @global mixed $modx
 * @param string $table the table name
 * @param string $field the field name
 * @param string $data  the field's data
 * @return bool|string If this only check the field, it uses bool type.<br />
 * If this check the datatype, it will return the datatype information.
 * @author goldsky
 */

function checkField($table, $field, $data=null) {
    global $modx;

    $metadata = $modx->db->getTableMetaData($table);
    if ($metadata[$field]) {
        return TRUE;
    } elseif ($data) {
        return $metadata[$field][$data];
    }
    else
        return FALSE;
}

function addField($table, $fieldName, $fieldInfo, $position=null) {
    if (checkField($GLOBALS['table_prefix'] . $table, $fieldName) === FALSE) {
        if (!mysql_query('ALTER TABLE ' . $GLOBALS['table_prefix'] . $table . ' ADD `' . $fieldName . '` ' . $fieldInfo . ' ' . $position))
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['field'] . ' ' . $GLOBALS['table_prefix'] . $table . '.' . $fieldName . ' ' . $lngi['created_err'];
        else
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['field'] . ' ' . $GLOBALS['table_prefix'] . $table . '.' . $fieldName . ' ' . $lngi['created'];
    }
}

function updateTableContent($lngi, $table, $whereClause, $script) {
    $select = 'SELECT * FROM ' . $GLOBALS['table_prefix'] . $table . ' ' . $whereClause;
    $query = mysql_query($select);
    $queryNumRows = mysql_num_rows($query);
    if ($queryNumRows == 0) {
        if (mysql_query($script)) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $table . $lngi['data_updated_suc'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $table . $lngi['data_updated_err']
                    . '<br />' . mysql_error()
                    . '<br />' . $script;
        }
    }
}

#################################################################################
#################################################################################
#################################################################################

if (isset($_GET['p']) && $_GET['p'] == 'del_inst_dir') {

    deleteAll(MODX_BASE_PATH . 'assets/modules/easy2/install/');
    header('Location: ' . $index);
    exit();
} elseif (!empty($_POST)) {

    $ms = array('suc' => array(), 'err' => array());

    // DIRS
    $_POST['path'] = str_replace('../', '', $_POST['path']);
    if (empty($_POST['path'])) {
        $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['empty_dir'];
        chref($index);
    }

    // CHECK/CREATE DIRS
    if (is_dir('../' . $_POST['path'])) {
        $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['dir_exists'] . ': ' . $_POST['path'];
        $_SESSION['easy2dir'] = $_POST['path'];
    } else {
        $_POST['path'] = preg_replace('/^\/?(.+)$/', '\\1', $_POST['path']);
        $dirs = explode('/', substr($_POST['path'], 0, -1));
        $npath = '..';
        foreach ($dirs as $dir) {
            $npath .= '/' . $dir;
            if (is_dir($npath) || empty($dir))
                continue;

            if (!mkdir($npath, 0777)) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['create_dir_err'] . ' "' . $npath . "'";
                chref($index);
            }
        }
        $_SESSION['easy2dir'] = substr($npath, 3) . '/';
        $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['dir_created'] . ': ' . $_POST['path'];
    }

    // CHECK/CREATE TABLES
    // mysql_list_fields()
    // GET All Tables
    $dbase = str_replace('`', '', $GLOBALS['dbase']);
    $res = mysql_query('SHOW TABLES FROM ' . $dbase);
    $tab = array();
    while ($row = mysql_fetch_row($res)) {
        $tab[$row[0]] = $row[0];
    }

    // easy2_dirs CHECK
    if (isset($tab['easy2_dirs'])) {
        if (!mysql_query('RENAME TABLE easy2_dirs TO ' . $GLOBALS['table_prefix'] . 'easy2_dirs')) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' easy2_dirs ' . $lngi['rename_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }

    // easy2_dirs CREATE
    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_dirs'])) {
        $createDirTable = 'CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_dirs (
                        `parent_id` INT(10) UNSIGNED NOT NULL DEFAULT \'0\',
                        `cat_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `cat_left` INT(10) NOT NULL DEFAULT \'0\',
                        `cat_right` INT(10) UNSIGNED NOT NULL DEFAULT \'0\',
                        `cat_level` INT(10) UNSIGNED NOT NULL DEFAULT \'0\',
                        `cat_name` VARCHAR(255) NOT NULL DEFAULT \'\',
                        `cat_alias` VARCHAR(255) NULL DEFAULT NULL,
                        `cat_summary` VARCHAR(255) NULL DEFAULT NULL,
                        `cat_tag` VARCHAR(255) NULL DEFAULT NULL,
                        `cat_description` TEXT NULL DEFAULT NULL,
                        `date_added` DATETIME NULL DEFAULT NULL,
                        `added_by` TINYINT(4) UNSIGNED NULL DEFAULT NULL,
                        `last_modified` DATETIME NULL DEFAULT NULL,
                        `modified_by` TINYINT(4) NULL DEFAULT NULL,
                        `cat_visible` TINYINT(4) NOT NULL DEFAULT \'1\',
                        `cat_redirect_link` VARCHAR(255) NULL DEFAULT NULL,
                        `cat_thumb_id` INT(50) UNSIGNED NULL DEFAULT NULL,
                        PRIMARY KEY (`cat_id`),
                        INDEX `cat_left` (`cat_left`)
                        ) TYPE=MyISAM';
        $queryCreateDirTable = mysql_query($createDirTable);
        if (!$queryCreateDirTable) {
            $_SESSION['easy2err'][] = __LINE__ . ': '
                    . $lngi['table'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_dirs '
                    . $lngi['create_err']
                    . '<br />' . mysql_error()
                    . '<br />' . $createTable;
            chref($index);
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ': '
                    . $lngi['table'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_dirs '
                    . $lngi['created'];
        }
    }

    // easy2_dirs fields UPGRADE for additional fields from previous version
    // additional field for 1.3.6 Beta4
    // cat_description
    addField('easy2_dirs', 'cat_description', 'TEXT NULL');

    // additional field for 1.3.6 Beta4
    // last_modified
    addField('easy2_dirs', 'last_modified', 'DATETIME NULL DEFAULT NULL');

    // additional field for 1.4.0 Beta4
    // cat_alias
    addField('easy2_dirs', 'cat_alias', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER cat_name');

    // change field for 1.4.0 RC1
    // cat_left
    if (checkField($GLOBALS['table_prefix'] . 'easy2_dirs', 'cat_left') !== FALSE
            && checkField($GLOBALS['table_prefix'] . 'easy2_dirs', 'cat_left', 'Type') === 'int(10) unsigned'
    ) {
        if (!mysql_query('ALTER TABLE ' . $GLOBALS['table_prefix'] . 'easy2_dirs CHANGE cat_left cat_left INT(10) default \'0\' NOT NULL')) {
            $_SESSION['easy2err'][] = __LINE__ . ': '
                    . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_dirs.cat_left '
                    . $lngi['upgrade_err'];
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_dirs.cat_left '
                    . $lngi['upgraded'];
        }
    }

    // rename field for 1.4.0 RC1
    // cat_tag
    if (checkField($GLOBALS['table_prefix'] . 'easy2_dirs', 'cat_tags') !== FALSE) {
        if (!mysql_query('ALTER TABLE ' . $GLOBALS['table_prefix'] . 'easy2_dirs CHANGE `cat_tags` `cat_tag` VARCHAR(255) DEFAULT NULL NULL')) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_dirs.cat_tag '
                    . $lngi['upgrade_err'];
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_dirs.cat_tag '
                    . $lngi['upgraded'];
        }
    }

    // additional field for 1.4.0 RC1
    // cat_tag
    addField('easy2_dirs', 'cat_tag', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER cat_summary');

    // additional field for 1.4.0 RC1
    // cat_summary
    addField('easy2_dirs', 'cat_summary', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER cat_alias');

    if (checkField($GLOBALS['table_prefix'] . 'easy2_dirs', 'cat_summary') === FALSE) {
        if (!mysql_query('ALTER TABLE ' . $GLOBALS['table_prefix'] . 'easy2_dirs ADD cat_summary varchar(255) default NULL AFTER cat_alias')) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_dirs.cat_summary '
                    . $lngi['created_err'];
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_dirs.cat_summary '
                    . $lngi['created'];
        }
    }

    // rearrange field for 1.4.0 RC1
    // cat_visible
    if (checkField($GLOBALS['table_prefix'] . 'easy2_dirs', 'cat_visible') !== FALSE) {
        @mysql_query('ALTER TABLE ' . $GLOBALS['table_prefix'] . 'easy2_dirs CHANGE `cat_visible` `cat_visible` TINYINT(4) DEFAULT \'1\' NOT NULL AFTER `last_modified`');
    }

    #*******************************************
    # START UPDATING DIR TABLE FOR 1.4.0 RC-2 **
    #*******************************************
    // date_added
    addField('easy2_dirs', 'date_added', 'DATETIME NULL DEFAULT NULL', 'AFTER cat_description');
    // added_by
    addField('easy2_dirs', 'added_by', 'TINYINT(4) UNSIGNED NULL DEFAULT NULL', 'AFTER date_added');
    // modified_by
    addField('easy2_dirs', 'modified_by', 'TINYINT(4) UNSIGNED NULL DEFAULT NULL', 'AFTER last_modified');
    // cat_redirect_link
    addField('easy2_dirs', 'cat_redirect_link', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER modified_by');
    #******************************************
    # ENDS UPDATING DIR TABLE FOR 1.4.0 RC-2 **
    #******************************************

    #*******************************************
    # START UPDATING DIR TABLE FOR 1.4.0 PL   **
    #*******************************************
    // cat_redirect_link
    addField('easy2_dirs', 'cat_thumb_id', 'INT(50) UNSIGNED NULL DEFAULT NULL', 'AFTER cat_redirect_link');
    #******************************************
    # ENDS UPDATING DIR TABLE FOR 1.4.0 PL   **
    #******************************************

    $res = mysql_query('SELECT cat_right FROM ' . $GLOBALS['table_prefix'] . 'easy2_dirs WHERE cat_id=1');
    if (mysql_num_rows($res) == 0) {
        $insertData = 'INSERT INTO ' . $GLOBALS['table_prefix'] . 'easy2_dirs '
                . '(parent_id, cat_id, cat_left, cat_right, cat_level, cat_name, cat_visible) '
                . 'VALUES (0,1,1,2,0,\'Easy 2\',1)';
        if (!mysql_query($insertData)) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['data'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_dirs ' . $lngi['add_err']
                    . '<br />' . mysql_error()
                    . '<br />' . $insertData;
            chref($index);
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['data'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_dirs ' . $lngi['added'];
        }
    }

    // easy2_comments renaming
    if (isset($tab['easy2_comments'])) {
        if (!mysql_query('RENAME TABLE easy2_comments TO ' . $GLOBALS['table_prefix'] . 'easy2_comments')) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_comments ' . $lngi['rename_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }

    // easy2_comments
    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_comments'])) {
        $createCommentTable = 'CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_comments (
                        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `file_id` INT(10) UNSIGNED NULL DEFAULT NULL,
                        `author` VARCHAR(64) NOT NULL DEFAULT \'\',
                        `email` VARCHAR(64) NOT NULL DEFAULT \'\',
                        `ip_address` CHAR(16) NOT NULL,
                        `comment` TEXT NOT NULL,
                        `date_added` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\',
                        `last_modified` DATETIME NULL DEFAULT NULL,
                        `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT \'0\' COMMENT \'hidden or visible\',
                        `read` TINYINT(3) UNSIGNED NULL DEFAULT \'0\' COMMENT \'has been read or not\',
                        `approved` TINYINT(3) UNSIGNED NULL DEFAULT \'0\' COMMENT \'approval sign, 0 | 1\',
                        `date_edited` DATETIME NULL DEFAULT NULL,
                        `edited_by` TINYINT(10) UNSIGNED NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY file_id (file_id)
                        ) TYPE=MyISAM';
        if (!mysql_query($createCommentTable)) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_comments '
                    . $lngi['create_err']
                    . '<br />' . mysql_error()
                    . '<br />' . $createCommentTable;
            chref($index);
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['table'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_comments '
                    . $lngi['created'];
        }
    }

    // easy2_comments fields UPGRADE for additional fields from previous version
    // additional field for 1.4.0 Beta1
    // ip_address
    if (checkField($GLOBALS['table_prefix'] . 'easy2_comments', 'ip_address') === FALSE) {
        if (!mysql_query('ALTER TABLE ' . $GLOBALS['table_prefix'] . 'easy2_comments ADD ip_address char(16) NOT NULL AFTER email')) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_comments.ip_address '
                    . $lngi['created_err'];
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_comments.ip_address '
                    . $lngi['created'];
        }
    }

    #************************************************
    # START UPDATING COMMENTS TABLE FOR 1.4.0 RC-2 **
    #************************************************
    // read
    addField('easy2_comments', 'read', 'TINYINT(3) UNSIGNED NULL DEFAULT \'0\' COMMENT \'has been read or not\'');
    // approved
    addField('easy2_comments', 'approved', 'TINYINT(3) UNSIGNED NULL DEFAULT \'0\' COMMENT \'approval sign, 0 | 1\'');
    // date_edited
    addField('easy2_comments', 'date_edited', 'DATETIME NULL DEFAULT NULL');
    // edited_by
    addField('easy2_comments', 'edited_by', 'TINYINT(10) UNSIGNED NULL DEFAULT NULL');
    #***********************************************
    # ENDS UPDATING COMMENTS TABLE FOR 1.4.0 RC-2 **
    #***********************************************
    // easy2_files CHECK
    if (isset($tab['easy2_files'])) {
        if (!mysql_query('RENAME TABLE easy2_files TO ' . $GLOBALS['table_prefix'] . 'easy2_files')) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_files ' . $lngi['rename_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }

    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_files'])) {
        // easy2_files CREATE
        $createFileTable = 'CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_files (
                        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `dir_id` INT(10) UNSIGNED NOT NULL DEFAULT \'0\',
                        `filename` VARCHAR(255) NOT NULL DEFAULT \'\',
                        `size` VARCHAR(32) NOT NULL DEFAULT \'\',
                        `width` INT(10) UNSIGNED NULL DEFAULT NULL,
                        `height` INT(10) UNSIGNED NULL DEFAULT NULL,
                        `alias` VARCHAR(255) NOT NULL DEFAULT \'\',
                        `summary` VARCHAR(255) NOT NULL DEFAULT \'\',
                        `tag` VARCHAR(255) NULL DEFAULT \'\',
                        `description` TEXT NULL,
                        `date_added` DATETIME NULL DEFAULT NULL,
                        `added_by` TINYINT(4) NULL DEFAULT NULL,
                        `last_modified` DATETIME NULL DEFAULT NULL,
                        `modified_by` TINYINT(4) NULL DEFAULT NULL,
                        `comments` INT(10) UNSIGNED NOT NULL DEFAULT \'0\',
                        `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'1\',
                        `redirect_link` VARCHAR(255) NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                        ) TYPE=MyISAM';
        if (!mysql_query($createFileTable)) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_files ' . $lngi['create_err']
                    . '<br />' . mysql_error()
                    . '<br />' . $createFileTable;
            chref($index);
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_files ' . $lngi['created'];
        }
    }

    // rename field for 1.4.0 RC1
    // tag
    if (checkField($GLOBALS['table_prefix'] . 'easy2_files', 'tags') !== FALSE) {
        if (!mysql_query('ALTER TABLE ' . $GLOBALS['table_prefix'] . 'easy2_files CHANGE `tags` `tag` VARCHAR(255) DEFAULT NULL NULL')) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_files.tags '
                    . $lngi['upgrade_err'];
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_files.tag '
                    . $lngi['upgraded'];
        }
    }

    // additional field for 1.4.0 RC1
    // summary
    addField('easy2_files', 'summary', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER name');

    // additional field for 1.4.0 RC1
    // tag
    addField('easy2_files', 'tag', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER summary');

    #*********************************************
    # START UPDATING FILES TABLE FOR 1.4.0 RC-2 **
    #*********************************************
    // additional field added_by
    addField('easy2_files', 'added_by', 'TINYINT(4) UNSIGNED NULL DEFAULT NULL', 'AFTER date_added');
    // additional field modified_by
    addField('easy2_files', 'modified_by', 'TINYINT(4) NULL DEFAULT NULL', 'AFTER last_modified');
    // additional field width
    addField('easy2_files', 'width', 'INT(10) UNSIGNED NULL DEFAULT NULL', 'AFTER size');
    // additional field height
    addField('easy2_files', 'height', 'INT(10) UNSIGNED NULL DEFAULT NULL', 'AFTER width');
    // additional redirect_link
    addField('easy2_files', 'redirect_link', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER status');
    #********************************************
    # ENDS UPDATING FILES TABLE FOR 1.4.0 RC-2 **
    #********************************************

    // rename field for 1.4.0 RC4
    // name => alias
    if (checkField($GLOBALS['table_prefix'] . 'easy2_files', 'name') !== FALSE) {
        if (!mysql_query('ALTER TABLE ' . $GLOBALS['table_prefix'] . 'easy2_files CHANGE `name` `alias` VARCHAR(255) DEFAULT NULL NULL')) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_files.name '
                    . $lngi['upgrade_err'];
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['field'] . ' '
                    . $GLOBALS['table_prefix'] . 'easy2_files.alias '
                    . $lngi['upgraded'];
        }
    }

    // adding ignore IP table for 1.4.0 Beta4
    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_ignoredip'])) {
        if (mysql_query('CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_ignoredip (
                        id int(10) unsigned NOT NULL auto_increment,
                        ign_date datetime NOT NULL,
                        ign_ip_address char(16) NOT NULL,
                        ign_username varchar(64) NOT NULL,
                        ign_email varchar(64) default NULL,
                        PRIMARY KEY (id)
                        ) TYPE=MyISAM')) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_ignoredip ' . $lngi['created'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_ignoredip ' . $lngi['create_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }

    #**************************************
    # START ADDING TABLES FOR 1.4.0 RC-2 **
    #**************************************
    // adding easy2_configs table for 1.4.0 RC2
    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_configs'])) {
        if (mysql_query('CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_configs (
                        `cfg_key` VARCHAR(50) NOT NULL DEFAULT \'\',
                        `cfg_val` VARCHAR(255) NULL DEFAULT NULL,
                        UNIQUE INDEX `cfg_key` (`cfg_key`)
                        ) TYPE=MyISAM')) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_configs ' . $lngi['created'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_configs ' . $lngi['create_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }
    // adding easy2_plugins table for 1.4.0 RC2
    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_plugins'])) {
        if (mysql_query('CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_plugins (
                        `id` INT(10) NOT NULL AUTO_INCREMENT,
                        `name` VARCHAR(255) NULL DEFAULT NULL,
                        `description` VARCHAR(255) NULL DEFAULT NULL,
                        `disabled` TINYINT(1) NULL DEFAULT NULL,
                        `indexfile` VARCHAR(255) NULL DEFAULT NULL,
                        `events` VARCHAR(255) NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                        ) TYPE=MyISAM')) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_plugins ' . $lngi['created'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_plugins ' . $lngi['create_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }

    // adding easy2_plugins table for 1.4.0 RC2
    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_plugin_events'])) {
        if (mysql_query('CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_plugin_events (
                        `pluginid` INT(10) NOT NULL,
                        `evtid` INT(10) NOT NULL,
                        `priority` INT(10) NOT NULL
                        ) TYPE=MyISAM')) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_plugin_events ' . $lngi['created'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_plugin_events ' . $lngi['create_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }

    // adding easy2_slideshows table for 1.4.0 RC2
    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_slideshows'])) {
        if (mysql_query('CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_slideshows (
                        `id` INT(10) NOT NULL AUTO_INCREMENT,
                        `name` VARCHAR(255) NULL DEFAULT NULL,
                        `description` VARCHAR(255) NULL DEFAULT NULL,
                        `indexfile` VARCHAR(255) NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                        ) TYPE=MyISAM')) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_slideshows ' . $lngi['created'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_slideshows ' . $lngi['create_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }
    // update easy2_slideshows content for 1.4.0 RC2
    $updateSlideShows = array(
        array(
            'name' => 'simple'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_slideshows` (`name`, `description`, `indexfile`) VALUES ('simple', 'A Simple jQuery slideshow by &lt;a href=&quot;http://jonraasch.com/blog/a-simple-jquery-slideshow&quot; target=&quot;_blank&quot;&gt;Jon Raasch&lt;/a&gt;', 'assets/libs/slideshows/simplejquery/simple.php')"
        )
        , array(
            'name' => 'galleryview'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_slideshows` (`name`, `description`, `indexfile`) VALUES ('galleryview', '&lt;a href=&quot;http://spaceforaname.com/galleryview&quot; target=&quot;_blank&quot;&gt;http://spaceforaname.com/galleryview&lt;/a&gt;', 'assets/libs/slideshows/galleryview/galleryview.php')"
        )
        , array(
            'name' => 'galleriffic'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_slideshows` (`name`, `description`, `indexfile`) VALUES ('galleriffic', '&lt;a href=&quot;http://www.twospy.com/galleriffic/&quot; target=&quot;_blank&quot;&gt;http://www.twospy.com/galleriffic/&lt;/a&gt;', 'assets/libs/slideshows/galleriffic/galleriffic.php')"
        )
        , array(
            'name' => 'smoothgallery'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_slideshows` (`name`, `description`, `indexfile`) VALUES ('smoothgallery', '&lt;a href=&quot;http://smoothgallery.jondesign.net/&quot; target=&quot;_blank&quot;&gt;http://smoothgallery.jondesign.net/&lt;/a&gt;', 'assets/libs/slideshows/smoothgallery/smoothgallery.php')"
        )
        , array(
            'name' => 'contentflow'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_slideshows` (`name`, `description`, `indexfile`) VALUES ('contentflow', '&lt;a href=&quot;http://www.jacksasylum.eu/ContentFlow/index.php&quot; target=&quot;_blank&quot;&gt;http://www.jacksasylum.eu/ContentFlow/index.php&lt;/a&gt;', 'assets/libs/slideshows/contentflow/contentflow.php')"
        )
    );

    for ($i = 0; $i < count($updateSlideShows); $i++) {
        updateTableContent($lngi, 'easy2_slideshows', 'WHERE name=\'' . $updateSlideShows[$i]['name'] . '\'', $updateSlideShows[$i]['script']);
    }

    // adding easy2_users_mgr table for 1.4.0 RC2
    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_users_mgr'])) {
        if (mysql_query('CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_users_mgr (
                        `id` INT(10) NOT NULL AUTO_INCREMENT,
                        `membergroup_id` INT(10) NULL DEFAULT NULL COMMENT \'modx groups id\',
                        `permissions` VARCHAR(255) NULL DEFAULT NULL COMMENT \'e2g_access\',
                        PRIMARY KEY (`id`)
                        ) TYPE=MyISAM')) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_users_mgr ' . $lngi['created'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_users_mgr ' . $lngi['create_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }

    // adding easy2_viewers table for 1.4.0 RC2
    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_viewers'])) {
        if (mysql_query('CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_viewers (
                        `id` INT(10) NOT NULL AUTO_INCREMENT,
                        `name` VARCHAR(50) NULL DEFAULT NULL,
                        `alias` VARCHAR(50) NULL DEFAULT NULL,
                        `description` VARCHAR(255) NULL DEFAULT NULL,
                        `disabled` TINYINT(1) NULL DEFAULT \'0\',
                        `headers_css` TEXT NULL COMMENT \'css header links\',
                        `autoload_css` TINYINT(1) NULL DEFAULT \'0\' COMMENT \'auto load css headers\',
                        `headers_js` TEXT NULL COMMENT \'js header links\',
                        `autoload_js` TINYINT(1) NULL DEFAULT \'0\' COMMENT \'auto load js headers\',
                        `headers_html` TEXT NULL COMMENT \'html block headers\',
                        `autoload_html` TINYINT(1) NULL DEFAULT \'0\' COMMENT \'auto load html block headers\',
                        `glibact` VARCHAR(255) NULL DEFAULT NULL COMMENT \'javascript action on images link\',
                        `clibact` VARCHAR(255) NULL DEFAULT NULL COMMENT \'javascript action on comment link\',
                        PRIMARY KEY (`id`)
                        ) TYPE=MyISAM')) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_viewers ' . $lngi['created'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_viewers ' . $lngi['create_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }
    // update easy2_viewers content for 1.4.0 RC2
    $updateViewers = array(
        array(
            'name' => 'highslide'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_viewers` "
            . "(`name`, `alias`, `description`, `disabled`, `headers_css`, `autoload_css`, `headers_js`, `autoload_js`, `headers_html`, `autoload_html`, `glibact`, `clibact`) "
            . "VALUES ('highslide', 'highslide 4.1.8', '&lt;a href=&quot;http://highslide.com/&quot; target=&quot;_blank&quot;&gt;http://highslide.com/&lt;/a&gt;', 0, 'assets/libs/highslide/highslide.css', 1, 'assets/libs/highslide/highslide-full.js\r\n| assets/libs/highslide/e2g.highslide.js', 1, '&lt;script type=&quot;text/javascript&quot;&gt;\r\n    hs.addSlideshow({\r\n        slideshowGroup: \'[+easy2:show_group+]\',\r\n        interval: 5000,\r\n        repeat: false,\r\n        useControls: true,\r\n        fixedControls: \'fit\',\r\n        overlayOptions: {\r\n            opacity: .6,\r\n            position: \'bottom center\'\r\n        }\r\n    });\r\n &lt;/script&gt;', 1, 'class=&quot;highslide&quot; onclick=&quot;return hs.expand(this, {slideshowGroup: \'[+easy2:show_group+]\'})&quot;', 'onclick=&quot;return hs.htmlExpand(this, { objectType: \'iframe\' } )&quot;')"
        )
        , array(
            'name' => 'lightbox2'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_viewers` "
            . "(`name`, `alias`, `description`, `disabled`, `headers_css`, `autoload_css`, `headers_js`, `autoload_js`, `headers_html`, `autoload_html`, `glibact`, `clibact`) "
            . "VALUES ('lightbox2', 'lightbox2', '&lt;a href=&quot;http://www.huddletogether.com/projects/lightbox2/&quot; target=&quot;_blank&quot;&gt;http://www.huddletogether.com/projects/lightbox2/&lt;/a&gt;', 0, 'assets/libs/lightbox2.04/css/lightbox.css\r\n| assets/libs/highslide/highslide.css', 1, 'assets/libs/lightbox2.04/js/prototype.js\r\n| assets/libs/lightbox2.04/js/scriptaculous.js?load=effects,builder\r\n| assets/libs/lightbox2.04/js/lightbox.js\r\n| assets/libs/highslide/highslide-iframe.js', 1, '', 0, 'class=&quot;[+easy2:show_group+]&quot; rel=&quot;lightbox[[+easy2:show_group+]]&quot;', 'onclick=&quot;return hs.htmlExpand(this, { objectType: \'iframe\'} )&quot;')"
        )
        , array(
            'name' => 'colorbox'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_viewers` "
            . "(`name`, `alias`, `description`, `disabled`, `headers_css`, `autoload_css`, `headers_js`, `autoload_js`, `headers_html`, `autoload_html`, `glibact`, `clibact`) "
            . "VALUES ('colorbox', 'colorbox 1.3.8 (jq)', '&lt;a href=&quot;http://colorpowered.com/colorbox/&quot; target=&quot;_blank&quot;&gt;http://colorpowered.com/colorbox/&lt;/a&gt;', 0, 'assets/libs/colorbox/colorbox.css', 1, 'assets/libs/jquery/jquery-1.4.2.min.js\r\n| assets/libs/colorbox/jquery.colorbox-min.js\r\n| assets/libs/colorbox/e2g.colorbox.js', 1, '', 0, 'class=&quot;cboxElement&quot; rel=&quot;group[[+easy2:show_group+]]&quot;', 'class=&quot;iframe&quot;')"
        )
        , array(
            'name' => 'fancybox'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_viewers` "
            . "(`name`, `alias`, `description`, `disabled`, `headers_css`, `autoload_css`, `headers_js`, `autoload_js`, `headers_html`, `autoload_html`, `glibact`, `clibact`) "
            . "VALUES ('fancybox', 'fancybox 1.3.1 (jq)', '&lt;a href=&quot;http://fancybox.net/&quot; target=&quot;_blank&quot;&gt;http://fancybox.net/&lt;/a&gt;', 0, 'assets/libs/fancybox/jquery.fancybox-1.3.1.css', 1, 'http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js\r\n| assets/libs/fancybox/jquery.fancybox-1.3.1.pack.js\r\n| assets/libs/fancybox/jquery.easing-1.3.pack.js\r\n| assets/libs/fancybox/jquery.mousewheel-3.0.2.pack.js\r\n| assets/libs/fancybox/e2g.fancybox.js', 1, '&lt;script type=&quot;text/javascript&quot;&gt;\r\n  $(document).ready(function() {\r\n    $(&quot;a.[+easy2:show_group+]&quot;).fancybox({\r\n        \'padding\'         : 10,\r\n        \'margin\'          : 0,\r\n        \'transitionIn\'    : \'elastic\',\r\n        \'transitionOut\'   : \'elastic\',\r\n        \'titlePosition\'   : \'over\',\r\n        \'type\'            : \'image\',\r\n        \'titleFormat\'     : function(title, currentArray, currentIndex, currentOpts) {\r\n            return \'&lt;span id=&quot;fancybox-title-over&quot;&gt;Image \' + (currentIndex + 1) + \' / \' + currentArray.length + (title.length ? \' &amp;nbsp; \' + title : \'\') + \'&lt;/span&gt;\';\r\n        }\r\n    });\r\n  });\r\n &lt;/script&gt;', 1, 'class=&quot;[+easy2:show_group+]&quot; rel=&quot;[+easy2:show_group+]&quot;', 'class=&quot;comment&quot;')"
        )
        , array(
            'name' => 'floatbox'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_viewers` "
            . "(`name`, `alias`, `description`, `disabled`, `headers_css`, `autoload_css`, `headers_js`, `autoload_js`, `headers_html`, `autoload_html`, `glibact`, `clibact`) "
            . "VALUES ('floatbox', 'floatbox 4.04', '&lt;a href=&quot;http://randomous.com/floatbox/home&quot; target=&quot;_blank&quot;&gt;http://randomous.com/floatbox/home&lt;/a&gt;', 0, 'assets/libs/floatbox/floatbox.css', 1, 'assets/libs/floatbox/floatbox.js\r\n| assets/libs/floatbox/e2g.options.js', 1, '', 0, 'class=&quot;floatbox&quot; data-fb-options=&quot;doSlideshow:false group:[+easy2:show_group+] type:img&quot;', 'class=&quot;floatbox&quot; data-fb-options=&quot;width:400 height:320 enableDragResize:true controlPos:tr innerBorder:0&quot;')"
        )
        , array(
            'name' => 'lightwindow'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_viewers` "
            . "(`name`, `alias`, `description`, `disabled`, `headers_css`, `autoload_css`, `headers_js`, `autoload_js`, `headers_html`, `autoload_html`, `glibact`, `clibact`) "
            . "VALUES ('lightwindow', 'lightwindow 2.0 (pt)', '&lt;a href=&quot;http://www.p51labs.com/lightwindow/&quot; target=&quot;_blank&quot;&gt;http://www.p51labs.com/lightwindow/&lt;/a&gt;', 0, 'assets/libs/lightwindow/css/lightwindow.css', 1, 'assets/libs/lightwindow/js/prototype.js\r\n| assets/libs/lightwindow/js/scriptaculous.js?load=effects\r\n| assets/libs/lightwindow/js/lightwindow.src.js', 1, '', 0, 'class=&quot;lightwindow&quot; rel=&quot;Gallery[[+easy2:show_group+]]&quot; params=&quot;lightwindow_type=image&quot;', 'class=&quot;lightwindow&quot; params=&quot;lightwindow_type=external,lightwindow_width=400,lightwindow_height=250&quot;')"
        )
        , array(
            'name' => 'shadowbox'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_viewers` "
            . "(`name`, `alias`, `description`, `disabled`, `headers_css`, `autoload_css`, `headers_js`, `autoload_js`, `headers_html`, `autoload_html`, `glibact`, `clibact`) "
            . "VALUES ('shadowbox', 'shadowbox 3.0.3 (base)', '&lt;a href=&quot;http://www.shadowbox-js.com/&quot; target=&quot;_blank&quot;&gt;http://www.shadowbox-js.com/&lt;/a&gt;', 0, 'assets/libs/shadowbox/shadowbox.css', 1, 'assets/libs/shadowbox/shadowbox.js\r\n| assets/libs/shadowbox/e2g.shadowbox.js', 1, '', 0, 'rel=&quot;shadowbox[[+easy2:show_group+]];player=img&quot;', 'rel=&quot;shadowbox;width=400;height=250;player=iframe&quot;')"
        )
        , array(
            'name' => 'slimbox'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_viewers` "
            . "(`name`, `alias`, `description`, `disabled`, `headers_css`, `autoload_css`, `headers_js`, `autoload_js`, `headers_html`, `autoload_html`, `glibact`, `clibact`) "
            . "VALUES ('slimbox', 'slimbox 1.71 (mt)', '&lt;a href=&quot;http://www.digitalia.be/software/slimbox&quot; target=&quot;_blank&quot;&gt;http://www.digitalia.be/software/slimbox&lt;/a&gt;', 0, 'assets/libs/slimbox-1.71/css/slimbox.css\r\n| assets/libs/highslide/highslide.css', 1, 'assets/libs/slimbox-1.71/js/mootools.js\r\n| assets/libs/slimbox-1.71/js/slimbox.js\r\n| assets/libs/highslide/highslide-iframe.js', 1, '', 0, 'rel=&quot;lightbox[[+easy2:show_group+]]&quot;', 'onclick=&quot;return hs.htmlExpand(this, { objectType: \'iframe\'} )&quot;')"
        )
        , array(
            'name' => 'slimbox2'
            , 'script' => "INSERT INTO `" . $GLOBALS['table_prefix'] . "easy2_viewers` "
            . "(`name`, `alias`, `description`, `disabled`, `headers_css`, `autoload_css`, `headers_js`, `autoload_js`, `headers_html`, `autoload_html`, `glibact`, `clibact`) "
            . "VALUES ('slimbox2', 'slimbox2 2.04 (jq)', '&lt;a href=&quot;http://www.digitalia.be/software/slimbox2&quot; target=&quot;_blank&quot;&gt;http://www.digitalia.be/software/slimbox2&lt;/a&gt;', 0, 'assets/libs/slimbox-2.04/css/slimbox2.css\r\n| assets/libs/highslide/highslide.css', 1, 'assets/libs/jquery/jquery-1.4.2.min.js\r\n| assets/libs/slimbox-2.04/js/slimbox2.js\r\n| assets/libs/highslide/highslide-iframe.js', 1, '', 0, 'rel=&quot;lightbox[[+easy2:show_group+]]&quot;', 'onclick=&quot;return hs.htmlExpand(this, { objectType: \'iframe\'} )&quot;')"
        )
    );

    for ($i = 0; $i < count($updateViewers); $i++) {
        updateTableContent($lngi, 'easy2_viewers', 'WHERE name=\'' . $updateViewers[$i]['name'] . '\'', $updateViewers[$i]['script']);
    }

    // adding easy2_webgroup_access table for 1.4.0 RC2
    if (!isset($tab[$GLOBALS['table_prefix'] . 'easy2_webgroup_access'])) {
        if (mysql_query('CREATE TABLE IF NOT EXISTS ' . $GLOBALS['table_prefix'] . 'easy2_webgroup_access (
                        `webgroup_id` INT(10) NOT NULL,
                        `type` VARCHAR(10) NULL DEFAULT NULL COMMENT \'dir/file type\',
                        `id` INT(10) NULL DEFAULT NULL COMMENT \'dir/file id\'
                        ) TYPE=MyISAM')) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_webgroup_access ' . $lngi['created'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['table'] . ' ' . $GLOBALS['table_prefix'] . 'easy2_webgroup_access ' . $lngi['create_err']
                    . '<br />' . mysql_error();
            chref($index);
        }
    }

    #*************************************
    # ENDS ADDING TABLES FOR 1.4.0 RC-2 **
    #*************************************
    // MODULE

    if (empty($e2g['mod_id']) || $e2g['mod_id'] == '') {
        $mod = '$o = include_once MODX_BASE_PATH . \'assets/modules/easy2/index.php\';' . "\n";
        $mod .= 'return $o;' . "\n";
        $res = mysql_query('UPDATE ' . $GLOBALS['table_prefix'] . 'site_modules SET modulecode = \'' . mysql_escape_string($mod) . '\' WHERE id =\'' . $_GET['id'] . '\'');
        if ($res) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['mod_updated'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['mod_update_err'] . '<br />' . mysql_error();
            chref($index);
        }
    }
    else
        $mod_id = $e2g['mod_id'];

    // SNIPPET

    if (empty($snippetId) || $snippetId == '') {
        $res = mysql_query('SELECT id FROM ' . $GLOBALS['table_prefix'] . 'site_snippets WHERE name =\'easy2\'');
        $snippet = '$o = include MODX_BASE_PATH . \'assets/modules/easy2/snippet.easy2gallery.php\';' . "\n";
        $snippet .= 'return $o;' . "\n";
        if (mysql_num_rows($res) == 0) {
            $sql = "INSERT INTO " . $GLOBALS['table_prefix'] . "site_snippets "
                    . "(name, description, snippet, moduleguid, locked, properties, category) "
                    . "VALUES('easy2', 'Easy 2 Gallery', '" . mysql_escape_string($snippet) . "', '', '1','', '0')";

            if (mysql_query($sql)) {
                $snippetId = mysql_insert_id();
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['snippet_added'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['snippet_add_err'];
                chref($index);
            }
        } else {
            $select = mysql_query('SELECT snippet FROM ' . $GLOBALS['table_prefix'] . 'site_snippets WHERE name =\'easy2\'');
            $result = mysql_result($select, 0, 0);
            if ($result != $snippet) {
                $sql = 'UPDATE ' . $GLOBALS['table_prefix'] . 'site_snippets SET snippet=\'' . mysql_escape_string($snippet) . '\' WHERE name =\'easy2\' LIMIT 1';
                if (mysql_query($sql)) {
                    $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['snippet_updated'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['snippet_update_err'];
                    chref($index);
                }
            }
        }
    }
    else
        $snippetId = $e2g['snippet_id'];

    // PLUGIN
    if (empty($pluginId) || $pluginId == '') {
        $select = 'SELECT id FROM ' . $GLOBALS['table_prefix'] . 'site_plugins WHERE name=\'easy2\'';
        $query = mysql_query($select);
        if (mysql_num_rows($query) == 0) {
            $plugin = '$o = include MODX_BASE_PATH . \'assets/modules/easy2/plugin.easy2gallery.php\';' . "\n";
            $plugin .= 'return $o;' . "\n";
            $insert = 'INSERT INTO ' . $GLOBALS['table_prefix'] . 'site_plugins '
                    . '(name,description,plugincode) '
                    . "VALUES ('easy2', 'Easy 2 Gallery plugin','" . mysql_escape_string($plugin) . "')"
            ;
            if (mysql_query($insert)) {
                $pluginId = mysql_insert_id();
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['plugin_added'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['plugin_add_err'];
                chref($index);
            }
        }
        else
            $pluginId = mysql_result($query, 0, 0);
    }
    else
        $pluginId = $e2g['plugin_id'];

    // PLUGIN EVENTS
    if (!empty($pluginId)) {
        $nEvtIds = array('90', '94'); // Plugin event's IDs. Will be added more later.
        $delete = mysql_query('DELETE FROM ' . $GLOBALS['table_prefix'] . 'site_plugin_events WHERE pluginid=\'' . $pluginId . '\'');
        if ($delete) {
            foreach ($nEvtIds as $nEvtId) {
                mysql_query('INSERT INTO ' . $GLOBALS['table_prefix'] . 'site_plugin_events '
                        . '(pluginid, evtid, priority) '
                        . "VALUES ('$pluginId','$nEvtId','0')"
                );
            }
        } else
            $_SESSION['easy2err'][] = __LINE__ . ': ' . __LINE__ . ' Error: ' . mysql_error();
    }

    /**
     * goldsky -- add the file's/folder's names restoration from the previous installation version.
     */
    if (restore(MODX_BASE_PATH . $e2g['dir'], 1)) {
        if ($_restore['d'] != 0 || $_restore['f'] != 0)
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $lngi['restore_suc'];
    } else {
        $_SESSION['easy2err'][] = __LINE__ . ': ' . $lngi['restore_err'];
        chref($index);
    }

    $_SESSION['easy2suc']['success'] = '<br /><br /><br />' . $lngi['success']
            . '<br /><br /><input type="button" value="' . $lngi['del_inst_dir'] . '" onclick="document.location.href=\'' . $index . '&p=del_inst_dir\'">';

    // SAVE DIR
    // TODO: switch to database checking
    unset($_SESSION['easy2dir']);

    chref($index);
} else {

    // SNIPPET
    if (empty($e2g['snippet_id']) || $e2g['snippet_id'] == '') {
        $select = mysql_query('SELECT id FROM ' . $GLOBALS['table_prefix'] . 'site_snippets WHERE name =\'easy2\'');
        if (mysql_num_rows($select) > 0)
            $snippetId = mysql_result($select, 0, 0);
        mysql_free_result($select);
    }
    else
        $snippetId = $e2g['snippet_id'];

    // PLUGIN
    if (empty($e2g['plugin_id']) || $e2g['plugin_id'] == '') {
        $select = mysql_query('SELECT id FROM ' . $GLOBALS['table_prefix'] . 'site_plugins WHERE name=\'easy2\'');
        if (mysql_num_rows($select) > 0)
            $pluginId = mysql_result($select, 0, 0);
        mysql_free_result($select);
    }
    else
        $pluginId = $e2g['plugin_id'];
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>
        <head>
            <title>Easy 2 Gallery <?php echo E2G_VERSION; ?> installation</title>
            <link rel="stylesheet" type="text/css"
                  href="media/style/<?php echo $_t; ?>/style.css" />
            <script type="text/javascript" src="media/script/tabpane.js"></script>
        </head>
        <body>
            <div class="sectionHeader">Easy 2 Gallery <?php echo E2G_VERSION; ?> Installation</div>
            <div class="sectionBody">
                <div class="tab-pane" id="easy2Pane"><script type="text/javascript">
                    tpResources = new WebFXTabPane(document.getElementById("easy2Pane"));
                    </script>
                    <div class="tab-page" id="install">
                        <h2 class="tab"><?php echo $lng['install']; ?></h2>
                        <script type="text/javascript">
                            tpResources.addTabPage(document.getElementById("install"));
                        </script>
                    <?php
                    if (count($_SESSION['easy2err']) > 0 || count($_SESSION['easy2suc']) > 0) {
                        $suc = $err = '';
                        if (count($_SESSION['easy2err']) > 0) {
                            $err = '<p class="warning">' . implode('<br />', $_SESSION['easy2err']) . '</p>';
                            $_SESSION['easy2err'] = array();
                            $err .= '<br /><br /><a href="#" onclick="document.location.href=\'' . $index . '\'"><b>' . $lngi['back'] . '</b></a>';
                        }
                        if (count($_SESSION['easy2suc']) > 0) {
                            $suc = '<p class="success">' . implode('<br />', $_SESSION['easy2suc']) . '</p>';
                            $_SESSION['easy2suc'] = array();
                        }
                        echo $suc . $err;
                    } else {
                    ?> <br />
                        <form method="post" action="">
                            <table cellspacing="0" cellpadding="0">
                                <tr>
                                    <td><b><?php echo $lngi['path']; ?>:</b></td>
                                    <td><input name="path" type="text" style="width: 100%"
                                               value="<?php echo $e2g['dir']; ?>" /> <input type="hidden"
                                               name="mod_id"
                                               value="<?php echo (!empty($e2g['mod_id']) ? $e2g['mod_id'] : $_GET['id']); ?>" />
                                        <input type="hidden" name="plugin_id"
                                               value="<?php echo $pluginId; ?>" /> <input type="hidden"
                                               name="snippet_id" value="<?php echo $snippetId; ?>" /></td>
                                </tr>
                            </table>
                            <div><?php echo htmlspecialchars_decode($lngi['comment1'], ENT_QUOTES); ?></div>
                            <div><?php echo htmlspecialchars_decode($lngi['comment'], ENT_QUOTES); ?></div>
                            <div style="color: green; font-weight: bold; font-size: 1.5em;"><?php echo htmlspecialchars_decode($lngi['system_check']); ?> :</div>

                        <?php
                        $iconOk = '<img src="' . MODX_BASE_URL . 'assets/modules/easy2/includes/tpl/icons/action_check.png" alt="" /> ';
                        $iconBad = '<img src="' . MODX_BASE_URL . 'assets/modules/easy2/includes/tpl/icons/action_delete.png" alt="" /> ';
                        $disabled = '';
                        echo '<ul>';
                        // PHP version
                        if (version_compare(PHP_VERSION, '5.2.0', '<')) {
                            $disabled = 'disabled="disabled"';
                            echo '<li>';
                            echo $iconBad . 'PHP version ' . PHP_VERSION . ' (Min: 5.2.0)';
                            echo '</li>';
                        } else {
                            echo '<li>';
                            echo $iconOk . 'PHP version ' . PHP_VERSION;
                            echo '</li>';
                        }

                        // PHP magic_quotes_gpc()
                        if (function_exists('get_magic_quotes_gpc')) {
//        $disabled = 'disabled="disabled"';
                            echo '<li>';
                            echo $iconBad . 'PHP magic_quotes_gpc()=ON. Try to disable it from .htaccess or php.ini';
                            echo '</li>';
                        } else {
                            echo '<li>';
                            echo $iconOk . 'PHP magic_quotes_gpc()=OFF';
                            echo '</li>';
                        }

                        // PHP Multibyte String
                        if (function_exists('mb_get_info') && is_array(mb_get_info())) {
                            echo '<li>';
                            echo $iconOk . 'PHP Multibyte String enabled';
                            echo '</li>';
                        } else {
                            $disabled = 'disabled="disabled"';
                            echo '<li>';
                            echo $iconBad . 'PHP Multibyte String disabled';
                            echo '</li>';
                        }

                        // PHP Zipclass
                        if (class_exists('ZipArchive')) {
                            echo '<li>';
                            echo $iconOk . 'PHP ZipArchive';
                            echo '</li>';
                        } else {
//        $disabled = 'disabled="disabled"';
                            echo '<li>';
                            echo $iconBad . 'PHP ZipArchive';
                            echo '</li>';
                        }

                        // Easy 2 javascript library folders
                        if (is_dir('../assets/libs')) {
                            echo '<li>';
                            echo $iconOk . 'assets/libs';
                            echo '</li>';
                        } else {
                            $disabled = 'disabled="disabled"';
                            echo '<li>';
                            echo $iconBad . 'assets/libs';
                            echo '</li>';
                        }

                        $style = '';
                        if ($disabled == 'disabled="disabled"')
                            $style = 'style="color:gray;"';

                        echo '</ul>';
                        ?>
                        <input type="submit" <?php echo $style; ?> value="<?php echo htmlspecialchars_decode($lngi['ok']); ?>" <?php echo $disabled; ?> />
                    </form>
                    <?php
                    }
                    ?></div>
            </div>
        </div>
    </body>
</html>
<?php }

return;