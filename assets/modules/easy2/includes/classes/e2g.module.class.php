<?php

if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

/**
 * EASY 2 GALLERY
 * Gallery Module Class for Easy 2 Gallery Module for MODx Evolution
 * @author Cx2 <inteldesign@mail.ru>
 * @author Temus <temus3@gmail.com>
 * @author goldsky <goldsky@modx-id.com>
 * @version 1.4.0
 */
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
    private $e2gModCfg;
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

    public function __construct($modx, $e2gModCfg, $e2g, $lng) {
        parent::__construct($modx, $e2gModCfg);
        $this->modx = & $modx;
        $this->e2gModCfg = $e2gModCfg;
        $this->e2g = $e2g;
        $this->lng = $lng;
    }

    /**
     * The main file explorer function
     * @param array $e2g The values from the config file
     * @return string The module's pages.
     */
    public function explore($e2g) {
        $modx = $this->modx;
        $lng = $this->lng;
        $e2gDebug = $this->e2gModCfg['e2g_debug'];
        $parentId = $this->e2gModCfg['parent_id'];
        $_a = $this->e2gModCfg['_a'];
        $_i = $this->e2gModCfg['_i'];
        $index = $this->e2gModCfg['index'];
        $blankIndex = $this->e2gModCfg['blank_index'];
        $e2gPages = $this->e2gModCfg['e2gPages'];
//        $gdir = $this->e2gModCfg['gdir'];
        $gdir = $this->e2gModCfg['dir'];
        $rootDir = $gdir;

        $getPathArray = $this->_getPath($parentId, NULL, 'array');
        $path = array();

        // Create the ROOT gallery's link
        $path['link'] = '';
        $path['string'] = '';
        foreach ($getPathArray as $k => $v) {
            $path['link'] .= '<a href="' . $index . '&amp;pid=' . $k . '">' . $v . '</a>/';
        }
        unset($getPathArray[1]);

        // Create the afterwards gallery's path
        if (!empty($getPathArray)) {
            $path['string'] = implode('/', $getPathArray) . '/';
            $gdir .= $path['string'];
        } elseif (isset($_GET['path'])) {
            $path['string'] = '';
            $getPath = str_replace('../', '', $_GET['path']);
            $pathArray = explode('/', $getPath);
            foreach ($pathArray as $v) {
                if (empty($v)) {
                    continue;
                }
                $path['string'] .= $v . '/';
                $path['link'] .= '<a href="' . $index . '&amp;pid=' . $parentId . '&amp;path=' . $path['string'] . '">' . $v . '</a> / ';
            }
            $gdir .= $path['string'];
        }

        /**
         * GALLERY ACTIONS
         */
        $act = empty($_GET['act']) ? '' : $_GET['act'];
        switch ($act) {
            case 'synchro':
                if ($this->_synchro(MODX_BASE_PATH . $e2g['dir'], 1)) {
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['synchro_suc'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['synchro_err'];
                }
                $this->_cleanCache();
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'upload_all':
                if (!$this->_uploadAll($gdir, $_POST, $_FILES)) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } else {
                    if ($_POST['gotofolder'] == 'gothere') {
                        header('Location: ' . html_entity_decode(
                                        MODX_MANAGER_URL
                                        . 'index.php?'
                                        . 'a=' . $_a
                                        . '&amp;id=' . $_i
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

            case 'show_dir' :
                $this->_showDir($_GET['dir_id']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'hide_dir' :
                $this->_hideDir($_GET['dir_id']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'show_file' :
                $this->_showFile($_GET['file_id']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'hide_file' :
                $this->_hideFile($_GET['file_id']);
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
                $this->_downloadChecked($gdir, $_GET['pid'], $_POST);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Move files/folders to the new folder
            case 'move_checked':
                if (!$this->_moveChecked($_POST)) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }

                $this->_cleanCache();
                /**
                 * REDIRECT PAGE TO THE SELECTED OPTION
                 */
                if (($_POST['gotofolder'] == 'gothere')) {
                    header('Location: ' . html_entity_decode(
                                    MODX_MANAGER_URL
                                    . 'index.php?'
                                    . 'a=' . $_a
                                    . '&amp;id=' . $_i
                                    . '&amp;e2gpg=2'
                                    . '&amp;pid=' . $_POST['newparent']
                    ));
                } else {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                }

                exit();
                break;

            case 'delete_dir':
                $this->_deleteDir($_GET);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'delete_file':
                $this->_deleteFile($_GET);
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
                    $url = $index . '&amp;act=clean_cache';
                } else {
                    $url = html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES);
                }

                $this->_saveConfig($_POST);

                header('Location: ' . html_entity_decode($url));
                exit();
                break;

            // Save translation
            case 'save_lang':
                $this->_saveLang($_POST);
                header('Location: ' . html_entity_decode($index));
                exit();
                break;

            // Add directory into database
            case 'add_dir':
                $this->_addAll('../' . str_replace('../', '', $this->_e2gDecode($_GET['dir_path']) . '/'), $parentId);
//                if ($this->_addAll('../' . str_replace('../', '', $this->_e2gDecode($_GET['dir_path']) . '/'), $parentId)) {
//                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_added'];
//                } else {
//                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_add_err'];
//                }

                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Add image into database
            case 'add_file':
                $this->_addFile(MODX_BASE_PATH . $_GET['file_path'], $_GET['pid']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();

            // Add slideshow
            case 'save_slideshow':
                $this->_saveSlideshow($_POST);
                header('Location: ' . html_entity_decode($index));
                exit();
                break;

            case 'update_slideshow':
                $this->_updateSlideshow($_POST);
                header('Location: ' . html_entity_decode($index));
                exit();
                break;

            // Add plugin
            case 'save_plugin':
                $this->_savePlugin($_POST);
                header('Location: ' . html_entity_decode($index));
                exit();
                break;

            case 'update_plugin':
                $this->_updatePlugin($_POST);
                header('Location: ' . html_entity_decode($index));
                exit();
                break;

            // Add thumbnail viewer
            case 'save_viewer':
                if (!$this->_saveViewer($_POST)) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } else {
                    header('Location: ' . html_entity_decode($index));
                }
                exit();
                break;

            // Update thumbnail viewer
            case 'update_viewer':
                if (!$this->_updateViewer($_POST)) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } else {
                    header('Location: ' . html_entity_decode($index));
                }
                exit();
                break;

            // Ignore ip address in image comments
            case 'ignore_ip':
                $this->_ignoreIp($_GET);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Unignore ip address in image comments
            case 'unignore_ip':
                $this->_unignoreIp($_GET);
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
                $this->_commentApprove($_GET['comid']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'com_hide':
                $this->_commentHide($_GET['comid']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'com_unhide':
                $this->_commentUnhide($_GET['comid']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'com_save':
                $this->_commentSave($_POST);
                $url = $index
                        . (!empty($_GET['page']) ? '&page=' . $_GET['page'] : NULL)
                        . (!empty($_GET['filter']) ? '&filter=' . $_GET['filter'] : NULL)
                        . (!empty($_GET['file_id']) ? '&file_id=' . $_GET['file_id'] : NULL)
                        . (!empty($_GET['pid']) ? '&pid=' . $_GET['pid'] : NULL)
                        . (!empty($_GET['tag']) ? '&tag=' . $_GET['tag'] : NULL);
                header('Location: ' . html_entity_decode($url, ENT_NOQUOTES));
                exit();
                break;

            case 'com_delete':
                $this->_commentDelete($_GET['comid']);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            /**
             * @todo add the hit reset
             */
            case 'reset_all_hit':
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['reset_all_hit_suc'];
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            case 'cancel':
                header('Location: ' . html_entity_decode($index, ENT_NOQUOTES));
                exit();
                break;

            case 'create_dir':
                // check names against bad characters
                if ($this->_hasBadChar($_POST['name'], __LINE__)
                        || !$this->_createDir($_POST, $gdir, $parentId)
                ) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } else {
                    header('Location: ' . html_entity_decode($index . '&amp;pid=' . $parentId));
                }
                exit();
                break;

            case 'save_dir':
                // check names against bad characters
                if ($this->_hasBadChar($_POST['newdirname'], __LINE__)
                        || !$this->_editDir($gdir, $_POST)
                ) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } elseif (isset($_GET['tag'])) {
                    header('Location: ' . html_entity_decode($index . '&amp;tag=' . $_GET['tag']));
                } else {
                    header('Location: ' . html_entity_decode($index . '&amp;pid=' . $parentId));
                }
                exit();
                break;

            case 'save_file':
                // check names against bad characters
                if ($this->_hasBadChar($_POST['newfilename'], __LINE__)
                        || !$this->_editFile($gdir, $_POST)
                ) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } elseif (isset($_GET['tag'])) {
                    header('Location: ' . html_entity_decode($index . '&amp;tag=' . $_GET['tag']));
                } else {
                    header('Location: ' . html_entity_decode($index . '&amp;pid=' . $parentId));
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
                header('Location: ' . html_entity_decode($index));
                exit;
                break;

            case 'save_web_dirs_perm':
                $this->_saveDirWebAccess($_POST);
                header('Location: ' . html_entity_decode($index));
                exit;
                break;

            case 'save_web_files_perm':
                $this->_saveFileWebAccess($_POST);
                header('Location: ' . html_entity_decode($index));
                exit;
                break;
        } // switch ($act)

        /**
         * PAGE ACTION
         */
        // for table row class looping
        $rowClass = array(' class="gridAltItem"', ' class="gridItem"');
        $rowNum = 0;
        $page = empty($_GET['page']) ? '' : $_GET['page'];
        switch ($page) {
            case 'create_dir':
                //the page content is rendered in ../tpl/pages/page.file.create_dir.inc.php
                break;

            case 'edit_dir' :
                if (empty($_GET['dir_id']) || !is_numeric($_GET['dir_id'])) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['id_err'];
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }

                // call up the database content first as the comparison subjects
                $res = mysql_query('SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs WHERE cat_id=' . (int) $_GET['dir_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);
                mysql_free_result($res);
                //the page content is rendered in ../tpl/pages/page.file.edit_dir.inc.php
                break;

            case 'edit_file':
                if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $id['id_err'];

                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }

                // call up the database content first as the comparison subjects
                $res = mysql_query('SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $_GET['file_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);
                mysql_free_result($res);

                $ext = substr($row['filename'], strrpos($row['filename'], '.'));
                $filename = substr($row['filename'], 0, -(strlen($ext)));

                //the page content is rendered in ../tpl/pages/page.file.edit_file.inc.php
                break;

            case 'comments':
                if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $id['id_err'];

                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }
                $res = mysql_query('SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $_GET['file_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);
                mysql_free_result($res);
                //the page content is rendered in ../tpl/pages/page.file.comments.inc.php
                break;

            case 'openexplorer':
                if (isset($_POST['newparent']))
                    $parentId = $_POST['newparent'];
                header('Location: ' . html_entity_decode($index . '&amp;pid=' . $parentId));
                exit();
                break;

            default:
                //the page content is rendered in ../tpl/pages/page.main.inc.php
                break;
        } // switch ($page)

        ob_start();
        include_once E2G_MODULE_PATH . 'includes/tpl/pages/main.inc.php';
        $output = ob_get_contents();
        ob_end_clean();

        echo $output;
    }

    private function _loadModPages($e2g) {
        $modx = $this->modx;
        $lng = $this->lng;
        $e2gDebug = $this->e2gModCfg['e2g_debug'];
        $parentId = $this->e2gModCfg['parent_id'];
        $_a = $this->e2gModCfg['_a'];
        $_i = $this->e2gModCfg['_i'];
        $index = $this->e2gModCfg['index'];
        $blankIndex = $this->e2gModCfg['blank_index'];
//        $gdir = $this->e2gModCfg['gdir'];
        $gdir = $this->e2gModCfg['dir'];
        $rootDir = $gdir;

        $getPathArray = $this->_getPath($parentId, NULL, 'array');
        $path = array();

        // Create the ROOT gallery's link
        foreach ($getPathArray as $k => $v) {
            $path['link'] .= '<a href="' . $index . '&amp;pid=' . $k . '">' . $v . '</a>/';
        }
        unset($getPathArray[1]);

        // Create the afterwards gallery's path
        if (!empty($getPathArray)) {
            $path['string'] = implode('/', $getPathArray) . '/';
            $gdir .= $path['string'];
        } elseif (isset($_GET['path'])) {
            $path['string'] = '';
            $getPath = str_replace('../', '', $_GET['path']);
            $pathArray = explode('/', $getPath);
//            $path['string'] = '';
            foreach ($pathArray as $v) {
                if (empty($v)) {
                    continue;
                }
                $path['string'] .= $v . '/';
                $path['link'] .= '<a href="' . $index . '&amp;pid=' . $parentId . '&amp;path=' . $path['string'] . '">' . $v . '</a> / ';
            }
            $gdir .= $path['string'];
        }
    }

    /**
     * To delete all files/folders that have been selected with the checkbox
     * @param string $path file's/folder's path
     */
    private function _deleteAll($path) {
        $lng = $this->lng;

        $res = array('d' => 0, 'f' => 0, 'e' => array());
        if (!$this->_validFolder($path))
            return $res;
        $fs = array();
        $fs = glob($path . '*');
        if ($fs != FALSE) {
            foreach ($fs as $f) {
                // using original file check, not _validFile($f), because it will delete not only images.
                if (is_file($f)) {
                    if (@unlink($f))
                        $res['f']++;
                    else
                        $res['e'][] = __LINE__ . ' : ' . $lng['file_delete_err'] . ' : ' . $f;
                } elseif (is_dir($f)) {
                    $sres = $this->_deleteAll($f . '/');

                    $res['f'] += $sres['f'];
                    $res['d'] += $sres['d'];
                    $res['e'] = array_merge($res['e'], $sres['e']);
                }
            }
        }
        if (count($res['e']) == 0 && @rmdir($path))
            $res['d']++;
        else
            $res['e'][] = __LINE__ . ' : ' . $lng['dir_delete_err'] . ' : ' . $path;
        return $res;
    }

    /**
     * move all content to a new parent
     * @param string $oldPath Previous folder
     * @param string $newPath On target folder
     * @return array Only returns result reports, for confirmation display
     */
    private function _moveAll($oldPath, $newPath) {
        $lng = $this->lng;

        $res = array('d' => 0, 'f' => 0, 'e' => array());
        if (!$this->_validFolder($oldPath)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['invalid_folder'] . ' : ' . $oldPath;
            return $res;
        }
        $fs = array();
        $fs = glob($oldPath . '/*');
        if ($fs != FALSE) {
            foreach ($fs as $f) {
                if (is_file($f)) {
                    $res['file'][] = $f;
                    $res['f']++;
                } elseif ($this->_validFolder($f)) {
                    $res['dir'][] = $f;
                    $res['d']++;
                    // $res = result (file/dir/error)
                    // $sres = result summary (file/dir/error)
                    $res['f'] += $sres['f'];
                    $res['d'] += $sres['d'];
                    $res['e'] = array_merge($res['e'], $sres['e']);

                    $oldBasename = $this->_basenameSafe($f);
                    $fNewPath .= '/' . $oldBasename;
                    $sres = $this->_moveAll($f, $newPath . $fNewPath);
                }
            }
        }
        if (@rename($oldPath, $newPath))
            $res['d']++;
        else
            $res['e'][] = __LINE__ . ' : ' . $lng['dir_move_err'] . ' : ' . $oldPath;

        // only returns the result calculation array
        return $res;
    }

    /**
     * To add all files from the upload form
     * @param string $path  file's/folder's path
     * @param int    $pid   current parent ID
     * @param string $cfg   module's configuration
     * @param string $lng   language translation
     */
    private function _addAll($path, $pid) {
        $modx = $this->modx;
        $e2g = $this->e2g;
        $lng = $this->lng;

        if (!$this->_validFolder($path)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['invalid_folder'] . ' : ' . $path;
            return FALSE;
        }

        require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
        $tree = new TTree();
        $tree->table = $modx->db->config['table_prefix'] . 'easy2_dirs';
        $name = $this->_basenameSafe($path);
        $name = $this->_e2gEncode($name);

        // converting non-latin names with MODx's stripAlias function
        $nameAlias = $modx->stripAlias($name);


        if ($name != $nameAlias) {
            $basePath = dirname($path);
            $rename = rename($path, $basePath . '/' . $this->_e2gDecode($nameAlias));
            if (!$rename) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_rename_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $path . ' => ' . $basePath . '/' . $this->_e2gDecode($nameAlias);
                return FALSE;
            }
            $this->_changeModOwnGrp('dir', $basePath . '/' . $this->_e2gDecode($nameAlias));

            // glue them back
            $path = $basePath . '/' . $this->_e2gDecode($nameAlias) . '/';
            $name = $nameAlias;
        }

        if (!($id = $tree->insert($name, $pid))) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_add_err'] . ' : ' . $tree->error;
            return FALSE;
        }
        $modx->db->query(
                'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'SET date_added=NOW() '
                . ', added_by=\'' . $modx->getLoginUserID() . '\' '
                . 'WHERE cat_id=' . $id
        );

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_added'] . ' : ' . $path;

        // invoke the plugin
        $this->_plugin('OnE2GFolderAdd', array('gid' => $id, 'foldername' => $name));

        /**
         * goldsky -- if there is no index.html inside folders, this will create it.
         */
        $this->_createsIndexHtml($path, $lng['indexfile']);

        $fs = array();
        $fs = @glob($path . '*');
        natsort($fs);

        // goldsky -- alter the maximum execution time
        if (function_exists('set_time_limit'))
            @set_time_limit(0);
        if ($fs != FALSE)
            foreach ($fs as $filePath) {
                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_start();

                if ($this->_validFolder($filePath)) {
                    // goldsky -- if the path is a dir, go deeper as $path==$filePath
                    if (!$this->_addAll($filePath . '/', $id)) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_add_err'] . ' : ' . $filePath;
                        return FALSE;
                    }
                } elseif ($this->_validFile($filePath)) {
                    /**
                     * INSERT filename into database
                     */
                    if (!$this->_addFile($filePath, $id)) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $filePath;
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
     * To add file from the upload form or add file button into the database
     * @param string $f filename
     * @param int $pid current parent ID
     * @param string $cfg module's configuration
     */
    private function _addFile($filePath, $pid) {
        $modx = $this->modx;
        $e2g = $this->e2g;
        $lng = $this->lng;
        $e2gDebug = $this->e2gModCfg['e2g_debug'];

        $inf = @getimagesize($filePath);
        if ($inf[2] > 3 || !is_numeric($pid)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_add_err'];
            return FALSE;
        }

        // RESIZE
        $basename = $this->_basenameSafe($filePath);
        $basename = $this->_e2gEncode($basename);

        // converting non-latin names with MODx's stripAlias function
        $fileAlias = $modx->stripAlias($basename);
        if ($basename != $fileAlias) {
            $dirPath = dirname($filePath);
            $rename = rename($filePath, $dirPath . '/' . $this->_e2gDecode($fileAlias));
            if (!$rename) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_rename_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $filePath . ' => ' . $dirPath . '/' . $this->_e2gDecode($fileAlias);
                return FALSE;
            }
            $this->_changeModOwnGrp('file', $dirPath . '/' . $this->_e2gDecode($fileAlias));

            $filePath = $dirPath . '/' . $this->_e2gDecode($fileAlias);
            $basename = $fileAlias;
        }

        $newInf = array();
        // RESIZE
        $newInf = $this->_resizeImg($filePath, $inf, $e2g['maxw'], $e2g['maxh'], $e2g['maxthq']);

        if ($newInf === FALSE) {
            clearstatcache();
            $newInf = $inf;
            $newInf['size'] = filesize($filePath);
            $newInf['time'] = filemtime($filePath);
        }

        $size = $newInf['size'];
        $width = $newInf[0];
        $height = $newInf[1];
        $time = $newInf['time'];

        $insertFileQuery = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . "SET dir_id ='$pid'"
                . ", filename='$basename'"
                . ", size='$size'"
                . ", width='$width'"
                . ", height='$height'"
                . ", date_added=NOW()"
                . ", added_by='" . $modx->getLoginUserID() . "'"
                . ", last_modified='$time'"
                . ", modified_by='" . $modx->getLoginUserID() . "'"
        ;
        if (!mysql_query($insertFileQuery)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertFileQuery;
            return FALSE;
        }
        unset($insertFileQuery);

        // invoke the plugin
        $this->_plugin('OnE2GFileAdd', array(
            'fid' => mysql_insert_id()
            , 'filename' => $filteredName
            , 'pid' => $pid
        ));

        if ($e2gDebug == '1')
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_added'] . ' ' . $basename;

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
        $e2g = $this->e2g;
        $lng = $this->lng;

        // if both configs are not zeros
        if ($w + $h != 0) {

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

            if ($e2g['resize_orientated_img'] == '1') {

                // the source image is the same or smaller than the destination on width AND height
                if ($inf[1] <= $w && $inf[0] <= $h) {
                    return FALSE;
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
            } // if ($e2g['resize_orientated_img'] == '1')
            else {

                // the source image is smaller than the destination on width AND height
                if ($inf[0] <= $w && $inf[1] <= $h) {
                    return FALSE;
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
                        return FALSE;
                    }
                } // if ( $srcRatio < 1 && $dstRatio > 1 )
                // source is landscape, destination is portrait
                elseif ($srcRatio > 1 && $dstRatio < 1) {
                    $h = round($w * $inf[1] / $inf[0], 2);
                }
            } // else if not oriented
        } // if ( $w + $h !== 0 )
        // OPEN
        if ($inf[2] == 1)
            $im = imagecreatefromgif($filename);
        elseif ($inf[2] == 2)
            $im = imagecreatefromjpeg($filename);
        elseif ($inf[2] == 3)
            $im = imagecreatefrompng($filename);
        else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_create_err'] . ' ' . $filename;
            return FALSE;
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
        $this->_changeModOwnGrp('file', $filename);

        imagedestroy($pic);
        imagedestroy($im);

        clearstatcache();
        $newInf = @getimagesize($filename);
        $newInf['size'] = filesize($filename);
        $newInf['time'] = filemtime($filename);
        return $newInf;
//        return TRUE;
    }

    /**
     * To get paths from the parent directory up to the Easy 2's ROOT gallery
     * @param int       $dirId      parent directory's ID
     * @param string    $option     output options: cat_name | cat_alias
     * @param mixed     $format     output formats: string | array
     * @return string
     */
    private function _getPath($dirId, $option='cat_name', $format='string') {
        return parent::getPath($dirId, $option, $format);
    }

    /**
     * To calculate the directory content
     * @param string $path folder's/dir's path
     */
    private function _countFiles($path) {
        $cnt = 0;
        $fs = array();
        $fs = glob($path . '/*.*');
        if ($fs != FALSE) {
            foreach ($fs as $f) {
                if ($this->_validFile($f))
                    $cnt++;
                else
                    continue;
            }
        }
        $sd = array();
        $sd = glob($path . '/*');
        if ($sd != FALSE)
            foreach ($sd as $d) {
                $cnt += $this->_countFiles($d);
            }
        return $cnt;
    }

    /**
     * To synchronize between physical gallery contents and database
     * @param string    $path   path to file or folder
     * @param int       $pid    current parent ID
     * @param string    $cfg    module's configuration
     */
    private function _synchro($path, $pid) {
        $modx = $this->modx;
        $e2g = $this->e2g;
        $lng = $this->lng;

        // goldsky -- alter the maximum execution time
        if (function_exists('set_time_limit'))
            @set_time_limit(0);

        $timeStart = microtime(TRUE);
        /**
         * STORE variable arrays for synchronizing comparison
         */
        // MySQL Dir list
        $selectDirs = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE parent_id=' . $pid;
        $querySelectDirs = mysql_query($selectDirs);
        if (!$querySelectDirs) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs;
            return FALSE;
        }

        $mdirs = array();
        while ($l = mysql_fetch_array($querySelectDirs, MYSQL_ASSOC)) {
            $mdirs[$l['cat_name']]['id'] = $l['cat_id']; // goldsky -- to be connected between db <--> fs
            $mdirs[$l['cat_name']]['name'] = $l['cat_name'];
        }
        mysql_free_result($querySelectDirs);

        // MySQL File list
        $selectFiles = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE dir_id=' . $pid;
        $querySelectFile = mysql_query($selectFiles);
        if (!$querySelectFile) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
            return FALSE;
        }

        $mfiles = array();
        while ($l = mysql_fetch_array($querySelectFile, MYSQL_ASSOC)) {
            $mfiles[$l['filename']]['id'] = $l['id'];
            $mfiles[$l['filename']]['name'] = $l['filename'];
        }

        mysql_free_result($querySelectFile);

        /**
         * goldsky -- if there is no index.html inside folders, this will create it.
         */
        $this->_createsIndexHtml($path, $lng['indexfile']);

        $fs = array();
        $fs = @glob($path . '*'); // goldsky -- DO NOT USE a slash here!
        natsort($fs);

        /**
         * READ the real physical objects, store into database
         */
        if ($fs != FALSE) {
            foreach ($fs as $filePath) {
                // goldsky -- adds output buffer to avoid PHP's memory limit
                ob_start();

                $name = $this->_basenameSafe($filePath);
                $name = $this->_e2gEncode($name);
                if ($this->_validFolder($filePath)) { // as a folder/directory
                    if ($name == '_thumbnails')
                        continue;
                    if (isset($mdirs[$name])) {
                        if (!$this->_synchro($filePath . '/', $mdirs[$name]['id'])) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $filePath;
                            return FALSE;
                        }
                        unset($mdirs[$name]);
                    } else { // as ALL folder and file children of the current directory
                        /**
                         * INSERT folder's and file's names into database
                         */
                        if (!$this->_addAll($filePath . '/', $pid)) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $filePath;
                            return FALSE;
                        }
                    }
                }
                // as an allowed file in the current directory
                elseif ($this->_validFile($filePath)) {
                    if (isset($mfiles[$name])) {
                        // goldsky -- add the resizing of old images
                        $inf = @getimagesize($filePath);
                        $newInf = array();
                        // RESIZE
                        if ($e2g['resize_old_img'] == '1') {
                            $newInf = $this->_resizeImg($filePath, $inf, $e2g['maxw'], $e2g['maxh'], $e2g['maxthq']);
                        } else {
                            clearstatcache();
                            $newInf = $inf;
                            $newInf['size'] = filesize($filePath);
                            $newInf['time'] = filemtime($filePath);
                        }

                        $size = $newInf['size'];
                        $width = $newInf[0];
                        $height = $newInf[1];
                        $time = $newInf['time'];

                        $updateFile = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                . "SET size='$size'"
                                . ", width='$width'"
                                . ", height='$height'"
                                . ", last_modified='$time'"
                                . ", modified_by='" . $modx->getLoginUserID() . "' "
                                . "WHERE filename='$name'"
                        ;

                        $queryUpdateFile = mysql_query($updateFile);
                        if (!$queryUpdateFile) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFile;
                            return FALSE;
                        }

                        // goldsky -- if this already belongs to a file in the record, skip it!
                        unset($mfiles[$name]);
                    } else {
                        /**
                         * INSERT filename into database
                         */
                        if (!$this->_addFile($filePath, $pid)) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $filePath;
                            return FALSE;
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
            }
        } // if ($fs!=FALSE)
        /**
         * UNMATCHED comparisons action
         */
        // Deleted physical dirs, DELETE record from database
        if (isset($mdirs) && count($mdirs) > 0) {
            require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
            $tree = new TTree();
            $tree->table = $modx->db->config['table_prefix'] . 'easy2_dirs';
            foreach ($mdirs as $key => $value) {
                $ids = $tree->delete($value['id']);
                $implodedDirIds = implode(',', $ids);
                $selectFiles = 'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE dir_id IN(' . $implodedDirIds . ')';
                $querySelectFiles = mysql_query($selectFiles);
                if (!$querySelectFiles) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
                    return FALSE;
                }
                $fileIds = array();
                while ($l = mysql_fetch_row($querySelectFiles)) {
                    $fileIds[] = $l[1];
                }
                mysql_free_result($querySelectFiles);

                if (count($fileIds) > 0) {
                    $implodedFileIds = implode(',', $fileIds);
                    $deleteFileComments = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                            . 'WHERE file_id IN(' . $implodedFileIds . ')';
                    $queryDeleteFileComments = mysql_query($deleteFileComments);
                    if (!$queryDeleteFileComments) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFileComments;
                        return FALSE;
                    }
                }

                $deleteFiles = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'WHERE dir_id IN(' . $implodedDirIds . ')';
                $queryDeleteFiles = mysql_query($deleteFiles);
                if (!$queryDeleteFiles) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFiles;
                    return FALSE;
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

            $deleteFiles = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE id IN(' . $implodedFileIds . ')';
            $queryDeleteFiles = mysql_query($deleteFiles);
            if (!$queryDeleteFiles) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFiles;
                return FALSE;
            }

            $deleteFileComments = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                    . 'WHERE file_id IN(' . $implodedFileIds . ')';
            $queryDeleteFileComments = mysql_query($deleteFileComments);
            if (!$queryDeleteFileComments) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFileComments;
                return FALSE;
            }
        }

        $timeEnd = microtime(TRUE);
        $timeTotal = $timeEnd - $timeStart;
        if ($e2g['e2g_debug'] == '1') {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . "Syncronized $path in $timeTotal seconds\n";
        }
        return TRUE;
    }

    /**
     * to check the existance of filename/folder in the file system.<br />
     * if exists, this will add numbering into the uploaded files.
     */
    private function _singleFile($name, $pid) {
        $modx = $this->modx;
        $selectCheck = 'SELECT filename FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE filename = \'' . $name . '\' AND dir_id = \'' . $pid . '\'';
        $queryCheck = @mysql_query($selectCheck);
        while ($rowCheck = @mysql_fetch_array($queryCheck)) {
            $fetchRow[$rowCheck['filename']] = $rowCheck['filename'];
        }
        mysql_free_result($queryCheck);

        if (isset($fetchRow[$name])) {
            $ext = substr($name, strrpos($name, '.'));
            $filename = substr($name, 0, -(strlen($ext)));
            $oldSuffix = end(@explode('_', $filename));
            $prefixFilename = substr($filename, 0, -(strlen($oldSuffix)) - 1);
            if (is_numeric($oldSuffix)) {
                $notNumberSuffix = '';
                $newNumberSuffix = (int) $oldSuffix + 1;
            } else {
                $notNumberSuffix = '_' . $oldSuffix;
                $newNumberSuffix = 1;
            }
            $newFilename = ( $prefixFilename != '' ? $prefixFilename . $notNumberSuffix : $filename ) . '_' . $newNumberSuffix . $ext;
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $name . ' exists, file was renamed to be ' . $newFilename;
        }
        else
            return $name;

        // recursive check
        $recursiveCheckSelect = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE filename=\'' . $newFilename . '\' AND dir_id = \'' . $pid . '\'';
        $recursiveCheckQuery = @mysql_query($recursiveCheckSelect);
        while ($recursiveCheckRow = @mysql_fetch_array($recursiveCheckQuery)) {
            $recursiveFetchRow[$recursiveCheckRow['filename']] = $recursiveCheckRow['filename'];
        }
        mysql_free_result($recursiveCheckQuery);
        if (isset($recursiveFetchRow[$newFilename])) {
            $recursiveNewFilename = $this->_singleFile($newFilename, $pid);
            if (!$recursiveNewFilename) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $name . ' exists, but file could not be renamed to be ' . $newFilename;
            } else
                $newFilename = $recursiveNewFilename;
        }

        return $newFilename;
    }

    /**
     * Check the valid characters in names
     * @param string    $characters The string to be checked
     * @param string    $line       Line number for debugging
     * @return bool     TRUE means BAD! | FALSE means GOOD!
     */
    private function _hasBadChar($characters, $line) {
        $lng = $this->lng;

        $badChars = array(
            "U+0000", "/", "\\", ":", "*", "?", "'", "\"", "<", ">", "|", ";"
            , "@", "=", "#", "&", "!", "*", "'", "(", ")", ",", "{", "}", ","
            , "^", "~", "[", "]", "`"
        );
        foreach ($badChars as $badChar) {
            if (strstr($characters, $badChar)) {
                $_SESSION['easy2err'][] = $line . ' : ' . $lng['char_bad'] . ' => <b>' . $characters . '</b>';
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
        $lng = $this->lng;
        $memUsage = memory_get_usage(true);
        $out = '<a>' . $lng['memory_usage'] . ' : ';
        if ($memUsage < 1024)
            $out .= $memUsage . " bytes";
        elseif ($memUsage < 1048576)
            $out .= round($memUsage / 1024, 2) . ' ' . $lng['kilobytes'];
        else
            $out.= round($memUsage / 1048576, 2) . ' ' . $lng['megabytes'];
        $out.= "</a>";
        return $out;
    }

    /**
     * Replace the basename function with this to grab non-unicode character.
     * @link http://drupal.org/node/278425#comment-2571500
     */
    private function _basenameSafe($path) {
        return parent::basenameSafe($path);
    }

    /**
     * To check the specified resource is a valid file.<br />
     * It will be checked against the folder validation first.
     * @author goldsky <goldsky@modx-id.com>
     */
    private function _validFile($filename) {
        return parent::validFile($filename);
    }

    /**
     * To check the specified resource is a valid folder, although it has a DOT in it.
     * @author goldsky <goldsky@modx-id.com>
     */
    private function _validFolder($foldername) {
        return parent::validFolder($foldername);
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
    private function _e2gEncode($text, $callback=FALSE) {
        return parent::e2gEncode($text, $callback);
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
    private function _e2gDecode($text, $callback=FALSE) {
        return parent::e2gDecode($text, $callback);
    }

    /**
     * get folders structure for select options.
     * @param   int     $parentid   Parent's ID
     * @param   bool    $selected   turn on the selected="selected" if the current folder is the selected folder
     * @param   string  $jsActions  Javascript's action
     * @return  string  The multiple options
     */
    private function _folderOptions($parentid=0, $selected=0, $jsActions=NULL) {
        $modx = $this->modx;

        $selectDirs = 'SELECT parent_id, cat_id, cat_name, cat_level '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE parent_id=' . $parentid;

        $querySelectDirs = mysql_query($selectDirs);
        if (!$querySelectDirs) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs;
            return FALSE;
        }

        $numDir = @mysql_num_rows($querySelectDirs);

        $childrenDirs = array();
        while ($l = mysql_fetch_array($querySelectDirs, MYSQL_ASSOC)) {
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
                    . ( ( $childDir['cat_id'] == $_GET['pid'] && $selected != 0 ) ? ' selected="selected"' : '' )
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
                    . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
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
                $output .= $this->_folderOptions($childDir['cat_id'], $selected, $jsActions);
            }
            mysql_free_result($querySelectSubFolders);
            //*********************************************************/
        } // foreach ($childrenDirs as $childDir)
        return $output;
    }

    /**
     * To return an options selection for tag
     * @param   string  $tag    the tag
     * @return  string  option selection
     */
    private function _tagOptions($tag) {
        $modx = $this->modx;

        // Directory
        $selectDirTags = 'SELECT DISTINCT cat_tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs ';
        $queryDirTags = mysql_query($selectDirTags);

        if (!$queryDirTags) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirTags;
            return FALSE;
        }

        $numDirTags = mysql_num_rows($queryDirTags);

        while ($l = mysql_fetch_array($queryDirTags)) {
            if ($l['cat_tag'] == '' || $l['cat_tag'] == NULL)
                continue;
            $tagOptions[] = $l['cat_tag'];
        }

        // File
        $selectFileTags = 'SELECT DISTINCT tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';
        $queryFileTags = mysql_query($selectFileTags);

        if (!$queryFileTags) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileTags;
            return FALSE;
        }

        $numFileTags = mysql_num_rows($queryFileTags);

        while ($l = mysql_fetch_array($queryFileTags)) {
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
     * To get directory's information
     * @param  int    $dirId  gallery's ID
     * @param  string $field  database field
     * @return mixed  the directory's data in an array
     */
    private function _getDirInfo($dirId, $field) {
        return parent::getDirInfo($dirId, $field);
    }

    /**
     * To get file's information
     * @param  int    $fileId  file's ID
     * @param  string $field  database field
     * @return mixed  the file's data in an array
     */
    private function _getFileInfo($fileId, $field) {
        return parent::getFileInfo($fileId, $field);
    }

    /**
     * @author Schoschie (nh t ngin dott de)
     * @link http://www.php.net/manual/en/features.file-upload.errors.php#90522
     * @param   int     $error_code
     * @return  string  The error message
     */
    private function _fileUploadErrorMessage($error_code) {
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
     * @param   string  $file   filename
     * @param   string  $path   starting path
     * @return  bool    true/FALSE
     * @author  goldsky <goldsky@modx-id.com>
     * @todo    unziping the non-latin file
     */
    private function _unzip($file, $path) {
        $modx = $this->modx;
        $lng = $this->lng;
        $e2gEncode = $this->e2gModCfg['e2g_encode'];
        $e2gDebug = $this->e2gModCfg['e2g_debug'];

        if ($e2gEncode == 'UTF-8 (Rin)') {
            include_once E2G_MODULE_PATH . 'includes/UTF8-2.1.0/UTF8.php';
            include_once E2G_MODULE_PATH . 'includes/UTF8-2.1.0/ReflectionTypehint.php';
        }

        $r = substr($path, strlen($path) - 1, 1);
        if ($e2gEncode == 'UTF-8 (Rin)') {
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
        if ($zipOpen === TRUE) {
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
                    if ($e2gEncode == 'none') {
                        $r = substr($zipEntryName, strlen($zipEntryName) - 1, 1);
                    }
                    if ($e2gEncode == 'UTF-8') {
                        $zipEntryName = utf8_decode($zipEntryName);
                        $r = substr($zipEntryName, strlen($zipEntryName) - 1, 1);
                    }
                    if ($e2gEncode == 'UTF-8 (Rin)') {
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
                                $unzipDirs[] = $modx->stripAlias($unzipDir);
                            }
                            $implodedDir = @implode('/', $unzipDirs);
                            $mkdir = mkdir($path . $this->_e2gDecode($implodedDir), 0777);
                            if (!$mkdir)
                                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['unzip_dir_err'] . ' <b>' . $path . $zipEntryName . '</b>';
                            else {
                                $dirCount++;
                                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dirs_uploaded'] . ' ' . $path . $zipEntryName;
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
                            $unzipFiles[] = $modx->stripAlias($unzipFile);
                        }
                        $implodedFile = @implode('/', $unzipFiles);
                        $fd = fopen($path . $this->_e2gDecode($implodedFile), 'w');
                        if ($fd) {
                            fwrite($fd, $zipContent);
                            fclose($fd);
                            $this->_changeModOwnGrp('file', $path . $implodedFile);

                            $fileCount++;
                            if ($e2gDebug == '1')
                                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['files_uploaded'] . ' ' . $path . $zipEntryName;
                        }
                        else {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['unzip_file_err'] . ' <b>' . $path . $zipEntryName . '</b>';
                        }
                    }
                    ob_end_clean();
                } // for($i = 0; $i < $zip->numFiles; $i++)
            } // if ( $zip->numFiles > 0)

            $zip->close();
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $dirCount . ' ' . $lng['dirs_uploaded'] . '.';
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $fileCount . ' ' . $lng['files_uploaded'] . '.';

            return TRUE;
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' Error : ' . $lng['zip_open_err'] . ' <b>' . $file . '</b>';
            return FALSE;
        }
    }

    /**
     * To make an Unauthorized page to avoid direct access to the folder
     * @param   string  $dir    path
     * @param   string  $text   language string
     * @return  mixed file creation
     */
    private function _createsIndexHtml($dir, $text) {
        if (!file_exists(realpath($dir . 'index.html'))) {
            // goldsky -- adds a cover file
            $indexHtml = $dir . 'index.html';
            $fh = fopen($indexHtml, 'w');
            if (!$fh)
                $_SESSION['easy2err'][] = __LINE__ . " : Could not open file " . $indexHtml;
            else {
                fwrite($fh, htmlspecialchars_decode($text));
                fclose($fh);
                $this->_changeModOwnGrp('file', $indexHtml);
            }
        }
    }

    /**
     * Invoking the script with plugin, at any specified places.
     * @param   string  $e2gEvtName     event trigger.
     * @param   mixed   $e2gEvtParams   parameters array: depends on the event trigger.
     * @return  mixed   if TRUE, will return the indexfile. Otherwise this will return FALSE.
     */
    private function _plugin($e2gEvtName, $e2gEvtParams=array()) {
        return parent::plugin($e2gEvtName, $e2gEvtParams);
    }

    /**
     * Upload multiple files
     * @param   string  $post   file's information
     * @param   string  $files  file's or zipfile's object
     * @return  mixed   FALSE on failure or return report string on succeed
     */
    private function _uploadAll($gdir, $post, $files) {
        $modx = $this->modx;
        $e2g = $this->e2g;
        $lng = $this->lng;
//        $gdir = $this->e2gModCfg['gdir'];
        $newParent = !empty($post['newparent']) ? $post['newparent'] : 1;

        if (empty($files['img']['tmp_name'][0]) && empty($files['zip']['tmp_name'][0])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['upload_err'] . ' : ' . $lng['upload_empty'];
            return FALSE;
        }

        // CREATE PATH
//        $path = $this->_getPath($newParent);
//        $gdir .= $path;
        // UPLOAD IMAGES
        if (!empty($files['img']['tmp_name'][0])) {
            $j = 0;
            $countFiles = count($files['img']['tmp_name']);
            for ($i = 0; $i < $countFiles; $i++) {
                if (!is_uploaded_file($files['img']['tmp_name'][$i])) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['upload_err'] . ' ' . $files['img']['name'][$i];
                    continue;
                }
                if (!preg_match('/^image\//i', $files['img']['type'][$i])) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['type_err'] . ' ' . $files['img']['type'][$i];
                    continue;
                }

                $inf = @getimagesize($files['img']['tmp_name'][$i]);
                if ($inf[2] > 3)
                    continue;
                $newInf = $this->_resizeImg($files['img']['tmp_name'][$i], $inf, $e2g['maxw'], $e2g['maxh'], $e2g['maxthq']);

                // converting non-latin names with MODx's stripAlias function
                $files['img']['name'][$i] = $modx->stripAlias(trim($files['img']['name'][$i]));

                /**
                 * CHECK the existing filenames inside the system.
                 * If exists, amend the filename with number
                 */
                $filteredName = $this->_singleFile($files['img']['name'][$i], $newParent);

                $insertFile = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'SET dir_id=\'' . $newParent . '\''
                        . ', filename=\'' . mysql_real_escape_string($filteredName) . '\''
//                        . ', size=\'' . (int) $files['img']['size'][$i] . '\''
                        . ', size=\'' . $newInf['size'] . '\''
                        . ', width=\'' . $newInf[0] . '\''
                        . ', height=\'' . $newInf[1] . '\''
                        . ', alias=\'' . mysql_real_escape_string(htmlspecialchars($post['alias'][$i])) . '\''
                        . ', summary=\'' . mysql_real_escape_string(htmlspecialchars($post['summary'][$i])) . '\''
                        . ', tag=\'' . mysql_real_escape_string(htmlspecialchars($post['tag'][$i])) . '\''
                        . ', description=\'' . mysql_real_escape_string(htmlspecialchars($post['description'][$i])) . '\''
                        . ', date_added=NOW()';
                $queryInsertFile = mysql_query($insertFile);
                if (!$queryInsertFile) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertFile;
                    continue;
                }

                if (!move_uploaded_file($files['img']['tmp_name'][$i], '../' . $this->_e2gDecode($gdir . $filteredName))) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['upload_err'] . ' : ' . '../' . $this->_e2gDecode($gdir . $filteredName);
                }
                $this->_changeModOwnGrp('file', '../' . $this->_e2gDecode($gdir . $filteredName), FALSE);

                // invoke the plugin
                $this->_plugin('OnE2GFileUpload', array(
                    'fid' => mysql_insert_id()
                    , 'filename' => $filteredName
                    , 'pid' => $newParent
                ));

                $j++;
            } // for ($i = 0; $i < $countFiles; $i++)
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $j . ' ' . $lng['files_uploaded'] . '.';
        } // Upload images
        // UPLOAD ZIP
        if ($files['zip']['error'] == 0 && $files['zip']['size'] > 0) {
            if (!$err = $this->_unzip(
                            realpath($files['zip']['tmp_name'])
                            , realpath(MODX_BASE_PATH . $this->_e2gDecode($gdir))
                    )
            ) {
                $_SESSION['easy2err'][] = __LINE__ . ' <span class="warning"><b>' . $lng['upload_err']
                        . ($err === 0 ? 'Missing zip library (php_zip.dll / zip.so)' : '') . '</b></span><br /><br />';
            }

            @unlink($files['zip']['tmp_name']);
            if ($this->_synchro('../' . $e2g['dir'], 1)) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['synchro_suc'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['synchro_err'];
            }
            $this->_cleanCache();

            // invoke the plugin
            $this->_plugin('OnE2GZipUpload', array(
                'path' => realpath(MODX_BASE_PATH . $this->_e2gDecode($gdir))
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
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_dirfile_err'];
            return FALSE;
        }

        $countRes = array();
        // show dirs
        if (!empty($post['dir'])) {
            foreach ($post['dir'] as $k => $v) {
                if (!is_numeric($k)) {
                    continue;
                }
                $selectDirStatus = 'SELECT cat_name, cat_visible FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'WHERE cat_id=' . $k;
                $querySelectDirStatus = mysql_query($selectDirStatus);
                if (!$querySelectDirStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirStatus;
                    return FALSE;
                }

                $l = mysql_fetch_array($querySelectDirStatus, MYSQL_ASSOC);
                mysql_free_result($querySelectDirStatus);

                if ($l['cat_visible'] == '1') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_hiddennot_inverse_err'] . ' : ' . $l['cat_name'];
                    continue;
                }

                $queryUpdateDirStatus = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'SET cat_visible=\'1\' '
                        . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
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
                $selectFileStatus = 'SELECT filename, status FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'WHERE id=' . $k;
                $querySelectFileStatus = mysql_query($selectFileStatus);
                if (!$querySelectFileStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileStatus;
                    return FALSE;
                }

                $l = mysql_fetch_array($querySelectFileStatus, MYSQL_ASSOC);
                mysql_free_result($querySelectFileStatus);

                if ($l['status'] == '1') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_hiddennot_inverse_err'] . ' : ' . $l['filename'];
                    continue;
                }

                $updateFileStatus = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'SET status=\'1\' '
                        . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
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
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['ddb'] . ' ' . $lng['dirs_hiddennot_suc'] . '.';
        }
        if (!empty($countRes['fdb'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['fdb'] . ' ' . $lng['files_hiddennot_suc'] . '.';
        }
        return TRUE;
    }

    /**
     * Hide the checked list
     * @param   mixed   $post   list's variables
     * @return  mixed   TRUE | report
     */
    private function _hideChecked($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_dirfile_err'];
            return FALSE;
        }

        $countRes = array();
        // hide dirs
        if (!empty($post['dir'])) {
            foreach ($post['dir'] as $k => $v) {
                if (!is_numeric($k)) {
                    continue;
                }
                $selectDirStatus = 'SELECT cat_name, cat_visible FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'WHERE cat_id=' . $k;
                $querySelectDirStatus = mysql_query($selectDirStatus);
                if (!$querySelectDirStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirStatus;
                    return FALSE;
                }

                $l = mysql_fetch_array($querySelectDirStatus, MYSQL_ASSOC);
                mysql_free_result($querySelectDirStatus);

                if ($l['cat_visible'] == '0') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_hidden_inverse_err'] . ' : ' . $l['cat_name'];
                    continue;
                }

                $queryUpdateDirStatus = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'SET cat_visible=\'0\' '
                        . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
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
                $selectFileStatus = 'SELECT filename, status FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'WHERE id=' . $k;
                $querySelectFileStatus = mysql_query($selectFileStatus);
                if (!$querySelectFileStatus) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileStatus;
                    return FALSE;
                }

                $l = mysql_fetch_array($querySelectFileStatus, MYSQL_ASSOC);
                mysql_free_result($querySelectFileStatus);

                if ($l['status'] == '0') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_hidden_inverse_err'] . ' : ' . $l['filename'];
                    continue;
                }

                $updateFileStatus = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'SET status=\'0\' '
                        . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
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
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['ddb'] . ' ' . $lng['dirs_hidden_suc'] . '.';
        }
        if (!empty($countRes['fdb'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['fdb'] . ' ' . $lng['files_hidden_suc'] . '.';
        }
        return TRUE;
    }

    /**
     * Unhide the on clicked folder
     * @param   int     $dirId  list's variables
     * @return  mixed   TRUE | report
     */
    private function _showDir($dirId) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($dirId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dpath_err'];
            return FALSE;
        }
        if (!is_numeric($dirId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['numeric_err'];
            return FALSE;
        }
        $updateDir = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'SET cat_visible=\'1\' '
                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                . 'WHERE cat_id=' . $dirId;
        $queryUpdateDir = mysql_query($updateDir);
        if (!$queryUpdateDir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateDir;
            return FALSE;
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_hiddennot_suc'];

        return TRUE;
    }

    /**
     * Hide the on clicked folder
     * @param   int     $dirId  list's variables
     * @return  mixed   TRUE | report
     */
    private function _hideDir($dirId) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($dirId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dpath_err'];
            return FALSE;
        }
        if (!is_numeric($dirId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['numeric_err'];
            return FALSE;
        }
        $updateDir = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'SET cat_visible=\'0\' '
                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                . 'WHERE cat_id=' . $dirId;
        $queryUpdateDir = mysql_query($updateDir);
        if (!$queryUpdateDir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateDir;
            return FALSE;
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_hidden_suc'];

        return TRUE;
    }

    /**
     * Unhide the on clicked file
     * @param   int     $fileId list's variables
     * @return  mixed   TRUE | report
     */
    private function _showFile($fileId) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($fileId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['fpath_err'];
            return FALSE;
        }
        if (!is_numeric($fileId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['numeric_err'];
            return FALSE;
        }
        $updateFile = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'SET status=\'1\' '
                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                . 'WHERE id=' . $fileId;
        $queryUpdateFile = mysql_query($updateFile);
        if (!$queryUpdateFile) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFile;
            return FALSE;
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_hiddennot_suc'];

        return TRUE;
    }

    /**
     * Hide the on clicked file
     * @param   int     $fileId list's variables
     * @return  mixed   TRUE | report
     */
    private function _hideFile($fileId) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($fileId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['fpath_err'];
            return FALSE;
        }
        if (!is_numeric($fileId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['numeric_err'];
            return FALSE;
        }
        $updateFile = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'SET status=\'0\' '
                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                . 'WHERE id=' . $fileId;
        $queryUpdateFile = mysql_query($updateFile);
        if (!$queryUpdateFile) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateFile;
            return FALSE;
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_hidden_suc'];

        return TRUE;
    }

    /**
     * Delete the checked list
     * @param   mixed   $post   list's variables
     * @return  mixed   TRUE | report
     */
    private function _deleteChecked($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_dirfile_err'];
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
            require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
            $tree = new TTree();
            $tree->table = $modx->db->config['table_prefix'] . 'easy2_dirs';
            foreach ($post['dir'] as $k => $v) {
                // the numeric keys are the member of the database
                if (is_numeric($k)) {
                    $ids = $tree->delete((int) $k);
                    $implodedDirIds = implode(',', $ids);
                    $selectFileIds = 'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
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
                        $delComments = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                                . 'WHERE file_id IN(' . $implodedFileIds . ')';
                        $delCommentsQuery = mysql_query($delComments);
                        if (!$delCommentsQuery) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $delComments;
                            return FALSE;
                        }
                    }
                    $delFiles = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
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
                    $v = str_replace('../', '', $this->_e2gDecode($v));
                    $d = $this->_deleteAll('../' . $v . '/');

                    if (empty($d['e'])) {
                        $res['dfp'][0] += $d['d'];
                        $res['ffp'][0] += $d['f'];
                    } else {
                        $res['dfp'][1]++;
                    }
                }
            } // foreach ($post['dir'] as $k => $v)
            if ($res['dfp'][0] == 0 && $res['ddb'][0] == 0) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dirs_delete_err'];
            } elseif ($res['dfp'][0] == $res['ddb'][0]) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['dfp'][0] . ' ' . $lng['dirs_deleted'] . '.';
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ddb'][0] . ' ' . $lng['dirs_deleted_fdb'] . '.';
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['dfp'][0] . ' ' . $lng['dirs_deleted_fhdd'] . '.';
            }
        } // if (!empty($post['dir']))
        // Delete images
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                // the numeric keys are the member of the database
                if (is_numeric($k)) {
                    $selectFileId = 'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $k;
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
                        $deleteComments = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                                . 'WHERE file_id IN(' . implode(',', $fileIds) . ')';
                        $queryDeleteComments = mysql_query($deleteComments);
                        if (!$queryDeleteComments) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteComments;
                            return FALSE;
                        }
                    }
                    $deleteFile = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $k;
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
                    $v = str_replace('../', '', $this->_e2gDecode($v));
                    if (@unlink('../' . $v)) {
                        $res['ffp'][0]++;
                    } else {
                        $res['ffp'][1]++;
                    }
                }
            } // foreach ($post['im'] as $k => $v)
            if ($res['ffp'][0] == 0 && $res['fdb'][0] == 0) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['files_delete_err'];
            }
        } // if (!empty($post['im']))
        if (!empty($res['ffp']) || !empty($res['fdb'])) {
            if ($res['ffp'][0] == $res['fdb'][0]) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ffp'][0] . ' ' . $lng['files_deleted'] . '.';
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['fdb'][0] . ' ' . $lng['files_deleted_fdb'] . '.';
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ffp'][0] . ' ' . $lng['files_deleted_fhdd'] . '.';
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
        $modx = $this->modx;
        $lng = $this->lng;

        $zipContent = '';
        $_zipContent = array();

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['zip_select_none'];
            return FALSE;
        }

        ob_start();
        foreach ($post['dir'] as $k => $v) {
            $_zipContents[] = realpath(MODX_BASE_PATH . $this->_e2gDecode($v));
        }
        foreach ($post['im'] as $k => $v) {
            $_zipContents[] = realpath(MODX_BASE_PATH . $this->_e2gDecode($v));
        }

        $dirName = MODX_BASE_PATH . $gdir;
        $dirUrl = MODX_BASE_URL . $gdir;
        $zipName = $dirName . $this->_getDirInfo($pid, 'cat_name') . '.zip';
        $zipName = $this->_e2gDecode($zipName);

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
                        if ($this->_validFile($currentDir . $node)) {
                            $filesToAdd[] = $node;
                        }
                    }

                    $localDir = substr($currentDir, $cutFrom);
                    $zip->addEmptyDir($localDir);

                    foreach ($filesToAdd as $file) {
                        $zipAddFile = $zip->addFile($currentDir . $file, $localDir . $file);
                        if (!$zipAddFile) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['zip_create_err'] . '<br />' . $currentDir . $file;
                            continue;
                        }
                    } // foreach ($filesToAdd as $file)
                } // while (!empty($dirStack))
            } // if (is_dir($_zipContent))
            elseif ($this->_validFile($_zipContent)) {
                $_zipContent = realpath($_zipContent);
                $basename = end(@explode(DIRECTORY_SEPARATOR, $_zipContent));
//                    $basename = $this>_e2gEncode($basename);
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
        $modx = $this->modx;
        $lng = $this->lng;
        $rootDir = $this->e2gModCfg['dir'];

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_dirfile_err'];
            return FALSE;
        }
        if (trim($post['newparent']) == '' || empty($post['newparent'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_newdir_err'];
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

        $newDir = $rootDir . $this->_getPath($post['newparent']);

        // MOVING DIRS
        if (!empty($post['dir'])) {
            require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
            $tree = new TTree();
            $tree->table = $modx->db->config['table_prefix'] . 'easy2_dirs';
            foreach ($post['dir'] as $k => $v) {

                //************* FILE SYSTEM UPDATE *************//
                if (!empty($v)) {
                    $oldPath = $newPath = array();

                    $oldPath['origin'] = str_replace('../', '', $v);
                    $oldPath['basename'] = $this->_basenameSafe($v);
                    $oldPath['decoded'] = str_replace('../', '', $this->_e2gDecode($v));
                    $this->_changeModOwnGrp('dir', MODX_BASE_PATH . $oldPath['decoded']);

                    $newPath['origin'] = $newDir . $this->_basenameSafe($v);
                    $newPath['basename'] = $this->_basenameSafe($newPath['origin']);
                    $newPath['decoded'] = $this->_e2gDecode($newPath['origin']);

                    // initiate the variables inside _moveAll functions.
                    $moveDir = $this->_moveAll(MODX_BASE_PATH . $oldPath['decoded'], MODX_BASE_PATH . $newPath['decoded']);
                    //************* DATABASE UPDATE *************//
                    if (is_numeric($k)) {
                        $ids = $tree->replace((int) $k, (int) $post['newparent']);
                        // goldsky -- the same result with this:
                        // $ids = $tree->update((int) $k, $this->_basenameSafe($v), (int) $post['newparent']);
                        if (!$ids) {
                            if (!empty($moveDir['e'])) {
                                $_SESSION['easy2err'] = $moveDir['e'];
                                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_move_err'] . $oldPath['origin'] . ' => ' . $newPath['origin'];
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
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_move_err'] . ' "' . $newPath['origin'] . "'";
                    } else {
                        if ($e2gDebug == '1')
                            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . __LINE__ . ' : ' . $lng['dir_move_suc'] . '
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
                        $this->_changeModOwnGrp('dir', MODX_BASE_PATH . $newPath['decoded']);
                    }
                    //************** END OF FILE SYSTEM UPDATE **************//
                    $oldPath = $newPath = array();
                    unset($oldPath, $newPath);
                } // if (!empty($v))
            } // foreach ($post['dir'] as $k => $v)

            if ($res['dfp'][0] == 0 && $res['ddb'][0] == 0) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dirs_move_err'];
            } elseif ($res['dfp'][0] == $res['ddb'][0]) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ffp'][0] . ' ' . $lng['files_moved'] . '.';
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['dfp'][0] . ' ' . $lng['dirs_moved'] . '.';
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ddb'][0] . ' ' . $lng['dirs_moved_fdb'] . '.';
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['dfp'][0] . ' ' . $lng['dirs_moved_fhdd'] . '.';
            }

            // ****************** list names ****************** //
        } // if (!empty($post['dir']))
        // MOVING IMAGES
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                // move the file
                if (!empty($v)) {

                    $oldFile['origin'] = str_replace('../', '', $v);
                    $oldFile['basename'] = $this->_basenameSafe($v);
                    $oldFile['decoded'] = str_replace('../', '', $this->_e2gDecode($v));
                    $this->_changeModOwnGrp('file', MODX_BASE_PATH . $oldFile['decoded']);

                    $newFile['origin'] = $newDir . $this->_basenameSafe($v);
                    $newFile['basename'] = $this->_basenameSafe($newFile['origin']);
                    $newFile['decoded'] = $this->_e2gDecode($newFile['origin']);

                    if (is_file(realpath('../' . $newFile['decoded']))) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_move_err']
                                . ' <span style="color:red;">' . $this->_basenameSafe($v) . '</span>, ' . $lng['file_exists'] . '.';
                        continue;
                    } else {
                        $moveFile = @rename('../' . $oldFile['decoded'], '../' . $newFile['decoded']);
                        if ($moveFile) {
                            $res['file'][] = $newFile['decoded'];

                            // update the database
                            if (is_numeric($k)) {
                                $files = array();
                                $filesRes = mysql_query(
                                                'SELECT id, dir_id '
                                                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                                . 'WHERE id=' . (int) $k
                                );
                                while ($l = mysql_fetch_array($filesRes)) {
                                    $files[$l['id']]['dir_id'] = $l['dir_id'];
                                }
                                mysql_free_result($filesRes);

                                // reject moving to the same new parent
                                if ($post['newparent'] == $files[$k]['dir_id']) {
                                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_to_same_dir_err'];
                                    continue;
                                }

                                // reject overwrite
                                $filesCheckSelect = 'SELECT A.id ai, B.filename bf '
                                        . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_files A, '
                                        . $modx->db->config['table_prefix'] . 'easy2_files B '
                                        . 'WHERE A.filename=B.filename '
                                        . 'AND A.id=' . (int) $k . ' '
                                        . 'AND B.dir_id=' . (int) $post['newparent']
                                ;
                                $filesCheckQuery = mysql_query($filesCheckSelect);
                                while ($f = mysql_fetch_array($filesCheckQuery)) {
                                    $filesCheck[$f['ai']]['filename'] = $f['bf'];
                                }
                                mysql_free_result($filesCheckQuery);
                                if (isset($filesCheck[$k]['filename'])) {
                                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_move_err']
                                            . ' <span style="color:red;">' . $this->_basenameSafe($v) . '</span>, ' . $lng['file_exists'] . '.';
                                    continue;
                                }

                                $updateFile = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                        . ' SET dir_id=' . $post['newparent'] . ' '
                                        . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                                        . ' WHERE id=' . (int) $k;

                                if (mysql_query($updateFile)) {
                                    $res['fdb'][0]++;
                                } else {
                                    $res['fdb'][1]++;
                                }
                            }
                            $res['ffp'][0]++;

                            $this->_changeModOwnGrp('file', MODX_BASE_PATH . $newFile['decoded']);
                        } else {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_move_err'];
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
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['files_move_err'];
            } elseif ($res['ffp'][0] == $res['fdb'][0]) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ffp'][0] . ' ' . $lng['files_moved'] . '.';
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['fdb'][0] . ' ' . $lng['files_moved_fdb'] . '.';
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['ffp'][0] . ' ' . $lng['files_moved_fhdd'] . '.';
            }

            // ****************** list names ****************** //
            if (!empty($res['file'])) {
                for ($i = 0; $i < count($res['file']); $i++) {
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['file'][$i];
                }
            }
            // ****************** list names ****************** //
        } // if (!empty($post['im']))
    }

    /**
     * Delete directory by click action
     * @param   string  $get   variables from $_GET parameter
     * @return  mixed   FALSE | report
     */
    private function _deleteDir($get) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($get['dir_id']) && empty($get['dir_path'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dpath_err'];
            return FALSE;
        }

        // check the parameters
        if (!empty($get['dir_id'])) {
            $dirNameDb = $this->_getDirInfo($get['dir_id'], 'cat_name');
            $dirNamePath = $this->_basenameSafe($get['dir_path']);
            if ($dirNameDb != $dirNamePath) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dpath_err'] . ' => ' . $dirNameDb . ' != ' . $dirNamePath;
                return FALSE;
            }
        }

        // the numeric keys are the member of the database
        if (is_numeric($get['dir_id'])) {
            require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
            $tree = new TTree();
            $tree->table = $modx->db->config['table_prefix'] . 'easy2_dirs';
            $ids = $tree->delete((int) $get['dir_id']);
            $fileIds = array();
            $res = mysql_query(
                            'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE dir_id IN(' . implode(',', $ids) . ')'
            );
            while ($l = mysql_fetch_row($res)) {
                $fileIds[] = $l[0];
            }
            mysql_free_result($res);

            if (count($fileIds) > 0) {
                mysql_query(
                        'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                        . 'WHERE file_id IN(' . implode(',', $fileIds) . ')');
            }
            mysql_query('DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE dir_id IN(' . implode(',', $ids) . ')');
        }
        if (!empty($get['dir_path'])) {
            $dirPath = str_replace('../', '', $this->_e2gDecode($get['dir_path']));
            $res = $this->_deleteAll('../' . $dirPath . '/');
        }
        if (count($ids) > 0 && count($res['e']) == 0) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['d'] . ' ' . ($res['d'] == 1 ? $lng['dir_deleted'] : $lng['dirs_deleted']);
        } elseif (count($ids) > 0) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['d'] . ' ' . ($res['d'] == 1 ? $lng['dir_delete_fdb'] : $lng['dirs_delete_fdb']);
        } elseif (count($res['e']) == 0) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $res['d'] . ' ' . ($res['d'] == 1 ? $lng['dir_delete_fhdd'] : $lng['dirs_delete_fhdd']);
        } else {
            if (!empty($res['e']))
                $_SESSION['easy2err'] = $res['e'];
            if (!empty($tree->error))
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $tree->error;
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_delete_err'];
        }

        // invoke the plugin
        $this->_plugin('OnE2GFolderDelete', array(
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
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($get['file_id']) && empty($get['file_path'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['fpath_err'];
            return FALSE;
        }

        // the numeric keys are the member of the database
        if (is_numeric($get['file_id'])) {
            $fileName = $this->_getFileInfo($get['file_id'], 'filename');
            $deleteRecord = mysql_query('DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . $get['file_id']);
            mysql_query('DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments WHERE file_id=' . $get['file_id']);
        }
        if (!empty($get['file_path'])) {
            $baseName = $this->_basenameSafe($get['file_path']);
            $filePath = str_replace('../', '', $this->_e2gDecode($get['file_path']));
            $deletePhysical = @unlink('../' . $filePath);
        }
        if ($deleteRecord && $deletePhysical) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_delete'] . ' : ' . $fileName;
        } elseif ($deleteRecord) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_delete_fdb'] . ' : ' . $fileName;
        } elseif ($deletePhysical) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_delete_fhdd'] . ' : ' . $baseName;
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_delete_err'] . ' : ' . $filePath;
        }

        // invoke the plugin
        $this->_plugin('OnE2GFileDelete', array('fid' => $get['file_id']));

        return TRUE;
    }

    /**
     * To delete all of the thumbnail folder's content
     * @param   string  $dir   path
     * @param   string  $lng   language string
     * @return  Result report
     */
    private function _cleanCache() {
        $e2g = $this->e2g;
        $lng = $this->lng;

        $res = $this->_deleteAll('../' . $e2g['dir'] . '_thumbnails/');
        if (empty($res['e'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['cache_clean'] . ', ' . $res['f'] . ' ' . $lng['files_deleted'] . ', ' . $res['d'] . ' ' . $lng['dirs_deleted'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['cache_clean_err'] . ', ' . $res['f'] . ' ' . $lng['files_deleted'] . ', ' . $res['d'] . ' ' . $lng['dirs_deleted'];
            $_SESSION['easy2err'] = array_merge($_SESSION['easy2err'], $res['e']);
        }
        return $res;
    }

    /**
     * Save config into database or create the default file.
     * @param   array   $post           Configuration values
     * @param   bool    $build_default  Build default config file
     * @return  NULL
     */
    private function _saveConfig($post, $build_default=FALSE) {
        $modx = $this->modx;
        $lng = $this->lng;

        // overriding empty values
        $post['maxh'] = !empty($post['maxh']) ? $post['maxh'] : '0';
        $post['maxw'] = !empty($post['maxw']) ? $post['maxw'] : '0';
        $post['w'] = !empty($post['w']) ? $post['w'] : '140';
        $post['h'] = !empty($post['h']) ? $post['h'] : '140';

        if ($build_default) {
            // CHECK/CREATE DIRS
            $post['dir'] = preg_replace('/^\/?(.+)$/', '\\1', $post['dir']);
            $dirs = explode('/', substr($post['dir'], 0, -1));
            $npath = '..';
            foreach ($dirs as $dir) {
                $npath .= '/' . $dir;
                if ($this->_validFolder($npath) || empty($dir))
                    continue;

                if (mkdir($npath)) {
                    $this->_changeModOwnGrp('dir', $npath);
                } else {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_create_err'] . ' "' . $npath . "'";
                }
            }
            ksort($post);

            $c = "<?php\r\n\$e2gDefault = array (\r\n";
            foreach ($post as $k => $v) {
                $c .= "        '$k' => " . (is_numeric($v) ? $v : "'" . addslashes($v) . "'") . ",\r\n";
            }
            $c .= ");\r\n?>";
            $f = fopen(E2G_MODULE_PATH . 'includes/configs/default.config.easy2gallery.php', 'w+');
            fwrite($f, $c);
            fclose($f);
            return TRUE;
        } else {
            ksort($post);
            $deleteConfigs = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_configs ';
            $queryDeleteConfigs = mysql_query($deleteConfigs);
            if (!$queryDeleteConfigs) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteConfigs;
                return FALSE;
            }
            // else
            foreach ($post as $k => $v) {
                $insertConfigs = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_configs '
                        . 'SET `cfg_key`=\'' . $k . '\', `cfg_val`=\'' . $v . '\'';
                $queryInsertConfigs = mysql_query($insertConfigs);
                if (!$queryInsertConfigs) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertConfigs;
                    return FALSE;
                }
            }

            // delete the config file, because this will always be checked as an upgrade option
            if (file_exists(realpath(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php'))) {
                $unlinkConfigFile = unlink(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php');
                if (!$unlinkConfigFile) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['config_file_del_err'];
                } else {
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['config_file_del_suc'];
                }
            }

            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['config_update_suc'];
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
            $c .= "    '$k' => '" . htmlspecialchars($v, ENT_QUOTES);
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
        $modx = $this->modx;
        $lng = $this->lng;

        $insertIgnoredIp = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                . '(ign_date, ign_ip_address, ign_username, ign_email) '
                . 'VALUES(NOW(),\'' . $get['ip'] . '\',\'' . $get['u'] . '\',\'' . $get['e'] . '\')';
        $queryInsertIgnoredIp = mysql_query($insertIgnoredIp);
        if (!$queryInsertIgnoredIp) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertIgnoredIp;
            return FALSE;
        }
        $updateCommentStatus = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET STATUS=\'0\' WHERE ip_address=\'' . $get['ip'] . '\'';
        $queryUpdateCommentStatus = mysql_query($updateCommentStatus);
        if (!$queryUpdateCommentStatus) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateCommentStatus;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['ip_ignored_suc'];
        return TRUE;
    }

    /**
     * Ungnore IP by clicking a comment's icon
     * @param   mixed   $get    Variables from the hyperlink
     * @return  mixed   TRUE | report
     */
    private function _unignoreIp($get) {
        $modx = $this->modx;
        $lng = $this->lng;

        $deleteIgnoredIp = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                . 'WHERE ign_ip_address =\'' . $get['ip'] . '\'';
        $queryDeleteIgnoredIp = mysql_query($deleteIgnoredIp);
        if (!$queryDeleteIgnoredIp) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteIgnoredIp;
            return FALSE;
        }
        $updateCommentStatus = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET STATUS=\'1\' WHERE ip_address=\'' . $get['ip'] . '\'';
        $queryUpdateCommentStatus = mysql_query($updateCommentStatus);
        if (!$queryUpdateCommentStatus) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateCommentStatus;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['ip_unignored_suc'];
        return TRUE;
    }

    /**
     * Unignore all IPs from the check list
     * @param   mixed     $post   Variables from the check list
     * @return  mixed    TRUE | report
     */
    private function _unignoredAllIps($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        foreach ($_POST['unignored_ip'] as $uignIP) {
            $deleteIgnoredIp = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                    . 'WHERE ign_ip_address =\'' . $uignIP . '\'';
            $queryDeleteIgnoredIp = mysql_query($deleteIgnoredIp);
            if (!$queryDeleteIgnoredIp) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteIgnoredIp;
                return FALSE;
            }
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['ip_unignored_suc'] . ' ' . $uignIP;
        }

        return TRUE;
    }

    /**
     * Add tags to the checked dirs/files
     * @param   mixed   $post   Variables from the check list
     * @return  mixed   TRUE | report
     */
    private function _tagAddChecked($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['tag_input'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['tag_err_novalue'];
            return FALSE;
        }

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_dirfile_err'];
            return FALSE;
        }

        // store the multiple tag input as an array
        $xpldTagInputs = explode(',', $post['tag_input']);
        for ($c = 0; $c < count($xpldTagInputs); $c++) {
            $xpldTagInputs[$c] = htmlspecialchars(trim($xpldTagInputs[$c]), ENT_QUOTES);
        }

        // Folders
        if (!empty($post['dir'])) {
            foreach ($post['dir'] as $k => $v) {
                if (!empty($v)) {
                    // check the existing value first
                    $selectDirTags = 'SELECT cat_tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                            . 'WHERE cat_id=' . $k;
                    $querySelectDirTags = mysql_query($selectDirTags);

                    if (!$querySelectDirTags) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirTags;
                        return FALSE;
                    }
                    while ($l = mysql_fetch_array($querySelectDirTags)) {
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
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['tag_err_exist'] . ' : ' . $impldIntTag . ' (' . $this->_basenameSafe($v) . ')';
                    }

                    // store the new value of file's tag
                    if (count($newTags) > 0) {
                        $newTags = implode(', ', $newTags);
                        $updateDirTags = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                                . 'SET cat_tag=\'' . $newTags . '\' '
                                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
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
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['tag_suc_new'];
        } // if (!empty($post['dir']))
        // Files
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                if (!empty($v)) {
                    // check the existing value first
                    $selectFileTags = 'SELECT tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE id=' . $k;
                    $querySelectFileTags = mysql_query($selectFileTags);
                    if (!$querySelectFileTags) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileTags;
                        return FALSE;
                    }
                    while ($l = mysql_fetch_array($querySelectFileTags)) {
                        $fileTags = $l['tag'];
                    }
                    mysql_free_result($querySelectFileTags);

                    $xpldFileTags = array();
                    $xpldFileTags = explode(',', $fileTags);

                    for ($c = 0; $c < count($xpldFileTags); $c++) {
                        $xpldFileTags[$c] = htmlspecialchars(trim($xpldFileTags[$c]), ENT_QUOTES);
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
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['tag_err_exist'] . ' : ' . $intTags . ' (' . $this->_basenameSafe($v) . ')';
                    }

                    // store the new value of file's tag
                    if (count($newTags) > 0) {
                        $newTags = implode(', ', $newTags);
                        $updateFileTags = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                . 'SET tag=\'' . $newTags . '\' '
                                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
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
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['tag_suc_new'];
        } // if (!empty($post['im']))

        return TRUE;
    }

    /**
     * Remove tags to the checked dirs/files
     * @param   mixed   $post   Variables from the check list
     * @return  mixed   TRUE | report
     */
    private function _tagRemoveChecked($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['tag_input'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['tag_err_novalue'];
            return FALSE;
        }

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_dirfile_err'];
            return FALSE;
        }

        // store the multiple tag input as an array
        $xpldTagInputs = explode(',', $post['tag_input']);
        for ($c = 0; $c < count($xpldTagInputs); $c++) {
            $xpldTagInputs[$c] = htmlspecialchars(trim($xpldTagInputs[$c]), ENT_QUOTES);
        }

        // Folders
        if (!empty($post['dir'])) {
            foreach ($post['dir'] as $k => $v) {
                if (!empty($v)) {
                    // check the existing value first
                    $selectDirTags = 'SELECT cat_tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                            . 'WHERE cat_id=' . $k;
                    $querySelectDirTags = mysql_query($selectDirTags);
                    if (!$querySelectDirTags) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirTags;
                        return FALSE;
                    }
                    while ($l = mysql_fetch_array($querySelectDirTags)) {
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
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['tag_err_noexist'] . ' : ' . $intTags . ' (' . $this->_basenameSafe($v) . ')';
                    }

                    // store the new value of file's tag
                    $newTags = implode(', ', $newTags);
                    $updateDirTags = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                            . 'SET cat_tag=\'' . $newTags . '\' '
                            . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
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
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['tag_suc_remove'];
        } // if (!empty($post['dir']))
        // Files
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                if (!empty($v)) {
                    // check the existing value first
                    $selectFileTags = 'SELECT tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE id=' . $k;
                    $querySelectFileTags = mysql_query($selectFileTags);
                    if (!$querySelectFileTags) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileTags;
                        return FALSE;
                    }
                    while ($l = mysql_fetch_array($querySelectFileTags)) {
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
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['tag_err_noexist'] . ' : ' . $intTags . ' (' . $this->_basenameSafe($v) . ')';
                    }

                    // store the new value of file's tag
                    $newTags = implode(', ', $newTags);
                    $updateFileTags = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                            . 'SET tag=\'' . $newTags . '\' '
                            . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
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
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['tag_suc_remove'];
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
        $modx = $this->modx;
        $lng = $this->lng;

        if (count($post['comments']) == 0) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['comment_err_noselect'];
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
                $updateComment = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments ';
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
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['comment_suc_update'] . ' ' . $countRes . ' ' . $lng['comments'];
        return TRUE;
    }

    /**
     * To delete comments
     * @param   int     $id     comment's ID
     * @return  mixed   TRUE | report
     */
    private function _commentDelete($id) {
        $modx = $this->modx;

        $selectFileId = 'SELECT file_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'WHERE id=' . (int) $id;
        $fileId = mysql_result(mysql_query($selectFileId), 0, 0);
        if (!$fileId) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFileId;
            return FALSE;
        }

        $deleteComment = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'WHERE id=' . (int) $id;
        $queryDeleteComment = mysql_query($deleteComment);
        if (!$queryDeleteComment) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteComment;
            return FALSE;
        } else {
            $updateFileComment = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'SET comments=comments-1 '
                    . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
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
        $modx = $this->modx;
        $lng = $this->lng;

        $updateComment = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET approved=\'1\' ,status=\'1\' '
                . 'WHERE id=' . $id;
        $queryUpdateComment = mysql_query($updateComment);
        if (!$queryUpdateComment) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateComment;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['comment_suc_approved'];
        return TRUE;
    }

    /**
     * Hide comment
     * @param   int     $id     comment's ID
     * @return  bool    TRUE | FALSE
     */
    private function _commentHide($id) {
        $modx = $this->modx;
        $lng = $this->lng;

        $updateComment = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET status=\'0\' '
                . 'WHERE id=' . $id;
        $queryUpdateComment = mysql_query($updateComment);
        if (!$queryUpdateComment) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateComment;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['comment_suc_hide'];
        return TRUE;
    }

    /**
     * Unhide comment
     * @param   int     $id     comment's ID
     * @return  bool    TRUE | FALSE
     */
    private function _commentUnhide($id) {
        $modx = $this->modx;
        $lng = $this->lng;

        $updateComment = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET status=\'1\' '
                . 'WHERE id=' . $id;
        $query = mysql_query($updateComment);
        $queryUpdateComment = mysql_query($updateComment);
        if (!$queryUpdateComment) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateComment;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['comment_suc_unhide'];
        return TRUE;
    }

    /**
     * Save comment after edited
     * @param   string  $post   values from the input form
     * @return  bool    TRUE | FALSE
     */
    private function _commentSave($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $comment = (isset($post['comment']) ? $post['comment'] : $post['hiddencomment']);
        $updateComment = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET comment=\'' . $modx->db->escape(htmlspecialchars($comment, ENT_QUOTES)) . '\' '
                . ', date_edited=NOW() '
                . ', edited_by=\'' . $modx->getLoginUserID() . '\' '
                . 'WHERE id=' . $post['comid'];
        $queryUpdateComment = mysql_query($updateComment);
        if (!$queryUpdateComment) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateComment;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['comment_suc_update'];
        return TRUE;
    }

    /**
     * Save a new plugin
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _savePlugin($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_name'];
            return FALSE;
        }

        $eventsArray = array();
        $eventsString = '';
        if (!empty($post['events'])) {
            $eventsArray = $post['events'];
            $eventsString = implode(',', $eventsArray);
        }

        $insertPlugin = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_plugins '
                . 'SET name=\'' . htmlspecialchars(trim($post['name']), ENT_QUOTES) . '\' '
                . ', description=\'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\' '
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
            $insertPluginEvt = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_plugin_events '
                    . 'SET pluginid=\'' . $pluginId . '\' '
                    . ', evtid=\'' . $evtId . '\'';
            $queryInsertPluginEvt = mysql_query($insertPluginEvt);
            if (!$queryInsertPluginEvt) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertPluginEvt;
                return FALSE;
            }
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['plugin_add_suc'];
        return TRUE;
    }

    /**
     * Update changes onplugin editing
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _updatePlugin($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_name'];
            return FALSE;
        } elseif (empty($post['plugin_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_id'];
            return FALSE;
        }

        $eventsArray = array();
        $eventsString = '';
        if (!empty($post['events'])) {
            $eventsArray = $post['events'];
            $eventsString = implode(',', $eventsArray);
        }
        $pluginId = $post['plugin_id'];
        $updatePlugin = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_plugins '
                . 'SET name=\'' . htmlspecialchars(trim($post['name']), ENT_QUOTES) . '\' '
                . ', description=\'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\' '
                . ', events=\'' . $eventsString . '\' '
                . ', indexfile=\'' . urldecode(trim($post['index_file'])) . '\' '
                . ', disabled=\'' . (int) $post['disabled'] . '\' '
                . 'WHERE id=' . $post['plugin_id'];
        $queryUpdatePlugin = mysql_query($updatePlugin);
        if (!$queryUpdatePlugin) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updatePlugin;
            return FALSE;
        }

        $deletePluginEvents = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_plugin_events '
                . 'WHERE pluginid=\'' . $pluginId . '\'';
        $queryDeletePluginEvents = mysql_query($deletePluginEvents);
        if (!$queryDeletePluginEvents) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deletePluginEvents;
            return FALSE;
        }

        foreach ($eventsArray as $evtId) {
            $insertPluginEvents = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_plugin_events '
                    . 'SET pluginid=\'' . $pluginId . '\' '
                    . ', evtid=\'' . $evtId . '\'';
            $queryInsertPluginEvents = mysql_query($insertPluginEvents);
            if (!$queryInsertPluginEvents) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertPluginEvents;
                return FALSE;
            }
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['plugin_update_suc'];
        return TRUE;
    }

    /**
     * Save a new viewer/javascript library
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _saveViewer($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_name'];
            return FALSE;
        } elseif (empty($post['alias'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_alias'];
            return FALSE;
        }

        $insertViewer = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_viewers '
                . 'SET name=\'' . mysql_real_escape_string($post['name']) . '\''
                . ', alias=\'' . mysql_real_escape_string($post['alias']) . '\''
                . ', description=\'' . mysql_real_escape_string(htmlspecialchars($post['description'])) . '\''
                . ', disabled=\'' . mysql_real_escape_string(htmlspecialchars($post['disabled'])) . '\''
                . ', headers_css=\'' . mysql_real_escape_string(htmlspecialchars($post['headers_css'])) . '\''
                . ', autoload_css=\'' . mysql_real_escape_string(htmlspecialchars($post['autoload_css'])) . '\''
                . ', headers_js=\'' . mysql_real_escape_string(htmlspecialchars($post['headers_js'])) . '\''
                . ', autoload_js=\'' . mysql_real_escape_string(htmlspecialchars($post['autoload_js'])) . '\''
                . ', headers_html=\'' . mysql_real_escape_string(htmlspecialchars($post['headers_html'])) . '\''
                . ', autoload_html=\'' . mysql_real_escape_string(htmlspecialchars($post['autoload_html'])) . '\''
                . ', glibact=\'' . mysql_real_escape_string(htmlspecialchars($post['glibact'])) . '\''
                . ', clibact=\'' . mysql_real_escape_string(htmlspecialchars($post['clibact'])) . '\''
        ;
        $queryInservtViewer = mysql_query($insertViewer);
        if (!$queryInservtViewer) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertViewer;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['viewer_add_suc'];
        return TRUE;
    }

    /**
     * Update changes on viewer/javascript library editing
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _updateViewer($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_name'];
            return FALSE;
        } elseif (empty($post['alias'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_alias'];
            return FALSE;
        } elseif (empty($post['viewer_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_id'];
            return FALSE;
        }

        $updateViewer = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_viewers '
                . 'SET name=\'' . mysql_real_escape_string($post['name']) . '\''
                . ', alias=\'' . mysql_real_escape_string($post['alias']) . '\''
                . ', description=\'' . mysql_real_escape_string(htmlspecialchars($post['description'])) . '\''
                . ', disabled=\'' . mysql_real_escape_string(htmlspecialchars($post['disabled'])) . '\''
                . ', headers_css=\'' . mysql_real_escape_string(htmlspecialchars($post['headers_css'])) . '\''
                . ', autoload_css=\'' . mysql_real_escape_string(htmlspecialchars($post['autoload_css'])) . '\''
                . ', headers_js=\'' . mysql_real_escape_string(htmlspecialchars($post['headers_js'])) . '\''
                . ', autoload_js=\'' . mysql_real_escape_string(htmlspecialchars($post['autoload_js'])) . '\''
                . ', headers_html=\'' . mysql_real_escape_string(htmlspecialchars($post['headers_html'])) . '\''
                . ', autoload_html=\'' . mysql_real_escape_string(htmlspecialchars($post['autoload_html'])) . '\''
                . ', glibact=\'' . mysql_real_escape_string(htmlspecialchars($post['glibact'])) . '\''
                . ', clibact=\'' . mysql_real_escape_string(htmlspecialchars($post['clibact'])) . '\''
                . ' WHERE id=' . $post['viewer_id']
        ;
        $updateQuery = mysql_query($updateViewer);
        if (!$updateQuery) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateViewer;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['viewer_update_suc'];
        return TRUE;
    }

    /**
     * Save a new slideshow
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _saveSlideshow($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['slideshow_add_err'];
            return FALSE;
        }

        $countPost = count($post['name']);
        for ($i = 0; $i < $countPost; $i++) {
            // skipping the dummy form, the zero key
            if (empty($post['name'][$i]))
                continue;
            $insertSlideshow = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_slideshows '
                    . 'SET name=\'' . htmlspecialchars(trim($post['name'][$i]), ENT_QUOTES) . '\' '
                    . ', description=\'' . htmlspecialchars(trim($post['description'][$i]), ENT_QUOTES) . '\' '
                    . ', indexfile=\'' . urldecode(trim($post['index_file'][$i])) . '\' '
            ;
            $queryInsertSlideshow = mysql_query($insertSlideshow);
            if (!$queryInsertSlideshow) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertSlideshow;
                return FALSE;
            }
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['slideshow_add_suc'];
        return TRUE;
    }

    /**
     * Update changes on slideshow editing
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _updateSlideshow($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_name'];
            return FALSE;
        } elseif (empty($post['slideshow_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_id'];
            return FALSE;
        }

        $updateSlideshow = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_slideshows '
                . 'SET name=\'' . htmlspecialchars(trim($post['name']), ENT_QUOTES) . '\' '
                . ', description=\'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\' '
                . ', indexfile=\'' . urldecode(trim($post['index_file'])) . '\' '
                . 'WHERE id=' . $post['slideshow_id']
        ;
        $queryUpdateSlideshow = mysql_query($updateSlideshow);
        if (!$queryUpdateSlideshow) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateSlideshow;
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['slideshow_update_suc'];
        return TRUE;
    }

    /**
     * Create folder
     * @param string    $post       values from the input form
     * @param string    $gdir       directory path
     * @param int       $parentId  parent's ID
     * @return <type>
     */
    private function _createDir($post, $gdir, $parentId) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_create_err'] . ' : ' . $lng['err_empty_name'];
            return FALSE;
        }

        // converting non-latin names with MODx's stripAlias function
        $dirName = htmlspecialchars($modx->stripAlias($post['name']), ENT_QUOTES);
        $mkdir = mkdir('../' . $this->_e2gDecode($gdir . $dirName));

        if (!$mkdir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_create_err'] . ' : ' . $lng['err_undefined'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->_e2gDecode($gdir . $dirName);
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_created'] . ' : ' . $gdir . $dirName;

        $this->_changeModOwnGrp('dir', '../' . $this->_e2gDecode($gdir . $dirName));
        $this->_createsIndexHtml(MODX_BASE_PATH . $this->_e2gDecode($gdir . $dirName) . '/', $lng['indexfile']);

        require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
        $tree = new TTree();
        $tree->table = $modx->db->config['table_prefix'] . 'easy2_dirs';
        $id = $tree->insert($dirName, $parentId);
        if (!$id) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $tree->error;
            $tree->delete($id);
            return FALSE;
        }

        $updateDir = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'SET '
                . 'cat_alias = \'' . htmlspecialchars(trim($post['alias']), ENT_QUOTES) . '\''
                . ', cat_summary = \'' . htmlspecialchars(trim($post['summary']), ENT_QUOTES) . '\''
                . ', cat_tag = \'' . htmlspecialchars(trim($post['tag']), ENT_QUOTES) . '\''
                . ', cat_description = \'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\''
                . ', date_added=NOW() '
                . ', added_by=\'' . $modx->getLoginUserID() . '\' '
                . 'WHERE cat_id=' . $id;
        $queryUpdateDir = mysql_query($updateDir);
        if (!$queryUpdateDir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateDir;
            return FALSE;
        }

        // invoke the plugin
        $this->_plugin('OnE2GFolderCreateFormSave', array(
            'cat_id' => $id
            , 'cat_alias' => htmlspecialchars(trim($post['alias']), ENT_QUOTES)
            , 'cat_summary' => htmlspecialchars(trim($post['summary']), ENT_QUOTES)
            , 'cat_tag' => htmlspecialchars(trim($post['tag']), ENT_QUOTES)
            , 'cat_description' => htmlspecialchars(trim($post['description']), ENT_QUOTES)
            , 'date_added' => time()
            , 'added_by' => $modx->getLoginUserID()
        ));

        return TRUE;
    }

    /**
     * Update the database from the directory/folder editing form
     * @param string    $gdir   directory path
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _editDir($gdir, $post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['cat_id']) || $gdir == '') {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['update_err'];
            return FALSE;
        }

//        $pathToDir = $this->_getPath($post['parent_id']);
        $newDirName = $modx->stripAlias($post['new_cat_name']);
//        $newDirPath = $this->_e2gDecode($gdir . $pathToDir . $newDirName);
        $newDirPath = $this->_e2gDecode($gdir . $newDirName);
        $oldDirName = $post['cat_name'];
//        $oldDirPath = $this->_e2gDecode($gdir . $pathToDir . $oldDirName);
        $oldDirPath = $this->_e2gDecode($gdir . $oldDirName);

        $renameDirConfirm = FALSE;
        // check the CHMOD permission first, EXCLUDE the root gallery
        if ($post['cat_id'] != 1 && $oldDirName != $newDirName) {
            $renameDir = rename('../' . $oldDirPath, '../' . $newDirPath);
            if (!$renameDir) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['update_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $oldDirPath . ' => ' . $newDirPath;
                return FALSE;
            }
            $renameDirConfirm = TRUE;
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_rename_suc'];
            $this->_changeModOwnGrp('dir', '../' . $newDirPath);
        }

        $updateDir = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs SET ';

        if ($post['cat_id'] != '1' && $renameDirConfirm === TRUE) {
            $updateDir .= 'cat_name = \'' . htmlspecialchars(trim($newDirName), ENT_QUOTES) . '\', '; // trailing comma!
        }

        $updateDir .= 'cat_alias = \'' . htmlspecialchars(trim($post['alias']), ENT_QUOTES) . '\''
                . ', cat_summary = \'' . htmlspecialchars(trim($post['summary']), ENT_QUOTES) . '\''
                . ', cat_tag = \'' . htmlspecialchars(trim($post['tag']), ENT_QUOTES) . '\''
                . ', cat_description = \'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\''
                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                . ', last_modified=NOW() '
                . 'WHERE cat_id=' . $post['cat_id'];
        $queryUpdateDir = mysql_query($updateDir);
        if (!$queryUpdateDir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateDir;
            return FALSE;
        }
        // Adding web group access
        $saveWebGroupsAccess = $this->_saveWebGroupsAccess($post['webGroups'], 'dir', $post['cat_id']);

        // invoke the plugin
        $this->_plugin('OnE2GFolderEditFormSave', array(
            'cat_id' => $post['cat_id']
            , 'cat_name' => ($renameDirConfirm === TRUE ? htmlspecialchars(trim($newDirName), ENT_QUOTES) : $oldDirName )
            , 'cat_alias' => htmlspecialchars(trim($post['alias']), ENT_QUOTES)
            , 'cat_summary' => htmlspecialchars(trim($post['summary']), ENT_QUOTES)
            , 'cat_tag' => htmlspecialchars(trim($post['tag']), ENT_QUOTES)
            , 'cat_description' => htmlspecialchars(trim($post['description']), ENT_QUOTES)
            , 'modified_by=' => $modx->getLoginUserID()
            , 'last_modified' => time()
        ));

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_updated_suc'];
        return TRUE;
    }

    /**
     * Update the database from the file editing form
     * @param int       $gdir   parent directory
     * @param string    $post   values from the input form
     * @return bool     TRUE | FALSE
     */
    private function _editFile($gdir, $post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['file_id']) || $gdir == '') {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['update_err'];
            return FALSE;
        }

        $newFilename = $modx->stripAlias($post['newfilename']);
        $filename = $post['filename'];
        $ext = $post['ext'];
        $oldFilePath = $this->_e2gDecode($gdir . $filename . $ext);
        $newFilePath = $this->_e2gDecode($gdir . $newFilename . $ext);

        if ($newFilename != $filename) {
            // check the CHMOD permission first
            $this->_changeModOwnGrp('file', '../' . $oldFilePath);
            $renameFile = rename('../' . $oldFilePath, '../' . $newFilePath);
            $this->_changeModOwnGrp('file', '../' . $newFilePath);

            if (!$renameFile) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['update_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $oldFilePath . ' => ' . $newFilePath;
                return FALSE;
            }
        }

        $updateFile = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files SET ';
        if ($newFilename != $filename) {
            $updateFile .= 'filename = \'' . htmlspecialchars(trim($newFilename) . $ext, ENT_QUOTES) . '\', '; // trailing comma!
        }
        $updateFile .= 'alias = \'' . htmlspecialchars(trim($post['alias']), ENT_QUOTES) . '\''
                . ', summary = \'' . htmlspecialchars(trim($post['summary']), ENT_QUOTES) . '\''
                . ', tag = \'' . htmlspecialchars(trim($post['tag']), ENT_QUOTES) . '\''
                . ', description = \'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\''
                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                . ', last_modified=NOW() '
                . 'WHERE id=' . $post['file_id'];
        $queryUpdateFile = mysql_query($updateFile);
        if (!$queryUpdateFile) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['update_err'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $updateFile;
            return FALSE;
        }

        // Adding webGroup access
        $saveWebGroupsAccess = $this->_saveWebGroupsAccess($post['webGroups'], 'file', $post['file_id']);

        // invoke the plugin
        $this->_plugin('OnE2GFileEditFormSave', array(
            'fid' => $_GET['file_id']
            , 'filename' => ($newFilename != $filename ? $newFilename : $filename)
            , 'alias' => htmlspecialchars(trim($post['alias']), ENT_QUOTES)
            , 'summary' => htmlspecialchars(trim($post['summary']), ENT_QUOTES)
            , 'tag' => htmlspecialchars(trim($post['tag']), ENT_QUOTES)
            , 'description' => htmlspecialchars(trim($post['description']), ENT_QUOTES)
            , 'modified_by' => $modx->getLoginUserID()
            , 'last_modified' => time()
        ));

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['updated'];
        return TRUE;
    }

    /**
     * Get the plugin's number from the events list.<br />
     * This is used to simplified any number changes on the plugin's form by the developer
     * @param   string  $e2gEvtName Plugin Event Name
     * @return  int     The plugin's number
     */
    private function _getEventNum($e2gEvtName) {
        $lng = $this->lng;

        // include the event's names
        if (file_exists(realpath(E2G_MODULE_PATH . 'includes/configs/config.events.easy2gallery.php'))) {
            include E2G_MODULE_PATH . 'includes/configs/config.events.easy2gallery.php';
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['config_file_err_missing'];
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
        $modx = $this->modx;
        $lng = $this->lng;

        /**
         * Synchronizing the Manager Users
         */
        $e2gMgrGroupsArray = $modx->db->makeArray($modx->db->query(
                                'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr '));
        $countE2gMgrGroups = count($e2gMgrGroupsArray);
        for ($i = 0; $i < $countE2gMgrGroups; $i++) {
            $e2gMgrGroupIds[$e2gMgrGroupsArray[$i]['membergroup_id']] = $e2gMgrGroupsArray[$i]['membergroup_id'];
        }

        $modxMemberGroups = $modx->db->makeArray($modx->db->query(
                                'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'membergroup_names '));
        $countModxMemberGroups = count($modxMemberGroups);

        // adding non-exist modx groups into e2g groups
        for ($i = 0; $i < $countModxMemberGroups; $i++) {
            $modxMemberGroupIds[$modxMemberGroups[$i]['id']] = $modxMemberGroups[$i]['id'];

            if (isset($e2gMgrGroupIds[$modxMemberGroups[$i]['id']]))
                continue;

            $insertMgrUser = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr '
                    . 'SET membergroup_id=\'' . $modxMemberGroups[$i]['id'] . '\'';
            $queryInsertMgrUser = mysql_query($insertMgrUser);
            if (!$queryInsertMgrUser) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_mgr_synchro_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
                return FALSE;
            }
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_mgr_synchro_suc'] . ' : ' . $modxMemberGroups[$i]['name'];
        }

        // deleting e2g groups of non-exist modx groups
        foreach ($e2gMgrGroupIds as $id) {
            if (isset($modxMemberGroupIds[$id]))
                continue;

            $deleteMgrUser = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr '
                    . 'WHERE membergroup_id=\'' . $id . '\'';
            $queryDeleteMgrUser = mysql_query($deleteMgrUser);
            if (!$queryDeleteMgrUser) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_mgr_synchro_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Load the E2G's manager access for its pages
     * @return  bool    TRUE | FALSE
     */
    private function _loadE2gMgrSessions() {
        $modx = $this->modx;

        // loading the hyperlinks ($e2gPages)
        require E2G_MODULE_PATH . 'includes/configs/config.pages.easy2gallery.php';

        // loading the MODx's attributes
        $getUserInfo = $modx->getUserInfo($_SESSION['mgrInternalKey']);
        $userId = $getUserInfo['id'];
        $userPermissions = $modx->db->getValue(
                        'SELECT e.permissions FROM ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr e '
                        . 'LEFT JOIN ' . $modx->db->config['table_prefix'] . 'membergroup_names m '
                        . 'ON e.membergroup_id = m.id '
                        . 'LEFT JOIN ' . $modx->db->config['table_prefix'] . 'member_groups g '
                        . 'ON g.user_group=m.id '
                        . 'WHERE g.member=\'' . $userId . '\''
        );

        $_SESSION['e2gMgr']['permissions'] = $userPermissions;

        $userRole = $modx->db->getValue(
                        'SELECT role FROM ' . $modx->db->config['table_prefix'] . 'user_attributes '
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
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($webGroupIds) && !isset($type) && !isset($id)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['webgroup_save_err_empty_params'];
            return FALSE;
        }

        // delete the existing access ...
        $deleteWebAccess = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                . 'WHERE type=\'' . $type . '\' AND id=\'' . $id . '\' ';
        $queryDeleteWebAccess = mysql_query($deleteWebAccess);
        if (!$queryDeleteWebAccess) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteWebAccess;
            return FALSE;
        }

        // ... then insert back the new one
        foreach ($webGroupIds as $webGroupId) {
            $insertWebAccess = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
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
        $modx = $this->modx;
        $lng = $this->lng;

        $countPostMgrAccess = count($post['mgrAccess']);
        $mgrAccess = @implode(',', $post['mgrAccess']);

        $updateMgrUser = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr '
                . 'SET permissions=\'' . $mgrAccess . '\' '
                . 'WHERE membergroup_id=\'' . $post['group_id'] . '\'';
        $queryUpdateMgrUser = mysql_query($updateMgrUser);
        if (!$queryUpdateMgrUser) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_update_err'];
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $updateMgrUser;
            return FALSE;
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_update_suc'];

        return TRUE;
    }

    /**
     * Save the web access of directories/folders
     * @param string $post All values from the form
     * @return mixed Saving the access or FALSE on failing
     */
    private function _saveDirWebAccess($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $deleteDirWebAccess = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                . 'WHERE webgroup_id=\'' . $post['group_id'] . '\' AND type=\'dir\'';
        $queryDeleteDirWebAccess = mysql_query($deleteDirWebAccess);
        if (!$queryDeleteDirWebAccess) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_update_err'];
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteDirWebAccess;
            return FALSE;
        }

        $dirWebAccess = $post['dirWebAccess'];
        foreach ($dirWebAccess as $v) {
            $insertDirWebAccess = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                    . 'SET webgroup_id=\'' . $post['group_id'] . '\', type=\'dir\', id=\'' . $v . '\'';
            $queryInsertDirWebAccess = mysql_query($insertDirWebAccess);
            if (!$queryInsertDirWebAccess) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_update_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertDirWebAccess;
                return FALSE;
            }
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_update_suc'];

        return TRUE;
    }

    /**
     * Save the web access of files/images
     * @param string $post All values from the form
     * @return mixed Saving the access or FALSE on failing
     */
    private function _saveFileWebAccess($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $deleteFileWebAccess = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                . 'WHERE webgroup_id=\'' . $post['group_id'] . '\' AND type=\'file\' ';
        $queryDeleteFileWebAccess = mysql_query($deleteFileWebAccess);
        if (!$queryDeleteFileWebAccess) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_update_err'];
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $deleteFileWebAccess;
            return FALSE;
        }

        $fileWebAccess = $post['fileWebAccess'];
        foreach ($fileWebAccess as $v) {
            $insertFileWebAccess = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                    . 'SET webgroup_id=\'' . $post['group_id'] . '\', type=\'file\', id=\'' . $v . '\'';
            $queryInsertFileWebAccess = mysql_query($insertFileWebAccess);
            if (!$queryInsertFileWebAccess) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_update_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $insertFileWebAccess;
                return FALSE;
            }
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_update_suc'];

        return TRUE;
    }

    /**
     * Collecting directory's IDs of the specified web group ID
     * @param int       $webGroupId modx's web group ID
     * @return array    An array of the directory IDs
     */
    private function _dirWebGroupIds($webGroupId) {
        $modx = $this->modx;

        $dirWebGroups = $modx->db->makeArray($modx->db->query(
                                'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
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
//        $modx = $this->modx;
//
//        $dirWebGroupIds = $this->_dirWebGroupIds($webGroupId);
//        foreach ($dirWebGroupIds as $id) {
//            $e2gWebGroupDirNames[$id] = $modx->db->getValue($modx->db->query(
//                                    'SELECT cat_name FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
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
        $modx = $this->modx;

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
        $modx = $this->modx;

        $fileWebGroups = $modx->db->makeArray($modx->db->query(
                                'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
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
        $modx = $this->modx;

        $fileWebGroupIds = $this->_fileWebGroupIds($webGroupId);
        if (in_array($id, $fileWebGroupIds))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Check whether the OLD config.pages.easy2gallery.php file still exist, which means this is an upgrade
     * @return mixed    redirect to the config page to do the saving action, or nothing for TRUE
     */
    private function _checkConfigCompletion() {
        $lng = $this->lng;
        $blankIndex = $this->e2gModCfg['blank_index'];

        // loading the hyperlinks ($e2gPages)
        require E2G_MODULE_PATH . 'includes/configs/config.pages.easy2gallery.php';

        // delete the config file, because this will always be checked as an upgrade option
        if (file_exists(realpath(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php'))
                && $_GET['e2gpg'] != $e2gPages['config']['e2gpg']
        ) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['config_save_warning'];
            header('Location: ' . html_entity_decode($blankIndex . '&amp;e2gpg=' . $e2gPages['config']['e2gpg']));
        } else
            return TRUE;
    }

    /**
     * Change chmod and chown
     * @param string    $type           dir/file
     * @param string    $fullPath       dir/file path
     * @param bool      $changeMode     TRUE | FALSE to initiate chmod
     * @param bool      $changeGroup    TRUE | FALSE to initiate chown
     * @return bool     TRUE | FALSE
     */
    private function _changeModOwnGrp($type, $fullPath, $checkPreviousMode = TRUE, $changeGroup = TRUE) {
        $lng = $this->lng;
        $e2gDebug = $this->e2gModCfg['e2g_debug'];

        if ($checkPreviousMode) {
            $oldPermission = substr(sprintf('%o', fileperms($fullPath)), -4);
            clearstatcache();
        }

        if ($type == 'dir' && $oldPermission != '0755') {
            $newPermission = @chmod($fullPath, 0755);
            clearstatcache();
            if (!$newPermission && $e2gDebug == '1') {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['chmod_err'] . ' fullPath = ' . $fullPath;
                $_SESSION['easy2err'][] = __LINE__ . ' : oldPermission = ' . $oldPermission;
                return FALSE;
            }
        }

        if ($type == 'file') {
            $newPermission = @chmod($fullPath, 0644);
            clearstatcache();
            if ($checkPreviousMode === TRUE && $oldPermission != '0644') {
                if (!$newPermission && $e2gDebug == '1') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['chmod_err'] . ' fullPath = ' . $fullPath;
                    $_SESSION['easy2err'][] = __LINE__ . ' : oldPermission = ' . $oldPermission;
                    return FALSE;
                }
            }
        }

        if ($changeGroup === TRUE) {
            $modxPath = "index.php";
            $modxStat = stat($modxPath);
            clearstatcache();
            $ownerCore = $modxStat['uid'];
            $groupCore = $modxStat['gid'];
            $oldFullPath = $fullPath;
            $oldStat = stat($oldFullPath);
            clearstatcache();
            $ownerOld = $oldStat['uid'];
            $groupOld = $oldStat['gid'];

            if (!function_exists('chown')) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['chown_err'] . ' ' . $lng['chown_err_disabled'];
                return FALSE;
            }

            if ($ownerOld != $ownerCore || $groupOld != $groupCore) {
                // Set the user
                $newOwner = @chown($fullPath, $ownerCore);
                clearstatcache();
                if (!$newOwner && $e2gDebug == '1') {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['chown_err'] . ' fullPath = ' . $fullPath;
                    $_SESSION['easy2err'][] = __LINE__ . ' : old Owner/Group = ' . $ownerOld . '/' . $groupOld;
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * Browsing the gallery
     * @param   string  $rootDir    ROOT gallery's path
     * @param   string  $path       path array: $path['string'] & $path['link']
     * @param   int     $pid        parent's ID
     * @return  mixed   show the module's page
     */
    private function _readDir($rootDir, $path, $pid) {
        $modx = $this->modx;
        $lng = $this->lng;
        $index = $this->e2gModCfg['index'];
        $modThumbW = $this->e2gModCfg['mod_w'];
        $modThumbH = $this->e2gModCfg['mod_h'];
        $modThumbThq = $this->e2gModCfg['mod_thq'];

        $gdir = $rootDir . $path['string'];
        $pathString = $path['string'];
        $pidPath = $this->_getPath($pid);

        if ($pathString == $pidPath) {
            ####################################################################
            ####                      MySQL Dir list                        ####
            ####################################################################
            $selectDirs = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs' . ' '
                    . 'WHERE parent_id = ' . $pid . ' '
                    . 'ORDER BY cat_name ASC'
            ;
            $querySelectDirs = mysql_query($selectDirs);
            if (!$querySelectDirs) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs;
            }

            $row = array(); // for return
            $mdirs = array();
            while ($l = mysql_fetch_array($querySelectDirs, MYSQL_ASSOC)) {
                // goldsky -- store the array to be connected between db <--> fs
                $mdirs[$l['cat_name']]['id'] = $l['cat_id'];
                $mdirs[$l['cat_name']]['name'] = $l['cat_name'];
                $mdirs[$l['cat_name']]['alias'] = $l['cat_alias'];
                $mdirs[$l['cat_name']]['cat_tag'] = $l['cat_tag'];
                $mdirs[$l['cat_name']]['cat_visible'] = $l['cat_visible'];
                $mdirs[$l['cat_name']]['date_added'] = $l['date_added'];
                $mdirs[$l['cat_name']]['last_modified'] = $l['last_modified'];
            }
            mysql_free_result($querySelectDirs);
        }

        $rowClass = array(' class="gridAltItem"', ' class="gridItem"');
        $rowNum = 0;

        //******************************************************************/
        //***************** FOLDERS/DIRECTORIES/GALLERIES ******************/
        //******************************************************************/
        $dirs = @glob('../' . $this->_e2gDecode($gdir) . '*', GLOB_ONLYDIR);
        if (FALSE !== $dirs) {
            if (is_array($dirs))
                natsort($dirs);

            foreach ($dirs as $dirPath) {
                $dirName = $this->_basenameSafe($dirPath);
                $dirName = $this->_e2gEncode($dirName);
                $dirName = urldecode($dirName);
                if ($dirName == '_thumbnails')
                    continue;

                $dirStyledName = $dirName; // will be overridden for styling below
                $dirNameUrlDecodeDirname = urldecode($dirName);
                $dirPathRawUrlEncoded = str_replace('%2F', '/', rawurlencode($gdir . $dirName));
                $dirCountFiles = $this->_countFiles($dirPath);

                #################### Template placeholders #####################

                $dirAlias = '';
                $dirTag = '';
                $dirTagLinks = '';
                $dirCheckBox = '';
                $dirAttributes = '';
                $dirAttributeIcons = '';
                $dirHref = '';
                $dirIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder.png"
                    width="16" height="16" border="0" alt="" />
                ';
                $dirButtons = '';

                if (isset($mdirs[$dirName])) {
                    $dirId = $mdirs[$dirName]['id'];
                    $dirAlias = $mdirs[$dirName]['alias'];
                    $dirTag = $mdirs[$dirName]['cat_tag'];
                    $dirTagLinks = $this->_createTagLinks($dirTag);
                    $dirTime = $this->_getTime($mdirs[$dirName]['date_added'], $mdirs[$dirName]['last_modified'], $dirPath);

                    if (!isset($_GET['path'])) {
                        // Checkbox
                        $dirCheckBox = '
                <input name="dir[' . $dirId . ']" value="' . rawurldecode($dirPath) . '" type="checkbox" style="border:0;padding:0" />
                ';
                    }
                    if ($mdirs[$dirName]['cat_visible'] == '1') {
                        $dirStyledName = '<b>' . $dirName . '</b>';
//                        $dirLink = '<a href="' . $index . '&amp;pid=' . $mdirs[$dirName]['id'] . '">' . $dirStyledName . '</a>';
                        $dirHref = $index . '&amp;pid=' . $mdirs[$dirName]['id'];
                        $dirButtons = $this->_actionButton('hide_dir', array(
                                    'act' => 'hide_dir'
                                    , 'dir_id' => $dirId
                                    , 'pid' => $pid
                                ));
                    } else {
                        $dirStyledName = '<i>' . $dirName . '</i>';
                        $dirAttributes = '<i>(' . $lng['hidden'] . ')</i>';
                        $dirAttributeIcons = '
                <a href="' . $index . '&amp;act=show_dir&amp;dir_id=' . $dirId . '&amp;name=' . $dirName . '&amp;pid=' . $pid . '">
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16"
                        height="16" alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />
                </a>
                ';
                        $dirHref = $index . '&amp;pid=' . $mdirs[$dirName]['id'];
                        $dirButtons = $this->_actionButton('show_dir', array(
                                    'act' => 'show_dir'
                                    , 'dir_id' => $dirId
                                    , 'pid' => $pid
                                ));
                    }
                    // edit dir
                    $dirButtons .= $this->_actionButton('edit_dir', array(
                                'page' => 'edit_dir'
                                , 'dir_id' => $dirId
                                , 'pid' => $pid
                            ));
                    // unset this to leave the deleted dirs from file system.
                    unset($mdirs[$dirName]);
                } // if (isset($mdirs[$dirName]))
                else {
                    /**
                     * Exist dir in file system, but has not yet inserted into database
                     */
                    if (!isset($_GET['path'])) {
                        // Checkbox
                        $dirCheckBox = '
                    <input name="dir[d' . $rowNum . ']" value="' . rawurldecode($dirPath) . '" type="checkbox" style="border:0;padding:0" />
                    ';
                        // add dir
                        $dirButtons .= $this->_actionButton('add_dir', array(
                                    'act' => 'add_dir'
                                    , 'dir_path' => $dirPath
                                ));
                    }
                    $dirTime = date($this->e2g['mod_date_format'], filemtime($dirPath));
                    clearstatcache();
                    $dirStyledName = '<b style="color:gray">' . $dirName . '</b>';
                    $dirAttributes = '<i>(' . $lng['new'] . ')</i>';
                    $dirHref = $index . '&amp;path=' . (!empty($path['string']) ? $path['string'] : '') . $dirName;
                    $dirId = NULL;
                    $dirIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_add.png" width="16"
                    height="16" alt="' . $lng['add_to_db'] . '" border="0" />
                    ';
                }

                if (!empty($dirId)) {
                    $dirButtons .= $this->_actionButton('delete_dir', array(
                                'act' => 'delete_dir'
                                , 'dir_path' => $dirPath
                                , 'dir_id' => $dirId
                                    )
                                    , 'onclick="return confirmDeleteFolder();"'
                    );
                } else {
                    $dirButtons .= $this->_actionButton('delete_dir', array(
                                'act' => 'delete_dir'
                                , 'dir_path' => $dirPath
                                    )
                                    , 'onclick="return confirmDeleteFolder();"'
                    );
                }

                $row['dir']['rowNum'][] = $rowNum;
                $row['dir']['rowClass'][] = $rowClass[$rowNum % 2];
                $row['dir']['checkBox'][] = $dirCheckBox;
                $row['dir']['id'][] = $dirId;
                $row['dir']['gid'][] = empty($dirId) ? '' : '[id: ' . $dirId . ']';
                $row['dir']['name'][] = $dirName;
                $row['dir']['styledName'][] = $dirStyledName;
                $row['dir']['path'][] = $dirPath;
                $row['dir']['pathRawUrlEncoded'][] = $dirPathRawUrlEncoded;
                $row['dir']['alias'][] = $dirAlias;
                $row['dir']['title'][] = ( trim($dirAlias) != '' ? $dirAlias : $dirName);
                $row['dir']['tagLinks'][] = $dirTagLinks;
                $row['dir']['time'][] = $dirTime;
                $row['dir']['count'][] = $dirCountFiles;
                $row['dir']['attributes'][] = $dirAttributes;
                $row['dir']['attributeIcons'][] = $dirAttributeIcons;
                $row['dir']['href'][] = $dirHref;
                $row['dir']['buttons'][] = $dirButtons;
                $row['dir']['icon'][] = $dirIcon;
                $row['dir']['size'][] = '---';
                $row['dir']['w'][] = '---';
                $row['dir']['h'][] = '---';
                $row['dir']['mod_w'][] = $modThumbW;
                $row['dir']['mod_h'][] = $modThumbH;
                $row['dir']['mod_thq'][] = $modThumbThq;

                $rowNum++;
            } // foreach ($dirs as $dirPath)

            /**
             * Deleted dirs from file system, but still exists in database,
             * which have been left from the above unsetting.
             */
            if (isset($mdirs) && count($mdirs) > 0) {
                foreach ($mdirs as $v) {
                    $row['deletedDir']['rowNum'][] = $rowNum;
                    $row['deletedDir']['rowClass'][] = $rowClass[$rowNum % 2];
                    $row['deletedDir']['checkBox'][] = '
                    <input name="dir[' . $v['id'] . ']" value="dir[' . $v['id'] . ']" type="checkbox" style="border:0;padding:0" />
                        ';
                    $row['deletedDir']['id'][] = $v['id'];
                    $row['deletedDir']['gid'][] = '[id: ' . $v['id'] . ']';
                    $row['deletedDir']['name'][] = $v['name'];
                    $row['deletedDir']['styledName'][] = '<b style="color:red;"><u>' . $v['name'] . '</u></b>';
                    $row['deletedDir']['path'][] = '';
                    $row['deletedDir']['alias'][] = $v['alias'];
                    $row['deletedDir']['title'][] = ( trim($v['alias']) != '' ? $v['alias'] : $v['name']);
                    $row['deletedDir']['tagLinks'][] = $this->_createTagLinks($v['cat_tag']);
                    $row['deletedDir']['time'][] = $this->_getTime($v['date_added'], $v['last_modified'], '');
                    $row['deletedDir']['count'][] = intval("0");
                    $row['deletedDir']['link'][] = '<b style="color:red;"><u>' . $v['name'] . '</u></b>';
                    $row['deletedDir']['attributes'][] = '<i>(' . $lng['deleted'] . ')</i>';
                    $row['deletedDir']['attributeIcons'][] = '
                    <a href="' . $index . '&amp;act=delete_dir&amp;dir_id=' . $v['id'] . '"
                       onclick="return confirmDeleteFolder();">
                        <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/delete.png" border="0"
                             alt="' . $lng['delete'] . '" title="' . $lng['delete'] . '" />
                    </a>
                    ';
                    $row['deletedDir']['href'][] = '';

                    $row['deletedDir']['buttons'][] = '
                    <a href="' . $index . '&amp;act=delete_dir&amp;dir_id=' . $v['id'] . '"
                       onclick="return confirmDeleteFolder();">
                        <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/delete.png" border="0"
                             alt="' . $lng['delete'] . '" title="' . $lng['delete'] . '" />
                    </a>';
                    $row['deletedDir']['icon'][] = '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_delete.png"
                        width="16" height="16" border="0" alt="" />
                    ';
                    $row['deletedDir']['mod_w'][] = $modThumbW;
                    $row['deletedDir']['mod_h'][] = $modThumbH;
                    $row['deletedDir']['mod_thq'][] = $modThumbThq;

                    $rowNum++;
                } // foreach ($mdirs as $k => $v)
            } // if (isset($mdirs) && count($mdirs) > 0)
        } // if (FALSE !== $dirs)
        ############################# DIR LIST ENDS ############################


        if ($pathString == $pidPath) {
            ####################################################################
            ####                      MySQL File list                       ####
            ####################################################################
            $selectFiles = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE dir_id = ' . $pid;
            $querySelectFiles = mysql_query($selectFiles);
            if (!$querySelectFiles) {
                $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
                return FALSE;
            }
            $mfiles = array();
            while ($l = mysql_fetch_array($querySelectFiles, MYSQL_ASSOC)) {
                // goldsky -- store the array to be connected between db <--> fs
                $mfiles[$l['filename']]['id'] = $l['id'];
                $mfiles[$l['filename']]['filename'] = $l['filename'];
                $mfiles[$l['filename']]['size'] = $l['size'];
                $mfiles[$l['filename']]['width'] = $l['width'];
                $mfiles[$l['filename']]['height'] = $l['height'];
                $mfiles[$l['filename']]['alias'] = $l['alias'];
                $mfiles[$l['filename']]['tag'] = $l['tag'];
                $mfiles[$l['filename']]['date_added'] = $l['date_added'];
                $mfiles[$l['filename']]['last_modified'] = $l['last_modified'];
                $mfiles[$l['filename']]['status'] = $l['status'];
            }
            mysql_free_result($querySelectFiles);
        }

        //******************************************************************/
        //************* FILE content for the current directory *************/
        //******************************************************************/
        $files = @glob('../' . $this->_e2gDecode($gdir) . '*.*');
        if (FALSE !== $files) {
            if (is_array($files))
                natsort($files);

            foreach ($files as $filePath) {
                if ($this->validFolder($filePath))
                    continue;
                if (!$this->validFile($filePath))
                    continue;
// TODO: Clean up this UTF-8 mess when adding file
                $filename = $this->_basenameSafe($filePath);
                $filename = $this->_e2gEncode($filename);
                $fileStyledName = $filename; // will be overridden for styling below
//echo __LINE__ . ' : $filename = ' . $filename . '<br />';
                $fileNameUrlDecodeFilename = urldecode($filename);
//echo __LINE__ . ' : $fileNameUrlDecodeFilename = ' . $fileNameUrlDecodeFilename . '<br />';
                $filePathRawUrlEncoded = str_replace('%2F', '/', rawurlencode($gdir . $filename));
//echo __LINE__ . ' : $filePathRawUrlEncoded = ' . $filePathRawUrlEncoded . '<br />';
                #################### Template placeholders #####################

                $fileAlias = '';
                $fileTag = '';
                $fileTagLinks = '';
                $fileCheckBox = '';
                $fileAttributes = '';
                $fileAttributeIcons = '';
                $fileIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture.png" width="16" height="16" border="0" alt="" />
                ';
                $fileButtons = '';

                if (isset($mfiles[$filename])) {
                    $fileId = $mfiles[$filename]['id'];
                    $fileAlias = $mfiles[$filename]['alias'];
                    $fileTagLinks = $this->_createTagLinks($mfiles[$filename]['tag']);
                    if (!isset($_GET['path'])) {
                        // Checkbox
                        $fileCheckBox = '
                <input name="im[' . $fileId . ']" value="im[' . $fileId . ']" type="checkbox" style="border:0;padding:0" />
                ';
                    }
                    $tag = $mfiles[$filename]['tag'];
                    $fileSize = round($mfiles[$filename]['size'] / 1024);
                    $width = $mfiles[$filename]['width'];
                    $height = $mfiles[$filename]['height'];
                    $fileTime = $this->_getTime($mfiles[$filename]['date_added'], $mfiles[$filename]['last_modified'], $filePath);

                    if ($mfiles[$filename]['status'] == '1') {
                        $fileButtons = $this->_actionButton('hide_file', array(
                                    'act' => 'hide_file'
                                    , 'file_id' => $fileId
                                    , 'pid' => $pid
                                ));
                    } else {
                        $fileStyledName = '<i>' . $filename . '</i>';
                        $fileAttributes = '<i>(' . $lng['hidden'] . ')</i>';
                        $fileAttributeIcons = '
                <a href="' . $index . '&amp;act=show_file&amp;file_id=' . $fileId . '&amp;pid=' . $pid . '">
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png"
                        width="16" height="16" alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />
                </a>
                ';
                        $fileButtons = $this->_actionButton('show_file', array(
                                    'act' => 'show_file'
                                    , 'file_id' => $fileId
                                    , 'pid' => $pid
                                ));
                    }
                    $fileButtons .= $this->_actionButton('comments', array(
                                'page' => 'comments'
                                , 'file_id' => $fileId
                                , 'pid' => $pid
                            ));

                    $fileButtons .= $this->_actionButton('edit_file', array(
                                'page' => 'edit_file'
                                , 'file_id' => $fileId
                                , 'pid' => $pid
                            ));

                    unset($mfiles[$filename]);
                } else {
                    /**
                     * Existed files in file system, but not yet inserted into database
                     */
                    if (!isset($_GET['path'])) {
                        // Checkbox
                        $fileCheckBox = '
                <input name="im[f' . $rowNum . ']" value="im[f' . $rowNum . ']" type="checkbox" style="border:0;padding:0" />
                ';
                    }
                    $fileTime = date($this->e2g['mod_date_format'], filemtime($filePath));
                    $fileStyledName = '<span style="color:gray"><b>' . $filename . '</b></span>';
                    $fileAttributes = '<i>(' . $lng['new'] . ')</i>';
                    $fileId = NULL;
                    $fileIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_add.png" width="16" height="16" border="0" alt="" />
                ';
                    $fileAttributeIcons = '';
                    if (empty($path['string'])) {
                        // add file
                        $fileButtons .= $this->_actionButton('add_file', array(
                                    'act' => 'add_file'
                                    , 'file_path' => $filePathRawUrlEncoded
                                    , 'pid' => $pid
                                ));
                    } else {
                        $fileButtons = '';
                    }
                    $fileSize = round(filesize($filePath) / 1024);
                    list($width, $height) = @getimagesize($filePath);
                }

                $fileButtons .= $this->_actionButton('delete_file', array(
                            'act' => 'delete_file'
                            , 'pid' => $pid
                            , 'file_path' => $filePathRawUrlEncoded
                            , 'file_id' => $fileId
                                )
                                , 'onclick="return confirmDelete();"'
                );

                $row['file']['rowNum'][] = $rowNum;
                $row['file']['rowClass'][] = $rowClass[$rowNum % 2];
                $row['file']['checkBox'][] = $fileCheckBox;
                $row['file']['dirId'][] = $pid;
                $row['file']['id'][] = $fileId;
                $row['file']['fid'][] = empty($fileId) ? '' : '[id:' . $fileId . ']';
                $row['file']['name'][] = $filename;
                $row['file']['styledName'][] = $fileStyledName;
                $row['file']['alias'][] = $fileAlias;
                $row['file']['title'][] = ( trim($fileAlias) != '' ? $fileAlias : $filename);
                $row['file']['tagLinks'][] = $fileTagLinks;
                $row['file']['path'][] = '../' . $gdir;
                $row['file']['pathRawUrlEncoded'][] = $filePathRawUrlEncoded;
                $row['file']['time'][] = $fileTime;
                $row['file']['attributes'][] = $fileAttributes;
                $row['file']['attributeIcons'][] = $fileAttributeIcons;
                $row['file']['buttons'][] = $fileButtons;
                $row['file']['icon'][] = $fileIcon;
                $row['file']['size'][] = $fileSize;
                $row['file']['w'][] = $width;
                $row['file']['h'][] = $height;
                $row['file']['mod_w'][] = $modThumbW;
                $row['file']['mod_h'][] = $modThumbH;
                $row['file']['mod_thq'][] = $modThumbThq;

                $rowNum++;
            } // foreach ($files as $filePath)

            /**
             * Deleted files from file system, but still exists in database
             */
            if (isset($mfiles) && count($mfiles) > 0) {
                foreach ($mfiles as $k => $v) {
                    $row['deletedFile']['rowNum'][] = $rowNum;
                    $row['deletedFile']['rowClass'][] = $rowClass[$rowNum % 2];
                    $row['deletedFile']['checkBox'][] = '
                <input name="im[' . $v['id'] . ']" value="' . $v['id'] . '" type="checkbox" style="border:0;padding:0" />
                ';
                    $row['deletedFile']['dirId'][] = $pid;
                    $row['deletedFile']['id'][] = $v['id'];
                    $row['deletedFile']['fid'][] = '[id:' . $v['id'] . ']';
                    $row['deletedFile']['name'][] = $v['filename'];
                    $row['deletedFile']['styledName'][] = '<b style="color:red;"><u>' . $v['filename'] . '</u></b>';
                    $row['deletedFile']['alias'][] = $v['alias'];
                    $row['deletedFile']['title'][] = ( trim($v['alias']) != '' ? $v['alias'] : $v['filename']);
                    $row['deletedFile']['tagLinks'][] = $this->_createTagLinks($v['tag']);
                    $row['deletedFile']['path'][] = $gdir;
                    $row['deletedFile']['pathRawUrlEncoded'][] = str_replace('%2F', '/', rawurlencode($gdir . $v['filename']));
                    $row['deletedFile']['time'][] = $this->_getTime($v['date_added'], $v['last_modified'], '');
                    $row['deletedFile']['attributes'][] = '<i>(' . $lng['deleted'] . ')</i>';
                    $row['deletedFile']['attributeIcons'][] = '
                <a href="' . $index . '&amp;act=delete_file&amp;file_path=' . $filePathRawUrlEncoded
                            . (empty($fileId) ? '' : '&amp;file_id=' . $fileId) . '"
                   onclick="return confirmDelete();">
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/delete.png" border="0"
                         alt="' . $lng['delete'] . '" title="' . $lng['delete'] . '" />
                </a>
                    ';
                    $deletedFileButtons = $this->_actionButton('comments', array(
                                'page' => 'comments'
                                , 'file_id' => $v['id']
                                , 'pid' => $pid
                                    )
                    );
                    $deletedFileButtons .= $this->_actionButton('delete_file', array(
                                'act' => 'delete_file'
                                , 'file_id' => $v['id']
                                    )
                                    , 'onclick="return confirmDelete();"'
                    );

                    $row['deletedFile']['buttons'][] = $deletedFileButtons;
                    $row['deletedFile']['icon'][] = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_delete.png" width="16" height="16" border="0" alt="" />
                ';
                    $row['deletedFile']['size'][] = round($v['size'] / 1024);
                    $row['deletedFile']['w'][] = $v['width'];
                    $row['deletedFile']['h'][] = $v['height'];
                    $row['deletedFile']['mod_w'][] = $modThumbW;
                    $row['deletedFile']['mod_h'][] = $modThumbH;
                    $row['deletedFile']['mod_thq'][] = $modThumbThq;

                    $rowNum++;
                }
            }
        } // if (FALSE !== $files)
        ############################ FILE LIST ENDS ############################
        // return dir and file contents
        return $row;
    }

    /**
     * Browsing the gallery by tagging
     * @param   string  $tag    the tag's value
     * @return  mixed   show the module's page
     */
    private function _readTag($tag) {
        $modx = $this->modx;
        $lng = $this->lng;
        $gdir = $this->e2gModCfg['gdir'];
        $index = $this->e2gModCfg['index'];
        $modThumbW = $this->e2gModCfg['mod_w'];
        $modThumbH = $this->e2gModCfg['mod_h'];
        $modThumbThq = $this->e2gModCfg['mod_thq'];

        $rowClass = array(' class="gridAltItem"', ' class="gridItem"');
        $rowNum = 0;

        //******************************************************************/
        //***************** FOLDERS/DIRECTORIES/GALLERIES ******************/
        //******************************************************************/
        $selectDirs = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE cat_tag LIKE \'%' . $tag . '%\' '
                . 'ORDER BY cat_name ASC';
        $querySelectDirs = mysql_query($selectDirs);
        if (!$querySelectDirs) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs;
            return FALSE;
        }
        $row['dir'] = array();
        while ($l = mysql_fetch_array($querySelectDirs, MYSQL_ASSOC)) {
            // goldsky -- store the array to be connected between db <--> fs
            $row['dir']['parent_id'][] = $l['parent_id'];
            $row['dir']['id'][] = $l['cat_id'];
            $row['dir']['name'][] = $l['cat_name'];
            $row['dir']['alias'][] = $l['cat_alias'];
            $row['dir']['tag'][] = $l['cat_tag'];
            $row['dir']['cat_visible'][] = $l['cat_visible'];
            $row['dir']['date_added'][] = $l['date_added'];
            $row['dir']['last_modified'][] = $l['last_modified'];

            ####################### Template placeholders ######################

            $row['dir']['rowNum'][] = $rowNum;
            $row['dir']['rowClass'][] = $rowClass[$rowNum % 2];
            $dirPath = $gdir . $this->_getPath($l['parent_id']);
            $row['dir']['checkBox'][] = '
                <input name="dir[' . $l['cat_id'] . ']" value="' . rawurldecode($dirPath . $l['cat_name']) . '" type="checkbox" style="border:0;padding:0" />
                ';
            $row['dir']['gid'][] = '[id: ' . $l['cat_id'] . ']';
            $row['dir']['path'][] = $dirPath;
            $row['dir']['pathRawUrlEncoded'][] = str_replace('%2F', '/', rawurlencode($dirPath . $l['cat_name']));
            $row['dir']['title'][] = ( trim($l['cat_alias']) != '' ? $l['cat_alias'] : $l['cat_name']);
            $row['dir']['tagLinks'][] = $this->_createTagLinks($l['cat_tag']);
            $row['dir']['time'][] = $this->_getTime($l['date_added'], $l['last_modified'], $dirPath . $l['cat_name']);
            $row['dir']['count'][] = $this->_countFiles($dirPath . $l['cat_name']);

            $dirStyledName = $l['cat_name']; // will be overridden for styling below
            $dirCheckBox = '';
            $dirLink = '';
            $dirAttributes = '';
            $dirAttributeIcons = '';
            $dirIcons = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder.png"
                    width="16" height="16" border="0" alt="" />
                ';
            $dirButtons = '';

            if ($l['cat_visible'] == '1') {
                $dirStyledName = '<b>' . $l['cat_name'] . '</b>';
                $dirButtons = $this->_actionButton('hide_dir', array(
                            'act' => 'hide_dir'
                            , 'dir_id' => $l['cat_id']
                            , 'pid' => $l['parent_id']
                        ));
            } else {
                $dirAttributes = '<i>(' . $lng['hidden'] . ')</i>';
                $dirAttributeIcons = '
                <a href="' . $index . '&amp;act=show_dir&amp;dir_id=' . $l['cat_id'] . '&amp;pid=' . $l['parent_id'] . '">
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16" height="16" alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />
                </a>
                ';
                $dirButtons = $this->_actionButton('show_dir', array(
                            'act' => 'show_dir'
                            , 'dir_id' => $l['cat_id']
                            , 'pid' => $l['parent_id']
                        ));
            }

            $dirButtons .= $this->_actionButton('edit_dir', array(
                        'page' => 'edit_dir'
                        , 'dir_id' => $l['cat_id']
                        , 'tag' => $tag
                    ));
            $dirButtons .= $this->_actionButton('delete_dir', array(
                        'act' => 'delete_dir'
                        , 'dir_path' => $dirPath . $l['cat_name']
                        , 'dir_id' => $l['cat_id']
                        , 'tag' => $tag
                            )
                            , 'onclick="return confirmDeleteFolder();"'
            );

            $row['dir']['styledName'][] = $dirStyledName;
            $row['dir']['attributes'][] = $dirAttributes;
            $row['dir']['attributeIcons'][] = $dirAttributeIcons;
            $row['dir']['href'][] = $index . '&amp;pid=' . $l['cat_id'];
            $row['dir']['buttons'][] = $dirButtons;
            $row['dir']['icon'][] = $dirIcons;
            $row['dir']['size'][] = '---';
            $row['dir']['w'][] = '---';
            $row['dir']['h'][] = '---';
            $row['dir']['mod_w'][] = $modThumbW;
            $row['dir']['mod_h'][] = $modThumbH;
            $row['dir']['mod_thq'][] = $modThumbThq;

            $rowNum++;
        }

        mysql_free_result($querySelectDirs);

        ############################################################################################################################
        //******************************************************************/
        //************* FILE content for the current directory *************/
        //******************************************************************/
        $selectFiles = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'WHERE tag LIKE \'%' . $tag . '%\' ';
        $querySelectFiles = mysql_query($selectFiles);
        $row['file'] = array();
        if (!$querySelectFiles) {
            $_SESSION['easy2err'][] = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
            return FALSE;
        }
        while ($l = mysql_fetch_array($querySelectFiles, MYSQL_ASSOC)) {
            // goldsky -- store the array to be connected between db <--> fs
            $row['file']['id'][] = $l['id'];
            $row['file']['dirId'][] = $l['dir_id'];
            $row['file']['name'][] = $l['filename'];
            $row['file']['size'][] = round($l['size'] / 1024);
            $row['file']['w'][] = $l['width'];
            $row['file']['h'][] = $l['height'];
            $row['file']['alias'][] = $l['alias'];
            $row['file']['tag'][] = $l['tag'];
            $row['file']['date_added'][] = $l['date_added'];
            $row['file']['last_modified'][] = $l['last_modified'];
            $row['file']['status'][] = $l['status'];

            ####################### Template placeholders ######################

            $fileStyledName = $l['filename']; // will be overridden for styling below
            $fileAlias = '';
            $fileTag = '';
            $fileTagLinks = '';
            $fileAttributes = '';
            $fileAttributeIcons = '';
            $fileIcon = '
            <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture.png" width="16" height="16" border="0" alt="" />
            ';
            $fileButtons = '';

            $row['file']['rowNum'][] = $rowNum;
            $row['file']['rowClass'][] = $rowClass[$rowNum % 2];
            $filePath = '../' . $gdir . $this->_getPath($l['dir_id']);
            $fileNameUrlDecodeFilename = urldecode($l['filename']);
            $filePathRawUrlEncoded = str_replace('%2F', '/', rawurlencode( $filePath . $l['filename']));

            if (!file_exists($filePath . $l['filename'])) {
                $fileIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_delete.png" width="16" height="16" border="0" alt="" />
                ';
                $fileStyledName = '<b style="color:red;"><u>' . $l['filename'] . '</u></b>';
                $fileAttributes = '<i>(' . $lng['deleted'] . ')</i>';
                $fileAttributeIcons = '
                <a href="' . $index . '&amp;act=delete_file&amp;file_path=' . $filePathRawUrlEncoded
                        . (empty($fileId) ? '' : '&amp;file_id=' . $fileId) . '"
                   onclick="return confirmDelete();">
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/delete.png" border="0"
                         alt="' . $lng['delete'] . '" title="' . $lng['delete'] . '" />
                </a>
                    ';
            } else {
                if ($l['status'] == '1') {
                    $fileButtons = $this->_actionButton('hide_file', array(
                                'act' => 'hide_file'
                                , 'file_id' => $l['id']
                                , 'tag' => $tag
                            ));
                } else {
                    $fileStyledName = '<i>' . $l['filename'] . '</i>';
                    $fileAttributes = '<i>(' . $lng['hidden'] . ')</i>';
                    $fileAttributeIcons = '
                    <a href="' . $index . '&amp;act=show_file&amp;file_id=' . $l['id'] . '&amp;pid=' . $l['dir_id'] . '">
                        <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16" height="16" alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />
                    </a>
                    ';
                    $fileButtons = $this->_actionButton('show_file', array(
                                'act' => 'show_file'
                                , 'file_id' => $l['id']
                                , 'tag' => $tag
                            ));
                }
            }

            $fileButtons .= $this->_actionButton('comments', array(
                        'page' => 'comments'
                        , 'file_id' => $l['id']
                        , 'tag' => $tag
                    ));

            $fileButtons .= $this->_actionButton('edit_file', array(
                        'page' => 'edit_file'
                        , 'file_id' => $l['id']
                        , 'tag' => $tag
                    ));

            $fileButtons .= $this->_actionButton('delete_file', array(
                        'act' => 'delete_file'
                        , 'pid' => $l['dir_id']
                        , 'file_path' => $filePathRawUrlEncoded
                        , 'file_id' => $l['id']
                            )
                            , 'onclick="return confirmDelete();"'
            );

            $row['file']['checkBox'][] = '
                <input name="im[' . $l['id'] . ']" value="im[' . $l['id'] . ']" type="checkbox" style="border:0;padding:0" />
                ';
            $row['file']['dirId'][] = $l['dir_id'];
            $row['file']['fid'][] = '[id:' . $l['id'] . ']';
            $row['file']['styledName'][] = $fileStyledName;
            $row['file']['title'][] = ( trim($l['alias']) != '' ? $l['alias'] : $l['filename']);
            $row['file']['tagLinks'][] = $this->_createTagLinks($l['tag']);
            $row['file']['path'][] = $filePath;
            $row['file']['pathRawUrlEncoded'][] = $filePathRawUrlEncoded;
            $row['file']['time'][] = $this->_getTime($l['date_added'], $l['last_modified'], $filePath . $l['filename']);
            $row['file']['attributes'][] = $fileAttributes;
            $row['file']['attributeIcons'][] = $fileAttributeIcons;
            $row['file']['buttons'][] = $fileButtons;
            $row['file']['icon'][] = $fileIcon;
            $row['file']['mod_w'][] = $modThumbW;
            $row['file']['mod_h'][] = $modThumbH;
            $row['file']['mod_thq'][] = $modThumbThq;

            $rowNum++;
        }
        mysql_free_result($querySelectFiles);

        // return dir and file contents
        return $row;
    }

    /**
     * Select any available date from the selections
     * @param date      $dateAdded      time when the object was added into the database
     * @param date      $lastModified   last time when the object was modified
     * @param string    $path           path
     * @return date     time
     */
    private function _getTime($dateAdded, $lastModified, $path) {
        $dateFormat = $this->e2g['mod_date_format'];

        $getTime = (strtotime($lastModified) != FALSE) ? strtotime($lastModified) : strtotime($dateAdded);
        if ($getTime == '' && isset($path)) {
            $getTime = filemtime($path);
            clearstatcache();
        }

        $getTime = @date($dateFormat, $getTime);

        return $getTime;
    }

    /**
     * Gallery's TEMPLATE function
     * @param string $tpl = gallery's template (@FILE or chunk)
     * @param string $data = template's array data
     * @param string $prefix = placeholder's prefix
     * @param string $suffix = placeholder's suffix
     * @return string templated data
     */
    private function _filler($tpl, $data, $prefix = '[+easy2:', $suffix = '+]') {
        return parent::filler($tpl, $data, $prefix, $suffix);
    }

    /**
     * Get template
     * @param string    $tpl Template
     * @return string   Template's content
     */
    private function _getTpl($tpl) {
        return parent::getTpl($tpl);
    }

    /**
     * To get thumbnail for each folder
     * @param  int    $gid folder's ID
     * @return string image's source
     */
    private function _folderImg($gid, $gdir) {
        return parent::folderImg($gid, $gdir);
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
     * @return mixed  FALSE/the thumbail's path
     */
    private function _imgShaper(
    $gdir
    , $path
    , $w
    , $h
    , $thq
    , $resizeType=NULL
    , $red=NULL
    , $green=NULL
    , $blue=NULL
    , $createWaterMark = 0
    ) {
        $modx = $this->modx;
        $e2gModCfg = $this->e2gModCfg;
        $e2g = $this->e2g;

        // decoding UTF-8
        $gdir = $this->_e2gDecode($gdir);
        $path = $this->_e2gDecode($path);
        if (empty($path))
            return FALSE;

        $w = !empty($w) ? $w : $e2gModCfg['mod_w'];
        $h = !empty($h) ? $h : $e2gModCfg['mod_h'];
        $thq = !empty($thq) ? $thq : $e2gModCfg['mod_thq'];
        $resizeType = $e2g['resize_type'];
        $red = $e2g['thbg_red'];
        $green = $e2g['thbg_green'];
        $blue = $e2g['thbg_blue'];

        $thumbPath = '_thumbnails/'
                . substr($path, 0, strrpos($path, '.'))
                . '_mod'
                . '_' . $w . 'x' . $h
                . '.jpg';

        if (!class_exists('E2gThumb')) {
            if (!file_exists(realpath(E2G_MODULE_PATH . 'includes/classes/e2g.public.thumbnail.class.php'))) {
                echo __LINE__ . ' : File <b>' . E2G_MODULE_PATH . 'includes/classes/e2g.public.thumbnail.class.php</b> does not exist.';
                $_SESSION['easy2err'][] = __LINE__ . ' : File <b>' . E2G_MODULE_PATH . 'includes/classes/e2g.public.thumbnail.class.php</b> does not exist.';
                return FALSE;
            } else {
                include_once E2G_MODULE_PATH . 'includes/classes/e2g.public.thumbnail.class.php';
            }
        }

        $imgShaper = new E2gThumb($modx, $e2gModCfg);
        $urlEncoding = $imgShaper->imgShaper($gdir, $path, $w, $h, $thq, $resizeType, $red, $green, $blue, $createWaterMark, $thumbPath);
        if (!$urlEncoding) {
            return FALSE;
        }
        return $urlEncoding;
    }

    /**
     * Create Tags as links for the module's pages
     * @param string    $tags   The tags
     * @return string   The tag's links
     */
    private function _createTagLinks($tags) {
        $index = $this->e2gModCfg['index'];

        if (empty($tags)) {
            return NULL;
        }
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
     * Crop text by length
     * @param   string  $charSet    character set
     * @param   int     $nameLen    text's length
     * @param   string  $text       text to be cropped
     * @return  string  shorthened text
     */
    private function _cropName($charSet, $nameLen, $text) {
        return parent::cropName($charSet, $nameLen, $text);
    }

    /**
     * Button's link, image, and attributes
     * @param string    $buttonName     button's name
     * @param array     $getParams      $_GET parameters to be appended into the link
     * @param string    $attributes     additional space for styles, onclick event, or anything else.
     * @return string   The button's hyperlink and image.
     */
    private function _actionButton($buttonName, $getParams=array(), $attributes=NULL) {
        $index = $this->e2gModCfg['index'];
        $lng = $this->lng;

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
                    . ' alt="' . $lng['visible'] . '" title="' . $lng['visible'] . '" border="0" />';
        } elseif ($buttonName == 'show_dir') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16" height="16"'
                    . ' alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />';
        } elseif ($buttonName == 'edit_dir') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_edit.png" width="16" height="16"'
                    . ' alt="' . $lng['edit'] . '" title="' . $lng['edit'] . '" border="0" />';
        } elseif ($buttonName == 'add_dir') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_add.png" width="16" height="16"'
                    . ' alt="' . $lng['add_to_db'] . '" title="' . $lng['add_to_db'] . '" border="0" />';
        } elseif ($buttonName == 'delete_dir') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/delete.png" width="16" height="16"'
                    . ' alt="' . $lng['delete'] . '" title="' . $lng['delete'] . '" border="0" />';
        }



        // images
        if ($buttonName == 'hide_file') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_opened.png" width="16" height="16"'
                    . ' alt="' . $lng['visible'] . '" title="' . $lng['visible'] . '" border="0" />';
        } elseif ($buttonName == 'show_file') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16" height="16"'
                    . ' alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />';
        } elseif ($buttonName == 'comments') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/comments.png" width="16" height="16"'
                    . ' alt="' . $lng['comments'] . '" title="' . $lng['comments'] . '" border="0" />';
        } elseif ($buttonName == 'edit_file') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_edit.png" width="16" height="16"'
                    . ' alt="' . $lng['edit'] . '" title="' . $lng['edit'] . '" border="0" />';
        } elseif ($buttonName == 'add_file') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_add.png" width="16" height="16"'
                    . ' alt="' . $lng['add_to_db'] . '" title="' . $lng['add_to_db'] . '" border="0" />';
        } elseif ($buttonName == 'delete_file') {
            $button .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/delete.png" width="16" height="16"'
                    . ' alt="' . $lng['delete'] . '" title="' . $lng['delete'] . '" border="0" />';
        }


        $button .= '
                </a>';

        return $button;
    }

}