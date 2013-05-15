<?php

/**
 * EASY 2 GALLERY
 * Gallery Module Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@fastmail.fm>
 */
$e2gPubClassFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'e2g.public.class.php';
if (!class_exists('E2gPub') && file_exists(realpath($e2gPubClassFile))) {
    include $e2gPubClassFile;
} else {
    return 'Missing $e2gPubClassFile';
}

class E2gMod extends E2gPub {

    /**
     * Inherit MODx functions
     * @var mixed modx's API
     */
    public $modx;

    /**
     * The module's configurations in an array
     * @var mixed all the module's settings
     */
    public $e2gModCfg;

    /**
     * The default configuration from the config fils
     * @var mixed default configuration
     */
    public $e2g;

    /**
     * The translation variables based on the manager's language setting
     * @var string language translation
     */
    public $lng;

    /**
     * Folders as the Drop down option
     * @var string drop down option
     */
    private $_dirDropDownOptions = array();

    /**
     * Sanitized $_GET array
     * @var array   sanitized $_GET values
     */
    public $sanitizedGets = array();

    /**
     * Gallery's path
     * @var array   string / link;
     */
    public $galleryPath = array();

    public function __construct($modx) {
        $this->modx = & $modx;
        $this->lng = parent::languageSwitch($modx->config['manager_language'], E2G_MODULE_PATH);
        if (!is_array($this->lng)) {
            die($this->lng); // FALSE returned.
        }

        $this->sanitizedGets = $this->sanitizedGets($_GET);
        $this->e2g = $this->loadSettings();
        $this->e2gModCfg = $this->_loadE2gModCfg();

        $this->galleryPath = $this->_galleryPath();

        $cfg = array_merge($this->e2g, $this->e2gModCfg);
        parent::__construct($modx, $cfg);
    }

    /**
     * Create the gallery path for address bar and the breadcrumbs
     * @return  array   array{'link'] or array['string']
     */
    private function _galleryPath() {
        $getPathArray = $this->getPath($this->e2gModCfg['parent_id'], NULL, 'array');
        // Create the ROOT gallery's link
        $path['link'] = '';
        $path['string'] = '';
        if (empty($getPathArray)) {
            return $path;
        }
        foreach ($getPathArray as $k => $v) {
            $path['link'] .= '<a href="' . $this->e2gModCfg['index'] . '&amp;pid=' . $k . '">' . $v . '</a>/';
        }
        unset($getPathArray[1]);

        // Create the afterwards gallery's path
        if (!empty($getPathArray)) {
            $path['string'] = implode('/', $getPathArray) . '/';
            $this->e2gModCfg['gdir'] .= $path['string'];
        }

        // 'path' request claims a new path
        if (!empty($this->sanitizedGets['path'])
                && $path['string'] !== $this->sanitizedGets['path']
        ) {
            $getPath = str_replace('../', '', $this->sanitizedGets['path']);
            $getPath = str_replace($this->e2gModCfg['gdir'], '', $this->e2g['dir'] . $this->sanitizedGets['path']);
            $pathArray = explode('/', $getPath);
            foreach ($pathArray as $v) {
                if (empty($v)) {
                    continue;
                }
                $path['string'] .= $v . '/';
                $path['link'] .= '<a href="' . $this->e2gModCfg['index']
                        . (!empty($_GET['pid']) ? '&amp;pid=' . $this->e2gModCfg['parent_id'] : '')
                        . '&amp;path=' . $path['string']
                        . '">' . $v . '</a>/';
            }
            $this->e2gModCfg['gdir'] .= $path['string'];
        }
        return $path;
    }

    /**
     * The main file explorer function
     * @return string The module's pages.
     */
    public function explore() {
        /**
         * GALLERY ACTIONS
         */
        $act = empty($this->sanitizedGets['act']) ? '' : $this->sanitizedGets['act'];
        switch ($act) {
            case 'synchro':
                // AJAX report
                $this->_cleanCache();
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'upload_all':
                if (!$this->_uploadAll($_POST, $_FILES)) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } else {
                    if ($_POST['gotofolder'] == 'gothere') {
                        header('Location: ' . html_entity_decode(
                                        MODX_MANAGER_URL
                                        . 'index.php?'
                                        . 'a=' . $this->e2gModCfg['_a']
                                        . '&amp;id=' . $this->e2gModCfg['_i']
                                        . '&amp;e2gpg=2'
                                        . '&amp;pid=' . $_POST['newparent']
                                ));
                    } else {
                        header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    }
                }
                exit();
                break;

            case 'show_checked' :
                $this->_showChecked($_POST);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'hide_checked' :
                $this->_hideChecked($_POST);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'unhide_dir' :
                $this->_unhide($this->sanitizedGets['dir_id']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'hide_dir' :
                $this->_hideDir($this->sanitizedGets['dir_id']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'unhide_file' :
                $this->_unhideFile($this->sanitizedGets['file_id']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'hide_file' :
                $this->_hideFile($this->sanitizedGets['file_id']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Multiple deletion
            case 'delete_checked':
                $this->_deleteChecked($_POST);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Download files/folders
            case 'download_checked':
                $this->_downloadChecked($this->e2gModCfg['gdir'], $this->sanitizedGets['pid'], $_POST);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Move files/folders to the new folder
            case 'move_checked':
                if ($this->_moveChecked($_POST) === FALSE) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }
                /**
                 * REDIRECT PAGE TO THE SELECTED OPTION
                 */
                $this->e2gModCfg['parent_id'] = $_POST['newparent'];
                if ($_POST['gotofolder'] == 'gothere') {
                    header('Location: ' . html_entity_decode(
                                    MODX_MANAGER_URL
                                    . 'index.php?'
                                    . 'a=' . $this->e2gModCfg['_a']
                                    . '&amp;id=' . $this->e2gModCfg['_i']
                                    . '&amp;e2gpg=2'
                                    . '&amp;pid=' . $this->e2gModCfg['parent_id']
                            ));
                } else {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                }
                $this->_cleanCache();

                exit();
                break;

            case 'delete_dir':
                $this->_deleteDir($this->sanitizedGets);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'delete_file':
                $this->_deleteFile($this->sanitizedGets);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'clean_cache':
                $this->_cleanCache();
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'save_config':
                if (!empty($_POST['clean_cache'])) {
                    unset($_POST['clean_cache']);
                    $url = $this->e2gModCfg['index'] . '&amp;act=clean_cache';
                } else {
                    $url = $_SERVER['HTTP_REFERER'];
                }

                $this->saveE2gSettings($_POST);

                header('Location: ' . html_entity_decode($url, ENT_NOQUOTES));
                exit();
                break;

            // Save translation
            case 'save_lang':
                $this->_saveLang($_POST);
                header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                exit();
                break;

            // Add directory into database
            case 'add_dir':
                $this->_addAll('../' . str_replace('../', '', $this->e2gDecode($this->sanitizedGets['dir_path']) . '/')
                        , $this->e2gModCfg['parent_id']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Add image into database
            case 'add_file':
                $this->_addFile(MODX_BASE_PATH . $this->sanitizedGets['file_path'], $this->sanitizedGets['pid']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();

            // Add slideshow
            case 'save_slideshow':
                if (!$this->_saveSlideshow($_POST)) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } else {
                    header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                }
                exit();
                break;

            // Delete slideshow
            case 'delete_slideshow':
                $this->_deleteSlideshow($this->sanitizedGets);
                header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                exit();
                break;

            case 'update_slideshow':
                $this->_updateSlideshow($_POST);
                header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                exit();
                break;

            // Add plugin
            case 'save_plugin':
                $this->_savePlugin($_POST);
                header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                exit();
                break;

            case 'update_plugin':
                $this->_updatePlugin($_POST);
                header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                exit();
                break;

            case 'delete_plugin':
                $this->_deletePlugin($this->sanitizedGets);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Add thumbnail viewer
            case 'save_viewer':
                if (!$this->_saveViewer($_POST)) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } else {
                    header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                }
                exit();
                break;

            // Update thumbnail viewer
            case 'update_viewer':
                if (!$this->_updateViewer($_POST)) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } else {
                    header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                }
                exit();
                break;

            case 'delete_viewer':
                $this->_deleteViewer($this->sanitizedGets);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Ignore ip address in image comments
            case 'ignore_ip':
                $this->_ignoreIp($this->sanitizedGets);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Unignore ip address in image comments
            case 'unignore_ip':
                $this->_unignoreIp($this->sanitizedGets);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Delete comments from comments manager
            case 'unignored_all_ips':
                $this->_unignoredAllIps($_POST);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Add tag to the selected objects
            case 'tag_add_checked':
                if ($this->_tagAddChecked($_POST))
                    $this->_cleanCache();
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Remove tag from the selected objects
            case 'tag_remove_checked':
                $this->_tagRemoveChecked($_POST);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'com_list_actions' :
                $this->_commentListActions($_POST);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'com_approve':
                $this->_commentApprove($this->sanitizedGets['comid']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'com_hide':
                $this->_commentHide($this->sanitizedGets['comid']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'com_unhide':
                $this->_commentUnhide($this->sanitizedGets['comid']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'com_save':
                $this->_commentSave($_POST);
                $url = $this->e2gModCfg['index']
                        . (!empty($this->sanitizedGets['page']) ? '&page=' . $this->sanitizedGets['page'] : NULL)
                        . (!empty($this->sanitizedGets['filter']) ? '&filter=' . $this->sanitizedGets['filter'] : NULL)
                        . (!empty($this->sanitizedGets['file_id']) ? '&file_id=' . $this->sanitizedGets['file_id'] : NULL)
                        . (!empty($this->sanitizedGets['pid']) ? '&pid=' . $this->sanitizedGets['pid'] : NULL)
                        . (!empty($this->sanitizedGets['tag']) ? '&tag=' . $this->sanitizedGets['tag'] : NULL);
                header('Location: ' . html_entity_decode($url, ENT_NOQUOTES));
                exit();
                break;

            case 'com_delete':
                $this->_commentDelete($this->sanitizedGets['comid']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            /**
             * @todo add the hit reset
             */
            case 'reset_all_hit':
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['reset_all_hit_suc'];
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'cancel':
                header('Location: ' . html_entity_decode($this->e2gModCfg['index'], ENT_NOQUOTES));
                exit();
                break;

            case 'create_dir':
                // check names against bad characters
                if ($this->_hasBadChar($_POST['name'], __LINE__)
                        || !$this->_createDir($_POST, $this->e2gModCfg['gdir'], $this->e2gModCfg['parent_id'])
                ) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } else {
                    header('Location: ' . html_entity_decode($this->e2gModCfg['index'] . '&amp;pid=' . $this->e2gModCfg['parent_id']));
                }
                exit();
                break;

            case 'save_dir':
                // check names against bad characters
                if ($this->_hasBadChar($_POST['newdirname'], __LINE__)
                        || !$this->_editDir($_POST)
                ) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } elseif (isset($this->sanitizedGets['tag'])) {
                    header('Location: ' . html_entity_decode($this->e2gModCfg['index'] . '&amp;tag=' . $this->sanitizedGets['tag']));
                } else {
                    header('Location: ' . html_entity_decode($this->e2gModCfg['index'] . '&amp;pid=' . $this->e2gModCfg['parent_id']));
                }
                exit();
                break;

            case 'save_file':
                // check names against bad characters
                if ($this->_hasBadChar($_POST['newfilename'], __LINE__)
                        || !$this->_editFile($this->e2gModCfg['gdir'], $_POST)
                ) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } elseif (isset($this->sanitizedGets['tag'])) {
                    header('Location: ' . html_entity_decode($this->e2gModCfg['index'] . '&amp;tag=' . $this->sanitizedGets['tag']));
                } else {
                    header('Location: ' . html_entity_decode($this->e2gModCfg['index'] . '&amp;pid=' . $this->e2gModCfg['parent_id']));
                }
                exit();
                break;

            case 'synchro_users' :
                $this->_synchroUserGroups();
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit;
                break;

            case 'save_mgr_permissions':
                $this->_saveMgrAccess($_POST);
                header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                exit;
                break;

            case 'save_web_dirs_perm':
                $this->_saveDirWebAccess($_POST);
                header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                exit;
                break;

            case 'save_web_files_perm':
                $this->_saveFileWebAccess($_POST);
                header('Location: ' . html_entity_decode($this->e2gModCfg['index']));
                exit;
                break;
        } // switch ($act)
        ob_start();
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/main.inc.php';
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * To delete all files/folders that have been selected with the checkbox
     * @param string $path file's/folder's path
     */
    private function _deleteAll($path) {
        $realPath = realpath($path);

        $res = array('d' => 0, 'f' => 0, 'e' => array());
        if (empty($realPath)) {
            return $res;
        }

        $fs = array();
        $fs = glob($realPath . DIRECTORY_SEPARATOR . '*');
        if ($fs != FALSE) {
            foreach ($fs as $f) {
                // using original file check, not _validFile($f), because it will delete not only images.
                if (is_file($f)) {
                    if (@unlink($f))
                        $res['f']++;
                    else
                        $res['e'][] = __LINE__ . ' : ' . $this->lng['file_delete_err'] . ' : ' . $f;
                } elseif (is_dir($f)) {
                    $sres = $this->_deleteAll($f);

                    $res['f'] += $sres['f'];
                    $res['d'] += $sres['d'];
                    $res['e'] = array_merge($res['e'], $sres['e']);
                }
            }
        }
        if (count($res['e']) == 0 && @rmdir($realPath))
            $res['d']++;
        else
            $res['e'][] = __LINE__ . ' : ' . $this->lng['dir_delete_err'] . ' : ' . $path;
        return $res;
    }

    /**
     * move all content to a new parent
     * @param string $oldPath Previous folder
     * @param string $newPath On target folder
     * @return array Only returns result reports, for confirmation display
     */
    private function _moveAll($oldPath, $newPath) {
        $oldRealPath = realpath($oldPath);
        $dirNewPath = realpath(dirname($newPath));
        $newRealPath = $dirNewPath . DIRECTORY_SEPARATOR . $this->basenameSafe($newPath);

        if (empty($oldRealPath) || !$this->validFolder($oldRealPath)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['invalid_folder'] . ' : ' . $oldPath;
            return $res;
        }
        if (empty($dirNewPath) || !$this->validFolder($dirNewPath)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['invalid_folder'] . ' : ' . $dirNewPath;
            return $res;
        }

        $res = array('d' => 0, 'f' => 0, 'e' => array());
        $fs = array();
        $fs = glob($oldRealPath . DIRECTORY_SEPARATOR . '*');
        if ($fs != FALSE) {
            foreach ($fs as $f) {
                if (is_file($f)) {
                    $res['file'][] = $f;
                    $res['f']++;
                } elseif ($this->validFolder($f)) {
                    $fBaseName = $this->basenameSafe($f);
                    $sres = array('d' => 0, 'f' => 0, 'e' => array());
                    $sres = $this->_moveAll($f, $newRealPath . DIRECTORY_SEPARATOR . $fBaseName);

                    $res['dir'][] = $f;
                    $res['d']++;
                    // $res = result (file/dir/error)
                    // $sres = result summary (file/dir/error)
                    $res['f'] += $sres['f'];
                    $res['d'] += $sres['d'];
                    $res['e'] = array_merge($res['e'], $sres['e']);
                }
            }
        }

        if (@rename($oldRealPath, $newRealPath))
            $res['d']++;
        else
            $res['e'][] = __LINE__ . ' : ' . $this->lng['dir_move_err'] . ' : ' . $oldPath;

        // only returns the result calculation array
        return $res;
    }

    /**
     * To add all folders from the upload form
     * @param string $path  file's/folder's path
     * @param int    $pid   current parent ID
     * @param string $cfg   module's configuration
     * @param string $lng   language translation
     */
    private function _addAll($path, $pid, $userId = null) {
        $realPath = realpath($path);
        $userId = !empty($userId) ? $userId : $this->modx->getLoginUserID();

        if (empty($realPath) || !$this->validFolder($realPath)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['invalid_folder'] . ' : ' . $path;
            return FALSE;
        }
        if (empty($userId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['invalid_uid'] . ' : ' . $userId;
            return FALSE;
        }

        require_once E2G_MODULE_PATH . 'includes/models/TTree.class.php';
        $tree = new TTree();
        $tree->table = $this->modx->db->config['table_prefix'] . 'easy2_dirs';
        $name = $this->basenameSafe($realPath);
        $name = $this->e2gEncode($name);

        // converting non-latin names with MODx's stripAlias function
        $nameAlias = $this->modx->stripAlias($name);

        if ($name != $nameAlias) {
            $basePath = dirname($realPath);
            $newPath = $basePath . DIRECTORY_SEPARATOR . $this->e2gDecode($nameAlias);
            $newPath = $this->_checkFolderDuplication($nameAlias, $pid);
            $fullNewPath = $basePath . DIRECTORY_SEPARATOR . $this->e2gDecode($newPath);
            $rename = rename($realPath, $fullNewPath);
            if (!$rename) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_rename_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . str_replace('../', '', $this->e2gEncode($path)) . ' => ';
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . str_replace('../', '', $this->e2gEncode(dirname($path))) . '/' . $nameAlias;
                return FALSE;
            }
            $this->changeModOwnGrp('dir', $basePath . DIRECTORY_SEPARATOR . $this->e2gDecode($nameAlias));

            // glue them back
            $realPath = $basePath . DIRECTORY_SEPARATOR . $this->e2gDecode($nameAlias) . DIRECTORY_SEPARATOR;
            $name = $nameAlias;
        }

        if (!($id = $tree->insert($name, $pid))) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_add_err'] . ' : ' . $tree->error;
            return FALSE;
        }
        $this->modx->db->query(
                'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'SET date_added=NOW() '
                . ', added_by=\'' . $userId . '\' '
                . 'WHERE cat_id=' . $id
        );

        $suc = realpath(dirname($path) . '/' . $this->e2gDecode($name));
        $suc = $this->e2gEncode($suc);
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['dir_added'] . ' : ' . $suc;

        // invoke the plugin
        $this->plugin('OnE2GFolderAdd', array('gid' => $id, 'foldername' => $name));

        /**
         * goldsky -- if there is no index.html inside folders, this will create it.
         */
        $this->createIndexHtml($realPath, $this->lng['indexfile']);

        $fs = array();
        $fs = @glob($realPath . DIRECTORY_SEPARATOR . '*');
        natsort($fs);

        if ($fs != FALSE)
            foreach ($fs as $filePath) {
                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_start();

                if ($this->validFolder($filePath)) {
                    // goldsky -- if the path is a dir, go deeper as $realPath==$filePath
                    if (!$this->_addAll($filePath, $id, $userId)) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_add_err'] . ' : ' . $filePath;
                        return FALSE;
                    }
                } elseif ($this->validFile($filePath)) {
                    /**
                     * INSERT filename into database
                     */
                    if (!$this->_addFile($filePath, $id, $userId)) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_add_err'] . ' : ' . $filePath;
                        return FALSE;
                    }
                }
                else
                    continue;
                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_end_clean();
            }
        return TRUE;
    }

    /**
     * Checking parent folder whether it exists in the database
     * @param   string  $filePath       complete file path
     * @param   int     $pid            parent's ID which is to be checked
     * @return  bool    TRUE | FALSE : False if the parent's name does not
     *  match with the specified parent's ID
     */
    private function _checkFolders($filePath, $pid) {
        $basePath = realpath($filePath);
        if (empty($filePath) || !$this->validFile($filePath)) {
            return FALSE;
        }

        $basename = $this->basenameSafe($filePath);
        $basename = $this->e2gEncode($basename);

        $rootPath = realpath(MODX_BASE_PATH . $this->e2gModCfg['dir']);
        $dirName = dirname($filePath);
        if ($rootPath == $dirName && $pid == '1') {
            return TRUE;
        }
        $basePath = str_replace($rootPath, '', $basePath);
        $basePath = trim($basePath, DIRECTORY_SEPARATOR);

        $pathArray = @explode(DIRECTORY_SEPARATOR, $basePath);
        $pathArrayReverse = array_reverse($pathArray);
        unset($pathArrayReverse[0]);

        if ($this->e2gEncode($pathArrayReverse[1]) != $this->getDirInfo($pid, 'cat_name')) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_db_notexists_err'] . ' : ' . $this->e2gEncode($pathArrayReverse[1]);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * To add file from the FTP or add file button into the database
     * @param  string   $f      filename
     * @param  int      $pid    current parent ID
     * @param  string   $userId logged in user's ID, for database signature
     * @return bool     TRUE | FALSE
     */
    private function _addFile($filePath, $pid, $userId = null) {
        $fileRealPath = realpath($filePath);
        $userId = !empty($userId) ? $userId : $this->modx->getLoginUserID();

        if (empty($fileRealPath)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_add_err'];
            return FALSE;
        }
        if (empty($userId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['invalid_uid'];
            return FALSE;
        }

        $inf = @getimagesize($fileRealPath);
        if ($inf[2] > 3 || !is_numeric($pid)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_add_err'];
            return FALSE;
        }

        if (!$this->_checkFolders($fileRealPath, $pid))
            return FALSE;

        $basename = $this->basenameSafe($fileRealPath);
        $basename = $this->e2gEncode($basename);

        // converting non-latin names with MODx's stripAlias function
        $fileAlias = $this->modx->stripAlias($basename);
        if ($basename != $fileAlias) {
            $dirPath = dirname($fileRealPath);
            $rename = rename($fileRealPath, $dirPath . DIRECTORY_SEPARATOR . $this->e2gDecode($fileAlias));
            if (!$rename) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_rename_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $filePath . ' => ' . $dirPath . DIRECTORY_SEPARATOR . $this->e2gDecode($fileAlias);
                return FALSE;
            }
            $this->changeModOwnGrp('file', $dirPath . DIRECTORY_SEPARATOR . $this->e2gDecode($fileAlias));

            $fileRealPath = $dirPath . DIRECTORY_SEPARATOR . $this->e2gDecode($fileAlias);
            $basename = $fileAlias;
        }

        $newInf = array();
        // RESIZE
        $newInf = $this->_resizeImg($fileRealPath, $inf, $this->e2g['maxw'], $this->e2g['maxh'], $this->e2g['maxthq']);
        $size = $newInf['size'];
        $width = $newInf[0];
        $height = $newInf[1];
        $time = $newInf['time'];

        $insertFile = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                . "SET dir_id ='$pid'"
                . ", filename='$basename'"
                . ", size='$size'"
                . ", width='$width'"
                . ", height='$height'"
                . ", date_added=NOW()"
                . ", added_by='$userId'"
                . ", last_modified='$time'"
                . ", modified_by='$userId'"
        ;
        $queryInsertFile = mysql_query($insertFile);
        if (!$queryInsertFile) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertFile;
            return FALSE;
        }

        // invoke the plugin
        $this->plugin('OnE2GFileAdd', array(
            'fid' => mysql_insert_id()
            , 'filename' => $filteredName
            , 'pid' => $pid
        ));

        if ($this->e2gModCfg['e2g_debug'] == '1')
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['file_added'] . ' ' . $basename;

        return TRUE;
    }

    /**
     * To resize image by configuration settings
     * @param string $filename  file's/folder's name
     * @param int    $inf       getimagesize($filename);
     * @param int    $w         width
     * @param int    $h         height
     * @param int    $thq       thumbnail quality
     */
    private function _resizeImg($filename, $inf, $w, $h, $thq) {
        // initial declaration
        $this->changeModOwnGrp('file', $filename);

        $newInf = @getimagesize($filename);
        $newInf['size'] = filesize($filename);
        $newInf['time'] = filemtime($filename);

        // if both configs are not zeros
        if ($w + $h !== 0) {

            // fixing the zero values to get a proportional dimension
            if ($w == 0 && $h != 0) {
                $w = round($h * $inf[0] / $inf[1]);
            } elseif ($w != 0 && $h == 0) {
                $h = round($w * $inf[1] / $inf[0]);
            }

            // width / height
            $srcRatio = round($inf[0] / $inf[1], 2);
            $dstRatio = round($w / $h, 2);
            // height / width
            $srcFlipRatio = round($inf[1] / $inf[0], 2);
            $dstFlipRatio = round($h / $w, 2);

            if ($srcRatio >= 1 && $dstRatio >= 1           // both are landscape ratios
                    or $srcRatio <= 1 && $dstRatio <= 1) { // both are portrait ratios
                // thinner ratio
                if ($srcRatio < $dstRatio) {
                    // taller height source image
                    if ($inf[1] > $h) {
                        $w = round($h * $inf[0] / $inf[1], 2);
                    }
                } else {
                    // wider width source image
                    if ($inf[0] > $w) {
                        $h = round($w * $inf[1] / $inf[0], 2);
                    }
                }
            }

            if ($this->e2g['resize_orientated_img'] == '1') {

                // the source image is the same or smaller than the destination on width AND height
                if ($inf[1] <= $w && $inf[0] <= $h) {
                    return $newInf;
//                    return FALSE;
                }

                if ($srcRatio < 1 && $dstRatio > 1           // source is portrait, destination is landscape
                        or $srcRatio > 1 && $dstRatio < 1) { // source is landscape, destination is portrait
                    // thinner ratio
                    if ($srcRatio < $dstFlipRatio) {
                        $h = $w;
                        $w = round($h * $inf[0] / $inf[1], 2);
                    }
                    // thicker ratio
                    else {
                        $w = $h;
                        $h = round($w * $inf[1] / $inf[0], 2);
                    }
                }
            } // if ($this->e2g['resize_orientated_img'] == '1')
            else {

                // the source image is smaller than the destination on width AND height
                if ($inf[0] <= $w && $inf[1] <= $h) {
                    return $newInf;
//                    return FALSE;
                }

                // source is portrait, destination is landscape
                if ($srcRatio < 1 && $dstRatio > 1) {
                    if ($inf[1] > $h) {
                        if ($srcRatio < $dstRatio) {
                            $w = round($h * $inf[0] / $inf[1], 2);
                        } else {
                            $h = round($w * $inf[1] / $inf[0], 2);
                        }
                    }

                    // if the source as same as the destination, do nothing and return back
                    if ($inf[1] == $h) {
                        return $newInf;
//                    return FALSE;
                    }
                } // if ( $srcRatio < 1 && $dstRatio > 1 )
                // source is landscape, destination is portrait
                elseif ($srcRatio > 1 && $dstRatio < 1) {
                    $h = round($w * $inf[1] / $inf[0], 2);
                }
            } // else if not oriented
            // OPEN
            if ($inf[2] == 1)
                $im = imagecreatefromgif($filename);
            elseif ($inf[2] == 2)
                $im = imagecreatefromjpeg($filename);
            elseif ($inf[2] == 3)
                $im = imagecreatefrompng($filename);
            else {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_create_err'] . ' ' . $filename;
                return $newInf;
//                return FALSE;
            }

            // CREATE NEW IMG
            $pic = imagecreatetruecolor($w, $h);
            $bgc = imagecolorallocate($pic, 255, 255, 255);
            imagefill($pic, 0, 0, $bgc);
            imagecopyresampled($pic, $im, 0, 0, 0, 0, $w, $h, $inf[0], $inf[1]);

            // SAVE
            if ($inf[2] == 1)
                imagegif($pic, $filename);
            elseif ($inf[2] == 2)
                imagejpeg($pic, $filename, $thq);
            elseif ($inf[2] == 3)
                imagepng($pic, $filename);

            imagedestroy($pic);
            imagedestroy($im);

            clearstatcache();
        } // if ( $w + $h !== 0 )
        // override the initial process
        $this->changeModOwnGrp('file', $filename);

        $newInf = @getimagesize($filename);
        $newInf['size'] = filesize($filename);
        $newInf['time'] = filemtime($filename);
        return $newInf;
    }

    public function getLoginUserID() {
        return $this->modx->getLoginUserID();
    }

    /**
     * To synchronize between physical gallery contents and database
     * @param string    $path   path to file or folder
     * @param int       $pid    current parent ID
     * @param string    $userId logged in user's ID, for database signature
     */
    public function synchro($path, $pid, $userId = null) {
        $path = realpath($path);
        $userId = !empty($userId) ? $userId : $this->modx->getLoginUserID();

        if (!$this->validFolder($path) || empty($path)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $path;
            return __LINE__ . ' : ' . $path;
//            return FALSE;
        }
        if (empty($pid)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_id'] . ' $pid=' . $pid;
            return __LINE__ . ' : ' . $this->lng['err_empty_id'] . ' $pid=' . $pid;
//            return FALSE;
        }
        if (empty($userId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['invalid_uid'] . ' : ' . $userId;
            return __LINE__ . ' : ' . $this->lng['invalid_uid'] . ' : ' . $userId;
//            return FALSE;
        }

        $timeStart = microtime(TRUE);
        /**
         * STORE variable arrays for synchronizing comparison
         */
        // MySQL Dir list
        $selectDirs = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE parent_id=' . $pid;
        $querySelectDirs = mysql_query($selectDirs);
        if (!$querySelectDirs) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs;
            return __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs;
//            return FALSE;
        }

        $mdirs = array();
        while ($l = mysql_fetch_assoc($querySelectDirs)) {
            $mdirs[$l['cat_name']]['id'] = $l['cat_id']; // goldsky -- to be connected between db <--> fs
            $mdirs[$l['cat_name']]['name'] = $l['cat_name'];
        }
        mysql_free_result($querySelectDirs);

        // MySQL File list
        $selectFiles = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE dir_id=' . $pid;
        $querySelectFile = mysql_query($selectFiles);
        if (!$querySelectFile) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
            return __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
//            return FALSE;
        }

        $mfiles = array();
        while ($l = mysql_fetch_assoc($querySelectFile)) {
            $mfiles[$l['filename']]['id'] = $l['id'];
            $mfiles[$l['filename']]['name'] = $l['filename'];
        }

        mysql_free_result($querySelectFile);

        /**
         * goldsky -- if there is no index.html inside folders, this will create it.
         */
        $this->createIndexHtml($path, $this->lng['indexfile']);

        $fs = array();
        $fs = @glob($path . DIRECTORY_SEPARATOR . '*');
        natsort($fs);

        /**
         * READ the real physical objects, store into database
         */
        if ($fs != FALSE) {
            foreach ($fs as $filePath) {
                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_start();

                $realPath = realpath($filePath);
                if (!empty($realPath)) {
                    $name = $this->basenameSafe($realPath);
                } else {
                    $name = basename($filePath);
                }

                $name = $this->e2gEncode($name);

                if ($this->validFolder($filePath)) { // as a folder/directory
                    if ($name == '_thumbnails')
                        continue;
                    if (isset($mdirs[$name])) {
                        $sync = $this->synchro($filePath, $mdirs[$name]['id'], $userId);
                        if ($sync !== TRUE) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['synchro_err'] . ' : ' . $filePath;
                            return __LINE__ . ' : ' . $this->lng['synchro_err'] . ' : ' . $sync;
//                            return FALSE;
                        }
                        unset($mdirs[$name]);
                    } else { // as ALL folder and file children of the current directory
                        /**
                         * INSERT folder's and file's names into database
                         */
                        if (!$this->_addAll($filePath, $pid, $userId)) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['all_add_error'] . ' : ' . $filePath;
                            return __LINE__ . ' : ' . $this->lng['all_add_error'] . ' : ' . $filePath;
//                            return FALSE;
                        }
                    }
                }
                // as an allowed file in the current directory
                elseif ($this->validFile($filePath)) {
                    if (isset($mfiles[$name])) {
                        // goldsky -- add the resizing of old images
                        $inf = @getimagesize($filePath);
                        $newInf = $this->_resizeImg($filePath, $inf, $this->e2g['maxw'], $this->e2g['maxh'], $this->e2g['maxthq']);
                        // RESIZE
                        if ($this->e2g['resize_old_img'] == '1') {
                            $size = $newInf['size'];
                            $width = $newInf[0];
                            $height = $newInf[1];
                            $time = $newInf['time'];
                        } else {
                            $size = filesize($filePath);
                            $width = $inf[0];
                            $height = $inf[1];
                            $time = filemtime($filePath);
                            clearstatcache();
                        }

                        $updateFile = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                                . "SET size='$size'"
                                . ", width='$width'"
                                . ", height='$height'"
                                . ", last_modified='$time'"
                                . ", modified_by='" . $userId . "' "
                                . "WHERE filename='$name'"
                        ;

                        $queryUpdateFile = mysql_query($updateFile);
                        if (!$queryUpdateFile) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFile;
                            return __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFile;
//                            return FALSE;
                        }

                        // goldsky -- if this already belongs to a file in the record, skip it!
                        unset($mfiles[$name]);
                    } else {
                        /**
                         * INSERT filename into database
                         */
                        if (!$this->_addFile($filePath, $pid, $userId)) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $filePath;
                            return __LINE__ . ' : ' . $filePath;
//                            return FALSE;
                        }
                    }
                }
                /**
                 * goldsky -- File/folder may exists, but NOT a valid folder or a valid file,
                 * probably has an unallowed extension or strange characters.
                 */
                else
                    continue;

                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_end_clean();

                usleep(10);
            }
        } // if ($fs!=FALSE)
        /**
         * UNMATCHED comparisons action
         */
        // Deleted physical dirs, DELETE record from database
        if (isset($mdirs) && count($mdirs) > 0) {
            require_once E2G_MODULE_PATH . 'includes/models/TTree.class.php';
            $tree = new TTree();
            $tree->table = $this->modx->db->config['table_prefix'] . 'easy2_dirs';
            foreach ($mdirs as $key => $value) {
                $ids = $tree->delete((int) $value['id']);
                if (empty($ids)) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_id'] . ' : ' . $key . '<br />';
                    return FALSE;
                }
                $implodedDirIds = implode(',', $ids);
                $selectFiles = 'SELECT id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files WHERE dir_id IN(' . $implodedDirIds . ')';
                $querySelectFiles = mysql_query($selectFiles);
                if (!$querySelectFiles) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
                    return __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
//                    return FALSE;
                }
                $fileIds = array();
                while ($l = mysql_fetch_row($querySelectFiles)) {
                    $fileIds[] = $l[0];
                }
                mysql_free_result($querySelectFiles);

                if (count($fileIds) > 0) {
                    $implodedFileIds = implode(',', $fileIds);
                    $deleteFileComments = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                            . 'WHERE file_id IN(' . $implodedFileIds . ')';
                    $queryDeleteFileComments = mysql_query($deleteFileComments);
                    if (!$queryDeleteFileComments) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFileComments;
                        return __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFileComments;
//                        return FALSE;
                    }
                }

                $deleteFiles = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                        . 'WHERE dir_id IN(' . $implodedDirIds . ')';
                $queryDeleteFiles = mysql_query($deleteFiles);
                if (!$queryDeleteFiles) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFiles;
                    return __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFiles;
//                    return FALSE;
                }
            }
        }

        // Deleted physical files, DELETE record from database
        if (isset($mfiles) && count($mfiles) > 0) {
            $fileIds = array();
            foreach ($mfiles as $v) {
                $fileIds[] = $v['id'];
            }
            $implodedFileIds = implode(',', $fileIds);

            $deleteFiles = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE id IN(' . $implodedFileIds . ')';
            $queryDeleteFiles = mysql_query($deleteFiles);
            if (!$queryDeleteFiles) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFiles;
                return __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFiles;
//                return FALSE;
            }

            $deleteFileComments = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                    . 'WHERE file_id IN(' . $implodedFileIds . ')';
            $queryDeleteFileComments = mysql_query($deleteFileComments);
            if (!$queryDeleteFileComments) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFileComments;
                return __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFileComments;
//                return FALSE;
            }
        }

        $timeEnd = microtime(TRUE);
        $timeTotal = $timeEnd - $timeStart;

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['synchro_suc'] . ' (' . $timeTotal . 's)';
        return TRUE;
    }

    /**
     * to check the existance of the file in the file system.<br />
     * if exists, this will add numbering into the uploaded files.
     * @param string    $name   name
     * @param int       $pid    parent's ID
     * @return string   new name if duplicate exists
     */
    private function _checkFileDuplication($name, $pid) {
        $selectCheck = 'SELECT filename FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE filename = \'' . $name . '\' AND dir_id = \'' . $pid . '\'';
        $queryCheck = @mysql_query($selectCheck);
        while ($l = @mysql_fetch_assoc($queryCheck)) {
            $fetchRow[$l['filename']] = $l['filename'];
        }
        mysql_free_result($queryCheck);

        if (isset($fetchRow[$name])) {
            $ext = substr($name, strrpos($name, '.'));
            $filename = substr($name, 0, -(strlen($ext)));
            $oldSuffix = end(@explode('_e2g_', $filename));
            $prefixFilename = substr($filename, 0, -(strlen($oldSuffix)) - 1);
            if (is_numeric($oldSuffix)) {
                $notNumberSuffix = '';
                $newNumberSuffix = (int) $oldSuffix + 1;
            } else {
                $notNumberSuffix = '_e2g_' . $oldSuffix;
                $newNumberSuffix = 1;
            }
            $newFilename = ( $prefixFilename != '' ? $prefixFilename . $notNumberSuffix : $filename ) . '_' . $newNumberSuffix . $ext;
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $name . ' exists, file was renamed to be ' . $newFilename;
        }
        else
            return $name;

        // recursive check
        $recursiveCheckSelect = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE filename=\'' . $newFilename . '\' AND dir_id = \'' . $pid . '\'';
        $recursiveCheckQuery = @mysql_query($recursiveCheckSelect);
        while ($l = @mysql_fetch_assoc($recursiveCheckQuery)) {
            $recursiveFetchRow[$l['filename']] = $l['filename'];
        }
        mysql_free_result($recursiveCheckQuery);
        if (isset($recursiveFetchRow[$newFilename])) {
            $recursiveNewFilename = $this->_checkFileDuplication($newFilename, $pid);
            if (!$recursiveNewFilename) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $name . ' exists, but file could not be renamed to be ' . $newFilename;
            } else
                $newFilename = $recursiveNewFilename;
        }

        return $newFilename;
    }

    /**
     * to check the existance of the folder in the file system.<br />
     * if exists, this will add numbering into the synchronized folder.
     * @param string    $name   name
     * @param int       $pid    parent's ID
     * @return string   new name if duplicate exists
     */
    private function _checkFolderDuplication($name, $pid) {
        $selectCheck = 'SELECT cat_name FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE cat_name = \'' . $name . '\' AND parent_id = \'' . $pid . '\'';
        $queryCheck = @mysql_query($selectCheck);
        while ($l = @mysql_fetch_assoc($queryCheck)) {
            $fetchRow[$l['cat_name']] = $l['cat_name'];
        }
        mysql_free_result($queryCheck);

        if (isset($fetchRow[$name])) {
            $copyName = $name;
            $oldSuffix = end(@explode('_', $copyName));
            $prefixDirName = substr($copyName, 0, -(strlen($oldSuffix)) - 1);
            if (is_numeric($oldSuffix)) {
                $notNumberSuffix = '';
                $newNumberSuffix = (int) $oldSuffix + 1;
            } else {
                $notNumberSuffix = '_' . $oldSuffix;
                $newNumberSuffix = 1;
            }
            $newDirName = ( $prefixDirName != '' ? $prefixDirName . $notNumberSuffix : $copyName ) . '_' . $newNumberSuffix . $ext;
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $name . ' exists, folder was renamed to be ' . $newDirName;
        }
        else
            return $name;

        // recursive check
        $recursiveCheckSelect = 'SELECT cat_name FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE cat_name=\'' . $newDirName . '\' AND parent_id = \'' . $pid . '\'';
        $recursiveCheckQuery = @mysql_query($recursiveCheckSelect);
        while ($l = @mysql_fetch_assoc($recursiveCheckQuery)) {
            $recursiveFetchRow[$l['cat_name']] = $l['cat_name'];
        }
        mysql_free_result($recursiveCheckQuery);
        if (isset($recursiveFetchRow[$newDirName])) {
            $recursiveNewDirName = $this->_checkFolderDuplication($newDirName, $pid);
            if (!$recursiveNewDirName) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $name . ' exists, but folder could not be renamed to be ' . $newDirName;
            } else
                $newDirName = $recursiveNewDirName;
        }

        return $newDirName;
    }

    /**
     * Check the valid characters in names
     * @param string    $characters The string to be checked
     * @param string    $line       Line number for debugging
     * @return bool     TRUE means BAD! | FALSE means GOOD!
     */
    private function _hasBadChar($characters, $line) {
        $badChars = array(
            "U+0000", "/", "\\", ":", "*", "?", "'", "\"", "<", ">", "|", ";"
            , "@", "=", "#", "&", "!", "*", "'", "(", ")", ",", "{", "}", ","
            , "^", "~", "[", "]", "`"
        );
        foreach ($badChars as $badChar) {
            if (strstr($characters, $badChar)) {
                $_SESSION['easy2err'][] = $line . ' : ' . $this->lng['char_bad'] . ' => <b>' . $characters . '</b>';
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Too much memory swallowed. Need a meter in here.
     * @link http://www.php.net/manual/en/function.memory-get-usage.php#93012
     */
    private function _echoMemoryUsage() {
        $memUsage = memory_get_usage(true);
        $out = '<a>' . $this->lng['memory_usage'] . ' : ';
        if ($memUsage < 1024)
            $out .= $memUsage . " bytes";
        elseif ($memUsage < 1048576)
            $out .= round($memUsage / 1024, 2) . ' ' . $this->lng['kilobytes'];
        else
            $out.= round($memUsage / 1048576, 2) . ' ' . $this->lng['megabytes'];
        $out.= "</a>";
        return $out;
    }

    /**
     * Get folders structure for select options, and put it into the property:
     * 1. check the internal property first
     * 2. create it if it doesn't exist
     * This will lighten up the page when one page call the same method more than once.
     * @param   int     $parentId   Parent's ID
     * @param   bool    $selected   turn on the selected="selected" if the current folder is the selected folder
     * @param   string  $jsActions  Javascript's action
     * @return  string  The multiple options
     */
    private function _getDirDropDownOptions($parentId = 0, $selected = 0, $jsActions = NULL) {
        if (!empty($this->_dirDropDownOptions[$parentId][$selected][$jsActions])) {
            return $this->_dirDropDownOptions[$parentId][$selected][$jsActions];
        }
        $this->_dirDropDownOptions[$parentId][$selected][$jsActions] = $this->_dirDropDownOptions($parentId, $selected, $jsActions);
        return $this->_dirDropDownOptions[$parentId][$selected][$jsActions];
    }

    /**
     * create folders structure for select options.
     * @param   int     $parentId   Parent's ID
     * @param   bool    $selected   turn on the selected="selected" if the current folder is the selected folder
     * @param   string  $jsActions  Javascript's action
     * @return  string  The multiple options
     */
    private function _dirDropDownOptions($parentId = 0, $selected = 0, $jsActions = NULL) {
        $selectDirs = 'SELECT parent_id, cat_id, cat_name, cat_level '
                . 'FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE parent_id=' . $parentId;

        $querySelectDirs = mysql_query($selectDirs);
        if (!$querySelectDirs) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs;
            return FALSE;
        }

        $numDir = @mysql_num_rows($querySelectDirs);

        $childrenDirs = array();
        while ($l = mysql_fetch_assoc($querySelectDirs)) {
            $childrenDirs[$l['cat_id']]['parent_id'] = $l['parent_id'];
            $childrenDirs[$l['cat_id']]['cat_id'] = $l['cat_id'];
            $childrenDirs[$l['cat_id']]['cat_name'] = $l['cat_name'];
            $childrenDirs[$l['cat_id']]['cat_level'] = $l['cat_level'];
        }
        mysql_free_result($querySelectDirs);

        $output = '';
        foreach ($childrenDirs as $childDir) {
            // DISPLAY
            $output .= '
                            <option value="' . $childDir['cat_id'] . '"'
                    . ( ( $childDir['cat_id'] == '1' ) ? ' style="background-color:#ddd;"' : '' )
                    . ( isset($jsActions) ? ' ' . $jsActions : '' )
                    . ( ( $childDir['cat_id'] == $this->sanitizedGets['pid'] && $selected != 0 ) ? ' selected="selected"' : '' )
                    . '>';

            // **************** ONLY START MARGIN **************** //
            for ($k = 1; $k < $childDir['cat_level']; $k++) {
                if ($k == 1)
                    $output .= '&nbsp;&nbsp;&nbsp;';
                else
                    $output .= '&nbsp;&brvbar;&nbsp;&nbsp;';
            }
            for ($k = 1; $k < $childDir['cat_level']; $k++) {
                if ($k == 1)
                    $output .= '&nbsp;&brvbar;';
            }
            if ($childDir['cat_level'] > 1)
                $output .= '--';
            // ***************** ONLY END MARGIN ***************** //

            $output .= '&nbsp;' . $childDir['cat_name'] . ' [id:' . $childDir['cat_id'] . ']</option>';

            //*********************************************************/
            // GET SUB-FOLDERS
            $selectSubFolders = 'SELECT parent_id, cat_id, cat_name '
                    . 'FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                    . 'WHERE parent_id=' . $childDir['cat_id'] . ' '
                    . 'ORDER BY cat_name ASC'
            ;
            $querySelectSubFolders = mysql_query($selectSubFolders);
            if (!$querySelectSubFolders) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectSubFolders;
                return FALSE;
            }

            $numSub = @mysql_num_rows($querySelectSubFolders);
            if ($numSub > 0) {
                $output .= $this->_dirDropDownOptions($childDir['cat_id'], $selected, $jsActions);
            }
            mysql_free_result($querySelectSubFolders);
            //*********************************************************/
        } // foreach ($childrenDirs as $childDir)
        return $output;
    }

    /**
     * create files structure for select options.
     * @param   int     $parentId   Parent's ID
     * @param   bool    $selected   turn on the selected="selected" if the current folder is the selected folder
     * @param   string  $jsActions  Javascript's action
     * @return  string  The multiple options
     */
    private function _fileDropDownOptions($parentId = 0, $selected = 0, $jsActions = NULL) {
        $selectFiles = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE dir_id=' . $parentId;

        $querySelectFiles = mysql_query($selectFiles);
        if (!$querySelectFiles) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
            return FALSE;
        }

        $numFile = @mysql_num_rows($querySelectFiles);
        if ($numFile > 0) {
            $childrenFiles = array();
            $output = '';
            $catThumbId = $this->getDirInfo($this->sanitizedGets['dir_id'], 'cat_thumb_id');
            while ($l = mysql_fetch_assoc($querySelectFiles)) {
                // DISPLAY
                $selected = $l['id'] == $catThumbId ? 1 : 0;
                $output .= '
                            <option value="' . $l['id'] . '"'
                        . ( isset($jsActions) ? ' ' . $jsActions : '' )
                        . ( $selected ? ' selected="selected"' : '' )
                        . '>';
                $output .= '&nbsp;' . $l['filename'] . ' [id:' . $l['id'] . ']';
                $path = $this->getPath($l['dir_id']);
                $img= $this->imgShaper($l['dir_id'], $this->e2gModCfg['dir'] . $path . $l['filename'], 30, 30, 90 );
                if ($img)
                    $output = '<img src="'.$img.'" />';
                $output .= '</option>';
            }
            mysql_free_result($querySelectFiles);

        }

        //*********************************************************/
        // GET SUB-FOLDERS
        $selectSubFolders = 'SELECT parent_id, cat_id, cat_name '
                . 'FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE parent_id=' . $parentId . ' '
                . 'ORDER BY cat_name ASC'
        ;
        $querySelectSubFolders = mysql_query($selectSubFolders);
        if (!$querySelectSubFolders) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectSubFolders;
            return FALSE;
        }

        $numSub = @mysql_num_rows($querySelectSubFolders);
        if ($numSub > 0) {
            while ($res = mysql_fetch_assoc($querySelectSubFolders)) {
                $output .= '<optgroup label="' . $res['cat_name'] . '">';
                $output .= $this->_fileDropDownOptions($res['cat_id'], $selected, $jsActions);
                $output .= '</optgroup>';
            }
        }
        mysql_free_result($querySelectSubFolders);
        //*********************************************************/
        return $output;
    }

    /**
     * To return an options selection for tag
     * @param   string  $tag    the tag
     * @return  string  option selection
     */
    private function _tagOptions($tag) {
        // Directory
        $selectDirTags = 'SELECT DISTINCT cat_tag FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs ';
        $queryDirTags = mysql_query($selectDirTags);

        if (!$queryDirTags) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirTags;
            return FALSE;
        }

        $numDirTags = mysql_num_rows($queryDirTags);

        while ($l = mysql_fetch_assoc($queryDirTags)) {
            if ($l['cat_tag'] == '' || $l['cat_tag'] == NULL)
                continue;
            $tagOptions[] = $l['cat_tag'];
        }

        // File
        $selectFileTags = 'SELECT DISTINCT tag FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files ';
        $queryFileTags = mysql_query($selectFileTags);

        if (!$queryFileTags) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileTags;
            return FALSE;
        }

        $numFileTags = mysql_num_rows($queryFileTags);

        while ($l = mysql_fetch_assoc($queryFileTags)) {
            if ($l['tag'] == '' || $l['tag'] == NULL)
                continue;
            $tagOptions[] = $l['tag'];
        }

        $singleTagOptions = array();
        for ($i = 0; $i < count($tagOptions); $i++) {
            $xpldTagOptions = @explode(',', $tagOptions[$i]);
            foreach ($xpldTagOptions as $xpldTag) {
                $xpldTag = trim($xpldTag);
                // recursive check of existing value
                if (!in_array($xpldTag, $singleTagOptions))
                    $singleTagOptions[] = $xpldTag;
            }
        }
        sort($singleTagOptions);
        for ($i = 0; $i < count($singleTagOptions); $i++) {
            $output .= '<option value="' . $singleTagOptions[$i] . '"'
                    . ( $tag == $singleTagOptions[$i] ? ' selected="selected"' : '' )
                    . '>' . $singleTagOptions[$i] . '</option>
                    ';
        }
        return $output;
    }

    /**
     * @author Jeff Miner mrjminer AT gmail DOT com
     * @link http://www.php.net/manual/en/features.file-upload.errors.php#99304
     * @param   int     $errorCode      Error code in number
     * @return  string  The error message
     */
    private function _fileUploadErrorMessage($errorCode) {
        $errorType = array(
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            'The uploaded file was only partially uploaded.',
            'No file was uploaded.',
            6 => 'Missing a temporary folder.',
            'Failed to write file to disk.',
            'A PHP extension stopped the file upload.'
        );
        $errorMessage = $errorType[$errorCode];
        return $errorMessage;
    }

    /**
     *
     * Unzip for Easy 2 Gallery : Unicode friendly, success/error reports.
     * @param   string  $file   filename
     * @param   string  $path   starting path
     * @return  bool    true/FALSE
     * @todo    unziping the non-latin file
     */
    private function _unzip($file, $path) {
        if ($this->e2gModCfg['e2g_encode'] == 'UTF-8 (Rin)') {
            $this->loadUtfRin();
        }

        $r = substr($path, strlen($path) - 1, 1);
        if ($this->e2gModCfg['e2g_encode'] == 'UTF-8 (Rin)') {
            $r = UTF8::substr($path, UTF8::strlen($path) - 1, 1);
        }
        else
            $r = substr($path, strlen($path) - 1, 1);
        if ($r != '\\' && $r != '/')
            $path .='/';

        if (!extension_loaded('zip')) {
            if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
                if (!@dl('php_zip.dll'))
                    return 0;
            } else {
                if (!@dl('zip.so'))
                    return 0;
            }
        }

        $zip = new ZipArchive;
        $zipOpen = $zip->open($file);

        if ($zipOpen !== TRUE) {
            $_SESSION['easy2err'][] = __LINE__ . ' Error : ' . $this->lng['zip_open_err'] . ' <b>' . $file . '</b>';
            return FALSE;
        }

        $fileCount = 0;
        $dirCount = 0;

        if ($zip->numFiles > 0) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                ob_start();

                $zipEntryName = $zip->getNameIndex($i);
                $zipContent = $zip->getFromIndex($i);
                /**
                 * ENCODING OPTIONS TO GET FILENAMES AND END SLASH
                 */
                if ($this->e2gModCfg['e2g_encode'] == 'none') {
                    $r = substr($zipEntryName, strlen($zipEntryName) - 1, 1);
                }
                if ($this->e2gModCfg['e2g_encode'] == 'UTF-8') {
                    $zipEntryName = utf8_decode($zipEntryName);
                    $r = substr($zipEntryName, strlen($zipEntryName) - 1, 1);
                }
                if ($this->e2gModCfg['e2g_encode'] == 'UTF-8 (Rin)') {
                    /**
                     * @uses Unicode conversion class.
                     * @todo : need more work on i18n stuff
                     */
                    $mbDetectEncoding = mb_detect_encoding($zipOpen);

                    // fixedmachine -- http://modxcms.com/forums/index.php/topic,49266.msg292206.html#msg292206
                    if ($mbDetectEncoding != 'ASCII' && $mbDetectEncoding != 'UTF-8') {
                        if (!$mbDetectEncoding) {
                            $zipEntryName = UTF8::convert_from($zipEntryName, "ASCII");
                        } else {
                            $zipEntryName = UTF8::convert_from($zipEntryName, $mbDetectEncoding);
                        }
                    }
                    $r = UTF8::substr($zipEntryName, UTF8::strlen($zipEntryName) - 1, 1);
                }

                // DETECT the directory entry
                if ($r == '/') {
                    // creates directory
                    if (!is_dir($path . $zipEntryName)) {
                        $xpldZipEntryName = array();
                        $unzipDirs = array();
                        // converting non-latin names with MODx's stripAlias function
                        $xpldZipEntryName = @explode('/', $zipEntryName);
                        foreach ($xpldZipEntryName as $unzipDir) {
                            $unzipDirs[] = $this->modx->stripAlias($unzipDir);
                        }
                        $implodedDir = @implode('/', $unzipDirs);
                        $mkdir = mkdir($path . $this->e2gDecode($implodedDir), 0777);
                        if (!$mkdir)
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['unzip_dir_err'] . ' <b>' . $path . $zipEntryName . '</b>';
                        else {
                            $dirCount++;
                            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['dirs_uploaded'] . ' ' . $path . $zipEntryName;
                        }
                    }
                } else {
                    // creates/copy the file
                    $xpldZipEntryName = array();
                    $unzipFiles = array();
                    // creates/copy the file
                    // converting non-latin names with MODx's stripAlias function
                    $xpldZipEntryName = @explode('/', $zipEntryName);
                    foreach ($xpldZipEntryName as $unzipFile) {
                        $unzipFiles[] = $this->modx->stripAlias($unzipFile);
                    }
                    $implodedFile = @implode('/', $unzipFiles);
                    $fd = fopen($path . $this->e2gDecode($implodedFile), 'w');
                    if ($fd) {
                        fwrite($fd, $zipContent);
                        fclose($fd);
                        $this->changeModOwnGrp('file', $path . $implodedFile);

                        $fileCount++;
                        if ($this->e2gModCfg['e2g_debug'] == '1')
                            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['files_uploaded'] . ' ' . $path . $zipEntryName;
                    }
                    else {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['unzip_file_err'] . ' <b>' . $path . $zipEntryName . '</b>';
                    }
                }
                ob_end_clean();
            } // for($i = 0; $i < $zip->numFiles; $i++)
        } // if ( $zip->numFiles > 0)

        $zip->close();
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $dirCount . ' ' . $this->lng['dirs_uploaded'] . '.';
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $fileCount . ' ' . $this->lng['files_uploaded'] . '.';

        return TRUE;
    }

    /**
     * Upload multiple files
     * @param   string  $post   file's information
     * @param   string  $files  file's or zipfile's object
     * @return  mixed   FALSE on failure or return report string on succeed
     */
    private function _uploadAll($post, $files) {
        $newParent = !empty($post['newparent']) ? $post['newparent'] : 1;

        if (empty($files['img']['name'][0]) && empty($files['zip']['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['upload_err'] . ' : ' . $this->lng['upload_empty'];
            return FALSE;
        }

        $error = 0;
        // CREATE PATH
        $path = $this->getPath($newParent);
        $newParentPath .= $this->e2g['dir'] . $path;
        // UPLOAD IMAGES
        if (!empty($files['img']['tmp_name'][0])) {
            $j = 0;
            $countFiles = count($files['img']['tmp_name']);
            for ($i = 0; $i < $countFiles; $i++) {
                if ($files['img']['error'][$i] !== 0) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : img error # '
                            . $files['img']['error'][$i] . $this->lng['upload_err'] . ' : '
                            . $this->_fileUploadErrorMessage($files['img']['error'][$i])
                            . ' => ' . $files['img']['name'][$i]
                    ;
                    $error++;
                    continue;
                }

                if (!is_uploaded_file($files['img']['tmp_name'][$i])) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['upload_err'] . ' ' . $files['img']['name'][$i];
                    continue;
                }
                if (!preg_match('/^image\//i', $files['img']['type'][$i])) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['type_err'] . ' ' . $files['img']['type'][$i] . ' => ' . $files['img']['name'][$i];
                    continue;
                }

                $inf = @getimagesize($files['img']['tmp_name'][$i]);
                if ($inf[2] > 3)
                    continue;
                $newInf = $this->_resizeImg($files['img']['tmp_name'][$i], $inf, $this->e2g['maxw'], $this->e2g['maxh'], $this->e2g['maxthq']);

                // converting non-latin names with MODx's stripAlias function
                $files['img']['name'][$i] = $this->modx->stripAlias(trim($files['img']['name'][$i]));

                /**
                 * CHECK the existing filenames inside the system.
                 * If exists, amend the filename with number
                 */
                $filteredName = $this->_checkFileDuplication($files['img']['name'][$i], $newParent);
                $filePath = '../' . $this->e2gDecode($newParentPath . $filteredName);
                $insertFile = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                        . 'SET dir_id=\'' . $newParent . '\''
                        . ', filename=\'' . $this->_escapeString($filteredName) . '\''
                        . ', size=\'' . $newInf['size'] . '\''
                        . ', width=\'' . $newInf[0] . '\''
                        . ', height=\'' . $newInf[1] . '\''
                        . ', alias=\'' . $this->_escapeString($post['alias'][$i]) . '\''
                        . ', summary=\'' . $this->_escapeString($post['summary'][$i]) . '\''
                        . ', tag=\'' . $this->_escapeString($post['tag'][$i]) . '\''
                        . ', description=\'' . $this->_escapeString($post['description'][$i]) . '\''
                        . ', date_added=NOW()';
                $queryInsertFile = mysql_query($insertFile);
                if (!$queryInsertFile) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertFile;
                    continue;
                }

                if (!move_uploaded_file($files['img']['tmp_name'][$i], $filePath)) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['upload_err'] . ' : ' . $filePath;
                }
                $this->changeModOwnGrp('file', $filePath, FALSE);

                // invoke the plugin
                $this->plugin('OnE2GFileUpload', array(
                    'fid' => mysql_insert_id()
                    , 'filename' => $filteredName
                    , 'pid' => $newParent
                ));

                $j++;
            } // for ($i = 0; $i < $countFiles; $i++)
            if ($error === 0)
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $j . ' ' . $this->lng['files_uploaded'] . '.';
        } // Upload images
        // UPLOAD ZIP
        if (!empty($files['zip']['name'])) {
            if ($files['zip']['error'] !== 0) {
                $error++;
                $_SESSION['easy2err'][] = __LINE__ . ' : zip error # ' . $files['zip']['error'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['upload_err'] . ' : '
                        . $this->_fileUploadErrorMessage($files['zip']['error'])
                        . ' (' . $files['zip']['name'] . ')'
                ;
                return FALSE;
            }
            $unzip = $this->_unzip(realpath($files['zip']['tmp_name']), realpath(MODX_BASE_PATH . $this->e2gDecode($newParentPath)));
            if (!$unzip) {
                $_SESSION['easy2err'][] = __LINE__ . ' <span class="warning"><b>' . $this->lng['upload_err']
                        . ($unzip === 0 ? 'Missing zip library (php_zip.dll / zip.so)' : '') . '</b></span><br /><br />';
            }

            @unlink($files['zip']['tmp_name']);
            $this->synchro('../' . $this->e2g['dir'], 1);
            $this->_cleanCache();

            // invoke the plugin
            $this->plugin('OnE2GZipUpload', array(
                'path' => realpath(MODX_BASE_PATH . $this->e2gDecode($newParentPath))
            ));
        }
        return TRUE;
    }

    /**
     * Unhide the checked list
     * @param   mixed   $post   list's variables
     * @return  mixed   TRUE | report
     */
    private function _showChecked($post) {
        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['select_dirfile_err'];
            return FALSE;
        }

        $countRes = array();
        // show dirs
        if (!empty($post['dir'])) {
            foreach ($post['dir'] as $k => $v) {
                if (!is_numeric($k)) {
                    continue;
                }
                $selectDirStatus = 'SELECT cat_name, cat_visible FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'WHERE cat_id=' . $k;
                $querySelectDirStatus = mysql_query($selectDirStatus);
                if (!$querySelectDirStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirStatus;
                    return FALSE;
                }

                $l = mysql_fetch_assoc($querySelectDirStatus);
                mysql_free_result($querySelectDirStatus);

                if ($l['cat_visible'] == '1') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_hiddennot_inverse_err'] . ' : ' . $l['cat_name'];
                    continue;
                }

                $queryUpdateDirStatus = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'SET cat_visible=\'1\' '
                        . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                        . 'WHERE cat_id=' . $k;
                $queryUpdateDirStatus = mysql_query($queryUpdateDirStatus);
                if (!$queryUpdateDirStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $queryUpdateDirStatus;
                    return FALSE;
                } else {
                    $countRes['ddb']++;
                }
            } // foreach ($post['dir'] as $k => $v)
        } // if (!empty($post['dir']))
        // show images
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                if (!is_numeric($k)) {
                    continue;
                }
                $selectFileStatus = 'SELECT filename, status FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                        . 'WHERE id=' . $k;
                $querySelectFileStatus = mysql_query($selectFileStatus);
                if (!$querySelectFileStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileStatus;
                    return FALSE;
                }

                $l = mysql_fetch_assoc($querySelectFileStatus);
                mysql_free_result($querySelectFileStatus);

                if ($l['status'] == '1') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_hiddennot_inverse_err'] . ' : ' . $l['filename'];
                    continue;
                }

                $updateFileStatus = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                        . 'SET status=\'1\' '
                        . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                        . 'WHERE id=' . $k;
                $queryUpdateFileStatus = mysql_query($updateFileStatus);
                if (!$queryUpdateFileStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFileStatus;
                    return FALSE;
                } else {
                    $countRes['fdb']++;
                }
            } // foreach ($post['im'] as $k => $v)
        } // if (!empty($post['im']))
        if (!empty($countRes['ddb'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['ddb'] . ' ' . $this->lng['dirs_hiddennot_suc'] . '.';
        }
        if (!empty($countRes['fdb'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['fdb'] . ' ' . $this->lng['files_hiddennot_suc'] . '.';
        }
        return TRUE;
    }

    /**
     * Hide the checked list
     * @param   mixed   $post   list's variables
     * @return  mixed   TRUE | report
     */
    private function _hideChecked($post) {
        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['select_dirfile_err'];
            return FALSE;
        }

        $countRes = array();
        // hide dirs
        if (!empty($post['dir'])) {
            foreach ($post['dir'] as $k => $v) {
                if (!is_numeric($k)) {
                    continue;
                }
                $selectDirStatus = 'SELECT cat_name, cat_visible FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'WHERE cat_id=' . $k;
                $querySelectDirStatus = mysql_query($selectDirStatus);
                if (!$querySelectDirStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirStatus;
                    return FALSE;
                }

                $l = mysql_fetch_assoc($querySelectDirStatus);
                mysql_free_result($querySelectDirStatus);

                if ($l['cat_visible'] == '0') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_hidden_inverse_err'] . ' : ' . $l['cat_name'];
                    continue;
                }

                $queryUpdateDirStatus = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'SET cat_visible=\'0\' '
                        . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                        . 'WHERE cat_id=' . $k;
                $queryUpdateDirStatus = mysql_query($queryUpdateDirStatus);
                if (!$queryUpdateDirStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $queryUpdateDirStatus;
                    return FALSE;
                } else {
                    $countRes['ddb']++;
                }
            } // foreach ($post['dir'] as $k => $v)
        } // if (!empty($post['dir']))
        // hide images
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                if (!is_numeric($k)) {
                    continue;
                }
                $selectFileStatus = 'SELECT filename, status FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                        . 'WHERE id=' . $k;
                $querySelectFileStatus = mysql_query($selectFileStatus);
                if (!$querySelectFileStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileStatus;
                    return FALSE;
                }

                $l = mysql_fetch_assoc($querySelectFileStatus);
                mysql_free_result($querySelectFileStatus);

                if ($l['status'] == '0') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_hidden_inverse_err'] . ' : ' . $l['filename'];
                    continue;
                }

                $updateFileStatus = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                        . 'SET status=\'0\' '
                        . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                        . 'WHERE id=' . $k;
                $queryUpdateFileStatus = mysql_query($updateFileStatus);
                if (!$queryUpdateFileStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFileStatus;
                    return FALSE;
                } else {
                    $countRes['fdb']++;
                }
            } // foreach ($post['im'] as $k => $v)
        } // if (!empty($post['im']))
        if (!empty($countRes['ddb'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['ddb'] . ' ' . $this->lng['dirs_hidden_suc'] . '.';
        }
        if (!empty($countRes['fdb'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['fdb'] . ' ' . $this->lng['files_hidden_suc'] . '.';
        }
        return TRUE;
    }

    /**
     * Unhide the on clicked folder
     * @param   int     $dirId  list's variables
     * @return  mixed   TRUE | report
     */
    private function _unhide($dirId) {
        if (empty($dirId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dpath_err'];
            return FALSE;
        }
        if (!is_numeric($dirId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['numeric_err'];
            return FALSE;
        }
        $updateDir = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'SET cat_visible=\'1\' '
                . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                . 'WHERE cat_id=' . $dirId;
        $queryUpdateDir = mysql_query($updateDir);
        if (!$queryUpdateDir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateDir;
            return FALSE;
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['dir_hiddennot_suc'];

        return TRUE;
    }

    /**
     * Hide the on clicked folder
     * @param   int     $dirId  list's variables
     * @return  mixed   TRUE | report
     */
    private function _hideDir($dirId) {
        if (empty($dirId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dpath_err'];
            return FALSE;
        }
        if (!is_numeric($dirId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['numeric_err'];
            return FALSE;
        }
        $updateDir = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'SET cat_visible=\'0\' '
                . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                . 'WHERE cat_id=' . $dirId;
        $queryUpdateDir = mysql_query($updateDir);
        if (!$queryUpdateDir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateDir;
            return FALSE;
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['dir_hidden_suc'];

        return TRUE;
    }

    /**
     * Unhide the on clicked file
     * @param   int     $fileId list's variables
     * @return  mixed   TRUE | report
     */
    private function _unhideFile($fileId) {
        if (empty($fileId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['fpath_err'];
            return FALSE;
        }
        if (!is_numeric($fileId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['numeric_err'];
            return FALSE;
        }
        $updateFile = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                . 'SET status=\'1\' '
                . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                . 'WHERE id=' . $fileId;
        $queryUpdateFile = mysql_query($updateFile);
        if (!$queryUpdateFile) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFile;
            return FALSE;
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['file_hiddennot_suc'];

        return TRUE;
    }

    /**
     * Hide the on clicked file
     * @param   int     $fileId list's variables
     * @return  mixed   TRUE | report
     */
    private function _hideFile($fileId) {
        if (empty($fileId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['fpath_err'];
            return FALSE;
        }
        if (!is_numeric($fileId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['numeric_err'];
            return FALSE;
        }
        $updateFile = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                . 'SET status=\'0\' '
                . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                . 'WHERE id=' . $fileId;
        $queryUpdateFile = mysql_query($updateFile);
        if (!$queryUpdateFile) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFile;
            return FALSE;
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['file_hidden_suc'];

        return TRUE;
    }

    /**
     * Delete the checked list
     * @param   mixed   $post   list's variables
     * @return  mixed   TRUE | report
     */
    private function _deleteChecked($post) {
        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['select_dirfile_err'];
            return FALSE;
        }

        $out = '';
        // COUNTER
        $res = array(
            'fdb' => array(0, 0),
            'ffp' => array(0, 0),
            'ddb' => array(0, 0),
            'dfp' => array(0, 0),
        );
        // Delete dirs
        if (!empty($post['dir'])) {
            require_once E2G_MODULE_PATH . 'includes/models/TTree.class.php';
            $tree = new TTree();
            $tree->table = $this->modx->db->config['table_prefix'] . 'easy2_dirs';

            foreach ($post['dir'] as $k => $v) {
                // the numeric keys are the member of the database
                if (is_numeric($k)) {
                    $ids = $tree->delete((int) $k);
                    if (empty($ids)) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_id'] . ' : ' . $k . '<br />';
                        return FALSE;
                    }
                    $implodedDirIds = implode(',', $ids);
                    $selectFileIds = 'SELECT id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE dir_id IN(' . $implodedDirIds . ')';
                    $querySelectFileIds = mysql_query($selectFileIds);
                    if (!$querySelectFileIds) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileIds;
                        return FALSE;
                    }
                    $fileIds = array();
                    while ($l = mysql_fetch_row($querySelectFileIds)) {
                        $fileIds[] = $l[0];
                    }
                    mysql_free_result($querySelectFileIds);

                    $implodedFileIds = implode(',', $fileIds);
                    if (count($fileIds) > 0) {
                        $delComments = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                                . 'WHERE file_id IN(' . $implodedFileIds . ')';
                        $delCommentsQuery = mysql_query($delComments);
                        if (!$delCommentsQuery) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $delComments;
                            return FALSE;
                        }
                    }
                    $delFiles = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE dir_id IN(' . $implodedDirIds . ')';
                    $delFilesQuery = mysql_query($delFiles);
                    if (!$delFilesQuery) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $delFiles;
                        return FALSE;
                    }
                    $res['fdb'][0] += mysql_affected_rows();
                    if (count($ids) > 0) {
                        $res['ddb'][0] += count($ids);
                    } else {
                        $res['ddb'][1]++;
                    }
                } // if (is_numeric($k))
                if (!empty($v)) {
                    $v = str_replace('../', '', $this->e2gDecode($v));
                    $d = $this->_deleteAll('../' . $v);

                    if (empty($d['e'])) {
                        $res['dfp'][0] += $d['d'];
                        $res['ffp'][0] += $d['f'];
                    } else {
                        $res['dfp'][1]++;
                    }
                }
            } // foreach ($post['dir'] as $k => $v)
            if ($res['dfp'][0] == 0 && $res['ddb'][0] == 0) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dirs_delete_err'];
            } elseif ($res['dfp'][0] == $res['ddb'][0]) {
                if ($res['ddb'][0] === 1)
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['dfp'][0] . ' ' . $this->lng['dir_deleted'] . '.';
                else
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['dfp'][0] . ' ' . $this->lng['dirs_deleted'] . '.';
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ddb'][0] . ' ' . $this->lng['dirs_deleted_fdb'] . '.';
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['dfp'][0] . ' ' . $this->lng['dirs_deleted_fhdd'] . '.';
            }
        } // if (!empty($post['dir']))
        // Delete images
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                // the numeric keys are the member of the database
                if (is_numeric($k)) {
                    $selectFileId = 'SELECT id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $k;
                    $querySelectFileId = mysql_query($selectFileId);
                    if (!$querySelectFileId) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileId;
                        return FALSE;
                    }

                    $fileIds = array();
                    while ($l = mysql_fetch_row($querySelectFileId))
                        $fileIds[] = $l[0];
                    mysql_free_result($querySelectFileId);

                    if (count($fileIds) > 0) {
                        $deleteComments = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                                . 'WHERE file_id IN(' . implode(',', $fileIds) . ')';
                        $queryDeleteComments = mysql_query($deleteComments);
                        if (!$queryDeleteComments) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteComments;
                            return FALSE;
                        }
                    }
                    $deleteFile = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $k;
                    $queryDeleteFile = mysql_query($deleteFile);
                    if (!$queryDeleteFile) {
                        $res['fdb'][1]++;
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFile;
                        return FALSE;
                    } else {
                        $res['fdb'][0]++;
                    }
                }
                if (!empty($v)) {
                    $v = str_replace('../', '', $this->e2gDecode($v));
                    $vRealPath = realpath('../' . $v);
                    if (@unlink($vRealPath)) {
                        $res['ffp'][0]++;
                    } else {
                        $res['ffp'][1]++;
                    }
                }
            } // foreach ($post['im'] as $k => $v)
            if ($res['ffp'][0] == 0 && $res['fdb'][0] == 0) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['files_delete_err'];
            }
        } // if (!empty($post['im']))
        if (!empty($res['ffp']) || !empty($res['fdb'])) {
            if ($res['ffp'][0] == $res['fdb'][0]) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ffp'][0] . ' ' . $this->lng['files_deleted'] . '.';
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['fdb'][0] . ' ' . $this->lng['files_deleted_fdb'] . '.';
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ffp'][0] . ' ' . $this->lng['files_deleted_fhdd'] . '.';
            }
        }
        return TRUE;
    }

    /**
     * Download the checked list as ZIP file
     * @param   string  $gdir   gallery's path
     * @param   int     $pid    parent's ID
     * @param   mixed   $post   variables from the check list
     * @return  mixed   TRUE | FALSE | report
     */
    private function _downloadChecked($gdir, $pid, $post) {
        $zipContent = '';
        $_zipContent = array();

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['zip_select_none'];
            return FALSE;
        }

        ob_start();
        foreach ($post['dir'] as $k => $v) {
            $_zipContents[] = realpath(MODX_BASE_PATH . $this->e2gDecode($v));
        }
        foreach ($post['im'] as $k => $v) {
            $_zipContents[] = realpath(MODX_BASE_PATH . $this->e2gDecode($v));
        }

        $dirName = MODX_BASE_PATH . $gdir;
        $dirUrl = MODX_BASE_URL . $gdir;
        $zipName = $dirName . $this->getDirInfo($pid, 'cat_name') . '.zip';
        $zipName = $this->e2gDecode($zipName);

        // delete existing zip file if there is any.
        @unlink($zipName);

        foreach ($_zipContents as $_zipContent) {
            //http://www.php.net/manual/en/function.ziparchive-addemptydir.php#91221
            $zip = new ZipArchive();

            $zip->open($zipName, ZipArchive::CREATE);

            if (is_dir($_zipContent)) {
                $_zipContent = realpath($_zipContent);
                if (substr($_zipContent, -1) != DIRECTORY_SEPARATOR) {
                    $_zipContent.= DIRECTORY_SEPARATOR;
                }

                $dirStack = array($_zipContent);
                //Find the index where the last dir starts
                $cutFrom = strrpos(substr($_zipContent, 0, -1), DIRECTORY_SEPARATOR) + 1;

                while (!empty($dirStack)) {
                    $currentDir = array_pop($dirStack);
                    $filesToAdd = array();

                    $dir = dir($currentDir);
                    while (FALSE !== ($node = $dir->read())) {
                        if (($node == '..') || ($node == '.')) {
                            continue;
                        }
                        if (is_dir($currentDir . $node)) {
                            array_push($dirStack, $currentDir . $node . DIRECTORY_SEPARATOR);
                        }
                        if ($this->validFile($currentDir . $node)) {
                            $filesToAdd[] = $node;
                        }
                    }

                    $localDir = substr($currentDir, $cutFrom);
                    $zip->addEmptyDir($localDir);

                    foreach ($filesToAdd as $file) {
                        $zipAddFile = $zip->addFile($currentDir . $file, $localDir . $file);
                        if (!$zipAddFile) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['zip_create_err'] . '<br />' . $currentDir . $file;
                            continue;
                        }
                    } // foreach ($filesToAdd as $file)
                } // while (!empty($dirStack))
            } // if (is_dir($_zipContent))
            elseif ($this->validFile($_zipContent)) {
                $_zipContent = realpath($_zipContent);
                $basename = end(@explode(DIRECTORY_SEPARATOR, $_zipContent));
                $zip->addFile($_zipContent, $basename);
            }
            $zip->close();
            $zipbasename = str_replace(' ', '', (end(@explode('/', $zipName))));
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $zipbasename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zipName));
        ob_clean();
        flush();
        readfile($zipName);
        @unlink($zipName);
        clearstatcache();
        exit();
    }

    /**
     * Move the checked list to the new destination
     * @param   mixed   $post   variables from the check list
     * @return  mixed   FALSE | report
     */
    private function _moveChecked($post) {
        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['select_dirfile_err'];
            return FALSE;
        }
        if (trim($post['newparent']) == '' || empty($post['newparent'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['select_newdir_err'];
            return FALSE;
        }

        $out = '';
        // COUNTER
        $res = array(
            'fdb' => array(0, 0),
            'ffp' => array(0, 0),
            'ddb' => array(0, 0),
            'dfp' => array(0, 0),
        );

        $newDir = $this->e2gModCfg['dir'] . $this->getPath($post['newparent']);
        $newRealPathParent = realpath('../' . $newDir);

        if (empty($newRealPathParent) || !$this->validFolder($newRealPathParent)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['invalid_folder'] . ' : ' . $newDir;
            return FALSE;
        }

        // MOVING DIRS
        if (!empty($post['dir'])) {
            require_once E2G_MODULE_PATH . 'includes/models/TTree.class.php';
            $tree = new TTree();
            $tree->table = $this->modx->db->config['table_prefix'] . 'easy2_dirs';
            foreach ($post['dir'] as $k => $v) {

                //************* FILE SYSTEM UPDATE *************//
                if (!empty($v)) {
                    $vRealPath = realpath('../' . $v);

                    if (empty($vRealPath) || !$this->validFolder($vRealPath)) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['invalid_folder'] . ' : ' . $v;
                        return FALSE;
                    }

                    $vBaseName = $this->basenameSafe($vRealPath);

                    $oldPath = array();
                    $newPath = array();

                    $oldPath['origin'] = str_replace('../', '', $v);
                    $oldPath['basename'] = $vBaseName;
                    $oldPath['decoded'] = $this->e2gDecode($vRealPath);
                    $this->changeModOwnGrp('dir', $oldPath['decoded']);

                    $newPath['origin'] = $newDir . $vBaseName;
                    $newPath['basename'] = $vBaseName;
                    $newPath['decoded'] = $newRealPathParent . DIRECTORY_SEPARATOR . $vBaseName;

                    // initiate the variables inside _moveAll functions.
                    $moveDir = $this->_moveAll($oldPath['decoded'], $newPath['decoded']);
                    //************* DATABASE UPDATE *************//
                    if (is_numeric($k)) {
                        $ids = $tree->replace((int) $k, (int) $post['newparent']);
                        // goldsky -- the same result with this:
                        // $ids = $tree->update((int) $k, $this->basenameSafe($v), (int) $post['newparent']);
                        if (!$ids) {
                            if (!empty($moveDir['e'])) {
                                $_SESSION['easy2err'] = $moveDir['e'];
                                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_move_err'] . ' : ' . $oldPath['origin'] . ' => ' . $newPath['origin'];
                            }
                            if (!empty($tree->error))
                                $_SESSION['easy2err'][] = __LINE__ . ' : Error: ' . $tree->error;

                            continue;
                        }
                        if ($e2gDebug == '1')
                            if (!empty($tree->reports)) {
                                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . __LINE__ . ' : ' . __METHOD__;
                                foreach ($tree->reports as $tree_report)
                                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . __LINE__ . ' : ' . $tree_report;
                            }
                        if (count($ids) > 0) {
                            $res['ddb'][0] += count($ids);
                        } else {
                            $res['ddb'][1]++;
                        }
                    } // if (is_numeric($k))
                    //************* CONTINUE FILE SYSTEM UPDATE *************//
                    if (!$moveDir) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_move_err'] . ' "' . $newPath['origin'] . "'";
                    } else {
                        if ($e2gDebug == '1')
                            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . __LINE__ . ' : ' . $this->lng['dir_move_suc'] . '
                                    from: <span style="color:blue;">' . $oldPath['origin'] . '</span>
                                    to: <span style="color:blue;">' . $newPath['origin'] . '</span>';

                        if (empty($moveDir['e'])) {
                            $res['dfp'][0] += $moveDir['d'];
                            $res['ffp'][0] += $moveDir['f'];
                        } else {
                            $res['dfp'][1]++;
                        }

                        if ($e2gDebug == '1') {
                            // ****************** list names ****************** //
                            if (!empty($moveDir['dir']) || !empty($moveDir['file'])) {
                                for ($i = 0; $i < count($moveDir['dir']); $i++) {
                                    $_SESSION['easy2suc'][] = __LINE__ . ' : dir: ' . $moveDir['dir'][$i];
                                }
                                for ($i = 0; $i < count($moveDir['file']); $i++) {
                                    $_SESSION['easy2suc'][] = __LINE__ . ' : file: ' . $moveDir['file'][$i];
                                }
                            }
                        }
                        $this->changeModOwnGrp('dir', $newPath['decoded']);
                    }
                    //************** END OF FILE SYSTEM UPDATE **************//
                    $oldPath = $newPath = array();
                    unset($oldPath, $newPath);
                } // if (!empty($v))
            } // foreach ($post['dir'] as $k => $v)

            if ($res['dfp'][0] == 0 && $res['ddb'][0] == 0) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dirs_move_err'];
            } elseif ($res['dfp'][0] == $res['ddb'][0]) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ffp'][0] . ' ' . $this->lng['files_moved'] . '.';
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['dfp'][0] . ' ' . $this->lng['dirs_moved'] . '.';
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ddb'][0] . ' ' . $this->lng['dirs_moved_fdb'] . '.';
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['dfp'][0] . ' ' . $this->lng['dirs_moved_fhdd'] . '.';
            }
        } // if (!empty($post['dir']))
        // MOVING IMAGES
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                // move the file
                if (!empty($v)) {
                    $vRealPath = realpath('../' . $v);

                    if (empty($vRealPath) || !$this->validFile($vRealPath)) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['invalid_file'] . ' : ' . $v;
                        return FALSE;
                    }

                    $vBaseName = $this->basenameSafe($vRealPath);

                    $oldFile = array();
                    $oldFile = array();

                    $oldFile['origin'] = str_replace('../', '', $v);
                    $oldFile['basename'] = $vBaseName;
                    $oldFile['decoded'] = $this->e2gDecode($vRealPath);

                    $this->changeModOwnGrp('file', $oldFile['decoded']);

                    $newFile['origin'] = $newDir . $vBaseName;
                    $newFile['basename'] = $vBaseName;
                    $newFile['decoded'] = $newRealPathParent . DIRECTORY_SEPARATOR . $vBaseName;

                    if (is_file($newFile['decoded'])) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_move_err']
                                . ' <span style="color:red;">' . $this->basenameSafe($v) . '</span>, ' . $this->lng['file_exists'] . '.';
                        continue;
                    } else {
                        $moveFile = @rename($oldFile['decoded'], $newFile['decoded']);
                        if ($moveFile) {
                            $res['file'][] = $newFile['decoded'];

                            // update the database
                            if (is_numeric($k)) {
                                $files = array();
                                $filesRes = mysql_query(
                                        'SELECT id, dir_id '
                                        . 'FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                                        . 'WHERE id=' . (int) $k
                                );
                                while ($l = mysql_fetch_assoc($filesRes)) {
                                    $files[$l['id']]['dir_id'] = $l['dir_id'];
                                }
                                mysql_free_result($filesRes);

                                // reject moving to the same new parent
                                if ($post['newparent'] == $files[$k]['dir_id']) {
                                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_to_same_dir_err'];
                                    continue;
                                }

                                // reject overwrite
                                $filesCheckSelect = 'SELECT A.id ai, B.filename bf '
                                        . 'FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files A, '
                                        . $this->modx->db->config['table_prefix'] . 'easy2_files B '
                                        . 'WHERE A.filename=B.filename '
                                        . 'AND A.id=' . (int) $k . ' '
                                        . 'AND B.dir_id=' . (int) $post['newparent']
                                ;
                                $filesCheckQuery = mysql_query($filesCheckSelect);
                                while ($f = mysql_fetch_assoc($filesCheckQuery)) {
                                    $filesCheck[$f['ai']]['filename'] = $f['bf'];
                                }
                                mysql_free_result($filesCheckQuery);
                                if (isset($filesCheck[$k]['filename'])) {
                                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_move_err']
                                            . ' <span style="color:red;">' . $this->basenameSafe($v) . '</span>, ' . $this->lng['file_exists'] . '.';
                                    continue;
                                }

                                $updateFile = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                                        . ' SET dir_id=' . $post['newparent'] . ' '
                                        . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                                        . ' WHERE id=' . (int) $k;

                                if (mysql_query($updateFile)) {
                                    $res['fdb'][0]++;
                                } else {
                                    $res['fdb'][1]++;
                                }
                            }
                            $res['ffp'][0]++;

                            $this->changeModOwnGrp('file', $newFile['decoded']);
                        } else {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_move_err'];
                            $_SESSION['easy2err'][] = __LINE__ . ' : fr : ' . $oldFile['origin'];
                            $_SESSION['easy2err'][] = __LINE__ . ' : to : ' . $newFile['decoded'];
                            $res['ffp'][1]++;
                        }
                    }

                    $oldFile = $newFile = array();
                    unset($oldFile, $newFile);
                }
            }

            if ($res['ffp'][0] == 0 && $res['fdb'][0] == 0) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['files_move_err'];
            } elseif ($res['ffp'][0] == $res['fdb'][0]) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ffp'][0] . ' ' . $this->lng['files_moved'] . '.';
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['fdb'][0] . ' ' . $this->lng['files_moved_fdb'] . '.';
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ffp'][0] . ' ' . $this->lng['files_moved_fhdd'] . '.';
            }

            // ****************** list names ****************** //
            if (!empty($res['file'])) {
                for ($i = 0; $i < count($res['file']); $i++) {
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['file'][$i];
                }
            }
        } // if (!empty($post['im']))
    }

    /**
     * Delete directory by click action
     * @param   string  $get   variables from $_GET parameter
     * @return  mixed   FALSE | report
     */
    private function _deleteDir($get) {
        if (empty($get['dir_id']) && empty($get['dir_path'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dpath_err'];
            return FALSE;
        }

        // the numeric keys are the member of the database
        if (is_numeric($get['dir_id'])) {
            require_once E2G_MODULE_PATH . 'includes/models/TTree.class.php';
            $tree = new TTree();
            $tree->table = $this->modx->db->config['table_prefix'] . 'easy2_dirs';
            $ids = $tree->delete((int) $get['dir_id']);

            if (empty($ids)) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_id'] . ' : ' . $get['dir_id'] . '<br />';
            }

            $fileIds = array();
            $res = mysql_query(
                    'SELECT id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE dir_id IN(' . @implode(',', $ids) . ')'
            );
            while ($l = mysql_fetch_row($res)) {
                $fileIds[] = $l[0];
            }
            mysql_free_result($res);

            if (count($fileIds) > 0) {
                mysql_query(
                        'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                        . 'WHERE file_id IN(' . @implode(',', $fileIds) . ')');
            }
            mysql_query('DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE dir_id IN(' . @implode(',', $ids) . ')');
        }

        if (!empty($get['dir_path'])) {
            $dirPath = str_replace('../', '', $this->e2gDecode($get['dir_path']));
            $res = $this->_deleteAll('../' . $dirPath);
        }

        if ((!empty($ids) && $ids !== FALSE) && count($res['e']) === 0) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['d'] . ' ' . ($res['d'] == 1 ? $this->lng['dir_deleted'] : $this->lng['dirs_deleted']);
        } elseif (count($ids) > 0) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['d'] . ' ' . ($res['d'] == 1 ? $this->lng['dir_delete_fdb'] : $this->lng['dirs_delete_fdb']);
        } elseif (count($res['e']) === 0 && $res['d'] > 0) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['d'] . ' ' . ($res['d'] == 1 ? $this->lng['dir_delete_fhdd'] : $this->lng['dirs_delete_fhdd']);
        } else {
            if (!empty($res['e']))
                $_SESSION['easy2err'] = $res['e'];
            if (!empty($tree->error))
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $tree->error;

            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_delete_err'];
        }

        // invoke the plugin
        $this->plugin('OnE2GFolderDelete', array(
            'dir_ids' => $ids
            , 'file_ids' => $fileIds
        ));

        return TRUE;
    }

    /**
     * Delete file by click action
     * @param   string  $get   variables from $_GET parameter
     * @return  mixed   FALSE | report
     */
    private function _deleteFile($get) {
        if (empty($get['file_id']) && empty($get['file_path'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['fpath_err'];
            return FALSE;
        }

        if (!empty($get['file_id']) && !empty($get['file_path'])) {
            $fileName = $this->getFileInfo($get['file_id'], 'filename');
            $dirId = $this->getFileInfo($get['file_id'], 'dir_id');
            $dirPath = $this->getPath($dirId);
            if ($this->e2g['dir'] . $dirPath . $fileName != $get['file_path']) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['fpath_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->e2g['dir'] . $dirPath . '/' . $fileName . ' != ' . $get['file_path'];
                return FALSE;
            }
        }

        $res = array('db' => 0, 'fs' => 0, 'dber' => 0, 'fser' => 0);
        // the numeric key is the member of the database
        if (is_numeric($get['file_id'])) {
            $fileName = $this->getFileInfo($get['file_id'], 'filename');
            $deleteFile = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . $get['file_id'];
            $queryDeleteFile = mysql_query($deleteFile);
            if (!$queryDeleteFile) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFile;
                $res['dber']++;
            } else {
                mysql_query('DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments WHERE file_id=' . $get['file_id']);
                $res['db']++;
            }
        }
        // for non-database member
        if (!empty($get['file_path'])) {
            $baseName = $this->basenameSafe($get['file_path']);
            $filePath = str_replace('../', '', $this->e2gDecode($get['file_path']));
            $fileRealPath = realpath('../' . $filePath);
            if (empty($fileRealPath)) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . $this->lng['file_delete_err'] . ' : ' . $filePath;
            } else {
                $deletePhysical = @unlink($fileRealPath);
                if (!$deletePhysical) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . $this->lng['file_delete_err'] . ' : ' . $filePath;
                    $res['fser']++;
                } else {
                    $res['fs']++;
                }
            }
        }

        if ($res['dber'] > 0 && $res['fser'] > 0) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['file_delete_err'] . ' : ' . $filePath;
        } elseif ($res['db'] > 0 && $res['fs'] === 0) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['file_delete_fdb'] . ' : ' . $fileName;
        } elseif ($res['db'] === 0 && $res['fs'] > 0) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['file_delete_fhdd'] . ' : ' . $baseName;
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['file_delete'] . ' : ' . $fileName;
        }

        // invoke the plugin
        $this->plugin('OnE2GFileDelete', array('fid' => $get['file_id']));

        return TRUE;
    }

    /**
     * To delete all of the thumbnail folder's content
     * @param   string  $dir   path
     * @param   string  $this->lng   language string
     * @return  Result report
     */
    private function _cleanCache() {
        $res = $this->_deleteAll('../' . $this->e2g['dir'] . '_thumbnails/');
        if (empty($res['e'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['cache_clean'] . ', ' . $res['f'] . ' ' . $this->lng['files_deleted'] . ', ' . $res['d'] . ' ' . $this->lng['dirs_deleted'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['cache_clean_err'] . ', ' . $res['f'] . ' ' . $this->lng['files_deleted'] . ', ' . $res['d'] . ' ' . $this->lng['dirs_deleted'];
            $_SESSION['easy2err'] = array_merge($_SESSION['easy2err'], $res['e']);
        }
        return $res;
    }

    /**
     * Save configs/settings into database or create the default file.
     * @param   array   $entries        Configuration values
     * @param   bool    $buildDefault   Build default config file
     * @return  NULL
     */
    public function saveE2gSettings($entries, $buildDefault = FALSE) {
        // overriding empty values
        $entries['maxh'] = !empty($entries['maxh']) ? $entries['maxh'] : '0';
        $entries['maxw'] = !empty($entries['maxw']) ? $entries['maxw'] : '0';
        $entries['w'] = !empty($entries['w']) ? $entries['w'] : '140';
        $entries['h'] = !empty($entries['h']) ? $entries['h'] : '140';

        if ($buildDefault) {
            if (!function_exists('fopen')
                    || !function_exists('fwrite')
                    || !function_exists('fclose')
            ) {
                return FALSE;
            }
            // CHECK/CREATE DIRS
            $entries['dir'] = preg_replace('/^\/?(.+)$/', '\\1', $entries['dir']);
            $dirs = explode('/', substr($entries['dir'], 0, -1));
            $npath = '..';
            foreach ($dirs as $dir) {
                $npath .= '/' . $dir;
                if ($this->validFolder($npath) || empty($dir))
                    continue;

                if (mkdir($npath)) {
                    $this->changeModOwnGrp('dir', $npath);
                } else {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_create_err'] . ' "' . $npath . "'";
                }
            }
            ksort($entries);

            $c = "<?php\r\n\$e2gDefault = array (\r\n";
            foreach ($entries as $k => $v) {
                $c .= "        '$k' => " . (is_numeric($v) ? $v : "'" . addslashes($v) . "'") . ",\r\n";
            }
            $c .= ");\r\n";
            $f = fopen(E2G_MODULE_PATH . 'includes/configs/default.config.easy2gallery.php', 'w+');
            fwrite($f, $c);
            fclose($f);

            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['config_update_suc'];
            return TRUE;
        } else {
            ksort($entries);
            $deleteConfigs = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_configs ';
            $queryDeleteConfigs = mysql_query($deleteConfigs);
            if (!$queryDeleteConfigs) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteConfigs;
                return FALSE;
            }
            // else
            foreach ($entries as $k => $v) {
                $insertConfigs = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_configs '
                        . 'SET `cfg_key`=\'' . $k . '\', `cfg_val`=\'' . mysql_real_escape_string($v) . '\'';
                $queryInsertConfigs = mysql_query($insertConfigs);
                if (!$queryInsertConfigs) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertConfigs;
                    return FALSE;
                }
            }

            // delete the config file, because this will always be checked as an upgrade option
            $oldConfigFile = realpath(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php');
            if (!empty($oldConfigFile) && file_exists($oldConfigFile)) {
                $unlinkConfigFile = @unlink($oldConfigFile);
                if (!$unlinkConfigFile) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['config_file_del_err'];
                } else {
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['config_file_del_suc'];
                }
            }

            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['config_update_suc'];
            return TRUE;
        }
    }

    /**
     * Save language file
     * @param   mixed   $post   Variables from the edit forms
     * @return  mixed   TRUE | report
     */
    private function _saveLang($post) {
        ksort($post);

        $lang = $post['lang'];
        $langFile = $post['langfile'];
        // these are only needed for passing the file's key/identification
        // not the translation itself.
        unset($post['lang'], $post['langfile']);

        $c = "<?php\r\n\$e2g_lang['" . $lang . "'] = array (\r\n";
        $countPost = count($post);
        $i = 0;
        foreach ($post as $k => $v) {
            $i++;
            if ($v == '') {
                unset($k);
                continue;
            }
            $c .= "    '$k' => '" . trim(htmlspecialchars($v, ENT_QUOTES));
            if ($i == $countPost)
                $c .= "'\r\n";
            else
                $c .= "',\r\n";
        }
        unset($i);
        $c .= ");\r\n";

        $f = fopen(E2G_MODULE_PATH . 'includes/langs/' . $langFile, 'w');
        fwrite($f, $c);
        fclose($f);

        // keep this in English, because the content itself might not exist in the current lang file.
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . 'Language file is updated.';
        return TRUE;
    }

    /**
     * Ignore IP by clicking a comment's icon
     * @param   mixed   $get    Variables from the hyperlink
     * @return  mixed   TRUE | report
     */
    private function _ignoreIp($get) {
        $insertIgnoredIp = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_ignoredip '
                . '(ign_date, ign_ip_address, ign_username, ign_email) '
                . 'VALUES(NOW(),\'' . $get['ip'] . '\',\'' . $get['u'] . '\',\'' . $get['e'] . '\')';
        $queryInsertIgnoredIp = mysql_query($insertIgnoredIp);
        if (!$queryInsertIgnoredIp) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertIgnoredIp;
            return FALSE;
        }
        $updateCommentStatus = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET STATUS=\'0\' WHERE ip_address=\'' . $get['ip'] . '\'';
        $queryUpdateCommentStatus = mysql_query($updateCommentStatus);
        if (!$queryUpdateCommentStatus) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateCommentStatus;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['ip_ignored_suc'];
        return TRUE;
    }

    /**
     * Ungnore IP by clicking a comment's icon
     * @param   mixed   $get    Variables from the hyperlink
     * @return  mixed   TRUE | report
     */
    private function _unignoreIp($get) {
        $deleteIgnoredIp = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_ignoredip '
                . 'WHERE ign_ip_address =\'' . $get['ip'] . '\'';
        $queryDeleteIgnoredIp = mysql_query($deleteIgnoredIp);
        if (!$queryDeleteIgnoredIp) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteIgnoredIp;
            return FALSE;
        }
        $updateCommentStatus = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET STATUS=\'1\' WHERE ip_address=\'' . $get['ip'] . '\'';
        $queryUpdateCommentStatus = mysql_query($updateCommentStatus);
        if (!$queryUpdateCommentStatus) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateCommentStatus;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['ip_unignored_suc'];
        return TRUE;
    }

    /**
     * Unignore all IPs from the check list
     * @param   mixed     $post   Variables from the check list
     * @return  mixed    TRUE | report
     */
    private function _unignoredAllIps($post) {
        foreach ($_POST['unignored_ip'] as $uignIP) {
            $deleteIgnoredIp = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_ignoredip '
                    . 'WHERE ign_ip_address =\'' . $uignIP . '\'';
            $queryDeleteIgnoredIp = mysql_query($deleteIgnoredIp);
            if (!$queryDeleteIgnoredIp) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteIgnoredIp;
                return FALSE;
            }
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['ip_unignored_suc'] . ' ' . $uignIP;
        }

        return TRUE;
    }

    /**
     * Add tags to the checked dirs/files
     * @param   mixed   $post   Variables from the check list
     * @return  mixed   TRUE | report
     */
    private function _tagAddChecked($post) {
        if (empty($post['tag_input'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['tag_err_novalue'];
            return FALSE;
        }

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['select_dirfile_err'];
            return FALSE;
        }

        // store the multiple tag input as an array
        $xpldTagInputs = explode(',', $post['tag_input']);
        for ($c = 0; $c < count($xpldTagInputs); $c++) {
            $xpldTagInputs[$c] = $this->_escapeString($xpldTagInputs[$c]);
        }

        // Folders
        if (!empty($post['dir'])) {
            foreach ($post['dir'] as $k => $v) {
                if (!empty($v)) {
                    // check the existing value first
                    $selectDirTags = 'SELECT cat_tag FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                            . 'WHERE cat_id=' . $k;
                    $querySelectDirTags = mysql_query($selectDirTags);

                    if (!$querySelectDirTags) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirTags;
                        return FALSE;
                    }
                    while ($l = mysql_fetch_assoc($querySelectDirTags)) {
                        $dirTags = $l['cat_tag'];
                    }
                    mysql_free_result($querySelectDirTags);

                    $xpldDirTags = array();
                    $xpldDirTags = explode(',', $dirTags);

                    for ($c = 0; $c < count($xpldDirTags); $c++) {
                        $xpldDirTags[$c] = trim($xpldDirTags[$c]);
                    }

                    $newTags = $intTags = array();
                    $intTags = array_intersect($xpldDirTags, $xpldTagInputs);
                    $newTags = array_unique(array_merge($xpldDirTags, $xpldTagInputs));

                    // clean ups
                    foreach ($newTags as $tagKey => $tagVal) {
                        if (empty($newTags[$tagKey]))
                            unset($newTags[$tagKey]);
                    }
                    sort($newTags, SORT_LOCALE_STRING);

                    if (count($intTags) > 0) {
                        $impldIntTag = @implode(', ', $intTags);
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['tag_err_exist'] . ' : ' . $impldIntTag . ' (' . $this->basenameSafe($v) . ')';
                    }

                    // store the new value of file's tag
                    if (count($newTags) > 0) {
                        $newTags = implode(', ', $newTags);
                        $updateDirTags = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                                . 'SET cat_tag=\'' . $newTags . '\' '
                                . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                                . ', last_modified=NOW() '
                                . 'WHERE cat_id=' . $k;
                        $queryUpdateDirTags = mysql_query($updateDirTags);
                        if (!$queryUpdateDirTags) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateDirTags;
                            return FALSE;
                        }
                    }
                } // if (!empty($v))
            } // foreach ($post['dir'] as $k => $v)
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['tag_suc_new'];
        } // if (!empty($post['dir']))
        // Files
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                if (!empty($v)) {
                    // check the existing value first
                    $selectFileTags = 'SELECT tag FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE id=' . $k;
                    $querySelectFileTags = mysql_query($selectFileTags);
                    if (!$querySelectFileTags) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileTags;
                        return FALSE;
                    }
                    while ($l = mysql_fetch_assoc($querySelectFileTags)) {
                        $fileTags = $l['tag'];
                    }
                    mysql_free_result($querySelectFileTags);

                    $xpldFileTags = array();
                    $xpldFileTags = explode(',', $fileTags);

                    for ($c = 0; $c < count($xpldFileTags); $c++) {
                        $xpldFileTags[$c] = $this->_escapeString($xpldFileTags[$c]);
                    }

                    $intTags = array_intersect($xpldFileTags, $xpldTagInputs);
                    $newTags = array_unique(array_merge($xpldFileTags, $xpldTagInputs));

                    // clean ups
                    foreach ($newTags as $tagKey => $tagVal) {
                        if (empty($newTags[$tagKey]))
                            unset($newTags[$tagKey]);
                    }
                    sort($newTags, SORT_LOCALE_STRING);

                    if (count($intTags) > 0) {
                        $intTags = implode(', ', $intTags);
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['tag_err_exist'] . ' : ' . $intTags . ' (' . $this->basenameSafe($v) . ')';
                    }

                    // store the new value of file's tag
                    if (count($newTags) > 0) {
                        $newTags = implode(', ', $newTags);
                        $updateFileTags = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                                . 'SET tag=\'' . $newTags . '\' '
                                . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                                . ', last_modified=NOW() '
                                . 'WHERE id=' . $k
                        ;
                        $queryUpdateFileTags = mysql_query($updateFileTags);
                        if (!$queryUpdateFileTags) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFileTags;
                            return FALSE;
                        }
                    }
                } // if (!empty($v))
            } // foreach ($post['im'] as $k => $v)
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['tag_suc_new'];
        } // if (!empty($post['im']))

        return TRUE;
    }

    /**
     * Remove tags to the checked dirs/files
     * @param   mixed   $post   Variables from the check list
     * @return  mixed   TRUE | report
     */
    private function _tagRemoveChecked($post) {
        if (empty($post['tag_input'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['tag_err_novalue'];
            return FALSE;
        }

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['select_dirfile_err'];
            return FALSE;
        }

        // store the multiple tag input as an array
        $xpldTagInputs = explode(',', $post['tag_input']);
        for ($c = 0; $c < count($xpldTagInputs); $c++) {
            $xpldTagInputs[$c] = $this->_escapeString($xpldTagInputs[$c]);
        }

        // Folders
        if (!empty($post['dir'])) {
            foreach ($post['dir'] as $k => $v) {
                if (!empty($v)) {
                    // check the existing value first
                    $selectDirTags = 'SELECT cat_tag FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                            . 'WHERE cat_id=' . $k;
                    $querySelectDirTags = mysql_query($selectDirTags);
                    if (!$querySelectDirTags) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirTags;
                        return FALSE;
                    }
                    while ($l = mysql_fetch_assoc($querySelectDirTags)) {
                        $dirTags = $l['cat_tag'];
                    }
                    mysql_free_result($querySelectDirTags);

                    $xpldDirTags = array();
                    $xpldDirTags = explode(',', $dirTags);

                    for ($c = 0; $c < count($xpldDirTags); $c++) {
                        $xpldDirTags[$c] = trim($xpldDirTags[$c]);
                    }

                    $newTags = $intTags = array();
                    $intTags = array_diff($xpldTagInputs, $xpldDirTags);
                    $newTags = array_unique(array_diff($xpldDirTags, $xpldTagInputs));

                    // clean ups
                    foreach ($newTags as $tagKey => $tagVal) {
                        if (empty($newTags[$tagKey]))
                            unset($newTags[$tagKey]);
                    }
                    sort($newTags, SORT_LOCALE_STRING);

                    if (count($intTags) > 0) {
                        $intTags = @implode(', ', $intTags);
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['tag_err_noexist'] . ' : ' . $intTags . ' (' . $this->basenameSafe($v) . ')';
                    }

                    // store the new value of file's tag
                    $newTags = implode(', ', $newTags);
                    $updateDirTags = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                            . 'SET cat_tag=\'' . $newTags . '\' '
                            . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                            . ', last_modified=NOW() '
                            . 'WHERE cat_id=' . $k
                    ;
                    $queryUpdateDirTags = mysql_query($updateDirTags);
                    if (!$queryUpdateDirTags) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateDirTags;
                        return FALSE;
                    }
                } // if (!empty($v))
            } // foreach ($post['dir'] as $k => $v)
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['tag_suc_remove'];
        } // if (!empty($post['dir']))
        // Files
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                if (!empty($v)) {
                    // check the existing value first
                    $selectFileTags = 'SELECT tag FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE id=' . $k;
                    $querySelectFileTags = mysql_query($selectFileTags);
                    if (!$querySelectFileTags) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileTags;
                        return FALSE;
                    }
                    while ($l = mysql_fetch_assoc($querySelectFileTags)) {
                        $fileTags = $l['tag'];
                    }
                    mysql_free_result($querySelectFileTags);

                    $xpldFileTags = array();
                    $xpldFileTags = explode(',', $fileTags);

                    for ($c = 0; $c < count($xpldFileTags); $c++) {
                        $xpldFileTags[$c] = trim($xpldFileTags[$c]);
                    }

                    $newTags = $intTags = array();
                    $intTags = array_diff($xpldTagInputs, $xpldFileTags);
                    $newTags = array_unique(array_diff($xpldFileTags, $xpldTagInputs));

                    // clean ups
                    foreach ($newTags as $tagKey => $tagVal) {
                        if (empty($newTags[$tagKey]))
                            unset($newTags[$tagKey]);
                    }
                    sort($newTags, SORT_LOCALE_STRING);
                    if (count($intTags) > 0) {
                        $intTags = implode(', ', $intTags);
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['tag_err_noexist'] . ' : ' . $intTags . ' (' . $this->basenameSafe($v) . ')';
                    }

                    // store the new value of file's tag
                    $newTags = implode(', ', $newTags);
                    $updateFileTags = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                            . 'SET tag=\'' . $newTags . '\' '
                            . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                            . ', last_modified=NOW() '
                            . 'WHERE id=' . $k
                    ;
                    $queryUpdateFileTags = mysql_query($updateFileTags);
                    if (!$queryUpdateFileTags) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFileTags;
                        return FALSE;
                    }
                } // if (!empty($v))
            } // foreach ($post['im'] as $k => $v)
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['tag_suc_remove'];
        } // if (!empty($post['im']))

        $this->_cleanCache();
        return TRUE;
    }

    /**
     * Multiple actions for the comment list checkboxes
     * @param   string  $post   values from the checkboxes
     * @return  bool    TRUE | FALSE
     */
    private function _commentListActions($post) {
        if (count($post['comments']) == 0) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['comment_err_noselect'];
            return FALSE;
        }

        $countRes = 0;
        if ($post['listCommentActions'] == 'delete') {
            foreach ($post['comments'] as $comId) {
                $deleteComment = $this->_commentDelete($comId);
                if ($deleteComment !== FALSE) {
                    $countRes++;
                }
            }
        } else {
            foreach ($post['comments'] as $comId) {
                $updateComment = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_comments ';
                switch ($post['listCommentActions']) {
                    case 'approve':
                        $updateComment .= 'SET approved=\'1\' ,status=\'1\' ';
                        break;
                    case 'unapprove':
                        $updateComment .= 'SET approved=\'0\' ,status=\'0\' ';
                        break;
                    case 'hide':
                        $updateComment .= 'SET status=\'0\' ';
                        break;
                    case 'unhide':
                        $updateComment .= 'SET status=\'1\' ';
                        break;
                }
                $updateComment .= 'WHERE id=\'' . $comId . '\'';
                $countRes++;

                $queryUpdateComment = mysql_query($updateComment);
                if (!$queryUpdateComment) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateComment;
                    return FALSE;
                }
            }
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['comment_suc_update'] . ' ' . $countRes . ' ' . $this->lng['comments'];
        return TRUE;
    }

    /**
     * To delete comments
     * @param   int     $id     comment's ID
     * @return  mixed   TRUE | report
     */
    private function _commentDelete($id) {
        $selectFileId = 'SELECT file_id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                . 'WHERE id=' . (int) $id;
        $fileId = mysql_result(mysql_query($selectFileId), 0, 0);
        if (!$fileId) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileId;
            return FALSE;
        }

        $deleteComment = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                . 'WHERE id=' . (int) $id;
        $queryDeleteComment = mysql_query($deleteComment);
        if (!$queryDeleteComment) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteComment;
            return FALSE;
        } else {
            $updateFileComment = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                    . 'SET comments=comments-1 '
                    . ', modified_by=\'' . $this->modx->getLoginUserID() . '\' '
                    . 'WHERE id=' . $fileId;
            $queryUpdateFileComment = mysql_query($updateFileComment);
            if (!$queryUpdateFileComment) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFileComment;
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Approve comment
     * @param   int     $id     comment's ID
     * @return  bool    TRUE | FALSE
     */
    private function _commentApprove($id) {
        $updateComment = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET approved=\'1\' ,status=\'1\' '
                . 'WHERE id=' . $id;
        $queryUpdateComment = mysql_query($updateComment);
        if (!$queryUpdateComment) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateComment;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['comment_suc_approved'];
        return TRUE;
    }

    /**
     * Hide comment
     * @param   int     $id     comment's ID
     * @return  bool    TRUE | FALSE
     */
    private function _commentHide($id) {
        $updateComment = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET status=\'0\' '
                . 'WHERE id=' . $id;
        $queryUpdateComment = mysql_query($updateComment);
        if (!$queryUpdateComment) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateComment;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['comment_suc_hide'];
        return TRUE;
    }

    /**
     * Unhide comment
     * @param   int     $id     comment's ID
     * @return  bool    TRUE | FALSE
     */
    private function _commentUnhide($id) {
        $updateComment = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET status=\'1\' '
                . 'WHERE id=' . $id;
        $query = mysql_query($updateComment);
        $queryUpdateComment = mysql_query($updateComment);
        if (!$queryUpdateComment) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateComment;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['comment_suc_unhide'];
        return TRUE;
    }

    /**
     * Save comment after edited
     * @param   string  $post   values from the input form
     * @return  bool    TRUE | FALSE
     */
    private function _commentSave($post) {
        $comment = (isset($post['comment']) ? $post['comment'] : $post['hiddencomment']);
        $updateComment = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET comment=\'' . $this->_escapeString($comment) . '\' '
                . ', date_edited=NOW() '
                . ', edited_by=\'' . $this->modx->getLoginUserID() . '\' '
                . 'WHERE id=' . $post['comid'];
        $queryUpdateComment = mysql_query($updateComment);
        if (!$queryUpdateComment) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateComment;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['comment_suc_update'];
        return TRUE;
    }

    /**
     * Save a new plugin
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _savePlugin($post) {
        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_name'];
            return FALSE;
        }

        $eventsArray = array();
        $eventsString = '';
        if (!empty($post['events'])) {
            $eventsArray = $post['events'];
            $eventsString = implode(',', $eventsArray);
        }

        $insertPlugin = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_plugins '
                . 'SET name=\'' . $this->_escapeString($post['name']) . '\' '
                . ', description=\'' . $this->_escapeString($post['description']) . '\' '
                . ', events=\'' . $eventsString . '\' '
                . ', indexfile=\'' . urldecode(trim($post['index_file'])) . '\' '
                . ', disabled=\'' . (int) $post['disabled'] . '\' ';
        $queryInsertPlugin = mysql_query($insertPlugin);
        if (!$queryInsertPlugin) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertPlugin;
            return FALSE;
        }

        $pluginId = mysql_insert_id();
        foreach ($eventsArray as $evtId) {
            $insertPluginEvt = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_plugin_events '
                    . 'SET pluginid=\'' . $pluginId . '\' '
                    . ', evtid=\'' . $evtId . '\'';
            $queryInsertPluginEvt = mysql_query($insertPluginEvt);
            if (!$queryInsertPluginEvt) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertPluginEvt;
                return FALSE;
            }
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['plugin_add_suc'];
        return TRUE;
    }

    private function _deletePlugin($get) {
        $delete = mysql_query('DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_plugins '
                . 'WHERE id="' . $get['plugin_id'] . '"'
        );
        if ($delete) {
            mysql_query('DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_plugin_events '
                    . 'WHERE pluginid="' . $get['plugin_id'] . '"'
            );
        }
    }

    /**
     * Update changes onplugin editing
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _updatePlugin($post) {
        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_name'];
            return FALSE;
        } elseif (empty($post['plugin_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_id'];
            return FALSE;
        }

        $eventsArray = array();
        $eventsString = '';
        if (!empty($post['events'])) {
            $eventsArray = $post['events'];
            $eventsString = implode(',', $eventsArray);
        }
        $pluginId = $post['plugin_id'];
        $updatePlugin = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_plugins '
                . 'SET name=\'' . $this->_escapeString($post['name']) . '\' '
                . ', description=\'' . $this->_escapeString($post['description']) . '\' '
                . ', events=\'' . $eventsString . '\' '
                . ', indexfile=\'' . urldecode(trim($post['index_file'])) . '\' '
                . ', disabled=\'' . (int) $post['disabled'] . '\' '
                . 'WHERE id=' . $post['plugin_id'];
        $queryUpdatePlugin = mysql_query($updatePlugin);
        if (!$queryUpdatePlugin) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updatePlugin;
            return FALSE;
        }

        $deletePluginEvents = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_plugin_events '
                . 'WHERE pluginid=\'' . $pluginId . '\'';
        $queryDeletePluginEvents = mysql_query($deletePluginEvents);
        if (!$queryDeletePluginEvents) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deletePluginEvents;
            return FALSE;
        }

        foreach ($eventsArray as $evtId) {
            $insertPluginEvents = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_plugin_events '
                    . 'SET pluginid=\'' . $pluginId . '\' '
                    . ', evtid=\'' . $evtId . '\'';
            $queryInsertPluginEvents = mysql_query($insertPluginEvents);
            if (!$queryInsertPluginEvents) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertPluginEvents;
                return FALSE;
            }
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['plugin_update_suc'];
        return TRUE;
    }

    /**
     * Save a new viewer/javascript library
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _saveViewer($post) {
        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_name'];
            return FALSE;
        } elseif (empty($post['alias'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_alias'];
            return FALSE;
        }

        $insertViewer = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_viewers '
                . 'SET name=\'' . $this->_escapeString($post['name']) . '\''
                . ', alias=\'' . $this->_escapeString($post['alias']) . '\''
                . ', description=\'' . $this->_escapeString($post['description']) . '\''
                . ', disabled=\'' . $this->_escapeString($post['disabled']) . '\''
                . ', headers_css=\'' . $this->_escapeString($post['headers_css']) . '\''
                . ', autoload_css=\'' . $this->_escapeString($post['autoload_css']) . '\''
                . ', headers_js=\'' . $this->_escapeString($post['headers_js']) . '\''
                . ', autoload_js=\'' . $this->_escapeString($post['autoload_js']) . '\''
                . ', headers_html=\'' . $this->_escapeString($post['headers_html']) . '\''
                . ', autoload_html=\'' . $this->_escapeString($post['autoload_html']) . '\''
                . ', glibact=\'' . $this->_escapeString($post['glibact']) . '\''
                . ', clibact=\'' . $this->_escapeString($post['clibact']) . '\''
        ;
        $queryInservtViewer = mysql_query($insertViewer);
        if (!$queryInservtViewer) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertViewer;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['viewer_add_suc'];
        return TRUE;
    }

    /**
     * Update changes on viewer/javascript library editing
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _updateViewer($post) {
        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_name'];
            return FALSE;
        } elseif (empty($post['alias'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_alias'];
            return FALSE;
        } elseif (empty($post['viewer_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_id'];
            return FALSE;
        }

        $updateViewer = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_viewers '
                . 'SET name=\'' . $this->_escapeString($post['name']) . '\''
                . ', alias=\'' . $this->_escapeString($post['alias']) . '\''
                . ', description=\'' . $this->_escapeString($post['description']) . '\''
                . ', disabled=\'' . $this->_escapeString($post['disabled']) . '\''
                . ', headers_css=\'' . $this->_escapeString($post['headers_css']) . '\''
                . ', autoload_css=\'' . $this->_escapeString($post['autoload_css']) . '\''
                . ', headers_js=\'' . $this->_escapeString($post['headers_js']) . '\''
                . ', autoload_js=\'' . $this->_escapeString($post['autoload_js']) . '\''
                . ', headers_html=\'' . $this->_escapeString($post['headers_html']) . '\''
                . ', autoload_html=\'' . $this->_escapeString($post['autoload_html']) . '\''
                . ', glibact=\'' . $this->_escapeString($post['glibact']) . '\''
                . ', clibact=\'' . $this->_escapeString($post['clibact']) . '\''
                . ' WHERE id=' . $post['viewer_id']
        ;
        $updateQuery = mysql_query($updateViewer);
        if (!$updateQuery) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateViewer;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['viewer_update_suc'] . ' : ' . htmlspecialchars($post['name']);
        return TRUE;
    }

    private function _deleteViewer($get) {
        mysql_query('DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_viewers '
                . 'WHERE id="' . $get['viewer_id'] . '"'
        );
    }

    /**
     * Save a new slideshow
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _saveSlideshow($post) {
        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['slideshow_add_err'];
            return FALSE;
        }

        if (is_array($post['name'])) {
            $countPost = count($post['name']);
            for ($i = 0; $i < $countPost; $i++) {
                // skipping the dummy form, the zero key
                if (empty($post['name'][$i]))
                    continue;
                $insertSlideshow = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows '
                        . 'SET name=\'' . $this->_escapeString($post['name'][$i]) . '\' '
                        . ', description=\'' . $this->_escapeString($post['description'][$i]) . '\' '
                        . ', indexfile=\'' . urldecode(trim($post['index_file'][$i])) . '\' '
                ;
                $queryInsertSlideshow = mysql_query($insertSlideshow);
                if (!$queryInsertSlideshow) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertSlideshow;
                    return FALSE;
                }
            }
        } else {
            $insertSlideshow = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows '
                    . 'SET name=\'' . $this->_escapeString($post['name']) . '\' '
                    . ', description=\'' . $this->_escapeString($post['description']) . '\' '
                    . ', indexfile=\'' . urldecode(trim($post['index_file'])) . '\' '
            ;
            $queryInsertSlideshow = mysql_query($insertSlideshow);
            if (!$queryInsertSlideshow) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertSlideshow;
                return FALSE;
            }
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['slideshow_add_suc'];
        return TRUE;
    }

    private function _deleteSlideshow($get) {
        mysql_query('DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows '
                . 'WHERE id="' . $get['ssid'] . '"'
        );
    }

    /**
     * Update changes on slideshow editing
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _updateSlideshow($post) {
        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_name'];
            return FALSE;
        } elseif (empty($post['slideshow_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['err_empty_id'];
            return FALSE;
        }

        $updateSlideshow = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_slideshows '
                . 'SET name=\'' . $this->_escapeString($post['name']) . '\' '
                . ', description=\'' . $this->_escapeString($post['description']) . '\' '
                . ', indexfile=\'' . urldecode(trim($post['index_file'])) . '\' '
                . 'WHERE id=' . $post['slideshow_id']
        ;
        $queryUpdateSlideshow = mysql_query($updateSlideshow);
        if (!$queryUpdateSlideshow) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateSlideshow;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['slideshow_update_suc'];
        return TRUE;
    }

    /**
     * Create folder
     * @param string    $post       values from the input form
     * @param string    $gdir       directory path
     * @param int       $parentId  parent's ID
     * @return bool     TRUE | FALSE
     */
    private function _createDir($post, $gdir, $parentId) {
        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_create_err'] . ' : ' . $this->lng['err_empty_name'];
            return FALSE;
        }

        // converting non-latin names with MODx's stripAlias function
        $dirName = htmlspecialchars($this->modx->stripAlias($post['name']), ENT_QUOTES);

        //check the dir existance
        if ($this->validFolder('../' . $this->e2gDecode($gdir . $dirName))) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_create_err'] . ' : ' . $this->lng['dir_exists'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->e2gDecode($gdir . $dirName);
            return FALSE;
        }

        $mkdir = @mkdir('../' . $this->e2gDecode($gdir . $dirName));

        if (!$mkdir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['dir_create_err'] . ' : ' . $this->lng['err_undefined'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->e2gDecode($gdir . $dirName);
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['dir_created'] . ' : ' . $gdir . $dirName;

        $this->changeModOwnGrp('dir', '../' . $this->e2gDecode($gdir . $dirName));
        $this->createIndexHtml(MODX_BASE_PATH . $this->e2gDecode($gdir . $dirName), $this->lng['indexfile']);

        require_once E2G_MODULE_PATH . 'includes/models/TTree.class.php';
        $tree = new TTree();
        $tree->table = $this->modx->db->config['table_prefix'] . 'easy2_dirs';
        $id = $tree->insert($dirName, $parentId);
        if (!$id) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $tree->error;
            $tree->delete($id);
            return FALSE;
        }

        $updateDir = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'SET '
                . 'cat_alias = \'' . $this->_escapeString($post['alias']) . '\''
                . ', cat_summary = \'' . $this->_escapeString($post['summary']) . '\''
                . ', cat_tag = \'' . $this->_escapeString($post['tag']) . '\''
                . ', cat_description = \'' . $this->_escapeString($post['description']) . '\''
                . ', date_added=NOW() '
                . ', added_by=\'' . $this->modx->getLoginUserID() . '\' '
                . 'WHERE cat_id=' . $id;
        $queryUpdateDir = mysql_query($updateDir);
        if (!$queryUpdateDir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateDir;
            return FALSE;
        }

        // invoke the plugin
        $this->plugin('OnE2GFolderCreateFormSave', array(
            'cat_id' => $id
            , 'cat_alias' => $this->_escapeString($post['alias'])
            , 'cat_summary' => $this->_escapeString($post['summary'])
            , 'cat_tag' => $this->_escapeString($post['tag'])
            , 'cat_description' => $this->_escapeString($post['description'])
            , 'date_added' => time()
            , 'added_by' => $this->modx->getLoginUserID()
        ));

        return TRUE;
    }

    /**
     * Update the database from the directory/folder editing form
     * @param string    $gdir   directory path
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _editDir($post) {
        if (empty($post['cat_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['update_err'];
            return FALSE;
        }

        $gdir = $this->e2gModCfg['dir'] . $this->getPath($post['parent_id']);

        $newDirName = $this->modx->stripAlias($post['new_cat_name']);
        $newDirPath = $this->e2gDecode($gdir . $newDirName);
        $oldDirName = $post['cat_name'];
        $oldDirPath = $this->e2gDecode($gdir . $oldDirName);

        $renameDirConfirm = FALSE;
        // check the CHMOD permission first, EXCLUDE the root gallery
        if ($post['cat_id'] != 1 && $oldDirName != $newDirName) {
            $renameDir = rename('../' . $oldDirPath, '../' . $newDirPath);
            if (!$renameDir) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['update_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $oldDirPath . ' => ' . $newDirPath;
                return FALSE;
            }
            $renameDirConfirm = TRUE;
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['dir_rename_suc'];
            $this->changeModOwnGrp('dir', '../' . $newDirPath);
        }

        $updateDir = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs SET ';

        if ($post['cat_id'] != '1' && $renameDirConfirm === TRUE) {
            $updateDir .= 'cat_name = \'' . $this->_escapeString($newDirName) . '\', '; // trailing comma!
        }

        $updates = array();
        if (!empty($post['alias'])) {
            $updates[] = 'cat_alias = \'' . $this->_escapeString($post['alias']) . '\'';
        }
        if (!empty($post['summary'])) {
            $updates[] = 'cat_summary = \'' . $this->_escapeString($post['summary']) . '\'';
        }
        if (!empty($post['tag'])) {
            $updates[] = 'cat_tag = \'' . $this->_escapeString($post['tag']) . '\'';
        }
        if (!empty($post['description'])) {
            $updates[] = 'cat_description = \'' . $this->_escapeString($post['description']) . '\'';
        }
        $updates[] = 'modified_by=\'' . $this->modx->getLoginUserID() . '\'';
        $updates[] = 'last_modified=NOW()';
        if (!empty($post['cat_redirect_link'])) {
            $updates[] = 'cat_redirect_link = \'' . $this->_escapeString($post['cat_redirect_link']) . '\'';
        }
        if (!empty($post['thumb_id'])) {
            $updates[] = 'cat_thumb_id = \'' . $post['thumb_id'] . '\'';
        }
        $updateDir .= @implode(',', $updates);
        $updateDir .= ' WHERE cat_id=' . $post['cat_id'];

        $queryUpdateDir = mysql_query($updateDir);
        if (!$queryUpdateDir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateDir;
            return FALSE;
        }
        // Adding web group access
        if (!empty($post['webGroups'])) {
            $this->_saveWebGroupsAccess($post['webGroups'], 'dir', $post['cat_id']);
        }

        // invoke the plugin
        $this->plugin('OnE2GFolderEditFormSave', array(
            'cat_id' => $post['cat_id']
            , 'cat_name' => ($renameDirConfirm === TRUE ? $this->_escapeString($newDirName) : $oldDirName )
            , 'cat_alias' => $this->_escapeString($post['alias'])
            , 'cat_summary' => $this->_escapeString($post['summary'])
            , 'cat_tag' => $this->_escapeString($post['tag'])
            , 'cat_description' => $this->_escapeString($post['description'])
            , 'modified_by=' => $this->modx->getLoginUserID()
            , 'last_modified' => time()
        ));

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['dir_updated_suc'];
        return TRUE;
    }

    /**
     * Update the database from the file editing form
     * @param int       $gdir   parent directory
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _editFile($gdir, $post) {
        if (empty($post['file_id']) || $gdir == '') {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['update_err'];
            return FALSE;
        }

        $newFilename = $this->modx->stripAlias($post['newfilename']);
        $filename = $post['filename'];
        $ext = $post['ext'];
        $oldFilePath = $this->e2gDecode($gdir . $filename . $ext);
        $newFilePath = $this->e2gDecode($gdir . $newFilename . $ext);

        if ($newFilename != $filename) {
            // check the CHMOD permission first
            $this->changeModOwnGrp('file', '../' . $oldFilePath);
            $renameFile = rename('../' . $oldFilePath, '../' . $newFilePath);
            $this->changeModOwnGrp('file', '../' . $newFilePath);

            if (!$renameFile) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['update_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $oldFilePath . ' => ' . $newFilePath;
                return FALSE;
            }
        }

        $updateFile = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_files SET ';
        if ($newFilename != $filename) {
            $updateFile .= 'filename = \'' . $this->_escapeString($newFilename) . $ext . '\', '; // trailing comma!
        }
        $updates = array();
        if (!empty($post['alias'])) {
            $updates[] = 'alias = \'' . $this->_escapeString($post['alias']) . '\'';
        }
        if (!empty($post['summary'])) {
            $updates[] = 'summary = \'' . $this->_escapeString($post['summary']) . '\'';
        }
        if (!empty($post['tag'])) {
            $updates[] = 'tag = \'' . $this->_escapeString($post['tag']) . '\'';
        }
        if (!empty($post['description'])) {
            $updates[] = 'description = \'' . $this->_escapeString($post['description']) . '\'';
        }
        $updates[] = 'modified_by=\'' . $this->modx->getLoginUserID() . '\'';
        $updates[] = 'last_modified=NOW()';
        if (!empty($post['redirect_link'])) {
            $updates[] = 'redirect_link = \'' . $this->_escapeString($post['redirect_link']) . '\'';
        }
        $updateFile .= @implode(',', $updates);
        $updateFile .= 'WHERE id=' . $post['file_id'];

        $queryUpdateFile = mysql_query($updateFile);
        if (!$queryUpdateFile) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['update_err'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $updateFile;
            return FALSE;
        }

        // Adding webGroup access
        if (!empty($post['webGroups'])) {
            $this->_saveWebGroupsAccess($post['webGroups'], 'file', $post['file_id']);
        }

        // invoke the plugin
        $this->plugin('OnE2GFileEditFormSave', array(
            'fid' => $this->sanitizedGets['file_id']
            , 'filename' => ($newFilename != $filename ? $newFilename : $filename)
            , 'alias' => $this->_escapeString($post['alias'])
            , 'summary' => $this->_escapeString($post['summary'])
            , 'tag' => $this->_escapeString($post['tag'])
            , 'description' => $this->_escapeString($post['description'])
            , 'modified_by' => $this->modx->getLoginUserID()
            , 'last_modified' => time()
        ));

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['updated'];
        return TRUE;
    }

    /**
     * Get the plugin's number from the events list.<br />
     * This is used to simplified any number changes on the plugin's form by the developer
     * @param   string  $e2gEvtName Plugin Event Name
     * @return  int     The plugin's number
     */
    private function _getEventNum($e2gEvtName) {
        // include the event's names
        $eventConfigFile = realpath(E2G_MODULE_PATH . 'includes/configs/config.events.easy2gallery.php');
        if (!empty($eventConfigFile) && file_exists($eventConfigFile)) {
            include $eventConfigFile;
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['config_file_err_missing'];
            return FALSE;
        }

        foreach ($e2gEvents as $k => $v) {
            if ($e2gEvents[$k] == $e2gEvtName)
                $e2gEvtNum = $k;
        }
        return $e2gEvtNum;
    }

    /**
     * Synchronizing the user group between E2G and MODx.
     * @return bool TRUE | FALSE
     */
    private function _synchroUserGroups() {
        /**
         * Synchronizing the Manager Users
         */
        $e2gMgrGroupsArray = $this->modx->db->makeArray($this->modx->db->query(
                        'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_users_mgr '));
        $e2gMgrGroupIds = array();
        $countE2gMgrGroups = count($e2gMgrGroupsArray);
        for ($i = 0; $i < $countE2gMgrGroups; $i++) {
            $e2gMgrGroupIds[$e2gMgrGroupsArray[$i]['membergroup_id']] = $e2gMgrGroupsArray[$i]['membergroup_id'];
        }

        $modxMemberGroups = $this->modx->db->makeArray($this->modx->db->query(
                        'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'membergroup_names '));
        $countModxMemberGroups = count($modxMemberGroups);

        // adding non-exist modx groups into e2g groups
        for ($i = 0; $i < $countModxMemberGroups; $i++) {
            $modxMemberGroupIds[$modxMemberGroups[$i]['id']] = $modxMemberGroups[$i]['id'];

            if (isset($e2gMgrGroupIds[$modxMemberGroups[$i]['id']]))
                continue;

            $insertMgrUser = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_users_mgr '
                    . 'SET membergroup_id=\'' . $modxMemberGroups[$i]['id'] . '\'';
            $queryInsertMgrUser = mysql_query($insertMgrUser);
            if (!$queryInsertMgrUser) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['user_mgr_synchro_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
                return FALSE;
            }
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['user_mgr_synchro_suc'] . ' : ' . $modxMemberGroups[$i]['name'];
        }

        // deleting e2g groups of non-exist modx groups
        if (!empty($e2gMgrGroupIds)) {
            foreach ($e2gMgrGroupIds as $id) {
                if (isset($modxMemberGroupIds[$id]))
                    continue;

                $deleteMgrUser = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_users_mgr '
                        . 'WHERE membergroup_id=\'' . $id . '\'';
                $queryDeleteMgrUser = mysql_query($deleteMgrUser);
                if (!$queryDeleteMgrUser) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['user_mgr_synchro_err'];
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * Load the E2G's manager access for its pages
     * @return  bool    TRUE | FALSE
     */
    private function _loadE2gMgrSessions() {
        // loading the hyperlinks ($this->e2gModCfg['e2gPages'])
        $pageConfigFile = realpath(E2G_MODULE_PATH . 'includes/configs/config.pages.easy2gallery.php');
        if (empty($pageConfigFile) || !file_exists($pageConfigFile)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['config_file_err_missing'];
            return FALSE;
        }
        require $pageConfigFile;

        // loading the MODx's attributes
        $getUserInfo = $this->modx->getUserInfo($_SESSION['mgrInternalKey']);
        $userId = $getUserInfo['id'];
        $userPermissions = $this->modx->db->getValue(
                'SELECT e.permissions FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_users_mgr e '
                . 'LEFT JOIN ' . $this->modx->db->config['table_prefix'] . 'membergroup_names m '
                . 'ON e.membergroup_id = m.id '
                . 'LEFT JOIN ' . $this->modx->db->config['table_prefix'] . 'member_groups g '
                . 'ON g.user_group=m.id '
                . 'WHERE g.member=\'' . $userId . '\''
        );

        $_SESSION['e2gMgr']['permissions'] = $userPermissions;

        $userRole = $this->modx->db->getValue(
                'SELECT role FROM ' . $this->modx->db->config['table_prefix'] . 'user_attributes '
                . 'WHERE internalKey=\'' . $userId . '\''
        );

        $_SESSION['e2gMgr']['role'] = $userRole;
        return TRUE;
    }

    /**
     * Save the web groups access from the dir/file edit page
     * @param   array   $webGroupIds    web groups in an array
     * @param   string  $type           dir/file type
     * @param   int     $id             dir's id / file's id
     * @return  bool    TRUE/FALSE
     */
    private function _saveWebGroupsAccess($webGroupIds, $type, $id) {
        if (empty($webGroupIds) && !isset($type) && !isset($id)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['webgroup_save_err_empty_params'];
            return FALSE;
        }

        // delete the existing access ...
        $deleteWebAccess = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                . 'WHERE type=\'' . $type . '\' AND id=\'' . $id . '\' ';
        $queryDeleteWebAccess = mysql_query($deleteWebAccess);
        if (!$queryDeleteWebAccess) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteWebAccess;
            return FALSE;
        }

        // ... then insert back the new one
        foreach ($webGroupIds as $webGroupId) {
            $insertWebAccess = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                    . 'SET webgroup_id=\'' . $webGroupId . '\', type=\'' . $type . '\', id=\'' . $id . '\' ';
            $queryInsertWebAccess = mysql_query($insertWebAccess);
            if (!$queryInsertWebAccess) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertWebAccess;
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Save the E2G's manager access
     * @param $post values from the input form
     * @return bool TRUE | FALSE
     */
    private function _saveMgrAccess($post) {
        $countPostMgrAccess = count($post['mgrAccess']);
        $mgrAccess = @implode(',', $post['mgrAccess']);

        $updateMgrUser = 'UPDATE ' . $this->modx->db->config['table_prefix'] . 'easy2_users_mgr '
                . 'SET permissions=\'' . $mgrAccess . '\' '
                . 'WHERE id=\'' . $post['group_id'] . '\'';
        $queryUpdateMgrUser = mysql_query($updateMgrUser);
        if (!$queryUpdateMgrUser) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['user_update_err'];
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateMgrUser;
            return FALSE;
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['user_update_suc'];

        return TRUE;
    }

    /**
     * Save the web access of directories/folders
     * @param string $post All values from the form
     * @return mixed Saving the access or FALSE on failing
     */
    private function _saveDirWebAccess($post) {
        $deleteDirWebAccess = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                . 'WHERE webgroup_id=\'' . $post['group_id'] . '\' AND type=\'dir\'';
        $queryDeleteDirWebAccess = mysql_query($deleteDirWebAccess);
        if (!$queryDeleteDirWebAccess) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['user_update_err'];
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteDirWebAccess;
            return FALSE;
        }

        $dirWebAccess = $post['dirWebAccess'];
        foreach ($dirWebAccess as $v) {
            $insertDirWebAccess = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                    . 'SET webgroup_id=\'' . $post['group_id'] . '\', type=\'dir\', id=\'' . $v . '\'';
            $queryInsertDirWebAccess = mysql_query($insertDirWebAccess);
            if (!$queryInsertDirWebAccess) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['user_update_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertDirWebAccess;
                return FALSE;
            }
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['user_update_suc'];

        return TRUE;
    }

    /**
     * Save the web access of files/images
     * @param string $post All values from the form
     * @return mixed Saving the access or FALSE on failing
     */
    private function _saveFileWebAccess($post) {
        $deleteFileWebAccess = 'DELETE FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                . 'WHERE webgroup_id=\'' . $post['group_id'] . '\' AND type=\'file\' ';
        $queryDeleteFileWebAccess = mysql_query($deleteFileWebAccess);
        if (!$queryDeleteFileWebAccess) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['user_update_err'];
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFileWebAccess;
            return FALSE;
        }

        $fileWebAccess = $post['fileWebAccess'];
        foreach ($fileWebAccess as $v) {
            $insertFileWebAccess = 'INSERT INTO ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                    . 'SET webgroup_id=\'' . $post['group_id'] . '\', type=\'file\', id=\'' . $v . '\'';
            $queryInsertFileWebAccess = mysql_query($insertFileWebAccess);
            if (!$queryInsertFileWebAccess) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['user_update_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertFileWebAccess;
                return FALSE;
            }
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $this->lng['user_update_suc'];

        return TRUE;
    }

    /**
     * Collecting directory's IDs of the specified web group ID
     * @param int       $webGroupId modx's web group ID
     * @return array    An array of the directory IDs
     */
    private function _dirWebGroupIds($webGroupId) {
        $dirWebGroups = $this->modx->db->makeArray($this->modx->db->query(
                        'SELECT id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                        . 'WHERE type=\'dir\' AND webgroup_id=\'' . $webGroupId . '\''
                ));
        foreach ($dirWebGroups as $k => $v) {
            $dirWebGroups[$k] = $v['id'];
        }
        return $dirWebGroups;
    }

    /**
     * Unused!
     */
//    private function _dirWebGroupNames($webGroupId) {
//        $dirWebGroupIds = $this->_dirWebGroupIds($webGroupId);
//        foreach ($dirWebGroupIds as $id) {
//            $e2gWebGroupDirNames[$id] = $this->modx->db->getValue($this->modx->db->query(
//                                    'SELECT cat_name FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs '
//                                    . 'WHERE cat_id=\'' . $id . '\''
//                    ));
//        }
//
//        return $e2gWebGroupDirNames;
//    }

    /**
     * Check the access between directory and web-user
     * @param int   $dirId      directory's ID
     * @param int   $webGroupId modx's web group ID
     * @return bool TRUE | FALSE
     */
    private function _checkDirWebGroup($dirId, $webGroupId) {
        $dirWebGroupIds = $this->_dirWebGroupIds($webGroupId);
        if (in_array($dirId, $dirWebGroupIds))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Collecting file's IDs of the specified web group ID
     * @param int       $webGroupId modx's web group ID
     * @return array    An array of the file IDs
     */
    private function _fileWebGroupIds($webGroupId) {
        $fileWebGroups = $this->modx->db->makeArray($this->modx->db->query(
                        'SELECT id FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                        . 'WHERE type=\'file\' '
                        . 'AND webgroup_id=\'' . $webGroupId . '\''
                ));
        foreach ($fileWebGroups as $k => $v) {
            $fileWebGroups[$k] = $v['id'];
        }
        return $fileWebGroups;
    }

    /**
     * Check the access between file and web-user
     * @param int   $id      file's ID
     * @param int   $webGroupId modx's web group ID
     * @return bool TRUE | FALSE
     */
    private function _checkFileWebGroup($id, $webGroupId) {
        $fileWebGroupIds = $this->_fileWebGroupIds($webGroupId);
        if (in_array($id, $fileWebGroupIds))
            return TRUE;
        else
            return FALSE;
    }

    public function webGroupNames($id, $type = 'dir') {
        $checkWebAccess = array();
        $webGroupNames = array();
        $checkWebAccessQuery = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                . 'WHERE type=\'' . $type . '\' AND id=\'' . $id . '\'';
        $checkWebAccess = $this->modx->db->makeArray($this->modx->db->query($checkWebAccessQuery));
        if (!empty($checkWebAccess)) {
            foreach ($checkWebAccess as $k => $v) {
                $webgroup_id[$type][$id][] = '\'' . $v['webgroup_id'] . '\'';
            }
            $implodeGroupId = implode(',', $webgroup_id[$type][$id]);
            $webAccessQuery = 'SELECT name FROM ' . $this->modx->db->config['table_prefix'] . 'webgroup_names '
                    . 'WHERE id IN (' . $implodeGroupId . ')';
            $webGroup = $this->modx->db->makeArray($this->modx->db->query($webAccessQuery));
            foreach ($webGroup as $k => $v) {
                $webGroupNames[] = $v['name'];
            }
        }
        return $webGroupNames;
    }

    /**
     * Check whether the OLD config.pages.easy2gallery.php file still exist, which means this is an upgrade
     * @return mixed    redirect to the config page to do the saving action, or nothing for TRUE
     */
    private function _checkConfigCompletion() {
        // loading the hyperlinks ($this->e2gModCfg['e2gPages'])
        $pageConfigFile = realpath(E2G_MODULE_PATH . 'includes/configs/config.pages.easy2gallery.php');
        if (empty($pageConfigFile) || !file_exists($pageConfigFile)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['config_file_err_missing'];
            return FALSE;
        }
        require $pageConfigFile;

        // delete the config file, because this will always be checked as an upgrade option
        if (file_exists(realpath(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php'))
                && $this->sanitizedGets['e2gpg'] != $this->e2gModCfg['e2gPages']['config']['e2gpg']
        ) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->lng['config_save_warning'];
            header('Location: ' . html_entity_decode($this->e2gModCfg['blank_index'] . '&amp;e2gpg=' . $this->e2gModCfg['e2gPages']['config']['e2gpg']));
        } else
            return TRUE;
    }

    /**
     * To get and create thumbnails
     * @param  int    $gdir             from $_GET['gid']
     * @param  string $path             directory path of each of thumbnail
     * @param  int    $w                thumbnail width
     * @param  int    $h                thumbnail height
     * @param  int    $thq              thumbnail quality
     * @param  string $resizeType       'inner' | 'resize'
     *                                  'inner' = crop the thumbnail
     *                                  'resize' = autofit the thumbnail
     * @param  int    $red              Red in RGB
     * @param  int    $green            Green in RGB
     * @param  int    $blue             Blue in RGB
     * @param  bool   $createWaterMark  create water mark
     * @return mixed  FALSE/the thumbnail's path
     */
    public function imgShaper(
    $gdir
    , $path
    , $w
    , $h
    , $thq
    , $resizeType = NULL
    , $red = NULL
    , $green = NULL
    , $blue = NULL
    , $createWaterMark = 0
    ) {
        // decoding UTF-8
        $gdir = $this->e2gDecode($gdir);
        $path = $this->e2gDecode($path);
        if (empty($path)) {
            return FALSE;
        }

        $w = !empty($w) ? $w : $this->e2gModCfg['mod_w'];
        $h = !empty($h) ? $h : $this->e2gModCfg['mod_h'];
        $thq = !empty($thq) ? $thq : $this->e2gModCfg['mod_thq'];
        $resizeType = !empty($resizeType) ? $resizeType : $this->e2g['resize_type'];
        $red = !empty($red) ? $red : $this->e2g['thbg_red'];
        $green = !empty($green) ? $green : $this->e2g['thbg_green'];
        $blue = !empty($blue) ? $blue : $this->e2g['thbg_blue'];
        $thumbPath = '_thumbnails/'
                . substr($path, 0, strrpos($path, '.'))
                . '_' . $resizeType
                . '_' . $w . 'x' . $h
                . '_' . $thq
                . '_' . $red . 'x' . $green . 'x' . $blue
                . '.jpg';

        // create cover file
        $thumbDirs = explode('/', dirname($thumbPath));
        $count = count($thumbDirs);
        $xpath = $gdir;
        for ($c = 0; $c < $count; $c++) {
            $xpath .= $thumbDirs[$c] . '/';
            $this->createIndexHtml($xpath, $this->lng['indexfile']);
        }

        if (!class_exists('E2gThumb')) {
            $thumbClassFile = realpath(E2G_MODULE_PATH . 'includes/models/e2g.public.thumbnail.class.php');
            if (empty($thumbClassFile) || !file_exists($thumbClassFile)) {
                $_SESSION['easy2err'][] = __LINE__ . ' : File <b>' . $thumbClassFile . '</b> does not exist.';
                return FALSE;
            } else {
                include_once $thumbClassFile;
            }
        }

        $imgShaper = new E2gThumb($this->modx, $this->e2gModCfg);
        $urlEncoding = $imgShaper->imgShaper($gdir, $path, $w, $h, $thq, $resizeType, $red, $green, $blue, $createWaterMark, $thumbPath);
        if (!$urlEncoding) {
            return FALSE;
        }
        return $urlEncoding;
    }

    /**
     * To calculate the directory content
     * @param   string  $path       folder's/dir's path
     * @return  int     file numbers
     */
    public function countFiles($path) {
        // catches the object oriented source or javascript variable
        $realPath = realpath($path);
        $path = !empty($realPath) ? $realPath : base64_decode($path);
        if (!file_exists($path)) {
            return FALSE;
        }

        if (empty($path)) {
            return $this->lng['invalid_folder'];
        }

        $cnt = 0;
        $fs = array();
        $fs = glob($path . DIRECTORY_SEPARATOR . '*.*');
        if ($fs != FALSE) {
            foreach ($fs as $f) {
                if ($this->validFile($f))
                    $cnt++;
                else
                    continue;
            }
        }
        $sd = array();
        $sd = glob($path . DIRECTORY_SEPARATOR . '*');
        if ($sd != FALSE)
            foreach ($sd as $d) {
                $cnt += $this->countFiles($d);
            }
        return $cnt;
    }

    /**
     * Select any available date from the selections
     * @param date      $dateAdded      time when the object was added into the database
     * @param date      $lastModified   last time when the object was modified
     * @param string    $path           path
     * @return date     time
     */
    public function getTime($dateAdded, $lastModified, $path) {
        $getTime = (strtotime($lastModified) != FALSE) ? strtotime($lastModified) : strtotime($dateAdded);
        if ($getTime == '' && isset($path)) {
            $getTime = filemtime($path);
            clearstatcache();
        }

        $getTime = @date($this->e2g['mod_date_format'], $getTime);

        return $getTime;
    }

    /**
     * Create Tags as links for the module's pages
     * @param string    $tags   The tags
     * @return string   The tag's links
     */
    public function createTagLinks($tags, $index = null) {
        if (empty($tags)) {
            return NULL;
        }
        $index = !empty($index) ? $index : $this->e2gModCfg['index'];

        $multipleTags = @explode(',', $tags);
        $countTags = count($multipleTags);
        $output = '';
        for ($c = 0; $c < $countTags; $c++) {
            $output .= '<a href="' . $index . '&amp;tag=' . trim($multipleTags[$c]) . '">' . trim($multipleTags[$c]) . '</a>';
            if ($c < ($countTags - 1))
                $output .= ', ';
        }

        return $output;
    }

    /**
     * Button's link, image, and attributes
     * @param string    $buttonName     button's name
     * @param array     $getParams      $_GET parameters to be appended into the link
     * @param string    $attributes     additional space for styles, onclick event, or anything else.
     * @param string    $index          change the URL if needed
     * @return string   The button's hyperlink and image.
     */
    public function actionIcon($buttonName, $getParams = array(), $attributes = NULL, $index = NULL) {
        $index = !empty($index) ? $index : $this->e2gModCfg['index'];

        if (!is_array($getParams) || empty($getParams)) {
            return FALSE;
        }

        $button = '
                <a href="' . $index;
        foreach ($getParams as $k => $v) {
            $button .= '&amp;' . $k . '=' . $v;
        }
        $button .= '" ';
        if (isset($attributes))
            $button .= $attributes;

        $button .= '>';

        // dirs
        if ($buttonName == 'hide_dir') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_opened.png" width="16" height="16"'
                    . ' alt="' . $this->lng['visible'] . '" title="' . $this->lng['visible'] . '" border="0" />';
        } elseif ($buttonName == 'unhide_dir') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16" height="16"'
                    . ' alt="' . $this->lng['hidden'] . '" title="' . $this->lng['hidden'] . '" border="0" />';
        } elseif ($buttonName == 'edit_dir') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_edit.png" width="16" height="16"'
                    . ' alt="' . $this->lng['edit'] . '" title="' . $this->lng['edit'] . '" border="0" />';
        } elseif ($buttonName == 'add_dir') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_add.png" width="16" height="16"'
                    . ' alt="' . $this->lng['add_to_db'] . '" title="' . $this->lng['add_to_db'] . '" border="0" />';
        } elseif ($buttonName == 'delete_dir') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/delete.png" width="16" height="16"'
                    . ' alt="' . $this->lng['delete'] . '" title="' . $this->lng['delete'] . '" border="0" />';
        }

        // images
        if ($buttonName == 'hide_file') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_opened.png" width="16" height="16"'
                    . ' alt="' . $this->lng['visible'] . '" title="' . $this->lng['visible'] . '" border="0" />';
        } elseif ($buttonName == 'unhide_file') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16" height="16"'
                    . ' alt="' . $this->lng['hidden'] . '" title="' . $this->lng['hidden'] . '" border="0" />';
        } elseif ($buttonName == 'comments') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/comments.png" width="16" height="16"'
                    . ' alt="' . $this->lng['comments'] . '" title="' . $this->lng['comments'] . '" border="0" />';
        } elseif ($buttonName == 'edit_file') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_edit.png" width="16" height="16"'
                    . ' alt="' . $this->lng['edit'] . '" title="' . $this->lng['edit'] . '" border="0" />';
        } elseif ($buttonName == 'add_file') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_add.png" width="16" height="16"'
                    . ' alt="' . $this->lng['add_to_db'] . '" title="' . $this->lng['add_to_db'] . '" border="0" />';
        } elseif ($buttonName == 'delete_file') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/delete.png" width="16" height="16"'
                    . ' alt="' . $this->lng['delete'] . '" title="' . $this->lng['delete'] . '" border="0" />';
        }


        $button .= '</a>' . "\n";

        return $button;
    }

    /**
     * Sanitizes the GET request for more security
     * @param   array   $_GET   the request values
     * @return  array   sanitized values
     */
    public function sanitizedGets($get) {
        $sanitizedGets = array();
        if (empty($get)) {
            return FALSE;
        }
        foreach ($get as $k => $v) {
            $xplds = @explode('/', $v);
            foreach ($xplds as $y => $x) {
                $xplds[$y] = $this->sanitizedString($x);
            }
            $v = @implode('/', $xplds);
            $sanitizedGets[$k] = $v;
        }
        return $sanitizedGets;
    }

    /**
     * Load properties
     * @return void
     */
    public function loadSettings() {
        $e2g = array();
        $countConfigs = array();
        /**
         * Create a smooth conversion between file based config to database base
         */
        // CONFIGURATIONS from the previous version installation
        $configFile = dirname(dirname(__FILE__)) . '/configs/config.easy2gallery.php';
        if (file_exists(realpath($configFile))) {
            require_once realpath($configFile);
            foreach ($e2g as $ck => $cv) {
                $configsKey[$ck] = $ck;
                $configsVal[$ck] = $cv;
            }
            $countConfigs['oldConfigFile'] = count($e2g);
        }

        // CONFIGURATIONS
        $configTables = 'SHOW TABLES LIKE \'' . $this->modx->db->config['table_prefix'] . 'easy2_configs\' ';
        $configTablesValues = $this->modx->db->getValue($this->modx->db->query($configTables));
        if (!empty($configTablesValues)) {
            $configsQuery = $this->modx->db->select('*', $this->modx->db->config['table_prefix'] . 'easy2_configs');
            if ($configsQuery) {
                while ($row = mysql_fetch_assoc($configsQuery)) {
                    $configsKey[$row['cfg_key']] = $row['cfg_key'];
                    $e2g[$row['cfg_key']] = $row['cfg_val'];
                }
            }
            $countConfigs['oldConfigDb'] = count($e2g);
        }

        // the default config will replace any blank value of config's.
        $defaultConfigFile = dirname(dirname(__FILE__)) . '/configs/default.config.easy2gallery.php';
        if (file_exists(realpath($defaultConfigFile))) {
            require_once realpath($defaultConfigFile);
            if (!empty($e2gDefault)) {
                foreach ($e2gDefault as $dk => $dv) {
                    if (!isset($configsKey[$dk])) {
                        $e2g[$dk] = $dv;
                    }
                }
            }
            $countConfigs['defaultConfigs'] = count($e2gDefault);
        }

        // Easy 2 Gallery module path
        if (!defined('E2G_GALLERY_PATH')) {
            define('E2G_GALLERY_PATH', MODX_BASE_PATH . $e2g['dir']);
        }
        // Easy 2 Gallery module URL
        if (!defined('E2G_GALLERY_URL')) {
            define('E2G_GALLERY_URL', MODX_SITE_URL . $e2g['dir']);
        }

        $saveE2gSettings = FALSE;
        if (isset($countConfigs['oldConfigFile'])) {
            $saveE2gSettings = TRUE;
        }
        if (isset($countConfigs['oldConfigDb']) && $countConfigs['oldConfigDb'] < $countConfigs['defaultConfigs']) {
            $saveE2gSettings = TRUE;
        }
        if (!isset($countConfigs['oldConfigFile']) && !isset($countConfigs['oldConfigDb']) && !$_SESSION['installE2g']) {
            $saveE2gSettings = TRUE;
        }
        if ($saveE2gSettings
                && $_SESSION['installE2g'] === FALSE // exclude new installation
        ) {
            $this->saveE2gSettings($e2g);
        }

        return $this->_htmlspecialcharsArray($e2g);
    }

    public function installE2g($modx, $e2g) {
        include E2G_MODULE_PATH . 'install/index.php';
        exit();
    }

    public function checkFolders() {
        // CHECKING THE root and _thumbnails FOLDERs
        if (!is_dir(MODX_BASE_PATH . $this->e2g['dir'])) {
            // INSTALL
            if (is_dir(E2G_MODULE_PATH . 'install')) {
                return $this->installE2g($this->modx, $this->e2g);
            } else {
                $_SESSION['easy2err'][] = '<b style="color:red">' . $this->lng['dir'] . ' &quot;'
                        . $this->e2g['dir'] . '&quot; ' . $this->lng['empty'] . '</b>';
//                exit;
            }
        } elseif (!is_dir(MODX_BASE_PATH . $this->e2g['dir'] . '_thumbnails')) {
            if (mkdir(MODX_BASE_PATH . $this->e2g['dir'] . '_thumbnails')) {
                @chmod(MODX_BASE_PATH . $this->e2g['dir'] . '_thumbnails', 0755);
            } else {
                $_SESSION['easy2err'][] = '<b style="color:red">' . $this->lng['_thumb_err'] . '</b>';
                exit;
            }
        }
    }

    private function _loadE2gModCfg() {
        $modx = $this->modx; // for below page
        $e2g = $this->e2g; // for below page
        $lng = $this->lng; // for below page
        include dirname(dirname(__FILE__)) . '/configs/params.module.easy2gallery.php';

        return $e2gModCfg;
    }

    /**
     * htmlspecialchars the input in an array form
     * @param   $array  subjects
     * @return  string  the processed subjects
     */
    private function _htmlspecialcharsArray($array) {
        if (empty($array))
            return $array;
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->_htmlspecialcharsArray($value);
            }
            $o[$key] = $this->_escapeString($value);
        }
        return $o;
    }

    /**
     * Escapes special characters in a string for use in an SQL statement
     * @param   string  $s  string
     * @return  string  escaped string
     */
    private function _escapeString($s) {
        $s = trim($s);
        $s = $this->modx->db->escape(htmlspecialchars($s, ENT_QUOTES));
        return $s;
    }

}