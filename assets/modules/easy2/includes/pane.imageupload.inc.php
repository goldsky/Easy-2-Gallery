<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="tab-page" id="addForm">
    <h2 class="tab"><?php echo $lng['upload']; ?></h2>
    <script type="text/javascript">
        tpResources.addTabPage(document.getElementById("addForm"));
    </script>
    <p><?php echo $lng['upload_dir'].': <b>'.utf8_encode($gdir).'</b>'; ?></p>
    <p><?php echo $lng['valid_extensions']; ?> .jpeg, .jpg, .gif, .png</p>
    <div class="tab-pane" id="tabImageUploadPane">
        <script type="text/javascript">
            tpResources2 = new WebFXTabPane(document.getElementById('tabImageUploadPane'));
        </script>
        <div class="tab-page" id="tabFile">
            <h2 class="tab">File</h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabFile') );
            </script>
            <form name="images" action="<?php echo $index.'&act=upload&pid='.$parent_id; ?>" method="post" enctype="multipart/form-data">
                <div id="imFields">
                    <div id="firstElt">
                        <table cellspacing="0" cellpadding="2" class="aForm" height="165">
                            <tr>
                                <!--th width="165" rowspan="3" class="imPreview" id="imBox">&nbsp;</th-->
                                <td width="70"><b><?php echo $lng['file'];?>:</b></td>
                                <td><input name="img[]" type="file" size="77" onchange="//uimPreview(this.value)"></td>
                            </tr>
                            <tr>
                                <td><b><?php echo $lng['name'];?>:</b></td>
                                <td><input name="name[]" type="text" size="75"></td>
                            </tr>
                            <tr>
                                <td valign="top"><b><?php echo $lng['description'];?>:</b></td>
                                <td>
                                    <textarea name="description[]" style="width: 475px;" cols="475" rows="3"></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <input type="submit" value="<?php echo $lng['upload_btn'];?>" name="upload_btn">
                <input type="button" value="<?php echo $lng['add_field_btn'];?>" onclick="javascript:addField(); void(0);">
            </form>
        </div>
        <div class="tab-page" id="tabZip">
            <h2 class="tab">Zip</h2>
            <script type="text/javascript">
                tpResources2.addTabPage( document.getElementById( 'tabZip') );
            </script>
            <ul>
                <li><?php echo $lng['char_limitation'];?></li>
                <li><?php echo $lng['zip_foldername'];?></li>
            </ul>
            <br />
            <form name="zipfile" action="<?php echo $index.'&act=uploadzip&pid='.$parent_id; ?>" 
                  method="post" enctype="multipart/form-data">
                <table cellspacing="0" cellpadding="2">
                    <tr>
                        <td><b><?php echo $lng['archive'];?>:</b></td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;<input name="zip" type="file" size="77" ></td>
                        <td><input type="submit" value="<?php echo $lng['upload_btn']; ?>"></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>
