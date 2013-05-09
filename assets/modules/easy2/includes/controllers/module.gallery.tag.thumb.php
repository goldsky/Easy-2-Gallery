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
if (empty ($modClassFile) || !file_exists($modClassFile)) {
    die(__FILE__ . ': Missing module class file.');
}
include ($modClassFile);

$e2gMod = new E2gMod($modx);

// LANGUAGE
$lng = E2gPub::languageSwitch($modx->config['manager_language'],E2G_MODULE_PATH);
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
$tag = $getRequests['tag'];
$gdir = $e2g['dir'] . $getRequests['path'];

//******************************************************************/
//**************************** Dir tags ****************************/
//******************************************************************/
$selectDirs = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_dirs '
        . 'WHERE cat_tag LIKE \'%' . $tag . '%\' '
        . 'ORDER BY cat_name ASC';
$querySelectDirs = mysql_query($selectDirs);
if (!$querySelectDirs) {
    $msg = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs;
    die($msg);
}

$fetchDirs = array();
while ($l = mysql_fetch_assoc($querySelectDirs)) {
    $fetchDirs[$l['cat_name']] = $l;
}
mysql_free_result($querySelectDirs);
uksort($fetchDirs, "strnatcmp");

//******************************************************************/
//*************************** FILE tags ****************************/
//******************************************************************/
$selectFiles = 'SELECT * FROM ' . $modx->db->config['table_prefix'] . 'easy2_files '
        . 'WHERE tag LIKE \'%' . $tag . '%\' '
        . 'ORDER BY id ASC';
$querySelectFiles = mysql_query($selectFiles);
if (!$querySelectFiles) {
    $msg = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
    die($msg);
}

$fetchFiles = array();
while ($l = mysql_fetch_assoc($querySelectFiles)) {
    $fetchFiles[$l['filename']] = $l;
}
mysql_free_result($querySelectFiles);
uksort($fetchFiles, "strnatcmp");

$rowClass = array(' class="gridAltItem"', ' class="gridItem"');
$rowNum = 0;

header('Content-Type: text/html; charset=\'' . $lng['charset'] . '\'');
#########################     DIRECTORIES      #########################
$dirPhRow = array();

foreach ($fetchDirs as $fetchDir) {
    // goldsky -- store the array to be connected between db <--> fs
    $dirPhRow['thumb.parent_id'] = $fetchDir['parent_id'];
    $dirPhRow['thumb.id'] = $fetchDir['cat_id'];
    $dirPhRow['thumb.name'] = $fetchDir['cat_name'];
    $dirPhRow['thumb.alias'] = $fetchDir['cat_alias'];
    $dirPhRow['thumb.tag'] = $fetchDir['cat_tag'];
    $dirPhRow['thumb.cat_visible'] = $fetchDir['cat_visible'];
    $dirPhRow['thumb.date_added'] = $fetchDir['date_added'];
    $dirPhRow['thumb.last_modified'] = $fetchDir['last_modified'];

    ####################### Template placeholders ######################

    $dirPhRow['thumb.rowNum'] = $rowNum;
    $dirPhRow['thumb.rowClass'] = $rowClass[$rowNum % 2];
    $dirPath = $gdir . $e2gMod->getPath($fetchDir['parent_id']);
    $dirPhRow['thumb.checkBox'] = '
                <input name="dir[' . $fetchDir['cat_id'] . ']" value="' . rawurldecode($dirPath . $fetchDir['cat_name']) . '" type="checkbox" style="border:0;padding:0" />
                ';
    $dirPhRow['thumb.gid'] = '[id: ' . $fetchDir['cat_id'] . ']';
    $dirPhRow['thumb.path'] = $dirPath;
    $dirPhRow['thumb.pathRawUrlEncoded'] = str_replace('%2F', '/', rawurlencode($dirPath . $fetchDir['cat_name']));
    $dirPhRow['thumb.title'] = ( trim($fetchDir['cat_alias']) != '' ? $fetchDir['cat_alias'] : $fetchDir['cat_name']);

    $dirStyledName = $fetchDir['cat_name']; // will be overridden for styling below
    $dirCheckBox = '';
    $dirLink = '';
    $dirAttributes = '';
    $dirAttributeIcons = '';
    $dirIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder.png"
                    width="16" height="16" border="0" alt="" />
                ';
    if (!empty($fetchDir['cat_redirect_link'])) {
        $dirIcon .= '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/link.png" width="16"
                    height="16" alt="link" title="' . $lng['redirect_link'] . ': ' . $fetchDir['cat_redirect_link'] . '" border="0" />
                        ';
    }
    $dirButtons = '';

    if ($fetchDir['cat_visible'] == '1') {
        $dirStyledName = '<b>' . $fetchDir['cat_name'] . '</b>';
        $dirButtons = $e2gMod->actionIcon('hide_dir', array(
                    'act' => 'hide_dir'
                    , 'dir_id' => $fetchDir['cat_id']
                    , 'pid' => $fetchDir['parent_id']
                        ), null, $index);
    } else {
        $dirAttributes = '<i>(' . $lng['hidden'] . ')</i>';
        $dirAttributeIcons = '
                <a href="' . $index . '&amp;act=unhide_dir&amp;dir_id=' . $fetchDir['cat_id'] . '&amp;pid=' . $fetchDir['parent_id'] . '">
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16" height="16" alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />
                </a>
                ';
        $dirButtons = $e2gMod->actionIcon('unhide_dir', array(
                    'act' => 'unhide_dir'
                    , 'dir_id' => $fetchDir['cat_id']
                    , 'pid' => $fetchDir['parent_id']
                        ), null, $index);
    }

    $dirButtons .= $e2gMod->actionIcon('edit_dir', array(
                'page' => 'edit_dir'
                , 'dir_id' => $fetchDir['cat_id']
                , 'tag' => $tag
                    ), null, $index);
    $dirButtons .= $e2gMod->actionIcon('delete_dir', array(
                'act' => 'delete_dir'
                , 'dir_path' => $dirPath . $fetchDir['cat_name']
                , 'dir_id' => $fetchDir['cat_id']
                , 'tag' => $tag
                    ), 'onclick="return confirmDeleteFolder();"', $index);

    $dirPhRow['thumb.styledName'] = $dirStyledName;
    $dirPhRow['thumb.attributes'] = $dirAttributes;
    $dirPhRow['thumb.attributeIcons'] = $dirAttributeIcons;
    $dirPhRow['thumb.href'] = $index . '&amp;pid=' . $fetchDir['cat_id'];
    $dirPhRow['thumb.buttons'] = $dirButtons;
    $dirPhRow['thumb.icon'] = $dirIcon;
    $dirPhRow['thumb.size'] = '---';
    $dirPhRow['thumb.w'] = '---';
    $dirPhRow['thumb.h'] = '---';
    $dirPhRow['thumb.mod_w'] = $e2g['mod_w'];
    $dirPhRow['thumb.mod_h'] = $e2g['mod_h'];
    $dirPhRow['thumb.mod_thq'] = $e2g['mod_thq'];

    ############################################################################

    $dirPhRow['thumb.src'] = '';
    $dirPhRow['thumb.thumb'] = '';
    if (!empty($dirPhRow['thumb.id'])) {
        // search image for subdir
        $folderImgInfos = $e2gMod->folderImg($dirPhRow['thumb.id'], $rootDir);

        // if there is an empty folder, or invalid content
        if ($folderImgInfos === FALSE) {
            $imgPreview = E2G_MODULE_URL . 'preview.easy2gallery.php?path='
                    . $dirPhRow['thumb.pathRawUrlEncoded']
                    . '&amp;mod_w=' . $dirPhRow['thumb.mod_w']
                    . '&amp;mod_h=' . $dirPhRow['thumb.mod_h']
                    . '&amp;text=' . $lng['empty']
            ;
            $dirPhRow['thumb.thumb'] = '
            <a href="' . $dirPhRow['thumb.href'] . '">
                <img src="' . $imgPreview
                    . '" alt="' . $dirPhRow['thumb.path'] . $dirPhRow['thumb.name']
                    . '" title="' . $dirPhRow['thumb.title']
                    . '" width="' . $dirPhRow['thumb.mod_w']
                    . '" height="' . $dirPhRow['thumb.mod_h']
                    . '" />
            </a>
';
        } else {
            // path to subdir's thumbnail
            $pathToImg = $e2gMod->getPath($folderImgInfos['dir_id']);
            $imgShaper = $e2gMod->imgShaper($rootDir
                            , $pathToImg . $folderImgInfos['filename']
                            , $dirPhRow['thumb.mod_w']
                            , $dirPhRow['thumb.mod_w']
                            , $dirPhRow['thumb.mod_thq']);
            if ($imgShaper === FALSE) {
                // folder has been deleted
                $imgPreview = E2G_MODULE_URL . 'preview.easy2gallery.php?path='
                        . $dirPhRow['thumb.pathRawUrlEncoded']
                        . '&amp;mod_w=' . $dirPhRow['thumb.mod_w']
                        . '&amp;mod_h=' . $dirPhRow['thumb.mod_h']
                        . '&amp;text=' . $lng['deleted']
                ;
                $imgSrc = E2G_MODULE_URL . 'preview.easy2gallery.php?path='
                        . $dirPhRow['thumb.pathRawUrlEncoded']
                        . '&amp;mod_w=300'
                        . '&amp;mod_h=100'
                        . '&amp;text=' . $lng['deleted']
                        . '&amp;th=5';
                $dirPhRow['thumb.thumb'] = '
            <a href="' . $imgSrc
                        . '" class="highslide" onclick="return hs.expand(this)"'
                        . ' title="' . $dirPhRow['thumb.name'] . ' ' . $dirPhRow['thumb.gid'] . ' ' . $dirPhRow['thumb.attributes']
                        . '">
                <img src="' . $imgPreview
                        . '" alt="' . $dirPhRow['thumb.path'] . $dirPhRow['thumb.name']
                        . '" title="' . $dirPhRow['thumb.title']
                        . '" width="' . $dirPhRow['thumb.mod_w']
                        . '" height="' . $dirPhRow['thumb.mod_h']
                        . '" />
            </a>
';
                unset($imgPreview);
            } else {
                /**
                 * $imgShaper returns the URL to the image
                 */
                $dirPhRow['thumb.src'] = $imgShaper;

                /**
                 * @todo: AJAX call to the image
                 */
                $dirPhRow['thumb.thumb'] = '
            <a href="' . $dirPhRow['thumb.href'] . '">
                <img src="' . '../' . str_replace('../', '', $imgShaper)
                        . '" alt="' . $dirPhRow['thumb.name']
                        . '" title="' . $dirPhRow['thumb.title']
                        . '" width="' . $dirPhRow['thumb.mod_w']
                        . '" height="' . $dirPhRow['thumb.mod_h']
                        . '" class="thumb-dir" />
                <span class="preloader" id="thumbDir_' . $dirPhRow['thumb.rowNum'] . '">
                    <script type="text/javascript">
                        thumbDir(\'' . '../' . str_replace('../', '', $imgShaper) . '\','
                        . $dirPhRow['thumb.rowNum'] . ');
                    </script>
                </span>
            </a>
';
                unset($imgShaper);
            }
        }
    } else {
        $imgPreview = E2G_MODULE_URL . 'preview.easy2gallery.php?path='
                . $dirPhRow['thumb.pathRawUrlEncoded']
                . '&amp;mod_w=' . $dirPhRow['thumb.mod_w']
                . '&amp;mod_h=' . $dirPhRow['thumb.mod_h']
                . '&amp;text=' . $lng['new'];
        $dirPhRow['thumb.thumb'] = '
            <a href="' . $dirPhRow['thumb.href'] . '">
                <img src="' . $imgPreview
                . '" alt="' . $dirPhRow['thumb.name']
                . '" title="' . $dirPhRow['thumb.title']
                . '" width="' . $dirPhRow['thumb.mod_w']
                . '" height="' . $dirPhRow['thumb.mod_h']
                . '" />
            </a>
';
        unset($imgPreview);
    }

    echo $e2gMod->filler($e2gMod->getTpl('file_thumb_dir_tpl'), $dirPhRow);

    $rowNum++;
}

#########################     FILES      #########################
$filePhRow = array();

foreach ($fetchFiles as $fetchFile) {
    $filePhRow['thumb.id'] = $fetchFile['id'];
    $filePhRow['thumb.dirId'] = $fetchFile['dir_id'];
    $filePhRow['thumb.name'] = $fetchFile['filename'];
    $filePhRow['thumb.size'] = round($fetchFile['size'] / 1024);
    $filePhRow['thumb.w'] = $fetchFile['width'];
    $filePhRow['thumb.h'] = $fetchFile['height'];
    $filePhRow['thumb.alias'] = $fetchFile['alias'];
    $filePhRow['thumb.tag'] = $fetchFile['tag'];
    $filePhRow['thumb.date_added'] = $fetchFile['date_added'];
    $filePhRow['thumb.last_modified'] = $fetchFile['last_modified'];
    $filePhRow['thumb.status'] = $fetchFile['status'];

    ####################### Template placeholders ######################

    $fileStyledName = $fetchFile['filename']; // will be overridden for styling below
    $fileAlias = '';
    $fileTag = '';
    $fileTagLinks = '';
    $fileAttributes = '';
    $fileAttributeIcons = '';
    $fileIcon = '
            <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture.png" width="16" height="16" border="0" alt="" />
            ';
    if (!empty($fetchFile['redirect_link'])) {
        $fileIcon .= '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/link.png" width="16"
                    height="16" alt="link" title="' . $lng['redirect_link'] . ': ' . $fetchFile['redirect_link'] . '" border="0" />
                        ';
    }
    $fileButtons = '';

    $filePhRow['thumb.rowNum'] = $rowNum;
    $filePhRow['thumb.rowClass'] = $rowClass[$rowNum % 2];
    $filePath = $gdir . $e2gMod->getPath($fetchFile['dir_id']);
    $fileNameUrlDecodeFilename = urldecode($fetchFile['filename']);
    $filePathRawUrlEncoded = str_replace('%2F', '/', rawurlencode($filePath . $fetchFile['filename']));

    if (!file_exists(realpath('../../../../../' . $filePath . $fetchFile['filename']))) {
        $fileIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_delete.png" width="16" height="16" border="0" alt="" />
                ';
        $fileStyledName = '<b style="color:red;"><u>' . $fetchFile['filename'] . '</u></b>';
        $fileAttributes = '<i>(' . $lng['deleted'] . ')</i>';
    } else {
        if ($fetchFile['status'] == '1') {
            $fileButtons = $e2gMod->actionIcon('hide_file', array(
                        'act' => 'hide_file'
                        , 'file_id' => $fetchFile['id']
                        , 'tag' => $tag
                            ), null, $index);
        } else {
            $fileStyledName = '<i>' . $fetchFile['filename'] . '</i>';
            $fileAttributes = '<i>(' . $lng['hidden'] . ')</i>';
            $fileAttributeIcons = $e2gMod->actionIcon('unhide_file', array(
                        'act' => 'unhide_file'
                        , 'file_id' => $fetchFile['id']
                        , 'tag' => $tag
                            ), null, $index);
            $fileButtons = $e2gMod->actionIcon('unhide_file', array(
                        'act' => 'unhide_file'
                        , 'file_id' => $fetchFile['id']
                        , 'tag' => $tag
                            ), null, $index);
        }
    }

    $fileButtons .= $e2gMod->actionIcon('comments', array(
                'page' => 'comments'
                , 'file_id' => $fetchFile['id']
                , 'tag' => $tag
                    ), null, $index);

    $fileButtons .= $e2gMod->actionIcon('edit_file', array(
                'page' => 'edit_file'
                , 'file_id' => $fetchFile['id']
                , 'tag' => $tag
                    ), null, $index);

    $fileButtons .= $e2gMod->actionIcon('delete_file', array(
                'act' => 'delete_file'
                , 'pid' => $fetchFile['dir_id']
                , 'file_path' => $filePathRawUrlEncoded
                , 'file_id' => $fetchFile['id']
                    ), 'onclick="return confirmDelete();"', $index);

    $filePhRow['thumb.checkBox'] = '
                <input name="im[' . $fetchFile['id'] . ']" value="im[' . $fetchFile['id'] . ']" type="checkbox" style="border:0;padding:0" />
                ';
    $filePhRow['thumb.dirId'] = $fetchFile['dir_id'];
    $filePhRow['thumb.fid'] = '[id:' . $fetchFile['id'] . ']';
    $filePhRow['thumb.styledName'] = $fileStyledName;
    $filePhRow['thumb.title'] = ( trim($fetchFile['alias']) != '' ? $fetchFile['alias'] : $fetchFile['filename']);
    $filePhRow['thumb.path'] = $filePath;
    $filePhRow['thumb.pathRawUrlEncoded'] = $filePathRawUrlEncoded;
    $filePhRow['thumb.attributes'] = $fileAttributes;
    $filePhRow['thumb.attributeIcons'] = $fileAttributeIcons;
    $filePhRow['thumb.buttons'] = $fileButtons;
    $filePhRow['thumb.icon'] = $fileIcon;
    $filePhRow['thumb.mod_w'] = $e2g['mod_w'];
    $filePhRow['thumb.mod_h'] = $e2g['mod_h'];
    $filePhRow['thumb.mod_thq'] = $e2g['mod_thq'];

    ############################################################################

    $filePhRow['thumb.link'] = '';
    $filePhRow['thumb.src'] = '';
    $filePhRow['thumb.thumb'] = '';
    if (!empty($filePhRow['thumb.id'])) {
        // path to subdir's thumbnail
        $pathToImg = $e2gMod->getPath($filePhRow['thumb.dirId']);
        $imgShaper = $e2gMod->imgShaper($rootDir
                        , $pathToImg . $filePhRow['thumb.name']
                        , $filePhRow['thumb.mod_w']
                        , $filePhRow['thumb.mod_w']
                        , $filePhRow['thumb.mod_thq']
        );

        // if there is an invalid content
        if ($imgShaper === FALSE) {
            $imgPreview = E2G_MODULE_URL . 'preview.easy2gallery.php?path='
                    . '&amp;mod_w=' . $filePhRow['thumb.mod_w']
                    . '&amp;mod_h=' . $filePhRow['thumb.mod_h']
                    . '&amp;text=' . __LINE__ . '-FALSE'
            ;
            $filePhRow['thumb.thumb'] = '
                <a href="' . $imgPreview
                    . '" class="highslide" onclick="return hs.expand(this)"'
                    . ' title="' . $filePhRow['thumb.name'] . ' ' . $filePhRow['thumb.fid'] . ' ' . $filePhRow['thumb.attributes']
                    . '">
                    <img src="' . $imgPreview
                    . '" alt="' . $filePhRow['thumb.path'] . $filePhRow['thumb.name']
                    . '" title="' . $filePhRow['thumb.title']
                    . '" width="' . $filePhRow['thumb.mod_w']
                    . '" height="' . $filePhRow['thumb.mod_h']
                    . '" />
                </a>
    ';
        } else {
            $filePhRow['thumb.src'] = $imgShaper;
            $filePhRow['thumb.thumb'] = '
            <a href="../' . $filePhRow['thumb.pathRawUrlEncoded']
                    . '" class="highslide" onclick="return hs.expand(this, { objectType: \'ajax\'})" '
                    . 'title="' . $filePhRow['thumb.name'] . ' ' . $filePhRow['thumb.fid'] . ' ' . $filePhRow['thumb.attributes']
                    . '">
                <img src="' . '../' . str_replace('../', '', $imgShaper)
                    . '" alt="' . $filePhRow['thumb.pathRawUrlEncoded'] . $filePhRow['thumb.name']
                    . '" title="' . $filePhRow['thumb.title']
                    . '" width="' . $filePhRow['thumb.mod_w']
                    . '" height="' . $filePhRow['thumb.mod_h']
                    . '" class="thumb-file" />
            </a>
';
        }
        unset($imgShaper);
    } else {
        // new image
        $imgPreview = E2G_MODULE_URL . 'preview.easy2gallery.php?path='
                . $filePhRow['thumb.pathRawUrlEncoded']
                . '&amp;mod_w=' . $filePhRow['thumb.mod_w']
                . '&amp;mod_h=' . $filePhRow['thumb.mod_h']
                . '&amp;text=' . __LINE__ . '-' . $lng['new']
        ;
        $filePhRow['thumb.thumb'] = '
            <a href="' . $filePhRow['thumb.path'] . $filePhRow['thumb.name']
                . '" class="highslide" onclick="return hs.expand(this)"'
                . ' title="' . $filePhRow['thumb.name'] . ' ' . $filePhRow['thumb.fid'] . ' ' . $filePhRow['thumb.attributes']
                . '">
                <img src="' . $imgPreview
                . '" alt="' . $filePhRow['thumb.path'] . $filePhRow['thumb.name']
                . '" title="' . $filePhRow['thumb.title']
                . '" width="' . $filePhRow['thumb.mod_w']
                . '" height="' . $filePhRow['thumb.mod_h']
                . '" />
            </a>
';
        unset($imgPreview);
    }

    echo $e2gMod->filler($e2gMod->getTpl('file_thumb_file_tpl'), $filePhRow);
    $rowNum++;
}

exit ();