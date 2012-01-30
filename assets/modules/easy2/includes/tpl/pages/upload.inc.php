<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<form name="images" action="<?php echo $this->e2gModCfg['index'] . '&amp;act=upload_all'; ?>" method="post" enctype="multipart/form-data">
    <?php if (!isset($this->sanitizedGets['pid'])) {
    ?>
        <p><b><?php echo $this->lng['upload_dir']; ?> :</b>
            <select name="newparent">
                <option value="">&nbsp;</option>
            <?php echo $this->_getDirDropDownOptions(); ?>
        </select>
        <span><?php echo $this->lng['and']; ?>: </span>
        <input type="radio" name="gotofolder" value="gothere" checked="checked"><?php echo $this->lng['go_there']; ?>
        <input type="radio" name="gotofolder" value="stayhere"><?php echo $this->lng['stay_here']; ?>
    </p><?php } else { ?>
    <ul class="actionButtons">
        <li>
            <a href="<?php echo $this->e2gModCfg['blank_index']; ?>&amp;e2gpg=<?php echo $this->e2gModCfg['e2gPages']['files']['e2gpg']; ?>&amp;pid=<?php echo $this->sanitizedGets['pid']; ?>">
                <img src="<?php echo E2G_MODULE_URL; ?>includes/tpl/icons/arrow_left.png" alt="" /> <?php echo $this->lng['back']; ?>
            </a>
        </li>
    </ul>
    <p><?php echo $this->lng['upload_dir'] . ': <b>' . $this->e2gModCfg['gdir'] . '</b>'; ?></p>
    <input type="hidden" name="newparent" value="<?php echo $this->sanitizedGets['pid']; ?>" />
    <input type="hidden" name="gotofolder" value="gothere" />
    <?php } ?>
        <p><b><?php echo $this->lng['extension_valid']; ?> :</b> .jpeg, .jpg, .gif, .png</p>
        <p><b><?php echo $this->lng['upload_limit']; ?> :</b> <?php echo ini_get('upload_max_filesize'); ?></p>
        <div class="tab-pane" id="tabFileUploadPane">
            <script type="text/javascript">
                tpFileUpload = new WebFXTabPane(document.getElementById('tabFileUploadPane'), false);
            </script>
        <?php echo $this->plugin('OnE2GFileUploadFormPrerender', array('gdir' => $this->e2gModCfg['gdir'])); ?>
        <div class="tab-page" id="tabFile">
            <h2 class="tab"><?php echo $this->lng['file']; ?></h2>
            <script type="text/javascript">
                tpFileUpload.addTabPage( document.getElementById( 'tabFile') );
            </script>
            <div id="imFields">
                <div id="firstElt">
                    <table cellspacing="0" cellpadding="2" class="aForm">
                        <tr>
                            <td><b><?php echo $this->lng['file']; ?>:</b></td>
                            <td><input name="img[]" type="file" size="95" /></td>
                        </tr>
                        <tr>
                            <td><b><?php echo $this->lng['alias']; ?> :</b></td>
                            <td><input name="alias[]" type="text" size="95" /></td>
                        </tr>
                        <tr>
                            <td><b><?php echo $this->lng['summary']; ?> :</b></td>
                            <td><input name="summary[]" type="text" size="95" /></td>
                        </tr>
                        <tr>
                            <td><b><?php echo $this->lng['tag']; ?> :</b></td>
                            <td><input name="tag[]" type="text" size="95" /></td>
                        </tr>
                        <tr>
                            <td valign="top"><b><?php echo $this->lng['description']; ?> :</b></td>
                            <td>
                                <textarea name="description[]" style="width:500px" cols="" rows="3"></textarea>
                            </td>
                        </tr>
                    </table>
                    <div style="margin-left: 80px;">
                        <input type="submit" value="<?php echo $this->lng['upload']; ?>" />
                        <input type="button" value="<?php echo $this->lng['btn_field_add']; ?>" onclick="addField();" />
                    </div>
                </div>
                <div class="clear">&nbsp;</div>
            </div>
        </div>
    </div>
    <?php
        // This is only the base dir. Need to get the target folder!
        // @todo adding AJAX processor from the _folderOption() above.
        echo $this->plugin('OnE2GFileUploadFormRender', array(
            'gdir' => $this->e2gModCfg['gdir']
        ));
        if (class_exists('ZipArchive')) {
    ?>
            <div class="tab-page" id="tabZip">
                <h2 class="tab"><?php echo $this->lng['zip']; ?></h2>
                <script type="text/javascript">
                    tpFileUpload.addTabPage( document.getElementById( 'tabZip') );
                </script>
                <ul>
                    <li><?php echo htmlspecialchars_decode($this->lng['char_limitation']); ?></li>
                </ul>
                <br />
        <?php echo $this->plugin('OnE2GZipUploadFormPrerender', array('gdir' => $this->e2gModCfg['gdir'])); ?>
            <table cellspacing="0" cellpadding="2">
                <tr>
                    <td><b><?php echo $this->lng['archive']; ?>:</b></td>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;<input name="zip" type="file" size="77" /></td>
                    <td><input type="submit" value="<?php echo $this->lng['upload']; ?>" /></td>
                </tr>
            </table>
        <?php echo $this->plugin('OnE2GZipUploadFormRender', array('gdir' => $this->e2gModCfg['gdir'])); ?>
        </div>
    <?php } ?>
</form>