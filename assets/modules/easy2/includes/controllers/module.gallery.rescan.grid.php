<?php

// harden it
if (!@require_once('../../../../../manager/includes/protect.inc.php'))
    die('Go away!');

// initialize the variables prior to grabbing the config file
$database_type = "";
$database_server = "";
$database_user = "";
$database_password = "";
$dbase = "";
$table_prefix = "";
$base_url = "";
$base_path = "";

// MODx config
if (!@require_once '../../../../../manager/includes/config.inc.php')
    die('Unable to include the MODx\'s config file');

mysql_connect($database_server, $database_user, $database_password) or die('MySQL connect error');
mysql_select_db(str_replace('`', '', $dbase));
@mysql_query("{$database_connection_method} {$database_connection_charset}");

// e2g's configs
$q = mysql_query('SELECT * FROM ' . $table_prefix . 'easy2_configs');
if (!$q)
    die(__FILE__ . ': MySQL query error for configs');
else {
    while ($row = mysql_fetch_assoc($q)) {
        $e2g[$row['cfg_key']] = $row['cfg_val'];
    }
}

// initiate a new document parser
include ('../../../../../manager/includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$modx->getSettings();

// Easy 2 Gallery module path
define('E2G_MODULE_PATH', MODX_BASE_PATH . 'assets/modules/easy2/');
// Easy 2 Gallery module URL
define('E2G_MODULE_URL', MODX_SITE_URL . '../../');

$modClassFile = realpath('../models/e2g.module.class.php');
if (empty($modClassFile) || !file_exists($modClassFile)) {
    die(__FILE__ . ': Missing module class file.');
}
include ($modClassFile);

$e2gMod = new E2gMod($modx);

// LANGUAGE
$lng = E2gPub::languageSwitch($modx->config['manager_language'], E2G_MODULE_PATH);
if (!is_array($lng)) {
    die($lng); // FALSE returned.
}

foreach ($lng as $k => $v) {
    $lng[$k] = $e2gMod->e2gEncode($v);
}

$getRequests = $e2gMod->sanitizedGets($_GET);
if (empty($getRequests)) {
    die('Request is empty');
}

$index = $e2gMod->e2gModCfg['index'];
$index = str_replace('assets/modules/easy2/includes/controllers/', '', $index);

$rootDir = '../../../../../' . $e2g['dir'];
$rootDir = rtrim($rootDir, '/') . '/'; // just to make sure there is a slash at the end path
$rootRealPath = realpath($rootDir);
if (empty($rootRealPath)) {
    echo __LINE__ . ' : Root Path is not real : ' . $rootDir . '<br />';
    die();
}
$pidPath = $e2gMod->getPath($getRequests['pid']);
$decodedPath = $e2gMod->e2gDecode($getRequests['path']);
$gdir = $e2g['dir'] . $getRequests['path'];

if ($getRequests['path'] == $pidPath) {
    ####################################################################
    ####                      MySQL Dir list                        ####
    ####################################################################
    $selectDirs = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs' . ' '
            . 'WHERE parent_id = ' . $getRequests['pid'] . ' '
            . 'ORDER BY cat_name ASC'
    ;
    $querySelectDirs = mysql_query($selectDirs);
    if (!$querySelectDirs) {
        $msg = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs;
        die($msg);
    }

    $rows = array(); // for return
    $mdirs = array();
    while ($l = mysql_fetch_assoc($querySelectDirs)) {
        $mdirs[$l['cat_name']] = $l;
    }
    mysql_free_result($querySelectDirs);
}

$galPh = array();

$galPh['th.selectAll'] = '<input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;" />';
$galPh['th.actions'] = $lng['actions'];
$galPh['th.type'] = $lng['type'];
$galPh['th.name'] = $lng['dir'] . ' / ' . $lng['filename'];
$galPh['th.alias'] = $lng['alias'] . ' / ' . $lng['name'];
$galPh['th.tag'] = $lng['tag'];
$galPh['th.date'] = $lng['date'];
$galPh['th.size'] = $lng['size'] . ' (Kb)';
$galPh['th.w'] = 'W (px)';
$galPh['th.h'] = 'H (px)';
$galPh['td.fileDefaultTableContent'] = '';

$rowClass = array(' class="gridAltItem"', ' class="gridItem"');
$rowNum = 0;

header('Content-Type: text/html; charset=\'' . $lng['charset'] . '\'');
//******************************************************************/
//***************** FOLDERS/DIRECTORIES/GALLERIES ******************/
//******************************************************************/
$scanDirs = @glob($rootDir . $decodedPath . '/*', GLOB_ONLYDIR);
if (FALSE !== $scanDirs):

    if (is_array($scanDirs))
        natsort($scanDirs);

    foreach ($scanDirs as $scanDir) {
        ob_start();
        if (!$e2gMod->validFolder($scanDir)) {
            continue;
        }

        $realPathDir = realpath($scanDir);
        if (!empty($realPathDir)) {
            $dirName = $e2gMod->basenameSafe($realPathDir);
        } else {
            $dirName = basename($scanDir);
        }

        $dirName = $e2gMod->e2gEncode($dirName);
        if ($dirName == '_thumbnails')
            continue;

        $dirStyledName = $dirName; // will be overridden for styling below
        $dirNameUrlDecodeDirname = urldecode($dirName);
        $dirPathRawUrlEncoded = str_replace('%2F', '/', rawurlencode($gdir . $dirName));
        switch ($e2g['mod_foldersize']) {
            case 'auto':
                $dirPhRow['td.count'] = '( ' . $e2gMod->countFiles($realPathDir) . ' )';
                break;
            case 'ajax':
                $dirPhRow['td.count'] = '( <span id="countfiles_' . $dirPathRawUrlEncoded . '"><span id="countfileslink_' . $dirPathRawUrlEncoded . '"><a href="javascript:;" onclick="countFiles(\'' . base64_encode($realPathDir) . '\', \'' . $dirPathRawUrlEncoded . '\')">' . $lng['folder_size'] . '</a></span></span> )';
                break;
            default:
                $dirPhRow['td.count'] = '';
                break;
        }

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
                    width="16" height="16" border="0" alt="folder" title="' . $lng['dir'] . '" />
                ';
        if (!empty($mdirs[$dirName]['cat_redirect_link'])) {
            $dirIcon .= '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/link.png" width="16"
                    height="16" alt="link" title="' . $lng['redirect_link'] . ': ' . $mdirs[$dirName]['cat_redirect_link'] . '" border="0" />
                        ';
        }
        $dirButtons = '';

        if (isset($mdirs[$dirName])) {
            $dirId = $mdirs[$dirName]['cat_id'];
            $dirAlias = $mdirs[$dirName]['cat_alias'];
            $dirTag = $mdirs[$dirName]['cat_tag'];
            $dirTagLinks = $e2gMod->createTagLinks($dirTag, $index);
            $dirTime = $e2gMod->getTime($mdirs[$dirName]['date_added'], $mdirs[$dirName]['last_modified'], $scanDir);

            // Checkbox
            $dirCheckBox = '
                <input name="dir[' . $dirId . ']" value="' . $dirPathRawUrlEncoded . '" type="checkbox" style="border:0;padding:0" />
                ';
            if ($mdirs[$dirName]['cat_visible'] == '1') {
                $dirStyledName = '<b>' . $dirName . '</b>';
                $dirHref = $index . '&amp;pid=' . $mdirs[$dirName]['cat_id'];
                $dirButtons = $e2gMod->actionIcon('hide_dir', array(
                    'act' => 'hide_dir'
                    , 'dir_id' => $dirId
                    , 'pid' => $getRequests['pid']
                        ), null, $index);
            } else {
                $dirStyledName = '<i>' . $dirName . '</i>';
                $dirAttributes = '<i>(' . $lng['hidden'] . ')</i>';
                $dirAttributeIcons = '
                <a href="' . $index . '&amp;act=unhide_dir&amp;dir_id=' . $dirId . '&amp;name=' . $dirName . '&amp;pid=' . $getRequests['pid'] . '">
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16"
                        height="16" alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />
                </a>
                ';
                $dirHref = $index . '&amp;pid=' . $mdirs[$dirName]['cat_id'];
                $dirButtons = $e2gMod->actionIcon('unhide_dir', array(
                    'act' => 'unhide_dir'
                    , 'dir_id' => $dirId
                    , 'pid' => $getRequests['pid']
                        ), null, $index);
            }
            // edit dir
            $dirButtons .= $e2gMod->actionIcon('edit_dir', array(
                'page' => 'edit_dir'
                , 'dir_id' => $dirId
                , 'pid' => $getRequests['pid']
                    ), null, $index);
            // unset this to leave the deleted dirs from file system.
            unset($mdirs[$dirName]);
        } // if (isset($mdirs[$dirName]))
        else {
            /**
             * Existing dir in file system, but has not yet inserted into database
             */
            // Checkbox
            $dirCheckBox = '
                <input name="dir[d' . $rowNum . ']" value="' . $dirPathRawUrlEncoded . '" type="checkbox" style="border:0;padding:0" />
                ';
            // add dir
            $dirButtons .= $e2gMod->actionIcon('add_dir', array(
                'act' => 'add_dir'
                , 'dir_path' => $dirPathRawUrlEncoded
                , 'pid' => $getRequests['pid']
                    ), null, $index);
            $dirTime = date($e2gMod->e2g['mod_date_format'], filemtime($scanDir));
            clearstatcache();
            $dirStyledName = '<b style="color:gray">' . $dirName . '</b>';
            $dirAttributes = '<i>(' . $lng['new'] . ')</i>';
            $dirHref = $index
                    . (!empty($getRequests['pid']) ? '&amp;pid=' . $getRequests['pid'] : '')
                    . '&amp;path=' . (!empty($getRequests['path']) ? $getRequests['path'] . '/' : '') . $dirName;
            $dirId = NULL;
            $dirIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_add.png" width="16"
                    height="16" alt="' . $lng['add_to_db'] . '" border="0" />
                    ';
        }

        if (!empty($dirId)) {
            $dirButtons .= $e2gMod->actionIcon('delete_dir', array(
                'act' => 'delete_dir'
                , 'dir_path' => $dirPathRawUrlEncoded
                , 'dir_id' => $dirId
                    ), 'onclick="return confirmDeleteFolder();"', $index);
        } else {
            $dirButtons .= $e2gMod->actionIcon('delete_dir', array(
                'act' => 'delete_dir'
                , 'dir_path' => $dirPathRawUrlEncoded
                    ), 'onclick="return confirmDeleteFolder();"', $index);
        }

        $dirPhRow['td.rowNum'] = $rowNum;
        $dirPhRow['td.rowClass'] = $rowClass[$rowNum % 2];
        $dirPhRow['td.checkBox'] = $dirCheckBox;
        $dirPhRow['td.id'] = $dirId;
        $dirPhRow['td.gid'] = empty($dirId) ? '' : '[id: ' . $dirId . ']';
        $dirPhRow['td.name'] = $dirName;
        $dirPhRow['td.styledName'] = $dirStyledName;
        $dirPhRow['td.path'] = $scanDir;
        $dirPhRow['td.pathRawUrlEncoded'] = $dirPathRawUrlEncoded;
        $dirPhRow['td.alias'] = $dirAlias;
        $dirPhRow['td.title'] = ( trim($dirAlias) != '' ? $dirAlias : $dirName);
        $dirPhRow['td.tagLinks'] = $dirTagLinks;
        $dirPhRow['td.time'] = $dirTime;
        $dirPhRow['td.attributes'] = $dirAttributes;
        $dirPhRow['td.attributeIcons'] = $dirAttributeIcons;
        $dirPhRow['td.href'] = $dirHref;
        $dirPhRow['td.buttons'] = $dirButtons;
        $dirPhRow['td.icon'] = $dirIcon;
        $dirPhRow['td.size'] = '---';
        $dirPhRow['td.w'] = '---';
        $dirPhRow['td.h'] = '---';
        $dirPhRow['td.mod_w'] = $e2g['mod_w'];
        $dirPhRow['td.mod_h'] = $e2g['mod_h'];
        $dirPhRow['td.mod_thq'] = $e2g['mod_thq'];

        ###################################################################

        $dirPhRow['td.rowDir'] = '<a href="' . $dirPhRow['td.href'] . '">'
                . $dirPhRow['td.styledName']
                . '</a> '
                . $dirPhRow['td.gid'] . ' ' . $dirPhRow['td.count'] . ' ' . $dirPhRow['td.attributes']
        ;

        $galPh['td.fileDefaultTableContent'] .= $e2gMod->filler($e2gMod->getTpl('file_default_table_row_dir_tpl'), $dirPhRow);
        $rowNum++;
        ob_flush();
        /**
         * to deal with thousands of pictures, this will make the script
         * sleeps for 10 ms
         */
        usleep(10);
    } // foreach ($dirs as $scanDir)
    ob_end_flush();

    /**
     * Deleted dirs from file system, but still exists in database,
     * which have been left from the above unsetting.
     */
    if (isset($mdirs) && count($mdirs) > 0) {
        foreach ($mdirs as $v) {
            $dirPhRow['td.rowNum'] = $rowNum;
            $dirPhRow['td.rowClass'] = $rowClass[$rowNum % 2];
            $dirPhRow['td.checkBox'] = '
                    <input name="dir[' . $v['cat_id'] . ']" value="dir[' . $v['cat_id'] . ']" type="checkbox" style="border:0;padding:0" />
                        ';
            $dirPhRow['td.id'] = $v['cat_id'];
            $dirPhRow['td.gid'] = '[id: ' . $v['cat_id'] . ']';
            $dirPhRow['td.name'] = $v['cat_name'];
            $dirPhRow['td.styledName'] = '<b style="color:red;"><u>' . $v['cat_name'] . '</u></b>';
            $dirPhRow['td.path'] = '';
            $dirPhRow['td.alias'] = $v['cat_alias'];
            $dirPhRow['td.title'] = ( trim($v['cat_alias']) != '' ? $v['cat_alias'] : $v['cat_name']);
            $dirPhRow['td.tagLinks'] = $e2gMod->createTagLinks($v['cat_tag'], $index);
            $dirPhRow['td.time'] = $e2gMod->getTime($v['date_added'], $v['last_modified'], '');
            $dirPhRow['td.count'] = intval("0");
            $dirPhRow['td.link'] = '<b style="color:red;"><u>' . $v['cat_name'] . '</u></b>';
            $dirPhRow['td.attributes'] = '<i>(' . $lng['deleted'] . ')</i>';
            $dirPhRow['td.attributeIcons'] = '';

            $dirPhRow['td.href'] = '';

            $dirPhRow['td.buttons'] = $e2gMod->actionIcon('delete_dir', array(
                'act' => 'delete_dir'
                , 'dir_id' => $v['cat_id']
                , 'pid' => $getRequests['pid']
                    ), 'onclick="return confirmDeleteFolder();"', $index);
            $deletedDirIcon = '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_delete.png"
                        width="16" height="16" border="0" alt="folder_delete.png" title="' . $lng['deleted'] . '" />
                    ';
            if (!empty($v['cat_redirect_link'])) {
                $deletedDirIcon .= '
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/link.png" width="16"
                        height="16" alt="link" title="' . $lng['redirect_link'] . ': ' . $mdirs[$dirName]['cat_redirect_link'] . '" border="0" />
                            ';
            }
            $dirPhRow['td.icon'] = $deletedDirIcon;

            $dirPhRow['td.mod_w'] = $e2g['mod_w'];
            $dirPhRow['td.mod_h'] = $e2g['mod_h'];
            $dirPhRow['td.mod_thq'] = $e2g['mod_thq'];

            ###################################################################

            $dirPhRow['td.rowDir'] = $dirPhRow['td.styledName'] . ' ' . $dirPhRow['td.gid'] . ' ' . $dirPhRow['td.attributes'];

            $galPh['td.fileDefaultTableContent'] .= $e2gMod->filler($e2gMod->getTpl('file_default_table_row_dir_tpl'), $dirPhRow);

            $rowNum++;
        } // foreach ($mdirs as $k => $v)
    } // if (isset($mdirs) && count($mdirs) > 0)

endif; // if (FALSE !== $scanDirs)
############################# DIR LIST ENDS ############################
//******************************************************************/
//************* FILE content for the current directory *************/
//******************************************************************/

if ($getRequests['path'] == $pidPath) {
    ####################################################################
    ####                      MySQL File list                       ####
    ####################################################################
    $selectFiles = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
            . 'WHERE dir_id = ' . $getRequests['pid'];
    $querySelectFiles = mysql_query($selectFiles);
    if (!$querySelectFiles) {
        $msg = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
        die($msg);
    }
    $mfiles = array();
    while ($l = mysql_fetch_assoc($querySelectFiles)) {
        $mfiles[$l['filename']] = $l;
    }
    mysql_free_result($querySelectFiles);
}

$scanFiles = @glob($rootDir . $decodedPath . '/*');
if (FALSE !== $scanFiles):

    if (is_array($scanFiles))
        natsort($scanFiles);

    foreach ($scanFiles as $scanFile) {

        if ($e2gMod->validFolder($scanFile)
                || !$e2gMod->validFile($scanFile)
        ) {
            continue;
        }

        ob_start();
        $realPathFile = realpath($scanFile);
        if (!empty($realPathDir)) {
            $filename = $e2gMod->basenameSafe($realPathFile);
        } else {
            $filename = basename($scanFile);
        }

        $filename = $e2gMod->e2gEncode($filename);
        $fileStyledName = $filename; // will be overridden for styling below
        $fileNameUrlDecodeFilename = urldecode($filename);
        $filePathRawUrlEncoded = str_replace('%2F', '/', rawurlencode($gdir . $filename));
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
        if (!empty($mfiles[$filename]['redirect_link'])) {
            $fileIcon .= '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/link.png" width="16"
                    height="16" alt="link" title="' . $lng['redirect_link'] . ': ' . $mfiles[$filename]['redirect_link'] . '" border="0" />
                        ';
        }
        $fileButtons = '';

        if (isset($mfiles[$filename])) {
            $fileId = $mfiles[$filename]['id'];
            $fileAlias = $mfiles[$filename]['alias'];
            $fileTagLinks = $e2gMod->createTagLinks($mfiles[$filename]['tag'], $index);
            // Checkbox
            $fileCheckBox = '
                <input name="im[' . $fileId . ']" value="' . $filePathRawUrlEncoded . '" type="checkbox" style="border:0;padding:0" />
                ';
            $tag = $mfiles[$filename]['tag'];
            $fileSize = round($mfiles[$filename]['size'] / 1024);
            $width = $mfiles[$filename]['width'];
            $height = $mfiles[$filename]['height'];
            $fileTime = $e2gMod->getTime($mfiles[$filename]['date_added'], $mfiles[$filename]['last_modified'], $scanFile);

            if ($mfiles[$filename]['status'] == '1') {
                $fileButtons = $e2gMod->actionIcon('hide_file', array(
                    'act' => 'hide_file'
                    , 'file_id' => $fileId
                    , 'pid' => $getRequests['pid']
                        ), null, $index);
            } else {
                $fileStyledName = '<i>' . $filename . '</i>';
                $fileAttributes = '<i>(' . $lng['hidden'] . ')</i>';
                $fileAttributeIcons = $e2gMod->actionIcon('unhide_file', array(
                    'act' => 'unhide_file'
                    , 'file_id' => $fileId
                    , 'pid' => $getRequests['pid']
                        ), null, $index);
                $fileButtons = $e2gMod->actionIcon('unhide_file', array(
                    'act' => 'unhide_file'
                    , 'file_id' => $fileId
                    , 'pid' => $getRequests['pid']
                        ), null, $index);
            }
            $fileButtons .= $e2gMod->actionIcon('comments', array(
                'page' => 'comments'
                , 'file_id' => $fileId
                , 'pid' => $getRequests['pid']
                    ), null, $index);

            $fileButtons .= $e2gMod->actionIcon('edit_file', array(
                'page' => 'edit_file'
                , 'file_id' => $fileId
                , 'pid' => $getRequests['pid']
                    ), null, $index);

            unset($mfiles[$filename]);
        } else {
            /**
             * Existed files in file system, but not yet inserted into database
             */
            // Checkbox
            $fileCheckBox = '
                <input name="im[f' . $rowNum . ']" value="im[f' . $rowNum . ']" type="checkbox" style="border:0;padding:0" />
                ';
            $fileTime = date($e2gMod->e2g['mod_date_format'], filemtime($scanFile));
            $fileStyledName = '<span style="color:gray"><b>' . $filename . '</b></span>';
            $fileAttributes = '<i>(' . $lng['new'] . ')</i>';
            $fileId = NULL;
            $fileIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_add.png" width="16" height="16" border="0" alt="" />
                ';
            $fileAttributeIcons = '';
            // add file
            $fileButtons .= $e2gMod->actionIcon('add_file', array(
                'act' => 'add_file'
                , 'file_path' => $filePathRawUrlEncoded
                , 'pid' => $getRequests['pid']
                    ), null, $index);
            $fileSize = round(filesize($scanFile) / 1024);
            list($width, $height) = @getimagesize($scanFile);
        }

        $fileButtons .= $e2gMod->actionIcon('delete_file', array(
            'act' => 'delete_file'
            , 'pid' => $getRequests['pid']
            , 'file_id' => $fileId
            , 'file_path' => $filePathRawUrlEncoded
                ), 'onclick="return confirmDelete();"', $index);

        $filePhRow['td.rowNum'] = $rowNum;
        $filePhRow['td.rowClass'] = $rowClass[$rowNum % 2];
        $filePhRow['td.checkBox'] = $fileCheckBox;
        $filePhRow['td.dirId'] = $getRequests['pid'];
        $filePhRow['td.id'] = $fileId;
        $filePhRow['td.fid'] = empty($fileId) ? '' : '[id:' . $fileId . ']';
        $filePhRow['td.name'] = $filename;
        $filePhRow['td.styledName'] = $fileStyledName;
        $filePhRow['td.alias'] = $fileAlias;
        $filePhRow['td.title'] = ( trim($fileAlias) != '' ? $fileAlias : $filename);
        $filePhRow['td.tagLinks'] = $fileTagLinks;
        $filePhRow['td.path'] = $rootDir;
        $filePhRow['td.pathRawUrlEncoded'] = $filePathRawUrlEncoded;
        $filePhRow['td.time'] = $fileTime;
        $filePhRow['td.attributes'] = $fileAttributes;
        $filePhRow['td.attributeIcons'] = $fileAttributeIcons;
        $filePhRow['td.buttons'] = $fileButtons;
        $filePhRow['td.icon'] = $fileIcon;
        $filePhRow['td.size'] = $fileSize;
        $filePhRow['td.w'] = $width;
        $filePhRow['td.h'] = $height;
        $filePhRow['td.mod_w'] = $e2g['mod_w'];
        $filePhRow['td.mod_h'] = $e2g['mod_h'];
        $filePhRow['td.mod_thq'] = $e2g['mod_thq'];

        ####################################################################

        $filePhRow['td.rowFile'] = '
                <a href="javascript:void(0)"'
                . ' onclick="imPreview(\''
                . $filePhRow['td.pathRawUrlEncoded'] . '\', '
                . $filePhRow['td.rowNum'] . ');">'
                . $filePhRow['td.styledName']
                . '</a> ' . $filePhRow['td.fid'] . ' ' . $filePhRow['td.attributes'] . '
                <div class="imPreview" id="rowPreview_' . $filePhRow['td.rowNum'] . '" style="display:none;"></div>
';

        $galPh['td.fileDefaultTableContent'] .= $e2gMod->filler($e2gMod->getTpl('file_default_table_row_file_tpl'), $filePhRow);
        $rowNum++;

        ob_flush();
        /**
         * to deal with thousands of pictures, this will make the script
         * sleeps for 10 ms
         */
        usleep(10);
    } // foreach ($dirs as $scanFile)
    ob_end_flush();

    /**
     * Deleted files from file system, but still exists in database
     */
    if (isset($mfiles) && count($mfiles) > 0) {
        foreach ($mfiles as $k => $v) {
            $filePhRow['td.rowNum'] = $rowNum;
            $filePhRow['td.rowClass'] = $rowClass[$rowNum % 2];
            $filePhRow['td.checkBox'] = '
                <input name="im[' . $v['id'] . ']" value="' . $v['id'] . '" type="checkbox" style="border:0;padding:0" />
                ';
            $filePhRow['td.dirId'] = $getRequests['pid'];
            $filePhRow['td.id'] = $v['id'];
            $filePhRow['td.fid'] = '[id:' . $v['id'] . ']';
            $filePhRow['td.name'] = $v['filename'];
            $filePhRow['td.styledName'] = '<b style="color:red;"><u>' . $v['filename'] . '</u></b>';
            $filePhRow['td.alias'] = $v['alias'];
            $filePhRow['td.title'] = ( trim($v['alias']) != '' ? $v['alias'] : $v['filename']);
            $filePhRow['td.tagLinks'] = $e2gMod->createTagLinks($v['tag'], $index);
            $filePhRow['td.path'] = $gdir;
            $filePhRow['td.pathRawUrlEncoded'] = str_replace('%2F', '/', rawurlencode($gdir . $v['filename']));
            $filePhRow['td.time'] = $e2gMod->getTime($v['date_added'], $v['last_modified'], '');
            $filePhRow['td.attributes'] = '<i>(' . $lng['deleted'] . ')</i>';
            $filePhRow['td.attributeIcons'] = '';

            $filePhRow['td.buttons'] = $e2gMod->actionIcon('delete_file', array(
                'act' => 'delete_file'
                , 'file_id' => $v['id']
                , 'pid' => $getRequests['pid']
                    ), 'onclick="return confirmDelete();"', $index);
            $filePhRow['td.icon'] = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_delete.png" width="16" height="16" border="0" alt="" />
                ';
            $filePhRow['td.size'] = round($v['size'] / 1024);
            $filePhRow['td.w'] = $v['width'];
            $filePhRow['td.h'] = $v['height'];
            $filePhRow['td.mod_w'] = $e2g['mod_w'];
            $filePhRow['td.mod_h'] = $e2g['mod_h'];
            $filePhRow['td.mod_thq'] = $e2g['mod_thq'];

            ###################################################################

            $filePhRow['td.rowFile'] = $filePhRow['td.styledName'] . ' ' . $filePhRow['td.fid'] . ' ' . $filePhRow['td.attributes'];
            $galPh['td.fileDefaultTableContent'] .= $e2gMod->filler($e2gMod->getTpl('file_default_table_row_file_tpl'), $filePhRow);

            $rowNum++;
        } // foreach ($mfiles as $k => $v)
    } // if (isset($mfiles) && count($mfiles) > 0)

endif; // if (FALSE !== $scanFiles)

echo $e2gMod->filler($e2gMod->getTpl('file_default_table_tpl'), $galPh);

exit();