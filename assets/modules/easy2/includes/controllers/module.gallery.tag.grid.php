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
        . 'ORDER BY filename ASC';
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

$galPh = array();

$galPh['th.selectAll'] = '<input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;" />';
$galPh['th.actions'] = $lng['actions'];
$galPh['th.path'] = $lng['path'];
$galPh['th.type'] = $lng['type'];
$galPh['th.name'] = $lng['dir'] . ' / ' . $lng['filename'];
$galPh['th.alias'] = $lng['alias'] . ' / ' . $lng['name'];
$galPh['th.tag'] = $lng['tag'];
$galPh['th.date'] = $lng['date'];
$galPh['th.size'] = $lng['size'] . ' (Kb)';
$galPh['th.w'] = 'W (px)';
$galPh['th.h'] = 'H (px)';
$galPh['td.fileTagTableContent'] = '';

header('Content-Type: text/html; charset=\'' . $lng['charset'] . '\'');
#########################     DIRECTORIES      #########################
$dirPhRow = array();
foreach ($fetchDirs as $fetchDir) {
    // goldsky -- store the array to be connected between db <--> fs
    $dirPhRow['td.parent_id'] = $fetchDir['parent_id'];
    $dirPhRow['td.id'] = $fetchDir['cat_id'];
    $dirPhRow['td.name'] = $fetchDir['cat_name'];
    $dirPhRow['td.alias'] = $fetchDir['cat_alias'];
    $dirPhRow['td.tag'] = $fetchDir['cat_tag'];
    $dirPhRow['td.cat_visible'] = $fetchDir['cat_visible'];
    $dirPhRow['td.date_added'] = $fetchDir['date_added'];
    $dirPhRow['td.last_modified'] = $fetchDir['last_modified'];

    ####################### Template placeholders ######################

    $dirPhRow['td.rowNum'] = $rowNum;
    $dirPhRow['td.rowClass'] = $rowClass[$rowNum % 2];
    $dirPath = $gdir . $e2gMod->getPath($fetchDir['parent_id']);
    $dirPhRow['td.checkBox'] = '
                <input name="dir[' . $fetchDir['cat_id'] . ']" value="' . rawurldecode($dirPath . $fetchDir['cat_name']) . '" type="checkbox" style="border:0;padding:0" />
                ';
    $dirPhRow['td.gid'] = '[id: ' . $fetchDir['cat_id'] . ']';
    $dirPhRow['td.path'] = '<a href="' . $index . '&amp;pid=' . $fetchDir['parent_id'] . '">' . $dirPath . '</a>';
    $dirPhRow['td.pathRawUrlEncoded'] = str_replace('%2F', '/', rawurlencode($dirPath . $fetchDir['cat_name']));
    $dirPhRow['td.title'] = ( trim($fetchDir['cat_alias']) != '' ? $fetchDir['cat_alias'] : $fetchDir['cat_name']);
    $dirPhRow['td.tagLinks'] = $e2gMod->createTagLinks($fetchDir['cat_tag'], $index);
    $dirPhRow['td.time'] = $e2gMod->getTime($fetchDir['date_added'], $fetchDir['last_modified'], '../../../../../' . $dirPath . $fetchDir['cat_name']);
    switch ($e2g['mod_foldersize']) {
        case 'auto':
            $dirPhRow['td.count'] = '( ' . $e2gMod->countFiles('../../../../../' . $dirPath . $fetchDir['cat_name']) . ' )';
            break;
        case 'ajax':
            $dirPhRow['td.count'] = '( <span id="countfiles_' . $fetchDir['cat_id'] . '"><span id="countfileslink_' . $fetchDir['cat_id'] . '"><a href="javascript:;" onclick="countFiles(\'' . base64_encode('../../../../../' . $e2gMod->e2gDecode($dirPath . $fetchDir['cat_name'])) . '\', \'' . $fetchDir['cat_id'] . '\')">' . $lng['folder_size'] . '</a></span></span> )';
            break;
        default:
            $dirPhRow['td.count'] = '';
            break;
    }

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
                    , 'tag' => $tag
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

    $dirPhRow['td.styledName'] = $dirStyledName;
    $dirPhRow['td.attributes'] = $dirAttributes;
    $dirPhRow['td.attributeIcons'] = $dirAttributeIcons;
    $dirPhRow['td.href'] = $index . '&amp;pid=' . $fetchDir['cat_id'];
    $dirPhRow['td.buttons'] = $dirButtons;
    $dirPhRow['td.icon'] = $dirIcon;
    $dirPhRow['td.size'] = '---';
    $dirPhRow['td.w'] = '---';
    $dirPhRow['td.h'] = '---';
    $dirPhRow['td.mod_w'] = $e2g['mod_w'];
    $dirPhRow['td.mod_h'] = $e2g['mod_h'];
    $dirPhRow['td.mod_thq'] = $e2g['mod_thq'];

    ########################################################################

    $dirPhRow['td.rowDir'] = '<a href="' . $dirPhRow['td.href'] . '">'
            . $dirPhRow['td.styledName']
            . '</a> '
            . $dirPhRow['td.gid'] . ' ' . $dirPhRow['td.count'] . ' ' . $dirPhRow['td.attributes']
    ;

    $galPh['td.fileTagTableContent'] .= $e2gMod->filler($e2gMod->getTpl('file_tag_table_row_dir_tpl'), $dirPhRow);
    $rowNum++;
}

#########################        FILES         #########################
$filePhRow = array();
foreach ($fetchFiles as $fetchFile) {
    $filePhRow['td.id'] = $fetchFile['id'];
    $filePhRow['td.dirId'] = $fetchFile['dir_id'];
    $filePhRow['td.name'] = $fetchFile['filename'];
    $filePhRow['td.size'] = round($fetchFile['size'] / 1024);
    $filePhRow['td.w'] = $fetchFile['width'];
    $filePhRow['td.h'] = $fetchFile['height'];
    $filePhRow['td.alias'] = $fetchFile['alias'];
    $filePhRow['td.tag'] = $fetchFile['tag'];
    $filePhRow['td.date_added'] = $fetchFile['date_added'];
    $filePhRow['td.last_modified'] = $fetchFile['last_modified'];
    $filePhRow['td.status'] = $fetchFile['status'];

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

    $filePhRow['td.rowNum'] = $rowNum;
    $filePhRow['td.rowClass'] = $rowClass[$rowNum % 2];
    $filePath = $gdir . $e2gMod->getPath($fetchFile['dir_id']);
    $fileNameUrlDecodeFilename = urldecode($fetchFile['filename']);
    $filePathRawUrlEncoded = str_replace('%2F', '/', rawurlencode($filePath . $fetchFile['filename']));

    if (!$e2gMod->validFile('../../../../../' . $e2gMod->e2gDecode($filePath . $fetchFile['filename']))) {
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

    $filePhRow['td.checkBox'] = '
                <input name="im[' . $fetchFile['id'] . ']" value="' . $filePathRawUrlEncoded . '" type="checkbox" style="border:0;padding:0" />
                ';
    $filePhRow['td.dirId'] = $fetchFile['dir_id'];
    $filePhRow['td.fid'] = '[id:' . $fetchFile['id'] . ']';
    $filePhRow['td.styledName'] = $fileStyledName;
    $filePhRow['td.title'] = ( trim($fetchFile['alias']) != '' ? $fetchFile['alias'] : $fetchFile['filename']);
    $filePhRow['td.tagLinks'] = $e2gMod->createTagLinks($fetchFile['tag'], $index);
    $filePhRow['td.path'] = '<a href="' . $index . '&amp;pid=' . $fetchFile['dir_id'] . '">' . $filePath . '</a>';
    $filePhRow['td.pathRawUrlEncoded'] = $filePathRawUrlEncoded;
    $filePhRow['td.time'] = $e2gMod->getTime($fetchFile['date_added'], $fetchFile['last_modified'], '../../../../../' . $filePath . $fetchFile['filename']);
    $filePhRow['td.attributes'] = $fileAttributes;
    $filePhRow['td.attributeIcons'] = $fileAttributeIcons;
    $filePhRow['td.buttons'] = $fileButtons;
    $filePhRow['td.icon'] = $fileIcon;
    $filePhRow['td.mod_w'] = $e2g['mod_w'];
    $filePhRow['td.mod_h'] = $e2g['mod_h'];
    $filePhRow['td.mod_thq'] = $e2g['mod_thq'];

    ########################################################################

    if ($filePhRow['td.attributes'] == '<i>(' . $lng['deleted'] . ')</i>') {
        $filePhRow['td.rowFile'] = $filePhRow['td.styledName'] . ' ' . $filePhRow['td.fid'] . ' ' . $filePhRow['td.attributes'];
    } else {

        $filePhRow['td.rowFile'] = '
                <a href="javascript:void(0)"'
                . ' onclick="imPreview(\''
                . $filePhRow['td.pathRawUrlEncoded'] . '\', '
                . $filePhRow['td.rowNum'] . ');">'
                . $filePhRow['td.styledName']
                . '</a> ' . $filePhRow['td.fid'] . ' ' . $filePhRow['td.attributes'] . '
                <div class="imPreview" id="rowPreview_' . $filePhRow['td.rowNum'] . '" style="display:none;"></div>
';
    }

    $galPh['td.fileTagTableContent'] .= $e2gMod->filler($e2gMod->getTpl('file_tag_table_row_file_tpl'), $filePhRow);
    $rowNum++;
}

echo $e2gMod->filler($e2gMod->getTpl('file_tag_table_tpl'), $galPh);

exit();