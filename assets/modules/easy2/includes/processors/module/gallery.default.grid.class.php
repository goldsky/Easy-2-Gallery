<?php

class ModuleDefaultGridProcessor extends E2GProcessor {

    public function process() {
        $output = '';

        $index = $this->modx->e2gMod->e2gModCfg['index'];
        $index = str_replace('assets/modules/easy2/includes/connector/', '', $index);

        $rootDir = MODX_BASE_PATH . $this->modx->e2gMod->e2g['dir'];
        $rootDir = rtrim($rootDir, '/') . '/'; // just to make sure there is a slash at the end path
        $rootRealPath = realpath($rootDir);
        if (empty($rootRealPath)) {
            $err =  __LINE__ . ' : Root Path is not real : ' . $rootDir . '<br />';
            $this->modx->e2gMod->setError($err);
            return FALSE;
        }
        $pidPath = $this->modx->e2gMod->getPath($this->config['pid']);
        $decodedPath = $this->modx->e2gMod->e2gDecode($this->config['path']);
        $gdir = $this->modx->e2gMod->e2g['dir'] . $this->config['path'];
        $gdirRealPath = realpath($rootDir . $this->modx->e2gMod->e2gDecode($this->config['path']));

        if ($this->config['path'] == $pidPath) {
            ####################################################################
            ####                      MySQL Dir list                        ####
            ####################################################################
            $selectDirs = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_dirs' . ' '
                    . 'WHERE parent_id = ' . $this->config['pid'] . ' '
                    . 'ORDER BY cat_name ASC'
            ;
            $querySelectDirs = mysql_query($selectDirs);
            if (!$querySelectDirs) {
                $err = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectDirs;
                $this->modx->e2gMod->setError($err);
                return FALSE;
            }
            $fetchDirs = array();
            while ($l = mysql_fetch_assoc($querySelectDirs)) {
                $fetchDirs[$l['cat_name']] = $l;
            }
            mysql_free_result($querySelectDirs);
            uksort($fetchDirs, "strnatcmp");

            ####################################################################
            ####                      MySQL File list                       ####
            ####################################################################
            $selectFiles = 'SELECT * FROM ' . $this->modx->db->config['table_prefix'] . 'easy2_files '
                    . 'WHERE dir_id = ' . $this->config['pid'] . ' '
                    . 'ORDER BY filename ASC'
            ;
            $querySelectFiles = mysql_query($selectFiles);
            if (!$querySelectFiles) {
                $err = __LINE__ . ' : #' . mysql_errno() . ' ' . mysql_error() . '<br />' . $selectFiles;
                $this->modx->e2gMod->setError($err);
                return FALSE;
            }
            $fetchFiles = array();
            while ($l = mysql_fetch_assoc($querySelectFiles)) {
                $fetchFiles[$l['filename']] = $l;
            }
            mysql_free_result($querySelectFiles);
            uksort($fetchFiles, "strnatcmp");
        }

        $rowClass = array(' class="gridAltItem"', ' class="gridItem"');
        $rowNum = 0;

        $galPh = array();

        $galPh['th.selectAll'] = '<input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;" />';
        $galPh['th.actions'] = $this->modx->e2gMod->lng['actions'];
        $galPh['th.type'] = $this->modx->e2gMod->lng['type'];
        $galPh['th.name'] = $this->modx->e2gMod->lng['dir'] . ' / ' . $this->modx->e2gMod->lng['filename'];
        $galPh['th.alias'] = $this->modx->e2gMod->lng['alias'] . ' / ' . $this->modx->e2gMod->lng['name'];
        $galPh['th.tag'] = $this->modx->e2gMod->lng['tag'];
        $galPh['th.date'] = $this->modx->e2gMod->lng['date'];
        $galPh['th.size'] = $this->modx->e2gMod->lng['size'] . ' (Kb)';
        $galPh['th.w'] = 'W (px)';
        $galPh['th.h'] = 'H (px)';
        $galPh['td.fileDefaultTableContent'] = '';

        header('Content-Type: text/html; charset=\'' . $this->modx->e2gMod->lng['charset'] . '\'');
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

            $dirPhRow['td.gid'] = '[id: ' . $fetchDir['cat_id'] . ']';
            $dirPhRow['td.path'] = $gdir;
            $dirPhRow['td.pathRawUrlEncoded'] = str_replace('%2F', '/', rawurlencode($gdir . $fetchDir['cat_name']));
            $dirPhRow['td.title'] = ( trim($fetchDir['cat_alias']) != '' ? $fetchDir['cat_alias'] : $fetchDir['cat_name']);
            $dirPhRow['td.tagLinks'] = $this->modx->e2gMod->createTagLinks($fetchDir['cat_tag'], $index);
            $dirPhRow['td.time'] = $this->modx->e2gMod->getTime($fetchDir['date_added'], $fetchDir['last_modified'], $gdirRealPath . DIRECTORY_SEPARATOR . $fetchDir['cat_name']);
            $dirPhRow['td.rowNum'] = $rowNum;
            $dirPhRow['td.rowClass'] = $rowClass[$rowNum % 2];
            $dirPhRow['td.checkBox'] = '
                <input name="dir[' . $fetchDir['cat_id'] . ']" value="' . $dirPhRow['td.pathRawUrlEncoded'] . '" type="checkbox" style="border:0;padding:0" />
                ';
            switch ($this->modx->e2gMod->e2g['mod_foldersize']) {
                case 'auto':
                    $dirPhRow['td.count'] = '( ' . $this->modx->e2gMod->countFiles('../../../../../' . $this->modx->e2gMod->e2gDecode($gdir . $fetchDir['cat_name'])) . ' )';
                    break;
                case 'ajax':
                    $dirPhRow['td.count'] = '( <span id="countfiles_' . $fetchDir['cat_id'] . '"><span id="countfileslink_' . $fetchDir['cat_id'] . '"><a href="javascript:;" onclick="countFiles(\'' . base64_encode('../../../../../' . $this->modx->e2gMod->e2gDecode($gdir . $fetchDir['cat_name'])) . '\', \'' . $fetchDir['cat_id'] . '\')">' . $this->modx->e2gMod->lng['folder_size'] . '</a></span></span> )';
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

            // checks any redirect link
            if (!empty($fetchDir['cat_redirect_link'])) {
                $dirIcon .= '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/link.png" width="16"
                    height="16" alt="link" title="' . $this->modx->e2gMod->lng['redirect_link'] . ': ' . $fetchDir['cat_redirect_link'] . '" border="0" />
                        ';
            }

            // checks the restricted web access
            $webGroupNames = $this->modx->e2gMod->webGroupNames($fetchDir['cat_id'], 'dir');
            if (!empty($webGroupNames)) {
                $webGroupNames = implode(', ', $webGroupNames);
                $dirIcon .= '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/icon_padlock.gif" width="16"
                    height="16" alt="lock" title="' . $this->modx->e2gMod->lng['access'] . ': ' . $webGroupNames . '" border="0" />
                        ';
            }

            $dirButtons = '';

            if (!$this->modx->e2gMod->validFolder($gdirRealPath . DIRECTORY_SEPARATOR . $this->modx->e2gMod->e2gDecode($fetchDir['cat_name']))) {
                $dirIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/folder_delete.png" width="16" height="16" border="0" alt="" />
                ';
                $dirStyledName = '<b style="color:red;"><u>' . $fetchDir['cat_name'] . '</u></b>';
                $dirAttributes = '<i>(' . $this->modx->e2gMod->lng['deleted'] . ')</i>';
                $dirPhRow['td.count'] = ''; // override!
            } else {
                if ($fetchDir['cat_visible'] == '1') {
                    $dirStyledName = '<b>' . $fetchDir['cat_name'] . '</b>';
                    $dirButtons = $this->modx->e2gMod->actionIcon('hide_dir', array(
                        'act' => 'hide_dir'
                        , 'dir_id' => $fetchDir['cat_id']
                        , 'pid' => $this->config['pid']
                            ), null, $index);
                } else {
                    $dirAttributes = '<i>(' . $this->modx->e2gMod->lng['hidden'] . ')</i>';
                    $dirAttributeIcons = '
                    <a href="' . $index . '&amp;act=unhide_dir&amp;dir_id=' . $fetchDir['cat_id'] . '&amp;pid=' . $fetchDir['parent_id'] . '">
                        <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/eye_closed.png" width="16" height="16" alt="' . $this->modx->e2gMod->lng['hidden'] . '" title="' . $this->modx->e2gMod->lng['hidden'] . '" border="0" />
                    </a>
                    ';
                    $dirButtons = $this->modx->e2gMod->actionIcon('unhide_dir', array(
                        'act' => 'unhide_dir'
                        , 'dir_id' => $fetchDir['cat_id']
                        , 'pid' => $fetchDir['parent_id']
                            ), null, $index);
                }
            }

            $dirButtons .= $this->modx->e2gMod->actionIcon('edit_dir', array(
                'page' => 'edit_dir'
                , 'dir_id' => $fetchDir['cat_id']
                , 'pid' => $this->config['pid']
                    ), null, $index);
            $dirButtons .= $this->modx->e2gMod->actionIcon('delete_dir', array(
                'act' => 'delete_dir'
                , 'dir_path' => $gdir . $fetchDir['cat_name']
                , 'dir_id' => $fetchDir['cat_id']
                , 'pid' => $this->config['pid']
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
            $dirPhRow['td.mod_w'] = $this->modx->e2gMod->e2g['mod_w'];
            $dirPhRow['td.mod_h'] = $this->modx->e2gMod->e2g['mod_h'];
            $dirPhRow['td.mod_thq'] = $this->modx->e2gMod->e2g['mod_thq'];

            ########################################################################

            $dirPhRow['td.rowDir'] = '<a href="' . $dirPhRow['td.href'] . '">'
                    . $dirPhRow['td.styledName']
                    . '</a> '
                    . $dirPhRow['td.gid'] . ' ' . $dirPhRow['td.count'] . ' ' . $dirPhRow['td.attributes']
            ;

            $galPh['td.fileDefaultTableContent'] .= $this->modx->e2gMod->filler($this->modx->e2gMod->getTpl('file_default_table_row_dir_tpl'), $dirPhRow);
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

            // checks any redirect link
            if (!empty($fetchFile['redirect_link'])) {
                $fileIcon .= '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/link.png" width="16"
                    height="16" alt="link" title="' . $this->modx->e2gMod->lng['redirect_link'] . ': ' . $fetchFile['redirect_link'] . '" border="0" />
                        ';
            }

            // checks the restricted web access
            $webGroupNames = $this->modx->e2gMod->webGroupNames($fetchFile['id'], 'file');
            if (!empty($webGroupNames)) {
                $webGroupNames = implode(', ', $webGroupNames);
                $fileIcon .= '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/icon_padlock.gif" width="16"
                    height="16" alt="lock" title="' . $this->modx->e2gMod->lng['access'] . ': ' . $webGroupNames . '" border="0" />
                        ';
            }

            $fileButtons = '';

            $filePhRow['td.rowNum'] = $rowNum;
            $filePhRow['td.rowClass'] = $rowClass[$rowNum % 2];
            $filePhRow['td.pathRawUrlEncoded'] = str_replace('%2F', '/', rawurlencode($gdir . $fetchFile['filename']));

            if (!$this->modx->e2gMod->validFile($gdirRealPath . DIRECTORY_SEPARATOR . $this->modx->e2gMod->e2gDecode($fetchFile['filename']))) {
                $fileIcon = '
                <img src="' . E2G_MODULE_URL . 'includes/tpl/icons/picture_delete.png" width="16" height="16" border="0" alt="" />
                ';
                $fileStyledName = '<b style="color:red;"><u>' . $fetchFile['filename'] . '</u></b>';
                $fileAttributes = '<i>(' . $this->modx->e2gMod->lng['deleted'] . ')</i>';
            } else {
                if ($fetchFile['status'] == '1') {
                    $fileButtons = $this->modx->e2gMod->actionIcon('hide_file', array(
                        'act' => 'hide_file'
                        , 'file_id' => $fetchFile['id']
                        , 'pid' => $this->config['pid']
                            ), null, $index);
                } else {
                    $fileStyledName = '<i>' . $fetchFile['filename'] . '</i>';
                    $fileAttributes = '<i>(' . $this->modx->e2gMod->lng['hidden'] . ')</i>';
                    $fileAttributeIcons = $this->modx->e2gMod->actionIcon('unhide_file', array(
                        'act' => 'unhide_file'
                        , 'file_id' => $fetchFile['id']
                        , 'pid' => $this->config['pid']
                            ), null, $index);
                    $fileButtons = $this->modx->e2gMod->actionIcon('unhide_file', array(
                        'act' => 'unhide_file'
                        , 'file_id' => $fetchFile['id']
                        , 'pid' => $this->config['pid']
                            ), null, $index);
                }
            }

            $fileButtons .= $this->modx->e2gMod->actionIcon('comments', array(
                'page' => 'comments'
                , 'file_id' => $fetchFile['id']
                , 'pid' => $this->config['pid']
                    ), null, $index);

            $fileButtons .= $this->modx->e2gMod->actionIcon('edit_file', array(
                'page' => 'edit_file'
                , 'file_id' => $fetchFile['id']
                , 'pid' => $this->config['pid']
                    ), null, $index);

            $fileButtons .= $this->modx->e2gMod->actionIcon('delete_file', array(
                'act' => 'delete_file'
                , 'pid' => $fetchFile['dir_id']
                , 'file_path' => $filePhRow['td.pathRawUrlEncoded']
                , 'file_id' => $fetchFile['id']
                    ), 'onclick="return confirmDelete();"', $index);

            $filePhRow['td.checkBox'] = '
                <input name="im[' . $fetchFile['id'] . ']" value="' . $gdir . $fetchFile['filename'] . '" type="checkbox" style="border:0;padding:0" />
                ';
            $filePhRow['td.dirId'] = $fetchFile['dir_id'];
            $filePhRow['td.fid'] = '[id:' . $fetchFile['id'] . ']';
            $filePhRow['td.styledName'] = $fileStyledName;
            $filePhRow['td.title'] = ( trim($fetchFile['alias']) != '' ? $fetchFile['alias'] : $fetchFile['filename']);
            $filePhRow['td.tagLinks'] = $this->modx->e2gMod->createTagLinks($fetchFile['tag'], $index);
            $filePhRow['td.path'] = $gdir;
            $filePhRow['td.time'] = $this->modx->e2gMod->getTime($fetchFile['date_added'], $fetchFile['last_modified'], '../../../../../' . $gdir . $fetchFile['filename']);
            $filePhRow['td.attributes'] = $fileAttributes;
            $filePhRow['td.attributeIcons'] = $fileAttributeIcons;
            $filePhRow['td.buttons'] = $fileButtons;
            $filePhRow['td.icon'] = $fileIcon;
            $filePhRow['td.mod_w'] = $this->modx->e2gMod->e2g['mod_w'];
            $filePhRow['td.mod_h'] = $this->modx->e2gMod->e2g['mod_h'];
            $filePhRow['td.mod_thq'] = $this->modx->e2gMod->e2g['mod_thq'];

            ########################################################################

            if ($filePhRow['td.attributes'] == '<i>(' . $this->modx->e2gMod->lng['deleted'] . ')</i>') {
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

            $galPh['td.fileDefaultTableContent'] .= $this->modx->e2gMod->filler($this->modx->e2gMod->getTpl('file_default_table_row_file_tpl'), $filePhRow);
            $rowNum++;
        }

        $output = $this->modx->e2gMod->filler($this->modx->e2gMod->getTpl('file_default_table_tpl'), $galPh);

        return $output;
    }

}

return 'ModuleDefaultGridProcessor';
