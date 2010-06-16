<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
                <div id="e2g_bottommenu">
                    <ul class="actionButtons">
                        <p><b><?php echo $lng['withselected']; ?>:</b></p>
                        <li>
                            <a name="delete" href="javascript: submitform(3)" style="font-weight:bold;color:red">
                                <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/delete.png" alt="" /> <?php echo $lng['delete']; ?>
                            </a>
                        </li>
                        <li>
                            <a name="download" href="javascript: submitform(4)">
                                <img src="<?php echo E2G_MODULE_URL; ?>includes/icons/page_white_compressed.png" alt="" /> <?php echo $lng['download']; ?>
                            </a>
                        </li>
                        <li>
                            <!--select name="listactions">
                                <option value="move"><?php echo $lng['move']; ?></option>
                                <option value="copy"><?php echo $lng['copy']; ?></option>
                            </select-->
                            <?php echo $lng['movetofolder']; ?> :
                            <select name="newparent">
                                <option value="">&nbsp;</option>';

                                <?php echo $this->_get_folder_options(0); ?>

                            </select>
                            <?php echo $lng['and']; ?>
                            <select name="gotofolder">
                                <option value="gothere"><?php echo $lng['go_there']; ?></option>
                                <option value="stayhere"><?php echo $lng['stay_here']; ?></option>
                            </select>
                            <a name="move" href="javascript: submitform(5)">
                                <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/sort.png" alt="" /> <?php echo $lng['go']; ?>
                            </a>
                        </li>
                    </ul>
                    <ul class="actionButtons">
                        <li>
                            <?php echo $lng['tag'];?>:
                            <input name="tag_input" type="text" size="20" />&nbsp;
                            <a name="tag_add" href="javascript: submitform(6)" title="Add Tag">
                                <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/add.png" alt="" /> <?php echo $lng['add']; ?>
                            </a>&nbsp;
                            <a name="tag_del" href="javascript: submitform(7)" title="Remove Tag">
                                <img src="<?php echo  MODX_MANAGER_URL ; ?>media/style/MODxCarbon/images/icons/delete.png" alt="" /> <?php echo $lng['remove']; ?>
                            </a>
                        </li>
                    </ul>
                </div>