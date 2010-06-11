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
                        <td width="12%"><b><?php echo $lng['e2g_debug']; ?>:</b></td>
                        <td>
                            <input type="radio" name="e2g_debug" value="0" <?php echo ($e2g['e2g_debug']=='0' ? 'checked="checked"' : '');?>> <?php echo $lng['no'];?>
                            <input type="radio" name="e2g_debug" value="1" <?php echo ($e2g['e2g_debug']=='1' ? 'checked="checked"' : '');?>> <?php echo $lng['yes'];?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['e2g_debug_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['path']; ?>:</b></td>
                        <td width="88%"><input name="dir" type="text" value="<?php echo $e2g['dir']; ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo htmlspecialchars_decode($lng['dir_cfg_desc']); ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['e2g_encode']; ?>:</b></td>
                        <td width="88%">
                            <select name="e2g_encode">
                                <option value="none"<?php echo ($e2g['e2g_encode']=='none'?' selected':''); ?>><?php echo $lng['none']; ?></option>
                                <option value="UTF-8"<?php echo ($e2g['e2g_encode']=='UTF-8'?' selected':''); ?>><?php echo 'UTF-8 (PHP)'; ?></option>
                                <option value="UTF-8 (Rin)"<?php echo ($e2g['e2g_encode']=='UTF-8 (Rin)'?' selected':''); ?>><?php echo 'UTF-8 (Rin)'; ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['e2g_encode_cfg_desc']; ?></td>
                    </tr>
                </table>
            </div>
            <div class="tab-page" id="tabImagesSettings">
                <h2 class="tab"><?php echo $lng['settings_img']; ?></h2>
                <script type="text/javascript">
                    tpResources2.addTabPage( document.getElementById( 'tabImagesSettings') );
                </script>
                <table cellspacing="0" cellpadding="2" width="100%">
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['w']; ?>:</b></td>
                        <td><input name="maxw" type="text" value="<?php echo $e2g['maxw']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo htmlspecialchars_decode($lng['w_cfg_desc']); ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['h']; ?>:</b></td>
                        <td><input name="maxh" type="text" value="<?php echo $e2g['maxh']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo htmlspecialchars_decode($lng['h_cfg_desc']); ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['thq']; ?>:</b></td>
                        <td><input name="maxthq" type="text" value="<?php echo $e2g['maxthq']; ?>" size="3"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo htmlspecialchars_decode($lng['thq_cfg_desc']); ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['img_old_resize']; ?>:</b></td>
                        <td>
                            <input type="radio" name="resizeoldimg" value="0" <?php echo ($e2g['resizeoldimg']=='0' ? 'checked="checked"' : '');?>> No
                            <input type="radio" name="resizeoldimg" value="1" <?php echo ($e2g['resizeoldimg']=='1' ? 'checked="checked"' : '');?>> Yes
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['img_old_resize_cfg_desc']; ?></td>
                    </tr>
                </table>
            </div>
            <div class="tab-page" id="tabThumbnailsSettings">
                <h2 class="tab"><?php echo $lng['settings_thumb']; ?></h2>
                <script type="text/javascript">
                    tpResources2.addTabPage( document.getElementById( 'tabThumbnailsSettings') );
                </script>
                <table cellspacing="0" cellpadding="2" width="100%">
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['w']; ?>:</b></td>
                        <td><input name="w" type="text" value="<?php echo $e2g['w']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['w_thumb_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['h']; ?>:</b></td>
                        <td><input name="h" type="text" value="<?php echo $e2g['h']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['h_thumb_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['resize_type']; ?>:</b></td>
                        <td>
                            <select name="resize_type">
                                <option value="inner"<?php echo ($e2g['resize_type']=='inner'?' selected':''); ?>><?php echo $lng['inner']; ?></option>
                                <option value="shrink"<?php echo ($e2g['resize_type']=='shrink'?' selected':''); ?>><?php echo $lng['shrink']; ?></option>
                                <option value="resize"<?php echo ($e2g['resize_type']=='resize'?' selected':''); ?>><?php echo $lng['resize']; ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['resize_type_cfg_desc']; ?></td>
                    </tr>


                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['thbg_rgb']; ?>:</b></td>
                        <td>
                            R: <input name="thbg_red" type="text" value="<?php echo $e2g['thbg_red']; ?>" size="3">
                            G: <input name="thbg_green" type="text" value="<?php echo $e2g['thbg_green']; ?>" size="3">
                            B: <input name="thbg_blue" type="text" value="<?php echo $e2g['thbg_blue']; ?>" size="3">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['thbg_rgb_cfg_desc']; ?></td>
                    </tr>


                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['thq']; ?>:</b></td>
                        <td><input name="thq" type="text" value="<?php echo $e2g['thq']; ?>" size="3"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['thq_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['glib']; ?>:</b></td>
                        <td><select name="glib">
                                <?php require_once (E2G_MODULE_PATH.'includes/configs/libs.config.easy2gallery.php');
                                foreach ($glibs as $k => $v) {
                                    echo '<option value="'.$k.'"'.(($e2g['glib']==$k)?' selected="selected"':'').'>'.$v['alias'].'</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['glib_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['grid']; ?>:</b></td>
                        <td><input type="radio" name="grid" value="css" <?php echo ($e2g['grid']=='css' ? 'checked="checked"' : ''); ?>> <?php echo $lng['css'] ;?>
                            <input type="radio" name="grid" value="table" <?php echo ($e2g['grid']=='table' ? 'checked="checked"' : ''); ?>> <?php echo $lng['table'] ;?> <br>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['grid_cfg_desc']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2"><br><b class="success" style="font-size:120%"><?php echo $lng['settings_display']; ?></b></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['name_len']; ?>:</b></td>
                        <td><input name="name_len" type="text" value="<?php echo $e2g['name_len']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['name_len_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['cat_name_len']; ?>:</b></td>
                        <td><input name="cat_name_len" type="text" value="<?php echo $e2g['cat_name_len']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['cat_name_len_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['colls']; ?>:</b></td>
                        <td><input name="colls" type="text" value="<?php echo $e2g['colls']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['colls_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['limit']; ?>:</b></td>
                        <td><input name="limit" type="text" value="<?php echo $e2g['limit']; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['limit_cfg_desc']; ?></td>
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
                        <td colspan="2"><?php echo $lng['order_cfg_desc']; ?></td>
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
                        <td colspan="2"><?php echo $lng['order2_cfg_desc']; ?></td>
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
                        <td colspan="2"><?php echo $lng['tpl_css_path_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['gallery']; ?>:</b></td>
                        <td><input name="tpl" type="text" value="<?php echo $e2g['tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['tpl_path_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['dir']; ?>:</b></td>
                        <td><input name="dir_tpl" type="text" value="<?php echo $e2g['dir_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['tpl_path_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['thumb']; ?>:</b></td>
                        <td><input name="thumb_tpl" type="text" value="<?php echo $e2g['thumb_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['tpl_path_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['thumb']; ?> RAND:</b></td>
                        <td><input name="rand_tpl" type="text" value="<?php echo $e2g['rand_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['tpl_path_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['tpl_lp']; ?>:</b></td>
                        <td><input name="page_tpl" type="text" value="<?php echo $e2g['page_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['tpl_lp_path_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['tpl_lp_css']; ?>:</b></td>
                        <td><input name="page_tpl_css" type="text" value="<?php echo $e2g['page_tpl_css']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['tpl_css_path_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['tpl_lp_cmt']; ?>:</b></td>
                        <td><input name="page_comments_tpl" type="text" value="<?php echo $e2g['page_comments_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['tpl_lp_cmt_path_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['comments']; ?>:</b></td>
                        <td><input name="comments_tpl" type="text" value="<?php echo $e2g['comments_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_comments_cfg_desc']); ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['comments_row']; ?>:</b></td>
                        <td><input name="comments_row_tpl" type="text" value="<?php echo $e2g['comments_row_tpl']; ?>" size="70"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo htmlspecialchars_decode($lng['tpl_comments_cfg_desc']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2"><br><b class="success" style="font-size:120%"><?php echo $lng['css']; ?></b></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['grid_class']; ?>:</b></td>
                        <td><input name="grid_class" type="text" value="<?php echo $e2g['grid_class']; ?>" size="20"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['grid_class_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['cfg_e2g_currentcrumb_class']; ?>:</b></td>
                        <td><input name="e2g_currentcrumb_class" type="text" value="<?php echo $e2g['e2g_currentcrumb_class']; ?>" size="20"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['classname']; ?></td>
                    </tr>
                    <tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['cfg_e2gback_class']; ?>:</b></td>
                        <td><input name="e2gback_class" type="text" value="<?php echo $e2g['e2gback_class']; ?>" size="20"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['classname']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['cfg_e2gpnums_class']; ?>:</b></td>
                        <td><input name="e2gpnums_class" type="text" value="<?php echo $e2g['e2gpnums_class']; ?>" size="20"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['classname']; ?></td>
                    </tr>
                </table>

            </div>
            <div class="tab-page" id="tabWatermarks">
                <h2 class="tab"><?php echo $lng['watermarks']; ?></h2>
                <script type="text/javascript">
                    tpResources2.addTabPage( document.getElementById( 'tabWatermarks') );
                </script>
                <table cellspacing="0" cellpadding="2" width="100%">
                    <tr>
                        <td colspan="2">
                                <input name="ewm" type="radio" value="1"<?php echo ($e2g['ewm']==1?' checked':''); ?> style="border:0">
                                <b><?php echo $lng['on']; ?></b>
                                <input name="ewm" type="radio" value="0"<?php echo ($e2g['ewm']==0?' checked':''); ?> style="border:0">
                                <b><?php echo $lng['off']; ?></b>
                        </td>
                    </tr>
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['type']; ?>:</b></td>
                        <td><select size="1" name="wmtype">
                                <option value="text"<?php echo ($e2g['wmtype']=='text'?' selected':''); ?>><?php echo $lng['text']; ?></option>
                                <option value="image"<?php echo ($e2g['wmtype']=='image'?' selected':''); ?>><?php echo $lng['image']; ?></option></select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['watermark_type_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['watermark_textpath']; ?>:</b></td>
                        <td><input name="wmt" type="text" value="<?php echo $e2g['wmt']; ?>"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['watermark_text_path_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['watermark_horpos']; ?>:</b></td>
                        <td><select size="1" name="wmpos1">
                                <option value="1"<?php echo ($e2g['wmpos1']==1?' selected':''); ?>><?php echo $lng['left']; ?></option>
                                <option value="2"<?php echo ($e2g['wmpos1']==2?' selected':''); ?>><?php echo $lng['center']; ?></option>
                                <option value="3"<?php echo ($e2g['wmpos1']==3?' selected':''); ?>><?php echo $lng['right']; ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['watermark_horpos_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td><b><?php echo $lng['watermark_verpos']; ?>:</b></td>
                        <td><select size="1" name="wmpos2">
                                <option value="1"<?php echo ($e2g['wmpos2']==1?' selected':''); ?>><?php echo $lng['top']; ?></option>
                                <option value="2"<?php echo ($e2g['wmpos2']==2?' selected':''); ?>><?php echo $lng['center']; ?></option>
                                <option value="3"<?php echo ($e2g['wmpos2']==3?' selected':''); ?>><?php echo $lng['bottom']; ?></option></select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['watermark_verpos_cfg_desc']; ?></td>
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
                        <td colspan="2"><?php echo $lng['ecl_cfg_desc']; ?></td>
                    </tr>
                    <tr class="gridAltItem">
                        <td width="12%"><b><?php echo $lng['ecl_page']; ?>:</b></td>
                        <td><input name="ecl_page" type="text" value="<?php echo $e2g['ecl_page']; ?>" size="3"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $lng['ecl_cfg_desc']; ?></td>
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