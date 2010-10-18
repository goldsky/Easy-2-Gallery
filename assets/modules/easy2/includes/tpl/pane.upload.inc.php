<?php
if (IN_MANAGER_MODE != 'true')
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="pane">
    <form name="images" action="<?php echo $index . '&amp;act=upload_all'; ?>"
          method="post" enctype="multipart/form-data">
        <p><b><?php echo $lng['upload_dir']; ?> :</b>
            <select name="newparent">
                <option value="">&nbsp;</option>
                <?php echo $this->_folderOptions(); ?>
            </select>
            <span><?php echo $lng['and']; ?>: </span>
            <input type="radio" name="gotofolder" value="gothere" checked="checked"><?php echo $lng['go_there']; ?>
            <input type="radio" name="gotofolder" value="stayhere"><?php echo $lng['stay_here']; ?>
        </p>
        <p><b><?php echo $lng['extension_valid']; ?> :</b> .jpeg, .jpg, .gif, .png</p>
        <p><b><?php echo $lng['upload_limit']; ?> :</b> <?php echo ini_get('upload_max_filesize'); ?></p>
        <div class="tab-pane" id="tabFileUploadPane">
            <script type="text/javascript">
                tpFileUpload = new WebFXTabPane(document.getElementById('tabFileUploadPane'), false);
            </script>
            <?php echo $this->_plugin('OnE2GFileUploadFormPrerender', array('gdir' => $gdir)); ?>
                <div class="tab-page" id="tabFile">
                    <h2 class="tab"><?php echo $lng['file']; ?></h2>
                    <script type="text/javascript">
                        tpFileUpload.addTabPage( document.getElementById( 'tabFile') );
                    </script>
                    <div id="imFields">
                        <div id="firstElt">
                            <table cellspacing="0" cellpadding="2" class="aForm">
                                <tr>
                                    <td><b><?php echo $lng['file']; ?>:</b></td>
                                    <td><input name="img[]" type="file" size="95" /></td>
                                </tr>
                                <tr>
                                    <td><b><?php echo $lng['name']; ?> :</b></td>
                                    <td><input name="name[]" type="text" size="95" /></td>
                                </tr>
                                <tr>
                                    <td><b><?php echo $lng['summary']; ?> :</b></td>
                                    <td><input name="summary[]" type="text" size="95" /></td>
                                </tr>
                                <tr>
                                    <td><b><?php echo $lng['tag']; ?> :</b></td>
                                    <td><input name="tag[]" type="text" size="95" /></td>
                                </tr>
                                <tr>
                                    <td valign="top"><b><?php echo $lng['description']; ?> :</b></td>
                                    <td>
                                        <textarea name="description[]" style="width:500px" cols="" rows="3"></textarea>
                                    </td>
                                </tr>
                            </table>
                            <div style="margin-left: 80px;">
                                <input type="submit" value="<?php echo $lng['upload']; ?>" />
                                <input type="button" value="<?php echo $lng['btn_field_add']; ?>" onclick="addField();" />
                            </div>
                        </div>
                        <div class="clear">&nbsp;</div>
                    </div>
                </div>
            </div>
        <?php
                // This is only the base dir. Need to get the target folder!
                // @todo adding AJAX processor from the _folderOption() above.
                echo $this->_plugin('OnE2GFileUploadFormRender', array(
                    'gdir' => $gdir
                    ));
                if (class_exists(ZipArchive)) {
        ?>
                    <div class="tab-page" id="tabZip">
                        <h2 class="tab"><?php echo $lng['zip']; ?></h2>
                        <script type="text/javascript">
                            tpFileUpload.addTabPage( document.getElementById( 'tabZip') );
                        </script>
                        <ul>
                            <li><?php echo htmlspecialchars_decode($lng['char_limitation']); ?></li>
                        </ul>
                        <br />
            <?php echo $this->_plugin('OnE2GZipUploadFormPrerender', array('gdir' => $gdir)); ?>
                    <table cellspacing="0" cellpadding="2">
                        <tr>
                            <td><b><?php echo $lng['archive']; ?>:</b></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;<input name="zip" type="file" size="77" /></td>
                            <td><input type="submit" value="<?php echo $lng['upload']; ?>" /></td>
                        </tr>
                    </table>
            <?php echo $this->_plugin('OnE2GZipUploadFormRender', array('gdir' => $gdir)); ?>
                </div>
        <?php } ?>
    </form>
</div>