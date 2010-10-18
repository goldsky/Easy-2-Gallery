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
class e2g_mod extends e2g_pub {

    /**
     * Inherit MODx functions
     * @var mixed modx's API
     */
    public $modx;
    /**
     * The module's configurations in an array
     * @var mixed all the module's settings
     */
    private $e2gmod_cfg;
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

    public function __construct($modx, $e2gmod_cfg, $e2g, $lng) {
        parent::__construct($modx, $e2gmod_cfg, $e2g, $lng);
        $this->modx = & $modx;
        $this->e2gmod_cfg = $e2gmod_cfg;
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
        $e2g['mdate_format'] = 'M d, Y, H:i';
        $e2gDebug = $this->e2gmod_cfg['e2g_debug'];
        $parentId = $this->e2gmod_cfg['parent_id'];
        $_a = $this->e2gmod_cfg['_a'];
        $_i = $this->e2gmod_cfg['_i'];
        $index = $this->e2gmod_cfg['index'];
        $gdir = $this->e2gmod_cfg['gdir'];
        $lng = $this->lng;

        $path = (isset($path) ? $path : '');
        // CREATE PATH
        $p = $this->_pathTo($parentId);
        foreach ($p as $k => $v) {
            $path .= '<a href="' . $index . '&amp;pid=' . $k . '">' . $v . '</a>/';
        }
        unset($p[1]);
        if (!empty($p))
            $gdir .= implode('/', $p) . '/';

        if (!empty($_GET['path'])) {
            $dirs = str_replace('../', '', $_GET['path']);
            $dirs = explode('/', $dirs);
            $cpath = '';
            foreach ($dirs as $v) {
                if (empty($v))
                    continue;
                $cpath .= $v . '/';
                $path .= '<a href="' . $index . '&amp;pid=' . $parentId . '&amp;path=' . $cpath . '">' . $v . '</a>/';
            }
            $gdir .= $cpath;
        }
        $path .= '';

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
                if ($this->_uploadAll($_POST, $_FILES) === FALSE) {
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                } else {
                    if ($_POST['gotofolder'] == 'gothere') {
                        header("Location: " . html_entity_decode(
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
                $this->_downloadChecked($_GET['pid'], $gdir, $_POST);
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Move files/folders to the new folder
            case 'move_checked':
                if ($this->_moveChecked($_POST) === FALSE)
                    header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                else {
                    $this->_cleanCache();
                    /**
                     * REDIRECT PAGE TO THE SELECTED OPTION
                     */
                    if ((isset($_POST['dir']) || isset($_POST['im']))
                            && !empty($_POST['newparent'])
                            && ($_POST['gotofolder'] == 'gothere')
                    ) {
                        header("Location: " . html_entity_decode(
                                        MODX_MANAGER_URL
                                        . 'index.php?'
                                        . 'a=' . $_a
                                        . '&amp;id=' . $_i
                                        . '&amp;e2gpg=2'
                                        . '&amp;pid=' . $_POST['newparent']
                        ));
                    }
                    else
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
                if ($this->_saveLang($_POST))
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . 'Language file is updated.';
                header('Location: ' . html_entity_decode($index));
                exit();

            // Add directory into database
            case 'add_dir':
                if ($this->_addAll('../' . str_replace('../', '', $this->_e2gDecode($_GET['dir_path']) . '/'), $parentId)) {
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_added'];
                } else {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_add_err'];
                }

                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();
                break;

            // Add image into database
            case 'add_file':
                if ($this->_addFile(MODX_BASE_PATH . $_GET['file_path'], $_GET['pid']))
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_added'];
                header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit();

            // Add slideshow
            case 'save_slideshow':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $this->_saveSlideshow($_POST);
                }
                header('Location: ' . html_entity_decode($index));
                exit();
                break;

            case 'update_slideshow':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $this->_updateSlideshow($_POST);
                }
                header('Location: ' . html_entity_decode($index));
                exit();
                break;

            // Add plugin
            case 'save_plugin':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $this->_savePlugin($_POST);
                }
                header('Location: ' . html_entity_decode($index));
                exit();
                break;

            case 'update_plugin':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $this->_updatePlugin($_POST);
                }
                header('Location: ' . html_entity_decode($index));
                exit();
                break;

            // Add thumbnail viewer
            case 'save_viewer':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    if ($this->_saveViewer($_POST) === TRUE) {
                        header('Location: ' . html_entity_decode($index));
                    } else {
                        header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    }
                }
                exit();
                break;

            // Update thumbnail viewer
            case 'update_viewer':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    if ($this->_updateViewer($_POST) === TRUE) {
                        header('Location: ' . html_entity_decode($index));
                    } else {
                        header('Location: ' . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    }
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
                if ($this->_tagRemoveChecked($_POST))
                    $this->_cleanCache();
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
                        . (!empty($_GET['page']) ? '&page=' . $_GET['page'] : null)
                        . (!empty($_GET['filter']) ? '&filter=' . $_GET['filter'] : null)
                        . (!empty($_GET['file_id']) ? '&file_id=' . $_GET['file_id'] : null)
                        . (!empty($_GET['pid']) ? '&pid=' . $_GET['pid'] : null);
                header('Location: ' . html_entity_decode($url, ENT_NOQUOTES));
                exit();
                break;

            case 'com_delete':
                $update = $this->_commentDelete($_GET['comid']);
                if ($update !== TRUE) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['comment_err_update'];
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
                }
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
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // check names against bad characters
                    if ($this->_hasBadChar($_POST['name'])) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['char_bad'];
                    } elseif ($this->_createDir($_POST, $gdir, $parentId) === false) {
                        header("Location: " . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    } else {
                        header("Location: " . html_entity_decode($index . "&amp;pid=" . $parentId));
                    }
                    exit();
                }
                exit();
                break;

            case 'save_dir':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // check names against bad characters
                    if ($this->_hasBadChar($_POST['newdirname'])) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['char_bad'];
                    } else {
                        $this->_editDir($_POST, $gdir);

                        // invoke the plugin
                        $this->_plugin('OnE2GFolderEditFormSave', array(
                            'cat_id' => (int) $_GET['dir_id']));
                    }
                    header('Location: ' . html_entity_decode($index . '&amp;pid=' . $parentId));
                    exit();
                }
                exit();
                break;

            case 'save_file':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // check names against bad characters
                    if ($this->_hasBadChar($_POST['newfilename'])) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['char_bad'] . ' : ' . $_POST['newfilename'];

                        header("Location: " . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    } else {
                        $this->_editFile($_POST, $gdir);
                        // invoke the plugin
                        $this->_plugin('OnE2GFileEditFormSave', array('fid' => $_GET['file_id']));

                        header('Location: ' . html_entity_decode($index . '&amp;pid=' . $parentId));
                    }
                    exit();
                }
                exit();
                break;

            case 'synchro_users' :
                $this->_synchroUserGroups();
                header("Location: " . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                exit;
                break;

            case 'save_mgr_permissions':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $this->_saveMgrAccess($_POST);
                }
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_acc_saved'];
                header("Location: " . html_entity_decode($index));
                exit;
                break;

            case 'save_web_dirs_perm':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $this->_saveWebDirsAccess($_POST);
                }
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_acc_saved'];
                header("Location: " . html_entity_decode($index));
                exit;
                break;

            case 'save_web_files_perm':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $this->_saveWebFilesAccess($_POST);
                }
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_acc_saved'];
                header("Location: " . html_entity_decode($index));
                exit;
                break;
        } // switch ($act)
        // for table row class looping
        $cl = array(' class="gridAltItem"', ' class="gridItem"');
        $i = 0;
        $page = empty($_GET['page']) ? '' : $_GET['page'];

        /**
         * PAGE ACTION
         */
        switch ($page) {
            case 'create_dir':
                //the page content is rendered in ../tpl/page.create_dir.inc.php
                break;

            case 'edit_dir' :
                if (empty($_GET['dir_id']) || !is_numeric($_GET['dir_id'])) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['id_err'];
                    header("Location: " . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }

                // call up the database content first as the comparison subjects
                $res = mysql_query('SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs WHERE cat_id=' . (int) $_GET['dir_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);
                mysql_free_result($res);
                //the page content is rendered in ../tpl/page.edit_dir.inc.php
                break;

            case 'edit_file':
                if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $id['id_err'];

                    header("Location: " . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }

                // call up the database content first as the comparison subjects
                $res = mysql_query('SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $_GET['file_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);
                mysql_free_result($res);

                $ext = substr($row['filename'], strrpos($row['filename'], '.'));
                $filename = substr($row['filename'], 0, -(strlen($ext)));

                //the page content is rendered in ../tpl/page.edit_file.inc.php
                break;

            case 'comments':
                if (empty($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $id['id_err'];

                    header("Location: " . html_entity_decode($_SERVER['HTTP_REFERER'], ENT_NOQUOTES));
                    exit();
                }
                $res = mysql_query('SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $_GET['file_id']);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);
                mysql_free_result($res);
                //the page content is rendered in ../tpl/page.comments.inc.php
                break;

            case 'openexplorer':
                if (isset($_POST['newparent']))
                    $parentId = $_POST['newparent'];
                header('Location: ' . html_entity_decode($index . '&amp;pid=' . $parentId));
                exit();
                break;

            case 'tag':
                // display list by tag
                if (isset($_GET['tag'])) {
                    $tag = trim($_GET['tag']);

                    //******************************************************************/
                    //***************** FOLDERS/DIRECTORIES/GALLERIES ******************/
                    //******************************************************************/
                    $selectDirsQuery = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                            . 'WHERE cat_tag LIKE \'%' . $tag . '%\' '
                            . 'ORDER BY cat_name ASC';
                    $res = mysql_query($selectDirsQuery);
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
                            $mdirs[$l['cat_name']]['date_added'] = $l['date_added'];
                            $mdirs[$l['cat_name']]['last_modified'] = $l['last_modified'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
                    }
                    mysql_free_result($res);
                    unset($selectDirsQuery, $res);

                    //******************************************************************/
                    //************* FILE content for the current directory *************/
                    //******************************************************************/
                    $selectFilesQuery = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE tag LIKE \'%' . $tag . '%\' ';
                    $res = mysql_query($selectFilesQuery);
                    $mfiles = array();
                    if ($res) {
                        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                            // goldsky -- store the array to be connected between db <--> fs
                            $mfiles[$l['filename']]['id'] = $l['id'];
                            $mfiles[$l['filename']]['dir_id'] = $l['dir_id'];
                            $mfiles[$l['filename']]['name'] = $l['filename'];
                            $mfiles[$l['filename']]['alias'] = $l['name'];
                            $mfiles[$l['filename']]['tag'] = $l['tag'];
                            $mfiles[$l['filename']]['date_added'] = $l['date_added'];
                            $mfiles[$l['filename']]['last_modified'] = $l['last_modified'];
                            $mfiles[$l['filename']]['status'] = $l['status'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $selectFilesQuery;
                    }
                    mysql_free_result($res);
                    unset($selectFilesQuery, $res);
                }
                //the page content is rendered in ../tpl/page.default.inc.php
                break;

            default:
                // display list by ROOT id
                if (empty($cpath)) {
                    // MySQL Dir list
                    $selectDirsQuery = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs' . ' '
                            . 'WHERE parent_id = ' . $parentId . ' '
                            . 'ORDER BY cat_name ASC'
                    ;
                    $res = mysql_query($selectDirsQuery);
                    $mdirs = array();
                    if ($res) {
                        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                            // goldsky -- store the array to be connected between db <--> fs
                            $mdirs[$l['cat_name']]['id'] = $l['cat_id'];
                            $mdirs[$l['cat_name']]['name'] = $l['cat_name'];
                            $mdirs[$l['cat_name']]['alias'] = $l['cat_alias'];
                            $mdirs[$l['cat_name']]['cat_tag'] = $l['cat_tag'];
                            $mdirs[$l['cat_name']]['cat_visible'] = $l['cat_visible'];
                            $mdirs[$l['cat_name']]['date_added'] = $l['date_added'];
                            $mdirs[$l['cat_name']]['last_modified'] = $l['last_modified'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
                    }
                    mysql_free_result($res);
                    unset($selectDirsQuery, $res);

                    // MySQL File list
                    $selectFilesQuery = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE dir_id = ' . $parentId;
                    $res = mysql_query($selectFilesQuery);
                    $mfiles = array();
                    if ($res) {
                        while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                            // goldsky -- store the array to be connected between db <--> fs
                            $mfiles[$l['filename']]['id'] = $l['id'];
                            $mfiles[$l['filename']]['name'] = $l['filename'];
                            $mfiles[$l['filename']]['size'] = $l['size'];
                            $mfiles[$l['filename']]['width'] = $l['width'];
                            $mfiles[$l['filename']]['height'] = $l['height'];
                            $mfiles[$l['filename']]['alias'] = $l['name'];
                            $mfiles[$l['filename']]['tag'] = $l['tag'];
                            $mfiles[$l['filename']]['date_added'] = $l['date_added'];
                            $mfiles[$l['filename']]['last_modified'] = $l['last_modified'];
                            $mfiles[$l['filename']]['status'] = $l['status'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $selectFilesQuery;
                    }
                    mysql_free_result($res);
                    unset($selectFilesQuery, $res);
                }
            //the page content is rendered in ../tpl/page.default.inc.php
        } // switch ($page)

        /**
         * MODULE's pages
         */
        ob_start();
        include_once E2G_MODULE_PATH . 'includes/tpl/pane.main.inc.php';
        $output = ob_get_contents();
        ob_end_clean();

        echo $output;
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
        if (!$this->_validFolder($oldPath))
            return $res;
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
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $tree->error;
            return FALSE;
        }
        $modx->db->query(
                'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'SET date_added=NOW() '
                . ', added_by=\'' . $modx->getLoginUserID() . '\' '
                . 'WHERE cat_id=' . $id
        );

        if (!$this->_validFolder($path)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $path;
            return FALSE;
        }

        // invoke the plugin
        $this->_plugin('OnE2GFolderAdd', array('gid' => $id, 'foldername' => $name));

        /**
         * goldsky -- if there is no index.html inside folders, this will create it.
         */
        $this->_createsIndexHtml($path);

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
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $filePath;
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
     * To add file from the upload form into the database
     * @param string $f filename
     * @param int $pid current parent ID
     * @param string $cfg module's configuration
     */
    private function _addFile($filePath, $pid) {
        $modx = $this->modx;
        $e2g = $this->e2g;
        $lng = $this->lng;

        $inf = @getimagesize($filePath);
        if ($inf[2] <= 3 && is_numeric($pid)) {
            // RESIZE
            $basename = $this->_basenameSafe($filePath);
            $basename = $this->_e2gEncode($basename);

            // converting non-latin names with MODx's stripAlias function
            $fnameAlias = $modx->stripAlias($basename);
            if ($basename != $fnameAlias) {
                // converting foldername using TransAlias plugin
                $basefpath = dirname($filePath);
                $rename = rename($filePath, $basefpath . '/' . $this->_e2gDecode($fnameAlias));
                if (!$rename) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_rename_err'];
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $filePath . ' => ' . $basefpath . '/' . $this->_e2gDecode($fnameAlias);
                    return FALSE;
                }
                $this->_changeModOwnGrp('file', $basefpath . '/' . $this->_e2gDecode($fnameAlias));

                $filePath = $basefpath . '/' . $this->_e2gDecode($fnameAlias);
                $basename = $fnameAlias;
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
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_add_err'] . '<br/>' . mysql_error() . '<br />' . $insertFileQuery;
                return FALSE;
            }
            unset($insertFileQuery);
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_add_err'];
            return FALSE;
        }

        // invoke the plugin
        $this->_plugin('OnE2GFileAdd', array(
            'fid' => mysql_insert_id()
            , 'filename' => $filteredName
            , 'pid' => $pid
        ));

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

            if ($e2g['resize_orientated_img'] == 1) {

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
            } // if ($e2g['resize_orientated_img'] == 1)
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
     * To add all file from the upload form
     * @param int $id gets ID
     * @param string $string current parent ID
     * @return int This returns ID. The folder's name is retrieved in the line 76.
     */
    private function _pathTo($id, $string = FALSE) {
        $modx = $this->modx;
        $result = array();
        $q = 'SELECT A.cat_id, A.cat_name '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs A, '
                . $modx->db->config['table_prefix'] . 'easy2_dirs B '
                . 'WHERE B.cat_id=' . $id . ' '
                . 'AND B.cat_left BETWEEN A.cat_left AND A.cat_right '
                . 'ORDER BY A.cat_left';
        $res = mysql_query($q);
        while ($l = mysql_fetch_row($res)) {
            $result[$l[0]] = $l[1];
        }
        mysql_free_result($res);
        unset($q, $res);
        if (empty($result))
            return null;
        if ($string) {
            $result = implode('/', array_keys($result)) . '/';
        }
        return $result;
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
        $res = mysql_query(
                        'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'WHERE parent_id=' . $pid . ' AND cat_visible = 1');
        $mdirs = array();
        if ($res) {
            while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                $mdirs[$l['cat_name']]['id'] = $l['cat_id']; // goldsky -- to be connected between db <--> fs
                $mdirs[$l['cat_name']]['name'] = $l['cat_name'];
            }
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $res;
            return FALSE;
        }
        mysql_free_result($res);
        // MySQL File list
        $res = mysql_query(
                        'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'WHERE dir_id=' . $pid);
        $mfiles = array();
        if ($res) {
            while ($l = mysql_fetch_array($res, MYSQL_ASSOC)) {
                $mfiles[$l['filename']]['id'] = $l['id'];
                $mfiles[$l['filename']]['name'] = $l['filename'];
            }
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $res;
            return FALSE;
        }
        mysql_free_result($res);

        /**
         * goldsky -- if there is no index.html inside folders, this will create it.
         */
        $this->_createsIndexHtml($path);

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
                        if ($e2g['resize_old_img'] == 1) {
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

                        $updateFileQuery = mysql_query($updateFile);
                        if (!$updateFileQuery) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['resize_err'];
                            $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error() . '<br />' . $updateFile;
                            return FALSE;
                        }
                        unset($updateFile);
                        mysql_free_result($updateFileQuery);

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
                $fileIds = array();
                $selectFile = 'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE dir_id IN(' . implode(',', $ids) . ')';
                $res = mysql_query($selectFile);
                if (!$res) {
                    $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $selectFile;
                    return FALSE;
                }
                while ($l = mysql_fetch_row($res)) {
                    $fileIds[] = $l[1];
                }
                mysql_free_result($res);
                unset($selectFile, $res);

                if (count($fileIds) > 0) {
                    mysql_query(
                            'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                            . 'WHERE file_id IN(' . implode(',', $fileIds) . ')');
                }
                @mysql_query(
                                'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                . 'WHERE dir_id IN(' . implode(',', $ids) . ')');
            }
        }

        // Deleted physical files, DELETE record from database
        if (isset($mfiles) && count($mfiles) > 0) {
            $mfiles_array = array();
            foreach ($mfiles as $key => $value) {
                $mfiles_array[] = $value['id'];
            }
            $qfiles = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE id IN(' . implode(',', $mfiles_array) . ')';
            $db_res = mysql_query($qfiles);
            @mysql_query(
                            'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                            . 'WHERE file_id IN(' . implode(',', $mfiles_array) . ')');
            if (!$db_res) {
                $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $qfiles;
                return FALSE;
            }
            mysql_free_result($db_res);
        }

        $timeEnd = microtime(TRUE);
        $timeTotal = $timeEnd - $timeStart;
        if ($e2g['e2g_debug'] == 1) {
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
     * to check the valid characters in names.<br />
     * TRUE means BAD!
     */
    private function _hasBadChar($characters) {
        $badChars = array(
            "U+0000", "/", "\\", ":", "*", "?", "'", "\"", "<", ">", "|", ";"
            , "@", "=", "#", "&", "!", "*", "'", "(", ")", ",", "{", "}", ","
            , "^", "~", "[", "]", "`"
        );
        foreach ($badChars as $badChar) {
            if (strstr($characters, $badChar))
                return TRUE;
        }
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
    private function _e2gEncode($text, $callback=false) {
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
    private function _e2gDecode($text, $callback=false) {
        return parent::e2gDecode($text, $callback);
    }

    /**
     * get folders structure for select options.
     * @param int       $parentid   Parent's ID
     * @param bool      $selected   turn on the selected="selected" if the current folder is the selected folder
     * @param string    $jsActions  Javascript's action
     * @return string   The multiple options
     */
    private function _folderOptions($parentid=0, $selected=0, $jsActions=null) {
        $modx = $this->modx;
        $e2gDebug = $this->e2gmod_cfg['e2g_debug'];

        $selectDir = 'SELECT parent_id, cat_id, cat_name, cat_level '
                . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                . 'WHERE parent_id=' . $parentid . ' '
        ;

        $queryDir = mysql_query($selectDir);
        $numDir = @mysql_num_rows($queryDir);

        $childrenDirs = array();
        if ($queryDir) {
            while ($l = mysql_fetch_array($queryDir, MYSQL_ASSOC)) {
                $childrenDirs[$l['cat_id']]['parent_id'] = $l['parent_id'];
                $childrenDirs[$l['cat_id']]['cat_id'] = $l['cat_id'];
                $childrenDirs[$l['cat_id']]['cat_name'] = $l['cat_name'];
                $childrenDirs[$l['cat_id']]['cat_level'] = $l['cat_level'];
            }
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
            if ($e2gDebug == 1)
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $selectDir;
        }
        mysql_free_result($queryDir);

        $output = (isset($output) ? $output : '');
        foreach ($childrenDirs as $childDir) {
            // DISPLAY
            $output .= '
                            <option value="' . $childDir['cat_id'] . '"'
                    . ( ( $childDir['cat_id'] == 1 ) ? ' style="background-color:#ddd;"' : '' )
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
            $selectSub = 'SELECT parent_id, cat_id, cat_name '
                    . 'FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                    . 'WHERE parent_id=' . $childDir['cat_id'] . ' '
                    . 'ORDER BY cat_name ASC'
            ;
            $querySub = mysql_query($selectSub);
            if (!$querySub) {
                $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $selectSub;
            } else {
                $numSub = @mysql_num_rows($querySub);
                if ($numSub > 0) {
                    $output .= $this->_folderOptions($childDir['cat_id'], $selected, $jsActions);
                }
            }
            mysql_free_result($querySub);
            //*********************************************************/
        } // foreach ($childrenDirs as $childDir)
        return $output;
    }

    /**
     * To return an options selection for tag
     * @param string    $tag   the tag
     * @return string   option selection
     */
    private function _tagOptions($tag) {
        $modx = $this->modx;
        $e2gDebug = $this->e2gmod_cfg['e2g_debug'];

        // Directory
        $selectDirTags = 'SELECT DISTINCT cat_tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs ';
        $queryDirTags = mysql_query($selectDirTags);
        $numDirTags = mysql_num_rows($queryDirTags);

        if ($queryDirTags)
            while ($l = mysql_fetch_array($queryDirTags)) {
                if ($l['cat_tag'] == '' || $l['cat_tag'] == null)
                    continue;
                $tagOptions[] = $l['cat_tag'];
            } else {
            $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
            if ($e2gDebug == 1)
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $selectDirTags;
        }

        // File
        $selectFileTags = 'SELECT DISTINCT tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_files ';
        $queryFileTags = mysql_query($selectFileTags);
        $numFileTags = mysql_num_rows($queryFileTags);

        if ($queryFileTags)
            while ($l = mysql_fetch_array($queryFileTags)) {
                if ($l['tag'] == '' || $l['tag'] == null)
                    continue;
                $tagOptions[] = $l['tag'];
            } else {
            $_SESSION['easy2err'][] = __LINE__ . ' MySQL ERROR: ' . mysql_error();
            if ($e2gDebug == 1)
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $selectFileTags;
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
     * @author Schoschie (nh t ngin dott de)
     * @link http://www.php.net/manual/en/features.file-upload.errors.php#90522
     * @param int $error_code
     * @return string The error message
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
     * @return  bool    true/false
     * @author  goldsky <goldsky@modx-id.com>
     * @todo : unziping the non-latin file
     */
    private function _unzip($file, $path) {
        $modx = $this->modx;
        $lng = $this->lng;
        $e2gEncode = $this->e2gmod_cfg['e2g_encode'];
        $e2gDebug = $this->e2gmod_cfg['e2g_debug'];

        if ($e2gEncode == 'UTF-8 (Rin)') {
            include_once E2G_MODULE_PATH . 'includes/UTF8-2.1.0/UTF8.php';
            include_once E2G_MODULE_PATH . 'includes/UTF8-2.1.0/ReflectionTypehint.php';
        }

        // added by Raymond
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
        // end mod

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
//die(__LINE__.': $zipEntryName = '.$zipEntryName);
//die(__LINE__.': $mbDetectEncoding = '.mb_detect_encoding($zipOpen));
//die(__LINE__.': $zipEntryName = '.$modx->stripAlias($zipEntryName));
//die(__LINE__.': $zipEntryName = '.$this->_e2gDecode($zipEntryName));
//die(__LINE__.': $zipEntryName = '.$this->_e2gEncode($zipEntryName));
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
                            if ($e2gDebug == 1)
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
     * @param string $dir   path
     * @param string $lng   language string
     * @return mixed file creation
     */
    private function _createsIndexHtml($dir) {
        $lng = $this->lng;

        if (!file_exists($dir . 'index.html')) {
            // goldsky -- adds a cover file
            $indexHtml = $dir . 'index.html';
            $fh = fopen($indexHtml, 'w');
            if (!$fh)
                $_SESSION['easy2err'][] = __LINE__ . " : Could not open file " . $indexHtml;
            else {
                fwrite($fh, htmlspecialchars_decode($lng['indexfile']));
                fclose($fh);
                $this->_changeModOwnGrp('file', $indexHtml);
            }
        }
    }

    /**
     * Invoking the script with plugin, at any specified places.
     * @param string    $e2gEvtName     event trigger.
     * @param mixed     $e2gEvtParams   parameters array: depends on the event trigger.
     * @return mixed    if TRUE, will return the indexfile. Otherwise this will return FALSE.
     */
    private function _plugin($e2gEvtName, $e2gEvtParams=array()) {
        return parent::plugin($e2gEvtName, $e2gEvtParams);
    }

    private function _uploadAll($post, $files) {
        $modx = $this->modx;
        $e2g = $this->e2g;
        $lng = $this->lng;
        $gdir = $this->e2gmod_cfg['gdir'];
        $newParent = !empty($post['newparent']) ? $post['newparent'] : 1;

        if (empty($files['img']['tmp_name'][0]) && empty($files['zip']['tmp_name'][0])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['upload_err'] . ' : ' . $lng['upload_empty'];
            return FALSE;
        }

        // CREATE PATH
        $p = $this->_pathTo($newParent);
        foreach ($p as $k => $v) {
            $path .= '<a href="' . $index . '&amp;pid=' . $k . '">' . $v . '</a>/';
        }
        unset($p[1]);
        if (!empty($p))
            $gdir .= implode('/', $p) . '/';

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

                $q = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'SET dir_id=\'' . $newParent . '\''
                        . ', filename=\'' . mysql_real_escape_string($filteredName) . '\''
//                        . ', size=\'' . (int) $files['img']['size'][$i] . '\''
                        . ', size=\'' . $newInf['size'] . '\''
                        . ', width=\'' . $newInf[0] . '\''
                        . ', height=\'' . $newInf[1] . '\''
                        . ', name=\'' . mysql_real_escape_string(htmlspecialchars($post['name'][$i])) . '\''
                        . ', summary=\'' . mysql_real_escape_string(htmlspecialchars($post['summary'][$i])) . '\''
                        . ', tag=\'' . mysql_real_escape_string(htmlspecialchars($post['tag'][$i])) . '\''
                        . ', description=\'' . mysql_real_escape_string(htmlspecialchars($post['description'][$i])) . '\''
                        . ', date_added=NOW()';
                if (!mysql_query($q)) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['db_err'] . ' : ' . mysql_error() . '<br />' . $q;
                    continue;
                }
                unset($q);

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

    private function _showChecked($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $countRes = array();
        // show dirs
        if (!empty($post['dir'])) {
            foreach ($post['dir'] as $k => $v) {
                if (is_numeric($k)) {
                    $res = mysql_query(
                                    'SELECT cat_visible FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                                    . 'WHERE cat_id=' . $k);
                    if (mysql_result($res, 0, 0) == '1') {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_hiddennot_inverse_err'] . ' : ' . $v;
                        mysql_free_result($res);
                        continue;
                    }

                    $res = mysql_query(
                                    'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                                    . 'SET cat_visible=\'1\' '
                                    . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                                    . 'WHERE cat_id=' . $k);
                    if ($res) {
                        $countRes['ddb']++;
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_hiddennot_err'] . ' : ' . $v;
                    }
                    mysql_free_result($res);
                } // if (is_numeric($k))
            } // foreach ($post['dir'] as $k => $v)
        } // if (!empty($post['dir']))
        // show images
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                if (is_numeric($k)) {
                    $res = mysql_query(
                                    'SELECT status FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                    . 'WHERE id=' . $k);
                    if (mysql_result($res, 0, 0) == '1') {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_hiddennot_inverse_err'] . ' : ' . $v;
                        mysql_free_result($res);
                        continue;
                    }

                    $res = mysql_query(
                                    'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                    . 'SET status=\'1\' '
                                    . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                                    . 'WHERE id=' . $k);
                    if ($res) {
                        $countRes['fdb']++;
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_hiddennot_err'] . ' : ' . $v;
                    }
                    mysql_free_result($res);
                } // if (is_numeric($k))
            } // foreach ($post['im'] as $k => $v)
        } // if (!empty($post['im']))
        if (!empty($countRes['ddb']) || !empty($countRes['fdb'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['ddb'] . ' ' . $lng['dirs_hiddennot_suc'] . '.';
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['fdb'] . ' ' . $lng['files_hiddennot_suc'] . '.';
        }
        return TRUE;
    }

    private function _hideChecked($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $countRes = array();
        // hide dirs
        if (!empty($post['dir'])) {
            foreach ($post['dir'] as $k => $v) {
                if (is_numeric($k)) {
                    $res = mysql_query(
                                    'SELECT cat_visible FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                                    . 'WHERE cat_id=' . $k);
                    if (mysql_result($res, 0, 0) == '0') {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_hidden_inverse_err'] . ' : ' . $v;
                        mysql_free_result($res);
                        continue;
                    }

                    $res = mysql_query(
                                    'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                                    . 'SET cat_visible=\'0\' '
                                    . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                                    . 'WHERE cat_id=' . $k);
                    if ($res) {
                        $countRes['ddb']++;
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_hidden_err'] . ' : ' . $v;
                    }
                    mysql_free_result($res);
                } // if (is_numeric($k))
            } // foreach ($post['dir'] as $k => $v)
        } // if (!empty($post['dir']))
        // hide images
        if (!empty($post['im'])) {
            foreach ($post['im'] as $k => $v) {
                if (is_numeric($k)) {
                    $res = mysql_query(
                                    'SELECT status FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                    . 'WHERE id=' . $k);
                    if (mysql_result($res, 0, 0) == '0') {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_hidden_inverse_err'] . ' : ' . $v;
                        mysql_free_result($res);
                        continue;
                    }

                    $res = mysql_query(
                                    'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                    . 'SET status=\'0\' '
                                    . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                                    . 'WHERE id=' . $k);
                    if ($res) {
                        $countRes['fdb']++;
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_hidden_err'] . ' : ' . $v;
                    }
                    mysql_free_result($res);
                } // if (is_numeric($k))
            } // foreach ($post['im'] as $k => $v)
        } // if (!empty($post['im']))
        if (!empty($countRes['ddb']) || !empty($countRes['fdb'])) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['ddb'] . ' ' . $lng['dirs_hidden_suc'] . '.';
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $countRes['fdb'] . ' ' . $lng['files_hidden_suc'] . '.';
        }
        return TRUE;
    }

    private function _showDir($dirId) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($dirId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dpath_err'];
            return FALSE;
        } else {
            $id = (int) $dirId;
            if (is_numeric($dirId)) {
                $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'SET cat_visible=\'1\' '
                        . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                        . 'WHERE cat_id=' . $id;
                $res = mysql_query($update);
            }
            if ($res) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_hiddennot_suc'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_hiddennot_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
                return FALSE;
            }
            mysql_free_result($res);
            return TRUE;
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    private function _hideDir($dirId) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($dirId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dpath_err'];
            return FALSE;
        } else {
            $id = (int) $dirId;
            if (is_numeric($dirId)) {
                $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                        . 'SET cat_visible=\'0\' '
                        . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                        . 'WHERE cat_id=' . $id;
                $res = mysql_query($update);
            }
            if ($res) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_hidden_suc'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_hidden_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
                return FALSE;
            }
            mysql_free_result($res);
            return TRUE;
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    private function _showFile($fileId) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($fileId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['fpath_err'];
            return FALSE;
        } else {
            $id = (int) $fileId;
            if (is_numeric($fileId)) {
                $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'SET status=\'1\' '
                        . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                        . 'WHERE id=' . $id;
                $res = mysql_query($update);
            }
            if ($res) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_hiddennot_suc'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_hiddennot_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
                return FALSE;
            }
            mysql_free_result($res);
            return TRUE;
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    private function _hideFile($fileId) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($fileId)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['fpath_err'];
            return FALSE;
        } else {
            $id = (int) $fileId;
            if (is_numeric($fileId)) {
                $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'SET status=\'0\' '
                        . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                        . 'WHERE id=' . $id;
                $res = mysql_query($update);
            }
            if ($res) {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_hidden_suc'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_hidden_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
                return FALSE;
            }
            mysql_free_result($db_res);
            return TRUE;
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    private function _deleteChecked($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_dirfile'];
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
                if (is_numeric($k)) {
                    $ids = $tree->delete((int) $k);
                    $fileIds = array();
                    $filesRes = mysql_query(
                                    'SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                    . 'WHERE dir_id IN(' . implode(',', $ids) . ')');
                    while ($l = mysql_fetch_row($filesRes)) {
                        $fileIds[] = $l[0];
                    }
                    mysql_free_result($filesRes);

                    if (count($fileIds) > 0) {
                        $delComments = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                                . 'WHERE file_id IN(' . implode(',', $fileIds) . ')';
                        $delCommentsQuery = mysql_query($delComments);
                        if (!$delCommentsQuery) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['db_err'] . ' : ' . mysql_error() . '<br />' . $delComments;
                        }
                    }
                    $delFiles = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE dir_id IN(' . implode(',', $ids) . ')';
                    $delFilesQuery = mysql_query($delFiles);
                    if (!$delFilesQuery) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['db_err'] . ' : ' . mysql_error() . '<br />' . $delFiles;
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
                $out .= $k . '=>' . $v . '<br>';
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
                if (is_numeric($k)) {
                    $fileIds = array();
                    $filesRes = mysql_query('SELECT id FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $k);
                    while ($l = mysql_fetch_row($filesRes))
                        $fileIds[] = $l[0];
                    mysql_free_result($filesRes);

                    if (count($fileIds) > 0) {
                        mysql_query(
                                'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                                . 'WHERE file_id IN(' . implode(',', $fileIds) . ')');
                    }
                    if (mysql_query('DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . (int) $k)) {
                        $res['fdb'][0]++;
                    } else {
                        $res['fdb'][1]++;
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
                $out .= $k . '=>' . $v . '<br>';
            }
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

    private function _downloadChecked($pid, $gdir, $post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $zipContent = '';
        $_zipContent = array();
        if (!empty($post['dir']) || !empty($post['im'])) {
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
                        while (false !== ($node = $dir->read())) {
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
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['zip_select_none'];
            return FALSE;
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    private function _moveChecked($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_dirfile'];
            return FALSE;
        } elseif ($post['newparent'] == '') {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_newdir'];
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
        // MOVING DIRS
        if (!empty($post['dir']) && !empty($post['newparent'])) {
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

                    $newParent = $this->_pathTo($post['newparent']);
                    unset($newParent[1]);
                    $newDir = $this->e2gmod_cfg['gdir'];
                    if (!empty($newParent))
                        $newDir .= implode('/', $newParent) . '/';
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
                                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_move_err'] . '
                                    from: <span style="color:blue;">' . $oldPath['origin'] . '</span>
                                    to: <span style="color:blue;">' . $newPath['origin'] . '</span>';
                            }
                            if (!empty($tree->error))
                                $_SESSION['easy2err'][] = __LINE__ . ' : Error: ' . $tree->error;

                            continue;
                        }
                        if ($e2gDebug == 1)
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
                        if ($e2gDebug == 1)
                            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . __LINE__ . ' : ' . $lng['dir_move_suc'] . '
                                    from: <span style="color:blue;">' . $oldPath['origin'] . '</span>
                                    to: <span style="color:blue;">' . $newPath['origin'] . '</span>';

                        if (empty($moveDir['e'])) {
                            $res['dfp'][0] += $moveDir['d'];
                            $res['ffp'][0] += $moveDir['f'];
                        } else {
                            $res['dfp'][1]++;
                        }

                        if ($e2gDebug == 1) {
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
        if (!empty($post['im']) && !empty($post['newparent'])) {
            foreach ($post['im'] as $k => $v) {
                // move the file
                if (!empty($v)) {

                    $oldFile['origin'] = str_replace('../', '', $v);
                    $oldFile['basename'] = $this->_basenameSafe($v);
                    $oldFile['decoded'] = str_replace('../', '', $this->_e2gDecode($v));
                    $this->_changeModOwnGrp('file', MODX_BASE_PATH . $oldFile['decoded']);

                    $newParent = $this->_pathTo($post['newparent']);
                    unset($newParent[1]);
                    $newDir = $this->e2gmod_cfg['gdir'];
                    if (!empty($newParent))
                        $newDir .= implode('/', $newParent) . '/';
                    $newFile['origin'] = $newDir . $this->_basenameSafe($v);
                    $newFile['basename'] = $this->_basenameSafe($newFile['origin']);
                    $newFile['decoded'] = $this->_e2gDecode($newFile['origin']);

                    if (is_file('../' . $newFile['decoded'])) {
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
                                mysql_free_result($updateFile);
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
     * @param string $get
     * @return <type>
     */
    private function _deleteDir($get) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($get['dir_id']) && empty($get['dir_path'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dpath_err'];
            return FALSE;
        }

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

    private function _deleteFile($get) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($get['file_id']) && empty($get['file_path'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['fpath_err'];
            return FALSE;
        }

        $id = (int) $get['file_id'];
        if (is_numeric($get['file_id'])) {
            $db_res = mysql_query('DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_files WHERE id=' . $id);
            mysql_query('DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments WHERE file_id=' . $id);
        }
        if (!empty($get['file_path'])) {
            $file_path = str_replace('../', '', $this->_e2gDecode($get['file_path']));
            $f_res = @unlink('../' . $file_path);
        }
        if ($db_res && $f_res) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_delete'];
        } elseif ($db_res) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_delete_fdb'];
        } elseif ($f_res) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['file_delete_fhdd'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['file_delete_err'] . ' : ' . $file_path;
        }
        mysql_free_result($db_res);

        // invoke the plugin
        $this->_plugin('OnE2GFileDelete', array('fid' => $id));

        return TRUE;
    }

    /**
     * To delete all of the thumbnail folder's content
     * @param string $dir   path
     * @param string $lng   language string
     * @return string result report
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
     * @param array $post           Configuration values
     * @param bool  $build_default  Build default config file
     * @return null
     */
    private function _saveConfig($post, $build_default=false) {
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
            $delete = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_configs ';
            $delQuery = mysql_query($delete);
            if (!$delQuery) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['config_delete_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                return FALSE;
            }
            // else
            foreach ($post as $k => $v) {
                $insert = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_configs '
                        . 'SET `cfg_key`=\'' . $k . '\', `cfg_val`=\'' . $v . '\'';
                $insQuery = mysql_query($insert);
                if (!$insQuery) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['config_update_err'];
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error() . '<br />' . $insert;
                    return FALSE;
                }
            }

            // delete the config file, because this will always be checked as an upgrade option
            if (file_exists(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php')) {
                unlink(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php');
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['config_file_del_suc'];
            }

            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['config_update_suc'];
            return TRUE;
        }
    }

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

        return TRUE;
    }

    private function _ignoreIp($get) {
        $modx = $this->modx;
        $lng = $this->lng;

        $insert = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                . '(ign_date, ign_ip_address, ign_username, ign_email) '
                . 'VALUES(NOW(),\'' . $get['ip'] . '\',\'' . $get['u'] . '\',\'' . $get['e'] . '\')';
        if (mysql_query($insert)) {
            $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                    . 'SET STATUS=\'0\' '
                    . 'WHERE ip_address=\'' . $get['ip'] . '\'';
            mysql_query($update);
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['ip_ignored_suc'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['ip_ignored_err'] . '<br />' . mysql_error();
            return FALSE;
        }
        unset($insert);
        return TRUE;
    }

    private function _unignoreIp($get) {
        $modx = $this->modx;
        $lng = $this->lng;

        $delete = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                . 'WHERE ign_ip_address =\'' . $_GET['ip'] . '\'';
        if (mysql_query($delete)) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['ip_unignored_suc'];
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['ip_unignored_err'] . '<br />' . mysql_error();
            return FALSE;
        }
        unset($delete);
        return TRUE;
    }

    private function _unignoredAllIps($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        foreach ($_POST['unignored_ip'] as $uignIP) {
            $delete = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_ignoredip '
                    . 'WHERE ign_ip_address =\'' . $uignIP . '\'';
            $delQuery = mysql_query($delete);
            if (!$delQuery) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['ip_unignored_err'] . '<br />' . mysql_error();
                continue;
            }
            unset($delete);
        }

        return TRUE;
    }

    private function _tagAddChecked($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['tag_input'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['tag_err_novalue'];
            return FALSE;
        }

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_dirfile'];
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
                    $dirTagSelect = 'SELECT cat_tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                            . 'WHERE cat_id=' . $k;
                    $dirTagQuery = mysql_query($dirTagSelect);
                    if ($dirTagQuery) {
                        while ($l = mysql_fetch_array($dirTagQuery)) {
                            $dirTags = $l['cat_tag'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $dirTagSelect;
                    }

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
                        $dirTagUpdate = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                                . 'SET cat_tag=\'' . $newTags . '\' '
                                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                                . ', last_modified=NOW() '
                                . 'WHERE cat_id=' . $k
                        ;
                        $dirTagUpdateQuery = mysql_query($dirTagUpdate);
                        if (!$dirTagUpdateQuery) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $dirTagUpdate;
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
                    $fileTagSelect = 'SELECT tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE id=' . $k;
                    $fileTagSelectQuery = mysql_query($fileTagSelect);
                    if ($fileTagSelectQuery) {
                        while ($l = mysql_fetch_array($fileTagSelectQuery)) {
                            $fileTags = $l['tag'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $fileTagSelect;
                    }
                    unset($fileTagSelect, $fileTagSelectQuery);

                    $xpldFileTags = array();
                    $xpldFileTags = explode(',', $fileTags);

                    for ($c = 0; $c < count($xpldFileTags); $c++) {
                        $xpldFileTags[$c] = htmlspecialchars(trim($xpldFileTags[$c]), ENT_QUOTES);
                    }

                    $newTags = $intTags = array();
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
                        $updateFileTag = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                                . 'SET tag=\'' . $newTags . '\' '
                                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                                . ', last_modified=NOW() '
                                . 'WHERE id=' . $k
                        ;
                        $updateFileTagQuery = mysql_query($updateFileTag);
                        if (!$updateFileTagQuery) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $updateFileTag;
                        }
                    }
                } // if (!empty($v))
            } // foreach ($post['im'] as $k => $v)
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['tag_suc_new'];
        } // if (!empty($post['im']))

        return TRUE;
    }

    private function _tagRemoveChecked($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($post['tag_input'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['tag_err_novalue'];
            return FALSE;
        }

        if (empty($post['dir']) && empty($post['im'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['select_dirfile'];
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
                    $dirTagSelect = 'SELECT cat_tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                            . 'WHERE cat_id=' . $k;
                    $dirTagQuery = mysql_query($dirTagSelect);
                    if ($dirTagQuery) {
                        while ($l = mysql_fetch_array($dirTagQuery)) {
                            $dirTags = $l['cat_tag'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $dirTagSelect;
                    }

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
                    $dirTagUpdate = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                            . 'SET cat_tag=\'' . $newTags . '\' '
                            . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                            . ', last_modified=NOW() '
                            . 'WHERE cat_id=' . $k
                    ;
                    $dirTagUpdateQuery = mysql_query($dirTagUpdate);
                    if (!$dirTagUpdateQuery) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $dirTagUpdate;
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
                    $fileTagSelect = 'SELECT tag FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
                            . 'WHERE id=' . $k
                    ;
                    $fileTagSelectQuery = mysql_query($fileTagSelect);
                    if ($fileTagSelectQuery) {
                        while ($l = mysql_fetch_array($fileTagSelectQuery)) {
                            $fileTags = $l['tag'];
                        }
                    } else {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $fileTagSelect;
                    }

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
                    $updateFileTag = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                            . 'SET tag=\'' . $newTags . '\' '
                            . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                            . ', last_modified=NOW() '
                            . 'WHERE id=' . $k
                    ;
                    $updateFileTagQuery = mysql_query($updateFileTag);
                    if (!$updateFileTagQuery) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $updateFileTag;
                    }
                } // if (!empty($v))
            } // foreach ($post['im'] as $k => $v)
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['tag_suc_remove'];
        } // if (!empty($post['im']))

        return TRUE;
    }

    /**
     * Multiple actions for the comment list checkboxes
     * @param string    $post   values from the checkboxes
     * @return bool TRUE|FALSE
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
            foreach ($post['comments'] as $com_id) {
                $update = $this->_commentDelete($com_id);
                if ($update !== TRUE) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['comment_err_update'];
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
                    return FALSE;
                } else {
                    $countRes++;
                    $query = mysql_query($update);
                }
            }
        } else {
            foreach ($post['comments'] as $com_id) {
                $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments ';
                switch ($post['listCommentActions']) {
                    case 'approve':
                        $update .= 'SET approved=\'1\' ,status=\'1\' ';
                        break;
                    case 'unapprove':
                        $update .= 'SET approved=\'0\' ,status=\'0\' ';
                        break;
                    case 'hide':
                        $update .= 'SET status=\'0\' ';
                        break;
                    case 'unhide':
                        $update .= 'SET status=\'1\' ';
                        break;
                }
                $update .= 'WHERE id=\'' . $com_id . '\'';
                $countRes++;

                $query = mysql_query($update);
                if (!$query) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['comment_err_update'];
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
                    return FALSE;
                }
            }
        }
        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['comment_suc_update'] . ' ' . $countRes . ' ' . $lng['comments'];
        unset($countRes);
        return TRUE;
    }

    /**
     * Approve comment
     * @param int   $id comment's ID
     * @return bool TRUE|FALSE
     */
    private function _commentApprove($id) {
        $modx = $this->modx;
        $lng = $this->lng;

        $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET approved=\'1\' ,status=\'1\' '
                . 'WHERE id=' . $id;
        $query = mysql_query($update);
        if (!$query) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['comment_err_update'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
            return FALSE;
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['comment_suc_approved'];
            return TRUE;
        }
    }

    /**
     * Hide comment
     * @param int   $id comment's ID
     * @return bool TRUE|FALSE
     */
    private function _commentHide($id) {
        $modx = $this->modx;
        $lng = $this->lng;

        $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET status=\'0\' '
                . 'WHERE id=' . $id;
        $query = mysql_query($update);
        if (!$query) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['comment_err_update'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
            return FALSE;
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['comment_suc_hide'];
            return TRUE;
        }
    }

    /**
     * Unhide comment
     * @param int   $id comment's ID
     * @return bool TRUE|FALSE
     */
    private function _commentUnhide($id) {
        $modx = $this->modx;
        $lng = $this->lng;

        $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET status=\'1\' '
                . 'WHERE id=' . $id;
        $query = mysql_query($update);
        if (!$query) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['comment_err_update'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
            return FALSE;
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['comment_suc_unhide'];
            return TRUE;
        }
    }

    /**
     * Save comment after edited
     * @param string    $post   values from the input form
     * @return bool     TRUE|FALSE
     */
    private function _commentSave($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $comment = (isset($post['comment']) ? $post['comment'] : $post['hiddencomment']);
        $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'SET comment=\'' . $modx->db->escape(htmlspecialchars($comment, ENT_QUOTES)) . '\' '
                . ', date_edited=NOW() '
                . ', edited_by=\'' . $modx->getLoginUserID() . '\' '
                . 'WHERE id=' . $post['comid'];
        $query = mysql_query($update);
        if (!$query) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['comment_err_update'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
            return FALSE;
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['comment_suc_update'];
            return TRUE;
        }
    }

    /**
     * To delete comments
     * @param int $id comment's ID
     * @return mixed if error, returns report, if true returns true.
     */
    private function _commentDelete($id) {
        $modx = $this->modx;

        $select = 'SELECT file_id FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                . 'WHERE id=' . (int) $id;
        $fileId = mysql_result(mysql_query($select), 0, 0);
        if (!$fileId) {
            $res['err'] = __LINE__ . ' : ' . mysql_error() . '<br />' . $update;
            return $res['err'];
        } else {
            $delete = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_comments '
                    . 'WHERE id=' . (int) $id;
            $query_del = mysql_query($delete);
            if (!$query_del) {
                $res['err'] = __LINE__ . ' : ' . mysql_error() . '<br />' . $delete;
                return $res['err'];
            } else {
                $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files '
                        . 'SET comments=comments-1 '
                        . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                        . 'WHERE id=' . $fileId;
                $query_upd = mysql_query($update);
                if (!$query_upd) {
                    $res['err'] = __LINE__ . ' : ' . mysql_error() . '<br />' . $update;
                    return $res['err'];
                }
            }
            return TRUE;
        }
    }

    /**
     * @todo is this function disposabled?
     * @param <type> $post
     * @param array $files
     * @param <type> $pid
     * @return <type>
     */
    private function _uploadFile($post, $files, $pid) {
        $modx = $this->modx;
        $lng = $this->lng;

        // converting non-latin names with MODx's stripAlias function
        $files['img']['name'] = $modx->stripAlias(trim($files['img']['name']));

        /**
         * CHECK the existing filenames inside the system.
         * If exists, amend the filename with number
         */
        $filteredName = $this->_singleFile($files['img']['name'], $pid);

        $insert = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_files '
                . 'SET dir_id=\'' . $parentId . '\''
                . ', filename=\'' . mysql_real_escape_string($filteredName) . '\''
                . ', size=\'' . (int) $files['img']['size'] . '\''
                . ', name=\'' . mysql_real_escape_string(htmlspecialchars($post['name'])) . '\''
                . ', summary=\'' . mysql_real_escape_string(htmlspecialchars($post['summary'])) . '\''
                . ', tag=\'' . mysql_real_escape_string(htmlspecialchars($post['tag'])) . '\''
                . ', description=\'' . mysql_real_escape_string(htmlspecialchars($post['description'])) . '\''
                . ', date_added=NOW()';
        if (!mysql_query($insert)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['db_err'] . ' : ' . mysql_error() . '<br />' . $insert;
            continue;
        }
        unset($insert);

        $inf = @getimagesize($files['img']['tmp_name']);
        if ($inf[2] > 3)
            continue;
        $this->_resizeImg($files['img']['tmp_name'], $inf, $e2g['maxw'], $e2g['maxh'], $e2g['maxthq']);
        if (!move_uploaded_file($files['img']['tmp_name'], '../' . $this->_e2gDecode($gdir . $filteredName))) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['upload_err'] . ' : ' . '../' . $this->_e2gDecode($gdir . $filteredName);
        }
        $this->_changeModOwnGrp('file', '../' . $this->_e2gDecode($gdir . $filteredName));

        return TRUE;
    }

    /**
     * Save a new plugin
     * @param string    $post   values from the input form
     * @return bool     TRUE|FALSE
     */
    private function _savePlugin($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $eventsArray = array();
        $events = array();
        if (!empty($post['events'])) {
            $eventsArray = $post['events'];
            $events = implode(',', $eventsArray);
        }
        else
            $events=array();

        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_name'];
            return FALSE;
        } else {
            $insert = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_plugins '
                    . 'SET name=\'' . htmlspecialchars(trim($post['name']), ENT_QUOTES) . '\' '
                    . ', description=\'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\' '
                    . ', events=\'' . $events . '\' '
                    . ', indexfile=\'' . urldecode(trim($post['index_file'])) . '\' '
                    . ', disabled=\'' . (int) $post['disabled'] . '\' '
            ;
            $insQuery = mysql_query($insert);
            if (!$insQuery) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['plugin_add_err'] . '<br />' . mysql_error() . ' ' . $insert;
                return FALSE;
            } else {
                $pluginId = mysql_insert_id();
                foreach ($eventsArray as $evtId) {
                    $insertEvt = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_plugin_events '
                            . 'SET pluginid=\'' . $pluginId . '\' '
                            . ', evtid=\'' . $evtId . '\'';
                    $updateEvt = mysql_query($insertEvt);
                    if (!$updateEvt) {
                        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['plugin_update_err'] . '<br />' . mysql_error()
                                . '<br />' . $insertEvt;
                        return FALSE;
                    }
                }
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['plugin_add_suc'];
                unset($insert, $insQuery);
                return TRUE;
            }
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    /**
     * Update changes onplugin editing
     * @param string    $post   values from the input form
     * @return bool     TRUE|FALSE
     */
    private function _updatePlugin($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $eventsArray = array();
        $events = array();
        if (!empty($post['events'])) {
            $eventsArray = $post['events'];
            $events = implode(',', $eventsArray);
        }
        else
            $events=array();

        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_name'];
            return FALSE;
        } elseif (empty($post['plugin_id'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_empty_id'];
            return FALSE;
        } else {
            $pluginId = $post['plugin_id'];
            $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_plugins '
                    . 'SET name=\'' . htmlspecialchars(trim($post['name']), ENT_QUOTES) . '\' '
                    . ', description=\'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\' '
                    . ', events=\'' . $events . '\' '
                    . ', indexfile=\'' . urldecode(trim($post['index_file'])) . '\' '
                    . ', disabled=\'' . (int) $post['disabled'] . '\' '
                    . 'WHERE id=' . $post['plugin_id']
            ;
            $updateQuery = mysql_query($update);
            if (!$updateQuery) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['plugin_update_err'] . '<br />' . mysql_error() . '<br />' . $update;
                return FALSE;
            } else {
                $cleanEvt = mysql_query(
                                'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_plugin_events '
                                . 'WHERE pluginid=\'' . $pluginId . '\''
                );
                if (!$cleanEvt) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['plugin_update_err'] . '<br />' . mysql_error()
                            . '<br />' . $cleanEvt;
                    return FALSE;
                } else {
                    foreach ($eventsArray as $evtId) {
                        $insertEvt = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_plugin_events '
                                . 'SET pluginid=\'' . $pluginId . '\' '
                                . ', evtid=\'' . $evtId . '\'';
                        $updateEvt = mysql_query($insertEvt);
                        if (!$updateEvt) {
                            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['plugin_update_err'] . '<br />' . mysql_error()
                                    . '<br />' . $insertEvt;
                        }
                    }
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['plugin_update_suc'];
                    unset($update, $updateQuery);
                    return TRUE;
                } // if (!$cleanEvt)
            } // if (!$updateQuery)
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    /**
     * Save a new viewer/javascript library
     * @param string    $post   values from the input form
     * @return bool     TRUE|FALSE
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
        } else {
            $insert = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_viewers '
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
            if (!mysql_query($insert)) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['db_err'] . ' : ' . mysql_error() . '<br />' . $insert;
                unset($insert);
                return FALSE;
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['viewer_add_suc'];
                unset($insert);
                return TRUE;
            }
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    /**
     * Update changes on viewer/javascript library editing
     * @param string    $post   values from the input form
     * @return bool     TRUE|FALSE
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
        } else {
            $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_viewers '
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
            $updateQuery = mysql_query($update);
            if (!$updateQuery) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['viewer_update_err'] . '<br />' . mysql_error() . '<br />' . $update;
                unset($update, $updateQuery);
                return FALSE;
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['viewer_update_suc'];
                unset($update, $updateQuery);
                return TRUE;
            }
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    /**
     * Save a new slideshow
     * @param string    $post   values from the input form
     * @return bool     TRUE|FALSE
     */
    private function _saveSlideshow($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $countPost = count($post['name']);
        for ($i = 0; $i < $countPost; $i++) {
            // skipping the dummy form, the zero key
            if (empty($post['name'][$i]))
                continue;
            $insert = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_slideshows '
                    . 'SET name=\'' . htmlspecialchars(trim($post['name'][$i]), ENT_QUOTES) . '\' '
                    . ', description=\'' . htmlspecialchars(trim($post['description'][$i]), ENT_QUOTES) . '\' '
                    . ', indexfile=\'' . urldecode(trim($post['index_file'][$i])) . '\' '
            ;
            $insQuery = mysql_query($insert);
            if (!$insQuery) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['slideshow_add_err'] . '<br />' . mysql_error() . ' ' . $insert;
                unset($countPost, $insert, $insQuery);
                return FALSE;
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['slideshow_add_suc'];
                unset($countPost, $insert, $insQuery);
                return TRUE;
            }
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    /**
     * Update changes on slideshow editing
     * @param string    $post   values from the input form
     * @return bool     TRUE|FALSE
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
        } else {
            $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_slideshows '
                    . 'SET name=\'' . htmlspecialchars(trim($post['name']), ENT_QUOTES) . '\' '
                    . ', description=\'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\' '
                    . ', indexfile=\'' . urldecode(trim($post['index_file'])) . '\' '
                    . 'WHERE id=' . $post['slideshow_id']
            ;
            $updateQuery = mysql_query($update);
            if (!$updateQuery) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['slideshow_update_err'] . '<br />' . mysql_error() . ' ' . $insert;
                unset($update, $updateQuery);
                return FALSE;
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['slideshow_update_suc'];
                unset($update, $updateQuery);
                return TRUE;
            }
        }
        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
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

        // converting non-latin names with MODx's stripAlias function
        $dirName = htmlspecialchars($modx->stripAlias($post['name']), ENT_QUOTES);
        $mkdir = mkdir('../' . $this->_e2gDecode($gdir . $dirName));

        if (empty($post['name'])) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_create_err'] . ' : ' . $lng['err_empty_name'];
            return FALSE;
        }
        if (!$mkdir) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['dir_create_err'] . ' : ' . $lng['err_undefined'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->_e2gDecode($gdir . $dirName);
            return FALSE;
        }

        $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_created'] . ' : ' . $gdir . $dirName;

        $this->_changeModOwnGrp('dir', '../' . $this->_e2gDecode($gdir . $dirName));

        // goldsky -- adds a cover file
        $this->_createsIndexHtml(MODX_BASE_PATH . $this->_e2gDecode($gdir . $dirName) . '/');

        require_once E2G_MODULE_PATH . 'includes/classes/TTree.class.php';
        $tree = new TTree();
        $tree->table = $modx->db->config['table_prefix'] . 'easy2_dirs';
        $id = $tree->insert($dirName, $parentId);
        if ($id) {
            $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
                    . 'SET '
                    . 'cat_alias = \'' . htmlspecialchars(trim($post['alias']), ENT_QUOTES) . '\''
                    . ', cat_summary = \'' . htmlspecialchars(trim($post['summary']), ENT_QUOTES) . '\''
                    . ', cat_tag = \'' . htmlspecialchars(trim($post['tag']), ENT_QUOTES) . '\''
                    . ', cat_description = \'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\''
                    . ', date_added=NOW() '
                    . ', added_by=\'' . $modx->getLoginUserID() . '\' '
                    . 'WHERE cat_id=' . $id;
            mysql_query($update);
            unset($update);

            // invoke the plugin
            $this->_plugin('OnE2GFolderCreateFormSave', array('cat_id' => $id));
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $tree->error;
            $tree->delete($id);
        }
        return TRUE;
    }

    /**
     * Update the database from the directory/folder editing form
     * @param string    $post   values from the input form
     * @param string    $gdir   directory path
     * @return bool     TRUE|FALSE
     */
    private function _editDir($post, $gdir) {
        $modx = $this->modx;
        $lng = $this->lng;

        $newDirName = $modx->stripAlias($post['newdirname']);
        $oldDirName = $post['cat_name'];

        // check the CHMOD permission first, EXCLUDE the root gallery
        if ($post['cat_id'] != 1) {
            $renameDir = rename('../' . $this->_e2gDecode($gdir . $oldDirName)
                            , '../' . $this->_e2gDecode($gdir . $newDirName));
            $this->_changeModOwnGrp('dir', '../' . $this->_e2gDecode($gdir . $newDirName));
        }

        if (!$renameDir && ($post['cat_id'] != 1)) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['update_err'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->_e2gDecode($gdir . $newDirName);
        } else {

            $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_dirs SET ';

            $renameDirConfirm = FALSE;
            if ($post['cat_id'] != '1' && $newDirName != $oldDirName) {
                $renameDirConfirm = TRUE;
                $update .= 'cat_name = \'' . htmlspecialchars(trim($newDirName), ENT_QUOTES) . '\', '; // trailing comma!
            }
            $update .= 'cat_alias = \'' . htmlspecialchars(trim($post['alias']), ENT_QUOTES) . '\''
                    . ', cat_summary = \'' . htmlspecialchars(trim($post['summary']), ENT_QUOTES) . '\''
                    . ', cat_tag = \'' . htmlspecialchars(trim($post['tag']), ENT_QUOTES) . '\''
                    . ', cat_description = \'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\''
                    . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                    . ', last_modified=NOW() '
                    . 'WHERE cat_id=' . $post['cat_id'];
            $updateQuery = mysql_query($update);
            if ($updateQuery) {
                if ($renameDirConfirm === TRUE) {
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_rename_suc'];
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_updated_suc'];
                }
                else
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['dir_updated_suc'];
            } else {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['update_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
                return FALSE;
            }
            mysql_free_result($updateQuery);
            unset($update);

            // Adding webGroup access
            $saveWebGroupsAccess = $this->_saveWebGroupsAccess($post['webGroups'], 'dir', $post['cat_id']);
            if ($saveWebGroupsAccess !== FALSE)
                return TRUE;
        }

        return FALSE;
    }

    /**
     * Update the database from the file editing form
     * @param string    $post   values from the input form
     * @param int       $gdir   parent directory
     * @return bool     TRUE|FALSE
     */
    private function _editFile($post, $gdir) {
        $modx = $this->modx;
        $lng = $this->lng;

        $newFilename = $modx->stripAlias($post['newfilename']);
        $filename = $post['filename'];
        $ext = $post['ext'];

        if ($newFilename != $filename) {
            // check the CHMOD permission first
            $this->_changeModOwnGrp('file', '../' . $this->_e2gDecode($gdir . $filename . $ext));
            $renameFile = rename('../' . $this->_e2gDecode($gdir . $filename . $ext)
                            , '../' . $this->_e2gDecode($gdir . $newFilename . $ext));
            $this->_changeModOwnGrp('file', '../' . $this->_e2gDecode($gdir . $newFilename . $ext));

            if (!$renameFile) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['update_err'];
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $this->_e2gDecode($gdir . $filename . $ext) . ' => '
                        . $this->_e2gDecode($gdir . $newFilename . $ext);
                return FALSE;
            }
        }

        $update = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_files SET ';
        if ($newFilename != $filename) {
            $update .= 'filename = \'' . htmlspecialchars(trim($newFilename) . $ext, ENT_QUOTES) . '\', '; // trailing comma!
        }
        $update .= 'name = \'' . htmlspecialchars(trim($post['name']), ENT_QUOTES) . '\''
                . ', summary = \'' . htmlspecialchars(trim($post['summary']), ENT_QUOTES) . '\''
                . ', tag = \'' . htmlspecialchars(trim($post['tag']), ENT_QUOTES) . '\''
                . ', description = \'' . htmlspecialchars(trim($post['description']), ENT_QUOTES) . '\''
                . ', modified_by=\'' . $modx->getLoginUserID() . '\' '
                . ', last_modified=NOW() '
                . 'WHERE id=' . $post['file_id'];
        $updateQuery = mysql_query($update);
        if ($updateQuery) {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['updated'];
            mysql_free_result($updateQuery);
            unset($update, $updateQuery);
//            return TRUE;
            // Adding webGroup access
            $saveWebGroupsAccess = $this->_saveWebGroupsAccess($post['webGroups'], 'file', $post['file_id']);
            if ($saveWebGroupsAccess !== FALSE)
                return TRUE;
        } else {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['update_err'];
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . mysql_error();
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $update;
            mysql_free_result($updateQuery);
            unset($update, $updateQuery);
            return FALSE;
        }
    }

    /**
     * Get the plugin's number from the events list<br />
     * This is used to simplified any number update on the plugin's form
     * @param string $e2gEvtName Plugin Event Name
     * @return int   the plugin's number
     */
    private function _getEventNum($e2gEvtName) {
        $lng = $this->lng;

        // include the event's names
        if (file_exists(E2G_MODULE_PATH . 'includes/configs/config.events.easy2gallery.php')) {
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
     * @return bool TRUE|FALSE
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
            else {
                $insertUser = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr '
                        . 'SET membergroup_id=\'' . $modxMemberGroups[$i]['id'] . '\'';
                $insertUserQuery = mysql_query($insertUser);
                if (!$insertUserQuery) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_mgr_synchro_err'] . ' ' . mysql_error() . '<br />' . $insertUser;
                    return FALSE;
                } else {
                    $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_mgr_synchro_suc'] . ' : ' . $modxMemberGroups[$i]['name'];
                }
            }
        }

        // deleting e2g groups of non-exist modx groups
        foreach ($e2gMgrGroupIds as $id) {
            if (isset($modxMemberGroupIds[$id]))
                continue;
            else {
                $deleteUser = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr '
                        . 'WHERE membergroup_id=\'' . $id . '\'';
                $deleteUserQuery = mysql_query($deleteUser);
                if (!$deleteUserQuery) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_mgr_synchro_err'] . ' ' . mysql_error() . '<br />' . $deleteUser;
                } else {
                    return TRUE;
                }
            }
        }
    }

    /**
     * Save the E2G's manager access
     * @param $post values from the input form
     * @return bool TRUE|FALSE
     */
    private function _saveMgrAccess($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $countPostMgrAccess = count($post['mgrAccess']);
        $mgrAccess = @implode(',', $post['mgrAccess']);

        $updateUser = 'UPDATE ' . $modx->db->config['table_prefix'] . 'easy2_users_mgr '
                . 'SET permissions=\'' . $mgrAccess . '\' '
                . 'WHERE membergroup_id=\'' . $post['group_id'] . '\'';

        $updateUserQuery = mysql_query($updateUser);
        if (!$updateUserQuery) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_update_err'] . ' ' . mysql_error() . '<br />' . $updateUser;
            return FALSE;
        } else {
            $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_update_suc'];
            return TRUE;
        }
    }

    /**
     * Load the E2G's access for its pages
     */
    private function _loadE2gMgrSessions() {
        $modx = $this->modx;

        /**
         * User Permissions
         */
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
    }

    /**
     * Save the web groups access from the dir/file edit page
     * @param <type> $webGroupIds   web groups in an array
     * @param <type> $type          dir/file
     * @param <type> $id            dir's id / file's id
     * @return bool                 TRUE/FALSE
     */
    private function _saveWebGroupsAccess($webGroupIds, $type, $id) {
        $modx = $this->modx;
        $lng = $this->lng;

        if (empty($webGroupIds) && !isset($type) && !isset($id))
            return FALSE;

        $deletePrevAccess = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                . 'WHERE '
                . 'type=\'' . $type . '\' '
                . 'AND id=\'' . $id . '\' '
        ;

        $deletePrevAccessQuery = mysql_query($deletePrevAccess);

        if (!$deletePrevAccessQuery) {
            return FALSE;
        }

        foreach ($webGroupIds as $webGroupId) {
            $insertAccess = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                    . 'SET '
                    . 'webgroup_id=\'' . $webGroupId . '\' '
                    . ', type=\'' . $type . '\' '
                    . ', id=\'' . $id . '\' ';

            $insertAccessQuery = mysql_query($insertAccess);
            if (!$insertAccessQuery) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Save the web access of directories/folders
     * @param string $post All values from the form
     * @return mixed Saving the access or FALSE on failing
     */
    private function _saveWebDirsAccess($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $deletePrevDirsAccess = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                . 'WHERE webgroup_id=\'' . $post['group_id'] . '\' '
                . 'AND type=\'dir\' '
        ;
        $deletePrevDirsAccessQuery = mysql_query($deletePrevDirsAccess);

        if (!$deletePrevDirsAccessQuery) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_update_err'] . ' ' . mysql_error() . '<br />' . $updateDirsAccess;
            return FALSE;
        }

        $webDirsAccess = $post['webDirsAccess'];
        foreach ($webDirsAccess as $v) {
            $insertDirsAccess = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                    . 'SET webgroup_id=\'' . $post['group_id'] . '\' '
                    . ', type=\'dir\' '
                    . ', id=\'' . $v . '\'';
            $insertDirsAccessQuery = mysql_query($insertDirsAccess);
            if (!$insertDirsAccessQuery) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_update_err'] . ' ' . mysql_error() . '<br />' . $insertDirsAccess;
                return FALSE;
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_update_suc'];
                return TRUE;
            }
        }

        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
    }

    /**
     * Save the web access of files/images
     * @param string $post All values from the form
     * @return mixed Saving the access or FALSE on failing
     */
    private function _saveWebFilesAccess($post) {
        $modx = $this->modx;
        $lng = $this->lng;

        $deletePrevFilesAccess = 'DELETE FROM ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                . 'WHERE webgroup_id=\'' . $post['group_id'] . '\' '
                . 'AND type=\'file\' '
        ;
        $deletePrevFilesAccessQuery = mysql_query($deletePrevFilesAccess);

        if (!$deletePrevFilesAccessQuery) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_update_err'] . ' ' . mysql_error() . '<br />' . $updateFilesAccess;
            return FALSE;
        }

        $webFilesAccess = $post['webFilesAccess'];
        foreach ($webFilesAccess as $v) {
            $insertFilesAccess = 'INSERT INTO ' . $modx->db->config['table_prefix'] . 'easy2_webgroup_access '
                    . 'SET webgroup_id=\'' . $post['group_id'] . '\' '
                    . ', type=\'file\' '
                    . ', id=\'' . $v . '\'';
            $insertFilesAccessQuery = mysql_query($insertFilesAccess);
            if (!$insertFilesAccessQuery) {
                $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['user_update_err'] . ' ' . mysql_error() . '<br />' . $insertFilesAccess;
                return FALSE;
            } else {
                $_SESSION['easy2suc'][] = __LINE__ . ' : ' . $lng['user_update_suc'];
                return TRUE;
            }
        }

        // if something weird happens, this should mark the line!
        $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['err_undefined'];
        return FALSE;
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
                                . 'WHERE type=\'dir\' '
                                . 'AND webgroup_id=\'' . $webGroupId . '\''
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
     * @return bool TRUE|FALSE
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
     * @return bool TRUE|FALSE
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
        $blankIndex = $this->e2gmod_cfg['blank_index'];

        // loading the hyperlinks ($e2gPages)
        require E2G_MODULE_PATH . 'includes/configs/config.pages.easy2gallery.php';

        // delete the config file, because this will always be checked as an upgrade option
        if (file_exists(E2G_MODULE_PATH . 'includes/configs/config.easy2gallery.php')
                && $_GET['e2gpg'] != $e2gPages['config']['e2gpg']
        ) {
            $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['config_save_warning'];
            header("Location: " . html_entity_decode($blankIndex . '&amp;e2gpg=' . $e2gPages['config']['e2gpg']));
        } else
            return TRUE;
    }

    /**
     * Change chmod and chown
     * @param string    $type           dir/file
     * @param string    $fullPath       dir/file path
     * @param bool      $changeMode     TRUE|FALSE to initiate chmod
     * @param bool      $changeGroup    TRUE|FALSE to initiate chown
     * @return bool     TRUE|FALSE
     */
    private function _changeModOwnGrp($type, $fullPath, $changeMode = TRUE, $changeGroup = TRUE) {
        $lng = $this->lng;

        if ($changeMode) {
            clearstatcache();
            $oldPermission = substr(sprintf('%o', fileperms($fullPath)), -4);

            if ($type == 'dir' && $oldPermission != '0755') {
                $newPermission = @chmod(MODX_BASE_PATH . $fullPath, 0755);
                if (!$newPermission) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['chmod_err'] . ' fullPath = ' . $fullPath;
                    $_SESSION['easy2err'][] = __LINE__ . ' : oldPermission = ' . $oldPermission;
                    return FALSE;
                }
            }

            if ($type == 'file' && $oldPermission != '0644') {
                $newPermission = @chmod(MODX_BASE_PATH . $fullPath, 0644);
                if (!$newPermission) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['chmod_err'] . ' fullPath = ' . $fullPath;
                    $_SESSION['easy2err'][] = __LINE__ . ' : oldPermission = ' . $oldPermission;
                    return FALSE;
                }
            }
        }

        if ($changeGroup) {
            $modxPath = MODX_BASE_PATH . "index.php";
            clearstatcache();
            $modxStat = stat($modxPath);
            $ownerCore = $modxStat['uid'];
            $groupCore = $modxStat['gid'];
            $oldFullPath = MODX_BASE_PATH . $fullPath;
            clearstatcache();
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
                if (!$newOwner) {
                    $_SESSION['easy2err'][] = __LINE__ . ' : ' . $lng['chown_err'] . ' fullPath = ' . $fullPath;
                    $_SESSION['easy2err'][] = __LINE__ . ' : old Owner/Group = ' . $ownerOld . '/' . $groupOld;
                    return FALSE;
                }
            }
        }
        
        return TRUE;
    }

}