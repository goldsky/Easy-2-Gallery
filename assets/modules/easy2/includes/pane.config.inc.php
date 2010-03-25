<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<div class="tab-page" id="Config">
    <h2 class="tab"><?php echo $lng['config']; ?></h2>
    <script type="text/javascript">
        tpResources.addTabPage(document.getElementById("Config"));
    </script>
    <form action="<?php echo $index; ?>&act=save_config" method="post">
        <input type="submit" value="<?php echo $lng['save']; ?>"> &nbsp; &nbsp; &nbsp;
        <input name="clean_cache" type="checkbox" value="1" style="border:0"> <?php echo $lng['clean_cache']; ?>
        <br /><br />
        <div class="tab-pane" id="tabConfigPane">
            <script type="text/javascript">
                tpResources2 = new WebFXTabPane(document.getElementById('tabConfigPane'));
            </script>
            <div class="tab-page" id="tabGeneralSettings">
                <h2 class="tab">General</h2>
                <script type="text/javascript">
                    tpResources2.addTabPage( document.getElementById( 'tabGeneralSettings') );
                </script>
                <table cellspacing="0" cellpadding="2" width="100%">
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['debug']; ?>:</b></td>
                        <td>
                            <input type="radio" name="debug" value="0" <?php echo ($e2g['debug']=='0' ? 'checked="checked"' : '');?>> No
                            <input type="radio" name="debug" value="1" <?php echo ($e2g['debug']=='1' ? 'checked="checked"' : '');?>> Yes
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com0']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['path']; ?>:</b></td>
                        <td width="88%"><input name="dir" type="text" value="<?php echo $e2g['dir']; ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com1']; ?></td>
                    </tr>
                </table>
            </div>
            <div class="tab-page" id="tabImagesSettings">
                <h2 class="tab"><?php echo $lng['newimgcfg']; ?></h2>
                <script type="text/javascript">
                    tpResources2.addTabPage( document.getElementById( 'tabImagesSettings') );
                </script>
                <table cellspacing="0" cellpadding="2" width="100%">
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['w']; ?>:</b></td>
                        <td><input name="maxw" type="text" value="<?php echo $e2g['maxw']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com2']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['h']; ?>:</b></td>
                        <td><input name="maxh" type="text" value="<?php echo $e2g['maxh']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com3']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['thq']; ?>:</b></td>
                        <td><input name="maxthq" type="text" value="<?php echo $e2g['maxthq']; ?>" size="3"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com4']; ?></td>
                    </tr>
                </table>
            </div>
            <div class="tab-page" id="tabThumbnailsSettings">
                <h2 class="tab"><?php echo $lng['thumbscfg']; ?></h2>
                <script type="text/javascript">
                    tpResources2.addTabPage( document.getElementById( 'tabThumbnailsSettings') );
                </script>
                <table cellspacing="0" cellpadding="2" width="100%">
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['w']; ?>:</b></td>
                        <td><input name="w" type="text" value="<?php echo $e2g['w']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com5']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['h']; ?>:</b></td>
                        <td><input name="h" type="text" value="<?php echo $e2g['h']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com6']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['resize_type']; ?>:</b></td>
                        <td>
                            <select name="resize_type">
                                <option value="inner"<?php echo ($e2g['resize_type']=='inner'?' selected':''); ?>><?php echo $lng['inner']; ?></option>
                                <option value="resize"<?php echo ($e2g['resize_type']=='resize'?' selected':''); ?>><?php echo $lng['resize']; ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com6a']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['thq']; ?>:</b></td>
                        <td><input name="thq" type="text" value="<?php echo $e2g['thq']; ?>" size="3"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com7']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['glib']; ?>:</b></td>
                        <td><select name="glib">
                                <?php require_once (MODX_BASE_PATH.'assets/modules/easy2/includes/config.libs.easy2gallery.php');
                                foreach ($glibs as $k => $v) {
                                    echo '<option value="'.$k.'"'.(($e2g['glib']==$k)?' selected="selected"':'').'>'.$v['alias'].'</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com10']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['notables']; ?>:</b></td>
                        <td><input type="radio" name="notables" value="1" <?php echo ($e2g['notables']=='1' ? 'checked="checked"' : ''); ?>> CSS
                            <input type="radio" name="notables" value="0" <?php echo ($e2g['notables']=='0' ? 'checked="checked"' : ''); ?>> Table <br>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><br><b class="success" style="font-size:120%"><?php echo $lng['thumbcnt']; ?></b></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['name_len']; ?>:</b></td>
                        <td><input name="name_len" type="text" value="<?php echo $e2g['name_len']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com18']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['cat_name_len']; ?>:</b></td>
                        <td><input name="cat_name_len" type="text" value="<?php echo $e2g['cat_name_len']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com20']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['colls']; ?>:</b></td>
                        <td><input name="colls" type="text" value="<?php echo $e2g['colls']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com8']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['limit']; ?>:</b></td>
                        <td><input name="limit" type="text" value="<?php echo $e2g['limit']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com9']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['order']; ?>:</b></td>
                        <td>
                            <select size="1" name="orderby">
                                <option value="date_added"<?php echo ($e2g['orderby']=='date_added'?' selected':''); ?>><?php echo $lng['date_added']; ?></option>
                                <option value="last_modified"<?php echo ($e2g['orderby']=='last_modified'?' selected':''); ?>><?php echo $lng['last_modified']; ?></option>
                                <option value="comments"<?php echo ($e2g['orderby']=='comments'?' selected':''); ?>><?php echo $lng['comments_cnt']; ?></option>
                                <option value="filename"<?php echo ($e2g['orderby']=='filename'?' selected':''); ?>><?php echo $lng['filename']; ?></option>
                                <option value="name"<?php echo ($e2g['orderby']=='name'?' selected':''); ?>><?php echo $lng['name']; ?></option>
                                <option value="random"<?php echo ($e2g['orderby']=='random'?' selected':''); ?>><?php echo $lng['random']; ?></option>
                            </select>
                            <select size="1" name="order">
                                <option value="ASC"<?php echo ($e2g['order']=='ASC'?' selected':''); ?>><?php echo $lng['asc']; ?></option>
                                <option value="DESC"<?php echo ($e2g['order']=='DESC'?' selected':''); ?>><?php echo $lng['desc']; ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com16']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['order2']; ?>:</b></td>
                        <td>
                            <select size="1" name="cat_orderby">
                                <option value="cat_id"<?php echo ($e2g['cat_orderby']=='cat_id'?' selected':''); ?>><?php echo $lng['date_added']; ?></option>
                                <option value="cat_name"<?php echo ($e2g['cat_orderby']=='cat_name'?' selected':''); ?>><?php echo $lng['name']; ?></option>
                                <option value="random"<?php echo ($e2g['cat_orderby']=='random'?' selected':''); ?>><?php echo $lng['random']; ?></option>
                            </select>
                            <select size="1" name="cat_order">
                                <option value="ASC"<?php echo ($e2g['cat_order']=='ASC'?' selected':''); ?>><?php echo $lng['asc']; ?></option>
                                <option value="DESC"<?php echo ($e2g['cat_order']=='DESC'?' selected':''); ?>><?php echo $lng['desc']; ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com21']; ?></td>
                    </tr>
                </table>
            </div>
            <div class="tab-page" id="tabTemplatesSettings">
                <h2 class="tab"><?php echo $lng['tpl']; ?></h2>
                <script type="text/javascript">
                    tpResources2.addTabPage( document.getElementById( 'tabTemplatesSettings') );
                </script>

                <table cellspacing="0" cellpadding="2" width="100%">
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['css']; ?>:</b></td>
                        <td><input name="css" type="text" value="<?php echo $e2g['css']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com17']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['gallery']; ?>:</b></td>
                        <td><input name="tpl" type="text" value="<?php echo $e2g['tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com17']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['dir']; ?>:</b></td>
                        <td><input name="dir_tpl" type="text" value="<?php echo $e2g['dir_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com17']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['thumb']; ?>:</b></td>
                        <td><input name="thumb_tpl" type="text" value="<?php echo $e2g['thumb_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com17']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['thumb']; ?> RAND:</b></td>
                        <td><input name="rand_tpl" type="text" value="<?php echo $e2g['rand_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com17']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['comments']; ?>:</b></td>
                        <td><input name="comments_tpl" type="text" value="<?php echo $e2g['comments_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com19']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['comments_row']; ?>:</b></td>
                        <td><input name="comments_row_tpl" type="text" value="<?php echo $e2g['comments_row_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com19']; ?></td>
                    </tr>
                </table>

            </div>
            <div class="tab-page" id="tabWatermarks">
                <h2 class="tab"><?php echo $lng['wm']; ?></h2>
                <script type="text/javascript">
                    tpResources2.addTabPage( document.getElementById( 'tabWatermarks') );
                </script>

                <table cellspacing="0" cellpadding="2" width="100%">
                    <tr>
                        <td colspan="2"><b>
                                <input name="ewm" type="radio" value="1"<?php echo ($e2g['ewm']==1?' checked':''); ?> style="border:0">
                                <?php echo $lng['on']; ?>
                                <input name="ewm" type="radio" value="0"<?php echo ($e2g['ewm']==0?' checked':''); ?> style="border:0">
                                <?php echo $lng['off']; ?></b></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['type']; ?>:</b></td>
                        <td><select size="1" name="wmtype">
                                <option value="text"<?php echo ($e2g['wmtype']=='text'?' selected':''); ?>><?php echo $lng['text']; ?></option>
                                <option value="image"<?php echo ($e2g['wmtype']=='image'?' selected':''); ?>><?php echo $lng['image']; ?></option></select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com12']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['wmt']; ?>:</b></td>
                        <td><input name="wmt" type="text" value="<?php echo $e2g['wmt']; ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com13']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['wmpos1']; ?>:</b></td>
                        <td><select size="1" name="wmpos1">
                                <option value="1"<?php echo ($e2g['wmpos1']==1?' selected':''); ?>><?php echo $lng['pos1']; ?></option>
                                <option value="2"<?php echo ($e2g['wmpos1']==2?' selected':''); ?>><?php echo $lng['pos2']; ?></option>
                                <option value="3"<?php echo ($e2g['wmpos1']==3?' selected':''); ?>><?php echo $lng['pos3']; ?></option></select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com14']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['wmpos2']; ?>:</b></td>
                        <td><select size="1" name="wmpos2">
                                <option value="1"<?php echo ($e2g['wmpos2']==1?' selected':''); ?>><?php echo $lng['pos4']; ?></option>
                                <option value="2"<?php echo ($e2g['wmpos2']==2?' selected':''); ?>><?php echo $lng['pos5']; ?></option>
                                <option value="3"<?php echo ($e2g['wmpos2']==3?' selected':''); ?>><?php echo $lng['pos6']; ?></option></select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com15']; ?></td>
                    </tr>
                </table>


            </div>
            <div class="tab-page" id="tabComments">
                <h2 class="tab"><?php echo $lng['comments']; ?></h2>
                <script type="text/javascript">
                    tpResources2.addTabPage( document.getElementById( 'tabComments') );
                </script>

                <table cellspacing="0" cellpadding="2" width="100%">
                    <tr>
                        <td colspan="2"><b>
                                <input name="ecm" type="radio" value="1"<?php echo ($e2g['ecm']==1?' checked':''); ?> style="border:0">
                                <?php echo $lng['on']; ?>
                                <input name="ecm" type="radio" value="0"<?php echo ($e2g['ecm']==0?' checked':''); ?> style="border:0">
                                <?php echo $lng['off']; ?></b></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['ecl']; ?>:</b></td>
                        <td><input name="ecl" type="text" value="<?php echo $e2g['ecl']; ?>" size="3"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cfg_com11']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['captcha']; ?>:</b></td>
                        <td><input name="captcha" type="checkbox"<?php echo ((isset($e2g['captcha']) && $e2g['captcha']==1)?' checked':''); ?> value="1" style="border:0"></td>
                    </tr>
                </table>


            </div>
        </div>
    </form>
</div>