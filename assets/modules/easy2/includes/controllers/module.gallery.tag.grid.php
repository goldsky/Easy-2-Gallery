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
    while ($row = mysql_fetch_array($q)) {
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

require_once E2G_MODULE_PATH . 'includes/utf8/utf8.php';

// initiate e2g's public module
include ('../configs/params.module.easy2gallery.php');
include ('../models/e2g.public.class.php'); //extending
include ('../models/e2g.module.class.php');

// LANGUAGE
if (file_exists(realpath('../langs/' . $modx->config['manager_language'] . '.inc.php'))) {
    include '../langs/' . $modx->config['manager_language'] . '.inc.php';

    // if there is a blank language parameter, english will fill it as the default.
    foreach ($e2g_lang[$modx->config['manager_language']] as $olk => $olv) {
        $oldLangKey[$olk] = $olk; // other languages
        $oldLangVal[$olk] = $olv;
    }

    include '../langs/english.inc.php';
    foreach ($e2g_lang['english'] as $enk => $env) {
        if (!isset($oldLangKey[$enk])) {
            $e2g_lang[$modx->config['manager_language']][$enk] = $env;
        }
    }

    $lng = $e2g_lang[$modx->config['manager_language']];
} else {
    include '../langs/english.inc.php';
    $lng = $e2g_lang['english'];
}

$e2gMod = new E2gMod($modx, $e2gModCfg, $e2g, $lng);

$getRequests = $e2gMod->sanitizedGets($_GET);
if (empty($getRequests)) {
    die('Request is empty');
}

$index = $e2gModCfg['index'];
$index = str_replace('assets/modules/easy2/includes/controllers/', '', $index);

$rootDir = '../../../../../' . $e2g['dir'];
$tag = $getRequests['tag'];
$gdir = $e2g['dir'] . $getRequests['path'];

$rowClass = array(' class="gridAltItem"', ' class="gridItem"');
$rowNum = 0;

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
?>
<table width="100%" cellpadding="2" cellspacing="0" class="grid" style="margin-bottom:10px;">
    <tr>
        <th width="25"><?php
if (!isset($getRequests['getpath'])):
?>
            <input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;" />
            <?php
            endif;
            ?>
        </th>
        <th width="60"><?php echo $lng['actions']; ?></th>
        <th><?php echo $lng['path']; ?></th>
        <th width="20"><?php echo $lng['type']; ?></th>
        <th><?php echo $lng['dir'] . ' / ' . $lng['filename']; ?></th>
        <th><?php echo $lng['alias'] . ' / ' . $lng['name']; ?></th>
        <th><?php echo $lng['tag']; ?></th>
        <th width="100"><?php echo $lng['date']; ?></th>
        <th width="50"><?php echo $lng['size']; ?> (Kb)</th>
        <th width="40">W (px)</th>
        <th width="40">H (px)</th>
    </tr>
    <?php
            #########################     DIRECTORIES      #########################
            $dirPhRow = array();
            while ($fetchDir = mysql_fetch_array($querySelectDirs, MYSQL_ASSOC)) {
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
                $dirPhRow['td.path'] = $dirPath;
                $dirPhRow['td.pathRawUrlEncoded'] = str_replace('%2F', '/', rawurlencode($dirPath . $fetchDir['cat_name']));
                $dirPhRow['td.title'] = ( trim($fetchDir['cat_alias']) != '' ? $fetchDir['cat_alias'] : $fetchDir['cat_name']);
                $dirPhRow['td.tagLinks'] = $e2gMod->createTagLinks($fetchDir['cat_tag']);
                $dirPhRow['td.time'] = $e2gMod->getTime($fetchDir['date_added'], $fetchDir['last_modified'], '../../../../../' . $dirPath . $fetchDir['cat_name']);
                $dirPhRow['td.count'] = $e2gMod->countFiles('../../../../../' . $dirPath . $fetchDir['cat_name']);

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
                <a href="' . $index . '&amp;act=show_dir&amp;dir_id=' . $fetchDir['cat_id'] . '&amp;pid=' . $fetchDir['parent_id'] . '">
                    <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16" height="16" alt="' . $lng['hidden'] . '" title="' . $lng['hidden'] . '" border="0" />
                </a>
                ';
                    $dirButtons = $e2gMod->actionIcon('show_dir', array(
                                'act' => 'show_dir'
                                , 'dir_id' => $fetchDir['cat_id']
                                , 'pid' => $fetchDir['parent_id']
                                    ), null, $index);
                }

                $dirButtons .= $e2gMod->actionIcon('edit_dir', array(
                            'page' => 'edit_dir'
                            , 'dir_id' => $fetchDir['cat_id']
                            , 'tag' => $tag
                        ));
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
                $dirPhRow['td.mod_w'] = $modThumbW;
                $dirPhRow['td.mod_h'] = $modThumbH;
                $dirPhRow['td.mod_thq'] = $modThumbThq;

                ########################################################################

                $dirPhRow['td.rowDir'] = '<a href="' . $dirPhRow['td.href'] . '">'
                        . $dirPhRow['td.styledName']
                        . '</a> '
                        . $dirPhRow['td.gid'] . ' (' . $dirPhRow['td.count'] . ') ' . $dirPhRow['td.attributes']
                ;

                echo $e2gMod->filler($e2gMod->getTpl('file_tag_table_row_dir_tpl'), $dirPhRow);
                $rowNum++;
            }

            mysql_free_result($querySelectDirs);

            #########################        FILES         #########################
            $filePhRow = array();
            while ($fetchFile = mysql_fetch_array($querySelectFiles, MYSQL_ASSOC)) {
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

                if (!file_exists('../../../../../' . $filePath . $fetchFile['filename'])) {
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
                        $fileAttributeIcons = $e2gMod->actionIcon('show_file', array(
                                    'act' => 'show_file'
                                    , 'file_id' => $fetchFile['id']
                                    , 'tag' => $tag
                                        ), null, $index);
                        $fileButtons = $e2gMod->actionIcon('show_file', array(
                                    'act' => 'show_file'
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
                <input name="im[' . $fetchFile['id'] . ']" value="im[' . $fetchFile['id'] . ']" type="checkbox" style="border:0;padding:0" />
                ';
                $filePhRow['td.dirId'] = $fetchFile['dir_id'];
                $filePhRow['td.fid'] = '[id:' . $fetchFile['id'] . ']';
                $filePhRow['td.styledName'] = $fileStyledName;
                $filePhRow['td.title'] = ( trim($fetchFile['alias']) != '' ? $fetchFile['alias'] : $fetchFile['filename']);
                $filePhRow['td.tagLinks'] = $e2gMod->createTagLinks($fetchFile['tag']);
                $filePhRow['td.path'] = $filePath;
                $filePhRow['td.pathRawUrlEncoded'] = $filePathRawUrlEncoded;
                $filePhRow['td.time'] = $e2gMod->getTime($fetchFile['date_added'], $fetchFile['last_modified'], '../../../../../' . $filePath . $fetchFile['filename']);
                $filePhRow['td.attributes'] = $fileAttributes;
                $filePhRow['td.attributeIcons'] = $fileAttributeIcons;
                $filePhRow['td.buttons'] = $fileButtons;
                $filePhRow['td.icon'] = $fileIcon;
                $filePhRow['td.mod_w'] = $modThumbW;
                $filePhRow['td.mod_h'] = $modThumbH;
                $filePhRow['td.mod_thq'] = $modThumbThq;

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

                echo $e2gMod->filler($e2gMod->getTpl('file_tag_table_row_file_tpl'), $filePhRow);
                $rowNum++;
            }
            mysql_free_result($querySelectFiles);
    ?>
        </table>
<?php
            exit();