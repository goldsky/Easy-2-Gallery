<?php

if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$galPh = array();
$galPh['gal.selectAll'] = '<input type="checkbox" onclick="selectAll(this.checked); void(0);" style="border:0;" /> ' . $lng['select_all'];
$galPh['gal.fileThumbGalContent'] = '';
$readTag = $this->_readTag($tag);

#########################     DIRECTORIES      #########################
$dirPhRow = array();
// count the name, because new dir (without ID) is also being read.
$countRowDirName = count($readTag['dir']['name']);
for ($b = 0; $b < $countRowDirName; $b++) {
    foreach ($readTag['dir'] as $k => $v) {
        $dirPhRow['thumb.' . $k] = $v[$b];
    }

    $dirPhRow['thumb.src'] = '';
    $dirPhRow['thumb.thumb'] = '';
    if (!empty($dirPhRow['thumb.id'])) {
        // search image for subdir
        $folderImgInfos = $this->_folderImg($dirPhRow['thumb.id'], '../' . $e2g['dir']);

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
            $pathToImg = $this->_getPath($folderImgInfos['dir_id']);
            $imgShaper = $this->_imgShaper('../' . $gdir
                            , $pathToImg . $folderImgInfos['filename']
                            , $dirPhRow['thumb.mod_w']
                            , $dirPhRow['thumb.mod_w']
                            , $thq);
            if ($imgShaper !== FALSE) {
                $dirPhRow['thumb.src'] = $imgShaper;
                $dirPhRow['thumb.thumb'] = '
            <a href="' . $dirPhRow['thumb.href'] . '">
                <img src="' . $imgShaper
                        . '" alt="' . $dirPhRow['thumb.name']
                        . '" title="' . $dirPhRow['thumb.title']
                        . '" width="' . $dirPhRow['thumb.mod_w']
                        . '" height="' . $dirPhRow['thumb.mod_h']
                        . '" />
            </a>
';
                unset($imgShaper);
            } else {
                // folder has been deleted
                $imgPreview = E2G_MODULE_URL . 'preview.easy2gallery.php?path='
                        . $dirPhRow['thumb.pathRawUrlEncoded']
                        . '&amp;mod_w=' . $dirPhRow['thumb.mod_w']
                        . '&amp;mod_h=' . $dirPhRow['thumb.mod_h']
                        . '&amp;text=' . __LINE__ . '-'
                ;
                $dirPhRow['thumb.thumb'] = '
            <a href="' . $dirPhRow['thumb.path'] . $dirPhRow['thumb.name']
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
            }
        }
    } else {
        // new folder
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

    $galPh['gal.fileThumbGalContent'] .= $this->_filler($this->_getTpl('file_thumb_dir_tpl'), $dirPhRow);
}

#########################     FILES      #########################
$filePhRow = array();
// count the name, because new file (without ID) is also being read.
$countRowFileName = count($readTag['file']['name']);
for ($b = 0; $b < $countRowFileName; $b++) {
    foreach ($readTag['file'] as $k => $v) {
        $filePhRow['thumb.' . $k] = $v[$b];
    }

    $filePhRow['thumb.link'] = '';

    $filePhRow['thumb.src'] = '';
    $filePhRow['thumb.thumb'] = '';
    if (!empty($filePhRow['thumb.id'])) {
        // path to subdir's thumbnail
        $pathToImg = $this->_getPath($filePhRow['thumb.dirId']);
        $imgShaper = $this->_imgShaper('../' . $gdir
                        , $pathToImg . $filePhRow['thumb.name']
                        , $filePhRow['thumb.mod_w']
                        , $filePhRow['thumb.mod_w']
                        , $thq);
        if ($imgShaper !== FALSE) {
            $filePhRow['thumb.src'] = $imgShaper;
            $filePhRow['thumb.thumb'] = '
            <a href="' . '../' . $filePhRow['thumb.pathRawUrlEncoded']
                    . '" class="highslide" onclick="return hs.expand(this)" '
                    . 'title="' . $filePhRow['thumb.name'] . ' ' . $filePhRow['thumb.fid'] . ' ' . $filePhRow['thumb.attributes']
                    . '">
                <img src="' . $imgShaper
                    . '" alt="' . $filePhRow['thumb.path'] . $filePhRow['thumb.name']
                    . '" title="' . $filePhRow['thumb.title']
                    . '" width="' . $filePhRow['thumb.mod_w']
                    . '" height="' . $filePhRow['thumb.mod_h']
                    . '" />
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
                . '&amp;text=' . __LINE__ . '-'
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

    $galPh['gal.fileThumbGalContent'] .= $this->_filler($this->_getTpl('file_thumb_file_tpl'), $filePhRow);
}

ob_start();
include_once E2G_MODULE_PATH . 'includes/tpl/pages/file.menu.bottom.inc.php';
$modBottomMenu = ob_get_contents();
ob_end_clean();
$galPh['bottomMenu'] = $modBottomMenu;

echo $this->_filler($this->_getTpl('file_thumb_gal_tpl'), $galPh);