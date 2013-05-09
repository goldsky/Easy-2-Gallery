<?php

class install {

    public $modx;
    public $e2g;
    public $lngi;

    public function __construct(&$modx, $e2g) {
        $this->modx = &$modx;
        $this->e2g = $e2g;
        $this->lngi = $this->loadLanguage();
    }

    public function loadLanguage() {
        $instLangFile = dirname(dirname(__FILE__)) . 'includes/langs/' . $this->modx->config['manager_language'] . '.inst.inc.php';
        if (file_exists(realpath($instLangFile))) {
            include $instLangFile;
            $lngi = $e2g_lang[$this->modx->config['manager_language']];
        } else {
            $englishLangFile = dirname(dirname(__FILE__)) . '/includes/langs/english.inst.inc.php';
            if (file_exists(realpath($englishLangFile))) {
                include $englishLangFile;
                $lngi = $e2g_lang['english'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': missing english language file: ' . $englishLangFile;
                return FALSE;
            }
        }

        return $lngi;
    }

    /**
     * Redirector
     * @param string $href hyperlink address
     */
    public function chref($href) {
        header('Location: ' . $href);
        exit();
    }

    /**
     * To restore file's and folder's name of previous version's installation
     * @param   string  $path   path to file or folder
     * @param   int     $pid    current parent ID
     */
    public function restore($path, $pid) {
        $_restore['d'] = 0;
        $_restore['f'] = 0;

        $oldConfigFile = realpath(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php');
        if (!empty($oldConfigFile) && file_exists($oldConfigFile)) {
            require $oldConfigFile;
        } else {
            $defConfigFile = realpath(E2G_MODULE_PATH . 'includes/configs/default.config.easy2gallery.php');
            if (!empty($defConfigFile) && file_exists($defConfigFile)) {
                require $defConfigFile;
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': missing default config file.';
                return FALSE;
            }
        }

        $timeStart = microtime(TRUE);
        /**
         * STORE variable arrays for synchronizing comparison
         */
        // MySQL Dir list
        $res = mysql_query('SELECT cat_id,cat_name,parent_id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs WHERE parent_id=' . $pid);

        $oldDirs = array();
        $newDirs = array();
        if ($res) {
            while ($l = mysql_fetch_assoc($res)) {
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
        $res = mysql_query('SELECT id,filename,size FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files WHERE dir_id=' . $pid);

        $oldFiles = array();
        $newFiles = array();
        if ($res) {
            while ($l = mysql_fetch_assoc($res)) {
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
                $baseDir = basename(MODX_BASE_PATH . $this->e2g['dir']);

                /**
                 * goldsky -- $this->restore FOLDER's name of previous version's process here!
                 */
                $name = basename($f);
                $oldBasename = $oldDirs[$name]['name'];
                $newBasename = $newDirs[$name]['name'];
                if ($name == '_thumbnails' && $name != $baseDir && $name != '')
                    continue;

                if ($this->validFolder($f)) {
                    if (isset($oldDirs[$name])) {
                        $nf = MODX_BASE_PATH . $this->e2g['dir'] . $oldBasename;
                        if (!rename($f, $nf))
                            $_SESSION['easy2err'][] = __LINE__ . ': ' . "Could not rename path " . $f;
                        else {
                            $_restore['d']++;
                            @chmod($nf, 0755);
                            $_SESSION['easy2suc'][] = __LINE__ . ': ' . 'Successful on renaming path " ' . $name . ' " to be " ' . $oldBasename . ' "';
                        }

                        $subFolder = MODX_BASE_PATH . $this->e2g['dir'] . $oldDirs[$name]['name'];
                        $this->restoreAll($subFolder . '/', $oldDirs[$name]['id']);

                        unset($oldDirs[$name]);
                    }
                    if (isset($newDirs[$name])) {
                        unset($newDirs[$name]);
                    }
                }
                if ($this->validFile($f)) {
                    $fbasename = basename($f);

                    $s = filesize($f);
                    // goldsky -- $ext returns '.jpg', including the dot
                    $ext = substr($fbasename, strrpos((string) $fbasename, '.'));
                    $trimmedName = rtrim($fbasename, $ext); // goldsky -- split the extension, to gain the file's ID
                    $newFilename = MODX_BASE_PATH . $this->e2g['dir'] . $oldFiles[$trimmedName]['name'];

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
                            $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['rename_file_err'];
                        else {
                            $_restore['f']++;
                            @chmod($newFilename, 0644);
                            $_SESSION['easy2suc'][] = __LINE__ . ': '
                                    . 'Successful on renaming file " '
                                    . $fbasename . ' " to be " ' . $oldFiles[$trimmedName]['name'] . ' "';
                        }
                    }
                } // if ($this->validFile($f))

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

    /**
     * Delete all contents of the given path
     * @param   string  $path   path
     * @return  array   report
     */
    public function deleteAll($path) {
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
                    $sres = $this->deleteAll($f . '/');

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
     * To check the specified resource is a valid file.<br />
     * It will be checked against the folder validation first.
     * @param string $filename the filename
     */
    public function validFile($filename, $e2g_debug=0) {
        $f = basename($filename);
        if ($this->validFolder($filename)) {
            if ($this->e2g['e2g_debug'] == 1) {
                return '<b style="color:red;">' . $filename . '</b> is not a file, it\'s a valid folder.';
            }
            else
                return FALSE;
        }
        elseif ($f != '' && !$this->validFolder($filename)) {
            if (file_exists(realpath($filename))) {
                $size = getimagesize($filename);
                $fp = fopen($filename, "rb");
                $allowedExt = array(
                    'image/jpeg' => TRUE,
                    'image/gif' => TRUE,
                    'image/png' => TRUE
                );
                if ($allowedExt[$size["mime"]] && $fp) {
                    if ($this->e2g['e2g_debug'] == 1) {
                        $fileinfo = 'Filename <b style="color:red;">' . $f . '</b> is a valid image file: ' . $size["mime"] . ' - ' . $size[3];
                    }
                    else
                        return TRUE;
                } else {
                    if ($this->e2g['e2g_debug'] == 1)
                        $fileinfo = 'Filename <b style="color:red;">' . $f . '</b> is an invalid image file: ' . $size[2] . ' - ' . $size[3];
                    else
                        return FALSE;
                }
            }
            else {
                if ($this->e2g['e2g_debug'] == 1)
                    $fileinfo .= 'Filename <b style="color:red;">' . $f . '</b> is NOT exists.<br />';
                else
                    return FALSE;
            }
            if ($this->e2g['e2g_debug'] == 1)
                return $fileinfo;
            else
                return TRUE;
        }
        else
            return FALSE;
    }

    /**
     * To check the specified resource is a valid folder, although it has a DOT in it.
     * @param string $foldername the folder's name
     */
    public function validFolder($folderName, $e2g_debug=0) {
        $openFolder = @opendir($folderName);
        if (!$openFolder) {
            if ($this->e2g['e2g_debug'] == 1)
                return '<b style="color:red;">' . $folderName . '</b> is NOT a valid folder.';
            else
                return FALSE;
        } else {
            if ($this->e2g['e2g_debug'] == 1) {
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
        if ($this->e2g['e2g_debug'] == 1)
            return '<br /><b style="color:red;">' . $folderName . '</b> is a valid folder.';
        else
            return TRUE;
    }

    /**
     * To LOOP restore file's and folder's name of previous version's installation
     * @param   string  $path path to file or folder
     * @param   int     $pid current parent ID
     */
    public function restoreAll($path, $pid) {
        /**
         * STORE variable arrays for synchronizing comparison
         */
        // MySQL Dir list
        $res = mysql_query('SELECT cat_id,cat_name FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs WHERE parent_id=' . $pid);

        $oldDirs = array();
        $newDirs = array();
        if ($res) {
            while ($l = mysql_fetch_assoc($res)) {
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
        $res = mysql_query('SELECT id,filename,size FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files WHERE dir_id=' . $pid);

        $oldFiles = array();
        $newFiles = array();
        if ($res) {
            while ($l = mysql_fetch_assoc($res)) {
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

        if (!$this->validFolder($path)) {
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

            if ($this->validFolder($f)) {
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
                    if (!$this->restoreAll($nf . '/', $oldDirs[$name]['id']))
                        return FALSE;

                    unset($oldDirs[$name]);
                }
                elseif (isset($newDirs[$name])) {
                    unset($newDirs[$name]);
                }
                else
                    continue;
            }
            elseif ($this->validFile($f)) {
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
                    $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['rename_file_err'];
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

    /**
     * Checking table's field existence
     * @param string $table the table name
     * @param string $field the field name
     * @param string $data  the field's data
     * @return bool|string If this only check the field, it uses bool type.<br />
     * If this check the datatype, it will return the datatype information.
     * @author goldsky
     */
    public function checkField($table, $field, $data=null) {
        $metadata = $this->modx->db->getTableMetaData($table);
        if ($metadata[$field]) {
            return TRUE;
        } elseif ($data) {
            return $metadata[$field][$data];
        }
        else
            return FALSE;
    }

    /**
     * Adding a field to the SQL table
     * @param   string  $table      table name
     * @param   string  $fieldName  field name
     * @param   string  $fieldInfo  additional field infor
     * @param   string  $position   field's position among the other existing fields
     */
    public function addField($table, $fieldName, $fieldInfo, $position=null) {
        if ($this->checkField($this->modx->db->config['table_prefix'] . $table, $fieldName) === FALSE) {
            if (!mysql_query('ALTER TABLE ' . $this->modx->db->config['table_prefix'] . $table . ' ADD `' . $fieldName . '` ' . $fieldInfo . ' ' . $position))
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['field'] . ' ' . $this->modx->db->config['table_prefix'] . $table . '.' . $fieldName . ' ' . $this->lngi['created_err'];
            else
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['field'] . ' ' . $this->modx->db->config['table_prefix'] . $table . '.' . $fieldName . ' ' . $this->lngi['created'];
        }
    }

    /**
     * Get the database character set and collation
     * @param   string  $databaseCollation  collation from the field selection
     * @param   string  $variable           charset
     * @return  string  database collation
     */
    public function databaseCharSet($databaseCollation, $variable) {
        // get collation
        $getCol = mysql_query("SHOW COLLATION");
        $showVars = $this->modx->db->makeArray($this->modx->db->query("SHOW VARIABLES"));
        foreach ($showVars as $v) {
            $mysqlVars[$v['Variable_name']] = $v['Value'];
        }
        $databaseCollation = $mysqlVars['collation_database'];

        $cola = array();
        if (@mysql_num_rows($getCol) > 0) {
            while ($row = mysql_fetch_assoc($getCol)) {
                $cola[$row['Collation']] = $row;
            }
        }
        return $cola[$databaseCollation][$variable];
    }

    public function initInstall($post, $index) {
        // DIRS
        $post['path'] = str_replace('../', '', $post['path']);
        if (empty($post['path'])) {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['empty_dir'];
            $this->chref($index);
        }

        $xPath = @explode('/', $post['path']);
        foreach ($xPath as $value) {
            if (empty($value))
                continue;
            $dirs[] = $value;
        }
        $post['path'] = @implode('/', $dirs) . '/';

        // CHECK/CREATE DIRS
        if (is_dir(MODX_BASE_PATH . $post['path'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['dir_exists'] . ': ' . $post['path'];
        } else {
            $npath = '..';
            foreach ($dirs as $dir) {
                $npath .= '/' . $dir;
                if (is_dir($npath) || empty($dir))
                    continue;

                if (!mkdir($npath, 0777)) {
                    $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['create_dir_err'] . ' "' . $npath . "'";
                    $this->chref($index);
                }
            }
            $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['dir_created'] . ': ' . $post['path'];
        }

        // CHECK/CREATE TABLES
        // mysql_list_fields()
        // GET All Tables
        $dbase = str_replace('`', '', $this->modx->db->config['dbase']);
        $res = mysql_query('SHOW TABLES FROM `' . $dbase . '`');
        if (!$res) {
            echo __LINE__ . ' : $dbase = ' . $dbase . '<br />';
            echo __LINE__ . ' : $dbase = ' . 'SHOW TABLES FROM ' . $dbase . '<br />';
            echo __LINE__ . ' : $params = ' . mysql_error() . '<br />';
            die();
        }
        $tab = array();
        while ($row = mysql_fetch_row($res)) {
            $tab[$row[0]] = $row[0];
        }

        // easy2_dirs CHECK
        if (isset($tab['easy2_dirs'])) {
            if (!mysql_query('RENAME TABLE easy2_dirs TO ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs')) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' easy2_dirs ' . $this->lngi['rename_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        // easy2_dirs CREATE
        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_dirs'])) {
            $createDirTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs (
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
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');

            $queryCreateDirTable = mysql_query($createDirTable);
            if (!$queryCreateDirTable) {
                $_SESSION['easy2err'][] = __LINE__ . ': '
                        . $this->lngi['table'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                        . $this->lngi['create_err']
                        . '<br />' . mysql_error()
                        . '<br />' . $createTable;
                $this->chref($index);
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ': '
                        . $this->lngi['table'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                        . $this->lngi['created'];
            }
        }

        // easy2_dirs fields UPGRADE for additional fields from previous version
        // additional field for 1.3.6 Beta4
        // cat_description
        $this->addField('easy2_dirs', 'cat_description', 'TEXT NULL');

        // additional field for 1.3.6 Beta4
        // last_modified
        $this->addField('easy2_dirs', 'last_modified', 'DATETIME NULL DEFAULT NULL');

        // additional field for 1.4.0 Beta4
        // cat_alias
        $this->addField('easy2_dirs', 'cat_alias', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER cat_name');

        // change field for 1.4.0 RC1
        // cat_left
        if ($this->checkField($this->modx->db->config['table_prefix'] . 'easy2_dirs', 'cat_left') !== FALSE
                && $this->checkField($this->modx->db->config['table_prefix'] . 'easy2_dirs', 'cat_left', 'Type') === 'int(10) unsigned'
        ) {
            if (!mysql_query('ALTER TABLE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs CHANGE cat_left cat_left INT(10) default \'0\' NOT NULL')) {
                $_SESSION['easy2err'][] = __LINE__ . ': '
                        . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_dirs.cat_left '
                        . $this->lngi['upgrade_err'];
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_dirs.cat_left '
                        . $this->lngi['upgraded'];
            }
        }

        // rename field for 1.4.0 RC1
        // cat_tag
        if ($this->checkField($this->modx->db->config['table_prefix'] . 'easy2_dirs', 'cat_tags') !== FALSE) {
            if (!mysql_query('ALTER TABLE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs CHANGE `cat_tags` `cat_tag` VARCHAR(255) DEFAULT NULL NULL')) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_dirs.cat_tag '
                        . $this->lngi['upgrade_err'];
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_dirs.cat_tag '
                        . $this->lngi['upgraded'];
            }
        }

        // additional field for 1.4.0 RC1
        // cat_tag
        $this->addField('easy2_dirs', 'cat_tag', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER cat_summary');

        // additional field for 1.4.0 RC1
        // cat_summary
        $this->addField('easy2_dirs', 'cat_summary', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER cat_alias');

        if ($this->checkField($this->modx->db->config['table_prefix'] . 'easy2_dirs', 'cat_summary') === FALSE) {
            if (!mysql_query('ALTER TABLE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs ADD cat_summary varchar(255) default NULL AFTER cat_alias')) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_dirs.cat_summary '
                        . $this->lngi['created_err'];
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_dirs.cat_summary '
                        . $this->lngi['created'];
            }
        }

        // rearrange field for 1.4.0 RC1
        // cat_visible
        if ($this->checkField($this->modx->db->config['table_prefix'] . 'easy2_dirs', 'cat_visible') !== FALSE) {
            @mysql_query('ALTER TABLE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs CHANGE `cat_visible` `cat_visible` TINYINT(4) DEFAULT \'1\' NOT NULL AFTER `last_modified`');
        }

        #*******************************************
        # START UPDATING DIR TABLE FOR 1.4.0 RC-2 **
        #*******************************************
        // date_added
        $this->addField('easy2_dirs', 'date_added', 'DATETIME NULL DEFAULT NULL', 'AFTER cat_description');
        // added_by
        $this->addField('easy2_dirs', 'added_by', 'TINYINT(4) UNSIGNED NULL DEFAULT NULL', 'AFTER date_added');
        // modified_by
        $this->addField('easy2_dirs', 'modified_by', 'TINYINT(4) UNSIGNED NULL DEFAULT NULL', 'AFTER last_modified');
        // cat_redirect_link
        $this->addField('easy2_dirs', 'cat_redirect_link', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER modified_by');
        #******************************************
        # ENDS UPDATING DIR TABLE FOR 1.4.0 RC-2 **
        #******************************************
        #*******************************************
        # START UPDATING DIR TABLE FOR 1.4.0 PL   **
        #*******************************************
        // cat_redirect_link
        $this->addField('easy2_dirs', 'cat_thumb_id', 'INT(50) UNSIGNED NULL DEFAULT NULL', 'AFTER cat_redirect_link');
        #******************************************
        # ENDS UPDATING DIR TABLE FOR 1.4.0 PL   **
        #******************************************

        $res = mysql_query('SELECT cat_right FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs WHERE cat_id=1');
        if (mysql_num_rows($res) == 0) {
            $insertData = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                    . '(parent_id, cat_id, cat_left, cat_right, cat_level, cat_name, cat_visible) '
                    . 'VALUES (0,1,1,2,0,\'Easy 2\',1)';
            if (!mysql_query($insertData)) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['data'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs ' . $this->lngi['add_err']
                        . '<br />' . mysql_error()
                        . '<br />' . $insertData;
                $this->chref($index);
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['data'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs ' . $this->lngi['added'];
            }
        }

        // easy2_comments renaming
        if (isset($tab['easy2_comments'])) {
            if (!mysql_query('RENAME TABLE easy2_comments TO ' . $this->modx->db->config['table_prefix'] . 'easy2_comments')) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_comments ' . $this->lngi['rename_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        // easy2_comments
        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_comments'])) {
            $createCommentTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_comments (
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
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');
            if (!mysql_query($createCommentTable)) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                        . $this->lngi['create_err']
                        . '<br />' . mysql_error()
                        . '<br />' . $createCommentTable;
                $this->chref($index);
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['table'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                        . $this->lngi['created'];
            }
        }

        // easy2_comments fields UPGRADE for additional fields from previous version
        // additional field for 1.4.0 Beta1
        // ip_address
        if ($this->checkField($this->modx->db->config['table_prefix'] . 'easy2_comments', 'ip_address') === FALSE) {
            if (!mysql_query('ALTER TABLE ' . $this->modx->db->config['table_prefix'] . 'easy2_comments ADD ip_address char(16) NOT NULL AFTER email')) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_comments.ip_address '
                        . $this->lngi['created_err'];
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_comments.ip_address '
                        . $this->lngi['created'];
            }
        }

        #************************************************
        # START UPDATING COMMENTS TABLE FOR 1.4.0 RC-2 **
        #************************************************
        // read
        $this->addField('easy2_comments', 'read', 'TINYINT(3) UNSIGNED NULL DEFAULT \'0\' COMMENT \'has been read or not\'');
        // approved
        $this->addField('easy2_comments', 'approved', 'TINYINT(3) UNSIGNED NULL DEFAULT \'0\' COMMENT \'approval sign, 0 | 1\'');
        // date_edited
        $this->addField('easy2_comments', 'date_edited', 'DATETIME NULL DEFAULT NULL');
        // edited_by
        $this->addField('easy2_comments', 'edited_by', 'TINYINT(10) UNSIGNED NULL DEFAULT NULL');
        #***********************************************
        # ENDS UPDATING COMMENTS TABLE FOR 1.4.0 RC-2 **
        #***********************************************
        // easy2_files CHECK
        if (isset($tab['easy2_files'])) {
            if (!mysql_query('RENAME TABLE easy2_files TO ' . $this->modx->db->config['table_prefix'] . 'easy2_files')) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_files ' . $this->lngi['rename_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_files'])) {
            // easy2_files CREATE
            $createFileTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_files (
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
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');
            if (!mysql_query($createFileTable)) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_files ' . $this->lngi['create_err']
                        . '<br />' . mysql_error()
                        . '<br />' . $createFileTable;
                $this->chref($index);
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_files ' . $this->lngi['created'];
            }
        }

        // rename field for 1.4.0 RC1
        // tag
        if ($this->checkField($this->modx->db->config['table_prefix'] . 'easy2_files', 'tags') !== FALSE) {
            if (!mysql_query('ALTER TABLE ' . $this->modx->db->config['table_prefix'] . 'easy2_files CHANGE `tags` `tag` VARCHAR(255) DEFAULT NULL NULL')) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_files.tags '
                        . $this->lngi['upgrade_err'];
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_files.tag '
                        . $this->lngi['upgraded'];
            }
        }

        // additional field for 1.4.0 RC1
        // summary
        $this->addField('easy2_files', 'summary', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER name');

        // additional field for 1.4.0 RC1
        // tag
        $this->addField('easy2_files', 'tag', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER summary');

        #*********************************************
        # START UPDATING FILES TABLE FOR 1.4.0 RC-2 **
        #*********************************************
        // additional field added_by
        $this->addField('easy2_files', 'added_by', 'TINYINT(4) UNSIGNED NULL DEFAULT NULL', 'AFTER date_added');
        // additional field modified_by
        $this->addField('easy2_files', 'modified_by', 'TINYINT(4) NULL DEFAULT NULL', 'AFTER last_modified');
        // additional field width
        $this->addField('easy2_files', 'width', 'INT(10) UNSIGNED NULL DEFAULT NULL', 'AFTER size');
        // additional field height
        $this->addField('easy2_files', 'height', 'INT(10) UNSIGNED NULL DEFAULT NULL', 'AFTER width');
        // additional redirect_link
        $this->addField('easy2_files', 'redirect_link', 'VARCHAR(255) NULL DEFAULT NULL', 'AFTER status');
        #********************************************
        # ENDS UPDATING FILES TABLE FOR 1.4.0 RC-2 **
        #********************************************
        // rename field for 1.4.0 RC4
        // name => alias
        if ($this->checkField($this->modx->db->config['table_prefix'] . 'easy2_files', 'name') !== FALSE) {
            if (!mysql_query('ALTER TABLE ' . $this->modx->db->config['table_prefix'] . 'easy2_files CHANGE `name` `alias` VARCHAR(255) DEFAULT NULL NULL')) {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_files.name '
                        . $this->lngi['upgrade_err'];
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['field'] . ' '
                        . $this->modx->db->config['table_prefix'] . 'easy2_files.alias '
                        . $this->lngi['upgraded'];
            }
        }

        // adding ignore IP table for 1.4.0 Beta4
        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_ignoredip'])) {
            $createIgnoreIpTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_ignoredip (
                        id int(10) unsigned NOT NULL auto_increment,
                        ign_date datetime NOT NULL,
                        ign_ip_address char(16) NOT NULL,
                        ign_username varchar(64) NOT NULL,
                        ign_email varchar(64) default NULL,
                        PRIMARY KEY (id)
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');
            if (mysql_query($createIgnoreIpTable)) {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_ignoredip ' . $this->lngi['created'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_ignoredip ' . $this->lngi['create_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        #**************************************
        # START ADDING TABLES FOR 1.4.0 RC-2 **
        #**************************************
        // adding easy2_configs table for 1.4.0 RC2
        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_configs'])) {
            $createConfigTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_configs (
                        `cfg_key` VARCHAR(50) NOT NULL DEFAULT \'\',
                        `cfg_val` VARCHAR(255) NULL DEFAULT NULL,
                        UNIQUE INDEX `cfg_key` (`cfg_key`)
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');
            if (mysql_query($createConfigTable)) {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_configs ' . $this->lngi['created'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_configs ' . $this->lngi['create_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        // adding easy2_plugins table for 1.4.0 RC2
        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_plugins'])) {
            $createPluginTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_plugins (
                        `id` INT(10) NOT NULL AUTO_INCREMENT,
                        `name` VARCHAR(255) NULL DEFAULT NULL,
                        `description` VARCHAR(255) NULL DEFAULT NULL,
                        `disabled` TINYINT(1) NULL DEFAULT NULL,
                        `indexfile` VARCHAR(255) NULL DEFAULT NULL,
                        `events` VARCHAR(255) NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');
            if (mysql_query($createPluginTable)) {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_plugins ' . $this->lngi['created'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_plugins ' . $this->lngi['create_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        // adding easy2_plugins table for 1.4.0 RC2
        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_plugin_events'])) {
            $createPluginEventTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_plugin_events (
                        `pluginid` INT(10) NOT NULL,
                        `evtid` INT(10) NOT NULL,
                        `priority` INT(10) NOT NULL
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');
            if (mysql_query($createPluginEventTable)) {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_plugin_events ' . $this->lngi['created'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_plugin_events ' . $this->lngi['create_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        // adding easy2_slideshows table for 1.4.0 RC2
        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_slideshows'])) {
            $createSlideshowTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows (
                        `id` INT(10) NOT NULL AUTO_INCREMENT,
                        `name` VARCHAR(255) NULL DEFAULT NULL,
                        `description` VARCHAR(255) NULL DEFAULT NULL,
                        `indexfile` VARCHAR(255) NULL DEFAULT NULL,
                        PRIMARY KEY (`id`)
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');
            if (mysql_query($createSlideshowTable)) {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows ' . $this->lngi['created'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows ' . $this->lngi['create_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        $this->_installSlideshows();

        // adding easy2_users_mgr table for 1.4.0 RC2
        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_users_mgr'])) {
            $createUserMgrTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_users_mgr (
                        `id` INT(10) NOT NULL AUTO_INCREMENT,
                        `membergroup_id` INT(10) NULL DEFAULT NULL COMMENT \'modx groups id\',
                        `permissions` VARCHAR(255) NULL DEFAULT NULL COMMENT \'e2g_access\',
                        PRIMARY KEY (`id`)
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');
            if (mysql_query($createUserMgrTable)) {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_users_mgr ' . $this->lngi['created'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_users_mgr ' . $this->lngi['create_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        // adding easy2_viewers table for 1.4.0 RC2
        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_viewers'])) {
            $createViewerTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_viewers (
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
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');
            if (mysql_query($createViewerTable)) {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_viewers ' . $this->lngi['created'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_viewers ' . $this->lngi['create_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        $this->_installViewers();

        // adding easy2_webgroup_access table for 1.4.0 RC2
        if (!isset($tab[$this->modx->db->config['table_prefix'] . 'easy2_webgroup_access'])) {
            $createWebAccessTable = 'CREATE TABLE IF NOT EXISTS ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access (
                        `webgroup_id` INT(10) NOT NULL,
                        `type` VARCHAR(10) NULL DEFAULT NULL COMMENT \'dir/file type\',
                        `id` INT(10) NULL DEFAULT NULL COMMENT \'dir/file id\'
                        )
                        ENGINE=MyISAM
                        CHARACTER SET ' . $this->databaseCharSet($post['database_collation'], 'Charset') . '
                        COLLATE ' . $this->databaseCharSet($post['database_collation'], 'Collation');
            if (mysql_query($createWebAccessTable)) {
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access ' . $this->lngi['created'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['table'] . ' ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access ' . $this->lngi['create_err']
                        . '<br />' . mysql_error();
                $this->chref($index);
            }
        }

        #*************************************
        # ENDS ADDING TABLES FOR 1.4.0 RC-2 **
        #*************************************
        // MODULE

        if (empty($this->e2g['mod_id'])) {

            $moduleFile = realpath(MODX_BASE_PATH . 'assets/modules/easy2/index.php');
            if (!empty($moduleFile) && file_exists($moduleFile)) {
                $moduleCode = '
$moduleFile = realpath(MODX_BASE_PATH . \'assets/modules/easy2/index.php\');
if (!empty($moduleFile) && file_exists($moduleFile)) {
    return include $moduleFile;
} else {
    return \'\';
}
';
            } else {
                return FALSE;
            }

            $select = mysql_query('SELECT modulecode FROM ' . $this->modx->db->config['table_prefix'] . 'site_modules WHERE id =\'' . $_GET['id'] . '\'');
            $result = mysql_result($select, 0, 0);
            if ($result != $moduleCode) {
                $res = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'site_modules '
                        . 'SET modulecode = \'' . mysql_escape_string($moduleCode)
                        . '\' WHERE id =\'' . $_GET['id'] . '\'';
                if (mysql_query($res)) {
                    $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['mod_updated'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['mod_update_err'] . '<br />' . mysql_error();
                    $this->chref($index);
                }
            }
        }
        else
            $post['mod_id'] = !empty($post['mod_id']) ? $post['mod_id'] : $this->e2g['mod_id'];

        // SNIPPET

        if (empty($post['snippet_id'])) {

            $snippetFile = realpath(MODX_BASE_PATH . 'assets/modules/easy2/snippet.easy2gallery.php');
            if (!empty($snippetFile) && file_exists($snippetFile)) {
                $snippetCode = '
$snippetFile = realpath(MODX_BASE_PATH . \'assets/modules/easy2/snippet.easy2gallery.php\');
if (!empty($snippetFile) && file_exists($snippetFile)) {
    return include $snippetFile;
} else {
    return \'\';
}
';
            } else {
                return FALSE;
            }

            $res = mysql_query('SELECT id FROM ' . $this->modx->db->config['table_prefix'] . 'site_snippets WHERE name =\'easy2\'');
            if (mysql_num_rows($res) == 0) {
                $sql = "INSERT INTO " . $this->modx->db->config['table_prefix'] . "site_snippets "
                        . "(name, description, snippet, moduleguid, locked, properties, category) "
                        . "VALUES('easy2', 'Easy 2 Gallery', '" . mysql_escape_string($snippetCode) . "', '', '1','', '0')";

                if (mysql_query($sql)) {
                    $post['snippet_id'] = mysql_insert_id();
                    $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['snippet_added'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['snippet_add_err'];
                    $this->chref($index);
                }
            } else {
                $select = mysql_query('SELECT snippet FROM ' . $this->modx->db->config['table_prefix'] . 'site_snippets WHERE name =\'easy2\'');
                $result = mysql_result($select, 0, 0);
                if ($result != $snippetCode) {
                    $sql = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'site_snippets '
                            . 'SET snippet=\'' . mysql_escape_string($snippetCode)
                            . '\' WHERE name =\'easy2\'';
                    if (mysql_query($sql)) {
                        $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['snippet_updated'];
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['snippet_update_err'];
                        $this->chref($index);
                    }
                }
            }
        }
        else
            $post['snippet_id'] = !empty($post['snippet_id']) ? $post['snippet_id'] : $this->e2g['snippet_id'];

        // PLUGIN
        $pluginFile = realpath(MODX_BASE_PATH . 'assets/modules/easy2/plugin.easy2gallery.php');
        if (!empty($pluginFile) && file_exists($pluginFile)) {
            $pluginCode = '
$pluginFile = realpath(MODX_BASE_PATH . \'assets/modules/easy2/plugin.easy2gallery.php\');
if (!empty($pluginFile) && file_exists($pluginFile)) {
    return include $pluginFile;
} else {
    return \'\';
}
';
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': missing plugin file.';
            return FALSE;
        }

        if (empty($post['plugin_id'])) {
            $select = 'SELECT id FROM ' . $this->modx->db->config['table_prefix'] . 'site_plugins WHERE name=\'easy2\'';
            $query = mysql_query($select);
            if (mysql_num_rows($query) === 0) {
                $insert = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'site_plugins '
                        . '(name,description,plugincode) '
                        . "VALUES ('easy2', 'Easy 2 Gallery plugin','" . mysql_escape_string($pluginCode) . "')"
                ;
                if (mysql_query($insert)) {
                    $post['plugin_id'] = mysql_insert_id();
                    $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['plugin_added'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['plugin_add_err'];
                    $this->chref($index);
                }
            } else {
                $post['plugin_id'] = mysql_result($query, 0, 0);
            }
        } else {
            $post['plugin_id'] = !empty($post['plugin_id']) ? $post['plugin_id'] : $this->e2g['plugin_id'];
        }

        // PLUGIN EVENTS
        if (!empty($post['plugin_id'])) {
            $nEvtIds = array('90', '94'); // Plugin event's IDs. Will be added more later.
            $delete = mysql_query('DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'site_plugin_events WHERE pluginid=\'' . $post['plugin_id'] . '\'');
            if ($delete) {
                foreach ($nEvtIds as $nEvtId) {
                    mysql_query('INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'site_plugin_events '
                            . '(pluginid, evtid, priority) '
                            . "VALUES ('" . $post['plugin_id'] . "','$nEvtId','0')"
                    );
                }
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ': ' . __LINE__ . ' Error: ' . mysql_error();
            }

            $select = mysql_query('SELECT plugincode FROM ' . $this->modx->db->config['table_prefix'] . 'site_plugins WHERE id =\'' . $post['plugin_id'] . '\'');
            $result = mysql_result($select, 0, 0);
            if ($result != $pluginCode) {
                $res = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'site_plugins '
                        . 'SET plugincode = \'' . mysql_escape_string($pluginCode)
                        . '\' WHERE id =\'' . $post['plugin_id'] . '\'';
                if (mysql_query($res)) {
                    $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['plugin_updated'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['plugin_update_err'] . '<br />' . mysql_error();
                    $this->chref($index);
                }
            }
        }

        /**
         * goldsky -- add the file's/folder's names restoration from the previous installation version.
         */
        if ($this->restore(MODX_BASE_PATH . $this->e2g['dir'], 1)) {
            if ($_restore['d'] != 0 || $_restore['f'] != 0)
                $_SESSION['easy2suc'][] = __LINE__ . ': ' . $this->lngi['restore_suc'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ': ' . $this->lngi['restore_err'];
            $this->chref($index);
        }

        $_SESSION['easy2suc']['success'] = '<br /><br /><br />' . $this->lngi['success']
                . '<br /><br /><input type="button" value="' . $this->lngi['del_inst_dir'] . '" onclick="document.location.href=\'' . $index . '&p=del_inst_dir\'">';

        $this->_saveInstallConfig('dir', $post['path']);
        $this->_saveInstallConfig('plugin_id', $post['plugin_id']);
        $this->_saveInstallConfig('snippet_id', $post['snippet_id']);
        $this->_saveInstallConfig('mod_id', $post['mod_id']);

        $_SESSION['installE2g'] = FALSE;
        unset($_SESSION['installE2g']);

        $this->chref($index);
    }

    private function _saveInstallConfig($cfgKey, $cfgVal) {
        // check and update the parent directory
        $sqlConfigDir = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_configs '
                . 'WHERE cfg_key=\'' . $cfgKey . '\'';
        $query = mysql_query($sqlConfigDir);
        while ($row = mysql_fetch_assoc($query)) {
            $resultConfigDir[$row['cfg_key']] = $row['cfg_val'];
        }

        if (!$resultConfigDir) {
            $this->modx->db->query(
                    'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_configs '
                    . 'SET cfg_key=\'' . $cfgKey . '\', cfg_val=\'' . $cfgVal . '\''
            );
        } elseif ($resultConfigDir[$cfgKey] !== $cfgVal) {
            $this->modx->db->query(
                    'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_configs '
                    . 'SET cfg_val=\'' . $cfgVal . '\' WHERE cfg_key=\'' . $cfgKey . '\''
            );
        }
    }

    /**
     * Install distributed viewers
     * @return void
     */
    private function _installViewers() {
        $viewers = include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'viewers.php';
        $select = 'SELECT * FROM `' . $this->modx->db->config['table_prefix'] . "easy2_viewers` ";
        $result = mysql_query($select);

        while ($l = mysql_fetch_assoc($result)) {
            $viewerRow[$l['name']] = $l;
        }

        // for development and easy package updates
        $buildDefault = FALSE;
        if ($buildDefault) {
            if (empty($viewerRow))
                return;

            if (!function_exists('fopen')
                    || !function_exists('fwrite')
                    || !function_exists('fclose')
            ) {
                return FALSE;
            }

            $c = "<?php\r\n
/**
 * Distributed viewers for Easy 2 Gallery
 * @package Easy 2 Gallery
 * @subpackage install
 */
if (IN_MANAGER_MODE != 'true')
    die('<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.');

\$updateViewers = array(
";
            foreach ($viewerRow as $k => $v) {
                $c .= "\t\"$k\" => array(\r\n";
                foreach ($v as $x => $y) {
                    if ($x === 'id' || $x === 'name') {
                        continue;
                    }
                    $c .= "\t\t\"$x\" => \"" . mysql_real_escape_string($y) . "\",\r\n";
                }
                $c .= "\t),\r\n";
            }
            $c .= ");\r\n\r\nreturn \$updateViewers;";

            $f = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'viewers.php', 'w+');
            fwrite($f, $c);
            fclose($f);
        } else {
            if (empty($viewers))
                return;

            foreach ($viewers as $name => $v) {
                if (!isset($viewerRow[$name])) {
                    $countFields = count($v);
                    $i = 0;
                    $query = 'INSERT INTO `' . $this->modx->db->config['table_prefix'] . 'easy2_viewers` SET ';
                    $query .= '`name` = \'' . mysql_real_escape_string($name) . '\',';
                    foreach ($v as $field => $value) {
                        $i++;
                        $query .= '`' . $field . '` = \'' . mysql_real_escape_string($value) . '\'';
                        if ($i < $countFields) {
                            $query .= ',';
                        }
                    }
                    if (!mysql_query($query)) {
                        $_SESSION['easy2err'][] = __LINE__ . ': ' . 'MySQL ERROR: ' . mysql_error();
                        $_SESSION['easy2err'][] = $query;
                    }
                }
            }
        }

        return TRUE;
    }

    /**
     * Install distributed slideshows
     * @return void
     */
    private function _installSlideshows() {
        $slideshows = include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'slideshows.php';
        $select = 'SELECT * FROM `' . $this->modx->db->config['table_prefix'] . "easy2_slideshows` ";
        $result = mysql_query($select);

        while ($l = mysql_fetch_assoc($result)) {
            $slideshowRow[$l['name']] = $l;
        }

        // for development and easy package updates
        $buildDefault = FALSE;
        if ($buildDefault) {
            if (empty($slideshowRow))
                return;

            if (!function_exists('fopen')
                    || !function_exists('fwrite')
                    || !function_exists('fclose')
            ) {
                return FALSE;
            }

            $c = "<?php\r\n
/**
 * Distributed slideshows for Easy 2 Gallery
 * @package Easy 2 Gallery
 * @subpackage install
 */
if (IN_MANAGER_MODE != 'true')
    die('<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.');

\$updateSlideShows = array(
";
            foreach ($slideshowRow as $k => $v) {
                $c .= "\t\"$k\" => array(\r\n";
                foreach ($v as $x => $y) {
                    if ($x === 'id' || $x === 'name') {
                        continue;
                    }
                    $c .= "\t\t\"$x\" => \"" . mysql_real_escape_string($y) . "\",\r\n";
                }
                $c .= "\t),\r\n";
            }
            $c .= ");\r\n\r\nreturn \$updateSlideShows;";

            $f = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'slideshows.php', 'w+');
            fwrite($f, $c);
            fclose($f);
        } else {
            if (empty($slideshows))
                return;

            foreach ($slideshows as $name => $v) {
                if (!isset($slideshowRow[$name])) {
                    $countFields = count($v);
                    $i = 0;
                    $query = 'INSERT INTO `' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows` SET ';
                    $query .= '`name` = \'' . mysql_real_escape_string($name) . '\',';
                    foreach ($v as $field => $value) {
                        $i++;
                        $query .= '`' . $field . '` = \'' . mysql_real_escape_string($value) . '\'';
                        if ($i < $countFields) {
                            $query .= ',';
                        }
                    }
                    if (!mysql_query($query)) {
                        $_SESSION['easy2err'][] = __LINE__ . ': ' . 'MySQL ERROR: ' . mysql_error();
                        $_SESSION['easy2err'][] = $query;
                    }
                }
            }
        }

        return TRUE;
    }

}