<?php

if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

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
$galPh['td.mod_row_content'] = '';

$read = $this->_readDir($e2g['dir'], $path, $parentId);

#########################     DIRECTORIES      #########################
$dirPhRow = array();
// count the name, because new dir (without ID) is also being read.
$countRowDirName = count($read['dir']['name']);
for ($b = 0; $b < $countRowDirName; $b++) {
    foreach ($read['dir'] as $k => $v) {
        $dirPhRow['td.' . $k] = $v[$b];
    }
    $galPh['td.mod_row_content'] .= $this->_filler($this->_getTpl('mod_tpl_table_row_dir'), $dirPhRow);
}

$dirPhRow = array();
$countDeletedDirs = count($read['deletedDir']['id']);
if ($countDeletedDirs > 0) {
    for ($b = 0; $b < $countDeletedDirs; $b++) {
        foreach ($read['deletedDir'] as $k => $v) {
            $dirPhRow['td.' . $k] = $v[$b];
        }
        $galPh['td.mod_row_content'] .= $this->_filler($this->_getTpl('mod_tpl_table_row_dir'), $dirPhRow);
    }
}
#########################        FILES         #########################
$filePhRow = array();
// count the name, because new file (without ID) is also being read.
$countRowFileName = count($read['file']['name']);
for ($b = 0; $b < $countRowFileName; $b++) {
    foreach ($read['file'] as $k => $v) {
        $filePhRow['td.' . $k] = $v[$b];
    }

    $filePhRow['td.rowFile'] = '
                <a href="javascript:void(0)"'
            . ' onclick="imPreview(\''
            . $filePhRow['td.pathRawUrlEncoded'] . '\', '
            . $filePhRow['td.rowNum'] . ');">'
            . $filePhRow['td.styledName']
            . '</a> ' . $filePhRow['td.fid'] . ' ' . $filePhRow['td.attributes'] . '
                <div class="imPreview" id="rowPreview_' . $filePhRow['td.rowNum'] . '" style="display:none;"></div>
';

    $galPh['td.mod_row_content'] .= $this->_filler($this->_getTpl('mod_tpl_table_row_file'), $filePhRow);
}

// deleted file
$filePhRow = array();
$countDeletedFiles = count($read['deletedFile']['id']);
if ($countDeletedFiles > 0) {
    for ($b = 0; $b < $countDeletedFiles; $b++) {
        foreach ($read['deletedFile'] as $k => $v) {
            $filePhRow['td.' . $k] = $v[$b];
        }
        $filePhRow['td.rowFile'] = $filePhRow['td.styledName'] . ' ' . $filePhRow['td.fid'] . ' ' . $filePhRow['td.attributes'];
        $galPh['td.mod_row_content'] .= $this->_filler($this->_getTpl('mod_tpl_table_row_file'), $filePhRow);
    }
}

ob_start();
include_once E2G_MODULE_PATH . 'includes/tpl/pages/menu.bottom.inc.php';
$modBottomMenu = ob_get_contents();
ob_end_clean();
$galPh['table.mod_bottom_menu'] = $modBottomMenu;

echo $this->_filler($this->_getTpl('mod_tpl_table'), $galPh);